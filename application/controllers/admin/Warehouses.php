<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Warehouses extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('warehouses_model');

        if (!is_admin()) {
            access_denied('Warehouse');
        }
    }

    /* List all departments */
    public function index()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data('warehouses');
        }
        $data['title'] = _l('warehouses');
        $this->load->view('admin/warehouses/manage', $data);
    }

    /* Delete department from database */

    public function warehouse($id = '')
    {
        if ($this->input->post()) {
            $message = '';
            $data = $this->input->post();

            if (!$this->input->post('id')) {
                $id = $this->warehouses_model->add($data);
                if ($id) {
                    $success = true;
                    $message = _l('added_successfully', _l('warehouse'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message
                ]);
            } else {
                $id = $data['id'];
                unset($data['id']);
                $success = $this->warehouses_model->update($data, $id);
                if ($success) {
                    $message = _l('updated_successfully', _l('warehouse'));
                }
                echo json_encode([
                    'success' => $success,
                    'message' => $message
                ]);
            }
            die;
        }
    }

    public function delete($id)
    {
        if (!$id) {
            redirect(admin_url('warehouses'));
        }
        $response = $this->warehouses_model->delete($id);
        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('is_referenced', _l('warehouse_lowercase')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('warehouse')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('warehouse_lowercase')));
        }
        redirect(admin_url('warehouses'));
    }
}
