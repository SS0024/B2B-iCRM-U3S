<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Inquiries extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('inquiries_model');
        $this->load->model('currencies_model');
    }

    public function index($proposal_id = '')
    {
        $this->list_inquiries($proposal_id);
    }

    public function list_inquiries($proposal_id = '')
    {
        close_setup_menu();
		
        if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && get_option('allow_staff_view_estimates_assigned') == 0) {
            access_denied('proposals');
        }

        $isPipeline = $this->session->userdata('inquiries_pipeline') == 'true';

        if ($isPipeline && !$this->input->get('status')) {
            $data['title']           = _l('inquiries_pipeline');
            $data['bodyclass']       = 'inquiries-pipeline';
            $data['switch_pipeline'] = false;
            // Direct access
            if (is_numeric($proposal_id)) {
                $data['proposalid'] = $proposal_id;
            } else {
                $data['proposalid'] = $this->session->flashdata('proposalid');
            }

            $this->load->view('admin/inquiries/pipeline/manage', $data);
        } else {

            // Pipeline was initiated but user click from home page and need to show table only to filter
            if ($this->input->get('status') && $isPipeline) {
                $this->pipeline(0, true);
            }

            $data['proposal_id']           = $proposal_id;
            $data['switch_pipeline']       = true;
            $data['title']                 = _l('inquiry');
            $data['statuses']              = $this->inquiries_model->get_statuses();
            $data['proposals_sale_agents'] = $this->inquiries_model->get_sale_agents();
            $data['years']                 = $this->inquiries_model->get_proposals_years();
            $data['estimates_contact_persons'] = get_div_cons_by_type('inquiry',null,[], true);
            $this->load->view('admin/inquiries/manage', $data);
        }
    }

    public function pipeline($set = 0, $manual = false)
    {
        if ($set == 1) {
            $set = 'true';
        } else {
            $set = 'false';
        }
        $this->session->set_userdata([
            'proposals_pipeline' => $set,
        ]);
        if ($manual == false) {
            redirect(admin_url('inquiries'));
        }
    }

    public function table()
    {
        if (!has_permission('proposals', '', 'view')
            && !has_permission('proposals', '', 'view_own')
            && get_option('allow_staff_view_proposals_assigned') == 0) {
            ajax_access_denied();
        }

        $this->app->get_table_data('inquiries');
    }

    public function proposal_relations($rel_id, $rel_type)
    {
        $this->app->get_table_data('inquiries_relations', [
            'rel_id'   => $rel_id,
            'rel_type' => $rel_type,
        ]);
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->inquiries_model->delete_attachment($id);
        } else {
            ajax_access_denied();
        }
    }

    public function clear_signature($id)
    {
        if (has_permission('proposals', '', 'delete')) {
            $this->inquiries_model->clear_signature($id);
        }

        redirect(admin_url('inquiries/list_inquiries/' . $id));
    }

    public function sync_data()
    {
        if (has_permission('proposals', '', 'create') || has_permission('proposals', '', 'edit')) {
            $has_permission_view = has_permission('proposals', '', 'view');

            $this->db->where('rel_id', $this->input->post('rel_id'));
            $this->db->where('rel_type', $this->input->post('rel_type'));

            if (!$has_permission_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }

            $address = trim($this->input->post('address'));
            $address = nl2br($address);
            $this->db->update('tblinquiries', [
                'phone'   => $this->input->post('phone'),
                'zip'     => $this->input->post('zip'),
                'country' => $this->input->post('country'),
                'state'   => $this->input->post('state'),
                'address' => $address,
                'city'    => $this->input->post('city'),
            ]);

            if ($this->db->affected_rows() > 0) {
                echo json_encode([
                    'message' => _l('all_data_synced_successfully'),
                ]);
            } else {
                echo json_encode([
                    'message' => _l('All inquiries are up to date, nothing to sync'),
                ]);
            }
        }
    }

    public function inquiry($id = '')
    {
        if ($this->input->post()) {
            $proposal_data = $this->input->post();
			$proposal_data['service_charge_tax_rate']	=	SERVICE_CHARGES_TAX_RATE;
            if ($id == '') {
                if (!has_permission('proposals', '', 'create')) {
                    access_denied('proposals');
                }
                $id = $this->inquiries_model->add($proposal_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('inquiry')));
                    if ($this->set_proposal_pipeline_autoload($id)) {
                        redirect(admin_url('inquiries'));
                    } else {
                        redirect(admin_url('inquiries/list_inquiries/' . $id));
                    }
                }
            } else {
                if (!has_permission('proposals', '', 'edit')) {
                    access_denied('proposals');
                }
                $success = $this->inquiries_model->update($proposal_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('inquiry')));
                }
                if ($this->set_proposal_pipeline_autoload($id)) {
                    redirect(admin_url('inquiries'));
                } else {
                    redirect(admin_url('inquiries/list_inquiries/' . $id));
                }
            }
        }
        if ($id == '') {
            $title = _l('add_new', _l('inquiry_lowercase'));
        } else {
            $data['proposal'] = $this->inquiries_model->get($id);

            if (!$data['proposal'] || !user_can_view_proposal($id)) {
                blank_page(_l('proposal_not_found'));
            }

            $data['estimate']    = $data['proposal'];
            $title               = _l('edit', _l('inquiry_lowercase'));
        }

        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows('tblitems') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();
        $data['items_brands'] = $this->invoice_items_model->get_brands();
        $data['items_units'] = $this->invoice_items_model->get_units();
        $this->load->model('warehouses_model');
        $data['warehouses'] = $this->warehouses_model->getAllWarehouses();

        $this->load->model('leads_model');
        $data['lead_statuses'] = $this->leads_model->get_status();
        $data['lead_sorces'] = $this->leads_model->get_source();
        $data['statuses']      = $this->inquiries_model->get_statuses();
        $data['staff']         = $this->staff_model->get('', ['active' => 1]);
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $data['bodyclass']       = 'inquiry';

        $data['title'] = $title;
        $this->load->view('admin/inquiries/proposal', $data);
    }

    public function set_proposal_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }

        if ($this->session->has_userdata('proposals_pipeline') && $this->session->userdata('proposals_pipeline') == 'true') {
            $this->session->set_flashdata('proposalid', $id);

            return true;
        }

        return false;
    }

    public function get_template()
    {
        $name = $this->input->get('name');
        echo $this->load->view('admin/inquiries/templates/' . $name, [], true);
    }

    public function send_expiry_reminder($id)
    {
        $canView = user_can_view_proposal($id);
        if (!$canView) {
            access_denied('proposals');
        } else {
            if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && $canView == false) {
                access_denied('proposals');
            }
        }

        $success = $this->inquiries_model->send_expiry_reminder($id);
        if ($success) {
            set_alert('success', _l('sent_expiry_reminder_success'));
        } else {
            set_alert('danger', _l('sent_expiry_reminder_fail'));
        }
        if ($this->set_proposal_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('inquiries/list_inquiries/' . $id));
        }
    }

    public function clear_acceptance_info($id)
    {
        if (is_admin()) {
            $this->db->where('id', $id);
            $this->db->update('tblproposals', get_acceptance_info_array(true));
        }

        redirect(admin_url('inquiries/list_inquiries/' . $id));
    }

    public function pdf($id)
    {
        if (!$id) {
            redirect(admin_url('inquiries'));
        }

        $canView = user_can_view_proposal($id);
        if (!$canView) {
            access_denied('proposals');
        } else {
            if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && $canView == false) {
                access_denied('proposals');
            }
        }

        $proposal = $this->inquiries_model->get($id);

        try {
            $pdf = proposal_pdf($proposal);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $proposal_number = format_inquiry_number($id);
        $pdf->Output($proposal_number . '.pdf', $type);
    }

    public function add_note($rel_id)
    {
        if ($this->input->post() && user_can_view_proposal($rel_id)) {
            $this->misc_model->add_note($this->input->post(), 'inquiry', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        if (user_can_view_proposal($id)) {
            $data['notes'] = $this->misc_model->get_notes($id, 'inquiry');
            $this->load->view('admin/includes/sales_notes_template', $data);
        }
    }

    public function convert_to_purchaseorder($id)
    {
        if (!has_permission('purchaseorder', '', 'create')) {
            access_denied('purchaseorder');
        }
        if ($this->input->post()) {
            $this->load->model('estimates_model');
            $estimate_id = $this->estimates_model->add($this->input->post());
            if ($estimate_id) {
                set_alert('success', _l('Inquiry converted to PO successfully'));
                $this->db->where('id', $id);
                $this->db->update('tblproposals', [
                    'estimate_id' => $estimate_id,
                    'status'      => 3,
                ]);
                logActivity('Inquiry Converted to PO [EstimateID: ' . $estimate_id . ', ProposalID: ' . $id . ']');

                do_action('proposal_converted_to_estimate', ['proposal_id' => $id, 'estimate_id' => $estimate_id]);

                redirect(admin_url('estimates/estimate/' . $estimate_id));
            } else {
                set_alert('danger', _l('proposal_converted_to_estimate_fail'));
            }
            if ($this->set_proposal_pipeline_autoload($id)) {
                redirect(admin_url('inquiries'));
            } else {
                redirect(admin_url('inquiries/list_inquiries/' . $id));
            }
        }
    }

    public function convert_to_invoice($id)
    {
        if (!has_permission('invoices', '', 'create')) {
            access_denied('invoices');
        }
        if ($this->input->post()) {
            $this->load->model('invoices_model');
            $invoice_id = $this->invoices_model->add($this->input->post());
            if ($invoice_id) {
                set_alert('success', _l('proposal_converted_to_invoice_success'));
                $this->db->where('id', $id);
                $this->db->update('tblproposals', [
                    'invoice_id' => $invoice_id,
                    'status'     => 3,
                ]);
                logActivity('Proposal Converted to Invoice [InvoiceID: ' . $invoice_id . ', ProposalID: ' . $id . ']');
                do_action('proposal_converted_to_invoice', ['proposal_id' => $id, 'invoice_id' => $invoice_id]);
                redirect(admin_url('invoices/invoice/' . $invoice_id));
            } else {
                set_alert('danger', _l('proposal_converted_to_invoice_fail'));
            }
            if ($this->set_proposal_pipeline_autoload($id)) {
                redirect(admin_url('inquiries'));
            } else {
                redirect(admin_url('inquiries/list_inquiries/' . $id));
            }
        }
    }

    public function get_invoice_convert_data($id)
    {
        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $this->load->model('taxes_model');
        $data['taxes']         = $this->taxes_model->get();
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows('tblitems') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['staff']          = $this->staff_model->get('', ['active' => 1]);
        $data['proposal']       = $this->inquiries_model->get($id);
        $data['billable_tasks'] = [];
        $data['add_items']      = $this->_parse_items($data['proposal']);

        if ($data['proposal']->rel_type == 'lead') {
            $this->db->where('leadid', $data['proposal']->rel_id);
            $data['customer_id'] = $this->db->get('tblclients')->row()->userid;
        } else {
            $data['customer_id'] = $data['proposal']->rel_id;
        }
        $data['custom_fields_rel_transfer'] = [
            'belongs_to' => 'inquiry',
            'rel_id'     => $id,
        ];
        $this->load->view('admin/inquiries/invoice_convert_template', $data);
    }

    private function _parse_items($proposal)
    {
        $items = [];
        foreach ($proposal->items as $item) {
            $taxnames = [];
            $taxes    = get_proposal_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                array_push($taxnames, $tax['taxname']);
            }
            $item['taxname']        = $taxnames;
            $item['parent_item_id'] = $item['id'];
            $item['id']             = 0;
            $items[]                = $item;
        }

        return $items;
    }

    /* Send proposal to email */

    public function get_estimate_convert_data($id)
    {
        $this->load->model('taxes_model');
        $data['taxes']         = $this->taxes_model->get();
        $data['currencies']    = $this->currencies_model->get();
        $data['base_currency'] = $this->currencies_model->get_base_currency();
        $this->load->model('invoice_items_model');
        $data['ajaxItems'] = false;
        if (total_rows('tblitems') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();

        $data['staff']     = $this->staff_model->get('', ['active' => 1]);
        $data['proposal']  = $this->inquiries_model->get($id);
        $data['add_items'] = $this->_parse_items($data['proposal']);

        $this->load->model('estimates_model');
        $data['estimate_statuses'] = $this->estimates_model->get_statuses();
        if ($data['proposal']->rel_type == 'lead') {
            $this->db->where('leadid', $data['proposal']->rel_id);
            $data['customer_id'] = $this->db->get('tblclients')->row()->userid;
        } else {
            $data['customer_id'] = $data['proposal']->rel_id;
        }

        $data['custom_fields_rel_transfer'] = [
            'belongs_to' => 'inquiry',
            'rel_id'     => $id,
        ];

        $this->load->view('admin/inquiries/estimate_convert_template', $data);
    }

    public function send_to_email($id)
    {
        $canView = user_can_view_proposal($id);
        if (!$canView) {
            access_denied('proposals');
        } else {
            if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && $canView == false) {
                access_denied('proposals');
            }
        }

        if ($this->input->post()) {
            try {
                $success = $this->inquiries_model->send_proposal_to_email($id, 'proposal-send-to-customer', $this->input->post('attach_pdf'), $this->input->post('cc'));
            } catch (Exception $e) {
                $message = $e->getMessage();
                echo $message;
                if (strpos($message, 'Unable to get the size of the image') !== false) {
                    show_pdf_unable_to_get_image_size_error();
                }
                die;
            }

            if ($success) {
                set_alert('success', _l('proposal_sent_to_email_success'));
            } else {
                set_alert('danger', _l('proposal_sent_to_email_fail'));
            }

            if ($this->set_proposal_pipeline_autoload($id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('inquiries/list_inquiries/' . $id));
            }
        }
    }

    public function copy($id)
    {
        if (!has_permission('proposals', '', 'create')) {
            access_denied('proposals');
        }
        $new_id = $this->inquiries_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('Inquiry copied successfully'));
            $this->set_proposal_pipeline_autoload($new_id);
            redirect(admin_url('inquiries/proposal/' . $new_id));
        } else {
            set_alert('success', _l('Failed to copy inquiry'));
        }
        if ($this->set_proposal_pipeline_autoload($id)) {
            redirect(admin_url('inquiries'));
        } else {
            redirect(admin_url('inquiries/list_inquiries/' . $id));
        }
    }

    public function mark_action_status($status, $id)
    {
        if (!has_permission('proposals', '', 'edit')) {
            access_denied('proposals');
        }
        $success = $this->inquiries_model->mark_action_status($status, $id);
        if ($success) {
            set_alert('success', _l('Inquiry status changed successfully'));
        } else {
            set_alert('danger', _l('Failed to change inquiry status'));
        }
        if ($this->set_proposal_pipeline_autoload($id)) {
            redirect(admin_url('inquiries'));
        } else {
            redirect(admin_url('inquiries/list_inquiries/' . $id));
        }
    }

    public function delete($id)
    {
        if (!has_permission('proposals', '', 'delete')) {
            access_denied('proposals');
        }
        $response = $this->inquiries_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('Tender')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('tender')));
        }
        redirect(admin_url('tenders'));
    }
	
    public function get_relation_data_values($rel_id, $rel_type)
    {
        echo json_encode($this->inquiries_model->get_relation_data_values($rel_id, $rel_type));
    }

    public function add_proposal_comment()
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->inquiries_model->add_comment($this->input->post()),
            ]);
        }
    }

    public function edit_comment($id)
    {
        if ($this->input->post()) {
            echo json_encode([
                'success' => $this->inquiries_model->edit_comment($this->input->post(), $id),
                'message' => _l('comment_updated_successfully'),
            ]);
        }
    }

    public function get_proposal_comments($id)
    {
        $data['comments'] = $this->inquiries_model->get_comments($id);
        $this->load->view('admin/inquiries/comments_template', $data);
    }

    public function remove_comment($id)
    {
        $this->db->where('id', $id);
        $comment = $this->db->get('tblproposalcomments')->row();
        if ($comment) {
            if ($comment->staffid != get_staff_user_id() && !is_admin()) {
                echo json_encode([
                    'success' => false,
                ]);
                die;
            }
            echo json_encode([
                'success' => $this->inquiries_model->remove_comment($id),
            ]);
        } else {
            echo json_encode([
                'success' => false,
            ]);
        }
    }

    // Pipeline

    public function save_proposal_data()
    {
        if (!has_permission('proposals', '', 'edit') && !has_permission('proposals', '', 'create')) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode([
                'success' => false,
                'message' => _l('access_denied'),
            ]);
            die;
        }
        $success = false;
        $message = '';

        $this->db->where('id', $this->input->post('proposal_id'));
        $this->db->update('tblproposals', [
            'content' => $this->input->post('content', false),
        ]);

        $success = $this->db->affected_rows() > 0;
        $message = _l('updated_successfully', _l('inquiry'));

        echo json_encode([
            'success' => $success,
            'message' => $message,
        ]);
    }

    public function pipeline_open($id)
    {
        if (has_permission('proposals', '', 'view') || has_permission('proposals', '', 'view_own') || get_option('allow_staff_view_proposals_assigned') == 1) {
            $data['proposal']      = $this->get_proposal_data_ajax($id, true);
            $data['proposal_data'] = $this->inquiries_model->get($id);
            $this->load->view('admin/inquiries/pipeline/proposal', $data);
        }
    }

    public function get_proposal_data_ajax($id, $to_return = false)
    {
        if (!has_permission('proposals', '', 'view') && !has_permission('proposals', '', 'view_own') && get_option('allow_staff_view_proposals_assigned') == 0) {
            echo _l('access_denied');
            die;
        }

        $proposal = $this->inquiries_model->get($id, [], true);

        if (!$proposal || !user_can_view_proposal($id)) {
            echo _l('proposal_not_found');
            die;
        }

        $template_name         = 'proposal-send-to-customer';
        $data['template_name'] = $template_name;

        $this->db->where('slug', $template_name);
        $this->db->where('language', 'english');
        $template_result = $this->db->get('tblemailtemplates')->row();

        $data['template_system_name'] = $template_result->name;
        $data['template_id']          = $template_result->emailtemplateid;

        $data['template_disabled'] = false;
        if (total_rows('tblemailtemplates', ['slug' => $data['template_name'], 'active' => 0]) > 0) {
            $data['template_disabled'] = true;
        }

        define('EMAIL_TEMPLATE_PROPOSAL_ID_HELP', $proposal->id);

        $data['template'] = get_email_template_for_sending($template_name, $proposal->email);

        $proposal_merge_fields  = get_available_merge_fields();
        $_proposal_merge_fields = [];
        array_push($_proposal_merge_fields, [
            [
                'name' => 'Items Table',
                'key'  => '{proposal_items}',
            ],
        ]);
        foreach ($proposal_merge_fields as $key => $val) {
            foreach ($val as $type => $f) {
                if ($type == 'proposals') {
                    foreach ($f as $available) {
                        foreach ($available['available'] as $av) {
                            if ($av == 'proposals') {
                                array_push($_proposal_merge_fields, $f);

                                break;
                            }
                        }

                        break;
                    }
                } elseif ($type == 'other') {
                    array_push($_proposal_merge_fields, $f);
                }
            }
        }
        $data['proposal_statuses']     = $this->inquiries_model->get_statuses();
        $data['members']               = $this->staff_model->get('', ['active' => 1]);
        $data['proposal_merge_fields'] = $_proposal_merge_fields;
        $data['proposal']              = $proposal;
        $data['activity'] = $this->inquiries_model->get_estimate_activity($id);
        $data['totalNotes']            = total_rows('tblnotes', ['rel_id' => $id, 'rel_type' => 'inquiry']);
        if ($to_return == false) {
            $this->load->view('admin/inquiries/proposals_preview_template', $data);
        } else {
            return $this->load->view('admin/inquiries/proposals_preview_template', $data, true);
        }
    }

    public function update_pipeline()
    {
        if (has_permission('proposals', '', 'edit')) {
            $this->inquiries_model->update_pipeline($this->input->post());
        }
    }

    public function get_pipeline()
    {
        if (has_permission('proposals', '', 'view') || has_permission('proposals', '', 'view_own') || get_option('allow_staff_view_proposals_assigned') == 1) {
            $data['statuses'] = $this->inquiries_model->get_statuses();
            $this->load->view('admin/inquiries/pipeline/pipeline', $data);
        }
    }

    public function pipeline_load_more()
    {
        $status = $this->input->get('status');
        $page   = $this->input->get('page');

        $proposals = $this->inquiries_model->do_kanban_query($status, $this->input->get('search'), $page, [
            'sort_by' => $this->input->get('sort_by'),
            'sort'    => $this->input->get('sort'),
        ]);

        foreach ($proposals as $proposal) {
            $this->load->view('admin/inquiries/pipeline/_kanban_card', [
                'inquiry' => $proposal,
                'status'   => $status,
            ]);
        }
    }

    public function get_due_date()
    {
        if ($this->input->post()) {
            $date    = $this->input->post('date');
            $duedate = '';
            if (get_option('inquiry_due_after') != 0) {
                $date    = to_sql_date($date);
                $d       = date('Y-m-d', strtotime('+' . get_option('inquiry_due_after') . ' DAY', strtotime($date)));
                $duedate = _d($d);
                echo $duedate;
            }
        }
    }
	
	 public function get_gst_devision($state= '')
    {
		$company_state 	  = get_option('company_state');
			
		if(isset($state) && !empty($state) && strtolower($state) == strtolower($company_state)){
			$data['devide_gst'] = "yes";
		}else{
			$data['devide_gst'] = 'no';
		}		
        echo json_encode($data);
    }

    public function ajax_load($custid = '')
    {
        $mo=$_REQUEST['mo'];
        if($_REQUEST['type']=='loaddivisions')
        {
            $this->db->select('*');
            $this->db->from('tbldivision');
            $this->db->where('userid', $_REQUEST['customer'] );
            $this->db->order_by('id', 'asc');
            $lead_division = $this->db->get()->result_array();
            ?>
            <select class="selectpicker display-block mbot15 flmd" name="division" id="division" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" style="display: block!important;"  onchange="fnbd(document.getElementById('division').value,'contact','<?php echo $mo;?>');">
                <?php
                if($estimate->division == "NULL" OR $estimate->division == "")
                {
                    ?>
                    <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                    <?php
                }
                else
                {
                    ?>
                    <option value="<?php echo $estimate->division; ?>"><?php echo $estimate->division; ?></option>
                    <?php
                }
                ?>
                <?php foreach($lead_division as $division){ ?>
                    <option value="<?php echo $division['id']; ?>"><?php echo $division['division']; ?></option>
                <?php } ?>
            </select>
            <?php
        }
        else
        {
            $this->db->select('*');
            $this->db->from('tblcontacts');
            $this->db->where('division', $_REQUEST['division'] );
            $this->db->order_by('id', 'asc');
            $lead_contact = $this->db->get()->result_array();
            if(!empty($_REQUEST['customer'])){
                ?>
                <label class="control-label"><?php echo 'Contact'; ?></label>
                <?php
            }
            ?>
            <select class="selectpicker display-block mbot15 flmd" name="contact" id="contact-select" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" style="display: block!important;">
                <?php
                if($estimate->contact == "NULL" OR $estimate->contact == "")
                {
                    ?>
                    <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                    <?php
                }
                else
                {
                    ?>
                    <option value="<?php echo $estimate->contact; ?>"><?php echo $estimate->contact; ?></option>
                    <?php
                }
                ?>
                <?php foreach($lead_contact as $contact){
                    if($contact['firstname'] !='/' AND $contact['firstname'] !='//')
                    {
                        ?>
                        <option value="<?php echo $contact['id']; ?>"><?php echo $contact['firstname']?> <?php echo $contact['lastname']; ?> (<?php echo $contact['title']; ?>) (<?php echo $contact['phonenumber']; ?>)</option>
                        <?php
                    }
                } ?>
            </select>
            <?php
        }
    }


    public function get_latest_price($data){
//        $data = $this->input->get();
        $description =urldecode($data);
        $this->load->model('invoice_items_model');
        $item = $this->invoice_items_model->getProductByCode($description);
        $item->long_description = nl2br($item->long_description);
        if(isset($item->upto)){
            $item->upto = _d($item->upto);
        }
        $item->extraDiscount = 0;
        if(isset($item->upto) && (strtotime($item->upto) < time())){
            $item->rate = 0;
        }

        echo json_encode($item);
    }
}
