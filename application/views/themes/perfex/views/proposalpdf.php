<?php
$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column = '';
$info_right_column .= '<span style="font-weight:bold;font-size:27px;">'.'Inquiry'.'</span><br />';

$info_right_column .= '<b style="color:#4e4e4e;"># ' . $number . '</b>';

function proposal_status_color($id, $replace_default_by_muted = false)
{
    $statusColor = '119, 119, 119';
    if ($id == 1) {
        $statusColor = '119, 119, 119';
    } elseif ($id == 2) {
        $statusColor = '252, 45, 66';
    } elseif ($id == 3) {
        $statusColor = '0, 191, 54';
    } elseif ($id == 4 || $id == 5) {
        // status sent and revised
        $statusColor = '3, 169, 244';
    }
    return $statusColor;
}

if(get_option('show_status_on_pdf_ei') == 1){
    $info_right_column .= '<br /><span class="color:rgb('.proposal_status_color($proposal->status).');text-transform:uppercase;">' . format_proposal_status($proposal->status,'',false) . '</span>';
}

// Add logo
$info_left_column .= pdf_logo_url();

// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);


$pdf->writeHTMLCell(($dimensions['wk'] - ($dimensions['rm'] + $dimensions['lm'])), '', '', '', $pdf_logo_url, 0, 1, false, true, 'L', true);

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
if($proposal->assigned != 0){
    $organization_info .= '<br />'._l('Admin Notes') . ': ' .  $proposal->adminnote .'<br />';
}
if($proposal->modifieddate != 0){
    $organization_info .= '<br />'._l('Modified Date') . ': ' .  _dt($proposal->modifieddate) .'<br />';
}

// Proposal to
$estimate_info = '<b>'._l('proposal_to').'</b>';
$estimate_info .= '<div style="color:#424242;">';
$estimate_info .= format_proposal_info($proposal,'pdf');
$estimate_info .= '</div>';

$estimate_info .= '<br />'._l('Inquiry Date') . ': ' . _d($proposal->date).'<br />';

if (!empty($proposal->open_till)) {
    $estimate_info .= _l('estimate_data_expiry_date') . ': ' . _d($proposal->open_till) . '<br />';
}

if (!empty($proposal->reference_no)) {
    $estimate_info .= _l('reference_no') . ': ' . $proposal->reference_no. '<br />';
}

if($proposal->assigned != 0 && get_option('show_sale_agent_on_estimates') == 1){
    $estimate_info .= _l('sale_agent_string') . ': ' .  get_staff_full_name($proposal->assigned, true). '<br />';
}

if ($proposal->project_id != 0 && get_option('show_project_on_estimate') == 1) {
    $estimate_info .= _l('project') . ': ' . get_project_name_by_id($proposal->project_id). '<br />';
}

