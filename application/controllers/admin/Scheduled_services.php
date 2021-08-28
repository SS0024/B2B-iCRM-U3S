<?php

defined('BASEPATH') or exit('No direct script access allowed');
class Scheduled_services extends Admin_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('Scheduled_services_model');
    }

    /* List all available items */
    public function index()
    {
        if (!has_permission('scheduled_services', '', 'view')) {
            access_denied('Scheduled Services');
        }

        $data['scheduled_services'] = $this->Scheduled_services_model->get_services(); 

        $data['title'] = _l('Scheduled Services');
        $this->load->view('admin/scheduled_services/services', $data);
    }

    public function table()
    {
        if (!has_permission('scheduled_services', '', 'view')) {
            ajax_access_denied();
        }
        $this->app->get_table_data('scheduled_services');
    }

    public function new_arrivals()
    {
        $data['list_of_machines'] = $this->Scheduled_services_model->get_machines();
        $data['list_of_consumable'] = $this->Scheduled_services_model->get_consumables();
        $data['scheduled_services'] = $this->Scheduled_services_model->get_services();

        $data['title'] = _l('New Arrivals');
        $this->load->view('admin/scheduled_services/new_arrivals', $data);

    }

    public function add_type_of_consumable()
    {
        if ($this->input->post() && has_permission('scheduled_services', '', 'create')) {
            $this->Scheduled_services_model->add_consumable($this->input->post());
            set_alert('success', _l('added_successfully', _l('Consumable')));
        }
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function add_type_of_machine()
    {
        if ($this->input->post() && has_permission('scheduled_services', '', 'create')) {
            $this->Scheduled_services_model->add_machine($this->input->post());
            set_alert('success', _l('added_successfully', _l('Machine')));
        }

    }

    public function update_type_of_machine($id)
    {
        if ($this->input->post() && has_permission('scheduled_services', '', 'edit')) {
            $this->Scheduled_services_model->edit_machine($this->input->post(), $id);
            set_alert('success', _l('updated_successfully', _l('Machine')));
        }
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function update_type_of_consumable($id)
    {
        if ($this->input->post() && has_permission('scheduled_services', '', 'edit')) {
            $this->Scheduled_services_model->edit_consumable($this->input->post(), $id);
            set_alert('success', _l('updated_successfully', _l('Consumable')));
        }
    }

    public function update_column_details()
    {
        if ($this->input->post() && has_permission('scheduled_services', '', 'edit')) {
            $data = $this->input->post();
            if ($data['val'] != ''){
                if($data['column'] == 'installation_date'){
                    $data['val'] = to_sql_date($data['val']);
                }
                $updateData =  [
                    $data['column'] => $data['val']
                ];
                if($data['column'] == 'running_hpd' || $data['column'] == 'running_hpy' || $data['column'] == 'running_days'){
                    $this->db->select('running_days,running_hpd,running_hpy');
                    $this->db->from('tblitems_in');
                    $this->db->where('id', $data['id']);
                    $this->db->where('rel_type', 'invoice');
                    $item = $this->db->get()->row();
                    $runningDays = isset($item->running_days) && $item->running_days != 0 ? $item->running_days : 365;
                    if($data['column'] == 'running_hpd'){
                        $updateData =[
                            'running_days'=>$runningDays,
                            'running_hpd'=> $data['val'],
                            'running_hpy'=> round(($data['val'] * $runningDays),0),
                        ];
                    }elseif ($data['column'] == 'running_hpy'){
                        $updateData =[
                            'running_days'=>$runningDays,
                            'running_hpy'=> $data['val'],
                            'running_hpd'=> round(($data['val'] / $runningDays),0),
                        ];
                    }elseif ($data['column'] == 'running_days'){
                        $runningDays = $data['column'] == 'running_days' ? $data['val'] : 365;
                        $runningHpd = isset($item->running_hpd) && $item->running_hpd != 0 ? $item->running_hpd : 0;
                        $updateData =[
                            'running_days'=>$runningDays,
                            'running_hpy'=> round(($runningHpd * $runningDays),0),
                            'running_hpd'=> $runningHpd,
                        ];
                    }
                }
                $this->db->where('id', $data['id']);
                $this->db->update('tblitems_in',$updateData);
                $this->db->select('*');
                $this->db->from('tblitems_in');
                $this->db->where('id', $data['id']);
                $this->db->where('rel_type', 'invoice');
                $item = $this->db->get()->row();
                echo json_encode($item);
                die();
            }
        }
    }

    public function delete_type_of_machine($id)
    {
        if (has_permission('scheduled_services', '', 'delete')) {
            if ($this->Scheduled_services_model->delete_machine($id)) {
                set_alert('success', _l('deleted', _l('Machine')));
            }
        }
        redirect(admin_url('scheduled_services/new_arrivals?machine_modal=true'));
    }

    public function delete_consumable($id)
    {
        if (has_permission('scheduled_services', '', 'delete')) {
            if ($this->Scheduled_services_model->delete_consumable($id)) {
                set_alert('success', _l('deleted', _l('Consumable')));
            }
        }
        redirect($_SERVER["HTTP_REFERER"]);
    }

    public function getMachineById()
    {
        $machine_id = $this->input->post("machineTypeId");
        $machineDetails = $this->Scheduled_services_model->get_machine_by_id($machine_id);
        echo json_encode(array("machine_type"=>$machineDetails->machine_type, "avg_running_hr_per_day"=>$machineDetails->avg_running_hr_per_day,"avg_running_hr_per_year"=>$machineDetails->avg_running_hr_per_year));
        exit;
    }

    public function add_machine_scheduled_services()
    {
        echo "Redirected";
        exit;
       $serviceArr = array();
       $budgetInINRSch = $this->input->post("budget_in_inr_sch");
       $schSerArr = $this->input->post("service_val");
       $schHoursArr = $this->input->post("hours_sch_service");
       $schDaysArr = $this->input->post("days_sch_service");

        if (isset($schSerArr) && isset($schHoursArr) && isset($schDaysArr)) {           
            if (count($schSerArr) > 0 ) {
                $i=0;
                foreach ($schSerArr as $keySer => $valSer) {
                    if(!empty($valSer)){
                        $serviceArr[$i]['service'] = $valSer;
                        $i++;                        
                    }
                }
            }
            
            if (count($schHoursArr) > 0) {
                $j=0;
                foreach ($schHoursArr as $keyHr => $valHr) {
                    if(!empty($valHr)){
                        $serviceArr[$j]['hours'] = $valHr;
                        $j++;                       
                    }
                }
            }

            if (count($schDaysArr) > 0) {
                $k=0;
                foreach ($schDaysArr as $keyDays => $valDays) {
                    if(!empty($valDays)){
                        $serviceArr[$k]['days'] = $valDays;
                        $k++;
                    }                
                }
            }          
        }

        $chkSchSer = $this->Scheduled_services_model->add_machine_scheduled_services($serviceArr);
        echo "<pre>";
        print_r($serviceArr);
        exit;
       

    }
    
    

    
}
