<?php

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column = '';
$info_right_column .= '<span style="font-weight:bold;font-size:27px;">REQUSITE FORM</span><br />';

$info_right_column .= '<b style="color:#4e4e4e;"># ' . $estimate_number . '</b>';

if(get_option('show_status_on_pdf_ei') == 1){
    $info_right_column .= '<br /><span style="color:rgb(119, 119, 119);text-transform:uppercase;">' . $status. '</span>';
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
    $organization_info .= $logo_url;
    $organization_info .= format_organization_info();
$organization_info .= '</div>';

// Estimate to
$estimate_info = '<b>' ._l('estimate_to') . '</b>';
$estimate_info .= '<div style="color:#424242;">';
$estimate_info .= format_customer_info($estimate, 'estimate', 'billing');
$estimate_info .= '</div>';

// ship to to
if($estimate->include_shipping == 1 && $estimate->show_shipping_on_estimate == 1){
    $estimate_info .= '<br /><b>' . _l('ship_to') . '</b>';
    $estimate_info .= '<div style="color:#424242;">';
    $estimate_info .= format_customer_info($estimate, 'estimate', 'shipping');
    $estimate_info .= '</div>';
}

$estimate_info .= '<br />'._l('estimate_data_date') . ': ' . _d($estimate->date).'<br />';

if (!empty($estimate->expirydate)) {
    $estimate_info .= _l('estimate_data_expiry_date') . ': ' . _d($estimate->expirydate) . '<br />';
}

if (!empty($estimate->reference_no)) {
    $estimate_info .= _l('reference_no') . ': ' . $estimate->reference_no. '<br />';
}

if($estimate->sale_agent != 0 && get_option('show_sale_agent_on_estimates') == 1){
    $estimate_info .= _l('sale_agent_string') . ': ' .  get_staff_full_name($estimate->sale_agent). '<br />';
}

if ($estimate->project_id != 0 && get_option('show_project_on_estimate') == 1) {
    $estimate_info .= _l('project') . ': ' . get_project_name_by_id($estimate->project_id). '<br />';
}

foreach($pdf_custom_fields as $field){
    $value = get_custom_field_value($estimate->id,$field['id'],'estimate');
    if($value == ''){continue;}
    $estimate_info .= $field['name'] . ': ' . $value. '<br />';
}

$left_info = $swap == '1' ? $estimate_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $estimate_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

// The Table
$pdf->Ln(do_action('pdf_info_and_table_separator', 6));
$item_width = 32;
// If show item taxes is disabled in PDF we should increase the item width table heading
$item_width = get_option('show_tax_per_item') == 0 ? $item_width+15 : $item_width;
$custom_fields_items = get_items_custom_fields_for_table_html($estimate->id,'estimate');

// Calculate headings width, in case there are custom fields for items
$total_headings = get_option('show_tax_per_item') == 1 ? 4 : 3;
$total_headings += count($custom_fields_items);
$headings_width = (100-($item_width+15)) / $total_headings;

$qty_heading = _l('estimate_table_quantity_heading');
if($estimate->show_quantity_as == 2){
    $qty_heading = _l('estimate_table_hours_heading');
} else if($estimate->show_quantity_as == 3){
    $qty_heading = _l('estimate_table_quantity_heading') .'/'._l('estimate_table_hours_heading');
}
$isDiscountDisplay = 0;
foreach ($estimate->items as $item) {
    if ($item['item_discount'] > 0) {
        $isDiscountDisplay = 1;
    }
}
// Header
$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';

$tblhtml .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';

$tblhtml .= '<th align="center">#</th>';
$tblhtml .= '<th align="left">' . _l('estimate_table_item_heading') . '</th>';

foreach ($custom_fields_items as $cf) {
    $tblhtml .= '<th align="left">' . $cf['name'] . '</th>';
}

$tblhtml .= '<th align="right">' . $qty_heading . '</th>';
$tblhtml .= '</tr>';

$tblhtml .= '<tbody>';

$items_data = get_table_items_and_taxes($estimate->items,'requsite_form',true,$estimate->is_bulk,$isDiscountDisplay);

$tblhtml .= $items_data['html'];
$taxes = $items_data['taxes'];

$tblhtml .= '</tbody>';
$tblhtml .= '</table>';

$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(8);
