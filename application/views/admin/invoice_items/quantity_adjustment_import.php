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
                                        <?php echo form_open_multipart($this->uri->uri_string(), array('id' => 'barcode-print-form', 'autocomplete' => 'off')); ?>
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
                                        <div class="col-md-12">
                                            <?php echo render_input('csv_file','choose_csv_file','','file'); ?>
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
