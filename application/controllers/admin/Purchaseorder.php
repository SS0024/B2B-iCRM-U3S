<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Purchaseorder extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchaseorder_model');
    }

    /* Get all estimates in case user go on index page */
    public function index($id = '')
    {
        $this->list_purchaseorder($id);
    }

    /* List all estimates datatables */
    public function list_purchaseorder($id = '')
    {
        if (!has_permission('purchaseorder', '', 'view') && !has_permission('purchaseorder', '', 'view_own')) {
            access_denied('purchaseorder');
        }

        $isPipeline = $this->session->userdata('estimate_pipeline') == 'true';

        $data['estimate_statuses'] = $this->purchaseorder_model->get_statuses();
        if ($isPipeline && !$this->input->get('status') && !$this->input->get('filter')) {
            $data['title'] = _l('estimates_pipeline');
            $data['bodyclass'] = 'estimates-pipeline estimates-total-manual';
            $data['switch_pipeline'] = false;

            if (is_numeric($id)) {
                $data['estimateid'] = $id;
            } else {
                $data['estimateid'] = $this->session->flashdata('estimateid');
            }

            $this->load->view('admin/purchaseorder/pipeline/manage', $data);
        } else {

            // Pipeline was initiated but user click from home page and need to show table only to filter
            if ($this->input->get('status') || $this->input->get('filter') && $isPipeline) {
                $this->pipeline(0, true);
            }

            $data['estimateid'] = $id;
            $data['switch_pipeline'] = true;
            $data['title'] = _l('Purchase Order');
            $data['bodyclass'] = 'estimates-total-manual';
            $data['estimates_years'] = $this->purchaseorder_model->get_estimates_years();
            $data['estimates_sale_agents'] = $this->purchaseorder_model->get_sale_agents();
            $this->load->view('admin/purchaseorder/manage', $data);
        }
    }

    public function table($clientid = '')
    {
        if (!has_permission('purchaseorder', '', 'view') && !has_permission('purchaseorder', '', 'view_own')) {
            ajax_access_denied();
        }

        $this->app->get_table_data('purchaseorder', [
            'clientid' => $clientid,
        ]);
    }

    /* Add new estimate or update existing */
    public function purchaseorder($id = '')
    {   
        // error_reporting(E_ALL);
        // ini_set('display_errors', TRUE);
        // ini_set('display_startup_errors', TRUE);
        if ($this->input->post()) {
            $estimate_data = $this->input->post();
            if ($id == '') {
                if (!has_permission('purchaseorder', '', 'create')) {
                    access_denied('purchaseorder');
                }
                $id = $this->purchaseorder_model->add($estimate_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('Purchase Order')));
                    if ($this->set_estimate_pipeline_autoload($id)) {
                        redirect(admin_url('purchaseorder/list_purchaseorder/'));
                    } else {
                        redirect(admin_url('purchaseorder/list_purchaseorder/' . $id));
                    }
                }
            } else {
                if (!has_permission('purchaseorder', '', 'edit')) {
                    access_denied('purchaseorder');
                }
                $success = $this->purchaseorder_model->update($estimate_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('Purchase order')));
                }
                if ($this->set_estimate_pipeline_autoload($id)) {
                    redirect(admin_url('purchaseorder/list_purchaseorder/'));
                } else {
                    redirect(admin_url('purchaseorder/list_purchaseorder/' . $id));
                }
            }
        }
        if ($id == '') {
            $title = _l('Create New Purchase Order');
        } else {
            $estimate = $this->purchaseorder_model->get($id);

            if (!$estimate) {
                blank_page(_l('purchaseorder_not_found'));
            }

            $data['estimate'] = $estimate;
            $data['edit'] = true;
            $title = _l('edit', _l('purchase order'));
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
        $data['items_groups'] = $this->invoice_items_model->get_groups();
        $data['items_brands'] = $this->invoice_items_model->get_brands();
        $data['items_units'] = $this->invoice_items_model->get_units();
        $this->load->model('warehouses_model');
        $data['warehouses'] = $this->warehouses_model->getAllWarehouses();

        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['estimate_statuses'] = $this->purchaseorder_model->get_statuses();
        $data['title'] = $title;
        $data['bodyclass'] = 'purchaseorder';
        $this->load->view('admin/purchaseorder/estimate', $data);
    }

    public function clear_signature($id)
    {
        if (has_permission('purchaseorder', '', 'delete')) {
            $this->purchaseorder_model->clear_signature($id);
        }

        redirect(admin_url('purchaseorder/list_purchaseorder/' . $id));
    }

    public function update_number_settings($id)
    {
        $response = [
            'success' => false,
            'message' => '',
        ];
        if (has_permission('purchaseorder', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update('tblpurchaseorder', [
                'prefix' => $this->input->post('prefix'),
            ]);
            if ($this->db->affected_rows() > 0) {
                $response['success'] = true;
                $response['message'] = _l('updated_successfully', _l('Purchaseorder'));
            }
        }

        echo json_encode($response);
        die;
    }

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

        if (total_rows('tblpurchaseorder', [
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
            echo $this->purchaseorder_model->delete_attachment($id);
        } else {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }
    }

    /* Get all estimate data used when user click on estimate number in a datatable left side*/
    public function get_estimate_data_ajax($id, $to_return = false)
    {
        if (!has_permission('purchaseorder', '', 'view') && !has_permission('purchaseorder', '', 'view_own')) {
            echo _l('access_denied');
            die;
        }

        if (!$id) {
            die('No estimate found');
        }

        $estimate = $this->purchaseorder_model->get($id);

        if (!$estimate) {
            echo _l('estimate_not_found');
            die;
        }

        $estimate->date = _d($estimate->date);
        $estimate->expirydate = _d($estimate->expirydate);
        if ($estimate->invoiceid !== null) {
            $this->load->model('invoices_model');
            $estimate->invoice = $this->invoices_model->get($estimate->invoiceid);
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

        $data['activity'] = $this->purchaseorder_model->get_estimate_activity($id);
        $data['estimate'] = $estimate;
        $data['members'] = $this->staff_model->get('', ['active' => 1]);
        $data['estimate_statuses'] = $this->purchaseorder_model->get_statuses();
        $data['totalNotes'] = total_rows('tblnotes', ['rel_id' => $id, 'rel_type' => 'estimate']);
        if ($to_return == false) {
            $this->load->view('admin/purchaseorder/estimate_preview_template', $data);
        } else {
            return $this->load->view('admin/purchaseorder/estimate_preview_template', $data, true);
        }
    }

    public function get_purchaseorder_total()
    {
        if ($this->input->post()) {
            $data['totals'] = $this->purchaseorder_model->get_estimates_total($this->input->post());

            $this->load->model('currencies_model');

            if (!$this->input->post('customer_id')) {
                $multiple_currencies = call_user_func('is_using_multiple_currencies', 'tblestimates');
            } else {
                $multiple_currencies = call_user_func('is_client_using_multiple_currencies', $this->input->post('customer_id'), 'tblestimates');
            }

            if ($multiple_currencies) {
                $data['currencies'] = $this->currencies_model->get();
            }

            $data['estimates_years'] = $this->purchaseorder_model->get_estimates_years();

            if (count($data['estimates_years']) >= 1 && $data['estimates_years'][0]['year'] != date('Y')) {
                array_unshift($data['estimates_years'], ['year' => date('Y')]);
            }

            $data['_currency'] = $data['totals']['currencyid'];
            unset($data['totals']['currencyid']);
            $this->load->view('admin/purchaseorder/estimates_total_template', $data);
        }
    }

    public function add_note($rel_id)
    {
        if ($this->input->post()) {
            $this->misc_model->add_note($this->input->post(), 'purchaseorder', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        $data['notes'] = $this->misc_model->get_notes($id, 'purchaseorder');
        $this->load->view('admin/includes/sales_notes_template', $data);
    }

    public function mark_action_status($status, $id)
    {
        if (!has_permission('purchaseorder', '', 'edit')) {
            access_denied('purchaseorder');
        }
        $success = $this->purchaseorder_model->mark_action_status($status, $id);
        if ($success) {
            set_alert('success', _l('PO status changed'));
        } else {
            set_alert('danger', _l('Failed to change PO status'));
        }
        if ($this->set_estimate_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('purchaseorder/list_purchaseorder/' . $id));
        }
    }

    public function send_expiry_reminder($id)
    {
        if (!has_permission('purchaseorder', '', 'view') && !has_permission('purchaseorder', '', 'view_own')) {
            access_denied('purchaseorder');
        }

        $success = $this->purchaseorder_model->send_expiry_reminder($id);
        if ($success) {
            set_alert('success', _l('sent_expiry_reminder_success'));
        } else {
            set_alert('danger', _l('sent_expiry_reminder_fail'));
        }
        if ($this->set_estimate_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('purchaseorder/list_purchaseorder/' . $id));
        }
    }

    /* Send estimate to email */
    public function send_to_email($id)
    {
        if (!has_permission('purchaseorder', '', 'view') && !has_permission('purchaseorder', '', 'view_own')) {
            access_denied('purchaseorder');
        }

        try {
            $success = $this->purchaseorder_model->send_estimate_to_client($id, '', $this->input->post('attach_pdf'), $this->input->post('cc'));
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
            redirect(admin_url('purchaseorder/list_purchaseorder/' . $id));
        }
    }

    /* Record new inoice payment view */
    public function record_invoice_payment_ajax($id)
    {
        $this->load->model('payment_modes_model');
        $this->load->model('purchaseorder_payments_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $data['invoice']  = $invoice  = $this->purchaseorder_model->get($id);
        $data['payments'] = $this->purchaseorder_payments_model->get_invoice_payments($id);
        $this->load->view('admin/purchaseorder/record_payment_template', $data);
    }


    public function record_payment()
    {
        if (!has_permission('payments', '', 'create')) {
            access_denied('Record Payment');
        }
        if ($this->input->post()) {
            $this->load->model('purchaseorder_payments_model');
            $post_data	=	$this->input->post();
            unset($post_data['temp_file_id']);
            $id = $this->purchaseorder_payments_model->process_payment($post_data, '');

            if ($id) {
                //echo $this->input->post('temp_file_id');
                if($this->input->post('temp_file_id')){
                    $temp_id = $this->input->post('temp_file_id');
                    $temp_attachment_data =	$this->misc_model->get_temp_attachment($temp_id);

                    if(!empty($temp_attachment_data)){

                        $attachment_detail[0]['file_name'] = $temp_attachment_data->file_name;
                        $attachment_detail[0]['filetype'] = $temp_attachment_data->filetype;

                        $this->misc_model->add_attachment_to_database($id,$temp_attachment_data->rel_type,$attachment_detail);


                        do_action('before_upload_invoice_payment_attachment', $id);


                        $img_source_path = get_upload_path_by_type('invoice_payment_temp').$temp_attachment_data->file_name;
                        $img_target_path = get_upload_path_by_type('invoice_payment'). $id . '/';

                        _maybe_create_upload_path($img_target_path);
                        $img_target_file_path = $img_target_path.$temp_attachment_data->file_name;

                        copy($img_source_path,$img_target_file_path);

                        //echo "copied";die;
                    }

                }
                set_alert('success', _l('invoice_payment_recorded'));
                redirect(admin_url('purchaseorder/list_purchaseorder/' . $post_data['purchaseorder_id']));

            } else {
                set_alert('danger', _l('invoice_payment_record_failed'));
            }


            redirect(admin_url('purchaseorder/list_purchaseorder/' . $this->input->post('purchaseorder_id')));


        }
    }

    /* Convert estimate to invoice */
    public function convert_to_invoice($id)
    {
        if (!has_permission('invoices', '', 'create')) {
            access_denied('invoices');
        }
        if (!$id) {
            die('No estimate found');
        }
        $draft_invoice = false;
        if ($this->input->get('save_as_draft')) {
            $draft_invoice = true;
        }
        $invoiceid = $this->purchaseorder_model->convert_to_invoice($id, false, $draft_invoice);
        if ($invoiceid) {
            set_alert('success', _l('estimate_convert_to_invoice_successfully'));
            if($draft_invoice){
                redirect(admin_url('invoices/invoice/' . $invoiceid));
            }else{
                redirect(admin_url('invoices/list_invoices/' . $invoiceid));
            }
        } else {
            if ($this->session->has_userdata('estimate_pipeline') && $this->session->userdata('estimate_pipeline') == 'true') {
                $this->session->set_flashdata('estimateid', $id);
            }
            if ($this->set_estimate_pipeline_autoload($id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('purchaseorder/list_purchaseorder/' . $id));
            }
        }
    }

    public function copy($id)
    {
        if (!has_permission('estimates', '', 'create')) {
            access_denied('estimates');
        }
        if (!$id) {
            die('No estimate found');
        }
        $new_id = $this->purchaseorder_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('estimate_copied_successfully'));
            if ($this->set_estimate_pipeline_autoload($new_id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('purchaseorder/estimate/' . $new_id));
            }
        }
        set_alert('danger', _l('estimate_copied_fail'));
        if ($this->set_estimate_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('purchaseorder/estimate/' . $id));
        }
    }

    public function copy_from_estimate($id)
    {
        if (!has_permission('purchaseorder', '', 'create')) {
            access_denied('purchaseorder');
        }
        if (!$id) {
            die('No estimate found');
        }
        $draft_invoice = false;
        if ($this->input->get('save_as_draft')) {
            $draft_invoice = true;
        }
        $new_id = $this->purchaseorder_model->copy_from_estimate($id);
        if ($new_id) {
            set_alert('success', _l('Purchase Order created successfully.'));
            if ($this->set_estimate_pipeline_autoload($new_id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('purchaseorder/purchaseorder/' . $new_id));
            }
        }
        set_alert('danger', _l('Failed to generate Purchase Order.'));
        if ($this->set_estimate_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('estimates/estimate/' . $id));
        }
    }

    public function copy_from_inquiry($id)
    {
        if (!has_permission('purchaseorder', '', 'create')) {
            access_denied('purchaseorder');
        }
        if (!$id) {
            die('No estimate found');
        }
        $draft_invoice = false;
        if ($this->input->get('save_as_draft')) {
            $draft_invoice = true;
        }
        $new_id = $this->purchaseorder_model->copy_from_inquiry($id);
        if ($new_id) {
            set_alert('success', _l('Purchase Order created successfully.'));
            if ($this->set_estimate_pipeline_autoload($new_id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('purchaseorder/purchaseorder/' . $new_id));
            }
        }
        set_alert('danger', _l('Failed to generate Purchase Order.'));
        if ($this->set_estimate_pipeline_autoload($id)) {
            redirect(admin_url('inquiries/inquiry/' . $id));
        } else {
            redirect(admin_url('inquiries/inquiry/' . $id));
        }
    }

    /* Delete estimate */
    public function delete($id)
    {
        if (!has_permission('purchaseorder', '', 'delete')) {
            access_denied('purchaseorder');
        }
        if (!$id) {
            redirect(admin_url('purchaseorder/list_purchaseorder'));
        }
        $success = $this->purchaseorder_model->delete($id);
        if (is_array($success)) {
            set_alert('warning', _l('is_invoiced_estimate_delete_error'));
        } elseif ($success == true) {
            set_alert('success', _l('deleted', _l('estimate')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('estimate_lowercase')));
        }
        redirect(admin_url('purchaseorder/list_purchaseorder'));
    }

    public function clear_acceptance_info($id)
    {
        if (is_admin()) {
            $this->db->where('id', $id);
            $this->db->update('tblestimates', get_acceptance_info_array(true));
        }

        redirect(admin_url('purchaseorder/list_purchaseorder/' . $id));
    }

    /* Generates estimate PDF and senting to email  */
    public function pdf($id)
    {
        $_SESSION['xqut'] = 1;
        if (!has_permission('purchaseorder', '', 'view') && !has_permission('purchaseorder', '', 'view_own')) {
            access_denied('purchaseorder');
        }
        if (!$id) {
            redirect(admin_url('purchaseorder/list_purchaseorder'));
        }
        $estimate = $this->purchaseorder_model->get($id);
        $estimate_number = format_purchaseorder_number($estimate->id);

        try {
            $pdf = purchaseorder_pdf($estimate);
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

    public function pipdf($id)
    {
        $_SESSION['xqut'] = 2;

        if (!has_permission('purchaseorder', '', 'view') && !has_permission('purchaseorder', '', 'view_own')) {
            access_denied('purchaseorder');
        }
        if (!$id) {
            redirect(admin_url('purchaseorder/list_purchaseorder'));
        }
        $estimate = $this->purchaseorder_model->get($id);
        $estimate_number = format_purchaseorder_number($estimate->id);

        try {
            $pdf = purchaseorder_pdf($estimate);
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
    public function get_pipeline()
    {
        if (has_permission('purchaseorder', '', 'view') || has_permission('purchaseorder', '', 'view_own')) {
            $data['estimate_statuses'] = $this->purchaseorder_model->get_statuses();
            $this->load->view('admin/purchaseorder/pipeline/pipeline', $data);
        }
    }

    public function pipeline_open($id)
    {
        if (!has_permission('purchaseorder', '', 'view') && !has_permission('purchaseorder', '', 'view_own')) {
            access_denied('purchaseorder');
        }

        $data['id'] = $id;
        $data['estimate'] = $this->get_estimate_data_ajax($id, true);
        $this->load->view('admin/purchaseorder/pipeline/estimate', $data);
    }


    public function ajax_load($custid = '')
    {
        $mo = $_REQUEST['mo'];
        if ($_REQUEST['type'] == 'loaddivisions') {
            $this->db->select('*');
            $this->db->from('tbldivision');
            $this->db->where('userid', $_REQUEST['customer']);
            $this->db->order_by('id', 'asc');
            $lead_division = $this->db->get()->result_array();
            ?>
            <select class="selectpicker display-block mbot15 flmd" name="division" id="division" data-width="100%"
                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                    style="display: block!important;"
                    onchange="fnbd(document.getElementById('division').value,'contact','<?php echo $mo; ?>');">
                <?php
                if ($estimate->division == "NULL" OR $estimate->division == "") {
                    ?>
                    <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                    <?php
                } else {
                    ?>
                    <option value="<?php echo $estimate->division; ?>"><?php echo $estimate->division; ?></option>
                    <?php
                }
                ?>
                <?php foreach ($lead_division as $division) { ?>
                    <option value="<?php echo $division['id']; ?>"><?php echo $division['division']; ?></option>
                <?php } ?>
            </select>
            <?php
        } else {
            $this->db->select('*');
            $this->db->from('tblcontacts');
            $this->db->where('division', $_REQUEST['division']);
            $this->db->order_by('id', 'asc');
            $lead_contact = $this->db->get()->result_array();
            if (!empty($_REQUEST['customer'])) {
                ?>
                <label class="control-label"><?php echo 'Contact'; ?></label>
                <?php
            }
            ?>
            <select class="selectpicker display-block mbot15 flmd" name="contact" id="contact-select" data-width="100%"
                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                    style="display: block!important;">
                <?php
                if ($estimate->contact == "NULL" OR $estimate->contact == "") {
                    ?>
                    <option value=""><?php echo _l('dropdown_non_selected_tex'); ?></option>
                    <?php
                } else {
                    ?>
                    <option value="<?php echo $estimate->contact; ?>"><?php echo $estimate->contact; ?></option>
                    <?php
                }
                ?>
                <?php foreach ($lead_contact as $contact) {
                    if ($contact['firstname'] != '/' AND $contact['firstname'] != '//') {
                        ?>
                        <option value="<?php echo $contact['id']; ?>"><?php echo $contact['firstname'] ?> <?php echo $contact['lastname']; ?>
                            (<?php echo $contact['title']; ?>) (<?php echo $contact['phonenumber']; ?>)
                        </option>
                        <?php
                    }
                } ?>
            </select>
            <?php
        }
    }


    public function update_pipeline()
    {
        if (has_permission('purchaseorder', '', 'edit')) {
            $this->purchaseorder_model->update_pipeline($this->input->post());
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
            redirect(admin_url('purchaseorder/list_purchaseorder'));
        }
    }

    public function pipeline_load_more()
    {
        $status = $this->input->get('status');
        $page = $this->input->get('page');

        $estimates = $this->purchaseorder_model->do_kanban_query($status, $this->input->get('search'), $page, [
            'sort_by' => $this->input->get('sort_by'),
            'sort' => $this->input->get('sort'),
        ]);

        foreach ($estimates as $estimate) {
            $this->load->view('admin/purchaseorder/pipeline/_kanban_card', [
                'estimate' => $estimate,
                'status' => $status,
            ]);
        }
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

    public function get_due_date()
    {
        if ($this->input->post()) {
            $date = $this->input->post('date');
            $duedate = '';
            if (get_option('purchaseorder_due_after') != 0) {
                $date = to_sql_date($date);
                $d = date('Y-m-d', strtotime('+' . get_option('purchaseorder_due_after') . ' DAY', strtotime($date)));
                $duedate = _d($d);
                echo $duedate;
            }
        }
    }

    public function sync_div_con(){
        $this->db->select('id,div_con');
        $this->db->from('tblpurchaseorder');
        $this->db->where('div_con <>', '');
        $oldData = $this->db->get()->result_array();
        foreach($oldData as $val){
            handle_removed_div_cons_post($val['id'],'purchaseorder');
            $div_cons = json_decode($val['div_con']);
            foreach ($div_cons as $con => $div) {
                if(isset($div)){
                    add_new_div_con_item_post($div, $con, $val['id'], 'purchaseorder');
                }
            }
        }
        echo 'done';
    }

    public function get_latest_number(){
        $next_estimate_number = get_option('next_purchaseorder_number');
        $__number = $next_estimate_number;
        $_estimate_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
        $response['success'] = true;
        $response['number'] = $_estimate_number;
        echo json_encode($response);
        die;
    }
}
