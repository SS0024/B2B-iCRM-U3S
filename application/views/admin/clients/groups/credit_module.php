<?php if(isset($client)){ ?>
<h4 class="customer-profile-group-heading"><?php echo _l('Credit Module'); ?></h4>
<div class="col-md-12">
 <div class="clearfix"></div>
 <div class="usernote ">
     <?php echo form_open($this->uri->uri_string(),array('class'=>'client-form','autocomplete'=>'off')); ?>
     <div class="form-group mtop10 mbot10 no-mbot">
         <p>Credit Facility</p>
         <div class="onoffswitch">
             <input type="checkbox" id="credit_facility"
                    class="onoffswitch-checkbox" <?php if (isset($client)) {
                 if ($client->credit_facility == 1) {
                     echo 'checked';
                 }
             }; ?> value="1"     name="credit_facility">
             <label class="onoffswitch-label" for="credit_facility" data-toggle="tooltip" ></label>
         </div>
     </div>
     <div class="row">
         <div class="col-md-6">
             <?php echo render_input( 'credit_period', 'Credit Period', $client->credit_period); ?>
         </div>
         <div class="col-md-6">
             <?php echo render_input( 'credit_amount', 'Credit Amount', $client->credit_amount); ?>
         </div>
     </div>
    <button class="btn btn-info pull-right mbot15">
        <?php echo _l( 'submit'); ?>
    </button>
    <?php echo form_close(); ?>
</div>
<div class="clearfix"></div>
<?php } ?>
