<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php do_action('before_items_page_content'); ?>
                        <?php if (has_permission('items', '', 'create')) { ?>
                            <div class="_buttons">
                                <a href="#" class="btn btn-info pull-left mleft5" data-toggle="modal"
                                   data-target="#tom"><?php echo _l('Machine Type'); ?></a>
                            </div>
                            <div class="_buttons">
                                <a href="#" class="btn btn-info pull-left mleft5" data-toggle="modal"
                                   data-target="#consumable"><?php echo _l('Consumable'); ?></a>
                            </div>
                            <button type="button" id="hiddenBtn" class="hide"></button>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-heading"/>
                        <?php } ?>
                        <?php
                        $table_data = array(
                            array(
                                'name' => _l('Customer Name'),
                                'th_attrs' => array('class'=> 'not_visible')
                            ),
                            _l('Customer Name'),
                            _l('TPL / Part No'),
                            _l('Invoice No'),
                            _l('FAB No'),
                            _l('Installation Date'),
                            _l('Type of Machine'),
                            _l('Category Type'),
                        );
                        $cf = get_custom_fields('scheduled_services');
                        foreach ($cf as $custom_field) {
                            array_push($table_data, $custom_field['name']);
                        }
                        render_datatable($table_data, 'scheduled-services'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/invoice_items/item'); ?>
<div class="modal fadtome" id="tom" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <?php echo _l('Machine Type'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <?php if (has_permission('scheduled_services', '', 'create')) { ?>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group col-md-12">
                                <input type="text" name="machine_type" id="machine_type" class="form-control"
                                       placeholder="<?php echo _l('Machine Type'); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" name="avg_running_hr_per_day" id="avg_running_hr_per_day"
                                       class="form-control" placeholder="<?php echo _l('Avg. Running hr/day'); ?>">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="input-group">
                                <input type="text" name="avg_running_hr_per_year" id="avg_running_hr_per_year"
                                       class="form-control" placeholder="<?php echo _l('Avg. Running hr/year'); ?>">
                            </div>
                        </div>

                        <div class="col-md-2">
              <span class="input-group-btn">
                <button class="btn btn-info p7" type="button"
                        id="new-machine-insert"><?php echo _l('Add Machine'); ?></button>
              </span>
                        </div>
                    </div>
                    <hr/>
                <?php } ?>
                <div class="row">
                    <div class="container-fluid">
                        <table class="table dt-table table-items-groups" data-order-col="0" data-order-type="asc">
                            <thead>
                            <tr>
                                <th><?php echo _l('Machine Type'); ?></th>
                                <th><?php echo _l('Avg Running Hr/Day'); ?></th>
                                <th><?php echo _l('Avg Running Hr/Year'); ?></th>
                                <th><?php echo _l('Budget(&#8377;)'); ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($list_of_machines as $machines) { ?>
                                <tr class="row-has-options" data-machine-row-id="<?php echo $machines['id']; ?>">
                                    <td data-order="<?php echo $machines['machine_type']; ?>">
                                        <span class="machine_type_plain_text"><?php echo $machines['machine_type']; ?></span>
                                        <div class="machine_type_edit hide">
                                            <div class="input-group">
                                                <input type="text" class="form-control">
                                                <span class="input-group-btn">
                          <!-- <button class="btn btn-info p8 update-machine-name" type="button"><?php echo _l('submit'); ?></button> -->
                        </span>
                                            </div>
                                        </div>
                                        <div class="row-options">
                                            <?php if (has_permission('scheduled_services', '', 'edit')) { ?>
                                                <a href="#" class="edit-new-machine">
                                                    <?php echo _l('edit'); ?>
                                                </a>
                                            <?php } ?>
                                            <?php if (has_permission('scheduled_services', '', 'delete')) { ?>
                                                |
                                                <a href="<?php echo admin_url('scheduled_services/delete_type_of_machine/' . $machines['id']); ?>"
                                                   class="delete-machine-type _delete text-danger">
                                                    <?php echo _l('delete'); ?>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </td>

                                    <td data-order="<?php echo $machines['machine_type']; ?>">
                                        <span class="avg_running_hr_per_day_plain_text"><?php echo $machines['avg_running_hr_per_day']; ?></span>
                                        <div class="avg_running_hr_per_day_edit hide">
                                            <div class="input-group">
                                                <input type="text" class="form-control">
                                                <span class="input-group-btn">
                            <!-- <button class="btn btn-info p8 update-hours" type="button"><?php echo _l('submit'); ?></button> -->
                          </span>
                                            </div>
                                        </div>

                                    </td>

                                    <td data-order="<?php echo $machines['machine_type']; ?>">
                                        <span class="avg_running_hr_per_year_plain_text"><?php echo $machines['avg_running_hr_per_year']; ?></span>

                                        <div class="avg_running_hr_per_year_edit hide">
                                            <div class="input-group">
                                                <input type="text" class="form-control">
                                                <span class="input-group-btn">
                            <!-- <button class="btn btn-info p8 update-days" type="button"><?php echo _l('submit'); ?></button> -->
                          </span>
                                            </div>
                                        </div>
                                    </td>

                                    <td data-order="<?php echo $machines['machine_type']; ?>">
                                        <span class="budget_in_inr_plain_text"><?php echo $machines['budget_in_inr']; ?></span>

                                        <div class="budget_in_inr_edit hide">
                                            <div class="input-group">
                                                <input type="text" class="form-control">
                                                <span class="input-group-btn">
                            <!-- <button class="btn btn-info p8 update-days" type="button"><?php echo _l('submit'); ?></button> -->
                          </span>
                                            </div>
                                        </div>
                                    </td>

                                    <td data-order="<?php echo $machines['machine_type']; ?>">

                                        <div class="update_machine_type hide">
                                            <div class="input-group">
                          <span class="input-group-btn">
                            <button class="btn btn-info p8 update-machine-rows"
                                    type="button"><?php echo _l('submit'); ?></button>
                          </span>
                                            </div>
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
<div class="modal fadtome" id="consumable" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <?php echo _l('Consumable'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <?php if (has_permission('scheduled_services', '', 'create')) { ?>
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="input-group col-md-12">
                                <input type="text" name="consumable" id="consumable_title" class="form-control" placeholder="<?php echo _l('Consumable'); ?>">
                            </div>
                        </div>
                        <div class="col-sm-4">
                          <span class="input-group-btn">
                            <button class="btn btn-info p7" type="button" id="new-consumable-insert"><?php echo _l('Add Consumable'); ?></button>
                          </span>
                        </div>
                    </div>
                    <hr/>
                <?php } ?>
                <div class="row">
                    <div class="container-fluid">
                        <table class="table dt-table table-items-groups" data-order-col="0" data-order-type="asc">
                            <thead>
                            <tr>
                                <th><?php echo _l('Machine Type'); ?></th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach ($list_of_consumable as $consumable) { ?>
                                <tr class="row-has-options" data-consumable-row-id="<?php echo $consumable['id']; ?>">
                                    <td data-order="<?php echo $consumable['title']; ?>">
                                        <span class="consumable_type_plain_text"><?php echo $consumable['title']; ?></span>
                                        <div class="consumable_type_edit hide">
                                            <div class="input-group">
                                                <input type="text" class="form-control">
                                                <span class="input-group-btn">
                          <!-- <button class="btn btn-info p8 update-machine-name" type="button"><?php echo _l('submit'); ?></button> -->
                        </span>
                                            </div>
                                        </div>
                                        <div class="row-options">
                                            <?php if (has_permission('scheduled_services', '', 'edit')) { ?>
                                                <a href="#" class="edit-new-consumable">
                                                    <?php echo _l('edit'); ?>
                                                </a>
                                            <?php } ?>
                                            <?php if (has_permission('scheduled_services', '', 'delete')) { ?>
                                                |
                                                <a href="<?php echo admin_url('scheduled_services/delete_consumable/' . $consumable['id']); ?>"
                                                   class="delete-consumable-type _delete text-danger">
                                                    <?php echo _l('delete'); ?>
                                                </a>
                                            <?php } ?>
                                        </div>
                                    </td>

                                    <td data-order="<?php echo $consumable['title']; ?>">

                                        <div class="update_consumable_type hide">
                                            <div class="input-group">
                                              <span class="input-group-btn">
                                                <button class="btn btn-info p8 update-consumable-rows"
                                                        type="button"><?php echo _l('submit'); ?></button>
                                              </span>
                                            </div>
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
<?php $this->load->view('admin/scheduled_services/newServiceModal'); ?>
<?php init_tail(); ?>
<script>
    $(function () {
        initDataTable('.table-scheduled-services', admin_url + 'scheduled_services/table', undefined, undefined, 'undefined', [0, 'asc']);
        if (get_url_param('machine_modal')) {
            // Set time out user to see the message
            setTimeout(function () {
                $('#tom').modal('show');
            }, 1000);
        }
        if (get_url_param('consumable_modal')) {
            // Set time out user to see the message
            setTimeout(function () {
                $('#consumable').modal('show');
            }, 1000);
        }

        $('#new-machine-insert').on('click', function () {
            var machine_type = $('#machine_type').val();
            var avg_running_hr_per_day = $('#avg_running_hr_per_day').val();
            var avg_running_hr_per_year = $('#avg_running_hr_per_year').val();

            if (machine_type != "" && avg_running_hr_per_day != "" && avg_running_hr_per_year != "") {
                $.post(admin_url + 'scheduled_services/add_type_of_machine', {
                    machine_type: machine_type,
                    avg_running_hr_per_day: avg_running_hr_per_day,
                    avg_running_hr_per_year: avg_running_hr_per_year
                }).done(function () {
                    window.location.href = admin_url + 'scheduled_services/new_arrivals?machine_modal=true';
                });
            }
        });

        $('#new-consumable-insert').on('click', function () {
            var consumable_title = $('#consumable_title').val();

            if (consumable_title != "") {
                $.post(admin_url + 'scheduled_services/add_type_of_consumable', {
                    title: consumable_title
                }).done(function () {
                    window.location.href = admin_url + 'scheduled_services/new_arrivals?consumable_modal=true';
                });
            }
        });

        $('body').on('click', '.edit-new-consumable', function (e) {
            e.preventDefault();
            var tr = $(this).parents('tr'),
                machine_id = tr.attr('data-consumable-row-id');
            tr.find('.consumable_type_plain_text').toggleClass('hide');

            tr.find('.consumable_type_edit').toggleClass('hide');
            tr.find('.consumable_type_edit input').val(tr.find('.consumable_type_plain_text').text());

            tr.find('.update_consumable_type').toggleClass('hide');
        });

        $('body').on('click', '.edit-new-machine', function (e) {
            e.preventDefault();
            var tr = $(this).parents('tr'),
                machine_id = tr.attr('data-machine-row-id');
            tr.find('.machine_type_plain_text').toggleClass('hide');
            tr.find('.avg_running_hr_per_day_plain_text').toggleClass('hide');
            tr.find('.avg_running_hr_per_year_plain_text').toggleClass('hide');
            tr.find('.budget_in_inr_plain_text').toggleClass('hide');

            tr.find('.machine_type_edit').toggleClass('hide');
            tr.find('.machine_type_edit input').val(tr.find('.machine_type_plain_text').text());

            tr.find('.avg_running_hr_per_day_edit').toggleClass('hide');
            tr.find('.avg_running_hr_per_day_edit input').val(tr.find('.avg_running_hr_per_day_plain_text').text());

            tr.find('.avg_running_hr_per_year_edit').toggleClass('hide');
            tr.find('.avg_running_hr_per_year_edit input').val(tr.find('.avg_running_hr_per_year_plain_text').text());

            tr.find('.budget_in_inr_edit').toggleClass('hide');
            tr.find('.budget_in_inr_edit input').val(tr.find('.budget_in_inr_plain_text').text());

            tr.find('.update_machine_type').toggleClass('hide');
        });

        $('body').on('click', '.update-machine-rows', function () {
            var tr = $(this).parents('tr');
            var machine_id = tr.attr('data-machine-row-id');
            machine_type = tr.find('.machine_type_edit input').val();
            avg_running_hr_per_day = tr.find('.avg_running_hr_per_day_edit input').val();
            avg_running_hr_per_year = tr.find('.avg_running_hr_per_year_edit input').val();
            budget_in_inr = tr.find('.budget_in_inr_edit input').val();


            if (machine_type != "" && avg_running_hr_per_day != "" && avg_running_hr_per_year != "" && budget_in_inr != "") {
                $.post(admin_url + 'scheduled_services/update_type_of_machine/' + machine_id, {
                    machine_type: machine_type,
                    avg_running_hr_per_day: avg_running_hr_per_day,
                    avg_running_hr_per_year: avg_running_hr_per_year,
                    budget_in_inr: budget_in_inr
                }).done(function () {
                    window.location.href = admin_url + 'scheduled_services/new_arrivals';
                });
            }
        });

        $('body').on('click', '.update-consumable-rows', function () {
            var tr = $(this).parents('tr');
            var machine_id = tr.attr('data-consumable-row-id');
            var consumable_title = tr.find('.consumable_type_edit input').val();

            if (consumable_title != "") {
                $.post(admin_url + 'scheduled_services/update_type_of_consumable/' + machine_id, {
                    title: consumable_title
                }).done(function () {
                    window.location.href = admin_url + 'scheduled_services/new_arrivals?consumable_modal=true';
                });
            }
        });

        $('body').on('click', '.new-arrival-services-insert', function () {
            var items_in_id = $(this).attr('data-id');
            var customer_name = $('#customer_name').html();
            var part_no = $('#part_no').html();
            var invoice_no = $('#invoice_no').html();
            var itm_fab_no = $('#itm_fab_no').val();
            var installation_date = $('#installation_date').val();
            var type_of_machine = $(this).closest('tr').find('#type_of_machine').val();

        });

    });

    $('.add-machine-scheduled-services').on('click', function () {
        var machine_type = $('#machine_type').val();
        var avg_running_hr_per_day = $('#avg_running_hr_per_day').val();
        var avg_running_hr_per_year = $('#avg_running_hr_per_year').val();

        if (machine_type != "" && avg_running_hr_per_day != "" && avg_running_hr_per_year != "") {
            $.post(admin_url + 'scheduled_services/add_type_of_machine', {
                machine_type: machine_type,
                avg_running_hr_per_day: avg_running_hr_per_day,
                avg_running_hr_per_year: avg_running_hr_per_year
            }).done(function () {
                window.location.href = admin_url + 'scheduled_services/new_arrivals?machine_modal=true';
            });
        }
    });

    setTimeout(function () {
        init_datepicker();
    }, 1000);

    function changeAmcEndDate(a){
        let date = a.split('-');
        let nextDate = moment('"'+date[2]+'-'+date[1]+'-'+date[0]+'"').add(1,'y').format('DD-MM-YYYY');
        $('#end_date_amc').val(nextDate);
    }

    function calculateDays(count, hours) {
        var days = parseInt(hours) / 24;
        $("#days_sch_service" + count).val(parseInt(days));
    }

    function onChangeUpdateDetails(id, column, val) {
        $.post(admin_url + "Scheduled_services/update_column_details", {
            id:id,
            column:column,
            val: val
        }).done(function (result) {
            // result = JSON.parse(result);
            // console.log(result);
            // if (result != '') {
            // $("#add-profile-attachment-wrapper").html(result.result);
            //console.log(result.result);
            // }
        });
    }
</script>
</body>
</html>
