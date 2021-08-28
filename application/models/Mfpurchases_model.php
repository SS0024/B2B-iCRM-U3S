<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Mfpurchases_model extends CRM_Model
{
//    private $shipping_fields = ['shipping_street', 'shipping_city', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country'];

    private $statuses = [1, 2, 3, 4, 5, 6];

    public function __construct()
    {
        parent::__construct();
    }

    public function get_statuses()
    {
        return $this->statuses;
    }

    public function get_sale_agents()
    {
        return $this->db->query("SELECT DISTINCT(sale_agent) as sale_agent, CONCAT(firstname, ' ', lastname) as full_name FROM tblinvoices JOIN tblstaff ON tblstaff.staffid=tblinvoices.sale_agent WHERE sale_agent != 0")->result_array();
    }

    public function unmark_as_cancelled($id)
    {
        $this->db->where('id', $id);
        $this->db->update('tblinvoices', [
            'status' => 'ordered',
        ]);
        if ($this->db->affected_rows() > 0) {
            $this->log_invoice_activity($id, 'invoice_activity_unmarked_as_cancelled');

            return true;
        }

        return false;
    }

    /**
     * Log invoice activity to database
     * @param mixed $id invoiceid
     * @param string $description activity description
     */
    public function log_mfpurchases_invoice_activity($id, $description = '', $client = false, $additional_data = '')
    {
        if (DEFINED('CRON')) {
            $staffid = '[CRON]';
            $full_name = '[CRON]';
        } else if (defined('STRIPE_SUBSCRIPTION_INVOICE')) {
            $staffid = null;
            $full_name = '[Stripe]';
        } elseif ($client == true) {
            $staffid = null;
            $full_name = '';
        } else {
            $staffid = get_staff_user_id();
            $full_name = get_staff_full_name(get_staff_user_id());
        }
        $this->db->insert('tblmfpurchaseactivity', [
            'description' => $description,
            'date' => date('Y-m-d H:i:s'),
            'rel_id' => $id,
            'rel_type' => 'purchase',
            'staffid' => $staffid,
            'full_name' => $full_name,
            'additional_data' => $additional_data,
        ]);
    }

    /**
     * Get this invoice generated recurring invoices
     * @param mixed $id main invoice id
     * @return array
     * @since  Version 1.0.1
     */
    public function get_invoice_recurring_invoices($id)
    {
        $this->db->select('id');
        $this->db->where('is_recurring_from', $id);
        $invoices = $this->db->get('tblinvoices')->result_array();
        $recurring_invoices = [];
        foreach ($invoices as $invoice) {
            $recurring_invoices[] = $this->get($invoice['id']);
        }

        return $recurring_invoices;
    }

    /**
     * Get invoice by id
     * @param mixed $id
     * @return array
     */
    public function get($id = '', $where = [])
    {
        $this->db->select('*, tblmfpurchases.id as id');
        $this->db->from('tblmfpurchases');
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where('tblmfpurchases' . '.id', $id);
            $invoice = $this->db->get()->row();
            if ($invoice) {
                $this->db->select();
                $this->db->from('tblmfpurchaseitems');
                $this->db->where('purchase_id', $id);
                $invoice->items = $this->db->get()->result_array();
            }
            return $invoice;
        }

        $this->db->order_by('YEAR(date)', 'desc');

        return $this->db->get()->result_array();
    }

    /**
     * Get mfpurcheses selected item by id
     * @param mixed $id
     * @return array
     */
    public function item_get($id)
    {

                $this->db->select();
                $this->db->from('tblmfpurchaseitems');
                $this->db->where('purchase_id', $id);
                $items = $this->db->get()->result_array();

            return $items;

    }
    /**
     * Get mfpurcheses selected suppler by id
     * @param mixed $id
     * @return array
     */
    public function suppliers_get($id)
    {

                $this->db->select();
                $this->db->from('tblmfitemssupplier');
                $this->db->where('rel_id', $id);
                $suppliers = $this->db->get()->result_array();

            return $suppliers;

    }
    /**
     * Get supplier data
     * @param mixed $id
     * @return array
     */
    public function suppliers_data_get()
    {

                $this->db->select();
                $this->db->from('tblsuppliers');
                $suppliers_data = $this->db->get()->result_array();

            return $suppliers_data;

    }

    /**
     * Update supplier data
     * @param mixed $id
     * @return array
     */
    public function update_supplier_price($supplier_item_price, $supplier_id)
    {
        $updateDataSupplier = array(
            'supplier_item_price' => $supplier_item_price,
        );
        $this->db->where('id', $supplier_id);
       $result = $this->db->update('tblmfitemssupplier', $updateDataSupplier);

       if($result) {
           return 1;
       }else{
           return 0;
       }

    }

    public function get_attachments($invoiceid, $id = '')
    {
        // If is passed id get return only 1 attachment
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $invoiceid);
        }
        $this->db->where('rel_type', 'invoice');
        $result = $this->db->get('tblfiles');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }

    /**
     * Get invoice total from all statuses
     * @param mixed $data $_POST data
     * @return array
     * @since  Version 1.0.2
     */
    public function get_invoices_total($data)
    {
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

        $result = [];
        $result['due'] = [];
        $result['paid'] = [];
        $result['overdue'] = [];

        $has_permission_view = has_permission('purchases', '', 'view');
        $has_permission_view_own = has_permission('purchases', '', 'view_own');

        for ($i = 1; $i <= 3; $i++) {
            $select = 'id,total';
            if ($i == 1) {
                $select .= ', (SELECT total - (SELECT COALESCE(SUM(amount),0) FROM tblinvoicepaymentrecords WHERE invoiceid = tblinvoices.id) - (SELECT COALESCE(SUM(amount),0) FROM tblcredits WHERE tblcredits.invoice_id=tblinvoices.id)) as outstanding';
            } elseif ($i == 2) {
                $select .= ',(SELECT SUM(amount) FROM tblinvoicepaymentrecords WHERE invoiceid=tblinvoices.id) as total_paid';
            }
            $this->db->select($select);
            $this->db->from('tblinvoices');
            $this->db->where('currency', $currencyid);
            // Exclude cancelled invoices
            $this->db->where('status !=', 5);
            // Exclude draft
            $this->db->where('status !=', 6);

            if (isset($data['project_id']) && $data['project_id'] != '') {
                $this->db->where('project_id', $data['project_id']);
            } elseif (isset($data['customer_id']) && $data['customer_id'] != '') {
                $this->db->where('clientid', $data['customer_id']);
            }

            if ($i == 3) {
                $this->db->where('status', 4);
            } elseif ($i == 1) {
                $this->db->where('status !=', 2);
            }

            if (isset($data['years']) && count($data['years']) > 0) {
                $this->db->where_in('YEAR(date)', $data['years']);
            } else {
                $this->db->where('YEAR(date)', date('Y'));
            }

            if (!$has_permission_view) {
                $whereUser = get_purchase_where_sql_for_staff(get_staff_user_id());
                $this->db->where('(' . $whereUser . ')');
            }

            $invoices = $this->db->get()->result_array();

            foreach ($invoices as $invoice) {
                if ($i == 1) {
                    $result['due'][] = $invoice['outstanding'];
                } elseif ($i == 2) {
                    $result['paid'][] = $invoice['total_paid'];
                } elseif ($i == 3) {
                    $result['overdue'][] = $invoice['total'];
                }
            }
        }
        $result['due'] = array_sum($result['due']);
        $result['paid'] = array_sum($result['paid']);
        $result['overdue'] = array_sum($result['overdue']);
        $result['symbol'] = $this->currencies_model->get_currency_symbol($currencyid);
        $result['currencyid'] = $currencyid;

        return $result;
    }

    /**
     * Insert new invoice to database
     * @param array $data invoice data
     * @return mixed - false if not insert, invoice ID if succes
     */
    public function add($data, $expense = false)
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        $data['prefix'] = get_option('purchase_prefix');
        $data['number_format'] = get_option('purchase_number_format');
        $data['created_by'] = !DEFINED('CRON') ? get_staff_user_id() : 0;
        $data['updated_by'] = !DEFINED('CRON') ? get_staff_user_id() : 0;


        if (isset($data['item_select'])) {
            unset($data['item_select']);
        }

        if (isset($data['show_quantity_as'])) {
            unset($data['show_quantity_as']);
        }

        if (isset($data['description'])) {
            unset($data['description']);
        }

        if (isset($data['long_description'])) {
            unset($data['long_description']);
        }

        if (isset($data['quantity'])) {
            unset($data['quantity']);
        }

        if (isset($data['suppliers'])) {
            unset($data['suppliers']);
        }

        if (isset($data['unit'])) {
            unset($data['unit']);
        }

        if (isset($data['group_id'])) {
            unset($data['group_id']);
        }

        $items = [];
        if (isset($data['newitems'])) {
            $items = $data['newitems'];
            unset($data['newitems']);
        }

        if (isset($data['date'])) {
            $data['date'] = to_sql_date($data['date'], true);
        }

        if (isset($data['terms'])) {
            $data['terms'] = nl2br_save_html($data['terms']);
        }

        if (isset($data['clientnote'])) {
            $data['clientnote'] = nl2br_save_html($data['clientnote']);
        }


        $this->db->insert('tblmfpurchases', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {

            // Update next invoice number in settings
            $this->db->where('name', 'next_mfpurchase_number');
            $this->db->set('value', 'value+1', false);
            $this->db->update('tbloptions');
//            update_invoice_status($insert_id);

            foreach ($items as $key => $item) {
                $item['status'] = isset($data['status']) ? $data['status'] : 'order';
                $item['date'] = $data['date'];
                $this->add_mfpurchase_item($insert_id, $item);
            }
            $this->log_mfpurchases_invoice_activity($insert_id, 'created the purchase');
            return $insert_id;
        }

        return false;
    }

    public function updateQuantityBalance($newDate, $warehouse_id)
    {
        $this->load->model('invoice_items_model');
        foreach ($newDate as $data) {
            $clause['id']= $data['item_id'];
            $originalItem = $this->getPurchasedItem($clause);
            if ($originalItem->quantity_balance != $data['quantity']) {
                $updateDataItem = array(
                    'quantity_balance' => $data['quantity'],
                );
                $this->db->where('id', $data['item_id']);
                $this->db->update('tblpurchaseitems', $updateDataItem);
                $this->invoice_items_model->syncProductQty($data['product_id'], $warehouse_id);
                $this->addStockTransaction(
                    ['rel_type' => 'purchase', 'id' => $data['item_id'], 'quantity' => $data['quantity']]
                );
            }
        }
    }

    public function addStockTransaction($data)
    {
        $this->db->insert('tblstock_transactions', [
            'rel_type' => $data['rel_type'],
            'rel_id' => $data['id'],
            'quantity' => $data['quantity'],
            'added_by' => get_staff_user_id(),
            'status' => '1',
        ]);
        return $this->db->insert_id();
    }

    public function removeStockTransaction($id, $rel_id)
    {
        $CI = &get_instance();
        $CI->db->where('rel_id', $id);
        $CI->db->where('rel_type', $rel_id);
        $CI->db->delete('tblstock_transactions');
        if ($CI->db->affected_rows() > 0) {
            return true;
        }
        return false;
    }

    /**
     * Update invoice data
     * @param array $data invoice data
     * @param mixed $id invoiceid
     * @return boolean
     */
    public function update($data, $id)
    {
        $original_invoice = $this->get($id);
        $affectedRows = 0;
        $save_and_send = isset($data['save_and_send']);

        $data['created_by'] = !DEFINED('CRON') ? get_staff_user_id() : 0;
        $data['updated_by'] = !DEFINED('CRON') ? get_staff_user_id() : 0;
        $data['number'] = trim($data['number']);
        $original_number_formatted = format_purchaseorder_number($id);
        $original_number = $original_invoice->number;


        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        if (isset($data['item_amount'])) {
            unset($data['item_amount']);
        }

        if (isset($data['currency'])) {
            unset($data['currency']);
        }

        if (isset($data['item_select'])) {
            unset($data['item_select']);
        }

        if (isset($data['suppliers'])) {
            unset($data['suppliers']);
        }

        if (isset($data['show_quantity_as'])) {
            unset($data['show_quantity_as']);
        }

        if (isset($data['description'])) {
            unset($data['description']);
        }

        if (isset($data['long_description'])) {
            unset($data['long_description']);
        }

        if (isset($data['quantity'])) {
            unset($data['quantity']);
        }

        if (isset($data['unit'])) {
            unset($data['unit']);
        }

        if (isset($data['is_bulk'])) {
            unset($data['is_bulk']);
        }

        if (isset($data['group_id'])) {
            unset($data['group_id']);
        }

        if (isset($data['quantity_received'])) {
            unset($data['quantity_received']);
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

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            if (handle_custom_fields_post($id, $custom_fields)) {
                $affectedRows++;
            }
            unset($data['custom_fields']);
        }



        if (isset($data['date'])) {
            $data['date'] = to_sql_date($data['date'], true);
        }

        if (isset($data['terms'])) {
            $data['terms'] = nl2br_save_html($data['terms']);
        }

        if (isset($data['clientnote'])) {
            $data['clientnote'] = nl2br_save_html($data['clientnote']);
        }
        // Delete items checked to be removed from database
        if (isset($data['removed_items'])) {
            foreach ($data['removed_items'] as $remove_item_id) {
                $original_item = $this->get_invoice_item($remove_item_id);
                $this->log_mfpurchases_invoice_activity($id, 'invoice_estimate_activity_removed_item', false, serialize([
                    $original_item->product_code,
                ]));
                $this->db->where('id', $remove_item_id);
                $this->db->delete('tblmfpurchaseitems');
                if ($this->db->affected_rows() > 0) {
                    $this->db->where('itemid', $remove_item_id)
                        ->where('rel_type', 'purchase')
                        ->delete('tblitemstax');
                    return true;
                }
            }
        }

        unset($data['removed_items']);

        if (isset($data['is_bulk'])) {
            unset($data['is_bulk']);
        }
        if (isset($data['isedit'])) {
            unset($data['isedit']);
        }
        if (isset($data['group_id'])) {
            unset($data['group_id']);
        }

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            if ($original_number != $data['number']) {
                $this->log_mfpurchases_invoice_activity($original_invoice->id, "Purchase number changed from {$original_number_formatted} to " . format_purchase_number($original_invoice->id), false, serialize([
                    $original_number_formatted,
                    format_purchase_number($original_invoice->id),
                ]));
            }
        }

        $this->load->model('invoice_items_model');
        if (count($items) > 0) {
            foreach ($items as $key => $item) {
                $original_item = $this->get_invoice_item($item['itemid']);
                $productDetails = $this->invoice_items_model->getProductByCode($item['description']);
                $updateDataItem = array(
                    'product_id' => $productDetails->id,
                    'product_code' => $item['description'],
                    'product_name' => $item['long_description'],
                    'quantity' => $item['qty'],
                    'product_unit_id' => isset($item['unit']) ? $item['unit'] : 0,
                    'product_unit_code' => isset($item['unit']) ? $item['unit'] : '',
                    'unit_quantity' => $item['qty'],
                    'date' => date('Y-m-d', strtotime($data['date'])),
                );
                $this->db->where('id', $item['itemid']);
                $this->db->update('tblmfpurchaseitems', $updateDataItem);


                if ($original_item->quantity != $updateDataItem['quantity']) {
                    $this->log_mfpurchases_invoice_activity($id, "updated item quantity from {$original_item->quantity} to " . $updateDataItem['quantity'], false, serialize([
                        $original_item->quantity,
                        $updateDataItem['quantity'],
                    ]));
                    $affectedRows++;
                }

                if (!isset($item['taxname']) || (isset($item['taxname']) && count($item['taxname']) == 0)) {
                    $this->db->where('itemid', $item['itemid'])
                        ->where('rel_type', 'purchase')
                        ->delete('tblitemstax');
                    if ($this->db->affected_rows() > 0) {
                        $affectedRows++;
                    }
                } else {
                    $this->db->where('itemid', $item['itemid']);
                    $this->db->where('rel_type', 'purchase');
                    $taxes = $this->db->get('tblitemstax')->result_array();
                    $i = 0;
                    foreach ($taxes as $tax) {
                        $taxes[$i]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
                        $taxes[$i]['onlytaxname'] = $tax['taxname'];
                        $taxes[$i]['taxnum'] = $tax['taxrate'] + 0;
                        $i++;
                    }
//                    $item_taxes        = get_invoice_item_taxes($item['itemid']);
                    $_item_taxes_names = [];
                    foreach ($taxes as $_item_tax) {
                        array_push($_item_taxes_names, $_item_tax['taxname']);
                    }
                    $i = 0;
                    foreach ($_item_taxes_names as $_item_tax) {
                        if (!in_array($_item_tax, $item['taxname'])) {
                            $this->db->where('id', $taxes[$i]['id'])
                                ->delete('tblitemstax');
                            if ($this->db->affected_rows() > 0) {
                                $affectedRows++;
                            }
                        }
                        $i++;
                    }
                    foreach ($item['taxname'] as $taxname) {
                        if ($taxname != '') {
                            $tax_array = explode('|', $taxname);
                            if (isset($tax_array[0]) && isset($tax_array[1])) {
                                $tax_name = trim($tax_array[0]);
                                $tax_rate = trim($tax_array[1]);
                                if (total_rows('tblitemstax', [
                                        'itemid' => $item['itemid'],
                                        'taxrate' => $tax_rate,
                                        'taxname' => $tax_name,
                                        'rel_id' => $id,
                                        'rel_type' => 'purchase',
                                    ]) == 0) {
                                    $this->db->insert('tblitemstax', [
                                        'itemid' => $item['itemid'],
                                        'taxrate' => $tax_rate,
                                        'taxname' => $tax_name,
                                        'rel_id' => $id,
                                        'rel_type' => 'purchase',
                                    ]);
                                }
                            }
                        }
                    }
                    $affectedRows++;
                }
            }
        }


        foreach ($newitems as $key => $item) {
            $this->add_mfpurchase_item($id, $item);
            $affectedRows++;
        }

        /*if ($affectedRows > 0) {
            // update_sales_total_tax_column($id, 'invoice', 'tblinvoices');
            update_invoice_status($id);
        }*/

        /*if ($save_and_send === true) {
            $this->send_invoice_to_client($id, '', true, '', true);
        }*/

        if ($affectedRows > 0) {
//            do_action('after_invoice_updated', $id);

            return true;
        }

        return false;
    }

    public function getWarehouseProductQuantity($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('tblwarehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function add_mfpurchase_item($purchase_id, $data)
    {
        $this->load->model('invoice_items_model');
        $productDetails = $this->invoice_items_model->getProductByCode($data['description']);
        $item = array(
            'product_id' => $productDetails->id,
            'product_code' => $data['description'],
            'product_name' => $data['long_description'],
            'quantity' => $data['qty'],
            'group_id' => $data['group_id'],
            'product_unit_id' => isset($data['unit']) ? $data['unit'] : 0,
            'product_unit_code' => isset($data['unit']) ? $data['unit'] : '',
            'unit_quantity' => $data['qty'],
//            'date' => date('Y-m-d', strtotime($data['date'])),
//            'status' => $data['status']
        );
        $item['purchase_id'] = $purchase_id;

        $this->db->insert('tblmfpurchaseitems', $item);
        $itemId = $this->db->insert_id();
        foreach ($data['suppliers'] as  $suppliers) {
            if ($suppliers != '') {
                        $this->db->insert('tblmfitemssupplier', [
                            'itemid' => $itemId,
                            'suppliers_id' => $suppliers,
                            'rel_id' => $purchase_id,
                            'rel_type' => 'Mfpurchase',
                        ]);
                }
            } 
        $this->log_mfpurchases_invoice_activity($purchase_id, 'Manufacthuring Purchases Astimet Invoice Activity', false, serialize([
            $item['product_code'],
        ]));
    }

    public function stock_in_delete($id)
    {
        $this->load->model('invoice_items_model');
        if ($purchase = $this->get($id)) {
            foreach ($purchase->items as $item) {
                $clause = array('product_id' => $item->product_id, 'warehouse_id' => $item->warehouse_id, 'status' => 'full_received');
                $qty = (0-$item->quantity_balance);
                $this->setPurchaseItem($clause, $qty);
                $this->invoice_items_model->syncProductQty($item->product_id, $item->warehouse_id);
                $updateDataItem = array(
                    'quantity_balance' => 0,
                );
                $this->db->where('id', $item['id']);
                $this->db->update('tblpurchaseitems', $updateDataItem);
                $this->removeStockTransaction($item->id,'purchase');
            }
            $this->db->where('id', $id);
            $this->db->update('tblpurchases', ['is_stock_in'=>1]);
            return true;
        }
        return false;
    }

    public function updateAVCO($data)
    {
        if (isset($data['product_code'])) {
            $this->db->select('id');
            $this->db->where('description', $data['product_code']);
            $this->db->from('tblitems');
            $product = $this->db->get()->row();
            $data['product_id'] = $product->id;
        }
        if ($wp_details = $this->getWarehouseProductQuantity($data['warehouse_id'], $data['product_id'])) {
            $total_cost = (($wp_details->quantity * $wp_details->avg_cost) + ($data['quantity'] * $data['cost']));
            $total_quantity = $wp_details->quantity + $data['quantity'];
            if (!empty($total_quantity)) {
                $avg_cost = ($total_cost / $total_quantity);
                $this->db->update('tblwarehouses_products', array('avg_cost' => $avg_cost), array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']));
            }
        } else {
            $this->db->insert('tblwarehouses_products', array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id'], 'avg_cost' => $data['cost'], 'quantity' => 0));
        }
    }

    public function setPurchaseItem($clause, $qty)
    {
        $this->db->select('*');
        $this->db->where('id', $clause['product_id']);
        $this->db->from('tblitems');
        if ($product = $this->db->get()->row()) {
            $clause['purchase_id =']=NULL;
            if ($pi = $this->getPurchasedItem($clause)) {
                $quantity_balance = $pi->quantity_balance + $qty;
                return $this->db->update('tblpurchaseitems', array('quantity_balance' => $quantity_balance), array('id' => $pi->id));
            } else {
                $this->db->where('id', $product->unit);
                $unit = $this->db->get('tblitems_brands')->row();
                unset($clause['purchase_id =']);
                $clause['product_unit_id'] = $product->unit;
                $clause['product_unit_code'] = isset($unit->name) ? $unit->name : '';
                $clause['product_code'] = $product->description;
                $clause['product_name'] = $product->long_description;
                $clause['purchase_id'] = $clause['transfer_id'] ?? null;
                $clause['net_unit_cost'] = $clause['real_unit_cost'] = $clause['unit_cost'] = $product->rate;
                $clause['quantity_balance'] = $clause['quantity'] = $clause['unit_quantity'] = $clause['quantity_received'] = $qty;
                $clause['subtotal'] = ($product->rate * $qty);
                $clause['status'] = 'full_received';
                $clause['date'] = date('Y-m-d');
                return $this->db->insert('tblpurchaseitems', $clause);
            }
        }
        return FALSE;
    }

    public function getPurchasedItem($clause)
    {
        $orderby = 'desc';
        $this->db->order_by('date', $orderby);
        $this->db->order_by('purchase_id', $orderby);
        $q = $this->db->get_where('tblpurchaseitems', $clause);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    private function map_shipping_columns($data, $expense = false)
    {
        if (!isset($data['include_shipping'])) {
            foreach ($this->shipping_fields as $_s_field) {
                if (isset($data[$_s_field])) {
                    $data[$_s_field] = null;
                }
            }
            $data['show_shipping_on_invoice'] = 1;
            $data['include_shipping'] = 0;
        } else {
            // We dont need to overwrite to 1 unless its coming from the main function add
            if (!DEFINED('CRON') && $expense == false) {
                $data['include_shipping'] = 1;
                // set by default for the next time to be checked
                if (isset($data['show_shipping_on_invoice']) && ($data['show_shipping_on_invoice'] == 1 || $data['show_shipping_on_invoice'] == 'on')) {
                    $data['show_shipping_on_invoice'] = 1;
                } else {
                    $data['show_shipping_on_invoice'] = 0;
                }
            }
            // else its just like they are passed
        }

        return $data;
    }

    /**
     * Delete invoice items and all connections
     * @param mixed $id invoiceid
     * @return boolean
     */
    public function delete($id, $simpleDelete = false)
    {
        /*if (get_option('delete_only_on_last_invoice') == 1 && $simpleDelete == false) {
            if (!is_last_invoice($id)) {
                return false;
            }
        }*/
//        $number = format_invoice_number($id);

//        do_action('before_invoice_deleted', $id);
        $this->db->where('id', $id);
        $this->db->delete('tblmfpurchases');
        if ($this->db->affected_rows() > 0) {
            /*if (get_option('invoice_number_decrement_on_delete') == 1 && $simpleDelete == false) {
                $current_next_invoice_number = get_option('next_invoice_number');
                if ($current_next_invoice_number > 1) {
                    // Decrement next invoice number to
                    $this->db->where('name', 'next_invoice_number');
                    $this->db->set('value', 'value-1', false);
                    $this->db->update('tbloptions');
                }
            }*/

            $this->db->where('purchase_id', $id);
            $this->db->delete('tblmfpurchaseitems');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'Mfpurchase');
            $this->db->delete('tblmfitemssupplier');
            return true;
        }

        return false;
    }

    /**
     *  Delete invoice attachment
     * @param mixed $id attachmentid
     * @return  boolean
     * @since  Version 1.0.4
     */
    public function delete_attachment($id)
    {
        $attachment = $this->get_attachments('', $id);
        $deleted = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_by_type('invoice') . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete('tblfiles');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                logActivity('Invoice Attachment Deleted [InvoiceID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_by_type('invoice') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('invoice') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('invoice') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    public function mark_as_cancelled($id)
    {
        $this->db->where('id', $id);
        $this->db->update('tblpurchases', [
            'status' => 'cancelled',
            'sent' => 1,
        ]);
        if ($this->db->affected_rows() > 0) {
            $this->log_invoice_activity($id, 'marked purchase as cancelled');
//            do_action('invoice_marked_as_cancelled', $id);

            return true;
        }

        return false;
    }

    /**
     * Send invoice to client
     * @param mixed $id invoiceid
     * @param string $template email template to sent
     * @param boolean $attachpdf attach invoice pdf or not
     * @return boolean
     */
    public function send_invoice_to_client($id, $template = '', $attachpdf = true, $cc = '', $manually = false)
    {
        $this->load->model('emails_model');

        $this->emails_model->set_rel_id($id);
        $this->emails_model->set_rel_type('invoice');

        $invoice = $this->get($id);
        $invoice = do_action('invoice_object_before_send_to_client', $invoice);

        if ($template == '') {
            if ($invoice->sent == 0) {
                $template = 'invoice-send-to-client';
            } else {
                $template = 'invoice-already-send';
            }
            $template = do_action('after_invoice_sent_template_statement', $template);
        }
        $invoice_number = format_invoice_number($invoice->id);

        $emails_sent = [];
        $sent = false;
        // Manually is used when sending the invoice via add/edit area button Save & Send
        if (!DEFINED('CRON') && $manually === false) {
            $sent_to = $this->input->post('sent_to');
        } else {
            $sent_to = [];
            $contacts = $this->clients_model->get_contacts($invoice->clientid, ['active' => 1, 'invoice_emails' => 1]);
            foreach ($contacts as $contact) {
                array_push($sent_to, $contact['id']);
            }
        }

        if (is_array($sent_to) && count($sent_to) > 0) {
            $status_updated = update_invoice_status($invoice->id, true, true);

            if ($attachpdf) {
                $_pdf_invoice = $this->get($id);
                $pdf = invoice_pdf($_pdf_invoice);
                $attach = $pdf->Output($invoice_number . '.pdf', 'S');
            }

            $i = 0;
            foreach ($sent_to as $contact_id) {
                if ($contact_id != '') {
                    if ($attachpdf) {
                        $this->emails_model->add_attachment([
                            'attachment' => $attach,
                            'filename' => $invoice_number . '.pdf',
                            'type' => 'application/pdf',
                        ]);
                    }
                    if ($this->input->post('email_attachments')) {
                        $_other_attachments = $this->input->post('email_attachments');
                        foreach ($_other_attachments as $attachment) {
                            $_attachment = $this->get_attachments($id, $attachment);
                            $this->emails_model->add_attachment([
                                'attachment' => get_upload_path_by_type('invoice') . $id . '/' . $_attachment->file_name,
                                'filename' => $_attachment->file_name,
                                'type' => $_attachment->filetype,
                                'read' => true,
                            ]);
                        }
                    }
                    $contact = $this->clients_model->get_contact($contact_id);
                    $merge_fields = [];
                    $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($invoice->clientid, $contact_id));

                    $merge_fields = array_merge($merge_fields, get_invoice_merge_fields($invoice->id));
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
            $this->set_invoice_sent($id, false, $emails_sent, true);
            do_action('invoice_sent', $id);

            return true;
        }
        // In case the invoice not sended and the status was draft and the invoice status is updated before send return back to draft status
        if ($invoice->status == 6 && $status_updated !== false) {
            $this->db->where('id', $invoice->id);
            $this->db->update('tblinvoices', [
                'status' => 6,
            ]);
        }


        return false;
    }

    /**
     * Set invoice to sent when email is successfuly sended to client
     * @param mixed $id invoiceid
     * @param mixed $manually is staff manually marking this invoice as sent
     * @return  boolean
     */
    public function set_invoice_sent($id, $manually = false, $emails_sent = [], $is_status_updated = false)
    {
        $this->db->where('id', $id);
        $this->db->update('tblinvoices', [
            'sent' => 1,
            'datesend' => date('Y-m-d H:i:s'),
        ]);
        $marked = false;
        if ($this->db->affected_rows() > 0) {
            $marked = true;
        }
        if (DEFINED('CRON')) {
            $additional_activity_data = serialize([
                '<custom_data>' . implode(', ', $emails_sent) . '</custom_data>',
            ]);
            $description = 'invoice_activity_sent_to_client_cron';
        } else {
            if ($manually == false) {
                $additional_activity_data = serialize([
                    '<custom_data>' . implode(', ', $emails_sent) . '</custom_data>',
                ]);
                $description = 'invoice_activity_sent_to_client';
            } else {
                $additional_activity_data = serialize([]);
                $description = 'invoice_activity_marked_as_sent';
            }
        }

        if ($is_status_updated == false) {
            update_invoice_status($id, true);
        }

        $this->log_invoice_activity($id, $description, false, $additional_activity_data);

        return $marked;
    }

    public function get_expenses_to_bill($clientid)
    {
        $this->load->model('expenses_model');
        $where = 'billable=1 AND clientid=' . $clientid . ' AND invoiceid IS NULL';
        if (!has_permission('expenses', '', 'view')) {
            $where .= ' AND tblexpenses.addedfrom=' . get_staff_user_id();
        }

        return $this->expenses_model->get('', $where);
    }

    public function check_for_merge_invoice($client_id, $current_invoice = '')
    {
        if ($current_invoice != '') {
            $this->db->select('status');
            $this->db->where('id', $current_invoice);
            $row = $this->db->get('tblinvoices')->row();
            // Cant merge on paid invoice and partialy paid and cancelled
            if ($row->status == 2 || $row->status == 3 || $row->status == 5) {
                return [];
            }
        }

        $statuses = [
            1,
            4,
            6,
        ];

        $has_permission_view = has_permission('purchases', '', 'view');
        $this->db->select('id');
        $this->db->where('clientid', $client_id);
        $this->db->where('STATUS IN (' . implode(', ', $statuses) . ')');
        if (!$has_permission_view) {
            $whereUser = get_purchase_where_sql_for_staff(get_staff_user_id());
            $this->db->where('(' . $whereUser . ')');
        }
        if ($current_invoice != '') {
            $this->db->where('id !=', $current_invoice);
        }

        $invoices = $this->db->get('tblinvoices')->result_array();
        $invoices = do_action('invoices_ids_available_for_merging', $invoices);

        $_invoices = [];

        foreach ($invoices as $invoice) {
            $inv = $this->get($invoice['id']);
            if ($inv) {
                $_invoices[] = $inv;
            }
        }

        return $_invoices;
    }

    /**
     * Copy invoice
     * @param mixed $id invoice id to copy
     * @return mixed
     */
    public function copy($id)
    {
        $_invoice = $this->get($id);
        $new_invoice_data = [];
        $new_invoice_data['clientid'] = $_invoice->clientid;
        $new_invoice_data['number'] = get_option('next_invoice_number');
        $new_invoice_data['date'] = _d(date('Y-m-d'));

        if ($_invoice->duedate && get_option('invoice_due_after') != 0) {
            $new_invoice_data['duedate'] = _d(date('Y-m-d', strtotime('+' . get_option('invoice_due_after') . ' DAY', strtotime(date('Y-m-d')))));
        }

        $new_invoice_data['save_as_draft'] = true;
        $new_invoice_data['recurring_type'] = $_invoice->recurring_type;
        $new_invoice_data['custom_recurring'] = $_invoice->custom_recurring;
        $new_invoice_data['show_quantity_as'] = $_invoice->show_quantity_as;
        $new_invoice_data['currency'] = $_invoice->currency;
        $new_invoice_data['subtotal'] = $_invoice->subtotal;
        $new_estimate_data['servicecharge'] = $_invoice->servicecharge;
        $new_estimate_data['packing_and_forwarding'] = $_invoice->packing_and_forwarding;
        $new_estimate_data['devide_gst'] = ($_invoice->devide_gst == 1 ? 'yes' : 'no');
        $new_invoice_data['total'] = $_invoice->total;
        $new_invoice_data['adminnote'] = $_invoice->adminnote;
        $new_invoice_data['adjustment'] = $_invoice->adjustment;
        $new_invoice_data['discount_percent'] = $_invoice->discount_percent;
        $new_invoice_data['discount_total'] = $_invoice->discount_total;
        $new_invoice_data['recurring'] = $_invoice->recurring;
        $new_invoice_data['discount_type'] = $_invoice->discount_type;
        $new_invoice_data['terms'] = $_invoice->terms;
        $new_invoice_data['sale_agent'] = $_invoice->sale_agent;
        $new_invoice_data['project_id'] = $_invoice->project_id;
        $new_invoice_data['cycles'] = $_invoice->cycles;
        $new_invoice_data['total_cycles'] = 0;
        // Since version 1.0.6
        $new_invoice_data['billing_street'] = clear_textarea_breaks($_invoice->billing_street);
        $new_invoice_data['billing_city'] = $_invoice->billing_city;
        $new_invoice_data['billing_state'] = $_invoice->billing_state;
        $new_invoice_data['billing_zip'] = $_invoice->billing_zip;
        $new_invoice_data['billing_country'] = $_invoice->billing_country;
        $new_invoice_data['shipping_street'] = clear_textarea_breaks($_invoice->shipping_street);
        $new_invoice_data['shipping_city'] = $_invoice->shipping_city;
        $new_invoice_data['shipping_state'] = $_invoice->shipping_state;
        $new_invoice_data['shipping_zip'] = $_invoice->shipping_zip;
        $new_invoice_data['shipping_country'] = $_invoice->shipping_country;
        if ($_invoice->include_shipping == 1) {
            $new_invoice_data['include_shipping'] = $_invoice->include_shipping;
        }
        $new_invoice_data['show_shipping_on_invoice'] = $_invoice->show_shipping_on_invoice;
        // Set to unpaid status automatically
        $new_invoice_data['status'] = 1;
        $new_invoice_data['clientnote'] = $_invoice->clientnote;
        $new_invoice_data['adminnote'] = $_invoice->adminnote;
        $new_invoice_data['allowed_payment_modes'] = unserialize($_invoice->allowed_payment_modes);
        $new_invoice_data['newitems'] = [];
        $key = 1;

        $custom_fields_items = get_custom_fields('items');
        foreach ($_invoice->items as $item) {
            $new_invoice_data['newitems'][$key]['description'] = $item['description'];
            $new_invoice_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $new_invoice_data['newitems'][$key]['qty'] = $item['qty'];
            $new_invoice_data['newitems'][$key]['unit'] = $item['unit'];
            $new_invoice_data['newitems'][$key]['taxname'] = [];
            $taxes = get_invoice_item_taxes($item['id']);
            foreach ($taxes as $tax) {
                // tax name is in format TAX1|10.00
                array_push($new_invoice_data['newitems'][$key]['taxname'], $tax['taxname']);
            }
            $new_invoice_data['newitems'][$key]['rate'] = $item['rate'];
            $new_invoice_data['newitems'][$key]['order'] = $item['item_order'];

            foreach ($custom_fields_items as $cf) {
                $new_invoice_data['newitems'][$key]['custom_fields']['items'][$cf['id']] = get_custom_field_value($item['id'], $cf['id'], 'items', false);

                if (!defined('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST')) {
                    define('COPY_CUSTOM_FIELDS_LIKE_HANDLE_POST', true);
                }
            }
            $key++;
        }
        $id = $this->invoices_model->add($new_invoice_data);
        if ($id) {
            $this->db->where('id', $id);
            $this->db->update('tblinvoices', [
                'cancel_overdue_reminders' => $_invoice->cancel_overdue_reminders,
            ]);

            $custom_fields = get_custom_fields('invoice');
            foreach ($custom_fields as $field) {
                $value = get_custom_field_value($_invoice->id, $field['id'], 'invoice', false);
                if ($value == '') {
                    continue;
                }
                $this->db->insert('tblcustomfieldsvalues', [
                    'relid' => $id,
                    'fieldid' => $field['id'],
                    'fieldto' => 'invoice',
                    'value' => $value,
                ]);
            }

            $tags = get_tags_in($_invoice->id, 'invoice');
            handle_tags_save($tags, $id, 'invoice');

            logActivity('Copied Invoice ' . format_invoice_number($_invoice->id));

            do_action('invoice_copied', ['copy_from' => $_invoice->id, 'copy_id' => $id]);

            return $id;
        }

        return false;
    }

    public function get_invoice_item($id)
    {
        $this->db->where('id', $id);

        return $this->db->get('tblpurchaseitems')->row();
    }

    /**
     * Send overdue notice to client for this invoice
     * @param mxied $id invoiceid
     * @return boolean
     */
    public function send_invoice_overdue_notice($id)
    {
        $this->load->model('emails_model');
        $this->emails_model->set_rel_id($id);
        $this->emails_model->set_rel_type('invoice');

        $invoice = $this->get($id);
        $invoice_number = format_invoice_number($invoice->id);
        $pdf = invoice_pdf($invoice);

        $attach_pdf = do_action('invoice_overdue_notice_attach_pdf', true);

        if ($attach_pdf === true) {
            $attach = $pdf->Output($invoice_number . '.pdf', 'S');
        }

        $emails_sent = [];
        $email_sent = false;
        $sms_sent = false;
        $sms_reminder_log = [];

        // For all cases update this to prevent sending multiple reminders eq on fail
        $this->db->where('id', $id);
        $this->db->update('tblinvoices', [
            'last_overdue_reminder' => date('Y-m-d'),
        ]);

        $contacts = $this->clients_model->get_contacts($invoice->clientid, ['active' => 1, 'invoice_emails' => 1]);
        foreach ($contacts as $contact) {
            if ($attach_pdf === true) {
                $this->emails_model->add_attachment([
                    'attachment' => $attach,
                    'filename' => $invoice_number . '.pdf',
                    'type' => 'application/pdf',
                ]);
            }

            $merge_fields = [];
            $merge_fields = array_merge($merge_fields, get_client_contact_merge_fields($invoice->clientid, $contact['id']));
            $merge_fields = array_merge($merge_fields, get_invoice_merge_fields($invoice->id));

            if ($this->emails_model->send_email_template('invoice-overdue-notice', $contact['email'], $merge_fields)) {
                array_push($emails_sent, $contact['email']);
                $email_sent = true;
            }

            if (can_send_sms_based_on_creation_date($invoice->datecreated)
                && $this->sms->trigger(SMS_TRIGGER_INVOICE_OVERDUE, $contact['phonenumber'], $merge_fields)) {
                $sms_sent = true;
                array_push($sms_reminder_log, $contact['firstname'] . ' (' . $contact['phonenumber'] . ')');
            }
        }

        if ($email_sent || $sms_sent) {
            if ($email_sent) {
                $this->log_invoice_activity($id, 'user_sent_overdue_reminder', false, serialize([
                    '<custom_data>' . implode(', ', $emails_sent) . '</custom_data>',
                    defined('CRON') ? ' ' : get_staff_full_name(),
                ]));
            }

            if ($sms_sent) {
                $this->log_invoice_activity($id, 'sms_reminder_sent_to', false, serialize([
                    implode(', ', $sms_reminder_log),
                ]));
            }

            do_action('invoice_overdue_reminder_sent', [
                'invoice_id' => $id,
                'sent_to' => $emails_sent,
                'sms_send' => $sms_sent,
            ]);

            return true;
        }

        return false;
    }

    /**
     * All invoice activity
     * @param mixed $id invoiceid
     * @return array
     */
    public function get_invoice_activity($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'purchase');
        $this->db->order_by('date', 'asc');

        return $this->db->get('tblsalesactivity')->result_array();
    }

    public function get_invoices_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM tblpurchases ORDER BY year DESC')->result_array();
    }
}