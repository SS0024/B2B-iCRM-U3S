<?php

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _l('invoice_pdf_heading') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;"># ' . $invoice_number . '</b><br>';
$info_right_column .= '<span style="color:#4e4e4e;">(ORIGINAL FOR RECIPIENT)</span>';

if (get_option('show_status_on_pdf_ei') == 1) {
    $info_right_column .= '<br /><span style="color:rgb(' . invoice_status_color_pdf($status) . ');text-transform:uppercase;">' . format_invoice_status($status, '', false) . '</span>';
}

if ($status != 2 && $status != 5 && get_option('show_pay_link_to_invoice_pdf') == 1
    && found_invoice_mode($payment_modes, $invoice->id, false)) {
    $info_right_column .= ' - <a style="color:#84c529;text-decoration:none;text-transform:uppercase;" href="' . site_url('invoice/' . $invoice->id . '/' . $invoice->hash) . '"><1b>' . _l('view_invoice_pdf_link_pay') . '</1b></a>';
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(10);
$custom_pdf_logo_image_url = get_option('custom_pdf_logo_image_url');
$width                     = 320;
if (get_option('company_logo_dark') != '' && file_exists(get_upload_path_by_type('company') . get_option('company_logo_dark'))) {
    $cimg = get_upload_path_by_type('company') . get_option('company_logo_dark');
} elseif (get_option('company_logo') != '' && file_exists(get_upload_path_by_type('company') . get_option('company_logo'))) {
    $cimg = get_upload_path_by_type('company') . get_option('company_logo');
} else {
    $cimg = '';
}
$logo_url = '';
if ($cimg != '') {
    $logo_url = '<img width="' . $width . 'px" src="' . $cimg . '"><br>';
}
$organization_info = '<div style="color:#424242;">';
$organization_info .=$logo_url;
$organization_info .= format_organization_info();
$organization_info .= '</div>';

// Bill to
$invoice_info = '<b>' . _l('invoice_bill_to') . '</b>';
$invoice_info .= '<div style="color:#424242;">';
$invoice_info .= format_customer_info($invoice, 'invoice', 'billing');
$invoice_info .= '</div>';

// ship to to
if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
    $invoice_info .= '<br /><b>' . _l('ship_to') . '</b>';
    $invoice_info .= '<div style="color:#424242;">';
    $invoice_info .= format_customer_info($invoice, 'invoice', 'shipping');
    $invoice_info .= '</div>';
}

$invoice_info .= '<br />' . _l('invoice_data_date') . ' ' . _d($invoice->date) . '<br />';

if (!empty($invoice->duedate)) {
    $invoice_info .= _l('invoice_data_duedate') . ' ' . _d($invoice->duedate) . '<br />';
}
if($invoice->purchaseorder_id){
    $invoice_info .= _l('Purchase Order:') . ' ' . format_purchaseorder_number($invoice->purchaseorder_id) . '<br />';
}
if ($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1) {
    $invoice_info .= _l('sale_agent_string') . ': ' . get_staff_full_name($invoice->sale_agent) . '<br />';
}

if ($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1) {
    $invoice_info .= _l('project') . ': ' . get_project_name_by_id($invoice->project_id) . '<br />';
}

foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
    if ($value == '') {
        continue;
    }
    $invoice_info .= $field['name'] . ': ' . $value . '<br />';
}

$left_info = $swap == '1' ? $invoice_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $invoice_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// The Table
$pdf->Ln(do_action('pdf_info_and_table_separator', 6));
$item_width = 38;

// If show item taxes is disabled in PDF we should increase the item width table heading
$item_width = get_option('show_tax_per_item') == 0 ? $item_width + 12 : $item_width;

$custom_fields_items = get_items_custom_fields_for_table_html($invoice->id, 'invoice');
// Calculate headings width, in case there are custom fields for items
$total_headings = get_option('show_tax_per_item') == 1 ? 4 : 3;
$total_headings += count($custom_fields_items);
$headings_width = (100 - ($item_width + 15)) / $total_headings;

// Header
$qty_heading = _l('invoice_table_quantity_heading');
if ($invoice->show_quantity_as == 2) {
    $qty_heading = _l('invoice_table_hours_heading');
} elseif ($invoice->show_quantity_as == 3) {
    $qty_heading = _l('invoice_table_quantity_heading') . '/' . _l('invoice_table_hours_heading');
}
$isDiscountDisplay = 0;
foreach ($invoice->items as $item) {
    if($invoice->is_bulk==0) {
        if ($item['item_discount'] > 0) {
            $isDiscountDisplay = 1;
            $itemPer = 30;
            $hsnPer = 15;
        } else {
            $itemPer = 35;
            $hsnPer = 20;
        }
    } else{
        $itemPer = 40;
        $hsnPer = 25;
    }
}
$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';

$tblhtml .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';

$tblhtml .= '<th align="center" width="5%">#</th>';
$tblhtml .= '<th align="left" width="'.$itemPer.'%">' . _l('invoice_table_item_heading') . '</th>';

foreach ($custom_fields_items as $cf) {
    $tblhtml .= '<th align="left"  width="'.$hsnPer.'%">' . $cf['name'] . '</th>';
}

$tblhtml .= '<th align="right" width="'.($invoice->is_bulk ? 15 : 10).'%">' . $qty_heading . '</th>';
if($invoice->is_bulk==0){
    $tblhtml .= '<th align="right" width="10%">' . _l('invoice_table_rate_heading') . '</th>';
}
if (get_option('show_tax_per_item') == 1) {
    $tblhtml .= '<th align="right" width="'.($invoice->is_bulk ? 15 : 10).'%">' . _l('invoice_table_tax_heading') . '</th>';
}

if($invoice->is_bulk==0){
    if($isDiscountDisplay) {
        $tblhtml .= '<th align="right" width="10%">' . _l('invoice_table_discount_heading') . '</th>';
    }
    $tblhtml .= '<th align="right" width="10%">' . _l('invoice_table_amount_heading') . '</th>';
}
$tblhtml .= '</tr>';

// Items
$tblhtml .= '<tbody>';
$items_data = get_table_items_and_taxes($invoice->items,'invoice', false,$invoice->is_bulk,$isDiscountDisplay);

$tblhtml .= $items_data['html'];
$taxes = $items_data['taxes'];

$tblhtml .= '</tbody>';
$tblhtml .= '</table>';

$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(8);

$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';

$tbltotal .= '
<tr>
    <td align="right" width="85%"><strong>' . _l('invoice_subtotal') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->subtotal, $invoice->symbol) . '</td>
</tr>';

if (is_sale_discount_applied($invoice)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_discount');
    if (is_sale_discount($invoice, 'percent')) {
        $tbltotal .= '(' . _format_number($invoice->discount_percent, true) . '%)';
    }
    $tbltotal .= '</strong>';
    $tbltotal .= '</td>';
    $tbltotal .= '<td align="right" width="15%">-' . format_money($invoice->discount_total, $invoice->symbol) . '</td>
    </tr>';
}

$tbltotal .= '<tr>
    <td align="right" width="85%"><strong>'._l('estimate_packing_and_forwarding').'</strong></td>
    <td align="right" width="15%">' . format_money($invoice->packing_and_forwarding,$invoice->symbol) . '</td>
</tr>';

$tbltotal .= '<tr>
    <td align="right" width="85%"><strong>'._l('estimate_servicecharge').'</strong></td>
    <td align="right" width="15%">' . format_money($invoice->servicecharge,$invoice->symbol) . '</td>
</tr>';

