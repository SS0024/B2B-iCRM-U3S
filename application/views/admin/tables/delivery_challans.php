<?php

defined('BASEPATH') or exit('No direct script access allowed');

$project_id = $this->ci->input->post('project_id');

$aColumns = [
    'number',
    'total',
    'total_tax',
    'YEAR(date) as year',
    'date',
    get_sql_select_client_company(),
    'tblprojects.name as project_name',
    '(SELECT GROUP_CONCAT(name SEPARATOR ",") FROM tbltags_in JOIN tbltags ON tbltags_in.tag_id = tbltags.id WHERE rel_id = tbldelivery_challans.id and rel_type="invoice" ORDER by tag_order ASC) as tags',
    'duedate',
    'tbldelivery_challans.status',
    'tbldelivery_challans.devide_gst as devide_gst'
];

$sIndexColumn = 'id';
$sTable = 'tbldelivery_challans';

$join = [
    'LEFT JOIN tblclients ON tblclients.userid = tbldelivery_challans.clientid',
    'LEFT JOIN tblcurrencies ON tblcurrencies.id = tbldelivery_challans.currency',
    'LEFT JOIN tblprojects ON tblprojects.id = tbldelivery_challans.project_id',
];

$custom_fields = get_table_custom_fields('invoice');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);

    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tbldelivery_challans.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$where = [];
$filter = [];
$custom_date_select = get_where_report_period();
if ($custom_date_select != '') {
    array_push($where, $custom_date_select);
}
if ($this->ci->input->post('not_sent')) {
    array_push($filter, 'AND sent = 0 AND tbldelivery_challans.status NOT IN(2,5)');
}
if ($this->ci->input->post('not_have_payment')) {
    array_push($filter, 'AND tbldelivery_challans.id NOT IN(SELECT invoiceid FROM tblinvoicepaymentrecords) AND tbldelivery_challans.status != 5');
}
if ($this->ci->input->post('recurring')) {
    array_push($filter, 'AND recurring > 0');
}

$statuses = $this->ci->delivery_challan_model->get_statuses();
$statusIds = [];
foreach ($statuses as $status) {
    if ($this->ci->input->post('invoices_' . $status)) {
        array_push($statusIds, $status);
    }
}
if (count($statusIds) > 0) {
    array_push($filter, 'AND tbldelivery_challans.status IN (' . implode(', ', $statusIds) . ')');
}

$agents = $this->ci->delivery_challan_model->get_sale_agents();
$agentsIds = [];
foreach ($agents as $agent) {
    if ($this->ci->input->post('sale_agent_' . $agent['sale_agent'])) {
        array_push($agentsIds, $agent['sale_agent']);
    }
}
if (count($agentsIds) > 0) {
    array_push($filter, 'AND sale_agent IN (' . implode(', ', $agentsIds) . ')');
}

$modesIds = [];
foreach ($data['payment_modes'] as $mode) {
    if ($this->ci->input->post('invoice_payments_by_' . $mode['id'])) {
        array_push($modesIds, $mode['id']);
    }
}
if (count($modesIds) > 0) {
    array_push($where, 'AND tbldelivery_challans.id IN (SELECT invoiceid FROM tblinvoicepaymentrecords WHERE paymentmode IN ("' . implode('", "', $modesIds) . '"))');
}

$years = $this->ci->delivery_challan_model->get_invoices_years();
$yearArray = [];
foreach ($years as $year) {
    if ($this->ci->input->post('year_' . $year['year'])) {
        array_push($yearArray, $year['year']);
    }
}
if (count($yearArray) > 0) {
    array_push($where, 'AND YEAR(date) IN (' . implode(', ', $yearArray) . ')');
}

if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

if ($clientid != '') {
    array_push($where, 'AND tbldelivery_challans.clientid=' . $clientid);
}

if ($project_id) {
    array_push($where, 'AND project_id=' . $project_id);
}

if (!has_permission('delivery_challan', '', 'view')) {
    $userWhere = 'AND ' . get_delivery_challan_where_sql_for_staff(get_staff_user_id());
    array_push($where, $userWhere);
}

$aColumns = do_action('invoices_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'tbldelivery_challans.id',
    'tbldelivery_challans.clientid',
    'symbol',
    'project_id',
    'hash',
    'recurring',
    'adminnote',
    'deleted_customer_name',
    'tbldelivery_challans.sale_agent as sale_agent',
]);

