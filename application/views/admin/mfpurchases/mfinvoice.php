<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<?php
			echo form_open($this->uri->uri_string(),array('id'=>'invoice-form','class'=>'_transaction_form invoice-form'));			
			if(isset($invoice)){
				echo form_hidden('isedit',1);
			}
			?>
			<div class="col-md-12">
				<?php $this->load->view('admin/mfpurchases/mfinvoice_template'); ?>
			</div>
			<?php echo form_close(); ?>
			<?php $this->load->view('admin/invoice_items/item'); ?>
		</div>
	</div>
</div>
<?php init_tail(); ?>
<script>
	$(function(){
        validate_purchase_form();
	    // Init accountacy currency symbol
	    init_currency_symbol();
        init_ajax_search('suppliers', '#supplier.ajax-search', undefined, admin_url + 'suppliers/search');
	    // Maybe items ajax search
	    init_ajax_search('items','#item_select.ajax-search',undefined,admin_url+'mfitems/mf_item_search');
        $("#supplier.ajax-search").on('change',function () {
            requestGetJSON(admin_url + 'suppliers/get_supplier/' + $("#supplier.ajax-search").val()).done(function (response) {
                if (response) {
                    console.log(response);
                    $("#credit_period_days").val(response.name); ;
                }
            });
        });

	});
</script>
</body>
</html>
