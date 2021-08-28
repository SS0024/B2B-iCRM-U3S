<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'tblitems_in.custom_invoice',
    'tblitems_in.description as description',
    'tblitems_in.long_description as long_description',
    't3.name as group_name',
    'fab_no',
    'running_days',
    'running_hpd',
    'running_hpy',
    'installation_date',
    'type_of_machine',
    'tblitems_in.id as id',
    ];
$sIndexColumn = 'id';
$sTable       = 'tblitems_in';

$join = [
    'LEFT JOIN tblinvoices t1 ON t1.id = tblitems_in.rel_id',
    'LEFT JOIN tblcontracts t2 ON tblitems_in.id = t2.item_id',
    'LEFT JOIN tblitems_groups t3 ON t3.id = tblitems_in.group_id',
    ];

$custom_fields = get_custom_fields('items');
$divisionTable = '';
foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    if($field['slug'] == 'items_dvision'){
        $divisionTable = 'ctable_' . $key;
    }
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tblitems_in.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="items" AND ctable_' . $key . '.fieldid=' . $field['id']);
}


$additionalSelect = [
    't1.number',
    't1.id as invoice_id',
    't2.id as contract_id'
    ];

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}
//
$where = ['AND tblitems_in.rel_type = "invoice" '.( isset($_GET['id'])? " and (tblitems_in.userId={$_GET['id']} OR t1.clientid = {$_GET['id']})" : '')
    ];

$filter = [];
if ($this->ci->input->post('is_unit')) {
    array_push($filter, 'AND t3.name IN ("ELGi(VAYU)", "EPSAC(EG 11-75)", "EPSAC(EN Unit)", "Recip(HV/HP)", "RECIP(Industrial)", "RECIP(LG Series)", "ATS UNIT","DPSAC UNIT","LEPSAC unit","GREAVES UNIT")');
}
if ($this->ci->input->post('is_spares')) {
    array_push($filter, 'AND t3.name NOT IN ("ELGi(VAYU)", "EPSAC(EG 11-75)", "EPSAC(EN Unit)", "Recip(HV/HP)", "RECIP(Industrial)", "RECIP(LG Series)", "ATS UNIT","DPSAC UNIT","LEPSAC unit","GREAVES UNIT")');
}

if (count($filter) > 0) {
    array_push($where, ' AND (' . prepare_dt_filter($filter) . ')');
}

//(((tblitems_in.fab_no IS NULL OR tblitems_in.fab_no = "") OR (tblitems_in.installation_date = "" OR tblitems_in.installation_date IS NULL)) )

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect,'Group By tblitems_in.id');

$output  = $result['output'];
$rResult = $result['rResult'];

$rowCount = 1;
foreach ($rResult as $aRow) {
    $newData = [];
    $aColumns = array_values($aColumns);
    if($aRow['customer_name'] == ''){
        $newData[] = '<span id="customer_name">' . ucwords($aRow['customer_name_2']).'</span>';
    }else{
        $newData[] = '<span id="customer_name">' . ucwords($aRow['customer_name']).'</span>';
    }

    $numberOutput = '';
    if(isset($aRow['invoice_id']) && $aRow['invoice_id'] != ''){
        $numberOutput = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['invoice_id']) . '" target="_blank">' . format_invoice_number($aRow['invoice_id']) . '</a>';
    }else{
        $numberOutput = '<a >'.$aRow['tblitems_in.custom_invoice'].'</a>';
    }


    $numberOutput .= '<div class="row-options">';

