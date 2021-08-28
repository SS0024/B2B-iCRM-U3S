<?php
/**
 * Included in application/views/admin/clients/client.php
 */
?>
<script>
Dropzone.options.clientAttachmentsUpload = false;
var customer_id = $('input[name="userid"]').val();
$(function() {

    if ($('#client-attachments-upload').length > 0) {
        new Dropzone('#client-attachments-upload',$.extend({},_dropzone_defaults(),{
            paramName: "file",
            accept: function(file, done) {
                done();
            },
            success: function(file, response) {
                if (this.getUploadingFiles().length === 0 && this.getQueuedFiles().length === 0) {
                    window.location.reload();
                }
            }
        }));
    }

    // Save button not hidden if passed from url ?tab= we need to re-click again
    if (tab_active) {
        $('body').find('.nav-tabs [href="#' + tab_active + '"]').click();
    }

    $('a[href="#customer_admins"]').on('click', function() {
        $('.btn-bottom-toolbar').addClass('hide');
    });

    $('.profile-tabs a').not('a[href="#customer_admins"]').on('click', function() {
        $('.btn-bottom-toolbar').removeClass('hide');
    });

    $("input[name='tasks_related_to[]']").on('change', function() {
        var tasks_related_values = []
        $('#tasks_related_filter :checkbox:checked').each(function(i) {
            tasks_related_values[i] = $(this).val();
        });
        $('input[name="tasks_related_to"]').val(tasks_related_values.join());
        $('.table-rel-tasks').DataTable().ajax.reload();
    });

    var contact_id = get_url_param('contactid');
    if (contact_id) {
        contact(customer_id, contact_id);
    }

    // consents=CONTACT_ID
    var consents = get_url_param('consents');
    if(consents){
        view_contact_consent(consents);
    }

    // If user clicked save and add new contact
    if (get_url_param('new_contact')) {
        contact(customer_id);
    }

    $('body').on('change', '.onoffswitch input.customer_file', function(event, state) {
        var invoker = $(this);
        var checked_visibility = invoker.prop('checked');
        var share_file_modal = $('#customer_file_share_file_with');
        setTimeout(function() {
            $('input[name="file_id"]').val(invoker.attr('data-id'));
            if (checked_visibility && share_file_modal.attr('data-total-contacts') > 1) {
                share_file_modal.modal('show');
            } else {
                do_share_file_contacts();
            }
        }, 200);
    });

    $('.customer-form-submiter').on('click', function() {
        var form = $('.client-form');
        if (form.valid()) {
            if ($(this).hasClass('save-and-add-contact')) {
                form.find('.additional').html(hidden_input('save_and_add_contact', 'true'));
            } else {
                form.find('.additional').html('');
            }
            form.submit();
        }
    });

    if (typeof(Dropbox) != 'undefined' && $('#dropbox-chooser').length > 0) {
        document.getElementById("dropbox-chooser").appendChild(Dropbox.createChooseButton({
            success: function(files) {
                $.post(admin_url + 'clients/add_external_attachment', {
                    files: files,
                    clientid: customer_id,
                    external: 'dropbox'
                }).done(function() {
                    window.location.reload();
                });
            },
            linkType: "preview",
            extensions: app_allowed_files.split(','),
        }));
    }

    /* Customer profile tickets table */
    $('.table-tickets-single').find('#th-submitter').removeClass('toggleable');

    initDataTable('.table-tickets-single', admin_url + 'tickets/index/false/' + customer_id, undefined, undefined, 'undefined', [$('table thead .ticket_created_column').index(), 'desc']);

    /* Customer profile contracts table */
    initDataTable('.table-contracts-single-client', admin_url + 'contracts/table/' + customer_id, undefined,undefined, 'undefined', [6, 'desc']);

    /* Custome profile contacts table */
    var contactsNotSortable = [];
    <?php if(is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1'){ ?>
        contactsNotSortable.push($('#th-consent').index());
    <?php } ?>
    _table_api = initDataTable('.table-contacts', admin_url + 'clients/contacts/' + customer_id, contactsNotSortable, contactsNotSortable);
    if(_table_api) {
          <?php if(is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1'){ ?>
        _table_api.on('draw', function () {
            var tableData = $('.table-contacts').find('tbody tr');
            $.each(tableData, function() {
                $(this).find('td:eq(1)').addClass('bg-light-gray');
            });
        });
        <?php } ?>
    }

    /* Custome profile division table */
    var contactsNotSortable = [];
    <?php if(is_gdpr() && get_option('gdpr_enable_consent_for_division') == '1'){ ?>
    contactsNotSortable.push($('#th-consent').index());
    <?php } ?>
    _table_api = initDataTable('.table-division', admin_url + 'clients/division/' + customer_id, contactsNotSortable, contactsNotSortable);
    if(_table_api) {
        <?php if(is_gdpr() && get_option('gdpr_enable_consent_for_division') == '1'){ ?>
        _table_api.on('draw', function () {
            var tableData = $('.table-division').find('tbody tr');
            $.each(tableData, function() {
                $(this).find('td:eq(1)').addClass('bg-light-gray');
            });
        });
        <?php } ?>
    }

    /* Customer profile invoices table */
    initDataTable('.table-invoices-single-client',
        admin_url + 'invoices/table/' + customer_id,
        'undefined',
        'undefined',
        'undefined', [
            [3, 'desc'],
            [0, 'desc']
        ]);

   initDataTable('.table-credit-notes', admin_url+'credit_notes/table/'+customer_id, ['undefined'], ['undefined'], undefined, [0, 'desc']);

    /* Customer profile Estimates table */
    initDataTable('.table-estimates-single-client',
        admin_url + 'estimates/table/' + customer_id,
        'undefined',
        'undefined',
        'undefined', [
            [3, 'desc'],
            [0, 'desc']
        ]);

    /* Customer profile payments table */
    initDataTable('.table-payments-single-client',
        admin_url + 'payments/table/' + customer_id, undefined, undefined,
        'undefined', [0, 'desc']);

    /* Customer profile reminders table */
    initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + customer_id + '/' + 'customer', undefined, undefined, undefined, [1, 'asc']);

    /* Customer profile expenses table */
    initDataTable('.table-expenses-single-client',
        admin_url + 'expenses/table/' + customer_id,
        'undefined',
        'undefined',
        'undefined', [5, 'desc']);

    /* Customer profile proposals table */
    initDataTable('.table-proposals-client-profile',
        admin_url + 'proposals/proposal_relations/' + customer_id + '/customer',
        'undefined',
        'undefined',
        'undefined', [6, 'desc']);

    /* Custome profile projects table */
    initDataTable('.table-projects-single-client', admin_url + 'projects/table/' + customer_id, undefined, undefined, 'undefined', <?php echo do_action('projects_table_default_order',json_encode(array(5,'asc'))); ?>);

    var vRules = {};
    if (app_company_is_required == 1) {
        vRules = {
            company: 'required',
            state: 'required',
        }
    }
    _validate_form($('.client-form'), vRules);
    $("body").find('input[name="vat"]').rules("add", {
        remote: {
            url: admin_url + "clients/validate_gst_exist",
            type: "post",
            data: {
                vat: function () {
                    return $('input[name="vat"]').val()
                },
                isedit: function () {
                    return $('input[name="userid"]').val()
                },
            }
        },
        messages: {
            remote: "GST number already exists."
        }
    });

    $('.billing-same-as-customer').on('click', function(e) {
        e.preventDefault();
        $('textarea[name="billing_street"]').val($('textarea[name="address"]').val());
        $('input[name="billing_city"]').val($('input[name="city"]').val());
        $('input[name="billing_state"]').val($('input[name="state"]').val());
        $('input[name="billing_zip"]').val($('input[name="zip"]').val());
        $('select[name="billing_country"]').selectpicker('val', $('select[name="country"]').selectpicker('val'));
    });

    $('.customer-copy-billing-address').on('click', function(e) {
        e.preventDefault();
        $('textarea[name="shipping_street"]').val($('textarea[name="billing_street"]').val());
        $('input[name="shipping_city"]').val($('input[name="billing_city"]').val());
        $('input[name="shipping_state"]').val($('input[name="billing_state"]').val());
        $('input[name="shipping_zip"]').val($('input[name="billing_zip"]').val());
        $('select[name="shipping_country"]').selectpicker('val', $('select[name="billing_country"]').selectpicker('val'));
    });

    $('body').on('hidden.bs.modal', '#contact', function() {
        $('#contact_data').empty();
    });

    $('.client-form').on('submit', function() {
        $('select[name="default_currency"]').prop('disabled', false);
    });

});

