<div class="panel_s<?php if (!isset($invoice) || (isset($invoice) && count($invoices_to_merge) == 0 && (isset($invoice) && !isset($invoice_from_project) && count($expenses_to_bill) == 0 || $invoice->status == 5))) {
    echo ' hide';
} ?>" id="invoice_top_info">
    <div class="panel-body">
        <div class="row">
            <div id="merge" class="col-md-6">
                <?php if (isset($invoice)) {
                    $this->load->view('admin/invoices/merge_invoice', array('invoices_to_merge' => $invoices_to_merge));
                } ?>
            </div>
        </div>
    </div>
</div>
<style>
    .bootstrap-select:not([class*=col-]):not([class*=form-control]):not(.input-group-btn){
        display: inherit;
    }
</style>
<div class="panel_s purchase accounting-template">
    <div class="additional"></div>
    <div class="panel-body">
        <?php
        $statuses = [
            ['id'=>'ordered', 'name'=>'Ordered'],
            ['id'=>'partial_received', 'name'=>'Partial Received'],
            ['id'=>'full_received', 'name'=>'Full Received'],
            ['id'=>'cancelled', 'name'=>'Cancelled'],
        ];
        $statusesValue = [
            'ordered' =>'Ordered',
            'partial_received' =>'Partial Received',
            'full_received'=>'Full Received',
            'cancelled'=>'Cancelled',
        ];
        if (isset($invoice)) { ?>
            <span class="label label-default default s-status mtop5 estimate-status-<?= $invoice->status; ?> estimate-status-default"><?= $statusesValue[$invoice->status];  ?></span>
            <hr class="hr-panel-heading"/>
        <?php } ?>
        <div class="row">
            <div class="col-md-6">
                <?php
                $next_estimate_number = get_option('next_mfpurchase_number');
                $format = get_option('purchase_number_format');
                if (isset($invoice)) {
                    $format = $invoice->number_format;
                }

                $prefix = get_option('purchase_prefix');

                if ($format == 1) {
                    $__number = $next_estimate_number;
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
                        $__number = $next_estimate_number;
                        $prefix = $prefix . '<span id="prefix_year">' . date('Y') . '</span>/';
                    }
                } else if ($format == 3) {
                    if (isset($invoice)) {
                        $yy = date('y', strtotime($invoice->date));
                        $__number = $invoice->number;
                        $prefix = '<span id="prefix">' . $invoice->prefix . '</span>';
                    } else {
                        $yy = date('y');
                        $__number = $next_estimate_number;
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
                        $__number = $next_estimate_number;
                    }
                }

                $_estimate_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
                $isedit = isset($invoice) ? 'true' : 'false';
                $data_original_number = isset($invoice) ? $invoice->number : 'false';
                ?>
                <div class="form-group">
                    <label for="number"><?php echo _l('Indent Number'); ?></label>
                    <div class="input-group">
                  <span class="input-group-addon">
                  <?php if (isset($invoice)) { ?>
                      <a href="#" onclick="return false;" data-toggle="popover" data-container='._transaction_form'
                         data-html="true"
                         data-content="<label class='control-label'><?php echo _l('settings_sales_estimate_prefix'); ?></label><div class='input-group'><input name='s_prefix' type='text' class='form-control' value='<?php echo $invoice->prefix; ?>'></div><button type='button' onclick='save_sales_number_settings(this); return false;' data-url='<?php echo admin_url('purchaseorder/update_number_settings/' . $invoice->id); ?>' class='btn btn-info btn-block mtop15'><?php echo _l('submit'); ?></button>"><i
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
            </div>
            <div class="col-md-6">
                <?php $value = (isset($invoice) ? $invoice->reference_no : ''); ?>
                <?php echo render_input('reference_no', 'Reference No', $value); ?>
            </div>
            <div class="clearfix"></div>
            <div class="col-md-6">
                <?php $value = (isset($invoice) ? _dt($invoice->date) : _dt(date('Y-m-d g:i A'))); ?>
                <?php echo render_datetime_input('date', 'Date', $value); ?>
            </div>
            <div class="col-md-6">
                <?php $value = isset($invoice) ? $invoice->status : ''; ?>
                <?php echo render_select('status', $statuses, array('id', 'name'), 'Status', $value, ['data-width'=>'100%','disabled'=> isset($invoice)], '', '', '', false); ?>
            </div>
            <div class="col-md-6">
                <?php $value = (isset($invoice) ? $invoice->adminnote : ''); ?>
                <?php echo render_textarea('adminnote', 'estimate_add_edit_admin_note', $value); ?>
            </div>
            <div class="col-md-6 row">
                <div class="col-md-6">
                    <?php
                    $i = 0;
                    $selected = '';
                    foreach ($staff as $member) {
                        if (isset($invoice)) {
                            if ($invoice->raised_by == $member['staffid']) {
                                $selected = $member['staffid'];
                            }
                        } elseif (get_staff_user_id() == $member['staffid']) {
                            $selected = $member['staffid'];
                        }
                        $i++;
                    }
                    echo render_select('raised_by', $staff, array('staffid', array('firstname', 'lastname')), 'mf_raised_by', $selected);
                    ?>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="approved_by"><?= _l("mf_approved_by") ?></label>
                        <div class="input-group">
                            <div class="input-group">
                          <?php
                          $check = '';
                          foreach ($staff as $member) {
                               if (get_staff_user_id() == $member['staffid']) {
                                $User_ID = $member['staffid'];
                                 $User_first_Name = $member['firstname'];
                                 $User_last_Name = $member['lastname'];
                                }
                              if (isset($invoice)) {
                                  if ($invoice->approved_by != null) {
                                      $check = 'checked';
                                  }

                              }
                                } ?>
                                <input type="text" readonly class="form-control" placeholder="<?php echo $User_first_Name. ' ' . $User_last_Name;?>">
                                <span class="input-group-addon">
                                    <input type="checkbox" <?php echo $check; ?> name="approved_by" id="approved_by" value="<?php echo $User_ID; ?>" />
                                  </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $this->load->view('admin/mfpurchases/_add_edit_items'); ?>


</div>
<div class="row">
    <div class="col-md-12 mtop15">
        <div class="panel-body bottom-transaction">
            <?php $value = (isset($invoice) ? $invoice->clientnote : get_option('predefined_clientnote_invoice')); ?>
            <?php echo render_textarea('clientnote', 'Supplier Note', $value, array(), array(), 'mtop15'); ?>
            <?php $value = (isset($invoice) ? $invoice->terms : get_option('predefined_terms_invoice')); ?>
            <?php echo render_textarea('terms', 'terms_and_conditions', $value, array(), array(), 'mtop15'); ?>
            <div class="btn-bottom-toolbar text-right">
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