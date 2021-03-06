<?php

defined('BASEPATH') or exit('No direct script access allowed');

$baseCurrencySymbol = $this->ci->currencies_model->get_base_currency()->symbol;

$aColumns = [
    'tblproposals.id',
    'subject',
    'proposal_to',
    'total',
	'total_tax',
    'date',
    'open_till',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM tbltags_in JOIN tbltags ON tbltags_in.tag_id = tbltags.id WHERE rel_id = tblproposals.id and rel_type="proposal" ORDER by tag_order ASC) as tags',
    'datecreated',
    'status',
    'tblproposals.assigned as assigned',
	'tblproposals.devide_gst as devide_gst'
];

$sIndexColumn = 'id';
$sTable       = 'tblproposals';

$where  = [];
$filter = [];
$custom_date_select = get_where_report_period();
if ($custom_date_select != '') {
    array_push($where, $custom_date_select);
}
if ($this->ci->input->post('leads_related')) {
    array_push($filter, 'OR rel_type="lead"');
}
if ($this->ci->input->post('customers_related')) {
    array_push($filter, 'OR rel_type="customer"');
}
if ($this->ci->input->post('expired')) {
    array_push($filter, 'OR open_till IS NOT NULL AND open_till <"' . date('Y-m-d') . '" AND status NOT IN(2,3)');
}

$statuses  = $this->ci->proposals_model->get_statuses();
$statusIds = [];

foreach ($statuses as $status) {
    if ($this->ci->input->post('proposals_' . $status)) {
        array_push($statusIds, $status);
    }
}
if (count($statusIds) > 0) {
    array_push($filter, 'AND status IN (' . implode(', ', $statusIds) . ')');
}

$agents    = $this->ci->proposals_model->get_sale_agents();
$agentsIds = [];
foreach ($agents as $agent) {
    if ($this->ci->input->post('sale_agent_' . $agent['sale_agent'])) {
        array_push($agentsIds, $agent['sale_agent']);
    }
}
if (count($agentsIds) > 0) {
    array_push($filter, 'AND assigned IN (' . implode(', ', $agentsIds) . ')');
}

$years      = $this->ci->proposals_model->get_proposals_years();
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

if (!has_permission('proposals', '', 'view')) {
    array_push($where, 'AND ' . get_proposals_sql_where_staff(get_staff_user_id()));
}

$join          = [];
$custom_fields = get_table_custom_fields('proposal');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);

    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tblproposals.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$aColumns = do_action('proposals_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'currency',
    'rel_id',
    'rel_type',
    'invoice_id',
    'hash',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $numberOutput = '<a href="' . admin_url('proposals/list_proposals/' . $aRow['tblproposals.id']) . '" onclick="init_proposal(' . $aRow['tblproposals.id'] . '); return false;">' . format_proposal_number($aRow['tblproposals.id']) . '</a>';

    $numberOutput .= '<div class="row-options">';

    $numberOutput .= '<a href="' . site_url('proposal/' . $aRow['tblproposals.id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
    if (has_permission('proposals', '', 'edit')) {
        $numberOutput .= ' | <a href="' . admin_url('proposals/proposal/' . $aRow['tblproposals.id']) . '">' . _l('edit') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    $row[] = '<a href="' . admin_url('proposals/list_proposals/' . $aRow['tblproposals.id']) . '" onclick="init_proposal(' . $aRow['tblproposals.id'] . '); return false;">' . $aRow['subject'] . '</a>';

    if ($aRow['rel_type'] == 'lead') {
        $toOutput = '<a href="#" onclick="init_lead(' . $aRow['rel_id'] . ');return false;" target="_blank" data-toggle="tooltip" data-title="' . _l('lead') . '">' . $aRow['proposal_to'] . '</a>';
    } elseif ($aRow['rel_type'] == 'customer') {
        $toOutput = '<a href="' . admin_url('clients/client/' . $aRow['rel_id']) . '" target="_blank" data-toggle="tooltip" data-title="' . _l('client') . '">' . $aRow['proposal_to'] . '</a>';
    }

    $row[] = $toOutput;

    $amount = format_money($aRow['total'], ($aRow['currency'] != 0 ? $this->ci->currencies_model->get_currency_symbol($aRow['currency']) : $baseCurrencySymbol));

    if ($aRow['invoice_id']) {
        $amount .= '<br /> <span class="hide"> - </span><span class="text-success">' . _l('estimate_invoiced') . '</span>';
    }

    $row[] = $amount;
	
	
	if($aRow['devide_gst'] == 0){
		$row[] = format_money($aRow['total_tax'], $baseCurrencySymbol);
    }else{
		$row[] = format_money(0, $baseCurrencySymbol);
	}		 
	
	if($aRow['devide_gst'] == 1 &&  $aRow['total_tax']!= 0){ 
		$cgst = $aRow['total_tax']/2;
		$row[] = format_money($cgst, $baseCurrencySymbol);
	}else{
		$row[] = format_money(0,$baseCurrencySymbol);
	}
	
	if($aRow['devide_gst'] == 1 &&  $aRow['total_tax']!= 0){
		$sgst = $aRow['total_tax']/2;
		$row[] = format_money($sgst, $baseCurrencySymbol);
	}else{
		$row[] = format_money(0, $baseCurrencySymbol);
	}
	
	
	


    $row[] = _d($aRow['date']);

    $row[] = _d($aRow['open_till']);

    $row[] = render_tags($aRow['tags']);

    @$this->ci->db->select('*');
    @$this->ci->db->from('tblstaff');
    @$this->ci->db->where('staffid', $aRow['assigned']);
    @$this->ci->db->order_by('staffid', 'asc');
    $saleAgent = @$this->ci->db->get()->row();
    $row[] = $saleAgent->firstname . ' ' . $saleAgent->lastname;

    $row[] = _d($aRow['datecreated']);

    $row[] = format_proposal_status($aRow['status']);

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $hook_data = do_action('proposals_table_row_data', [
        'output' => $row,
        'row'    => $aRow,
    ]);

    $row = $hook_data['output'];

    $output['aaData'][] = $row;
}
