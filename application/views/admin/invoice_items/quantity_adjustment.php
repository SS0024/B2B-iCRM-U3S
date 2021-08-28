<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tab-content">
                            <h4 class="customer-profile-group-heading"><?= 'Quantity Adjustment'; ?></h4>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-part">
                                        <?php echo form_open($this->uri->uri_string(), array('id' => 'barcode-print-form', 'autocomplete' => 'off')); ?>
                                        <div class="col-md-4">
                                            <?php $value = isset($adjustment) ? _dt($adjustment->date) : '' ?>
                                            <?php echo render_datetime_input('date', 'Date', $value); ?>
                                        </div>
                                        <div class="col-md-4">
                                            <?php $value = isset($adjustment) ? $adjustment->reference_no : '' ?>
                                            <?php echo render_input('reference_no', 'Reference No', $value); ?>
                                        </div>
                                        <div class="col-md-4">
                                            <?php $value = isset($adjustment) ? $adjustment->warehouse_id : '' ?>
                                            <?php echo render_select('warehouse', $warehouses, array('id', 'name'), 'Warehouse', $value); ?>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="styles" class="control-label">Add Item</label>
                                                <select name="item_select" class="selectpicker no-margin<?php if($ajaxItems == true){echo ' ajax-search';} ?>" data-width="100%" id="item_select" data-none-selected-text="<?php echo _l('add_item'); ?>" data-live-search="true">
                                                    <option value=""></option>
                                                    <?php foreach($items as $group_id=>$_items){ ?>
                                                        <optgroup data-group-id="<?php echo $group_id; ?>" label="<?php echo $_items[0]['group_name']; ?>">
                                                            <?php foreach($_items as $item){ ?>
                                                                <?php
                                                                if ((isset($bodyclass) && ($bodyclass == 'estimates' || $bodyclass =='invoice' || $bodyclass =='purchaseorder'))){
                                                                    if(isset($item['quantity']) && $item['quantity'] != 0){
                                                                        ?>
                                                                        <option value="<?php echo $item['id']; ?>" data-subtext="<?php echo strip_tags(mb_substr($item['long_description'],0,200)).'...'; ?>">(<?php echo _format_number($item['rate']); ; ?>) <?php echo $item['description']; ?></option>
                                                                        <?php
                                                                    }
                                                                }else{
                                                                    ?>
                                                                    <option value="<?php echo $item['id']; ?>" data-subtext="<?php echo strip_tags(mb_substr($item['long_description'],0,200)).'...'; ?>">(<?php echo _format_number($item['rate']); ; ?>) <?php echo $item['description']; ?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                            <?php } ?>
                                                        </optgroup>
                                                    <?php } ?>
                                                </select>
                                                <!--<select name="add_item" class="selectpicker no-margin ajax-search"
                                                        data-width="100%" id="item_select"
                                                        data-none-selected-text="<?php /*echo _l('add_item'); */?>"
                                                        data-live-search="true">
                                                    <option value=""></option>
                                                    <?php /*foreach($items as $group_id=>$_items){ */?>
                                                        <optgroup data-group-id="<?php /*echo $group_id; */?>" label="<?php /*echo $_items[0]['group_name']; */?>">
                                                            <?php /*foreach($_items as $item){ */?>
                                                                <option value="<?php /*echo $item['id']; */?>" data-subtext="<?php /*echo strip_tags(mb_substr($item['long_description'],0,200)).'...'; */?>">(<?php /*echo _format_number($item['rate']); ; */?>) <?php /*echo $item['description']; */?></option>
                                                            <?php /*} */?>
                                                        </optgroup>
                                                    <?php /*} */?>
                                                </select>-->
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="controls table-controls">
                                                <table id="bcTable"
                                                       class="table items table-bordered table-condensed table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th class="col-xs-4"><?= _l("product_name") . ' (Product Code)'; ?></th>
                                                        <th class="col-xs-2"><?= _l("Type"); ?></th>
                                                        <th class="col-xs-2"><?= _l("quantity"); ?></th>
                                                        <th class="col-xs-1 text-center" style="width:30px;">
                                                            <i class="fa fa-trash-o"
                                                               style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                        </th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    <?php
                                                    foreach ($adjustment->products as $product) {
                                                        ?>
                                                        <tr id="row_<?= $product['itemid'] ?>"
                                                            class="row_<?= $product['itemid'] ?>"
                                                            data-item-id="<?= $product['itemid'] ?>">
                                                            <td>
                                                                <input name="product_id[]" type="hidden" class="rid"
                                                                       value="<?= $product['itemid'] ?>">
                                                                <span class="sname"
                                                                      id="name_<?= $product['itemid'] ?>"><?= $product['long_description'] . ' (' . $product['description'] . ')' ?></span>
                                                            </td>
                                                            <td>
                                                                <select name="type[]"
                                                                        class="form-contol selectpicker rtype"
                                                                        style="width:100%;">
                                                                    <option <?= ($product['type'] == 'subtraction') ? 'selected="selected"' : ''; ?>
                                                                            value="subtraction">Subtraction
                                                                    </option>
                                                                    <option <?= ($product['type'] == 'addition') ? 'selected="selected"' : ''; ?>
                                                                            value="addition">Addition
                                                                    </option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <input class="form-control rquantity" name="quantity[]"
                                                                       type="number" value="<?= $product['quantity'] ?>"
                                                                       data-id="<?= $product['itemid'] ?>"
                                                                       data-item="<?= $product['itemid'] ?>"
                                                                       id="quantity_<?= $product['itemid'] ?>">
                                                            </td>
                                                            <td class="text-center"><i class="fa fa-times del tip qadel"
                                                                                       id="<?= $product['itemid'] ?>"
                                                                                       title="Remove"
                                                                                       style="cursor:pointer;"></i></td>
                                                        </tr>
                                                        <?php
                                                    }
                                                    ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <?php
                                            $value = isset($adjustment) ? $adjustment->note : '';
                                            echo render_textarea('note', 'Note', $value);
                                            ?>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <?php echo form_submit('print', _l("save"), 'class="btn btn-primary"'); ?>
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
    if (typeof (jQuery) != 'undefined') {
        init_item_js();
    } else {
        window.addEventListener('load', function () {
            var initItemsJsInterval = setInterval(function () {
                if (typeof (jQuery) != 'undefined') {
                    init_item_js();
                    clearInterval(initItemsJsInterval);
                }
            }, 1000);
        });
    }


    function init_item_js() {
        $(function () {
            init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');
            // Add item to preview from the dropdown for invoices estimates
            $(document).on('click', '.del', function () {
                var id = $(this).attr('id');
                $(this).closest('#row_' + id).remove();
            });
            _validate_form($('#barcode-print-form'), {date: 'required', warehouse: 'required'});
        });
        $('select[name="item_select"]').on("changed.bs.select",
            function(e, clickedIndex, newValue, oldValue) {
                var itemid = $(this).selectpicker('val');
                if (newValue != oldValue &&  itemid != '') {
                    requestGetJSON('invoice_items/get_item_by_id/' + itemid).done(function (response) {
                        add_product_item(response);
                    });
                }
            });
    }

    function add_product_item(item) {
        if (typeof item.itemid != "undefined") {
            var row_no = item.itemid;
            var newTr = $('<tr id="row_' + row_no + '" class="row_' + row_no + '" data-item-id="' + row_no + '"></tr>');
            tr_html = '<td><input name="product_id[]" type="hidden" class="rid" value="' + row_no + '"><span class="sname" id="name_' + row_no + '">' + item.long_description + ' (' + item.description + ')</span></td>';
            tr_html += '<td><select name="type[]" class="form-contol selectpicker rtype" style="width:100%;"><option value="subtraction">Subtraction</option><option value="addition">Addition</option></select></td>';
            tr_html += '<td><input class="form-control text-center rquantity" name="quantity[]" type="text" value="1" data-id="' + row_no + '" data-item="' + row_no + '" id="quantity_' + row_no + '"></td>';
            tr_html += '<td class="text-center"><i class="fa fa-times del tip qadel" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
            newTr.html(tr_html);
            newTr.prependTo("#bcTable tbody");
            if ($('#item_select').hasClass('ajax-search') && $('#item_select').selectpicker('val') !== '') {
                $('#item_select').prepend('<option></option>');
            }
            init_selectpicker();
            $('#item_select').selectpicker('val', '');
            return true;
        }
        return false;
    }

</script>
</body>
</html>
