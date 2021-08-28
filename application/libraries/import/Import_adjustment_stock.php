<?php

defined('BASEPATH') or exit('No direct script access allowed');
require_once(APPPATH . 'libraries/import/App_import.php');

class Import_adjustment_stock extends App_import
{
    protected $notImportableFields = [];

    protected $requiredFields = [];

    public function __construct()
    {
//        $this->notImportableFields = do_action('not_importable_leads_fields', ['id', 'source', 'assigned', 'status', 'dateadded', 'last_status_change', 'addedfrom', 'leadorder', 'date_converted', 'lost', 'junk', 'is_imported_from_email_integration', 'email_integration_uid', 'is_public', 'dateassigned', 'client_id', 'lastcontact', 'last_lead_status', 'from_form_id', 'default_language', 'hash']);

        $this->addImportGuidelinesInfo('Duplicate email rows won\'t be imported.', true);

        parent::__construct();
    }

    public function perform()
    {
//        $this->initialize();
        $tmpDir = $this->getTemporaryFileLocation();
        $this->ci->load->helper('string');
        $newFileName = random_string('md5').'.csv';
        $newFilePath =  FCPATH. 'uploads/count_stock/'.$newFileName;
        $this->ci->load->model('invoice_items_model');
        if (move_uploaded_file($tmpDir, $newFilePath)) {
            $this->tmpFileStoragePath = $newFilePath;
            $this->readFileRows();
            $databaseFields      = $this->getImportableDatabaseFields();
            $totalDatabaseFields = count($databaseFields);
            $rw = 2; $differences = 0; $matches = 0;
//            echo '<pre>';
            /*$q = $this->ci->db->query("SELECT DISTINCT(`tblitems_in`.`description`) as description FROM `tblitems_in`");
            if ($q->num_rows() > 0) {
                foreach (($q->result()) as $row) {
                    $innerCodes[] = "'".trim($row->description)."'";
                }
            }*/
            /*$sheetCodes = array_map(function ($d){ return "'".trim($d)."'"; },array_column($this->getRows(), 0));
            $productCodes = implode(', ',array_values(array_unique($sheetCodes)));
            $statement = "DELETE FROM `tblitems` WHERE description NOT IN ({$productCodes})";
            echo $statement;
            die();*/
            foreach ($this->getRows() as $rowNumber => $row) {
                $insert = [];
                for ($i = 0; $i < $totalDatabaseFields; $i++) {
                    $row[$i] = $this->checkNullValueAddedByUser($row[$i]);

                    $insert[$databaseFields[$i]] = $row[$i];
                }

                $insert = $this->trimInsertValues($insert);
                if (count($insert) > 0) {
                    $product = $this->ci->invoice_items_model->getProductByCode($insert['product_code']);
                    if($product){
                        $diff = $insert['count'] - $insert['expected'];
                        $products[] = array(
                            'product_id' => $product->id,
                            'type' => $this->checkNumber($diff),
                            'quantity' => abs($diff),
                            'warehouse_id' => $_POST['warehouse']
                        );
                    }
                    $this->incrementImported();
                }
            }
            $newDate['reference_no'] = isset($_POST['reference_no']) ? $_POST['reference_no'] : "";
            $newDate['warehouse_id'] = $_POST['warehouse'];
            $newDate['note'] = $_POST['note'];
            $newDate['products'] = $products;
            $id = $this->ci->invoice_items_model->addAdjustment($newDate);
        }
    }

    private function checkNumber($x) {
        if ($x > 0)
         return "addition";
        if ($x < 0)
         return "subtraction";
    }

    /**
     * Move the uploaded file into the corresponding temporary directory
     * @return boolean
     */
    private function moveUploadedFile()
    {
        $newFilePath = $this->tmpDir . $this->filename;

        if (move_uploaded_file($this->temporaryFileFromFormLocation, $newFilePath)) {
            $this->tmpFileStoragePath = $newFilePath;

            return true;
        }

        return false;
    }

    protected function failureRedirectURL()
    {
        return admin_url('Invoice_items/finalize_count');
    }
}
