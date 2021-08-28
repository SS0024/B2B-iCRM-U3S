<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Purchaseorder_payments_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('purchaseorder_model');
    }

    /**
     * Get all invoice payments
     * @param  mixed $purchaseorder_id purchaseorder_id
     * @return array
     */
    public function get_invoice_payments($purchaseorder_id)
    {
        $this->db->select('*,tblpurchaseorderpaymentrecords.id as paymentid');
        $this->db->join('tblinvoicepaymentsmodes', 'tblinvoicepaymentsmodes.id = tblpurchaseorderpaymentrecords.paymentmode', 'left');
        $this->db->order_by('tblpurchaseorderpaymentrecords.id', 'asc');
        $this->db->where('purchaseorder_id', $purchaseorder_id);
        // $payments = $this->db->get('tblpurchaseorderpaymentrecords')->result_array();
        $query = $this->db->get('tblpurchaseorderpaymentrecords');
        $payments = array();
        if($query !== FALSE && $query->num_rows() > 0){
            foreach ($query->result_array() as $row) {
                $payments[] = $row;
            }
        }
       
        // Since version 1.0.1
        $this->load->model('payment_modes_model');
        $online_modes = $this->payment_modes_model->get_online_payment_modes(true);
        $i            = 0;
        foreach ($payments as $payment) {
            if (is_null($payment['id'])) {
                foreach ($online_modes as $online_mode) {
                    if ($payment['paymentmode'] == $online_mode['id']) {
                        $payments[$i]['id']   = $online_mode['id'];
                        $payments[$i]['name'] = $online_mode['name'];
                    }
                }
            }
            $i++;
        }

        return $payments;
    }

    /**
     * Process invoice payment offline or online
     * @since  Version 1.0.1
     * @param  array $data $_POST data
     * @return boolean
     */
    public function process_payment($data, $purchaseorder_id = '')
    {
        // Offline payment mode from the admin side
        if (is_numeric($data['paymentmode'])) {
            if (is_staff_logged_in()) {
                $id = $this->add($data);

                return $id;
            }

            return false;

        // Is online payment mode request by client or staff
        } elseif (!is_numeric($data['paymentmode']) && !empty($data['paymentmode'])) {
            // This request will come from admin area only
            // If admin clicked the button that dont want to pay the invoice from the getaways only want
            if (is_staff_logged_in() && has_permission('payments', '', 'create')) {
                if (isset($data['do_not_redirect'])) {
                    $id = $this->add($data);

                    return $id;
                }
            }
            if (!is_numeric($purchaseorder_id)) {
                if (!isset($data['purchaseorder_id'])) {
                    die('No invoice specified');
                }
                $purchaseorder_id = $data['purchaseorder_id'];
            }

            if (isset($data['do_not_send_email_template'])) {
                unset($data['do_not_send_email_template']);
                $this->session->set_userdata([
                    'do_not_send_email_template' => true,
                ]);
            }

            $invoice = $this->purchaseorder_model->get($purchaseorder_id);
            // Check if request coming from admin area and the user added note so we can insert the note also when the payment is recorded
            if (isset($data['note']) && $data['note'] != '') {
                $this->session->set_userdata([
                    'payment_admin_note' => $data['note'],
                ]);
            }

            if (get_option('allow_payment_amount_to_be_modified') == 0) {
                $data['amount'] = get_service_invoice_total_left_to_pay($purchaseorder_id, $invoice->total);
            }

            $data['purchaseorder_id'] = $purchaseorder_id;
            $data['invoice']   = $invoice;
            $data              = do_action('before_process_gateway_func', $data);

            $cf = $data['paymentmode'] . '_gateway';

			$this->$cf->process_payment($data);
        }

        return false;
    }

    /**
     * Record new payment
     * @param array $data payment data
     * @return boolean
     */
    public function add($data, $subscription = false)
    {
        // Check if field do not redirect to payment processor is set so we can unset from the database
        if (isset($data['do_not_redirect'])) {
            unset($data['do_not_redirect']);
        }

        if (is_staff_logged_in()) {
            if (isset($data['date'])) {
                $data['date'] = to_sql_date($data['date']);
            } else {
                $data['date'] = date('Y-m-d H:i:s');
            }
            if (isset($data['note'])) {
                $data['note'] = nl2br($data['note']);
            } elseif ($this->session->has_userdata('payment_admin_note')) {
                $data['note'] = nl2br($this->session->userdata('payment_admin_note'));
                $this->session->unset_userdata('payment_admin_note');
            }
        } else {
            $data['date'] = date('Y-m-d H:i:s');
        }

        $data['daterecorded'] = date('Y-m-d H:i:s');
        $data                 = do_action('before_payment_recorded', $data);

        $this->db->insert('tblpurchaseorderpaymentrecords', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            $invoice      = $this->purchaseorder_model->get($data['purchaseorder_id']);

            $activity_lang_key = 'invoice_activity_payment_made_by_staff';

            $this->purchaseorder_model->log_estimate_activity($data['purchaseorder_id'], $activity_lang_key, !is_staff_logged_in() ? true : false, serialize([
                format_money($data['amount'], $invoice->symbol),
                '<a href="' . admin_url('service_payments/payment/' . $insert_id) . '" target="_blank">#' . $insert_id . '</a>',
            ]));

            logActivity('Payment Recorded [ID:' . $insert_id . ', Invoice Number: ' . format_purchaseorder_number($invoice->id) . ', Total: ' . format_money($data['amount'], $invoice->symbol) . ']');

            do_action('after_payment_added', $insert_id);

            return $insert_id;
        }

        return false;
    }

    /**
     * Get payment by ID
     * @param  mixed $id payment id
     * @return object
     */
    public function get($id)
    {
        $this->db->select('*,tblpurchaseorderpaymentrecords.id as paymentid');
        $this->db->join('tblinvoicepaymentsmodes', 'tblinvoicepaymentsmodes.id = tblpurchaseorderpaymentrecords.paymentmode', 'left');
        $this->db->order_by('tblpurchaseorderpaymentrecords.id', 'asc');
        $this->db->where('tblpurchaseorderpaymentrecords.id', $id);
        $payment = $this->db->get('tblpurchaseorderpaymentrecords')->row();
        if (!$payment) {
            return false;
        }
        // Since version 1.0.1
        $this->load->model('payment_modes_model');
        $online_modes = $this->payment_modes_model->get_online_payment_modes(true);
        if (is_null($payment->id)) {
            foreach ($online_modes as $online_mode) {
                if ($payment->paymentmode == $online_mode['id']) {
                    $payment->name = $online_mode['name'];
                }
            }
        }

        return $payment;
    }

    /**
     * Update payment
     * @param  array $data payment data
     * @param  mixed $id   paymentid
     * @return boolean
     */
    public function update($data, $id)
    {
        $payment = $this->get($id);

        $data['date'] = to_sql_date($data['date']);
        $data['note'] = nl2br($data['note']);
        $_data        = do_action('before_payment_updated', [
            'data' => $data,
            'id'   => $id,
        ]);
        $data = $_data['data'];
        $this->db->where('id', $id);
        $this->db->update('tblpurchaseorderpaymentrecords', $data);
        if ($this->db->affected_rows() > 0) {
            if ($data['amount'] != $payment->amount) {
//                update_service_invoice_status($payment->purchaseorder_id);
            }
            logActivity('Payment Updated [Number:' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * Delete payment from database
     * @param  mixed $id paymentid
     * @return boolean
     */
    public function delete($id)
    {
        $current         = $this->get($id);
        $current_invoice = $this->purchaseorder_model->get($current->purchaseorder_id);
        $purchaseorder_id       = $current->purchaseorder_id;
        do_action('before_payment_deleted', [
            'paymentid' => $id,
            'purchaseorder_id' => $purchaseorder_id,
        ]);
        $this->db->where('id', $id);
        $this->db->delete('tblpurchaseorderpaymentrecords');
        if ($this->db->affected_rows() > 0) {
//            update_service_invoice_status($purchaseorder_id);
            $this->purchaseorder_model->log_invoice_activity($purchaseorder_id, 'invoice_activity_payment_deleted', false, serialize([
                $current->paymentid,
                format_money($current->amount, $current_invoice->symbol),
            ]));
            logActivity('Payment Deleted [ID:' . $id . ', Invoice Number: ' . format_purchaseorder_number($current->id) . ']');

            return true;
        }

        return false;
    }
}
