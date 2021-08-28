<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<?php
			include_once(APPPATH.'views/admin/mfpurchases/filter_params.php');
			$this->load->view('admin/mfpurchases/list_template');
			?>
		</div>
	</div>
</div>
<?php $this->load->view('admin/includes/modals/sales_attach_file'); ?>
<script>var hidden_columns = [];</script>
<?php init_tail(); ?>
<script>
	$(function(){
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
            let table_purchases = $(".table-mfpurchases");
            if(table_purchases.length > 0){
                table_purchases.DataTable().ajax.reload()
                    .columns.adjust()
                    .responsive.recalc();
            }
        }
        init_mfpurchase();
	});

</script>
</body>
</html>
