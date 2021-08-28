<?php
     $table_data = array(
      _l('Service Pi'),
      _l('invoice_dt_table_heading_amount'),
      _l('invoice_gst'),
      _l('invoice_igst'),
      _l('invoice_cgst'),
      _l('invoice_sgst'),
//      _l('invoice_total_paid'),
//      _l('invoice_amount_due'),
      array(
        'name'=>_l('invoice_estimate_year'),
        'th_attrs'=>array('class'=>'not_visible')
      ),
      _l('invoice_dt_table_heading_date'),
     array(
       'name'=>_l('invoice_dt_table_heading_client'),
       'th_attrs'=>array('class'=>(isset($client) ? 'not_visible' : ''))
       ),
      _l('Assigned Engineer'),
      _l('Admin Notes'),
//      _l('Tags'),
      _l('invoice_dt_table_heading_duedate'));
    $table_data = do_action('invoices_table_columns',$table_data);
    render_datatable($table_data, (isset($class) ? $class : 'service_pi'));

?>
