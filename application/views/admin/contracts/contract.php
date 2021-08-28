<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-5 left-column">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo form_open($this->uri->uri_string(), array('id' => 'contract-form')); ?>
                        <div class="row">
                            <div class="_buttons">
                                <a href="#" class="btn btn-info pull-left mleft5" data-toggle="modal"
                                   data-target="#consumable"><?php echo _l('Consumable'); ?></a>
                            </div>
                        </div>
                        <div class="form-group mtop10">
                            <div class="checkbox checkbox-primary no-mtop checkbox-inline">
                                <input type="checkbox" id="trash" name="trash" data-toggle="tooltip"
                                       title="<?php echo _l('contract_trash_tooltip'); ?>" <?php if (isset($contract)) {
                                    if ($contract->trash == 1) {
                                        echo 'checked';
                                    }
                                }; ?>>
                                <label for="trash"><?php echo _l('contract_trash'); ?></label>
                            </div>
                            <div class="checkbox checkbox-primary checkbox-inline">
                                <input type="checkbox" name="not_visible_to_client"
                                       id="not_visible_to_client" <?php if (isset($contract)) {
                                    if ($contract->not_visible_to_client == 1) {
                                        echo 'checked';
                                    }
                                }; ?>>
                                <label for="not_visible_to_client"><?php echo _l('contract_not_visible_to_client'); ?></label>
                            </div>
                        </div>
                        <div class="form-group select-placeholder">
                            <label for="clientid" class="control-label"><span
                                        class="text-danger">* </span><?php echo _l('contract_client_string'); ?></label>
                            <select id="clientid" name="client" data-live-search="true" data-width="100%"
                                    class="ajax-search"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <?php $selected = (isset($contract) ? $contract->client : '');
                                if ($selected == '') {
                                    $selected = (isset($customer_id) ? $customer_id : '');
                                }
                                if ($selected != '') {
                                    $rel_data = get_relation_data('customer', $selected);
                                    $rel_val = get_relation_values($rel_data, 'customer');
                                    echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                } ?>
                            </select>
                        </div>
                        <?php
                        $selected = (isset($contract) ? $contract->contract_type : '');
                        if (isset($category_type) && !empty($category_type)) {
                            $selected = array_flatten(array_filter($types, function ($d) use ($category_type) {
                                if (strtolower($d['name']) === strtolower($category_type)) {
                                    return $d['id'];
                                }
                            }));
                        }
                        if(isset($contract) && $contract->subject != ''){
                            $value = $contract->subject;
                        }else{
                            $value = $rel_val['name'].' | '.(isset($selected[1]) ? $selected[1] . ' |' : '').(isset($subject) ? $subject : '');
                        }
                        ?>
                        <i class="fa fa-question-circle pull-left" data-toggle="tooltip"
                           title="<?php echo _l('contract_subject_tooltip'); ?>"></i>
                        <?php echo render_input('subject', 'contract_subject', $value); ?>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contract_value"><?php echo _l('contract_value'); ?></label>
                                    <div class="input-group" data-toggle="tooltip"
                                         title="<?php echo _l('contract_value_tooltip'); ?>">
                                        <input type="number" class="form-control" name="contract_value"
                                               value="<?php if (isset($contract)) {
                                                   echo $contract->contract_value;
                                               } ?>">
                                        <div class="input-group-addon">
                                            <?php echo $base_currency->symbol; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <?php $value = (isset($contract->spare_value) ? $contract->spare_value : ''); ?>
                                    <label for="spare_value"><?php echo _l('Spare Budget'); ?></label>
                                    <div class="input-group" data-toggle="tooltip"
                                         title="<?php echo _l('Spare Budget'); ?>">
                                        <input type="number" class="form-control" name="spare_value"
                                               value="<?php echo $value; ?>">
                                        <div class="input-group-addon">
                                            <?php echo $base_currency->symbol; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php
                        if (is_admin() || get_option('staff_members_create_inline_contract_types') == '1') {
                            echo render_select_with_input_group('contract_type', $types, array('id', 'name'), 'contract_type', $selected[0], '<a href="#" onclick="new_type();return false;"><i class="fa fa-plus"></i></a>');
                        } else {
                            echo render_select('contract_type', $types, array('id', 'name'), 'contract_type', $selected[0]);
                        }
                        ?>
                        <div class="row">
                            <div class="col-md-6">
                                <?php $value = (isset($contract) ? _d($contract->datestart) : (isset($item_details->installation_date) ? _d($item_details->installation_date) : _d(date('Y-m-d')))); ?>
                                <?php echo render_date_input('datestart', 'contract_start_date', $value, ['onchange' => "changeAmcEndDate(this.value)"]); ?>
                            </div>
                            <div class="col-md-6">
                                <?php $value = (isset($contract) ? _d($contract->dateend) : ''); ?>
                                <?php echo render_date_input('dateend', 'contract_end_date', $value); ?>
                                <?php $value = (isset($_GET['product_id']) && !empty($_GET['product_id']) ? $_GET['product_id'] : $contract->item_id); ?>
                                <input type="hidden" name="item_id" value="<?= $value ?>">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="consumable"><?php echo _l('Consumables'); ?></label>
                                <div class="form-group">
                                    <select name="consumable[]" class="selectpicker" multiple data-width="100%"
                                            data-none-selected-text="<?php echo _l('Select Consumable'); ?>">
                                        <?php
                                        $consumables = isset($contract->consumable) ? explode(', ', $contract->consumable) : array();
                                        foreach ($list_of_consumable as $consumable) { ?>
                                            <option value="<?php echo $consumable['id']; ?>"
                                                <?= in_array($consumable['id'], $consumables) ? "selected='selected'" : "" ?>><?php echo $consumable['title']; ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="scheduled_service_div <?= ($category_type == 'scheduled service' || $category_type == 'amc'  || ($contract->contract_type == 1 || $contract->contract_type == 3)) ? '' : 'hidden' ?>">
                            <hr>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Running Days Per Year:</label>
                                        <span id="avg_running_day_per_year_amc_span"><?php echo isset($item_details->running_days) ? $item_details->running_days : 0; ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Avg. running per hr/day:</label>
                                        <span id="avg_running_hr_per_day_amc_span"><?php echo isset($item_details->running_hpd) ? $item_details->running_hpd : 0; ?></span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Avg. running per hr/year:</label>
                                        <span id="avg_running_hr_per_year_amc_span"><?php echo isset($item_details->running_hpy) ? $item_details->running_hpy : 0; ?></span>
                                    </div>
                                </div>
                            </div>
                            <hr/>
                            <div class="row">
                                <div class="col-md-<?= (isset($contract->id) && !empty($contract->id)) ? 2 : 3 ?>">
                                    <div class="form-group">
                                        <label><b>Services</b></label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><b>Total Hours</b></label>
                                    </div>
                                </div>

                                <div class="col-md-<?= (isset($contract->id) && !empty($contract->id)) ? 2 : 3 ?>">
                                    <div class="form-group">
                                        <label><b>No of days</b></label>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><b>Estimate Date</b></label>
                                    </div>
                                </div>
                                <?php
                                if (isset($contract->id) && !empty($contract->id)) {
                                    ?>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label><b>Convert Task</b></label>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                            <?php
                            function addOrdinalNumberSuffix($num)
                            {
                                if (!in_array(($num % 100), array(11, 12, 13))) {
                                    switch ($num % 10) {
                                        // Handle 1st, 2nd, 3rd
                                        case 1:
                                            return $num . 'st';
                                        case 2:
                                            return $num . 'nd';
                                        case 3:
                                            return $num . 'rd';
                                    }
                                }
                                return $num . 'th';
                            }

                            if (isset($contract->services) && !empty($contract->services)) {
                                $services = json_decode($contract->services, true);
                                $servicesCount = count($services);
                                foreach ($services as $i => $val) {
                                    ?>
                                    <div class="row">
                                        <div class="col-md-2">
                                            <span for="sch_service<?= $i ?>"
                                                  id="sch_service<?= $i ?>"><?php echo addOrdinalNumberSuffix($i) . " Service"; ?>
                                            </span>
                                            <input type="hidden" name="services[<?= $i ?>][service_val]"
                                                   id="service_val<?= $i ?>" value="<?= $i ?>">
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="number" name="services[<?= $i ?>][total_hours]"
                                                       id="total_hours<?= $i ?>"
                                                       class="form-control numericField"
                                                       value="<?= $val['total_hours'] ?>"
                                                       placeholder="<?php echo _l('Total Hours'); ?>"
                                                       onChange="calculateDays(<?= $i ?>, this.value, false)">
                                            </div>
                                        </div>

                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <input type="text" name="services[<?= $i ?>][no_of_days]"
                                                       id="no_of_days<?= $i ?>"
                                                       class="form-control"
                                                       value="<?= $val['no_of_days'] ?>"
                                                       onChange="calculateDays(<?= $i ?>, this.value, true)"
                                                       placeholder="<?php echo _l('No of Days'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <div class="input-group date">
                                                    <input type="text" id="estimate_date<?= $i ?>"
                                                           value="<?= $val['estimate_date'] ?>"
                                                           name="services[<?= $i ?>][estimate_date]"
                                                           class="form-control datepicker"
                                                           placeholder="Estimate Date">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-calendar calendar-icon"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <?php
                                            if(isset($val['task_id'])){
                                                ?>
                                                <input type="hidden" name="services[<?= $i ?>][task_id]" value="<?= $val['task_id'] ?>">
                                                <a href="<?= admin_url('tasks/view/' . $val['task_id']) ?>" target="_blank" class="btn btn-info pull-left mbot25 mright5 new-task-relation"><?= $val['task_id'] ?></a>
                                                <?php
                                            }else{
                                                ?>
                                                <a href="#"
                                                   class="btn btn-info pull-left mbot25 mright5 new-task-relation"
                                                   onclick="new_task_from_relation_service('.table-rel-tasks','contract',<?= $contract->id ?>,'<?= addOrdinalNumberSuffix($i) . " Service" ?>', <?= $i ?>); return false;">Add
                                                    Task</a>
                                                <?php
                                            }
                                            ?>
                                            <!--                                                <button type="button" onclick="" class="btn btn-info">Add Task</button>-->
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div class="more_service_div">

                                </div>
                                <button type="button" onclick="addNewServiceDiv(); return false;"
                                        class="btn btn-info"><?php echo _l('Add'); ?></button>
                                <?php
                            } else {
                                $servicesCount = 4;
                                for ($i = 1; $i <= 4; $i++) {
                                    ?>
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                            <span for="sch_service<?= $i ?>"
                                                  id="sch_service<?= $i ?>"><?php echo addOrdinalNumberSuffix($i) . " Service"; ?></span>
                                                <input type="hidden" name="services[<?= $i ?>][service_val]"
                                                       id="service_val<?= $i ?>" value="<?= $i ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="number" name="services[<?= $i ?>][total_hours]"
                                                       id="total_hours<?= $i ?>"
                                                       class="form-control numericField"
                                                       placeholder="<?php echo _l('Total Hours'); ?>"
                                                       onChange="calculateDays(<?= $i ?>, this.value, false)">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" name="services[<?= $i ?>][no_of_days]"
                                                       id="no_of_days<?= $i ?>"
                                                       class="form-control"
                                                       onChange="calculateDays(<?= $i ?>, this.value, true)"
                                                       placeholder="<?php echo _l('No of Days'); ?>">
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <div class="input-group date">
                                                    <input type="text" id="estimate_date<?= $i ?>"
                                                           value="<?= $val['estimate_date'] ?>"
                                                           name="services[<?= $i ?>][estimate_date]"
                                                           class="form-control datepicker"
                                                           placeholder="Estimate Date">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-calendar calendar-icon"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                                <div class="more_service_div">

                                </div>
                                <button type="button" onclick="addNewServiceDiv(); return false;"
                                        class="btn btn-info"><?php echo _l('Add'); ?></button>
                                <?php
                            }
                            ?>
                            <hr>
                        </div>

<!--                        <div class="amc_div ">-->
<!--                            <hr>-->
<!--                            <div class="row">-->
<!--                                <div class="col-md-4">-->
<!--                                    <div class="form-group">-->
<!--                                        <label>Running Days Per Year:</label>-->
<!--                                        <span id="avg_running_hr_per_day_amc_span">--><?php //echo isset($item_details->running_days) ? $item_details->running_days : 0; ?><!--</span>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                                <div class="col-md-4">-->
<!--                                    <div class="form-group">-->
<!--                                        <label>Avg. running per hr/day:</label>-->
<!--                                        <span id="avg_running_hr_per_day_amc_span">--><?php //echo isset($item_details->running_hpd) ? $item_details->running_hpd : 0; ?><!--</span>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                                <div class="col-md-4">-->
<!--                                    <div class="form-group">-->
<!--                                        <label>Avg. running per hr/year:</label>-->
<!--                                        <span id="avg_running_hr_per_year_amc_span">--><?php //echo isset($item_details->running_hpy) ? $item_details->running_hpy : 0; ?><!--</span>-->
<!--                                    </div>-->
<!--                                </div>-->
<!--                            </div>-->
<!--                            <div class="row">-->
<!--                                <div class="col-md-6">-->
<!--                                    --><?php //$value = (isset($contract->no_of_visits) ? $contract->no_of_visits : 1); ?>
<!--                                    --><?php //echo render_input('no_of_visits', 'No of Visits', $value, 'number', array('min' => 1)); ?>
<!--                                </div>-->
<!--                            </div>-->
<!--                            <hr>-->
<!--                        </div>-->

                        <?php $value = (isset($contract) ? $contract->description : ''); ?>
                        <?php echo render_textarea('description','contract_description',$value,array('rows'=>6,'placeholder'=>_l('task_add_description'),'data-task-ae-editor'=>true, !is_mobile() ? 'onclick' : 'onfocus'=>(!isset($task) || isset($task) && $task->description == '' ? 'init_editor(\'.tinymce-task\', {height:200, auto_focus: true});' : '')),array(),'no-mbot','tinymce-task'); ?>
                        <?php $rel_id = (isset($contract) ? $contract->id : false); ?>
                        <?php echo render_custom_fields('contracts', $rel_id); ?>
                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
            <?php if (isset($contract)) { ?>
                <div class="col-md-7 right-column">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin"><?php echo $contract->subject; ?></h4>
                            <a href="<?php echo site_url('contract/' . $contract->id . '/' . $contract->hash); ?>"
                               target="_blank">
                                <?php echo _l('view_contract'); ?>
                            </a>
                            <hr class="hr-panel-heading"/>
                            <?php if ($contract->trash > 0) {
                                echo '<div class="ribbon default"><span>' . _l('contract_trash') . '</span></div>';
                            } ?>
                            <div class="horizontal-scrollable-tabs preview-tabs-top">
                                <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
                                <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
                                <div class="horizontal-tabs">
                                    <ul class="nav nav-tabs tabs-in-body-no-margin contract-tab nav-tabs-horizontal mbot15"
                                        role="tablist">
                                        <li role="presentation"
                                            class="<?php if (!$this->input->get('tab') || $this->input->get('tab') == 'tab_content') {
                                                echo 'active';
                                            } ?>">
                                            <a href="#tab_content" aria-controls="tab_content" role="tab"
                                               data-toggle="tab">
                                                <?php echo _l('contract_content'); ?>
                                            </a>
                                        </li>
                                        <li role="presentation"
                                            class="<?php if ($this->input->get('tab') == 'attachments') {
                                                echo 'active';
                                            } ?>">
                                            <a href="#attachments" aria-controls="attachments" role="tab"
                                               data-toggle="tab">
                                                <?php echo _l('contract_attachments'); ?>
                                                <?php if ($totalAttachments = count($contract->attachments)) { ?>
                                                    <span class="badge attachments-indicator"><?php echo $totalAttachments; ?></span>
                                                <?php } ?>
                                            </a>
                                        </li>
                                        <li role="presentation">
                                            <a href="#tab_comments" aria-controls="tab_comments" role="tab"
                                               data-toggle="tab" onclick="get_contract_comments(); return false;">
                                                <?php echo _l('contract_comments'); ?>
                                                <?php
                                                $totalComments = total_rows('tblcontractcomments', 'contract_id=' . $contract->id)
                                                ?>
                                                <span class="badge comments-indicator<?php echo $totalComments == 0 ? ' hide' : ''; ?>"><?php echo $totalComments; ?></span>
                                            </a>
                                        </li>
                                        <li role="presentation"
                                            class="<?php if ($this->input->get('tab') == 'renewals') {
                                                echo 'active';
                                            } ?>">
                                            <a href="#renewals" aria-controls="renewals" role="tab" data-toggle="tab">
                                                <?php echo _l('no_contract_renewals_history_heading'); ?>
                                                <?php if ($totalRenewals = count($contract_renewal_history)) { ?>
                                                    <span class="badge"><?php echo $totalRenewals; ?></span>
                                                <?php } ?>
                                            </a>
                                        </li>
                                        <li role="presentation" class="tab-separator">
                                            <a href="#tab_tasks" aria-controls="tab_tasks" role="tab" data-toggle="tab"
                                               onclick="init_rel_tasks_table(<?php echo $contract->id; ?>,'contract'); return false;">
                                                <?php echo _l('tasks'); ?>
                                            </a>
                                        </li>
                                        <li role="presentation" class="tab-separator">
                                            <a href="#tab_notes"
                                               onclick="get_sales_notes(<?php echo $contract->id; ?>,'contracts'); return false"
                                               aria-controls="tab_notes" role="tab" data-toggle="tab">
                                                <?php echo _l('contract_notes'); ?>
                                                <span class="notes-total">
                                    <?php if ($totalNotes > 0) { ?>
                                        <span class="badge"><?php echo $totalNotes; ?></span>
                                    <?php } ?>
                                 </span>
                                            </a>
                                        </li>
                                        <li role="presentation" data-toggle="tooltip"
                                            title="<?php echo _l('emails_tracking'); ?>" class="tab-separator">
                                            <a href="#tab_emails_tracking" aria-controls="tab_emails_tracking"
                                               role="tab" data-toggle="tab">
                                                <?php if (!is_mobile()) { ?>
                                                    <i class="fa fa-envelope-open-o" aria-hidden="true"></i>
                                                <?php } else { ?>
                                                    <?php echo _l('emails_tracking'); ?>
                                                <?php } ?>
                                            </a>
                                        </li>
                                        <li role="presentation" class="tab-separator toggle_view">
                                            <a href="#" onclick="contract_full_view(); return false;"
                                               data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>">
                                                <i class="fa fa-expand"></i></a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <div class="tab-content">
                                <div role="tabpanel"
                                     class="tab-pane<?php if (!$this->input->get('tab') || $this->input->get('tab') == 'tab_content') {
                                         echo ' active';
                                     } ?>" id="tab_content">
                                    <div class="row">
                                        <?php if ($contract->signed == 1) { ?>
                                            <div class="col-md-12">
                                                <div class="alert alert-success">
                                                    <?php echo _l('document_signed_info', array(
                                                            '<b>' . $contract->acceptance_firstname . ' ' . $contract->acceptance_lastname . '</b> (<a href="mailto:' . $contract->acceptance_email . '">' . $contract->acceptance_email . '</a>)',
                                                            '<b>' . _dt($contract->acceptance_date) . '</b>',
                                                            '<b>' . $contract->acceptance_ip . '</b>')
                                                    ); ?>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        <div class="col-md-12 text-right _buttons">
                                            <div class="btn-group">
                                                <a href="#" class="btn btn-default dropdown-toggle"
                                                   data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i
                                                            class="fa fa-file-pdf-o"></i><?php if (is_mobile()) {
                                                        echo ' PDF';
                                                    } ?> <span class="caret"></span></a>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li class="hidden-xs"><a
                                                                href="<?php echo admin_url('contracts/pdf/' . $contract->id . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a>
                                                    </li>
                                                    <li class="hidden-xs"><a
                                                                href="<?php echo admin_url('contracts/pdf/' . $contract->id . '?output_type=I'); ?>"
                                                                target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a>
                                                    </li>
                                                    <li>
                                                        <a href="<?php echo admin_url('contracts/pdf/' . $contract->id); ?>"><?php echo _l('download'); ?></a>
                                                    </li>
                                                    <li>
                                                        <a href="<?php echo admin_url('contracts/pdf/' . $contract->id . '?print=true'); ?>"
                                                           target="_blank">
                                                            <?php echo _l('print'); ?>
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <a href="#" class="btn btn-default"
                                               data-target="#contract_send_to_client_modal" data-toggle="modal"><span
                                                        class="btn-with-tooltip" data-toggle="tooltip"
                                                        data-title="<?php echo _l('contract_send_to_email'); ?>"
                                                        data-placement="bottom">
                              <i class="fa fa-envelope"></i></span>
                                            </a>
                                            <div class="btn-group">
                                                <button type="button" class="btn btn-default pull-left dropdown-toggle"
                                                        data-toggle="dropdown" aria-haspopup="true"
                                                        aria-expanded="false">
                                                    <?php echo _l('more'); ?> <span class="caret"></span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-right">
                                                    <li>
                                                        <a href="<?php echo site_url('contract/' . $contract->id . '/' . $contract->hash); ?>"
                                                           target="_blank">
                                                            <?php echo _l('view_contract'); ?>
                                                        </a>
                                                    </li>
                                                    <?php if (has_permission('contracts', '', 'create')) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('contracts/copy/' . $contract->id); ?>">
                                                                <?php echo _l('contract_copy'); ?>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if ($contract->signed == 1 && has_permission('contracts', '', 'delete')) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('contracts/clear_signature/' . $contract->id); ?>"
                                                               class="_delete">
                                                                <?php echo _l('clear_signature'); ?>
                                                            </a>
                                                        </li>
                                                    <?php } ?>
                                                    <?php if (has_permission('contracts', '', 'delete')) { ?>
                                                        <li>
                                                            <a href="<?php echo admin_url('contracts/delete/' . $contract->id); ?>"
                                                               class="_delete">
                                                                <?php echo _l('delete'); ?></a>
                                                        </li>
                                                    <?php } ?>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <?php if (isset($contract_merge_fields)) { ?>
                                                <hr class="hr-panel-heading"/>
                                                <p class="bold mtop10 text-right"><a href="#"
                                                                                     onclick="slideToggle('.avilable_merge_fields'); return false;"><?php echo _l('available_merge_fields'); ?></a>
                                                </p>
                                                <div class=" avilable_merge_fields mtop15 hide">
                                                    <ul class="list-group">
                                                        <?php
                                                        foreach ($contract_merge_fields as $field) {
                                                            foreach ($field as $f) {
                                                                if (strpos($f['key'], 'statement_') === FALSE && strpos($f['key'], 'password') === FALSE && strpos($f['key'], 'email_signature') === FALSE) {
                                                                    echo '<li class="list-group-item"><b>' . $f['name'] . '</b>  <a href="#" class="pull-right" onclick="insert_merge_field(this); return false">' . $f['key'] . '</a></li>';
                                                                }
                                                            }
                                                        } ?>
                                                    </ul>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>
                                    <hr class="hr-panel-heading"/>
                                    <div class="editable tc-content"
                                         style="border:1px solid #d2d2d2;min-height:70px; border-radius:4px;">
                                        <?php
                                        if (empty($contract->content)) {
                                            echo do_action('new_contract_default_content', '<span class="text-danger text-uppercase mtop15 editor-add-content-notice"> ' . _l('click_to_add_content') . '</span>');
                                        } else {
                                            echo $contract->content;
                                        }
                                        ?>
                                    </div>
                                    <?php if (!empty($contract->signature)) { ?>
                                        <div class="row mtop25">
                                            <div class="col-md-6">
                                            </div>
                                            <div class="col-md-6 text-right">
                                                <p class="bold"><?php echo _l('document_customer_signature_text'); ?>
                                                    <?php if ($contract->signed == 1 && has_permission('contracts', '', 'delete')) { ?>
                                                        <a href="<?php echo admin_url('contracts/clear_signature/' . $contract->id); ?>"
                                                           data-toggle="tooltip"
                                                           title="<?php echo _l('clear_signature'); ?>"
                                                           class="_delete text-danger">
                                                            <i class="fa fa-remove"></i>
                                                        </a>
                                                    <?php } ?>
                                                </p>
                                                <img src="<?php echo site_url('download/preview_image?path=' . protected_file_url_by_path(get_upload_path_by_type('contract') . $contract->id . '/' . $contract->signature)); ?>"
                                                     alt="">
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="tab_notes">
                                    <?php echo form_open(admin_url('contracts/add_note/' . $contract->id), array('id' => 'sales-notes', 'class' => 'contract-notes-form')); ?>
                                    <?php echo render_textarea('description'); ?>
                                    <div class="text-right">
                                        <button type="submit"
                                                class="btn btn-info mtop15 mbot15"><?php echo _l('contract_add_note'); ?></button>
                                    </div>
                                    <?php echo form_close(); ?>
                                    <hr/>
                                    <div class="panel_s mtop20 no-shadow" id="sales_notes_area">
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="tab_comments">
                                    <div class="row contract-comments mtop15">
                                        <div class="col-md-12">
                                            <div id="contract-comments"></div>
                                            <div class="clearfix"></div>
                                            <textarea name="content" id="comment" rows="4"
                                                      class="form-control mtop15 contract-comment"></textarea>
                                            <button type="button" class="btn btn-info mtop10 pull-right"
                                                    onclick="add_contract_comment();"><?php echo _l('proposal_add_comment'); ?></button>
                                        </div>
                                    </div>
                                </div>
                                <div role="tabpanel"
                                     class="tab-pane<?php if ($this->input->get('tab') == 'attachments') {
                                         echo ' active';
                                     } ?>" id="attachments">
                                    <?php echo form_open(admin_url('contracts/add_contract_attachment/' . $contract->id), array('id' => 'contract-attachments-form', 'class' => 'dropzone')); ?>
                                    <?php echo form_close(); ?>
                                    <div class="text-right mtop15">
                                        <div id="dropbox-chooser"></div>
                                    </div>
                                    <div id="contract_attachments" class="mtop30">
                                        <?php
                                        $data = '<div class="row">';
                                        foreach ($contract->attachments as $attachment) {
                                            $href_url = site_url('download/file/contract/' . $attachment['attachment_key']);
                                            if (!empty($attachment['external'])) {
                                                $href_url = $attachment['external_link'];
                                            }
                                            $data .= '<div class="display-block contract-attachment-wrapper">';
                                            $data .= '<div class="col-md-10">';
                                            $data .= '<div class="pull-left"><i class="' . get_mime_class($attachment['filetype']) . '"></i></div>';
                                            $data .= '<a href="' . $href_url . '">' . $attachment['file_name'] . '</a>';
                                            $data .= '<p class="text-muted">' . $attachment["filetype"] . '</p>';
                                            $data .= '</div>';
                                            $data .= '<div class="col-md-2 text-right">';
                                            if ($attachment['staffid'] == get_staff_user_id() || is_admin()) {
                                                $data .= '<a href="#" class="text-danger" onclick="delete_contract_attachment(this,' . $attachment['id'] . '); return false;"><i class="fa fa fa-times"></i></a>';
                                            }
                                            $data .= '</div>';
                                            $data .= '<div class="clearfix"></div><hr/>';
                                            $data .= '</div>';
                                        }
                                        $data .= '</div>';
                                        echo $data;
                                        ?>
                                    </div>
                                </div>
                                <div role="tabpanel" class="tab-pane<?php if ($this->input->get('tab') == 'renewals') {
                                    echo ' active';
                                } ?>" id="renewals">
                                    <?php if (has_permission('contracts', '', 'create') || has_permission('contracts', '', 'edit')) { ?>
                                        <div class="_buttons">
                                            <a href="#" class="btn btn-default" data-toggle="modal"
                                               data-target="#renew_contract_modal">
                                                <i class="fa fa-refresh"></i> <?php echo _l('contract_renew_heading'); ?>
                                            </a>
                                        </div>
                                        <hr/>
                                    <?php } ?>
                                    <div class="clearfix"></div>
                                    <?php
                                    if (count($contract_renewal_history) == 0) {
                                        echo _l('no_contract_renewals_found');
                                    }
                                    foreach ($contract_renewal_history as $renewal) { ?>
                                        <div class="display-block">
                                            <div class="media-body">
                                                <div class="display-block">
                                                    <b>
                                                        <?php
                                                        echo _l('contract_renewed_by', $renewal['renewed_by']);
                                                        ?>
                                                    </b>
                                                    <?php if ($renewal['renewed_by_staff_id'] == get_staff_user_id() || is_admin()) { ?>
                                                        <a href="<?php echo admin_url('contracts/delete_renewal/' . $renewal['id'] . '/' . $renewal['contractid']); ?>"
                                                           class="pull-right _delete text-danger"><i
                                                                    class="fa fa-remove"></i></a>
                                                        <br/>
                                                    <?php } ?>
                                                    <small class="text-muted"><?php echo _dt($renewal['date_renewed']); ?></small>
                                                    <hr class="hr-10"/>
                                                    <span class="text-success bold" data-toggle="tooltip"
                                                          title="<?php echo _l('contract_renewal_old_start_date', _d($renewal['old_start_date'])); ?>">
                                 <?php echo _l('contract_renewal_new_start_date', _d($renewal['new_start_date'])); ?>
                                 </span>
                                                    <br/>
                                                    <?php if (is_date($renewal['new_end_date'])) {
                                                        $tooltip = '';
                                                        if (is_date($renewal['old_end_date'])) {
                                                            $tooltip = _l('contract_renewal_old_end_date', _d($renewal['old_end_date']));
                                                        }
                                                        ?>
                                                        <span class="text-success bold" data-toggle="tooltip"
                                                              title="<?php echo $tooltip; ?>">
                                 <?php echo _l('contract_renewal_new_end_date', _d($renewal['new_end_date'])); ?>
                                 </span>
                                                        <br/>
                                                    <?php } ?>
                                                    <?php if ($renewal['new_value'] > 0) {
                                                        $contract_renewal_value_tooltip = '';
                                                        if ($renewal['old_value'] > 0) {
                                                            $contract_renewal_value_tooltip = ' data-toggle="tooltip" data-title="' . _l('contract_renewal_old_value', _format_number($renewal['old_value'])) . '"';
                                                        } ?>
                                                        <span class="text-success bold"<?php echo $contract_renewal_value_tooltip; ?>>
                                 <?php echo _l('contract_renewal_new_value', _format_number($renewal['new_value'])); ?>
                                 </span>
                                                        <br/>
                                                    <?php } ?>
                                                </div>
                                            </div>
                                            <hr/>
                                        </div>
                                    <?php } ?>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
                                    <?php
                                    $this->load->view('admin/includes/emails_tracking', array(
                                            'tracked_emails' =>
                                                get_tracked_emails($contract->id, 'contract'))
                                    );
                                    ?>
                                </div>
                                <div role="tabpanel" class="tab-pane" id="tab_tasks">
                                    <?php init_relation_tasks_table(array('data-new-rel-id' => $contract->id, 'data-new-rel-type' => 'contract')); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
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
<?php init_tail(); ?>
<?php if (isset($contract)) { ?>
    <!-- init table tasks -->
    <script>
        var contract_id = '<?php echo $contract->id; ?>';
    </script>
    <?php $this->load->view('admin/contracts/send_to_client'); ?>
    <?php $this->load->view('admin/contracts/renew_contract'); ?>
<?php } ?>
<?php $this->load->view('admin/contracts/contract_type'); ?>
<script>
    var servicesCount = <?= $servicesCount ?>;
    Dropzone.autoDiscover = false;
    $(function () {

        if ($('#contract-attachments-form').length > 0) {
            new Dropzone("#contract-attachments-form", $.extend({}, _dropzone_defaults(), {
                success: function (file) {
                    if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                        var location = window.location.href;
                        window.location.href = location.split('?')[0] + '?tab=attachments';
                    }
                }
            }));
        }
        $("#contract_type").on('change', function () {
            console.log($('#contract_type option:selected').text());
            if ($('#contract_type option:selected').text() == 'Scheduled Service') {
                $(".amc_div").addClass('hidden');
                $(".scheduled_service_div").removeClass('hidden');
            } else if ($('#contract_type option:selected').text() == 'AMC') {
                $(".scheduled_service_div").addClass('hidden');
                $(".amc_div").removeClass('hidden');
            } else {
                $(".scheduled_service_div").addClass('hidden');
                $(".amc_div").addClass('hidden');
            }

            return false;
        });
        // In case user expect the submit btn to save the contract content
        $('#contract-form').on('submit', function () {
            $('#inline-editor-save-btn').click();
            return true;
        });


        if (typeof (Dropbox) != 'undefined' && $('#dropbox-chooser').length > 0) {
            document.getElementById("dropbox-chooser").appendChild(Dropbox.createChooseButton({
                success: function (files) {
                    $.post(admin_url + 'contracts/add_external_attachment', {
                        files: files,
                        contract_id: contract_id,
                        external: 'dropbox'
                    }).done(function () {
                        var location = window.location.href;
                        window.location.href = location.split('?')[0] + '?tab=attachments';
                    });
                },
                linkType: "preview",
                extensions: app_allowed_files.split(','),
            }));
        }
        $('#new-consumable-insert').on('click', function () {
            var consumable_title = $('#consumable_title').val();

            if (consumable_title != "") {
                $.post(admin_url + 'scheduled_services/add_type_of_consumable', {
                    title: consumable_title
                }).done(function () {
                    window.location.href = site_url+'/<?= $this->uri->uri_string(); ?>?consumable_modal=true';
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
        $('body').on('click', '.update-consumable-rows', function () {
            var tr = $(this).parents('tr');
            var machine_id = tr.attr('data-consumable-row-id');
            var consumable_title = tr.find('.consumable_type_edit input').val();

            if (consumable_title != "") {
                $.post(admin_url + 'scheduled_services/update_type_of_consumable/' + machine_id, {
                    title: consumable_title
                }).done(function () {
                    window.location.href =  site_url+'/<?= $this->uri->uri_string(); ?>?consumable_modal=true';
                });
            }
        });
        _validate_form($('#contract-form'), {
            client: 'required',
            datestart: 'required',
            subject: 'required'
        });
        _validate_form($('#renew-contract-form'), {
            new_start_date: 'required'
        });

        var _templates = [];
        $.each(contractsTemplates, function (i, template) {
            _templates.push({
                url: admin_url + 'contracts/get_template?name=' + template,
                title: template
            });
        });

        var editor_settings = {
            selector: 'div.editable',
            inline: true,
            theme: 'inlite',
            relative_urls: false,
            remove_script_host: false,
            inline_styles: true,
            verify_html: false,
            cleanup: false,
            apply_source_formatting: false,
            valid_elements: '+*[*]',
            valid_children: "+body[style], +style[type]",
            file_browser_callback: elFinderBrowser,
            table_default_styles: {
                width: '100%'
            },
            fontsize_formats: '8pt 10pt 12pt 14pt 18pt 24pt 36pt',
            pagebreak_separator: '<p pagebreak="true"></p>',
            plugins: [
                'advlist pagebreak autolink autoresize lists link image charmap hr',
                'searchreplace visualblocks visualchars code',
                'media nonbreaking table contextmenu',
                'paste textcolor colorpicker'
            ],
            autoresize_bottom_margin: 50,
            insert_toolbar: 'image media quicktable | bullist numlist | h2 h3 | hr',
            selection_toolbar: 'save_button bold italic underline superscript | forecolor backcolor link | alignleft aligncenter alignright alignjustify | fontselect fontsizeselect h2 h3',
            contextmenu: "image media inserttable | cell row column deletetable | paste pastetext searchreplace | visualblocks pagebreak charmap | code",
            setup: function (editor) {

                editor.addCommand('mceSave', function () {
                    save_contract_content(true);
                });

                editor.addShortcut('Meta+S', '', 'mceSave');

                editor.on('MouseLeave blur', function () {
                    if (tinymce.activeEditor.isDirty()) {
                        save_contract_content();
                    }
                });

                editor.on('MouseDown ContextMenu', function () {
                    if (!is_mobile() && !$('.left-column').hasClass('hide')) {
                        contract_full_view();
                    }
                });

                editor.on('blur', function () {
                    $.Shortcuts.start();
                });


                editor.on('focus', function () {
                    $.Shortcuts.stop();
                });

            }
        }

        if (_templates.length > 0) {
            editor_settings.templates = _templates;
            editor_settings.plugins[3] = 'template ' + editor_settings.plugins[3];
            editor_settings.contextmenu = editor_settings.contextmenu.replace('inserttable', 'inserttable template');
        }

        if (is_mobile()) {

            editor_settings.theme = 'modern';
            editor_settings.mobile = {};
            editor_settings.mobile.theme = 'mobile';
            editor_settings.mobile.toolbar = _tinymce_mobile_toolbar();

            editor_settings.inline = false;
            window.addEventListener("beforeunload", function (event) {
                if (tinymce.activeEditor.isDirty()) {
                    save_contract_content();
                }
            });
        }

        tinymce.init(editor_settings);

    });

    function ordinal_suffix_of(i) {
        var j = i % 10,
            k = i % 100;
        if (j == 1 && k != 11) {
            return i + "st";
        }
        if (j == 2 && k != 12) {
            return i + "nd";
        }
        if (j == 3 && k != 13) {
            return i + "rd";
        }
        return i + "th";
    }


    function save_contract_content(manual) {
        var editor = tinyMCE.activeEditor;
        var data = {};
        data.contract_id = contract_id;
        data.content = editor.getContent();
        $.post(admin_url + 'contracts/save_contract_data', data).done(function (response) {
            response = JSON.parse(response);
            if (typeof (manual) != 'undefined') {
                // Show some message to the user if saved via CTRL + S
                alert_float('success', response.message);
            }
            // Invokes to set dirty to false
            editor.save();
        }).fail(function (error) {
            var response = JSON.parse(error.responseText);
            alert_float('danger', response.message);
        });
    }

    function removeService(n){
        $("#service_row_"+n).remove();
        servicesCount = servicesCount - 1;
        return false;
    }

    function addNewServiceDiv() {
        servicesCount = servicesCount + 1;
        if(servicesCount > 12){
            alert_float('warning','Added maximum services.');
            servicesCount = 12;
            return false;
        }else {
            let html = '<div class="row" id="service_row_'+servicesCount+'">\n' +
                '<div class="col-md-3">\n' +
                '<div class="form-group">\n' +
                '<span for="sch_service' + servicesCount + '" id="sch_service' + servicesCount + '">' + ordinal_suffix_of(servicesCount) + ' Service <a href="javascript:void(0);" class="text-danger" onclick="removeService('+servicesCount+')">Remove</a>\n' +
                '<input type="hidden" name="services[' + servicesCount + '][service_val]" id="service_val' + servicesCount + '" value="' + servicesCount + '">\n' +
                '</div>\n' +
                '</div>\n' +
                '<div class="col-md-3">\n' +
                '<div class="form-group">\n' +
                '<input type="number" name="services[' + servicesCount + '][total_hours]" id="total_hours' + servicesCount + '" class="form-control numericField" placeholder="Total Hours" onChange="calculateDays(' + servicesCount + ', this.value, false)"></div>\n' +
                '</div>\n' +
                '<div class="col-md-3">\n' +
                '<div class="form-group">\n' +
                '<input type="text" name="services[' + servicesCount + '][no_of_days]" id="no_of_days' + servicesCount + '" class="form-control" onChange="calculateDays(' + servicesCount + ', this.value, true)" placeholder="No of Days">\n' +
                '</div>\n' +
                '</div>\n' +
                '<div class="col-md-3">\n' +
                '<div class="form-group">\n' +
                '<div class="input-group date">\n' +
                '<input type="text" id="estimate_date' + servicesCount + '" value="" name="services[' + servicesCount + '][estimate_date]" class="form-control datepicker" placeholder="Estimate Date">\n' +
                '<div class="input-group-addon">\n' +
                '<i class="fa fa-calendar calendar-icon"></i></div></div></div></div></div>';
            $('.more_service_div').append(html);
        }
        return true;
    }

    function delete_contract_attachment(wrapper, id) {
        if (confirm_delete()) {
            $.get(admin_url + 'contracts/delete_contract_attachment/' + id, function (response) {
                if (response.success == true) {
                    $(wrapper).parents('.contract-attachment-wrapper').remove();

                    var totalAttachmentsIndicator = $('.attachments-indicator');
                    var totalAttachments = totalAttachmentsIndicator.text().trim();
                    if (totalAttachments == 1) {
                        totalAttachmentsIndicator.remove();
                    } else {
                        totalAttachmentsIndicator.text(totalAttachments - 1);
                    }
                } else {
                    alert_float('danger', response.message);
                }
            }, 'json');
        }
        return false;
    }

    function insert_merge_field(field) {
        var key = $(field).text();
        tinymce.activeEditor.execCommand('mceInsertContent', false, key);
    }

    function contract_full_view() {
        $('.left-column').toggleClass('hide');
        $('.right-column').toggleClass('col-md-7');
        $('.right-column').toggleClass('col-md-12');
        $(window).trigger('resize');
    }

    function add_contract_comment() {
        var comment = $('#comment').val();
        if (comment == '') {
            return;
        }
        var data = {};
        data.content = comment;
        data.contract_id = contract_id;
        $('body').append('<div class="dt-loader"></div>');
        $.post(admin_url + 'contracts/add_comment', data).done(function (response) {
            response = JSON.parse(response);
            $('body').find('.dt-loader').remove();
            if (response.success == true) {
                $('#comment').val('');
                get_contract_comments();
            }
        });
    }

    function get_contract_comments() {
        if (typeof (contract_id) == 'undefined') {
            return;
        }
        requestGet('contracts/get_comments/' + contract_id).done(function (response) {
            $('#contract-comments').html(response);
            var totalComments = $('[data-commentid]').length;
            var commentsIndicator = $('.comments-indicator');
            if (totalComments == 0) {
                commentsIndicator.addClass('hide');
            } else {
                commentsIndicator.removeClass('hide');
                commentsIndicator.text(totalComments);
            }
        });
    }

    function remove_contract_comment(commentid) {
        if (confirm_delete()) {
            requestGetJSON('contracts/remove_comment/' + commentid).done(function (response) {
                if (response.success == true) {

                    var totalComments = $('[data-commentid]').length;

                    $('[data-commentid="' + commentid + '"]').remove();

                    var commentsIndicator = $('.comments-indicator');
                    if (totalComments - 1 == 0) {
                        commentsIndicator.addClass('hide');
                    } else {
                        commentsIndicator.removeClass('hide');
                        commentsIndicator.text(totalComments - 1);
                    }
                }
            });
        }
    }

    function edit_contract_comment(id) {
        var content = $('body').find('[data-contract-comment-edit-textarea="' + id + '"] textarea').val();
        if (content != '') {
            $.post(admin_url + 'contracts/edit_comment/' + id, {
                content: content
            }).done(function (response) {
                response = JSON.parse(response);
                if (response.success == true) {
                    alert_float('success', response.message);
                    $('body').find('[data-contract-comment="' + id + '"]').html(nl2br(content));
                }
            });
            toggle_contract_comment_edit(id);
        }
    }

    function toggle_contract_comment_edit(id) {
        $('body').find('[data-contract-comment="' + id + '"]').toggleClass('hide');
        $('body').find('[data-contract-comment-edit-textarea="' + id + '"]').toggleClass('hide');
    }

    function changeAmcEndDate(a) {
        let date = a.split('-');
        let nextDate = moment('"' + date[2] + '-' + date[1] + '-' + date[0] + '"').add(1, 'y').subtract(1, 'd').format('DD-MM-YYYY');
        $('#dateend').val(nextDate);
    }

    function calculateDays(count, hours, a = false) {
        let days = '';
        if (a) {
            days = parseInt(hours);
        } else {
            let hour = $("#avg_running_hr_per_day_amc_span").text();
            days = parseInt(hours) / parseInt(hour);
            $("#no_of_days" + count).val(parseInt(days));
        }
        let date = $("#datestart").val().split('-');
        let nextDate = moment('"' + date[2] + '-' + date[1] + '-' + date[0] + '"').add(days, 'days').subtract(1, 'd').format('DD-MM-YYYY');
        $("#estimate_date" + count).val(nextDate);
    }

    // Create new task directly from relation, related options selected after modal is shown
    function new_task_from_relation_service(table, rel_type, rel_id, service, count) {
        if (typeof (rel_type) == 'undefined' && typeof (rel_id) == 'undefined') {
            rel_id = $(table).data('new-rel-id');
            rel_type = $(table).data('new-rel-type');
        }
        let hour = $('#total_hours' + count).val();
        let due_date = $('#estimate_date' + count).val();
        let date = due_date.split('-');
        let start_date = moment('"' + date[2] + '-' + date[1] + '-' + date[0] + '"').subtract(7, 'days').format('DD-MM-YYYY');
        var url = admin_url + 'tasks/task?rel_id=' + rel_id + '&rel_type=' + rel_type + '&service_no=' + service + '&hour=' + hour + '&start_date=' + start_date + '&due_date=' + due_date;
        new_task(url);
    }
    <?php
    if (!isset($contract)){
        $value = (isset($item_details->installation_date) ? _d($item_details->installation_date) : _d(date('Y-m-d')));
        ?>
        changeAmcEndDate('<?= _d($value); ?>');
        <?php
    }
    ?>
</script>
</body>
</html>
