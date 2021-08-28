<div class="panel-body mtop10">
    <div class="row">
        <div class="col-md-4">
            <?php $this->load->view('admin/invoice_items/item_select'); ?>
        </div>
        <div class="col-md-8 text-right show_quantity_as_wrapper">
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
                <!--<div class="radio radio-primary radio-inline">
                    <input type="radio" value="2" id="2" name="show_quantity_as"
                           data-text="<?php /*echo _l('estimate_table_hours_heading'); */?>" <?php /*if (isset($estimate) && $estimate->show_quantity_as == 2){
                        echo 'checked';
                    }elseif (isset($invoice) && $invoice->show_quantity_as == 2) */?>>
                    <label for="2"><?php /*echo _l('quantity_as_hours'); */?></label>
                </div>
                <div class="radio radio-primary radio-inline">
                    <input type="radio" id="3" value="3" name="show_quantity_as"
                           data-text="<?php /*echo _l('estimate_table_quantity_heading'); */?>/<?php /*echo _l('estimate_table_hours_heading'); */?>" <?php /*if (isset($estimate) && $estimate->show_quantity_as == 3){
                        echo 'checked';
                    }elseif (isset($invoice) && $invoice->show_quantity_as == 3) */?>>
                    <label for="3"><?php /*echo _l('estimate_table_quantity_heading'); */?>
                        /<?php /*echo _l('estimate_table_hours_heading'); */?></label>
                </div>-->
            </div>
        </div>
    </div>
    <div class="table-responsive s_table">
        <table class="table estimate-items-table items table-main-estimate-edit no-mtop">
            <thead>
            <tr>
                <th></th>
                <th width="20%" align="left"><i class="fa fa-exclamation-circle" aria-hidden="true"
                                                data-toggle="tooltip"
                                                data-title="<?php echo _l('item_description_new_lines_notice'); ?>"></i> <?php echo _l('estimate_table_item_heading'); ?>
                </th>
                <th width="20%" align="left"><?php echo _l('estimate_table_item_description'); ?></th>
                <th width="35%" class="received_qty" align="left"> <?php echo "Suppliers"; ?></th>
                <th width="10%" align="left"><?php echo _l('Item Group'); ?></th>
                <?php
                $qty_heading = _l('estimate_table_quantity_heading');
                if (isset($estimate) && $estimate->show_quantity_as == 2) {
                    $qty_heading = _l('estimate_table_hours_heading');
                } elseif (isset($invoice) && $invoice->show_quantity_as == 3) {
                    $qty_heading = _l('estimate_table_quantity_heading') . '/' . _l('estimate_table_hours_heading');
                }
                ?>
                <th width="10%" class="qty" align="right"><?php echo $qty_heading; ?></th>

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
                    <div class="form-group mbot15 select-placeholder">
                        <div class="form-group">
                            <label for="supplier" class="control-label">Supplier</label>
                            <select class="selectpicker suppliers" name="suppliers[]" data-actions-box="true"
                                    multiple="true" data-width="100%"
                                    data-title="<?php echo _l('dropdown_non_selected_tex'); ?>">


                                <?php
                                $this->db->select('id, company as name');
                                $Spplier_list= $this->db->get('tblsuppliers')->result_array();

                                foreach ($Spplier_list as $supplier) {
                                    $selected = '';
                                    if (isset($invoice)) {
                                        if ($invoice->allowed_payment_modes) {
                                            $inv_modes = unserialize($invoice->allowed_payment_modes);
                                            if (is_array($inv_modes)) {
                                                foreach ($inv_modes as $_allowed_payment_mode) {
                                                    if ($_allowed_payment_mode == $supplier['id']) {
                                                        $selected = ' selected';
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                            $selected = '';
                                    }
                                    ?>
                                    <option value="<?php echo $supplier['id']; ?>"<?php echo $selected; ?>><?php echo $supplier['name']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </td>
                <td>
                    <?php echo render_select('group_id', $items_groups, array('id', 'name')); ?>
                </td>
                <td>
                    <input type="number" name="quantity" min="0" value="1" class="form-control"
                           placeholder="<?php echo _l('item_quantity_placeholder'); ?>">
                    <input type="text" placeholder="<?php echo _l('unit'); ?>" name="unit"
                           class="form-control input-transparent text-right">
                </td>
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
                            onclick="add_item_to_MfPtable('undefined','undefined',<?php echo $new_item; ?>); return false;"
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

                    /*if (!isset($is_proposal)) {
                        $estimate_item_taxes = get_estimate_item_taxes($item['id']);
                    } else {
                        $estimate_item_taxes = get_proposal_item_taxes($item['id']);
                    }
                    if (isset($invoice)) {
                        $estimate_item_taxes = get_invoice_item_taxes($item['id']);;
                    }*/
                    $this->db->where('itemid', $item['id']);
                    $this->db->where('rel_type', 'purchase');
                    $taxes = $this->db->get('tblitemstax')->result_array();
                    $l     = 0;
                    foreach ($taxes as $tax) {
                        $taxes[$l]['taxname'] = $tax['taxname'] . '|' . $tax['taxrate'];
                        $taxes[$l]['onlytaxname'] = $tax['taxname'];
                        $taxes[$l]['taxnum'] = $tax['taxrate'] + 0;
                        $l++;
                    }
                    if ($item['id'] == 0) {
                        $estimate_item_taxes = $item['taxname'];
                        $manual = true;
                    }else{
                        $estimate_item_taxes = $taxes;
                    }
                    $table_row .= form_hidden('' . $items_indicator . '[' . $i . '][itemid]', $item['id']);
                    $amount = $item['real_unit_cost'] * $item['quantity'];

                    $amount = _format_number($amount);
                    // order input
                    $table_row .= '<input type="hidden" class="order" name="' . $items_indicator . '[' . $i . '][order]">';
                    $table_row .= '</td>';
                    $table_row .= '<td class="bold description"><textarea name="' . $items_indicator . '[' . $i . '][description]" class="form-control description-area" rows="5">' . clear_textarea_breaks($item['product_code']) . '</textarea></td>';
                    $table_row .= '<td><textarea name="' . $items_indicator . '[' . $i . '][long_description]" class="form-control" rows="5">' . clear_textarea_breaks($item['product_name']) . '</textarea>';
                    $table_row .= '</td>';

                    $table_row .='<td> <div class="form-group mbot15 select-placeholder"> <div class="form-group"> <label for="supplier" class="control-label">Supplier</label> <select class="selectpicker suppliers" name="newitems['.$i.'][suppliers][]" data-actions-box="true" multiple="true" data-width="100%" data-title="'. _l('dropdown_non_selected_tex') . '">';
                          $this->db->select("id, company as name");
                          $Sppliers_list= $this->db->get("tblsuppliers")->result_array();

                           foreach ($Sppliers_list as $supplier) {
                           $selected = "";
                            if (isset($invoice)) {
                                  if ($invoice->id) {
                                      $supplier_rel_id = $invoice->id;
                                      $supplier_item_id = $item['id'];
                                      $this->db->select("*");
                                      $this->db->where("itemid" , $supplier_item_id);
                                      $Spplier_list= $this->db->get("tblmfitemssupplier")->result_array();

                                     foreach ($Spplier_list as $Selected_Supplier) {
                                         if ($Selected_Supplier['suppliers_id'] == $supplier["id"]) {
                                                     $selected = "selected";
                                         }
                                     }
                                 }
                            } else {

                                  $selected = " ";
                            }

            $table_row .=' <option value=" '.  $supplier['id']. ' " '. $selected .' > '. $supplier['name'] .'</option>';
              }
            $table_row .= '</select> </div> </div> </td>';
                    $table_row .= '<td>' . render_select($items_indicator."[$i][group_id]", $items_groups, array('id', 'name'), '', $item['group_id']) . '</td>';


                    $table_row .= '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity="' . round($item['quantity']) . '" name="' . $items_indicator . '[' . $i . '][qty]" value="' . round($item['quantity']) . '" class="form-control item-qty">';

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



    <div id="removed-items"></div>
</div>

<script>


    // Append the added items to the preview to the MfPurchase table as items
    function add_item_to_MfPtable(data, itemid, merge_invoice, bill_expense) {

        // If not custom data passed get from the preview
        data = typeof(data) == 'undefined' || data == 'undefined' ? get_item_Mfpreview_values() : data;

        if (data.description === "" && data.long_description === "" ) { return; }
        var table_row = '';
        var unit_placeholder = '';
        var item_key = $("body").find('tbody .item').length + 1;

        table_row += '<tr class="sortable item" data-merge-invoice="' + merge_invoice + '" data-bill-expense="' + bill_expense + '">';

        table_row += '<td class="dragger">';

        // Check if quantity is number
        if (isNaN(data.qty)) {
            data.qty = 1;
        }

        $("body").append('<div class="dt-loader"></div>');
        var regex = /<br[^>]*>/gi;
        get_suppliers_dropdown_template(item_key, data.suppliers).done(function(Supplier_dropdown) {

            // order input
            table_row += '<input type="hidden" class="order" name="newitems[' + item_key + '][order]">';

            table_row += '</td>';

            table_row += '<td class="bold description"><textarea name="newitems[' + item_key + '][description]" class="form-control" rows="5">' + data.description + '</textarea></td>';

            table_row += '<td><textarea name="newitems[' + item_key + '][long_description]" class="form-control item_long_description" rows="5">' + data.long_description.replace(regex, "\n") + '</textarea></td>';
             table_row += '<td class="taxrate">' + Supplier_dropdown + '</td>';


            get_groupID_dropdown_template(item_key, data.group_id).done(function(Item_group_dropdown) {
                table_row += '<td class="taxrate">' + Item_group_dropdown + '</td>';

            });
            // var options = $("#group_id").html();
            // table_row +=  '<td><div class="form-group"><select name="newitems[' + item_key + '][group_id]" data-width="100%" data-none-selected-text="Nothing selected" class="selectpicker">' + options + '</select></div></td>';

            table_row += '<td><input type="number" min="0" onblur="calculate_total();" onchange="calculate_total();" data-quantity name="newitems[' + item_key + '][qty]" value="' + data.qty + '" class="form-control">';

            table_row += '<td><a href="#" class="btn btn-danger pull-left" onclick="delete_item(this,' + itemid + '); return false;"><i class="fa fa-trash"></i></a></td>';

            table_row += '</tr>';

            $('table.items tbody').append(table_row);

            $(document).trigger({
                type: "item-added-to-table",
                data: data,
                row: table_row
            });

            setTimeout(function() {
                calculate_total();
            }, 15);


            if ($('#item_select').hasClass('ajax-search') && $('#item_select').selectpicker('val') !== '') {
                $('#item_select').prepend('<option></option>');
            }

            init_selectpicker();
            init_datepicker();
            init_color_pickers();
            clear_item_preview_values();
            reorder_items();

            $('body').find('#items-warning').remove();
            $("body").find('.dt-loader').remove();
            $('#item_select').selectpicker('val', '');

            // if (cf_has_required && $('.invoice-form').length) {
            //     validate_invoice_form();
            // } else if (cf_has_required && $('.estimate-form').length) {
            //     validate_estimate_form();
            // } else if (cf_has_required && $('.proposal-form').length) {
            //     validate_proposal_form();
            // } else if (cf_has_required && $('.credit-note-form').length) {
            //     validate_credit_note_form();
            // }

            return true;

        });

        return false;
    }

    function get_suppliers_dropdown_template(item_key,supplierid) {

        jQuery.ajaxSetup({ async: false });
        var d = $.post(admin_url + 'misc/get_suppliers_dropdown_template/', {item_key : item_key , supplierid: supplierid });
        jQuery.ajaxSetup({ async: true });

        return d;
    }
    function get_groupID_dropdown_template(item_key, group_id) {

        jQuery.ajaxSetup({ async: false });
        var d = $.post(admin_url + 'misc/get_item_group_dropdown_for_mfitems/', {item_key : item_key , group_id: group_id });
        jQuery.ajaxSetup({ async: true });

        return d;
    }

    function get_item_Mfpreview_values() {
        var response = {};
        response.description = $('.main textarea[name="description"]').val();
        response.long_description = $('.main textarea[name="long_description"]').val();
        response.qty = $('.main input[name="quantity"]').val();
        response.suppliers = $('.main select.suppliers').selectpicker('val');
        response.group_id = $('.main select#group_id').selectpicker('val');
        return response;
    }
</script>