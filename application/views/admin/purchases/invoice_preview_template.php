<?php if((credits_can_be_applied_to_invoice($invoice->status) && $credits_available > 0)) { ?>
<div class="alert alert-warning mbot5">
   <?php echo _l('x_credits_available',format_money($credits_available,$customer_currency->symbol)); ?>
   <br />
   <a href="#" data-toggle="modal" data-target="#apply_credits"><?php echo _l('apply_credits'); ?></a>
</div>
<?php } ?>
<?php echo form_hidden('_attachment_sale_id',$invoice->id); ?>
<?php echo form_hidden('_attachment_sale_type','invoice'); ?>
<div class="col-md-12 no-padding">
   <div class="panel_s">
      <div class="panel-body">
         <?php if($invoice->recurring > 0){
            echo '<div class="ribbon info"><span>'._l('invoice_recurring_indicator').'</span></div>';
            } ?>
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
                  <?php if(count($invoice->payments) > 0) { ?>
                  <li role="presentation">
                     <a href="#invoice_payments_received" aria-controler="invoice_payments_received" role="tab" data-toggle="tab">
                     <?php echo _l('payments'); ?> <span class="badge"><?php echo count($invoice->payments); ?></span>
                     </a>
                  </li>
                  <?php } ?>
                   <li role="presentation">
                       <a href="#tab_activity" aria-controls="tab_activity" role="tab" data-toggle="tab">
                           <?php echo _l('invoice_view_activity_tooltip'); ?>
                       </a>
                   </li>
                   <li role="presentation">
                       <a href="#tab_reminders"
                          onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $invoice->id; ?> + '/' + 'purchase', undefined, undefined, undefined,[1,'asc']); return false;"
                          aria-controls="tab_reminders" role="tab" data-toggle="tab">
                           <?php echo _l('estimate_reminders'); ?>
                           <?php
                           $total_reminders = total_rows('tblreminders',
                               array(
                                   'isnotified' => 0,
                                   'staff' => get_staff_user_id(),
                                   'rel_type' => 'purchase',
                                   'rel_id' => $invoice->id
                               )
                           );
                           if ($total_reminders > 0) {
                               echo '<span class="badge">' . $total_reminders . '</span>';
                           }
                           ?>
                       </a>
                   </li>
                   <li role="presentation" class="tab-separator">
                       <a href="#tab_notes" onclick="get_sales_notes(<?php echo $invoice->id; ?>,'purchases'); return false" aria-controls="tab_notes" role="tab" data-toggle="tab">
                           <?php echo _l('estimate_notes'); ?> <span class="notes-total">
                     <?php if($totalNotes > 0){ ?>
                         <span class="badge"><?php echo $totalNotes; ?></span>
                     <?php } ?>
                     </span>
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
            <div class="col-md-2">
               <?php
               $statusesValue = [
                   'ordered' =>'Ordered',
                   'partial_received' =>'Partial Received',
                   'full_received'=>'Full Received',
                   'cancelled'=>'Cancelled',
               ];
               ?>
                <span class="label label-default default s-status mtop5 estimate-status-<?= $invoice->status; ?> estimate-status-default"><?= $statusesValue[$invoice->status];  ?></span>
            </div>
            <div class="col-md-10 _buttons">
               <div class="visible-xs">
                  <div class="mtop10"></div>
               </div>
               <div class="pull-right">
                  <?php
                     $_tooltip = _l('invoice_sent_to_email_tooltip');
                     $_tooltip_already_send = '';
                     if($invoice->sent == 1 && is_date($invoice->datesend)){
                      $_tooltip_already_send = _l('invoice_already_send_to_client_tooltip',time_ago($invoice->datesend));
                     }
                     ?>
                  <?php if(has_permission('purchases','','edit')){ ?>
                  <a href="<?php echo admin_url('purchases/purchase/'.$invoice->id); ?>" data-toggle="tooltip" title="<?php echo _l('Edit Purchase'); ?>" class="btn btn-default btn-with-tooltip" data-placement="bottom"><i class="fa fa-pencil-square-o"></i></a>
                  <?php }
                  if($invoice->is_stock_in == 1){
                  ?>
                   <a href="<?php echo admin_url('purchases/stock_in/'.$invoice->id); ?>" data-toggle="tooltip" title="Stock In" class="btn btn-default btn-with-tooltip" data-placement="bottom">Stock In</a>
                   <?php } ?>
                  <!--<div class="btn-group">
                     <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-file-pdf-o"></i><?php /*if(is_mobile()){echo ' PDF';} */?> <span class="caret"></span></a>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <li class="hidden-xs"><a href="<?php /*echo admin_url('purchases/pdf/'.$invoice->id.'?output_type=I'); */?>"><?php /*echo _l('view_pdf'); */?></a></li>
                        <li class="hidden-xs"><a href="<?php /*echo admin_url('purchases/pdf/'.$invoice->id.'?output_type=I'); */?>" target="_blank"><?php /*echo _l('view_pdf_in_new_window'); */?></a></li>
                        <li><a href="<?php /*echo admin_url('purchases/pdf/'.$invoice->id); */?>"><?php /*echo _l('download'); */?></a></li>
                        <li>
                        <li><a href="<?php /*echo admin_url('purchases/pipdf/'.$invoice->id); */?>"><?php /*echo "Download PI"; */?></a></li>
                        <li>
                           <a href="<?php /*echo admin_url('purchases/pdf/'.$invoice->id.'?print=true'); */?>" target="_blank">
                           <?php /*echo _l('print'); */?>
                           </a>
                        </li>
                     </ul>
                  </div>-->
                  <?php /*if(!empty($invoice->clientid)){ */?><!--
                  <span<?php /*if($invoice->status == '5'){ */?> data-toggle="tooltip" data-title="<?php /*echo _l('invoice_cancelled_email_disabled'); */?>"<?php /*} */?>>
                  <a href="#" class="invoice-send-to-client btn-with-tooltip btn btn-default<?php /*if($invoice->status == 5){echo ' disabled';} */?>" data-toggle="tooltip" title="<?php /*echo $_tooltip; */?>" data-placement="bottom"><span data-toggle="tooltip" data-title="<?php /*echo $_tooltip_already_send; */?>"><i class="fa fa-envelope"></i></span></a>
                  </span>
                  --><?php /*} */?>
                  <!-- Single button -->
				  <!-- Delivery Bill -->
                  <div class="btn-group">
                     <button type="button" class="btn btn-default pull-left dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <?php echo _l('more'); ?> <span class="caret"></span>
                     </button>
                     <ul class="dropdown-menu dropdown-menu-right">
                        <!--<li><a href="<?php /*echo site_url('invoice/' . $invoice->id . '/' .  $invoice->hash) */?>" target="_blank"><?php /*echo _l('view_invoice_as_customer_tooltip'); */?></a></li>
                        <li>
                           <?php /*if(($invoice->status == 4 || ($invoice->status == 3 && !empty($invoice->duedate) && $invoice->duedate && date('Y-m-d') > date('Y-m-d',strtotime(to_sql_date($invoice->duedate))))) && is_invoices_overdue_reminders_enabled()){ */?>
                           <a href="<?php /*echo admin_url('purchases/send_overdue_notice/'.$invoice->id); */?>"><?php /*echo _l('send_overdue_notice_tooltip'); */?></a>
                           <?php /*} */?>
                        </li>
                        <?php /*if($invoice->status != 5 && has_permission('credit_notes','','create') && !empty($invoice->clientid)) {*/?>
                        <li>
                           <a href="<?php /*echo admin_url('credit_notes/credit_note_from_invoice/'.$invoice->id); */?>" id="invoice_create_credit_note" data-status="<?php /*echo $invoice->status; */?>"><?php /*echo _l('create_credit_note'); */?></a>
                        </li>
                        <?php /*} */?>
                        <li>
                           <a href="#" data-toggle="modal" data-target="#sales_attach_file"><?php /*echo _l('invoice_attach_file'); */?></a>
                        </li>
                        <?php /*if(has_permission('purchases','','create')){ */?>
                        <li>
                           <a href="<?php /*echo admin_url('purchases/copy/'.$invoice->id); */?>"><?php /*echo _l('invoice_copy'); */?></a>
                        </li>
                        <?php /*} */?>
                        <?php /*if($invoice->sent == 0){ */?>
                        <li>
                           <a href="<?php /*echo admin_url('purchases/mark_as_sent/'.$invoice->id); */?>"><?php /*echo _l('invoice_mark_as_sent'); */?></a>
                        </li>
                        --><?php /*} */?>
                        <?php if(has_permission('purchases','','edit') || has_permission('purchases','','create')){ ?>
                        <li>
                           <?php if($invoice->status != 5 && $invoice->status != 2 && $invoice->status != 3){ ?>
                           <a href="<?php echo admin_url('purchases/mark_as_cancelled/'.$invoice->id); ?>"><?php echo _l('invoice_mark_as',_l('invoice_status_cancelled')); ?></a>
                           <?php } else if($invoice->status == 5) { ?>
                           <a href="<?php echo admin_url('purchases/unmark_as_cancelled/'.$invoice->id); ?>"><?php echo _l('invoice_unmark_as',_l('invoice_status_cancelled')); ?></a>
                           <?php } ?>
                        </li>
                        <?php } ?>
                        <?php /*if(!in_array($invoice->status, array(2,5,6)) && has_permission('purchases','','edit') && $invoice->duedate && is_invoices_overdue_reminders_enabled()){ */?><!--
                        <li>
                           <?php /*if($invoice->cancel_overdue_reminders == 1) { */?>
                           <a href="<?php /*echo admin_url('purchases/resume_overdue_reminders/'.$invoice->id); */?>"><?php /*echo _l('resume_overdue_reminders'); */?></a>
                           <?php /*} else { */?>
                           <a href="<?php /*echo admin_url('purchases/pause_overdue_reminders/'.$invoice->id); */?>"><?php /*echo _l('pause_overdue_reminders'); */?></a>
                           <?php /*} */?>
                        </li>
                        <?php /*} */?>
                        <?php
