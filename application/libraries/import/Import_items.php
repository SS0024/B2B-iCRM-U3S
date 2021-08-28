<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/import/App_import.php');

class Import_items extends App_import
{
    protected $notImportableFields = ['id'];

    public $requiredFields = ['description', 'rate'];

    public function __construct()
    {
        $this->addItemsGuidelines();

        parent::__construct();
    }

    private function addItemsGuidelines()
    {
        $this->addImportGuidelinesInfo('In the column <b>Tax</b> and <b>Tax2</b>, you <b>must</b> add either the <b>TAX NAME or the TAX ID</b>, which you can get them by navigating to <a href="' . admin_url('taxes') . '" target="_blank">Setup->Finance->Taxes</a>.');
        $this->addImportGuidelinesInfo('In the column <b>Group</b>, you <b>must</b> add either the <b>GROUP NAME or the GROUP ID</b>, which you can get them by clicking <a href="' . admin_url('invoice_items?groups_modal=true') . '" target="_blank">here</a>.');
        $this->addImportGuidelinesInfo('In the column <b>UserId</b>, you <b>must</b> add either the <b>CUSTOMER NAME or the CUSTOMER ID</b>, which you can get them by clicking <a href="' . admin_url('clients') . '" target="_blank">here</a>.');
    }

    public function perform()
    {
        $this->initialize();

        $databaseFields      = $this->getImportableDatabaseFields();
        $totalDatabaseFields = count($databaseFields);

        foreach ($this->getRows() as $rowNumber => $row) {
            $insert = [];
            for ($i = 0; $i < $totalDatabaseFields; $i++) {
                $row[$i] = $this->checkNullValueAddedByUser($row[$i]);

                if ($databaseFields[$i] == 'description' && $row[$i] == '') {
                    $row[$i] = '/';
                } elseif ($databaseFields[$i] == 'upto') {
                    $row[$i] = to_sql_date($row[$i]);
                } elseif (_startsWith($databaseFields[$i], 'rate') && !is_numeric($row[$i])) {
                    $row[$i] = 0;
                } elseif ($databaseFields[$i] == 'group_id') {
                    $row[$i] = $this->groupValue($row[$i]);
                } elseif ($databaseFields[$i] == 'tax' || $databaseFields[$i] == 'tax2') {
                    $row[$i] = $this->taxValue($row[$i]);
                }

                $insert[$databaseFields[$i]] = $row[$i];

            }

            $insert = $this->trimInsertValues($insert);

            if (count($insert) > 0) {
               $this->incrementImported();


                if (!empty($insert['tax2']) && empty($insert['tax'])) {
                    $insert['tax']  = $insert['tax2'];
                    $insert['tax2'] = 0;
                }

                $id = null;

                if (!$this->isSimulation()) {
                    if(isset($insert['margin']) && (int)$insert['margin'] != 0){
                        $insert['rate'] = (float)$insert['product_cost'] + (((float)$insert['product_cost'] * (float)$insert['margin']) / 100);
                    }
                    elseif(isset($insert['rate']) && (int)$insert['rate'] != 0){
                        $insert['margin'] = ((float)$insert['rate'] - (float)$insert['product_cost']) * (100/(float)$insert['product_cost']);
                    }


                    if(isset($insert['description']) && !empty($insert['description'])){
                        print_r($insert['description']); die;
                        $this->ci->db->select('*');
                        $this->ci->db->where('description', $insert['description']);
                        $_item = $this->ci->db->get('tblitems')->row();
                        if(isset($_item->id)){
                            $id = $_item->id;
                            $this->ci->db->where('id', $id);
                            $this->ci->db->update('tblitems', $insert);
                        }else{
                            $this->ci->db->insert('tblitems', $insert);
                            $id = $this->ci->db->insert_id();
                        }
                    }else{
                        $this->ci->db->insert('tblitems', $insert);
                        $id = $this->ci->db->insert_id();
                    }
                } else {
                    $this->simulationData[$rowNumber] = $this->formatValuesForSimulation($insert);
                }

                $this->handleCustomFieldsInsert($id, $row, $i, $rowNumber, 'items_pr');
            }

            if ($this->isSimulation() && $rowNumber >= $this->maxSimulationRows) {
                break;
            }
        }
    }