function delete_contact_profile_image(contact_id) {
    requestGet('clients/delete_contact_profile_image/'+contact_id).done(function(){
        $('body').find('#contact-profile-image').removeClass('hide');
        $('body').find('#contact-remove-img').addClass('hide');
        $('body').find('#contact-img').attr('src', '<?php echo base_url('assets/images/user-placeholder.jpg'); ?>');
    });
}

function validate_contact_form() {
    _validate_form('#contact-form', {
        firstname: 'required',
        lastname: 'required',
        password: {
            required: {
                depends: function(element) {
                    var sent_set_password = $('input[name="send_set_password_email"]');
                    if ($('#contact input[name="contactid"]').val() == '' && sent_set_password.prop('checked') == false) {
                        return true;
                    }
                }
            }
        },
        email: {
            <?php if(do_action('contact_email_required',"true") === "true"){ ?>
            required: true,
            <?php } ?>
            email: true,
            // Use this hook only if the contacts are not logging into the customers area and you are not using support tickets piping.
            <?php if(do_action('contact_email_unique',"true") === "true"){ ?>
            remote: {
                url: admin_url + "misc/contact_email_exists",
                type: 'post',
                data: {
                    email: function() {
                        return $('#contact input[name="email"]').val();
                    },
                    userid: function() {
                        return $('body').find('input[name="contactid"]').val();
                    }
                }
            }
            <?php } ?>
        }
    }, contactFormHandler);
}

