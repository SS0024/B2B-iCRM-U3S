<?php

defined('BASEPATH') or exit('No direct script access allowed');

$project_id = $this->ci->input->post('project_id');

$aColumns = [
    'number',
    'date',
    'reference_no',
    'raised_by',
    'approved_by',
    'adminnote',
    'CONCAT(tblstaff.firstname," ",tblstaff.lastname) as full_name'
];

$sIndexColumn = 'id';
$sTable = 'tblmfpurchases';

$join = [
    'LEFT JOIN tblstaff ON tblstaff.staffid = tblmfpurchases.updated_by',
];

$where = [];
$filter = [];
$custom_date_select = get_where_report_period();
if ($custom_date_select != '') {
    array_push($where, $custom_date_select);
}

$aColumns = do_action('invoices_table_sql_columns', $aColumns);

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'tblmfpurchases.id as id'
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
    $numberOutput = '<a href="' . admin_url('mfpurchases/list_mfpurchases/' . $aRow['id']) . '" onclick="init_mfpurchase(' . $aRow['id'] . '); return false;" >' . format_purchase_number($aRow['id']) . '</a>';

    $numberOutput .= '<div class="row-options">';

//    $numberOutput .= '<a href="' . site_url('invoice/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
    if (has_permission('mfpurchases', '', 'edit')) {
        $numberOutput .= ' <a href="' . admin_url('mfpurchases/mfpurchase/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }
    if(has_permission('mfpurchases', '', 'delete')){
        $numberOutput .= ' | <a href="' . admin_url('mfpurchases/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;
    $row[] = _dt($aRow['date']);

    $row[] = $aRow['reference_no'];
    $CI = & get_instance();
    $CI->db->where('staffid', $aRow['raised_by']);
    $staff_raised_by = $CI->db->select('firstname,lastname')->from('tblstaff')->get()->row();
    $row[] = $staff_raised_by ? $staff_raised_by->firstname . ' ' . $staff_raised_by->lastname : '';
    $CI->db->where('staffid', $aRow['approved_by']);
    $staff_approved_by = $CI->db->select('firstname,lastname')->from('tblstaff')->get()->row();
    $row[] = $staff_approved_by ? $staff_approved_by->firstname . ' ' . $staff_approved_by->lastname : '';
    $row[] = $aRow['adminnote'];
    $row[] = $aRow['full_name'];
    $output['aaData'][] = $row;
}
