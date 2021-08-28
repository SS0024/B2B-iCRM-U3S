<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Requsite_forms extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('requsite_form_model');
    }

    /* Get all estimates in case user go on index page */
    public function index($id = '')
    {
        $this->list_requsite_forms($id);
    }

    /* List all estimates datatables */
    public function list_requsite_forms($id = '')
    {
        if (!has_permission('requisite_forms', '', 'view') && !has_permission('requisite_forms', '', 'view_own')) {
            access_denied('requisite_forms');
        }

        $isPipeline = $this->session->userdata('estimate_pipeline') == 'true';

        $data['estimate_statuses'] = $this->requsite_form_model->get_statuses();
        if ($isPipeline && !$this->input->get('status') && !$this->input->get('filter')) {
            $data['title'] = _l('Requsite Forms');
            $data['bodyclass'] = 'requsite_forms-pipeline requsite_forms-total-manual';
            $data['switch_pipeline'] = false;

            if (is_numeric($id)) {
                $data['estimateid'] = $id;
            } else {
                $data['estimateid'] = $this->session->flashdata('estimateid');
            }

            $this->load->view('admin/requsite_forms/pipeline/manage', $data);
        } else {

            // Pipeline was initiated but user click from home page and need to show table only to filter
            if ($this->input->get('status') || $this->input->get('filter') && $isPipeline) {
                $this->pipeline(0, true);
            }

            $data['estimateid'] = $id;
            $data['switch_pipeline'] = true;
            $data['title'] = _l('Requsite Forms');
            $data['bodyclass'] = 'requsite_forms-total-manual';
            $data['estimates_years'] = $this->requsite_form_model->get_estimates_years();
            $data['estimates_sale_agents'] = $this->requsite_form_model->get_sale_agents();
            $this->load->view('admin/requsite_forms/manage', $data);
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
            'estimate_pipeline' => $set,
        ]);
        if ($manual == false) {
            redirect(admin_url('requsite_forms/list_requsite_forms'));
        }
    }

    /* Add new estimate or update existing */

    public function table($clientid = '')
    {
        if (!has_permission('requisite_forms', '', 'view') && !has_permission('requisite_forms', '', 'view_own')) {
            ajax_access_denied();
        }

        $this->app->get_table_data('requsite_forms', [
            'clientid' => $clientid,
        ]);
    }

    public function requsite_form($id = '')
    {
        if ($this->input->post()) {
            $estimate_data = $this->input->post();
            $task_id = null;
            if (isset($estimate_data['reference_task_id']) && $estimate_data['reference_task_id'] != 0){
                $task_id = $estimate_data['reference_task_id'];
            }
            unset($estimate_data['changeable_fab_no']);
            unset($estimate_data['reference_task_id']);
            if ($id == '') {
                if (!has_permission('requisite_forms', '', 'create')) {
                    access_denied('requisite_forms');
                }
                $id = $this->requsite_form_model->add($estimate_data);
                if(isset($task_id)){
                    $this->db->where('id', $task_id);
                    $this->db->update('tblstafftasks', ['is_requsite'=>1,'requsite_form_id' => $id]);
                }
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('estimate')));
                    if ($this->set_estimate_pipeline_autoload($id)) {
                        redirect(admin_url('requsite_forms/list_requsite_forms/'));
                    } else {
                        redirect(admin_url('requsite_forms/list_requsite_forms/' . $id));
                    }
                }
            } else {
                if (!has_permission('requisite_forms', '', 'edit')) {
                    access_denied('requisite_forms');
                }
                $success = $this->requsite_form_model->update($estimate_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('estimate')));
                }
                if ($this->set_estimate_pipeline_autoload($id)) {
                    redirect(admin_url('requsite_forms/list_requsite_forms/'));
                } else {
                    redirect(admin_url('requsite_forms/list_requsite_forms/' . $id));
                }
            }
        }
        if ($id == '') {
            $title = _l('Create Requsite Form');
        } else {
            $estimate = $this->requsite_form_model->get($id);

            if (!$estimate) {
                blank_page(_l('Requsite Form not found'));
            }

            $data['estimate'] = $estimate;
            $data['edit'] = true;
            $title = _l('edit', _l('Requsite Form'));
        }
        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }
        $this->load->model('taxes_model');
        $data['taxes'] = $this->taxes_model->get();
        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $this->load->model('invoice_items_model');

        $data['ajaxItems'] = false;
        if (total_rows('tblitems') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items'] = [];
            $data['ajaxItems'] = true;
        }

        if($this->input->get('task_id')){
            $this->db->select('*');
            $this->db->join('tblcontracts', 'tblcontracts.id = tblstafftasks.rel_id and tblstafftasks.rel_type = "contract"','left');
            $this->db->where('tblstafftasks.id', $this->input->get('task_id'));
            $task = $this->db->get('tblstafftasks')->row();
            if($task->rel_type == 'contract'){
                $data['customer_id'] =$task->client;
            }else{
                $data['customer_id'] =$task->rel_id;
            }
        }

        $data['items_groups'] = $this->invoice_items_model->get_groups();
        $data['items_brands'] = $this->invoice_items_model->get_brands();
        $data['items_units'] = $this->invoice_items_model->get_units();
        $this->load->model('warehouses_model');
        $data['warehouses'] = $this->warehouses_model->getAllWarehouses();

        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['estimate_statuses'] = $this->requsite_form_model->get_statuses();
        $data['title'] = $title;
        $this->load->view('admin/requsite_forms/estimate', $data);
    }

    public function set_estimate_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }
        if ($this->session->has_userdata('estimate_pipeline') && $this->session->userdata('estimate_pipeline') == 'true') {
            $this->session->set_flashdata('estimateid', $id);

            return true;
        }

        return false;
    }

    public function clear_signature($id)
    {
        if (has_permission('requisite_forms', '', 'delete')) {
            $this->requsite_form_model->clear_signature($id);
        }

        redirect(admin_url('requsite_forms/list_requsite_forms/' . $id));
    }

    public function update_number_settings($id)
    {
        $response = [
            'success' => false,
            'message' => '',
        ];
        if (has_permission('requisite_forms', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update('tblrequsiteform', [
                'prefix' => $this->input->post('prefix'),
            ]);
            if ($this->db->affected_rows() > 0) {
                $response['success'] = true;
                $response['message'] = _l('updated_successfully', _l('estimate'));
            }
        }

        echo json_encode($response);
        die;
    }

    /* Get all estimate data used when user click on estimate number in a datatable left side*/

    public function validate_estimate_number()
    {
        $isedit = $this->input->post('isedit');
        $number = $this->input->post('number');
        $date = $this->input->post('date');
        $original_number = $this->input->post('original_number');
        $number = trim($number);
        $number = ltrim($number, '0');

        if ($isedit == 'true') {
            if ($number == $original_number) {
                echo json_encode(true);
                die;
            }
        }

        if (total_rows('tblrequsiteform', [
                'YEAR(date)' => date('Y', strtotime(to_sql_date($date))),
                'number' => $number,
            ]) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->requsite_form_model->delete_attachment($id);
        } else {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }
    }

    public function get_estimates_total()
    {
        if ($this->input->post()) {
            $data['totals'] = $this->requsite_form_model->get_estimates_total($this->input->post());

            $this->load->model('currencies_model');

            if (!$this->input->post('customer_id')) {
                $multiple_currencies = call_user_func('is_using_multiple_currencies', 'tblestimates');
            } else {
                $multiple_currencies = call_user_func('is_client_using_multiple_currencies', $this->input->post('customer_id'), 'tblestimates');
            }

            if ($multiple_currencies) {
                $data['currencies'] = $this->currencies_model->get();
            }

            $data['estimates_years'] = $this->requsite_form_model->get_estimates_years();

            if (count($data['estimates_years']) >= 1 && $data['estimates_years'][0]['year'] != date('Y')) {
                array_unshift($data['estimates_years'], ['year' => date('Y')]);
            }

            $data['_currency'] = $data['totals']['currencyid'];
            unset($data['totals']['currencyid']);
            $this->load->view('admin/requsite_forms/estimates_total_template', $data);
        }
    }

    public function add_note($rel_id)
    {
        if ($this->input->post()) {
            $this->misc_model->add_note($this->input->post(), 'requsite_forms', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
            $data['notes'] = $this->misc_model->get_notes($id, 'requsite_forms');
            $this->load->view('admin/includes/sales_notes_template', $data);
    }

    public function mark_action_status($status, $id)
    {
        if (!has_permission('requisite_forms', '', 'edit')) {
            access_denied('requisite_forms');
        }
        $success = $this->requsite_form_model->mark_action_status($status, $id);
        if ($success) {
            set_alert('success', _l('estimate_status_changed_success'));
        } else {
            set_alert('danger', _l('estimate_status_changed_fail'));
        }
        if ($this->set_estimate_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('requsite_forms/list_requsite_forms/' . $id));
        }
    }

    /* Send estimate to email */

    public function send_expiry_reminder($id)
    {
        if (!has_permission('requisite_forms', '', 'view') && !has_permission('requisite_forms', '', 'view_own')) {
            access_denied('requisite_forms');
        }

        $success = $this->requsite_form_model->send_expiry_reminder($id);
        if ($success) {
            set_alert('success', _l('sent_expiry_reminder_success'));
        } else {
            set_alert('danger', _l('sent_expiry_reminder_fail'));
        }
        if ($this->set_estimate_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('requsite_forms/list_requsite_forms/' . $id));
        }
    }

    /* Convert estimate to invoice */

    public function send_to_email($id)
    {
        if (!has_permission('requisite_forms', '', 'view') && !has_permission('requisite_forms', '', 'view_own')) {
            access_denied('requisite_forms');
        }

        try {
            $success = $this->requsite_form_model->send_estimate_to_client($id, '', $this->input->post('attach_pdf'), $this->input->post('cc'));
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', _l('estimate_sent_to_client_success'));
        } else {
            set_alert('danger', _l('estimate_sent_to_client_fail'));
        }
        if ($this->set_estimate_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('requsite_forms/list_requsite_forms/' . $id));
        }
    }

    public function convert_to_quotation($id)
    {
        if (!has_permission('estimates', '', 'create')) {
            access_denied('estimates');
        }
        if (!$id) {
            die('No requsite form found');
        }
        $draft_invoice = false;
        if ($this->input->get('save_as_draft')) {
            $draft_invoice = true;
        }
        $invoiceid = $this->requsite_form_model->convert_to_quotation($id, false, $draft_invoice);
        if ($invoiceid) {
            set_alert('success', _l('Requsite form converted to quotation successfully'));
            redirect(admin_url('estimates/estimate/' . $invoiceid));
        } else {
            if ($this->session->has_userdata('estimate_pipeline') && $this->session->userdata('estimate_pipeline') == 'true') {
                $this->session->set_flashdata('estimateid', $id);
            }
            if ($this->set_estimate_pipeline_autoload($id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('requsite_forms/list_requsite_forms/' . $id));
            }
        }
    }

    /* Delete estimate */

    public function copy($id)
    {
        if (!has_permission('requisite_forms', '', 'create')) {
            access_denied('requisite_forms');
        }
        if (!$id) {
            die('No estimate found');
        }
        $new_id = $this->requsite_form_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('estimate_copied_successfully'));
            if ($this->set_estimate_pipeline_autoload($new_id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('requsite_forms/requsite_form/' . $new_id));
            }
        }
        set_alert('danger', _l('estimate_copied_fail'));
        if ($this->set_estimate_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('requsite_forms/requsite_form/' . $id));
        }
    }

    public function delete($id)
    {
        if (!has_permission('requisite_forms', '', 'delete')) {
            access_denied('requisite_forms');
        }
        if (!$id) {
            redirect(admin_url('requsite_forms/list_requsite_forms'));
        }
        $success = $this->requsite_form_model->delete($id);
        if (is_array($success)) {
            set_alert('warning', _l('is_invoiced_estimate_delete_error'));
        } elseif ($success == true) {
            set_alert('success', _l('deleted', _l('Requsite form')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('requsite form')));
        }
        redirect(admin_url('requsite_forms/list_requsite_forms'));
    }

    public function bulk_action()
    {
        $total_deleted = 0;
        if ($this->input->post()) {
            $ids = $this->input->post('ids');

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($this->invoices_model->delete($id)) {
                            $total_deleted++;
                        }
                    }
                }
            }
        }

        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('Total customers deleted: ' . $total_deleted));
        }
    }

    /* Generates estimate PDF and senting to email  */

    public function clear_acceptance_info($id)
    {
        if (is_admin()) {
            $this->db->where('id', $id);
            $this->db->update('tblestimates', get_acceptance_info_array(true));
        }

        redirect(admin_url('requsite_forms/list_requsite_forms/' . $id));
    }

    public function pdf($id)
    {
        $_SESSION['xqut'] = 1;
        if (!has_permission('requisite_forms', '', 'view') && !has_permission('requisite_forms', '', 'view_own')) {
            access_denied('requisite_forms');
        }
        if (!$id) {
            redirect(admin_url('requsite_forms/list_requsite_forms'));
        }
        $estimate = $this->requsite_form_model->get($id);
        $estimate_number = format_requsite_form_number($estimate->id);

        try {
            $pdf = requsite_form_pdf($estimate);
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

        $fileNameHookData = do_action('estimate_file_name_admin_area', [
            'file_name' => mb_strtoupper(slug_it($estimate_number)) . '.pdf',
            'estimate' => $estimate,
        ]);

        $pdf->Output($fileNameHookData['file_name'], $type);
    }


    // Pipeline

    public function pipdf($id)
    {
        $_SESSION['xqut'] = 2;


        if (!has_permission('requisite_forms', '', 'view') && !has_permission('requisite_forms', '', 'view_own') && $canView == false) {
            access_denied('requisite_forms');
        }
        if (!$id) {
            redirect(admin_url('requsite_forms/list_requsite_forms'));
        }
        $estimate = $this->requsite_form_model->get($id);
        $estimate_number = format_estimate_number($estimate->id);

        try {
            $pdf = estimate_pdf($estimate);
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

        $fileNameHookData = do_action('estimate_file_name_admin_area', [
            'file_name' => mb_strtoupper(slug_it($estimate_number)) . '.pdf',
            'estimate' => $estimate,
        ]);

        $pdf->Output($fileNameHookData['file_name'], $type);
    }

    public function get_pipeline()
    {
        if (has_permission('requisite_forms', '', 'view') || has_permission('requisite_forms', '', 'view_own')) {
            $data['estimate_statuses'] = $this->requsite_form_model->get_statuses();
            $this->load->view('admin/requsite_forms/pipeline/pipeline', $data);
        }
    }

    public function pipeline_open($id)
    {
        if (!has_permission('requisite_forms', '', 'view') && !has_permission('requisite_forms', '', 'view_own')) {
            access_denied('requisite_forms');
        }

        $data['id'] = $id;
        $data['estimate'] = $this->get_estimate_data_ajax($id, true);
        $this->load->view('admin/requsite_forms/pipeline/estimate', $data);
    }

    public function get_estimate_data_ajax($id, $to_return = false)
    {
        if (!has_permission('requisite_forms', '', 'view') && !has_permission('requisite_forms', '', 'view_own')) {
            echo _l('access_denied');
            die;
        }

        if (!$id) {
            die('No requsite form found');
        }

        $estimate = $this->requsite_form_model->get($id);

        if (!$estimate) {
            echo _l('No requsite form found');
            die;
        }

        $estimate->date = _d($estimate->date);
        $estimate->expirydate = _d($estimate->expirydate);
        if ($estimate->invoiceid !== null) {
            $this->load->model('estimates_model');
            $estimate->invoice = $this->estimates_model->get($estimate->invoiceid);
        }

        if ($estimate->sent == 0) {
            $template_name = 'estimate-send-to-client';
        } else {
            $template_name = 'estimate-already-send';
        }

        $contact = $this->clients_model->get_contact(get_primary_contact_user_id($estimate->clientid));
        $email = '';
        if ($contact) {
            $email = $contact->email;
        }

        $data['template'] = get_email_template_for_sending($template_name, $email);
        $data['template_name'] = $template_name;

        $this->db->where('slug', $template_name);
        $this->db->where('language', 'english');
        $template_result = $this->db->get('tblemailtemplates')->row();

        $data['template_system_name'] = $template_result->name;
        $data['template_id'] = $template_result->emailtemplateid;

        $data['template_disabled'] = false;
        if (total_rows('tblemailtemplates', ['slug' => $data['template_name'], 'active' => 0]) > 0) {
            $data['template_disabled'] = true;
        }

        $data['activity'] = $this->requsite_form_model->get_estimate_activity($id);
        $data['estimate'] = $estimate;
        $data['members'] = $this->staff_model->get('', ['active' => 1]);
        $data['estimate_statuses'] = $this->requsite_form_model->get_statuses();
        $data['totalNotes'] = total_rows('tblnotes', ['rel_id' => $id, 'rel_type' => 'estimate']);
        if ($to_return == false) {
            $this->load->view('admin/requsite_forms/estimate_preview_template', $data);
        } else {
            return $this->load->view('admin/requsite_forms/estimate_preview_template', $data, true);
        }
    }

    public function update_pipeline()
    {
        if (has_permission('requisite_forms', '', 'edit')) {
            $this->requsite_form_model->update_pipeline($this->input->post());
        }
    }

    public function pipeline_load_more()
    {
        $status = $this->input->get('status');
        $page = $this->input->get('page');

        $estimates = $this->requsite_form_model->do_kanban_query($status, $this->input->get('search'), $page, [
            'sort_by' => $this->input->get('sort_by'),
            'sort' => $this->input->get('sort'),
        ]);

        foreach ($estimates as $estimate) {
            $this->load->view('admin/requsite_forms/pipeline/_kanban_card', [
                'estimate' => $estimate,
                'status' => $status,
            ]);
        }
    }

    public function get_due_date()
    {
        if ($this->input->post()) {
            $date = $this->input->post('date');
            $duedate = '';
            if (get_option('estimate_due_after') != 0) {
                $date = to_sql_date($date);
                $d = date('Y-m-d', strtotime('+' . get_option('estimate_due_after') . ' DAY', strtotime($date)));
                $duedate = _d($d);
                echo $duedate;
            }
        }
    }

    public function get_latest_number(){
        $next_estimate_number = get_option('next_requsite_form_number');
        $__number = $next_estimate_number;
        $_estimate_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
        $response['success'] = true;
        $response['number'] = $_estimate_number;
        echo json_encode($response);
        die;
    }
}
