<div class="panel_s invoice accounting-template">
    <div class="additional"></div>
    <div class="panel-body">
        <?php
        if (isset($invoice) && $invoice->devide_gst == 1) {
            echo form_hidden('devide_gst', "yes");
        } else {
            echo form_hidden('devide_gst', "no");
        }
        ?>
        <?php if (isset($invoice)) { ?>
            <?php echo format_delivery_challan_status($invoice->status); ?>
            <hr class="hr-panel-heading"/>
        <?php } ?>
        <?php do_action('before_render_invoice_template'); ?>
        <div class="row">
            <div class="col-md-6">
                <div class="f_client_id">
                    <div class="form-group select-placeholder">
                        <label for="clientid" class="control-label"><?php echo _l('invoice_select_customer'); ?></label>
                        <select id="clientid" name="clientid" data-live-search="true" data-width="100%"
                                class="ajax-search<?php if (isset($invoice) && empty($invoice->clientid)) {
                                    echo ' customer-removed';
                                } ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <?php $selected = (isset($invoice) ? $invoice->clientid : '');
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
                <?php
                if (!isset($invoice_from_project)) { ?>
                    <div class="form-group select-placeholder projects-wrapper<?php if ((!isset($invoice)) || (isset($invoice) && !customer_has_projects($invoice->clientid))) {
                        echo ' hide';
                    } ?>">
                        <label for="project_id"><?php echo _l('project'); ?></label>
                        <div id="project_ajax_search_wrapper">
                            <select name="project_id" id="project_id" class="projects ajax-search"
                                    data-live-search="true" data-width="100%"
                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                <?php
                                if (isset($invoice) && $invoice->project_id != 0) {
                                    echo '<option value="' . $invoice->project_id . '" selected>' . get_project_name_by_id($invoice->project_id) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                <?php } ?>
                <div class="row">
                    <div class="col-md-12">
                        <hr class="hr-10"/>
                        <a href="#" class="edit_shipping_billing_info" data-toggle="modal"
                           data-target="#billing_and_shipping_details"><i class="fa fa-pencil-square-o"></i></a>
                        <?php include_once(APPPATH . 'views/admin/delivery_challan/billing_and_shipping_template.php'); ?>
                    </div>
                    <div class="col-md-6">
                        <p class="bold"><?php echo _l('invoice_bill_to'); ?></p>
                        <address>
              <span class="billing_street">
                <?php $billing_street = (isset($invoice) ? $invoice->billing_street : '--'); ?>
                <?php $billing_street = ($billing_street == '' ? '--' : $billing_street); ?>
                <?php echo $billing_street; ?></span><br>
                            <span class="billing_city">
                <?php $billing_city = (isset($invoice) ? $invoice->billing_city : '--'); ?>
                <?php $billing_city = ($billing_city == '' ? '--' : $billing_city); ?>
                <?php echo $billing_city; ?></span>,
                            <span class="billing_state">
                <?php $billing_state = (isset($invoice) ? $invoice->billing_state : '--'); ?>
                <?php $billing_state = ($billing_state == '' ? '--' : $billing_state); ?>
                <?php echo $billing_state; ?></span>
                            <br/>
                            <span class="billing_country">
                <?php $billing_country = (isset($invoice) ? get_country_short_name($invoice->billing_country) : '--'); ?>
                <?php $billing_country = ($billing_country == '' ? '--' : $billing_country); ?>
                <?php echo $billing_country; ?></span>,
                            <span class="billing_zip">
                <?php $billing_zip = (isset($invoice) ? $invoice->billing_zip : '--'); ?>
                <?php $billing_zip = ($billing_zip == '' ? '--' : $billing_zip); ?>
                <?php echo $billing_zip; ?></span>
                        </address>
                    </div>
                    <div class="col-md-6">
                        <p class="bold"><?php echo _l('ship_to'); ?></p>
                        <address>
              <span class="shipping_street">
                <?php $shipping_street = (isset($invoice) ? $invoice->shipping_street : '--'); ?>
                <?php $shipping_street = ($shipping_street == '' ? '--' : $shipping_street); ?>
                <?php echo $shipping_street; ?></span><br>
                            <span class="shipping_city">
                <?php $shipping_city = (isset($invoice) ? $invoice->shipping_city : '--'); ?>
                <?php $shipping_city = ($shipping_city == '' ? '--' : $shipping_city); ?>
                <?php echo $shipping_city; ?></span>,
                            <span class="shipping_state">
                <?php $shipping_state = (isset($invoice) ? $invoice->shipping_state : '--'); ?>
                <?php $shipping_state = ($shipping_state == '' ? '--' : $shipping_state); ?>
                <?php echo $shipping_state; ?></span>
                            <br/>
                            <span class="shipping_country">
                <?php $shipping_country = (isset($invoice) ? get_country_short_name($invoice->shipping_country) : '--'); ?>
                <?php $shipping_country = ($shipping_country == '' ? '--' : $shipping_country); ?>
                <?php echo $shipping_country; ?></span>,
                            <span class="shipping_zip">
                <?php $shipping_zip = (isset($invoice) ? $invoice->shipping_zip : '--'); ?>
                <?php $shipping_zip = ($shipping_zip == '' ? '--' : $shipping_zip); ?>
                <?php echo $shipping_zip; ?></span>
                        </address>
                    </div>
                </div>
                <?php
                $next_invoice_number = get_option('next_delivery_challan_number');
                $format = get_option('delivery_challan_number_format');
                if (isset($invoice)) {
                    $format = $invoice->number_format;
                }
                $prefix = get_option('delivery_challan_prefix');
                if ($format == 1) {
                    $__number = $next_invoice_number;
                    if (isset($invoice)) {
                        $__number = $invoice->number;
                        $prefix = '<span id="prefix">' . $invoice->prefix . '</span>';
                    }
                } else if ($format == 2) {
                    if (isset($invoice)) {
                        $__number = $invoice->number;
                        $prefix = $invoice->prefix;
                        $prefix = '<span id="prefix">' . $prefix . '</span><span id="prefix_year">' . date('Y', strtotime($invoice->date)) . '</span>/';
                    } else {
                        $__number = $next_invoice_number;
                        $prefix = $prefix . '<span id="prefix_year">' . date('Y') . '</span>/';
                    }
                } else if ($format == 3) {
                    if (isset($invoice)) {
                        $yy = date('y', strtotime($invoice->date));
                        $__number = $invoice->number;
                        $prefix = '<span id="prefix">' . $invoice->prefix . '</span>';
                    } else {
                        $yy = date('y');
                        $__number = $next_invoice_number;
                    }
                } else if ($format == 4) {
                    if (isset($invoice)) {
                        $yyyy = date('Y', strtotime($invoice->date));
                        $mm = date('m', strtotime($invoice->date));
                        $__number = $invoice->number;
                        $prefix = '<span id="prefix">' . $invoice->prefix . '</span>';
                    } else {
                        $yyyy = date('Y');
                        $mm = date('m');
                        $__number = $next_invoice_number;
                    }
                }
                $_invoice_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
                $isedit = isset($invoice) ? 'true' : 'false';
                $data_original_number = isset($invoice) ? $invoice->number : 'false';
                ?>
                <div class="form-group">
                    <label for="number"><?php echo _l('DC Number'); ?></label>
                    <div class="input-group">
            <span class="input-group-addon">
              <?php if (isset($invoice)) { ?>
                  <a href="#" onclick="return false;" data-toggle="popover" data-container='._transaction_form'
                     data-html="true"
                     data-content="<label class='control-label'><?php echo _l('settings_sales_invoice_prefix'); ?></label><div class='input-group'><input name='s_prefix' type='text' class='form-control' value='<?php echo $invoice->prefix; ?>'></div><button type='button' onclick='save_sales_number_settings(this); return false;' data-url='<?php echo admin_url('delivery_challan/update_number_settings/' . $invoice->id); ?>' class='btn btn-info btn-block mtop15'><?php echo _l('submit'); ?></button>">
              <i class="fa fa-cog"></i>
            </a>
              <?php }
              echo $prefix;
              ?>
          </span>
                        <input type="text" name="number" class="form-control" value="<?php echo $_invoice_number; ?>"
                               data-isedit="<?php echo $isedit; ?>"
                               data-original-number="<?php echo $data_original_number; ?>">
                        <?php if ($format == 3) { ?>
                            <span class="input-group-addon">
            <span id="prefix_year" class="format-n-yy"><?php echo $yy; ?></span>
          </span>
                        <?php } else if ($format == 4) { ?>
                            <span class="input-group-addon">
            <span id="prefix_month" class="format-mm-yyyy"><?php echo $mm; ?></span>
            /
            <span id="prefix_year" class="format-mm-yyyy"><?php echo $yyyy; ?></span>
          </span>
                        <?php } if (!isset($invoice)) {
                            ?>
                            <span class="input-group-addon">
                      <a class="btn btn-default btn-default-dt-options btn-dt-reload update_number"
                         href="javascript:void(0)" data-toggle="tooltip" title=""
                         data-original-title="Get latest number"><span><i class="fa fa-refresh"></i></span></a>
                  </span>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <?php $value = (isset($invoice) ? _d($invoice->date) : _d(date('Y-m-d')));
                        $date_attrs = array();
                        ?>
                        <?php echo render_date_input('date', 'invoice_add_edit_date', $value, $date_attrs); ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                        $value = '';
                        if (isset($invoice)) {
                            $value = _d($invoice->duedate);
                        } else {
                            if (get_option('invoice_due_after') != 0) {
                                $value = _d(date('Y-m-d', strtotime('+' . get_option('delivery_challan_due_after') . ' DAY', strtotime(date('Y-m-d')))));
                            }
                        }
                        ?>
                        <?php echo render_date_input('duedate', 'invoice_add_edit_duedate', $value); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="panel_s no-shadow">
                    <div class="form-group">
                        <label for="tags" class="control-label"><i class="fa fa-tag"
                                                                   aria-hidden="true"></i> <?php echo _l('tags'); ?>
                        </label>
                        <input type="text" class="tagsinput" id="tags" name="tags"
                               value="<?php echo(isset($invoice) ? prep_tags_input(get_tags_in($invoice->id, 'invoice')) : ''); ?>"
                               data-role="tagsinput">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            $s_attrs = array('disabled' => true, 'data-show-subtext' => true);
                            $s_attrs = do_action('invoice_currency_disabled', $s_attrs);
                            foreach ($currencies as $currency) {
                                if ($currency['isdefault'] == 1) {
                                    $s_attrs['data-base'] = $currency['id'];
                                }
                                if (isset($invoice)) {
                                    if ($currency['id'] == $invoice->currency) {
                                        $selected = $currency['id'];
                                    }
                                } else {
                                    if ($currency['isdefault'] == 1) {
                                        $selected = $currency['id'];
                                    }
                                }
                            }
                            ?>
                            <?php echo render_select('currency', $currencies, array('id', 'name', 'symbol'), 'invoice_add_edit_currency', $selected, $s_attrs); ?>
                        </div>
                        <div class="col-md-6">
                            <?php
                            $i = 0;
                            $selected = '';
                            foreach ($staff as $member) {
                                if (isset($invoice)) {
                                    if ($invoice->sale_agent == $member['staffid']) {
                                        $selected = $member['staffid'];
                                    }
                                } elseif (get_staff_user_id() == $member['staffid']) {
                                    $selected = $member['staffid'];
                                }
                                $i++;
                            }
                            echo render_select('sale_agent', $staff, array('staffid', array('firstname', 'lastname')), 'sale_agent_string', $selected);
                            ?>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group select-placeholder">
                                <label for="discount_type"
                                       class="control-label"><?php echo _l('discount_type'); ?></label>
                                <select name="discount_type" class="selectpicker" data-width="100%"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value=""><?php echo _l('no_discount'); ?></option>
                                    <option selected value="before_tax" <?php
                                    if (isset($invoice)) {
                                        if ($invoice->discount_type == 'before_tax') {
                                            echo 'selected';
                                        }
                                    } ?>><?php echo _l('discount_type_before_tax'); ?></option>
                                    <!-- <option value="after_tax" <?php if (isset($invoice)) {
                                        if ($invoice->discount_type == 'after_tax') {
                                            echo 'selected';
                                        }
                                    } ?>><?php echo _l('discount_type_after_tax'); ?></option> -->
                                </select>
                            </div>
                        </div>
                        <div class="recurring_custom <?php if ((isset($invoice) && $invoice->custom_recurring != 1) || (!isset($invoice))) {
                            echo 'hide';
                        } ?>">
                            <div class="col-md-6">
                                <?php $value = (isset($invoice) && $invoice->custom_recurring == 1 ? $invoice->recurring : 1); ?>
                                <?php echo render_input('repeat_every_custom', '', $value, 'number', array('min' => 1)); ?>
                            </div>
                            <div class="col-md-6">
                                <select name="repeat_type_custom" id="repeat_type_custom" class="selectpicker"
                                        data-width="100%"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value="day" <?php if (isset($invoice) && $invoice->custom_recurring == 1 && $invoice->recurring_type == 'day') {
                                        echo 'selected';
                                    } ?>><?php echo _l('invoice_recurring_days'); ?></option>
                                    <option value="week" <?php if (isset($invoice) && $invoice->custom_recurring == 1 && $invoice->recurring_type == 'week') {
                                        echo 'selected';
                                    } ?>><?php echo _l('invoice_recurring_weeks'); ?></option>
                                    <option value="month" <?php if (isset($invoice) && $invoice->custom_recurring == 1 && $invoice->recurring_type == 'month') {
                                        echo 'selected';
                                    } ?>><?php echo _l('invoice_recurring_months'); ?></option>
                                    <option value="year" <?php if (isset($invoice) && $invoice->custom_recurring == 1 && $invoice->recurring_type == 'year') {
                                        echo 'selected';
                                    } ?>><?php echo _l('invoice_recurring_years'); ?></option>
                                </select>
                            </div>
                        </div>
                        <div id="cycles_wrapper"
                             class="<?php if (!isset($invoice) || (isset($invoice) && $invoice->recurring == 0)) {
                                 echo ' hide';
                             } ?>">
                            <div class="col-md-12">
                                <?php $value = (isset($invoice) ? $invoice->cycles : 0); ?>
                                <div class="form-group recurring-cycles">
                                    <label for="cycles"><?php echo _l('recurring_total_cycles'); ?>
                                        <?php if (isset($invoice) && $invoice->total_cycles > 0) {
                                            echo '<small>' . _l('cycles_passed', $invoice->total_cycles) . '</small>';
                                        }
                                        ?>
                                    </label>
                                    <div class="input-group">
                                        <input type="number" class="form-control"<?php if ($value == 0) {
                                            echo ' disabled';
                                        } ?> name="cycles" id="cycles"
                                               value="<?php echo $value; ?>" <?php if (isset($invoice) && $invoice->total_cycles > 0) {
                                            echo 'min="' . ($invoice->total_cycles) . '"';
                                        } ?>>
                                        <div class="input-group-addon">
                                            <div class="checkbox">
                                                <input type="checkbox"<?php if ($value == 0) {
                                                    echo ' checked';
                                                } ?> id="unlimited_cycles">
                                                <label for="unlimited_cycles"><?php echo _l('cycles_infinity'); ?></label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php $value = (isset($invoice) ? $invoice->adminnote : ''); ?>
                    <?php echo render_textarea('adminnote', 'invoice_add_edit_admin_note', $value); ?>
                </div>
            </div>
        </div>
    </div>

    <!--  -->
    <?php $this->load->view('admin/estimates/_add_edit_items'); ?>


