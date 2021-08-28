<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="_filters _hidden_inputs hidden">
                    <input type="hidden" name="out_stock" value="1">
                </div>
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <div class="btn-group pull-right btn-with-tooltip-group _filter_data" data-toggle="tooltip" data-title="Filter by">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fa fa-filter" aria-hidden="true"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-left" style="width:300px;">
                                    <li ><a href="#" data-cview="all" onclick="dt_custom_view('','.table-warehouses',''); return false;">All</a>
                                    </li>
                                    <li class="divider"></li>
                                    <li  class="active">
                                        <a href="#" data-view="1" onclick="dt_custom_view('1','.table-warehouses','out_stock'); return false;">
                                            Stock Out remain</a>
                                    </li>
                                    <li>
                                        <a href="#" data-cview="2" onclick="dt_custom_view('2','.table-warehouses','out_stock'); return false;">
                                            Stock Out done</a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading">
                        <?php render_datatable(array(
                            _l('Invoice Number'),
                            _l('Date'),
                            _l('Actions')
                        ), 'warehouses'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function () {
        var Invoices_Estimates_ServerParams = {};
        var Invoices_Estimates_Filter = $('._hidden_inputs._filters input');

        $.each(Invoices_Estimates_Filter, function() {
            Invoices_Estimates_ServerParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
        });
        initDataTable('.table-warehouses', window.location.href, [2], [2],Invoices_Estimates_ServerParams);
    });
</script>
</body>
</html>
