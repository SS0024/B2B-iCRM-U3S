
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
                        <th class="description" width="20%"
                            align="left"><?php echo _l('invoice_table_item_heading'); ?> \ Suppliers </th>

                        <?php

                        $SUPPLIERS = array();
                        foreach ($invoice->items as $item) {

                            $this->db->where('rel_id', $item['purchase_id']);
                            $this->db->where('rel_type', 'Mfpurchase');
                            $item_suppliers = $this->db->get('tblmfitemssupplier')->result_array();


                            foreach ($item_suppliers as $supplier) {
                                $SUPPLIERS[] = $supplier['suppliers_id'];
                            }
                        }

                                $SUPPLIER_ID = array_unique($SUPPLIERS);

for($i=0; $i <= count($SUPPLIER_ID); $i++){

    $this->db->select('company');
    $this->db->where('id', $SUPPLIER_ID[$i]);
    $item_suppliers_company = $this->db->get('tblsuppliers')->result_array();
                        foreach ($item_suppliers_company as $supplier_name) {


                            ?>
                            <th align="left" width="200px">
                                <?php echo  $supplier_name['company']; ?>
                            </th>

                            <?php
                        }
}?>


                    </tr>
                    </thead>
                    <tbody>
<?php
$i = 1;
                    $itemHTML = '';
                    $SUPPLIERS = array();
                    foreach ($invoice->items as $item) {


                        $this->db->where('rel_id', $item['purchase_id']);
                        $this->db->where('rel_type', 'Mfpurchase');
                        $item_suppliers = $this->db->get('tblmfitemssupplier')->result_array();


                        foreach ($item_suppliers as $supplier) {
                        $SUPPLIERS[] = $supplier['suppliers_id'];

                        }




                        $SUPPLIER_ID = array_unique($SUPPLIERS);
                        $ITEM_ID = $item['id'] ;


                        for($i=0; $i <= count($SUPPLIER_ID); $i++){

                        $this->db->select('supplier_item_price');
                        $this->db->where('suppliers_id', $SUPPLIER_ID[$i]);
                        $this->db->where('itemid', $ITEM_ID);
                            $item_suppliers_price = $this->db->get('tblmfitemssupplier')->result_array();
                        foreach ($item_suppliers_price as $supplier_price) {



                            $itemHTML.='   <td align="right" width="200px">';
                            $itemHTML.= $supplier_price['supplier_item_price'];
                            $itemHTML.=' </td>';


                        }
                        }
                     $statusColor = '';
                        if ($item['quantity'] == $item['quantity_balance']) {
                            $statusColor = 'border-left: 4px solid #84c529;';
                        } elseif ($item['quantity'] == 0) {
                            $statusColor = '';
                        } elseif ($item['quantity'] != $item['quantity_balance']) {
                            $statusColor = 'border-left: 4px solid #ff6f00';
                        }
                        ?>
                        <tr class="sortable" style="<?= $statusColor ?>" data-item-id="<?= $item['product_id'] ?>">
                            <td class="dragger item_no ui-sortable-handle" align="center"><?= $i ?></td>
                            <td class="description"
                                align="left;"><?= $item['product_code'] . '<br>' . $item['product_name'] ?></td>

                            <?= $itemHTML ?>

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

</div>