</div>
<div class="row" id="tax_summry_area_div">

    <div id="removed-items"></div>
    <div id="billed-tasks"></div>
    <div id="billed-expenses"></div>
    <?php echo form_hidden('task_id'); ?>
    <?php echo form_hidden('expense_id'); ?>
</div>
<div class="row">
    <div class="col-md-12 mtop15">
        <div class="panel-body bottom-transaction">
            <?php $value = (isset($invoice) ? $invoice->clientnote : get_option('predefined_clientnote_invoice')); ?>
            <?php echo render_textarea('clientnote', 'invoice_add_edit_client_note', $value, array(), array(), 'mtop15'); ?>
            <?php /*$value = (isset($invoice) ? $invoice->terms : get_option('predefined_terms_invoice')); */?><!--
            --><?php /*echo render_textarea('terms', 'terms_and_conditions', $value, array(), array(), 'mtop15'); */?>
            <div class="btn-bottom-toolbar text-right">
                <?php if (!isset($invoice)) { ?>
                    <button class="btn-tr btn btn-default mleft10 text-right invoice-form-submit save-as-draft transaction-submit">
                        <?php echo _l('save_as_draft'); ?>
                    </button>
                <?php } ?>
                <!-- <button class="btn-tr btn btn-info mleft10 text-right invoice-form-submit save-and-send transaction-submit">
<?php echo _l('save_and_send'); ?>
</button> -->
                <button class="btn-tr btn btn-info mleft10 text-right invoice-form-submit transaction-submit">
                    <?php echo _l('submit'); ?>
                </button>
            </div>
        </div>
        <div class="btn-bottom-pusher"></div>
    </div>
</div>
</div>