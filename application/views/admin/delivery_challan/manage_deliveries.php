<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="panel_s">
			<div class="panel-body">
				<?php $this->load->view('admin/delivery_challan/deliveries_table_html'); ?>
			</div>
		</div>
	</div>
</div>
<?php init_tail(); ?>
<script>
	$(function(){
		initDataTable('.table-payments', admin_url+'delivery_challan/deliveries_table', undefined, undefined,'undefined',<?php echo do_action('payments',json_encode(array(0,'desc'))); ?>);
	});
</script>
</body>
</html>