if($invoice->devide_gst == 1 &&  $invoice->total_tax != 0){
    $cgst = $invoice->total_tax/2;
    $sgst = $invoice->total_tax/2;

    $tbltotal .= '<tr><td align="right" width="85%"><strong>CGST</strong></td><td align="right" width="15%">'.format_money($cgst, $invoice->symbol).'</td></tr>';
    $tbltotal .= '<tr><td align="right" width="85%"><strong>SGST</strong></td><td align="right" width="15%">'.format_money($sgst, $invoice->symbol).'</td></tr>';
}else{
    $tbltotal .= '<tr><td align="right" width="85%"><strong>IGST</strong></td><td align="right" width="15%">'.format_money($invoice->total_tax, $invoice->symbol).'</td></tr>';
}

$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('invoice_total') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->total, $invoice->symbol) . '</td>
</tr>';

if (count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_total_paid') . '</strong></td>
        <td align="right" width="15%">-' . format_money(sum_from_table('tblinvoicepaymentrecords', [
            'field' => 'amount',
            'where' => [
                'invoiceid' => $invoice->id,
            ],
        ]), $invoice->symbol) . '</td>
    </tr>';
}

if (get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('applied_credits') . '</strong></td>
        <td align="right" width="15%">-' . format_money($credits_applied, $invoice->symbol) . '</td>
    </tr>';
}

if (get_option('show_amount_due_on_invoice') == 1 && $invoice->status != 5) {
    $tbltotal .= '<tr style="background-color:#f0f0f0;">
       <td align="right" width="85%"><strong>' . _l('invoice_amount_due') . '</strong></td>
       <td align="right" width="15%">' . format_money($invoice->total_left_to_pay, $invoice->symbol) . '</td>
   </tr>';
}

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');

$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';

// Items
$tblhtml .= '<tbody>';

$items_data = get_table_items_and_taxes($invoice->items, 'invoice');

if(isset($items_data['tax_qty'])) {
    $tax_qtys = $items_data['tax_qty'];

    if(!empty($tax_qtys)) {

        $tblhtml .= '<div style="font-weight:bold;"> Tax Summary</div>';

        $tblhtml .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';
        $tblhtml .= '<thead>';
        $tblhtml .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';
        $tblhtml .= '<th align="left">#</th>';

        $tblhtml .= '<th align="left">Name</th>';

        $tblhtml .= '<th align="left">Code</th>';

        $tblhtml .= '<th align="left">Qty</th>';

        $tblhtml .= '<th align="left">Tax Excl Amt</th>';

        $tblhtml .= '<th align="left">Tax Amount</th>';
        $tblhtml .= '</tr>';
        $tblhtml .= '</thead>';
        $tblhtml .= '<tbody>';

        $i = 1;
        $tax_total = 0;
        if(isset($invoice->servicecharge) && $invoice->servicecharge > 0 && !empty($invoice->service_charge_tax_rate)){
            $service_charge_tax	=	$invoice->servicecharge * $invoice->service_charge_tax_rate/100;
            $tax_total = $service_charge_tax;
            $tblhtml .= '<tr>';
            $tblhtml .= '<td>'.$i.'</td>';
            $tblhtml .= '<td>'.'GST@'.$invoice->service_charge_tax_rate.'%|'.$invoice->service_charge_tax_rate.'</td>';
            $tblhtml .= '<td>'.'GST@'.$invoice->service_charge_tax_rate.'%|'.$invoice->service_charge_tax_rate.'</td>';
            $tblhtml .= '<td>1</td>';
            $tblhtml .= '<td>'.format_money($invoice->servicecharge,$invoice->symbol).'</td>';
            $tblhtml .= '<td>'.format_money($service_charge_tax,$invoice->symbol).'</td>';
            $tblhtml .= '</tr>';
            $i++;
        }

        foreach($tax_qtys as $key => $tax_qty) {
            $tblhtml .= '<tr>';
            $tblhtml .= '<td>'.$i.'</td>';
            $tblhtml .= '<td>'.$tax_qty['tax_name'].'</td>';
            $tblhtml .= '<td>'.$tax_qty['tax_name'].'</td>';
            $tblhtml .= '<td>'.$tax_qty['total_qtys'].'</td>';

            if(isset($invoice->packing_and_forwarding) && $invoice->packing_and_forwarding > 0){
                $packing_and_forwording = (($invoice->packing_and_forwarding/$invoice->subtotal)*$tax_qty['total_prcs'])+$tax_qty['total_prcs'];
                $tax = $packing_and_forwording * $key / 100;
                $tax_total = $tax_total + $tax;
                $tblhtml .= '<td>'. format_money($packing_and_forwording, $invoice->symbol).'</td>';
                $tblhtml .= '<td>'.format_money($tax, $invoice->symbol).'</td>';
            }else{
                $tblhtml .= '<td>'.format_money($tax_qty['total_prcs'],$invoice->symbol).'</td>';
                $tblhtml .= '<td>'. format_money($tax_qty['total_tprs'],$invoice->symbol).'</td>';
                $tax_total = $tax_total + $tax_qty['total_tprs'];
            }

            $tblhtml .= '</tr>';
            $i++;
        }

        if($tax_total > 0){
            $tblhtml .= '<tr><td align="right" width="78%" colspan="5"><strong>Total</strong></td><td align="right" width="15%">'.format_money($tax_total, $invoice->symbol).'</td></tr>';
        }

        $tblhtml .= '</tbody></table>';
    }
}
$tblhtml .= '</tbody>';
$tblhtml .= '</table>';
$pdf->writeHTML($tblhtml, true, false, false, false, '');

if (get_option('total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', 8);
    $pdf->Cell(0, 0, _l('num_word') . ': ' . $CI->numberword->convert($invoice->total, $invoice->currency_name), 0, 1, 'C', 0, '', 0);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name, '', 2);
    $pdf->Ln(4);
}

if (count($invoice->payments) > 0 && get_option('show_transactions_on_invoice_pdf') == 1) {
    $pdf->Ln(4);
    $border = 'border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid; 1px solid black;';
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_received_payments'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
    $tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">
        <tr height="20"  style="color:#000;border:1px solid #000;">
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_number_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_mode_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_date_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_amount_heading') . '</th>
    </tr>';
    $tblhtml .= '<tbody>';
    foreach ($invoice->payments as $payment) {
        $payment_name = $payment['name'];
        if (!empty($payment['paymentmethod'])) {
            $payment_name .= ' - ' . $payment['paymentmethod'];
        }
        $tblhtml .= '
            <tr>
            <td>' . $payment['paymentid'] . '</td>
            <td>' . $payment_name . '</td>
            <td>' . _d($payment['date']) . '</td>
            <td>' . format_money($payment['amount'], $invoice->symbol) . '</td>
            </tr>
        ';
    }
    $tblhtml .= '</tbody>';
    $tblhtml .= '</table>';
    $pdf->writeHTML($tblhtml, true, false, false, false, '');
}

