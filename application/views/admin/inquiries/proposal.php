<?php init_head(); ?>
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
<div id="wrapper">
    <div class="content accounting-template proposal">
        <div class="row">
            <?php
            if (isset($proposal)) {
                echo form_hidden('isedit', $proposal->id);
            }
            $rel_type = '';
            $rel_id = '';
            if (isset($proposal) || ($this->input->get('rel_id') && $this->input->get('rel_type'))) {
                if ($this->input->get('rel_id')) {
                    $rel_id = $this->input->get('rel_id');
                    $rel_type = $this->input->get('rel_type');
                } else {
                    $rel_id = $proposal->rel_id;
                    $rel_type = $proposal->rel_type;
                }
            }
            ?>
            <?php echo form_open($this->uri->uri_string(), array('id' => 'proposal-form', 'class' => '_transaction_form proposal-form')); ?>
            <?php
            if (isset($proposal) && $proposal->devide_gst == 1) {
                echo form_hidden('devide_gst', "yes");
            } else {
                echo form_hidden('devide_gst', "no");
            }
            ?>
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row">
                            <?php if (isset($proposal)) { ?>
                                <div class="col-md-12">
                                    <?php echo format_proposal_status($proposal->status); ?>
                                </div>
                                <div class="clearfix"></div>
                                <hr/>
                            <?php } ?>
                            <div class="col-md-6 border-right">
                                <?php /*$value = (isset($proposal) ? $proposal->subject : ''); */ ?><!--
                        <?php /*$attrs = (isset($proposal) ? array() : array('autofocus'=>true)); */ ?>
                        --><?php /*echo render_input('subject','proposal_subject',$value,'text',$attrs); */ ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group select-placeholder">
                                            <label class="control-label"><?php echo 'Lead Status'; ?></label>
                                            <select class="selectpicker display-block mbot15" name="lead_status"
                                                    data-width="100%"
                                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                                    required>
                                                <option value="">Nothing Selected</option>
                                                <?php foreach ($lead_statuses as $status) { ?>
                                                    <option value="<?php echo $status['name']; ?>" <?php if ($proposal->lead_status == $status['name']) echo 'selected'; ?>><?php echo $status['name']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group select-placeholder">
                                            <label class="control-label"><?php echo 'Lead Source'; ?></label>
                                            <select class="selectpicker display-block mbot15" name="lead_source"
                                                    data-width="100%"
                                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                                    required>
                                                <option value="">Nothing Selected</option>
                                                <?php foreach ($lead_sorces as $sorces) { ?>
                                                    <option value="<?php echo $sorces['name']; ?>" <?php if ($proposal->lead_source == $sorces['name']) echo 'selected'; ?>><?php echo $sorces['name']; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group select-placeholder">
                                    <label for="rel_type"
                                           class="control-label"><?php echo _l('proposal_related'); ?></label>
                                    <select name="rel_type" id="rel_type" class="selectpicker" data-width="100%"
                                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                        <option value=""></option>
                                        <option value="lead" <?php if ((isset($proposal) && $proposal->rel_type == 'lead') || $this->input->get('rel_type')) {
                                            if ($rel_type == 'lead') {
                                                echo 'selected';
                                            }
                                        } ?>><?php echo _l('proposal_for_lead'); ?></option>
                                        <option value="customer" <?php if ((isset($proposal) && $proposal->rel_type == 'customer') || $this->input->get('rel_type')) {
                                            if ($rel_type == 'customer') {
                                                echo 'selected';
                                            }
                                        } ?>><?php echo _l('proposal_for_customer'); ?></option>
                                    </select>
                                </div>
                                <?php
                                if ($estimate->rel_id != '') {
                                    $mo = 'edit';
                                } else {
                                    $mo = '';
                                }
                                ?>
                                <div class="form-group select-placeholder<?php if ($rel_id == '') {
                                    echo ' hide';
                                } ?> " id="rel_id_wrapper">
                                    <label for="rel_id"><span class="rel_id_label"></span></label>
                                    <div id="rel_id_select">
                                        <select name="rel_id" id="rel_id" class="ajax-search" data-width="100%"
                                                data-live-search="true"
                                                data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                                onchange="fnbd(this.value,'division','<?php echo $mo; ?>');">
                                            <?php if ($rel_id != '' && $rel_type != '') {
                                                $rel_data = get_relation_data($rel_type, $rel_id);
                                                $rel_val = get_relation_values($rel_data, $rel_type);
                                                echo '<option value="' . $rel_val['id'] . '" selected>' . $rel_val['name'] . '</option>';
                                            } ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="row" >
                                    <div class="table-responsive s_table">
                                        <table class="table div_con <?= ((!isset($proposal) || $proposal->rel_type == 'lead') ? 'hide':'') ?>">
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
                                                            data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>"
                                                    >
                                                        <?php
                                                        if (isset($estimate->client)) {
                                                            $this->db->select('*');
                                                            $this->db->from('tblcontacts');
                                                            $array = array('userid' => $clnt->userid, 'division' => $estimate->division);
                                                            $this->db->where($array);

                                                            $this->db->order_by('id', 'asc');
                                                            $lead_contact = $this->db->get()->result_array();
                                                            foreach ($lead_contact as $contact) {
                                                                if ($contact['firstname'] != '/' AND $contact['firstname'] != '//') {
                                                                    ?>
                                                                    <option value="<?php echo $contact['id']; ?>"><?php echo $contact['firstname'] ?> <?php echo $contact['lastname']; ?>
                                                                        (<?php echo $contact['title']; ?>) (<?php echo $contact['phonenumber']; ?>)
                                                                    </option>
                                                                    <?php
                                                                }
                                                            }
                                                        } ?>
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
                                                $divConArray = get_div_cons_by_type('inquiry', $estimate->id);
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
                                <div class="row">
                                    <div class="col-md-4">
                                        <?php $value = (isset($proposal) ? _d($proposal->date) : _d(date('Y-m-d'))) ?>
                                        <?php echo render_date_input('date', 'proposal_date', $value); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?php
                                        $value = '';
                                        if (isset($proposal)) {
                                            $value = _d($proposal->open_till);
                                        } else {
                                            if (get_option('inquiry_due_after') != 0) {
                                                $value = _d(date('Y-m-d', strtotime('+' . get_option('inquiry_due_after') . ' DAY', strtotime(date('Y-m-d')))));
                                            }
                                        }
                                        echo render_date_input('open_till', 'proposal_open_till', $value); ?>
                                    </div>
                                    <div class="col-md-4">
                                        <?php $value = (isset($proposal) ? _d($proposal->follow_up_date) : '') ?>
                                        <?php echo render_date_input('follow_up_date', 'Follow up date', $value); ?>
                                    </div>
                                </div>
                                <?php
                                $selected = '';
                                $s_attrs = array('data-show-subtext' => true);
                                foreach ($currencies as $currency) {
                                    if ($currency['isdefault'] == 1) {
                                        $s_attrs['data-base'] = $currency['id'];
                                    }
                                    if (isset($proposal)) {
                                        if ($currency['id'] == $proposal->currency) {
                                            $selected = $currency['id'];
                                        }
                                        if ($proposal->rel_type == 'customer') {
                                            $s_attrs['disabled'] = true;
                                        }
                                    } else {
                                        if ($rel_type == 'customer') {
                                            $customer_currency = $this->clients_model->get_customer_default_currency($rel_id);
                                            if ($customer_currency != 0) {
                                                $selected = $customer_currency;
                                            } else {
                                                if ($currency['isdefault'] == 1) {
                                                    $selected = $currency['id'];
                                                }
                                            }
                                            $s_attrs['disabled'] = true;
                                        } else {
                                            if ($currency['isdefault'] == 1) {
                                                $selected = $currency['id'];
                                            }
                                        }
                                    }
                                }
                                ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php
                                        echo render_select('currency', $currencies, array('id', 'name', 'symbol'), 'proposal_currency', $selected, do_action('proposal_currency_disabled', $s_attrs));
                                        ?>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group select-placeholder">
                                            <label for="discount_type"
                                                   class="control-label"><?php echo _l('discount_type'); ?></label>
                                            <select name="discount_type" class="selectpicker" data-width="100%"
                                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                <option value="" selected><?php echo _l('no_discount'); ?></option>
                                                <option value="before_tax" selected="" <?php
                                                ?>><?php echo _l('discount_type_before_tax'); ?></option>
                                                <!-- <option value="after_tax" <?php if (isset($estimate)) {
                                                    if ($estimate->discount_type == 'after_tax') {
                                                        echo 'selected';
                                                    }
                                                } ?>><?php echo _l('discount_type_after_tax'); ?></option> -->
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <?php $fc_rel_id = (isset($proposal) ? $proposal->id : false); ?>
                                <?php echo render_custom_fields('proposal', $fc_rel_id); ?>
                                <div class="form-group no-mbot">
                                    <label for="tags" class="control-label"><i class="fa fa-tag"
                                                                               aria-hidden="true"></i> <?php echo _l('tags'); ?>
                                    </label>
                                    <input type="text" class="tagsinput" id="tags" name="tags"
                                           value="<?php echo(isset($proposal) ? prep_tags_input(get_tags_in($proposal->id, 'proposal')) : ''); ?>"
                                           data-role="tagsinput">
                                </div>
                                <div class="form-group mtop10 no-mbot">
                                    <p><?php echo _l('proposal_allow_comments'); ?></p>
                                    <div class="onoffswitch">
                                        <input type="checkbox" id="allow_comments"
                                               class="onoffswitch-checkbox" <?php if (isset($proposal)) {
                                            if ($proposal->allow_comments == 1) {
                                                echo 'checked';
                                            }
                                        }; ?> value="on" name="allow_comments">
                                        <label class="onoffswitch-label" for="allow_comments" data-toggle="tooltip"
                                               title="<?php echo _l('proposal_allow_comments_help'); ?>"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group select-placeholder">
                                            <label for="status"
                                                   class="control-label"><?php echo _l('proposal_status'); ?></label>
                                            <?php
                                            $disabled = '';
                                            if (isset($proposal)) {
                                                if ($proposal->estimate_id != NULL || $proposal->invoice_id != NULL) {
                                                    $disabled = 'disabled';
                                                }
                                            }
                                            ?>
                                            <select name="status" class="selectpicker"
                                                    data-width="100%" <?php echo $disabled; ?>
                                                    data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                <?php foreach ($statuses as $status) { ?>
                                                    <option value="<?php echo $status; ?>" <?php if ((isset($proposal) && $proposal->status == $status) || (!isset($proposal) && $status == 0)) {
                                                        echo 'selected';
                                                    } ?>><?php echo format_proposal_status($status, '', false); ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <?php
                                        $i = 0;
                                        $selected = '';
                                        foreach ($staff as $member) {
                                            if (isset($proposal)) {
                                                if ($proposal->assigned == $member['staffid']) {
                                                    $selected = $member['staffid'];
                                                }
                                            }
                                            $i++;
                                        }
                                        echo render_select('assigned', $staff, array('staffid', array('firstname', 'lastname')), 'proposal_assigned', $selected);
                                        ?>
                                    </div>
                                </div>
                                <?php //$value = (isset($proposal) ? $proposal->proposal_to : ''); ?>
                                <?php /*echo render_input('proposal_to', 'proposal_to', $value); */?>
                                <?php $value = (isset($proposal) ? $proposal->address : ''); ?>
                                <?php echo render_textarea('address', 'proposal_address', $value); ?>
                                <div class="row">
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->city : ''); ?>
                                        <?php echo render_input('city', 'billing_city', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->state : ''); ?>
                                        <?php echo render_input('state', 'billing_state', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $countries = get_all_countries(); ?>
                                        <?php $selected = (isset($proposal) ? $proposal->country : ''); ?>
                                        <?php echo render_select('country', $countries, array('country_id', array('short_name'), 'iso2'), 'billing_country', $selected); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->zip : ''); ?>
                                        <?php echo render_input('zip', 'billing_zip', $value); ?>
                                    </div>
                                    <div class="col-md-6 email_field">
                                        <?php $value = (isset($proposal) ? $proposal->email : ''); ?>
                                        <?php echo render_input('email', 'proposal_email', $value); ?>
                                    </div>
                                    <div class="col-md-6">
                                        <?php $value = (isset($proposal) ? $proposal->phone : ''); ?>
                                        <?php echo render_input('phone', 'proposal_phone', $value); ?>
                                    </div>
                                </div>
                                <?php $value = (isset($proposal) ? $proposal->reference_no : ''); ?>
                                <?php echo render_input('reference_no', 'reference_no', $value); ?>
                                <?php $value = (isset($proposal) ? $proposal->adminnote : ''); ?>
                                <?php echo render_textarea('adminnote', 'estimate_add_edit_admin_note', $value); ?>
                            </div>
                        </div>
                        <div class="btn-bottom-toolbar bottom-transaction text-right">
<!--                            <p class="no-mbot pull-left mtop5 btn-toolbar-notice">--><?php //echo _l('include_proposal_items_merge_field_help', '<b>{proposal_items}</b>'); ?><!--</p>-->
                            <!-- <button type="button" class="btn btn-info mleft10 proposal-form-submit save-and-send transaction-submit">
                        <?php echo _l('save_and_send'); ?>
                    </button> -->
                            <button class="btn btn-info mleft5 proposal-form-submit transaction-submit" type="button">
                                <?php echo _l('submit'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="panel_s">
                    <?php $this->load->view('admin/estimates/_add_edit_items'); ?>
                </div>
            </div>
            <div class="col-md-12 mtop15">
                <div class="panel-body bottom-transaction">
                    <?php $value = (isset($proposal) ? $proposal->clientnote : get_option('predefined_clientnote_inquiry')); ?>
                    <?php echo render_textarea('clientnote', 'estimate_add_edit_client_note', $value, array(), array(), 'mtop15'); ?>
                    <?php $value = (isset($proposal) ? $proposal->terms : get_option('predefined_terms_inquiry')); ?>
                    <?php echo render_textarea('terms', 'terms_and_conditions', $value, array(), array(), 'mtop15'); ?>
                </div>
                <div class="btn-bottom-pusher"></div>
            </div>
            <?php echo form_close(); ?>
            <?php $this->load->view('admin/invoice_items/item'); ?>
        </div>
        <div class="btn-bottom-pusher"></div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    var _rel_id = $('#rel_id'),
        _rel_type = $('#rel_type'),
        _rel_id_wrapper = $('#rel_id_wrapper'),
        data = {};
    function fnbd(vl, ty, mo) {
        if (vl != '' && _rel_type.val() == "customer") {
            if (mo == '') {
                if (ty == 'division') {
                    $('.main .division-td').load(admin_url+'inquiries/ajax_load?type=loaddivisions&customer=' + vl + '&mo=' + mo, function () {
                        $("#division").selectpicker();
                    });
                } else if (ty == 'contact') {
                    $('.main .contact-td').load(admin_url+'inquiries/ajax_load?type=loadcontacs&division=' + vl + '&mo=' + mo, function () {
                        $("#contact-select").selectpicker();
                    });
                    requestGetJSON(admin_url+"invoices/client_change_data/" + _rel_id.val()+'?division='+ vl).done(function (e) {
                        $("body").find('input[name="devide_gst"]').val(e.devide_gst);
                        var a = $("#expenses_to_bill");
                        for (var n in 0 === a.length ? e.expenses_bill_info = "" : a.html(e.expenses_bill_info), "" !== e.merge_info || "" !== e.expenses_bill_info ? $("#invoice_top_info").removeClass("hide") : $("#invoice_top_info").addClass("hide"), billingAndShippingFields) billingAndShippingFields[n].indexOf("billing") > -1 && (billingAndShippingFields[n].indexOf("country") > -1 ? $('select[name="' + billingAndShippingFields[n] + '"]').selectpicker("val", e.billing_shipping[0][billingAndShippingFields[n]]) : billingAndShippingFields[n].indexOf("billing_street") > -1 ? $('textarea[name="' + billingAndShippingFields[n] + '"]').val(e.billing_shipping[0][billingAndShippingFields[n]]) : $('input[name="' + billingAndShippingFields[n] + '"]').val(e.billing_shipping[0][billingAndShippingFields[n]]));
                        for (var n in empty(e.billing_shipping[0].shipping_street) || $('input[name="include_shipping"]').prop("checked", !0).change(), billingAndShippingFields) billingAndShippingFields[n].indexOf("shipping") > -1 && (billingAndShippingFields[n].indexOf("country") > -1 ? $('select[name="' + billingAndShippingFields[n] + '"]').selectpicker("val", e.billing_shipping[0][billingAndShippingFields[n]]) : billingAndShippingFields[n].indexOf("shipping_street") > -1 ? $('textarea[name="' + billingAndShippingFields[n] + '"]').val(e.billing_shipping[0][billingAndShippingFields[n]]) : $('input[name="' + billingAndShippingFields[n] + '"]').val(e.billing_shipping[0][billingAndShippingFields[n]]));
                        init_billing_and_shipping_details();
                        /*if(e.contact_details){
                            $("#email").val(e.contact_details.email);
                        }*/
                    })
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
    $(function () {

        init_currency_symbol();
        // Maybe items ajax search
        init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');
        validate_proposal_form();


        $('body').on('change', '#contact-select', function () {
            if ($(this).val() != '') {
                $.get(admin_url + 'clients/get_contact/' + $(this).val(), function (response) {
                    response = JSON.parse(response);
                    var email_val = $('input[name="email"]').val();
                    if(email_val !=''){
                        $('input[name="email"]').val(email_val+', '+response.email);
                    }else{
                        $('input[name="email"]').val(response.email);
                    }
                    
                });
            }
        })

        $('body').on('change', '#rel_id', function () {
            if (_rel_type.val() == "customer") {
                $(".table-responsive.s_table .table.div_con").removeClass('hide');
            }else{
                $("#div_con").val(JSON.stringify({}));
                $(".table-responsive.s_table .table.div_con").addClass('hide');
            }
            if ($(this).val() != '') {
                $.get(admin_url + 'proposals/get_relation_data_values/' + $(this).val() + '/' + _rel_type.val(), function (response) {
                    $('input[name="proposal_to"]').val(response.to);
                    $('textarea[name="address"]').val(response.address);
                    if(_rel_type.val() != 'customer'){
                        $('input[name="email"]').val(response.email);
                    }
                    $('input[name="phone"]').val(response.phone);
                    $('input[name="city"]').val(response.city);
                    $('input[name="state"]').val(response.state);
                    $('input[name="zip"]').val(response.zip);
                    $('select[name="country"]').selectpicker('val', response.country);

                    $('input[name="devide_gst"]').val(response.devide_gst);

                    var currency_selector = $('#currency');
                    if (_rel_type.val() == 'customer') {
                        if (typeof (currency_selector.attr('multi-currency')) == 'undefined') {
                            currency_selector.attr('disabled', true);
                        }

                    } else {
                        currency_selector.attr('disabled', false);
                    }
                    var proposal_to_wrapper = $('[app-field-wrapper="proposal_to"]');
                    if (response.is_using_company == false && !empty(response.company)) {
                        proposal_to_wrapper.find('#use_company_name').remove();
                        proposal_to_wrapper.find('#use_company_help').remove();
                        proposal_to_wrapper.append('<div id="use_company_help" class="hide">' + response.company + '</div>');
                        proposal_to_wrapper.find('label')
                            .prepend("<a href=\"#\" id=\"use_company_name\" data-toggle=\"tooltip\" data-title=\"<?php echo _l('use_company_name_instead'); ?>\" onclick='document.getElementById(\"proposal_to\").value = document.getElementById(\"use_company_help\").innerHTML.trim(); this.remove();'><i class=\"fa fa-building-o\"></i></a> ");
                    } else {
                        proposal_to_wrapper.find('label #use_company_name').remove();
                        proposal_to_wrapper.find('label #use_company_help').remove();
                    }
                    /* Check if customer default currency is passed */
                    if (response.currency) {
                        currency_selector.selectpicker('val', response.currency);
                    } else {
                        /* Revert back to base currency */
                        currency_selector.selectpicker('val', currency_selector.data('base'));
                    }
                    currency_selector.selectpicker('refresh');
                    currency_selector.change();
                }, 'json');
            }
        });
        $('.rel_id_label').html(_rel_type.find('option:selected').text());
        _rel_type.on('change', function () {
            if (_rel_type.val() != "customer") {
                $("#div_con").val(JSON.stringify({}));
                $(".table-responsive.s_table .table.div_con").addClass('hide');
            }
            var clonedSelect = _rel_id.html('').clone();
            _rel_id.selectpicker('destroy').remove();
            _rel_id = clonedSelect;
            $('#rel_id_select').append(clonedSelect);
            proposal_rel_id_select();
            if ($(this).val() != '') {
                _rel_id_wrapper.removeClass('hide');
            } else {
                _rel_id_wrapper.addClass('hide');
            }
            $('.rel_id_label').html(_rel_type.find('option:selected').text());
        });
        proposal_rel_id_select();
        <?php if(!isset($proposal) && $rel_id != ''){ ?>
        _rel_id.change();
        <?php } ?>


        $('input[name="state"]').on("blur", function () {
            var state = $(this).val();

            if (state != '') {
                $.get(admin_url + 'proposals/get_gst_devision/' + state, function (response) {
                    response = JSON.parse(response);
                    $('input[name="devide_gst"]').val(response.devide_gst);
                    setTimeout(function () {
                        calculate_total();
                    }, 15);
                });
            }
        });

        jQuery("[name=amount]").blur(function () {
            alert("This input field has lost its focus.");
        });

    });

    function proposal_rel_id_select() {
        var serverData = {};
        serverData.rel_id = _rel_id.val();
        data.type = _rel_type.val();
        <?php if(isset($proposal)){ ?>
        serverData.connection_type = 'proposal';
        serverData.connection_id = '<?php echo $proposal->id; ?>';
        <?php } ?>
        init_ajax_search(_rel_type.val(), _rel_id, serverData);
    }

    function validate_proposal_form() {
        _validate_form($('#proposal-form'), {
            subject: 'required',
            assigned: 'required',
            // proposal_to: 'required',
            rel_type: 'required',
            rel_id: 'required',
            date: 'required',
            open_till: 'required',
            email: {
                // email: true,
                // required: true
            },
            currency: 'required',
        });
    }

    function getLatestPrice(e,n){
        requestGetJSON(admin_url +'inquiries/get_latest_price/' + e).done(function (e) {
            $('input[name="items['+n+'][item_amount]"]').val(e.rate);
            $('input[name="items['+n+'][rate]"]').val(e.rate);
            calculate_total();
        });
    }
</script>
</body>
</html>
