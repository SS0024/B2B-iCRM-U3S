<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Warehouses_model extends CRM_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array $_POST data
     * @return integer
     * Add new department
     */
    public function add($data)
    {
        $data = do_action('before_department_added', $data);
        $this->db->insert('tblwarehouses', $data);
        $insert_id = $this->db->insert_id();
        if ($insert_id) {
            do_action('after_department_added', $insert_id);
            logActivity('New warehouse Added [' . $data['name'] . ', ID: ' . $insert_id . ']');
        }

        return $insert_id;
    }

    /**
     * @param  array $_POST data
     * @param  integer ID
     * @return boolean
     * Update department to database
     */
    public function update($data, $id)
    {
        $dep_original = $this->get($id);
        if (!$dep_original) {
            return false;
        }
        $hook_data['data'] = $data;
        $hook_data['id']   = $id;
        $hook_data         = do_action('before_department_updated', $hook_data);
        $data              = $hook_data['data'];
        $id                = $hook_data['id'];

        if ($data['email'] == '') {
            $data['email'] = null;
        }

        $this->db->where('id', $id);
        $this->db->update('tblwarehouses', $data);
        if ($this->db->affected_rows() > 0) {
            logActivity('Warehouse Updated [Name: ' . $data['name'] . ', ID: ' . $id . ']');

            return true;
        }

        return false;
    }

    /**
     * @param  integer ID (optional)
     * @param  boolean (optional)
     * @return mixed
     * Get department object based on passed id if not passed id return array of all departments
     * Second parameter is to check if the request is coming from clientarea, so if any departments are hidden from client to exclude
     */
    public function get($id = false, $clientarea = false)
    {
        if (is_numeric($id)) {
            $this->db->where('id', $id);

            return $this->db->get('tblwarehouses')->row();
        }

        $departments = $this->object_cache->get('warehouses');

        if (!$departments && !is_array($departments)) {
            $departments = $this->db->get('tblwarehouses')->result_array();
            $this->object_cache->add('warehouses', $departments);
        }

        return $departments;
    }

    /**
     * @param  integer ID
     * @return mixed
     * Delete department from database, if used return array with key referenced
     */
    public function delete($id)
    {
        $id      = do_action('before_delete_department', $id);
        $current = $this->get($id);
        do_action('before_department_deleted', $id);
        $this->db->where('id', $id);
        $this->db->delete('tblwarehouses');
        if ($this->db->affected_rows() > 0) {
            logActivity('Warehouses Deleted [ID: ' . $id . ']');

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
}
