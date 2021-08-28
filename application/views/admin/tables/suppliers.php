<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = has_permission('suppliers', '', 'delete');

$custom_fields = get_table_custom_fields('suppliers');
$this->ci->db->query("SET sql_mode = ''");

$aColumns = [
    '1',
    'tblsuppliers.id as userid',
    'company',
    'email',
    'tblsuppliers.phonenumber as phonenumber',
    'address',
    'tblsuppliers.city as city',
    'state',
    'zip',
    'tblsuppliers.gst_number as gst_number',
    'name',
];

$sIndexColumn = 'id';
$sTable       = 'tblsuppliers';
$where        = [];
// Add blank where all filter can be stored
$filter = [];

$join = [
];

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);
    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tblsuppliers.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="' . $field['fieldto'] . '" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

$countries  = $this->ci->clients_model->get_clients_distinct_countries();
$countryIds = [];
foreach ($countries as $country) {
    if ($this->ci->input->post('country_' . $country['country_id'])) {
        array_push($countryIds, $country['country_id']);
    }
}
if (count($countryIds) > 0) {
    array_push($filter, 'AND country IN (' . implode(',', $countryIds) . ')');
}


if (count($filter) > 0) {
    array_push($where, 'AND (' . prepare_dt_filter($filter) . ')');
}

$aColumns = do_action('customers_table_sql_columns', $aColumns);

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [

], 'group by tblsuppliers.id', [7 => 'name']);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];

    // Bulk actions
    $row[] = '<div class="checkbox"><input type="checkbox" value="' . $aRow['userid'] . '"><label></label></div>';
    // User id
    $row[] = $aRow['userid'];

    // Company
    $company  = $aRow['company'];
    $isPerson = false;

    if ($company == '') {
        $company  = _l('no_company_view_profile');
        $isPerson = true;
    }

    $url = admin_url('suppliers/supplier/' . $aRow['userid']);

    $company = '<a href="' . $url . '">' . $company . '</a>';

    $company .= '<div class="row-options">';
    $company .= '<a href="' . $url . '">' . _l('view') . '</a>';

    if ($hasPermissionDelete) {
        $company .= ' | <a href="' . admin_url('suppliers/delete/' . $aRow['userid']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }

    $company .= '</div>';

    $row[] = $company;

    // Primary contact email
    $row[] = ($aRow['email'] ? '<a href="mailto:' . $aRow['email'] . '">' . $aRow['email'] . '</a>' : '');

    // Primary contact phone
    $row[] = ($aRow['phonenumber'] ? '<a href="tel:' . $aRow['phonenumber'] . '">' . $aRow['phonenumber'] . '</a>' : '');


    $row[] = $aRow['address'];
    $row[] = $aRow['city'];
    $row[] = $aRow['state'];
    $row[] = $aRow['zip'];
    $row[] = $aRow['gst_number'];

    $row[] = $aRow['name'];

    // Custom fields add values
    /*foreach ($customFieldsColumns as $customFieldColumn) {
        $row[] = (strpos($customFieldColumn, 'date_picker_') !== false ? _d($aRow[$customFieldColumn]) : $aRow[$customFieldColumn]);
    }*/

    $hook = do_action('customers_table_row_data', [
        'output' => $row,
        'row'    => $aRow,
    ]);

    $row = $hook['output'];

    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
