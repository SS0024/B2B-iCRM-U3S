<!-- Modal Contact -->
<div class="modal fade" id="division" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/clients/division/'.$customer_id.'/'.$contactid,array('id'=>'contact-form','autocomplete'=>'off')); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="location.href='';"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo $title; ?><br /><small class="color-white" id=""><?php echo get_company_name($customer_id,true); ?></small></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="pull-right">
                        <a href="javascript:void(0);" onclick="loadsamecustomerinfo(<?php echo $customer_id;?>);" style="margin-right: 20px;">Same as Customer Info</a>
                    </div>
                    <div class="col-md-12">
                        <!-- // For email exist check -->
                        <?php echo form_hidden('userid',$customer_id); ?>
                        <?php $value=( isset($divisions[0]) ? $divisions[0]->division : ''); ?>
                        <?php echo render_input( 'division', 'Division Name',$value); ?>
                        <?php $value=( isset($divisions[0]) ? $divisions[0]->address : ''); ?>
                        <?php echo render_input( 'address', 'Address',$value); ?>
                        <?php $value=( isset($divisions[0]) ? $divisions[0]->city : ''); ?>
                        <?php echo render_input( 'city', 'City',$value); ?>
                        <?php $value=( isset($divisions[0]) ? $divisions[0]->pincode : ''); ?>
                        <?php echo render_input( 'pincode', 'Pincode',$value); ?>
                         <?php $value=( isset($divisions[0]) ? $divisions[0]->state : ''); ?>
                        <?php echo render_input( 'state', 'State',$value); ?>
                        <?php $value=( isset($divisions[0]) ? $divisions[0]->country : ''); ?>
                        <?php echo render_input( 'country', 'Country',$value,'text',array('autocomplete'=>'off')); ?>
                    <!-- fake fields are a workaround for chrome autofill getting the wrong fields -->
                    <!-- <input  type="text" class="fake-autofill-field" name="fakeusernameremembered" value='' tabindex="-1" />
                    <input  type="password" class="fake-autofill-field" name="fakepasswordremembered" value='' tabindex="-1"/>
 -->
    <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
        <button type="submit" class="btn btn-info" data-loading-text="<?php echo _l('wait_text'); ?>" onclick="divcks();" autocomplete="off" data-form="#division-form"><?php echo _l('submit'); ?></button>
    </div>
    <?php echo form_close(); ?>
</div>
</div>
</div>
<!-- <?php if(!isset($division)){ ?>
    <script>
        $(function(){
            // Guess auto email notifications based on the default contact permissios
            var permInputs = $('input[name="permissions[]"]');
            $.each(permInputs,function(i,input){
                input = $(input);
                if(input.prop('checked') === true){
                    $('#contact_email_notifications [data-perm-id="'+input.val()+'"]').prop('checked',true);
                }
            });
        });
    </script>
<?php } ?> -->
<script type="text/javascript">
    function divcks()
    {
        setTimeout(function() {
            location.href='';
        }, 2000);
    }
    function loadsamecustomerinfo(cust)
    {
        $.post("../loadcustominfo/"+cust, function( data ) {
            var totl=JSON.parse(data);
            $('input[name="address"]').val(totl.address);
            $('input[name="city"]').val(totl.city);
            $('input[name="pincode"]').val(totl.zip);
            $('input[name="state"]').val(totl.state);
            $('input[name="country"]').val(totl.country);
        });
    }
</script>