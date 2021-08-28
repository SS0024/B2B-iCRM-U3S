<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Requsite_form_model extends CRM_Model
{
    private $statuses;

    private $shipping_fields = ['shipping_street', 'shipping_city', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country'];

    public function __construct()
    {
        parent::__construct();
        $this->statuses = [7 => 'Pending',
            1 => 'Delay due to Customer',
            2 => 'Delay due to Engineer',
            3 => 'Delay due to Principal',
            4 => 'Clarification Required',
            5 => 'Canceled',
            6 => 'Quoted'];
    }

    /**
     * Get unique sale agent for estimates / Used for filters
     * @return array
     */
    public function get_sale_agents()
    {
        return $this->db->query("SELECT DISTINCT(sale_agent) as sale_agent, CONCAT(firstname, ' ', lastname) as full_name FROM tblrequsiteform JOIN tblstaff on tblstaff.staffid=tblrequsiteform.sale_agent WHERE sale_agent != 0")->result_array();
    }

    /**
     * Function that will perform estimates pipeline query
     * @param  mixed  $status
     * @param  string  $search
     * @param  integer $page
     * @param  array   $sort
     * @param  boolean $count
     * @return array
     */
    public function do_kanban_query($status, $search = '', $page = 1, $sort = [], $count = false)
    {
        $default_pipeline_order      = get_option('default_estimates_pipeline_sort');
        $default_pipeline_order_type = get_option('default_estimates_pipeline_sort_type');
        $limit                       = get_option('estimates_pipeline_limit');

        $fields_client    = $this->db->list_fields('tblclients');
        $fields_estimates = $this->db->list_fields('tblestimates');

        $has_permission_view = has_permission('requisite_forms', '', 'view');

        $this->db->select('tblestimates.id,status,invoiceid,' . get_sql_select_client_company() . ',total,currency,symbol,date,expirydate,clientid');
        $this->db->from('tblestimates');
        $this->db->join('tblclients', 'tblclients.userid = tblestimates.clientid', 'left');
        $this->db->join('tblcurrencies', 'tblestimates.currency = tblcurrencies.id');
        $this->db->where('status', $status);

        if (!$has_permission_view) {
            $this->db->where(get_requsite_form_where_sql_for_staff(get_staff_user_id()));
        }

        if ($search != '') {
            if (!_startsWith($search, '#')) {
                $where = '(';
                $i     = 0;
                foreach ($fields_client as $f) {
                    $where .= 'tblclients.' . $f . ' LIKE "%' . $search . '%"';
                    $where .= ' OR ';
                    $i++;
                }
                $i = 0;
                foreach ($fields_estimates as $f) {
                    $where .= 'tblestimates.' . $f . ' LIKE "%' . $search . '%"';
                    $where .= ' OR ';

                    $i++;
                }
                $where = substr($where, 0, -4);
                $where .= ')';
                $this->db->where($where);
            } else {
                $this->db->where('tblestimates.id IN
                (SELECT rel_id FROM tbltags_in WHERE tag_id IN
                (SELECT id FROM tbltags WHERE name="' . strafter($search, '#') . '")
                AND tbltags_in.rel_type=\'estimate\' GROUP BY rel_id HAVING COUNT(tag_id) = 1)
                ');
            }
        }

        if (isset($sort['sort_by']) && $sort['sort_by'] && isset($sort['sort']) && $sort['sort']) {
            $this->db->order_by('tblestimates.' . $sort['sort_by'], $sort['sort']);
        } else {
            $this->db->order_by('tblestimates.' . $default_pipeline_order, $default_pipeline_order_type);
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
     * Copy estimate
     * @param  mixed $id estimate id to copy
     * @return mixed
     */
    public function copy($id)
    {
        $_estimate                       = $this->get($id);
        $new_estimate_data               = [];
        $new_estimate_data['clientid']   = $_estimate->clientid;
        $new_estimate_data['project_id'] = $_estimate->project_id;
        $new_estimate_data['number']     = get_option('next_estimate_number');
        $new_estimate_data['date']       = _d(date('Y-m-d'));
        $new_estimate_data['expirydate'] = null;

        if ($_estimate->expirydate && get_option('estimate_due_after') != 0) {
            $new_estimate_data['expirydate'] = _d(date('Y-m-d', strtotime('+' . get_option('estimate_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }

        $new_estimate_data['show_quantity_as'] = $_estimate->show_quantity_as;
        $new_estimate_data['currency']         = $_estimate->currency;
        $new_estimate_data['subtotal']         = $_estimate->subtotal;
        $new_estimate_data['servicecharge']         = $_estimate->servicecharge;
        $new_estimate_data['packing_and_forwarding']         = $_estimate->packing_and_forwarding;
        $new_estimate_data['devide_gst']         = ($_estimate->devide_gst == 1 ? 'yes':'no');
        $new_estimate_data['total']            = $_estimate->total;
        $new_estimate_data['adminnote']        = $_estimate->adminnote;
        $new_estimate_data['adjustment']       = $_estimate->adjustment;
        $new_estimate_data['discount_percent'] = $_estimate->discount_percent;
        $new_estimate_data['discount_total']   = $_estimate->discount_total;
        $new_estimate_data['discount_type']    = $_estimate->discount_type;
        $new_estimate_data['terms']            = $_estimate->terms;
        $new_estimate_data['sale_agent']       = $_estimate->sale_agent;
        $new_estimate_data['reference_no']     = $_estimate->reference_no;
        // Since version 1.0.6
        $new_estimate_data['billing_street']   = clear_textarea_breaks($_estimate->billing_street);
        $new_estimate_data['billing_city']     = $_estimate->billing_city;
        $new_estimate_data['billing_state']    = $_estimate->billing_state;
        $new_estimate_data['billing_zip']      = $_estimate->billing_zip;
        $new_estimate_data['billing_country']  = $_estimate->billing_country;
        $new_estimate_data['shipping_street']  = clear_textarea_breaks($_estimate->shipping_street);
        $new_estimate_data['shipping_city']    = $_estimate->shipping_city;
        $new_estimate_data['shipping_state']   = $_estimate->shipping_state;
        $new_estimate_data['shipping_zip']     = $_estimate->shipping_zip;
        $new_estimate_data['shipping_country'] = $_estimate->shipping_country;
        if ($_estimate->include_shipping == 1) {
            $new_estimate_data['include_shipping'] = $_estimate->include_shipping;
        }
        $new_estimate_data['show_shipping_on_estimate'] = $_estimate->show_shipping_on_estimate;
        // Set to unpaid status automatically
        $new_estimate_data['status']     = 1;
        $new_estimate_data['clientnote'] = $_estimate->clientnote;
        $new_estimate_data['adminnote']  = '';
        $new_estimate_data['newitems']   = [];
        $custom_fields_items             = get_custom_fields('items');
        $key                             = 1;
        foreach ($_estimate->items as $item) {
            $new_estimate_data['newitems'][$key]['description']      = $item['description'];
            $new_estimate_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $new_estimate_data['newitems'][$key]['qty']              = $item['qty'];
            $new_estimate_data['newitems'][$key]['unit']             = $item['unit'];
            $new_estimate_data['newitems'][$key]['taxname']          = [];
            $taxes                                                   = get_estimate_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                // tax name is in format TAX1|10.00
                array_push($new_estimate_data['newitems'][$key]['taxname'], $tax['taxname']);
            }
            $new_estimate_data['newitems'][$key]['rate']  = $item['rate'];
            $new_estimate_data['newitems'][$key]['order'] = $item['item_order'];
            foreach ($custom_fields_items as $cf) {
                $new_estimate_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);

                if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                }
            }
            $key++;
        }
        $id = $this->add($new_estimate_data);
        if ($id) {
            $custom_fields = get_custom_fields('estimate');
            foreach ($custom_fields as $field) {
                $value = get_custom_field_value($_estimate->id, $field['id'], 'estimate', false);
                if ($value == '') {
                    continue;
                }

                $this->db->insert('tblcustomfieldsvalues', [
                    'relid'   => $id,
                    'fieldid' => $field['id'],
                    'fieldto' => 'estimate',
                    'value'   => $value,
                ]);
            }

            $tags = get_tags_in($_estimate->id, 'estimate');
            handle_tags_save($tags, $id, 'estimate');

            logActivity('Copied Estimate ' . format_estimate_number($_estimate->id));

            return $id;
        }

        return false;
    }

    /**
     * Get estimate/s
     * @param  mixed $id    estimate id
     * @param  array  $where perform where
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
        $this->db->select('*,tblcurrencies.id as currencyid, tblrequsiteform.id as id, tblcurrencies.name as currency_name');
        $this->db->from('tblrequsiteform');
        $this->db->join('tblcurrencies', 'tblcurrencies.id = tblrequsiteform.currency', 'left');
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where('tblrequsiteform.id', $id);
            $estimate = $this->db->get()->row();
            if ($estimate) {
                $estimate->attachments                           = $this->get_attachments($id);
                $estimate->visible_attachments_to_customer_found = false;
                foreach ($estimate->attachments as $attachment) {
                    if ($attachment['visible_to_customer'] == 1) {
                        $estimate->visible_attachments_to_customer_found = true;

                        break;
                    }
                }
                $this->db->select();
                $this->db->from('tblrequsiteform_items');
                $this->db->where('rel_id', $id);
                $this->db->order_by('item_order', 'asc');
                $estimate->items = $this->db->get()->result_array();
                if ($estimate->project_id != 0) {
                    $this->load->model('projects_model');
                    $estimate->project_data = $this->projects_model->get($estimate->project_id);
                }
                $estimate->client = $this->clients_model->get($estimate->clientid);
                if (!$estimate->client) {
                    $estimate->client          = new stdClass();
                    $estimate->client->company = $estimate->deleted_customer_name;
                }
            }

            return $estimate;
        }
        $this->db->order_by('number,YEAR(date)', 'desc');

        return $this->db->get()->result_array();
    }

    /**
     * Get estimate attachments
     * @param  mixed $estimate_id
     * @param  string $id          attachment id
     * @return mixed
     */
    public function get_attachments($estimate_id, $id = '')
    {
        // If is passed id get return only 1 attachment
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $estimate_id);
        }
        $this->db->where('rel_type', 'requsite_form');
        $result = $this->db->get('tblfiles');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }

    /**
     * Insert new estimate to database
     * @param array $data invoiec data
     * @return mixed - false if not insert, estimate ID if succes
     */
    public function add($data)
    {
        $data['datecreated'] = date('Y-m-d H:i:s');

        $data['addedfrom'] = get_staff_user_id();

        $data['prefix'] = get_option('requsite_form_prefix');

        $data['number_format'] = get_option('requsite_form_number_format');

		$data['devide_gst'] = (isset($data['devide_gst']) && $data['devide_gst'] == "yes") ? 1 : 0;

		if (isset($data['item_amount'])) {
            unset($data['item_amount']);
        }

		if (isset($data['item_discount'])) {
            unset($data['item_discount']);
        }

        if (isset($data['group_id'])) {
            unset($data['group_id']);
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        $data['hash'] = app_generate_hash();

        $items = [];
        if (isset($data['newitems'])) {
            $items = $data['newitems'];
            unset($data['newitems']);
        }

        $data = $this->map_shipping_columns($data);

        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);

        if (isset($data['shipping_street'])) {
            $data['shipping_street'] = trim($data['shipping_street']);
            $data['shipping_street'] = nl2br($data['shipping_street']);
        }

        $hook_data = do_action('before_estimate_added', [
            'data'  => $data,
            'items' => $items,
        ]);

        $data  = $hook_data['data'];
        $items = $hook_data['items'];
        $this->db->insert('tblrequsiteform', $data);
        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            // Update next estimate number in settings
            $this->db->where('name', 'next_requsite_form_number');
            $this->db->set('value', 'value+1', false);
            $this->db->update('tbloptions');
            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields);
            }
            foreach ($items as $key => $item) {
                $this->db->insert('tblrequsiteform_items', [
                    'description'      => $item['description'],
                    'long_description' => nl2br($item['long_description']),
                    'qty'              => $item['qty'],
                    'rel_id'           => $insert_id,
                    'group_id'         => $item['group_id'],
                    'item_order'       => $item['order'],
                    'unit'             => $item['unit']
                ]);
            }
            $this->log_estimate_activity($insert_id, 'Created the requsite form.');
            return $insert_id;
        }

        return false;
    }

    private function map_shipping_columns($data)
    {
        if (!isset($data['include_shipping'])) {
            foreach ($this->shipping_fields as $_s_field) {
                if (isset($data[$_s_field])) {
                    $data[$_s_field] = null;
                }
            }
            $data['show_shipping_on_estimate'] = 1;
            $data['include_shipping']          = 0;
        } else {
            $data['include_shipping'] = 1;
            // set by default for the next time to be checked
            if (isset($data['show_shipping_on_estimate']) && ($data['show_shipping_on_estimate'] == 1 || $data['show_shipping_on_estimate'] == 'on')) {
                $data['show_shipping_on_estimate'] = 1;
            } else {
                $data['show_shipping_on_estimate'] = 0;
            }
        }

        return $data;
    }

    /**
     * Log estimate activity to database
     * @param  mixed $id   estimateid
     * @param  string $description activity description
     */
    public function log_estimate_activity($id, $description = '', $client = false, $additional_data = '')
    {
        $staffid   = get_staff_user_id();
        $full_name = get_staff_full_name(get_staff_user_id());
        if (DEFINED('CRON')) {
            $staffid   = '[CRON]';
            $full_name = '[CRON]';
        } elseif ($client == true) {
            $staffid   = null;
            $full_name = '';
        }

        $this->db->insert('tblsalesactivity', [
            'description'     => $description,
            'date'            => date('Y-m-d H:i:s'),
            'rel_id'          => $id,
            'rel_type'        => 'requsite_form',
            'staffid'         => $staffid,
            'full_name'       => $full_name,
            'additional_data' => $additional_data,
        ]);
    }

    /**
     * Send estimate to client
     * @param  mixed  $id        estimateid
     * @param  string  $template  email template to sent
     * @param  boolean $attachpdf attach estimate pdf or not
     * @return boolean
     */
    public function send_estimate_to_client($id, $template = '', $attachpdf = true, $cc = '', $manually = false)
    {
        $this->load->model('emails_model');

        $this->emails_model->set_rel_id($id);
        $this->emails_model->set_rel_type('estimate');

        $estimate = $this->get($id);
        if ($template == '') {
            if ($estimate->sent == 0) {
                $template = 'estimate-send-to-client';
            } else {
                $template = 'estimate-already-send';
            }
        }
        $estimate_number = format_estimate_number($estimate->id);


        $emails_sent = [];
        $sent        = false;
        $sent_to     = $this->input->post('sent_to');
        if ($manually === true) {
            $sent_to  = [];
            $contacts = $this->clients_model->get_contacts($estimate->clientid, ['active' => 1, 'estimate_emails' => 1]);
            foreach ($contacts as $contact) {
                array_push($sent_to, $contact['id']);
            }
        }

        $status_now          = $estimate->status;
        $status_auto_updated = false;
        if (is_array($sent_to) && count($sent_to) > 0) {
            $i = 0;
            // Auto update status to sent in case when user sends the estimate is with status draft
            if ($status_now == 1) {
                $this->db->where('id', $estimate->id);
                $this->db->update('tblestimates', [
                    'status' => 2,
                ]);
                $status_auto_updated = true;
            }

            if ($attachpdf) {
                $_pdf_estimate = $this->get($estimate->id);
                $pdf           = estimate_pdf($_pdf_estimate);
                $attach        = $pdf->Output($estimate_number . '.pdf', 'S');
            }

            foreach ($sent_to as $contact_id) {
                if ($contact_id != '') {
                    if ($attachpdf) {
                        $this->emails_model->add_attachment([
                            'attachment' => $attach,
                            'filename'   => $estimate_number . '.pdf',
                            'type'       => 'application/pdf',
                        ]);
                    }

                    if ($this->input->post('email_attachments')) {
                        $_other_attachments = $this->input->post('email_attachments');

                        foreach ($_other_attachments as $attachment) {
                            $_attachment = $this->get_attachments($id, $attachment);

                            $this->emails_model->add_attachment([
                                'attachment' => get_upload_path_by_type('estimate') . $id . '/' . $_attachment->file_name,
                                'filename'   => $_attachment->file_name,
                                'type'       => $_attachment->filetype,
                                'read'       => true,
                            ]);
                        }
                    }

                    $contact = $this->clients_model->get_contact($contact_id);

                    $merge_fields = [];
                    $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($estimate->clientid, $contact_id));
                    $merge_fields = array_merge($merge_fields, get_estimate_merge_fields($estimate->id));
                    // Send cc only for the first contact
                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }
                    if ($this->emails_model->send_email_template($template, $contact->email, $merge_fields, '', $cc)) {
                        $sent = true;
                        array_push($emails_sent, $contact->email);
                    }
                }
                $i++;
            }
        } else {
            return false;
        }
        if ($sent) {
            $this->set_estimate_sent($id, $emails_sent);
            do_action('estimate_sent', $id);

            return true;
        }
        if ($status_auto_updated) {
            // Estimate not send to customer but the status was previously updated to sent now we need to revert back to draft
            $this->db->where('id', $estimate->id);
            $this->db->update('tblestimates', [
                    'status' => 1,
                ]);
        }


        return false;
    }

    /**
     * Set estimate to sent when email is successfuly sended to client
     * @param mixed $id estimateid
     */
    public function set_estimate_sent($id, $emails_sent = [])
    {
        $this->db->where('id', $id);
        $this->db->update('tblestimates', [
            'sent'     => 1,
            'datesend' => date('Y-m-d H:i:s'),
        ]);
        $this->log_estimate_activity($id, 'invoice_estimate_activity_sent_to_client', false, serialize([
            '<custom_data>' . implode(', ', $emails_sent) . '</custom_data>',
        ]));
        // Update estimate status to sent
        $this->db->where('id', $id);
        $this->db->update('tblestimates', [
            'status' => 2,
        ]);
    }

    /**
     * Performs estimates totals status
     * @param  array $data
     * @return array
     */
    public function get_estimates_total($data)
    {
        $statuses            = $this->get_statuses();
        $has_permission_view = has_permission('requisite_forms', '', 'view');
        $this->load->model('currencies_model');
        if (isset($data['currency'])) {
            $currencyid = $data['currency'];
        } elseif (isset($data['customer_id']) && $data['customer_id'] != '') {
            $currencyid = $this->clients_model->get_customer_default_currency($data['customer_id']);
            if ($currencyid == 0) {
                $currencyid = $this->currencies_model->get_base_currency()->id;
            }
        } elseif (isset($data['project_id']) && $data['project_id'] != '') {
            $this->load->model('projects_model');
            $currencyid = $this->projects_model->get_currency($data['project_id'])->id;
        } else {
            $currencyid = $this->currencies_model->get_base_currency()->id;
        }

        $symbol = $this->currencies_model->get_currency_symbol($currencyid);
        $where  = '';
        if (isset($data['customer_id']) && $data['customer_id'] != '') {
            $where = ' AND clientid=' . $data['customer_id'];
        }

        if (isset($data['project_id']) && $data['project_id'] != '') {
            $where .= ' AND project_id=' . $data['project_id'];
        }

        if (!$has_permission_view) {
            $where .= ' AND ' . get_requsite_form_where_sql_for_staff(get_staff_user_id());
        }

        $sql = 'SELECT';
        foreach ($statuses as $estimate_status) {
            $sql .= '(SELECT SUM(total) FROM tblestimates WHERE status=' . $estimate_status;
            $sql .= ' AND currency =' . $currencyid;
            if (isset($data['years']) && count($data['years']) > 0) {
                $sql .= ' AND YEAR(date) IN (' . implode(', ', $data['years']) . ')';
            } else {
                $sql .= ' AND YEAR(date) = ' . date('Y');
            }
            $sql .= $where;
            $sql .= ') as "' . $estimate_status . '",';
        }

        $sql     = substr($sql, 0, -1);
        $result  = $this->db->query($sql)->result_array();
        $_result = [];
        $i       = 1;
        foreach ($result as $key => $val) {
            foreach ($val as $status => $total) {
                $_result[$i]['total']  = $total;
                $_result[$i]['symbol'] = $symbol;
                $_result[$i]['status'] = $status;
                $i++;
            }
        }
        $_result['currencyid'] = $currencyid;

        return $_result;
    }

    /**
     * Get estimate statuses
     * @return array
     */
    public function get_statuses()
    {
        return $this->statuses;
    }

    /**
     * Update estimate data
     * @param  array $data estimate data
     * @param  mixed $id   estimateid
     * @return boolean
     */
    public function update($data, $id)
    {
        $affectedRows = 0;

        $data['number'] = trim($data['number']);

        $original_estimate = $this->get($id);

        $original_status = $original_estimate->status;

        $original_number = $original_estimate->number;

        $original_number_formatted = format_requsite_form_number($id);

		$data['devide_gst'] = (isset($data['devide_gst']) && $data['devide_gst'] == "yes") ? 1 : 0;

        $save_and_send = isset($data['save_and_send']);

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


        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);

        $data['shipping_street'] = trim($data['shipping_street']);
        $data['shipping_street'] = nl2br($data['shipping_street']);

        $data = $this->map_shipping_columns($data);

        $hook_data = do_action('before_estimate_updated', [
            'data'          => $data,
            'id'            => $id,
            'items'         => $items,
            'newitems'      => $newitems,
            'removed_items' => isset($data['removed_items']) ? $data['removed_items'] : [],
        ]);

        $data                  = $hook_data['data'];
        $data['removed_items'] = $hook_data['removed_items'];
        $items                 = $hook_data['items'];
        $newitems              = $hook_data['newitems'];

        // Delete items checked to be removed from database
        foreach ($data['removed_items'] as $remove_item_id) {
            $original_item = $this->get_estimate_item($remove_item_id);
            $this->db->where('id', $remove_item_id);
            if ($this->db->delete('tblrequsiteform_items')) {
                $affectedRows++;
                $this->log_estimate_activity($id, 'invoice_estimate_activity_removed_item', false, serialize([
                    $original_item->description,
                ]));
            }
        }
        unset($data['removed_items']);

        $this->db->where('id', $id);
        $this->db->update('tblrequsiteform', $data);

        foreach ($items as $key => $item) {
            $original_item = $this->get_estimate_item($item['itemid']);
            $update =  [
                'description'      => $item['description'],
                'long_description' => nl2br($item['long_description']),
                'qty'              => $item['qty'],
                'group_id'         => $item['group_id'],
                'item_order'       => $item['order'],
                'unit'             => $item['unit']
            ];
            $this->db->where('id', $item['itemid']);
            $this->db->update('tblrequsiteform_items', $update);
            if ($this->db->affected_rows() > 0) {
                $affectedRows++;
                /*if ($original_item->rate != $item['rate']) {
                    $this->log_estimate_activity($id, "updated item rate from {$original_item->rate} to {$item['rate']}", false);
                }*/
                if ($original_item->qty != $item['qty']) {
                    $this->log_estimate_activity($id, "updated item qty from {$original_item->qty} to {$item['qty']}", false);
                }
            }
        }

        foreach ($newitems as $key => $item) {
            $this->db->insert('tblrequsiteform_items', [
                'description'      => $item['description'],
                'long_description' => nl2br($item['long_description']),
                'qty'              => $item['qty'],
                'rel_id'           => $id,
                'group_id'         => $item['group_id'],
                'item_order'       => $item['order'],
                'unit'             => $item['unit']
            ]);
            $affectedRows++;
            $this->log_estimate_activity($id, 'invoice_estimate_activity_added_item', false, serialize([
                $item['description'],
            ]));
        }

        // if ($affectedRows > 0) {
            // update_sales_total_tax_column($id, 'estimate', 'tblestimates');
        // }

        /*if ($save_and_send === true) {
            $this->send_estimate_to_client($id, '', true, '', true);
        }*/

        if ($affectedRows > 0) {
//            do_action('after_estimate_updated', $id);

            return true;
        }

        return false;
    }

    /**
     * Get item by id
     * @param  mixed $id item id
     * @return object
     */
    public function get_estimate_item($id)
    {
        $this->db->where('id', $id);

        return $this->db->get('tblrequsiteform_items')->row();
    }

    /**
     * Delete estimate items and all connections
     * @param  mixed $id estimateid
     * @return boolean
     */
    public function delete($id, $simpleDelete = false)
    {
        if (get_option('delete_only_on_last_estimate') == 1 && $simpleDelete == false) {
            if (!is_last_estimate($id)) {
                return false;
            }
        }
        $estimate = $this->get($id);
        if (!is_null($estimate->invoiceid) && $simpleDelete == false) {
            return [
                'is_invoiced_estimate_delete_error' => true,
            ];
        }
        do_action('before_estimate_deleted', $id);

        $number = format_requsite_form_number($id);

        $this->clear_signature($id);

        $this->db->where('id', $id);
        $this->db->delete('tblrequsiteform');

        if ($this->db->affected_rows() > 0) {

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'requsiteform');
            $this->db->delete('tblsalesactivity');

            $attachments = $this->get_attachments($id);
            foreach ($attachments as $attachment) {
                $this->delete_attachment($attachment['id']);
            }

            // Get related tasks
            $this->db->where('rel_type', 'estimate');
            $this->db->where('rel_id', $id);
            $tasks = $this->db->get('tblstafftasks')->result_array();
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id']);
            }
            if ($simpleDelete == false) {
                logActivity('Estimates Deleted [Number: ' . $number . ']');
            }

            return true;
        }

        return false;
    }

    public function clear_signature($id)
    {
        $this->db->select('signature');
        $this->db->where('id', $id);
        $estimate = $this->db->get('tblestimates')->row();

        if ($estimate) {
            $this->db->where('id', $id);
            $this->db->update('tblestimates', ['signature' => null]);

            if (!empty($estimate->signature)) {
                unlink(get_upload_path_by_type('estimate') . $id . '/' . $estimate->signature);
            }

            return true;
        }

        return false;
    }

    /**
     *  Delete estimate attachment
     * @param   mixed $id  attachmentid
     * @return  boolean
     */
    public function delete_attachment($id)
    {
        $attachment = $this->get_attachments('', $id);
        $deleted    = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_by_type('requsite_form') . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete('tblfiles');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                logActivity('Requisite form Attachment Deleted [EstimateID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_by_type('requsite_form') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('requsite_form') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('requsite_form') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * Send expiration reminder to customer
     * @param  mixed $id estimate id
     * @return boolean
     */
    public function send_expiry_reminder($id)
    {
        $estimate         = $this->get($id);
        $estimate_number  = format_estimate_number($estimate->id);
        $pdf              = estimate_pdf($estimate);
        $attach           = $pdf->Output($estimate_number . '.pdf', 'S');
        $emails_sent      = [];
        $sms_sent         = false;
        $sms_reminder_log = [];

        // For all cases update this to prevent sending multiple reminders eq on fail
        $this->db->where('id', $id);
        $this->db->update('tblestimates', [
            'is_expiry_notified' => 1,
        ]);

        $contacts = $this->clients_model->get_contacts($estimate->clientid, ['active' => 1, 'estimate_emails' => 1]);
        $this->load->model('emails_model');

        $this->emails_model->set_rel_id($id);
        $this->emails_model->set_rel_type('estimate');

        foreach ($contacts as $contact) {
            $this->emails_model->add_attachment([
                    'attachment' => $attach,
                    'filename'   => $estimate_number . '.pdf',
                    'type'       => 'application/pdf',
                ]);
            $merge_fields = [];
            $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($estimate->clientid, $contact['id']));
            $merge_fields = array_merge($merge_fields, get_estimate_merge_fields($estimate->id));

            if ($this->emails_model->send_email_template('estimate-expiry-reminder', $contact['email'], $merge_fields)) {
                array_push($emails_sent, $contact['email']);
            }

            if (can_send_sms_based_on_creation_date($estimate->datecreated)
                && $this->sms->trigger(SMS_TRIGGER_ESTIMATE_EXP_REMINDER, $contact['phonenumber'], $merge_fields)) {
                $sms_sent = true;
                array_push($sms_reminder_log, $contact['firstname'] . ' (' . $contact['phonenumber'] . ')');
            }
        }

        if (count($emails_sent) > 0 || $sms_sent) {
            if (count($emails_sent) > 0) {
                $this->log_estimate_activity($id, 'not_expiry_reminder_sent', false, serialize([
                    '<custom_data>' . implode(', ', $emails_sent) . '</custom_data>',
                ]));
            }

            if ($sms_sent) {
                $this->log_estimate_activity($id, 'sms_reminder_sent_to', false, serialize([
                   implode(', ', $sms_reminder_log),
                ]));
            }

            return true;
        }

        return false;
    }

    /**
     * All estimate activity
     * @param  mixed $id estimateid
     * @return array
     */
    public function get_estimate_activity($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'requsite_form');
        $this->db->order_by('date', 'asc');

        return $this->db->get('tblsalesactivity')->result_array();
    }

    /**
     * Updates pipeline order when drag and drop
     * @param  mixe $data $_POST data
     * @return void
     */
    public function update_pipeline($data)
    {
        $this->mark_action_status($data['status'], $data['estimateid']);
        foreach ($data['order'] as $order_data) {
            $this->db->where('id', $order_data[0]);
            $this->db->update('tblestimates', [
                'pipeline_order' => $order_data[1],
            ]);
        }
    }

    public function mark_action_status($action, $id, $client = false)
    {
        $this->db->where('id', $id);
        $this->db->update('tblestimates', [
            'status' => $action,
        ]);

        $notifiedUsers = [];

        if ($this->db->affected_rows() > 0) {
            $estimate = $this->get($id);
            if ($client == true) {
                $this->db->where('staffid', $estimate->addedfrom);
                $this->db->or_where('staffid', $estimate->sale_agent);
                $staff_estimate = $this->db->get('tblstaff')->result_array();
                $invoiceid      = false;
                $invoiced       = false;

                $this->load->model('emails_model');

                $this->emails_model->set_rel_id($id);
                $this->emails_model->set_rel_type('estimate');

                $merge_fields_for_staff_email = [];
                if (!is_client_logged_in()) {
                    $contact_id = get_primary_contact_user_id($estimate->clientid);
                } else {
                    $contact_id = get_contact_user_id();
                }
                $merge_fields_for_staff_email = array_merge($merge_fields_for_staff_email, get_client_contact_merge_fields($estimate->clientid, $contact_id));
                $merge_fields_for_staff_email = array_merge($merge_fields_for_staff_email, get_estimate_merge_fields($estimate->id));


                if ($action == 4) {
                    if (get_option('estimate_auto_convert_to_invoice_on_client_accept') == 1) {
                        $invoiceid = $this->convert_to_invoice($id, true);
                        $this->load->model('invoices_model');
                        if ($invoiceid) {
                            $invoiced = true;
                            $invoice  = $this->invoices_model->get($invoiceid);
                            $this->log_estimate_activity($id, 'estimate_activity_client_accepted_and_converted', true, serialize([
                                '<a href="' . admin_url('invoices/list_invoices/' . $invoiceid) . '">' . format_invoice_number($invoice->id) . '</a>',
                            ]));
                        }
                    } else {
                        $this->log_estimate_activity($id, 'estimate_activity_client_accepted', true);
                    }

                    // Send thank you email to all contacts with permission estimates
                    $contacts = $this->clients_model->get_contacts($estimate->clientid, ['active' => 1, 'estimate_emails' => 1]);
                    foreach ($contacts as $contact) {
                        $merge_fields = [];
                        $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($estimate->clientid, $contact['id']));
                        $merge_fields = array_merge($merge_fields, get_estimate_merge_fields($estimate->id));
                        $this->emails_model->send_email_template('estimate-thank-you-to-customer', $contact['email'], $merge_fields);
                    }
                    foreach ($staff_estimate as $member) {
                        $notified = add_notification([
                            'fromcompany'     => true,
                            'touserid'        => $member['staffid'],
                            'description'     => 'not_estimate_customer_accepted',
                            'link'            => 'estimates/list_estimates/' . $id,
                            'additional_data' => serialize([
                                format_estimate_number($estimate->id),
                            ]),
                        ]);
                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }
                        // Send staff email notification that customer accepted estimate
                        $this->emails_model->send_email_template('estimate-accepted-to-staff', $member['email'], $merge_fields_for_staff_email);
                    }

                    pusher_trigger_notification($notifiedUsers);
                    do_action('estimate_accepted', $id);

                    return [
                        'invoiced'  => $invoiced,
                        'invoiceid' => $invoiceid,
                    ];
                } elseif ($action == 3) {
                    foreach ($staff_estimate as $member) {
                        $notified = add_notification([
                            'fromcompany'     => true,
                            'touserid'        => $member['staffid'],
                            'description'     => 'not_estimate_customer_declined',
                            'link'            => 'estimates/list_estimates/' . $id,
                            'additional_data' => serialize([
                                format_estimate_number($estimate->id),
                            ]),
                        ]);

                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }
                        // Send staff email notification that customer declined estimate
                        $this->emails_model->send_email_template('estimate-declined-to-staff', $member['email'], $merge_fields_for_staff_email);
                    }

                    pusher_trigger_notification($notifiedUsers);
                    $this->log_estimate_activity($id, 'estimate_activity_client_declined', true);
                    do_action('estimate_declined', $id);

                    return [
                        'invoiced'  => $invoiced,
                        'invoiceid' => $invoiceid,
                    ];
                }
            } else {
                if ($action == 2) {
                    $this->db->where('id', $id);
                    $this->db->update('tblestimates', ['sent' => 1, 'datesend' => date('Y-m-d H:i:s')]);
                }
                // Admin marked estimate
                $this->log_estimate_activity($id, 'estimate_activity_marked', false, serialize([
                    '<status>' . $action . '</status>',
                ]));

                return true;
            }
        }

        return false;
    }

    /**
     * Convert estimate to invoice
     * @param  mixed $id estimate id
     * @return mixed     New invoice ID
     */
    public function convert_to_quotation($id, $client = false, $draft_invoice = false)
    {
        // Recurring invoice date is okey lets convert it to new invoice
        $_estimate = $this->get($id);

        $new_invoice_data = [];
        if ($draft_invoice == true) {
            $new_invoice_data['save_as_draft'] = true;
        }
        $new_invoice_data['clientid']   = $_estimate->clientid;
        $new_invoice_data['project_id'] = $_estimate->project_id;
        $new_invoice_data['number']     = get_option('next_estimate_number');
        $new_invoice_data['date']       = _d(date('Y-m-d'));
        if(get_option('estimate_due_after') != 0){
            $new_invoice_data['expirydate']  = _d(date('Y-m-d', strtotime('+' . get_option('estimate_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }
        /*if (get_option('invoice_due_after') != 0) {
            $new_invoice_data['duedate'] = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }*/
        $new_invoice_data['show_quantity_as'] = $_estimate->show_quantity_as;
        $new_invoice_data['currency']         = $_estimate->currency;
        $new_invoice_data['subtotal']         = $_estimate->subtotal;
        $new_invoice_data['total']            = $_estimate->total;
        $new_invoice_data['adjustment']       = $_estimate->adjustment;
        $new_invoice_data['discount_percent'] = $_estimate->discount_percent;
        $new_invoice_data['discount_total']   = $_estimate->discount_total;
        $new_invoice_data['discount_type']    = $_estimate->discount_type;
        $new_invoice_data['packing_and_forwarding']    = isset($_estimate->packing_and_forwarding) ? $_estimate->packing_and_forwarding : '';
        $new_invoice_data['devide_gst']       = ($_estimate->devide_gst==1) ? "yes" : "";
        $new_invoice_data['servicecharge']    =isset($_estimate->servicecharge) ? $_estimate->servicecharge : '';


        $new_invoice_data['sale_agent']       = $_estimate->sale_agent;
        // Since version 1.0.6
        $new_invoice_data['billing_street']   = clear_textarea_breaks($_estimate->billing_street);
        $new_invoice_data['billing_city']     = $_estimate->billing_city;
        $new_invoice_data['billing_state']    = $_estimate->billing_state;
        $new_invoice_data['billing_zip']      = $_estimate->billing_zip;
        $new_invoice_data['billing_country']  = $_estimate->billing_country;
        $new_invoice_data['shipping_street']  = clear_textarea_breaks($_estimate->shipping_street);
        $new_invoice_data['shipping_city']    = $_estimate->shipping_city;
        $new_invoice_data['shipping_state']   = $_estimate->shipping_state;
        $new_invoice_data['shipping_zip']     = $_estimate->shipping_zip;
        $new_invoice_data['shipping_country'] = $_estimate->shipping_country;

        if ($_estimate->include_shipping == 1) {
            $new_invoice_data['include_shipping'] = 1;
        }

//        $new_invoice_data['show_shipping_on_invoice'] = $_estimate->show_shipping_on_estimate;
        $new_invoice_data['terms']                    = get_option('predefined_terms_estimate');
        $new_invoice_data['clientnote']               = get_option('predefined_clientnote_estimate');
        // Set to unpaid status automatically
        $new_invoice_data['status']    = 1;
        $new_invoice_data['adminnote'] = '';

        $this->load->model('payment_modes_model');
        $modes = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $temp_modes = [];
        foreach ($modes as $mode) {
            if ($mode['selected_by_default'] == 0) {
                continue;
            }
            $temp_modes[] = $mode['id'];
        }
        $new_invoice_data['newitems']              = [];
        $custom_fields_items                       = get_custom_fields('items');
        $key                                       = 1;
        $this->load->model('invoice_items_model');
        foreach ($_estimate->items as $item) {
            $itm = $this->invoice_items_model->getProductByCode($item['description']);
            $new_invoice_data['newitems'][$key]['description']      = $item['description'];
            $new_invoice_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $new_invoice_data['newitems'][$key]['qty']              = $item['qty'];
            $new_invoice_data['newitems'][$key]['fab_no']    = '';
            $new_invoice_data['newitems'][$key]['item_amount']      = $itm->rate;
            $new_invoice_data['newitems'][$key]['group_id']      = $itm->group_id;
            $new_invoice_data['newitems'][$key]['unit']             = $itm->unit;
            $new_invoice_data['newitems'][$key]['taxname']          = [$itm->taxname.'|'.$itm->taxrate];
            $new_invoice_data['newitems'][$key]['rate']  = $item['rate'];
            $new_invoice_data['newitems'][$key]['order'] = $item['item_order'];
            $new_invoice_data['newitems'][$key]['custom_fields']=[];
            foreach ($custom_fields_items as $cf) {
                $val = get_custom_field_value($itm->id, $cf['id'], 'items_pr');
                if ($cf['type'] == 'textarea') {
                    $val = clear_textarea_breaks($val);
                }
                $new_invoice_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = $val;

                if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                }
            }
            $key++;
        }
        $this->load->model('estimates_model');
        $id = $this->estimates_model->add($new_invoice_data);
        if ($id) {
            // Customer accepted the estimate and is auto converted to invoice
            if (!is_staff_logged_in()) {
                $this->db->where('rel_type', 'requsite_form');
                $this->db->where('rel_id', $id);
                $this->db->delete('tblsalesactivity');
                $this->estimates_model->log_invoice_activity($id, 'Quotation auto created from requsite form with number '.'<a href="' . admin_url('requsite_forms/list_requsite_forms/' . $_estimate->id) . '">' . format_requsite_form_number($_estimate->id) . '</a>', true);
            }
            // For all cases update addefrom and sale agent from the invoice
            // May happen staff is not logged in and these values to be 0
            $this->db->where('id', $id);
            $this->db->update('tblestimates', [
                'addedfrom'  => $_estimate->addedfrom,
                'sale_agent' => $_estimate->sale_agent,
            ]);

            // Update estimate with the new invoice data and set to status accepted
//            $this->delete($_estimate->id);
//            do_action('estimate_converted_to_invoice', ['invoice_id' => $id, 'estimate_id' => $_estimate->id]);

            $this->db->where('id', $_estimate->id);
            $this->db->update('tblrequsiteform', [
                'invoiced_date' => date('Y-m-d H:i:s'),
                'invoiceid'     => $id,
                'status'        => 6,
            ]);

            if ($client == false) {
                $this->log_estimate_activity($_estimate->id, 'converted this Requsite form to quotation.<br /> <a href="' . admin_url('estimates/list_estimates/' . $id) . '">' . format_estimate_number($id) . '</a>', false);
            }

//            do_action('estimate_converted_to_invoice', ['invoice_id' => $id, 'estimate_id' => $_estimate->id]);
        }

        return $id;
    }

    /**
     * Get estimate unique year for filtering
     * @return array
     */
    public function get_estimates_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM tblestimates ORDER BY year DESC')->result_array();
    }
}
