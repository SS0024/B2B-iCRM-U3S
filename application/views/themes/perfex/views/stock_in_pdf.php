<?php

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _l('invoice_way_bill_pdf_heading') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;"># ' . $invoice_number . '</b>';


if ($status != 2 && $status != 5 && get_option('show_pay_link_to_invoice_pdf') == 1
    && found_invoice_mode($payment_modes, $invoice->id, false)) {
    $info_right_column .= ' - <a style="color:#84c529;text-decoration:none;text-transform:uppercase;" href="' . site_url('invoice/' . $invoice->id . '/' . $invoice->hash) . '"><1b>' . _l('view_invoice_pdf_link_pay') . '</1b></a>';
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(10);

$organization_info = '<div style="color:#424242;">';

$organization_info .= format_organization_info();

$organization_info .= '</div>';


$invoice_info .= '<br />' . _l('Purchase Date') . ':- ' . _d($invoice->date) . '<br />';

if (!empty($invoice->duedate)) {
    $invoice_info .= _l('invoice_data_duedate') . ' ' . _d($invoice->duedate) . '<br />';
}


$left_info = $swap == '1' ? $invoice_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $invoice_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// The Table
$pdf->Ln(do_action('pdf_info_and_table_separator', 6));
$item_width = 38;

// If show item taxes is disabled in PDF we should increase the item width table heading
$item_width = get_option('show_tax_per_item') == 0 ? $item_width + 15 : $item_width;

//$custom_fields_items = get_items_custom_fields_for_table_html($invoice->id, 'invoice');
//print_r($custom_fields_items));die;
// Calculate headings width, in case there are custom fields for items
// $total_headings = get_option('show_tax_per_item') == 1 ? 4 : 3;
// print_r(get_option('show_tax_per_item'));die;
// $total_headings += count($custom_fields_items);
$total_headings = 2;
$headings_width = 57; //(100 - ($item_width + 6)) / $total_headings;

// Header
$qty_heading = _l('invoice_table_quantity_heading');


$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';
$tblhtml .= '<thead>';
$tblhtml .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';

$tblhtml .= '<th align="center">#</th>';
$tblhtml .= '<th align="left">' . _l('invoice_table_item_heading') . '</th>';

// foreach ($custom_fields_items as $cf) {
    // $tblhtml .= '<th width="' . $headings_width . '%" align="left">' . $cf['name'] . '</th>';
// }

$tblhtml .= '<th align="right">' . $qty_heading . '</th>';

// if (get_option('show_tax_per_item') == 1) {
    // $tblhtml .= '<th width="' . $headings_width . '%" align="right">' . _l('invoice_table_tax_heading') . '</th>';
// }

//$tblhtml .= '<th width="' . $headings_width . '%" align="right">' . _l('invoice_table_amount_heading') . '</th>';
$tblhtml .= '</tr>';
$tblhtml .= '</thead>';
// Items
$tblhtml .= '<tbody width="100%" >';

$items_data = get_stock_table_items($invoice->items, 'stock_in');
//$items_data = get_table_items($invoice->items, 'invoice');

//print_r($items_data);die;
$tblhtml .= $items_data['html'];


$tblhtml .= '</tbody>';
$tblhtml .= '</table>';

$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(8);

