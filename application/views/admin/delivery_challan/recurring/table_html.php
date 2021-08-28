<?php
     $table_data = [
      _l('invoice_dt_table_heading_number'),
      _l('invoice_dt_table_heading_amount'),
      [
        'name'     => _l('invoice_estimate_year'),
        'th_attrs' => ['class' => 'not_visible'],
      ],
      [
       'name' => _l('invoice_dt_table_heading_client'),
      ],
      _l('frequency'),
      _l('cycles_remaining'),
      _l('last_invoice_date'),
      _l('last_invoice_date'),
    ];
    render_datatable($table_data, 'invoices');
