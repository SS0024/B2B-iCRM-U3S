<div class="col-md-12 no-padding animated fadeIn">
    <div class="panel_s">
        <?php echo form_open_multipart('admin/purchaseorder/record_payment',array('id'=>'record_payment_form','class'=>'dropzone dropzone-manual')); ?>
        <?php echo form_hidden('purchaseorder_id',$invoice->id); ?>		<?php echo form_hidden('temp_file_id',0,array('id' =>'temp_file_id')); ?>
        <div class="panel-body">
            <h4 class="no-margin"><?php echo _l('record_payment_for_invoice'); ?> <?php echo format_purchaseorder_number($invoice->id); ?></h4>
           <hr class="hr-panel-heading" />
            <div class="row">
                <div class="col-md-6">
                    <?php
                    echo render_input('amount','record_payment_amount_received',$amount,'number',array()); ?>
                    <?php echo render_date_input('date','record_payment_date',_d(date('Y-m-d'))); ?>
                    <div class="form-group">
                        <label for="paymentmode" class="control-label"><?php echo _l('payment_mode'); ?></label>
                        <select class="selectpicker" name="paymentmode" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <option value=""></option>
                            <?php foreach($payment_modes as $mode){ ?>
                            <?php if(is_payment_mode_allowed_for_invoice($mode['id'],$invoice->id)){ ?>
                            <option value="<?php echo $mode['id']; ?>"><?php echo $mode['name']; ?></option>
                            <?php } ?>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="checkbox checkbox-primary mtop15 do_not_redirect hide inline-block">
                        <input type="checkbox" name="do_not_redirect" id="do_not_redirect" checked>
                        <label for="do_not_redirect"><?php echo _l('do_not_redirect_payment'); ?></label>
                    </div>

                </div>
                <div class="col-md-6">
                    <?php echo render_input('transactionid','payment_transaction_id'); ?>
                       <div class="form-gruoup">
                            <label for="note" class="control-label"><?php echo _l('record_payment_leave_note'); ?></label>
                            <textarea name="note" class="form-control" rows="8" placeholder="<?php echo _l('invoice_record_payment_note_placeholder'); ?>" id="note"></textarea>
                        </div>
                </div>
            </div>
            <div class="pull-right mtop15">
                <a href="#" class="btn btn-danger" onclick="init_purchaseorder(<?php echo $invoice->id; ?>); return false;"><?php echo _l('cancel'); ?></a>
                <button type="submit" autocomplete="off" data-loading-text="<?php echo _l('wait_text'); ?>" data-form="#record_payment_form" class="btn btn-success"><?php echo _l('submit'); ?></button>
            </div>
            <?php
            if($payments){ ?>
            <div class="mtop25 inline-block full-width">
                <h5 class="bold"><?php echo _l('invoice_payments_received'); ?></h5>
                <?php include_once(APPPATH . 'views/admin/purchaseorder/invoice_payments_table.php'); ?>
            </div>
            <?php } ?>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>
<script>	
	var paymentDropzone = '';		
	Dropzone.options.recordPaymentForm = false;			
   $(function(){	 		 	 	 
     init_selectpicker();
     init_datepicker();
     _validate_form($('#record_payment_form'),{amount:'required',date:'required',paymentmode:'required'});
	 
     var $sMode = $('select[name="paymentmode"]');
     var total_available_payment_modes = $sMode.find('option').length - 1;
     if(total_available_payment_modes == 1) {
        $sMode.selectpicker('val',$sMode.find('option').eq(1).attr('value'));
        $sMode.trigger('change');
     }	 	 
	 
	 if($('#dropzoneDragArea').length > 0){        
		
		paymentDropzone = new Dropzone("#record_payment_form",  $.extend({},_dropzone_defaults(),{         

			autoProcessQueue: true,          
			//autoProcessQueue: false,          
			clickable: '#dropzoneDragArea',          
			previewsContainer: '.dropzone-previews',          
			addRemoveLinks: true, 
			createImageThumbnails:true,
			maxFiles: 1,
			uploadMultiple: false,
			url:admin_url + 'purchaseorder/add_invoice_payment_attachment/',
			success:function(file,response){           
				response = JSON.parse(response);		   
				//console.log(response);           
				if (response.id) {			  
					$('input[name="temp_file_id"]').val(response.id);
					$('.dropzone-file-preview').html(response.data);
					$('.dropzone-file-preview').show();
					$("#dropzoneDragArea").hide();
					
				}else{			    
					var invoice_id =  $('input[name="purchaseorder_id"]').val();
					window.location.assign(admin_url + 'purchaseorder/list_invoices#'+invoice_id);
					
				}        
			}		        
		}));     
	}
	
	$(document).on("submit","#record_payment_fo123rm",function(e){
		e.preventDefault();
		if(typeof(paymentDropzone) !== 'undefined'){
			if (paymentDropzone.getQueuedFiles().length > 0) {
				//paymentDropzone.options.url = admin_url + 'expenses/add_expense_attachment/' + response.expenseid;
				paymentDropzone.processQueue();
				// if (paymentDropzone.getUploadingFiles().length === 0 && paymentDropzone.getQueuedFiles().length === 0) {
					// return true;
				// }
			}else{
				alert("in else");
				//$("#record_payment_form").submit();
				return true;
			}
		}else{
			alert("in outer else");
			//$("#record_payment_form").submit();
			return true;
		}
		
		
	});
 });  
 
 // Deleting payment attachment
function delete_temp_payment_attachment(wrapper, id) {
    if (confirm_delete()) {
        requestGetJSON(admin_url+'purchaseorder/delete_temp_payment_attachment/' + id).done(function(response) {
			if (response.success === true || response.success == 'true') { 
						$('input[name="temp_file_id"]').val(0);
						$('.dropzone-file-preview').html('');
						$('.dropzone-file-preview').hide();
						$("#dropzoneDragArea").show();
			}
        }).fail(function(data) {
            alert_float('danger', data.responseText);
        });
    }
}
</script>