foreach($pdf_custom_fields as $field){
    $value = get_custom_field_value($proposal->id,$field['id'],'estimate');
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
$custom_fields_items = get_items_custom_fields_for_table_html($proposal->id,'inquiry');

// Calculate headings width, in case there are custom fields for items
$total_headings = get_option('show_tax_per_item') == 1 ? 4 : 3;
$total_headings += count($custom_fields_items);
$headings_width = (100-($item_width+15)) / $total_headings;

// The same language keys from estimates are used here
$qty_heading = _l('estimate_table_quantity_heading');
if($proposal->show_quantity_as == 2){
    $qty_heading = _l('estimate_table_hours_heading');
} else if($proposal->show_quantity_as == 3){
    $qty_heading = _l('estimate_table_quantity_heading') .'/'._l('estimate_table_hours_heading');
}
$isDiscountDisplay = 0;
foreach ($proposal->items as $item) {
    if ($item['item_discount'] > 0) {
        $isDiscountDisplay = 1;
    }
}
if($proposal->is_bulk==0) {
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
$items_html = '<table width="100%" bgcolor="#fff" cellspacing="0" cellpadding="8">';

$items_html .= '<tr height="30" bgcolor="' . get_option('pdf_table_heading_color') . '" style="color:' . get_option('pdf_table_heading_text_color') . ';">';

$items_html .= '<th  align="center" width="5%">#</th>';
$items_html .= '<th  align="left" width="'.$itemPer.'%">' . _l('estimate_table_item_heading') . '</th>';

foreach ($custom_fields_items as $cf) {
    $items_html .= '<th  align="left" width="'.$hsnPer.'%">' . $cf['name'] . '</th>';
}

$items_html .= '<th  align="right" width="'.($proposal->is_bulk ? 15 : 10).'%">' . $qty_heading . '</th>';
if($proposal->is_bulk==0){
    $items_html .= '<th  align="right" width="10%">' . _l('estimate_table_rate_heading') . '</th>';
}

if (get_option('show_tax_per_item') == 1) {
    $items_html .= '<th  align="right" width="'.($proposal->is_bulk ? 15 : 10).'%">' . _l('estimate_table_tax_heading') . '</th>';
}

if($proposal->is_bulk==0){
    if($isDiscountDisplay) {
        $items_html .= '<th  align="right" width="10%">' . _l('estimate_table_item_discount_heading') . '</th>';
    }
    $items_html .= '<th  align="right" width="10%">' . _l('estimate_table_amount_heading') . '</th>';
}
$items_html .= '</tr>';

$items_html .= '<tbody>';

$items_data = get_table_items_and_taxes($proposal->items,'inquiry',false,$proposal->is_bulk,$isDiscountDisplay);

$taxes = $items_data['taxes'];
$items_html .= $items_data['html'];

$items_html .= '</tbody>';
$items_html .= '</table>';
$items_html .= '<br /><br />';
$items_html .= '';
$items_html .= '<table cellpadding="6" style="font-size:'.($font_size+4).'px">';


//$pdf->writeHTML($items_html, true, false, false, false, '');
$pdf->Ln(8);

$items_html .= '
<tr>
    <td align="right" width="85%"><strong>'._l('estimate_subtotal').'</strong></td>
    <td align="right" width="15%">' . format_money($proposal->subtotal,$proposal->symbol) . '</td>
</tr>';

if(is_sale_discount_applied($proposal)){
    $items_html .= '
    <tr>
        <td align="right" width="85%"><strong>' . _l('estimate_discount');
        if(is_sale_discount($proposal,'percent')){
            $items_html .= '(' . _format_number($proposal->discount_percent, true) . '%)';
        }
        $items_html .= '</strong>';
        $items_html .= '</td>';
        $items_html .= '<td align="right" width="15%">-' . format_money($proposal->discount_total, $proposal->symbol) . '</td>
    </tr>';
}

//if ((int)$proposal->transportation != 0) {
    //$items_html .= '<tr>
    //<td align="right" width="85%"><strong>'._l('estimate_transportation').'</strong></td>
    //<td align="right" width="15%">' . format_money($proposal->transportation,$proposal->symbol) . '</td>
//</tr>';
//}


//if ((int)$proposal->servicecharge != 0) {
    $items_html .= '<tr>
    <td align="right" width="85%"><strong>'._l('estimate_servicecharge').'</strong></td>
    <td align="right" width="15%">' . format_money($proposal->servicecharge,$proposal->symbol) . '</td>
</tr>';
//}


//if ((int)$proposal->packing_and_forwarding != 0) {
    $items_html .= '<tr>
    <td align="right" width="85%"><strong>'._l('estimate_packing_and_forwarding').'</strong></td>
    <td align="right" width="15%">' . format_money($proposal->packing_and_forwarding,$proposal->symbol) . '</td>
</tr>';
//}

// if(isset($proposal->servicecharge) && $proposal->servicecharge > 0 && !empty($proposal->service_charge_tax_rate)){
		// $service_charge_tax	=	$proposal->servicecharge * $proposal->service_charge_tax_rate/100;
		 // $items_html .= '<tr>
				// <td align="right" width="85%"><strong>' .'GST@'.$proposal->service_charge_tax_rate.'%' . ' (' . _format_number($proposal->service_charge_tax_rate) . '%)' . '</strong></td>
				// <td align="right" width="15%">' . format_money($service_charge_tax, $proposal->symbol) . '</td>
			// </tr>';
	// }


// foreach ($taxes as $tax) {
    // $items_html .= '<tr>
    // <td align="right" width="85%"><strong>' . $tax['taxname'] . ' (' . _format_number($tax['taxrate']) . '%)' . '</strong></td>
    // <td align="right" width="15%">' . format_money($tax['total_tax'], $proposal->symbol) . '</td>
// </tr>';
// }

	if($proposal->devide_gst == 1 &&  $proposal->total_tax != 0){ 
		$cgst = $proposal->total_tax/2;
		$sgst = $proposal->total_tax/2;				
		
		$items_html .= '<tr><td align="right" width="85%"><strong>CGST</strong></td><td align="right" width="15%">'.format_money($cgst, $proposal->symbol).'</td></tr>';
		$items_html .= '<tr><td align="right" width="85%"><strong>SGST</strong></td><td align="right" width="15%">'.format_money($sgst, $proposal->symbol).'</td></tr>';			
	}else{
		$items_html .= '<tr><td align="right" width="85%"><strong>IGST</strong></td><td align="right" width="15%">'.format_money($proposal->total_tax, $proposal->symbol).'</td></tr>';
	}

$items_html .= '
<tr style="background-color:#f0f0f0;">
    <td align="right" width="85%"><strong>'._l('estimate_total').'</strong></td>
    <td align="right" width="15%">' . format_money($proposal->total, $proposal->symbol) . '</td>
</tr>';
$items_html .= '</table>';

$pdf->writeHTML($items_html, true, false, false, false, '');

if(get_option('total_to_words_enabled') == 1){
    // Set the font bold
    $pdf->SetFont($font_name,'B',8);
    $pdf->Cell(0, 0, _l('num_word').': '.$CI->numberword->convert($proposal->total,$proposal->currency_name), 0, 1, 'C', 0, '', 0);
    // Set the font again to normal like the rest of the pdf
    $pdf->SetFont($font_name,'',2);
    $pdf->Ln(4);
}

if (!empty($proposal->clientnote)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name,'B',$font_size);
    $pdf->Cell(0, 0, _l('estimate_note'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name,'',$font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $proposal->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($proposal->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name,'B',$font_size);
    $pdf->Cell(0, 0, _l('terms_and_conditions'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name,'',$font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $proposal->terms, 0, 1, false, true, 'L', true);
}
