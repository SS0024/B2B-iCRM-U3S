<style>
    .table.div_con thead {
        background: #415164;
        color: #fff;
        border: 0;
    }
    .table.div_con thead tr th{
        color: #fff;
    }
</style>
<div class="panel_s accounting-template purchaseorder">
    <div class="panel-body">
        <?php if (isset($estimate)) { ?>
            <?php echo format_purchaseorder_status($estimate->status);
            ?>
            <hr class="hr-panel-heading"/>
        <?php } ?>
        <?php
        if (isset($estimate) && $estimate->clientid != '') {
            $mo = 'edit';
        } else {
            $mo = '';
        }
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="f_client_id">
                    <div class="form-group select-placeholder">
                        <label for="clientid"
                               class="control-label"><?php echo _l('estimate_select_customer'); ?></label>
                        <select id="clientid" name="clientid" data-live-search="true" data-width="100%"
                                class="ajax-search<?php if (isset($estimate) && empty($estimate->clientid)) {
                                    echo ' customer-removed';
                                } ?>" onchange="fnbd(this.value,'division','<?php echo $mo; ?>');" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <?php $selected = (isset($estimate) ? $estimate->clientid : '');
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
                <?php
                $selected = '';
                foreach ($staff as $member) {
                    if (isset($estimate)) {
                        if($estimate->sale_agent == $member['staffid']) {
                            $selected = $member['staffid'];
                        }
                    }elseif (get_staff_user_id() == $member['staffid']){
                        $selected = $member['staffid'];
                    }
                }
                echo render_select('sale_agent', $staff, array('staffid', array('firstname', 'lastname')), 'Assigned', $selected);
                ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12" >
                <div class="table-responsive s_table">
                    <table class="table div_con <?= ((!isset($estimate)) ? 'hide':'') ?>">
                        <thead class="grey lighten-2">
                        <tr>
                            <th align="center">Division</th>
                            <th align="center" class="qty">Contact</th>
                            <th width="10%" align="center"><i class="fa fa-cog"></i></th>
                        </tr>
                        </thead>
                        <tbody class="ui-sortable">
                        <tr class="main">
                            <td class="division-td">
                                <select class="selectpicker display-block mbot15" name="division" id="division"
                                        data-width="100%"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                        onchange="fnbd(document.getElementById('division').value,'contact','<?php echo $mo; ?>');">
                                    <option></option>
                                    <?php
                                    if (isset($estimate->client)) {
                                        $this->db->select('*');
                                        $this->db->from('tbldivision');
                                        $clnt = $estimate->client;
                                        $this->db->where('userid', $clnt->userid);
                                        $this->db->order_by('id', 'asc');
                                        $lead_division = $this->db->get()->result_array();
                                        foreach ($lead_division as $division) { ?>
                                            <option value="<?php echo $division['id']; ?>"><?php echo $division['division']; ?></option>
                                        <?php }
                                    }
                                    ?>
                                </select>
                            </td>
                            <td class="contact-td">
                                <select class="selectpicker display-block mbot15" name="contact" id="contact-select"
                                        data-width="100%"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" >
                                </select>
                            </td>
                            <td class="action-td">
                                <button type="button"
                                        onclick="add_div_con_to_table(this); return false;"
                                        class="btn pull-right btn-info"><i class="fa fa-check"></i></button>
                            </td>
                        </tr>
                        <?php
                        $oldDivCon = [];
                        if(isset($estimate->id)){
                            $divConArray = get_div_cons_by_type('purchaseorder', $estimate->id);
                            foreach ($divConArray as $con_div)
                            {
                                $oldDivCon[$con_div['con']] = $con_div['div'];
                                ?>
                                <tr>
                                    <td><?= $con_div['division']; ?></td>
                                    <td><?= $con_div['contact_name'] ?></td>
                                    <td><button type="button" onclick="remove_div_con_to_table(<?= $con_div['div'] ?>,<?= $con_div['con'] ?>, this); return false;" class="btn pull-right btn-danger "><i class="fa fa-trash"></i></button></td>
                                </tr>
                                <?php
                            }
                        }
                        ?>
                        </tbody>
                    </table>
                    <input type="hidden" value='<?= json_encode($oldDivCon) ?>' name="div_con" id="div_con">
                </div>
            </div>
            <div class="col-md-6">

                <div class="form-group select-placeholder projects-wrapper<?php if ((!isset($estimate)) || (isset($estimate) && !customer_has_projects($estimate->clientid))) {
                    echo ' hide';
                } ?>">
                    <label for="project_id"><?php echo _l('project'); ?></label>
                    <div id="project_ajax_search_wrapper">
                        <select name="project_id" id="project_id" class="projects ajax-search" data-live-search="true"
                                data-width="100%"
                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                            <?php
                            if (isset($estimate) && $estimate->project_id != 0) {
                                echo '<option value="' . $estimate->project_id . '" selected>' . get_project_name_by_id($estimate->project_id) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <!--<div class="form-group select-placeholder divisions-wrapper<?php /*if((!isset($estimate)) || (isset($estimate) && !customer_has_divisions($estimate->clientid))){ echo ' hide';} */?>">
                    <label for="division_id"><?php /*echo _l('Division'); */?></label>
                    <div id="division_ajax_search_wrapper">
                        <select name="division_id" id="division_id" class="divisions ajax-search" data-live-search="true" data-width="100%" data-none-selected-text="<?php /*echo _l('dropdown_non_selected_tex'); */?>">
                            <?php
/*                            if(isset($estimate) && $estimate->division_id != 0){
                                echo '<option value="'.$estimate->division_id.'" selected>'.get_division_name_by_id($estimate->division_id).'</option>';
                            }
                            */?>
                        </select>
                    </div>
                </div>-->
                <div class="row">
                    <div class="col-md-12">
                        <a href="#" class="edit_shipping_billing_info" data-toggle="modal"
                           data-target="#billing_and_shipping_details"><i class="fa fa-pencil-square-o"></i></a>
                        <?php include_once(APPPATH . 'views/admin/purchaseorder/billing_and_shipping_template.php'); ?>
                    </div>
                    <div class="col-md-6">
                        <p class="bold"><?php echo _l('invoice_bill_to'); ?></p>
                        <address>
                     <span class="billing_street">
                     <?php $billing_street = (isset($estimate) ? $estimate->billing_street : '--'); ?>
                     <?php $billing_street = ($billing_street == '' ? '--' : $billing_street); ?>
                     <?php echo $billing_street; ?></span><br>
                            <span class="billing_city">
                     <?php $billing_city = (isset($estimate) ? $estimate->billing_city : '--'); ?>
                     <?php $billing_city = ($billing_city == '' ? '--' : $billing_city); ?>
                     <?php echo $billing_city; ?></span>,
                            <span class="billing_state">
                     <?php $billing_state = (isset($estimate) ? $estimate->billing_state : '--'); ?>
                     <?php $billing_state = ($billing_state == '' ? '--' : $billing_state); ?>
                     <?php echo $billing_state; ?></span>
                            <br/>
                            <span class="billing_country">
                     <?php $billing_country = (isset($estimate) ? get_country_short_name($estimate->billing_country) : '--'); ?>
                     <?php $billing_country = ($billing_country == '' ? '--' : $billing_country); ?>
                     <?php echo $billing_country; ?></span>,
                            <span class="billing_zip">
                     <?php $billing_zip = (isset($estimate) ? $estimate->billing_zip : '--'); ?>
                     <?php $billing_zip = ($billing_zip == '' ? '--' : $billing_zip); ?>
                     <?php echo $billing_zip; ?></span>
                        </address>
                    </div>
                    <div class="col-md-6">
                        <p class="bold"><?php echo _l('ship_to'); ?></p>
                        <address>
                     <span class="shipping_street">
                     <?php $shipping_street = (isset($estimate) ? $estimate->shipping_street : '--'); ?>
                     <?php $shipping_street = ($shipping_street == '' ? '--' : $shipping_street); ?>
                     <?php echo $shipping_street; ?></span><br>
                            <span class="shipping_city">
                     <?php $shipping_city = (isset($estimate) ? $estimate->shipping_city : '--'); ?>
                     <?php $shipping_city = ($shipping_city == '' ? '--' : $shipping_city); ?>
                     <?php echo $shipping_city; ?></span>,
                            <span class="shipping_state">
                     <?php $shipping_state = (isset($estimate) ? $estimate->shipping_state : '--'); ?>
                     <?php $shipping_state = ($shipping_state == '' ? '--' : $shipping_state); ?>
                     <?php echo $shipping_state; ?></span>
                            <br/>
                            <span class="shipping_country">
                     <?php $shipping_country = (isset($estimate) ? get_country_short_name($estimate->shipping_country) : '--'); ?>
                     <?php $shipping_country = ($shipping_country == '' ? '--' : $shipping_country); ?>
                     <?php echo $shipping_country; ?></span>,
                            <span class="shipping_zip">
                     <?php $shipping_zip = (isset($estimate) ? $estimate->shipping_zip : '--'); ?>
                     <?php $shipping_zip = ($shipping_zip == '' ? '--' : $shipping_zip); ?>
                     <?php echo $shipping_zip; ?></span>
                            <span class="shipping_zip">
                     <?php $division = (isset($estimate) ? $estimate->division : '--'); ?>
                     <?php $division = ($division == '' ? '--' : $division); ?>
                     <?php echo $division; ?></span>
                        </address>
                    </div>
                </div>
                <?php
                $next_estimate_number = get_option('next_purchaseorder_number');
                $format = get_option('purchaseorder_number_format');

                if (isset($estimate)) {
                    $format = $estimate->number_format;
                }

                $prefix = get_option('purchaseorder_prefix');

                if ($format == 1) {
                    $__number = $next_estimate_number;
                    if (isset($estimate)) {
                        $__number = $estimate->number;
                        $prefix = '<span id="prefix">' . $estimate->prefix . '</span>';
                    }
                } else if ($format == 2) {
                    if (isset($estimate)) {
                        $__number = $estimate->number;
                        $prefix = $estimate->prefix;
                        $prefix = '<span id="prefix">' . $prefix . '</span><span id="prefix_year">' . date('Y', strtotime($estimate->date)) . '</span>/';
                    } else {
                        $__number = $next_estimate_number;
                        $prefix = $prefix . '<span id="prefix_year">' . date('Y') . '</span>/';
                    }
                } else if ($format == 3) {
                    if (isset($estimate)) {
                        $yy = date('y', strtotime($estimate->date));
                        $__number = $estimate->number;
                        $prefix = '<span id="prefix">' . $estimate->prefix . '</span>';
                    } else {
                        $yy = date('y');
                        $__number = $next_estimate_number;
                    }
                } else if ($format == 4) {
                    if (isset($estimate)) {
                        $yyyy = date('Y', strtotime($estimate->date));
                        $mm = date('m', strtotime($estimate->date));
                        $__number = $estimate->number;
                        $prefix = '<span id="prefix">' . $estimate->prefix . '</span>';
                    } else {
                        $yyyy = date('Y');
                        $mm = date('m');
                        $__number = $next_estimate_number;
                    }
                }

                $_estimate_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
                $isedit = isset($estimate) ? 'true' : 'false';
                $data_original_number = isset($estimate) ? $estimate->number : 'false';
                ?>
                <div class="form-group">
                    <label for="number"><?php echo _l('PO Number'); ?></label>
                    <div class="input-group">
                  <span class="input-group-addon">
                  <?php if (isset($estimate)) { ?>
                      <a href="#" onclick="return false;" data-toggle="popover" data-container='._transaction_form'
                         data-html="true"
                         data-content="<label class='control-label'><?php echo _l('settings_sales_estimate_prefix'); ?></label><div class='input-group'><input name='s_prefix' type='text' class='form-control' value='<?php echo $estimate->prefix; ?>'></div><button type='button' onclick='save_sales_number_settings(this); return false;' data-url='<?php echo admin_url('purchaseorder/update_number_settings/' . $estimate->id); ?>' class='btn btn-info btn-block mtop15'><?php echo _l('submit'); ?></button>"><i
                                  class="fa fa-cog"></i></a>
                  <?php }
                  echo $prefix;
                  ?>
                 </span>
                        <input type="text" name="number" class="form-control" value="<?php echo $_estimate_number; ?>"
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
                        <?php } if (!isset($estimate)) {
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
                        <?php $value = (isset($estimate) ? _d($estimate->date) : _d(date('Y-m-d'))); ?>
                        <?php echo render_date_input('date', 'PO Date', $value); ?>
                    </div>
                    <div class="col-md-6">
                        <?php
                        $value = '';
                        if (isset($estimate)) {
                            $value = _d($estimate->expirydate);
                        } else {
                            if (get_option('purchaseorder_due_after') != 0) {
                                $value = _d(date('Y-m-d', strtotime('+' . get_option('purchaseorder_due_after') . ' DAY', strtotime(date('Y-m-d')))));
                            }
                        }
                        echo render_date_input('expirydate', 'Supply Date', $value); ?>
                    </div>
                </div>
                <div class="clearfix mbot15"></div>
                <?php $rel_id = (isset($estimate) ? $estimate->id : false); ?>
                <?php
                if (isset($custom_fields_rel_transfer)) {
                    $rel_id = $custom_fields_rel_transfer;
                }
                ?>
                <?php echo render_custom_fields('estimate', $rel_id); ?>
            </div>
            <div class="col-md-6">
                <div class="panel_s no-shadow">
                    <div class="form-group">
                        <label for="tags" class="control-label"><i class="fa fa-tag"
                                                                   aria-hidden="true"></i> <?php echo _l('tags'); ?>
                        </label>
                        <input type="text" class="tagsinput" id="tags" name="tags"
                               value="<?php echo(isset($estimate) ? prep_tags_input(get_tags_in($estimate->id, 'estimate')) : ''); ?>"
                               data-role="tagsinput">
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <?php
                            $s_attrs = array('disabled' => true, 'data-show-subtext' => true);
                            $s_attrs = do_action('estimate_currency_disabled', $s_attrs);
                            foreach ($currencies as $currency) {
                                if ($currency['isdefault'] == 1) {
                                    $s_attrs['data-base'] = $currency['id'];
                                }
                                if (isset($estimate)) {
                                    if ($currency['id'] == $estimate->currency) {
                                        $selected = $currency['id'];
                                    }
                                } else {
                                    if ($currency['isdefault'] == 1) {
                                        $selected = $currency['id'];
                                    }
                                }
                            }
                            ?>
                            <?php echo render_select('currency', $currencies, array('id', 'name', 'symbol'), 'estimate_add_edit_currency', $selected, $s_attrs); ?>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group select-placeholder">
                                <label class="control-label"><?php echo _l('estimate_status'); ?></label>
                                <select class="selectpicker display-block mbot15" name="status" data-width="100%"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <?php foreach ($estimate_statuses as $status) { ?>
                                        <option value="<?php echo $status; ?>" <?php if (isset($estimate) && $estimate->status == $status) {
                                            echo 'selected';
                                        } ?>><?php echo format_purchaseorder_status($status, '', false); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <?php $value = (isset($estimate) ? $estimate->reference_no : ''); ?>
                            <?php echo render_input('reference_no', 'Customer Order Number', $value); ?>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group select-placeholder">
                                <label for="discount_type"
                                       class="control-label"><?php echo _l('discount_type'); ?></label>
                                <select name="discount_type" class="selectpicker" data-width="100%"
                                        data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                    <option value=""><?php echo _l('no_discount'); ?></option>
                                    <option selected value="before_tax" <?php
                                    if (isset($estimate)) {
                                        if ($estimate->discount_type == 'before_tax') {
                                            echo 'selected';
                                        }
                                    } ?>><?php echo _l('discount_type_before_tax'); ?></option>
                                    <!-- <option value="after_tax" <?php if (isset($estimate)) {
                                        if ($estimate->discount_type == 'after_tax') {
                                            echo 'selected';
                                        }
                                    } ?>><?php echo _l('discount_type_after_tax'); ?></option> -->
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php $value = (isset($estimate) ? $estimate->adminnote : ''); ?>
                    <?php echo render_textarea('adminnote', 'estimate_add_edit_admin_note', $value); ?>

                </div>
            </div>
        </div>
    </div>
    <?php $this->load->view('admin/purchaseorder/_add_edit_items'); ?>


    <div class="row">
        <div class="col-md-12 mtop15">
            <div class="panel-body bottom-transaction">
                <?php $value = (isset($estimate) ? $estimate->clientnote : get_option('predefined_clientnote_estimate')); ?>
                <?php echo render_textarea('clientnote', 'estimate_add_edit_client_note', $value, array(), array(), 'mtop15'); ?>
                <?php $value = (isset($estimate) ? $estimate->terms : get_option('predefined_terms_estimate')); ?>
                <?php echo render_textarea('terms', 'terms_and_conditions', $value, array(), array(), 'mtop15'); ?>
                <div class="btn-bottom-toolbar text-right">
                    <!-- <button type="button" class="btn-tr btn btn-info mleft10 estimate-form-submit save-and-send transaction-submit">
              <?php echo _l('save_and_send'); ?>
              </button> -->
                    <button type="button" class="btn-tr btn btn-info mleft10 estimate-form-submit transaction-submit">
                        <?php echo _l('submit'); ?>
                    </button>
                </div>
            </div>
            <div class="btn-bottom-pusher"></div>
        </div>
    </div>
</div>
<script>
     var data = {};
    function fnbd(vl, ty, mo) {
        if (vl != '') {
            $("table.div_con").removeClass('hide');
            if (mo == '') {
                if (ty == 'division') {
                    $('.main .division-td').load(admin_url+'inquiries/ajax_load?type=loaddivisions&customer=' + vl + '&mo=' + mo, function () {
                        $("#division").selectpicker();
                    });
                } else if (ty == 'contact') {
                    $('.main .contact-td').load(admin_url+'inquiries/ajax_load?type=loadcontacs&division=' + vl + '&mo=' + mo, function () {
                        $("#contact-select").selectpicker();
                    });
                }
            } else {
                if (ty == 'division') {
                    $('.main .division-td').load(admin_url+'inquiries/ajax_load?type=loaddivisions&customer=' + vl + '&mo=' + mo, function () {
                        $("#division").selectpicker();
                    });
                } else if (ty == 'contact') {
                    $('.main .contact-td').load(admin_url+'inquiries/ajax_load?type=loadcontacs&division=' + vl + '&mo=' + mo, function () {
                        $("#contact-select").selectpicker();
                    });
                }
            }

        }
    }

    function add_div_con_to_table(a) {
        var divConValue;
        if ($("#div_con").val() != '') {
            divConValue = JSON.parse($("#div_con").val());
        } else {
            divConValue = {};
        }
        let mainHtmlBlock = $(a).parent().parent();
        let divisionId = mainHtmlBlock.find('#division').val();
        let divisionName = mainHtmlBlock.find('#division option:selected').text();
        let contactId = mainHtmlBlock.find('#contact-select').val();
        let contactName = mainHtmlBlock.find('#contact-select option:selected').text();
        if(divisionId != '' && divisionId != null && contactId != '' && contactId  != null){
            let htmlBlock = '<tr>' +
                '<td>' + divisionName + '</td>' +
                '<td>' + contactName + '</td>' +
                '<td><button type="button" onclick="remove_div_con_to_table(' + divisionId + ',' + contactId + ', this); return false;" class="btn pull-right btn-danger "><i class="fa fa-trash"></i></button></td>' +
                '</tr>';
            if(Object.keys(divConValue).map(function(e) { return e; }).indexOf(contactId) < 0){
                divConValue[contactId] = divisionId;
                divConValue = Object.assign({}, divConValue);
                $("#div_con").val(JSON.stringify(divConValue));
                mainHtmlBlock.parent().append(htmlBlock);
                $("#division").selectpicker("val", "");
                $("#contact-select").selectpicker("val", "");
            }
        }
    }

    function filterByProperty(array, prop, value){
        var filtered = [];
        for(var i = 0; i < array.length; i++){
            var obj = array[i];
            for(var key in obj){
                if(typeof(obj[key] == "object")){
                    var item = obj[key];
                    if(item[prop] == value){
                        filtered.push(item);
                    }
                }
            }
        }
        return filtered;
    }


    function remove_div_con_to_table(divisionId, contactId, a) {
        var divConValue;
        if ($("#div_con").val() != '') {
            divConValue = JSON.parse($("#div_con").val());
        } else {
            divConValue = {};
        }
        divConValue = filterByProperty(divConValue, contactId, divisionId);
        $("#div_con").val(JSON.stringify(divConValue));
        $(a).parent().parent().remove();
    }
</script>