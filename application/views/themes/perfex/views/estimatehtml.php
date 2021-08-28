<div class="col-md-12 page-pdf-html-logo">
    <?php get_company_logo('','pull-left'); ?>
    <?php if(is_client_logged_in() && has_contact_permission('estimates')){ ?>
        <a href="<?php echo site_url('clients/estimates/'); ?>" class="btn btn-default pull-right">
            <?php echo _l('client_go_to_dashboard'); ?>
        </a>
    <?php } ?>
</div>
<?php  ?>
<div class="clearfix"></div>
<div class="panel_s mtop20">
    <div class="panel-body">
        <div class="col-md-10 col-md-offset-1">
            <div class="row">
                <div class="col-md-6">
                    <div class="mtop10 display-block">
                        <?php echo format_estimate_status($estimate->status,'',true); ?>
                    </div>
                </div>
                <div class="col-md-6 text-right _buttons">
                   <div class="visible-xs">
                    <div class="mtop10"></div>
                </div>
                <?php
                // Is not accepted, declined and expired
                if ($estimate->status != 4 && $estimate->status != 3 && $estimate->status != 5) {
                    $can_be_accepted = true;
                    if($identity_confirmation_enabled == '0'){
                        echo form_open($this->uri->uri_string(),array('class'=>'pull-right mright10'));
                        echo form_hidden('estimate_action',4);
                        echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-btn accept"><i class="fa fa-check"></i> '._l('clients_accept_estimate').'</button>';
                        echo form_close();
                    } else {
                        echo '<button type="button" id="accept_action" class="btn btn-success mright10 pull-right action-btn accept"><i class="fa fa-check"></i> '._l('clients_accept_estimate').'</button>';
                    }
                } else if($estimate->status == 3){
                    if (($estimate->expirydate >= date('Y-m-d') || !$estimate->expirydate) && $estimate->status != 5) {
                        $can_be_accepted = true;
                        if($identity_confirmation_enabled == '0'){
                            echo form_open($this->uri->uri_string(),array('class'=>'pull-right mright10'));
                            echo form_hidden('estimate_action',4);
                            echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-btn accept"><i class="fa fa-check"></i> '._l('clients_accept_estimate').'</button>';
                            echo form_close();
                        } else {
                            echo '<button type="button" id="accept_action" class="btn btn-success mright10 pull-right action-btn accept"><i class="fa fa-check"></i> '._l('clients_accept_estimate').'</button>';
                        }
                    }
                }
                // Is not accepted, declined and expired
               if ($estimate->status != 4 && $estimate->status != 3 && $estimate->status != 5) {
                    echo form_open($this->uri->uri_string(),array('class'=>'pull-right mright10'));
                    echo form_hidden('estimate_action',3);
                    echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-default action-btn accept"><i class="fa fa-remove"></i> '._l('clients_decline_estimate').'</button>';
                    echo form_close();
                }
                ?>
                <?php echo form_open($this->uri->uri_string(),array('class'=>'pull-right')); ?>
                <button type="submit" name="estimatepdf" class="btn btn-default action-btn download mright10" value="estimatepdf">
                    <i class="fa fa-file-pdf-o"></i> <?php echo _l('clients_invoice_html_btn_download'); ?>
                </button>
                <?php echo form_close(); ?>
            </div>
        </div>
        <div class="row mtop40">
            <div class="col-md-6 col-sm-6">
                <h4 class="bold"><?php echo format_estimate_number($estimate->id); ?></h4>
                <address>
                    <?php echo format_organization_info(); ?>
                </address>
            </div>
            <div class="col-sm-6 text-right">
                <span class="bold"><?php echo _l('estimate_to'); ?>:</span>
                <address>
                 <?php echo format_customer_info($estimate, 'estimate', 'billing'); ?>
             </address>
             <!-- shipping details -->
             <?php if($estimate->include_shipping == 1 && $estimate->show_shipping_on_estimate == 1){ ?>
             <span class="bold"><?php echo _l('ship_to'); ?>:</span>
             <address>
                <?php echo format_customer_info($estimate, 'estimate', 'shipping'); ?>
            </address>
            <?php } ?>
            <p class="no-mbot">
                <span class="bold">
                    <?php echo _l('estimate_data_date'); ?>
                </span>
                <?php echo _d($estimate->date); ?>
            </p>
            <?php if(!empty($estimate->expirydate)){ ?>
            <p class="no-mbot">
                <span class="bold"><?php echo _l('estimate_data_expiry_date'); ?></span>
                <?php echo _d($estimate->expirydate); ?>
            </p>
            <?php } ?>
            <?php if(!empty($estimate->reference_no)){ ?>
            <p class="no-mbot">
                <span class="bold"><?php echo _l('reference_no'); ?>:</span>
                <?php echo $estimate->reference_no; ?>
            </p>
            <?php } ?>
            <?php if($estimate->sale_agent != 0 && get_option('show_sale_agent_on_estimates') == 1){ ?>
            <p class="no-mbot">
                <span class="bold"><?php echo _l('sale_agent_string'); ?>:</span>
                <?php echo get_staff_full_name($estimate->sale_agent); ?>
            </p>
            <?php } ?>
            <?php if($estimate->project_id != 0 && get_option('show_project_on_estimate') == 1){ ?>
            <p class="no-mbot">
                <span class="bold"><?php echo _l('project'); ?>:</span>
                <?php echo get_project_name_by_id($estimate->project_id); ?>
            </p>
            <?php } ?>
            <?php $pdf_custom_fields = get_custom_fields('estimate',array('show_on_pdf'=>1,'show_on_client_portal'=>1));
            foreach($pdf_custom_fields as $field){
                $value = get_custom_field_value($estimate->id,$field['id'],'estimate');
                if($value == ''){continue;} ?>
                <p class="no-mbot">
                    <span class="bold"><?php echo $field['name']; ?>: </span>
                    <?php echo $value; ?>
                </p>
                <?php } ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="table-responsive">
                    <table class="table items">
                        <thead>
                            <tr>
                                <th align="center">#</th>
                                <th class="description" width="50%" align="left"><?php echo _l('estimate_table_item_heading'); ?></th>
                                <?php
                                    $custom_fields = get_items_custom_fields_for_table_html($estimate->id,'estimate');
                                    foreach($custom_fields as $cf){
                                       echo '<th class="custom_field" align="left">' . $cf['name'] . '</th>';
                                }
                                $qty_heading = _l('estimate_table_quantity_heading');
                                if($estimate->show_quantity_as == 2){
                                    $qty_heading = _l('estimate_table_hours_heading');
                                } else if($estimate->show_quantity_as == 3){
                                    $qty_heading = _l('estimate_table_quantity_heading') .'/'._l('estimate_table_hours_heading');
                                }
                                ?>
                                m<th align="right"><?php echo $qty_heading; ?></th>
                                <?php if($estimate->is_bulk == 0){ ?>
                                    <th align="right"><?php echo _l('estimate_table_rate_heading'); ?></th>
                                <?php } ?>
                                <?php if(get_option('show_tax_per_item') == 1){ ?>
                                <th align="right"><?php echo _l('estimate_table_tax_heading'); ?></th>
                                <?php } ?>
                                <?php if($estimate->is_bulk == 0){?>
                                    <th align="right"><?php echo _l('estimate_table_item_discount_heading'); ?></th>
                                    <th align="right"><?php echo _l('estimate_table_amount_heading'); ?></th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                           <?php
                           $items_data = get_table_items_and_taxes($estimate->items,'estimate',false,$estimate->is_bulk);
                           $taxes = $items_data['taxes'];
                           echo $items_data['html'];
                           ?>
                       </tbody>
                   </table>
               </div>
			</div>
			
			 <!--<div class="col-md-12">
				<div class="table-responsive">
					<?php 
					if(isset($items_data['tax_qty'])) {
						$tax_qtys = $items_data['tax_qty'];
						
						if(!empty($tax_qtys)) {	?>
							<div style="font-weight:bold;"> Tax Summary	</div>
							<table class="table items estimate-items-preview">
									<thead>
										<tr>
											<th align="left">#</th>
										
											<th align="left">Name</th>
										
											<th align="left">Code</th>
										
											<th align="left">Qty</th>
										
											<th align="left">Tax Excl Amt</th>
										
											<th align="left">Tax Amount</th>
										</tr>
									</thead>
									<tbody>
										<?php
										$i = 1;
										if(isset($estimate->servicecharge) && $estimate->servicecharge > 0 && !empty($estimate->service_charge_tax_rate) && $estimate->service_charge_tax_rate != 0){ 
											$service_charge_tax = $estimate->servicecharge*$estimate->service_charge_tax_rate /100;
											?>
												<tr>
													<td><?php echo $i; ?></td>
													<td><?php echo 'GST@'.$estimate->service_charge_tax_rate.'%|'.$estimate->service_charge_tax_rate; ?></td>
													<td><?php echo 'GST@'.$estimate->service_charge_tax_rate.'%|'.$estimate->service_charge_tax_rate; ?></td>
													<td>1</td>
													<td><?php echo $estimate->servicecharge; ?></td>
													<td><?php echo $service_charge_tax; ?></td>
												</tr>
												
										<?php }
										$i++;
										foreach($tax_qtys as $tax_qty) {  ?>
											<tr>
												<td><?php echo $i; ?></td>
												<td><?php echo $tax_qty['tax_name']; ?></td>
												<td><?php echo $tax_qty['tax_name']; ?></td>
												<td><?php echo $tax_qty['total_qtys']; ?></td>
												<td><?php echo $tax_qty['total_prcs']; ?></td>
												<td><?php echo $tax_qty['total_tprs']; ?></td>
											</tr>
							<?php		$i++;
										}
										?>
									</tbody>
							</table>
							<?php
						}
					}
					?>
					
				</div>
			 </div>-->
			 

           <div class="col-md-6 col-md-offset-6">
            <table class="table text-right">
				
			
                <tbody>
                    <tr id="subtotal">
                        <td><span class="bold"><?php echo _l('estimate_subtotal'); ?></span>
                        </td>
                        <td class="subtotal">
                            <?php echo format_money($estimate->subtotal,$estimate->symbol); ?>
                        </td>
                    </tr>
                     <?php if(is_sale_discount_applied($estimate)){ ?>
                     <tr>
                         <td>
                            <span class="bold"><?php echo _l('estimate_discount'); ?>
                             <?php if(is_sale_discount($estimate,'percent')){ ?>
                             (<?php echo _format_number($estimate->discount_percent,true); ?>%)
                             <?php } ?></span>
                         </td>
                         <td class="discount">
                            <?php echo '-' . format_money($estimate->discount_total,$estimate->symbol); ?>
                         </td>
                     </tr>
                    <?php } ?>
					<?php //if((int)$estimate->transportation != 0){ ?>
              <!-- <tr>
                  <td>
                     <span class="bold"><?php //echo _l('estimate_transportation'); ?></span>
                  </td>
                  <td class="transportation">
                     <?php //echo format_money($estimate->transportation,$estimate->symbol); ?>
                  </td>
               </tr> -->
               <?php //} ?>
			    <?php //if((int)$estimate->packing_and_forwarding != 0){ ?>
               <tr>
                  <td>
                     <span class="bold"><?php echo _l('estimate_packing_and_forwarding'); ?></span>
                  </td>
                  <td class="packing_and_forwarding">
                     <?php echo format_money($estimate->packing_and_forwarding,$estimate->symbol); ?>
                  </td>
               </tr>
               <?php //} ?>
			   <?php //if((int)$estimate->servicecharge != 0){ ?>
               <tr>
                  <td>
                     <span class="bold"><?php echo _l('estimate_servicecharge'); ?></span>
                  </td>
                  <td class="servicecharge">
                     <?php echo format_money($estimate->servicecharge,$estimate->symbol); ?>
                  </td>
               </tr>
               <?php //} ?>
			   
			  
                    <?php
						// if(isset($estimate->servicecharge) && $estimate->servicecharge > 0 && !empty($estimate->service_charge_tax_rate) && $estimate->service_charge_tax_rate != 0){ 
								// $service_charge_tax = $estimate->servicecharge*$estimate->service_charge_tax_rate /100;
											
								// echo '<tr class="tax-area"><td class="bold">GST@'.$estimate->service_charge_tax_rate.'% ('._format_number($estimate->service_charge_tax_rate).'%)</td><td>'.format_money($service_charge_tax, $estimate->symbol).'</td></tr>';
						// }	
					
                        // foreach($taxes as $tax){
                            // echo '<tr class="tax-area"><td class="bold">'.$tax['taxname'].' ('._format_number($tax['taxrate']).'%)</td><td>'.format_money($tax['total_tax'], $estimate->symbol).'</td></tr>';
                        // }
						
						if($estimate->devide_gst == 1 &&  $estimate->total_tax != 0){ 
							$cgst = $estimate->total_tax/2;
							$sgst = $estimate->total_tax/2;				
							
							echo '<tr class="tax-area"><td class="bold">CGST</td><td>'.format_money($cgst, $estimate->symbol).'</td></tr>';
							echo '<tr class="tax-area"><td class="bold">SGST</td><td>'.format_money($sgst, $estimate->symbol).'</td></tr>';			
						}else{
							echo '<tr class="tax-area"><td class="bold">IGST</td><td>'.format_money($estimate->total_tax, $estimate->symbol).'</td></tr>';
						}
						
                    ?>
                    <?php //if((int)$estimate->adjustment != 0){ ?>
                    <!--<tr>
                        <td>
                            <span class="bold"><?php //echo _l('estimate_adjustment'); ?></span>
                        </td>
                        <td class="adjustment">
                            <?php //echo format_money($estimate->adjustment,$estimate->symbol); ?>
                        </td>
                    </tr>-->
                    <?php //} ?>
                    <tr>
                        <td><span class="bold"><?php echo _l('estimate_total'); ?></span>
                        </td>
                        <td class="total">
                            <?php echo format_money($estimate->total,$estimate->symbol); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <?php
        if(get_option('total_to_words_enabled') == 1){ ?>
        <div class="col-md-12 text-center">
           <p class="bold"><?php echo  _l('num_word').': '.$this->numberword->convert($estimate->total,$estimate->currency_name); ?></p>
       </div>
       <?php } ?>
       <?php if(count($estimate->attachments) > 0 && $estimate->visible_attachments_to_customer_found == true){ ?>
       <div class="clearfix"></div>
       <div class="col-md-12"><hr />
        <p class="bold mbot15 font-medium"><?php echo _l('estimate_files'); ?></p>
    </div>
    <?php foreach($estimate->attachments as $attachment){
        // Do not show hidden attachments to customer
        if($attachment['visible_to_customer'] == 0){continue;}
        $attachment_url = site_url('download/file/sales_attachment/'.$attachment['attachment_key']);
        if(!empty($attachment['external'])){
            $attachment_url = $attachment['external_link'];
        }
        ?>
        <div class="col-md-12 mbot15">
            <div class="pull-left"><i class="<?php echo get_mime_class($attachment['filetype']); ?>"></i></div>
            <a href="<?php echo $attachment_url; ?>"><?php echo $attachment['file_name']; ?></a>
        </div>
        <?php } ?>
        <?php } ?>
        <?php if(!empty($estimate->clientnote)){ ?>
        <div class="col-md-12">
            <b><?php echo _l('estimate_note'); ?></b><br /><br /><?php echo $estimate->clientnote; ?>
        </div>
        <?php } ?>
        <?php if(!empty($estimate->terms)){ ?>
        <div class="col-md-12">
            <hr />
            <b><?php echo _l('terms_and_conditions'); ?></b><br /><br /><?php echo $estimate->terms; ?>
        </div>
        <?php } ?>
    </div>
</div>
</div>
</div>
<?php
if($identity_confirmation_enabled == '1' && $can_be_accepted){
    get_template_part('identity_confirmation_form',array('formData'=>form_hidden('estimate_action',4)));
}
?>
