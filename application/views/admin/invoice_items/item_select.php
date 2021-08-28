<div class="form-group mbot25 items-wrapper select-placeholder<?php if(has_permission('items','','create')){ echo ' input-group-select'; } ?>">
  <div class="<?php if(has_permission('items','','create')){ echo 'input-group input-group-select'; } ?>">
    <div class="items-select-wrapper">
     <select name="item_select" class="selectpicker no-margin<?php if($ajaxItems == true){echo ' ajax-search';} ?><?php if(has_permission('items','','create')){ echo ' _select_input_group'; } ?>" data-width="100%" id="item_select" data-none-selected-text="<?php echo _l('add_item'); ?>" data-live-search="true">
      <option value=""></option>
      <?php foreach($items as $group_id=>$_items){ ?>
      <optgroup data-group-id="<?php echo $group_id; ?>" label="<?php echo $_items[0]['group_name']; ?>">
       <?php foreach($_items as $item){ ?>
           <?php
                if ((isset($bodyclass) && ($bodyclass == 'estimates' || $bodyclass =='invoice' || $bodyclass =='purchaseorder'))){
                    if(isset($item['quantity']) && $item['quantity'] != 0){
                        ?>
                        <option value="<?php echo $item['id']; ?>" data-subtext="<?php echo strip_tags(mb_substr($item['long_description'],0,200)).'...'; ?>">(<?php echo _format_number($item['rate']); ; ?>) <?php echo $item['description']; ?></option>
                        <?php
                    }
                }elseif((isset($bodyclass) && ($bodyclass == 'purchase'))){
                    ?>
                    <option value="<?php echo $item['id']; ?>" data-subtext="<?php echo strip_tags(mb_substr($item['long_description'],0,200)).'...'; ?>">(<?php echo _format_number($item['product_cost']); ; ?>) <?php echo $item['description']; ?></option>
                    <?php
                }elseif((isset($bodyclass) && ($bodyclass == 'mfpurchase'))){
                    ?>
                    <option value="<?php echo $item['id']; ?>" data-subtext="<?php echo strip_tags(mb_substr($item['long_description'],0,200)).'...'; ?>">(<?php echo _format_number($item['product_cost']); ; ?>) <?php echo $item['description']; ?></option>
                    <?php
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
 <?php if(has_permission('items','','create')){ ?>
 <div class="input-group-addon">
   <a href="#" data-toggle="modal" data-target="#sales_item_modal">
    <i class="fa fa-plus"></i>
  </a>
</div>
<?php } ?>
</div>
</div>