//    $numberOutput .= '<a href="' . site_url('invoice/' . $aRow['invoice_id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
    if (has_permission('my_products', '', 'edit')) {
        $numberOutput .= '<a href="#" data-toggle="modal" data-target="#new_products" data-id="' . $aRow['id'] . '">' . _l('edit') . '</a>';
    }

    if (has_permission('my_products', '', 'delete')) {
        $numberOutput .= ' | <a href="' . admin_url('clients/delete_product/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }
    $numberOutput .= '</div>';


//    $numberOutput = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" target="_blank">' . format_invoice_number($aRow['invoice_id']) . '</a>';
//    $invoice_no = $aRow['prefix'].sprintf('%01d', $aRow['number']);
    $newData[] = $numberOutput;
    $newData[] = '<span data-toggle="tooltip" title="' . $aRow['description'] . '" >' . ucwords($aRow['description']).'<br>'. $aRow['long_description'].'</span>';
    $newData[] = '<span data-toggle="tooltip" title="' . $aRow['cvalue_0'] . '" >' . ucwords($aRow['cvalue_0']).'</span>';
    /*$newData[] = '<span data-toggle="tooltip" title="' . $aRow['cvalue_1'] . '" >' . ucwords($aRow['cvalue_1']).'</span>';
    $newData[] = '<span data-toggle="tooltip" title="' . $aRow['cvalue_2'] . '" >' . ucwords($aRow['cvalue_2']).'</span>';*/
    $newData[] = '<span data-toggle="tooltip" title="' . $aRow['group_name'] . '" >' . ucwords($aRow['group_name']).'</span>';
    if($aRow['type_of_machine'] == '' || $aRow['type_of_machine'] == 0){
        $newData[] = '<div ><input type="text" class="form-control" style="width: 110px;" onchange="onChangeUpdateDetails('.$aRow['id'].',\'fab_no\', this.value)" name="itm_fab_no" id="itm_fab_no_'.$aRow['id'].'" value="'.$aRow['fab_no'].'"></div>';
        $newData[] = '<div ><input type="text" class="form-control" style="width: 60px;" maxlength="3" onchange="onChangeUpdateDetails('.$aRow['id'].',\'running_days\', this.value)" name="itm_running_days" id="itm_running_days_'.$aRow['id'].'" value="'.round($aRow['running_days']).'"></div>';
        $newData[] = '<div ><input type="text" class="form-control" style="width: 60px;" maxlength="2" onchange="onChangeUpdateDetails('.$aRow['id'].',\'running_hpd\', this.value)" name="itm_running_hpd" id="itm_running_hpd_'.$aRow['id'].'" value="'.round($aRow['running_hpd']).'"></div>';
        $newData[] = '<div ><input type="text" class="form-control" style="width: 60px;" maxlength="4" onchange="onChangeUpdateDetails('.$aRow['id'].',\'running_hpy\', this.value)" name="itm_running_hpy" id="itm_running_hpy_'.$aRow['id'].'" value="'.round($aRow['running_hpy']).'"></div>';
        $newData[] = '<div class="input-group date"><input type="text" name="installation_date" style="width: 100px;" id="installation_date_'.$aRow['id'].'" value="'. _dt($aRow['installation_date']) .'" onchange="onChangeUpdateDetails('.$aRow['id'].',\'installation_date\', this.value)" class="form-control datepicker" value=""><div class="input-group-addon"><i class="fa fa-calendar calendar-icon"></i></div>';
        $newData[] = '<input type="hidden" name="scheduled_id" value="'.$aRow['id'].'"/>
            <input type="hidden" name="no_of_rows" id="no_of_rows" value="'.count($aColumns).'"/>
            <select class="selectpicker service_dropdown" data-id="'.$aRow['id'].'" data-width="100%" data-none-selected-text="Select Service" tabindex="-98">
              <option>Select Service</option>
              <option value="'.admin_url('contracts/contract?product_id=' . $aRow['id']).'&category_type=scheduled service">Scheduled</option>
              <option value="'.admin_url('contracts/contract?product_id=' . $aRow['id']).'&category_type=amc">AMC</option>
          </select>';
    }else{
        $newData[] = '<span data-toggle="tooltip" title="' . $aRow['fab_no'] . '" >' . ucwords($aRow['fab_no']).'</span>';
        $newData[] = '<span data-toggle="tooltip" title="' . $aRow['running_days'] . '" >' . ucwords($aRow['running_days']).'</span>';
        $newData[] = '<span data-toggle="tooltip" title="' . $aRow['running_hpd'] . '" >' . ucwords($aRow['running_hpd']).'</span>';
        $newData[] = '<span data-toggle="tooltip" title="' . $aRow['running_hpy'] . '" >' . ucwords($aRow['running_hpy']).'</span>';
        $newData[] = '<span data-toggle="tooltip" title="' . _d($aRow['installation_date']) . '" >' . _d($aRow['installation_date']).'</span>';
        $newData[] = '<span data-toggle="tooltip" title="' . ($aRow['type_of_machine'] == 3 ? 'AMC' : 'Scheduled') . '" ><a href="'.admin_url('contracts/contract/'.$aRow['contract_id']).'">' . ($aRow['type_of_machine'] == 3 ? 'AMC' : 'Scheduled').'</a></span>';
    }

    $output['aaData'][] = $newData;
    $rowCount++;
}
/*
  <div class="radio radio-primary radio-inline">
                        <input type="radio" class="category_type_sch_amc" value="scheduled" id="category_type_sch'.$rowCount.'" name="category_type'.$rowCount.'" data-item_id="'.$aRow['id'].'" onclick="location.href = \''.admin_url('contracts/contract?product_id=' . $aRow['id']).'&category_type=scheduled service\'">
                        <label for="category_type_sch'.$rowCount.'">Scheduled</label>
                        &nbsp;
                        <input type="radio" class="category_type_sch_amc" value="amc" id="category_type_amc'.$rowCount.'" name="category_type'.$rowCount.'" data-item_id="'.$aRow['id'].'" onclick="location.href = \''.admin_url('contracts/contract?product_id=' . $aRow['id']).'&category_type=amc\'">
                        <label for="category_type_amc'.$rowCount.'">AMC</label>
            </div>
 * */