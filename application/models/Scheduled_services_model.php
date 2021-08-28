<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Scheduled_services_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get invoice item by ID
     * @param  mixed $id
     * @return mixed - array if not passed id, object if id passed
     */
    public function get_services()
    {
        
        $this->db->select('*');
        $this->db->from('tblitems_in');
        $this->db->where('rel_type', 'invoice');

        return $this->db->get()->result_array();
    }

    public function add_machine($data)
    {
        $this->db->insert('tbltypeofmachines', $data);
        logActivity('Machine Created [Name: ' . $data['machine_type'] . ']');

        return $this->db->insert_id();
    }

    public function add_consumable($data)
    {
        $this->db->insert('tblconsumables', $data);
        logActivity('Consumable Created [Name: ' . $data['title'] . ']');

        return $this->db->insert_id();
    }

    public function edit_machine($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('tbltypeofmachines', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Machine Updated [Name: ' . $data['machine_type'] . ']');

            return true;
        }

        return false;
    }

    public function edit_consumable($data, $id)
    {
        $this->db->where('id', $id);
        $this->db->update('tblconsumables', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Consumable Updated [Name: ' . $data['machine_type'] . ']');

            return true;
        }

        return false;
    }

    /*public function delete_machine($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('tbltypeofmachines');
        if ($this->db->affected_rows() > 0) {
            logActivity('Machine Deleted [Name: ' . $data['machine_type'] . ']');

            return true;
        }

        return false;
    }*/

    public function delete_machine($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('tbltypeofmachines');
        if ($this->db->affected_rows() > 0) {
            /*$this->db->where('relid', $id);
            $this->db->where('fieldto', 'items_pr');
            $this->db->delete('tblcustomfieldsvalues');*/

            logActivity('Machine Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    public function delete_consumable($id)
    {
        $this->db->where('id', $id);
        $this->db->delete('tblconsumables');
        if ($this->db->affected_rows() > 0) {
            /*$this->db->where('relid', $id);
            $this->db->where('fieldto', 'items_pr');
            $this->db->delete('tblcustomfieldsvalues');*/

            logActivity('Consumable Deleted [ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    public function get_machines()
    {        
        $this->db->select('*');
        $this->db->from('tbltypeofmachines');
        $this->db->where('status', 1);
        $this->db->order_by('machine_type', 'asc');

        return $this->db->get()->result_array();
    }

    public function get_consumables()
    {
        $this->db->select('*');
        $this->db->from('tblconsumables');
        $this->db->order_by('title', 'asc');
        return $this->db->get()->result_array();
    }

    public function get_machine_by_id($id)
    {        
        $this->db->select('*');
        $this->db->from('tbltypeofmachines');
        $this->db->where('id', $id);
        return $this->db->get()->row();
    }

    /*public function add_machine_scheduled_services($data)
    {
        foreach ($data as $key => $val) {
            
            $this->db->insert('tblscheduledservices', $val);
        
        }
        
        logActivity('Service Scheduled [Service: ' . $data['service'] . ']');
        return $this->db->insert_id();
    }*/

    


}
