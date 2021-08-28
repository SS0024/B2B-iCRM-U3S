<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Delivery_module_model extends CRM_Model
{
    

    public function __construct()
    {
        parent::__construct();

      
    }

   
       /**
     * Add new expense
     * @param mixed $data All $_POST data
     * @return  mixed
     */
    public function add($data)
    {
		
        $data['date'] = to_sql_date($data['date']);
        $data['note'] = nl2br($data['note']);		
        $data['address'] = nl2br($data['address']);		
       

        //$data['addedfrom'] = get_staff_user_id();
        //$data['dateadded'] = date('Y-m-d H:i:s');
		
		/* if(empty($data['delivered_by'])){
			$data['delivered_by'] = 0;
		} */
		
		
        $this->db->insert('tbldeliverymodules', $data);
        $insert_id = $this->db->insert_id();
		
        if ($insert_id) {
            //logActivity('New Expense Added [' . $insert_id . ']');

            return $insert_id;
        }

        return false;
    }
	
	
	public function get_by_invoice_id($invoice_id = 0){
		$this->db->select();
		$this->db->where('invoice_id', $invoice_id);
		$delivery_detail = $this->db->get('tbldeliverymodules')->row();
		
		 if ($delivery_detail) {
			$delivery_detail->attachment            = '';
			$delivery_detail->filetype              = '';
			$delivery_detail->attachment_added_from = 0;
			
			//echo $delivery_detail->id;
			$this->db->where('rel_id', $delivery_detail->id);
			$this->db->where('rel_type', 'delivery_modules');
			$file = $this->db->get('tblfiles')->row();
			// echo $this->db->last_query();
			//echo "here";
			//print_r($file);die;
			if ($file) {
				$delivery_detail->attachment            = $file->file_name;
				$delivery_detail->filetype              = $file->filetype;
				$delivery_detail->attachment_added_from = $file->staffid;
			}
		 }
		return $delivery_detail;
	}
	
	public function get_delivery_modules_by_invoice_id($invoice_id = 0){
			$this->db->select('*,tbldeliverymodules.address as delivery_module_address,tbldeliverymodulestatus.name as dstatusname,tblfiles.rel_type,tblfiles.file_name,tblfiles.filetype,tblfiles.rel_id');
			$this->db->from('tbldeliverymodules');
			$this->db->join('tblclients', 'tbldeliverymodules.customer_id = tblclients.userid', 'left');
			$this->db->join('tbldeliverymodulestatus', 'tbldeliverymodules.status_id = tbldeliverymodulestatus.id', 'left');
			$this->db->join('tblstaff', 'tbldeliverymodules.delivered_by = tblstaff.staffid', 'left');
			$this->db->join('tblfiles', 'tbldeliverymodules.id = tblfiles.rel_id AND tblfiles.rel_type = "delivery_modules"', 'left');
			$this->db->where('invoice_id', $invoice_id);
			$deliverymodules = $this->db->get()->result_array();
			
			return $deliverymodules;
	}
	
	public function get_all_delivery_modules(){
			$this->db->select('*,tbldeliverymodules.address as delivery_module_address,tbldeliverymodulestatus.name as dstatusname,tblfiles.rel_type,tblfiles.file_name,tblfiles.filetype,tblfiles.rel_id');
			$this->db->from('tbldeliverymodules');
			$this->db->join('tblclients', 'tbldeliverymodules.customer_id = tblclients.userid', 'left');
			$this->db->join('tbldeliverymodulestatus', 'tbldeliverymodules.status_id = tbldeliverymodulestatus.id', 'left');
			$this->db->join('tblstaff', 'tbldeliverymodules.delivered_by = tblstaff.staffid', 'left');
			$this->db->join('tblfiles', 'tbldeliverymodules.id = tblfiles.rel_id AND tblfiles.rel_type = "delivery_modules"', 'left');
			// $this->db->where('invoice_id', $invoice_id); 
			$deliverymodules = $this->db->get()->result_array();
			
			return $deliverymodules;
	}
	
	 /**
     * Update expense
     * @param  mixed $data All $_POST data
     * @param  mixed $id   expense id to update
     * @return boolean
     */
    public function update($data, $id)
    {
        $affectedRows = 0;
        $data['date'] = to_sql_date($data['date']);
        $data['note'] = nl2br($data['note']);
        $this->db->where('id', $id);
        $this->db->update('tbldeliverymodules', $data);
        if ($this->db->affected_rows() > 0) {
            //logActivity('Expense Updated [' . $id . ']');
            $affectedRows++;
        }
        if ($affectedRows > 0) {
            return true;
        }

        return false;
    }
	
	public function get_invoice_delivery_status($invoice_id = 0){
		
		$this->db->select('tbldeliverymodulestatus.name');
        $this->db->from('tbldeliverymodules');
        $this->db->join('tbldeliverymodulestatus', 'tbldeliverymodules.status_id = tbldeliverymodulestatus.id', 'left');
		$this->db->where('tbldeliverymodules.invoice_id', $invoice_id);
		$invoice_status = $this->db->get()->row();
		return $invoice_status;
	}
	
	
	 public function delete_delivery_module_attachment($id)
    {
        if (is_dir(get_upload_path_by_type('delivery_modules') . $id)) {
            if (delete_dir(get_upload_path_by_type('delivery_modules') . $id)) {
                $this->db->where('rel_id', $id);
                $this->db->where('rel_type', 'delivery_modules');
                $this->db->delete('tblfiles');
               // logActivity('Expense Receipt Deleted [ExpenseID: ' . $id . ']');

                return true;
            }
        }

        return false;
    }

	
}
