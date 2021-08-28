<!-- Modal Contact -->
<div class="modal fade" id="new_products" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/clients/product/' . $client->userid, array('id' => 'product-form', 'autocomplete' => 'off')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $title; ?><br/><small class="color-white"
                                                                                           id=""><?php echo get_company_name($client->userid, true); ?></small>
                </h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <?php
                        function render_division_select($statuses, $selected = '', $lang_key = '', $name = 'status', $select_attrs = [])
                        {
                            if (is_admin() || get_option('staff_members_create_inline_lead_status') == '1') {
                                return render_select_with_input_group($name, $statuses, ['id', 'name'], $lang_key, $selected, '<a href="#" onclick="new_lead_status_inline();return false;" class="inline-field-new"><i class="fa fa-plus"></i></a>', $select_attrs);
                            }

                            return render_select($name, $statuses, ['id', 'name'], $lang_key, $selected, $select_attrs);
                        }

                        ?>
                        <?php echo form_hidden('customerId', $client->userid); ?>
                        <?php echo form_hidden('invoice_no'); ?>
                        <?php echo form_hidden('id'); ?>
                        <?php $value = (isset($contact) ? $contact->firstname : ''); ?>
                        <?php echo render_input('invoice_custom', 'Invoice', $value); ?>
                        <?php $value = (isset($contact) ? $contact->firstname : ''); ?>
                        <?php echo render_input('description', 'TPL / Part No', $value); ?>
                        <?php $value = (isset($contact) ? $contact->lastname : ''); ?>
                        <?php echo render_textarea('long_description', 'Long Description', $value); ?>
                        <?php $value = (isset($contact) ? $contact->title : ''); ?>
                        <?php echo render_input('fab_no', 'Fab No', $value); ?>
                        <div id="custom_fields_items">
                            <?php echo render_custom_fields('items'); ?>
                        </div>
                        <?php /*$selected = (isset($contact) ? $contact->title : ''); */?><!--
                        <?php /*echo render_division_select($division_master, $selected, 'Divisions', 'divisions', array('data-ays-ignore' => true)); */?>
                        <div class="form-group" app-field-wrapper="invoice_custom">
                            <label for="invoice_custom" class="control-label">Avg. running Hour/Day</label>
                            <input type="text" id="avg_running_hour_day" name="avg_running_hour_day" onchange="changeAvgCalculation(true)" onblur="changeAvgCalculation()" class="form-control" value="">
                        </div>
                        <div class="form-group" app-field-wrapper="invoice_custom">
                            <label for="invoice_custom" class="control-label">Avg. running Hour/Year</label>
                            <input type="text" id="avg_running_hour_year" name="avg_running_hour_year" onchange="changeAvgCalculation(false)" onblur="changeAvgCalculation()" class="form-control" value="">
                        </div>-->
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info" data-loading-text="<?php echo _l('wait_text'); ?>" autocomplete="off" data-form="#product-form"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