if (found_invoice_mode($payment_modes, $invoice->id, true, true)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', 6);
    $pdf->Cell(0, 0, _l('invoice_html_offline_payment'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', 2);

    foreach ($payment_modes as $mode) {
        if (is_numeric($mode['id'])) {
            if (!is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) {
                continue;
            }
        }
        if (isset($mode['show_on_pdf']) && $mode['show_on_pdf'] == 1) {
            $pdf->Ln(1);
            $pdf->Cell(0, 0, $mode['name'], 0, 1, 'L', 0, '', 0);
            $pdf->Ln(2);
            $pdf->writeHTMLCell('', '', '', '', $mode['description'], 0, 1, false, true, 'L', true);
        }
    }
}

if (!empty($invoice->clientnote)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_note'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $invoice->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($invoice->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $invoice->terms, 0, 1, false, true, 'L', true);
}
$pdf->AddPage();

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column = '';

$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _l('invoice_pdf_heading') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;"># ' . $invoice_number . '</b><br>';
$info_right_column .= '<span style="color:#4e4e4e;">(DUPLICATE FOR TRANSPORTER)</span>';

if (get_option('show_status_on_pdf_ei') == 1) {
    $info_right_column .= '<br /><span style="color:rgb(' . invoice_status_color_pdf($status) . ');text-transform:uppercase;">' . format_invoice_status($status, '', false) . '</span>';
}

if ($status != 2 && $status != 5 && get_option('show_pay_link_to_invoice_pdf') == 1
    && found_invoice_mode($payment_modes, $invoice->id, false)) {
    $info_right_column .= ' - <a style="color:#84c529;text-decoration:none;text-transform:uppercase;" href="' . site_url('invoice/' . $invoice->id . '/' . $invoice->hash) . '"><1b>' . _l('view_invoice_pdf_link_pay') . '</1b></a>';
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(10);
$custom_pdf_logo_image_url = get_option('custom_pdf_logo_image_url');
$width = 320;
if (get_option('company_logo_dark') != '' && file_exists(get_upload_path_by_type('company') . get_option('company_logo_dark'))) {
    $cimg = get_upload_path_by_type('company') . get_option('company_logo_dark');
} elseif (get_option('company_logo') != '' && file_exists(get_upload_path_by_type('company') . get_option('company_logo'))) {
    $cimg = get_upload_path_by_type('company') . get_option('company_logo');
} else {
    $cimg = '';
}
$logo_url = '';
if ($cimg != '') {
    $logo_url = '<img width="' . $width . 'px" src="' . $cimg . '"><br>';
}
$organization_info = '<div style="color:#424242;">';
$organization_info .= $logo_url;
$organization_info .= format_organization_info();
$organization_info .= '</div>';

// Bill to
$invoice_info = '<b>' . _l('invoice_bill_to') . '</b>';
$invoice_info .= '<div style="color:#424242;">';
$invoice_info .= format_customer_info($invoice, 'invoice', 'billing');
$invoice_info .= '</div>';

// ship to to
if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
    $invoice_info .= '<br /><b>' . _l('ship_to') . '</b>';
    $invoice_info .= '<div style="color:#424242;">';
    $invoice_info .= format_customer_info($invoice, 'invoice', 'shipping');
    $invoice_info .= '</div>';
}

$invoice_info .= '<br />' . _l('invoice_data_date') . ' ' . _d($invoice->date) . '<br />';

if (!empty($invoice->duedate)) {
    $invoice_info .= _l('invoice_data_duedate') . ' ' . _d($invoice->duedate) . '<br />';
}
if($invoice->purchaseorder_id){
    $invoice_info .= _l('Purchase Order:') . ' ' . format_purchaseorder_number($invoice->purchaseorder_id) . '<br />';
}
if ($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1) {
    $invoice_info .= _l('sale_agent_string') . ': ' . get_staff_full_name($invoice->sale_agent) . '<br />';
}

if ($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1) {
    $invoice_info .= _l('project') . ': ' . get_project_name_by_id($invoice->project_id) . '<br />';
}

foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
    if ($value == '') {
        continue;
    }
    $invoice_info .= $field['name'] . ': ' . $value . '<br />';
}

$left_info = $swap == '1' ? $invoice_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $invoice_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// The Table
$pdf->Ln(do_action('pdf_info_and_table_separator', 6));
$item_width = 38;

// If show item taxes is disabled in PDF we should increase the item width table heading
$item_width = get_option('show_tax_per_item') == 0 ? $item_width + 12 : $item_width;

$custom_fields_items = get_items_custom_fields_for_table_html($invoice->id, 'invoice');
// Calculate headings width, in case there are custom fields for items
$total_headings = get_option('show_tax_per_item') == 1 ? 4 : 3;
$total_headings += count($custom_fields_items);
$headings_width = (100 - ($item_width + 15)) / $total_headings;

// Header
$qty_heading = _l('invoice_table_quantity_heading');
if ($invoice->show_quantity_as == 2) {
    $qty_heading = _l('invoice_table_hours_heading');
} elseif ($invoice->show_quantity_as == 3) {
    $qty_heading = _l('invoice_table_quantity_heading') . '/' . _l('invoice_table_hours_heading');
}
$isDiscountDisplay = 0;
foreach ($invoice->items as $item) {
    if ($invoice->is_bulk == 0) {
        if ($item['item_discount'] > 0) {
            $isDiscountDisplay = 1;
            $itemPer = 30;
            $hsnPer = 15;
        } else {
            $itemPer = 35;
            $hsnPer = 20;
        }
    } else {
        $itemPer = 40;
        $hsnPer = 25;
    }
}
$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';

$tblhtml .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';

$tblhtml .= '<th align="center" width="5%">#</th>';
$tblhtml .= '<th align="left" width="' . $itemPer . '%">' . _l('invoice_table_item_heading') . '</th>';

foreach ($custom_fields_items as $cf) {
    $tblhtml .= '<th align="left"  width="' . $hsnPer . '%">' . $cf['name'] . '</th>';
}

$tblhtml .= '<th align="right" width="' . ($invoice->is_bulk ? 15 : 10) . '%">' . $qty_heading . '</th>';
if ($invoice->is_bulk == 0) {
    $tblhtml .= '<th align="right" width="10%">' . _l('invoice_table_rate_heading') . '</th>';
}
if (get_option('show_tax_per_item') == 1) {
    $tblhtml .= '<th align="right" width="' . ($invoice->is_bulk ? 15 : 10) . '%">' . _l('invoice_table_tax_heading') . '</th>';
}

if ($invoice->is_bulk == 0) {
    if ($isDiscountDisplay) {
        $tblhtml .= '<th align="right" width="10%">' . _l('invoice_table_discount_heading') . '</th>';
    }
    $tblhtml .= '<th align="right" width="10%">' . _l('invoice_table_amount_heading') . '</th>';
}
$tblhtml .= '</tr>';

// Items
$tblhtml .= '<tbody>';
$items_data = get_table_items_and_taxes($invoice->items, 'invoice', false, $invoice->is_bulk, $isDiscountDisplay);

$tblhtml .= $items_data['html'];
$taxes = $items_data['taxes'];

$tblhtml .= '</tbody>';
$tblhtml .= '</table>';

$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(8);

$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';

$tbltotal .= '
<tr>
    <td align="right" width="85%"><strong>' . _l('invoice_subtotal') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->subtotal, $invoice->symbol) . '</td>
</tr>';

if (is_sale_discount_applied($invoice)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_discount');
    if (is_sale_discount($invoice, 'percent')) {
        $tbltotal .= '(' . _format_number($invoice->discount_percent, true) . '%)';
    }
    $tbltotal .= '</strong>';
    $tbltotal .= '</td>';
    $tbltotal .= '<td align="right" width="15%">-' . format_money($invoice->discount_total, $invoice->symbol) . '</td>
    </tr>';
}

$tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('estimate_packing_and_forwarding') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->packing_and_forwarding, $invoice->symbol) . '</td>
</tr>';

$tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('estimate_servicecharge') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->servicecharge, $invoice->symbol) . '</td>
</tr>';

if ($invoice->devide_gst == 1 && $invoice->total_tax != 0) {
    $cgst = $invoice->total_tax / 2;
    $sgst = $invoice->total_tax / 2;

    $tbltotal .= '<tr><td align="right" width="85%"><strong>CGST</strong></td><td align="right" width="15%">' . format_money($cgst, $invoice->symbol) . '</td></tr>';
    $tbltotal .= '<tr><td align="right" width="85%"><strong>SGST</strong></td><td align="right" width="15%">' . format_money($sgst, $invoice->symbol) . '</td></tr>';
} else {
    $tbltotal .= '<tr><td align="right" width="85%"><strong>IGST</strong></td><td align="right" width="15%">' . format_money($invoice->total_tax, $invoice->symbol) . '</td></tr>';
}