function contactFormHandler(form) {
    $('#contact input[name="is_primary"]').prop('disabled', false);

    $("#contact input[type=file]").each(function() {
        if($(this).val() === "") {
            $(this).prop('disabled', true);
        }
    });

    var formURL = $(form).attr("action");
    var formData = new FormData($(form)[0]);

    $.ajax({
        type: 'POST',
        data: formData,
        mimeType: "multipart/form-data",
        contentType: false,
        cache: false,
        processData: false,
        url: formURL
    }).done(function(response){
             response = JSON.parse(response);
            if (response.success) {
                alert_float('success', response.message);
                if(typeof(response.is_individual) != 'undefined' && response.is_individual) {
                    $('.new-contact').addClass('disabled');
                    if(!$('.new-contact-wrapper')[0].hasAttribute('data-toggle')) {
                        $('.new-contact-wrapper').attr('data-toggle','tooltip');
                    }
                }
            }
            if ($.fn.DataTable.isDataTable('.table-contacts')) {
                $('.table-contacts').DataTable().ajax.reload(null,false);
            }
            if (response.proposal_warning && response.proposal_warning != false) {
                $('body').find('#contact_proposal_warning').removeClass('hide');
                $('body').find('#contact_update_proposals_emails').attr('data-original-email', response.original_email);
                $('#contact').animate({
                    scrollTop: 0
                }, 800);
            } else {
                $('#contact').modal('hide');
            }
    }).fail(function(error){
        alert_float('danger', JSON.parse(error.responseText));
    });
    return false;
}

function contact(client_id, contact_id) {
    if (typeof(contact_id) == 'undefined') {
        contact_id = '';
    }
    requestGet('clients/contact/' + client_id + '/' + contact_id).done(function(response) {
        $('#contact_data').html(response);
        $('#contact').modal({
            show: true,
            backdrop: 'static'
        });
        $('body').off('shown.bs.modal','#contact');
        $('body').on('shown.bs.modal', '#contact', function() {
            if (contact_id == '') {
                $('#contact').find('input[name="firstname"]').focus();
            }
        });
        init_selectpicker();
        init_datepicker();
        custom_fields_hyperlink();
        validate_contact_form();
    }).fail(function(error) {
        var response = JSON.parse(error.responseText);
        alert_float('danger', response.message);
    });
}

function division(client_id, division_id) {
    if (typeof(division_id) == 'undefined') {
        division_id = '';
    }
    requestGet('clients/division/' + client_id + '/' + division_id).done(function(response) {
        $('#division_data').html(response);
        $('#division').modal({
            show: true,
            backdrop: 'static'
        });
        $('body').off('shown.bs.modal','#division');
        $('body').on('shown.bs.modal', '#division', function() {
            if (division_id == '') {
                $('#division').find('input[name="division"]').focus();
            }
        });
        init_selectpicker();
        init_datepicker();
        custom_fields_hyperlink();
        validate_contact_form();
    }).fail(function(error) {
        var response = JSON.parse(error.responseText);
        alert_float('danger', response.message);
    });
}

