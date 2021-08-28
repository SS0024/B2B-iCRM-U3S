<script>
    var salesChart;
    var groupsChart;
    var paymentMethodsChart;
    var customersTable;
    var report_from = $('input[name="report-from"]');
    var report_to = $('input[name="report-to"]');
    var report_customers = $('#customers-report');
    var report_customers_groups = $('#customers-group');
    var report_invoices = $('#invoices-report');
    var report_estimates = $('#estimates-report');
    var report_rfr_export = $('#rfr-export-report');
    var report_proposals = $('#product-inquiries-report');
    var report_inquiry = $('#report-inquiry');
    var report_product_status_quoted_items = $('#report-product-status-quoted-items');
    var report_items = $('#items-report');
    var report_credit_notes = $('#credit-notes');
    var report_payments_received = $('#payments-received-report');
    var date_range = $('#date-range');
    var report_from_choose = $('#report-time');
    var fnServerParams = {
        "report_months": '[name="months-report"]',
        "report_from": '[name="report-from"]',
        "report_to": '[name="report-to"]',
        "report_currency": '[name="currency"]',
        "invoice_status": '[name="invoice_status"]',
        "estimate_status": '[name="estimate_status"]',
        "sale_agent_invoices": '[name="sale_agent_invoices"]',
        "sale_agent_items": '[name="sale_agent_items"]',
        "sale_agent_estimates": '[name="sale_agent_estimates"]',
        "lead_status": '[name="lead_status"]',
        "lead_source": '[name="lead_source"]',
        "proposals_sale_agents": '[name="proposals_sale_agents"]',
        "proposal_status": '[name="proposal_status"]',
        "credit_note_status": '[name="credit_note_status"]',
    }
    $(function () {
        $('select[name="currency"],select[name="invoice_status"],select[name="estimate_status"],select[name="lead_status"],select[name="lead_source"],select[name="sale_agent_invoices"],select[name="sale_agent_items"],select[name="sale_agent_estimates"],select[name="payments_years"],select[name="proposals_sale_agents"],select[name="proposal_status"],select[name="credit_note_status"]').on('change', function () {
            gen_reports();
        });

        report_from.on('change', function () {
            var val = $(this).val();
            var report_to_val = report_to.val();
            if (val != '') {
                report_to.attr('disabled', false);
                if (report_to_val != '') {
                    gen_reports();
                }
            } else {
                report_to.attr('disabled', true);
            }
        });

        report_to.on('change', function () {
            var val = $(this).val();
            if (val != '') {
                gen_reports();
            }
        });

        $('select[name="months-report"]').on('change', function () {
            var val = $(this).val();
            report_to.attr('disabled', true);
            report_to.val('');
            report_from.val('');
            if (val == 'custom') {
                date_range.addClass('fadeIn').removeClass('hide');
                return;
            } else {
                if (!date_range.hasClass('hide')) {
                    date_range.removeClass('fadeIn').addClass('hide');
                }
            }
            gen_reports();
        });

        $('.table-payments-received-report').on('draw.dt', function () {
            var paymentReceivedReportsTable = $(this).DataTable();
            var sums = paymentReceivedReportsTable.ajax.json().sums;
            $(this).find('tfoot').addClass('bold');
            $(this).find('tfoot td').eq(0).html("<?php echo _l('invoice_total'); ?> (<?php echo _l('per_page'); ?>)");
            $(this).find('tfoot td.total').html(sums.total_amount);
        });

        $('.table-proposals-report').on('draw.dt', function () {
            var proposalsReportTable = $(this).DataTable();
            var sums = proposalsReportTable.ajax.json().sums;
            add_common_footer_sums($(this), sums);
            <?php foreach($proposal_taxes as $key => $tax){ ?>
            $(this).find('tfoot td.total_tax_single_<?php echo $key; ?>').html(sums['total_tax_single_<?php echo $key; ?>']);
            <?php } ?>
        });

        $('.table-invoices-report').on('draw.dt', function () {
            var invoiceReportsTable = $(this).DataTable();
            var sums = invoiceReportsTable.ajax.json().sums;
            add_common_footer_sums($(this), sums);
            $(this).find('tfoot td.amount_open').html(sums.amount_open);
            $(this).find('tfoot td.applied_credits').html(sums.applied_credits);
            <?php foreach($invoice_taxes as $key => $tax){ ?>
            $(this).find('tfoot td.total_tax_single_<?php echo $key; ?>').html(sums['total_tax_single_<?php echo $key; ?>']);
            <?php } ?>
        });

        $('.table-credit-notes-report').on('draw.dt', function () {
            var creditNotesTable = $(this).DataTable();
            var sums = creditNotesTable.ajax.json().sums;
            add_common_footer_sums($(this), sums);
            $(this).find('tfoot td.remaining_amount').html(sums.remaining_amount);
            <?php foreach($credit_note_taxes as $key => $tax){ ?>
            $(this).find('tfoot td.total_tax_single_<?php echo $key; ?>').html(sums['total_tax_single_<?php echo $key; ?>']);
            <?php } ?>
        });

        $('.table-estimates-report').on('draw.dt', function () {
            var estimatesReportsTable = $(this).DataTable();
            var sums = estimatesReportsTable.ajax.json().sums;

            add_common_footer_sums($(this), sums);
            <?php foreach($estimate_taxes as $key => $tax){ ?>
            $(this).find('tfoot td.total_tax_single_<?php echo $key; ?>').html(sums['total_tax_single_<?php echo $key; ?>']);
            <?php } ?>
        });

        $('.table-items-report').on('draw.dt', function () {
            var itemsTable = $(this).DataTable();
            var sums = itemsTable.ajax.json().sums;
            $(this).find('tfoot').addClass('bold');
            $(this).find('tfoot td').eq(0).html("<?php echo _l('invoice_total'); ?> (<?php echo _l('per_page'); ?>)");
            $(this).find('tfoot td.amount').html(sums.total_amount);
            $(this).find('tfoot td.qty').html(sums.total_qty);
        });

    });

    function add_common_footer_sums(table, sums) {
        // table.find('tfoot').addClass('bold');
        //table.find('tfoot td').eq(0).html("<?php //echo _l('invoice_total'); ?>// (<?php //echo _l('per_page'); ?>//)");
        // table.find('tfoot td.subtotal').html(sums.subtotal);
        // table.find('tfoot td.total').html(sums.total);
        // table.find('tfoot td.total_tax').html(sums.total_tax);
        // table.find('tfoot td.total_IGST').html(sums.igst);
        // table.find('tfoot td.total_CGST').html(sums.cgst);
        // table.find('tfoot td.total_SGST').html(sums.sgst);
        // table.find('tfoot td.discount_total').html(sums.discount_total);
        // table.find('tfoot td.adjustment').html(sums.adjustment);
        // table.find('tfoot td.servicecharge').html(sums.servicecharge);
        // table.find('tfoot td.packing_and_forwarding').html(sums.packing_and_forwarding);
    }

    function init_report(e, type) {
        var report_wrapper = $('#report');

        if (report_wrapper.hasClass('hide')) {
            report_wrapper.removeClass('hide');
        }

        $('head title').html($(e).text());
        $('.customers-group-gen').addClass('hide');

        report_credit_notes.addClass('hide');
        report_customers_groups.addClass('hide');
        report_customers.addClass('hide');
        report_invoices.addClass('hide');
        report_rfr_export.addClass('hide');
        report_estimates.addClass('hide');
        report_payments_received.addClass('hide');
        report_items.addClass('hide');
        report_proposals.addClass('hide');
        report_inquiry.addClass('hide');
        report_product_status_quoted_items.addClass('hide');

        $('#income-years').addClass('hide');
        $('.chart-income').addClass('hide');
        $('.chart-payment-modes').addClass('hide');


        report_from_choose.addClass('hide');

        $('select[name="months-report"]').selectpicker('val', 'this_month');
        // Clear custom date picker
        report_to.val('');
        report_from.val('');
        $('#currency').removeClass('hide');

        if (type != 'total-income' && type != 'payment-modes') {
            report_from_choose.removeClass('hide');
        }

        if (type == 'estimates-report') {
            report_estimates.removeClass('hide');
        } else if (type == 'product-inquiries-report') {
            report_proposals.removeClass('hide');
        } else if (type == 'rfr-export-report') {
            report_rfr_export.removeClass('hide');
        } else if (type == 'report-inquiry') {
            report_inquiry.removeClass('hide');
        } else if (type == 'report-product-status-quoted-items') {
            report_product_status_quoted_items.removeClass('hide');
        }
        gen_reports();
    }

    function estimates_report() {
        if ($.fn.DataTable.isDataTable('.table-estimates-report')) {
            $('.table-estimates-report').DataTable().destroy();
        }
        initDataTable('.table-estimates-report', admin_url + 'reports/inquiries_report', false, false, fnServerParams, [
            [3, 'desc'],
            [0, 'desc']
        ]).columns.adjust();
    }

    function proposals_report() {
        if ($.fn.DataTable.isDataTable('.table-proposals-report')) {
            $('.table-proposals-report').DataTable().destroy();
        }

        initDataTable('.table-proposals-report', admin_url + 'reports/product_inquiries_report', false, false, fnServerParams, [0, 'desc']);
    }

    function rfr_export_report() {
        if ($.fn.DataTable.isDataTable('.table-rfr-report')) {
            $('.table-rfr-report').DataTable().destroy();
        }

        initDataTable('.table-rfr-report', admin_url + 'reports/rfr_export_report', false, false, fnServerParams, [0, 'desc']);
    }

    function inquiry_report() {
        if ($.fn.DataTable.isDataTable('.table-inquiry-report')) {
            $('.table-inquiry-report').DataTable().destroy();
        }

        initDataTable('.table-inquiry-report', admin_url + 'reports/inquiry_export_report', false, false, fnServerParams, [0, 'desc']);
    }

    function product_status_quated_item_report() {
        if ($.fn.DataTable.isDataTable('.table-product-status-quoted-item-report')) {
            $('.table-product-status-quoted-item-report').DataTable().destroy();
        }

        initDataTable('.table-product-status-quoted-item-report', admin_url + 'reports/product_status_quated_item_report', false, false, fnServerParams, [0, 'desc']);
    }
   
    // Main generate report function
    function gen_reports() {

        if (!report_estimates.hasClass('hide')) {
            estimates_report();
        } else if (!report_proposals.hasClass('hide')) {
            proposals_report();
        } else if (!report_rfr_export.hasClass('hide')) {
            rfr_export_report();
        }else if (!report_inquiry.hasClass('hide')) {
            inquiry_report();
        }else if (!report_product_status_quoted_items.hasClass('hide')) {
            product_status_quated_item_report();
        }
    }

    $(".product_wise_report").click();
</script>
