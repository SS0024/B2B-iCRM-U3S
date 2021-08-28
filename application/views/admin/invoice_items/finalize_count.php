<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tab-content">
                            <h4 class="customer-profile-group-heading"><?= 'Finalize Stock Count'; ?></h4>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="col-md-12">
                                        <div class="panel-body">
                                            <table class="table table-striped table-bordered" style="margin-bottom: 0;">
                                                <tbody>
                                                <tr>
                                                    <td><?= _l('Warehouse'); ?></td>
                                                    <td><?= $warehouse->name . ' ( ' . $warehouse->code . ' )'; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?= _l('Date'); ?></td>
                                                    <td><?= _dt($stock_count->date); ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?= _l('Reference'); ?></td>
                                                    <td><?= $stock_count->reference_no; ?></td>
                                                </tr>
                                                <tr>
                                                    <td><?= _l('Type'); ?></td>
                                                    <td><?= ucfirst($stock_count->type); ?></td>
                                                </tr>
                                                <?php if ($stock_count->type == 'partial') { ?>
                                                    <tr>
                                                        <td><?= _l('Categories'); ?></td>
                                                        <td><?= $stock_count->category_names; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?= _l('Brands'); ?></td>
                                                        <td><?= $stock_count->brand_names; ?></td>
                                                    </tr>
                                                <?php } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-md-12 mtop20">
                                        <div class="panel panel-default download_csv">
                                            <div class="panel-body">
                                                <a href="<?= base_url('uploads/count_stock/' . $stock_count->initial_file); ?>"
                                                   class="btn btn-primary pull-right">
                                                    <i class="fa fa-download"></i> <?= _l('Download CSV file of current stock'); ?>
                                                </a>
                                                <span class="text-success">
                                        <?= _l("The first line in downloaded csv file should remain as it is. Please do not change the order of columns."); ?></span><br/><?= _l("The correct column order is"); ?>
                                                <span class="text-info">
                                            (<?= _l("Product code") . ', ' . _l("Count"); ?>)
                                        </span>
                                                <?= _l("&amp; you must follow this.<br>Please make sure the csv file is UTF-8 encoded and not saved with byte order mark (BOM)."); ?>
                                                <br>
                                                <strong><?= _l('You just need to update the COUNT column in the downloaded csv file.'); ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-part">
                                        <?php echo form_open_multipart($this->uri->uri_string(), array('id' => 'barcode-print-form')); ?>
                                        <div class="col-md-12">
                                            <?php echo render_input('csv_file','choose_csv_file','','file'); ?>
                                        </div>
                                        <div class="col-md-12">
                                            <?php $value = isset($stock_count) ? $stock_count->note : '' ?>
                                            <?php echo render_textarea('note', 'Note', $value); ?>
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
        _validate_form($('#barcode-print-form'), {csv_file: {required:true,extension: "csv"}});
        $('.radio .type').change(function () {
            if (this.checked) {
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