function update_all_proposal_emails_linked_to_contact(contact_id) {
    var data = {};
    data.update = true;
    data.original_email = $('body').find('#contact_update_proposals_emails').data('original-email');
    $.post(admin_url + 'clients/update_all_proposal_emails_linked_to_customer/' + contact_id, data).done(function(response) {
        response = JSON.parse(response);
        if (response.success) {
            alert_float('success', response.message);
        }
        $('#contact').modal('hide');
    });
}

function do_share_file_contacts(edit_contacts, file_id) {
    var contacts_shared_ids = $('select[name="share_contacts_id[]"]');
    if (typeof(edit_contacts) == 'undefined' && typeof(file_id) == 'undefined') {
        var contacts_shared_ids_selected = $('select[name="share_contacts_id[]"]').val();
    } else {
        var _temp = edit_contacts.toString().split(',');
        for (var cshare_id in _temp) {
            contacts_shared_ids.find('option[value="' + _temp[cshare_id] + '"]').attr('selected', true);
        }
        contacts_shared_ids.selectpicker('refresh');
        $('input[name="file_id"]').val(file_id);
        $('#customer_file_share_file_with').modal('show');
        return;
    }
    var file_id = $('input[name="file_id"]').val();
    $.post(admin_url + 'clients/update_file_share_visibility', {
        file_id: file_id,
        share_contacts_id: contacts_shared_ids_selected,
        customer_id: $('input[name="userid"]').val()
    }).done(function() {
        window.location.reload();
    });
}

function save_longitude_and_latitude(clientid) {
    var data = {};
    data.latitude = $('#latitude').val();
    data.longitude = $('#longitude').val();
    $.post(admin_url + 'clients/save_longitude_and_latitude/'+clientid, data).done(function(response) {
       if(response == 'success') {
            alert_float('success', "<?php echo _l('updated_successfully', _l('client')); ?>");
       }
        setTimeout(function(){
            window.location.reload();
        },1200);
    }).fail(function(error) {
        alert_float('danger', error.responseText);
    });
}

function fetch_lat_long_from_google_cprofile() {
    var data = {};
    data.address = $('#long_lat_wrapper').data('address');
    data.city = $('#long_lat_wrapper').data('city');
    data.country = $('#long_lat_wrapper').data('country');
    $('#gmaps-search-icon').removeClass('fa-google').addClass('fa-spinner fa-spin');
    $.post(admin_url + 'misc/fetch_address_info_gmaps', data).done(function(data) {
        data = JSON.parse(data);
        $('#gmaps-search-icon').removeClass('fa-spinner fa-spin').addClass('fa-google');
        if (data.response.status == 'OK') {
            $('input[name="latitude"]').val(data.lat);
            $('input[name="longitude"]').val(data.lng);
        } else {
            if (data.response.status == 'ZERO_RESULTS') {
                alert_float('warning', "<?php echo _l('g_search_address_not_found'); ?>");
            } else {
                alert_float('danger', data.response.status);
            }
        }
    });
}

setTimeout(function () {
    init_datepicker();
}, 1000);

function onChangeUpdateDetails(id, column, val) {
    $.post(admin_url + "Scheduled_services/update_column_details", {
        id:id,
        column:column,
        val: val
    }).done(function (result) {
        if(result){
            result = JSON.parse(result);
            if(column == 'running_days' || column == 'running_hpy' || column == 'running_hpd'){
                $('#itm_running_days_'+id).val(result.running_days);
                $('#itm_running_hpd_'+id).val(result.running_hpd);
                $('#itm_running_hpy_'+id).val(result.running_hpy);
            }
        }
        // result = JSON.parse(result);
        // console.log(result);
        // if (result != '') {
        // $("#add-profile-attachment-wrapper").html(result.result);
        //console.log(result.result);
        // }
    });
}

