<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
		 <?php //$value = (isset($billing_shipping) ? $billing_shipping : ''); 
					   $shipping_invoice_info = format_customer_info($invoice, 'invoice', 'shipping');
					   if(empty($shipping_invoice_info)){
						    $shipping_invoice_info = format_customer_info($invoice, 'invoice', 'billing');
					   }
					   ?>
         <?php echo form_open_multipart($this->uri->uri_string(),array('id'=>'expense-form','class'=>'dropzone dropzone-manual')) ;?>
		 <?php echo form_hidden('address',$shipping_invoice_info); ?>
         <div class="col-md-6">
            <div class="panel_s">
               <div class="panel-body">
                 <h4 class="no-margin"><?php echo $title; ?></h4>
                  <hr class="hr-panel-heading" />
                 
                  <?php $value = (isset($delivery_detail) ? _d($delivery_detail->date) : _d(date('Y-m-d')));
                    $date_attrs = array();
                    // if(isset($expense) && $expense->recurring > 0 && $expense->last_recurring_date != null) {
                      // $date_attrs['disabled'] = true;
                    // }
                   ?>
                  <?php echo render_date_input('date','invoice_delivery_add_edit_date',$value,$date_attrs); ?>
                  
				  <?php $value = (isset($invoice->id) ? $invoice->id : ''); ?>
				  <?php echo render_input('delivery_reference_no','invoice_delivery_reference_number',$value); ?>
				  
				  <?php /* $value = $invoice->id; //(isset($delivery_detail) ? $delivery_detail->sale_reference_no : ''); ?>
				  <?php echo render_input('sale_reference_no','invoice_delivery_sale_reference_number',$value); */ ?>
				  
					<div class="form-group" app-field-wrapper="customer_id">
						<label class="control-label"><?php echo _l('invoice_delivery_sale_reference_number'); ?></label>
						<?php $value = format_service_invoice_number($invoice->id); ?>
						<div class="form-control"><?php echo $value; ?></div>
						 <?php echo form_hidden('sale_reference_no',$value); ?>
					</div>
				  
				  
					<div class="form-group" app-field-wrapper="customer_id">
                     <label class="control-label"><?php echo _l('expense_add_edit_customer'); ?></label>
                     <?php $value = (isset($customer_name) ? $customer_name : ''); ?>
					 <div class="form-control"><?php echo $value; ?></div>
					
					</div>
				  
				  
					<div class="form-group" app-field-wrapper="customer_id">
					 
					   <label class="control-label"><?php echo "Address"; ?></label>
					   <div class="form-control" style="height:175px;"><?php echo $shipping_invoice_info; ?></div>
					  <?php //echo render_textarea('address','Address',$invoice_info,array('rows'=>4),array()); ?>
                  
					</div>
                 
                   <div class="btn-bottom-toolbar text-right">
                  <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                  </div>
               </div>
            </div>
         </div>
         <div class="col-md-6">
            <div class="panel_s">
               <div class="panel-body">
                  <h4 class="no-margin"><?php //echo _l('advanced_options'); ?></h4>
                  <hr class="hr-panel-heading" />
                  <?php
					 $selected = (isset($delivery_detail) ? $delivery_detail->status_id : '');
					 echo render_select('status_id',$status,array('id','name'),'Status',$selected);
				   ?>
				   
				   <?php
					 $selected = ''; //(isset($delivery_detail) ? $delivery_detail->delivered_by : '');
					 echo render_select('delivered_by',$staff,array('staffid','full_name'),'Delivered By',$selected);
				   ?>
				   
				   <?php $value = ''; //(isset($delivery_detail) ? $delivery_detail->received_by : ''); 
						echo render_input('received_by','invoice_delivery_received_by',$value); ?>
				  
				   <?php if(isset($delivery_detail) && $delivery_detail->attachment !== ''){ ?>
					
					
					
					<!-- -->
					 <div class="form-group" app-field-wrapper="attachment">
						 <div class="row">
						 
							<?php 
								$path = get_upload_path_by_type('delivery_modules').$delivery_detail->id.'/'.$delivery_detail->attachment;
								$href_url = site_url('download/file/delivery_modules/'.$delivery_detail->id.'/'.$delivery_detail->attachment);
								
								$img_url = site_url('download/preview_image?path='.protected_file_url_by_path($path,true).'&type='.$delivery_detail->filetype);
							?>
					 
						 
							 <div class="col-md-10">
								 <div class="preview-image">
									 <a href="<?php echo $href_url; ?>" target="_blank" data-lightbox="task-attachment" class="">
										<img src="<?php // echo $img_url; ?>" class="img img-responsive" width="200px" height="150px">
									 </a>
								 </div>
							 </div>
							 
							 <?php if($delivery_detail->attachment_added_from == get_staff_user_id() || is_admin()){ ?>
							 <div class="col-md-2 text-right">
								<a href="<?php echo admin_url('service_invoices/delete_delivery_module_attachment/'.$delivery_detail->id.'/'.$delivery_detail->invoice_id); ?>" class="text-danger _delete"><i class="fa fa fa-times"></i></a>
							 </div>
							 <?php } ?>
						 </div>
					</div>
                  <?php } ?>
                  
				  <?php if(!isset($delivery_detail) || (isset($delivery_detail) && $delivery_detail->attachment == '')){ ?>
                  <div id="dropzoneDragArea" class="dz-default dz-message">
                     <span><?php echo _l('expense_add_edit_attach_receipt'); ?></span>
                  </div>
                  <div class="dropzone-previews"></div> 
                  <?php } ?>
				  
				  <!-- <div class="form-group" app-field-wrapper="customer_id">
					<label class="control-label"><?php echo "Address"; ?></label>
				    <div class="form-control"><input type="file" name="attachment"></div>
				  </div>-->
				  
				  <?php $value = ''; // (isset($delivery_detail) ? $delivery_detail->note : ''); ?>
                  <?php echo render_textarea('note','expense_add_edit_note',$value,array('rows'=>4),array()); ?>
               </div>
            </div>
         </div>
         <?php echo form_close(); ?>
      </div>
      <div class="btn-bottom-pusher"></div>
   </div>
