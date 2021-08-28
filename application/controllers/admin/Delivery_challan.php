<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Delivery_challan extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('delivery_challan_model');
        $this->load->model('credit_notes_model');
        $this->load->model('delivery_module_status_model');
        $this->load->model('delivery_module_model');
    }

    /* Get all invoices in case user go on index page */
    public function index($id = '')
    {
        $this->list_invoices($id);
    }

    /* List all invoices datatables */
    public function list_invoices($id = '')
    {
        if (!has_permission('delivery_challan', '', 'view')
            && !has_permission('delivery_challan', '', 'view_own')) {
            access_denied('delivery_challan');
        }

        close_setup_menu();

        $this->load->model('payment_modes_model');
        $data['payment_modes']        		= $this->payment_modes_model->get('', [], true);
        $data['invoiceid']            		= $id;
        $data['title']               		= _l('Delivery Challans');
        $data['invoices_years']       		= $this->delivery_challan_model->get_invoices_years();
        $data['invoices_sale_agents'] 		= $this->delivery_challan_model->get_sale_agents();
        $data['invoices_statuses']    		= $this->delivery_challan_model->get_statuses();
        $data['bodyclass']            		= 'invoices-total-manual';
        $this->load->view('admin/delivery_challan/manage', $data);
    }

    /* List all recurring invoices */
    public function recurring($id = '')
    {
        if (!has_permission('delivery_challan', '', 'view')
            && !has_permission('delivery_challan', '', 'view_own')) {
            access_denied('delivery_challan');
        }

        close_setup_menu();

        $data['invoiceid']            = $id;
        $data['title']                = _l('invoices_list_recurring');
        $data['invoices_years']       = $this->delivery_challan_model->get_invoices_years();
        $data['invoices_sale_agents'] = $this->delivery_challan_model->get_sale_agents();
        $this->load->view('admin/delivery_challan/recurring/list', $data);
    }

    public function table($clientid = '')
    {
        if (!has_permission('delivery_challan', '', 'view')
            && !has_permission('delivery_challan', '', 'view_own')) {
            ajax_access_denied();
        }

        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [], true);

        $this->app->get_table_data('delivery_challans', [
            'clientid' => $clientid,
            'data'     => $data,
        ]);
    }

    public function client_change_data($customer_id, $current_invoice = '')
    {
        if ($this->input->is_ajax_request()) {
            $this->load->model('projects_model');
            $data                     = [];
            $data['billing_shipping'] = $this->clients_model->get_customer_billing_and_shipping_details($customer_id);
            $data['client_currency']  = $this->clients_model->get_customer_default_currency($customer_id);

			$company_state 	  = get_option('company_state');



			//if(!empty($data['billing_shipping']) && isset($data['billing_shipping'][0]['billing_state']) && strtolower($data['billing_shipping'][0]['billing_state']) == strtolower($company_state)){

			if(!empty($data['billing_shipping']) && isset($data['billing_shipping'][0]['shipping_state']) && strtolower($data['billing_shipping'][0]['shipping_state']) == strtolower($company_state)){
				$data['devide_gst'] = "yes";
			}else{
				$data['devide_gst'] = 'no';
			}


            $data['customer_has_projects'] = customer_has_projects($customer_id);
            $data['billable_tasks']        = $this->tasks_model->get_billable_tasks($customer_id);

            if ($current_invoice != '') {
                $this->db->select('status');
                $this->db->where('id', $current_invoice);
                $current_invoice_status = $this->db->get('tbldelivery_challans')->row()->status;
            }

//            $_data['invoices_to_merge'] = !isset($current_invoice_status) || (isset($current_invoice_status) && $current_invoice_status != 5) ? $this->delivery_challan_model->check_for_merge_invoice($customer_id, $current_invoice) : [];

//            $data['merge_info'] = $this->load->view('admin/delivery_challan/merge_invoice', $_data, true);

            $this->load->model('currencies_model');

            $__data['expenses_to_bill'] = !isset($current_invoice_status) || (isset($current_invoice_status) && $current_invoice_status != 5) ? $this->delivery_challan_model->get_expenses_to_bill($customer_id) : [];

            $data['expenses_bill_info'] = $this->load->view('admin/delivery_challan/bill_expenses', $__data, true);
            echo json_encode($data);
        }
    }

    public function update_number_settings($id)
    {
        $response = [
            'success' => false,
            'message' => '',
        ];
        if (has_permission('delivery_challan', '', 'edit')) {
            $affected_rows = 0;

            $this->db->where('id', $id);
            $this->db->update('tbldelivery_challans', [
                'prefix' => $this->input->post('prefix'),
            ]);
            if ($this->db->affected_rows() > 0) {
                $affected_rows++;
            }

            if ($affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = _l('updated_successfully', _l('Delivery Challan'));
            }
        }
        echo json_encode($response);
        die;
    }

    public function validate_invoice_number()
    {
        $isedit          = $this->input->post('isedit');
        $number          = $this->input->post('number');
        $date            = $this->input->post('date');
        $original_number = $this->input->post('original_number');
        $number          = trim($number);
        $number          = ltrim($number, '0');
        if ($isedit == 'true') {
            if ($number == $original_number) {
                echo json_encode(true);
                die;
            }
        }
        if (total_rows('tbldelivery_challans', [
            'YEAR(date)' => date('Y', strtotime(to_sql_date($date))),
            'number' => $number,
        ]) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

    public function add_note($rel_id)
    {
        if ($this->input->post()) {
            $this->misc_model->add_note($this->input->post(), 'deliverychallan', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        $data['notes'] = $this->misc_model->get_notes($id, 'deliverychallan');
        $this->load->view('admin/includes/sales_notes_template', $data);
    }

    public function pause_overdue_reminders($id)
    {
        if (has_permission('delivery_challan', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update('tbldelivery_challans', ['cancel_overdue_reminders' => 1]);
        }
        redirect(admin_url('delivery_challan/list_invoices/' . $id));
    }

    public function resume_overdue_reminders($id)
    {
        if (has_permission('delivery_challan', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update('tbldelivery_challans', ['cancel_overdue_reminders' => 0]);
        }
        redirect(admin_url('delivery_challan/list_invoices/' . $id));
    }

    public function mark_as_invoiced($id)
    {
        if (!has_permission('delivery_challan', '', 'edit') && !has_permission('delivery_challan', '', 'create')) {
            access_denied('invoices');
        }

        $success = $this->delivery_challan_model->mark_as_invoiced($id);

        if ($success) {
            set_alert('success', _l('DC marked as Invoiced successfully'));
        }

        redirect(admin_url('delivery_challan/list_invoices/' . $id));
    }

    public function mark_as_returned($id)
    {
        if (!has_permission('delivery_challan', '', 'edit') && !has_permission('delivery_challan', '', 'create')) {
            access_denied('invoices');
        }

        $success = $this->delivery_challan_model->mark_as_returned($id);

        if ($success) {
            set_alert('success', _l('DC marked as Returned successfully'));
        }

        redirect(admin_url('delivery_challan/list_invoices/' . $id));
    }

    public function mark_as_cancelled($id)
    {
        if (!has_permission('delivery_challan', '', 'edit') && !has_permission('delivery_challan', '', 'create')) {
            access_denied('invoices');
        }

        $success = $this->delivery_challan_model->mark_as_cancelled($id);

        if ($success) {
            set_alert('success', _l('DC marked as cancelled successfully'));
        }

        redirect(admin_url('delivery_challan/list_invoices/' . $id));
    }

    public function unmark_as_cancelled($id)
    {
        if (!has_permission('delivery_challan', '', 'edit') && !has_permission('delivery_challan', '', 'create')) {
            access_denied('delivery_challan');
        }
        $success = $this->delivery_challan_model->unmark_as_cancelled($id);
        if ($success) {
            set_alert('success', _l('DC marked as cancelled successfully'));
        }
        redirect(admin_url('delivery_challan/list_invoices/' . $id));
    }

    public function copy($id)
    {
        if (!$id) {
            redirect(admin_url('invoices'));
        }
        if (!has_permission('delivery_challan', '', 'create')) {
            access_denied('delivery_challan');
        }
        $new_id = $this->delivery_challan_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('invoice_copy_success'));
            redirect(admin_url('delivery_challan/invoice/' . $new_id));
        } else {
            set_alert('success', _l('invoice_copy_fail'));
        }
        redirect(admin_url('delivery_challan/invoice/' . $id));
    }

    public function get_merge_data($id)
    {
        $invoice = $this->delivery_challan_model->get($id);
        $cf      = get_custom_fields('items');

        $i = 0;

        foreach ($invoice->items as $item) {
            $invoice->items[$i]['taxname']          = get_invoice_item_taxes($item['id']);
            $invoice->items[$i]['long_description'] = clear_textarea_breaks($item['long_description']);
            $this->db->where('item_id', $item['id']);
            $rel              = $this->db->get('tblitemsrelated')->result_array();
            $item_related_val = '';
            $rel_type         = '';
            foreach ($rel as $item_related) {
                $rel_type = $item_related['rel_type'];
                $item_related_val .= $item_related['rel_id'] . ',';
            }
            if ($item_related_val != '') {
                $item_related_val = substr($item_related_val, 0, -1);
            }
            $invoice->items[$i]['item_related_formatted_for_input'] = $item_related_val;
            $invoice->items[$i]['rel_type']                         = $rel_type;

            $invoice->items[$i]['custom_fields'] = [];

            foreach ($cf as $custom_field) {
                $custom_field['value']                 = get_custom_field_value($item['id'], $custom_field['id'], 'items');
                $invoice->items[$i]['custom_fields'][] = $custom_field;
            }
            $i++;
        }
        echo json_encode($invoice);
    }

    public function get_bill_expense_data($id)
    {
        $this->load->model('expenses_model');
        $expense = $this->expenses_model->get($id);

        $expense->qty              = 1;
        $expense->long_description = clear_textarea_breaks($expense->description);
        $expense->description      = $expense->name;
        $expense->rate             = $expense->amount;
        if ($expense->tax != 0) {
            $expense->taxname = [];
            array_push($expense->taxname, $expense->tax_name . '|' . $expense->taxrate);
        }
        if ($expense->tax2 != 0) {
            array_push($expense->taxname, $expense->tax_name2 . '|' . $expense->taxrate2);
        }
        echo json_encode($expense);
    }

    /* Add new invoice or update existing */
    public function invoice($id = '')
    {
        if ($this->input->post()) {
            $invoice_data = $this->input->post();
			// echo "<pre>";
			// print_r($invoice_data);die;
			$invoice_data['service_charge_tax_rate']	=	SERVICE_CHARGES_TAX_RATE;

            if ($id == '') {
                if (!has_permission('delivery_challan', '', 'create')) {
                    access_denied('delivery_challan');
                }
				//print_r($invoice_data);die;
                $id = $this->delivery_challan_model->add($invoice_data);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('Delivery Challan')));
                    redirect(admin_url('delivery_challan/list_invoices/' . $id));
                }
            } else {
                if (!has_permission('delivery_challan', '', 'edit')) {
                    access_denied('delivery_challan');
                }
                $success = $this->delivery_challan_model->update($invoice_data, $id);
                if ($success) {
                    set_alert('success', _l('updated_successfully', _l('Delivery Challan')));
                }
                redirect(admin_url('delivery_challan/list_invoices/' . $id));
            }
        }

        if ($id == '') {
            $title                  = _l('Create New Delivery Challan');
            $data['billable_tasks'] = [];
        } else {
            $invoice = $this->delivery_challan_model->get($id);

//            $data['invoices_to_merge'] = $this->delivery_challan_model->check_for_merge_invoice($invoice->clientid, $invoice->id);
            $data['expenses_to_bill']  = $this->delivery_challan_model->get_expenses_to_bill($invoice->clientid);

            $data['invoice']        = $invoice;
            $data['edit']           = true;
            $data['billable_tasks'] = $this->tasks_model->get_billable_tasks($invoice->clientid, !empty($invoice->project_id) ? $invoice->project_id : '');

            $title = _l('edit', _l('delivery challan')) . ' - ' . format_delivery_challan_number($invoice->id);
        }

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        $this->load->model('payment_modes_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);

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

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['staff']     = $this->staff_model->get('', ['active' => 1]);
        $data['title']     = $title;
        $data['bodyclass'] = 'delivery_challan';
        $this->load->view('admin/delivery_challan/invoice', $data);
    }

    public function stock_out_list($id = '')
    {
        if (!has_permission('stock_out', '', 'view')
            && !has_permission('stock_out', '', 'view_own')) {
            access_denied('stock_out');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('stock_out_extra_list');
        }
        $data['title'] = _l('Stock Out list');
        $this->load->view('admin/delivery_challan/stock_list', $data);
    }

    public function stock_out($invoice_id)
    {
        if (!has_permission('stock_out', '', 'create')) {
            access_denied('stock_out');
        }
        $invoice = $this->delivery_challan_model->get($invoice_id);
        if ($this->input->post()) {
            $products = [];
            $i = isset($_POST['description']) ? sizeof($_POST['description']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $description = $_POST['description'][$r];
                $quantity = $_POST['quantity'][$r];
                $warehouseId = $_POST['warehouse_id'][$r];
                $products[] = array(
                    'description' => $description,
                    'item_id' => $_POST['item_id'][$r],
                    'warehouse_id' => $warehouseId,
                    'invoice_id' => $invoice_id,
                    'quantity' => $quantity
                );
            }
            $newDate = $products;
            $success = $this->delivery_challan_model->updateQuantityBalance($newDate);
            $this->db->where('id', $invoice_id);
            $this->db->update('tbldelivery_challans', ['is_stock_out'=>2]);
            set_alert('success', 'Stock Out successfully.');
            redirect(admin_url('delivery_challan/stock_out_list/'));

        }
        $this->load->model('warehouses_model');
        $data['warehouses'] = $this->warehouses_model->getAllWarehouses();
        $data['adjustment'] = $invoice;
        $this->load->view('admin/delivery_challan/stock_out', $data);
    }

    /* Get all invoice data used when user click on invoiec number in a datatable left side*/
    public function get_invoice_data_ajax($id)
    {
        if (!has_permission('delivery_challan', '', 'view') && !has_permission('delivery_challan', '', 'view_own')) {
            echo _l('access_denied');
            die;
        }

        if (!$id) {
            die(_l('Delivery challan not found'));
        }

        $invoice = $this->delivery_challan_model->get($id);

        if (!$invoice) {
            echo _l('Delivery challan not found');
            die;
        }

        $invoice->date    = _d($invoice->date);
        $invoice->duedate = _d($invoice->duedate);
        $template_name    = 'invoice-send-to-client';
        if ($invoice->sent == 1) {
            $template_name = 'invoice-already-send';
        }

        $template_name = do_action('after_invoice_sent_template_statement', $template_name);

        $contact = $this->clients_model->get_contact(get_primary_contact_user_id($invoice->clientid));
        $email   = '';
        if ($contact) {
            $email = $contact->email;
        }

        $data['template'] = get_email_template_for_sending($template_name, $email);

//        $data['invoices_to_merge'] = $this->delivery_challan_model->check_for_merge_invoice($invoice->clientid, $id);
        $data['template_name']     = $template_name;
        $this->db->where('slug', $template_name);
        $this->db->where('language', 'english');
        $template_result = $this->db->get('tblemailtemplates')->row();

        $data['template_system_name'] = $template_result->name;
        $data['template_id']          = $template_result->emailtemplateid;

        $data['template_disabled'] = false;
        if (total_rows('tblemailtemplates', ['slug' => $data['template_name'], 'active' => 0]) > 0) {
            $data['template_disabled'] = true;
        }
        // Check for recorded payments
        $this->load->model('payments_model');
        $data['members']                    = $this->staff_model->get('', ['active' => 1]);
        $data['payments']                   = $this->payments_model->get_invoice_payments($id);
        $data['activity']                   = $this->delivery_challan_model->get_invoice_activity($id);
        $data['totalNotes']                 = total_rows('tblnotes', ['rel_id' => $id, 'rel_type' => 'deliverychallan']);
        $data['invoice_recurring_invoices'] = $this->delivery_challan_model->get_invoice_recurring_invoices($id);

        $data['applied_credits'] = $this->credit_notes_model->get_applied_invoice_credits($id);
        // This data is used only when credit can be applied to invoice
        if (credits_can_be_applied_to_invoice($invoice->status)) {
            $data['credits_available'] = $this->credit_notes_model->total_remaining_credits_by_customer($invoice->clientid);

            if ($data['credits_available'] > 0) {
                $data['open_credits'] = $this->credit_notes_model->get_open_credits($invoice->clientid);
            }

            $customer_currency = $this->clients_model->get_customer_default_currency($invoice->clientid);
            $this->load->model('currencies_model');

            if ($customer_currency != 0) {
                $data['customer_currency'] = $this->currencies_model->get($customer_currency);
            } else {
                $data['customer_currency'] = $this->currencies_model->get_base_currency();
            }
        }
		$data['invoices_delivery_status']   = $this->delivery_module_model->get_invoice_delivery_status($id);

		$data['invoices_delivery_modules']  = $this->delivery_module_model->get_delivery_modules_by_invoice_id($id);
        $data['invoice'] = $invoice;
        $this->load->view('admin/delivery_challan/invoice_preview_template', $data);
    }

    public function apply_credits($invoice_id)
    {
        $total_credits_applied = 0;
        foreach ($this->input->post('amount') as $credit_id => $amount) {
            $success = $this->credit_notes_model->apply_credits($credit_id, [
            'invoice_id' => $invoice_id,
            'amount'     => $amount,
        ]);
            if ($success) {
                $total_credits_applied++;
            }
        }

        if ($total_credits_applied > 0) {
            update_invoice_status($invoice_id, true);
            set_alert('success', _l('invoice_credits_applied'));
        }
        redirect(admin_url('delivery_challan/list_invoices/' . $invoice_id));
    }

    public function get_invoices_total()
    {
        if ($this->input->post()) {
            load_invoices_total_template();
        }
    }

    /* Record new inoice payment view */
    public function record_invoice_payment_ajax($id)
    {
        $this->load->model('payment_modes_model');
        $this->load->model('payments_model');
        $data['payment_modes'] = $this->payment_modes_model->get('', [
            'expenses_only !=' => 1,
        ]);
        $data['invoice']  = $invoice  = $this->delivery_challan_model->get($id);
        $data['payments'] = $this->payments_model->get_invoice_payments($id);
        $this->load->view('admin/delivery_challan/record_payment_template', $data);
    }

    /* This is where invoice payment record $_POST data is send */
    public function record_payment()
    {
        if (!has_permission('payments', '', 'create')) {
            access_denied('Record Payment');
        }
        if ($this->input->post()) {
			//print_r($this->input->post());
			//// print_r($_FILES);
			//echo "in record_payment()";die;
            $this->load->model('payments_model');
			$post_data	=	$this->input->post();
			unset($post_data['temp_file_id']);
            $id = $this->payments_model->process_payment($post_data, '');

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
               redirect(admin_url('payments/payment/' . $id));

            } else {
                set_alert('danger', _l('invoice_payment_record_failed'));
            }


            redirect(admin_url('delivery_challan/list_invoices/' . $this->input->post('invoiceid')));


        }
    }

    /* Send invoiece to email */
    public function send_to_email($id)
    {
        if (!has_permission('delivery_challan', '', 'view') && !has_permission('delivery_challan', '', 'view_own') && $canView == false) {
            access_denied('delivery_challan');
        }

        try {
            $success = $this->delivery_challan_model->send_invoice_to_client($id, '', $this->input->post('attach_pdf'), $this->input->post('cc'));
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
            set_alert('success', _l('invoice_sent_to_client_success'));
        } else {
            set_alert('danger', _l('invoice_sent_to_client_fail'));
        }
        redirect(admin_url('delivery_challan/list_invoices/' . $id));
    }

    /* Delete invoice payment*/
    public function delete_payment($id, $invoiceid)
    {
        if (!has_permission('payments', '', 'delete')) {
            access_denied('payments');
        }
        $this->load->model('payments_model');
        if (!$id) {
            redirect(admin_url('payments'));
        }
        $response = $this->payments_model->delete($id);
        if ($response == true) {
            set_alert('success', _l('deleted', _l('payment')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('payment_lowercase')));
        }
        redirect(admin_url('delivery_challan/list_invoices/' . $invoiceid));
    }

    /* Delete invoice */
    public function delete($id)
    {
        if (!has_permission('delivery_challan', '', 'delete')) {
            access_denied('delivery_challan');
        }
        if (!$id) {
            redirect(admin_url('delivery_challan/list_invoices'));
        }
        $success = $this->delivery_challan_model->delete($id);

        if ($success) {
            set_alert('success', _l('deleted', _l('Delivery Challan')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('invoice_lowercase')));
        }
        if (strpos($_SERVER['HTTP_REFERER'], 'list_invoices') !== false) {
            redirect(admin_url('delivery_challan/list_invoices'));
        } else {
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function delete_attachment($id)
    {
        $file = $this->misc_model->get_file($id);
        if ($file->staffid == get_staff_user_id() || is_admin()) {
            echo $this->delivery_challan_model->delete_attachment($id);
        } else {
            header('HTTP/1.0 400 Bad error');
            echo _l('access_denied');
            die;
        }
    }

    /* Will send overdue notice to client */
    public function send_overdue_notice($id)
    {
        if (!has_permission('delivery_challan', '', 'view') && !has_permission('delivery_challan', '', 'view_own') && $canView == false) {
            access_denied('delivery_challan');
        }

        $send = $this->delivery_challan_model->send_invoice_overdue_notice($id);
        if ($send) {
            set_alert('success', _l('invoice_overdue_reminder_sent'));
        } else {
            set_alert('warning', _l('invoice_reminder_send_problem'));
        }
        redirect(admin_url('delivery_challan/list_invoices/' . $id));
    }

    public function stock_out_pdf($id)
    {
        if (!$id) {
            redirect(admin_url('purchases/stock_in_list'));
        }

        $invoice        = $this->delivery_challan_model->get($id);
        $invoice        = do_action('before_admin_view_invoice_pdf', $invoice);
        $invoice_number = format_delivery_challan_number($invoice->id);

        try {
            $pdf = stock_out_extra_pdf($invoice);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }
        $pdf->Output(mb_strtoupper(slug_it($invoice_number)) . '.pdf', 'I');
    }

    /* Generates invoice PDF and senting to email of $send_to_email = true is passed */
    public function pdf($id)
    {
        if (!$id) {
            redirect(admin_url('delivery_challan/list_invoices'));
        }
        if (!has_permission('delivery_challan', '', 'view') && !has_permission('delivery_challan', '', 'view_own') && $canView == false) {
            access_denied('delivery_challan');
        }

        $invoice        = $this->delivery_challan_model->get($id);
        $invoice        = do_action('before_admin_view_invoice_pdf', $invoice);
        $invoice_number = format_delivery_challan_number($invoice->id);

        try {
            $pdf = delivery_challan_pdf($invoice);
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




        $pdf->Output(mb_strtoupper(slug_it($invoice_number)) . '.pdf', $type);
    }

    public function pipdf($id)
    {
        if (!$id) {
            redirect(admin_url('delivery_challan/list_invoices'));
        }
        if (!has_permission('delivery_challan', '', 'view') && !has_permission('delivery_challan', '', 'view_own') && $canView == false) {
            access_denied('delivery_challan');
        }

        $invoice        = $this->delivery_challan_model->get($id);
        $invoice        = do_action('before_admin_view_invoice_pdf', $invoice);
        $invoice_number = format_delivery_challan_number($invoice->id);

        try {
            $pdf = delivery_challan_pdf($invoice);
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




        $pdf->Output(mb_strtoupper(slug_it($invoice_number)) . '.pdf', $type);
    }

    public function mark_as_sent($id)
    {
        if (!$id) {
            redirect(admin_url('delivery_challan/list_invoices'));
        }
        $success = $this->delivery_challan_model->set_invoice_sent($id, true);
        if ($success) {
            set_alert('success', _l('invoice_marked_as_sent'));
        } else {
            set_alert('warning', _l('invoice_marked_as_sent_failed'));
        }
        redirect(admin_url('delivery_challan/list_invoices/' . $id));
    }

    public function get_due_date()
    {
        if ($this->input->post()) {
            $date    = $this->input->post('date');
            $duedate = '';
            if (get_option('invoice_due_after') != 0) {
                $date    = to_sql_date($date);
                $d       = date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime($date)));
                $duedate = _d($d);
                echo $duedate;
            }
        }
    }

	 public function delivery_details($invoice_id = 0){

		 $invoice			= $this->delivery_challan_model->get($invoice_id);
		 $delivery_detail	= $this->delivery_module_model->get_delivery_modules_by_invoice_id($invoice_id);
		 $oldStatus = array_values(array_unique(array_pluck($delivery_detail,'status_id')));
		 $customer_id		= $invoice->clientid;
		 $data = array();


			if ($this->input->post()) {
				$data = $this->input->post();
				$data['invoice_id']		=	$invoice_id;
				$data['customer_id']	=	$customer_id;

				// if(!empty($delivery_detail)){
					// $delivery_detail_id = $delivery_detail->id;
					// $success = $this->delivery_module_model->add($data);
					//$success = $this->delivery_module_model->add($data, $delivery_detail_id);
					// if ($success) {
						// set_alert('success', _l('updated_successfully', _l('delivery_detail')));
					// }
					// echo json_encode([
							// 'url'       => admin_url('invoices/list_invoices#/' . $invoice_id),
							// 'delivery_moduleid' => $delivery_detail_id,
							// 'invoice_id' => $invoice_id
						// ]);
					// die;

				// }else{
					$id = $this->delivery_module_model->add($data);
					if ($id) {
						set_alert('success', _l('added_successfully', _l('delivery_detail')));
						echo json_encode([
							'url'       => admin_url('delivery_challan/list_invoices#/' . $invoice_id),
							'delivery_moduleid' => $id,
							'invoice_id' => $invoice_id
						]);
						die;
					}
					echo json_encode([
						'url' => admin_url('delivery_challan/list_invoices#/' . $invoice_id),
					]);
					die;
				//}
			}


            // if ($id == '') {
                // if (!has_permission('expenses', '', 'create')) {
                    // set_alert('danger', _l('access_denied'));
                    // echo json_encode([
                        // 'url' => admin_url('expenses/expense'),
                    // ]);
                    // die;
                // }
                // $id = $this->expenses_model->add($this->input->post());
                // if ($id) {
                    // set_alert('success', _l('added_successfully', _l('expense')));
                    // echo json_encode([
                        // 'url'       => admin_url('expenses/list_expenses/' . $id),
                        // 'expenseid' => $id,
                    // ]);
                    // die;
                // }
                // echo json_encode([
                    // 'url' => admin_url('expenses/expense'),
                // ]);
                // die;
            // }
            // if (!has_permission('expenses', '', 'edit')) {
                // set_alert('danger', _l('access_denied'));
                // echo json_encode([
                        // 'url' => admin_url('expenses/expense/' . $id),
                    // ]);
                // die;
            // }
            // $success = $this->expenses_model->update($this->input->post(), $id);
            // if ($success) {
                // set_alert('success', _l('updated_successfully', _l('expense')));
            // }
            // echo json_encode([
                    // 'url'       => admin_url('expenses/list_expenses/' . $id),
                    // 'expenseid' => $id,
                // ]);
            // die;
        // }


		// if(!empty($delivery_detail)){
			 $data['title']      		=  _l('add_deliverydetails');
		 //}
		/*  else{
			 $data['title']      		=  _l('add_deliverydetails');
		 } */





		 /* $data['billing_shipping'] 	= $this->clients_model->get_customer_billing_and_shipping_details($customer_id); */
		 $data['customer_name'] 	= $this->clients_model->get_customer_name($customer_id);
		 $data['staff']    	 	 	= $this->staff_model->get('', ['active' => 1]);
		 $data['invoice']    	 	= $invoice;
		// $data['delivery_detail']   = $delivery_detail;
		 //print_r($delivery_detail);die;
         $statuss= $this->delivery_module_status_model->get();
         $newStatus = [];
         foreach ($statuss as $status){
             if(!in_array($status['id'],$oldStatus) ){
                 $newStatus[] = $status;
             }
         }
		 $data['status'] 	 = $newStatus;
		 $this->load->view('admin/delivery_challan/delivery_details', $data);
	 }

	 public function deliveries_old($invoice_id = 0){

		$data['delivery_modules']  = $this->delivery_module_model->get_all_delivery_modules();

		$this->load->view('admin/delivery_challan/deliveries', $data);
	 }



	 /* List all invoice deliveries */
    public function deliveries()
    {
        if (!has_permission('deliveries', '', 'view')
            && !has_permission('deliveries', '', 'view_own')) {
            access_denied('invoices');
        }
        $data['title'] = _l('deliveries');
        $this->load->view('admin/delivery_challan/manage_deliveries', $data);
    }

	 public function deliveries_table($clientid = '')
    {
		//echo "test";die;
        //$this->app->get_table_data('delivery_modules', []);

		$this->app->get_table_data('delivery_modules', [
            'clientid' => $clientid,
        ]);
    }



	public function add_delivery_module_attachment($delivery_moduleid = 0, $invoice_id = 0){
		handle_delivery_module_attachments($delivery_moduleid);

		echo json_encode([
            'url' => admin_url('delivery_challan/list_invoices/' . $invoice_id),
        ]);


	}

	public function delete_delivery_module_attachment($id,$invoice_id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'delivery_modules');
        $file = $this->db->get('tblfiles')->row();

        if ($file->staffid == get_staff_user_id() || is_admin()) {
            $success = $this->delivery_module_model->delete_delivery_module_attachment($id);
            if ($success) {
                set_alert('success', _l('deleted', _l('delivery_attachment')));
            } else {
                set_alert('warning', _l('problem_deleting', _l('delivery_attachment')));
            }
           redirect(admin_url('delivery_challan/delivery_details/' . $invoice_id));
        } else {
            access_denied('expenses');
        }
    }


	public function add_invoice_payment_attachment(){
		$attachment = handle_temp_invoice_payment_attachments();
		//print_r($attachment);
		$id	=	$attachment['id'];

		$data = '<div class="row">';

		$attachment_url = site_url('download/file/invoice_payment_temp/'.$id);

		$data .= '<div class="display-block lead-attachment-wrapper">';
		$data .= '<div class="col-md-10">';
		$data .= '<div class="pull-left"><i class="'.get_mime_class($attachment['filetype']).'"></i></div>';
		$data .= '<a href="'.$attachment_url.'" target="_blank">'.$attachment['file_name'].'</a>';
		$data .= '<p class="text-muted">'.$attachment["filetype"].'</p>';
		$data .= '</div>';
		$data .= '<div class="col-md-2 text-right">';
		if($attachment['staffid'] == get_staff_user_id() || is_admin()){
			$data .= '<a href="#" class="text-danger" onclick="delete_temp_payment_attachment(this,'.$attachment['id'].'); return false;"><i class="fa fa fa-times"></i></a>';
		}
		$data .= '</div>';
		$data .= '<div class="clearfix"></div><hr/>';
		$data .= '</div>';
		$data .= '</div>';


		echo json_encode([
            //'url' => admin_url('payments/payment/' . $invoice_payment_record_id),
			'id' => $id,
			'data' => $data
        ]);
	}


	 public function delete_temp_payment_attachment($id)
    {
        if (!is_staff_member() || !is_admin()) {
            $this->access_denied_ajax();
        }
        echo json_encode([
            'success' => $this->misc_model->delete_temp_payment_attachment($id),
        ]);
    }

	public function check_apply_diffrent_gst($customer_id){
		$billing_shipping = $this->clients_model->get_customer_billing_and_shipping_details($customer_id);
		echo $company_state 	  = get_option('company_state');

		pr($billing_shipping);die;

	}

    /* Get item by id / ajax */
    public function get_item_by_id($id)
    {
        if ($this->input->is_ajax_request()) {
            $this->db->select('tblitems_in.*');
            $this->db->from('tblitems_in');
//            $this->db->join('tblitems', 'tblitems.description = tblitems_in.description', 'left');
            $this->db->where('tblitems_in.id', $id);
            $this->db->where('tblitems_in.rel_type', 'deliverychallan');
            $item = $this->db->get()->row();
            $item->custom_fields_html = render_custom_fields('items', $id, [], ['items_pr' => true]);
            if(isset($item->rel_id) && $item->rel_id != 0){
                $this->db->select('tblitems.*');
                $this->db->from('tblitems');
                $this->db->where('tblitems.description', $item->description);
                $mainItem = $this->db->get()->row();
                $item->group_id = $mainItem->group_id;
                $item->invoice_no_full = format_invoice_number($item->rel_id);
            }
            echo json_encode($item);
        }
    }

    public function get_latest_number(){
        $next_estimate_number = get_option('next_delivery_challan_number');
        $format = get_option('delivery_challan_number_format');

        if ($format == 1) {
            $__number = $next_estimate_number;
            if(isset($estimate)){
                $__number = $estimate->number;
            }
        } else if($format == 2) {
            if(isset($estimate)){
                $__number = $estimate->number;
            } else {
                $__number = $next_estimate_number;
            }
        } else if($format == 3) {
            if(isset($estimate)){
                $__number = $estimate->number;
            } else {
                $__number = $next_estimate_number;
            }
        } else if($format == 4) {
            if(isset($estimate)){
                $__number = $estimate->number;
            } else {
                $__number = $next_estimate_number;
            }
        }
        $_estimate_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
        $response['success'] = true;
        $response['number'] = $_estimate_number;
        echo json_encode($response);
        die;
    }

}