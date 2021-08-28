<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (empty($type)){
    $type = 'purchase';
}

switch($type){
    case 'sale':
        $aColumns = [
            'tblitems.description as description',
            'tblinvoices.number as invoice_number',
            'tblinvoices.date as date',
            'tblinvoices.status as invoice_status',
            'tblitems_in.qty as qty',
            'tblitems.id as id',
            'tblinvoices.id as invoice_id',
        ];
        $sIndexColumn = 'id';
        $sTable       = 'tblitems_in';
        $join = [
            'LEFT JOIN tblinvoices ON tblinvoices.id = tblitems_in.rel_id',
            'LEFT JOIN tblitems ON tblitems.description = tblitems_in.description'
        ];
        $where = [
            'AND tblitems_in.rel_type like "invoice"','AND tblitems.id = '.$id
        ];
        break;
    case 'dc':
        $aColumns = [
            'tblitems.description as description',
            'tbldelivery_challans.number as dc_number',
            'tbldelivery_challans.date as date',
            'tbldelivery_challans.status as dc_status',
            'tblitems_in.qty as qty',
            'tblitems.id as id',
            'tbldelivery_challans.id as invoice_id',
        ];
        $sIndexColumn = 'id';
        $sTable       = 'tblitems_in';
        $join = [
            'LEFT JOIN tbldelivery_challans ON tbldelivery_challans.id = tblitems_in.rel_id',
            'LEFT JOIN tblitems ON tblitems.description = tblitems_in.description'
        ];
        $where = [
            'AND tblitems_in.rel_type like "deliverychallan"','AND tblitems.id = '.$id
        ];
        break;
    case 'adjustment':
        $aColumns = [
            'tblitems.description as description',
            'tbladjustments.reference_no as reference_no',
            'tbladjustments.date as date',
            'tbladjustment_items.type as status',
            'tbladjustment_items.quantity as qty',
            'tblitems.id as item_id',
        ];
        $sIndexColumn = 'id';
        $sTable       = 'tbladjustment_items';
        $join = [
            'LEFT JOIN tbladjustments ON tbladjustments.id = tbladjustment_items.adjustment_id',
            'LEFT JOIN tblitems ON tblitems.id = tbladjustment_items.product_id'
        ];
        $where = [
            'AND tbladjustment_items.adjustment_id IS NOT NULL','AND tblitems.id = '.$id
        ];
        break;
    case 'stock_transaction':
        $aColumns = [
            'tblitems.description as description',
            '(CASE WHEN tblstock_transactions.rel_type="purchase" THEN
            tblpurchaseitems.purchase_id
            ELSE tblitems_in.rel_id
            END) as r_number',
            'tblstock_transactions.added_on as date',
            'tblstock_transactions.rel_type as type',
            'tblstock_transactions.quantity as qty',
            'tblitems.id as item_id',
        ];
        $sIndexColumn = 'id';
        $sTable       = 'tblstock_transactions';
        $join = [
            'LEFT JOIN tblpurchaseitems ON (tblstock_transactions.rel_type="purchase" AND tblpurchaseitems.id = tblstock_transactions.rel_id)',
            'LEFT JOIN tblitems_in ON ((tblstock_transactions.rel_type="sale" OR tblstock_transactions.rel_type="sale_extra" ) AND tblitems_in.id = tblstock_transactions.rel_id)',
            'LEFT JOIN tblitems ON ((CASE
                WHEN tblstock_transactions.rel_type="purchase" THEN (tblpurchaseitems.product_id = tblitems.id)
                ELSE (tblitems_in.description = tblitems.description)
                END))'
        ];
        $where = [
            'AND tblitems.id = '.$id
        ];
        break;
    default:
        $aColumns = [
            'tblpurchaseitems.product_code as description',
            'tblpurchases.number as purchase_no',
            'tblpurchases.date as date',
            'tblpurchaseitems.status as status',
            'tblpurchaseitems.quantity as qty',
            'tblpurchaseitems.quantity_balance as quantity_balance',
            'tblitems.id as id',
            'tblpurchases.id as purchase_id',
        ];
        $sIndexColumn = 'id';
        $sTable       = 'tblpurchaseitems';
        $join = [
            'LEFT JOIN tblpurchases ON tblpurchases.id = tblpurchaseitems.purchase_id',
            'LEFT JOIN tblitems ON tblitems.id = tblpurchaseitems.product_id'
        ];
        $where = [
            'AND tblpurchaseitems.purchase_id IS NOT NULL','AND tblitems.id = '.$id
        ];
}



$result  = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, []);
$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    $row[] = $aRow['description'];
    if (isset($aRow['invoice_number'])){
        $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['invoice_id']) . '" target="_blank">' . format_invoice_number($aRow['invoice_id']) . '</a>';
    }
    if (isset($aRow['dc_number'])){
        $row[] = '<a href="' . admin_url('delivery_challan/list_invoices/' . $aRow['invoice_id']) . '" target="_blank">' . format_delivery_challan_number($aRow['invoice_id']) . '</a>';
    }
    if (isset($aRow['purchase_no'])){
        $row[] = '<a href="' . admin_url('purchases/list_purchases/' . $aRow['invoice_id']) . '" target="_blank">' . format_purchase_number($aRow['invoice_id']) . '</a>';
    }
    if (isset($aRow['reference_no'])){
        $row[] = $aRow['reference_no'];
    }
    if (isset($aRow['r_number'])){
        if($aRow['type'] === 'sale'){
            $row[] = '<a href="' . admin_url('invoices/list_invoices/' . $aRow['r_number']) . '" target="_blank">' . format_invoice_number($aRow['r_number']) . '</a>';
        }else if($aRow['type'] === 'sale_extra'){
            $row[] = '<a href="' . admin_url('delivery_challan/list_invoices/' . $aRow['r_number']) . '" target="_blank">' . format_delivery_challan_number($aRow['r_number']) . '</a>';
        }else{
            $row[] = '<a href="' . admin_url('purchases/list_purchases/' . $aRow['r_number']) . '" target="_blank">' . format_purchase_number($aRow['r_number']) . '</a>';
        }
    }
    if (isset($aRow['date'])){
        $row[] = _dt($aRow['date']);
    }
    if (isset($aRow['status'])){
        $row[] = ucfirst(implode(' ',explode('_',$aRow['status'])));
    }
    if (isset($aRow['type'])){
        if ($aRow['type']==='sale_extra'){
            $row[] = 'DC';
        }else{
            $row[] = ucfirst(implode(' ',explode('_',$aRow['type'])));
        }
    }
    if (isset($aRow['invoice_status'])){
        $row[] = format_invoice_status($aRow['invoice_status']);
    }
    if (isset($aRow['dc_status'])){
        $row[] = format_delivery_challan_status($aRow['dc_status']);
    }
    if (isset($aRow['qty'])){
        $row[] = $aRow['qty'];
    }
    if (isset($aRow['quantity_balance'])){
        $row[] = $aRow['quantity_balance'];
    }
    if (isset($aRow['added_by'])){
        $row[] = $aRow['added_by'];
    }

    $output['aaData'][] = $row;
}
