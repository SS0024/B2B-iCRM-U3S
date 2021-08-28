<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'date',
    'id',
    'is_stock_in'
    ];
$sIndexColumn = 'id';
$sTable       = 'tblpurchases';
$where = [];
array_push($where, 'AND is_stock_in <> 0');
if($this->ci->input->post('in_stock') == 1){
    array_push($where, 'AND is_stock_in = 1');
}
if($this->ci->input->post('in_stock') == 2){
    array_push($where, 'AND is_stock_in = 2');
}
$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], $where, []);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $numberOutput = '';

    // If is from client area table
    /*if (is_numeric($clientid) || $project_id) {
        $numberOutput = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" target="_blank">' . format_invoice_number($aRow['id']) . '</a>';
    } else {
        $numberOutput = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['id']) . '" onclick="init_purchase(' . $aRow['id'] . '); return false;">' .  . '</a>';
    }*/
    if(has_permission('purchases', '', 'view')){
        $numberOutput = '<a href="' . admin_url('purchases/list_purchases/' . $aRow['id']) . '">' . format_purchase_number($aRow['id']) . '</a>';
    }else{
        $numberOutput = '<a href="javascript:void(0);">' . format_purchase_number($aRow['id']) . '</a>';
    }
    $numberOutput .= '<div class="row-options">';
//    $numberOutput .= '<a href="' . site_url('invoice/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
    if(has_permission('stock_in', '', 'delete') && $aRow['is_stock_in'] == 2){
        $numberOutput .= ' | <a href="' . admin_url('purchases/stock_in_delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;
    $row[] = _dt($aRow['date']);
    if($aRow['is_stock_in'] == 2){
        $row[] = icon_btn(admin_url('purchases/stock_in_pdf/' . $aRow['id']),'file-pdf-o');
    }elseif(has_permission('stock_in', '', 'create')){
        $row[] = '<a href="' . admin_url('purchases/stock_in/' . $aRow['id']) . '" class="btn btn-default btn-icon" >Stock In</a>';
    }

    $output['aaData'][] = $row;
}
//$_data = '<a href="#" onclick="edit_department(this,' . $aRow['departmentid'] . '); return false" data-name="' . $aRow['name'] . '" data-calendar-id="' . $aRow['calendar_id'] . '" data-email="' . $aRow['email'] . '" data-hide-from-client="' . $aRow['hidefromclient'] . '" data-host="' . $aRow['host'] . '" data-password="' . $ps . '" data-imap_username="' . $aRow['imap_username'] . '" data-encryption="' . $aRow['encryption'] . '" data-delete-after-import="' . $aRow['delete_after_import'] . '">' . $_data . '</a>';