@$this->ci->load->model('delivery_module_model');
$allStatus = @$this->ci->delivery_module_status_model->get();
$output = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    $numberOutput = '';

    // If is from client area table
    if (is_numeric($clientid) || $project_id) {
        $numberOutput = '<a href="' . admin_url('delivery_challan/list_invoices/' . $aRow['id']) . '" target="_blank">' . format_delivery_challan_number($aRow['id']) . '</a>';
    } else {
        $numberOutput = '<a href="' . admin_url('delivery_challan/list_invoices/' . $aRow['id']) . '" onclick="init_delivery_challan(' . $aRow['id'] . '); return false;">' . format_delivery_challan_number($aRow['id']) . '</a>';
    }

    if ($aRow['recurring'] > 0) {
        $numberOutput .= '<br /><span class="label label-primary inline-block mtop4"> ' . _l('invoice_recurring_indicator') . '</span>';
    }

    $numberOutput .= '<div class="row-options">';

//    $numberOutput .= '<a href="' . site_url('invoice/' . $aRow['id'] . '/' . $aRow['hash']) . '" target="_blank">' . _l('view') . '</a>';
    if (has_permission('delivery_challan', '', 'edit')) {
        $numberOutput .= ' <a href="' . admin_url('delivery_challan/invoice/' . $aRow['id']) . '">' . _l('edit') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    $row[] = format_money($aRow['total'], $aRow['symbol']);

    $row[] = format_money($aRow['total_tax'], $aRow['symbol']);

    if ($aRow['devide_gst'] == 0) {
        $row[] = format_money($aRow['total_tax'], $aRow['symbol']);
    } else {
        $row[] = format_money(0, $aRow['symbol']);
    }

    if ($aRow['devide_gst'] == 1 && $aRow['total_tax'] != 0) {
        $cgst = $aRow['total_tax'] / 2;
        $row[] = format_money($cgst, $aRow['symbol']);
    } else {
        $row[] = format_money(0, $aRow['symbol']);
    }

    if ($aRow['devide_gst'] == 1 && $aRow['total_tax'] != 0) {
        $sgst = $aRow['total_tax'] / 2;
        $row[] = format_money($sgst, $aRow['symbol']);
    } else {
        $row[] = format_money(0, $aRow['symbol']);
    }


    $row[] = format_money(sum_from_table('tblinvoicepaymentrecords', array('field' => 'amount', 'where' => array('invoiceid' => $aRow['id']))), $aRow['symbol']);

    $row[] = format_money(get_invoice_total_left_to_pay($aRow['id'], $aRow['total']), $aRow['symbol']);

    $row[] = $aRow['year'];

    $row[] = _d($aRow['date']);

    if (empty($aRow['deleted_customer_name'])) {
        $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';
    } else {
        $row[] = $aRow['deleted_customer_name'];
    }

    if(isset($aRow['sale_agent']) && $aRow['sale_agent'] != 0){
        @$this->ci->db->select('*');
        @$this->ci->db->from('tblstaff');
        @$this->ci->db->where('staffid', $aRow['sale_agent']);
        @$this->ci->db->order_by('staffid', 'asc');
        $saleAgent = @$this->ci->db->get()->row();
        $row[] = $saleAgent->firstname . ' ' . $saleAgent->lastname;
    }else{
        $row[] = '';
    }

    $row[] = $aRow['adminnote'];

    $row[] = _d($aRow['duedate']);

    $delivery_detail = @$this->ci->delivery_module_model->get_delivery_modules_by_invoice_id($aRow['id']);
    $oldStatus = array_values(array_unique(array_pluck($delivery_detail,'status_id')));

//    $row[] = format_invoice_status($aRow['tbldelivery_challans.status']);
   /* $deliveryStatusHtml = '';
    foreach ($allStatus as $val){
        if(!empty($oldStatus) && in_array($val['id'] , $oldStatus) && (end($oldStatus) == $val['id'])){
            $deliveryStatusHtml.= "<span class='text-success'>{$val['name']}</span><br>";
        }
        elseif(!empty($oldStatus) && in_array($val['id'] , $oldStatus)){
            $deliveryStatusHtml.= "<span style='color: gray'><strike>{$val['name']}</strike></span><br>";
        }else{
            $deliveryStatusHtml.= "<span style='color: rgba(128, 128, 128, 0.60)'>{$val['name']}</span><br>";
        }
    }

    $row[] = $deliveryStatusHtml;*/

    // Custom fields add values
    foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }

    $hook = do_action('invoices_table_row_data', [
        'output' => $row,
        'row' => $aRow,
    ]);

    $row = $hook['output'];

    $output['aaData'][] = $row;
}
