<?php

defined('BASEPATH') or exit('No direct script access allowed');

$project_id = $this->ci->input->post('project_id');

$aColumns = [
    'number',
    'date',
    get_sql_select_client_company(),
    'tblrequsiteform.status as status',
    'adminnote',
    'tblrequsiteform.sale_agent as sale_agent',
//	'tblestimates.devide_gst as devide_gst'
    ];

$join = [
    'LEFT JOIN tblclients ON tblclients.userid = tblrequsiteform.clientid',
    'LEFT JOIN tblcurrencies ON tblcurrencies.id = tblrequsiteform.currency',
    'LEFT JOIN tblprojects ON tblprojects.id = tblrequsiteform.project_id',
];

$sIndexColumn = 'id';
$sTable       = 'tblrequsiteform';
$custom_fields = get_table_custom_fields('requsite_form');
foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);

    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tblrequsiteform.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$where  = [];
$filter = [];
$custom_date_select = get_where_report_period();
if ($custom_date_select != '') {
    array_push($where, $custom_date_select);
}
$this->ci->load->model('requsite_form_model');
$statuses  = $this->ci->requsite_form_model->get_statuses();
$statusIds = [];
foreach ($statuses as $key=>$status) {
    if ($this->ci->input->post('estimates_' . $key)) {
        array_push($statusIds, $key);
    }
}
if (count($statusIds) > 0) {
    array_push($filter, 'AND tblrequsiteform.status IN (' . implode(', ', $statusIds) . ')');
}
$agents    = $this->ci->requsite_form_model->get_sale_agents();
$agentsIds = [];
foreach ($agents as $agent) {
    if ($this->ci->input->post('sale_agent_' . $agent['sale_agent'])) {
        array_push($agentsIds, $agent['sale_agent']);
    }
}
if (count($agentsIds) > 0) {
    array_push($filter, 'AND sale_agent IN (' . implode(', ', $agentsIds) . ')');
}
$years      = $this->ci->requsite_form_model->get_estimates_years();
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
    array_push($where, 'AND tblrequsiteform.clientid=' . $clientid);
}

if ($project_id) {
    array_push($where, 'AND project_id=' . $project_id);
}

if (!has_permission('requisite_forms', '', 'view')) {
    $userWhere = 'AND ' . get_requsite_form_where_sql_for_staff(get_staff_user_id());
    array_push($where, $userWhere);
}

/*$aColumns = do_action('estimates_table_sql_columns', $aColumns);*/

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'tblrequsiteform.id as id',
    'tblrequsiteform.clientid',
    'symbol',
    'project_id',
    'invoiceid',
    'deleted_customer_name',
    'tblrequsiteform.sale_agent as sale_agent',
    'hash',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $numberOutput = '';
    // If is from client area table or projects area request
    if (is_numeric($clientid) || $project_id) {
        $numberOutput = '<a href="' . admin_url('requsite_forms/list_requsite_forms/' . $aRow['id']) . '" target="_blank">' . format_requsite_form_number($aRow['id']) . '</a>';
    } else {
        $numberOutput = '<a href="' . admin_url('requsite_forms/list_requsite_forms/' . $aRow['id']) . '" onclick="init_requsite_form(' . $aRow['id'] . '); return false;">' . format_requsite_form_number($aRow['id']) . '</a>';
    }

    $numberOutput .= '<div class="row-options">';

//    $numberOutput .= '<a href="' . site_url('requsite_forms/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
    if (has_permission('requisite_forms', '', 'edit')) {
        $numberOutput .= ' <a href="' . admin_url('requsite_forms/requsite_form/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }
    if (has_permission('requisite_forms', '', 'delete')) {
        $numberOutput .= ' | <a href="' . admin_url('requsite_forms/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;
    $date = _d($aRow['date']);
    if ($aRow['invoiceid']) {
        $date .= '<br /><span class="hide"> - </span><span class="text-success">' . _l('estimate') . '</span>';
    }
    $row[] = $date;

    if (empty($aRow['deleted_customer_name'])) {
        $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';
    } else {
        $row[] = $aRow['deleted_customer_name'];
    }

    $row[] = $statuses[$aRow['status']];
    $row[] = $aRow['adminnote'];

    //$row[] = '<a href="' . admin_url('projects/view/' . $aRow['project_id']) . '">' . $aRow['project_name'] . '</a>';

    @$this->ci->db->select('*');
    @$this->ci->db->from('tblstaff');
    @$this->ci->db->where('staffid', $aRow['sale_agent']);
    @$this->ci->db->order_by('staffid', 'asc');
    $saleAgent = @$this->ci->db->get()->row();
    $row[] = $saleAgent->firstname . ' ' . $saleAgent->lastname;
//    $row[] = $aRow['staff_name'];

// Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }
    $hook = do_action('estimates_table_row_data', [
        'output' => $row,
        'row'    => $aRow,
    ]);

    $row                = $hook['output'];
    $output['aaData'][] = $row;
}

echo json_encode($output);
die();
