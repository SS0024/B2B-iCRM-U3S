<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tab-content">
                            <h4 class="customer-profile-group-heading"><?= 'Stock In'; ?></h4>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-part">
                                        <?php echo form_open($this->uri->uri_string(), array('id' => 'barcode-print-form', 'autocomplete' => 'off')); ?>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="styles" class="control-label">Item box</label>
                                                <input type="text" class="form-control" name="item_box" value=""
                                                       onchange="updateStockDetails(this.value)"/>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="controls table-controls">
                                                <table id="bcTable"
                                                       class="table items table-bordered table-condensed table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th class="col-xs-4"><?= _l("product_name") . ' (Product Code)'; ?></th>
                                                        <th class="col-xs-2"><?= _l("quantity"); ?></th>
                                                        <th class="col-xs-2"><?= _l("quantity"); ?> Received</th>
                                                        <th class="col-xs-1 text-center" style="width:30px;">
                                                            <i class="fa fa-trash-o"
                                                               style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                        </th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    $i = 0;
                                                    $productIds = [];
                                                    echo "<input type='hidden' id='is_have_stock_count' value='0'>";
                                                    foreach ($adjustment->items as $product) {
                                                        $productIds[] = $product['product_id'];
                                                        if ($product['quantity_received'] == $product['quantity_balance']) {
                                                            echo "<input type='hidden' id='change_stock_status_{$product['product_id']}' value='1'>";
                                                        }else{
                                                            echo "<input type='hidden' id='change_stock_status_{$product['product_id']}' value='0'>";
                                                        }
                                                        ?>
                                                        <tr id="row_<?= $product['product_id'] ?>"
                                                            class="row_<?= $product['product_id'] ?>"
                                                            data-item-id="<?= $product['product_id'] ?>">
                                                            <td>
                                                                <input name="item_id[]" type="hidden" class="rid"
                                                                       value="<?= $product['id'] ?>">
                                                                <input name="product_id[]" type="hidden" class="rid"
                                                                       value="<?= $product['product_id'] ?>">
                                                                <span class="sname"
                                                                      id="name_<?= $product['product_id'] ?>"><?= $product['product_name'] . ' (' . $product['product_code'] . ')' ?></span>&nbsp;&nbsp;&nbsp;
                                                                <a href="<?= admin_url('invoice_items/print_barcodes/' . $product['product_id']) . '?quantity=' . round($product['quantity_received'], 0) ?>&is_purchase=true"
                                                                   class="text-success"><?= _l('Print Barcodes') ?></a>
                                                            </td>
                                                            <td>
                                                                <input class="form-control rquantity" readonly
                                                                       name="quantity[]"
                                                                       type="number"
                                                                       value="<?= round($product['quantity_balance']) ?>"
                                                                       data-id="<?= $product['id'] ?>"
                                                                       data-item="<?= $product['product_id'] ?>"
                                                                       max="<?= $product['quantity_received'] ?>"
                                                                       id="quantity_<?= $product['product_id'] ?>">
                                                            </td>
                                                            <td>
                                                                <span class="sname"><?= round($product['quantity_received'], 0) ?></span>
                                                            </td>
                                                            <td class="text-center" id="change_stock_status_<?= $product['product_id'] ?>" >
                                                                <?php
                                                                if ($product['quantity_received'] == $product['quantity_balance']) {
                                                                    ?>
                                                                    <i class="fa fa-check tip qadel"
                                                                       style="cursor:pointer;"></i>
                                                                    <?php
                                                                } else {
                                                                    ?>
                                                                    <i class="fa fa-times tip qadel"
                                                                       style="cursor:pointer;"></i>
                                                                    <?php
                                                                }
                                                                ?>
                                                            </td>
                                                        </tr>
                                                        <?php
                                                    }
                                                    ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <?php echo form_submit('print', _l("update"), 'class="btn btn-primary" id="submit_btn"'); ?>
                                            </div>
                                        </div>
                                        <?= form_close(); ?>
                                        <div class="clearfix"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    var productIds = JSON.parse('<?= json_encode($productIds); ?>');
    function updateStockDetails(itemLabel) {
        requestGetJSON('invoice_items/get_item_by_code/' + itemLabel).done(function (response) {
            if (response.id) {
                var itemQ = $("#quantity_" + response.id);
                if (itemQ.attr('max') < (parseInt(itemQ.val()) + 1)) {
                    alert_float('warning', 'Quantity cannot be higher then received quantity.')
                } else {
                    itemQ.val(parseInt(itemQ.val()) + 1);
                    if(itemQ.attr('max') == parseInt(itemQ.val())){
                        $("#change_stock_status_"+response.id).val(1);
                        $("#change_stock_status_"+response.id).html('<i class="fa fa-check tip qadel" style="cursor:pointer;"></i>');
                    }else {
                        $("#change_stock_status_"+response.id).val(0);
                        $("#change_stock_status_"+response.id).html('<i class="fa fa-times tip qadel" style="cursor:pointer;"></i>');
                    }
                }
                $('input[name="item_box"]').val('')
                checkStatus();
            }
        });
    }
    function checkStatus() {
        var is_have_stock_count = [];
        for (i = 0; i < productIds.length; i++) {
            var val =$("#change_stock_status_"+productIds[i]).val();
            is_have_stock_count.push(val);
        }
        let unique = is_have_stock_count.filter((v, i, a) => a.indexOf(v) === i);
        if(unique.indexOf("0") != -1){
            $("#submit_btn").prop('disabled',true);
        }else {
            $("#submit_btn").prop('disabled',false);
        }
    }
    checkStatus();
</script>
</body>
</html>
