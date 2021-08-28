<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tab-content">
                            <h4 class="customer-profile-group-heading"><?= 'Stock Count'; ?></h4>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-part">
                                        <?php echo form_open($this->uri->uri_string(), array('id' => 'barcode-print-form')); ?>
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
                                            <div class="form-group">
                                                <label for="type"><?php echo _l('Type'); ?></label><br/>
                                                <div class="radio radio-primary radio-inline">
                                                    <input type="radio" name="type" class="type" value="full"
                                                           id="full" >
                                                    <label for="full">Full</label>
                                                </div>
                                                <div class="radio radio-primary radio-inline">
                                                    <input type="radio" name="type" class="type"  value="partial"
                                                           id="partial" >
                                                    <label for="partial">Partial</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12 partials" style="display:none;">
                                            <div class="well well-sm">
                                                <div class="col-md-6"><?php echo render_select('brand[]', $items_brands, array('id', 'name'), 'Brand','',['multiple'=>true], [], '', '', false); ?></div>
                                                <div class="col-md-6"><?php echo render_select('category[]', $items_groups, array('id', 'name'), 'item_group','',['multiple'=>true], [], '', '', false); ?></div>
                                                <div class="clearfix"></div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <?php echo form_submit('save', _l("Save"), 'class="btn btn-primary"'); ?>
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
    $(function () {
        _validate_form($('#barcode-print-form'), {warehouse: 'required', date: 'required'});
        $('.radio .type').change(function () {
            if(this.checked) {
                if (this.value == 'partial')
                    $('.partials').slideDown();
                else
                    $('.partials').slideUp();
            }
        });
    });
</script>
</body>
</html>
