<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'tblitems.description as description',
    'mailto',
    'created_at',
    'staffid',
    '1',
    ];
$sIndexColumn = 'id';
$sTable       = 'tblrfr';

$join = [
    'LEFT JOIN tblitems ON tblrfr.item_id = tblitems.id',
//    'LEFT JOIN tblitems_brands ON tblitems_brands.id = tblitems.brand',
//    'LEFT JOIN tblitems_units ON tblitems_units.id = tblitems.unit',
    ];
$additionalSelect = [
    'tblrfr.id as id',
    /*'tblitems.id',
    't1.name as taxname_1',
    't2.name as taxname_2',
    't1.id as tax_id_1',
    't2.id as tax_id_2',
    'group_id',*/
    ];

$custom_fields = get_custom_fields('items');

foreach ($custom_fields as $key => $field) {
    $selectAs = (is_cf_date($field) ? 'date_picker_cvalue_' . $key : 'cvalue_' . $key);

    array_push($customFieldsColumns, $selectAs);
    array_push($aColumns, 'ctable_' . $key . '.value as ' . $selectAs);
    array_push($join, 'LEFT JOIN tblcustomfieldsvalues as ctable_' . $key . ' ON tblitems.id = ctable_' . $key . '.relid AND ctable_' . $key . '.fieldto="items_pr" AND ctable_' . $key . '.fieldid=' . $field['id']);
}

// Fix for big queries. Some hosting have max_join_limit
if (count($custom_fields) > 4) {
    @$this->ci->db->query('SET SQL_BIG_SELECTS=1');
}

$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, [], $additionalSelect,'Group By tblitems.id');
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

        if ($aColumns[$i] == 'staffid') {
            if (!$aRow['staffid']) {
                $aRow['staffid'] = 0;
            }
            $_data = '<span data-toggle="tooltip" title="' . get_staff_full_name($aRow['staffid']) . '" >' . get_staff_full_name($aRow['staffid']) . '</span>';
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
            $_data = '<button type="button" class="btn btn-success" onclick="openPriceUpdateModel('.$aRow['id'].')">Update Price</button>';
        } elseif ($aColumns[$i] == 'tblitems.description as description') {
            if (!$aRow['description']) {
                $aRow['description'] = '';
            }
            $_data = '<span data-toggle="tooltip" title="' . $aRow['description'] . '" data-taxid="' . $aRow['description'] . '">' . $aRow['description'] .'</span>';
//            $_data = '<a href="#" data-toggle="modal" data-target="#sales_item_modal" data-id="' . $aRow['id'] . '">' . $_data . '</a>';
            /*$_data .= '<div class="row-options">';

            if (has_permission('items', '', 'edit')) {
                $_data .= '<a href="#" data-toggle="modal" data-target="#sales_item_modal" data-id="' . $aRow['id'] . '">' . _l('edit') . '</a>';
            }

            if (has_permission('items', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('invoice_items/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $_data .= ' | <a href="' . admin_url('invoice_items/print_barcodes/' . $aRow['id']) . '" class="text-success">' . _l('Print Barcodes') . '</a>';
            $_data .= '</div>';*/
        } else {
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
