<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <!--<div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4>Purchase Report</h4>
                    </div>
                    <div class="panel-body">
                        <div class="clearfix"></div>
                        <?php /*render_datatable(array(
                            _l('Code'),
                            _l('Purchase no'),
                            _l('Items purchase date'),
                            _l('Status'),
                            _l('Order Qty'),
                            _l('Quantity Get'),
                        ), 'item_purchase_report'); */?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4>Invoice Item Report</h4>
                    </div>
                    <div class="panel-body">
                        <div class="clearfix"></div>
                        <?php /*render_datatable(array(
                            _l('Code'),
                            _l('Invoice No'),
                            _l('Invoice Date'),
                            _l('Status'),
                            _l('Qty'),
                        ), 'item_sale_report'); */?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4>DC Item Report</h4>
                    </div>
                    <div class="panel-body">
                        <div class="clearfix"></div>
                        <?php /*render_datatable(array(
                            _l('Code'),
                            _l('DC No'),
                            _l('DC Date'),
                            _l('Status'),
                            _l('Qty'),
                        ), 'item_dc_report'); */?>
                    </div>
                </div>
            </div>
        </div>-->
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4>Stock Transaction Report</h4>
                    </div>
                    <div class="panel-body">
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('Code'),
                            _l('Purchase/Invoice/DC'),
                            _l('Date'),
                            _l('Type'),
                            _l('Qty'),
                        ), 'item_stock_transaction_report'); ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-heading">
                        <h4>Adjustment Report</h4>
                    </div>
                    <div class="panel-body">
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('Code'),
                            _l('Adjustment Ref No'),
                            _l('Adjustment Date'),
                            _l('Adjustment Mode(add/substract)'),
                            _l('Qty'),
                        ), 'item_adjustment_report'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function () {
        // initDataTable('.table-item_purchase_report', window.location.href+'?type=purchase', [], []);
        initDataTable('.table-item_stock_transaction_report', window.location.href+'?type=stock_transaction', [], []);
        initDataTable('.table-item_adjustment_report', window.location.href+'?type=adjustment', [], []);
        // initDataTable('.table-item_sale_report', window.location.href+'?type=sale', [], []);
        // initDataTable('.table-item_dc_report', window.location.href+'?type=dc', [], []);
        _validate_form($('form'), {
            code: 'required', name: 'required', address : 'required'
        }, manage_departments);
        $('#department').on('hidden.bs.modal', function (event) {
            $('#additional').html('');
            $('#department input[type="text"]').val('');
            $('#department input[type="email"]').val('');
            $('input[name="delete_after_import"]').prop('checked', false);
            $('.add-title').removeClass('hide');
            $('.edit-title').removeClass('hide');
        });
    });

    function manage_departments(form) {
        var data = $(form).serialize();
        var url = form.action;
        $.post(url, data).done(function (response) {
            response = JSON.parse(response);
            if (response.success == true) {
                alert_float('success', response.message);
            }
            $('.table-warehouses').DataTable().ajax.reload();
            $('#department').modal('hide');
        }).fail(function (data) {
            var error = JSON.parse(data.responseText);
            alert_float('danger', error.message);
        });
        return false;
    }

    function new_department() {
        $('#department').modal('show');
        $('.edit-title').addClass('hide');
    }

    function edit_department(invoker, id) {
        $('#additional').append(hidden_input('id', id));
        $('#department input[name="name"]').val($(invoker).data('name'));
        $('#department input[name="code"]').val($(invoker).data('code'));
        $('#department input[name="phone"]').val($(invoker).data('phone'));
        $('#department input[name="email"]').val($(invoker).data('email'));
        $('#department textarea[name="address"]').val($(invoker).data('address'));
        $('#department').modal('show');
        $('.add-title').addClass('hide');
    }
</script>
</body>
</html>
