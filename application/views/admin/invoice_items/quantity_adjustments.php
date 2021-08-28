<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if(has_permission('quantity_adjustments','','create')){ ?>
                        <div class="_buttons">
                            <a href="<?php echo admin_url('invoice_items/quantity_adjustment'); ?>" class="btn btn-info pull-left" ><?php echo _l('New quantity adjustment'); ?></a>
                        </div>
                            <a href="<?php echo admin_url('invoice_items/import_quantity_adjustment'); ?>" class="btn btn-info pull-left mleft5"><?php echo _l('import'); ?></a>
                        <?php } ?>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading" />
                        <?php echo form_open('admin/invoice_items/adjustment_actions', array('id' => 'action-form')); ?>
                            <div class="box-content">
                                <div class="row">
                                    <div class="col-lg-12">
                                        <?php render_datatable(array(
                                            _l('Date'),
                                            _l('reference_no'),
                                            _l('warehouse'),
                                            _l('note'),
                                            _l('actions'),
                                        ), 'quantity_adjustment'); ?>
                                        <!--<div class="table-responsive">
                                            <table id="dmpData" class="table table-bordered table-condensed table-hover table-striped">
                                                <thead>
                                                <tr>
                                                    <th style="min-width:30px; width: 30px; text-align: center;">
                                                        <input class="checkbox checkft" type="checkbox" name="check"/>
                                                    </th>
                                                    <th class="col-xs-2"><?/*= _l("date"); */?></th>
                                                    <th class="col-xs-2"><?/*= _l("reference_no"); */?></th>
                                                    <th class="col-xs-2"><?/*= _l("warehouse"); */?></th>
                                                    <th class="col-xs-2"><?/*= _l("created_by"); */?></th>
                                                    <th><?/*= _l("note"); */?></th>
                                                    <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                                                    </th>
                                                    <th style="min-width:75px; text-align:center;"><?/*= _l("actions"); */?></th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    <td colspan="8" class="dataTables_empty"><?/*= _l('loading_data_from_server') */?></td>
                                                </tr>
                                                </tbody>
                                                <tfoot class="dtFilter">
                                                <tr class="active">
                                                    <th style="min-width:30px; width: 30px; text-align: center;">
                                                        <input class="checkbox checkft" type="checkbox" name="check"/>
                                                    </th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th></th>
                                                    <th style="min-width:30px; width: 30px; text-align: center;"><i class="fa fa-chain"></i>
                                                    </th>
                                                    <th style="width:75px; text-align:center;"><?/*= _l("actions"); */?></th>
                                                </tr>
                                                </tfoot>
                                            </table>
                                        </div>-->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="display: none;">
                            <input type="hidden" name="form_action" value="" id="form_action"/>
                            <?= form_submit('performAction', 'performAction', 'id="action-form-submit"') ?>
                        </div>
                        <?= form_close() ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function () {
        initDataTable('.table-quantity_adjustment', window.location.href, [3], [3]);
    });
</script>