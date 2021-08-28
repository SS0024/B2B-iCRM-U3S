<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<?php
			echo form_open($this->uri->uri_string(),array('id'=>'requsite_forms-form','class'=>'_transaction_form'));
			if(isset($estimate) && $estimate->devide_gst == 1){				
				echo form_hidden('devide_gst',"yes");			
			}else{				
				echo form_hidden('devide_gst',"no");			
			}
			if(isset($estimate)){
				echo form_hidden('isedit');
			}
			?>
			<div class="col-md-12">
				<?php $this->load->view('admin/requsite_forms/estimate_template'); ?>
			</div>
			<?php echo form_close(); ?>
			<?php $this->load->view('admin/invoice_items/item'); ?>
		</div>
        <div class="modal fade" id="itemWareHouseModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabelunit">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabelunit"> Products Fab No
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="container-fluid">
                                <table class="table dt-table table-warehouse-items" data-order-col="0" data-order-type="asc">
                                    <thead>
                                    <tr>
                                        <th><?php echo 'Fab No'; ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="itemBOMModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabelunit">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabelunit"> BOM Details
                        </h4>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="container-fluid">
                                <div class="text-justify" id="textDiv">

                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>
</div>
<?php init_tail(); ?>
<script>
	$(function(){
        validate_requsite_forms_form();
		// Init accountacy currency symbol
		init_currency_symbol();
		// Project ajax search
		init_ajax_project_search_by_customer_id();
		// Maybe items ajax search
	    init_ajax_search('items','#item_select.ajax-search',undefined,admin_url+'items/search');
        $('body').on("click",'.update_number',function (e) {
            e.preventDefault();
            requestGetJSON(admin_url+'requsite_forms/get_latest_number').done(function(response) {
                if (response.success === true || response.success == 'true') {
                    $('body input[name="number"]').val(response.number)
                }
            }).fail(function(data) {
                alert_float('danger', data.responseText);
            });
        })
	});
    var wareHouseTableUrl = '';
    function openWareHouseQuantityModal(){
        let itemId= $('select[name="item_select"] option:selected').text();
        if(itemId != ''){
            $('#itemWareHouseModel').modal('show');
            wareHouseTableUrl = admin_url+'invoice_items/getFabNos?itemId='+itemId;
        }
    }
    $('#itemWareHouseModel').on('shown.bs.modal', function () {
        $(".table-warehouse-items").DataTable().destroy();
        initDataTable('.table-warehouse-items', wareHouseTableUrl, undefined, undefined,'undefined',[0,'asc']);
    });
    $('#itemWareHouseModel').on('hidden.bs.modal', function (e) {
        $(".table-warehouse-items").DataTable().destroy();
    });
    var itemBOMUrl = '';
    function openItemBom(){
        $('#itemBOMModel').modal('show');
        let itemId = $("input[name='changeable_fab_no']").val();
        itemBOMUrl = admin_url+'invoice_items/getBOM?fab_no='+itemId;
    }
    $('#itemBOMModel').on('shown.bs.modal', function () {
        $("#textDiv").load(itemBOMUrl);
    });
    $('#itemBOMModel').on('hidden.bs.modal', function (e) {
        $("#textDiv").html();
    });
</script>
</body>
</html>
