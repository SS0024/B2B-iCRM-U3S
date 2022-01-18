<?php

defined('BASEPATH') or exit('No direct script access allowed');

$baseCurrencySymbol = $this->ci->currencies_model->get_base_currency()->symbol;

$aColumns = [
    'tblinquiries.id',
    'total',
    'subject',
    'total_tax',
    '(CASE 
        WHEN rel_type="lead" THEN (SELECT company FROM tblleads WHERE tblleads.id = tblinquiries.rel_id LIMIT 1)
        WHEN rel_type="customer" THEN (SELECT (CASE company WHEN "" THEN (SELECT CONCAT(firstname, " ", lastname) FROM tblcontacts WHERE userid = tblclients.userid and is_primary = 1) ELSE company END) company FROM tblclients where tblclients.userid = tblinquiries.rel_id LIMIT 1)
        END) as customer_name',
    'tblinquiries.devide_gst as devide_gst',
    '1',
    'tblinquiries.adminnote as adminnote',
    'tblinquiries.assigned as assigned',
    'date',
    'open_till',
    'reference_no',
    'lead_status',
    'is_rfr',
];

$sIndexColumn = 'id';
$sTable       = 'tblinquiries';

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

$statuses  = $this->ci->inquiries_model->get_statuses();
$statusIds = [];

foreach ($statuses as $status) {
    if ($this->ci->input->post('proposals_' . $status)) {
        array_push($statusIds, $status);
    }
}
if (count($statusIds) > 0) {
    array_push($filter, 'AND status IN (' . implode(', ', $statusIds) . ')');
}

$agents    = $this->ci->inquiries_model->get_sale_agents();
$agentsIds = [];
foreach ($agents as $agent) {
    if ($this->ci->input->post('sale_agent_' . $agent['sale_agent'])) {
        array_push($agentsIds, $agent['sale_agent']);
    }
}
if (count($agentsIds) > 0) {
    array_push($filter, 'AND assigned IN (' . implode(', ', $agentsIds) . ')');
}

$years      = $this->ci->inquiries_model->get_proposals_years();
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
if(isset($_POST['contact_person']) && !empty($_POST['contact_person'])){
    $customWhere = [
        'tblitems_in_div_con.contact_id' => ['in', $_POST['contact_person']],
    ];

    $divConsDetails = get_div_cons_by_type('inquiry', null, $customWhere);
    $estimateIds = implode(',',array_unique(array_pluck($divConsDetails,'rel_id')));
    if(!empty($estimateIds)){
        array_push($where, 'AND tblinquiries.id IN ('.$estimateIds.')');
    }else{
        array_push($where, 'AND tblinquiries.id IN (0)');
    }
}
if(isset($_POST['customer']) && !empty($_POST['customer'])){
    array_push($where, 'AND tblinquiries.rel_id = "'.$_POST['customer'].'" ');
}
$join          = [];
$custom_fields = get_table_custom_fields('proposal');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);

    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tblinquiries.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
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
    'is_rfr',
    'status',
    'hash',
]);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $numberOutput = '<a href="' . admin_url('inquiries/list_inquiries/' . $aRow['tblinquiries.id']) . '" onclick="init_inquiry(' . $aRow['tblinquiries.id'] . '); return false;">'.format_inquiry_number($aRow['tblinquiries.id']) .'</a>';

    $numberOutput .= '<div class="row-options">';
    if (has_permission('proposals', '', 'edit')) {
        $numberOutput .= '<a href="' . admin_url('inquiries/inquiry/' . $aRow['tblinquiries.id']) . '">' . _l('edit') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    $amount = format_money($aRow['total'], ($aRow['currency'] != 0 ? $this->ci->currencies_model->get_currency_symbol($aRow['currency']) : $baseCurrencySymbol));
    $pos = $this->ci->inquiries_model->get_purchaseorders($aRow['tblinquiries.id']);
    if ($aRow['invoice_id'] || (count($pos) > 0)) {
        $amount .= '<br /> <span class="hide"> - </span><span class="text-success">' . _l('PO') . '</span>';
    }

    $row[] = $amount;

    $contactPerson = [];
    $divisions = [];
    $divConArray = get_div_cons_by_type('inquiry', $aRow['tblinquiries.id']);
    foreach ($divConArray as $con_div)
    {
        $contactPerson[] = $con_div['contact_name'];
        $divisions[] = $con_div['division'];
    }

    $row[] = implode(', ', $contactPerson);
    $row[] = implode(', ', array_values(array_unique($divisions)));

    if ($aRow['rel_type'] == 'lead') {
        $toOutput = '<a href="#" onclick="init_lead(' . $aRow['rel_id'] . ');return false;" target="_blank" data-toggle="tooltip" data-title="' . _l('lead') . '">' . $aRow['customer_name'] . '</a>';
    } elseif ($aRow['rel_type'] == 'customer') {
        $toOutput = '<a href="' . admin_url('clients/client/' . $aRow['rel_id']) . '" target="_blank" data-toggle="tooltip" data-title="' . _l('client') . '">' . $aRow['customer_name'] . '</a>';
    }

    $row[] = $toOutput;

    $estimateItem = get_items_by_type('inquiry', $aRow['tblinquiries.id'])[0];
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
    @$this->ci->db->where('staffid', $aRow['assigned']);
    @$this->ci->db->order_by('staffid', 'asc');
    $saleAgent = @$this->ci->db->get()->row();
    $row[] = $saleAgent->firstname . ' ' . $saleAgent->lastname;

    $row[] = _d($aRow['date']);

    $row[] = _d($aRow['follow_up_date']);
    $row[] = _d($aRow['open_till']);

    $row[] = format_proposal_status($aRow['status']);

    $row[] = $aRow['lead_status'];
    $row[] = $aRow['is_rfr'] == 1 ? '<span class="label label-danger  s-status proposal-status-5">RFR</span>' : '';

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