function new_lead_status_inline() {
    _gen_lead_add_inline_on_select_field('divisions');
}
function _gen_lead_add_inline_on_select_field(type) {
    var html = '';
    html = "<div id=\"new_lead_" + type + "_inline\" class=\"form-group\"><label for=\"new_" + type + "_name\">" + $('label[for=\"' + type + '\"]').html().trim() + "</label><div class=\"input-group\"><input type=\"text\" id=\"new_" + type + "_name\" name=\"new_" + type + "_name\" class=\"form-control\"><div class=\"input-group-addon\"><a href=\"#\" onclick=\"lead_add_inline_select_submit('" + type + "'); return false;\" class=\"lead-add-inline-submit-" + type + "\"><i class=\"fa fa-check\"></i></a></div></div></div>";
    $('.form-group-select-input-' + type).after(html);
    $('body').find('#new_' + type + '_name').focus();
    $('.lead-save-btn,#form_info button[type="submit"],#leads-email-integration button[type="submit"],.btn-import-submit').prop('disabled', true);
    $(".inline-field-new").addClass('disabled').css('opacity', 0.5);
    $('.form-group-select-input-' + type).addClass('hide');
}
function lead_add_inline_select_submit(type) {
    var val = $('#new_' + type + '_name').val().trim();
    if (val !== '') {

        var requestURI = type;
        if (type.indexOf('lead_') > -1) {
            requestURI = requestURI.replace('lead_', '');
        }

        var data = {};
        data.name = val;
        data.inline = true;
        $.post(admin_url + 'clients/' + requestURI, data).done(function(response) {
            response = JSON.parse(response);
            if (response.success === true || response.success == 'true') {
                var select = $('body').find('select#' + type);
                select.append('<option value="' + response.id + '">' + val + '</option>');
                select.selectpicker('val', response.id);
                select.selectpicker('refresh');
                select.parents('.form-group').removeClass('has-error');
            }
        });
    }

    $('#new_lead_' + type + '_inline').remove();
    $('.form-group-select-input-' + type).removeClass('hide');
    $('.lead-save-btn,#form_info button[type="submit"],#leads-email-integration button[type="submit"],.btn-import-submit').prop('disabled', false);
    $(".inline-field-new").removeClass('disabled').removeAttr('style');
}

function changeAvgCalculation(is_hour) {
    var avg_running_hour_day = $('#avg_running_hour_day');
    var avg_running_hour_year = $('#avg_running_hour_year');
    if(is_hour){
        avg_running_hour_year.val((365 * avg_running_hour_day.val()).toFixed(2));
    }else {
        avg_running_hour_day.val((avg_running_hour_year.val() / 365).toFixed(2));
    }
}


