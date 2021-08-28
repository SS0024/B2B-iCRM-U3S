<?php

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column = '';
if( $_SESSION['xqut'] == 2)
{
	$info_right_column .= '<span style="font-weight:bold;font-size:27px;">'.'PROFORMA INVOICE'.'</span><br />';

}
else
{
	$info_right_column .= '<span style="font-weight:bold;font-size:27px;">'.'QUOTATIONS'.'</span><br />';
}


$info_right_column .= '<b style="color:#4e4e4e;"># ' . $estimate_number . '</b>';

if(get_option('show_status_on_pdf_ei') == 1){
    $info_right_column .= '<br /><span style="color:rgb('.estimate_status_color_pdf($status).');text-transform:uppercase;">' . format_estimate_status($status,'',false) . '</span>';
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
if($estimate->is_bulk==0) {
    if ($isDiscountDisplay) {
        if(count($custom_fields_items) > 0){
            $itemPer = 30;
            $hsnPer = 15;
        }else{
            $itemPer = 45;
        }
    } else {
        if(count($custom_fields_items) > 0){
            $itemPer = 35;
            $hsnPer = 20;
        }else{
            $itemPer = 55;
        }
    }
} else{
    if(count($custom_fields_items) > 0){
        $itemPer = 40;
        $hsnPer = 25;
    }else{
        $itemPer = 65;
    }
}
// Header
$tblhtml = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';

$tblhtml .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';

$tblhtml .= '<th align="center" width="5%">#</th>';
$tblhtml .= '<th align="left" width="'.$itemPer.'%">' . _l('estimate_table_item_heading') . '</th>';

foreach ($custom_fields_items as $cf) {
    $tblhtml .= '<th align="left" width="'.$hsnPer.'%">' . $cf['name'] . '</th>';
}

$tblhtml .= '<th align="right" width="'.($estimate->is_bulk ? 15 : 10).'%">' . $qty_heading . '</th>';
if($estimate->is_bulk==0){
	$tblhtml .= '<th align="right" width="10%">' . _l('estimate_table_rate_heading') . '</th>';
}

if (get_option('show_tax_per_item') == 1) {
    $tblhtml .= '<th align="right" width="'.($estimate->is_bulk ? 15 : 10).'%">' . _l('estimate_table_tax_heading') . '</th>';
}
if($estimate->is_bulk==0){
    if($isDiscountDisplay){
        $tblhtml .= '<th align="right" width="10%">' . _l('estimate_table_item_discount_heading') . '</th>';
    }
	
	$tblhtml .= '<th align="right" width="10%">' . _l('estimate_table_amount_heading') . '</th>';
}
$tblhtml .= '</tr>';

$tblhtml .= '<tbody>';
$items_data = get_table_items_and_taxes($estimate->items,'estimate', false,$estimate->is_bulk,$isDiscountDisplay);

$tblhtml .= $items_data['html'];
$taxes = $items_data['taxes'];

$tblhtml .= '</tbody>';
$tblhtml .= '</table>';
// if(isset($items_data['tax_qty'])) {
	// $tax_qtys = $items_data['tax_qty'];

	// if(!empty($tax_qtys)) {
		
		// $tblhtml .= '<div style="font-weight:bold;"> Tax Summary</div>';
		
		// $tblhtml .= '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';
		// $tblhtml .= '<thead>';
		// $tblhtml .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';
		// $tblhtml .= '<th align="left">#</th>';
				
		// $tblhtml .= '<th align="left">Name</th>';
				
		// $tblhtml .= '<th align="left">Code</th>';
				
		// $tblhtml .= '<th align="left">Qty</th>';
				
		// $tblhtml .= '<th align="left">Tax Excl Amt</th>';
				
		// $tblhtml .= '<th align="left">Tax Amount</th>';
		// $tblhtml .= '</tr>';
		// $tblhtml .= '</thead>';
		// $tblhtml .= '<tbody>';
		
		// $i = 1;
		
		
		// if(isset($estimate->servicecharge) && $estimate->servicecharge > 0 && !empty($estimate->service_charge_tax_rate)){
			// $service_charge_tax	=	$estimate->servicecharge * $estimate->service_charge_tax_rate/100;
			// $tblhtml .= '<tr>';
			// $tblhtml .= '<td>'.$i.'</td>';
			// $tblhtml .= '<td>'.'GST@'.$estimate->service_charge_tax_rate.'%|'.$estimate->service_charge_tax_rate.'</td>';
			// $tblhtml .= '<td>'.'GST@'.$estimate->service_charge_tax_rate.'%|'.$estimate->service_charge_tax_rate.'</td>';
			// $tblhtml .= '<td>1</td>';
			// $tblhtml .= '<td>'.$estimate->servicecharge.'</td>';
			// $tblhtml .= '<td>'.$service_charge_tax.'</td>';
			// $tblhtml .= '</tr>';
			// $i++;
		// }
		// foreach($tax_qtys as $tax_qty) {  
			// $tblhtml .= '<tr>';
			// $tblhtml .= '<td>'.$i.'</td>';
			// $tblhtml .= '<td>'.$tax_qty['tax_name'].'</td>';
			// $tblhtml .= '<td>'.$tax_qty['tax_name'].'</td>';
			// $tblhtml .= '<td>'.$tax_qty['total_qtys'].'</td>';
			// $tblhtml .= '<td>'.$tax_qty['total_prcs'].'</td>';
			// $tblhtml .= '<td>'.$tax_qty['total_tprs'].'</td>';
			// $tblhtml .= '</tr>';
		// $i++;
		// }
		
		// $tblhtml .= '</tbody></table>';
	// }
// }


$pdf->writeHTML($tblhtml, true, false, false, false, '');

$pdf->Ln(8);
$tbltotal = '';
$tbltotal .= '<table cellpadding="6" style="font-size:'.($font_size+4).'px">';


$tbltotal .= '
<tr>
    <td align="right" width="85%"><strong>'._l('estimate_subtotal').'</strong></td>
    <td align="right" width="15%">' . format_money($estimate->subtotal,$estimate->symbol) . '</td>
</tr>';

if(is_sale_discount_applied($estimate)){
    $tbltotal .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('estimate_discount');
        if(is_sale_discount($estimate,'percent')){
            $tbltotal .= '(' . _format_number($estimate->discount_percent, true) . '%)';
        }
        $tbltotal .= '</strong>';
        $tbltotal .= '</td>';
        $tbltotal .= '<td align="right" width="15%">-' . format_money($estimate->discount_total, $estimate->symbol) . '</td>
    </tr>';
}
//if ((int)$estimate->packing_and_forwarding != 0) {    
	$tbltotal .= '<tr>    <td align="right" width="85%"><strong>'._l('estimate_packing_and_forwarding').'</strong></td>    <td align="right" width="15%">' . format_money($estimate->packing_and_forwarding,$estimate->symbol) . '</td></tr>';
//}
//if ((int)$estimate->transportation != 0) {    
	//$tbltotal .= '<tr>    <td align="right" width="85%"><strong>'._l('estimate_transportation').'</strong></td>    <td align="right" width="15%">' . format_money($estimate->transportation,$estimate->symbol) . '</td></tr>';
	
//}

//if ((int)$estimate->servicecharge != 0) {   
	$tbltotal .= '<tr>    <td align="right" width="85%"><strong>'._l('estimate_servicecharge').'</strong></td>    <td align="right" width="15%">' . format_money($estimate->servicecharge,$estimate->symbol) . '</td></tr>';
//}



// if(isset($estimate->servicecharge) && $estimate->servicecharge > 0 && !empty($estimate->service_charge_tax_rate)){
		// $service_charge_tax	=	$estimate->servicecharge * $estimate->service_charge_tax_rate/100;
		
		 // $tbltotal .= '<tr>
				// <td align="right" width="85%"><strong>' .'GST@'.$estimate->service_charge_tax_rate.'%' . ' (' . _format_number($estimate->service_charge_tax_rate) . '%)' . '</strong></td>
				// <td align="right" width="15%">' . format_money($service_charge_tax, $estimate->symbol) . '</td>
			// </tr>';
	// }

// foreach ($taxes as $tax) {
    // $tbltotal .= '<tr>
    // <td align="right" width="85%"><strong>' . $tax['taxname'] . ' (' . _format_number($tax['taxrate']) . '%)' . '</strong></td>
    // <td align="right" width="15%">' . format_money($tax['total_tax'], $estimate->symbol) . '</td>
// </tr>';
// }

	if($estimate->devide_gst == 1 &&  $estimate->total_tax != 0){ 
		$cgst = $estimate->total_tax/2;
		$sgst = $estimate->total_tax/2;				
		
		$tbltotal .= '<tr><td align="right" width="85%"><strong>CGST</strong></td><td align="right" width="15%">'.format_money($cgst, $estimate->symbol).'</td></tr>';
		$tbltotal .= '<tr><td align="right" width="85%"><strong>SGST</strong></td><td align="right" width="15%">'.format_money($sgst, $estimate->symbol).'</td></tr>';			
	}else{
		$tbltotal .= '<tr><td align="right" width="85%"><strong>IGST</strong></td><td align="right" width="15%">'.format_money($estimate->total_tax, $estimate->symbol).'</td></tr>';
	}



$tbltotal .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>'._l('estimate_total').'</strong></td>
    <td align="right" width="15%">' . format_money($estimate->total, $estimate->symbol) . '</td>
</tr>';

$tbltotal .= '</table>';

$pdf->writeHTML($tbltotal, true, false, false, false, '');

if(get_option('total_to_words_enabled') == 1){
     // Set the font bold
     $pdf->SetFont($font_name,'B',8);
     $pdf->Cell(0, 0, _l('num_word').': '.$CI->numberword->convert($estimate->total,$estimate->currency_name), 0, 1, 'C', 0, '', 0);
     // Set the font again to normal like the rest of the pdf
     $pdf->SetFont($font_name,'',2);
     $pdf->Ln(4);
}

if (!empty($estimate->clientnote)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name,'B',$font_size);
    $pdf->Cell(0, 0, _l('estimate_note'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name,'',$font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $estimate->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($estimate->terms)) {
    $pdf->SetFont($font_name,'$pdf->Ln(4);B',$font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name,'',$font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $estimate->terms, 0, 1, false, true, 'L', true);
}
