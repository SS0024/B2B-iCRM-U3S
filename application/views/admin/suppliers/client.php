<?php init_head(); ?>
<div id="wrapper" class="customer_profile">
   <div class="content">
      <div class="row">
         <?php if($group == 'profile'){ ?>
         <div class="btn-bottom-toolbar btn-toolbar-container-out text-right">
            <button class="btn btn-info only-save customer-form-submiter">
            <?php echo _l( 'submit'); ?>
            </button>
            <?php if(!isset($client)){ ?>
            <!--<button class="btn btn-info save-and-add-contact customer-form-submiter">
            <?php /*echo _l( 'save_customer_and_add_contact'); */?>
            </button>-->
            <?php } ?>
         </div>
         <?php } ?>
         <div class="col-md-<?php if(isset($client)){echo 12;} else {echo 12;} ?>">
            <div class="panel_s">
               <div class="panel-body">
                  <?php if(isset($client)){ ?>
                  <?php echo form_hidden( 'isedit'); ?>
                  <?php echo form_hidden( 'userid',$client->id); ?>
                  <div class="clearfix"></div>
                  <?php } ?>
                  <div>
                     <div class="tab-content">
                        <?php $this->load->view('admin/suppliers/groups/'.$group); ?>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <?php if($group == 'profile'){ ?>
      <div class="btn-bottom-pusher"></div>
      <?php } ?>
   </div>
</div>
<?php init_tail(); ?>
<?php
if ($group == 'products') {
    $this->load->view('admin/scheduled_services/newServiceModal');
}
?>
<?php if(isset($client)){ ?>
<script>
   $(function(){
      init_rel_tasks_table(<?php echo $client->userid; ?>,'customer');
   });
</script>
<?php } ?>
<?php if(!empty(get_option('google_api_key')) && !empty($client->latitude) && !empty($client->longitude)){ ?>
<script>
   var latitude = '<?php echo $client->latitude; ?>';
   var longitude = '<?php echo $client->longitude; ?>';
   var mapMarkerTitle = '<?php echo $client->company; ?>';
</script>
<?php echo app_script('assets/js','map.js'); ?>
<script async defer src="https://maps.googleapis.com/maps/api/js?key=<?php echo get_option('google_api_key'); ?>&callback=initMap"></script>
<?php } ?>
<?php $this->load->view('admin/suppliers/client_js'); ?>
</body>
</html>
