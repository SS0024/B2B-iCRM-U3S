<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Tenders_model extends CRM_Model
{
    private $statuses;

    private $copy = false;

    public function __construct()
    {
        parent::__construct();
        $this->statuses = do_action('before_set_proposal_statuses', [
            6,
            4,
            2,
            3
        ]);
    }

    public function get_statuses()
    {
        return $this->statuses;
    }

    public function get_sale_agents()
    {
        return $this->db->query('SELECT DISTINCT(assigned) as sale_agent FROM tbltenders WHERE assigned != 0')->result_array();
    }

    public function get_proposals_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM tbltenders')->result_array();
    }

    public function do_kanban_query($status, $search = '', $page = 1, $sort = [], $count = false)
    {
        $default_pipeline_order = get_option('default_proposals_pipeline_sort');
        $default_pipeline_order_type = get_option('default_proposals_pipeline_sort_type');
        $limit = get_option('proposals_pipeline_limit');

        $has_permission_view = has_permission('proposals', '', 'view');
        $has_permission_view_own = has_permission('proposals', '', 'view_own');
        $allow_staff_view_proposals_assigned = get_option('allow_staff_view_proposals_assigned');
        $staffId = get_staff_user_id();

        $this->db->select('id,invoice_id,estimate_id,subject,rel_type,rel_id,total,date,open_till,currency,proposal_to,status');
        $this->db->from('tbltenders');
        $this->db->where('status', $status);
        if (!$has_permission_view) {
            $this->db->where(get_proposals_sql_where_staff(get_staff_user_id()));
        }
        if ($search != '') {
            if (!_startsWith($search, '#')) {
                $this->db->where('(
                phone LIKE "%' . $search . '%"
                OR
                zip LIKE "%' . $search . '%"
                OR
                content LIKE "%' . $search . '%"
                OR
                state LIKE "%' . $search . '%"
                OR
                city LIKE "%' . $search . '%"
                OR
                email LIKE "%' . $search . '%"
                OR
                address LIKE "%' . $search . '%"
                OR
                proposal_to LIKE "%' . $search . '%"
                OR
                total LIKE "%' . $search . '%"
                OR
                subject LIKE "%' . $search . '%")');
            } else {
                $this->db->where('tbltenders.id IN
                (SELECT rel_id FROM tbltags_in WHERE tag_id IN
                (SELECT id FROM tbltags WHERE name="' . strafter($search, '#') . '")
                AND tbltags_in.rel_type=\'proposal\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                ');
            }
        }

        if (isset($sort['sort_by']) && $sort['sort_by'] && isset($sort['sort']) && $sort['sort']) {
            $this->db->order_by($sort['sort_by'], $sort['sort']);
        } else {
            $this->db->order_by($default_pipeline_order, $default_pipeline_order_type);
        }

        if ($count == false) {
            if ($page > 1) {
                $page--;
                $position = ($page * $limit);
                $this->db->limit($limit, $position);
            } else {
                $this->db->limit($limit);
            }
        }

        if ($count == false) {
            return $this->db->get()->result_array();
        }

        return $this->db->count_all_results();
    }

    /**
     * Update proposal
     * @param mixed $data $_POST data
     * @param mixed $id proposal id
     * @return boolean
     */
    public function update($data, $id)
    {
        $affectedRows = 0;

        $data['allow_comments'] = isset($data['allow_comments']) ? 1 : 0;

        $data['devide_gst'] = (isset($data['devide_gst']) && $data['devide_gst'] == "yes") ? 1 : 0;

        $current_proposal = $this->get($id);

        $original_status = $current_proposal->status;

        $original_number = $current_proposal->number;

        $save_and_send = isset($data['save_and_send']);

        if (empty($data['rel_type'])) {
            $data['rel_id'] = null;
            $data['rel_type'] = '';
        } else {
            if (empty($data['rel_id'])) {
                $data['rel_id'] = null;
                $data['rel_type'] = '';
            }
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }

        if (isset($data['item_amount'])) {
            unset($data['item_amount']);
        }

        if (isset($data['item_discount'])) {
            unset($data['item_discount']);
        }

        if (isset($data['group_id'])) {
            unset($data['group_id']);
        }

        if (isset($data['item_other_status'])) {
            unset($data['item_other_status']);
        }

        if (isset($data['item_remark'])) {
            unset($data['item_remark']);
        }

        $items = [];
        if (isset($data['items'])) {
            $items = $data['items'];
            unset($data['items']);
        }

        $newitems = [];
        if (isset($data['newitems'])) {
            $newitems = $data['newitems'];
            unset($data['newitems']);
        }

        $div_cons = [];
        if (isset($data['div_con'])) {
            $div_cons = json_decode($data['div_con']);
            unset($data['div_con']);
        }

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $id, 'tender')) {
                $affectedRows++;
            }
        }

        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);

        $hook_data = do_action('before_proposal_updated', [
            'data' => $data,
            'id' => $id,
            'items' => $items,
            'newitems' => $newitems,
            'removed_items' => isset($data['removed_items']) ? $data['removed_items'] : [],
        ]);

        $data = $hook_data['data'];
        $data['removed_items'] = $hook_data['removed_items'];
        $newitems = $hook_data['newitems'];
        $items = $hook_data['items'];

        // Delete items checked to be removed from database
        foreach ($data['removed_items'] as $remove_item_id) {
            $original_item = $this->get_estimate_item($remove_item_id);
            if (handle_removed_sales_item_post($remove_item_id, 'tender')) {
                $affectedRows++;
                $this->log_estimate_activity($id, 'invoice_estimate_activity_removed_item', false, serialize([
                    $original_item->description,
                ]));
            }
        }
        if (isset($data['is_bulk']) && $data['is_bulk'] == "on") {
            $data['is_bulk'] = 1;
        } else {
            $data['is_bulk'] = 0;
        }
        if (!empty($div_cons)) {
            handle_removed_div_cons_post($id, 'tender');
            foreach ($div_cons as $con => $div) {
                if (isset($div)) {
                    add_new_div_con_item_post($div, $con, $id, 'tender');
                }
            }
        }
        unset($data['removed_items']);
        $data['modifieddate'] = date('Y-m-d H:i:s');
        $data['total_tax'] = ($data['total'] + $data['discount_total']) - ($data['subtotal'] + $data['packing_and_forwarding'] + $data['servicecharge']);
        $this->db->where('id', $id);
        $this->db->update('tbltenders', $data);
        if ($this->db->affected_rows() > 0) {
            // Check for status change
            if ($original_status != $data['status']) {
                $this->log_estimate_activity($current_proposal->id, 'Tender Status Updated: From: <original_status>' . $original_status . '</original_status> to <new_status>' . $data['status'] . '</new_status>', false);
            }
            if ($original_number != $data['number']) {
                $this->log_estimate_activity($current_proposal->id, 'Tender number changed from '.$original_number.' to '.format_inquiry_number($current_proposal->id), false);
            }
            $affectedRows++;
            $proposal_now = $this->get($id);
            if ($current_proposal->assigned != $proposal_now->assigned) {
                if ($proposal_now->assigned != get_staff_user_id()) {
                    $notified = add_notification([
                        'description' => 'not_proposal_assigned_to_you',
                        'touserid' => $proposal_now->assigned,
                        'fromuserid' => get_staff_user_id(),
                        'link' => 'inquiries/list_proposals/' . $id,
                        'additional_data' => serialize([
                            $proposal_now->subject,
                        ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$proposal_now->assigned]);
                    }
                }
            }
        }

        foreach ($items as $key => $item) {
            $original_item = $this->get_estimate_item($item['itemid']);

            if (update_sales_item_post($item['itemid'], $item, 'rate')) {
                $this->log_estimate_activity($id, 'invoice_estimate_activity_updated_item_rate', false, serialize([
                    $original_item->rate,
                    $item['rate'],
                ]));
                $affectedRows++;
            }

            if (update_sales_item_post($item['itemid'], $item, 'qty')) {
                $this->log_estimate_activity($id, 'invoice_estimate_activity_updated_qty_item', false, serialize([
                    $item['description'],
                    $original_item->qty,
                    $item['qty'],
                ]));
                $affectedRows++;
            }

            if (update_sales_item_post($item['itemid'], $item, 'item_discount')) {
                $this->log_estimate_activity($id, 'invoice_estimate_activity_updated_item_discount', false, serialize([
                    $item['description'],
                    $original_item->item_discount,
                    $item['item_discount'],
                ]));
                $affectedRows++;
            }

            if (update_sales_item_post($item['itemid'], $item, 'description')) {
                $this->log_estimate_activity($id, 'invoice_estimate_activity_updated_item_short_description', false, serialize([
                    $original_item->description,
                    $item['description'],
                ]));
                $affectedRows++;
            }

            if (update_sales_item_post($item['itemid'], $item, 'long_description')) {
                $this->log_estimate_activity($id, 'invoice_estimate_activity_updated_item_long_description', false, serialize([
                    $original_item->long_description,
                    $item['long_description'],
                ]));
                $affectedRows++;
            }

            if (update_sales_item_post($item['itemid'], $item)) {
                $this->log_estimate_activity($id, 'invoice_estimate_activity_updated_item', false);
                $affectedRows++;
            }

            if (isset($item['custom_fields'])) {
                if (handle_custom_fields_post($item['itemid'], $item['custom_fields'])) {
                    $affectedRows++;
                }
            }

            if (!isset($item['taxname']) || (isset($item['taxname']) && count($item['taxname']) == 0)) {
                if (delete_taxes_from_item($item['itemid'], 'tender')) {
                    $affectedRows++;
                }
            } else {
                $item_taxes = get_proposal_item_taxes($item['itemid']);
                $_item_taxes_names = [];
                foreach ($item_taxes as $_item_tax) {
                    array_push($_item_taxes_names, $_item_tax['taxname']);
                }
                $i = 0;
                foreach ($_item_taxes_names as $_item_tax) {
                    if (!in_array($_item_tax, $item['taxname'])) {
                        $this->db->where('id', $item_taxes[$i]['id'])
                            ->delete('tblitemstax');
                        if ($this->db->affected_rows() > 0) {
                            $affectedRows++;
                        }
                    }
                    $i++;
                }
                if (_maybe_insert_post_item_tax($item['itemid'], $item, $id, 'tender')) {
                    $affectedRows++;
                }
            }
        }

        foreach ($newitems as $key => $item) {
            if ($new_item_added = add_new_sales_item_post($item, $id, 'tender')) {
                _maybe_insert_post_item_tax($new_item_added, $item, $id, 'tender');
                $this->log_estimate_activity($id, 'invoice_estimate_activity_added_item', false, serialize([
                    $item['description'],
                ]));
                $affectedRows++;
            }
        }

        if ($affectedRows > 0) {
            // update_sales_total_tax_column($id, 'tender', 'tbltenders');
            logActivity('Tender Updated [ID:' . $id . ']');
            $this->log_estimate_activity($id, 'Tender Updated [ID:' . $id . ']');
        }

        if ($save_and_send === true) {
            $this->send_proposal_to_email($id, 'proposal-send-to-customer', true);
        }

        if ($affectedRows > 0) {
            do_action('after_proposal_updated', $id);

            return true;
        }

        return false;
    }

    /**
     * Get item by id
     * @param mixed $id item id
     * @return object
     */
    public function get_estimate_item($id)
    {
        $this->db->where('id', $id);

        return $this->db->get('tblitems_in')->row();
    }

    /**
     * Get proposals
     * @param mixed $id proposal id OPTIONAL
     * @return mixed
     */
    public function get($id = '', $where = [], $for_editor = false)
    {
        $this->db->where($where);

        if (is_client_logged_in()) {
            $this->db->where('status !=', 0);
        }

        $this->db->select('*,tblcurrencies.id as currencyid, tbltenders.id as id, tblcurrencies.name as currency_name, (CASE 
        WHEN rel_type="lead" THEN (SELECT CASE company WHEN "" THEN name ELSE company END as company FROM tblleads WHERE tblleads.id = tbltenders.rel_id LIMIT 1)
        WHEN rel_type="customer" THEN (SELECT ' . get_sql_select_client_company() . ' FROM tblclients where tblclients.userid = tbltenders.rel_id LIMIT 1)
        ELSE NULL
        END) as proposal_to, (CASE 
        WHEN rel_type="lead" THEN (SELECT tblleads.client_id FROM tblleads WHERE tblleads.id = tbltenders.rel_id LIMIT 1)
        ELSE 0
        END) as lead_customer_id');
        $this->db->from('tbltenders');
        $this->db->join('tblcurrencies', 'tblcurrencies.id = tbltenders.currency', 'left');

        if (is_numeric($id)) {
            $this->db->where('tbltenders.id', $id);
            $proposal = $this->db->get()->row();
            if ($proposal) {
                $proposal->attachments = $this->get_attachments($id);
                $proposal->items = get_items_by_type('tender', $id);
                $proposal->visible_attachments_to_customer_found = false;
                foreach ($proposal->attachments as $attachment) {
                    if ($attachment['visible_to_customer'] == 1) {
                        $proposal->visible_attachments_to_customer_found = true;

                        break;
                    }
                }
                if ($for_editor == false) {
                    $proposal = parse_proposal_content_merge_fields($proposal);
                }
            }

            return $proposal;
        }

        return $this->db->get()->result_array();
    }

    public function get_attachments($proposal_id, $id = '')
    {
        // If is passed id get return only 1 attachment
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $proposal_id);
        }
        $this->db->where('rel_type', 'tender');
        $result = $this->db->get('tblfiles');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }

    public function send_proposal_to_email($id, $template = '', $attachpdf = true, $cc = '')
    {
        $this->load->model('emails_model');

        $this->emails_model->set_rel_id($id);
        $this->emails_model->set_rel_type('tender');

        $proposal = $this->get($id);

        // Proposal status is draft update to sent
        if ($proposal->status == 6) {
            $this->db->where('id', $id);
            $this->db->update('tbltenders', ['status' => 4]);
            $proposal = $this->get($id);
        }

        if ($attachpdf) {
            $pdf = proposal_pdf($proposal);
            $attach = $pdf->Output(slug_it($proposal->subject) . '.pdf', 'S');
            $this->emails_model->add_attachment([
                'attachment' => $attach,
                'filename' => slug_it($proposal->subject) . '.pdf',
                'type' => 'application/pdf',
            ]);
        }

        if ($this->input->post('email_attachments')) {
            $_other_attachments = $this->input->post('email_attachments');
            foreach ($_other_attachments as $attachment) {
                $_attachment = $this->get_attachments($id, $attachment);
                $this->emails_model->add_attachment([
                    'attachment' => get_upload_path_by_type('tender') . $id . '/' . $_attachment->file_name,
                    'filename' => $_attachment->file_name,
                    'type' => $_attachment->filetype,
                    'read' => true,
                ]);
            }
        }

        $merge_fields = [];
        $merge_fields = array_merge($merge_fields, get_proposal_merge_fields($proposal->id));
        $sent = $this->emails_model->send_email_template($template, $proposal->email, $merge_fields, '', $cc);
        if ($sent) {

            // Set to status sent
            $this->db->where('id', $id);
            $this->db->update('tbltenders', [
                'status' => 4,
            ]);

            do_action('proposal_sent', $id);

            return true;
        }

        return false;
    }

    public function update_pipeline($data)
    {
        $this->mark_action_status($data['status'], $data['proposalid']);
        foreach ($data['order'] as $order_data) {
            $this->db->where('id', $order_data[0]);
            $this->db->update('tbltenders', [
                'pipeline_order' => $order_data[1],
            ]);
        }
    }

    /**
     * Take proposal action (change status) manually
     * @param mixed $status status id
     * @param mixed $id proposal id
     * @param boolean $client is request coming from client side or not
     * @return boolean
     */
    public function mark_action_status($status, $id, $client = false)
    {
        $original_proposal = $this->get($id);
        $this->db->where('id', $id);
        $this->db->update('tbltenders', [
            'status' => $status,
        ]);

        if ($this->db->affected_rows() > 0) {
            // Client take action
            if ($client == true) {
                $revert = false;
                // Declined
                if ($status == 2) {
                    $message = 'not_proposal_proposal_declined';
                } elseif ($status == 3) {
                    $message = 'not_proposal_proposal_accepted';
                    // Accepted
                } else {
                    $revert = true;
                }
                // This is protection that only 3 and 4 statuses can be taken as action from the client side
                if ($revert == true) {
                    $this->db->where('id', $id);
                    $this->db->update('tbltenders', [
                        'status' => $original_proposal->status,
                    ]);

                    return false;
                }
                $merge_fields = [];
                $merge_fields = array_merge($merge_fields, get_proposal_merge_fields($original_proposal->id));

                // Get creator and assigned;
                $this->db->where('staffid', $original_proposal->addedfrom);
                $this->db->or_where('staffid', $original_proposal->assigned);
                $staff_proposal = $this->db->get('tblstaff')->result_array();
                $notifiedUsers = [];
                foreach ($staff_proposal as $member) {
                    $notified = add_notification([
                        'fromcompany' => true,
                        'touserid' => $member['staffid'],
                        'description' => $message,
                        'link' => 'inquiries/list_proposals/' . $id,
                        'additional_data' => serialize([
                            format_proposal_number($id),
                        ]),
                    ]);
                    if ($notified) {
                        array_push($notifiedUsers, $member['staffid']);
                    }
                }

                pusher_trigger_notification($notifiedUsers);

                $this->load->model('emails_model');

                $this->emails_model->set_rel_id($id);
                $this->emails_model->set_rel_type('tender');

                // Send thank you to the customer email template
                if ($status == 3) {
                    foreach ($staff_proposal as $member) {
                        $this->emails_model->send_email_template('proposal-client-accepted', $member['email'], $merge_fields);
                    }
                    $this->emails_model->send_email_template('proposal-client-thank-you', $original_proposal->email, $merge_fields);
                    do_action('proposal_accepted', $id);
                } else {
                    // Client declined send template to admin
                    foreach ($staff_proposal as $member) {
                        $this->emails_model->send_email_template('proposal-client-declined', $member['email'], $merge_fields);
                    }
                    do_action('proposal_declined', $id);
                }
            } else {
                // in case admin mark as open the the open till date is smaller then current date set open till date 7 days more
                if ((date('Y-m-d', strtotime($original_proposal->open_till)) < date('Y-m-d')) && $status == 1) {
                    $open_till = date('Y-m-d', strtotime('+7 DAY', strtotime(date('Y-m-d'))));
                    $this->db->where('id', $id);
                    $this->db->update('tbltenders', [
                        'open_till' => $open_till,
                    ]);
                }
            }
            // Admin marked estimate
            $this->log_estimate_activity($id, 'Marked Tender as '.'<status>' . format_proposal_status($status, '', false) . '</status>', false);
            logActivity('Tender Status Changes [InquiryID:' . $id . ', Status:' . format_proposal_status($status, '', false) . ',Client Action: ' . (int)$client . ']');

            return true;
        }

        return false;
    }

    /**
     * Add proposal comment
     * @param mixed $data $_POST comment data
     * @param boolean $client is request coming from the client side
     */
    public function add_comment($data, $client = false)
    {
        if (is_staff_logged_in()) {
            $client = false;
        }

        if (isset($data['action'])) {
            unset($data['action']);
        }
        $data['dateadded'] = date('Y-m-d H:i:s');
        if ($client == false) {
            $data['staffid'] = get_staff_user_id();
        }
        $data['content'] = nl2br($data['content']);
        $this->db->insert('tblproposalcomments', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $proposal = $this->get($data['proposalid']);

            // No notifications client when proposal is with draft status
            if ($proposal->status == '6' && $client == false) {
                return true;
            }

            $merge_fields = [];
            $merge_fields = array_merge($merge_fields, get_proposal_merge_fields($proposal->id));

            $this->load->model('emails_model');

            $this->emails_model->set_rel_id($data['proposalid']);
            $this->emails_model->set_rel_type('tender');

            if ($client == true) {
                // Get creator and assigned
                $this->db->select('staffid,email,phonenumber');
                $this->db->where('staffid', $proposal->addedfrom);
                $this->db->or_where('staffid', $proposal->assigned);
                $staff_proposal = $this->db->get('tblstaff')->result_array();
                $notifiedUsers = [];
                foreach ($staff_proposal as $member) {
                    $notified = add_notification([
                        'description' => 'not_proposal_comment_from_client',
                        'touserid' => $member['staffid'],
                        'fromcompany' => 1,
                        'fromuserid' => null,
                        'link' => 'inquiries/list_proposals/' . $data['proposalid'],
                        'additional_data' => serialize([
                            $proposal->subject,
                        ]),
                    ]);

                    if ($notified) {
                        array_push($notifiedUsers, $member['staffid']);
                    }

                    // Send email/sms to admin that client commented
                    $this->emails_model->send_email_template('proposal-comment-to-admin', $member['email'], $merge_fields);
                    $this->sms->trigger(SMS_TRIGGER_PROPOSAL_NEW_COMMENT_TO_STAFF, $member['phonenumber'], $merge_fields);
                }
                pusher_trigger_notification($notifiedUsers);
            } else {
                // Send email/sms to client that admin commented
                $this->emails_model->send_email_template('proposal-comment-to-client', $proposal->email, $merge_fields);
                $this->sms->trigger(SMS_TRIGGER_PROPOSAL_NEW_COMMENT_TO_CUSTOMER, $proposal->phone, $merge_fields);
            }

            return true;
        }

        return false;
    }

    public function edit_comment($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('tblproposalcomments', [
            'content' => nl2br($data['content']),
        ]);
        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * Get proposal comments
     * @param mixed $id proposal id
     * @return array
     */
    public function get_comments($id)
    {
        $this->db->where('proposalid', $id);
        $this->db->order_by('dateadded', 'ASC');

        return $this->db->get('tblproposalcomments')->result_array();
    }

    /**
     * Remove proposal comment
     * @param mixed $id comment id
     * @return boolean
     */
    public function remove_comment($id)
    {
        $comment = $this->get_comment($id);
        $this->db->where('id', $id);
        $this->db->delete('tblproposalcomments');
        if ($this->db->affected_rows() > 0) {
            logActivity('Proposal Comment Removed [ProposalID:' . $comment->proposalid . ', Comment Content: ' . $comment->content . ']');

            return true;
        }

        return false;
    }

    /**
     * Get proposal single comment
     * @param mixed $id comment id
     * @return object
     */
    public function get_comment($id)
    {
        $this->db->where('id', $id);

        return $this->db->get('tblproposalcomments')->row();
    }

    /**
     * Copy proposal
     * @param mixed $id proposal id
     * @return mixed
     */
    public function copy($id)
    {
        $this->copy = true;
        $proposal = $this->get($id, [], true);
        $not_copy_fields = [
            'addedfrom',
            'id',
            'datecreated',
            'hash',
            'status',
            'invoice_id',
            'estimate_id',
            'is_expiry_notified',
            'date_converted',
            'signature',
            'acceptance_firstname',
            'acceptance_lastname',
            'acceptance_email',
            'acceptance_date',
            'acceptance_ip',
        ];
        $fields = $this->db->list_fields('tbltenders');
        $insert_data = [];
        foreach ($fields as $field) {
            if (!in_array($field, $not_copy_fields)) {
                $insert_data[$field] = $proposal->$field;
            }
        }

        $insert_data['addedfrom'] = get_staff_user_id();
        $insert_data['datecreated'] = date('Y-m-d H:i:s');
        $insert_data['date'] = _d(date('Y-m-d'));
        $insert_data['status'] = 6;
        $insert_data['hash'] = app_generate_hash();

        // in case open till is expired set new 7 days starting from current date
        if ($insert_data['open_till'] && get_option('proposal_due_after') != 0) {
            $insert_data['open_till'] = _d(date('Y-m-d', strtotime('+' . get_option('proposal_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }

        $insert_data['newitems'] = [];
        $custom_fields_items = get_custom_fields('items');
        $key = 1;
        foreach ($proposal->items as $item) {
            $insert_data['newitems'][$key]['description'] = $item['description'];
            $insert_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $insert_data['newitems'][$key]['qty'] = $item['qty'];
            $insert_data['newitems'][$key]['unit'] = $item['unit'];
            $insert_data['newitems'][$key]['taxname'] = [];
            $taxes = get_proposal_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                // tax name is in format TAX1|10.00
                array_push($insert_data['newitems'][$key]['taxname'], $tax['taxname']);
            }
            $insert_data['newitems'][$key]['item_amount'] = $item['item_amount'];
            $insert_data['newitems'][$key]['rate'] = $item['rate'];
            $insert_data['newitems'][$key]['order'] = $item['item_order'];
            foreach ($custom_fields_items as $cf) {
                $insert_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);

                if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                }
            }
            $key++;
        }

        $id = $this->add($insert_data);

        if ($id) {
            $custom_fields = get_custom_fields('tender');
            foreach ($custom_fields as $field) {
                $value = get_custom_field_value($proposal->id, $field['id'], 'tender', false);
                if ($value == '') {
                    continue;
                }
                $this->db->insert('tblcustomfieldsvalues', [
                    'relid' => $id,
                    'fieldid' => $field['id'],
                    'fieldto' => 'tender',
                    'value' => $value,
                ]);
            }

            $tags = get_tags_in($proposal->id, 'tender');
            handle_tags_save($tags, $id, 'tender');

            logActivity('Copied Proposal ' . format_proposal_number($proposal->id));

            return $id;
        }

        return false;
    }

    /**
     * Inserting new proposal function
     * @param mixed $data $_POST data
     */
    public function add($data)
    {
        // echo "<pre>";print_r($data);exit;
        $data['allow_comments'] = isset($data['allow_comments']) ? 1 : 0;

        $save_and_send = isset($data['save_and_send']);

        $tags = isset($data['tags']) ? $data['tags'] : '';

        $data['devide_gst'] = (isset($data['devide_gst']) && $data['devide_gst'] == "yes") ? 1 : 0;

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        if (isset($data['item_amount'])) {
            unset($data['item_amount']);
        }

        if (isset($data['item_discount'])) {
            unset($data['item_discount']);
        }

        if (isset($data['group_id'])) {
            unset($data['group_id']);
        }

        $data['address'] = trim($data['address']);
        $data['address'] = nl2br($data['address']);

        $data['datecreated'] = date('Y-m-d H:i:s');
        $data['addedfrom'] = get_staff_user_id();
        $data['hash'] = app_generate_hash();

        if (isset($data['is_bulk']) && $data['is_bulk'] == "on") {
            $data['is_bulk'] = 1;
        } else {
            $data['is_bulk'] = 0;
        }


        if (empty($data['rel_type'])) {
            unset($data['rel_type']);
            unset($data['rel_id']);
        } else {
            if (empty($data['rel_id'])) {
                unset($data['rel_type']);
                unset($data['rel_id']);
            }
        }

        $items = [];
        if (isset($data['newitems'])) {
            $items = $data['newitems'];
            unset($data['newitems']);
        }

        if (isset($data['reminder_date'])){
            $reminderDate = $data['reminder_date'];
            unset($data['reminder_date']);
        }

        $div_cons = [];
        if (isset($data['div_con'])) {
            $div_cons = json_decode($data['div_con']);
            unset($data['div_con']);
            unset($data['division']);
            unset($data['contact']);
        }

        if ($this->copy == false) {
            $data['content'] = '{proposal_items}';
        }

        $hook_data = do_action('before_create_proposal', [
            'data' => $data,
            'items' => $items,
        ]);

        $data = $hook_data['data'];
        $items = $hook_data['items'];
        $data['modifieddate'] = date('Y-m-d H:i:s');
        $data['total_tax'] = ($data['total'] + $data['discount_total']) - ($data['subtotal'] + $data['packing_and_forwarding'] + $data['servicecharge']);
        $this->db->insert('tbltenders', $data);
        // echo $this->db->last_query();exit;
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }

            handle_tags_save($tags, $insert_id, 'tender');

            foreach ($div_cons as $con => $div) {
                if (isset($div)) {
                    add_new_div_con_item_post($div, $con, $insert_id, 'tender');
                }
            }

            foreach ($items as $key => $item) {
                if ($itemid = add_new_sales_item_post($item, $insert_id, 'tender')) {
                    _maybe_insert_post_item_tax($itemid, $item, $insert_id, 'tender');
                }
            }

            /*$proposal = $this->get($insert_id);
            if ($proposal->assigned != 0) {
                if ($proposal->assigned != get_staff_user_id()) {
                    $notified = add_notification([
                        'description' => 'not_proposal_assigned_to_you',
                        'touserid' => $proposal->assigned,
                        'fromuserid' => get_staff_user_id(),
                        'link' => 'inquiries/list_proposals/' . $insert_id,
                        'additional_data' => serialize([
                            $proposal->subject,
                        ]),
                    ]);
                    if ($notified) {
                        pusher_trigger_notification([$proposal->assigned]);
                    }
                }
            }*/

            if (isset($reminderDate)){
                $this->load->model('misc_model');
                $reminderData = [
                   'date' => $reminderDate,
                   'description' => 'Reminder For EMD',
                   'rel_type' => 'tender',
                   'staff' => $data['assigned'],
                   'rel_id' => $insert_id,
                ];
                $this->misc_model->add_reminder($reminderData, $insert_id);
            }

            if ($data['rel_type'] == 'lead') {
                $this->load->model('leads_model');
                $this->leads_model->log_lead_activity($data['rel_id'], 'not_lead_activity_created_proposal', false, serialize([
                    '<a href="' . admin_url('inquiries/list_proposals/' . $insert_id) . '" target="_blank">' . $data['subject'] . '</a>',
                ]));
            }

            // update_sales_total_tax_column($insert_id, 'tender', 'tbltenders');
            logActivity('New tender Created [ID:' . $insert_id . ']');
            $this->log_estimate_activity($insert_id, 'New tender created.');

            if ($save_and_send === true) {
                $this->send_proposal_to_email($insert_id, 'proposal-send-to-customer', true);
            }

            do_action('proposal_created', $insert_id);

            return $insert_id;
        }

        return false;
    }

    /**
     * Delete proposal
     * @param mixed $id proposal id
     * @return boolean
     */
    public function delete($id)
    {
        $this->clear_signature($id);
        $proposal = $this->get($id);

        $this->db->where('id', $id);
        $this->db->delete('tbltenders');
        if ($this->db->affected_rows() > 0) {
            delete_tracked_emails($id, 'tender');

            // Get related tasks
            $this->db->where('rel_type', 'tender');
            $this->db->where('rel_id', $id);

            $tasks = $this->db->get('tblstafftasks')->result_array();
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id']);
            }

            $attachments = $this->get_attachments($id);
            foreach ($attachments as $attachment) {
                $this->delete_attachment($attachment['id']);
            }

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'tender');
            $this->db->delete('tblnotes');

            $this->db->where('relid IN (SELECT id from tblitems_in WHERE rel_type="proposal" AND rel_id="' . $id . '")');
            $this->db->where('fieldto', 'items');
            $this->db->delete('tblcustomfieldsvalues');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'tender');
            $this->db->delete('tblitems_in');


            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'tender');
            $this->db->delete('tblitemstax');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'tender');
            $this->db->delete('tbltags_in');

            // Delete the custom field values
            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'tender');
            $this->db->delete('tblcustomfieldsvalues');

            $this->db->where('rel_type', 'tender');
            $this->db->where('rel_id', $id);
            $this->db->delete('tblreminders');

            $this->db->where('rel_type', 'tender');
            $this->db->where('rel_id', $id);
            $this->db->delete('tblviewstracking');

            logActivity('Tender Deleted [InquiryID:' . $id . ']');

            return true;
        }

        return false;
    }

    public function clear_signature($id)
    {
        $this->db->select('signature');
        $this->db->where('id', $id);
        $proposal = $this->db->get('tbltenders')->row();

        if ($proposal) {
            $this->db->where('id', $id);
            $this->db->update('tbltenders', ['signature' => null]);

            if (!empty($proposal->signature)) {
                unlink(get_upload_path_by_type('tender') . $id . '/' . $proposal->signature);
            }

            return true;
        }

        return false;
    }

    /**
     *  Delete proposal attachment
     * @param mixed $id attachmentid
     * @return  boolean
     */
    public function delete_attachment($id)
    {
        $attachment = $this->get_attachments('', $id);
        $deleted = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_by_type('tender') . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete('tblfiles');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                logActivity('Proposal Attachment Deleted [ID: ' . $attachment->rel_id . ']');
            }
            if (is_dir(get_upload_path_by_type('tender') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('tender') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('tender') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * Get relation proposal data. Ex lead or customer will return the necesary db fields
     * @param mixed $rel_id
     * @param string $rel_type customer/lead
     * @return object
     */
    public function get_relation_data_values($rel_id, $rel_type)
    {
        $data = new StdClass();
        if ($rel_type == 'customer') {
            $this->db->where('userid', $rel_id);
            $_data = $this->db->get('tblclients')->row();

            $primary_contact_id = get_primary_contact_user_id($rel_id);

            if ($primary_contact_id) {
                $contact = $this->clients_model->get_contact($primary_contact_id);
                $data->email = $contact->email;
            }

            $data->phone = $_data->phonenumber;
            $data->is_using_company = false;
            if (isset($contact)) {
                $data->to = $contact->firstname . ' ' . $contact->lastname;
            } else {
                if (!empty($_data->company)) {
                    $data->to = $_data->company;
                    $data->is_using_company = true;
                }
            }
            $data->company = $_data->company;
            $data->address = clear_textarea_breaks($_data->address);
            $data->zip = $_data->zip;
            $data->country = $_data->country;
            $data->state = $_data->state;
            $data->city = $_data->city;

            $default_currency = $this->clients_model->get_customer_default_currency($rel_id);
            if ($default_currency != 0) {
                $data->currency = $default_currency;
            }
        } elseif ($rel_type = 'lead') {
            $this->db->where('id', $rel_id);
            $_data = $this->db->get('tblleads')->row();
            $data->phone = $_data->phonenumber;

            $data->is_using_company = false;

            if (empty($_data->company)) {
                $data->to = $_data->name;
            } else {
                $data->to = $_data->company;
                $data->is_using_company = true;
            }

            $data->company = $_data->company;
            $data->address = $_data->address;
            $data->email = $_data->email;
            $data->zip = $_data->zip;
            $data->country = $_data->country;
            $data->state = $_data->state;
            $data->city = $_data->city;
        }

        $company_state = get_option('company_state');

        if (isset($data->state) && !empty($data->state) && strtolower($data->state) == strtolower($company_state)) {
            $data->devide_gst = "yes";
        } else {
            $data->devide_gst = 'no';
        }

        return $data;
    }

    /**
     * Sent proposal to email
     * @param mixed $id proposalid
     * @param string $template email template to sent
     * @param boolean $attachpdf attach proposal pdf or not
     * @return boolean
     */
    public function send_expiry_reminder($id)
    {
        $proposal = $this->get($id);
        $pdf = proposal_pdf($proposal);
        $attach = $pdf->Output(slug_it($proposal->subject) . '.pdf', 'S');

        // For all cases update this to prevent sending multiple reminders eq on fail
        $this->db->where('id', $proposal->id);
        $this->db->update('tbltenders', [
            'is_expiry_notified' => 1,
        ]);

        $this->load->model('emails_model');

        $this->emails_model->set_rel_id($id);
        $this->emails_model->set_rel_type('tender');

        $this->emails_model->add_attachment([
            'attachment' => $attach,
            'filename' => slug_it($proposal->subject) . '.pdf',
            'type' => 'application/pdf',
        ]);

        $merge_fields = [];
        $merge_fields = array_merge($merge_fields, get_proposal_merge_fields($proposal->id));
        $sent = $this->emails_model->send_email_template('proposal-expiry-reminder', $proposal->email, $merge_fields);

        if (can_send_sms_based_on_creation_date($proposal->datecreated)) {
            $sms_sent = $this->sms->trigger(SMS_TRIGGER_PROPOSAL_EXP_REMINDER, $proposal->phone, $merge_fields);
        }

        return true;
    }


    /**
     * All estimate activity
     * @param mixed $id estimateid
     * @return array
     */
    public function get_estimate_activity($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'tender');
        $this->db->order_by('date', 'asc');

        return $this->db->get('tblsalesactivity')->result_array();
    }

    /**
     * Log estimate activity to database
     * @param mixed $id estimateid
     * @param string $description activity description
     */
    public function log_estimate_activity($id, $description = '', $client = false, $additional_data = '')
    {
        $staffid = get_staff_user_id();
        $full_name = get_staff_full_name(get_staff_user_id());
        if (DEFINED('CRON')) {
            $staffid = '[CRON]';
            $full_name = '[CRON]';
        } elseif ($client == true) {
            $staffid = null;
            $full_name = '';
        }

        $this->db->insert('tblsalesactivity', [
            'description' => $description,
            'date' => date('Y-m-d H:i:s'),
            'rel_id' => $id,
            'rel_type' => 'tender',
            'staffid' => $staffid,
            'full_name' => $full_name,
            'additional_data' => $additional_data,
        ]);
    }
}
