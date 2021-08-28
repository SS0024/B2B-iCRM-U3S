
<?php echo form_hidden('_attachment_sale_id',$invoice->id); ?>
<?php echo form_hidden('_attachment_sale_type','invoice'); ?>
<div class="col-md-12 no-padding">
   <div class="panel_s">
      <div class="panel-body">

         <div class="horizontal-scrollable-tabs preview-tabs-top">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
               <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                  <li role="presentation" class="active">
                     <a href="#tab_invoice" aria-controls="tab_invoice" role="tab" data-toggle="tab">
                     <?php echo _l('Indent'); ?>
                     </a>
                  </li>
                  <li role="presentation">
                     <a href="#tab_supplier_price" aria-controls="tab_supplier_price" role="tab" data-toggle="tab">
                     <?php echo _l('Comparison Report'); ?>
                     </a>
                  </li>
                   <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>" class="tab-separator toggle_view">
                       <a href="#" onclick="small_table_full_view(); return false;">
                           <i class="fa fa-expand"></i></a>
                   </li>
               </ul>
            </div>
         </div>
         <div class="row">
            <div class="col-md-12 _buttons">
               <div class="visible-xs">
                  <div class="mtop10"></div>
               </div>
               <div class="pull-right">
                  <?php
                     $_tooltip = _l('invoice_sent_to_email_tooltip');
                     $_tooltip_already_send = '';

                     ?>
                  <?php if(has_permission('mfpurchases','','edit')){ ?>
                  <a href="<?php echo admin_url('mfpurchases/supplier_price/'.$invoice->id); ?>" data-toggle="tooltip" title="<?php echo _l('Add or Edit Supplier Price'); ?>" class="btn btn-default btn-with-tooltip" data-placement="bottom"><i class="fa fa-pencil-square"></i></a>
                  <?php }
                  if(has_permission('mfpurchases','','edit')){ ?>
                  <a href="<?php echo admin_url('mfpurchases/mfpurchase/'.$invoice->id); ?>" data-toggle="tooltip" title="<?php echo _l('Edit MFPurchase'); ?>" class="btn btn-default btn-with-tooltip" data-placement="bottom"><i class="fa fa-pencil-square-o"></i></a>
                  <?php } ?>

               </div>
            </div>
         </div>
         <div class="clearfix"></div>
         <hr class="hr-panel-heading" />
         <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="tab_invoice">

               <?php $this->load->view('admin/mfpurchases/invoice_preview_html'); ?>
            </div>
             <div role="tabpanel" class="tab-pane" id="tab_supplier_price">
                  <?php $this->load->view('admin/mfpurchases/supplier_price_html'); ?>
             </div>
            <?php if(count($applied_credits) > 0){ ?>
            <div class="tab-pane" role="tabpanel" id="invoice_applied_credits">
               <div class="table-responsive">
                  <table class="table table-bordered table-hover no-mtop">
                     <thead>
                        <th><span class="bold"><?php echo _l('credit_note'); ?> #</span></th>
                        <th><span class="bold"><?php echo _l('credit_date'); ?></span></th>
                        <th><span class="bold"><?php echo _l('credit_amount'); ?></span></th>
                     </thead>
                     <tbody>
                        <?php foreach($applied_credits as $credit) { ?>
                        <tr>
                           <td>
                              <a href="<?php echo admin_url('credit_notes/list_credit_notes/'.$credit['credit_id']); ?>"><?php echo format_credit_note_number($credit['credit_id']); ?></a>
                           </td>
                           <td><?php echo _d($credit['date']); ?></td>
                           <td><?php echo format_money($credit['amount'],$invoice->symbol) ?>
                              <?php if(has_permission('credit_notes','','delete')){ ?>
                              <a href="<?php echo admin_url('credit_notes/delete_invoice_applied_credit/'.$credit['id'].'/'.$credit['credit_id'].'/'.$invoice->id); ?>" class="pull-right text-danger _delete"><i class="fa fa-trash"></i></a>
                              <?php } ?>
                           </td>
                        </tr>
                        <?php } ?>
                     </tbody>
                  </table>
               </div>
            </div>
            <?php } ?>
            <div role="tabpanel" class="tab-pane" id="tab_delivery_details">
                <?php
                /* echo '<pre>';
                 print_r($invoices_delivery_modules);
                  echo '</pre>'; */

                if(!empty($invoices_delivery_modules)) { ?>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <div style="font-weight:bold;"> Delivery Details	</div>
                                <table class="table items estimate-items-preview">
                                    <thead>
                                    <tr>
                                        <th align="left">#</th>

                                        <th align="left">Date</th>

                                        <th align="left">Status</th>

                                        <th align="left">Delivery Reference No</th>

                                        <th align="left">Delivered By</th>

                                        <th align="left">Sale Reference No</th>

                                        <th align="left">Received By</th>

                                        <th align="left">Customer</th>

                                        <th align="left">Note</th>

                                        <th align="left">Address</th>

                                        <th align="left">Attachment</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $i = 1;
                                    foreach($invoices_delivery_modules as $invoices_delivery_module) {  ?>
                                        <tr>
                                            <td><?php echo $i; ?></td>
                                            <td><?php echo $invoices_delivery_module['date']; ?></td>
                                            <td><?php echo $invoices_delivery_module['dstatusname']; ?></td>
                                            <td><?php echo $invoices_delivery_module['delivery_reference_no']; ?></td>
                                            <td><?php echo $invoices_delivery_module['firstname'] .' '. $invoices_delivery_module['lastname']; ?></td>
                                            <td>
                                                <a href="<?php echo admin_url('/purchases/list_purchases/'.$invoices_delivery_module['invoice_id']);?>" target="_blank"><?php echo $invoices_delivery_module['sale_reference_no']; ?></a>
                                            </td>
                                            <td><?php echo $invoices_delivery_module['received_by']; ?></td>
                                            <td><?php echo $invoices_delivery_module['company']; ?></td>
                                            <td><?php echo $invoices_delivery_module['note']; ?></td>

                                            <td><?php echo $invoices_delivery_module['delivery_module_address'];?>
                                            </td>
                                            <td>
                                                <?php if (!empty($invoices_delivery_module['file_name'])) {



                                                    $path = get_upload_path_by_type('delivery_modules').'/'.$invoices_delivery_module['rel_id'].'/'.$invoices_delivery_module['file_name'];

                                                    $href_url = site_url('download/file/delivery_modules/'.$invoices_delivery_module['rel_id'].'/'.$invoices_delivery_module['file_name']);

                                                    $img_url = site_url('download/preview_image?path='.protected_file_url_by_path($path,true).'&type='.$invoices_delivery_module['filetype']);

                                                    ?>
                                                    <div class="preview-image"><a href="<?php echo $href_url;?>" target="_blank" data-lightbox="task-attachment" class=""><img src="<?php echo $img_url; ?>" class="img img-responsive" width="100px" height="80px"></a></div>

                                                <?php } ?>
                                            </td>
                                        </tr>
                                        <?php		$i++;
                                    }
                                    ?>
                                    </tbody>
                                </table>

                            </div>
                        </div>
                    </div>

                    <?php
                }
                ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_tasks">
               <?php init_relation_tasks_table(array('data-new-rel-id'=>$invoice->id,'data-new-rel-type'=>'invoice')); ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_reminders">
               <a href="#" class="btn btn-info btn-xs" data-toggle="modal" data-target=".reminder-modal-purchase-<?php echo $invoice->id; ?>"><i class="fa fa-bell-o"></i> <?php echo _l('invoice_set_reminder_title'); ?></a>
               <hr />
               <?php render_datatable(array( _l( 'reminder_description'), _l( 'reminder_date'), _l( 'reminder_staff'), _l( 'reminder_is_notified')), 'reminders'); ?>
               <?php $this->load->view('admin/includes/modals/reminder',array('id'=>$invoice->id,'name'=>'purchase','members'=>$members,'reminder_title'=>_l('invoice_set_reminder_title'))); ?>
            </div>
            <?php if($invoice->recurring > 0){ ?>
            <div role="tabpanel" class="tab-pane" id="tab_child_invoices">
               <?php if(count($invoice_recurring_invoices)){ ?>
               <p class="mtop30 bold"><?php echo _l('invoice_add_edit_recurring_invoices_from_invoice'); ?></p>
               <br />
               <ul class="list-group">
                  <?php foreach($invoice_recurring_invoices as $recurring){ ?>
                  <li class="list-group-item">
                     <a href="<?php echo admin_url('purchases/list_purchases/'.$recurring->id); ?>" onclick="init_purchase(<?php echo $recurring->id; ?>); return false;" target="_blank"><?php echo format_invoice_number($recurring->id); ?>
                     <span class="pull-right bold"><?php echo format_money($recurring->total,$recurring->symbol); ?></span>
                     </a>
                     <br />
                     <span class="inline-block mtop10">
                     <?php echo '<span class="bold">'._d($recurring->date).'</span>'; ?><br />
                     <?php echo format_invoice_status($recurring->status,'',false); ?>
                     </span>
                  </li>
                  <?php } ?>
               </ul>
               <?php } else { ?>
               <p class="bold"><?php echo _l('no_child_found',_l('invoices')); ?></p>
               <?php } ?>
            </div>
            <?php } ?>
            <div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
               <?php
                  $this->load->view('admin/includes/emails_tracking',array(
                     'tracked_emails'=>
                     get_tracked_emails($invoice->id, 'invoice'))
                  );
                  ?>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_notes">
               <?php echo form_open(admin_url('purchases/add_note/'.$invoice->id),array('id'=>'sales-notes','class'=>'invoice-notes-form')); ?>
               <?php echo render_textarea('description'); ?>
               <div class="text-right">
                  <button type="submit" class="btn btn-info mtop15 mbot15"><?php echo _l('estimate_add_note'); ?></button>
               </div>
               <?php echo form_close(); ?>
               <hr />
               <div class="panel_s mtop20 no-shadow" id="sales_notes_area"></div>
            </div>
            <div role="tabpanel" class="tab-pane ptop10" id="tab_activity">
               <div class="row">
                  <div class="col-md-12">
                     <div class="activity-feed">
                        <?php foreach($activity as $activity){
                           $_custom_data = false;
                           ?>
                        <div class="feed-item" data-sale-activity-id="<?php echo $activity['id']; ?>">
                           <div class="date">
                              <span class="text-has-action" data-toggle="tooltip" data-title="<?php echo _dt($activity['date']); ?>">
                              <?php echo time_ago($activity['date']); ?>
                              </span>
                           </div>
                           <div class="text">
                              <?php if(is_numeric($activity['staffid']) && $activity['staffid'] != 0){ ?>
                              <a href="<?php echo admin_url('profile/'.$activity["staffid"]); ?>">
                              <?php echo staff_profile_image($activity['staffid'],array('staff-profile-xs-image pull-left mright5'));
                                 ?>
                              </a>
                              <?php } ?>
                              <?php
                                 $additional_data = '';
                                 if(!empty($activity['additional_data'])){
                                  $additional_data = unserialize($activity['additional_data']);
                                  $i = 0;
                                  foreach($additional_data as $data){
                                    if(strpos($data,'<original_status>') !== false){
                                      $original_status = get_string_between($data, '<original_status>', '</original_status>');
                                      $additional_data[$i] = format_invoice_status($original_status,'',false);
                                    } else if(strpos($data,'<new_status>') !== false){
                                      $new_status = get_string_between($data, '<new_status>', '</new_status>');
                                      $additional_data[$i] = format_invoice_status($new_status,'',false);
                                    } else if(strpos($data,'<custom_data>') !== false){
                                      $_custom_data = get_string_between($data, '<custom_data>', '</custom_data>');
                                      unset($additional_data[$i]);
                                    }
                                    $i++;
                                  }
                                 }
                                 $_formatted_activity = _l($activity['description'],$additional_data);
                                 if($_custom_data !== false){
                                  $_formatted_activity .= ' - ' .$_custom_data;
                                 }
                                 if(!empty($activity['full_name'])){
                                 $_formatted_activity = $activity['full_name'] . ' - ' . $_formatted_activity;
                                 }
                                 echo $_formatted_activity;
                                 if(is_admin()){
                                 echo '<a href="#" class="pull-right text-danger" onclick="delete_sale_activity('.$activity['id'].'); return false;"><i class="fa fa-remove"></i></a>';
                                 }
                                 ?>
                           </div>
                        </div>
                        <?php } ?>
                     </div>
                  </div>
               </div>
            </div>
            <div role="tabpanel" class="tab-pane" id="tab_views">
               <?php
                  $views_activity = get_views_tracking('invoice',$invoice->id);
                  if(count($views_activity) === 0) {
                     echo '<h4 class="no-mbot">'._l('not_viewed_yet',_l('invoice_lowercase')).'</h4>';
                  }
                  foreach($views_activity as $activity){ ?>
               <p class="text-success no-margin">
                  <?php echo _l('view_date') . ': ' . _dt($activity['date']); ?>
               </p>
               <p class="text-muted">
                  <?php echo _l('view_ip') . ': ' . $activity['view_ip']; ?>
               </p>
               <hr />
               <?php } ?>
            </div>
         </div>
      </div>
   </div>
</div>
<?php $this->load->view('admin/mfpurchases/invoice_send_to_client'); ?>
<?php $this->load->view('admin/credit_notes/apply_invoice_credits'); ?>
<?php $this->load->view('admin/credit_notes/invoice_create_credit_note_confirm'); ?>
<script>
   init_items_sortable(true);
   init_btn_with_tooltips();
   init_datepicker();
   init_selectpicker();
   init_form_reminder();
   init_tabs_scrollable();
</script>