    public function perform_customer_product()
    {
        $this->initialize();

        $databaseFields      = $this->getImportableDatabaseFields();
        $totalDatabaseFields = count($databaseFields);

        foreach ($this->getRows() as $rowNumber => $row) {
            $insert = [];
            for ($i = 0; $i < $totalDatabaseFields; $i++) {
                $row[$i] = $this->checkNullValueAddedByUser($row[$i]);
                if ($databaseFields[$i] == 'description' && $row[$i] == '') {
                    $row[$i] = '/';
                } elseif (_startsWith($databaseFields[$i], 'rate') && !is_numeric($row[$i])) {
                    $row[$i] = 0;
                } elseif ($databaseFields[$i] == 'group_id') {
                    $row[$i] = $this->groupValue($row[$i]);
                } elseif ($databaseFields[$i] == 'userId') {
                    $row[$i] = $this->customerValue($row[$i]);
                } elseif ($databaseFields[$i] == 'tax' || $databaseFields[$i] == 'tax2') {
                    $row[$i] = $this->taxValue($row[$i]);
                }
                $insert[$databaseFields[$i]] = $row[$i];
            }
            $insert = $this->trimInsertValues($insert);
            if (count($insert) > 0) {
                $this->incrementImported();

                if (!empty($insert['tax2']) && empty($insert['tax'])) {
                    $insert['tax']  = $insert['tax2'];
                    $insert['tax2'] = 0;
                }
                $insert['rel_type'] = 'invoice';
                $insert['rel_id'] = '0';
                $id = null;
                $this->ci->db->insert('tblitems_in', $insert);
                $id = $this->ci->db->insert_id();

                $this->handleCustomFieldsInsert($id, $row, $i, $rowNumber, 'items');
            }

            if ($this->isSimulation() && $rowNumber >= $this->maxSimulationRows) {
                break;
            }
        }
    }


    // new exported file import code


    private $fields;
    private $separator = ';';
    private $enclosure = '"';
    private $max_row_size = 4096;

    public function parse_csv($filepath){
        // Open uploaded CSV file with read-only mode
        $csvFile = fopen($filepath, 'r');

        // Get Fields and values
        $this->fields = fgetcsv($csvFile, $this->max_row_size, $this->separator, $this->enclosure);
        $keys_values = explode(',', $this->fields[0]);
        $keys = $this->escape_string($keys_values);

        $databaseFields      = $this->getImportableDatabaseFields();
        print_r($databaseFields);
print_r($keys); die;
        // Store CSV data in an array
        $csvData = array();
        $i = 1;
        while(($row = fgetcsv($csvFile, $this->max_row_size, $this->separator, $this->enclosure)) !== FALSE){
            // Skip empty lines
            if($row != NULL){
                $values = explode(',', $row[0]);
                if(count($keys) == count($values)){
                    $arr        = array();
                    $new_values = array();
                    $new_values = $this->escape_string($values);
                    for($j = 0; $j < count($keys); $j++){
                        if($keys[$j] != ""){
                            $arr[$keys[$j]] = $new_values[$j];
                        }
                    }
                    $csvData[$i] = $arr;
                    $i++;
                }
            }
        }
        // Close opened CSV file
        fclose($csvFile);

        return $csvData;

    }

    function escape_string($data){
        $result = array();
        foreach($data as $row){
            $result[] = str_replace('"', '', $row);
        }
        return $result;
    }


    private function groupValue($value)
    {
        if ($value != '') {
            if (!is_numeric($value)) {
                $group = $this->getGroupBy('name', $value);
                $value = $group ? $group->id : 0;
            }
        } else {
            $value = 0;
        }

        return $value;
    }

    private function getGroupBy($field, $idOrName)
    {
        $this->ci->db->where($field, $idOrName);

        return $this->ci->db->get('tblitems_groups')->row();
    }

    private function customerValue($value)
    {
        if ($value != '') {
            if (!is_numeric($value)) {
                $group = $this->getCustomerBy('company', $value);
                $value = $group ? $group->userid : 0;
            }
        } else {
            $value = 0;
        }

        return $value;
    }

    private function getCustomerBy($field, $idOrName)
    {
        $this->ci->db->where($field, $idOrName);

        return $this->ci->db->get('tblclients')->row();
    }

    private function taxValue($value)
    {
        if ($value != '') {
            if (!is_numeric($value)) {
                $tax   = $this->getTaxBy('name', $value);
                $value = $tax ? $tax->id : 0;
            }
        } else {
            $value = 0;
        }

        return $value;
    }

    private function getTaxBy($field, $idOrName)
    {
        $this->ci->db->where($field, $idOrName);

        return $this->ci->db->get('tbltaxes')->row();
    }

    private function formatValuesForSimulation($values)
    {
        foreach ($values as $column => $val) {
            if ($column == 'group_id' && !empty($val) && is_numeric($val)) {
                $group = $this->getGroupBy('id', $val);
                if ($group) {
                    $values[$column] = $group->name;
                }
            } elseif (($column == 'tax' || $column == 'tax2') && !empty($val) && is_numeric($val)) {
                $tax = $this->getTaxBy('id', $val);
                if ($tax) {
                    $values[$column] = $tax->name . ' (' . $tax->taxrate . '%)';
                }
            }
        }

        return $values;
    }

    public function formatFieldNameForHeading($field)
    {
        $this->ci->load->model('currencies_model');

        if (strtolower($field) == 'group_id') {
            return 'Group';
        } elseif (_startsWith($field, 'rate')) {
            $str = 'Rate - ';
            // Base currency
            if ($field == 'rate') {
                $str .= $this->ci->currencies_model->get_base_currency()->name;
            } else {
                $str .= $this->ci->currencies_model->get(strafter($field, 'rate_currency_'))->name;
            }

            return $str;
        }

        return parent::formatFieldNameForHeading($field);
    }

    protected function failureRedirectURL()
    {
        return admin_url('invoice_items/import');
    }
}
