<?php

defined('BASEPATH') or exit('No direct script access allowed');

$hasPermissionDelete = has_permission('payments', '', 'delete');

$aColumns = [
    'tblinvoiceservicepaymentrecords.id as id',
    'invoiceid',
    'paymentmode',
    'transactionid',
    get_sql_select_client_company(),
    'amount',
    'tblinvoiceservicepaymentrecords.date as date',
    'tblfiles.file_name as payment_attachment',
    'tblfiles.rel_type',
    'tblfiles.filetype',
    'tblfiles.id as payment_attachment_id'
];

$join = [
    'LEFT JOIN tblserviceinvoices ON tblserviceinvoices.id = tblinvoiceservicepaymentrecords.invoiceid',
    'LEFT JOIN tblclients ON tblclients.userid = tblserviceinvoices.clientid',
    'LEFT JOIN tblcurrencies ON tblcurrencies.id = tblserviceinvoices.currency',
    'LEFT JOIN tblinvoicepaymentsmodes ON tblinvoicepaymentsmodes.id = tblinvoiceservicepaymentrecords.paymentmode',
    'LEFT JOIN tblfiles ON tblfiles.rel_id = tblinvoiceservicepaymentrecords.id AND tblfiles.rel_type = "invoice_payment"',
];

$where = [];
if ($clientid != '') {
    array_push($where, 'AND tblclients.userid=' . $clientid);
}

if (!has_permission('payments', '', 'view')) {
    $whereUser = '';
    $whereUser .= 'AND (invoiceid IN (SELECT id FROM tblserviceinvoices WHERE (addedfrom=' . get_staff_user_id() . ' AND addedfrom IN (SELECT staffid FROM tblstaffpermissions JOIN tblpermissions ON tblpermissions.permissionid=tblstaffpermissions.permissionid WHERE tblpermissions.name = "invoices" AND can_view_own=1)))';
    if (get_option('allow_staff_view_invoices_assigned') == 1) {
        $whereUser .= ' OR invoiceid IN (SELECT id FROM tblserviceinvoices WHERE sale_agent=' . get_staff_user_id() . ')';
    }
    $whereUser .= ')';
    array_push($where, $whereUser);
}

$sIndexColumn = 'id';
$sTable = 'tblinvoiceservicepaymentrecords';

$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [
    'clientid',
    'symbol',
    'tblinvoicepaymentsmodes.name as payment_mode_name',
    'tblinvoicepaymentsmodes.id as paymentmodeid',
    'paymentmethod',
]);

$output = $result['output'];
$rResult = $result['rResult'];

$this->ci->load->model('payment_modes_model');
$online_modes = $this->ci->payment_modes_model->get_online_payment_modes(true);

foreach ($rResult as $aRow) {
    $row = [];

    $link = admin_url('service_payments/payment/' . $aRow['id']);


    $options = icon_btn('service_payments/payment/' . $aRow['id'], 'pencil-square-o');

    if ($hasPermissionDelete) {
        $options .= icon_btn('service_payments/delete/' . $aRow['id'], 'remove', 'btn-danger _delete');
    }

    $numberOutput = '<a href="' . $link . '">' . $aRow['id'] . '</a>';

    $numberOutput .= '<div class="row-options">';
    $numberOutput .= '<a href="' . $link . '">' . _l('view') . '</a>';
    if ($hasPermissionDelete) {
        $numberOutput .= ' | <a href="' . admin_url('service_payments/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
    }
    $numberOutput .= '</div>';

    $row[] = $numberOutput;

    $row[] = '<a href="' . admin_url('service_invoices/list_invoices/' . $aRow['invoiceid']) . '">' . format_service_invoice_number($aRow['invoiceid']) . '</a>';

    $outputPaymentMode = $aRow['payment_mode_name'];
    // Since version 1.0.1
    if (is_null($aRow['paymentmodeid'])) {
        foreach ($online_modes as $online_mode) {
            if ($aRow['paymentmode'] == $online_mode['id']) {
                $outputPaymentMode = $online_mode['name'];
            }
        }
    }
    if (!empty($aRow['paymentmethod'])) {
        $outputPaymentMode .= ' - ' . $aRow['paymentmethod'];
    }
    $row[] = $outputPaymentMode;

    $row[] = $aRow['transactionid'];

    $row[] = '<a href="' . admin_url('clients/client/' . $aRow['clientid']) . '">' . $aRow['company'] . '</a>';
    $attachmentOutput = '';
    if (!empty($aRow['payment_attachment'])) {
        $path = get_upload_path_by_type('invoice_payment') . '/' . $aRow['id'] . '/' . $aRow['payment_attachment'];
        $href_url = site_url('download/file/invoice_payment/' . $aRow['id'] . '/' . $aRow['payment_attachment']);
        $img_url = site_url('download/preview_image?path=' . protected_file_url_by_path($path, true) . '&type=' . $aRow['filetype']);
        $attachmentOutput = '<div class="preview-image"><a href="' . $href_url . '" target="_blank" data-lightbox="task-attachment" class=""><img src="' . $img_url . '" class="img img-responsive" width="100px" height="80px"></a></div>';
    }

    $row[] = $attachmentOutput;

    $row[] = format_money($aRow['amount'], $aRow['symbol']);

    $row[] = _d($aRow['date']);

    $row['DT_RowClass'] = 'has-row-options';

    $output['aaData'][] = $row;
}