$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('invoice_total') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->total, $invoice->symbol) . '</td>
</tr>';

if (count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_total_paid') . '</strong></td>
        <td align="right" width="15%">-' . format_money(sum_from_table('tblinvoicepaymentrecords', [
            'field' => 'amount',
            'where' => [
                'invoiceid' => $invoice->id,
            ],
        ]), $invoice->symbol) . '</td>
    </tr>';
}

if (get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('applied_credits') . '</strong></td>
        <td align="right" width="15%">-' . format_money($credits_applied, $invoice->symbol) . '</td>
    </tr>';
}

if (get_option('show_amount_due_on_invoice') == 1 && $invoice->status != 5) {
    $tbltotal .= '<tr style="background-color:#f0f0f0;">
       <td align="right" width="85%"><strong>' . _l('invoice_amount_due') . '</strong></td>
       <td align="right" width="15%">' . format_money($invoice->total_left_to_pay, $invoice->symbol) . '</td>
   </tr>';
}

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');

$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';

// Items
$tblhtml .= '<tbody>';

$items_data = get_table_items_and_taxes($invoice->items, 'invoice');

if (isset($items_data['tax_qty'])) {
    $tax_qtys = $items_data['tax_qty'];

    if (!empty($tax_qtys)) {

        $tblhtml .= '<div style="font-weight:bold;"> Tax Summary</div>';

        $tblhtml .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';
        $tblhtml .= '<thead>';
        $tblhtml .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';
        $tblhtml .= '<th align="left">#</th>';

        $tblhtml .= '<th align="left">Name</th>';

        $tblhtml .= '<th align="left">Code</th>';

        $tblhtml .= '<th align="left">Qty</th>';

        $tblhtml .= '<th align="left">Tax Excl Amt</th>';

        $tblhtml .= '<th align="left">Tax Amount</th>';
        $tblhtml .= '</tr>';
        $tblhtml .= '</thead>';
        $tblhtml .= '<tbody>';

        $i = 1;
        $tax_total = 0;
        if (isset($invoice->servicecharge) && $invoice->servicecharge > 0 && !empty($invoice->service_charge_tax_rate)) {
            $service_charge_tax = $invoice->servicecharge * $invoice->service_charge_tax_rate / 100;
            $tax_total = $service_charge_tax;
            $tblhtml .= '<tr>';
            $tblhtml .= '<td>' . $i . '</td>';
            $tblhtml .= '<td>' . 'GST@' . $invoice->service_charge_tax_rate . '%|' . $invoice->service_charge_tax_rate . '</td>';
            $tblhtml .= '<td>' . 'GST@' . $invoice->service_charge_tax_rate . '%|' . $invoice->service_charge_tax_rate . '</td>';
            $tblhtml .= '<td>1</td>';
            $tblhtml .= '<td>' . format_money($invoice->servicecharge, $invoice->symbol) . '</td>';
            $tblhtml .= '<td>' . format_money($service_charge_tax, $invoice->symbol) . '</td>';
            $tblhtml .= '</tr>';
            $i++;
        }

        foreach ($tax_qtys as $key => $tax_qty) {
            $tblhtml .= '<tr>';
            $tblhtml .= '<td>' . $i . '</td>';
            $tblhtml .= '<td>' . $tax_qty['tax_name'] . '</td>';
            $tblhtml .= '<td>' . $tax_qty['tax_name'] . '</td>';
            $tblhtml .= '<td>' . $tax_qty['total_qtys'] . '</td>';

            if (isset($invoice->packing_and_forwarding) && $invoice->packing_and_forwarding > 0) {
                $packing_and_forwording = (($invoice->packing_and_forwarding / $invoice->subtotal) * $tax_qty['total_prcs']) + $tax_qty['total_prcs'];
                $tax = $packing_and_forwording * $key / 100;
                $tax_total = $tax_total + $tax;
                $tblhtml .= '<td>' . format_money($packing_and_forwording, $invoice->symbol) . '</td>';
                $tblhtml .= '<td>' . format_money($tax, $invoice->symbol) . '</td>';
            } else {
                $tblhtml .= '<td>' . format_money($tax_qty['total_prcs'], $invoice->symbol) . '</td>';
                $tblhtml .= '<td>' . format_money($tax_qty['total_tprs'], $invoice->symbol) . '</td>';
                $tax_total = $tax_total + $tax_qty['total_tprs'];
            }

            $tblhtml .= '</tr>';
            $i++;
        }

        if ($tax_total > 0) {
            $tblhtml .= '<tr><td align="right" width="78%" colspan="5"><strong>Total</strong></td><td align="right" width="15%">' . format_money($tax_total, $invoice->symbol) . '</td></tr>';
        }

        $tblhtml .= '</tbody></table>';
    }
}
$tblhtml .= '</tbody>';
$tblhtml .= '</table>';
$pdf->writeHTML($tblhtml, true, false, false, false, '');

if (get_option('total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', 8);
    $pdf->Cell(0, 0, _l('num_word') . ': ' . $CI->numberword->convert($invoice->total, $invoice->currency_name), 0, 1, 'C', 0, '', 0);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name, '', 2);
    $pdf->Ln(4);
}

if (count($invoice->payments) > 0 && get_option('show_transactions_on_invoice_pdf') == 1) {
    $pdf->Ln(4);
    $border = 'border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid; 1px solid black;';
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_received_payments'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
    $tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">
        <tr height="20"  style="color:#000;border:1px solid #000;">
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_number_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_mode_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_date_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_amount_heading') . '</th>
    </tr>';
    $tblhtml .= '<tbody>';
    foreach ($invoice->payments as $payment) {
        $payment_name = $payment['name'];
        if (!empty($payment['paymentmethod'])) {
            $payment_name .= ' - ' . $payment['paymentmethod'];
        }
        $tblhtml .= '
            <tr>
            <td>' . $payment['paymentid'] . '</td>
            <td>' . $payment_name . '</td>
            <td>' . _d($payment['date']) . '</td>
            <td>' . format_money($payment['amount'], $invoice->symbol) . '</td>
            </tr>
        ';
    }
    $tblhtml .= '</tbody>';
    $tblhtml .= '</table>';
    $pdf->writeHTML($tblhtml, true, false, false, false, '');
}

