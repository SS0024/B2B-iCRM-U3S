<?php

defined('BASEPATH') or exit('No direct script access allowed');
$aColumns = [
    'division',
    'address',
    'city',
    'pincode',
    'state',
    'country',
    'userid',
];
$sIndexColumn = 'id';
$sTable       = 'tbldivision';

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, [], [], ['id']);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'division') {
            $_data .= '<div class="row-options"><a href="'.admin_url('clients/delete_division/'.$aRow['id']).'" class="text-danger _delete">' . _l('delete') . '</a><font color="green"> <b>|</b> <a href="#" onclick="division(' . $aRow['userid'] . ',' . $aRow['id'] . ');return false;">' . _l('edit') . '</a></div>';
        }
        $row[] = $_data;
    }

    $options = icon_btn('#', 'pencil-square-o', 'btn-default', ['onclick' => 'edit_type(this,' . $aRow['id'] . '); return false;', 'data-name' => $aRow['name']]);
    $row[]   = $options .= icon_btn('contracts/delete_contract_type/' . $aRow['id'], 'remove', 'btn-danger _delete');

    $output['aaData'][] = $row;
}
