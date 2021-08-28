<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
         <div class="_filters _hidden_inputs">
            <?php
               foreach($statuses as $_status){
                $val = '';
                if($_status == $this->input->get('status')){
                  $val = $_status;
                }
                echo form_hidden('proposals_'.$_status,$val);
               }
               foreach($years as $year){
                echo form_hidden('year_'.$year['year'],$year['year']);
               }
               foreach($proposals_sale_agents as $agent){
                echo form_hidden('sale_agent_'.$agent['sale_agent']);
               }
               echo form_hidden('leads_related');
               echo form_hidden('customers_related');
               echo form_hidden('expired');
               ?>
         </div>
         <div class="col-md-12">
            <div class="panel_s mbot10">
               <div class="panel-body _buttons">
                  <?php if(has_permission('proposals','','create')){ ?>
                  <a href="<?php echo admin_url('proposals/proposal'); ?>" class="btn btn-info pull-left display-block">
                  <?php echo _l('new_proposal'); ?>
                  </a>
                  <?php } ?>
                  <a href="<?php echo admin_url('proposals/pipeline/'.$switch_pipeline); ?>" class="btn btn-default mleft5 pull-left hidden-xs"><?php echo _l('switch_to_pipeline'); ?></a>
                  <div class="display-block text-right">
                     <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-filter" aria-hidden="true"></i>
                        </button>
                        <ul class="dropdown-menu width300">
                           <li>
                              <a href="#" data-cview="all" onclick="dt_custom_view('','.table-proposals',''); return false;">
                              <?php echo _l('proposals_list_all'); ?>
                              </a>
                           </li>
                           <li class="divider"></li>
                           <?php foreach($statuses as $status){ ?>
                           <li class="<?php if($this->input->get('status') == $status){echo 'active';} ?>">
                              <a href="#" data-cview="proposals_<?php echo $status; ?>" onclick="dt_custom_view('proposals_<?php echo $status; ?>','.table-proposals','proposals_<?php echo $status; ?>'); return false;">
                              <?php echo format_proposal_status($status,'',false); ?>
                              </a>
                           </li>
                           <?php } ?>
                           <?php if(count($years) > 0){ ?>
                           <li class="divider"></li>
                           <?php foreach($years as $year){ ?>
                           <li class="active">
                              <a href="#" data-cview="year_<?php echo $year['year']; ?>" onclick="dt_custom_view(<?php echo $year['year']; ?>,'.table-proposals','year_<?php echo $year['year']; ?>'); return false;"><?php echo $year['year']; ?>
                              </a>
                           </li>
                           <?php } ?>
                           <?php } ?>
                           <?php if(count($proposals_sale_agents) > 0){ ?>
                           <div class="clearfix"></div>
                           <li class="divider"></li>
                           <li class="dropdown-submenu pull-left">
                              <a href="#" tabindex="-1"><?php echo _l('sale_agent_string'); ?></a>
                              <ul class="dropdown-menu dropdown-menu-left">
                                 <?php foreach($proposals_sale_agents as $agent){ ?>
                                 <li>
                                    <a href="#" data-cview="sale_agent_<?php echo $agent['sale_agent']; ?>" onclick="dt_custom_view('sale_agent_<?php echo $agent['sale_agent']; ?>','.table-proposals','sale_agent_<?php echo $agent['sale_agent']; ?>'); return false;"><?php echo get_staff_full_name($agent['sale_agent']); ?>
                                    </a>
                                 </li>
                                 <?php } ?>
                              </ul>
                           </li>
                           <?php } ?>
                           <div class="clearfix"></div>
                           <li class="divider"></li>
                           <li>
                              <a href="#" data-cview="expired" onclick="dt_custom_view('expired','.table-proposals','expired'); return false;">
                              <?php echo _l('proposal_expired'); ?>
                              </a>
                           </li>
                           <li>
                              <a href="#" data-cview="leads_related" onclick="dt_custom_view('leads_related','.table-proposals','leads_related'); return false;">
                              <?php echo _l('proposals_leads_related'); ?>
                              </a>
                           </li>
                           <li>
                              <a href="#" data-cview="customers_related" onclick="dt_custom_view('customers_related','.table-proposals','customers_related'); return false;">
                              <?php echo _l('proposals_customers_related'); ?>
                              </a>
                           </li>
                        </ul>
                     </div>
                     <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs" onclick="toggle_small_view('.table-proposals','#proposal'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
                  </div>
               </div>
            </div>
            <div class="row">
               <div class="col-md-12" id="small-table">
                  <div class="panel_s">
                     <div class="panel-body">
                         <div class="row">
                             <div class="col-md-12">
                                 <p class="bold"><?php echo _l('filter_by'); ?></p>
                             </div>
                             <div class="col-md-4">
                                 <div class="form-group" id="report-time">
                                     <label for="months-report"><?php echo _l('period_datepicker'); ?></label><br />
                                     <select class="selectpicker" name="months-report" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                         <option value=""><?php echo _l('report_sales_months_all_time'); ?></option>
                                         <option value="this_month"><?php echo _l('this_month'); ?></option>
                                         <option value="1"><?php echo _l('last_month'); ?></option>
                                         <option value="this_year"><?php echo _l('this_year'); ?></option>
                                         <option value="last_year"><?php echo _l('last_year'); ?></option>
                                         <option value="3" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-2 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_three_months'); ?></option>
                                         <option value="6" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-5 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_six_months'); ?></option>
                                         <option value="12" data-subtext="<?php echo _d(date('Y-m-01', strtotime("-11 MONTH"))); ?> - <?php echo _d(date('Y-m-t')); ?>"><?php echo _l('report_sales_months_twelve_months'); ?></option>
                                         <option value="custom"><?php echo _l('period_datepicker'); ?></option>
                                     </select>
                                 </div>
                             </div>
                             <div class="col-md-4">
                                 <div id="date-range" class="hide mbot15">
                                     <div class="row">
                                         <div class="col-md-6">
                                             <label for="report-from" class="control-label"><?php echo _l('report_sales_from_date'); ?></label>
                                             <div class="input-group date">
                                                 <input type="text" class="form-control datepicker" id="report-from" name="report-from">
                                                 <div class="input-group-addon">
                                                     <i class="fa fa-calendar calendar-icon"></i>
                                                 </div>
                                             </div>
                                         </div>
                                         <div class="col-md-6">
                                             <label for="report-to" class="control-label"><?php echo _l('report_sales_to_date'); ?></label>
                                             <div class="input-group date">
                                                 <input type="text" class="form-control datepicker" disabled="disabled" id="report-to" name="report-to">
                                                 <div class="input-group-addon">
                                                     <i class="fa fa-calendar calendar-icon"></i>
                                                 </div>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                             </div>
                         </div>
                        <!-- if invoiceid found in url -->
                        <?php echo form_hidden('proposal_id',$proposal_id); ?>
                        <?php
                           $table_data = array(
                              _l('proposal') . ' #',
                              _l('proposal_subject'),
                              _l('proposal_to'),
                              _l('proposal_total'),
                              _l('proposal_igst'),
                              _l('proposal_cgst'),
                              _l('proposal_sgst'),
                              _l('proposal_date'),
                              _l('proposal_open_till'),
                              _l('tags'),
                              _l('Assigned Engineer'),
                              _l('proposal_date_created'),
                              _l('proposal_status'),
                            );

                             $custom_fields = get_custom_fields('proposal',array('show_on_table'=>1));
                             foreach($custom_fields as $field){
                                array_push($table_data,$field['name']);
                             }

                             $table_data = do_action('proposals_table_columns',$table_data);
                             render_datatable($table_data,'proposals',[],[
                                 'data-last-order-identifier' => 'proposals',
                                 'data-default-order'         => get_table_last_order('proposals'),
                             ]);
                           ?>
                     </div>
                  </div>
               </div>
               <div class="col-md-7 small-table-right-col">
                  <div id="proposal" class="hide">
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>var hidden_columns = [4,5,6,7];</script>
<?php init_tail(); ?>
<div id="convert_helper"></div>
<script>
   var proposal_id;
   $(function(){
     var Proposals_ServerParams = {
         "report_months": '[name="months-report"]',
         "report_from": '[name="report-from"]',
         "report_to": '[name="report-to"]',
     };
     $.each($('._hidden_inputs._filters input'),function(){
       Proposals_ServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
     });
     initDataTable('.table-proposals', admin_url+'proposals/table', ['undefined'], ['undefined'], Proposals_ServerParams, [7, 'desc']);
     init_proposal();
       var report_from = $('input[name="report-from"]');
       var report_to = $('input[name="report-to"]');
       var date_range = $('#date-range');
       $('select[name="months-report"]').on('change', function() {
           var val = $(this).val();
           report_to.attr('disabled', true);
           report_to.val('');
           report_from.val('');
           if (val == 'custom') {
               date_range.addClass('fadeIn').removeClass('hide');
               return;
           } else {
               if (!date_range.hasClass('hide')) {
                   date_range.removeClass('fadeIn').addClass('hide');
               }
           }
           gen_reports()
       });
       report_from.on('change', function() {
           var val = $(this).val();
           var report_to_val = report_to.val();
           if (val != '') {
               report_to.attr('disabled', false);
               if (report_to_val != '') {
                   gen_reports();
               }
           } else {
               report_to.attr('disabled', true);
           }
       });
       report_to.on('change', function() {
           var val = $(this).val();
           if (val != '') {
               gen_reports()
           }
       });
       function gen_reports(){
           let table_purchases = $(".table-proposals");
           if(table_purchases.length > 0){
               table_purchases.DataTable().ajax.reload()
                   .columns.adjust()
                   .responsive.recalc();
           }
       }
   });
</script>
<?php echo app_script('assets/js','proposals.js'); ?>
</body>
</html>
