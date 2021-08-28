<div class="<?php if ($openEdit == true) {
    echo 'open-edit ';
} ?>lead-wrapper" <?php if (isset($lead) && ($lead->junk == 1 || $lead->lost == 1)) {
    echo 'lead-is-junk-or-lost';
} ?>>
    <?php if (isset($lead)) { ?>
        <div class="btn-group pull-left lead-actions-left">
            <a href="#" lead-edit class="mright10 font-medium-xs pull-left<?php if ($lead_locked == true) {
                echo ' hide';
            } ?>">
                <?php echo _l('edit'); ?>
                <i class="fa fa-pencil-square-o"></i>
            </a>
            <a href="#" class="font-medium-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
               aria-expanded="false" id="lead-more-btn">
                <?php echo _l('more'); ?>
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-left" id="lead-more-dropdown">
                <?php if ($lead->junk == 0) {
                    if ($lead->lost == 0 && (total_rows('tblclients', array('leadid' => $lead->id)) == 0)) { ?>
                        <li>
                            <a href="#" onclick="lead_mark_as_lost(<?php echo $lead->id; ?>); return false;">
                                <i class="fa fa-mars"></i>
                                <?php echo _l('lead_mark_as_lost'); ?>
                            </a>
                        </li>
                    <?php } else if ($lead->lost == 1) { ?>
                        <li>
                            <a href="#" onclick="lead_unmark_as_lost(<?php echo $lead->id; ?>); return false;">
                                <i class="fa fa-smile-o"></i>
                                <?php echo _l('lead_unmark_as_lost'); ?>
                            </a>
                        </li>
                    <?php } ?>
                <?php } ?>
                <!-- mark as junk -->
                <?php if ($lead->lost == 0) {
                    if ($lead->junk == 0 && (total_rows('tblclients', array('leadid' => $lead->id)) == 0)) { ?>
                        <li>
                            <a href="#" onclick="lead_mark_as_junk(<?php echo $lead->id; ?>); return false;">
                                <i class="fa fa fa-times"></i>
                                <?php echo _l('lead_mark_as_junk'); ?>
                            </a>
                        </li>
                    <?php } else if ($lead->junk == 1) { ?>
                        <li>
                            <a href="#" onclick="lead_unmark_as_junk(<?php echo $lead->id; ?>); return false;">
                                <i class="fa fa-smile-o"></i>
                                <?php echo _l('lead_unmark_as_junk'); ?>
                            </a>
                        </li>
                    <?php } ?>
                <?php } ?>
                <?php if (((is_lead_creator($lead->id) || has_permission('leads', '', 'delete')) && $lead_locked == false) || is_admin()) { ?>
                    <li>
                        <a href="<?php echo admin_url('leads/delete/' . $lead->id); ?>"
                           class="text-danger delete-text _delete" data-toggle="tooltip" title="">
                            <i class="fa fa-remove"></i>
                            <?php echo _l('lead_edit_delete_tooltip'); ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        </div>
        <a data-toggle="tooltip" class="btn btn-default pull-right lead-print-btn lead-top-btn lead-view mleft5"
           onclick="print_lead_information(); return false;" data-placement="top" title="<?php echo _l('print'); ?>"
           href="#">
            <i class="fa fa-print"></i>
        </a>
        <?php
        $client = false;
        $convert_to_client_tooltip_email_exists = '';
        if (total_rows('tblcontacts', array('email' => $lead->email)) > 0 && total_rows('tblclients', array('leadid' => $lead->id)) == 0) {
            $convert_to_client_tooltip_email_exists = _l('lead_email_already_exists');
            $text = _l('lead_convert_to_client');
        } else if (total_rows('tblclients', array('leadid' => $lead->id))) {
            $client = true;
        } else {
            $text = _l('lead_convert_to_client');
        }
        ?>
        <?php if ($lead_locked == false) { ?>
            <div class="lead-edit<?php if (isset($lead)) {
                echo ' hide';
            } ?>">
                <button type="button" class="btn btn-info pull-right mleft5 lead-top-btn lead-save-btn"
                        onclick="document.getElementById('lead-form-submit').click();">
                    <?php echo _l('submit'); ?>
                </button>
            </div>
        <?php } ?>
        <?php if ($client && (has_permission('customers', '', 'view') || is_customer_admin(get_client_id_by_lead_id($lead->id)))) { ?>
            <a data-toggle="tooltip" class="btn btn-success pull-right lead-top-btn lead-view" data-placement="top"
               title="<?php echo _l('lead_converted_edit_client_profile'); ?>"
               href="<?php echo admin_url('clients/client/' . get_client_id_by_lead_id($lead->id)); ?>">
                <i class="fa fa-user-o"></i>
            </a>
        <?php } ?>
        <?php
        echo total_rows('tblclients', array('userid' => $lead->client_id));
        if ((total_rows('tblclients', array('userid' => $lead->client_id)) == 0) && total_rows('tblclients', array('leadid' => $lead->id)) == 0) { ?>
            <a href="#" data-toggle="tooltip" data-title="<?php echo $convert_to_client_tooltip_email_exists; ?>"
               class="btn btn-success pull-right lead-convert-to-customer lead-top-btn lead-view"
               onclick="convert_lead_to_customer(<?php echo $lead->id; ?>); return false;">
                <i class="fa fa-user-o"></i>
                <?php echo $text; ?>
            </a>
        <?php } ?>
    <?php } ?>
    <div class="clearfix no-margin"></div>

    <?php if (isset($lead)) { ?>

        <div class="row mbot15">
            <hr class="no-margin"/>
        </div>

        <div class="alert alert-warning hide mtop20" role="alert" id="lead_proposal_warning">
            <?php echo _l('proposal_warning_email_change', array(_l('lead_lowercase'), _l('lead_lowercase'), _l('lead_lowercase'))); ?>
            <hr/>
            <a href="#" onclick="update_all_proposal_emails_linked_to_lead(<?php echo $lead->id; ?>); return false;">
                <?php echo _l('update_proposal_email_yes'); ?>
            </a>
            <br/>
            <a href="#" onclick="init_lead_modal_data(<?php echo $lead->id; ?>); return false;">
                <?php echo _l('update_proposal_email_no'); ?>
            </a>
        </div>
    <?php } ?>
    <?php echo form_open_multipart((isset($lead) ? admin_url('leads/lead/' . $lead->id) : admin_url('leads/lead')), array('id' => 'lead_form', 'class' => 'dropzone dropzone-manual')); ?>
    <div class="row">
        <div class="lead-view<?php if (!isset($lead)) {
            echo ' hide';
        } ?>" id="leadViewWrapper">
            <div class="col-md-4 col-xs-12 lead-information-col">
                <div class="lead-info-heading">
                    <h4 class="no-margin font-medium-xs bold">
                        <?php echo _l('lead_info'); ?>
                    </h4>
                </div>
                <p class="text-muted lead-field-heading"><?php echo _l('lead_add_edit_assigned'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->assigned != 0 ? get_staff_full_name($lead->assigned) : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('lead_company'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->company != '' ? $lead->company : '-') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('lead_add_edit_name'); ?></p>
                <p class="bold font-medium-xs lead-name"><?php echo(isset($lead) && $lead->name != '' ? $lead->name : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('lead_title'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->title != '' ? $lead->title : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('lead_add_edit_email'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->email != '' ? '<a href="mailto:' . $lead->email . '">' . $lead->email . '</a>' : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('lead_add_edit_phonenumber'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->phonenumber != '' ? '<a href="tel:' . $lead->phonenumber . '">' . $lead->phonenumber . '</a>' : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('lead_address'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->address != '' ? $lead->address : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('lead_city'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->city != '' ? $lead->city : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('lead_state'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->state != '' ? $lead->state : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('lead_country'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->country != 0 ? get_country($lead->country)->short_name : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('lead_zip'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->zip != '' ? $lead->zip : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('lead_website'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->website != '' ? '<a href="' . maybe_add_http($lead->website) . '" target="_blank">' . $lead->website . '</a>' : '-') ?></p>
            </div>
            <div class="col-md-4 col-xs-12 lead-information-col">
                <div class="lead-info-heading">
                    <h4 class="no-margin font-medium-xs bold">
                        <?php echo _l('lead_general_info'); ?>
                    </h4>
                </div>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('Type Of Customer'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->type_of_customer != '' ? $lead->type_of_customer : '-') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('Customer Classification'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->customer_classification != '' ? $lead->customer_classification : '-') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('Cluster'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->cluster != '' ? $lead->cluster : '-') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('Industry'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->industry != '' ? $lead->industry : '-') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('lead_add_edit_status'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->status_name != '' ? $lead->status_name : '-') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('Companies Competing'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->companies_competing != '' ? $lead->companies_competing : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('lead_add_edit_source'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->source_name != 1 ? 'Part of Monthly Plan' : 'Out of Monthly Plan') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('Follow Up Date'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->date != '' ? '<span class="text-has-action" data-toggle="tooltip" data-title="' . _dt($lead->date) . '">' . time_ago($lead->date) . '</span>' : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('Month of Lead Closer'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->month_of_lead_closer != '' ? _d($lead->month_of_lead_closer) : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('Buy Back Potential'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->buy_back_potential == 1 ? 'Yes' : 'No') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('Buy Back Proposal'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->buy_back_proposal == 1 ? 'Yes' : 'No') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('CFM Required'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->cfm_required != '' ? $lead->cfm_required : '-') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('Pressure Required'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->pressure_required != '' ? $lead->pressure_required : '-') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('Machine Required'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->machine_required != '' ? $lead->machine_required : '-') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('Accessories'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->accessories != '' ? $lead->accessories : '-') ?></p>
                <?php if (get_option('disable_language') == 0) { ?>
                    <p class="text-muted lead-field-heading"><?php echo _l('localization_default_language'); ?></p>
                    <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->default_language != '' ? $lead->default_language : _l('system_default_string')) ?></p>
                <?php } ?>
                <p class="text-muted lead-field-heading"><?php echo _l('leads_dt_datecreated'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->dateadded != '' ? '<span class="text-has-action" data-toggle="tooltip" data-title="' . _dt($lead->dateadded) . '">' . time_ago($lead->dateadded) . '</span>' : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('leads_dt_last_contact'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->lastcontact != '' ? '<span class="text-has-action" data-toggle="tooltip" data-title="' . _dt($lead->lastcontact) . '">' . time_ago($lead->lastcontact) . '</span>' : '-') ?></p>
                <p class="text-muted lead-field-heading"><?php echo _l('lead_public'); ?></p>
                <p class="bold font-medium-xs mbot15">
                    <?php if (isset($lead)) {
                        if ($lead->is_public == 1) {
                            echo _l('lead_is_public_yes');
                        } else {
                            echo _l('lead_is_public_no');
                        }
                    } else {
                        echo '-';
                    }
                    ?>
                </p>
                <?php if (isset($lead) && $lead->from_form_id != 0) { ?>
                    <p class="text-muted lead-field-heading"><?php echo _l('web_to_lead_form'); ?></p>
                    <p class="bold font-medium-xs mbot15"><?php echo $lead->form_data->name; ?></p>
                <?php } ?>
            </div>
            <div class="col-md-4 col-xs-12 lead-information-col">
                <?php if (total_rows('tblcustomfields', array('fieldto' => 'leads', 'active' => 1)) > 0 && isset($lead)) { ?>
                    <div class="lead-info-heading">
                        <h4 class="no-margin font-medium-xs bold">
                            <?php echo _l('custom_fields'); ?>
                        </h4>
                    </div>
                    <?php
                    $custom_fields = get_custom_fields('leads');
                    foreach ($custom_fields as $field) {
                        $value = get_custom_field_value($lead->id, $field['id'], 'leads'); ?>
                        <p class="text-muted lead-field-heading no-mtop"><?php echo $field['name']; ?></p>
                        <p class="bold font-medium-xs"><?php echo($value != '' ? $value : '-') ?></p>
                    <?php } ?>
                <?php } ?>
            </div>
            <div class="col-md-4 col-xs-12 lead-information-col">
                <div class="lead-info-heading">
                    <h4 class="no-margin font-medium-xs bold">
                        <?php echo _l('Existing Models at Customer Site'); ?>
                    </h4>
                </div>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('Existing Model Company'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->existing_model_company != '' ? $lead->existing_model_company : '-') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('Existing Model & KW'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->existing_model_model != '' ? $lead->existing_model_model : '-') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('Oil Type'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->existing_model_oil_type == 1 ? "Oil Injected" : 'Oil Free') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('Running Hrs'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->existing_model_running_hrs != '' ? $lead->existing_model_running_hrs : '-') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('Loading Hrs'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->existing_model_loading_hrs != '' ? $lead->existing_model_loading_hrs : '-') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('AMC cost'); ?></p>
                <p class="bold font-medium-xs mbot15"><?php echo(isset($lead) && $lead->existing_model_amc_cost != '' ? format_money($lead->existing_model_amc_cost,$customer_currency->symbol) : '-') ?></p>
                <p class="text-muted lead-field-heading no-mtop"><?php echo _l('Consumables'); ?></p>
                <p class="bold font-medium-xs mbot15">
                    <?php
                    $consumables = isset($lead->consumable) ? explode(', ', $lead->consumable) : array();
                    $consumableLable = [];
                    foreach ($list_of_consumable as $consumable) {
                        if (in_array($consumable['id'], $consumables)) {
                            $consumableLable[] = $consumable['title'];
                        }
                        ?>
                    <?php }
                    echo implode(',<br>', $consumableLable);
                    ?>
            </div>
            <div class="clearfix"></div>
            <div class="col-md-12">
                <p class="text-muted lead-field-heading"><?php echo _l('lead_description'); ?></p>
                <p class="bold font-medium-xs"><?php echo(isset($lead) && $lead->description != '' ? $lead->description : '-') ?></p>
            </div>
            <div class="col-md-12">
                <p class="text-muted lead-field-heading"><?php echo _l('tags'); ?></p>
                <p class="bold font-medium-xs mbot10">
                    <?php
                    if (isset($lead)) {
                        $tags = get_tags_in($lead->id, 'lead');
                        if (count($tags) > 0) {
                            echo render_tags($tags);
                            echo '<div class="clearfix"></div>';
                        } else {
                            echo '-';
                        }
                    }
                    ?>
                </p>
                <?php if (isset($lead_profile_attachment) && !empty($lead_profile_attachment)) { ?>

                    <div class="display-block lead-attachment-wrapper">
                        <div class="col-md-10">
                            <!--<i class="<?php //echo get_mime_class($lead_profile_attachment->filetype); ?>"></i> <a href="<?php //echo site_url('download/file/lead_attachment/'.$lead_profile_attachment->id); ?>" target="_blank"><?php //echo $lead_profile_attachment->file_name; ?></a> -->


                            <?php
                            $path = get_upload_path_by_type('lead') . '/' . $lead_profile_attachment->rel_id . '/' . $lead_profile_attachment->file_name;
                            $href_url = site_url('download/file/lead_attachment/' . $lead_profile_attachment->id . '/' . $lead_profile_attachment->file_name);

                            $img_url = site_url('download/preview_image?path=' . protected_file_url_by_path($path, true) . '&type=' . $lead_profile_attachment->filetype); ?>


                            <div class="preview-image">
                                <a href="<?php echo $href_url; ?>" target="_blank" data-lightbox="task-attachment"
                                   class="">
                                    <img src="<?php echo $img_url; ?>" class="img img-responsive" width="200px"
                                         height="150px">
                                </a>
                            </div>

                        </div>

                        <?php if ($lead_profile_attachment->attachment_added_from == get_staff_user_id() || is_admin()) { ?>
                            <div class="col-md-2 text-right">
                                <a href="#" class="text-danger _delete"
                                   onclick="delete_lead_attachment(this,<?php echo $lead_profile_attachment->id; ?>, <?php echo $lead_profile_attachment->rel_id; ?>); return false;"><i
                                            class="fa fa fa-times"></i></a>
                            </div>
                        <?php } ?>
                        <div class="clearfix"></div>
                        <hr>
                    </div>
                <?php } else { ?>
                    <div class="col-md-12" id="add-profile-attachment-wrapper">
                    </div>
                <?php } ?>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="lead-edit<?php if (isset($lead)) {
            echo ' hide';
        } ?>">
            <div class="col-md-12">
                <?php if (!isset($lead)) { ?>
                    <div class="lead-select-date-contacted hide">
                        <?php echo render_datetime_input('custom_contact_date', 'lead_add_edit_datecontacted', '', array('data-date-end-date' => date('Y-m-d'))); ?>
                    </div>
                <?php } else { ?>
                    <?php echo render_datetime_input('lastcontact', 'leads_dt_last_contact', _dt($lead->lastcontact), array('data-date-end-date' => date('Y-m-d'))); ?>
                <?php } ?>
                <div class="checkbox-inline checkbox checkbox-primary<?php if (isset($lead)) {
                    echo ' hide';
                } ?><?php if (isset($lead) && (is_lead_creator($lead->id) || has_permission('leads', '', 'edit'))) {
                    echo ' lead-edit';
                } ?>">
                    <input type="checkbox" name="is_public" <?php if (isset($lead)) {
                        if ($lead->is_public == 1) {
                            echo 'checked';
                        }
                    }; ?> id="lead_public">
                    <label for="lead_public"><?php echo _l('lead_public'); ?></label>
                </div>
                <?php if (!isset($lead)) { ?>
                    <div class="checkbox-inline checkbox checkbox-primary">
                        <input type="checkbox" name="contacted_today" id="contacted_today" checked>
                        <label for="contacted_today"><?php echo _l('lead_add_edit_contacted_today'); ?></label>
                    </div>
                <?php } ?>
            </div>
            <div class="col-md-6">
                <?php
                $assigned_attrs = array();
                $selected = (isset($lead) ? $lead->assigned : get_staff_user_id());
                if (isset($lead)
                    && $lead->assigned == get_staff_user_id()
                    && $lead->addedfrom != get_staff_user_id()
                    && !is_admin($lead->assigned)
                    && !has_permission('leads', '', 'view')
                ) {
                    $assigned_attrs['disabled'] = true;
                }
                echo render_select('assigned', $members, array('staffid', array('firstname', 'lastname')), 'Sales Person Name', $selected, $assigned_attrs); ?>
            </div>
            <div class="col-md-6">
                <div class="f_client_id">
                    <div class="form-group select-placeholder">
                        <label for="clientid"
                               class="control-label"><?php echo _l('estimate_select_customer'); ?></label>
                        <select id="clientid" name="client_id" data-live-search="true" data-width="100%"
                                class="ajax-search<?php if (isset($lead) && empty($lead->clientid)) {
                                    echo ' customer-removed';
                                } ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <?php $selected = (isset($lead) ? $lead->client_id : '');
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
                </div>
            </div>
            <div class="col-md-6">
                <?php $value = (isset($lead) ? $lead->company : ''); ?>
                <?php echo render_input('company', 'lead_company', $value); ?>
                <?php $value = (isset($lead) ? $lead->name : ''); ?>
                <?php echo render_input('name', 'Customer Name', $value); ?>
                <?php $value = (isset($lead) ? $lead->title : ''); ?>
                <?php echo render_input('title', 'Customer Designation', $value); ?>
                <?php $value = (isset($lead) ? $lead->email : ''); ?>
                <?php echo render_input('email', 'Customer Email', $value); ?>
                <?php $value = (isset($lead) ? $lead->phonenumber : ''); ?>
                <?php echo render_input('phonenumber', 'Customer Contact', $value);
                if ((isset($lead) && empty($lead->website)) || !isset($lead)) {
                    $value = (isset($lead) ? $lead->website : '');
                    echo render_input('website', 'Customer Website', $value);
                } else { ?>
                    <div class="form-group">
                        <label for="website"><?php echo _l('Customer Website'); ?></label>
                        <div class="input-group">
                            <input type="text" name="website" id="website" value="<?php echo $lead->website; ?>"
                                   class="form-control">
                            <div class="input-group-addon">
                     <span>
                      <a href="<?php echo maybe_add_http($lead->website); ?>" target="_blank" tabindex="-1">
                        <i class="fa fa-globe"></i>
                      </a>
                    </span>
                            </div>
                        </div>
                    </div>
                <?php }
                ?>
            </div>
            <div class="col-md-6">
                <?php $value = (isset($lead) ? $lead->address : ''); ?>
                <?php echo render_textarea('address', 'lead_address', $value, array('rows' => 1, 'style' => 'height:36px;font-size:100%;')); ?>
                <?php $value = (isset($lead) ? $lead->city : ''); ?>
                <?php echo render_input('city', 'lead_city', $value); ?>
                <?php $value = (isset($lead) ? $lead->state : ''); ?>
                <?php echo render_input('state', 'lead_state', $value); ?>
                <?php
                $countries = get_all_countries();
                $customer_default_country = get_option('customer_default_country');
                $selected = (isset($lead) ? $lead->country : $customer_default_country);
                echo render_select('country', $countries, array('country_id', array('short_name')), 'lead_country', $selected, array('data-none-selected-text' => _l('dropdown_non_selected_tex')));
                ?>
                <?php $value = (isset($lead) ? $lead->zip : ''); ?>
                <?php echo render_input('zip', 'lead_zip', $value); ?>
                <?php if (get_option('disable_language') == 0) { ?>
                    <div class="form-group">
                        <label for="default_language"
                               class="control-label"><?php echo _l('localization_default_language'); ?></label>
                        <select name="default_language" data-live-search="true" id="default_language"
                                class="form-control selectpicker"
                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <option value=""><?php echo _l('system_default_string'); ?></option>
                            <?php foreach ($this->app->get_available_languages() as $language) {
                                $selected = '';
                                if (isset($lead)) {
                                    if ($lead->default_language == $language) {
                                        $selected = 'selected';
                                    }
                                }
                                ?>
                                <option value="<?php echo $language; ?>" <?php echo $selected; ?>><?php echo ucfirst($language); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                <?php } ?>
            </div>
            <div class="clearfix"></div>
            <hr class="no-mtop mbot15"/>
            <div class="col-md-4">
                <?php
                $typeOfCustomer = [
                    ["id" => "Cold call-New Companies", "name" => "Cold call-New Companies"],
                    ["id" => "Unresponsive Customers", "name" => "Unresponsive Customers"],
                    ["id" => "Consultants", "name" => "Consultants"],
                    ["id" => "Responsive Competition Customers", "name" => "Responsive Competition Customers"],
                    ["id" => "New Customer - first time user", "name" => "New Customer - first time user"],
                    ["id" => "Existing Customers", "name" => "Existing Customers"],
                ];
                $selected = (isset($lead) ? $lead->type_of_customer : '');
                echo render_select('type_of_customer', $typeOfCustomer, ['id', 'name'], 'Type Of Customer', $selected);
                ?>
            </div>
            <div class="col-md-4">
                <?php
                $customerClassifications = [
                    ["id" => "First time buyer", "name" => "First time buyer"],
                    ["id" => "Not Aware", "name" => "Not Aware"],
                    ["id" => "Unresponsive & Estranged", "name" => "Unresponsive & Estranged"],
                    ["id" => "Always unresponsive", "name" => "Always unresponsive"],
                    ["id" => "Lost to competitors", "name" => "Lost to competitors"],
                    ["id" => "100% Competition", "name" => "100% Competition"],
                    ["id" => "Lost to competition but won-back", "name" => "Lost to competition but won-back"],
                    ["id" => "Migrated from competition", "name" => "Migrated from competition"],
                    ["id" => "100% ELGI", "name" => "100% ELGI"],
                    ["id" => "No brand loyalty (Purchases all brands)", "name" => "No brand loyalty (Purchases all brands)"],
                ];
                $selected = (isset($lead) ? $lead->customer_classification : '');
                echo render_select('customer_classification', $customerClassifications, ['id', 'name'], 'Customer Classification', $selected);
                ?>
            </div>
            <div class="col-md-4">
                <?php
                $clusters = [
                    ["id" => "Process Budget", "name" => "Process Budget"],
                    ["id" => "Experience TCO", "name" => "Experience TCO"],
                    ["id" => "Experience Budget", "name" => "Experience Budget"],
                    ["id" => "Brand Buyers", "name" => "Brand Buyers"],
                    ["id" => "Relationship Buyers", "name" => "Relationship Buyers"],
                    ["id" => "OEM Marinated", "name" => "OEM Marinated"],
                    ["id" => "Reference Led Buyers", "name" => "Reference Led Buyers"],
                    ["id" => "EPC Buyers", "name" => "EPC Buyers"],
                    ["id" => "End-user Influenced buyer", "name" => "End-user Influenced buyer"],
                ];
                $selected = (isset($lead) ? $lead->cluster : '');
                echo render_select('cluster', $clusters, ['id', 'name'], 'Cluster', $selected);
                ?>
            </div>
            <div class="col-md-4">
                <?php
                $industries = [
                    ["id" => "Textile", "name" => "Textile"],
                    ["id" => "Auto OEM & Ancillaries", "name" => "Auto OEM & Ancillaries"],
                    ["id" => "Iron and Steel", "name" => "Iron and Steel"],
                    ["id" => "Food Processing", "name" => "Food Processing"],
                    ["id" => "Sortex - Rice", "name" => "Sortex - Rice"],
                    ["id" => "Sortex - Dal", "name" => "Sortex - Dal"],
                    ["id" => "Cashew", "name" => "Cashew"],
                    ["id" => "Power", "name" => "Power"],
                    ["id" => "Plastics", "name" => "Plastics"],
                    ["id" => "Foundry/Forgings", "name" => "Foundry/Forgings"],
                    ["id" => "Cement", "name" => "Cement"],
                    ["id" => "Pharmaceuticals", "name" => "Pharmaceuticals"],
                    ["id" => "Leather", "name" => "Leather"],
                    ["id" => "Packing", "name" => "Packing"],
                    ["id" => "Paper & Pulp", "name" => "Paper & Pulp"],
                    ["id" => "Printing", "name" => "Printing"],
                    ["id" => "Chemicals", "name" => "Chemicals"],
                    ["id" => "Rubber", "name" => "Rubber"],
                    ["id" => "FMCG", "name" => "FMCG"],
                    ["id" => "General Engineering", "name" => "General Engineering"],
                    ["id" => "Other", "name" => "Other"],
                ];
                $selected = (isset($lead) ? $lead->industry : '');
                echo render_select('industry', $industries, ['id', 'name'], 'Industry', $selected);
                ?>
            </div>
            <div class="col-md-4">
                <?php
                $selected = '';
                if (isset($lead)) {
                    $selected = $lead->status;
                } else if (isset($status_id)) {
                    $selected = $status_id;
                }
                foreach ($statuses as $key => $status) {
                    if ($status['isdefault'] == 1) {
                        $statuses[$key]['option_attributes'] = array('data-subtext' => _l('leads_converted_to_client'));
                    }
                }
                echo render_leads_status_select($statuses, $selected, 'lead_add_edit_status');
                ?>
            </div>
            <div class="col-md-12">
                <?php $value = (isset($lead) ? $lead->companies_competing : ''); ?>
                <?php echo render_textarea('companies_competing', 'Companies Competing', $value); ?>
            </div>
            <div class="clearfix"></div>
            <hr class="no-mtop mbot15"/>
            <div class="col-md-4">
                <label for="source" class="control-label clearfix">
                    <?php echo _l('Source of Company', '', false); ?>
                </label>
                <div class="row">
                    <div class="form-group col-sm-6">
                        <label for="part_of_monthly_plan" class="control-label clearfix">
                            <?php echo _l('Part of Monthly Plan', '', false); ?>
                        </label>
                        <div class="onoffswitch">
                            <input type="radio" name="source" id="part_of_monthly_plan" value="0"
                                   class="onoffswitch-checkbox" <?php if (isset($lead) && $lead->source == 0) {
                                echo 'checked';
                            } ?>>
                            <label class="onoffswitch-label" for="part_of_monthly_plan"></label>
                        </div>
                    </div>
                    <div class="form-group col-sm-6">
                        <label for="out_of_monthly_plan" class="control-label clearfix">
                            <?php echo _l('Out of Monthly Plan', '', false); ?>
                        </label>
                        <div class="onoffswitch">
                            <input type="radio" name="source" id="out_of_monthly_plan" value="1"
                                   class="onoffswitch-checkbox" <?php if (isset($lead) && $lead->source == 1) {
                                echo 'checked';
                            } ?>>
                            <label class="onoffswitch-label" for="out_of_monthly_plan"></label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <?php $value = (isset($lead) ? _d($lead->date) : _d(date('Y-m-d'))); ?>
                <?php echo render_date_input('date', 'Follow Up Date', $value); ?>
            </div>
            <div class="col-md-4">
                <?php $value = (isset($lead) ? $lead->month_of_lead_closer : ''); ?>
                <?php echo render_date_input('month_of_lead_closer', 'Month of Lead Closer', $value); ?>
            </div>

            <div class="col-md-12">
                <?php $value = (isset($lead) ? $lead->description : ''); ?>
                <?php echo render_textarea('description', 'lead_description', $value); ?>
            </div>
            <div class="clearfix"></div>
            <hr class="mtop5 mbot10"/>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="buy_back_potential" class="control-label clearfix">
                        <?php echo _l('Buy Back Potential', '', false); ?>
                    </label>
                    <div class="onoffswitch">
                        <input type="checkbox" name="buy_back_potential" id="buy_back_potential" value="1"
                               class="onoffswitch-checkbox" <?php if (isset($lead) && $lead->buy_back_potential == 1) {
                            echo 'checked';
                        } ?>>
                        <label class="onoffswitch-label" for="buy_back_potential"></label>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="form-group">
                    <label for="buy_back_proposal" class="control-label clearfix">
                        <?php echo _l('Buy Back Proposal', '', false); ?>
                    </label>
                    <div class="onoffswitch">
                        <input type="checkbox" name="buy_back_proposal" id="buy_back_proposal" value="1"
                               class="onoffswitch-checkbox" <?php if (isset($lead) && $lead->buy_back_proposal == 1) {
                            echo 'checked';
                        } ?>>
                        <label class="onoffswitch-label" for="buy_back_proposal"></label>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <?php $value = (isset($lead) ? $lead->cfm_required : ''); ?>
                <?php echo render_input('cfm_required', 'CFM Required', $value); ?>
            </div>
            <div class="col-md-4">
                <?php $value = (isset($lead) ? $lead->pressure_required : ''); ?>
                <?php echo render_input('pressure_required', 'Pressure Required', $value); ?>
            </div>
            <div class="col-md-4">
                <?php $value = (isset($lead) ? $lead->machine_required : ''); ?>
                <?php echo render_input('machine_required', 'Machine Required', $value); ?>
            </div>
            <div class="col-md-6">
                <?php $value = (isset($lead) ? $lead->accessories : ''); ?>
                <?php echo render_input('accessories', 'Accessories', $value); ?>
            </div>
            <div class="clearfix"></div>
            <hr class="mtop5 mbot10"/>
            <div class="col-md-12">
                <h4><b>Existing Models at customer site</b></h4>
                <div class="row">
                    <div class="col-md-4">
                        <?php $value = (isset($lead) ? $lead->existing_model_company : ''); ?>
                        <?php echo render_input('existing_model_company', 'Existing Model Company', $value); ?>
                    </div>
                    <div class="col-md-4">
                        <?php $value = (isset($lead) ? $lead->existing_model_model : ''); ?>
                        <?php echo render_input('existing_model_model', 'Existing Model & KW', $value); ?>
                    </div>
                    <div class="col-md-4">
                        <label for="existing_model_oil_type" class="control-label clearfix">
                            <b><?php echo _l('Oil Type', '', false); ?></b>
                        </label>
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label for="oil_injected" class="control-label clearfix">
                                    <?php echo _l('Oil Injected', '', false); ?>
                                </label>
                                <div class="onoffswitch">
                                    <input type="radio" name="existing_model_oil_type" id="oil_injected" value="0"
                                           class="onoffswitch-checkbox" <?php if (isset($lead) && $lead->existing_model_oil_type == 0) {
                                        echo 'checked';
                                    } ?>>
                                    <label class="onoffswitch-label" for="oil_injected"></label>
                                </div>
                            </div>
                            <div class="form-group col-sm-6">
                                <label for="oil_free" class="control-label clearfix">
                                    <?php echo _l('Oil Free', '', false); ?>
                                </label>
                                <div class="onoffswitch">
                                    <input type="radio" name="existing_model_oil_type" id="oil_free" value="1"
                                           class="onoffswitch-checkbox" <?php if (isset($lead) && $lead->existing_model_oil_type == 1) {
                                        echo 'checked';
                                    } ?>>
                                    <label class="onoffswitch-label" for="oil_free"></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="col-md-2">
                        <?php $value = (isset($lead) ? $lead->existing_model_running_hrs : ''); ?>
                        <?php echo render_input('existing_model_running_hrs', 'Running Hrs', $value); ?>
                    </div>
                    <div class="col-md-2">
                        <?php $value = (isset($lead) ? $lead->existing_model_loading_hrs : ''); ?>
                        <?php echo render_input('existing_model_loading_hrs', 'Loading Hrs', $value); ?>
                    </div>
                    <div class="col-md-2">
                        <?php $value = (isset($lead) ? $lead->existing_model_amc_cost : ''); ?>
                        <?php echo render_input('existing_model_amc_cost', 'AMC cost', $value); ?>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="consumable"><?php echo _l('Consumables'); ?></label>
                            <select name="consumable[]" class="selectpicker" multiple data-width="100%"
                                    data-none-selected-text="<?php echo _l('Select Consumable'); ?>">
                                <?php
                                $consumables = isset($lead->consumable) ? explode(', ', $lead->consumable) : array();
                                foreach ($list_of_consumable as $consumable) { ?>
                                    <option value="<?php echo $consumable['id']; ?>"
                                        <?= in_array($consumable['id'], $consumables) ? "selected='selected'" : "" ?>><?php echo $consumable['title']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 mtop15">
                <?php $rel_id = (isset($lead) ? $lead->id : false); ?>
                <?php echo render_custom_fields('leads', $rel_id); ?>
            </div>
            <div class="clearfix"></div>
            <hr class="mtop5 mbot10"/>

            <?php if (isset($lead_profile_attachment) && !empty($lead_profile_attachment)) { ?>

                <div class="display-block lead-attachment-wrapper" id="lead-profile-attachment-wrapper">
                    <div class="col-md-10">


                        <?php
                        $path = get_upload_path_by_type('lead') . '/' . $lead_profile_attachment->rel_id . '/' . $lead_profile_attachment->file_name;
                        $href_url = site_url('download/file/lead_attachment/' . $lead_profile_attachment->id . '/' . $lead_profile_attachment->file_name);

                        $img_url = site_url('download/preview_image?path=' . protected_file_url_by_path($path, true) . '&type=' . $lead_profile_attachment->filetype); ?>


                        <div class="preview-image">
                            <a href="<?php echo $href_url; ?>" target="_blank" data-lightbox="task-attachment" class="">
                                <img src="<?php echo $img_url; ?>" class="img img-responsive" width="200px"
                                     height="150px">
                            </a>
                        </div>

                    </div>

                    <?php if ($lead_profile_attachment->attachment_added_from == get_staff_user_id() || is_admin()) { ?>
                        <div class="col-md-2 text-right">
                            <a href="#" class="text-danger _delete"
                               onclick="delete_lead_attachment(this,<?php echo $lead_profile_attachment->id; ?>, <?php echo $lead_profile_attachment->rel_id; ?>); return false;"><i
                                        class="fa fa fa-times"></i></a>
                        </div>
                    <?php } ?>
                    <div class="clearfix"></div>
                    <hr>
                </div>
            <?php } else { ?>


                <div class="col-md-12" id="add-profile-attachment-wrapper">
                    <div id="dropzoneDragArea" class="dz-default dz-message">
                        <span><?php echo _l('lead_attach_image'); ?></span>
                    </div>
                    <div class="dropzone-previews"></div>
                </div>
            <?php } ?>
            <div class="clearfix"></div>
            <hr class="mtop5 mbot10"/>
            <div class="col-md-12">
                <div class="form-group no-mbot" id="inputTagsWrapper">
                    <label for="tags" class="control-label"><i class="fa fa-tag"
                                                               aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                    <input type="text" class="tagsinput" id="tags" name="tags"
                           value="<?php echo(isset($lead) ? prep_tags_input(get_tags_in($lead->id, 'lead')) : ''); ?>"
                           data-role="tagsinput">
                </div>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <?php if (isset($lead)) { ?>
        <div class="lead-latest-activity lead-view">
            <div class="lead-info-heading">
                <h4 class="no-margin bold font-medium-xs"><?php echo _l('lead_latest_activity'); ?></h4>
            </div>
            <div id="lead-latest-activity" class="pleft5"></div>
        </div>
    <?php } ?>
    <?php if ($lead_locked == false) { ?>
        <div class="lead-edit<?php if (isset($lead)) {
            echo ' hide';
        } ?>">
            <hr/>
            <button type="submit" class="btn btn-info pull-right lead-save-btn"
                    id="lead-form-submit"><?php echo _l('submit'); ?></button>
            <button type="button" class="btn btn-default pull-right mright5"
                    data-dismiss="modal"><?php echo _l('close'); ?></button>
        </div>
    <?php } ?>
    <div class="clearfix"></div>
    <?php echo form_close(); ?>
</div>
<?php if (isset($lead) && $lead_locked == true) { ?>
    <script>
        $(function () {
            // Set all fields to disabled if lead is locked
            var lead_fields = $('.lead-wrapper').find('input, select, textarea');
            $.each(lead_fields, function () {
                $(this).attr('disabled', true);
                if ($(this).is('select')) {
                    $(this).selectpicker('refresh');
                }
            });
        });
    </script>

<?php } ?>
<script>
    Dropzone.options.expenseForm = false;
    var leadProfileDropzone;
    $(function () {
        if ($('#dropzoneDragArea').length > 0) {
            leadProfileDropzone = new Dropzone("#lead_form", $.extend({}, _dropzone_defaults(), {
                autoProcessQueue: false,
                clickable: '#dropzoneDragArea',
                previewsContainer: '.dropzone-previews',
                addRemoveLinks: true,
                maxFiles: 1,
                success: function (file, response) {
                    response = JSON.parse(response);
                    if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                        window.location.assign(response.url);
                    }
                },
            }));
        }

        if ($('input[name="leadid"]').length > 0 && ($('input[name="leadid"]').val())) {
            if ($("#lead-profile-attachment-wrapper").length == 0) {
                //alert("her1e");
                var lead_id = $('input[name="leadid"]').val();
                myVar = setTimeout(function () {
                    $.ajax({
                        async: true,
                        url: admin_url + "leads/get_lead_profile_attachment/" + lead_id,
                        success: function (result) {
                            result = JSON.parse(result);
                            //console.log(result);
                            if (result != '') {
                                $("#add-profile-attachment-wrapper").html(result.result);
                                //console.log(result.result);
                            }
                        }
                    });

                }, 5000);


                // $.get(admin_url + "leads/get_lead_profile_attachment/" + lead_id).done(function(result) {
                // result = JSON.parse(result);
                //console.log(result);
                // if(result !=''){
                // $("#add-profile-attachment-wrapper").html(result.result);
                // console.log(result.result);
                // }

                // });
            }
            //alert("here");
        }
        init_ajax_search("customer", "#clientid.ajax-search");
        $("body").on("change", '.f_client_id select[name="client_id"]', function () {
            var e = $(this).val(),
                clientUrl = "invoices/client_change_data/";
            requestGetJSON(clientUrl + e).done(function (e) {
                let customerDetails = e.customer_details;
                $('input[name="company"]').val(customerDetails.company);
                $('input[name="phonenumber"]').val(customerDetails.phonenumber);
                $('input[name="website"]').val(customerDetails.website);
                $('textarea[name="address"]').val(customerDetails.address);
                $('input[name="city"]').val(customerDetails.city);
                $('input[name="state"]').val(customerDetails.state);
                $('input[name="zip"]').val(customerDetails.zip);
            })
        });
    });
</script>