<div class="modal fade" id="sales_item_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <span class="edit-title"><?php echo _l('invoice_item_edit_heading'); ?></span>
                    <span class="add-title"><?php echo _l('invoice_item_add_heading'); ?></span>
                </h4>
            </div>
            <?php echo form_open('admin/invoice_items/manage', array('id' => 'invoice_item_form')); ?>
            <?php echo form_hidden('itemid'); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="alert alert-warning affect-warning hide">
                            <?php echo _l('changing_items_affect_warning'); ?>
                        </div>
                        <?php echo render_input('description', 'invoice_items_list_description'); ?>
                        <?php echo render_textarea('long_description', 'invoice_item_add_edit_description'); ?>
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="product_cost"
                                           class="control-label"><?php echo _l('invoice_item_add_edit_purchase_rate').' - ' . $base_currency->name . ' <small>(' . _l('base_currency_string') . ')</small>'; ?></label>
                                    <input type="number" id="product_cost" name="product_cost" class="form-control"
                                           value="">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="margin" class="control-label">Margin (%)</label>
                                    <input type="number" id="margin" name="margin" onchange="calculate_rate(true); return false;" class="form-control" value="">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="rate"
                                           class="control-label"><?php echo _l('invoice_item_add_edit_rate').' - '. $base_currency->name . ' <small>(' . _l('base_currency_string') . ')</small>'; ?></label>
                                    <input type="number" id="rate" name="rate" onchange="calculate_rate(false); return false;" class="form-control" value="">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <?php echo render_input('warranty','Warranty in months','','number'); ?>
                            </div>
                            <div class="col-md-6">
                                <?php $crdate = date("d-m-Y H:i:s", strtotime("+2 year", strtotime(date('Y-m-d'))));
                                 echo render_date_input('upto','Valid Upto',$crdate); ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?php
                                    $bs = array(['id' => 'code25', 'name' => 'Code25'],
                                        ['id' => 'code39', 'name' => 'Code39'],
                                        ['id' => 'code128', 'name' => 'Code128'],
                                        ['id' => 'ean8', 'name' => 'EAN8'],
                                        ['id' => 'ean13', 'name' => 'EAN13'],
                                        ['id' => 'upca', 'name' => 'UPC-A'],
                                        ['id' => 'upce', 'name' => 'UPC-E']);
                                    echo render_select('barcode_symbology', $bs, array('id', 'name'), 'Barcode Symbology','code128'); ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?php echo render_select('brand', $items_brands, array('id', 'name'), 'Brand'); ?>
                                </div>
                            </div>
                        </div>
                        <?php
                        foreach ($currencies as $currency) {
                            if ($currency['isdefault'] == 0 && total_rows('tblclients', array('default_currency' => $currency['id'])) > 0) { ?>
                                <div class="form-group">
                                    <label for="rate_currency_<?php echo $currency['id']; ?>" class="control-label">
                                        <?php echo _l('invoice_item_add_edit_rate_currency', $currency['name']); ?></label>
                                    <input type="number" id="rate_currency_<?php echo $currency['id']; ?>"
                                           name="rate_currency_<?php echo $currency['id']; ?>" class="form-control"
                                           value="">
                                </div>
                            <?php }
                        }
                        ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="tax"><?php echo _l('tax_1'); ?></label>
                                    <select class="selectpicker display-block" data-width="100%" name="tax"
                                            data-none-selected-text="<?php echo _l('no_tax'); ?>">
                                        <option value=""></option>
                                        <?php foreach ($taxes as $tax) { ?>
                                            <option value="<?php echo $tax['id']; ?>"
                                                    data-subtext="<?php echo $tax['name']; ?>"><?php echo $tax['taxrate']; ?>
                                                %
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?php echo render_input('rack_no','Rack No'); ?>
                                </div>
                            </div>
                        </div>
                        <div class="clearfix mbot15"></div>
                        <div class="row">
                            <div class="col-md-6"><?php echo render_select('unit', $items_units, array('id', 'name'), 'unit'); ?></div>
                            <div class="col-md-6"><?php echo render_select('purchase_unit', $items_units, array('id', 'name'), 'Purchase unit'); ?></div>
                        </div>
                        <div class="row">
                            <div class="col-md-6"><?php echo render_select('group_id', $items_groups, array('id', 'name'), 'item_group'); ?></div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label"
                                           for="alert_quantity"><?php echo 'Alert Quantity'; ?></label>
                                    <div class="input-group">
                                        <input type="text" id="alert_quantity" name="alert_quantity"
                                               class="form-control" value="">
                                        <span class="input-group-addon">
                                            <input type="checkbox" name="track_quantity" id="track_quantity" checked="checked"
                                                   value="1" />
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                            <input type="checkbox" name="manufacturing_item" id="manufacturing_item" />
                                        </span>
                                            <input type="text" readonly class="form-control" placeholder="Manufacturing Item">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="warehouse_quantity" id="warehouse_quantity">
                            <label><?= _l("Warehouse Quantity") ?></label><br>
                            <?php
                            if (!empty($warehouses)) {
                                if ($product) {
                                    echo '<div class="well"><div id="show_wh_edit">';
                                    if (!empty($warehouses_products)) {
                                        echo '<div style="display:none;">';
                                        foreach ($warehouses_products as $wh_pr) {
                                            echo '<span class="bold text-info">' . $wh_pr->name . ': <span class="padding05" id="rwh_qty_' . $wh_pr->id . '">' . $this->sma->formatQuantity($wh_pr->quantity) . '</span>' . ($wh_pr->rack ? ' (<span class="padding05" id="rrack_' . $wh_pr->id . '">' . $wh_pr->rack . '</span>)' : '') . '</span><br>';
                                        }
                                        echo '</div>';
                                    }
                                    if (count($warehouses) > 0){
                                        foreach ($warehouses as $warehouse) {
                                            //$whs[$warehouse->id] = $warehouse->name;
                                            echo '<div class="col-md-6" style="padding-bottom:15px;">' . $warehouse->name . '<br><div class="form-group">' . form_hidden('wh_' . $warehouse->id, $warehouse->id) . form_input('wh_qty_' . $warehouse->id, (isset($_POST['wh_qty_' . $warehouse->id]) ? $_POST['wh_qty_' . $warehouse->id] : (isset($warehouse->quantity) ? $warehouse->quantity : '')), 'class="form-control wh" id="wh_qty_' . $warehouse->id . '" placeholder="' . _l('quantity') . '"') . '</div>';
                                            echo '</div>';
                                        }
                                    }else{
                                        echo '<div class="col-md-12" style="padding-bottom:15px;">
                                        No Warehouse
                                        </div>';
                                    }
                                    echo '</div><div class="clearfix"></div></div>';
                                } else {
                                    echo '<div class="well">';
                                    foreach ($warehouses as $warehouse) {
                                        //$whs[$warehouse->id] = $warehouse->name;

                                        echo '<div class="col-md-6" >' . $warehouse->name . '<br><div class="form-group">' . form_hidden('wh_' . $warehouse->id, $warehouse->id) . form_input('wh_qty_' . $warehouse->id, (isset($_POST['wh_qty_' . $warehouse->id]) ? $_POST['wh_qty_' . $warehouse->id] : ''), 'class="form-control" id="wh_qty_' . $warehouse->id . '" placeholder="' . _l('Quantity') . '"') . '</div>';
                                        //                                        if ($Settings->racks) {
//                                            echo '<div class="form-group">' . form_input('rack_' . $warehouse->id, (isset($_POST['rack_' . $warehouse->id]) ? $_POST['rack_' . $warehouse->id] : ''), 'class="form-control" id="rack_' . $warehouse->id . '" placeholder="' . _l('rack') . '"') . '</div>';
//                                        }
                                        echo '</div>';
                                    }
                                    echo '<div class="clearfix"></div></div>';
                                }
                            }else{
                                echo '<div class="well" style="padding-bottom: 30px;color: #ff0000;"><div id="show_wh_edit">';
                                echo '<div class="col-md-12 text-center" style="padding-bottom:15px;">
                                        <b>No Warehouse</b>
                                        </div>';
                                echo '</div></div>';
                            }
                            ?>
                        </div>

                        <div class="clearfix"></div>
                        <div class="form-group standard">
                            <div class="form-group">
                                <?= _l("Supplier") ?>
                                <button type="button" class="btn btn-primary btn-xs" id="addSupplier"><i
                                            class="fa fa-plus"></i>
                                </button>
                            </div>
                            <div class="row" id="supplier-con">
                                <div class="col-xs-12">
                                    <div class="form-group">
                                        <select id="supplier" name="supplier" class="selectpicker ajax-search" data-width="100%" data-none-selected-text="Nothing selected" data-live-search="true"></select>
                                    </div>
                                </div>
                                <!--<div class="col-xs-6">
                                    <div class="form-group">
                                        <?/*= form_input('supplier_part_no', (isset($_POST['supplier_part_no']) ? $_POST['supplier_part_no'] : ""), 'class="form-control tip" id="supplier_part_no" placeholder="' . _l('Supplier Part No') . '"'); */?>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="form-group">
                                        <?/*= form_input('supplier_price', (isset($_POST['supplier_price']) ? $_POST['supplier_price'] : ""), 'class="form-control tip" id="supplier_price" placeholder="' . _l('Supplier Price') . '"'); */?>
                                    </div>
                                </div>-->
                            </div>
                            <div id="ex-suppliers"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="control-label" for="tax"><?php echo _l('item_category'); ?></label>
                                    <select class="selectpicker display-block" data-width="100%" name="category"
                                            data-none-selected-text="<?php echo _l('item_category'); ?>">
                                        <option value=""></option>
                                        <option value="Slow Moving" data-subtext="Slow Moving">Slow Moving</option>
                                        <option value="Fast Moving" data-subtext="Fast Moving">Fast Moving</option>
                                        <option value="Non Moving" data-subtext="Non Moving">Non Moving</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <?php echo render_textarea('note', 'Note'); ?>
                            </div>
                        </div>
                        <div id="custom_fields_items">
                            <?php echo render_custom_fields('items'); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                <?php echo form_close(); ?>
            </div>
        </div>
    </div>
</div>
<script>
    // Maybe in modal? Eq convert to invoice or convert proposal to estimate/invoice
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

    // Items add/edit
    function manage_invoice_items(form) {
        var data = $(form).serialize();

        var url = form.action;
        $.post(url, data).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                var item_select = $('#item_select');
                if ($("body").find('.accounting-template').length > 0) {
                    if (!item_select.hasClass('ajax-search')) {
                        var group = item_select.find('[data-group-id="' + response.item.group_id + '"]');
                        if (group.length == 0) {
                            var _option = '<optgroup label="' + (response.item.group_name == null ? '' : response.item.group_name) + '" data-group-id="' + response.item.group_id + '">' + _option + '</optgroup>';
                            if (item_select.find('[data-group-id="0"]').length == 0) {
                                item_select.find('option:first-child').after(_option);
                            } else {
                                item_select.find('[data-group-id="0"]').after(_option);
                            }
                        } else {
                            group.prepend('<option data-subtext="' + response.item.long_description + '" value="' + response.item.itemid + '">(' + accounting.formatNumber(response.item.rate) + ') ' + response.item.description + '</option>');
                        }
                    }
                    if (!item_select.hasClass('ajax-search')) {
                        item_select.selectpicker('refresh');
                    } else {

                        item_select.contents().filter(function () {
                            return !$(this).is('.newitem') && !$(this).is('.newitem-divider');
                        }).remove();

                        var clonedItemsAjaxSearchSelect = item_select.clone();
                        item_select.selectpicker('destroy').remove();
                        $("body").find('.items-select-wrapper').append(clonedItemsAjaxSearchSelect);
                        init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');
                    }

                    add_item_to_preview(response.item.itemid);
                } else {
                    // Is general items view
                    $('.table-invoice-items').DataTable().ajax.reload(null, false);
                }
                alert_float('success', response.message);
            }
            $('#sales_item_modal').modal('hide');
        }).fail(function (data) {
            alert_float('danger', data.responseText);
        });
        return false;
    }

    var su = 2;

    function init_item_js() {
        // Add item to preview from the dropdown for invoices estimates
        $("body").on('change', 'select[name="item_select"]', function () {
            var itemid = $(this).selectpicker('val');
            if (itemid != '') {
                add_item_to_preview(itemid);
            }
        });

        // Items modal show action
        $("body").on('show.bs.modal', '#sales_item_modal', function (event) {

            $('.affect-warning').addClass('hide');

            var $itemModal = $('#sales_item_modal');
            $('input[name="itemid"]').val('');
            $itemModal.find('input').not('input[type="hidden"]').val('');
            $itemModal.find('textarea').val('');
            $itemModal.find('select').selectpicker('val', '').selectpicker('refresh');
            $('select[name="tax2"]').selectpicker('val', '').change();
            $('select[name="tax"]').selectpicker('val', '').change();
            $('select[name="barcode_symbology"]').selectpicker('val', 'code128').change();
            $itemModal.find('.add-title').removeClass('hide');
            $itemModal.find('.edit-title').addClass('hide');
            $('input[name="track_quantity"]').val('1');
            var id = $(event.relatedTarget).data('id');
            $("#warehouse_quantity").removeClass('hidden');
            // If id found get the text from the datatable
            if (typeof (id) !== 'undefined') {
                $("#warehouse_quantity").addClass('hidden');
                $('.affect-warning').removeClass('hide');
                $('input[name="itemid"]').val(id);

                requestGetJSON('invoice_items/get_item_by_id/' + id).done(function (response) {
                    $itemModal.find('input[name="description"]').val(response.description);
                    $itemModal.find('textarea[name="long_description"]').val(response.long_description.replace(/(<|<)br\s*\/*(>|>)/g, " "));
                    $itemModal.find('input[name="rate"]').val(response.rate);
                    $itemModal.find('input[name="product_cost"]').val(response.product_cost);
                    $itemModal.find('input[name="margin"]').val(response.margin);
                    $itemModal.find('input[name="warranty"]').val(response.warranty);
                    $itemModal.find('input[name="upto"]').val(response.upto);
                    $itemModal.find('input[name="rack_no"]').val(response.rack_no);
                    $itemModal.find('textarea[name="note"]').val(response.note);
                    /*$itemModal.find('input[name="unit"]').val(response.unit);
                    $itemModal.find('input[name="purchase_unit"]').val(response.purchase_unit);*/
                    $itemModal.find('input[name="alert_quantity"]').val(response.alert_quantity);
                    $('select[name="tax"]').selectpicker('val', response.taxid).change();

                    $('select[name="unit"]').selectpicker('val', response.unit_id).change();
                    $('select[name="purchase_unit"]').selectpicker('val', response.purchase_unit_id).change();
                    $('select[name="barcode_symbology"]').selectpicker('val', response.barcode_symbology).change();
                    $('select[name="group_id"]').selectpicker('val', response.group_id).change();
                    $('select[name="brand"]').selectpicker('val', response.brand).change();
                    // $('select[name="tax2"]').selectpicker('val', response.taxid_2).change();
                    if(response.track_quantity){
                        $('input[name="track_quantity"]').prop('checked', true);
                    }else {
                        $('input[name="track_quantity"]').prop('checked', false);
                    }
                    if(response.manufacturing_item == 'true'){
                        $('input[name="manufacturing_item"]').prop('checked', true);
                    }else {
                        $('input[name="manufacturing_item"]').prop('checked', false);
                    }
                    $('select[name="category"]').selectpicker('val', response.category).change();
                    if(response.supplier1 !== null && response.supplier1 !== '' && response.supplier1 !== '0') {
                        // $itemModal.find('input[name="supplier_part_no"]').val(response.supplier1_part_no);
                        // $itemModal.find('input[name="supplier_price"]').val(response.supplier1price);
                        $itemModal.find('select[name="supplier"]').append('<option value="' + response.supplier1 + '" selected="selected">' + response.supplier1_name + '</option>');
                        $('select[name="supplier"]').selectpicker('val', response.supplier1).change();
                    }

                    if(response.supplier2 !== null && response.supplier2 !== '' && response.supplier2 !== '0'){
                        var html = '<div style="clear:both;height:5px;"></div><div class="row"><div class="col-xs-12"><div class="form-group"><select id="supplier_2" name="supplier_2" class="selectpicker ajax-search" data-width="100%" data-none-selected-text="Nothing selected" data-live-search="true"></select></div></div></div>';
                        $('#ex-suppliers').append(html);
                        $itemModal.find('select[name="supplier_2"]').append('<option value="'+response.supplier2+'" selected="selected">'+response.supplier2_name+'</option>');
                        // $itemModal.find('input[name="supplier_2_part_no"]').val(response.supplier2_part_no);
                        // $itemModal.find('input[name="supplier_2_price"]').val(response.supplier2price);
                        $('select[name="supplier_2"]').selectpicker('val', response.supplier2).change();
                    }

                    if(response.supplier3 !== null && response.supplier3 !== '' && response.supplier3 !== '0'){
                        var html = '<div style="clear:both;height:5px;"></div><div class="row"><div class="col-xs-12"><div class="form-group"><select id="supplier_3" name="supplier_3" class="selectpicker ajax-search" data-width="100%" data-none-selected-text="Nothing selected" data-live-search="true"></select></div></div></div>';
                        $('#ex-suppliers').append(html);
                        $itemModal.find('select[name="supplier_3"]').append('<option value="'+response.supplier3+'" selected="selected">'+response.supplier3_name+'</option>');
                        // $itemModal.find('input[name="supplier_3_part_no"]').val(response.supplier3_part_no);
                        // $itemModal.find('input[name="supplier_3_price"]').val(response.supplier3price);
                        $('select[name="supplier_3"]').selectpicker('val', response.supplier3).change();
                    }

                    if(response.supplier4 !== null && response.supplier4 !== '' && response.supplier4 !== '0'){
                        var html = '<div style="clear:both;height:5px;"></div><div class="row"><div class="col-xs-12"><div class="form-group"><select id="supplier_4" name="supplier_4" class="selectpicker ajax-search" data-width="100%" data-none-selected-text="Nothing selected" data-live-search="true"></select></div></div></div>';
                        $('#ex-suppliers').append(html);
                        $itemModal.find('select[name="supplier_4"]').append('<option value="'+response.supplier4+'" selected="selected">'+response.supplier4_name+'</option>');
                        // $itemModal.find('input[name="supplier_4_part_no"]').val(response.supplier4_part_no);
                        // $itemModal.find('input[name="supplier_4_price"]').val(response.supplier4price);
                        $('select[name="supplier_4"]').selectpicker('val', response.supplier4).change();
                    }

                    if(response.supplier5 !== null && response.supplier5 !== '' && response.supplier5 !== '0'){
                        var html = '<div style="clear:both;height:5px;"></div><div class="row"><div class="col-xs-12"><div class="form-group"><select id="supplier_5" name="supplier_5" class="selectpicker ajax-search" data-width="100%" data-none-selected-text="Nothing selected" data-live-search="true"></select></div></div></div>';
                        $('#ex-suppliers').append(html);
                        $itemModal.find('select[name="supplier_5"]').append('<option value="'+response.supplier5+'" selected="selected">'+response.supplier5_name+'</option>');
                        // $itemModal.find('input[name="supplier_5_part_no"]').val(response.supplier5_part_no);
                        // $itemModal.find('input[name="supplier_5_price"]').val(response.supplier5price);
                        $('select[name="supplier_5"]').selectpicker('val', response.supplier5).change();
                    }

                    $itemModal.find('#group_id').selectpicker('val', response.group_id);
                    $("#warehouse_quantity").addClass('hidden');
                    $.each(response, function (column, value) {
                        if (column.indexOf('rate_currency_') > -1) {
                            $itemModal.find('input[name="' + column + '"]').val(value);
                        }
                    });

                    $('#custom_fields_items').html(response.custom_fields_html);

                    init_selectpicker();
                    init_color_pickers();
                    init_datepicker();

                    $itemModal.find('.add-title').addClass('hide');
                    $itemModal.find('.edit-title').removeClass('hide');
                    validate_item_form();
                });

            }
        });

        $("body").on("hidden.bs.modal", '#sales_item_modal', function (event) {
            $('#item_select').selectpicker('val', '');
        });
        $('select[name="unit"]').on("change",function(){
            $('select[name="purchase_unit"]').selectpicker('val', $('select[name="unit"]').val()).change();
        })

        $('#addSupplier').click(function () {
            if (su <= 5) {
                var html = '<div class="row" id="main_row_' + su + '" style="margin-top:10px;"><div class="col-xs-10"><div class="form-group"><select id="supplier_' + su + '" name="supplier_' + su + '" class="selectpicker ajax-search" data-width="100%" data-none-selected-text="Nothing selected" data-live-search="true"></select></div></div><div class="col-xs-2"><div class="form-group"><button type="button" class="btn btn-danger btn-xs" onclick="removeItem(this); return false" data-id='+ su +'><i class="fa fa-minus"></i></button></div></div></div>';
                $('#ex-suppliers').append(html);
                var sup = $('#supplier_' + su);
                suppliers(sup);
                su++;
            } else {
                alert_float('warning','<?= _l('Max allowed limit reached.') ?>');
                return false;
            }
        });
        init_selectpicker();
        init_ajax_search('suppliers', '#supplier.ajax-search', undefined, admin_url + 'suppliers/search');
        validate_item_form();
    }


    function removeItem(a) {
        let id=$(a).data('id');
        $('#main_row_'+id).remove();
    }

    function validate_item_form() {
        // Set validation for invoice item form
        _validate_form($('#invoice_item_form'), {
            description: 'required',
            barcode_symbology: 'required',
            rate: {
                required: true,
            },
            product_cost: {
                required: true,
            },
            group_id: {
                required: true,
            }
        }, manage_invoice_items), $("body").find('input[name="description"]').rules("add", {
            remote: {
                url: admin_url + "invoice_items/validate_item_exist",
                type: "post",
                data: {
                    description: function () {
                        return $('input[name="description"]').val()
                    },
                    isedit: function () {
                        return $('input[name="itemid"]').val()
                    },
                }
            },
            messages: {
                remote: "Part/TPL/Model already exists."
            }
        });
    }

    function suppliers(ele) {
        init_ajax_search('customer', ele, undefined, admin_url + 'suppliers/search');
    }

    function calculate_rate(a) {
        let productCost = $("#product_cost").val();
        let margin = $("#margin");
        let rate = $("#rate");
        if(a){
            let rateVal = parseFloat(productCost) + ((parseFloat(productCost) * parseInt(margin.val())) / 100);
            rate.val(rateVal);
        }else {
            let marginPer = (parseFloat(rate.val()) - parseFloat(productCost)) * (100 / parseFloat(productCost));
            margin.val(marginPer);
        }
    }
</script>
