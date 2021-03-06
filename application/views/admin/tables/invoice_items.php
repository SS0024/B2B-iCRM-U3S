<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    '1',
    'description',
    'long_description',
    'tblitems_brands.name as brand_name',

    'tblitems.rate',
    't1.taxrate as taxrate_1',
//    't2.taxrate as taxrate_2',
    'tblitems_units.name as unit',
    'rack_no',
    'category',
    'note',
    'quantity',
    'hold_stock as hold_qty',
    'quantity as indent_qty',
    'alert_quantity',
    'tblitems_groups.name',
    'upto',
    '(CASE 
      WHEN (upto < now() && (SELECT count(*) from tblrfr where tblrfr.item_id = tblitems.id) > 0) THEN "sent"
      WHEN upto < now() THEN "pending"  
      ELSE ""
      END
      ) as rfr_status'
    ];
$sIndexColumn = 'id';
$sTable       = 'tblitems';

$join = [
    'LEFT JOIN tbltaxes t1 ON t1.id = tblitems.tax',
    'LEFT JOIN tbltaxes t2 ON t2.id = tblitems.tax2',
    'LEFT JOIN tblitems_groups ON tblitems_groups.id = tblitems.group_id',
    'LEFT JOIN tblitems_brands ON tblitems_brands.id = tblitems.brand',
    'LEFT JOIN tblitems_units ON tblitems_units.id = tblitems.unit',
    ];
$additionalSelect = [
    'tblitems.id',
    't1.name as taxname_1',
    't2.name as taxname_2',
    't1.id as tax_id_1',
    't2.id as tax_id_2',
    'group_id',
    ];

