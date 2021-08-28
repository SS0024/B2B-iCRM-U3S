<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<?php
			include_once(APPPATH.'views/admin/delivery_challan/filter_params.php');
			$this->load->view('admin/delivery_challan/list_template');
			?>
		</div>
	</div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>var hidden_columns = [2,6,7,8];</script>
<?php init_tail(); ?>
<script>
	$(function(){
        init_delivery_challan();
	});
</script>
</body>
</html>