</div>
<?php //$this->load->view('admin/expenses/expense_category'); ?>
<?php init_tail(); ?>
<script>
   var customer_currency = '';
   Dropzone.options.expenseForm = false;
   var expenseDropzone;
   init_ajax_project_search_by_customer_id();
   var selectCurrency = $('select[name="currency"]');
   <?php if(isset($customer_currency)){ ?>
     var customer_currency = '<?php echo $customer_currency; ?>';
   <?php } ?>
     $(function(){

     if($('#dropzoneDragArea').length > 0){
        expenseDropzone = new Dropzone("#expense-form",  $.extend({},_dropzone_defaults(),{
          autoProcessQueue: false,
          clickable: '#dropzoneDragArea',
          previewsContainer: '.dropzone-previews',
          addRemoveLinks: true,
          maxFiles: 1,
          success:function(file,response){
           response = JSON.parse(response);
           if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
             window.location.assign(response.url);
           }
         },
       }));
     }

     _validate_form($('#expense-form'),{status_id:'required',date:'required',sale_reference_no:'required'},deliverySubmitHandler);

     
    });
	
	
	

     function deliverySubmitHandler(form){
		
      //selectCurrency.prop('disabled',false);

      //$('select[name="tax2"]').prop('disabled',false);
      //$('input[name="billable"]').prop('disabled',false);
      $('input[name="date"]').prop('disabled',false);

      $.post(form.action, $(form).serialize()).done(function(response) {
        response = JSON.parse(response);
        if (response.delivery_moduleid) {
         if(typeof(expenseDropzone) !== 'undefined'){
          if (expenseDropzone.getQueuedFiles().length > 0) {
            expenseDropzone.options.url = admin_url + 'service_invoices/add_delivery_module_attachment/' + response.delivery_moduleid+'/'+response.invoice_id;
            expenseDropzone.processQueue();
          } else {
            window.location.assign(response.url);
          }
        } else {
          window.location.assign(response.url);
        }
      } else {
        window.location.assign(response.url);
      }
    });
      return false;
    }
	
    </script>
</body>
</html>
