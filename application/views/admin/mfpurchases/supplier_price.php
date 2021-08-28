<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
        <style>
            table.itemses tr.main td {
                  padding-top: 5px !important;
                  padding-bottom: 5px !important;
        </style>
		<div class="row">
			<?php
			echo form_open($this->uri->uri_string(),array('id'=>'invoice-form','class'=>'_transaction_form invoice-form'));
			?>
			<div class="col-md-12">
                <div class="panel_s purchase accounting-template">
                    <div class="panel-body mtop10">
                        <h2>
                            <?php echo 'Supplier Price'; ?>
                        </h2>
                        <div class="table-responsive s_table">

                                <table class="table itemses invoice-items-preview">
                                <thead>
                                <tr>
                                    <th width="35%" align="left"><i class="fa fa-exclamation-circle" aria-hidden="true" ></i> <?php echo _l('estimate_table_item_heading'); ?>
                                    </th>
                                    <th width="65%" class="received_qty" align="left"> <?php echo "Suppliers"; ?></th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php
                                foreach ($items as $item){ ?>
                                <tr class="main">

                                    <td>
                              <?php echo $item['product_code'].'<br/>'.$item['product_name']; ?>
                                    </td>
                                    <td>
                                        <?php
                                        foreach ($suppliers as $supplier){
                                        if ($supplier['itemid'] == $item['id']){ ?>
                                            <table>

                                        <tr>
                                        <td width="75%">
                                            <?php
                                            foreach ($suppliers_data as $supplier_data){
                                                if($supplier_data['id'] == $supplier['suppliers_id']){
                                                  echo  $supplier_data['company'];
                                                }
                                            }

                                           ?>
                                        </td>
                                            <td width="25%">
                                                <input type="hidden" name="supplier_id[]" value="<?php echo $supplier['id']; ?>" >
                                                <input type="number" name="<?php echo $supplier['id']; ?>" class="form-control"
                                                       placeholder="<?php echo _l('item_amount_placeholder'); ?>"   min="0"  value="<?php echo $supplier['supplier_item_price']; ?>" >
                                            </td>
                                        </tr>
                                            </table>

                                            <?php  }
                                            } ?>

                                    </td>
                                </tr>
                                <?php } ?>

                                </tbody>
                            </table>
                        </div>



                        <div id="removed-items"></div>
                    </div>


                </div>

            </div>
			</div>

        <div class="row">
            <div class="col-md-12 mtop15">
                <div class="panel-body ">
                    <div class="btn-bottom-toolbar text-right">
                        <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                    </div>
                </div>
                <div class="btn-bottom-pusher"></div>
            </div>
        </div>
			<?php echo form_close(); ?>

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
