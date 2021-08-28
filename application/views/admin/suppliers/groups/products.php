<?php if (isset($client)) { ?>
    <h4 class="customer-profile-group-heading"><?php echo _l('My Products'); ?></h4>
    <div class="_buttons">
        <a href="#" class="btn btn-info mbot25" data-toggle="modal"
           data-target="#new_products"><?php echo _l('New Products'); ?></a>
        <div class="visible-xs">
            <div class="clearfix"></div>
        </div>
        <div class="btn-group pull-right btn-with-tooltip-group _filter_data" data-toggle="tooltip"
             data-title="<?php echo _l('filter_by'); ?>">
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                    aria-expanded="false">
                <i class="fa fa-filter" aria-hidden="true"></i>
                <div class="_filters _hidden_inputs scheduledServices_filters hidden">
                    <?php
                    echo form_hidden('is_unit','is_unit');
                    echo form_hidden('is_spares');
                    ?>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-left" style="width:300px;">
                <li ><a href="#" data-cview="all"
                                      onclick="dt_custom_view('','.table-scheduled-services',''); return false;"><?php echo _l('customers_sort_all'); ?></a>
                </li>
                <li class="divider"></li>
                <li class="active"><a href="#" data-cview="is_unit"
                       onclick="dt_custom_view('is_unit','.table-scheduled-services','is_unit'); return false;">Unit</a>
                </li>
                <li class="divider"></li>
                <li ><a href="#" data-cview="is_spares"
                       onclick="dt_custom_view('is_spares','.table-scheduled-services','is_spares'); return false;">Spares</a>
                </li>
                <div class="clearfix"></div>
            </ul>
        </div>
    </div>
    <div class="clearfix"></div>
    <?php
    $table_data = array(
        array(
            'name' => _l('Customer Name'),
            'th_attrs' => array('class' => 'not_visible')
        ),
        _l('Invoice No'),
        _l('TPL / Part No'),
        _l('HSN/SAC'),
        _l('Model'),
        _l('Dvision'),
        _l('FAB No'),
        _l('RDPY'),
        _l('RHPD'),
        _l('RHPY'),
        _l('Installation Date'),
//        _l('Type of Machine'),
        _l('Service type'),
    );
    $cf = get_custom_fields('scheduled_services');
    foreach ($cf as $custom_field) {
        array_push($table_data, $custom_field['name']);
    }
    render_datatable($table_data, 'scheduled-services');
//   $this->load->view('admin/estimates/table_html', array('class'=>'estimates-single-client'));
    include_once(APPPATH . 'views/admin/clients/modals/product.php');
    ?>
<?php } ?>
