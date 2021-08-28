<!DOCTYPE html>
<html lang="en">
<head>
    <?php $isRTL = (is_rtl() ? 'true' : 'false'); ?>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="user-scalable=no, width=device-width, initial-scale=1, maximum-scale=1" />
    <?php if(get_option('favicon') != ''){ ?>
    <link href="<?php echo base_url('uploads/company/'.get_option('favicon')); ?>" rel="shortcut icon">
    <?php } ?>
    <title><?php if (isset($title)){ echo $title; } else { echo get_option('companyname'); } ?></title>
    <?php echo app_stylesheet('assets/css','reset.css'); ?>
    <link href='<?php echo base_url('assets/plugins/roboto/roboto.css'); ?>' rel='stylesheet'>
    <link href="<?php echo base_url('assets/plugins/app-build/vendor.css?v='.get_app_version()); ?>" rel="stylesheet">
    <?php if($isRTL === 'true'){ ?>
    <link href="<?php echo base_url('assets/plugins/bootstrap-arabic/css/bootstrap-arabic.min.css'); ?>" rel="stylesheet">
    <?php } ?>
    <?php if(isset($calendar_assets)){ ?>
    <link href='<?php echo base_url('assets/plugins/fullcalendar/fullcalendar.min.css?v='.get_app_version()); ?>' rel='stylesheet' />
    <?php } ?>
    <?php if(isset($projects_assets)){ ?>
    <link href='<?php echo base_url('assets/plugins/jquery-comments/css/jquery-comments.css'); ?>' rel='stylesheet' />
    <link href='<?php echo base_url('assets/plugins/gantt/css/style.css'); ?>' rel='stylesheet' />
    <?php } ?>
    <?php echo app_stylesheet('assets/css','style.css'); ?>
    <?php if(file_exists(FCPATH.'assets/css/custom.css')){ ?>
    <link href="<?php echo base_url('assets/css/custom.css'); ?>" rel="stylesheet">
    <?php } ?>
    <?php render_custom_styles(array('general','tabs','buttons','admin','modals','tags')); ?>
    <?php render_admin_js_variables(); ?>
    <script>
        appLang['datatables'] = <?php echo json_encode(get_datatables_language_array()); ?>;
        var totalUnreadNotifications = <?php echo $current_user->total_unread_notifications; ?>,
        proposalsTemplates = <?php echo json_encode(get_proposal_templates()); ?>,
        contractsTemplates = <?php echo json_encode(get_contract_templates()); ?>,
        availableTags = <?php echo json_encode(get_tags_clean()); ?>,
        availableTagsIds = <?php echo json_encode(get_tags_ids()); ?>,
        billingAndShippingFields = ['billing_street','billing_city','billing_state','billing_zip','billing_country','shipping_street','shipping_city','shipping_state','shipping_zip','shipping_country'],
        locale = '<?php echo $locale; ?>',
        isRTL = '<?php echo $isRTL; ?>',
        tinymceLang = '<?php echo get_tinymce_language(get_locale_key($app_language)); ?>',
        monthsJSON = '<?php echo json_encode(array(_l('January'),_l('February'),_l('March'),_l('April'),_l('May'),_l('June'),_l('July'),_l('August'),_l('September'),_l('October'),_l('November'),_l('December'))); ?>',				
		service_charges_tax_rate = <?php echo SERVICE_CHARGES_TAX_RATE; ?>,		
        taskid,taskTrackingStatsData,taskAttachmentDropzone,taskCommentAttachmentDropzone,leadAttachmentsDropzone,newsFeedDropzone,expensePreviewDropzone,taskTrackingChart,cfh_popover_templates = {},_table_api;				
    </script>
    <style>

        @keyframes dtb-spinner{100%{transform:rotate(360deg)}}@-o-keyframes dtb-spinner{100%{-o-transform:rotate(360deg);transform:rotate(360deg)}}@-ms-keyframes dtb-spinner{100%{-ms-transform:rotate(360deg);transform:rotate(360deg)}}@-webkit-keyframes dtb-spinner{100%{-webkit-transform:rotate(360deg);transform:rotate(360deg)}}@-moz-keyframes dtb-spinner{100%{-moz-transform:rotate(360deg);transform:rotate(360deg)}}div.dt-button-info{position:fixed;top:50%;left:50%;width:400px;margin-top:-100px;margin-left:-200px;background-color:white;border:2px solid #111;box-shadow:3px 3px 8px rgba(0,0,0,0.3);border-radius:3px;text-align:center;z-index:21}div.dt-button-info h2{padding:0.5em;margin:0;font-weight:normal;border-bottom:1px solid #ddd;background-color:#f3f3f3}div.dt-button-info>div{padding:1em}div.dt-button-collection-title{text-align:center;padding:0.3em 0 0.5em;font-size:0.9em}div.dt-button-collection-title:empty{display:none}div.dt-button-collection{position:absolute;z-index:2001}div.dt-button-collection div.dropdown-menu{display:block;z-index:2002;min-width:100%}div.dt-button-collection div.dt-button-collection-title{background-color:white;border:1px solid rgba(0,0,0,0.15)}div.dt-button-collection.fixed{position:fixed;top:50%;left:50%;margin-left:-75px;border-radius:0}div.dt-button-collection.fixed.two-column{margin-left:-200px}div.dt-button-collection.fixed.three-column{margin-left:-225px}div.dt-button-collection.fixed.four-column{margin-left:-300px}div.dt-button-collection>:last-child{display:block !important;-webkit-column-gap:8px;-moz-column-gap:8px;-ms-column-gap:8px;-o-column-gap:8px;column-gap:8px}div.dt-button-collection>:last-child>*{-webkit-column-break-inside:avoid;break-inside:avoid}div.dt-button-collection.two-column{width:400px}div.dt-button-collection.two-column>:last-child{padding-bottom:1px;-webkit-column-count:2;-moz-column-count:2;-ms-column-count:2;-o-column-count:2;column-count:2}div.dt-button-collection.three-column{width:450px}div.dt-button-collection.three-column>:last-child{padding-bottom:1px;-webkit-column-count:3;-moz-column-count:3;-ms-column-count:3;-o-column-count:3;column-count:3}div.dt-button-collection.four-column{width:600px}div.dt-button-collection.four-column>:last-child{padding-bottom:1px;-webkit-column-count:4;-moz-column-count:4;-ms-column-count:4;-o-column-count:4;column-count:4}div.dt-button-collection .dt-button{border-radius:0}div.dt-button-collection.fixed{max-width:none}div.dt-button-collection.fixed:before,div.dt-button-collection.fixed:after{display:none}div.dt-button-background{position:fixed;top:0;left:0;width:100%;height:100%;z-index:999}@media screen and (max-width: 767px){div.dt-buttons{float:none;width:100%;text-align:center;margin-bottom:0.5em}div.dt-buttons a.btn{float:none}}div.dt-buttons button.btn.processing,div.dt-buttons div.btn.processing,div.dt-buttons a.btn.processing{color:rgba(0,0,0,0.2)}div.dt-buttons button.btn.processing:after,div.dt-buttons div.btn.processing:after,div.dt-buttons a.btn.processing:after{position:absolute;top:50%;left:50%;width:16px;height:16px;margin:-8px 0 0 -8px;box-sizing:border-box;display:block;content:' ';border:2px solid #282828;border-radius:50%;border-left-color:transparent;border-right-color:transparent;animation:dtb-spinner 1500ms infinite linear;-o-animation:dtb-spinner 1500ms infinite linear;-ms-animation:dtb-spinner 1500ms infinite linear;-webkit-animation:dtb-spinner 1500ms infinite linear;-moz-animation:dtb-spinner 1500ms infinite linear}
        #scroll {
            position:fixed;
            right:10px;
            bottom:60px;
            cursor:pointer;
            width:50px;
            height:50px;
            background-color:#3498db;
            text-indent:-9999px;
            display:none;
            z-index: 1111;
            -webkit-border-radius:60px;
            -moz-border-radius:60px;
            border-radius:60px
        }
        #scroll span {
            position:absolute;
            top:50%;
            left:50%;
            margin-left:-8px;
            margin-top:-12px;
            height:0;
            width:0;
            border:8px solid transparent;
            border-bottom-color:#ffffff;
        }
        #scroll:hover {
            background-color:#e74c3c;
            opacity:1;filter:"alpha(opacity=100)";
            -ms-filter:"alpha(opacity=100)";
        }
    </style>
    <?php do_action('app_admin_head'); ?>
</head>
<body <?php echo admin_body_class(isset($bodyclass) ? $bodyclass : ''); ?><?php if($isRTL === 'true'){ echo 'dir="rtl"';}; ?>>
<?php do_action('after_body_start'); ?>
