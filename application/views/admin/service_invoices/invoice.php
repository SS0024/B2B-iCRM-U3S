<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<?php
			echo form_open($this->uri->uri_string(),array('id'=>'service_invoice-form','class'=>'_transaction_form service_invoice-form'));
			if(isset($invoice) && $invoice->devide_gst == 1){				
				echo form_hidden('devide_gst',"yes");			
			}else{				
				echo form_hidden('devide_gst',"no");			
			}
			if(isset($invoice)){
				echo form_hidden('isedit');
			}
			?>
			<div class="col-md-12">
				<?php $this->load->view('admin/service_invoices/invoice_template'); ?>
			</div>
			<?php echo form_close(); ?>
			<?php $this->load->view('admin/invoice_items/item'); ?>
		</div>
	</div>
</div>
<?php init_tail(); ?>
<script>
	$(function(){
        validate_service_invoice_form();
	    // Init accountacy currency symbol
	    init_currency_symbol();
	    // Project ajax search
	    init_ajax_project_search_by_customer_id();
	    // Maybe items ajax search
	    init_ajax_search('items','#item_select.ajax-search',undefined,admin_url+'items/search');
        $('body').on("click",'.update_number',function (e) {
            e.preventDefault();
            requestGetJSON(admin_url+'service_invoices/get_latest_number').done(function(response) {
                if (response.success === true || response.success == 'true') {
                    $('body input[name="number"]').val(response.number)
                }
            }).fail(function(data) {
                alert_float('danger', data.responseText);
            });
        })
	});
</script>
</body>
</html>