$custom_fields = get_custom_fields('items');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);

    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tblitems.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="items_pr" AND ctable_' . $key . '.fieldid=' . $field['id']);
}
$where = [];
if ($this->ci->input->post('exclude_inactive')) {
    array_push($where, 'AND tblitems.quantity > 0');
}
// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect,'Group By tblitems.id');
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        if (strpos($aColumns[$i], 'as') !== false && !isset($aRow[$aColumns[$i]])) {
            $_data = $aRow[strafter($aColumns[$i], 'as ')];
        } else {
            $_data = $aRow[$aColumns[$i]];
        }

        if ($aColumns[$i] == 't1.taxrate as taxrate_1') {
            if (!$aRow['taxrate_1']) {
                $aRow['taxrate_1'] = 0;
            }
            $_data = '<span data-toggle="tooltip" title="' . $aRow['taxname_1'] . '" data-taxid="' . $aRow['tax_id_1'] . '">' . $aRow['taxrate_1'] . '%' . '</span>';
        } /*elseif ($aColumns[$i] == 't2.taxrate as taxrate_2') {
            if (!$aRow['taxrate_2']) {
                $aRow['taxrate_2'] = 0;
            }
            $_data = '<span data-toggle="tooltip" title="' . $aRow['taxname_2'] . '" data-taxid="' . $aRow['tax_id_2'] . '">' . $aRow['taxrate_2'] . '%' . '</span>';
        } */elseif ($aColumns[$i] == 'upto') {
            if (!$aRow['upto']) {
                $aRow['upto'] = '';
            }
            $_data = '<span data-toggle="tooltip" title="' . $aRow['upto'] . '" data-taxid="' . $aRow['upto'] . '">' . _d($aRow['upto']) .'</span>';
        } elseif ($aColumns[$i] == '1') {
            $_data = '<div class="checkbox"><input type="checkbox" value="' . $aRow['id'] . '"><label></label></div>';
        } elseif ($aColumns[$i] == 'tblitems_brands.name as brand_name') {
            if (!$aRow['brand_name']) {
                $aRow['brand_name'] = '';
            }
            $_data = '<span data-toggle="tooltip" title="' . $aRow['brand_name'] . '" data-taxid="' . $aRow['brand_name'] . '">' . $aRow['brand_name'] .'</span>';
        }  elseif ($aColumns[$i] == '(CASE 
      WHEN (upto < now() && (SELECT count(*) from tblrfr where tblrfr.item_id = tblitems.id) > 0) THEN "sent"
      WHEN upto < now() THEN "pending"  
      ELSE ""
      END
      ) as rfr_status') {
            if (!$aRow['rfr_status']) {
                $_data = '';
            }else{
                $_data = '<span class="label label-danger  s-status proposal-status-5" title="' . $aRow['rfr_status'] . '">' . $aRow['rfr_status'] .'</span>';
            }
        } elseif ($aColumns[$i] == 'quantity') {
            if (!$aRow['quantity']) {
                $aRow['quantity'] = 0;
            }
            $avlQty = 0;
            if($aRow['hold_qty'] != 0){
                $avlQty = $aRow['quantity'] - $aRow['hold_qty'];
            }else{
                $avlQty = $aRow['quantity'];
            }
            $_data = '<a href="javascrip:void(0);" class="pull-right" onclick="openWareHouseQuantityModal('.$aRow['id'].')" title="' . $avlQty . '" >' . round($avlQty) .'</a>';
        } elseif ($aColumns[$i] == 'hold_stock as hold_qty') {
            if (!$aRow['hold_qty']) {
                $aRow['hold_qty'] = 0;
            }
            $_data = '<a href="javascrip:void(0);" class="pull-right" onclick="openHoldItemModal('.$aRow['id'].')" title="' . $aRow['hold_qty'] . '" >' . round($aRow['hold_qty']) .'</a>';
        } elseif ($aColumns[$i] == 'quantity as indent_qty') {
            if (!$aRow['indent_qty']) {
                $aRow['indent_qty'] = 0;
            }
            @$this->ci->db->select('SUM((`quantity` - `quantity_balance`)) AS qty');
            @$this->ci->db->from('tblpurchaseitems');
            @$this->ci->db->where('product_id', $aRow['id']);
            @$this->ci->db->where('purchase_id IS NOT NULL', null);
            $indentQty = @$this->ci->db->get()->row();
            $_data = '<a href="javascrip:void(0);" class="pull-right" onclick="openIndentModal('.$aRow['id'].')" title="' . $indentQty->qty . '" >' . round($indentQty->qty) .'</a>';
        } elseif ($aColumns[$i] == 'alert_quantity') {
            if (!$aRow['alert_quantity']) {
                $aRow['alert_quantity'] = 0;
            }
            $_data = '<span data-toggle="tooltip" class="pull-right" title="' . $aRow['alert_quantity'] . '" data-taxid="' . $aRow['alert_quantity'] . '">' . $aRow['alert_quantity'] .'</span>';
        } elseif ($aColumns[$i] == 'description') {
            $_data = '<a href="#" data-toggle="modal" data-target="#sales_item_modal" data-id="' . $aRow['id'] . '">' . $_data . '</a>';
            $_data .= '<div class="row-options">';

            if (has_permission('items', '', 'edit')) {
                $_data .= '<a href="#" data-toggle="modal" data-target="#sales_item_modal" data-id="' . $aRow['id'] . '">' . _l('edit') . '</a>';
            }

            if (has_permission('items', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('invoice_items/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $_data .= ' | <a href="' . admin_url('invoice_items/print_barcodes/' . $aRow['id']) . '" class="text-success">' . _l('Print Barcodes') . '</a>';
            $_data .= '</div>';
        }
//        elseif ($aColumns[$i] == 'category'){
//
//        }
        else {
            if (_startsWith($aColumns[$i], 'ctable_') && is_date($_data)) {
                $_data = _d($_data);
            }
        }

        $row[]              = $_data;
        $row['DT_RowClass'] = 'has-row-options';
        if ((!empty($aRow['upto']) && $aRow['upto'] < date('Y-m-d'))) {
            $row['DT_RowClass'] .= ' text-danger';
        }
    }


    $output['aaData'][] = $row;
}
