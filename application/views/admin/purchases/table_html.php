<?php
     $table_data = array(
      _l('Indent Number'),
      _l('Date'),
      _l('Reference No'),
      _l('Warehouse'),
      _l('Admin Note'),
      _l('Supplier'),
      _l('Supply Date'),
      _l('Expected Delivery Date'),
      _l('Indent Status'),
      _l('Grand Total'),
      _l('Last Modified By'));
    $table_data = do_action('invoices_table_columns',$table_data);
    render_datatable($table_data, (isset($class) ? $class : 'purchases'));
	
?>
