<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'date',
    'reference_no',
    'tblwarehouses.name as warehouse_name',
    'type',
    'brand_names',
    'category_names',
    'initial_file',
    'final_file',
//    'email',
    'tblstock_counts.id as id',
    'finalized'
    ];
$sIndexColumn = 'id';
$sTable       = 'tblstock_counts';
$join = [
    'LEFT JOIN tblwarehouses ON tblwarehouses.id = tblstock_counts.warehouse_id',
];
$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], []);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $options = '';
    $row = [];
    $row[] = _dt($aRow['date']);
    $row[] = $aRow['reference_no'];
    $row[] = $aRow['warehouse_name'];
    $row[] = $aRow['type'];
    $row[] = $aRow['brand_names'];
    $row[] = $aRow['category_names'];
    $row[] = '<div class="text-center"><a href="'.base_url('uploads/count_stock/' . $aRow['initial_file']).'" class="tip" title="" data-original-title="Download"><i class="fa fa-file-o"></i></a></div>';
    if(isset($aRow['finalized']) && $aRow['finalized']){
        $row[] = '<div class="text-center"><a href="'.admin_url('invoice_items/final_stock_excel/' . $aRow['id']).'" class="tip" title="" data-original-title="Download"><i class="fa fa-chain"></i></a></div>';
    }else{
        $row[]='';
    }
//    $row[] = $aRow['initial_file'];

    /*$options = icon_btn('invoice_items/fi/' . $aRow['id'], 'pencil-square-o', 'btn-default', [
        ]);*/
    $row[] = $options .= '<a href="finalize_count/' . $aRow['id'] . '" class="" ><span class="label label-primary inline-block"> Details</span></a>';

    $output['aaData'][] = $row;
}
//$_data = '<a href="#" onclick="edit_department(this,' . $aRow['departmentid'] . '); return false" data-name="' . $aRow['name'] . '" data-calendar-id="' . $aRow['calendar_id'] . '" data-email="' . $aRow['email'] . '" data-hide-from-client="' . $aRow['hidefromclient'] . '" data-host="' . $aRow['host'] . '" data-password="' . $ps . '" data-imap_username="' . $aRow['imap_username'] . '" data-encryption="' . $aRow['encryption'] . '" data-delete-after-import="' . $aRow['delete_after_import'] . '">' . $_data . '</a>';