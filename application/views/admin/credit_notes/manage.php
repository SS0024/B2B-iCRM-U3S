<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
        <div class="_filters _hidden_inputs">
           <?php
            foreach($statuses as $status) {
               echo form_hidden('credit_notes_status_'.$status['id'],isset($status['filter_default'])
                  && $status['filter_default'] ? 'true' : '');
            }
           foreach($years as $year){
              echo form_hidden('year_'.$year['year'],$year['year']);
           }
        ?>
     </div>
     <div class="col-md-12">
      <div class="panel_s mbot10">
         <div class="panel-body _buttons">
            <?php if(has_permission('credit_notes','','create')){ ?>
            <a href="<?php echo admin_url('credit_notes/credit_note'); ?>" class="btn btn-info pull-left display-block">
               <?php echo _l('new_credit_note'); ?>
            </a>
            <?php } ?>
            <div class="display-block text-right">
             <div class="btn-group pull-right mleft4 btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="<?php echo _l('filter_by'); ?>">
               <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  <i class="fa fa-filter" aria-hidden="true"></i>
               </button>
               <ul class="dropdown-menu width300">
                  <li>
                     <a href="#" data-cview="all" onclick="dt_custom_view('','.table-credit-notes',''); return false;">
                        <?php echo _l('credit_notes_list_all'); ?>
                     </a>
                  </li>
                  <li class="divider"></li>
                  <?php foreach($statuses as $status){ ?>
                  <li class="<?php if(isset($status['filter_default']) && $status['filter_default']){echo 'active';} ?>">
                     <a href="#" data-cview="credit_notes_status_<?php echo $status['id']; ?>" onclick="dt_custom_view('credit_notes_status_<?php echo $status['id']; ?>','.table-credit-notes','credit_notes_status_<?php echo $status['id']; ?>'); return false;">
                        <?php echo format_credit_note_status($status['id'],true); ?>
                     </a>
                  </li>
                  <?php } ?>
                  <div class="clearfix"></div>
                  <?php
                  if(count($years) > 0){ ?>
                  <li class="divider"></li>
                  <?php foreach($years as $year){ ?>
                  <li class="active">
                     <a href="#" data-cview="year_<?php echo $year['year']; ?>" onclick="dt_custom_view(<?php echo $year['year']; ?>,'.table-credit-notes','year_<?php echo $year['year']; ?>'); return false;"><?php echo $year['year']; ?>
                     </a>
                  </li>
                  <?php } ?>
                  <?php } ?>
               </ul>
            </div>
            <a href="#" class="btn btn-default btn-with-tooltip toggle-small-view hidden-xs" onclick="toggle_small_view('.table-credit-notes','#credit_note'); return false;" data-toggle="tooltip" title="<?php echo _l('invoices_toggle_table_tooltip'); ?>"><i class="fa fa-angle-double-left"></i></a>
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
               <!-- if credit not id found in url -->
               <?php echo form_hidden('credit_note_id',$credit_note_id); ?>
               <?php $this->load->view('admin/credit_notes/table_html'); ?>
            </div>
         </div>
      </div>
      <div class="col-md-7 small-table-right-col">
         <div id="credit_note" class="hide">
         </div>
      </div>
   </div>
</div>
</div>
</div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>
   var hidden_columns = [4,5,6,7];
</script>
<?php init_tail(); ?>
<script>
   $(function(){
       var Credit_Notes_ServerParams = {
           "report_months": '[name="months-report"]',
           "report_from": '[name="report-from"]',
           "report_to": '[name="report-to"]',
       };
     $.each($('._hidden_inputs._filters input'),function(){
       Credit_Notes_ServerParams[$(this).attr('name')] = '[name="'+$(this).attr('name')+'"]';
     });
     initDataTable('.table-credit-notes', admin_url+'credit_notes/table', ['undefined'], ['undefined'], Credit_Notes_ServerParams, [0, 'desc']);
     init_credit_note();
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
           let table_purchases = $(".table-credit-notes");
           if(table_purchases.length > 0){
               table_purchases.DataTable().ajax.reload()
                   .columns.adjust()
                   .responsive.recalc();
           }
       }
  });
</script>
</body>
</html>
