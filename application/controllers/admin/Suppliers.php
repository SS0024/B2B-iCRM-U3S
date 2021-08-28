<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Suppliers extends Admin_controller
{
    public $pdf_zip;
    private $not_importable_clients_fields;

    public function __construct()
    {
        parent::__construct();
        $this->load->model('suppliers_model');
        $this->not_importable_clients_fields = do_action('not_importable_clients_fields', ['userid', 'id', 'is_primary', 'password', 'datecreated', 'last_ip', 'last_login', 'last_password_change', 'active', 'new_pass_key', 'new_pass_key_requested', 'leadid', 'default_currency', 'profile_image', 'default_language', 'direction', 'show_primary_contact', 'invoice_emails', 'estimate_emails', 'project_emails', 'task_emails', 'contract_emails', 'credit_note_emails', 'ticket_emails', 'addedfrom', 'registration_confirmed', 'last_active_time']);
        // last_active_time is from Chattr plugin, causing issue
    }

    /* List all clients */
    public function index()
    {
        if (!has_permission('suppliers', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('suppliers', '', 'create')) {
                access_denied('suppliers');
            }
        }

        $data['title'] = _l('Suppliers');

        $this->load->view('admin/suppliers/manage', $data);
    }

    public function table()
    {
        if (!has_permission('suppliers', '', 'view')) {
            if (!have_assigned_customers() && !has_permission('suppliers', '', 'create')) {
                ajax_access_denied();
            }
        }

        $this->app->get_table_data('suppliers');
    }

    /* Edit client or add new client*/
    public function supplier($id = '')
    {
        if (!has_permission('suppliers', '', 'view')) {
            if ($id != '' && !is_customer_admin($id)) {
                access_denied('suppliers');
            }
        }

        if ($this->input->post() && !$this->input->is_ajax_request()) {
            if ($id == '') {
                if (!has_permission('suppliers', '', 'create')) {
                    access_denied('suppliers');
                }

                $data = $this->input->post();
                $save_and_add_contact = false;
                $id = $this->suppliers_model->add($data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('Supplier')));
                    redirect(admin_url('suppliers'));
                }
            } else {
                if (!has_permission('suppliers', '', 'edit')) {
                    if (!is_customer_admin($id)) {
                        access_denied('suppliers');
                    }
                }
                $success = $this->suppliers_model->update($this->input->post(), $id);
                if ($success == true) {
                    set_alert('success', _l('updated_successfully', _l('Supplier')));
                }
                redirect(admin_url('suppliers'));
            }
        }

        if (!$this->input->get('group')) {
            $group = 'profile';
        } else {
            $group = $this->input->get('group');
        }

        if ($group != 'contacts' && $contact_id = $this->input->get('contactid')) {
            redirect(admin_url('clients/client/' . $id . '?group=contacts&contactid=' . $contact_id));
        }

        // View group
        $data['group'] = $group;
        // Customer groups
        $data['groups'] = $this->suppliers_model->get_groups();

        if ($id == '') {
            $title = _l('add_new', _l('supplier'));
        } else {
            $client = $this->suppliers_model->get($id);
            if (!$client) {
                blank_page('Client Not Found');
            }

            $data['staff'] = $this->staff_model->get('', ['active' => 1]);

            $data['client'] = $client;
            $title = $client->company;

            // Get all active staff members (used to add reminder)
            $data['members'] = $data['staff'];
        }

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['bodyclass'] = 'customer-profile dynamic-create-groups';
        $data['title'] = $title;

        $this->load->view('admin/suppliers/client', $data);
    }

    public function export($contact_id)
    {
        if (is_admin()) {
            export_contact_data($contact_id);
        }
    }

    public function save_longitude_and_latitude($client_id)
    {
        if (!has_permission('suppliers', '', 'edit')) {
            if (!is_customer_admin($client_id)) {
                ajax_access_denied();
            }
        }

        $this->db->where('userid', $client_id);
        $this->db->update('tblclients', [
            'longitude' => $this->input->post('longitude'),
            'latitude' => $this->input->post('latitude'),
        ]);
        if ($this->db->affected_rows() > 0) {
            echo 'success';
        } else {
            echo 'false';
        }
    }

    public function confirm_registration($client_id)
    {
        if (!is_admin()) {
            access_denied('Customer Confirm Registration, ID: ' . $client_id);
        }
        $this->suppliers_model->confirm_registration($client_id);
        set_alert('success', _l('customer_registration_successfully_confirmed'));
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function update_file_share_visibility()
    {
        if ($this->input->post()) {
            $file_id = $this->input->post('file_id');
            $share_contacts_id = [];

            if ($this->input->post('share_contacts_id')) {
                $share_contacts_id = $this->input->post('share_contacts_id');
            }

            $this->db->where('file_id', $file_id);
            $this->db->delete('tblcustomerfiles_shares');

            foreach ($share_contacts_id as $share_contact_id) {
                $this->db->insert('tblcustomerfiles_shares', [
                    'file_id' => $file_id,
                    'contact_id' => $share_contact_id,
                ]);
            }
        }
    }

    public function delete_contact_profile_image($contact_id)
    {
        do_action('before_remove_contact_profile_image');
        if (file_exists(get_upload_path_by_type('contact_profile_images') . $contact_id)) {
            delete_dir(get_upload_path_by_type('contact_profile_images') . $contact_id);
        }
        $this->db->where('id', $contact_id);
        $this->db->update('tblcontacts', [
            'profile_image' => null,
        ]);
    }

    public function mark_as_active($id)
    {
        $this->db->where('userid', $id);
        $this->db->update('tblclients', [
            'active' => 1,
        ]);
        redirect(admin_url('clients/client/' . $id));
    }

    public function consents($id)
    {
        if (!has_permission('suppliers', '', 'view')) {
            if (!is_customer_admin(get_user_id_by_contact_id($id))) {
                echo _l('access_denied');
                die;
            }
        }

        $this->load->model('gdpr_model');
        $data['purposes'] = $this->gdpr_model->get_consent_purposes($id, 'contact');
        $data['consents'] = $this->gdpr_model->get_consents(['contact_id' => $id]);
        $data['contact_id'] = $id;
        $this->load->view('admin/gdpr/contact_consent', $data);
    }

    public function update_all_proposal_emails_linked_to_customer($contact_id)
    {
        $success = false;
        $email = '';
        if ($this->input->post('update')) {
            $this->load->model('proposals_model');

            $this->db->select('email,userid');
            $this->db->where('id', $contact_id);
            $contact = $this->db->get('tblcontacts')->row();

            $proposals = $this->proposals_model->get('', [
                'rel_type' => 'customer',
                'rel_id' => $contact->userid,
                'email' => $this->input->post('original_email'),
            ]);
            $affected_rows = 0;

            foreach ($proposals as $proposal) {
                $this->db->where('id', $proposal['id']);
                $this->db->update('tblproposals', [
                    'email' => $contact->email,
                ]);
                if ($this->db->affected_rows() > 0) {
                    $affected_rows++;
                }
            }

            if ($affected_rows > 0) {
                $success = true;
            }
        }
        echo json_encode([
            'success' => $success,
            'message' => _l('proposals_emails_updated', [
                _l('contact_lowercase'),
                $contact->email,
            ]),
        ]);
    }

    public function assign_admins($id)
    {
        if (!has_permission('suppliers', '', 'create') && !has_permission('suppliers', '', 'edit')) {
            access_denied('suppliers');
        }
        $success = $this->suppliers_model->assign_admins($this->input->post(), $id);
        if ($success == true) {
            set_alert('success', _l('updated_successfully', _l('client')));
        }

        redirect(admin_url('clients/client/' . $id . '?tab=customer_admins'));
    }

    public function delete_customer_admin($customer_id, $staff_id)
    {
        if (!has_permission('suppliers', '', 'create') && !has_permission('suppliers', '', 'edit')) {
            access_denied('suppliers');
        }

        $this->db->where('customer_id', $customer_id);
        $this->db->where('staff_id', $staff_id);
        $this->db->delete('tblcustomeradmins');
        redirect(admin_url('clients/client/' . $customer_id) . '?tab=customer_admins');
    }

    public function delete_contact($customer_id, $id)
    {
        if (!has_permission('suppliers', '', 'delete')) {
            if (!is_customer_admin($customer_id)) {
                access_denied('suppliers');
            }
        }
        $contact = $this->suppliers_model->get_contact($id);
        $hasProposals = false;
        if ($contact && is_gdpr()) {
            if (total_rows('tblproposals', ['email' => $contact->email]) > 0) {
                $hasProposals = true;
            }
        }

        $this->suppliers_model->delete_contact($id);
        if ($hasProposals) {
            $this->session->set_flashdata('gdpr_delete_warning', true);
        }
        redirect(admin_url('clients/client/' . $customer_id . '?group=contacts'));
    }

    public function contacts($client_id)
    {
        $this->app->get_table_data('contacts', [
            'client_id' => $client_id,
        ]);
    }

    public function upload_attachment($id)
    {
        handle_client_attachments_upload($id);
    }

    public function add_external_attachment()
    {
        if ($this->input->post()) {
            $this->misc_model->add_attachment_to_database($this->input->post('clientid'), 'customer', $this->input->post('files'), $this->input->post('external'));
        }
    }

    public function delete_attachment($customer_id, $id)
    {
        if (has_permission('suppliers', '', 'delete') || is_customer_admin($customer_id)) {
            $this->suppliers_model->delete_attachment($id);
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function search()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            echo json_encode($this->suppliers_model->search($this->input->post('q')));
        }
    }
    public function get_supplier($q)
    {
        if ($this->input->is_ajax_request()) {
            $result =$this->suppliers_model->get($q);
            if($q){
                $company_state 	  = get_option('company_state');
               if(!empty($result) && isset($result->state) && strtolower($result->state) == strtolower($company_state)){
                   $result->devide_gst = "yes";
               }else{
                   $result->devide_gst = 'no';
               }
            }
            echo json_encode($result);
        }
    }

    /* Delete client */
    public function delete($id)
    {
        if (!has_permission('suppliers', '', 'delete')) {
            access_denied('suppliers');
        }
        if (!$id) {
            redirect(admin_url('suppliers'));
        }
        $response = $this->suppliers_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('Supplier')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('supplier')));
        }
        redirect(admin_url('suppliers'));
    }

    /* Staff can login as client */
    public function login_as_client($id)
    {
        if (is_admin()) {
            login_as_client($id);
        }
        do_action('after_contact_login');
        redirect(site_url());
    }

    public function get_customer_billing_and_shipping_details($id)
    {
        echo json_encode($this->suppliers_model->get_customer_billing_and_shipping_details($id));
    }

    /* Change client status / active / inactive */
    public function change_contact_status($id, $status)
    {
        if (has_permission('suppliers', '', 'edit') || is_customer_admin(get_user_id_by_contact_id($id))) {
            if ($this->input->is_ajax_request()) {
                $this->suppliers_model->change_contact_status($id, $status);
            }
        }
    }

    /* Change client status / active / inactive */
    public function change_client_status($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->suppliers_model->change_client_status($id, $status);
        }
    }

    /* Zip function for credit notes */
    public function zip_credit_notes($id)
    {
        $has_permission_view = has_permission('credit_notes', '', 'view');

        if (!$has_permission_view && !has_permission('credit_notes', '', 'view_own')) {
            access_denied('Zip Customer Credit Notes');
        }

        if ($this->input->post()) {
            $status = $this->input->post('credit_note_zip_status');
            $zip_file_name = $this->input->post('file_name');
            if ($this->input->post('zip-to') && $this->input->post('zip-from')) {
                $from_date = to_sql_date($this->input->post('zip-from'));
                $to_date = to_sql_date($this->input->post('zip-to'));
                if ($from_date == $to_date) {
                    $this->db->where('date', $from_date);
                } else {
                    $this->db->where('date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
                }
            }
            $this->db->select('id');
            $this->db->from('tblcreditnotes');
            if ($status != 'all') {
                $this->db->where('status', $status);
            }
            $this->db->where('clientid', $id);
            $this->db->order_by('number', 'desc');

            if (!$has_permission_view) {
                $this->db->where('addedfrom', get_staff_user_id());
            }
            $credit_notes = $this->db->get()->result_array();

            $this->load->model('credit_notes_model');

            $this->load->helper('file');
            if (!is_really_writable(TEMP_FOLDER)) {
                show_error('/temp folder is not writable. You need to change the permissions to 755');
            }

            $dir = TEMP_FOLDER . $zip_file_name;

            if (is_dir($dir)) {
                delete_dir($dir);
            }

            if (count($credit_notes) == 0) {
                set_alert('warning', _l('client_zip_no_data_found', _l('credit_notes')));
                redirect(admin_url('clients/client/' . $id . '?group=credit_notes'));
            }

            mkdir($dir, 0755);

            foreach ($credit_notes as $credit_note) {
                $credit_note = $this->credit_notes_model->get($credit_note['id']);
                $this->pdf_zip = credit_note_pdf($credit_note);
                $_temp_file_name = slug_it(format_credit_note_number($credit_note->id));
                $file_name = $dir . '/' . strtoupper($_temp_file_name);
                $this->pdf_zip->Output($file_name . '.pdf', 'F');
            }

            $this->load->library('zip');
            // Read the credit notes
            $this->zip->read_dir($dir, false);
            // Delete the temp directory for the client
            delete_dir($dir);
            $this->zip->download(slug_it(get_option('companyname')) . '-credit-notes-' . $zip_file_name . '.zip');
            $this->zip->clear_data();
        }
    }

    public function zip_invoices($id)
    {
        $has_permission_view = has_permission('invoices', '', 'view');
        if (!$has_permission_view && !has_permission('invoices', '', 'view_own') && get_option('allow_staff_view_invoices_assigned') == '0') {
            access_denied('Zip Customer Invoices');
        }
        if ($this->input->post()) {
            $status = $this->input->post('invoice_zip_status');
            $zip_file_name = $this->input->post('file_name');
            if ($this->input->post('zip-to') && $this->input->post('zip-from')) {
                $from_date = to_sql_date($this->input->post('zip-from'));
                $to_date = to_sql_date($this->input->post('zip-to'));
                if ($from_date == $to_date) {
                    $this->db->where('date', $from_date);
                } else {
                    $this->db->where('date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
                }
            }
            $this->db->select('id');
            $this->db->from('tblinvoices');
            if ($status != 'all') {
                $this->db->where('status', $status);
            }
            $this->db->where('clientid', $id);
            $this->db->order_by('number,YEAR(date)', 'desc');

            if (!$has_permission_view) {
                $this->db->where(get_invoices_where_sql_for_staff(get_staff_user_id()));
            }

            $invoices = $this->db->get()->result_array();
            $this->load->model('invoices_model');
            $this->load->helper('file');
            if (!is_really_writable(TEMP_FOLDER)) {
                show_error('/temp folder is not writable. You need to change the permissions to 755');
            }
            $dir = TEMP_FOLDER . $zip_file_name;
            if (is_dir($dir)) {
                delete_dir($dir);
            }
            if (count($invoices) == 0) {
                set_alert('warning', _l('client_zip_no_data_found', _l('invoices')));
                redirect(admin_url('clients/client/' . $id . '?group=invoices'));
            }
            mkdir($dir, 0755);
            foreach ($invoices as $invoice) {
                $invoice_data = $this->invoices_model->get($invoice['id']);
                $this->pdf_zip = invoice_pdf($invoice_data);
                $_temp_file_name = slug_it(format_invoice_number($invoice_data->id));
                $file_name = $dir . '/' . strtoupper($_temp_file_name);
                $this->pdf_zip->Output($file_name . '.pdf', 'F');
            }
            $this->load->library('zip');
            // Read the invoices
            $this->zip->read_dir($dir, false);
            // Delete the temp directory for the client
            delete_dir($dir);
            $this->zip->download(slug_it(get_option('companyname')) . '-invoices-' . $zip_file_name . '.zip');
            $this->zip->clear_data();
        }
    }

    /* Since version 1.0.2 zip client invoices */
    public function zip_estimates($id)
    {
        $has_permission_view = has_permission('estimates', '', 'view');
        if (!$has_permission_view && !has_permission('estimates', '', 'view_own') && get_option('allow_staff_view_estimates_assigned') == '0') {
            access_denied('Zip Customer Estimates');
        }

        if ($this->input->post()) {
            $status = $this->input->post('estimate_zip_status');
            $zip_file_name = $this->input->post('file_name');
            if ($this->input->post('zip-to') && $this->input->post('zip-from')) {
                $from_date = to_sql_date($this->input->post('zip-from'));
                $to_date = to_sql_date($this->input->post('zip-to'));
                if ($from_date == $to_date) {
                    $this->db->where('date', $from_date);
                } else {
                    $this->db->where('date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
                }
            }
            $this->db->select('id');
            $this->db->from('tblestimates');
            if ($status != 'all') {
                $this->db->where('status', $status);
            }
            if (!$has_permission_view) {
                $this->db->where(get_estimates_where_sql_for_staff(get_staff_user_id()));
            }
            $this->db->where('clientid', $id);
            $this->db->order_by('number,YEAR(date)', 'desc');
            $estimates = $this->db->get()->result_array();
            $this->load->helper('file');
            if (!is_really_writable(TEMP_FOLDER)) {
                show_error('/temp folder is not writable. You need to change the permissions to 0755');
            }
            $this->load->model('estimates_model');
            $dir = TEMP_FOLDER . $zip_file_name;
            if (is_dir($dir)) {
                delete_dir($dir);
            }
            if (count($estimates) == 0) {
                set_alert('warning', _l('client_zip_no_data_found', _l('estimates')));
                redirect(admin_url('clients/client/' . $id . '?group=estimates'));
            }
            mkdir($dir, 0755);
            foreach ($estimates as $estimate) {
                $estimate_data = $this->estimates_model->get($estimate['id']);
                $this->pdf_zip = estimate_pdf($estimate_data);
                $_temp_file_name = slug_it(format_estimate_number($estimate_data->id));
                $file_name = $dir . '/' . strtoupper($_temp_file_name);
                $this->pdf_zip->Output($file_name . '.pdf', 'F');
            }
            $this->load->library('zip');
            // Read the invoices
            $this->zip->read_dir($dir, false);
            // Delete the temp directory for the client
            delete_dir($dir);
            $this->zip->download(slug_it(get_option('companyname')) . '-estimates-' . $zip_file_name . '.zip');
            $this->zip->clear_data();
        }
    }

    public function zip_payments($id)
    {
        if (!$id) {
            die('Invoice ID not passed');
        }

        $has_permission_view = has_permission('payments', '', 'view');
        if (!$has_permission_view && !has_permission('invoices', '', 'view_own') && get_option('allow_staff_view_invoices_assigned') == '0') {
            access_denied('Zip Customer Payments');
        }

        if ($this->input->post('zip-to') && $this->input->post('zip-from')) {
            $from_date = to_sql_date($this->input->post('zip-from'));
            $to_date = to_sql_date($this->input->post('zip-to'));
            if ($from_date == $to_date) {
                $this->db->where('tblinvoicepaymentrecords.date', $from_date);
            } else {
                $this->db->where('tblinvoicepaymentrecords.date BETWEEN "' . $from_date . '" AND "' . $to_date . '"');
            }
        }
        $this->db->select('tblinvoicepaymentrecords.id as paymentid');
        $this->db->from('tblinvoicepaymentrecords');
        $this->db->where('tblclients.userid', $id);
        if (!$has_permission_view) {
            $whereUser = '';
            $whereUser .= '(invoiceid IN (SELECT id FROM tblinvoices WHERE addedfrom=' . get_staff_user_id() . ')';
            if (get_option('allow_staff_view_invoices_assigned') == 1) {
                $whereUser .= ' OR invoiceid IN (SELECT id FROM tblinvoices WHERE sale_agent=' . get_staff_user_id() . ')';
            }
            $whereUser .= ')';
            $this->db->where($whereUser);
        }
        $this->db->join('tblinvoices', 'tblinvoices.id = tblinvoicepaymentrecords.invoiceid', 'left');
        $this->db->join('tblclients', 'tblclients.userid = tblinvoices.clientid', 'left');
        if ($this->input->post('paymentmode')) {
            $this->db->where('paymentmode', $this->input->post('paymentmode'));
        }
        $payments = $this->db->get()->result_array();
        $zip_file_name = $this->input->post('file_name');
        $this->load->helper('file');
        if (!is_really_writable(TEMP_FOLDER)) {
            show_error('/temp folder is not writable. You need to change the permissions to 0755');
        }
        $dir = TEMP_FOLDER . $zip_file_name;
        if (is_dir($dir)) {
            delete_dir($dir);
        }
        if (count($payments) == 0) {
            set_alert('warning', _l('client_zip_no_data_found', _l('payments')));
            redirect(admin_url('clients/client/' . $id . '?group=payments'));
        }
        mkdir($dir, 0755);
        $this->load->model('payments_model');
        $this->load->model('invoices_model');
        foreach ($payments as $payment) {
            $payment_data = $this->payments_model->get($payment['paymentid']);
            $payment_data->invoice_data = $this->invoices_model->get($payment_data->invoiceid);
            $this->pdf_zip = payment_pdf($payment_data);
            $file_name = $dir;
            $file_name .= '/' . strtoupper(_l('payment'));
            $file_name .= '-' . strtoupper($payment_data->paymentid) . '.pdf';
            $this->pdf_zip->Output($file_name, 'F');
        }
        $this->load->library('zip');
        // Read the invoices
        $this->zip->read_dir($dir, false);
        // Delete the temp directory for the client
        delete_dir($dir);
        $this->zip->download(slug_it(get_option('companyname')) . '-payments-' . $zip_file_name . '.zip');
        $this->zip->clear_data();
    }

    public function import()
    {
        if (!has_permission('suppliers', '', 'create')) {
            access_denied('suppliers');
        }
        $country_fields = ['country', 'billing_country', 'shipping_country'];

        $simulate_data = [];
        $total_imported = 0;
        if ($this->input->post()) {

            // Used when checking existing company to merge contact
            $contactFields = $this->db->list_fields('tblcontacts');

            if (isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {

                do_action('before_import_customers');

                // Get the temp file path
                $tmpFilePath = $_FILES['file_csv']['tmp_name'];
                // Make sure we have a filepath
                if (!empty($tmpFilePath) && $tmpFilePath != '') {

                    $tmpDir = TEMP_FOLDER . '/' . time() . uniqid() . '/';

                    if (!file_exists(TEMP_FOLDER)) {
                        mkdir(TEMP_FOLDER, 0755);
                    }

                    if (!file_exists($tmpDir)) {
                        mkdir($tmpDir, 0755);
                    }
                    // Setup our new file path
                    $newFilePath = $tmpDir . $_FILES['file_csv']['name'];


                    if (move_uploaded_file($tmpFilePath, $newFilePath)) {
                        $import_result = true;
                        $fd = fopen($newFilePath, 'r');
                        $rows = [];
                        while ($row = fgetcsv($fd)) {
                            $rows[] = $row;
                        }

                        $data['total_rows_post'] = count($rows);
                        fclose($fd);
                        if (count($rows) <= 1) {
                            set_alert('warning', 'Not enought rows for importing');
                            redirect(admin_url('clients/import'));
                        }
                        unset($rows[0]);
                        if ($this->input->post('simulate')) {
                            if (count($rows) > 500) {
                                set_alert('warning', 'Recommended splitting the CSV file into smaller files. Our recomendation is 500 row, your CSV file has ' . count($rows));
                            }
                        }
                        $client_contacts_fields = $this->db->list_fields('tblcontacts');
                        $i = 0;
                        foreach ($client_contacts_fields as $cf) {
                            if ($cf == 'phonenumber') {
                                $client_contacts_fields[$i] = 'contact_phonenumber';
                            }
                            $i++;
                        }
                        $db_temp_fields = $this->db->list_fields('tblclients');
                        $db_temp_fields = array_merge($client_contacts_fields, $db_temp_fields);
                        $db_fields = [];
                        foreach ($db_temp_fields as $field) {
                            if (in_array($field, $this->not_importable_clients_fields)) {
                                continue;
                            }
                            $db_fields[] = $field;
                        }
                        $custom_fields = get_custom_fields('suppliers');
                        $_row_simulate = 0;

                        $required = [
                            'firstname',
                            'lastname',
                            'email',
                        ];

                        if (get_option('company_is_required') == 1) {
                            array_push($required, 'company');
                        }

                        foreach ($rows as $row) {
                            // do for db fields
                            $insert = [];
                            $duplicate = false;
                            for ($i = 0; $i < count($db_fields); $i++) {
                                if (!isset($row[$i])) {
                                    continue;
                                }
                                if ($db_fields[$i] == 'email') {
                                    $email_exists = total_rows('tblcontacts', [
                                        'email' => $row[$i],
                                    ]);
                                    // don't insert duplicate emails
                                    if ($email_exists > 0) {
                                        $duplicate = true;
                                    }
                                }
                                // Avoid errors on required fields;
                                if (in_array($db_fields[$i], $required) && $row[$i] == '' && $db_fields[$i] != 'company') {
                                    $row[$i] = '/';
                                } elseif (in_array($db_fields[$i], $country_fields)) {
                                    if ($row[$i] != '') {
                                        if (!is_numeric($row[$i])) {
                                            $this->db->where('iso2', $row[$i]);
                                            $this->db->or_where('short_name', $row[$i]);
                                            $this->db->or_where('long_name', $row[$i]);
                                            $country = $this->db->get('tblcountries')->row();
                                            if ($country) {
                                                $row[$i] = $country->country_id;
                                            } else {
                                                $row[$i] = 0;
                                            }
                                        }
                                    } else {
                                        $row[$i] = 0;
                                    }
                                }
                                if ($row[$i] === 'NULL' || $row[$i] === 'null') {
                                    $row[$i] = '';
                                }
                                $insert[$db_fields[$i]] = $row[$i];
                            }


                            if ($duplicate == true) {
                                continue;
                            }
                            if (count($insert) > 0) {
                                $total_imported++;
                                $insert['datecreated'] = date('Y-m-d H:i:s');
                                if ($this->input->post('default_pass_all')) {
                                    $insert['password'] = $this->input->post('default_pass_all', false);
                                }
                                if (!$this->input->post('simulate')) {
                                    $insert['donotsendwelcomeemail'] = true;
                                    foreach ($insert as $key => $val) {
                                        $insert[$key] = trim($val);
                                    }

                                    if (isset($insert['company']) && $insert['company'] != '' && $insert['company'] != '/') {
                                        if (total_rows('tblclients', ['company' => $insert['company']]) === 1) {
                                            $this->db->where('company', $insert['company']);
                                            $existingCompany = $this->db->get('tblclients')->row();
                                            $tmpInsert = [];

                                            foreach ($insert as $key => $val) {
                                                foreach ($contactFields as $tmpContactField) {
                                                    if (isset($insert[$tmpContactField])) {
                                                        $tmpInsert[$tmpContactField] = $insert[$tmpContactField];
                                                    }
                                                }
                                            }
                                            $tmpInsert['donotsendwelcomeemail'] = true;
                                            if (isset($insert['contact_phonenumber'])) {
                                                $tmpInsert['phonenumber'] = $insert['contact_phonenumber'];
                                            }

                                            $contactid = $this->suppliers_model->add_contact($tmpInsert, $existingCompany->userid, true);

                                            continue;
                                        }
                                    }
                                    $insert['is_primary'] = 1;

                                    $clientid = $this->suppliers_model->add($insert, true);
                                    if ($clientid) {
                                        if ($this->input->post('groups_in[]')) {
                                            $groups_in = $this->input->post('groups_in[]');
                                            foreach ($groups_in as $group) {
                                                $this->db->insert('tblcustomergroups_in', [
                                                    'customer_id' => $clientid,
                                                    'groupid' => $group,
                                                ]);
                                            }
                                        }
                                        if (!has_permission('suppliers', '', 'view')) {
                                            $assign['customer_admins'] = [];
                                            $assign['customer_admins'][] = get_staff_user_id();
                                            $this->suppliers_model->assign_admins($assign, $clientid);
                                        }
                                    }
                                } else {
                                    foreach ($country_fields as $country_field) {
                                        if (array_key_exists($country_field, $insert)) {
                                            if ($insert[$country_field] != 0) {
                                                $c = get_country($insert[$country_field]);
                                                if ($c) {
                                                    $insert[$country_field] = $c->short_name;
                                                }
                                            } elseif ($insert[$country_field] == 0) {
                                                $insert[$country_field] = '';
                                            }
                                        }
                                    }
                                    $simulate_data[$_row_simulate] = $insert;
                                    $clientid = true;
                                }
                                if ($clientid) {
                                    $insert = [];
                                    foreach ($custom_fields as $field) {
                                        if (!$this->input->post('simulate')) {
                                            if ($row[$i] != '' && $row[$i] !== 'NULL' && $row[$i] !== 'null') {
                                                $this->db->insert('tblcustomfieldsvalues', [
                                                    'relid' => $clientid,
                                                    'fieldid' => $field['id'],
                                                    'value' => $row[$i],
                                                    'fieldto' => 'suppliers',
                                                ]);
                                            }
                                        } else {
                                            $simulate_data[$_row_simulate][$field['name']] = $row[$i];
                                        }
                                        $i++;
                                    }
                                }
                            }
                            $_row_simulate++;
                            if ($this->input->post('simulate') && $_row_simulate >= 100) {
                                break;
                            }
                        }
                        @delete_dir($tmpDir);
                    }
                } else {
                    set_alert('warning', _l('import_upload_failed'));
                }
            }
        }
        if (count($simulate_data) > 0) {
            $data['simulate'] = $simulate_data;
        }
        if (isset($import_result)) {
            set_alert('success', _l('import_total_imported', $total_imported));
        }
        $data['groups'] = $this->suppliers_model->get_groups();
        $data['not_importable'] = $this->not_importable_clients_fields;
        $data['title'] = _l('import');
        $data['bodyclass'] = 'dynamic-create-groups';
        $this->load->view('admin/clients/import', $data);
    }

    public function groups()
    {
        if (!is_admin()) {
            access_denied('Customer Groups');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('customers_groups');
        }
        $data['title'] = _l('customer_groups');
        $this->load->view('admin/clients/groups_manage', $data);
    }

    public function group()
    {
        if (!is_admin() && get_option('staff_members_create_inline_customer_groups') == '0') {
            access_denied('Customer Groups');
        }

        if ($this->input->is_ajax_request()) {
            $data = $this->input->post();
            if ($data['id'] == '') {
                $id = $this->suppliers_model->add_group($data);
                $message = $id ? _l('added_successfully', _l('customer_group')) : '';
                echo json_encode([
                    'success' => $id ? true : false,
                    'message' => $message,
                    'id' => $id,
                    'name' => $data['name'],
                ]);
            } else {
                $success = $this->suppliers_model->edit_group($data);
                $message = '';
                if ($success == true) {
                    $message = _l('updated_successfully', _l('customer_group'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message,
                ]);
            }
        }
    }

    public function delete_group($id)
    {
        if (!is_admin()) {
            access_denied('Delete Customer Group');
        }
        if (!$id) {
            redirect(admin_url('clients/groups'));
        }
        $response = $this->suppliers_model->delete_group($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('customer_group')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('customer_group_lowercase')));
        }
        redirect(admin_url('clients/groups'));
    }

    public function bulk_action()
    {
        do_action('before_do_bulk_action_for_customers');
        $total_deleted = 0;
        if ($this->input->post()) {
            $ids = $this->input->post('ids');
            $groups = $this->input->post('groups');

            if (is_array($ids)) {
                foreach ($ids as $id) {
                    if ($this->input->post('mass_delete')) {
                        if ($this->suppliers_model->delete($id)) {
                            $total_deleted++;
                        }
                    } else {
                        if (!is_array($groups)) {
                            $groups = false;
                        }
                        $this->client_groups_model->sync_customer_groups($id, $groups);
                    }
                }
            }
        }

        if ($this->input->post('mass_delete')) {
            set_alert('success', _l('total_clients_deleted', $total_deleted));
        }
    }

    public function vault_entry_create($customer_id)
    {
        $data = $this->input->post();

        if (isset($data['fakeusernameremembered'])) {
            unset($data['fakeusernameremembered']);
        }

        if (isset($data['fakepasswordremembered'])) {
            unset($data['fakepasswordremembered']);
        }

        unset($data['id']);
        $data['creator'] = get_staff_user_id();
        $data['creator_name'] = get_staff_full_name($data['creator']);
        $data['description'] = nl2br($data['description']);
        $data['password'] = $this->encryption->encrypt($this->input->post('password', false));

        if (empty($data['port'])) {
            unset($data['port']);
        }

        $this->suppliers_model->vault_entry_create($data, $customer_id);
        set_alert('success', _l('added_successfully', _l('vault_entry')));
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function vault_entry_update($entry_id)
    {
        $entry = $this->suppliers_model->get_vault_entry($entry_id);

        if ($entry->creator == get_staff_user_id() || is_admin()) {
            $data = $this->input->post();

            if (isset($data['fakeusernameremembered'])) {
                unset($data['fakeusernameremembered']);
            }
            if (isset($data['fakepasswordremembered'])) {
                unset($data['fakepasswordremembered']);
            }

            $data['last_updated_from'] = get_staff_full_name(get_staff_user_id());
            $data['description'] = nl2br($data['description']);

            if (!empty($data['password'])) {
                $data['password'] = $this->encryption->encrypt($this->input->post('password', false));
            } else {
                unset($data['password']);
            }

            if (empty($data['port'])) {
                unset($data['port']);
            }

            $this->suppliers_model->vault_entry_update($entry_id, $data);
            set_alert('success', _l('updated_successfully', _l('vault_entry')));
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function vault_entry_delete($id)
    {
        $entry = $this->suppliers_model->get_vault_entry($id);
        if ($entry->creator == get_staff_user_id() || is_admin()) {
            $this->suppliers_model->vault_entry_delete($id);
        }
        redirect($_SERVER['HTTP_REFERER']);
    }

    public function vault_encrypt_password()
    {
        $id = $this->input->post('id');
        $user_password = $this->input->post('user_password', false);
        $user = $this->staff_model->get(get_staff_user_id());

        $this->load->helper('phpass');

        $hasher = new PasswordHash(PHPASS_HASH_STRENGTH, PHPASS_HASH_PORTABLE);
        if (!$hasher->CheckPassword($user_password, $user->password)) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['error_msg' => _l('vault_password_user_not_correct')]);
            die;
        }

        $vault = $this->suppliers_model->get_vault_entry($id);
        $password = $this->encryption->decrypt($vault->password);

        $password = html_escape($password);

        // Failed to decrypt
        if (!$password) {
            header('HTTP/1.0 400 Bad error');
            echo json_encode(['error_msg' => _l('failed_to_decrypt_password')]);
            die;
        }

        echo json_encode(['password' => $password]);
    }

    public function get_vault_entry($id)
    {
        $entry = $this->suppliers_model->get_vault_entry($id);
        unset($entry->password);
        $entry->description = clear_textarea_breaks($entry->description);
        echo json_encode($entry);
    }

    public function statement_pdf()
    {
        $customer_id = $this->input->get('customer_id');

        if (!has_permission('invoices', '', 'view') && !has_permission('payments', '', 'view')) {
            set_alert('danger', _l('access_denied'));
            redirect(admin_url('clients/client/' . $customer_id));
        }

        $from = $this->input->get('from');
        $to = $this->input->get('to');

        $data['statement'] = $this->suppliers_model->get_statement($customer_id, to_sql_date($from), to_sql_date($to));

        try {
            $pdf = statement_pdf($data['statement']);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';
        if ($this->input->get('print')) {
            $type = 'I';
        }

        $pdf->Output(slug_it(_l('customer_statement') . '-' . $data['statement']['client']->company) . '.pdf', $type);
    }

    public function send_statement()
    {
        $customer_id = $this->input->get('customer_id');

        if (!has_permission('invoices', '', 'view') && !has_permission('payments', '', 'view')) {
            set_alert('danger', _l('access_denied'));
            redirect(admin_url('clients/client/' . $customer_id));
        }

        $from = $this->input->get('from');
        $to = $this->input->get('to');

        $send_to = $this->input->post('send_to');
        $cc = $this->input->post('cc');

        $success = $this->suppliers_model->send_statement_to_email($customer_id, $send_to, $from, $to, $cc);
        // In case client use another language
        load_admin_language();
        if ($success) {
            set_alert('success', _l('statement_sent_to_client_success'));
        } else {
            set_alert('danger', _l('statement_sent_to_client_fail'));
        }

        redirect(admin_url('clients/client/' . $customer_id . '?group=statement'));
    }

    public function statement()
    {
        if (!has_permission('invoices', '', 'view') && !has_permission('payments', '', 'view')) {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }

        $customer_id = $this->input->get('customer_id');
        $from = $this->input->get('from');
        $to = $this->input->get('to');

        $data['statement'] = $this->suppliers_model->get_statement($customer_id, to_sql_date($from), to_sql_date($to));

        $data['from'] = $from;
        $data['to'] = $to;

        $viewData['html'] = $this->load->view('admin/clients/groups/_statement', $data, true);

        echo json_encode($viewData);
    }
}
