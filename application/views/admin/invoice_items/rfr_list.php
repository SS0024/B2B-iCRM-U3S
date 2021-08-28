<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php do_action('before_items_page_content'); ?>
                        <?php if(has_permission('items','','create')){ ?>
                            <div class="_buttons">
                                <a href="#" class="btn btn-info pull-left" data-toggle="modal" data-target="#tasks_bulk_actions"><?php echo _l('New RFR'); ?></a>
                            </div>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-heading" />
                        <?php } ?>
                        <?php
                        $table_data = array(
                            _l('invoice_items_list_description'),
                            _l('Mail to'),
                            _l('Created At'),
                            _l('Added By'),
                            _l('Action'));
                        render_datatable($table_data,'invoice-items'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade bulk_actions" id="tasks_bulk_actions" tabindex="-1" role="dialog" data-table="<?php echo (isset($table) ? $table : '.table-invoice-items'); ?>">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open_multipart('admin/invoice_items/rfr_request',['id'=>'rfr-form']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
            </div>
            <div class="modal-body">
                <div id="bulk_change">
                    <?php echo render_input('mailto','Mail To'); ?>
                    <?php echo render_textarea('message','Message','',array('rows'=>6,'placeholder'=>_l('Message'),'data-task-ae-editor'=>true, !is_mobile() ? 'onclick' : 'onfocus'=>(!isset($task) || isset($task) && $task->description == '' ? 'init_editor(\'.tinymce-task\', {height:200, auto_focus: true});' : '')),array(),'no-mbot','tinymce-task'); ?>
                    <div class="form-group">
                        <label for="attachment" class="control-label">Attachment</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>
                    <div class="form-group mbot25 items-wrapper select-placeholder">
                        <label for="item_id" class="control-label"> <small class="req text-danger">* </small>Add Item</label>
                        <div class="items-select-wrapper">
                            <select name="item_id" class="selectpicker no-margin" data-width="100%" id="item_select" data-none-selected-text="<?php echo _l('add_item'); ?>" data-live-search="true">
                                <option value=""></option>
                                <?php foreach($expire_items as $group_id=>$_items){ ?>
                                    <optgroup data-group-id="<?php echo $group_id; ?>" label="<?php echo $_items[0]['group_name']; ?>">
                                        <?php foreach($_items as $item){ ?>
                                            <option value="<?php echo $item['id']; ?>" data-subtext="<?php echo strip_tags(mb_substr($item['long_description'],0,200)).'...'; ?>">(<?php echo _format_number($item['rate']); ; ?>) <?php echo $item['description']; ?></option>
                                        <?php } ?>
                                    </optgroup>
                                <?php } ?>
                            </select>
                        </div>
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
<div class="modal fade" id="update_price" tabindex="-1" role="dialog" data-table="<?php echo (isset($table) ? $table : '.table-invoice-items'); ?>">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/invoice_items/update_rfr_price',['id'=>'rfr-price-form']); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('bulk_actions'); ?></h4>
            </div>
            <div class="modal-body">
                <div id="bulk_change">
                    <input type="hidden" value="" name="rfr_id">
                    <?php echo render_date_input('upto','Valid Upto'); ?>
                    <?php echo render_input('price','Price','','number'); ?>
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
<?php init_tail(); ?>
<script>
    $(function() {
        initDataTable('.table-invoice-items', admin_url + 'invoice_items/rfr_table', undefined, undefined, 'undefined', [0, 'asc']);
        _validate_form($('#rfr-form'), {
            mailto: {
                required:true,
                email : true
            },
            item_id: 'required'
        });
        _validate_form($('#rfr-price-form'), {
            upto: {
                required:true
            },
            price: 'required'
        });

    });
    function openPriceUpdateModel(id) {
        $("input[name='rfr_id']").val(id)
        $("#update_price").modal('show');
    }
</script>
</body>
</html>
