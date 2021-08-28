<?php
     $table_data = array(
      _l('Indent Number'),
      _l('Date'),
      _l('Reference No'),
      _l('Raised By'),
      _l('Approve By'),
      _l('Admin Note'),
      _l('Last Modified By'));
    $table_data = do_action('invoices_table_columns',$table_data);
    render_datatable($table_data,  'mfpurchases');
	
?>