/*                           if((get_option('delete_only_on_last_invoice') == 1 && is_last_invoice($invoice->id)) || (get_option('delete_only_on_last_invoice') == 0)){ */?>
                        <?php /*if(has_permission('purchases','','delete')){ */?>
                        <li data-toggle="tooltip" data-title="<?php /*echo _l('delete_invoice_tooltip'); */?>">
                           <a href="<?php /*echo admin_url('purchases/delete/'.$invoice->id); */?>" class="text-danger delete-text _delete"><?php /*echo _l('delete_invoice'); */?></a>
                        </li>
                        <?php /*} */?>
                        --><?php /*} */?>
                     </ul>
                  </div>
                  <?php /*if(has_permission('payments','','create') && abs($invoice->total) > 0){ */?><!--
                  <a href="#" onclick="record_payment(<?php /*echo $invoice->id; */?>); return false;"  class="mleft10 pull-right btn btn-success<?php /*if($invoice->status == 2 || $invoice->status == 5){echo ' disabled';} */?>">
                     <i class="fa fa-plus-square"></i> <?php /*echo _l('payment'); */?></a>
                  --><?php /*} */?>
               </div>
            </div>
         </div>
         <div class="clearfix"></div>
         <hr class="hr-panel-heading" />
         <div class="tab-content">
            <div role="tabpanel" class="tab-pane active" id="tab_invoice">
               <?php if($invoice->status == 5 && $invoice->recurring > 0) { ?>
               <div class="alert alert-info">
                  Recurring invoice with status Cancelled <b>is still ongoing recurring invoice</b>. If you want to stop this recurring invoice you should update the invoice recurring field to <b>No</b>.
               </div>
               <?php } ?>
               <?php $this->load->view('admin/purchases/invoice_preview_html'); ?>
            </div>
            <?php if(count($invoice->payments) > 0) { ?>
            <div class="tab-pane" role="tabpanel" id="invoice_payments_received">
               <?php include_once(APPPATH . 'views/admin/purchases/invoice_payments_table.php'); ?>
            </div>
            <?php } ?>
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
<?php $this->load->view('admin/purchases/invoice_send_to_client'); ?>
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
