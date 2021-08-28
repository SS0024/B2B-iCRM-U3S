<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('invoice_items/stock_count'); ?>" class="btn btn-info pull-left" ><?php echo _l('New Stock Count'); ?></a>
                        </div>
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
                                            _l('Type'),
                                            _l('Brands'),
                                            _l('Item Groups'),
                                            array('name'=>'<i class="fa fa-file-o"></i>','th_attrs'=>["class"=>'text-right']),
                                            array('name'=>'<i class="fa fa-chain"></i>','th_attrs'=>["class"=>'text-right']),
                                            _l('actions'),
                                        ), 'quantity_adjustment'); ?>
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