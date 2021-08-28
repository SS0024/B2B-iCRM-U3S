<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Delivery_module_status_model extends CRM_Model
{
    

    public function __construct()
    {
        parent::__construct();

      
    }

    /**
     * Get client object based on passed clientid if not passed clientid return array of all clients
     * @param  mixed $id    client id
     * @param  array  $where
     * @return mixed
     */
    public function get()
    {
		
        $this->db->select();
        //$this->db->where($where);
        $this->db->order_by('id', 'asc');

        return $this->db->get('tbldeliverymodulestatus')->result_array();
    }

}
