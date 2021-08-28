<?php

$table_data = array(
    _l('Purchase Order'),
    _l('estimate_dt_table_heading_amount'),
    _l('Contact Persons (Mob)'),
    _l('Divisions'),
    /*array(
        'name' => _l('invoice_estimate_year'),
        'th_attrs' => array('class' => 'not_visible')
    ),*/
    array(
        'name' => _l('estimate_dt_table_heading_client'),
        'th_attrs' => array('class' => (isset($client) ? 'not_visible' : ''))
    ),
    'Brand',
    'Group',
    'Admin Note',
    'Sales Engineer',
    _l('estimate_dt_table_heading_date'),
    _l('estimate_dt_table_heading_expirydate'),
//      _l('reference_no'),
    _l('estimate_dt_table_heading_status'),
//    'Lead Status',
    /*_l('estimates_gst'),
    _l('estimates_igst'),
    _l('estimates_cgst'),
    _l('estimates_sgst'),*/
//      'Lead Source'
);

$custom_fields = get_custom_fields('purchaseorder', array('show_on_table' => 1));

foreach ($custom_fields as $field) {
    array_push($table_data, $field['name']);
}

$table_data = do_action('purchaseorder_table_columns', $table_data);
render_datatable($table_data, isset($class) ? $class : 'purchaseorder');
