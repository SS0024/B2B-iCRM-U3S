<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">

            <?php echo $this->import->downloadSampleFormHtml(); ?>
            <?php echo $this->import->maxInputVarsWarningHtml(); ?>

            <?php if(!$this->import->isSimulation()) { ?>

              <?php echo $this->import->importGuidelinesInfoHtml(); ?>
              <?php echo $this->import->createSampleTableHtml(); ?>

            <?php } else { ?>

              <?php echo $this->import->simulationDataInfo(); ?>
              <?php echo $this->import->createSampleTableHtml(true); ?>

            <?php } ?>
            <div class="row">
              <div class="col-md-4">
                <?php echo form_open_multipart($this->uri->uri_string(),array('id'=>'import_form')) ;?>
                <?php echo form_hidden('items_import','true'); ?>
                <?php echo render_input('file_csv','choose_csv_file','','file'); ?>
                <div class="form-group">
                  <button type="button" class="btn btn-info import btn-import-submit"><?php echo _l('import'); ?></button>
                </div>
                <?php echo form_close(); ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php init_tail(); ?>
<script src="<?php echo base_url('assets/plugins/jquery-validation/additional-methods.min.js'); ?>"></script>
<script>
  $(function(){
   _validate_form($('#import_form'),{file_csv:{required:true,extension: "csv"}});
      $('.btn-import-submit').on('click',function(){
          if($(this).hasClass('simulate')){
              $('#import_form').append(hidden_input('simulate',true));
          }
          $('#import_form').submit();
      });
 });
</script>
</body>
</html>
