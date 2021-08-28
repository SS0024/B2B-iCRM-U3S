<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Invoice_items extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('invoice_items_model');
    }

    /* List all available items */
    public function index()
    {
        if (!has_permission('items', '', 'view')) {
            access_denied('Invoice Items');
        }

        $this->load->model('taxes_model');
        $this->load->model('warehouses_model');
        $data['taxes'] = $this->taxes_model->get();
        $data['items_groups'] = $this->invoice_items_model->get_groups();
        $data['items_parent_groups'] = $this->invoice_items_model->get_groups();
        $data['items_brands'] = $this->invoice_items_model->get_brands();
        $data['items_units'] = $this->invoice_items_model->get_units();
        $data['warehouses'] = $this->warehouses_model->getAllWarehouses();

        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();

        $data['base_currency'] = $this->currencies_model->get_base_currency();

        $data['title'] = _l('invoice_items');
        $this->load->view('admin/invoice_items/manage', $data);
    }

    public function import_price()
    {
        if (!has_permission('items', '', 'create')) {
            access_denied('Items Import');
        }

        $this->load->library('import/import_items', [], 'import');

        $this->import->setDatabaseFields(array('description', 'product_cost', 'margin', 'rate'));

        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }

        if ($this->input->post()
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {
            $this->import->setSimulation($this->input->post('simulate'))
                ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                ->setFilename($_FILES['file_csv']['name'])
                ->perform();

            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
            }
        }

        $data['title'] = _l('Import Product Price');
        $this->load->view('admin/invoice_items/import_price', $data);
    }

    public function barcode($product_code = NULL, $bcs = 'code128', $height = 40)
    {
        header('Content-type: image/svg+xml');
        echo $this->sma->barcode($product_code, $bcs, $height, true, false, true);
    }

    function print_barcodes($product_id = NULL)
    {
        if (!has_permission('print_barcode', '', 'view')) {
            access_denied('Print Barcode/Label');
        }
        $data['ajaxItems'] = false;
        if (total_rows('tblitems') <= ajax_on_total_items()) {
            $data['nitems'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['nitems']     = [];
            $data['ajaxItems'] = true;
        }
        $data['items_groups'] = $this->invoice_items_model->get_groups();
        if ($this->input->post()) {
            $style = $this->input->post('styles');
            $bci_size = ($style == 10 || $style == 12 ? 50 : ($style == 14 || $style == 18 ? 30 : 20));
            $this->load->model('currencies_model');
            $currencies = $this->currencies_model->get();
            $s = isset($_POST['product']) ? sizeof($_POST['product']) : 0;
            if ($s < 1) {
                set_alert('error', _l('No product selected. Please select at least one product.'));
                redirect(admin_url('invoice_items/print_barcodes'));
            }
            for ($m = 0; $m < $s; $m++) {
                $pid = $_POST['product'][$m];
                $quantity = $_POST['quantity'][$m];
                $product = $this->invoice_items_model->get($pid);
                $product->price = $product->rate;
                $barcodes[] = array(
                    'site' => $this->input->post('site_name') ? get_option('invoice_company_name') : FALSE,
                    'name' => $this->input->post('product_name') ? $product->long_description : FALSE,
                    'image' => $this->input->post('check_promo'),
                    // 'barcode' => $this->product_barcode($product->code, $product->barcode_symbology, $bci_size),
                    'barcode' => $product->description,
                    'bcs' => $product->barcode_symbology,
                    'bcis' => $bci_size,
                    'price' => $this->input->post('price') ? $product->product_cost : FALSE,
                    'rprice' => $this->input->post('price') ? $product->rate : FALSE,
                    'unit' => $this->input->post('unit') ? $product->purchase_unit : FALSE,
                    'category' => $this->input->post('category') ? $product->group_name : FALSE,
                    'currencies' => TRUE,
                    'variants' => FALSE,
                    'quantity' => $quantity
                );
                $pr[$pid] = array(
                    'itemid' => $pid,
                    'description' => $product->description,
                    'long_description' => $product->long_description,
                    'quantity' => $this->input->get('quantity') ? $this->input->get('quantity') : $product->quantity,
                    'price' => $product->rate);

            }
            $data['barcodes'] = $barcodes;
            $data['currencies'] = $currencies;
            $data['style'] = $style;
            $data['items'] = isset($pr) ? json_encode($pr) : false;
            $data['title'] = _l('Print Barcodes');

            $this->load->view('admin/invoice_items/print_barcodes', $data);
        } else {
            if ($product_id) {
                $row = $this->invoice_items_model->get($product_id);
                if ($row = $this->invoice_items_model->get($product_id)) {
                    $pr[$row->itemid] = array(
                        'itemid' => $row->itemid,
                        'description' => $row->long_description,
                        'long_description' => $row->long_description,
                        'quantity' => $this->input->get('quantity') ? $this->input->get('quantity') : $row->quantity,
                        'price' => $row->rate);

                    $data['message'] = 'Product added to list';
                }
            }
            /*if ($this->input->get('category')) {
                if ($products = $this->products_model->getCategoryProducts($this->input->get('category'))) {
                    foreach ($products as $row) {
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }
                    $this->data['message'] = lang('products_added_to_list');
                } else {
                    $pr = array();
                    $this->session->set_flashdata('error', lang('no_product_found'));
                }
            }

            if ($this->input->get('subcategory')) {
                if ($products = $this->products_model->getSubCategoryProducts($this->input->get('subcategory'))) {
                    foreach ($products as $row) {
                        $selected_variants = false;
                        if ($variants = $this->products_model->getProductOptions($row->id)) {
                            foreach ($variants as $variant) {
                                $selected_variants[$variant->id] = $variant->quantity > 0 ? 1 : 0;
                            }
                        }
                        $pr[$row->id] = array('id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'code' => $row->code, 'name' => $row->name, 'price' => $row->price, 'qty' => $row->quantity, 'variants' => $variants, 'selected_variants' => $selected_variants);
                    }
                    $this->data['message'] = lang('products_added_to_list');
                } else {
                    $pr = array();
                    $this->session->set_flashdata('error', lang('no_product_found'));
                }
            }*/
            $data['title'] = _l('Print Barcodes');
            $data['items'] = isset($pr) ? json_encode($pr) : false;
            $this->load->view('admin/invoice_items/print_barcodes', $data);
        }
    }

    public function quantity_adjustments($warehouse_id = NULL)
    {
        if (!has_permission('quantity_adjustments', '', 'view')) {
            access_denied('quantity_adjustments');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('quantity_adjustment');
        }
//        if (!$this->session->userdata('warehouse_id')) {
        $this->load->model('warehouses_model');
        $data['warehouses'] = $this->warehouses_model->get();
//            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        /*} else {
            $this->data['warehouses'] = null;
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }*/

//        $meta = array('page_title' => lang('quantity_adjustments'), 'bc' => $bc);
        $this->load->view('admin/invoice_items/quantity_adjustments', $data);
    }

    public function stock_counts($warehouse_id = NULL)
    {
        if (!has_permission('stock_counts', '', 'view')) {
            access_denied('stock_counts');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('stock_counts');
        }
//        if (!$this->session->userdata('warehouse_id')) {
        $this->load->model('warehouses_model');
        $data['warehouses'] = $this->warehouses_model->get();
//            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        /*} else {
            $this->data['warehouses'] = null;
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }*/

