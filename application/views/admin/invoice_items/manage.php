<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php $this->load->view('admin/invoice_items/top_stats'); ?>
                        <?php do_action('before_items_page_content'); ?>
                        <?php if (has_permission('items', '', 'create')) { ?>
                            <div class="_buttons">
                                <a href="#" class="btn btn-info pull-left" data-toggle="modal"
                                   data-target="#sales_item_modal"><?php echo _l('new_invoice_item'); ?></a>
                                <a href="#" class="btn btn-info pull-left mleft5" data-toggle="modal"
                                   data-target="#groups"><?php echo _l('item_groups'); ?></a>
                                <a href="<?php echo admin_url('invoice_items/import'); ?>"
                                   class="btn btn-info pull-left mleft5"><?php echo _l('import'); ?></a>
                                <a href="<?php echo admin_url('invoice_items/import_price'); ?>"
                                   class="btn btn-info pull-left mleft5"><?php echo _l('Import Product Price'); ?></a>
                                <a href="#" class="btn btn-info pull-left mleft5" data-toggle="modal"
                                   data-backdrop="static" data-keyboard="false"
                                   data-target="#brands"><?php echo 'BRANDS'; ?></a>
                                <a href="#" class="btn btn-info pull-left mleft5" data-toggle="modal"
                                   data-backdrop="static" data-keyboard="false"
                                   data-target="#units"><?php echo 'UNITS'; ?></a>
                                <div class="display-block text-right">
                                    <a href="#" class="btn btn-default btn-with-tooltip invoices-total"
                                       onclick="slideToggle('#stats-top'); init_invoices_total(true); return false;"
                                       data-toggle="tooltip" title="<?php echo _l('view_stats_tooltip'); ?>"><i
                                                class="fa fa-bar-chart"></i></a>
                                </div>
                            </div>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-heading"/>
                        <?php } ?>
                        <a href="#" data-toggle="modal" data-target="#tasks_bulk_actions"
                           class="hide bulk-actions-btn table-btn"
                           data-table=".table-invoice-items"><?php echo _l('Request For Rate'); ?></a>
                        <div class="checkbox">
                            <input type="checkbox"  id="exclude_inactive" value="1" name="exclude_inactive">
                            <label for="exclude_inactive">Exclude 0 Qty items</label>
                        </div>
                        <div class="clearfix mtop20"></div>
                        <?php
                        $table_data = array(
                            _l('invoice_items_list_description'),
                            _l('invoice_item_add_edit_description'),
                            _l('Brand'),
                            _l('item_category'),
                            _l('invoice_items_list_rate'),
                            _l('tax_1'),
                            _l('unit'),
                            _l('item_rack_no'),
                            _l('item_note'),
                            _l('Avl Qty'),
                            _l('Hold Qty'),
                            _l('Indent Qty'),
                            _l('Alert Qty'),
                            _l('item_group_name'),
                        );
                        //            _l('Valid upto'),
                        //            _l('RFR Status'));
                        /*$cf = get_custom_fields('items');
                        foreach($cf as $custom_field) {
                            array_push($table_data,$custom_field['name']);
                        }*/
                        array_unshift($table_data, [
                            'name' => '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="invoice-items"><label></label></div>',
//              'th_attrs' => ['class' => (isset($bulk_actions) ? '' : 'not_visible')],
                        ]);
                        render_datatable($table_data, 'invoice-items'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/invoice_items/item'); ?>
<div class="modal fade" id="groups" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <?php echo _l('item_groups'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <?php if (has_permission('items', '', 'create')) { ?>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_input('item_group_name', 'item_group_name', ''); ?>
                        </div>
                        <div class="col-md-6"><?php echo render_select('item_group_parent__id', $items_parent_groups, array('id', 'name'), 'item_group'); ?></div>
                        <div class="col-md-12">
                            <button class="btn btn-info p7" type="button"
                                    id="new-item-group-insert"><?php echo _l('new_item_group'); ?></button>
                        </div>
                    </div>

                    <hr/>
                <?php } ?>
                <div class="row">
                    <div class="container-fluid">
                        <table class="table dt-table table-items-groups" data-order-col="0" data-order-type="asc">
                            <thead>
                            <tr>
                                <th><?php echo _l('ID'); ?></th>
                                <th><?php echo _l('item_group_name'); ?></th>
                                <th><?php echo _l('Related to'); ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($items_groups as $group) { ?>
                                <tr class="row-has-options" data-group-row-id="<?php echo $group['id']; ?>">
                                    <td data-order="<?php echo $group['id']; ?>"><?php echo $group['id']; ?></td>
                                    <td data-order="<?php echo $group['name']; ?>">
                                        <span class="group_name_plain_text"><?php echo $group['name']; ?></span>
                                        <div class="group_edit hide">
                                            <div class="input-group">
                                                <input type="text" class="form-control">
                                                <span class="input-group-btn">
                      <button class="btn btn-info p8 update-item-group"
                              type="button"><?php echo _l('submit'); ?></button>
                    </span>
                                            </div>
                                        </div>
                                        <div class="row-options">
                                            <?php if (has_permission('items', '', 'edit')) { ?>
                                                <a href="#" class="edit-item-group">
                                                    <?php echo _l('edit'); ?>
                                                </a>
                                            <?php } ?>
                                            <?php if (has_permission('items', '', 'delete')) { ?>
                                                |
                                                <a href="<?php echo admin_url('invoice_items/delete_group/' . $group['id']); ?>"
                                                   class="delete-item-group _delete text-danger">
                                                    <?php echo _l('delete'); ?>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td data-order="<?php echo $group['parent_name']; ?>"><?php echo $group['parent_name']; ?></td>
                                </tr>
                            <?php } ?>
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

<div class="modal fade bulk_actions" id="tasks_bulk_actions" tabindex="-1" role="dialog"
     data-table="<?php echo(isset($table) ? $table : '.table-invoice-items'); ?>">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open_multipart('admin/invoice_items/rfr_request', ['id' => 'rfr-form']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
            </div>
            <div class="modal-body">
                <div id="bulk_change">
                    <input type="hidden" name="ids">
                    <?php echo render_input('mailto', 'Mail To'); ?>
                    <?php echo render_textarea('message', 'Message', '', array('rows' => 6, 'placeholder' => _l('Message'), 'data-task-ae-editor' => true, !is_mobile() ? 'onclick' : 'onfocus' => (!isset($task) || isset($task) && $task->description == '' ? 'init_editor(\'.tinymce-task\', {height:200, auto_focus: true});' : '')), array(), 'no-mbot', 'tinymce-task'); ?>
                    <div class="form-group">
                        <label for="attachment" class="control-label">Attachment</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('confirm'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<div class="modal fade" id="brands" tabindex="-1" role="dialog" aria-labelledby="myModalLabelbrand">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabelbrand">
                    <?php echo 'BRANDS'; ?>
                </h4>
            </div>
            <div class="modal-body">
                <?php if (has_permission('items', '', 'create')) { ?>
                    <div class="input-group">
                        <input type="text" name="item_brand_name" id="item_brand_name" class="form-control"
                               placeholder="<?php echo 'BRAND'; ?>">
                        <span class="input-group-btn">
                            <button class="btn btn-info p7" type="button"
                                    id="new-item-brand-insert"><?php echo 'NEW BRAND'; ?></button>
                        </span>
                    </div>
                    <hr/>
                <?php } ?>
                <div class="row">
                    <div class="container-fluid">
                        <table class="table dt-table table-items-brands" data-order-col="0" data-order-type="asc">
                            <thead>
                            <tr>
                                <th><?php echo 'ID'; ?></th>
                                <th><?php echo 'NAME'; ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($items_brands as $brand) { ?>
                                <tr class="row-has-options" data-brand-row-id="<?php echo $brand['id']; ?>">
                                    <td data-order="<?php echo $brand['id']; ?>"><?php echo $brand['id']; ?></td>
                                    <td data-order="<?php echo $brand['name']; ?>">
                                        <span class="brand_name_plain_text"><?php echo $brand['name']; ?></span>
                                        <div class="brand_edit hide">
                                            <div class="input-group">
                                                <input type="text" class="form-control">
                                                <span class="input-group-btn">
                                                  <button class="btn btn-info p8 update-item-brand"
                                                          type="button"><?php echo _l('submit'); ?></button>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="row-options">
                                            <?php if (has_permission('items', '', 'edit')) { ?>
                                                <a href="#" class="edit-item-brand">
                                                    <?php echo _l('edit'); ?>
                                                </a>
                                            <?php } ?>
                                            <?php if (has_permission('items', '', 'delete')) { ?>
                                                |
                                                <a href="<?php echo admin_url('invoice_items/delete_brand/' . $brand['id']); ?>"
                                                   class="delete-item-brand _delete text-danger">
                                                    <?php echo _l('delete'); ?>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </td>

                                </tr>
                            <?php } ?>
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
<div class="modal fade" id="units" tabindex="-1" role="dialog" aria-labelledby="myModalLabelunit">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabelunit">
                    <?php echo 'UNIT'; ?>
                </h4>
            </div>
            <div class="modal-body">
                <?php if (has_permission('items', '', 'create')) { ?>
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_input('item_unit_name', 'Unit Name', ''); ?>
                        </div>
                        <div class="col-md-6"><?php echo render_select('item_unit_base_unit', $items_units, array('id', 'name'), 'Unit'); ?></div>
                        <div id="measuring"><!--style="display:none;"-->
                            <div class="col-md-6">
                                <?php
                                $oopts = array('*', '/', '+', '-');
                                ?>
                                <?php echo render_select('item_unit_operator', $oopts, [], 'Operator'); ?>
                            </div>

                            <div class="col-md-6">
                                <?php echo render_input('item_unit_operation_value', 'Operation Value', ''); ?>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <button class="btn btn-info p7" type="button"
                                    id="new-item-unit-insert"><?php echo _l('NEW UNIT'); ?></button>
                        </div>
                    </div>
                    <hr/>
                <?php } ?>
                <div class="row">
                    <div class="container-fluid">
                        <table class="table dt-table table-items-units" data-order-col="0" data-order-type="asc">
                            <thead>
                            <tr>
                                <th><?php echo 'ID'; ?></th>
                                <th><?php echo 'NAME'; ?></th>
                                <th><?php echo 'Base Unit'; ?></th>
                                <th><?php echo 'Operator'; ?></th>
                                <th><?php echo 'Operation Value'; ?></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($items_units as $brand) { ?>
                                <tr class="row-has-options" data-unit-row-id="<?php echo $brand['id']; ?>">
                                    <td data-order="<?php echo $brand['id']; ?>"><?php echo $brand['id']; ?></td>
                                    <td data-order="<?php echo $brand['name']; ?>">
                                        <span class="unit_name_plain_text"><?php echo $brand['name']; ?></span>
                                        <div class="unit_edit hide">
                                            <div class="input-group">
                                                <input type="text" class="form-control">
                                                <span class="input-group-btn">
                                                  <button class="btn btn-info p8 update-item-unit"
                                                          type="button"><?php echo _l('submit'); ?></button>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="row-options">
                                            <?php if (has_permission('items', '', 'edit')) { ?>
                                                <a href="#" class="edit-item-unit">
                                                    <?php echo _l('edit'); ?>
                                                </a>
                                            <?php } ?>
                                            <?php if (has_permission('items', '', 'delete')) { ?>
                                                |
                                                <a href="<?php echo admin_url('invoice_items/delete_unit/' . $brand['id']); ?>"
                                                   class="delete-item-unit _delete text-danger">
                                                    <?php echo _l('delete'); ?>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </td>
                                    <td data-order="<?php echo $brand['base_unit_name']; ?>"><?php echo $brand['base_unit_name']; ?></td>
                                    <td data-order="<?php echo $brand['operator']; ?>"><?php echo $brand['operator']; ?></td>
                                    <td data-order="<?php echo $brand['operation_value']; ?>"><?php echo $brand['operation_value']; ?></td>
                                </tr>
                            <?php } ?>
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
<div class="modal fade" id="itemWareHouseModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabelunit">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabelunit"> Warehouse Quantity
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="container-fluid">
                        <table class="table dt-table table-warehouse-items" data-order-col="0" data-order-type="asc">
                            <thead>
                            <tr>
                                <th><?php echo 'Warehouse Name'; ?></th>
                                <th><?php echo 'Quantity'; ?></th>
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
<div class="modal fade" id="itemIndentModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabelunit">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabelunit"> Indent Quantity
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="container-fluid">
                        <table class="table dt-table table-indent-items" data-order-col="0" data-order-type="asc">
                            <thead>
                            <tr>
                                <th><?php echo 'Purchase No.'; ?></th>
                                <th><?php echo 'Quantity'; ?></th>
                                <th><?php echo 'Purchase Date'; ?></th>
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
<div class="modal fade" id="holdItemModel" tabindex="-1" role="dialog" aria-labelledby="myModalLabelunit">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabelunit"> Hold Items
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="container-fluid">
                        <table class="table dt-table table-hold-items" data-order-col="0" data-order-type="asc">
                            <thead>
                            <tr>
                                <th><?php echo 'Purchase Order / Invoice No.'; ?></th>
                                <th><?php echo 'Quantity'; ?></th>
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
<?php init_tail(); ?>
<script>
    $(function () {
        var CustomersServerParams = {
            'exclude_inactive': '[name="exclude_inactive"]:checked'
        };
        var tAPI = initDataTable('.table-invoice-items', admin_url + 'invoice_items/table', [0], [0], CustomersServerParams, [1, 'asc']);
        $('input[name="exclude_inactive"]').on('change', function () {
            tAPI.ajax.reload();
        });
        if (get_url_param('groups_modal')) {
            // Set time out user to see the message
            setTimeout(function () {
                $('#groups').modal('show');
            }, 1000);
        }
        if (get_url_param('brands_modal')) {
            // Set time out user to see the message
            setTimeout(function () {
                $('#brands').modal('show');
            }, 1000);
        }
        if (get_url_param('units_modal')) {
            // Set time out user to see the message
            setTimeout(function () {
                $('#units').modal('show');
            }, 1000);
        }

        $('#new-item-group-insert').on('click', function () {
            var group_name = $('#item_group_name').val();
            var group_parent_id = $('#item_group_parent__id').val();
            if (group_name != '') {
                $.post(admin_url + 'invoice_items/add_group', {
                    name: group_name,
                    parent_id: group_parent_id
                }).done(function () {
                    window.location.href = admin_url + 'invoice_items?groups_modal=true';
                });
            }
        });

        $('#new-item-brand-insert').on('click', function () {
            var brand_name = $('#item_brand_name').val();
            if (brand_name != '') {
                $.post(admin_url + 'invoice_items/add_brand', {name: brand_name}).done(function () {
                    window.location.href = admin_url + 'invoice_items?brands_modal=true';
                });
            }
        });

        $('#new-item-unit-insert').on('click', function () {
            var item_unit_name = $('#item_unit_name').val();
            var item_unit_base_unit = $('#item_unit_base_unit').val();
            var item_unit_operator = $('#item_unit_operator').val();
            var item_unit_operation_value = $('#item_unit_operation_value').val();
            if (item_unit_name != '') {
                $.post(admin_url + 'invoice_items/add_unit', {
                    name: item_unit_name,
                    base_unit: item_unit_base_unit,
                    operator: item_unit_operator,
                    operation_value: item_unit_operation_value
                }).done(function () {
                    window.location.href = admin_url + 'invoice_items?units_modal=true';
                });
            }
        });

        $('body').on('click', '.edit-item-group', function (e) {
            e.preventDefault();
            var tr = $(this).parents('tr'),
                group_id = tr.attr('data-group-row-id');
            tr.find('.group_name_plain_text').toggleClass('hide');
            tr.find('.group_edit').toggleClass('hide');
            tr.find('.group_edit input').val(tr.find('.group_name_plain_text').text());
        });

        $('body').on('click', '.update-item-group', function () {
            var tr = $(this).parents('tr');
            var group_id = tr.attr('data-group-row-id');
            name = tr.find('.group_edit input').val();
            if (name != '') {
                $.post(admin_url + 'invoice_items/update_group/' + group_id, {name: name}).done(function () {
                    window.location.href = admin_url + 'invoice_items';
                });
            }
        });

        $('body').on('click', '.edit-item-brand', function (e) {
            e.preventDefault();
            var tr = $(this).parents('tr'),
                brand_id = tr.attr('data-brand-row-id');
            tr.find('.brand_name_plain_text').toggleClass('hide');
            tr.find('.brand_edit').toggleClass('hide');
            tr.find('.brand_edit input').val(tr.find('.brand_name_plain_text').text());
        });

        $('body').on('click', '.update-item-brand', function () {
            var tr = $(this).parents('tr');
            var brand_id = tr.attr('data-brand-row-id');
            name = tr.find('.brand_edit input').val();
            if (name != '') {
                $.post(admin_url + 'invoice_items/update_brand/' + brand_id, {name: name}).done(function () {
                    window.location.href = admin_url + 'invoice_items';
                });
            }
        });

        $('body').on('click', '.edit-item-unit', function (e) {
            e.preventDefault();
            var tr = $(this).parents('tr'),
                brand_id = tr.attr('data-unit-row-id');
            tr.find('.unit_name_plain_text').toggleClass('hide');
            tr.find('.unit_edit').toggleClass('hide');
            tr.find('.unit_edit input').val(tr.find('.unit_name_plain_text').text());
        });

        $('body').on('click', '.update-item-unit', function () {
            var tr = $(this).parents('tr');
            var brand_id = tr.attr('data-unit-row-id');
            name = tr.find('.unit_edit input').val();
            if (name != '') {
                $.post(admin_url + 'invoice_items/update_unit/' + brand_id, {name: name}).done(function () {
                    window.location.href = admin_url + 'invoice_items';
                });
            }
        });
        $(window).off('beforeunload');
    });

    var wareHouseTableUrl = '';

    function openWareHouseQuantityModal(itemId) {
        $('#itemWareHouseModel').modal('show');
        wareHouseTableUrl = admin_url + 'invoice_items/wareHouseTable?itemId=' + itemId;
    }

    $('#itemWareHouseModel').on('shown.bs.modal', function () {
        $(".table-warehouse-items").DataTable().destroy();
        initDataTable('.table-warehouse-items', wareHouseTableUrl, undefined, undefined, 'undefined', [0, 'asc']);
    });
    $('#itemWareHouseModel').on('hidden.bs.modal', function (e) {
        $(".table-warehouse-items").DataTable().destroy();
    });

    var indentTableUrl = '';

    function openIndentModal(itemId) {
        $('#itemIndentModel').modal('show');
        indentTableUrl = admin_url + 'invoice_items/indentTable?itemId=' + itemId;
    }

    $('#itemIndentModel').on('shown.bs.modal', function () {
        $(".table-indent-items").DataTable().destroy();
        initDataTable('.table-indent-items', indentTableUrl, undefined, undefined, 'undefined', [0, 'asc']);
    });
    $('#itemIndentModel').on('hidden.bs.modal', function (e) {
        $(".table-indent-items").DataTable().destroy();
    });
    var holdItemTableUrl = '';

    function openHoldItemModal(itemId) {
        $('#holdItemModel').modal('show');
        holdItemTableUrl = admin_url + 'invoice_items/holdItemTable?itemId=' + itemId;
    }

    $('#holdItemModel').on('shown.bs.modal', function () {
        $(".table-hold-items").DataTable().destroy();
        initDataTable('.table-hold-items', holdItemTableUrl, undefined, undefined, 'undefined', [0, 'asc']);
    });
    $('#holdItemModel').on('hidden.bs.modal', function (e) {
        $(".table-hold-items").DataTable().destroy();
    });
    _validate_form($('#rfr-form'), {
        mailto: {
            required: true,
            email: true
        }
    });
    $('#tasks_bulk_actions').on('shown.bs.modal', function (e) {
        var ids = [];
        var rows = $($('#tasks_bulk_actions').attr('data-table')).find('tbody tr');
        $.each(rows, function () {
            var checkbox = $($(this).find('td').eq(0)).find('input');
            if (checkbox.prop('checked') === true) {
                ids.push(checkbox.val());
            }
        });
        $('input[name="ids"]').val(ids);
    })
</script>
</body>
</html>
