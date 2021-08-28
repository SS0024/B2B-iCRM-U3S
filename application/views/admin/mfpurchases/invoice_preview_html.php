
<div id="invoice-preview">
    <div class="row">
         <div class="col-md-6 col-sm-6">
            <h4 class="bold">
                <?php
                $tags = get_tags_in($invoice->id, 'invoice');
                if (count($tags) > 0) {
                    echo '<i class="fa fa-tag" aria-hidden="true" data-toggle="tooltip" data-title="' . implode(', ', $tags) . '"></i>';
                }
                ?>
                <a href="<?php echo admin_url('mfpurchases/mfpurchase/' . $invoice->id); ?>">
               <span id="invoice-number">
                  <?php echo format_purchase_number($invoice->id); ?>
               </span>
                </a>
            </h4>

        </div>
        <div class="col-sm-6 text-right">
                <span class="bold"><?php echo _l('ship_to'); ?>:</span>
            <address>
                <?php echo format_organization_info(); ?>
            </address>
            <p class="no-mbot">
            <span class="bold">
               <?php echo _l('Indent Date:-'); ?>
            </span>
                <?php echo $invoice->date; ?>
            </p>
            <?php
            if ($invoice->adminnote != '') {
                ?>
                <p>
                    <b>Admin notes:-</b><br>
                    <?= $invoice->adminnote ?>
                </p>
                <?php
            }  ?>

            <?php $pdf_custom_fields = get_custom_fields('invoice', array('show_on_pdf' => 1));
            foreach ($pdf_custom_fields as $field) {
                $value = get_custom_field_value($invoice->id, $field['id'], 'invoice');
                if ($value == '') {
                    continue;
                } ?>
                <p class="no-mbot">
                    <span class="bold"><?php echo $field['name']; ?>: </span>
                    <?php echo $value; ?>
                </p>
            <?php } ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">

                <table class="table items invoice-items-preview">
                    <thead>
                    <tr>
                        <th align="center">#</th>
                        <th class="description" width="50%"
                            align="left"><?php echo _l('invoice_table_item_heading'); ?></th>
                        <?php
                        $qty_heading = _l('invoice_table_quantity_heading');

                        ?>
                        <th align="right"><?php echo $qty_heading; ?></th>

                        <th align="right"><?php echo 'Item Suppliers'; ?></th>

                        <th align="right"><?php echo 'Item Group'; ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $i = 1;
                    $_calculated_taxes = [];
                    $itemHTML = '';
                    foreach ($invoice->items as $item) {

                            $itemHTML .= '<td align="right">';


                        $item_Suppliers = [];

                        $this->db->where('itemid', $item['id']);
                        $this->db->where('rel_type', 'Mfpurchase');
                        $item_suppliers = $this->db->get('tblmfitemssupplier')->result_array();


                        foreach ($item_suppliers as $supplier) {
//                            print_r($item['id']);
                            $this->db->select('company');
                            $this->db->where('id', $supplier['suppliers_id']);
                            $item_suppliers_company = $this->db->get('tblsuppliers')->result_array();
                            if($supplier['supplier_item_price'] > 0){
                                $supplier_price = $supplier['supplier_item_price'];
                            } else {
                                $supplier_price = '-';
                            }
                            foreach ($item_suppliers_company as $supplier_name) {

                                $itemHTML .= $supplier_name['company'] .'('.$supplier_price.'), <br/>';
                            }
                        }

                            $itemHTML .= '</td>';


                        ?>
                        <tr class="sortable" data-item-id="<?= $item['product_id'] ?>">
                            <td class="dragger item_no ui-sortable-handle" align="center"><?= $i ?></td>
                            <td class="description"
                                align="left;"><?= $item['product_code'] . '<br>' . $item['product_name'] ?></td>
                            <td align="right"><?= round($item['quantity']) ?></td>
                            <?= $itemHTML ?>
                            <td align="right"><?php
                                $this->db->select('name');
                                $this->db->where('id', $item['group_id']);
                                $item_group = $this->db->get('tblitems_groups')->result_array();
                                foreach ($item_group as $item_group_name) {
                                    echo $item_group_name['name'];
                                }


                                ?> </td>
                        </tr>
                        <?php
                        $i++;
                        $itemHTML = '';
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>


    </div>
    <?php if ($invoice->clientnote != '') { ?>
        <hr/>
        <div class="col-md-12 row mtop15">
            <p class="bold text-muted"><?php echo _l('invoice_note'); ?></p>
            <p><?php echo $invoice->clientnote; ?></p>
        </div>
    <?php } ?>
    <?php if ($invoice->terms != '') { ?>
        <hr/>
        <div class="col-md-12 row mtop15">
            <p class="bold text-muted"><?php echo _l('terms_and_conditions'); ?></p>
            <p><?php echo $invoice->terms; ?></p>
        </div>
    <?php } ?>




</div>
