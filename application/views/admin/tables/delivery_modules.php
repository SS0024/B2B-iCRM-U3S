<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = has_permission('deliverymodules', '', 'delete');


$aColumns = [
	'tbldeliverymodules.id as id',
	'tbldeliverymodules.invoice_id as invoice_id',
	'tbldeliverymodules.customer_id as customer_id',
	'tbldeliverymodules.note as note',
	'tbldeliverymodules.date as date',
	'tbldeliverymodules.delivery_reference_no as delivery_reference_no',
	'tbldeliverymodules.sale_reference_no as sale_reference_no',
	'tbldeliverymodules.received_by as received_by',
    'tbldeliverymodules.address as delivery_module_addres',
    'tbldeliverymodulestatus.name as dstatusname',
	'tblclients.company as company',
	'tblstaff.firstname as firstname',
	'tblstaff.lastname as lastname',
    'tblfiles.rel_type as rel_type',
    'tblfiles.file_name as file_name',
    'tblfiles.filetype as filetype',
    'tblfiles.rel_id as rel_id'
    
    ];

$join = [
    'LEFT JOIN tblclients ON tbldeliverymodules.customer_id = tblclients.userid',
    'LEFT JOIN tbldeliverymodulestatus ON tbldeliverymodules.status_id = tbldeliverymodulestatus.id',
    'LEFT JOIN tblstaff ON tbldeliverymodules.delivered_by = tblstaff.staffid',
    'LEFT JOIN tblfiles ON tbldeliverymodules.id = tblfiles.rel_id AND tblfiles.rel_type = "delivery_modules"',
    ];

$where = [];
// if ($invoice_id != '') {
    // array_push($where, 'AND tblclients.userid=' . $invoice_id);
// }

// if (!has_permission('deliverymodules', '', 'view')) {
    // $whereUser = '';
    // $whereUser .= 'AND (invoiceid IN (SELECT id FROM tblinvoices WHERE (addedfrom=' . get_staff_user_id() . ' AND addedfrom IN (SELECT staffid FROM tblstaffpermissions JOIN tblpermissions ON tblpermissions.permissionid=tblstaffpermissions.permissionid WHERE tblpermissions.name = "invoices" AND can_view_own=1)))';
    // if (get_option('allow_staff_view_invoices_assigned') == 1) {
        // $whereUser .= ' OR invoiceid IN (SELECT id FROM tblinvoices WHERE sale_agent=' . get_staff_user_id() . ')';
    // }
    // $whereUser .= ')';
    // array_push($where, $whereUser);
// }
$custom_date_select = get_where_report_period();
if ($custom_date_select != '') {
    array_push($where, $custom_date_select);
}
$sIndexColumn = 'id';
$sTable       = 'tbldeliverymodules';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where);
//print_r($result);die;
$output  = $result['output'];
$rResult = $result['rResult'];



foreach ($rResult as $aRow) {
    $row = [];

    $link = admin_url('payments/payment/' . $aRow['id']);


    $options = icon_btn('payments/payment/' . $aRow['id'], 'pencil-square-o');

    if ($hasPermissionDelete) {
        $options .= icon_btn('payments/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }

    $numberOutput = '<a href="' . $link . '">' . $aRow['id'] . '</a>';

    $numberOutput .= '<div class="row-options">';
    $numberOutput .= '<a href="' . $link . '">' . _l('view') . '</a>';
    if ($hasPermissionDelete) {
        $numberOutput .= ' | <a href="' . admin_url('payments/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $aRow['id'];

    $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['sale_reference_no']) . '">' . $aRow['sale_reference_no']. '</a>';

   

    $row[] = $aRow['delivery_reference_no'];
    $row[] = $aRow['firstname'].' '.$aRow['lastname'];
    $row[] = $aRow['received_by'];
    $row[] = $aRow['company'];
    $row[] = $aRow['dstatusname'];
    $row[] = _d($aRow['date']);
    
    $row[] = $aRow['delivery_module_addres'];
	
	
	$attachmentOutput = '';
    if (!empty($aRow['file_name'])) {
       
	   
	   
		$path = get_upload_path_by_type('delivery_modules').'/'.$aRow['rel_id'].'/'.$aRow['file_name'];
						
		$href_url = site_url('download/file/delivery_modules/'.$aRow['rel_id'].'/'.$aRow['file_name']);
						
		$img_url = site_url('download/preview_image?path='.protected_file_url_by_path($path,true).'&type='.$aRow['filetype']);
					 
					
		$attachmentOutput = '<div class="preview-image"><a href="'.$href_url.'" target="_blank" data-lightbox="task-attachment" class=""><img src="'.$img_url.'" class="img img-responsive" width="100px" height="80px"></a></div>';
	   
	   

       
    }
	
	$row[] = $attachmentOutput;
	
	
	$row[] = $aRow['note'];

    // $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';

    // $row[] = format_money($aRow['amount'], $aRow['symbol']);

    // $row[] = _d($aRow['date']);

    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}