if (found_invoice_mode($payment_modes, $invoice->id, true, true)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', 6);
    $pdf->Cell(0, 0, _l('invoice_html_offline_payment'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', 2);

    foreach ($payment_modes as $mode) {
        if (is_numeric($mode['id'])) {
            if (!is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) {
                continue;
            }
        }
        if (isset($mode['show_on_pdf']) && $mode['show_on_pdf'] == 1) {
            $pdf->Ln(1);
            $pdf->Cell(0, 0, $mode['name'], 0, 1, 'L', 0, '', 0);
            $pdf->Ln(2);
            $pdf->writeHTMLCell('', '', '', '', $mode['description'], 0, 1, false, true, 'L', true);
        }
    }
}

if (!empty($invoice->clientnote)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_note'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $invoice->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($invoice->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $invoice->terms, 0, 1, false, true, 'L', true);
}
$pdf->AddPage();

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column = '';

$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _l('invoice_pdf_heading') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;"># ' . $invoice_number . '</b><br>';
$info_right_column .= '<span style="color:#4e4e4e;">(TRIPLICATE FOR SUPPLIER)</span>';

if (get_option('show_status_on_pdf_ei') == 1) {
    $info_right_column .= '<br /><span style="color:rgb(' . invoice_status_color_pdf($status) . ');text-transform:uppercase;">' . format_invoice_status($status, '', false) . '</span>';
}

if ($status != 2 && $status != 5 && get_option('show_pay_link_to_invoice_pdf') == 1
    && found_invoice_mode($payment_modes, $invoice->id, false)) {
    $info_right_column .= ' - <a style="color:#84c529;text-decoration:none;text-transform:uppercase;" href="' . site_url('invoice/' . $invoice->id . '/' . $invoice->hash) . '"><1b>' . _l('view_invoice_pdf_link_pay') . '</1b></a>';
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(10);
$custom_pdf_logo_image_url = get_option('custom_pdf_logo_image_url');
$width = 320;
if (get_option('company_logo_dark') != '' && file_exists(get_upload_path_by_type('company') . get_option('company_logo_dark'))) {
    $cimg = get_upload_path_by_type('company') . get_option('company_logo_dark');
} elseif (get_option('company_logo') != '' && file_exists(get_upload_path_by_type('company') . get_option('company_logo'))) {
    $cimg = get_upload_path_by_type('company') . get_option('company_logo');
} else {
    $cimg = '';
}
$logo_url = '';
if ($cimg != '') {
    $logo_url = '<img width="' . $width . 'px" src="' . $cimg . '"><br>';
}
$organization_info = '<div style="color:#424242;">';
$organization_info .= $logo_url;
$organization_info .= format_organization_info();
$organization_info .= '</div>';

// Bill to
$invoice_info = '<b>' . _l('invoice_bill_to') . '</b>';
$invoice_info .= '<div style="color:#424242;">';
$invoice_info .= format_customer_info($invoice, 'invoice', 'billing');
$invoice_info .= '</div>';

// ship to to
if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
    $invoice_info .= '<br /><b>' . _l('ship_to') . '</b>';
    $invoice_info .= '<div style="color:#424242;">';
    $invoice_info .= format_customer_info($invoice, 'invoice', 'shipping');
    $invoice_info .= '</div>';
}

$invoice_info .= '<br />' . _l('invoice_data_date') . ' ' . _d($invoice->date) . '<br />';

if (!empty($invoice->duedate)) {
    $invoice_info .= _l('invoice_data_duedate') . ' ' . _d($invoice->duedate) . '<br />';
}
if($invoice->purchaseorder_id){
    $invoice_info .= _l('Purchase Order:') . ' ' . format_purchaseorder_number($invoice->purchaseorder_id) . '<br />';
}
if ($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1) {
    $invoice_info .= _l('sale_agent_string') . ': ' . get_staff_full_name($invoice->sale_agent) . '<br />';
}

if ($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1) {
    $invoice_info .= _l('project') . ': ' . get_project_name_by_id($invoice->project_id) . '<br />';
}

foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
    if ($value == '') {
        continue;
    }
    $invoice_info .= $field['name'] . ': ' . $value . '<br />';
}

$left_info = $swap == '1' ? $invoice_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $invoice_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// The Table
$pdf->Ln(do_action('pdf_info_and_table_separator', 6));
$item_width = 38;

// If show item taxes is disabled in PDF we should increase the item width table heading
$item_width = get_option('show_tax_per_item') == 0 ? $item_width + 12 : $item_width;

$custom_fields_items = get_items_custom_fields_for_table_html($invoice->id, 'invoice');
// Calculate headings width, in case there are custom fields for items
$total_headings = get_option('show_tax_per_item') == 1 ? 4 : 3;
$total_headings += count($custom_fields_items);
$headings_width = (100 - ($item_width + 15)) / $total_headings;

// Header
$qty_heading = _l('invoice_table_quantity_heading');
if ($invoice->show_quantity_as == 2) {
    $qty_heading = _l('invoice_table_hours_heading');
} elseif ($invoice->show_quantity_as == 3) {
    $qty_heading = _l('invoice_table_quantity_heading') . '/' . _l('invoice_table_hours_heading');
}
$isDiscountDisplay = 0;
foreach ($invoice->items as $item) {
    if ($invoice->is_bulk == 0) {
        if ($item['item_discount'] > 0) {
            $isDiscountDisplay = 1;
            $itemPer = 30;
            $hsnPer = 15;
        } else {
            $itemPer = 35;
            $hsnPer = 20;
        }
    } else {
        $itemPer = 40;
        $hsnPer = 25;
    }
}
$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';

$tblhtml .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';

$tblhtml .= '<th align="center" width="5%">#</th>';
$tblhtml .= '<th align="left" width="' . $itemPer . '%">' . _l('invoice_table_item_heading') . '</th>';

foreach ($custom_fields_items as $cf) {
    $tblhtml .= '<th align="left"  width="' . $hsnPer . '%">' . $cf['name'] . '</th>';
}

$tblhtml .= '<th align="right" width="' . ($invoice->is_bulk ? 15 : 10) . '%">' . $qty_heading . '</th>';
if ($invoice->is_bulk == 0) {
    $tblhtml .= '<th align="right" width="10%">' . _l('invoice_table_rate_heading') . '</th>';
}
if (get_option('show_tax_per_item') == 1) {
    $tblhtml .= '<th align="right" width="' . ($invoice->is_bulk ? 15 : 10) . '%">' . _l('invoice_table_tax_heading') . '</th>';
}

if ($invoice->is_bulk == 0) {
    if ($isDiscountDisplay) {
        $tblhtml .= '<th align="right" width="10%">' . _l('invoice_table_discount_heading') . '</th>';
    }
    $tblhtml .= '<th align="right" width="10%">' . _l('invoice_table_amount_heading') . '</th>';
}
$tblhtml .= '</tr>';

// Items
$tblhtml .= '<tbody>';
$items_data = get_table_items_and_taxes($invoice->items, 'invoice', false, $invoice->is_bulk, $isDiscountDisplay);

$tblhtml .= $items_data['html'];
$taxes = $items_data['taxes'];

$tblhtml .= '</tbody>';
$tblhtml .= '</table>';

$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(8);

$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';

$tbltotal .= '
<tr>
    <td align="right" width="85%"><strong>' . _l('invoice_subtotal') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->subtotal, $invoice->symbol) . '</td>
</tr>';

if (is_sale_discount_applied($invoice)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_discount');
    if (is_sale_discount($invoice, 'percent')) {
        $tbltotal .= '(' . _format_number($invoice->discount_percent, true) . '%)';
    }
    $tbltotal .= '</strong>';
    $tbltotal .= '</td>';
    $tbltotal .= '<td align="right" width="15%">-' . format_money($invoice->discount_total, $invoice->symbol) . '</td>
    </tr>';
}

$tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('estimate_packing_and_forwarding') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->packing_and_forwarding, $invoice->symbol) . '</td>
</tr>';

$tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('estimate_servicecharge') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->servicecharge, $invoice->symbol) . '</td>
</tr>';

if ($invoice->devide_gst == 1 && $invoice->total_tax != 0) {
    $cgst = $invoice->total_tax / 2;
    $sgst = $invoice->total_tax / 2;

    $tbltotal .= '<tr><td align="right" width="85%"><strong>CGST</strong></td><td align="right" width="15%">' . format_money($cgst, $invoice->symbol) . '</td></tr>';
    $tbltotal .= '<tr><td align="right" width="85%"><strong>SGST</strong></td><td align="right" width="15%">' . format_money($sgst, $invoice->symbol) . '</td></tr>';
} else {
    $tbltotal .= '<tr><td align="right" width="85%"><strong>IGST</strong></td><td align="right" width="15%">' . format_money($invoice->total_tax, $invoice->symbol) . '</td></tr>';
}

$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('invoice_total') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->total, $invoice->symbol) . '</td>
</tr>';

