<?php

defined('BASEPATH') or exit('No direct script access allowed');

$project_id = $this->ci->input->post('project_id');

$aColumns = [
    'number',
    'total',
    'total_tax',
    'YEAR(date) as year',
    get_sql_select_client_company(),
    'tblprojects.name as project_name',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM tbltags_in JOIN tbltags ON tbltags_in.tag_id = tbltags.id WHERE rel_id = tblpurchaseorder.id and rel_type="estimate" ORDER by tag_order ASC) as tags',
    'adminnote',
    'sale_agent',
    'date',
    'expirydate',
    'tblpurchaseorder.status'
];

$join = [
    'LEFT JOIN tblclients ON tblclients.userid = tblpurchaseorder.clientid',
    'LEFT JOIN tblcurrencies ON tblcurrencies.id = tblpurchaseorder.currency',
    'LEFT JOIN tblprojects ON tblprojects.id = tblpurchaseorder.project_id',
];

$sIndexColumn = 'id';
$sTable = 'tblpurchaseorder';

$custom_fields = get_table_custom_fields('estimate');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tblpurchaseorder.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$where = [];
$filter = [];
$custom_date_select = get_where_report_period();
if ($custom_date_select != '') {
    array_push($where, $custom_date_select);
}
if ($this->ci->input->post('not_sent')) {
    array_push($filter, 'OR (sent= 0 AND tblpurchaseorder.status NOT IN (2,3,4))');
}
if ($this->ci->input->post('invoiced')) {
    array_push($filter, 'OR invoiceid IS NOT NULL');
}
if ($this->ci->input->post('not_invoiced')) {
    array_push($filter, 'OR invoiceid IS NULL');
}
$statuses = $this->ci->purchaseorder_model->get_statuses();
$statusIds = [];
foreach ($statuses as $status) {
    if ($this->ci->input->post('estimates_' . $status)) {
        array_push($statusIds, $status);
    }
}
if (count($statusIds) > 0) {
    array_push($filter, 'AND tblpurchaseorder.status IN (' . implode(', ', $statusIds) . ')');
}

$agents = $this->ci->purchaseorder_model->get_sale_agents();
$agentsIds = [];
foreach ($agents as $agent) {
    if ($this->ci->input->post('sale_agent_' . $agent['sale_agent'])) {
        array_push($agentsIds, $agent['sale_agent']);
    }
}
if (count($agentsIds) > 0) {
    array_push($filter, 'AND sale_agent IN (' . implode(', ', $agentsIds) . ')');
}

$years = $this->ci->purchaseorder_model->get_estimates_years();
$yearsArray = [];
foreach ($years as $year) {
    if ($this->ci->input->post('year_' . $year['year'])) {
        array_push($yearsArray, $year['year']);
    }
}
if (count($yearsArray) > 0) {
    array_push($filter, 'AND YEAR(date) IN (' . implode(', ', $yearsArray) . ')');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

if ($clientid != '') {
    array_push($where, 'AND tblpurchaseorder.clientid=' . $clientid);
}

if ($project_id) {
    array_push($where, 'AND project_id=' . $project_id);
}

if (!has_permission('purchaseorder', '', 'view')) {
    $userWhere = 'AND ' . get_purchaseorder_where_sql_for_staff(get_staff_user_id());
    array_push($where, $userWhere);
}

$aColumns = do_action('estimates_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'tblpurchaseorder.id',
    'tblpurchaseorder.clientid',
//    'tblpurchaseorder.invoiceid',
    'symbol',
    'lead_status',
    '(select count(*) from tblinvoices where tblinvoices.purchaseorder_id = tblpurchaseorder.id) as invoiceid',
    'lead_source',
    'deleted_customer_name',
    'hash',
]);

$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $numberOutput = '';
    // If is from client area table or projects area request
    if (is_numeric($clientid) || $project_id) {
        $numberOutput = '<a href="' . admin_url('purchaseorder/list_purchaseorder/' . $aRow['id']) . '" target="_blank">' . format_purchaseorder_number($aRow['id']) . '</a>';
    } else {
        $numberOutput = '<a href="' . admin_url('purchaseorder/list_purchaseorder/' . $aRow['id']) . '" onclick="init_purchaseorder(' . $aRow['id'] . '); return false;">' . format_purchaseorder_number($aRow['id']) . '</a>';
    }

    $numberOutput .= '<div class="row-options">';

