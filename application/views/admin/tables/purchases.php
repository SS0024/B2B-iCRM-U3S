<?php

defined('BASEPATH') or exit('No direct script access allowed');

$project_id = $this->ci->input->post('project_id');

$aColumns = [
    'number',
    'date',
    'reference_no',
    'tblwarehouses.name as warehouse_name',
    'adminnote',
    'tblsuppliers.company as company',
    'supply_date',
    'expected_delivery_date',
    'status',
    'total',
    'CONCAT(tblstaff.firstname," ",tblstaff.lastname) as full_name'
];

$sIndexColumn = 'id';
$sTable = 'tblpurchases';

$join = [
    'LEFT JOIN tblsuppliers ON tblsuppliers.id = tblpurchases.supplier_id',
    'LEFT JOIN tblstaff ON tblstaff.staffid = tblpurchases.updated_by',
    'LEFT JOIN tblwarehouses ON tblwarehouses.id = tblpurchases.warehouse_id',
];


$where = [];
$filter = [];
$custom_date_select = get_where_report_period();
if ($custom_date_select != '') {
    array_push($where, $custom_date_select);
}

$aColumns = do_action('invoices_table_sql_columns', $aColumns);

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'tblpurchases.id as id',
    'is_stock_in'
]);

@$this->ci->load->model('delivery_module_model');
$allStatus = @$this->ci->delivery_module_status_model->get();
$output = $result['output'];
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
    $numberOutput = '<a href="' . admin_url('purchases/list_purchases/' . $aRow['id']) . '" onclick="init_purchase(' . $aRow['id'] . '); return false;" >' . format_purchase_number($aRow['id']) . '</a>';

    $numberOutput .= '<div class="row-options">';

//    $numberOutput .= '<a href="' . site_url('invoice/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
    if (has_permission('purchases', '', 'edit')) {
        $numberOutput .= ' <a href="' . admin_url('purchases/purchase/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }
    if(has_permission('purchases', '', 'delete') && $aRow['is_stock_in'] != 2){
        $numberOutput .= ' | <a href="' . admin_url('purchases/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;
    $row[] = _dt($aRow['date']);

    $row[] = $aRow['reference_no'];
    $row[] = $aRow['warehouse_name'];
    $row[] = $aRow['adminnote'];
    $row[] = $aRow['company'];
    $row[] = _d($aRow['supply_date']);
    $row[] = _d($aRow['expected_delivery_date']);
    $row[] = ucfirst(implode(' ',explode('_',$aRow['status'])));
    $row[] = format_money($aRow['total']);
    $row[] = $aRow['full_name'];
    if ((!empty($aRow['expected_delivery_date']) && $aRow['expected_delivery_date'] < date('Y-m-d'))) {
        $row['DT_RowClass'] .= ' text-danger';
    }
    $output['aaData'][] = $row;
}