if (count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_total_paid') . '</strong></td>
        <td align="right" width="15%">-' . format_money(sum_from_table('tblinvoicepaymentrecords', [
            'field' => 'amount',
            'where' => [
                'invoiceid' => $invoice->id,
            ],
        ]), $invoice->symbol) . '</td>
    </tr>';
}

if (get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('applied_credits') . '</strong></td>
        <td align="right" width="15%">-' . format_money($credits_applied, $invoice->symbol) . '</td>
    </tr>';
}

if (get_option('show_amount_due_on_invoice') == 1 && $invoice->status != 5) {
    $tbltotal .= '<tr style="background-color:#f0f0f0;">
       <td align="right" width="85%"><strong>' . _l('invoice_amount_due') . '</strong></td>
       <td align="right" width="15%">' . format_money($invoice->total_left_to_pay, $invoice->symbol) . '</td>
   </tr>';
}

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');

$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';

// Items
$tblhtml .= '<tbody>';

$items_data = get_table_items_and_taxes($invoice->items, 'invoice');

if (isset($items_data['tax_qty'])) {
    $tax_qtys = $items_data['tax_qty'];

    if (!empty($tax_qtys)) {

        $tblhtml .= '<div style="font-weight:bold;"> Tax Summary</div>';

        $tblhtml .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';
        $tblhtml .= '<thead>';
        $tblhtml .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';
        $tblhtml .= '<th align="left">#</th>';

        $tblhtml .= '<th align="left">Name</th>';

        $tblhtml .= '<th align="left">Code</th>';

        $tblhtml .= '<th align="left">Qty</th>';

        $tblhtml .= '<th align="left">Tax Excl Amt</th>';

        $tblhtml .= '<th align="left">Tax Amount</th>';
        $tblhtml .= '</tr>';
        $tblhtml .= '</thead>';
        $tblhtml .= '<tbody>';

        $i = 1;
        $tax_total = 0;
        if (isset($invoice->servicecharge) && $invoice->servicecharge > 0 && !empty($invoice->service_charge_tax_rate)) {
            $service_charge_tax = $invoice->servicecharge * $invoice->service_charge_tax_rate / 100;
            $tax_total = $service_charge_tax;
            $tblhtml .= '<tr>';
            $tblhtml .= '<td>' . $i . '</td>';
            $tblhtml .= '<td>' . 'GST@' . $invoice->service_charge_tax_rate . '%|' . $invoice->service_charge_tax_rate . '</td>';
            $tblhtml .= '<td>' . 'GST@' . $invoice->service_charge_tax_rate . '%|' . $invoice->service_charge_tax_rate . '</td>';
            $tblhtml .= '<td>1</td>';
            $tblhtml .= '<td>' . format_money($invoice->servicecharge, $invoice->symbol) . '</td>';
            $tblhtml .= '<td>' . format_money($service_charge_tax, $invoice->symbol) . '</td>';
            $tblhtml .= '</tr>';
            $i++;
        }

        foreach ($tax_qtys as $key => $tax_qty) {
            $tblhtml .= '<tr>';
            $tblhtml .= '<td>' . $i . '</td>';
            $tblhtml .= '<td>' . $tax_qty['tax_name'] . '</td>';
            $tblhtml .= '<td>' . $tax_qty['tax_name'] . '</td>';
            $tblhtml .= '<td>' . $tax_qty['total_qtys'] . '</td>';

            if (isset($invoice->packing_and_forwarding) && $invoice->packing_and_forwarding > 0) {
                $packing_and_forwording = (($invoice->packing_and_forwarding / $invoice->subtotal) * $tax_qty['total_prcs']) + $tax_qty['total_prcs'];
                $tax = $packing_and_forwording * $key / 100;
                $tax_total = $tax_total + $tax;
                $tblhtml .= '<td>' . format_money($packing_and_forwording, $invoice->symbol) . '</td>';
                $tblhtml .= '<td>' . format_money($tax, $invoice->symbol) . '</td>';
            } else {
                $tblhtml .= '<td>' . format_money($tax_qty['total_prcs'], $invoice->symbol) . '</td>';
                $tblhtml .= '<td>' . format_money($tax_qty['total_tprs'], $invoice->symbol) . '</td>';
                $tax_total = $tax_total + $tax_qty['total_tprs'];
            }

            $tblhtml .= '</tr>';
            $i++;
        }

        if ($tax_total > 0) {
            $tblhtml .= '<tr><td align="right" width="78%" colspan="5"><strong>Total</strong></td><td align="right" width="15%">' . format_money($tax_total, $invoice->symbol) . '</td></tr>';
        }

        $tblhtml .= '</tbody></table>';
    }
}
$tblhtml .= '</tbody>';
$tblhtml .= '</table>';
$pdf->writeHTML($tblhtml, true, false, false, false, '');

if (get_option('total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', 8);
    $pdf->Cell(0, 0, _l('num_word') . ': ' . $CI->numberword->convert($invoice->total, $invoice->currency_name), 0, 1, 'C', 0, '', 0);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name, '', 2);
    $pdf->Ln(4);
}

if (count($invoice->payments) > 0 && get_option('show_transactions_on_invoice_pdf') == 1) {
    $pdf->Ln(4);
    $border = 'border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid; 1px solid black;';
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_received_payments'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
    $tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">
        <tr height="20"  style="color:#000;border:1px solid #000;">
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_number_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_mode_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_date_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_amount_heading') . '</th>
    </tr>';
    $tblhtml .= '<tbody>';
    foreach ($invoice->payments as $payment) {
        $payment_name = $payment['name'];
        if (!empty($payment['paymentmethod'])) {
            $payment_name .= ' - ' . $payment['paymentmethod'];
        }
        $tblhtml .= '
            <tr>
            <td>' . $payment['paymentid'] . '</td>
            <td>' . $payment_name . '</td>
            <td>' . _d($payment['date']) . '</td>
            <td>' . format_money($payment['amount'], $invoice->symbol) . '</td>
            </tr>
        ';
    }
    $tblhtml .= '</tbody>';
    $tblhtml .= '</table>';
    $pdf->writeHTML($tblhtml, true, false, false, false, '');
}

