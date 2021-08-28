<?php
   $table_data = [
       _l('the_number_sign'),
       '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="expenses"><label></label></div>',
     _l('expense_dt_table_heading_category'),
     _l('expense_dt_table_heading_amount'),
     _l('Note'),
     _l('expense_receipt'),
     _l('expense_dt_table_heading_date'),
   ];
/*array_unshift($table_data, [
    'name'     => '<span class="hide"> - </span><div class="checkbox mass_select_all_wrap"><input type="checkbox" id="mass_select_all" data-to-table="expenses"><label></label></div>',
    'th_attrs' => ['class' => (isset($bulk_actions) ? '' : 'not_visible')],
]);*/
   if (!isset($project)) {
       array_push($table_data, _l('project'));
       array_push($table_data, [
       'name'     => _l('expense_dt_table_heading_customer'),
       'th_attrs' => ['class' => (isset($client) ? 'not_visible' : '')],
     ]);
   }

   $table_data = array_merge($table_data, [
     _l('invoice'),
     _l('expense_dt_table_heading_reference_no'),
     _l('Added By'),
     _l('expense_dt_table_heading_payment_mode'),
   ]);

  $custom_fields = get_custom_fields('expenses', ['show_on_table' => 1]);

  foreach ($custom_fields as $field) {
      array_push($table_data, $field['name']);
  }

  $table_data = do_action('expenses_table_columns', $table_data);
  render_datatable($table_data, (isset($class) ? $class : 'expenses'), [], [
    'data-last-order-identifier' => 'expenses',
    'data-default-order'         => get_table_last_order('expenses'),
  ]);
