<?php
  render_datatable([
     _l('delivery_module_table_number_heading'),
     _l('delivery_module_sale_ref_no_heading'),
     _l('delivery_module_table_delivery_ref_heading'),
      _l('delivery_module_table_delivered_by_heading'),
      _l('delivery_module_table_received_by_heading'),
     _l('delivery_module_table_customer_heading'),
     _l('delivery_module_table_status_heading'),
     _l('delivery_module_table_date_heading'),
	 _l('delivery_module_table_address_heading'),
	 _l('delivery_module_attachment_heading'),
     _l('delivery_module_table_note_heading'),     
  ], (isset($class) ? $class : 'payments'), [], [
         'data-last-order-identifier' => 'payments',
         'data-default-order'         => get_table_last_order('payments'),
]);


