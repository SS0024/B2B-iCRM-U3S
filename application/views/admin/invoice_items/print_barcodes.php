<?php init_head(); ?>
<style>
    @media print {
        html, body, .container, #content,
        .box-content, .col-lg-12, .table-responsive,
        .table-responsive .table, .dataTable, .box, .row  { width: 100% !important; height: auto !important; border: none !important; padding: 0 !important; margin: 0 !important; }
        .lt .sidebar-con { width: 0; display: none;  }
        body:before, body:after, .no-print,
        #header, #sidebar-left, .sidebar-nav, .main-menu,
        footer, .breadcrumb, .box-header, .box-header .fa, .box-icon, .alert, .introtext,
        .table-responsive .row, .table-responsive .table th:first-child,
        .table-responsive .table td:first-child, .table-responsive .table tfoot,
        .buttons,#header, .modal-open #content, .modal-body .close, .pagination, .close, .staff_note,.customer-profile-group-heading, .form-part, #menu, #setup-menu-wrapper, #header, .tip.no-print {
            display: none;
        }
        #wrapper{margin: 0;min-height:auto !important;}
        .container { width: auto !important; }
        .content { padding: 0 !important; }
        h3 { margin-top: 0; }
        .modal { position: static; }
        .modal .table-responsive { display: block; }
        .modal .table th:first-child, .modal .table td:first-child, .modal .table th, .modal .table td { display: table-cell !important; }
        .modal-content { display: block !important; background: white !important; border: none !important; }
        .modal-content .table tfoot { display: table-row-group !important; }
        .modal-header { border-bottom: 0; }
        .modal-lg { width: 100%; }
        .table-responsive .table th,
        .table-responsive .table td { display: table-cell; border-top: none !important; border-left: none !important; border-right: none !important; border-bottom: 1px solid #CCC !important; }
        a:after {
            display: none;
        }
        .print-table thead th:first-child, .print-table thead th:last-child, .print-table td:first-child, .print-table td:last-child {
            display: table-cell !important;
        }
        .fa-3x { font-size: 1.5em; }
        .border-right {
            border-right: 0 !important;
        }
        /*.table thead th { background: #F5F5F5 !important; background-color: #F5F5F5 !important; border-top: 1px solid #f5F5F5 !important; }*/
        .well { border-top: 0 !important; }
        .box-header { border: 0 !important; }
        .box-header h2 { display: block; border: 0 !important; }
        .order-table tfoot { display: table-footer-group !important; }
        .print-only { display: block !important; }
        .reports-table th, .reports-table td { display: table-cell !important; }
        table thead { display: table-header-group; }
        .white-text { color: #FFF !important;  text-shadow: 0 0 3px #FFF !important; -webkit-print-color-adjust: exact; }
        #bl .barcodes td { padding: 2px !important; }
        /*#bl .barcodes .bcimg { max-width: 100%; }*/
        #lp .labels { text-align:center;font-size:10pt;page-break-after:always;padding:1px; }
        #lp .labels img { max-width: 100%; }
        #lp .labels .name { font-size:0.8em; font-weight:bold; }
        #lp .labels .price { font-size:0.8em; font-weight:bold; }
        .well { border: none !important; box-shadow: none; }
        /*.table { margin-bottom: 20px !important;  }*/
    }
    /*;border: 1px solid #CCC;*/
    /*Please modify the styles below for barcode/label printing */
    .barcode { width: 8.45in; height: 10.3in; display: block; border: 1px solid #CCC; margin: 10px auto; padding-top: 0.1in; page-break-after:always; }
    .barcode .item { display: block; overflow: hidden; text-align: center; border: 1px dotted #CCC; font-size: 12px; line-height: 14px; float: left; }
    .style50 { font-size: 10px; line-height: 12px; margin: 0 auto; display: block; text-align: center; border: 1px dotted #CCC; text-transform: uppercase; page-break-after:always; }
    .barcode .style30 { width: 2.625in; height: 1in; margin: 0 0.07in; padding-top: 0.05in; }
    .barcode .style30:nth-child(3n+1) {  margin-left: 0.1in; }
    .barcode .style20 { width: 4in; height: 1in; margin: 0 0.1in; padding-top: 0.05in; }
    .barcode .style14 { width: 4in; height: 1.33in; margin: 0 0.1in; padding-top: 0.1in; }
    .barcode .style10 { width: 4in; height: 2in; margin: 0 0.1in; padding-top: 0.1in; font-size: 14px; line-height: 20px; }
    .barcode .barcode_site, .barcode .barcode_name, .barcode .barcode_image, .barcode .variants { display: block; }
    .barcode .barcode_price, .barcode .barcode_unit, .barcode .barcode_category { display: inline-block; }
    .barcode .product_image { width: 0.8in; float: left; margin: 5px; }
    .barcode .style10 .product_image { width: 1in; }
    .barcode .style30 .product_image { width: 0.5in; float: left; margin: 5px; }
    .barcode .product_image img { max-width: 100%; }
    .barcode_2_2 .product_image img { max-width: 70%; }
    .style50 .product_image, .style40 .product_image { display: none; }
    .style50 .barcode_site, .style50 .barcode_name, .style50 .barcode_image, .style50 .barcode_price { display: block; }
    .barcode .barcode_image img, .style50 .barcode_image img { max-width: 100%; }
    .barcode .barcode_site { font-weight: bold; }
    .barcode_2_2 .barcode_site { font-weight: bold; }
    .barcode .barcode_site, .barcode .barcode_name { font-size: 14px; }
    .barcode_2_2 .barcode_site, .barcode_2_2 .barcode_name, .barcode_2_2 .barcode_price, .barcode_2_2 .barcode_unit, .barcode_2_2 .barcode_category{ font-size: 12px;line-height: 14px; }
    .barcode .style10 .barcode_site, .barcode .style10 .barcode_name { font-size: 16px; }
    /*border: 1px dotted #CCC;*/

    .barcodea4 { width: 8.25in; height: 11.6in; display: block; border: 1px solid #CCC; margin: 10px auto; padding: 0.1in 0 0 0.1in; page-break-after:always; }
    .barcodea4 .item { display: block; overflow: hidden; text-align: center; border: 1px dotted #CCC; font-size: 12px; line-height: 14px; text-transform: uppercase; float: left; }
    .barcodea4 .style40 { width: 1.799in; height: 1.003in; margin: 0 0.07in; padding-top: 0.05in; }
    .barcodea4 .style24 { width: 2.48in; height: 1.335in; margin-left: 0.079in; padding-top: 0.05in; }
    .barcodea4 .style18 { width: 2.5in; height: 1.835in; margin-left: 0.079in; padding-top: 0.05in; font-size: 13px; line-height: 20px; }
    .barcodea4 .style12 { width: 2.5in; height: 2.834in; margin-left: 0.079in; padding-top: 0.05in; font-size: 14px; line-height: 20px; }
    .barcodea4 .barcode_site, .barcodea4 .barcode_name, .barcodea4 .barcode_image, .barcodea4 .variants { display: block; }
    .barcodea4 .barcode_price, .barcodea4 .barcode_unit, .barcodea4 .barcode_category { display: inline-block; }
    .barcodea4 .product_image { width: 2in; float: left; margin: 5px; }
    .barcodea4 .style12 .product_image { width: 2.3in; height:auto; max-height: 1.5in; display: block; }
    .barcodea4 .style18 .product_image { width: 2.2in; height:auto; max-height: 1.5in; display: block; margin: 0px 5px;}
    .barcodea4 .style24 .product_image { width: 2.2in; height:auto; max-height: 1.5in; display: block; margin: -5px 5px;}
    .barcodea4 .style24 .product_image img { max-width: 100%; max-height: 100%; }
    .barcodea4 .style18 .product_image img { max-width: 100%; max-height: 100%; }
    .barcodea4 .style12 .product_image img { max-width: 100%; max-height: 100%; }
    .barcodea4 .style24 .barcode_site, .barcodea4 .style24 .barcode_name { font-size: 14px; }
    .barcodea4 .style18 .barcode_site, .barcodea4 .style18 .barcode_name { font-size: 14px; font-weight: bold; }
    .barcodea4 .style12 .barcode_site, .barcodea4 .style12 .barcode_name { font-size: 15px; font-weight: bold; }
    .style12-2 {
        width: 45.0mm;
        height: 45.0mm;
        margin: 1.5mm 1.15mm !important;
        display: block;
        overflow: hidden;
        text-align: center;
        font-size: 9px;
        line-height: 140%;
        float: left;
        vertical-align: middle;
        text-transform: uppercase;
        padding-top: 0.35in;
    }
    .barcode_2_2 {
        width: 101.6mm;
        /*height: 50.0mm;*/
        display: block;
        margin: 0px auto;
        page-break-after: always;
    }
    @media print {
        @page {
            margin: 0;
            padding: 0;
            border: none !important;
        }
        body,.tab-content,.panel-body,.content,.row,#wrapper,.panel_s{
            margin: 0 !important;
            padding: 0 !important;
            border: none !important
        }
        #wrapper{
            min-height: auto !important;
        }
        .tooltip, #sliding-ad { display: none !important; }
        .barcode, .barcodea4 { margin: 0;margin-left: 0px; }
        .barcode, .barcode_2_2 { margin: 0;margin-left: 0px; }
        .barcode, .barcode .item, .barcodea4, .barcodea4 .item, .style50, .div50 { border: none !important; }
        .barcode, .barcode .item, .barcodea4, .barcode_2_2 .item .barcodea4, .barcodea4 .item, .style50, .div50 { border: none !important; }
        .div50, .modal-content { page-break-after:always; }
    }

</style>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="tab-content">
                            <h4 class="customer-profile-group-heading"><?= _l('Print Barcode/Label'); ?></h4>
                            <div class="row">
                                <div class="col-lg-12">
                                    <div class="form-part">
                                        <?php echo form_open('admin/invoice_items/print_barcodes', array('id' => 'barcode-print-form')); ?>
                                        <?php
                                        if(!$this->input->get('is_purchase')){
                                            ?>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="styles" class="control-label">Add Item</label>
                                                    <select name="item_select" class="selectpicker no-margin<?php if($ajaxItems == true){echo ' ajax-search';} ?>" data-width="100%" id="item_select" data-none-selected-text="<?php echo _l('add_item'); ?>" data-live-search="true">
                                                        <option value=""></option>
                                                        <?php foreach($nitems as $group_id=>$_items){ ?>
                                                        <optgroup data-group-id="<?php echo $group_id; ?>" label="<?php echo $_items[0]['group_name']; ?>">
                                                            <?php foreach($_items as $item){ ?>
                                                                <?php
                                                                if ((isset($bodyclass) && ($bodyclass == 'estimates' || $bodyclass =='invoice' || $bodyclass =='purchaseorder'))){
                                                                    if(isset($item['quantity']) && $item['quantity'] != 0){
                                                                        ?>
                                                                        <option value="<?php echo $item['id']; ?>" data-subtext="<?php echo strip_tags(mb_substr($item['long_description'],0,200)).'...'; ?>">(<?php echo _format_number($item['rate']); ; ?>) <?php echo $item['description']; ?></option>
                                                                        <?php
                                                                    }
                                                                }else{
                                                                    ?>
                                                                    <option value="<?php echo $item['id']; ?>" data-subtext="<?php echo strip_tags(mb_substr($item['long_description'],0,200)).'...'; ?>">(<?php echo _format_number($item['rate']); ; ?>) <?php echo $item['description']; ?></option>
                                                                    <?php
                                                                }
                                                                ?>
                                                            <?php } ?>
                                                        </optgroup>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <?php
                                        }
                                        ?>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <?php
                                                $bs = array(
//                                                    ['id' => '', 'name' => _l('select').' Style'],
                                                    ['id' => '12-2', 'name' => _l('2 Sticker per Sheet (2" x 2")')],
                                                    ['id' => '40', 'name' => _l('40_per_sheet')],
                                                    ['id' => '30', 'name' => _l('30_per_sheet')],
                                                    ['id' => '24', 'name' => _l('24_per_sheet')],
                                                    ['id' => '20', 'name' => _l('20_per_sheet')],
                                                    ['id' => '18', 'name' => _l('18_per_sheet')],
                                                    ['id' => '14', 'name' => _l('14_per_sheet')],
                                                    ['id' => '12', 'name' => _l('12_per_sheet')],
                                                    ['id' => '10', 'name' => _l('1 Sticker per Sheet (4" x 2")')],
                                                    ['id' => '50', 'name' => _l('continuous_feed')]
                                                );
                                                echo render_select('styles', $bs, array('id', 'name'), 'Style',50,[],[],'','',false); ?>
                                            </div>
                                        </div>
                                        <!--<div class="form-group">
                                            <label><?/*= _l('Add Item'); */?></label>
                                            <?php /*echo form_input('add_item', '', 'class="form-control" id="add_item" placeholder="Add Item"'); */?>
                                        </div>-->
                                        <div class="row">
                                            <div class="col-md-12 cf-con">
                                                <div class=" ">
                                                    <div class="col-xs-4">
                                                        <div class="form-group">
                                                            <div class="input-group">
                                                                <?= form_input('cf_width', '', 'class="form-control" id="cf_width" placeholder="' . _l("width") . '"'); ?>
                                                                <span class="input-group-addon" style="padding-left:10px;padding-right:10px;"><?= _l('inches'); ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-4">
                                                        <div class="form-group">
                                                            <div class="input-group">
                                                                <?= form_input('cf_height', '', 'class="form-control" id="cf_height" placeholder="' . _l("height") . '"'); ?>
                                                                <span class="input-group-addon" style="padding-left:10px;padding-right:10px;"><?= _l('inches'); ?></span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-4">
                                                        <div class="form-group">
                                                            <?php $oopts = array(0 => _l('portrait'), 1 => _l('landscape')); ?>
                                                            <?= form_dropdown('cf_orientation', $oopts , '', 'class="form-control" id="cf_orientation" placeholder="' . _l("orientation") . '"'); ?>
                                                        </div>
                                                    </div>
                                                    <div class="col-xs-12">
                                                        <span class="help-block"><?= _l('barcode_tip'); ?></span>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="controls table-controls">
                                                <table id="bcTable"
                                                       class="table items table-bordered table-condensed table-hover">
                                                    <thead>
                                                    <tr>
                                                        <th class="col-xs-5"><?= _l("product_name"); ?></th>
                                                        <th class="col-xs-5"><?= _l("quantity"); ?></th>
                                                        <th class="col-xs-2 text-center" style="width:30px;">
                                                            <i class="fa fa-trash-o" style="opacity:0.5; filter:alpha(opacity=50);"></i>
                                                        </th>
                                                    </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>

                                            <div class="form-group">
                                                <span style="font-weight: bold; margin-right: 15px;">Print:</span>
                                                <div class="checkbox-inline checkbox checkbox-primary">
                                                    <input type="checkbox" name="check_promo" id="check_promo" value="1" checked="checked">
                                                    <label for="check_promo"><?php echo _l('Logo'); ?></label>
                                                </div>
                                                <div class="checkbox-inline checkbox checkbox-primary">
                                                    <input type="checkbox" name="site_name" id="site_name" value="1" checked="checked">
                                                    <label for="site_name"><?php echo _l('site_name'); ?></label>
                                                </div>

                                                <div class="checkbox-inline checkbox checkbox-primary">
                                                    <input type="checkbox" name="product_name" id="product_name" value="1" checked="checked">
                                                    <label for="product_name"><?php echo _l('product_name'); ?></label>
                                                </div>

                                                <div class="checkbox-inline checkbox checkbox-primary">
                                                    <input type="checkbox" name="price" id="price" value="1" checked="checked">
                                                    <label for="price"><?php echo _l('price'); ?></label>
                                                </div>

                                                <!--<div class="checkbox-inline checkbox checkbox-primary">
                                                    <input type="checkbox" name="currencies" id="currencies" value="1">
                                                    <label for="currencies"><?php /*echo _l('currencies'); */?></label>
                                                </div>-->

                                                <div class="checkbox-inline checkbox checkbox-primary">
                                                    <input type="checkbox" name="unit" id="unit" value="1">
                                                    <label for="unit"><?php echo _l('unit'); ?></label>
                                                </div>

                                                <div class="checkbox-inline checkbox checkbox-primary">
                                                    <input type="checkbox" name="category" id="category" value="1">
                                                    <label for="category"><?php echo _l('category'); ?></label>
                                                </div>
                                            </div>

                                            <div class="form-group">
                                                <?php echo form_submit('print', _l("update"), 'class="btn btn-primary"'); ?>
                                                <button type="button" id="reset" class="btn btn-danger"><?= _l('reset'); ?></button>
                                            </div>
                                        </div>
                                        <?= form_close(); ?>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div id="barcode-con">
                                        <?php
                                        if ($this->input->post('print')) {
                                            if (!empty($barcodes)) {
                                                echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" title="'._l('print').'"><i class="icon fa fa-print"></i> '._l('print').'</button>';
                                                $c = 1;
                                                if ($style == "12-2") {
                                                    echo '<div class="barcode_2_2">';
                                                }elseif ($style == 12 || $style == 18 || $style == 24 || $style == 40) {
                                                    echo '<div class="barcodea4">';
                                                } elseif ($style != 50) {
                                                    echo '<div class="barcode">';
                                                }
                                                foreach ($barcodes as $item) {
                                                    for ($r = 1; $r <= $item['quantity']; $r++) {
                                                        echo '<div class="item style'.$style.'" '.
                                                            ($style == 50 && $this->input->post('cf_width') && $this->input->post('cf_height') ?
                                                                'style="width:'.$this->input->post('cf_width').'in;height:'.$this->input->post('cf_height').'in;border:0;"' : '')
                                                            .'>';
                                                        if ($style == 50) {
                                                            if ($this->input->post('cf_orientation')) {
                                                                $ty = (($this->input->post('cf_height')/$this->input->post('cf_width'))*100).'%';
                                                                $landscape = '
                                                -webkit-transform-origin: 0 0;
                                                -moz-transform-origin:    0 0;
                                                -ms-transform-origin:     0 0;
                                                transform-origin:         0 0;
                                                -webkit-transform: translateY('.$ty.') rotate(-90deg);
                                                -moz-transform:    translateY('.$ty.') rotate(-90deg);
                                                -ms-transform:     translateY('.$ty.') rotate(-90deg);
                                                transform:         translateY('.$ty.') rotate(-90deg);
                                                ';
                                                                echo '<div class="div50" style="width:'.$this->input->post('cf_height').'in;height:'.$this->input->post('cf_width').'in;border: 1px dotted #CCC;'.$landscape.'">';
                                                            } else {
                                                                echo '<div class="div50" style="width:'.$this->input->post('cf_width').'in;height:'.$this->input->post('cf_height').'in;border: 1px dotted #CCC;padding-top:0.025in;">';
                                                            }
                                                        }
                                                        if($item['image']) {
                                                            echo '<span class="product_image"><img src="'.base_url('uploads/company/'.get_option('company_logo')).'" alt="'.get_option('companyname').'" /></span><br>';
                                                        }
                                                        if($item['site']) {
                                                            echo '<span class="barcode_site">'.$item['site'].'</span><br>';
                                                        }
                                                        if($item['name']) {
                                                            echo '<span class="barcode_name">'.$item['name'].'</span><br>';
                                                        }
                                                        if($item['price']) {
                                                            echo '<span class="barcode_price">'._l('price').' ';
                                                            if($item['currencies']) {
                                                                foreach ($currencies as $currency) {
                                                                    echo $currency['name'] . ': ' . format_money($item['price'], $currency['symbol']).', ';
                                                                }
                                                            } else {
                                                                echo $item['price'];
                                                            }
                                                            echo '</span> ';
                                                        }
                                                        if($item['unit']) {
                                                            echo '<span class="barcode_unit">'._l('unit').': '.$item['unit'].'</span><br> ';
                                                        }
                                                        if($item['category']) {
                                                            echo '<span class="barcode_category">'._l('category').': '.$item['category'].'</span> <br>';
                                                        }
                                                        echo '<span class="barcode_image"><img src="'.admin_url('invoice_items/barcode/'.$item['barcode'].'/'.$item['bcs'].'/'.$item['bcis']).'" alt="'.$item['barcode'].'" class="bcimg" /></span>';
                                                        if ($style == 50) {
                                                            echo '</div>';
                                                        }
                                                        echo '</div>';
                                                        if ($style == 40) {
                                                            if ($c % 40 == 0) {
                                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                            }
                                                        } elseif ($style == 30) {
                                                            if ($c % 30 == 0) {
                                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                                            }
                                                        } elseif ($style == 24) {
                                                            if ($c % 24 == 0) {
                                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                            }
                                                        } elseif ($style == 20) {
                                                            if ($c % 20 == 0) {
                                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                                            }
                                                        } elseif ($style == 18) {
                                                            if ($c % 18 == 0) {
                                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                            }
                                                        } elseif ($style == 14) {
                                                            if ($c % 14 == 0) {
                                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                                            }
                                                        } elseif ($style == "12-2") {
                                                            if ($c % 2 == 0) {
                                                                echo '</div><div class="clearfix"></div><div class="barcode_2_2">';
                                                            }
                                                        }  elseif ($style == 12) {
                                                            if ($c % 12 == 0) {
                                                                echo '</div><div class="clearfix"></div><div class="barcodea4">';
                                                            }
                                                        } elseif ($style == 10) {
                                                            if ($c % 10 == 0) {
                                                                echo '</div><div class="clearfix"></div><div class="barcode">';
                                                            }
                                                        }
                                                        $c++;
                                                    }
                                                }
                                                if ($style != 50) {
                                                    echo '</div>';
                                                }
                                                echo '<button type="button" onclick="window.print();return false;" class="btn btn-primary btn-block tip no-print" title="'._l('print').'"><i class="icon fa fa-print"></i> '._l('print').'</button>';
                                            } else {
                                                echo '<h3>'._l('no_product_selected').'</h3>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script type="text/javascript">
    if (typeof (jQuery) != 'undefined') {
        init_barcode_js();
        $(window).off('beforeunload');
    } else {
        window.addEventListener('load', function () {
            var initItemsJsInterval = setInterval(function () {
                debugger;
                if (typeof (jQuery) != 'undefined') {
                    init_barcode_js();
                    clearInterval(initItemsJsInterval);
                    $(window).off('beforeunload');
                }
            }, 1000);

        });
    }
    var ac = false; bcitems = {};
    /*if (localStorage.getItem('bcitems')) {
        bcitems = JSON.parse(localStorage.getItem('bcitems'));
    }*/
    <?php if($items) { ?>
    localStorage.setItem('bcitems', JSON.stringify(<?= $items; ?>));
    <?php } ?>
    // init_item_js()
    function init_barcode_js(){
        $(document).ready(function() {
            <?php if ($this->input->post('print')) { ?>
            $( window ).load(function() {
                $('html, body').animate({
                    scrollTop: ($("#barcode-con").offset().top)-15
                }, 1000);
            });
            <?php } ?>
            if (localStorage.getItem('bcitems')) {
                loadItems();
                localStorage.removeItem('bcitems');
            }

            $('#styles').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
                var id = $('#styles').selectpicker('val');
                localStorage.setItem('bcstyle', id);
                if ($(this).val() == 50) {
                    $('.cf-con').removeClass('hidden');
                    $('.cf-con').slideDown();
                } else {
                    $('.cf-con').addClass('hidden');
                    $('.cf-con').slideUp();
                }
            });
            if (style = localStorage.getItem('bcstyle')) {
                $('#styles').selectpicker('val',style);
                if (style == 50) {
                    $('.cf-con').removeClass('hidden');
                    $('.cf-con').slideDown();
                } else {
                    $('.cf-con').addClass('hidden');
                    $('.cf-con').slideUp();
                }
            }

            $('#cf_width').change(function (e) {
                localStorage.setItem('cf_width', $(this).val());
            });
            if (cf_width = localStorage.getItem('cf_width')) {
                $('#cf_width').val(cf_width);
            }

            $('#cf_height').change(function (e) {
                localStorage.setItem('cf_height', $(this).val());
            });
            if (cf_height = localStorage.getItem('cf_height')) {
                $('#cf_height').val(cf_height);
            }

            $('#cf_orientation').change(function (e) {
                localStorage.setItem('cf_orientation', $(this).val());
            });
            if (cf_orientation = localStorage.getItem('cf_orientation')) {
                $('#cf_orientation').val(cf_orientation);
            }

            $(document).on('change', '#site_name', function() {
                if(this.checked) {
                    localStorage.setItem('bcsite_name', 1);
                }else {
                    localStorage.setItem('bcsite_name', 0);
                }
            });

            if (site_name = parseInt(localStorage.getItem('bcsite_name'))) {
                $('#site_name').prop('checked',true);
            }else {
                $('#site_name').prop('checked',false);
            }

            $(document).on('change', '#product_name', function(event) {
                if(this.checked) {
                    localStorage.setItem('bcproduct_name', 1);
                }else {
                    localStorage.setItem('bcproduct_name', 0);
                }
            });

            if (product_name = parseInt(localStorage.getItem('bcproduct_name'))) {
                $('#product_name').prop('checked',true);
            }else {
                $('#product_name').prop('checked',false);
            }

            $(document).on('change', '#price', function(event) {
                if(this.checked) {
                    localStorage.setItem('bcprice', 1);
                }else {
                    localStorage.setItem('bcprice', 0);
                }
            });

            if (price = parseInt(localStorage.getItem('bcprice'))) {
                $('#price').prop('checked',true);
            }else {
                $('#price').prop('checked',false);
            }

            $(document).on('change', '#currencies', function(event) {
                if(this.checked) {
                    localStorage.setItem('bccurrencies', 1);
                }else {
                    localStorage.setItem('bccurrencies', 0);
                }
            });

            if (currencies = parseInt(localStorage.getItem('bccurrencies'))) {
                $('#currencies').prop('checked',true);
            }else {
                $('#currencies').prop('checked',false);
            }


            $(document).on('change', '#unit', function(event) {
                if(this.checked) {
                    localStorage.setItem('bcunit', 1);
                }else {
                    localStorage.setItem('bcunit', 0);
                }
            });

            if (unit = parseInt(localStorage.getItem('bcunit'))) {
                $('#unit').prop('checked',true);
            }else {
                $('#unit').prop('checked',false);
            }

            $(document).on('change', '#category', function(event) {
                if(this.checked) {
                    localStorage.setItem('bccategory', 1);
                }else {
                    localStorage.setItem('bccategory', 0);
                }
            });

            if (category = parseInt(localStorage.getItem('bccategory'))) {
                $('#category').prop('checked',true);
            }else {
                $('#category').prop('checked',false);
            }

            $(document).on('change', '#check_promo', function(event) {
                if(this.checked) {
                    localStorage.setItem('bccheck_promo', 1);
                }else {
                    localStorage.setItem('bccheck_promo', 0);
                }
            });
            if (check_promo = parseInt(localStorage.getItem('bccheck_promo'))) {
                $('#check_promo').prop('checked',true);
            }else {
                $('#check_promo').prop('checked',false);
            }


            $(document).on('click', '.del', function () {
                var id = $(this).attr('id');
                delete bcitems[id];
                localStorage.setItem('bcitems', JSON.stringify(bcitems));
                $(this).closest('#row_' + id).remove();
            });

            /*$('#item_select').on('changed.bs.select', function (e, clickedIndex, isSelected, previousValue) {
                var id = $('#item_select').selectpicker('val');
                // do something...
                requestGetJSON('invoice_items/get_item_by_id/' + id).done(function(response) {
                    add_product_item(response);
                });
            });*/
            $("body").on('changed.bs.select', 'select[name="item_select"]', function (e, clickedIndex, newValue, oldValue) {
                var itemid = $(this).selectpicker('val');
                if (newValue != oldValue &&  itemid != '') {
                    requestGetJSON('invoice_items/get_item_by_id/' + itemid).done(function (response) {
                        add_product_item(response);
                        if ($('#item_select').hasClass('ajax-search') && $('#item_select').selectpicker('val') !== '') {
                            $('#item_select').prepend('<option></option>');
                        }
                        init_selectpicker();
                        $('#item_select').selectpicker('val', '');
                    });
                }
            });
            $('#reset').click(function (e) {

                bootbox.confirm(lang.r_u_sure, function (result) {
                    if (result) {
                        if (localStorage.getItem('bcitems')) {
                            localStorage.removeItem('bcitems');
                        }
                        if (localStorage.getItem('bcstyle')) {
                            localStorage.removeItem('bcstyle');
                        }
                        if (localStorage.getItem('bcsite_name')) {
                            localStorage.removeItem('bcsite_name');
                        }
                        if (localStorage.getItem('bcproduct_name')) {
                            localStorage.removeItem('bcproduct_name');
                        }
                        if (localStorage.getItem('bcprice')) {
                            localStorage.removeItem('bcprice');
                        }
                        if (localStorage.getItem('bccurrencies')) {
                            localStorage.removeItem('bccurrencies');
                        }
                        if (localStorage.getItem('bcunit')) {
                            localStorage.removeItem('bcunit');
                        }
                        if (localStorage.getItem('bccategory')) {
                            localStorage.removeItem('bccategory');
                        }
                        // if (localStorage.getItem('cf_width')) {
                        //     localStorage.removeItem('cf_width');
                        // }
                        // if (localStorage.getItem('cf_height')) {
                        //     localStorage.removeItem('cf_height');
                        // }
                        // if (localStorage.getItem('cf_orientation')) {
                        //     localStorage.removeItem('cf_orientation');
                        // }

                        $('#modal-loading').show();
                        window.location.replace("<?= admin_url('products/print_barcodes'); ?>");
                    }
                });
            });

            var old_row_qty;
            $(document).on("focus", '.quantity', function () {
                old_row_qty = $(this).val();
            }).on("change", '.quantity', function () {
                var row = $(this).closest('tr');
                if (!is_numeric($(this).val())) {
                    $(this).val(old_row_qty);
                    bootbox.alert(lang.unexpected_value);
                    return;
                }
                var new_qty = parseFloat($(this).val()),
                    item_id = row.attr('data-item-id');
                bcitems[item_id].qty = new_qty;
                localStorage.setItem('bcitems', JSON.stringify(bcitems));
            });
            $(window).off('beforeunload');
        });
    }

    init_ajax_search('items','#item_select.ajax-search',undefined,admin_url+'items/search');

    function add_product_item(item) {
        ac = true;
        if (item == null) {
            return false;
        }

        item_id = item.itemid;
        if (!bcitems[item_id]) {
            bcitems[item_id] = item
        }else {
            bcitems[item_id] = item
        }

        localStorage.setItem('bcitems', JSON.stringify(bcitems));
        loadItems();
        return true;

    }

    function loadItems () {
        if (localStorage.getItem('bcitems')) {
            $("#bcTable tbody").empty();
            bcitems = JSON.parse(localStorage.getItem('bcitems'));
            $.each(bcitems, function () {
                var item = this;
                var row_no = item.itemid;
                var vd = '';
                var newTr = $('<tr id="row_' + row_no + '" class="row_' + item.itemid + '" data-item-id="' + item.itemid + '"></tr>');
                tr_html = '<td><input name="product[]" type="hidden" value="' + item.itemid + '"><span id="name_' + row_no + '">' + item.long_description + ' (' + item.description + ')</span></td>';
                if(item.quantity != null){
                    tr_html += '<td><input class="form-control quantity text-center" name="quantity[]" type="text" <?= ($this->input->get('is_purchase') ? 'readonly="true"':'') ?> value="' + item.quantity + '" data-id="' + row_no + '" data-item="' + item.itemid + '" id="quantity_' + row_no + '"></td>';
                }else {
                    tr_html += '<td><input class="form-control quantity text-center" name="quantity[]" type="text" value="0" data-id="' + row_no + '" data-item="' + item.itemid + '" id="quantity_' + row_no + '"></td>';
                }
                tr_html += '<td class="text-center"><i class="fa fa-times tip del" id="' + row_no + '" title="Remove" style="cursor:pointer;"></i></td>';
                newTr.html(tr_html);
                newTr.appendTo("#bcTable");
            });
            return true;
        }
    }

</script>
</body>
</html>