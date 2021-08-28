<?php init_head(); ?>
<div id="wrapper">
  <div class="content">
    <div class="row">
      <div class="col-md-12">
        <div class="panel_s">
          <div class="panel-body">
            <?php if(isset($consent_purposes)) { ?>
            <div class="row mbot15">
              <div class="col-md-3 contacts-filter-column">
               <div class="select-placeholder">
                <select name="custom_view" title="<?php echo _l('gdpr_consent'); ?>" id="custom_view" class="selectpicker" data-width="100%">
                 <option value=""></option>
                 <?php foreach($consent_purposes as $purpose) { ?>
                 <option value="consent_<?php echo $purpose['id']; ?>">
                  <?php echo $purpose['name']; ?>
                </option>
                <?php } ?>
              </select>
            </div>
          </div>
        </div>
        <?php } ?>
        <div class="clearfix"></div>
        <?php
        $table_data = array(_l('Division title'),_l('Address'),_l('City'),_l('Pincode'),_l('State'),_l('Country'));
        if(is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1'){
       }
       /*$custom_fields = get_custom_fields('divisions',array('show_on_table'=>1));
       foreach($custom_fields as $field){
        array_push($table_data,$field['name']);
      }*/
      render_datatable($table_data,'all-divisions');
      ?>
    </div>
  </div>
</div>
</div>
</div>
</div>
<?php init_tail(); ?>
<?php $this->load->view('admin/clients/client_js'); ?>
<div id="division_data"></div>
<div id="consent_data"></div>
<script>
 $(function(){
  var optionsHeading = [];
  var allContactsServerParams = {
   "custom_view": "[name='custom_view']",
 }
 <?php if(is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1'){ ?>
  optionsHeading.push($('#th-consent').index());
  <?php } ?>
  _table_api = initDataTable('.table-all-divisions', window.location.href, optionsHeading, optionsHeading, allContactsServerParams, [0,'asc']);
  if(_table_api) {
   <?php if(is_gdpr() && get_option('gdpr_enable_consent_for_contacts') == '1'){ ?>
    _table_api.on('draw', function () {
      var tableData = $('.table-all-divisions').find('tbody tr');
      $.each(tableData, function() {
        $(this).find('td:eq(2)').addClass('bg-light-gray');
      });
    });
    $('select[name="custom_view"]').on('change', function(){
      _table_api.ajax.reload()
      .columns.adjust()
      .responsive.recalc();
    });
    <?php } ?>
  }
});
</script>
</body>
</html>
