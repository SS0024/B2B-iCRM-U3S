<?php echo form_hidden('_attachment_sale_id', $proposal->id); ?>
<?php echo form_hidden('_attachment_sale_type', 'inquiry'); ?>
<div class="panel_s">
    <div class="panel-body">
        <div class="horizontal-scrollable-tabs preview-tabs-top">
            <div class="scroller arrow-left"><i class="fa fa-angle-left"></i></div>
            <div class="scroller arrow-right"><i class="fa fa-angle-right"></i></div>
            <div class="horizontal-tabs">
                <ul class="nav nav-tabs nav-tabs-horizontal mbot15" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#tab_proposal" aria-controls="tab_proposal" role="tab" data-toggle="tab">
                            <?php echo _l('inquiry'); ?>
                        </a>
                    </li>
                    <?php
                    if (count($proposal->attachments) > 0) { ?>
                        <li role="presentation">
                            <a href="#tab_attachments" aria-controls="tab_attachments" role="tab" data-toggle="tab">
                                <?php echo _l('Attachments'); ?>
                            </a>
                        </li>
                    <?php } ?>
                    <?php if (isset($proposal)) { ?>
                        <!--<li role="presentation">
                            <a href="#tab_comments" onclick="get_proposal_comments(); return false;"
                               aria-controls="tab_comments" role="tab" data-toggle="tab">
                                <?php /*echo _l('proposal_comments'); */?>
                            </a>
                        </li>-->
                        <li role="presentation">
                            <a href="#tab_reminders"
                               onclick="initDataTable('.table-reminders', admin_url + 'misc/get_reminders/' + <?php echo $proposal->id; ?> + '/' + 'inquiry', undefined, undefined, undefined,[1,'asc']); return false;"
                               aria-controls="tab_reminders" role="tab" data-toggle="tab">
                                <?php echo _l('estimate_reminders'); ?>
                                <?php
                                $total_reminders = total_rows('tblreminders',
                                    array(
                                        'isnotified' => 0,
                                        'staff' => get_staff_user_id(),
                                        'rel_type' => 'inquiry',
                                        'rel_id' => $proposal->id
                                    )
                                );
                                if ($total_reminders > 0) {
                                    echo '<span class="badge">' . $total_reminders . '</span>';
                                }
                                ?>
                            </a>
                        </li>
                        <li role="presentation" class="tab-separator">
                            <a href="#tab_tasks"
                               onclick="init_rel_tasks_table(<?php echo $proposal->id; ?>,'inquiry'); return false;"
                               aria-controls="tab_tasks" role="tab" data-toggle="tab">
                                <?php echo _l('tasks'); ?>
                            </a>
                        </li>
                        <li role="presentation">
                            <a href="#tab_activity" aria-controls="tab_activity" role="tab" data-toggle="tab">
                                <?php echo _l('estimate_view_activity_tooltip'); ?>
                            </a>
                        </li>
                        <li role="presentation" class="tab-separator">
                            <a href="#tab_notes"
                               onclick="get_sales_notes(<?php echo $proposal->id; ?>,'inquiries'); return false"
                               aria-controls="tab_notes" role="tab" data-toggle="tab">
                                <?php echo _l('estimate_notes'); ?>
                                <span class="notes-total">
                        <?php if ($totalNotes > 0) { ?>
                            <span class="badge"><?php echo $totalNotes; ?></span>
                        <?php } ?>
                     </span>
                            </a>
                        </li>
                        <li role="presentation" data-toggle="tooltip" title="<?php echo _l('emails_tracking'); ?>"
                            class="tab-separator">
                            <a href="#tab_emails_tracking" aria-controls="tab_emails_tracking" role="tab"
                               data-toggle="tab">
                                <?php if (!is_mobile()) { ?>
                                    <i class="fa fa-envelope-open-o" aria-hidden="true"></i>
                                <?php } else { ?>
                                    <?php echo _l('emails_tracking'); ?>
                                <?php } ?>
                            </a>
                        </li>
                        <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('view_tracking'); ?>"
                            class="tab-separator">
                            <a href="#tab_views" aria-controls="tab_views" role="tab" data-toggle="tab">
                                <?php if (!is_mobile()) { ?>
                                    <i class="fa fa-eye"></i>
                                <?php } else { ?>
                                    <?php echo _l('view_tracking'); ?>
                                <?php } ?>
                            </a>
                        </li>
                        <li role="presentation" data-toggle="tooltip" data-title="<?php echo _l('toggle_full_view'); ?>"
                            class="tab-separator toggle_view">
                            <a href="#" onclick="small_table_full_view(); return false;">
                                <i class="fa fa-expand"></i></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <?php echo format_proposal_status($proposal->status, 'pull-left mright5 mtop5'); ?>
            </div>
            <div class="col-md-9 text-right _buttons proposal_buttons">
                <?php if (has_permission('proposals', '', 'edit')) { ?>
                    <a href="<?php echo admin_url('inquiries/inquiry/' . $proposal->id); ?>" data-placement="left"
                       data-toggle="tooltip" title="<?php echo _l('proposal_edit'); ?>"
                       class="btn btn-default btn-with-tooltip" data-placement="bottom"><i
                                class="fa fa-pencil-square-o"></i></a>
                <?php } ?>
                <div class="btn-group">
                    <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                       aria-expanded="false"><i class="fa fa-file-pdf-o"></i><?php if (is_mobile()) {
                            echo ' PDF';
                        } ?> <span class="caret"></span></a>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <li class="hidden-xs"><a
                                    href="<?php echo admin_url('inquiries/pdf/' . $proposal->id . '?output_type=I'); ?>"><?php echo _l('view_pdf'); ?></a>
                        </li>
                        <li class="hidden-xs"><a
                                    href="<?php echo admin_url('inquiries/pdf/' . $proposal->id . '?output_type=I'); ?>"
                                    target="_blank"><?php echo _l('view_pdf_in_new_window'); ?></a></li>
                        <li>
                            <a href="<?php echo admin_url('inquiries/pdf/' . $proposal->id); ?>"><?php echo _l('download'); ?></a>
                        </li>
                        <li>
                            <a href="<?php echo admin_url('inquiries/pdf/' . $proposal->id . '?print=true'); ?>"
                               target="_blank">
                                <?php echo _l('print'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <!--<a href="#" class="btn btn-default btn-with-tooltip" data-target="#proposal_send_to_customer"
                   data-toggle="modal"><span data-toggle="tooltip" class="btn-with-tooltip"
                                             data-title="<?php /*echo _l('proposal_send_to_email'); */?>"
                                             data-placement="bottom"><i class="fa fa-envelope"></i></span></a>-->
                <div class="btn-group ">
                    <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown"
                            aria-haspopup="true" aria-expanded="false">
                        <?php echo _l('more'); ?> <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-right">
                        <!--<li>
                            <a href="<?php /*echo site_url('proposal/' . $proposal->id . '/' . $proposal->hash); */ ?>"
                               target="_blank"><?php /*echo _l('proposal_view'); */ ?></a>
                        </li>-->
                        <?php if (!empty($proposal->open_till) && date('Y-m-d') < $proposal->open_till && ($proposal->status == 4 || $proposal->status == 1) && is_proposals_expiry_reminders_enabled()) { ?>
                            <li>
                                <a href="<?php echo admin_url('inquiries/send_expiry_reminder/' . $proposal->id); ?>"><?php echo _l('send_expiry_reminder'); ?></a>
                            </li>
                        <?php } ?>
                        <li>
                            <a href="#" data-toggle="modal"
                               data-target="#sales_attach_file"><?php echo _l('invoice_attach_file'); ?></a>
                        </li>
                       <!-- <?php /*if (has_permission('proposals', '', 'create')) { */?>
                            <li>
                                <a href="<?php /*echo admin_url() . 'inquiries/copy/' . $proposal->id; */?>"><?php /*echo _l('proposal_copy'); */?></a>
                            </li>
                        --><?php /*} */?>
                        <?php if ($proposal->estimate_id == NULL && $proposal->invoice_id == NULL) { ?>
                            <?php foreach ($proposal_statuses as $status) {
                                if (has_permission('proposals', '', 'edit')) {
                                    if ($proposal->status != $status) { ?>
                                        <li>
                                            <a href="<?php echo admin_url() . 'inquiries/mark_action_status/' . $status . '/' . $proposal->id; ?>"><?php echo _l('proposal_mark_as', format_proposal_status($status, '', false)); ?></a>
                                        </li>
                                        <?php
                                    }
                                }
                            } ?>
                        <?php } ?>
                        <?php if (!empty($proposal->signature) && has_permission('proposals', '', 'delete')) { ?>
                            <li>
                                <a href="<?php echo admin_url('inquiries/clear_signature/' . $proposal->id); ?>"
                                   class="_delete">
                                    <?php echo _l('clear_signature'); ?>
                                </a>
                            </li>
                        <?php } ?>
                        <?php if (has_permission('proposals', '', 'delete')) { ?>
                            <li>
                                <a href="<?php echo admin_url() . 'inquiries/delete/' . $proposal->id; ?>"
                                   class="text-danger delete-text _delete"><?php echo _l('proposal_delete'); ?></a>
                            </li>
                        <?php } ?>
                    </ul>
                </div>
                <?php
                    $showBtn = false;
                    foreach ($proposal->items as $estimateItem){
                        if($estimateItem['item_other_status'] ==='lost' || $estimateItem['item_other_status'] ==='won'){
                            $showBtn = true;
                        }
                    }
                    ?>
                    <?php if ((has_permission('estimates', '', 'create') || has_permission('invoices', '', 'create')) && $proposal->is_rfr == 0 && $showBtn) { ?>
                        <div class="btn-group">
                            <button type="button"
                                    class="btn btn-success dropdown-toggle<?php if ($proposal->rel_type == 'customer' && total_rows('tblclients', array('active' => 0, 'userid' => $proposal->rel_id)) > 0) {
                                        echo ' disabled';
                                    } ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <?php echo _l('proposal_convert'); ?> <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-right">
                                <?php
                                $disable_convert = false;
                                $not_related = false;
                                if ($proposal->rel_type == 'lead') {
                                    if ((total_rows('tblclients', array('userid' => $proposal->lead_customer_id)) == 0) && (total_rows('tblclients', array('leadid' => $proposal->rel_id)) == 0)) {
                                        $disable_convert = true;
                                        $help_text = 'You need to convert the lead to customer in order to create ';
                                    }
                                } else if (empty($proposal->rel_type)) {
                                    $disable_convert = true;
                                    $help_text = 'The inquiry needs to be related to customer in order to convert to ';
                                }
                                ?>
                                <?php if (has_permission('purchaseorder', '', 'create')) { ?>
                                    <li <?php if ($disable_convert) {
                                        echo 'data-toggle="tooltip" title="' . _l($help_text . _l('inquiry')) . '"';
                                    } ?>>
                                        <a href="<?php echo admin_url('purchaseorder/copy_from_inquiry/' . $proposal->id); ?>" <?php if ($disable_convert) {
                                            echo 'style="cursor:not-allowed;" onclick="return false;"';
                                        } ?>><?php echo _l('convert') . ' PO'; ?></a></li>
                                <?php } ?>
                            </ul>
                        </div>
                    <?php } ?>
            </div>
        </div>
        <div class="clearfix"></div>
        <hr class="hr-panel-heading"/>
        <div class="row">
            <div class="col-md-12">
                <div class="tab-content">
                    <div role="tabpanel" class="tab-pane active" id="tab_proposal">
                        <div class="row mtop10">
                            <?php if ($proposal->status == 3 && !empty($proposal->acceptance_firstname) && !empty($proposal->acceptance_lastname) && !empty($proposal->acceptance_email)) { ?>
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <?php echo _l('accepted_identity_info', array(
                                            _l('proposal_lowercase'),
                                            '<b>' . $proposal->acceptance_firstname . ' ' . $proposal->acceptance_lastname . '</b> (<a href="mailto:' . $proposal->acceptance_email . '">' . $proposal->acceptance_email . '</a>)',
                                            '<b>' . _dt($proposal->acceptance_date) . '</b>',
                                            '<b>' . $proposal->acceptance_ip . '</b>' . (is_admin() ? '&nbsp;<a href="' . admin_url('inquiries/clear_acceptance_info/' . $proposal->id) . '" class="_delete text-muted" data-toggle="tooltip" data-title="' . _l('clear_this_information') . '"><i class="fa fa-remove"></i></a>' : '')
                                        )); ?>
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="col-md-6 col-sm-6">
                                <h4 class="bold">
                                    <?php
                                    $tags = get_tags_in($proposal->id, 'inquiry');
                                    if (count($tags) > 0) {
                                        echo '<i class="fa fa-tag" aria-hidden="true" data-toggle="tooltip" data-title="' . implode(', ', $tags) . '"></i>';
                                    }
                                    ?>
                                    <a href="<?php echo admin_url('inquiries/inquiry/' . $proposal->id); ?>">
                           <span id="proposal-number">
                           <?php echo format_inquiry_number($proposal->id); ?>
                           </span>
                                    </a>
                                </h4>
                                <h5 class="bold mbot15 font-medium"><a
                                            href="<?php echo admin_url('inquiries/inquiry/' . $proposal->id); ?>"><?php echo $proposal->subject; ?></a>
                                </h5>
                                <address>
                                    <?php echo format_organization_info(); ?>
                                </address>

                                <?php
                                if (!empty($proposal->purchaseorders)) {
                                    echo '<p>';
                                    foreach ($proposal->purchaseorders as $purchaseorder) {
                                        echo '<a href="' . admin_url('purchaseorder/list_purchaseorder/' . $purchaseorder['id']) . '" class="btn btn-info" target="_blank">' . format_purchaseorder_number($purchaseorder['id']) . '</a><br><br>';
                                    }
                                    echo '</p>';
                                }
                                ?>
                                <p><b>Admin notes:-</b><br><?= $proposal->adminnote ?></p>
                                <span>Modified: <?= _dt($proposal->modifieddate) ?></span>
                            </div>
                            <div class="col-md-6 col-sm-6 text-right">
                                <address>
                                    <span class="bold"><?php echo _l('proposal_to'); ?>:</span><br/>
                                    <?php echo format_proposal_info($proposal, 'admin'); ?>
                                </address>
                                <?php
                                $divConArray = get_div_cons_by_type('inquiry', $proposal->id);
                                foreach ($divConArray as $con_div) {
                                    ?>
                                    <p class="no-mbot">
                                        <span class="bold"><?php echo 'Division'; ?>:</span>
                                        <?php
                                        echo $con_div['division']; ?>
                                    </p>
                                    <p class="no-mbot">
                                        <span class="bold"><?php echo 'Contact'; ?>:</span>
                                        <?php
                                        echo $con_div['contact_name']; ?>
                                    </p>
                                    <br>
                                    <?php
                                }
                                ?>
                                <p class="no-mbot">
                           <span class="bold">
                           <?php echo _l('proposal_date'); ?>:
                           </span>
                                    <?php echo $proposal->date; ?>
                                </p>

                                <?php if (!empty($proposal->open_till)) { ?>
                                    <p class="no-mbot">
                                        <span class="bold"><?php echo _l('proposal_open_till'); ?>:</span>
                                        <?php echo $proposal->open_till; ?>
                                    </p>
                                <?php } ?>
                                <?php if (!empty($proposal->assigned)) { ?>
                                    <p class="no-mbot">
                                        <span class="bold"><?php echo _l('Assigned'); ?>:</span>
                                        <?php echo get_staff_full_name($proposal->assigned, true); ?>
                                    </p>
                                <?php } ?>
                                <?php if (!empty($proposal->reference_no)) { ?>
                                    <p class="no-mbot">
                                        <span class="bold"><?php echo _l('reference_no'); ?>:</span>
                                        <?php echo $proposal->reference_no; ?>
                                    </p>
                                <?php } ?>
                            </div>
                        </div>
                        <hr class="hr-panel-heading"/>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <?php
                                    $isDiscountDisplay = 0;
                                    foreach ($proposal->items as $item) {
                                        if ($item['item_discount'] > 0) {
                                            $isDiscountDisplay = 1;
                                        }
                                    }
                                    $items_data = get_table_items_and_taxes($proposal->items, 'inquiry', true,0,$isDiscountDisplay);
                                    ?>
                                    <table class="table items invoice-items-preview">
                                        <thead>
                                        <tr>
                                            <th align="center">#</th>
                                            <th class="description" width="50%"
                                                align="left"><?php echo _l('invoice_table_item_heading'); ?></th>
                                            <?php
                                            $custom_fields = get_items_custom_fields_for_table_html($proposal->id, 'inquiry');
                                            foreach ($custom_fields as $cf) {
                                                echo '<th class="custom_field" align="left">' . $cf['name'] . '</th>';
                                            }
                                            $qty_heading = _l('invoice_table_quantity_heading');
                                            if ($proposal->show_quantity_as == 2) {
                                                $qty_heading = _l('invoice_table_hours_heading');
                                            } else if ($proposal->show_quantity_as == 3) {
                                                $qty_heading = _l('invoice_table_quantity_heading') . '/' . _l('invoice_table_hours_heading');
                                            }
                                            ?>
                                            <th align="right"><?php echo $qty_heading; ?></th>
                                            <th align="right"><?php echo _l('invoice_table_rate_heading'); ?></th>
                                            <?php if (get_option('show_tax_per_item') == 1) { ?>
                                                <th align="right"><?php echo _l('invoice_table_tax_heading'); ?></th>
                                            <?php } ?>
                                            <?php //if(isset($items_data['is_disc']) && $items_data['is_disc'] == 1){ p //}
                                            if ($isDiscountDisplay) {
                                                ?>
                                                <th align="right"><?php echo _l('estimate_table_item_discount_heading'); ?></th>
                                                <?php
                                            }
                                            ?>
                                            <th align="right"><?php echo _l('invoice_table_amount_heading'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $taxes = $items_data['taxes'];
                                        echo $items_data['html'];
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                            <div class="col-md-5 col-md-offset-7">
                                <table class="table text-right">
                                    <tbody>
                                    <tr id="subtotal">
                                        <td><span class="bold"><?php echo _l('invoice_subtotal'); ?></span>
                                        </td>
                                        <td class="subtotal">
                                            <?php echo format_money($proposal->subtotal, $proposal->symbol); ?>
                                        </td>
                                    </tr>
                                    <?php if (is_sale_discount_applied($proposal)) { ?>
                                        <tr>
                                            <td>
                  <span class="bold"><?php echo _l('invoice_discount'); ?>
                      <?php if (is_sale_discount($proposal, 'percent')) { ?>
                          (<?php echo _format_number($proposal->discount_percent, true); ?>%)
                      <?php } ?></span>
                                            </td>
                                            <td class="discount">
                                                <?php echo '-' . format_money($proposal->discount_total, $proposal->symbol); ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                    <tr>
                                        <td>
                                            <span class="bold"><?php echo _l('estimate_packing_and_forwarding'); ?></span>
                                        </td>
                                        <td class="packing_and_forwarding">
                                            <?php echo format_money($proposal->packing_and_forwarding, $proposal->symbol); ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <span class="bold"><?php echo _l('estimate_servicecharge'); ?></span>
                                        </td>
                                        <td class="servicecharge">
                                            <?php echo format_money($proposal->servicecharge, $proposal->symbol); ?>
                                        </td>
                                    </tr>
                                    <?php
                                    if ($proposal->devide_gst == 1 && $proposal->total_tax != 0) {
                                        $cgst = $proposal->total_tax / 2;
                                        $sgst = $proposal->total_tax / 2;

                                        echo '<tr class="tax-area"><td class="bold">CGST</td><td>' . format_money($cgst, $proposal->symbol) . '</td></tr>';
                                        echo '<tr class="tax-area"><td class="bold">SGST</td><td>' . format_money($sgst, $proposal->symbol) . '</td></tr>';
                                    } else {
                                        echo '<tr class="tax-area"><td class="bold">IGST</td><td>' . format_money($proposal->total_tax, $proposal->symbol) . '</td></tr>';
                                    }

                                    ?>
                                    <tr>
                                        <td><span class="bold"><?php echo _l('invoice_total'); ?></span>
                                        </td>
                                        <td class="total">
                                            <?php echo format_money($proposal->total, $proposal->symbol); ?>
                                        </td>
                                    </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <b style="color:black" class="company-name-formatted text-capitalize"><?= amount_in_word($proposal->total); ?></b>
                        <hr class="hr-panel-heading test"/>

                        <?php if ($proposal->clientnote != '') { ?>
                            <div class="mtop15">
                                <p class="bold text-muted"><?php echo _l('estimate_note'); ?></p>
                                <p><?php echo $proposal->clientnote; ?></p>
                            </div>
                        <?php } ?>
                        <?php if ($proposal->terms != '') { ?>
                            <div class="mtop15">
                                <p class="bold text-muted"><?php echo _l('terms_and_conditions'); ?></p>
                                <p><?php echo $proposal->terms; ?></p>
                            </div>
                        <?php } ?>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="tab_attachments">
                        <?php
                        if (count($proposal->attachments) > 0) { ?>
                            <p class="bold"><?php echo _l('Inquiry Files'); ?></p>
                            <?php foreach ($proposal->attachments as $attachment) {
                                $attachment_url = site_url('download/file/sales_attachment/' . $attachment['attachment_key']);
                                if (!empty($attachment['external'])) {
                                    $attachment_url = $attachment['external_link'];
                                }
                                ?>
                                <div class="mbot15 row" data-attachment-id="<?php echo $attachment['id']; ?>">
                                    <div class="col-md-8">
                                        <div class="pull-left"><i
                                                    class="<?php echo get_mime_class($attachment['filetype']); ?>"></i>
                                        </div>
                                        <a href="<?php echo $attachment_url; ?>"
                                           target="_blank"><?php echo $attachment['file_name']; ?></a>
                                        <br/>
                                        <small class="text-muted"> <?php echo $attachment['filetype']; ?></small>
                                    </div>
                                    <div class="col-md-4 text-right">
                                        <?php if ($attachment['visible_to_customer'] == 0) {
                                            $icon = 'fa-toggle-off';
                                            $tooltip = _l('show_to_customer');
                                        } else {
                                            $icon = 'fa-toggle-on';
                                            $tooltip = _l('hide_from_customer');
                                        }
                                        ?>
                                        <a href="#" data-toggle="tooltip"
                                           onclick="toggle_file_visibility(<?php echo $attachment['id']; ?>,<?php echo $proposal->id; ?>,this); return false;"
                                           data-title="<?php echo $tooltip; ?>"><i class="fa <?php echo $icon; ?>"
                                                                                   aria-hidden="true"></i></a>
                                        <?php if ($attachment['staffid'] == get_staff_user_id() || is_admin()) { ?>
                                            <a href="#" class="text-danger"
                                               onclick="delete_inquiry_attachment(<?php echo $attachment['id']; ?>); return false;"><i
                                                        class="fa fa-times"></i></a>
                                        <?php } ?>
                                    </div>
                                </div>
                            <?php } ?>
                        <?php } ?>
                        <div class="clearfix"></div>
                    </div>
                    <!--<div role="tabpanel" class="tab-pane" id="tab_comments">
                        <div class="row proposal-comments mtop15">
                            <div class="col-md-12">
                                <div id="proposal-comments"></div>
                                <div class="clearfix"></div>
                                <textarea name="content" id="comment" rows="4"
                                          class="form-control mtop15 proposal-comment"></textarea>
                                <button type="button" class="btn btn-info mtop10 pull-right"
                                        onclick="add_proposal_comment();"><?php /*echo _l('proposal_add_comment'); */?></button>
                            </div>
                        </div>
                    </div>-->
                    <div role="tabpanel" class="tab-pane" id="tab_notes">
                        <?php echo form_open(admin_url('inquiries/add_note/' . $proposal->id), array('id' => 'sales-notes', 'class' => 'inquiry-notes-form')); ?>
                        <?php echo render_textarea('description'); ?>
                        <div class="text-right">
                            <button type="submit"
                                    class="btn btn-info mtop15 mbot15"><?php echo _l('estimate_add_note'); ?></button>
                        </div>
                        <?php echo form_close(); ?>
                        <hr/>
                        <div class="panel_s mtop20 no-shadow" id="sales_notes_area">
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="tab_activity">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="activity-feed">
                                    <?php foreach ($activity as $activity) {
                                        $_custom_data = false;
                                        ?>
                                        <div class="feed-item" data-sale-activity-id="<?php echo $activity['id']; ?>">
                                            <div class="date">
                              <span class="text-has-action" data-toggle="tooltip"
                                    data-title="<?php echo _dt($activity['date']); ?>">
                              <?php echo time_ago($activity['date']); ?>
                              </span>
                                            </div>
                                            <div class="text">
                                                <?php if (is_numeric($activity['staffid']) && $activity['staffid'] != 0) { ?>
                                                    <a href="<?php echo admin_url('profile/' . $activity["staffid"]); ?>">
                                                        <?php echo staff_profile_image($activity['staffid'], array('staff-profile-xs-image pull-left mright5'));
                                                        ?>
                                                    </a>
                                                <?php } ?>
                                                <?php
                                                $additional_data = '';
                                                if (!empty($activity['additional_data'])) {
                                                    $additional_data = unserialize($activity['additional_data']);
                                                    $i = 0;
                                                    foreach ($additional_data as $data) {
                                                        if (strpos($data, '<original_status>') !== false) {
                                                            $original_status = get_string_between($data, '<original_status>', '</original_status>');
                                                            $additional_data[$i] = format_estimate_status($original_status, '', false);
                                                        } else if (strpos($data, '<new_status>') !== false) {
                                                            $new_status = get_string_between($data, '<new_status>', '</new_status>');
                                                            $additional_data[$i] = format_estimate_status($new_status, '', false);
                                                        } else if (strpos($data, '<status>') !== false) {
                                                            $status = get_string_between($data, '<status>', '</status>');
                                                            $additional_data[$i] = format_estimate_status($status, '', false);
                                                        } else if (strpos($data, '<custom_data>') !== false) {
                                                            $_custom_data = get_string_between($data, '<custom_data>', '</custom_data>');
                                                            unset($additional_data[$i]);
                                                        }
                                                        $i++;
                                                    }
                                                }
                                                $_formatted_activity = _l($activity['description'], $additional_data);
                                                if ($_custom_data !== false) {
                                                    $_formatted_activity .= ' - ' . $_custom_data;
                                                }
                                                if (!empty($activity['full_name'])) {
                                                    $_formatted_activity = $activity['full_name'] . ' - ' . $_formatted_activity;
                                                }
                                                echo $_formatted_activity;
                                                if (is_admin()) {
                                                    echo '<a href="#" class="pull-right text-danger" onclick="delete_sale_activity(' . $activity['id'] . '); return false;"><i class="fa fa-remove"></i></a>';
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="tab_emails_tracking">
                        <?php
                        $this->load->view('admin/includes/emails_tracking', array(
                                'tracked_emails' =>
                                    get_tracked_emails($proposal->id, 'inquiry'))
                        );
                        ?>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="tab_tasks">
                        <?php init_relation_tasks_table(array('data-new-rel-id' => $proposal->id, 'data-new-rel-type' => 'inquiry')); ?>
                    </div>
                    <div role="tabpanel" class="tab-pane" id="tab_reminders">
                        <a href="#" data-toggle="modal" class="btn btn-info"
                           data-target=".reminder-modal-inquiry-<?php echo $proposal->id; ?>"><i
                                    class="fa fa-bell-o"></i> <?php echo _l('Set inquiry reminder'); ?></a>
                        <hr/>
                        <?php render_datatable(array(_l('reminder_description'), _l('reminder_date'), _l('reminder_staff'), _l('reminder_is_notified')), 'reminders'); ?>
                        <?php $this->load->view('admin/includes/modals/reminder', array('id' => $proposal->id, 'name' => 'inquiry', 'members' => $members, 'reminder_title' => _l('Set inquiry reminder'))); ?>
                    </div>
                    <div role="tabpanel" class="tab-pane ptop10" id="tab_views">
                        <?php
                        $views_activity = get_views_tracking('inquiry', $proposal->id);
                        if (count($views_activity) === 0) {
                            echo '<h4 class="no-margin">' . _l('not_viewed_yet', _l('proposal_lowercase')) . '</h4>';
                        }
                        foreach ($views_activity as $activity) { ?>
                            <p class="text-success no-margin">
                                <?php echo _l('view_date') . ': ' . _dt($activity['date']); ?>
                            </p>
                            <p class="text-muted">
                                <?php echo _l('view_ip') . ': ' . $activity['view_ip']; ?>
                            </p>
                            <hr/>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('admin/inquiries/send_proposal_to_email_template'); ?>
<script>
    init_btn_with_tooltips();
    init_datepicker();
    init_selectpicker();
    init_form_reminder();
    init_tabs_scrollable();
    // defined in manage proposals
    proposal_id = '<?php echo $proposal->id; ?>';
    init_proposal_editor();
</script>
