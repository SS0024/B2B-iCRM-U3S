<div id="stats-top" class="hide">
    <div class="panel_s mtop20">
        <div class="panel-body">
            <?php
            $where_all = '';
            $total_invoice_items = total_rows('tblitems', $where_all);
            ?>
            <div class="row text-left quick-top-stats">
                <div class="col-lg-5ths col-md-5ths">
                    <div class="row">
                        <div class="col-md-6">
                                <h5>Total Item</h5>
                        </div>
                        <div class="col-md-6 text-right">
                            <?php echo $total_invoice_items; ?> / <?php echo $total_invoice_items; ?>
                        </div>
                        <div class="col-md-12">
                            <div class="progress no-margin">
                                <div class="progress-bar progress-bar-default"
                                     role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"
                                     style="width: 0%" data-percent="100">100%
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $total_by_status = total_rows('tblitems', 'alert_quantity <> 0');
                $percent = ($total_invoice_items > 0 ? number_format(($total_by_status * 100) / $total_invoice_items, 2) : 0);
                ?>
                <div class="col-lg-5ths col-md-5ths">
                    <div class="row">
                        <div class="col-md-7">
                                <h5>Alerted Item</h5>
                        </div>
                        <div class="col-md-5 text-right">
                            <?php echo $total_by_status; ?> / <?php echo $total_invoice_items; ?>
                        </div>
                        <div class="col-md-12">
                            <div class="progress no-margin">
                                <div class="progress-bar progress-bar-danger"
                                     role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"
                                     style="width: 0%" data-percent="<?php echo $percent; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $this->db->select('count(*) as count');
                $this->db->from('tblpurchaseitems');
                $this->db->where('purchase_id IS NOT NULL', null);
                $this->db->where('(`quantity` - `quantity_balance`)  > 0', null);
                $this->db->group_by('product_id');
                $indentQty = $this->db->get()->row();
                $total_by_status = $indentQty->count;
                $percent = ($total_invoice_items > 0 ? number_format(($total_by_status * 100) / $total_invoice_items, 2) : 0);
                ?>
                <div class="col-lg-5ths col-md-5ths">
                    <div class="row">
                        <div class="col-md-7">
                                <h5>Indented Item</h5>
                        </div>
                        <div class="col-md-5 text-right">
                            <?php echo $total_by_status; ?> / <?php echo $total_invoice_items; ?>
                        </div>
                        <div class="col-md-12">
                            <div class="progress no-margin">
                                <div class="progress-bar progress-bar-info"
                                     role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"
                                     style="width: 0%" data-percent="<?php echo $percent; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $total_by_status = total_rows('tblitems', 'hold_stock <> 0');
                $percent = ($total_invoice_items > 0 ? number_format(($total_by_status * 100) / $total_invoice_items, 2) : 0);
                ?>
                <div class="col-lg-5ths col-md-5ths">
                    <div class="row">
                        <div class="col-md-7">
                            <h5>Hold Item</h5>
                        </div>
                        <div class="col-md-5 text-right">
                            <?php echo $total_by_status; ?> / <?php echo $total_invoice_items; ?>
                        </div>
                        <div class="col-md-12">
                            <div class="progress no-margin">
                                <div class="progress-bar progress-bar-<?php echo get_invoice_status_label($status); ?>"
                                     role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"
                                     style="width: 0%" data-percent="<?php echo $percent; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                $total_by_status = total_rows('tblitems', 'quantity <> 0');
                $percent = ($total_invoice_items > 0 ? number_format(($total_by_status * 100) / $total_invoice_items, 2) : 0);
                ?>
                <div class="col-lg-5ths col-md-5ths">
                    <div class="row">
                        <div class="col-md-7">
                                <h5>Available Item</h5>
                        </div>
                        <div class="col-md-5 text-right">
                            <?php echo $total_by_status; ?> / <?php echo $total_invoice_items; ?>
                        </div>
                        <div class="col-md-12">
                            <div class="progress no-margin">
                                <div class="progress-bar progress-bar-<?php echo get_invoice_status_label($status); ?>"
                                     role="progressbar" aria-valuenow="40" aria-valuemin="0" aria-valuemax="100"
                                     style="width: 0%" data-percent="<?php echo $percent; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr/>
</div>