// Maybe in modal? Eq convert to invoice or convert proposal to estimate/invoice
if(typeof(jQuery) != 'undefined'){
    init_item_js();
} else {
    window.addEventListener('load', function () {
        var initItemsJsInterval = setInterval(function(){
            if(typeof(jQuery) != 'undefined') {
                init_item_js();
                clearInterval(initItemsJsInterval);
            }
        }, 1000);
    });
}
// Items add/edit
function manage_invoice_items(form) {
    $("#new_products").find('textarea[name="bom"]').val(tinyMCE.get("bom").getContent());
    var data = $(form).serialize();
    var url = form.action;
    $.post(url, data).done(function (response) {
        window.location.reload();
        /*response = JSON.parse(response);
        if (response.success == true) {
            var item_select = $('#item_select');
            if ($("body").find('.accounting-template').length > 0) {
                if (!item_select.hasClass('ajax-search')) {
                    var group = item_select.find('[data-group-id="' + response.item.group_id + '"]');
                    if (group.length == 0) {
                        var _option = '<optgroup label="' + (response.item.group_name == null ? '' : response.item.group_name) + '" data-group-id="' + response.item.group_id + '">' + _option + '</optgroup>';
                        if (item_select.find('[data-group-id="0"]').length == 0) {
                            item_select.find('option:first-child').after(_option);
                        } else {
                            item_select.find('[data-group-id="0"]').after(_option);
                        }
                    } else {
                        group.prepend('<option data-subtext="' + response.item.long_description + '" value="' + response.item.itemid + '">(' + accounting.formatNumber(response.item.rate) + ') ' + response.item.description + '</option>');
                    }
                }
                if (!item_select.hasClass('ajax-search')) {
                    item_select.selectpicker('refresh');
                } else {

                    item_select.contents().filter(function () {
                        return !$(this).is('.newitem') && !$(this).is('.newitem-divider');
                    }).remove();

                    var clonedItemsAjaxSearchSelect = item_select.clone();
                    item_select.selectpicker('destroy').remove();
                    $("body").find('.items-select-wrapper').append(clonedItemsAjaxSearchSelect);
                    init_ajax_search('items', '#item_select.ajax-search', undefined, admin_url + 'items/search');
                }

                add_item_to_preview(response.item.itemid);
            } else {
                // Is general items view
                $('.table-invoice-items').DataTable().ajax.reload(null, false);
            }
            alert_float('success', response.message);
        }
        $('#new_products').modal('hide');*/
    }).fail(function (data) {
        alert_float('danger', data.responseText);
    });
    return false;
}
function init_item_js() {
    // Items modal show action
    $("body").on('show.bs.modal', '#new_products', function (event) {

        $('.affect-warning').addClass('hide');

        var $itemModal = $('#new_products');
        $('input[name="invoice_custom"]').val('');
        $itemModal.find('input').not('input[type="hidden"]').val('');
        $itemModal.find('textarea').val('');
        $itemModal.find('select').selectpicker('val', '').selectpicker('refresh');
        $('select[name="tax2"]').selectpicker('val', '').change();
        $('select[name="tax"]').selectpicker('val', '').change();
        $itemModal.find('.add-title').removeClass('hide');
        $itemModal.find('.edit-title').addClass('hide');

        var id = $(event.relatedTarget).data('id');
        // If id found get the text from the datatable
        if (typeof (id) !== 'undefined') {

            $('.affect-warning').removeClass('hide');
            $('input[name="itemid"]').val(id);

            requestGetJSON('invoices/get_item_by_id/' + id).done(function (response) {
                if(typeof response.invoice_no_full == "undefined"){
                    $itemModal.find('input[name="invoice_custom"]').val(response.custom_invoice);
                }else {
                    $itemModal.find('input[name="invoice_custom"]').val(response.invoice_no_full);
                }
                $itemModal.find('input[name="invoice_no"]').val(response.rel_id);
                $itemModal.find('select[name="group_id"]').selectpicker('val', response.group_id).change();
                $itemModal.find('input[name="id"]').val(response.id);
                $itemModal.find('input[name="description"]').val(response.description);
                $itemModal.find('input[name="fab_no"]').val(response.fab_no);
                $itemModal.find('textarea[name="bom"]').val(response.bom);
                $itemModal.find('textarea[name="long_description"]').val(response.long_description.replace(/(<|<)br\s*\/*(>|>)/g, " "));
                $('#custom_fields_items').html(response.custom_fields_html);
                init_selectpicker();
                init_color_pickers();
                init_datepicker();
                validate_item_form();
            });

        }
    });

    $("body").on("hidden.bs.modal", '#new_products', function (event) {
        $('#item_select').selectpicker('val', '');
    });

    validate_item_form();
}
function validate_item_form(){
    // Set validation for invoice item form
    _validate_form($('#product-form'), {
        invoice_custom: 'required',
        description: 'required',
        group_id: 'required',
    }, manage_invoice_items);
}
if($('.table-scheduled-services').length > 0){
    var scheduledServicesParams = {},
        scheduledServices_Filters = $('._hidden_inputs._filters.scheduledServices_filters input');
    $.each(scheduledServices_Filters, function() {
        scheduledServicesParams[$(this).attr('name')] = '[name="' + $(this).attr('name') + '"]';
    });
    initDataTable('.table-scheduled-services', admin_url + 'scheduled_services/table?id=<?php echo $client->userid; ?>', undefined, undefined, scheduledServicesParams, [0, 'asc']);

    $(document).on('change', '.selectpicker.service_dropdown', function(e){
        e.preventDefault();
        let id = $(this).data('id');
        if($("#itm_fab_no_"+id).val() == ""){
            alert_float("warning","Please fill FAB No.");
            return false;
        }
        if($("#itm_running_days_"+id).val() == ""){
            alert_float("warning","Please fill RDPY.");
            return false;
        }
        if($("#itm_running_hpd_"+id).val() == ""){
            alert_float("warning","Please fill RHPD.");
            return false;
        }
        if($("#itm_running_hpy_"+id).val() == ""){
            alert_float("warning","Please fill RHPY.");
            return false;
        }
        if($("#installation_date_"+id).val() == ""){
            alert_float("warning","Please fill Installation Date.");
            return false;
        }
        if($(this).val() != ""){
            window.location = $(this).val();
        }
    });
}
</script>
