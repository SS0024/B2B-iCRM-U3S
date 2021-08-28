<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (has_permission('warehouses', '', 'create')) { ?>
                        <div class="_buttons">
                            <a href="#" onclick="new_department(); return false;"
                               class="btn btn-info pull-left display-block">
                                <?php echo _l('new_warehouse'); ?>
                            </a>
                        </div>
                        <?php } ?>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <div class="clearfix"></div>
                        <?php render_datatable(array(
                            _l('Code'),
                            _l('Name'),
                            _l('Phone'),
                            _l('Email'),
                            _l('Address'),
                            _l('Actions')
                        ), 'warehouses'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="department" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <?php echo form_open(admin_url('warehouses/warehouse')); ?>
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title">
                    <span class="edit-title"><?php echo _l('Edit Warehouse'); ?></span>
                    <span class="add-title"><?php echo _l('new_warehouse'); ?></span>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div id="additional"></div>
                        <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                        <?php echo render_input('code', 'Code'); ?>
                        <?php echo render_input('name', 'Name'); ?>
                        <?php echo render_input('phone', 'Phone'); ?>
                        <?php echo render_input('email', 'Email','','email'); ?>
                        <?php echo render_textarea('address', 'Address'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
        </div><!-- /.modal-content -->
        <?php echo form_close(); ?>
    </div><!-- /.modal-dialog -->
</div><!-- /.modal -->
<?php init_tail(); ?>
<script>
    $(function () {
        initDataTable('.table-warehouses', window.location.href, [3], [3]);
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