if (found_invoice_mode($payment_modes, $invoice->id, true, true)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', 6);
    $pdf->Cell(0, 0, _l('invoice_html_offline_payment'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', 2);

    foreach ($payment_modes as $mode) {
        if (is_numeric($mode['id'])) {
            if (!is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) {
                continue;
            }
        }
        if (isset($mode['show_on_pdf']) && $mode['show_on_pdf'] == 1) {
            $pdf->Ln(1);
            $pdf->Cell(0, 0, $mode['name'], 0, 1, 'L', 0, '', 0);
            $pdf->Ln(2);
            $pdf->writeHTMLCell('', '', '', '', $mode['description'], 0, 1, false, true, 'L', true);
        }
    }
}

if (!empty($invoice->clientnote)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_note'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $invoice->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($invoice->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $invoice->terms, 0, 1, false, true, 'L', true);
}
$pdf->AddPage();

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column = '';

$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _l('invoice_pdf_heading') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;"># ' . $invoice_number . '</b><br>';
$info_right_column .= '<span style="color:#4e4e4e;">(EXTRA COPY)</span>';

if (get_option('show_status_on_pdf_ei') == 1) {
    $info_right_column .= '<br /><span style="color:rgb(' . invoice_status_color_pdf($status) . ');text-transform:uppercase;">' . format_invoice_status($status, '', false) . '</span>';
}

if ($status != 2 && $status != 5 && get_option('show_pay_link_to_invoice_pdf') == 1
    && found_invoice_mode($payment_modes, $invoice->id, false)) {
    $info_right_column .= ' - <a style="color:#84c529;text-decoration:none;text-transform:uppercase;" href="' . site_url('invoice/' . $invoice->id . '/' . $invoice->hash) . '"><1b>' . _l('view_invoice_pdf_link_pay') . '</1b></a>';
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(10);
$custom_pdf_logo_image_url = get_option('custom_pdf_logo_image_url');
$width = 320;
if (get_option('company_logo_dark') != '' && file_exists(get_upload_path_by_type('company') . get_option('company_logo_dark'))) {
    $cimg = get_upload_path_by_type('company') . get_option('company_logo_dark');
} elseif (get_option('company_logo') != '' && file_exists(get_upload_path_by_type('company') . get_option('company_logo'))) {
    $cimg = get_upload_path_by_type('company') . get_option('company_logo');
} else {
    $cimg = '';
}
$logo_url = '';
if ($cimg != '') {
    $logo_url = '<img width="' . $width . 'px" src="' . $cimg . '"><br>';
}
$organization_info = '<div style="color:#424242;">';
$organization_info .= $logo_url;
$organization_info .= format_organization_info();
$organization_info .= '</div>';

// Bill to
$invoice_info = '<b>' . _l('invoice_bill_to') . '</b>';
$invoice_info .= '<div style="color:#424242;">';
$invoice_info .= format_customer_info($invoice, 'invoice', 'billing');
$invoice_info .= '</div>';

// ship to to
if ($invoice->include_shipping == 1 && $invoice->show_shipping_on_invoice == 1) {
    $invoice_info .= '<br /><b>' . _l('ship_to') . '</b>';
    $invoice_info .= '<div style="color:#424242;">';
    $invoice_info .= format_customer_info($invoice, 'invoice', 'shipping');
    $invoice_info .= '</div>';
}

$invoice_info .= '<br />' . _l('invoice_data_date') . ' ' . _d($invoice->date) . '<br />';

if (!empty($invoice->duedate)) {
    $invoice_info .= _l('invoice_data_duedate') . ' ' . _d($invoice->duedate) . '<br />';
}
if($invoice->purchaseorder_id){
    $invoice_info .= _l('Purchase Order:') . ' ' . format_purchaseorder_number($invoice->purchaseorder_id) . '<br />';
}
if ($invoice->sale_agent != 0 && get_option('show_sale_agent_on_invoices') == 1) {
    $invoice_info .= _l('sale_agent_string') . ': ' . get_staff_full_name($invoice->sale_agent) . '<br />';
}

if ($invoice->project_id != 0 && get_option('show_project_on_invoice') == 1) {
    $invoice_info .= _l('project') . ': ' . get_project_name_by_id($invoice->project_id) . '<br />';
}

foreach ($pdf_custom_fields as $field) {
    $value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
    if ($value == '') {
        continue;
    }
    $invoice_info .= $field['name'] . ': ' . $value . '<br />';
}

$left_info = $swap == '1' ? $invoice_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $invoice_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// The Table
$pdf->Ln(do_action('pdf_info_and_table_separator', 6));
$item_width = 38;

// If show item taxes is disabled in PDF we should increase the item width table heading
$item_width = get_option('show_tax_per_item') == 0 ? $item_width + 12 : $item_width;

$custom_fields_items = get_items_custom_fields_for_table_html($invoice->id, 'invoice');
// Calculate headings width, in case there are custom fields for items
$total_headings = get_option('show_tax_per_item') == 1 ? 4 : 3;
$total_headings += count($custom_fields_items);
$headings_width = (100 - ($item_width + 15)) / $total_headings;

// Header
$qty_heading = _l('invoice_table_quantity_heading');
if ($invoice->show_quantity_as == 2) {
    $qty_heading = _l('invoice_table_hours_heading');
} elseif ($invoice->show_quantity_as == 3) {
    $qty_heading = _l('invoice_table_quantity_heading') . '/' . _l('invoice_table_hours_heading');
}
$isDiscountDisplay = 0;
foreach ($invoice->items as $item) {
    if ($invoice->is_bulk == 0) {
        if ($item['item_discount'] > 0) {
            $isDiscountDisplay = 1;
            $itemPer = 30;
            $hsnPer = 15;
        } else {
            $itemPer = 35;
            $hsnPer = 20;
        }
    } else {
        $itemPer = 40;
        $hsnPer = 25;
    }
}
$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';

$tblhtml .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';

$tblhtml .= '<th align="center" width="5%">#</th>';
$tblhtml .= '<th align="left" width="' . $itemPer . '%">' . _l('invoice_table_item_heading') . '</th>';

foreach ($custom_fields_items as $cf) {
    $tblhtml .= '<th align="left"  width="' . $hsnPer . '%">' . $cf['name'] . '</th>';
}

$tblhtml .= '<th align="right" width="' . ($invoice->is_bulk ? 15 : 10) . '%">' . $qty_heading . '</th>';
if ($invoice->is_bulk == 0) {
    $tblhtml .= '<th align="right" width="10%">' . _l('invoice_table_rate_heading') . '</th>';
}
if (get_option('show_tax_per_item') == 1) {
    $tblhtml .= '<th align="right" width="' . ($invoice->is_bulk ? 15 : 10) . '%">' . _l('invoice_table_tax_heading') . '</th>';
}

if ($invoice->is_bulk == 0) {
    if ($isDiscountDisplay) {
        $tblhtml .= '<th align="right" width="10%">' . _l('invoice_table_discount_heading') . '</th>';
    }
    $tblhtml .= '<th align="right" width="10%">' . _l('invoice_table_amount_heading') . '</th>';
}
$tblhtml .= '</tr>';

// Items
$tblhtml .= '<tbody>';
$items_data = get_table_items_and_taxes($invoice->items, 'invoice', false, $invoice->is_bulk, $isDiscountDisplay);

$tblhtml .= $items_data['html'];
$taxes = $items_data['taxes'];

$tblhtml .= '</tbody>';
$tblhtml .= '</table>';

$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(8);

$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:' . ($font_size + 4) . 'px">';

$tbltotal .= '
<tr>
    <td align="right" width="85%"><strong>' . _l('invoice_subtotal') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->subtotal, $invoice->symbol) . '</td>
</tr>';

if (is_sale_discount_applied($invoice)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_discount');
    if (is_sale_discount($invoice, 'percent')) {
        $tbltotal .= '(' . _format_number($invoice->discount_percent, true) . '%)';
    }
    $tbltotal .= '</strong>';
    $tbltotal .= '</td>';
    $tbltotal .= '<td align="right" width="15%">-' . format_money($invoice->discount_total, $invoice->symbol) . '</td>
    </tr>';
}

$tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('estimate_packing_and_forwarding') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->packing_and_forwarding, $invoice->symbol) . '</td>
</tr>';

$tbltotal .= '<tr>
    <td align="right" width="85%"><strong>' . _l('estimate_servicecharge') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->servicecharge, $invoice->symbol) . '</td>
</tr>';

if ($invoice->devide_gst == 1 && $invoice->total_tax != 0) {
    $cgst = $invoice->total_tax / 2;
    $sgst = $invoice->total_tax / 2;

    $tbltotal .= '<tr><td align="right" width="85%"><strong>CGST</strong></td><td align="right" width="15%">' . format_money($cgst, $invoice->symbol) . '</td></tr>';
    $tbltotal .= '<tr><td align="right" width="85%"><strong>SGST</strong></td><td align="right" width="15%">' . format_money($sgst, $invoice->symbol) . '</td></tr>';
} else {
    $tbltotal .= '<tr><td align="right" width="85%"><strong>IGST</strong></td><td align="right" width="15%">' . format_money($invoice->total_tax, $invoice->symbol) . '</td></tr>';
}

$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>' . _l('invoice_total') . '</strong></td>
    <td align="right" width="15%">' . format_money($invoice->total, $invoice->symbol) . '</td>
</tr>';

if (count($invoice->payments) > 0 && get_option('show_total_paid_on_invoice') == 1) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('invoice_total_paid') . '</strong></td>
        <td align="right" width="15%">-' . format_money(sum_from_table('tblinvoicepaymentrecords', [
            'field' => 'amount',
            'where' => [
                'invoiceid' => $invoice->id,
            ],
        ]), $invoice->symbol) . '</td>
    </tr>';
}

