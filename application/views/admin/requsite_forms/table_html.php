<?php

   $table_data = array(
      _l('Requsite form Number'),
      _l('Date'),
      _l('Customer'),
      _l('Status'),
      _l('Admin Note'),
      _l('Assigned Engineer'));

    $custom_fields = get_custom_fields('requsite_form',array('show_on_table'=>1));

    foreach($custom_fields as $field){
        array_push($table_data,$field['name']);
    }

   $table_data = do_action('estimates_table_columns',$table_data);

render_datatable($table_data, isset($class) ? $class : 'requsite_forms');
