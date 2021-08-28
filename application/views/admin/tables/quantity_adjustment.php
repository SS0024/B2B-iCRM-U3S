<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'date',
    'reference_no',
    'tblwarehouses.name as warehouse_name',
    'note',
//    'email',
    'tbladjustments.id as id',
    ];
$sIndexColumn = 'id';
$sTable       = 'tbladjustments';
$join = [
    'LEFT JOIN tblwarehouses ON tblwarehouses.id = tbladjustments.warehouse_id',
];
$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], []);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = _dt($aRow['date']);
    $row[] = $aRow['reference_no'];
    $row[] = $aRow['warehouse_name'];
    $row[] = $aRow['note'];
    $options = '';
    if(has_permission('quantity_adjustments','','edit')) {
        $options .= icon_btn('invoice_items/quantity_adjustment/' . $aRow['id'], 'pencil-square-o', 'btn-default', [
        ]);
    }
    if(has_permission('quantity_adjustments','','delete')) {
        $options .= icon_btn('invoice_items/quantity_adjustment_delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }
    $row[] = $options;

    $output['aaData'][] = $row;
}
//$_data = '<a href="#" onclick="edit_department(this,' . $aRow['departmentid'] . '); return false" data-name="' . $aRow['name'] . '" data-calendar-id="' . $aRow['calendar_id'] . '" data-email="' . $aRow['email'] . '" data-hide-from-client="' . $aRow['hidefromclient'] . '" data-host="' . $aRow['host'] . '" data-password="' . $ps . '" data-imap_username="' . $aRow['imap_username'] . '" data-encryption="' . $aRow['encryption'] . '" data-delete-after-import="' . $aRow['delete_after_import'] . '">' . $_data . '</a>';