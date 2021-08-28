<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Invoice_items_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get invoice item by ID
     * @param mixed $id
     * @return mixed - array if not passed id, object if id passed
     */
    public function get($id = '')
    {
        $columns = $this->db->list_fields('tblitems');
        $rateCurrencyColumns = '';
        foreach ($columns as $column) {
            if (strpos($column, 'rate_currency_') !== false) {
                $rateCurrencyColumns .= $column . ',';
            }
        }
        $this->db->select($rateCurrencyColumns . 'tblitems.id as itemid,rate,product_cost,barcode_symbology,brand,purchase_unit,rack_no,warranty,upto,product_cost,alert_quantity,manufacturing_item,track_quantity,margin,
        supplier1, s1.company as supplier1_name, supplier2, s2.company as supplier2_name,
        supplier3, s3.company as supplier3_name, supplier4, s4.company as supplier4_name, supplier5, s5.company as supplier5_name,
            t1.taxrate as taxrate,t1.id as taxid,t1.name as taxname,
            t2.taxrate as taxrate_2,t2.id as taxid_2,t2.name as taxname_2,quantity,
            description,long_description,group_id,tblitems_groups.name as group_name,t6.name as unit,t7.name as purchase_unit,t6.id as unit_id,t7.id as purchase_unit_id');
        $this->db->from('tblitems');
        $this->db->join('tbltaxes t1', 't1.id = tblitems.tax', 'left');
        $this->db->join('tbltaxes t2', 't2.id = tblitems.tax2', 'left');
        $this->db->join('tblitems_units t6', 't6.id = tblitems.unit', 'left');
        $this->db->join('tblitems_units t7', 't7.id = tblitems.purchase_unit', 'left');
        $this->db->join('tblsuppliers s1', 's1.id = tblitems.supplier1', 'left');
        $this->db->join('tblsuppliers s2', 's2.id = tblitems.supplier2', 'left');
        $this->db->join('tblsuppliers s3', 's3.id = tblitems.supplier3', 'left');
        $this->db->join('tblsuppliers s4', 's4.id = tblitems.supplier4', 'left');
        $this->db->join('tblsuppliers s5', 's5.id = tblitems.supplier5', 'left');
        $this->db->join('tblitems_groups', 'tblitems_groups.id = tblitems.group_id', 'left');
        $this->db->order_by('description', 'asc');
        $this->db->group_by('itemid');
        if (is_numeric($id)) {
            $this->db->where('tblitems.id', $id);

            return $this->db->get()->row();
        }

        return $this->db->get()->result_array();
    }

    public function getProductByCode($code)
    {
        $columns = $this->db->list_fields('tblitems');
        $rateCurrencyColumns = '';
        foreach ($columns as $column) {
            if (strpos($column, 'rate_currency_') !== false) {
                $rateCurrencyColumns .= $column . ',';
            }
        }
        $this->db->select($rateCurrencyColumns . 'tblitems.id as id,rate,product_cost,barcode_symbology,brand,purchase_unit,product_cost,upto,alert_quantity,track_quantity,margin,
        s1.company as supplier1_name, s2.company as supplier2_name, s3.company as supplier3_name, s4.company as supplier4_name, s5.company as supplier5_name,
            t1.taxrate as taxrate,t1.id as taxid,t1.name as taxname,
            t2.taxrate as taxrate_2,t2.id as taxid_2,t2.name as taxname_2,quantity,
            description,long_description,group_id,tblitems_groups.name as group_name,t6.name as unit,t7.name as purchase_unit,t6.id as unit_id,t7.id as purchase_unit_id');
        $this->db->from('tblitems');
        $this->db->join('tbltaxes t1', 't1.id = tblitems.tax', 'left');
        $this->db->join('tbltaxes t2', 't2.id = tblitems.tax2', 'left');
        $this->db->join('tblitems_units t6', 't6.id = tblitems.unit', 'left');
        $this->db->join('tblitems_units t7', 't7.id = tblitems.purchase_unit', 'left');
        $this->db->join('tblsuppliers s1', 's1.id = tblitems.supplier1', 'left');
        $this->db->join('tblsuppliers s2', 's2.id = tblitems.supplier2', 'left');
        $this->db->join('tblsuppliers s3', 's3.id = tblitems.supplier3', 'left');
        $this->db->join('tblsuppliers s4', 's4.id = tblitems.supplier4', 'left');
        $this->db->join('tblsuppliers s5', 's5.id = tblitems.supplier5', 'left');
        $this->db->join('tblitems_groups', 'tblitems_groups.id = tblitems.group_id', 'left');
        $this->db->order_by('description', 'asc');
        $this->db->group_by('id');
        $q = $this->db->like('description' , $code)->get();
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function finalizeStockCount($id, $data, $products)
    {
        if ($this->db->update('tblstock_counts', $data, array('id' => $id))) {
            foreach ($products as $product) {
                $this->db->insert('tblstock_count_items', $product);
            }
            return TRUE;
        }
        return FALSE;
    }

    public function addStockCount($data)
    {
        if ($this->db->insert('tblstock_counts', $data)) {
            return TRUE;
        }
        return FALSE;
    }

    public function get_grouped()
    {
        $items = [];
        $this->db->order_by('name', 'asc');
        $groups = $this->db->get('tblitems_groups')->result_array();

        array_unshift($groups, [
            'id' => 0,
            'name' => '',
        ]);

        foreach ($groups as $group) {
            $this->db->select('*,tblitems_groups.name as group_name,tblitems.id as id, tblitems.quantity as quantity, IF((tblitems.upto IS NULL or tblitems.upto > now()), tblitems.rate, 0) as rate');
            $this->db->where('group_id', $group['id']);
//            $this->db->group_start()->where('tblitems.upto',NULL)->or_where('tblitems.upto > ', date('Y-m-d',time()))->group_end();
            $this->db->join('tblitems_groups', 'tblitems_groups.id = tblitems.group_id', 'left');
            $this->db->order_by('description', 'asc');
            $_items = $this->db->get('tblitems')->result_array();
            if (count($_items) > 0) {
                $items[$group['id']] = [];
                foreach ($_items as $i) {
                    array_push($items[$group['id']], $i);
                }
            }
        }

        return $items;
    }

    public function get_expired_grouped()
    {
        $items = [];
        $this->db->order_by('name', 'asc');
        $groups = $this->db->get('tblitems_groups')->result_array();

        array_unshift($groups, [
            'id' => 0,
            'name' => '',
        ]);

        foreach ($groups as $group) {
            $this->db->select('*,tblitems_groups.name as group_name,tblitems.id as id, tblitems.quantity as quantity');
            $this->db->where('group_id', $group['id']);
            $this->db->group_start()->where('tblitems.upto < ', date('Y-m-d',time()))->group_end();
            $this->db->join('tblitems_groups', 'tblitems_groups.id = tblitems.group_id', 'left');
            $this->db->order_by('description', 'asc');
            $_items = $this->db->get('tblitems')->result_array();
            if (count($_items) > 0) {
                $items[$group['id']] = [];
                foreach ($_items as $i) {
                    array_push($items[$group['id']], $i);
                }
            }
        }

        return $items;
    }

    /**
     * Add new invoice item
     * @param array $data Invoice item data
     * @return boolean
     */
    public function add($data)
    {
        unset($data['itemid']);
        if ($data['tax'] == '') {
            unset($data['tax']);
        }

        if (isset($data['tax2']) && $data['tax2'] == '') {
            unset($data['tax2']);
        }

        if (isset($data['group_id']) && $data['group_id'] == '') {
            $data['group_id'] = 0;
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        if (isset($data['warehouse_qty'])) {
            $warehouse_qty = $data['warehouse_qty'];
            unset($data['warehouse_qty']);
        }

        $columns = $this->db->list_fields('tblitems');
        $this->load->dbforge();

        foreach ($data as $column => $itemData) {
            if (!in_array($column, $columns) && strpos($column, 'rate_currency_') !== false) {
                $field = [
                    $column => [
                        'type' => 'decimal(15,' . get_decimal_places() . ')',
                        'null' => true,
                    ],
                ];
                $this->dbforge->add_column('tblitems', $field);
            }
        }

        $this->db->insert('tblitems', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            if (isset($warehouse_qty) && !empty($warehouse_qty)) {
                $wh_total_quantity = 0;
                foreach ($warehouse_qty as $wh_qty) {
                    if (isset($wh_qty['quantity']) && !empty($wh_qty['quantity'])) {
                        $this->db->insert('tblwarehouses_products', array('product_id' => $insert_id, 'warehouse_id' => $wh_qty['warehouse_id'], 'quantity' => $wh_qty['quantity'], 'avg_cost' => $data['rate']));
                        $wh_total_quantity = $wh_total_quantity+ $wh_qty['quantity'];
                        $subtotal = ($data['rate'] * $wh_qty['quantity']);

                        $purchaseItem = array(
                            'product_id' => $insert_id,
                            'product_code' => $data['description'],
                            'product_name' => $data['long_description'],
                            'net_unit_cost' => $data['rate'],
                            'unit_cost' => $data['rate'],
                            'real_unit_cost' => $data['rate'],
                            'quantity' => $wh_qty['quantity'],
                            'quantity_balance' => $wh_qty['quantity'],
                            'quantity_received' => $wh_qty['quantity'],
                            'subtotal' => $subtotal,
                            'warehouse_id' => $wh_qty['warehouse_id'],
                            'date' => date('Y-m-d'),
                            'status' => 'full_received',
                        );
                        $this->db->insert('tblpurchaseitems', $purchaseItem);
                    }
                }
                $this->syncQuantity(null,null,null,$insert_id);
            }
            if (isset($custom_fields)) {
                handle_custom_fields_post($insert_id, $custom_fields, true);
            }
            logActivity('New Invoice Item Added [ID:' . $insert_id . ', ' . $data['description'] . ']');

            return $insert_id;
        }

        return false;
    }

    public function addAdjustment($data)
    {
        $products = [];
        if (isset($data['products'])) {
            $products = $data['products'];
            unset($data['products']);
        }
        if ($this->db->insert('tbladjustments', $data)) {
            $adjustment_id = $this->db->insert_id();
            foreach ($products as $product) {
                $product['adjustment_id'] = $adjustment_id;
                $this->db->insert('tbladjustment_items', $product);
                $newAdjutment['product_id'] = $product['product_id'];
                $newAdjutment['quantity'] = $product['quantity'];
                $newAdjutment['warehouse_id'] = $data['warehouse_id'];
                $newAdjutment['type'] = $product['type'];
                $this->syncAdjustment($newAdjutment);
            }
            return $adjustment_id;
        }
        return false;
    }

    public function getAdjustmentItems($adjustment_id)
    {
        $this->db->select('tbladjustment_items.*, tblitems.description as product_code, tblitems.long_description as product_name')
            ->join('tblitems', 'tblitems.id=tbladjustment_items.product_id', 'left')
            ->group_by('tbladjustment_items.id')
            ->order_by('id', 'asc');

        $this->db->where('adjustment_id', $adjustment_id);

        $q = $this->db->get('tbladjustment_items');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function reverseAdjustment($id)
    {
        $this->load->model('purchases_model');
        if ($products = $this->getAdjustmentItems($id)) {
            foreach ($products as $adjustment) {
                $clause = array('product_id' => $adjustment->product_id, 'warehouse_id' => $adjustment->warehouse_id, 'status' => 'full_received');
                $qty = $adjustment->type == 'subtraction' ? (0+$adjustment->quantity) : (0-$adjustment->quantity);
                $this->purchases_model->setPurchaseItem($clause, $qty);
                $this->syncProductQty($adjustment->product_id, $adjustment->warehouse_id);
            }
        }
    }

    public function updateAdjustment($data, $warehouse_id)
    {
        $products = [];
        if (isset($data['products'])) {
            $products = $data['products'];
            unset($data['products']);
        }
        $this->reverseAdjustment($warehouse_id);
        $this->db->where('id', $warehouse_id);
        if ($this->db->update('tbladjustments', $data)) {
            foreach ($products as $product) {
                $this->db->select();
                $this->db->where('adjustment_id',$warehouse_id);
                $this->db->where('product_id',$product['product_id']);
                $this->db->where('warehouse_id',$product['warehouse_id']);
                $row = $this->db->get('tbladjustment_items')->row();
                if($row){
                    $this->db->where('id', $row->id);
                    $this->db->update('tbladjustment_items', $product);
                }else{
                    $product['adjustment_id'] = $warehouse_id;
                    $this->db->insert('tbladjustment_items', $product);
                }
                $newAdjustmenr['product_id'] = $product['product_id'];
                $newAdjustmenr['quantity'] = $product['quantity'];
                $newAdjustmenr['warehouse_id'] = $product['warehouse_id'];
                $newAdjustmenr['type'] = $product['type'];
                $this->syncAdjustment($newAdjustmenr);
            }
            return $warehouse_id;
        }
        return false;
    }

    public function getAdjustment($id = '')
    {
        $this->db->select('*');
        $this->db->from('tbladjustments');
        $this->db->join('tblwarehouses w', 'w.id = tbladjustments.warehouse_id', 'left');
        $this->db->order_by('w.name', 'asc');
        if (is_numeric($id)) {
            $this->db->where('tbladjustments.id', $id);
            $adjustment = $this->db->get()->row();
            $this->db->select('*,i1.id as itemid,tbladjustment_items.quantity as quantity');
            $this->db->from('tbladjustment_items');
            $this->db->join('tblitems i1', 'i1.id = tbladjustment_items.product_id', 'left');
            $this->db->where('adjustment_id', $id);
            $adjustment->products =  $this->db->get()->result_array();
            return $adjustment;
        }

        return $this->db->get()->result_array();
    }

    public function syncAdjustment($data = array())
    {
        if(! empty($data)) {
            $clause = array('product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id'], 'status' => 'full_received');
            $qty = $data['type'] == 'subtraction' ? 0 - $data['quantity'] : 0 + $data['quantity'];
            $this->load->model('purchases_model');
            $this->purchases_model->setPurchaseItem($clause, $qty);
            $this->syncProductQty($data['product_id'], $data['warehouse_id']);
        }
    }

    public function getWarehouseProducts($product_id, $warehouse_id = NULL) {
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $q = $this->db->get_where('tblwarehouses_products', array('product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncProductQty($product_id, $warehouse_id) {
        $balance_qty = $this->getBalanceQuantity($product_id);
        $wh_balance_qty = $this->getBalanceQuantity($product_id, $warehouse_id);
        if ($this->db->update('tblitems', array('quantity' => $balance_qty), array('id' => $product_id))) {
            if ($this->getWarehouseProducts($product_id, $warehouse_id)) {
                $this->db->update('tblwarehouses_products', array('quantity' => $wh_balance_qty), array('product_id' => $product_id, 'warehouse_id' => $warehouse_id));
            } else {
                if( ! $wh_balance_qty) { $wh_balance_qty = 0; }
                $product = $this->get($product_id);
                $this->db->insert('tblwarehouses_products', array('quantity' => $wh_balance_qty, 'product_id' => $product_id, 'warehouse_id' => $warehouse_id, 'avg_cost' => $product->rate));
            }
            return TRUE;
        }
        return FALSE;
    }

    private function getBalanceQuantity($product_id, $warehouse_id = NULL) {
        $this->db->select('SUM(COALESCE(quantity_balance, 0)) as stock', False);
        $this->db->where('product_id', $product_id)->where('quantity_balance !=', 0);
        if ($warehouse_id) {
            $this->db->where('warehouse_id', $warehouse_id);
        }
        $this->db->group_start()->where('status', 'full_received')->or_where('status', 'partial_received')->group_end();
        $q = $this->db->get('tblpurchaseitems');
        if ($q->num_rows() > 0) {
            $data = $q->row();
            return $data->stock;
        }
        return 0;
    }

    /**
     * Update invoiec item
     * @param array $data Invoice data to update
     * @return boolean
     */
    public function edit($data)
    {
        $itemid = $data['itemid'];
        unset($data['itemid']);

        if (isset($data['group_id']) && $data['group_id'] == '') {
            $data['group_id'] = 0;
        }

        if (isset($data['tax']) && $data['tax'] == '') {
            $data['tax'] = null;
        }

        if (isset($data['tax2']) && $data['tax2'] == '') {
            $data['tax2'] = null;
        }

        if (isset($data['custom_fields'])) {
            $custom_fields = $data['custom_fields'];
            unset($data['custom_fields']);
        }

        if (isset($data['warehouse_qty'])) {
//            $warehouse_qty = $data['warehouse_qty'];
            unset($data['warehouse_qty']);
        }

        $columns = $this->db->list_fields('tblitems');
        $this->load->dbforge();

        foreach ($data as $column => $itemData) {
            if (!in_array($column, $columns) && strpos($column, 'rate_currency_') !== false) {
                $field = [
                    $column => [
                        'type' => 'decimal(15,' . get_decimal_places() . ')',
                        'null' => true,
                    ],
                ];
                $this->dbforge->add_column('tblitems', $field);
            }
        }

        $affectedRows = 0;
        $this->db->where('id', $itemid);
        $this->db->update('tblitems', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Invoice Item Updated [ID: ' . $itemid . ', ' . $data['description'] . ']');
            $affectedRows++;
        }

        if (isset($custom_fields)) {
            if (handle_custom_fields_post($itemid, $custom_fields, true)) {
                $affectedRows++;
            }
        }

        return $affectedRows > 0 ? true : false;
    }

    public function search($q)
    {
        $this->db->select('rate, id, description as name, long_description as subtext');
        $this->db->where('manufacturing_item', 'false');
        $this->db->group_start();
        $this->db->like('description', $q);
        $this->db->or_like('long_description', $q);
        $this->db->group_end();
        $items = $this->db->get('tblitems')->result_array();

        foreach ($items as $key => $item) {
            $items[$key]['subtext'] = strip_tags(mb_substr($item['subtext'], 0, 200)) . '...';
            $items[$key]['name'] = '(' . _format_number($item['rate']) . ') ' . $item['name'];
        }

        return $items;
    }

    public function mf_item_search($q)
    {
        $this->db->select('rate, id, description as name, long_description as subtext');
        $this->db->where('manufacturing_item', 'true');
        $this->db->group_start();
        $this->db->like('description', $q);
        $this->db->or_like('long_description', $q);
        $this->db->group_end();
        $items = $this->db->get('tblitems')->result_array();
 
        foreach ($items as $key => $item) {
            $items[$key]['subtext'] = strip_tags(mb_substr($item['subtext'], 0, 200)) . '...';
            $items[$key]['name'] = '(' . _format_number($item['rate']) . ') ' . $item['name'];
        }

        return $items;
    }

    /**
     * Delete invoice item
     * @param mixed $id
     * @return boolean
     */
    public function delete($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('tblitems');
        if ($this->db->affected_rows() > 0) {
            $this->db->where('relid', $id);
            $this->db->where('fieldto', 'items_pr');
            $this->db->delete('tblcustomfieldsvalues');

            logActivity('Invoice Item Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    public function getStockCountProducts($warehouse_id, $categories = NULL, $brands = NULL)
    {
        $columns = $this->db->list_fields('tblitems');
        $rateCurrencyColumns = '';
        foreach ($columns as $column) {
            if (strpos($column, 'rate_currency_') !== false) {
                $rateCurrencyColumns .= $column . ',';
            }
        }
        $this->db->select($rateCurrencyColumns."tblitems.id as itemid,rate,product_cost,barcode_symbology,purchase_unit,product_cost,alert_quantity,track_quantity,margin,tblitems_brands.name as brand,tblwarehouses_products.quantity as quantity,
            description,long_description,group_id,tblitems_groups.name as group_name,unit")
            ->join('tblwarehouses_products', 'tblwarehouses_products.product_id=tblitems.id', 'left')
            ->join('tblitems_groups', 'tblitems_groups.id = tblitems.group_id', 'left')
            ->join('tblitems_brands', 'tblitems.brand=tblitems_brands.id', 'left')
            ->where('tblwarehouses_products.warehouse_id', $warehouse_id)
            ->order_by('tblitems.description', 'asc');
        if ($categories) {
            $r = 1;
            $this->db->group_start();
            foreach ($categories as $category) {
                if ($r == 1) {
                    $this->db->where('tblitems.group_id', $category);
                } else {
                    $this->db->or_where('tblitems.group_id', $category);
                }
                $r++;
            }
            $this->db->group_end();
        }
        if ($brands) {
            $r = 1;
            $this->db->group_start();
            foreach ($brands as $brand) {
                if ($r == 1) {
                    $this->db->where('tblitems.brand', $brand);
                } else {
                    $this->db->or_where('tblitems.brand', $brand);
                }
                $r++;
            }
            $this->db->group_end();
        }

        $q = $this->db->get('tblitems');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStouckCountByID($id)
    {
        $q = $this->db->get_where("tblstock_counts", array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function get_rfr($id = NULL, $where=null)
    {
        if(isset($where)){
            if(isset($where['item_id'])){
                $this->db->where('item_id', $where['item_id']);
            }
        }
        if(isset($id)){
            $this->db->where('id', $id);
            return $this->db->get('tblrfr')->row();
        }
//        $this->db->order_by('name', 'asc');

        return $this->db->get('tblrfr')->result_array();
    }

    public function get_brands($id = NULL)
    {
        if(isset($id)){
            $this->db->where('id', $id);
            return $this->db->get('tblitems_brands')->row();
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get('tblitems_brands')->result_array();
    }

    public function add_brand($data)
    {
        $this->db->insert('tblitems_brands', $data);
        logActivity('Items Brand Created [Name: ' . $data['name'] . ']');

        return $this->db->insert_id();
    }

    public function add_rfr($data)
    {
        $data['staffid'] = !DEFINED('CRON') ? get_staff_user_id() : 0;
        $this->db->insert('tblrfr', $data);

        return $this->db->insert_id();
    }

    public function edit_brand($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('tblitems_brands', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Items Brand Updated [Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    public function delete_brand($id)
    {
        $this->db->where('id', $id);
        $group = $this->db->get('tblitems_brands')->row();

        if ($group) {
            $this->db->where('brand_id', $id);
            $this->db->update('tblitems', [
                'brand_id' => 0,
            ]);

            $this->db->where('id', $id);
            $this->db->delete('tblitems_brands');

            logActivity('Item Brand Deleted [Name: ' . $group->name . ']');

            return true;
        }

        return false;
    }

    public function delete_quantity_adjustment($adjustment_id)
    {
        $this->db->where('id', $adjustment_id);
        $group = $this->db->get('tbladjustments')->row();
        $this->reverseAdjustment($adjustment_id);
        if ($group) {
            $this->db->where('adjustment_id', $adjustment_id);
            $this->db->delete('tbladjustment_items');

            $this->db->where('id', $adjustment_id);
            $this->db->delete('tbladjustments');

            logActivity('Adjustment Deleted [id: ' . $group->id . ']');

            return true;
        }

        return false;
    }

    public function get_units()
    {
        $this->db->select("tblitems_units.id as id, tblitems_units.name, tblitems_units.base_unit, b.name as base_unit_name, tblitems_units.operator, tblitems_units.operation_value", FALSE)
        ->join("tblitems_units b", 'b.id=tblitems_units.base_unit', 'left')->group_by('tblitems_units.id');
        $this->db->order_by('tblitems_units.name', 'asc');

        return $this->db->get('tblitems_units')->result_array();
    }

    public function getUnitChildren($base_unit)
    {
        $this->db->where('base_unit', $base_unit);
        $q = $this->db->get("tblitems_units");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function add_unit($data)
    {
        $this->db->insert('tblitems_units', $data);
        logActivity('Items Unit Created [Name: ' . $data['name'] . ']');

        return $this->db->insert_id();
    }

    public function edit_unit($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('tblitems_units', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Items Unit Updated [Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    public function delete_unit($id)
    {
        $this->db->where('id', $id);
        $group = $this->db->get('tblitems_units')->row();

        if ($group) {
            $this->db->where('unit', $id);
            $this->db->update('tblitems', [
                'unit' => 0,
            ]);
            $this->db->where('purchase_unit', $id);
            $this->db->update('tblitems', [
                'purchase_unit' => 0,
            ]);

            $this->db->where('id', $id);
            $this->db->delete('tblitems_units');

            logActivity('Item Unit Deleted [Name: ' . $group->name . ']');

            return true;
        }

        return false;
    }

    public function get_groups($id = NULL)
    {
        $this->db->select('*, IF(tblitems_groups.parent_id != 0, (select ig2.name from tblitems_groups as ig2 where tblitems_groups.parent_id = ig2.id limit 1), "Parent") as parent_name');
        if(isset($id)){
            $this->db->where('id', $id);
            return $this->db->get('tblitems_groups')->row();
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get('tblitems_groups')->result_array();
    }

    public function get_parent_groups($id = NULL)
    {
        $this->db->where('parent_id', '0');
        if(isset($id)){
            $this->db->where('id', $id);
            return $this->db->get('tblitems_groups')->row();
        }
        $this->db->order_by('name', 'asc');

        return $this->db->get('tblitems_groups')->result_array();
    }

    public function add_group($data)
    {
        $this->db->insert('tblitems_groups', $data);
        logActivity('Items Group Created [Name: ' . $data['name'] . ']');

        return $this->db->insert_id();
    }

    public function edit_group($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('tblitems_groups', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Items Group Updated [Name: ' . $data['name'] . ']');

            return true;
        }

        return false;
    }

    public function delete_group($id)
    {
        $this->db->where('id', $id);
        $group = $this->db->get('tblitems_groups')->row();

        if ($group) {
            $this->db->where('group_id', $id);
            $this->db->update('tblitems', [
                'group_id' => 0,
            ]);

            $this->db->where('id', $id);
            $this->db->delete('tblitems_groups');

            logActivity('Item Group Deleted [Name: ' . $group->name . ']');

            return true;
        }

        return false;
    }

    public function getAllWarehouses() {
        $q = $this->db->get('tblwarehouses');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getAllPurchaseItems($purchase_id) {
        $q = $this->db->get_where('tblpurchaseitems', array('purchase_id' => $purchase_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function syncQuantity($adjustmentId = NULL, $purchase_id = NULL, $oitems = NULL, $product_id = NULL) {
        if ($adjustmentId) {
            $purchase_items =[];
            $q = $this->db->query('SELECT * FROM `tblpurchaseitems` WHERE `product_id` IN (SELECT product_id FROM `tbladjustment_items` WHERE adjustment_id = ?)', [$adjustmentId]);
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $purchase_items[] = $row;
                }
            }
            if(!empty($purchase_items)){
                foreach ($purchase_items as $item) {
                    $this->syncProductQty($item->product_id, $item->warehouse_id);
                }
            }
        } elseif ($purchase_id) {
            $purchase = $this->db->get_where('tblpurchases', array('id' => $purchase_id), 1)->row();
            $purchase_items =  $this->getAllPurchaseItems($purchase_id);
            foreach ($purchase_items as $item) {
                $this->syncProductQty($item->product_id, $purchase->warehouse_id);
            }
        } elseif ($oitems) {
            foreach ($oitems as $item) {
                $this->syncProductQty($item->product_id, $item->warehouse_id);
            }
        } elseif ($product_id) {
            $warehouses = $this->getAllWarehouses();
            foreach ($warehouses as $warehouse) {
                $this->syncProductQty($product_id, $warehouse->id);
            }
        }
    }
}
