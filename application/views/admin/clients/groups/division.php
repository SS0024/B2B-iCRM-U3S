<?php if(isset($client)){ ?>
<h4 class="customer-profile-group-heading">Division</h4>
<?php if($this->session->flashdata('gdpr_delete_warning')){ ?>
    <div class="alert alert-warning">
     [GDPR] The division you removed has associated proposals using the email address of the division and other personal information. You may want to re-check all proposals related to this customer and remove any personal data from proposals linked to this division.
   </div>
<?php } ?>
<?php if((has_permission('customers','','create') || is_customer_admin($client->userid)) && $client->registration_confirmed == '1'){
   $disable_new_contacts = false;
   if(is_empty_customer_company($client->userid) && total_rows('tblcontacts',array('userid'=>$client->userid)) == 1){
      $disable_new_contacts = true;
   }
   ?>
<div class="inline-block new-contact-wrapper" data-title="<?php echo _l('customer_contact_person_only_one_allowed'); ?>"<?php if($disable_new_contacts){ ?> data-toggle="tooltip"<?php } ?>>
   <a href="#" onclick="division(<?php echo $client->userid; ?>); return false;" class="btn btn-info new-division mbot25"><?php echo 'New Division'; ?></a>
</div>
<?php 
//$divisions = get_all_divisions();

?>
<table class="table dataTable no-footer">
  <thead>
    <th>Division</th>
    <th>Address</th>
    <th>Pincode</th>
    <th>City</th>
    <th>State</th>
    <th>Country</th>
  </thead>
  <tbody>
  <?php 
  //echo 123;
//print_r($divisions);
    foreach ($divisions as $division_data) {
      
  ?><tr>
    
  
    <td><?php echo $division_data->division;   ?>
    <div class="row-options"><a href="#" onclick="division(<?php echo $client->userid;?>,<?php echo $division_data->id;?>);return false;">Edit </a> | <a href="../../clients/delete_division/<?php echo $division_data->id;?>" class="text-danger _delete">Delete </a></div>
    </td>
    <td><?php echo $division_data->address;  ?></td>
    <td><?php echo $division_data->pincode;  ?></td>
    <td><?php echo $division_data->city;  ?></td>
    <td><?php echo $division_data->state;  ?></td>
    <td><?php echo $division_data->country;  ?></td>
    </tr>
  <?php
  }
  
  ?>
  </tbody>
</table>
<?php
  }
} 
?>
<div id="division_data"></div>
<div id="consent_data"></div>
