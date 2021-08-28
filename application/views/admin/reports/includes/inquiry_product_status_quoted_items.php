<div id="report-product-status-quoted-items" class="hide">
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                <label for="estimate_status"><?php echo _l('estimate_status'); ?></label>
                <select name="estimate_status" class="selectpicker" multiple data-width="100%"
                        data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
                    <?php foreach ($estimate_statuses as $status) { ?>
                        <option value="<?php echo $status; ?>"><?php echo format_proposal_status($status, '', false) ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>
        <?php if (count($estimates_sale_agents) > 0) { ?>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="sale_agent_estimates"><?php echo _l('sale_agent_string'); ?></label>
                    <select name="sale_agent_estimates" class="selectpicker" multiple data-width="100%"
                            data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
                        <?php foreach ($estimates_sale_agents as $agent) { ?>
                            <option value="<?php echo $agent['sale_agent']; ?>"><?php echo get_staff_full_name($agent['sale_agent']); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        <?php } ?>
        <?php if (count($lead_statuses) > 0) { ?>
            <div class="col-md-3">
                <div class="form-group select-placeholder">
                    <label for="lead_status" class="control-label"><?php echo 'Lead Status'; ?></label>
                    <select name="lead_status" class="selectpicker" multiple data-width="100%"
                            data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
                        <?php foreach ($lead_statuses as $statuses) { ?>

                            <option value="<?php echo $statuses['name']; ?>"><?php echo $statuses['name']; ?></option>
                        <?php } ?>
                    </select>
                    <!--<select class="selectpicker display-block mbot15" name="lead_status" data-width="100%"
                       data-none-selected-text="<?php /*echo _l('dropdown_non_selected_tex'); */ ?>" required>
                   <option value="">Nothing Selected</option>

               </select>-->
                </div>
            </div>
        <?php } ?>
        <?php if (count($lead_sorces) > 0) { ?>
            <div class="col-md-3">
                <div class="form-group select-placeholder">
                    <label class="control-label"><?php echo 'Lead Source'; ?></label>
                    <select name="lead_source" class="selectpicker" multiple data-width="100%"
                            data-none-selected-text="<?php echo _l('invoice_status_report_all'); ?>">
                        <?php foreach ($lead_sorces as $sorces) { ?>
                            <option value="<?php echo $sorces['name']; ?>"><?php echo $sorces['name']; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>
        <?php } ?>
    </div>
    <div class="clearfix"></div>
    <table class="table table-product-status-quoted-item-report scroll-responsive">
        <thead>
        <tr>
            <th><?php echo _l('Assigned To'); ?></th>
            <th>Customer Name</th>
            <th>Inquiry Number</th>
            <th>Inquiry Date</th>
            <th>Inquiry Status</th>
            <th>Lead Status</th>
            <th>Lead Source</th>
            <th>Admin Note</th>
            <th>Reference</th>
            <th>Contact Persons (Mob)</th>
            <th>Division</th>
            <th>Brand</th>
            <th>Group</th>
            <th>Product Name</th>
            <th>Product Description</th>
            <th>Unit</th>
            <th>Rate</th>
            <th>Quantity</th>
            <th>Amount</th>
            <th>Inquiry Total Value</th>
            <th>Po Status</th>
            <th>RFR Status</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>
