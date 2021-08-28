<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'code',
    'name',
    'address',
    'phone',
    'email',
    'id',
    ];
$sIndexColumn = 'id';
$sTable       = 'tblwarehouses';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], []);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['code'];
    $row[] = $aRow['name'];
    $row[] = $aRow['phone'];
    $row[] = $aRow['email'];
    $row[] = $aRow['address'];
    $options ='';
    if (has_permission('warehouses', '', 'edit')) {
        $options .= icon_btn('warehouses/warehouse/' . $aRow['id'], 'pencil-square-o', 'btn-default', [
            'onclick' => 'edit_department(this,' . $aRow['id'] . '); return false', 'data-code' => $aRow['code'], 'data-name' => $aRow['name'], 'data-address' => $aRow['address'], 'data-email' => $aRow['email'], 'data-phone' => $aRow['phone'],
        ]);
    }
    if (has_permission('warehouses', '', 'delete')) {
        $options .= icon_btn('warehouses/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }
    $row[] = $options;
    $output['aaData'][] = $row;
}
//$_data = '<a href="#" onclick="edit_department(this,' . $aRow['departmentid'] . '); return false" data-name="' . $aRow['name'] . '" data-calendar-id="' . $aRow['calendar_id'] . '" data-email="' . $aRow['email'] . '" data-hide-from-client="' . $aRow['hidefromclient'] . '" data-host="' . $aRow['host'] . '" data-password="' . $ps . '" data-imap_username="' . $aRow['imap_username'] . '" data-encryption="' . $aRow['encryption'] . '" data-delete-after-import="' . $aRow['delete_after_import'] . '">' . $_data . '</a>';