//    $numberOutput .= '<a href="' . site_url('purchaseorder/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
    if (has_permission('purchaseorder', '', 'edit')) {
        $numberOutput .= '<a href="' . admin_url('purchaseorder/purchaseorder/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    $amount = format_money($aRow['total'], $aRow['symbol']);

    if ($aRow['invoiceid']) {
        $amount .= '<br /><span class="hide"> - </span><span class="text-success">' . _l('estimate_invoiced') . '</span>';
    }

    $row[] = $amount;

//    $row[] = $aRow['year'];
    $contactPerson = [];
    $divisions = [];
    $divConArray = get_div_cons_by_type('purchaseorder', $aRow['id']);
    foreach ($divConArray as $con_div)
    {
        $contactPerson[] = $con_div['contact_name'];
        $divisions[] = $con_div['division'];
    }

    $row[] = implode(', ', $contactPerson);
    $row[] = implode(', ', array_values(array_unique($divisions)));
    if (empty($aRow['deleted_customer_name'])) {
        $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';
    } else {
        $row[] = $aRow['deleted_customer_name'];
    }

    $estimateItem = get_items_by_type('purchaseorder', $aRow['id'])[0];
    @$this->ci->db->select('*');
    @$this->ci->db->from('tblitems');
    @$this->ci->db->where('description', $estimateItem['description']);
    $recdata = @$this->ci->db->get()->row();

    @$this->ci->db->select('*');
    @$this->ci->db->from('tblitems_groups');
    @$this->ci->db->where('id', $estimateItem['group_id']);
    $groupdata = @$this->ci->db->get()->row();

    @$this->ci->db->select('*');
    @$this->ci->db->from('tblitems_brands');
    @$this->ci->db->where('id', $recdata->brand);
    $branddata = @$this->ci->db->get()->row();
    
    $row[] = $branddata->name;

    $row[] = $groupdata->name;

    $row[] = $aRow['adminnote'];
    @$this->ci->db->select('*');
    @$this->ci->db->from('tblstaff');
    @$this->ci->db->where('staffid', $aRow['sale_agent']);
    @$this->ci->db->order_by('staffid', 'asc');
    $saleAgent = @$this->ci->db->get()->row();
    $row[] = $saleAgent->firstname . ' ' . $saleAgent->lastname;

    //$row[] = '<a href="' . admin_url('projects/view/' . $aRow['project_id']) . '">' . $aRow['project_name'] . '</a>';

    $row[] = _d($aRow['date']);

    $row[] = _d($aRow['expirydate']);

//    $row[] = $aRow['reference_no'];

    $row[] = format_purchaseorder_status($aRow['tblpurchaseorder.status']);

//    $row[] = $aRow['lead_status'];

//    $row[] = $aRow['lead_source'];

    /*$row[] = format_money($aRow['total_tax'], $aRow['symbol']);

    if($aRow['devide_gst'] == 0){
        $row[] = format_money($aRow['total_tax'], $aRow['symbol']);
    }else{
        $row[] = format_money(0, $aRow['symbol']);
    }

    if($aRow['devide_gst'] == 1 &&  $aRow['total_tax']!= 0){
        $cgst = $aRow['total_tax']/2;
        $row[] = format_money($cgst, $aRow['symbol']);
    }else{
        $row[] = format_money(0, $aRow['symbol']);
    }

    if($aRow['devide_gst'] == 1 &&  $aRow['total_tax']!= 0){
        $sgst = $aRow['total_tax']/2;
        $row[] = format_money($sgst, $aRow['symbol']);
    }else{
        $row[] = format_money(0, $aRow['symbol']);
    }*/


    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $hook = do_action('estimates_table_row_data', [
        'output' => $row,
        'row' => $aRow,
    ]);

    $row = $hook['output'];
    $output['aaData'][] = $row;
}

echo json_encode($output);
die();
