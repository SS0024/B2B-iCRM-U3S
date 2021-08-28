<div class="modal fade" id="scheduledServicesModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <?php echo _l('Scheduled Services'); ?>
                </h4>
            </div>
            <?php echo form_open(site_url('admin/scheduled_services/add_machine_scheduled_services'), ['id' => 'machineSchForm']); ?>
            <div class="modal-body">
                <?php if (has_permission('scheduled_services', '', 'create')) { ?>
                    <div class="row" style="font-size: 14px">
                        <div class="col-md-2">
                            <span style="font-weight: bold;">Machine Type:</span>
                        </div>
                        <div class="col-md-2">
                            <span id="machine_type_name"></span>
                        </div>

                        <div class="col-md-2">
                            <span style="font-weight: bold;">Avg. running per hr/day:</span>
                        </div>
                        <div class="col-md-2">
                            <span id="avg_running_hr_per_day_span"></span>
                        </div>

                        <div class="col-md-2">
                            <span style="font-weight: bold;">Avg. running per hr/year:</span>
                        </div>
                        <div class="col-md-2">
                            <span id="avg_running_hr_per_year_span"></span>
                        </div>
                    </div>
                    <hr>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="input-group col-md-12">
                                <input type="text" name="budget_in_inr_sch" id="budget_in_inr_sch"
                                       class="form-control numericField"
                                       placeholder="<?php echo _l('Budget in INR'); ?>">
                            </div>
                        </div>
                    </div>
                    <hr/>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <span name="sch_service1" id="sch_service1"><?php echo _l('1st Service'); ?></span>
                                <input type="hidden" name="service_val[]" id="service_val1" value="1">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <input type="text" name="hours_sch_service[]" id="hours_sch_service1"
                                       class="form-control numericField" placeholder="<?php echo _l('Total Hours'); ?>"
                                       onChange="calculateDays(1, this.value)">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <input type="text" name="days_sch_service[]" id="days_sch_service1" class="form-control"
                                       placeholder="<?php echo _l('No of Days'); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <select name="consumable[]" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('Select Consumable'); ?>">
                                    <?php foreach($list_of_consumable as $consumable){ ?>
                                        <option value="<?php echo $consumable['id']; ?>"><?php echo $consumable['title']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <span name="sch_service2" id="sch_service2"><?php echo _l('2nd Service'); ?></span>
                                <input type="hidden" name="service_val[]" id="service_val2" value="2">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <input type="text" name="hours_sch_service[]" id="hours_sch_service2"
                                       class="form-control numericField" placeholder="<?php echo _l('Total Hours'); ?>"
                                       onChange="calculateDays(2, this.value)">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <input type="text" name="days_sch_service[]" id="days_sch_service2" class="form-control"
                                       placeholder="<?php echo _l('No of Days'); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <select name="consumable[]" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('Select Consumable'); ?>">
                                    <?php foreach($list_of_consumable as $consumable){ ?>
                                        <option value="<?php echo $consumable['id']; ?>"><?php echo $consumable['title']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <span name="sch_service3" id="sch_service3"><?php echo _l('3rd Service'); ?></span>
                                <input type="hidden" name="service_val[]" id="service_val3" value="3">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <input type="text" name="hours_sch_service[]" id="hours_sch_service3"
                                       class="form-control numericField" placeholder="<?php echo _l('Total Hours'); ?>"
                                       onChange="calculateDays(3, this.value)">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <input type="text" name="days_sch_service[]" id="days_sch_service3" class="form-control"
                                       placeholder="<?php echo _l('No of Days'); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <select name="consumable[]" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('Select Consumable'); ?>">
                                    <?php foreach($list_of_consumable as $consumable){ ?>
                                        <option value="<?php echo $consumable['id']; ?>"><?php echo $consumable['title']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <span name="sch_service4" id="sch_service4"><?php echo _l('4th Services'); ?></span>
                                <input type="hidden" name="service_val[]" id="service_val4" value="4">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <input type="text" name="hours_sch_service[]" id="hours_sch_service4"
                                       class="form-control numericField" placeholder="<?php echo _l('Total Hours'); ?>"
                                       onChange="calculateDays(4, this.value)">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <input type="text" name="days_sch_service[]" id="days_sch_service4" class="form-control"
                                       placeholder="<?php echo _l('No of Days'); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <select name="consumable[]" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('Select Consumable'); ?>">
                                    <?php foreach($list_of_consumable as $consumable){ ?>
                                        <option value="<?php echo $consumable['id']; ?>"><?php echo $consumable['title']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <span name="sch_service5" id="sch_service5"><?php echo _l('5th Services'); ?></span>
                                <input type="hidden" name="service_val[]" id="service_val5" value="5">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <input type="text" name="hours_sch_service[]" id="hours_sch_service5"
                                       class="form-control numericField" placeholder="<?php echo _l('Total Hours'); ?>"
                                       onChange="calculateDays(5, this.value)">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <input type="text" name="days_sch_service[]" id="days_hours_sch_service5"
                                       class="form-control" placeholder="<?php echo _l('No of Days'); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <select name="consumable[]" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('Select Consumable'); ?>">
                                    <?php foreach($list_of_consumable as $consumable){ ?>
                                        <option value="<?php echo $consumable['id']; ?>"><?php echo $consumable['title']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <span name="sch_service6" id="sch_service[]"><?php echo _l('6th Services'); ?></span>
                                <input type="hidden" name="service_val[]" id="service_val6" value="6">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <input type="text" name="hours_sch_service[]" id="hours_sch_service6"
                                       class="form-control numericField" placeholder="<?php echo _l('Total Hours'); ?>"
                                       onChange="calculateDays(6, this.value)">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <input type="text" name="days_sch_service[]" id="days_sch_service6" class="form-control"
                                       placeholder="<?php echo _l('No of Days'); ?>" readonly>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="input-group col-md-12">
                                <select name="consumable[]" class="selectpicker" multiple data-width="100%" data-none-selected-text="<?php echo _l('Select Consumable'); ?>">
                                    <?php foreach($list_of_consumable as $consumable){ ?>
                                        <option value="<?php echo $consumable['id']; ?>"><?php echo $consumable['title']; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>

                <?php } ?>

            </div>
            <div class="modal-footer">
                <button type="submit" id="schSerBtn" class="btn btn-info pull-right"><?php echo _l('Save'); ?></button>
                <button type="button" class="btn btn-default pull-left add-machine-scheduled-services"
                        data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
<div class="modal fade" id="amcServicesModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <?php echo form_open(site_url('admin/contracts/contract')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">
                    <?php echo _l('AMC Services'); ?>
                </h4>
            </div>
            <div class="modal-body">
                <?php if (has_permission('scheduled_services', '', 'create')) { ?>
                    <div class="row" style="font-size: 14px">
                        <div class="col-md-2">
                            <span style="font-weight: bold;">Machine Type:</span>
                        </div>
                        <div class="col-md-2">
                            <span id="machine_type_name_amc"></span>
                        </div>

                        <div class="col-md-2">
                            <span style="font-weight: bold;">Avg. running per hr/day:</span>
                        </div>
                        <div class="col-md-2">
                            <span id="avg_running_hr_per_day_amc_span"></span>
                        </div>

                        <div class="col-md-2">
                            <span style="font-weight: bold;">Avg. running per hr/year:</span>
                        </div>
                        <div class="col-md-2">
                            <span id="avg_running_hr_per_year_amc_span"></span>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-3">
                            <span style="font-weight: bold;">Start Date</span>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group col-md-12">
                                <input type="hidden" name="is_new_arrival" value="1">
                                <input type="hidden" id="amc_item_id" name="item_id" >
                                <input type="hidden" id="amc_machine_type" name="machine_type" >
                                <input type="hidden" id="amc_avg_running_hr_per_day" name="amc_avg_running_hr_per_day" >
                                <input type="hidden" id="amc_avg_running_hr_per_year" name="amc_avg_running_hr_per_year" >
                                <input type="text" id="start_date_amc" name="start_date_amc" onchange="changeAmcEndDate(this.value)"
                                       class="form-control datepicker" value="">
                                <div class="input-group-addon"><i class="fa fa-calendar calendar-icon"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <span style="font-weight: bold;">End Date</span>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group col-md-12">
                                <input type="text" id="end_date_amc" name="end_date_amc" class="form-control datepicker"
                                       value="">
                                <div class="input-group-addon"><i class="fa fa-calendar calendar-icon"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <span style="font-weight: bold;">No of visits</span>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group col-md-12">
                                <input type="text" name="no_of_visits_amc" id="no_of_visits_amc"
                                       class="form-control numericField"
                                       placeholder="<?php echo _l('No of Visits'); ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <span style="font-weight: bold;">Budget in INR</span>
                        </div>
                        <div class="col-md-4">
                            <div class="input-group col-md-12">
                                <input type="text" name="budget_in_inr_amc" id="budget_in_inr_amc"
                                       class="form-control numericField"
                                       placeholder="<?php echo _l('Budget in INR'); ?>">
                            </div>
                        </div>
                    </div>
                <?php } ?>

            </div>
            <div class="modal-footer">
                <button type="submit" id="amcSerBtn" class="btn btn-info pull-right"><?php echo _l('Save'); ?></button>
                <button type="button" class="btn btn-default pull-left"
                        data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

<script>
    function catType(object, cat_type_val, row_count) {
        let current =$(object);
        var machineTypeId = current.closest('tr').find('select[name="type_of_machine"] option:selected').val();
        if (machineTypeId != "") {
            $.ajax({
                url: admin_url + 'scheduled_services/getMachineById',
                method: "POST",
                dataType: "json",
                data: {'machineTypeId': machineTypeId},
                success: function (data) {
                    if (cat_type_val == "scheduled") {
                        $('#scheduledServicesModal').modal('show');
                        $("#machine_type_name").html(data.machine_type);
                        $("#avg_running_hr_per_day_span").html(data.avg_running_hr_per_day);
                        $("#avg_running_hr_per_year_span").html(data.avg_running_hr_per_year);
                    } else if (cat_type_val == "amc") {
                        $("#amcServicesModal").modal('show');
                        $("#machine_type_name_amc").html(data.machine_type);
                        $("#amc_machine_type").val(data.machine_type);
                        $("#avg_running_hr_per_day_amc_span").html(data.avg_running_hr_per_day);
                        $("#amc_avg_running_hr_per_day").val(data.avg_running_hr_per_day);
                        $("#avg_running_hr_per_year_amc_span").html(data.avg_running_hr_per_year);
                        $("#amc_avg_running_hr_per_year").val(data.avg_running_hr_per_year);
                        $("#amc_item_id").val(current.data('item_id'));

                    }
                }
            });
            $("#schSerBtn").prop("disabled", false);
        } else {
            alert("Please select the machine type");
            $("#schSerBtn").prop("disabled", true);
        }
    }
</script>