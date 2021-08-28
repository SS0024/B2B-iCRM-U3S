<div class="panel-body mtop10">
    <div class="row">
        <div class="col-md-8">
            <div class="col-md-5">
                <?php $this->load->view('admin/invoice_items/item_select'); ?>
            </div>
            <div class="col-md-3">
                <input type="text" name="changeable_fab_no" class="form-control" placeholder="Enter Fab No." value="">
            </div>
            <div class="col-md-4">
                <button type="button" class="btn-tr btn btn-info mleft10" onclick="openItemBom()">
                    <?php echo _l('View BOM'); ?>
                </button>
            </div>
        </div>
        <div class="col-md-4 text-right show_quantity_as_wrapper">
            <div class="mtop10">
                <span><?php echo _l('show_quantity_as'); ?></span>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" value="1" id="1" name="show_quantity_as"
                           data-text="<?php echo _l('estimate_table_quantity_heading'); ?>" <?php if (isset($estimate) && $estimate->show_quantity_as == 1) {
                        echo 'checked';
                    } elseif (isset($invoice) && $invoice->show_quantity_as == 1) {
                        echo 'checked';
                    } else {
                        echo "checked";
                    } ?>>
                    <label for="1"><?php echo _l('quantity_as_qty'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" value="2" id="2" name="show_quantity_as"
                           data-text="<?php echo _l('estimate_table_hours_heading'); ?>" <?php if (isset($estimate) && $estimate->show_quantity_as == 2){
                        echo 'checked';
                    }elseif (isset($invoice) && $invoice->show_quantity_as == 2) ?>>
                    <label for="2"><?php echo _l('quantity_as_hours'); ?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="3" value="3" name="show_quantity_as"
                           data-text="<?php echo _l('estimate_table_quantity_heading'); ?>/<?php echo _l('estimate_table_hours_heading'); ?>" <?php if (isset($estimate) && $estimate->show_quantity_as == 3){
                        echo 'checked';
                    }elseif (isset($invoice) && $invoice->show_quantity_as == 3) ?>>
                    <label for="3"><?php echo _l('estimate_table_quantity_heading'); ?>
                        /<?php echo _l('estimate_table_hours_heading'); ?></label>
                </div>
            </div>
        </div>
    </div>
    <div class="table-responsive s_table">
        <table class="table estimate-items-table items table-main-estimate-edit no-mtop">
            <thead>
            <tr>
                <th></th>
                <th width="25%" align="left"><i class="fa fa-exclamation-circle" aria-hidden="true"
                                                data-toggle="tooltip"
                                                data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i> <?php echo _l('estimate_table_item_heading'); ?>
                </th>
                <th width="25%" align="left"><?php echo _l('estimate_table_item_description'); ?></th>
                <th width="20%" align="left"><?php echo _l('Item Group'); ?></th>
                <?php
//                $custom_fields = get_custom_fields('items');
                foreach ($custom_fields as $cf) {
                    echo '<th width="10%" align="left" class="custom_field">' . $cf['name'] . '</th>';
                }
                if (isset($bodyclass) && $bodyclass == 'invoice') {
                    echo '<th width="10%" align="left" class="custom_field">FAB No</th>';
                }
                $qty_heading = _l('estimate_table_quantity_heading');
                if (isset($estimate) && $estimate->show_quantity_as == 2) {
                    $qty_heading = _l('estimate_table_hours_heading');
                } elseif (isset($invoice) && $invoice->show_quantity_as == 3) {
                    $qty_heading = _l('estimate_table_quantity_heading') . '/' . _l('estimate_table_hours_heading');
                }
                ?>
                <th width="15%" class="qty" align="right"><?php echo $qty_heading; ?></th>

                <th align="center"><i class="fa fa-cog"></i></th>
            </tr>
            </thead>
            <tbody>
            <tr class="main">
                <td></td>
                <td>
                    <textarea name="description" rows="4" class="form-control"
                              placeholder="<?php echo _l('item_description_placeholder'); ?>"></textarea>
                </td>
                <td>
                    <textarea name="long_description" rows="4" class="form-control"
                              placeholder="<?php echo _l('item_long_description_placeholder'); ?>"></textarea>
                </td>
                <td>
                    <?php echo render_select('group_id', $items_groups, array('id', 'name')); ?>
                </td>
                <?php //echo render_custom_fields_items_table_add_edit_preview();
                if (isset($bodyclass) && $bodyclass == 'invoice') {
                    ?>
                    <td>
                        <div class="form-group" app-field-wrapper="">
                            <input type="text" name="fab_no" class="form-control" placeholder="" value="">
                        </div>
                        <!--                       <input type="text" placeholder="FAB No." class="form-control input-transparent text-right">-->
                    </td>
                    <?php
                }
                ?>
                <td>
                    <input type="number" name="quantity" min="0" value="1" class="form-control"
                           placeholder="<?php echo _l('item_quantity_placeholder'); ?>">
                    <input type="text" placeholder="<?php echo _l('unit'); ?>" name="unit"
                           class="form-control input-transparent text-right">
                    <label id="stockdetails">Stock : 0</label>
                </td>


                <!--<td class="pro_amt_td">
                    <input type="number" name="item_amount" class="form-control"
                           placeholder="<?php /*echo _l('item_amount_placeholder'); */ ?>" onchange="get_item_rate();"
                           onblur="get_item_rate();" min="0" value="0">
                </td>

                <td class="pro_disc_td">
                    <div class="input-group">
                        <input type="number" name="item_discount" class="form-control"
                               placeholder="<?php /*echo _l('item_discount_placeholder'); */ ?>" onchange="get_item_rate()"
                               min="0" value="0">
                        <div class="input-group-addon">
                            <span class="discount-total-type-selected">%</span>
                        </div>
                    </div>
                </td>


                <td class="pro_rate_td">
                    <input type="number" name="rate" class="form-control"
                           placeholder="<?php /*echo _l('item_rate_placeholder'); */ ?>" readonly="readonly">
                </td>
                <td>
                    <?php
                /*                    $default_tax = unserialize(get_option('default_tax'));
                                    $select = '<select class="selectpicker display-block tax main-tax" data-width="100%" name="taxname" multiple data-none-selected-text="' . _l('no_tax') . '">';
                                    foreach ($taxes as $tax) {
                                        $selected = '';
                                        if (is_array($default_tax)) {
                                            if (in_array($tax['name'] . '|' . $tax['taxrate'], $default_tax)) {
                                                $selected = ' selected ';
                                            }
                                        }
                                        $select .= '<option value="' . $tax['name'] . '|' . $tax['taxrate'] . '"' . $selected . 'data-taxrate="' . $tax['taxrate'] . '" data-taxname="' . $tax['name'] . '" data-subtext="' . $tax['name'] . '">' . $tax['taxrate'] . '%</option>';
                                    }
                                    $select .= '</select>';
                                    echo $select;
                                    */ ?>
                </td>
                <td class="pro_total_amt_td"></td>-->
                <td>
                    <?php
                    $new_item = 'undefined';
                    if (isset($estimate)) {
                        $new_item = true;
                    } elseif (isset($invoice)) {
                        $new_item = true;
                    }
                    ?>
                    <button type="button"
                            onclick="add_item_to_table('undefined','undefined',<?php echo $new_item; ?>); return false;"
                            class="btn pull-right btn-info"><i class="fa fa-check"></i></button>
                </td>
            </tr>
            <?php if (isset($estimate) || isset($add_items) || isset($invoice)) {
                $i = 1;

                $items_indicator = 'newitems';
                if (isset($estimate)) {
                    $add_items = $estimate->items;
                    $items_indicator = 'items';
                } elseif (isset($invoice)) {
                    $add_items = $invoice->items;
                    $items_indicator = 'items';
                }

                foreach ($add_items as $item) {
                    $manual = false;
                    $table_row = '<tr class="sortable item">';
                    $table_row .= '<td class="dragger">';
                    if ($item['qty'] == '' || $item['qty'] == 0) {
                        $item['qty'] = 1;
                    }
                    if (!isset($is_proposal)) {
                        $estimate_item_taxes = get_estimate_item_taxes($item['id']);
                    } else {
                        $estimate_item_taxes = get_proposal_item_taxes($item['id']);
                    }
                    if (isset($invoice)) {
                        $estimate_item_taxes = get_invoice_item_taxes($item['id']);;
                    }
                    if ($item['id'] == 0) {
                        $estimate_item_taxes = $item['taxname'];
                        $manual = true;
                    }
                    $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][itemid]', $item['id']);
                    $amount = $item['rate'] * $item['qty'];
                    $amount = _format_number($amount);
                    // order input
                    $table_row .= '<input type="hidden" class="order" name="' . $items_indicator . '[' . $i . '][order]">';
                    $table_row .= '</td>';
                    $table_row .= '<td class="bold description"><textarea name="' . $items_indicator . '[' . $i . '][description]" class="form-control" rows="5">' . clear_textarea_breaks($item['description']) . '</textarea></td>';
                    $table_row .= '<td><textarea name="' . $items_indicator . '[' . $i . '][long_description]" class="form-control" rows="5">' . clear_textarea_breaks($item['long_description']) . '</textarea>';
                    if ($bodyclass == 'estimate') {
                        $table_row .= '<select class="selectpicker display-block mtop2" name="' . $items_indicator . '[' . $i . '][item_other_status]" onblur="calculate_total();" onchange="calculate_total();"  data-width="100%" data-none-selected-text="Select product status">
                               <option value="">Select product status</option>
                               <option ' . ($item['item_other_status'] == 'lost' ? 'selected="selected"' : '') . ' value="lost">Lost</option>
                               <option ' . ($item['item_other_status'] == 'won' ? 'selected="selected"' : '') . ' value="won">Won</option>
                           </select>
                           <input name="' . $items_indicator . '[' . $i . '][item_remark]" value="' . $item["item_remark"] . '" onblur="calculate_total();" onchange="calculate_total();"  class="form-control mtop2" placeholder="Remarks">';
                    }
                    $table_row .= '</td>';
                    $table_row .= '<td>' . render_select('group_id', $items_groups, array('id', 'name'), '', $item['group_id']) . '</td>';
//                    $table_row .= render_custom_fields_items_table_in($item, $items_indicator . '[' . $i . ']');

                    if (isset($bodyclass) && $bodyclass == 'invoice') {
                        $table_row .= '<td><input type="text" name="' . $items_indicator . '[' . $i . '][fab_no]" value="' . $item['fab_no'] . '" class="form-control">';
                    }

                    $table_row .= '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity="' . $item['fab_no'] . '" name="' . $items_indicator . '[' . $i . '][qty]" value="' . $item['qty'] . '" class="form-control">';
                    $unit_placeholder = '';
                    if (!$item['unit']) {
                        $unit_placeholder = _l('unit');
                        $item['unit'] = '';
                    }
                    $table_row .= '<input type="text" placeholder="' . $unit_placeholder . '" name="' . $items_indicator . '[' . $i . '][unit]" class="form-control input-transparent text-right" value="' . $item['unit'] . '">';
                    $table_row .= '<label id="stockdetails">Stock : '.get_stock_item_details_from_code($item['description']).'</label></td>';


                    /*$table_row .= '<td class="item_amount pro_amt_td"><input type="number" data-toggle="tooltip" title="' . _l('numbers_not_formatted_while_editing') . '" onblur="calculate_total();" onchange="calculate_total();" name="' . $items_indicator . '[' . $i . '][item_amount]" value="' . $item['item_amount'] . '" class="form-control" min="0"></td>';


                    $table_row .= '<td class="item_discount pro_disc_td"><div class="input-group"><input type="number" data-toggle="tooltip" title="' . _l('numbers_not_formatted_while_editing') . '" onblur="calculate_total();" onchange="calculate_total();" name="' . $items_indicator . '[' . $i . '][item_discount]" value="' . $item['item_discount'] . '" class="form-control" min="0"><div class="input-group-addon"><span class="discount-total-type-selected">%</span></div></div></td>';


                    $table_row .= '<td class="rate pro_rate_td"><input type="number" data-toggle="tooltip" title="' . _l('numbers_not_formatted_while_editing') . '" onblur="calculate_total();" onchange="calculate_total();" name="' . $items_indicator . '[' . $i . '][rate]" value="' . $item['rate'] . '" class="form-control" readonly="readonly"></td>';

                    $table_row .= '<td class="taxrate">' . $this->misc_model->get_taxes_dropdown_template('' . $items_indicator . '[' . $i . '][taxname][]', $estimate_item_taxes, (isset($is_proposal) ? 'proposal' : 'estimate'), $item['id'], true, $manual) . '</td>';

                    $table_row .= '<td class="amount pro_total_amt_td" align="right">' . $amount . '</td>';*/
                    $table_row .= '<td><a href="#" class="btn btn-danger pull-right" onclick="delete_item(this,' . $item['id'] . '); return false;"><i class="fa fa-times"></i></a></td>';
                    $table_row .= '</tr>';
                    echo $table_row;
                    $i++;
                }
            }
            ?>
            </tbody>
        </table>
    </div>
    <div class="col-md-8 col-md-offset-4">
        <table class="table text-right">
            <tbody>

            <!--<tr id="bulk">
                <td><span class="bold">Bulk : </span>
                </td>
                <td class="bulk_area">
                    <input type="checkbox" class="bulk_effect" name="is_bulk"
                           id="is_bulk" <?php /*if (isset($estimate) && $estimate->is_bulk == 1) {
                        echo "checked";
                    } elseif (isset($invoice) && $invoice->is_bulk == 1) {
                        echo "checked";
                    } */ ?> >
                </td>
            </tr>-->

            <!--<tr id="subtotal">
                <td><span class="bold"><?php /*echo _l('estimate_subtotal'); */ ?> :</span>
                </td>
                <td class="subtotal">
                </td>
            </tr>
            <tr id="discount_area">
                <td>
                    <div class="row">
                        <div class="col-md-7">
                            <span class="bold"><?php /*echo _l('estimate_discount'); */ ?></span>
                        </div>
                        <div class="col-md-5">
                            <div class="input-group" id="discount-total">

                                <input type="number" value="<?php /*if (isset($estimate)) {
                                    echo $estimate->discount_percent;
                                } elseif (isset($invoice)) {
                                    echo $invoice->discount_percent;
                                } else {
                                    echo "0";
                                } */ ?>"
                                       class="form-control pull-left input-discount-percent<?php /*if (isset($estimate) && !is_sale_discount($estimate, 'percent') && is_sale_discount_applied($estimate)) {
                                           echo ' hide';
                                       } elseif (isset($invoice) && !is_sale_discount($invoice, 'percent') && is_sale_discount_applied($invoice)) {
                                           echo ' hide';
                                       } */ ?>" min="0" max="100" name="discount_percent">

                                <input type="number" data-toggle="tooltip"
                                       data-title="<?php /*echo _l('numbers_not_formatted_while_editing'); */ ?>"
                                       value="<?php /*if (isset($estimate)) {
                                           echo $estimate->discount_total;
                                       } elseif (isset($invoice)) {
                                           echo $invoice->discount_total;
                                       } else {
                                           echo "0";
                                       } */ ?>"
                                       class="form-control pull-left input-discount-fixed<?php /*if (!isset($estimate) || (isset($estimate) && !is_sale_discount($estimate, 'fixed'))){
                                           echo ' hide';
                                       }elseif (!isset($invoice) || (isset($invoice) && !is_sale_discount($invoice, 'fixed'))) */ ?>"
                                       min="0" name="discount_total">

                                <div class="input-group-addon">
                                    <div class="dropdown">
                                        <a class="dropdown-toggle" href="#" id="dropdown_menu_tax_total_type"
                                           data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                 <span class="discount-total-type-selected">
                                  <?php /*if (!isset($estimate) || isset($estimate) && (is_sale_discount($estimate, 'percent') || !is_sale_discount_applied($estimate))) {
                                      echo '%';
                                  } elseif (!isset($invoice) || isset($invoice) && (is_sale_discount($invoice, 'percent') || !is_sale_discount_applied($invoice))) {
                                      echo '%';
                                  } else {
                                      echo _l('discount_fixed_amount');
                                  }
                                  */ ?>
                                 </span>
                                            <span class="caret"></span>
                                        </a>
                                        <ul class="dropdown-menu" id="discount-total-type-dropdown"
                                            aria-labelledby="dropdown_menu_tax_total_type">
                                            <li>
                                                <a href="#"
                                                   class="discount-total-type discount-type-percent<?php /*if (!isset($estimate) || (isset($estimate) && is_sale_discount($estimate, 'percent')) || (isset($estimate) && !is_sale_discount_applied($estimate))) {
                                                       echo ' selected';
                                                   } elseif (!isset($invoice) || (isset($invoice) && is_sale_discount($invoice, 'percent')) || (isset($invoice) && !is_sale_discount_applied($invoice))) {
                                                       echo ' selected';
                                                   } */ ?>">%</a>
                                            </li>
                                            <li>
                                                <a href="#"
                                                   class="discount-total-type discount-type-fixed<?php /*if (isset($estimate) && is_sale_discount($estimate, 'fixed')) {
                                                       echo ' selected';
                                                   } elseif (isset($invoice) && is_sale_discount($invoice, 'fixed')) {
                                                       echo ' selected';
                                                   } */ ?>">
                                                    <?php /*echo _l('discount_fixed_amount'); */ ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </td>
                <td class="discount-total"></td>
            </tr>

            <tr>
                <td>
                    <div class="row">
                        <div class="col-md-7">
                            <span class="bold"><?php /*echo _l('estimate_packing_and_forwarding'); */ ?></span>
                        </div>
                        <div class="col-md-5">

                            <input type="number" data-toggle="tooltip"
                                   data-title="<?php /*echo _l('numbers_not_formatted_while_editing'); */ ?>"
                                   value="<?php /*if (isset($estimate)) {
                                       echo $estimate->packing_and_forwarding;
                                   } elseif (isset($proposal)) {
                                       echo $proposal->packing_and_forwarding;
                                   } elseif (isset($invoice)) {
                                       echo $invoice->packing_and_forwarding;
                                   } else {
                                       echo 0;
                                   } */ ?>" class="form-control pull-left" name="packing_and_forwarding">
                        </div>
                    </div>
                </td>
                <td class="packing_and_forwarding"></td>
            </tr>

            <tr id="service_charge">
                <td>
                    <div class="row">
                        <div class="col-md-7">
                            <span class="bold"><?php /*echo _l('estimate_servicecharge'); */ ?></span>
                        </div>
                        <div class="col-md-5">

                            <input type="number" data-toggle="tooltip"
                                   data-title="<?php /*echo _l('numbers_not_formatted_while_editing'); */ ?>"
                                   value="<?php /*if (isset($estimate)) {
                                       echo $estimate->servicecharge;
                                   } elseif (isset($proposal)) {
                                       echo $proposal->servicecharge;
                                   } elseif (isset($invoice)) {
                                       echo $invoice->servicecharge;
                                   } else {
                                       echo 0;
                                   } */ ?>" class="form-control pull-left" name="servicecharge">
                        </div>
                    </div>
                </td>
                <td class="servicecharge"></td>
            </tr>-->

            <!--<tr>
               <td>
                  <div class="row">
                     <div class="col-md-7">
                        <span class="bold"><?php //echo _l('estimate_transportation'); ?></span>
                     </div>
                     <div class="col-md-5">
					 
                        <input type="number" data-toggle="tooltip" data-title="<?php //echo _l('numbers_not_formatted_while_editing'); ?>" value="<?php //if(isset($estimate)){echo $estimate->transportation; } else { echo 0; } ?>" class="form-control pull-left" name="transportation">
                     </div>
                  </div>
               </td>
               <td class="transportation"></td>
            </tr>-->

            <!--<tr>
               <td>
                  <div class="row">
                     <div class="col-md-7">
                        <span class="bold"><?php //echo _l('estimate_adjustment'); ?></span>
                     </div>
                     <div class="col-md-5">
					 
                        <input type="number" data-toggle="tooltip" data-title="<?php //echo _l('numbers_not_formatted_while_editing'); ?>" value="<?php //if(isset($estimate)){echo $estimate->adjustment; } else { echo 0; } ?>" class="form-control pull-left" name="adjustment">
                     </div>
                  </div>
               </td>
               <td class="adjustment"></td>
            </tr> -->


            <!--<tr>
                <td><span class="bold"><?php /*echo _l('estimate_total'); */ ?> :</span>
                </td>
                <td class="total">
                </td>
            </tr>-->
            </tbody>
        </table>
    </div>

    <!--<div class="row" id="tax_summry_area_div">
        <div class="control-label"><span class="bold">Tax Summery</span></div>
        <table class="table" id="tax_summry_area">
            <thead>
            <tr>
                <th align="left">#</th>

                <th align="left">Name</th>

                <th align="left">Code</th>

                <th align="left">Qty</th>

                <th align="left">Tax Excl Amt</th>

                <th align="left">Tax Amount</th>
            </tr>
            </thead>
            <tbody id="tax_summry_area_body">
            </tbody>
        </table>
    </div>-->

    <div id="removed-items"></div>
</div>