//        $meta = array('page_title' => lang('quantity_adjustments'), 'bc' => $bc);
        $this->load->view('admin/invoice_items/stock_counts', $data);
    }


    public function syncQuantityByType($type, $id){
        if($type == 'adjustment'){
            $this->invoice_items_model->syncQuantity($id, NULL, NULL, null);
            echo 'done';
        }elseif ($type == 'product'){
            $this->invoice_items_model->syncQuantity(NULL, NULL, NULL, $id);
            echo 'done';
        }else{
            echo 'No available type found';
        }
    }


    public function syncHoldQtyByType(){
        $q = $this->db->query("SELECT id  FROM `tblinvoices` WHERE `is_stock_out` in (0,1)");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $items = get_items_by_type('invoice',$row->id);
                foreach ($items as $item){
                    $this->db->select('id');
                    $this->db->from('tblitems');
                    $iDetails = $this->db->where('description' , $item['description'])->get();
                    if ($iDetails->num_rows() > 0) {
                        $product_id = $iDetails->row()->id;
                        $q2 = $this->db->query('SELECT sum(qty) as hold_qty FROM `tblhold_items` WHERE product_id  = ?', [$product_id]);
                        if ($q2->num_rows() > 0 && isset($q2->row()->hold_qty)) {
                            $res = $q2->row();
                            $this->db->where('id', $product_id);
                            $this->db->set('hold_stock', $res->hold_qty, false);
                            $this->db->update('tblitems');
                            echo 'done' . $res->hold_qty;
                        } else {
                            $this->db->where('id', $product_id);
                            $this->db->update('tblitems', ['hold_stock' => '0']);
                            echo 'done' . '0';
                        }
                    }
                }
            }
        }
        $q = $this->db->query("SELECT id  FROM `tblpurchaseorder` WHERE `status` not in (7)");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $items = get_items_by_type('purchaseorder',$row->id);
                foreach ($items as $item){
                    $this->db->select('id');
                    $this->db->from('tblitems');
                    $iDetails = $this->db->where('description' , $item['description'])->get();
                    if ($iDetails->num_rows() > 0) {
                        $product_id = $iDetails->row()->id;
                        $q2 = $this->db->query('SELECT sum(qty) as hold_qty FROM `tblhold_items` WHERE product_id  = ?', [$product_id]);
                        if ($q2->num_rows() > 0 && isset($q2->row()->hold_qty)) {
                            $res = $q2->row();
                            $this->db->where('id', $product_id);
                            $this->db->set('hold_stock', $res->hold_qty, false);
                            $this->db->update('tblitems');
                            echo 'done' . $res->hold_qty;
                        } else {
                            $this->db->where('id', $product_id);
                            $this->db->update('tblitems', ['hold_stock' => '0']);
                            echo 'done' . '0';
                        }
                    }
                }
            }
        }
        $q = $this->db->query("SELECT id  FROM `tbldelivery_challans` WHERE `is_stock_out` in (0,1)");
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $items = get_items_by_type('delivery_challan',$row->id);
                foreach ($items as $item){
                    $this->db->select('id');
                    $this->db->from('tblitems');
                    $iDetails = $this->db->where('description' , $item['description'])->get();
                    if ($iDetails->num_rows() > 0) {
                        $product_id = $iDetails->row()->id;
                        $q2 = $this->db->query('SELECT sum(qty) as hold_qty FROM `tblhold_items` WHERE product_id  = ?', [$product_id]);
                        if ($q2->num_rows() > 0 && isset($q2->row()->hold_qty)) {
                            $res = $q2->row();
                            $this->db->where('id', $product_id);
                            $this->db->set('hold_stock', $res->hold_qty, false);
                            $this->db->update('tblitems');
                            echo 'done' . $res->hold_qty;
                        } else {
                            $this->db->where('id', $product_id);
                            $this->db->update('tblitems', ['hold_stock' => '0']);
                            echo 'done' . '0';
                        }
                    }
                }
            }
        }
    }

    public function import_quantity_adjustment(){
        if (!has_permission('quantity_adjustments', '', 'create') && !has_permission('quantity_adjustments', '', 'edit')) {
            access_denied('quantity_adjustments');
        }

        if ($this->input->post() && isset($_FILES['csv_file']['name']) && $_FILES['csv_file']['name'] != '') {
            $this->load->library('import/import_adjustment_stock', [], 'import');
            $this->import->setDatabaseFields(array('product_code', 'expected','count'));
            $this->import->setTemporaryFileLocation($_FILES['csv_file']['tmp_name'])
                ->setFilename($_FILES['csv_file']['name'])
                ->perform();
            set_alert('success', _l('Stock is adjusted now.'));
            redirect(admin_url('invoice_items/quantity_adjustments'));
        }
        $this->load->model('warehouses_model');
        $data['warehouses'] = $this->warehouses_model->get();
        $this->load->view('admin/invoice_items/quantity_adjustment_import', $data);
    }

    public function quantity_adjustment($warehouse_id = NULL)
    {
        if (!has_permission('quantity_adjustments', '', 'create') && !has_permission('quantity_adjustments', '', 'edit')) {
            access_denied('quantity_adjustments');
        }

        if ($this->input->post()) {
            $newDate['reference_no'] = $this->input->post('reference_no') ? $this->input->post('reference_no') : "";
            $newDate['warehouse_id'] = $this->input->post('warehouse');
            $newDate['note'] = $this->input->post('note');
            $i = isset($_POST['product_id']) ? sizeof($_POST['product_id']) : 0;
            for ($r = 0; $r < $i; $r++) {
                $product_id = $_POST['product_id'][$r];
                $type = $_POST['type'][$r];
                $quantity = $_POST['quantity'][$r];
                $products[] = array(
                    'product_id' => $product_id,
                    'type' => $type,
                    'quantity' => $quantity,
                    'warehouse_id' => $this->input->post('warehouse')
                );
            }
            $newDate['products'] = $products;
            if (!isset($warehouse_id)) {
                $id = $this->invoice_items_model->addAdjustment($newDate);
                if ($id) {
                    set_alert('success', _l('added_successfully', _l('Quantity Adjustment')));
                    redirect(admin_url('invoice_items/quantity_adjustments'));
                }
            } else {
                $success = $this->invoice_items_model->updateAdjustment($newDate, $warehouse_id);
                if ($success == true) {
                    set_alert('success', _l('updated_successfully', _l('Quantity Adjustment')));
                }
                redirect(admin_url('invoice_items/quantity_adjustments'));
            }

        }
        if (isset($warehouse_id)) {
            $data['adjustment'] = $this->invoice_items_model->getAdjustment($warehouse_id);
        }
        $data['ajaxItems'] = false;
        if (total_rows('tblitems') <= ajax_on_total_items()) {
            $data['items'] = $this->invoice_items_model->get_grouped();
        } else {
            $data['items'] = [];
            $data['ajaxItems'] = true;
        }
        $this->load->model('warehouses_model');
        $data['warehouses'] = $this->warehouses_model->get();
        $this->load->view('admin/invoice_items/quantity_adjustment', $data);
    }

    public function final_stock_excel($id) {
        set_time_limit(0);
        ini_set('post_max_size','200M');
        ini_set('upload_max_filesize','200M');
        ini_set('max_execution_time','200M');
        ini_set('max_input_time','200M');
        ini_set('memory_limit','200M');


        $finalCsv = "finalized_stock_".date("Y-m-d").".xls";

        //header("Content-Type: text/plain");
        header("Content-Disposition: attachment; filename=\"$finalCsv\"");
        header("Content-Type: application/vnd.ms-excel");

        $this->db->where('stock_count_id', $id);
        $items = $this->db->get('tblstock_count_items')->result_array();
        $flag = false;
        foreach($items as $row) {
            $finalData = [
                'product_code' => $row['product_code'],
                'product_name' => $row['product_name'],
                'expected' => $row['expected'],
                'counted' => $row['counted'],
            ];
            if(!$flag) {
                echo implode("\t", array_keys($finalData)) . "\r\n";
                $flag = true;
            }
            array_walk($finalData, array($this, "cleanData"));
            echo implode("\t", array_values($finalData)) . "\r\n";
        }
        exit;
    }

    function cleanData(&$str)
    {
        // escape tab characters
        $str = preg_replace("/\t/", "\\t", $str);

        // escape new lines
        $str = preg_replace("/\r?\n/", "\\n", $str);

        // convert 't' and 'f' to boolean values
        if($str == 't') $str = 'TRUE';
        if($str == 'f') $str = 'FALSE';

        // force certain number/date formats to be imported as strings
        if(preg_match("/^0/", $str) || preg_match("/^\+?\d{8,}$/", $str) || preg_match("/^\d{4}.\d{1,2}.\d{1,2}/", $str)) {
            $str = "'$str";
        }

        // escape fields that include double quotes
        if(strstr($str, '"')) $str = '"' . str_replace('"', '""', $str) . '"';
    }

    public function stock_count($warehouse_id = NULL)
    {
        if (!has_permission('stock_counts', '', 'create') && !has_permission('stock_counts', '', 'edit')) {
            access_denied('stock_counts');
        }

        if ($this->input->post()) {
            $warehouse_id = $this->input->post('warehouse');
            $type = $this->input->post('type');
            $categories = $this->input->post('category') ? $this->input->post('category') : NULL;
            $brands = $this->input->post('brand') ? $this->input->post('brand') : NULL;
            $this->load->helper('string');
            $name = random_string('md5') . '.csv';
            $products = $this->invoice_items_model->getStockCountProducts($warehouse_id, $categories, $brands);
            $rw = 0;
            $items = [];
            foreach ($products as $product) {
                $items[] = array(
                    'product_code' => $product->description,
                    'product_name' => $product->long_description,
                    'expected' => $product->quantity,
                    'counted' => ''
                );
                $rw++;
            }
            if (!empty($items)) {
                $img_target_path = FCPATH . 'uploads/count_stock/';
                _maybe_create_upload_path($img_target_path);
                $csv_file = fopen($img_target_path = FCPATH . 'uploads/count_stock/' . $name, 'w');

                fprintf($csv_file, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($csv_file, array(_l('Product Code'), _l('Product Name'), _l('Expected'), _l('Counted')));
                foreach ($items as $item) {
                    array_walk($item, array($this, "cleanData"));
                    fputcsv($csv_file, $item);
                }
//                 file_put_contents('./files/'.$name, $csv_file);
//                 fwrite($csv_file, $txt);
                fclose($csv_file);
            } else {
                set_alert('warning', _l('No product found.'));
                redirect($_SERVER["HTTP_REFERER"]);
            }

            $date = date('Y-m-d H:s:i');

            $category_ids = '';
            $brand_ids = '';
            $category_names = '';
            $brand_names = '';
            if ($categories) {
                $r = 1;
                $s = sizeof($categories);
                foreach ($categories as $category_id) {
                    $category = $this->invoice_items_model->get_groups($category_id);
                    if ($r == $s) {
                        $category_names .= $category->name;
                        $category_ids .= $category->id;
                    } else {
                        $category_names .= $category->name . ', ';
                        $category_ids .= $category->id . ', ';
                    }
                    $r++;
                }
            }
            if ($brands) {
                $r = 1;
                $s = sizeof($brands);
                foreach ($brands as $brand_id) {
                    $brand = $this->invoice_items_model->get_brands($brand_id);
                    if ($r == $s) {
                        $brand_names .= $brand->name;
                        $brand_ids .= $brand->id;
                    } else {
                        $brand_names .= $brand->name . ', ';
                        $brand_ids .= $brand->id . ', ';
                    }
                    $r++;
                }
            }

            $data = array(
                'date' => $date,
                'warehouse_id' => $warehouse_id,
                'reference_no' => $this->input->post('reference_no'),
                'type' => $type,
                'categories' => $category_ids,
                'category_names' => $category_names,
                'brands' => $brand_ids,
                'brand_names' => $brand_names,
                'initial_file' => $name,
                'products' => $rw,
                'rows' => $rw,
                'created_by' => get_staff_user_id()
            );

            $id = $this->invoice_items_model->addStockCount($data);
            if ($id) {
                set_alert('success', _l('added_successfully', _l('Stock Count')));
                redirect(admin_url('invoice_items/stock_counts'));
            }


        }
        if (isset($warehouse_id)) {
            $data['adjustment'] = $this->invoice_items_model->getAdjustment($warehouse_id);
        }

        $data['items_groups'] = $this->invoice_items_model->get_groups();
        $data['items_brands'] = $this->invoice_items_model->get_brands();
        $this->load->model('warehouses_model');
        $data['warehouses'] = $this->warehouses_model->get();
        $this->load->view('admin/invoice_items/stock_count', $data);
    }

    function finalize_count($id)
    {
        $stock_count = $this->invoice_items_model->getStouckCountByID($id);
        if (!$stock_count || $stock_count->finalized) {
            set_alert('warning', _l('Stock count has been finalized'));
            redirect(admin_url('invoice_items/stock_counts'));
        }

        if ($this->input->post() && isset($_FILES['csv_file']['name']) && $_FILES['csv_file']['name'] != '') {
            $this->load->library('import/import_finalize_count', [], 'import');
            $_POST['stock_count_id'] = $id;
            $this->import->setDatabaseFields(array('product_code', 'count'));
            $this->import->setTemporaryFileLocation($_FILES['csv_file']['tmp_name'])
                ->setFilename($_FILES['csv_file']['name'])
                ->perform();
            set_alert('success', _l('Stock is finalized now.'));
            redirect(admin_url('invoice_items/stock_counts'));
        }

        $data['stock_count'] = $stock_count;
        $this->load->model('warehouses_model');
        $data['warehouse'] = $this->warehouses_model->get($stock_count->warehouse_id);
        $this->load->view('admin/invoice_items/finalize_count', $data);

    }

    public function quantity_adjustment_delete($warehouse_id)
    {
        if (has_permission('quantity_adjustments', '', 'delete')) {
            if ($this->invoice_items_model->delete_quantity_adjustment($warehouse_id)) {
                set_alert('success', _l('deleted', _l('Quantity Adjustment')));
            }
        }
        redirect(admin_url('invoice_items/quantity_adjustments'));
    }

    public function import()
    {
        if (!has_permission('items', '', 'create')) {
            access_denied('Items Import');
        }

        $this->load->library('import/import_items', [], 'import');

        $this->import->setDatabaseFields($this->db->list_fields('tblitems'))
            ->setCustomFields(get_custom_fields('items'));

        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }

        if ($this->input->post('items_import')
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {
            $this->import->setSimulation($this->input->post('simulate'))
                ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                ->setFilename($_FILES['file_csv']['name'])
                ->perform();

            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
            }
        }

        if ($this->input->post('new_items_import')
            && isset($_FILES['new_file_csv']['name']) && $_FILES['new_file_csv']['name'] != '') {

//            $this->import->setSimulation($this->input->post('simulate'))
//                ->setTemporaryFileLocation($_FILES['new_file_csv']['tmp_name'])
//                ->setFilename($_FILES['new_file_csv']['name'])
//                ->perform();

            $data = array();
            $memData = array();

            // Parse data from CSV file
            $csvData = $this->import->parse_csv($_FILES['new_file_csv']['tmp_name']);

            // Insert/update CSV data into database
            if(!empty($csvData)){
                foreach($csvData as $row){ $rowCount++;
print_r($row);
print_r($row['Brand']); die;
                    // Prepare data for DB insertion
                    $memData = array(
                        'name' => $row['Name'],
                        'email' => $row['Email'],
                        'phone' => $row['Phone'],
                        'status' => $row['Status'],
                    );

                    // Check whether email already exists in the database
                    $con = array(
                        'where' => array(
                            'email' => $row['Email']
                        ),
                        'returnType' => 'count'
                    );
                    $prevCount = $this->member->getRows($con);

                    if($prevCount > 0){
                        // Update member data
                        $condition = array('email' => $row['Email']);
                        $update = $this->member->update($memData, $condition);

                        if($update){
                            $updateCount++;
                        }
                    }else{
                        // Insert member data
                        $insert = $this->member->insert($memData);

                        if($insert){
                            $insertCount++;
                        }
                    }
                }

                // Status message with imported data count
                $notAddCount = ($rowCount - ($insertCount + $updateCount));
                $successMsg = 'Members imported successfully. Total Rows ('.$rowCount.') | Inserted ('.$insertCount.') | Updated ('.$updateCount.') | Not Inserted ('.$notAddCount.')';
                $this->session->set_userdata('success_msg', $successMsg);
            }
            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
            }
        }

        $data['title'] = _l('import');
        $this->load->view('admin/invoice_items/import', $data);
    }

    public function import_customer_products()
    {
        if (!has_permission('items', '', 'create')) {
            access_denied('Items Import');
        }

        $this->load->library('import/import_items', [], 'import');
        $this->import->requiredFields = ['userId','description','fab_no','item_group'];
        $this->import->setDatabaseFields(['userId','custom_invoice','description','long_description','fab_no','group_id'])
            ->setCustomFields(get_custom_fields('items'));

        if ($this->input->post('download_sample') === 'true') {
            $this->import->downloadSample();
        }

        if ($this->input->post()
            && isset($_FILES['file_csv']['name']) && $_FILES['file_csv']['name'] != '') {
            $this->import->setSimulation($this->input->post('simulate'))
                ->setTemporaryFileLocation($_FILES['file_csv']['tmp_name'])
                ->setFilename($_FILES['file_csv']['name'])
                ->perform_customer_product();

            $data['total_rows_post'] = $this->import->totalRows();

            if (!$this->import->isSimulation()) {
                set_alert('success', _l('import_total_imported', $this->import->totalImported()));
                redirect(admin_url('invoice_items/import_customer_products'));
            }
        }

        $data['title'] = _l('import').' Customer Product';
        $this->load->view('admin/invoice_items/import_customer_product', $data);
    }

    public function table()
    {
        if (!has_permission('items', '', 'view')) {
            ajax_access_denied();
        }
        $this->app->get_table_data('invoice_items');
    }

    public function rfr_table()
    {
        if (!has_permission('items', '', 'view')) {
            ajax_access_denied();
        }
        $this->app->get_table_data('rfr_items');
    }

    public function rfr_list(){
        if (!has_permission('items', '', 'view')) {
            access_denied('Invoice Items');
        }

        $this->load->model('taxes_model');
        $this->load->model('currencies_model');
        $data['currencies'] = $this->currencies_model->get();
        $data['expire_items'] = $this->invoice_items_model->get_expired_grouped();
        $data['base_currency'] = $this->currencies_model->get_base_currency();



        $data['title'] = _l('invoice_items');
        $this->load->view('admin/invoice_items/rfr_list', $data);
    }


    public function rfr_request(){
        if($this->input->post()){
            $data = $this->input->post();
            $itemsDetails = [];
            if(isset($data['ids'])){
                $data['ids'] = explode(',',$data['ids']);
                foreach ($data['ids'] as $id){
                    $rfr = $this->invoice_items_model->get_rfr(null, ['item_id'=>$id]);
                    if(count($rfr) < 1){
                        $rfr = $this->invoice_items_model->add_rfr([
                            'item_id'=>$id,
                            'mailto'=> $data['mailto']
                        ]);
                        $this->db->select('description,long_description,tblitems_groups.name as group_name,tblitems_brands.name as brand');
                        $this->db->from('tblitems');
                        $this->db->join('tblitems_groups', 'tblitems_groups.id = tblitems.group_id', 'left')->join('tblitems_brands', 'tblitems.brand=tblitems_brands.id', 'left');
                        $this->db->where('tblitems.id', $id);
                        $item = $this->db->get()->row();
                        $itemsDetails[] = $item->description.'|'.$item->long_description.'|'.$item->group_name.'|'.$item->brand;
                    }
                }
            }elseif(isset($data['item_id'])){
                $rfr = $this->invoice_items_model->get_rfr(null, ['item_id'=>$data['item_id']]);
                if(count($rfr) < 1){
                    $rfr = $this->invoice_items_model->add_rfr([
                        'item_id'=>$data['item_id'],
                        'mailto'=> $data['mailto']
                    ]);
                    $this->db->select('description,long_description,tblitems_groups.name as group_name,tblitems_brands.name as brand');
                    $this->db->from('tblitems');
                    $this->db->join('tblitems_groups', 'tblitems_groups.id = tblitems.group_id', 'left')->join('tblitems_brands', 'tblitems.brand=tblitems_brands.id', 'left');
                    $this->db->where('tblitems.id', $data['item_id']);
                    $item = $this->db->get()->row();
                    $itemsDetails[] = $item->description.'|'.$item->long_description.'|'.$item->group_name.'|'.$item->brand;
                }
            }
            if(!empty($itemsDetails)){
                $finalMessage = '';
                if($data['message']){
                    $finalMessage .=$data['message'];
                }

                $finalMessage .= '<br>'.implode('<br>',$itemsDetails);
                $this->load->config('email');
                // Simulate fake template to be parsed
                $template           = new StdClass();
                $template->message  = get_option('email_header') . $finalMessage . get_option('email_footer');
                $template->fromname = get_option('companyname') != '' ? get_option('companyname') : 'TEST';
                $template->subject  = 'New Request for Rate for Products';

                $template = parse_email_template($template);

                $this->email->initialize();
                if ($_FILES['attachment']["size"]  > 0) {
                    $this->email->attach($_FILES['attachment']['tmp_name'], 'attachment', $_FILES['attachment']['name']);
                }
                $this->email->set_newline(config_item('newline'));
                $this->email->set_crlf(config_item('crlf'));

                $this->email->from(get_option('smtp_email'), $template->fromname);
                $this->email->to($data['mailto']);
                $this->email->subject($template->subject);
                $this->email->message($template->message);
                if ($this->email->send(true)) {
                    set_alert('success', 'New RFR generated.');
//                do_action('smtp_test_email_success');
                } else {
                    set_alert('success', 'Error in sending mails.');

                }
            }
        }
        redirect(admin_url('invoice_items/rfr_list'));
    }

    public function update_rfr_price(){
        if($this->input->post()){
            $data = $this->input->post();
            $rfr = $this->invoice_items_model->get_rfr($data['rfr_id']);
            if(isset($rfr->item_id)){
                $this->db->select('margin');
                $this->db->from('tblitems');
                $this->db->where('id', $rfr->item_id);
                $item = $this->db->get()->row();
                if(isset($item->margin)){
                    $productCost  = $data['price'];
                    $rate= (float)$productCost + (((float)$productCost * (int)$item->margin) / 100);
                    $update = [
                        'product_cost' => $productCost,
                        'upto' => to_sql_date($data['upto']),
                        'rate' => $rate
                    ];
                    $this->db->where('id', $rfr->item_id);
                    $this->db->update('tblitems', $update);
                    $this->db->where('id', $data['rfr_id']);
                    $this->db->delete('tblrfr');
                    set_alert('success', 'Price updated successfully.');
                }else{
                    set_alert('error', 'Item not found.');
                }
            }else{
                set_alert('error', 'RFR not found.');
            }
        }
        redirect(admin_url('invoice_items'));
    }

    /* Edit or update items / ajax request /*/
    public function manage()
    {
        $this->load->model('warehouses_model');
        $warehouses = $this->warehouses_model->getAllWarehouses();
        if (has_permission('items', '', 'view')) {
            if ($this->input->post()) {
                $data = $this->input->post();
                if($data['itemid'] == ''){
                    if (total_rows('tblitems', [
                            'description' => $data['description'],
                        ]) > 0) {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Item alredy exists.',
                        ]);
                        exit;
                    }
                }


                if (isset($data['supplier'])) {
                    $data['supplier1'] = $data['supplier'];
                    unset($data['supplier']);
                } else {
                    $data['supplier1'] = '';
                }

                if (isset($data['supplier_2'])) {
                    $data['supplier2'] = $data['supplier_2'];
                    unset($data['supplier_2']);
                } else {
                    $data['supplier2'] = '';
                }

                if (isset($data['supplier_3'])) {
                    $data['supplier3'] = $data['supplier_3'];
                    unset($data['supplier_3']);
                } else {
                    $data['supplier3'] = '';
                }

                if (isset($data['supplier_4'])) {
                    $data['supplier4'] = $data['supplier_4'];
                    unset($data['supplier_4']);
                } else {
                    $data['supplier4'] = '';
                }

                if (isset($data['supplier_5'])) {
                    $data['supplier5'] = $data['supplier_5'];
                    unset($data['supplier_5']);
                } else {
                    $data['supplier5'] = '';
                }

                if (isset($data['manufacturing_item'])) {
                    if($data['manufacturing_item'] == ''){
                        $data['manufacturing_item'] = 'true';
                    } else{
                        $data['manufacturing_item'] = 'true';
                    }
                }

                if(isset($data['upto'])){
                    $data['upto'] = to_sql_date($data['upto']);
                }
                foreach ($warehouses as $warehouse) {
                    if ($this->input->post('wh_' . $warehouse->id)) {
                        $data['warehouse_qty'][] = array(
                            'warehouse_id' => $this->input->post('wh_' . $warehouse->id),
                            'quantity' => $this->input->post('wh_qty_' . $warehouse->id)
                        );
                        unset($data['wh_' . $warehouse->id]);
                        unset($data['wh_qty_' . $warehouse->id]);
                    }
                }
                if ($data['itemid'] == '') {
                    if (!has_permission('items', '', 'create')) {
                        header('HTTP/1.0 400 Bad error');
                        echo _l('access_denied');
                        die;
                    }
                    $id = $this->invoice_items_model->add($data);
                    $success = false;
                    $message = '';
                    if ($id) {
                        $success = true;
                        $message = _l('added_successfully', _l('invoice_item'));
                    }
                    echo json_encode([
                        'success' => $success,
                        'message' => $message,
                        'item' => $this->invoice_items_model->get($id),
                    ]);
                } else {
                    if (!has_permission('items', '', 'edit')) {
                        header('HTTP/1.0 400 Bad error');
                        echo _l('access_denied');
                        die;
                    }
                    $success = $this->invoice_items_model->edit($data);
                    $message = '';
                    if ($success) {
                        $message = _l('updated_successfully', _l('invoice_item'));
                    }
                    echo json_encode([
                        'success' => $success,
                        'message' => $message,
                    ]);
                }
            }
        }
    }

    public function add_group()
    {
        if ($this->input->post() && has_permission('items', '', 'create')) {
            $this->invoice_items_model->add_group($this->input->post());
            set_alert('success', _l('added_successfully', _l('item_group')));
        }
    }

    public function add_unit()
    {
        if ($this->input->post() && has_permission('items', '', 'create')) {
            $this->invoice_items_model->add_unit($this->input->post());
            set_alert('success', _l('added_successfully', _l('Item Unit')));
        }
    }

    public function add_brand()
    {
        if ($this->input->post() && has_permission('items', '', 'create')) {
            $this->invoice_items_model->add_brand($this->input->post());
            set_alert('success', 'Brand added successfully');
        }
    }

    public function update_group($id)
    {
        if ($this->input->post() && has_permission('items', '', 'edit')) {
            $this->invoice_items_model->edit_group($this->input->post(), $id);
            set_alert('success', _l('updated_successfully', _l('item_group')));
        }
    }

    public function update_brand($id)
    {
        if ($this->input->post() && has_permission('items', '', 'edit')) {
            $this->invoice_items_model->edit_brand($this->input->post(), $id);
            set_alert('success', 'Brand updated successfully');
        }
    }

    public function update_unit($id)
    {
        if ($this->input->post() && has_permission('items', '', 'edit')) {
            $this->invoice_items_model->edit_unit($this->input->post(), $id);
            set_alert('success', 'Unit updated successfully');
        }
    }

    public function delete_group($id)
    {
        if (has_permission('items', '', 'delete')) {
            if ($this->invoice_items_model->delete_group($id)) {
                set_alert('success', _l('deleted', _l('item_group')));
            }
        }
        redirect(admin_url('invoice_items?groups_modal=true'));
    }

    public function delete_brand($id)
    {
        if (has_permission('items', '', 'delete')) {
            if ($this->invoice_items_model->delete_brand($id)) {
                set_alert('success', 'Brand deleted successfully');
            }
        }
        redirect(admin_url('invoice_items?brands_modal=true'));
    }

    public function delete_unit($id)
    {
        if (has_permission('items', '', 'delete')) {
            if ($this->invoice_items_model->delete_unit($id)) {
                set_alert('success', 'Unit deleted successfully');
            }
        }
        redirect(admin_url('invoice_items?units_modal=true'));
    }

    /* Delete item*/
    public function delete($id)
    {
        if (!has_permission('items', '', 'delete')) {
            access_denied('Invoice Items');
        }

        if (!$id) {
            redirect(admin_url('invoice_items'));
        }

        $response = $this->invoice_items_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('invoice_item_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('invoice_item')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('invoice_item_lowercase')));
        }
        redirect(admin_url('invoice_items'));
    }

    public function search()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            echo json_encode($this->invoice_items_model->search($this->input->post('q')));
        }
    }

    public function mf_item_search()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            echo json_encode($this->invoice_items_model->mf_item_search($this->input->post('q')));
        }
    }

    /* Get item by id / ajax */
    public function get_item_by_id($id)
    {
        if ($this->input->is_ajax_request()) {
            $item = $this->invoice_items_model->get($id);

            $item->long_description = nl2br($item->long_description);
            $item->custom_fields_html = render_custom_fields('items', $id, [], ['items_pr' => true]);
            $item->custom_fields = [];

            $cf = get_custom_fields('items');

            foreach ($cf as $custom_field) {
                $val = get_custom_field_value($id, $custom_field['id'], 'items_pr');
                if ($custom_field['type'] == 'textarea') {
                    $val = clear_textarea_breaks($val);
                }
                $custom_field['value'] = $val;
                $item->custom_fields[] = $custom_field;
            }
            if(isset($item->quantity)){
                $item->quantity = get_stock_item_details_from_code($item->description);
            }
            if(isset($item->upto)){
                $item->upto = _d($item->upto);
            }
            $item->extraDiscount = 0;
            if(isset($item->upto) && (strtotime($item->upto) < time())){
                $item->rate = 0;
            }
            if ($this->input->get('customerId') != '' && $item->group_name != "ELGi(Lubricants)") {
                $this->db->select('price_hike, discount');
                $this->db->where('userid', $this->input->get('customerId'));
                $result = $this->db->get('tblclients')->row();
                if ($result->price_hike != 0 && $result->price_hike != '') {
                    $item->rate = round($item->rate + ($item->rate * $result->price_hike / 100), 2);
                }

                if ($result->discount != 0 && $result->discount != '') {

                    $item->extraDiscount = $result->discount;
                }
            }

            echo json_encode($item);
        }
    }

    public function validate_item_exist()
    {
        $isedit          = $this->input->post('isedit');
        $description = $this->input->post('description');
        if ($isedit) {
            if ($number == $original_number) {
                echo json_encode(true);
                die;
            }
        }
        if (total_rows('tblitems', [
                'description' => $description,
            ]) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }

    /* Get item by id / ajax */
    public function get_item_by_code($id)
    {
        if ($this->input->is_ajax_request()) {
            $item = $this->invoice_items_model->getProductByCode($id);
            $item->long_description = nl2br($item->long_description);
            echo json_encode($item);
        }
    }

    function wareHouseTable()
    {
        if ($this->input->is_ajax_request()) {
            $aColumns = [
                'name',
                'code',
                'quantity'
            ];
            $sIndexColumn = 'id';
            $sTable = 'tblwarehouses_products';

            $join = [
                'LEFT JOIN tblwarehouses ON tblwarehouses_products.warehouse_id = tblwarehouses.id'
            ];
            $where = [];
            array_push($where, 'AND tblwarehouses_products.product_id =' . $this->input->get('itemId'));

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where);
            $output = $result['output'];
            $rResult = $result['rResult'];
            foreach ($rResult as $aRow) {
                $row = [];
                $row[] = $aRow['name'] . " ({$aRow['code']})";
                $row[] = $aRow['quantity'];
                $output['aaData'][] = $row;
            }
            echo json_encode($output);
            die();
        }
    }

    function indentTable()
    {
        if ($this->input->is_ajax_request()) {
            $aColumns = [
                'purchase_id as purchase_id',
                '(quantity - quantity_balance) AS qty',
                'date'
            ];
            $sIndexColumn = 'id';
            $sTable = 'tblpurchaseitems';

            $join = [];
            $where = [];
            array_push($where, 'AND purchase_id IS NOT NULL and (quantity - quantity_balance) <> 0 and product_id =' . $this->input->get('itemId'));

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where);
            $output = $result['output'];
            $rResult = $result['rResult'];
            foreach ($rResult as $aRow) {
                $row = [];
                $row[] = '<a href="' . admin_url('purchases/list_purchases/' . $aRow['purchase_id']) . '" target="_blank">' . format_purchase_number($aRow['purchase_id']) . '</a>';
                $row[] = $aRow['qty'];
                $row[] = _d($aRow['date']);
                $output['aaData'][] = $row;
            }
            echo json_encode($output);
            die();
        }
    }

    function holdItemTable()
    {
        if ($this->input->is_ajax_request()) {
            $aColumns = [
                'qty',
                'rel_type',
                'rel_id',
            ];
            $sIndexColumn = 'id';
            $sTable = 'tblhold_items';

            $join = [];
            $where = [];
            array_push($where, 'AND product_id =' . $this->input->get('itemId'));

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where);
            $output = $result['output'];
            $rResult = $result['rResult'];
            foreach ($rResult as $aRow) {
                $row = [];
                if($aRow['rel_type'] == 'invoice'){
                    $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['rel_id']) . '" target="_blank">' . format_invoice_number($aRow['rel_id']) . '</a>';
                }else{
                    $row[] = '<a href="' . admin_url('purchaseorder/list_purchaseorder/' . $aRow['rel_id']) . '" target="_blank">' . format_purchaseorder_number($aRow['rel_id']) . '</a>';
                }
                $row[] = $aRow['qty'];
                $output['aaData'][] = $row;
            }
            echo json_encode($output);
            die();
        }
    }

    function getFabNos()
    {
        if ($this->input->is_ajax_request()) {
            $aColumns = [
                'fab_no'
            ];
            $sIndexColumn = 'id';
            $sTable = 'tblitems_in';
            $itemId = $this->input->get('itemId');
            $itemId = explode(' ',$itemId);
            if(isset($itemId[1])){
                $join = [];
                $where = [];
                array_push($where, 'AND description like "' . $itemId[1].'"');
                array_push($where, 'AND (fab_no is not null and fab_no <> "")');

                $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where,['id']);
                $output = $result['output'];
                $rResult = $result['rResult'];
                foreach ($rResult as $aRow) {
                    $row = [];
                    $row[] = '<a href="javascript:void(0);" onclick="openItemBom('.$aRow['id'].')">' . $aRow['fab_no'] . '</a>';
                    $output['aaData'][] = $row;
                }
                echo json_encode($output);
                die();
            }
        }
    }

    function getBOM()
    {
        $fabNo = $this->input->get('fab_no');
        if($fabNo){
            $this->db->like('fab_no', $fabNo);
            $result = $this->db->get('tblitems_in')->row();
            if (isset($result->bom)){
                echo $result->bom;
            }else{
                echo '';
            }
        }
    }
}