if (get_option('show_credits_applied_on_invoice') == 1 && $credits_applied = total_credits_applied_to_invoice($invoice->id)) {
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('applied_credits') . '</strong></td>
        <td align="right" width="15%">-' . format_money($credits_applied, $invoice->symbol) . '</td>
    </tr>';
}

if (get_option('show_amount_due_on_invoice') == 1 && $invoice->status != 5) {
    $tbltotal .= '<tr style="background-color:#f0f0f0;">
       <td align="right" width="85%"><strong>' . _l('invoice_amount_due') . '</strong></td>
       <td align="right" width="15%">' . format_money($invoice->total_left_to_pay, $invoice->symbol) . '</td>
   </tr>';
}

$tbltotal .= '</table>';
$pdf->writeHTML($tbltotal, true, false, false, false, '');

$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';

// Items
$tblhtml .= '<tbody>';

$items_data = get_table_items_and_taxes($invoice->items, 'invoice');

if (isset($items_data['tax_qty'])) {
    $tax_qtys = $items_data['tax_qty'];

    if (!empty($tax_qtys)) {

        $tblhtml .= '<div style="font-weight:bold;"> Tax Summary</div>';

        $tblhtml .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';
        $tblhtml .= '<thead>';
        $tblhtml .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';
        $tblhtml .= '<th align="left">#</th>';

        $tblhtml .= '<th align="left">Name</th>';

        $tblhtml .= '<th align="left">Code</th>';

        $tblhtml .= '<th align="left">Qty</th>';

        $tblhtml .= '<th align="left">Tax Excl Amt</th>';

        $tblhtml .= '<th align="left">Tax Amount</th>';
        $tblhtml .= '</tr>';
        $tblhtml .= '</thead>';
        $tblhtml .= '<tbody>';

        $i = 1;
        $tax_total = 0;
        if (isset($invoice->servicecharge) && $invoice->servicecharge > 0 && !empty($invoice->service_charge_tax_rate)) {
            $service_charge_tax = $invoice->servicecharge * $invoice->service_charge_tax_rate / 100;
            $tax_total = $service_charge_tax;
            $tblhtml .= '<tr>';
            $tblhtml .= '<td>' . $i . '</td>';
            $tblhtml .= '<td>' . 'GST@' . $invoice->service_charge_tax_rate . '%|' . $invoice->service_charge_tax_rate . '</td>';
            $tblhtml .= '<td>' . 'GST@' . $invoice->service_charge_tax_rate . '%|' . $invoice->service_charge_tax_rate . '</td>';
            $tblhtml .= '<td>1</td>';
            $tblhtml .= '<td>' . format_money($invoice->servicecharge, $invoice->symbol) . '</td>';
            $tblhtml .= '<td>' . format_money($service_charge_tax, $invoice->symbol) . '</td>';
            $tblhtml .= '</tr>';
            $i++;
        }

        foreach ($tax_qtys as $key => $tax_qty) {
            $tblhtml .= '<tr>';
            $tblhtml .= '<td>' . $i . '</td>';
            $tblhtml .= '<td>' . $tax_qty['tax_name'] . '</td>';
            $tblhtml .= '<td>' . $tax_qty['tax_name'] . '</td>';
            $tblhtml .= '<td>' . $tax_qty['total_qtys'] . '</td>';

            if (isset($invoice->packing_and_forwarding) && $invoice->packing_and_forwarding > 0) {
                $packing_and_forwording = (($invoice->packing_and_forwarding / $invoice->subtotal) * $tax_qty['total_prcs']) + $tax_qty['total_prcs'];
                $tax = $packing_and_forwording * $key / 100;
                $tax_total = $tax_total + $tax;
                $tblhtml .= '<td>' . format_money($packing_and_forwording, $invoice->symbol) . '</td>';
                $tblhtml .= '<td>' . format_money($tax, $invoice->symbol) . '</td>';
            } else {
                $tblhtml .= '<td>' . format_money($tax_qty['total_prcs'], $invoice->symbol) . '</td>';
                $tblhtml .= '<td>' . format_money($tax_qty['total_tprs'], $invoice->symbol) . '</td>';
                $tax_total = $tax_total + $tax_qty['total_tprs'];
            }

            $tblhtml .= '</tr>';
            $i++;
        }

        if ($tax_total > 0) {
            $tblhtml .= '<tr><td align="right" width="78%" colspan="5"><strong>Total</strong></td><td align="right" width="15%">' . format_money($tax_total, $invoice->symbol) . '</td></tr>';
        }

        $tblhtml .= '</tbody></table>';
    }
}
$tblhtml .= '</tbody>';
$tblhtml .= '</table>';
$pdf->writeHTML($tblhtml, true, false, false, false, '');

if (get_option('total_to_words_enabled') == 1) {
    // Set the font bold
    $pdf->SetFont($font_name, 'B', 8);
    $pdf->Cell(0, 0, _l('num_word') . ': ' . $CI->numberword->convert($invoice->total, $invoice->currency_name), 0, 1, 'C', 0, '', 0);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name, '', 2);
    $pdf->Ln(4);
}

if (count($invoice->payments) > 0 && get_option('show_transactions_on_invoice_pdf') == 1) {
    $pdf->Ln(4);
    $border = 'border-bottom-color:#000000;border-bottom-width:1px;border-bottom-style:solid; 1px solid black;';
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_received_payments'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(4);
    $tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="5" border="0">
        <tr height="20"  style="color:#000;border:1px solid #000;">
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_number_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_mode_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_date_heading') . '</th>
        <th width="25%;" style="' . $border . '">' . _l('invoice_payments_table_amount_heading') . '</th>
    </tr>';
    $tblhtml .= '<tbody>';
    foreach ($invoice->payments as $payment) {
        $payment_name = $payment['name'];
        if (!empty($payment['paymentmethod'])) {
            $payment_name .= ' - ' . $payment['paymentmethod'];
        }
        $tblhtml .= '
            <tr>
            <td>' . $payment['paymentid'] . '</td>
            <td>' . $payment_name . '</td>
            <td>' . _d($payment['date']) . '</td>
            <td>' . format_money($payment['amount'], $invoice->symbol) . '</td>
            </tr>
        ';
    }
    $tblhtml .= '</tbody>';
    $tblhtml .= '</table>';
    $pdf->writeHTML($tblhtml, true, false, false, false, '');
}

if (found_invoice_mode($payment_modes, $invoice->id, true, true)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', 6);
    $pdf->Cell(0, 0, _l('invoice_html_offline_payment'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', 2);

    foreach ($payment_modes as $mode) {
        if (is_numeric($mode['id'])) {
            if (!is_payment_mode_allowed_for_invoice($mode['id'], $invoice->id)) {
                continue;
            }
        }
        if (isset($mode['show_on_pdf']) && $mode['show_on_pdf'] == 1) {
            $pdf->Ln(1);
            $pdf->Cell(0, 0, $mode['name'], 0, 1, 'L', 0, '', 0);
            $pdf->Ln(2);
            $pdf->writeHTMLCell('', '', '', '', $mode['description'], 0, 1, false, true, 'L', true);
        }
    }
}

if (!empty($invoice->clientnote)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('invoice_note'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $invoice->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($invoice->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $invoice->terms, 0, 1, false, true, 'L', true);
}

