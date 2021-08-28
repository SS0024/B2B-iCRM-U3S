<?php init_head(); ?>
<div id="wrapper">
   <div class="content">
      <div class="row">
		 <div class="col-md-12">
            
            <div class="row">
               <div class="col-md-12" id="small-table">
                  <div class="panel_s">
                     <div class="panel-body">
                        <!-- if invoiceid found in url -->
                        
						<input type="hidden" name="proposal_id" value="">
                        <div class="">
							<div id="DataTables_Table_0_wrapper" class="dataTables_wrapper form-inline dt-bootstrap no-footer">
								<div class="row"></div>
								<?php /* <div class="row"><div class="col-md-7"><div class="dataTables_length" id="DataTables_Table_0_length"><label><select name="DataTables_Table_0_length" aria-controls="DataTables_Table_0" class="form-control input-sm"><option value="10">10</option><option value="25">25</option><option value="50">50</option><option value="100">100</option><option value="-1">All</option></select></label></div><div class="dt-buttons btn-group"><a class="btn btn-default buttons-collection btn-default-dt-options" tabindex="0" aria-controls="DataTables_Table_0" href="#"><span>Export</span></a><a class="btn btn-default btn-default-dt-options btn-dt-reload" tabindex="0" aria-controls="DataTables_Table_0" href="#" data-toggle="tooltip" title="" data-original-title="Reload"><span><i class="fa fa-refresh"></i></span></a></div></div><div class="col-md-5"><div id="DataTables_Table_0_filter" class="dataTables_filter"><label><div class="input-group"><span class="input-group-addon"><span class="fa fa-search"></span></span><input type="search" class="form-control input-sm" placeholder="Search..." aria-controls="DataTables_Table_0"></div></label></div></div></div>
								<div id="DataTables_Table_0_processing" class="dataTables_processing panel panel-default" style="display: none;"><div class="dt-loader"></div></div> */ ?>
								<table data-last-order-identifier="proposals" data-default-order="" class="table table-proposals dataTable no-footer dtr-inline collapsed" id="DataTables_Table_0" role="grid" aria-describedby="DataTables_Table_0_info">
									<thead>
										<tr role="row">
											<th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Proposal # activate to sort column ascending">#</th>
											<th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Subject activate to sort column ascending">Date</th>
											<th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="To activate to sort column ascending">Status</th>
											<th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Total activate to sort column ascending">Delivery Reference No</th>
											<th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="IGST activate to sort column ascending">Delivered By</th>
											<th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="CGST activate to sort column ascending">Sale Reference No</th>
											<th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="SGST activate to sort column ascending">Received By</th>
											<th class="sorting_desc" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-sort="descending" aria-label="Date activate to sort column ascending">Customer</th>
											<th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Open Till activate to sort column ascending">Note</th>
											<th class="sorting" tabindex="0" aria-controls="DataTables_Table_0" rowspan="1" colspan="1" aria-label="Tags activate to sort column ascending">Address</th>
											
										</tr>
											</thead>
									<tbody>
										<?php
											if(isset($delivery_modules) && !empty($delivery_modules) ) {
												$i = 1;
												foreach($delivery_modules as $delivery_module) {
													?>
														<tr role="row" class="odd">
															<td>
																<?php echo $i; ?>
															</td>
															<td>
																<?php echo $delivery_module['date']; ?>
															</td>
															
															<td>
																<?php echo $delivery_module['dstatusname']; ?>
															</td>
															<td>
																<?php echo $delivery_module['delivery_reference_no']; ?>
															</td>
															
															<td>
																<?php echo $delivery_module['firstname'] .' '. $delivery_module['lastname']; ?>
															</td>
															
															<td>
																<a href="<?php echo admin_url('/delivery_challan/list_invoices/'.$delivery_module['invoice_id']);?>" target="_blank"><?php echo $delivery_module['sale_reference_no']; ?></a>
															</td>
															
															<td>
																<?php echo $delivery_module['received_by']; ?>
															</td>
															
															<td>
																<?php echo $delivery_module['company']; ?>
															</td>
															
															<td>
																<?php echo $delivery_module['note']; ?>
															</td>
															
															<td>
																<?php echo $delivery_module['delivery_module_address']; ?>
															</td>
															
														</tr>	
													<?php
												$i++;
												}
											}
										?>
										
										</tbody></table>
										<?php /* <div class="row"><div class="col-md-4"><div class="dataTables_info" id="DataTables_Table_0_info" role="status" aria-live="polite">Showing 1 to 13 of 13 entries</div></div></div><div class="row"><div id="colvis"></div><div id="" class="dt-page-jump"></div><div class="dataTables_paginate paging_simple_numbers" id="DataTables_Table_0_paginate"><ul class="pagination"><li class="paginate_button previous disabled" id="DataTables_Table_0_previous"><a href="#" aria-controls="DataTables_Table_0" data-dt-idx="0" tabindex="0">Previous</a></li><li class="paginate_button active"><a href="#" aria-controls="DataTables_Table_0" data-dt-idx="1" tabindex="0">1</a></li><li class="paginate_button next disabled" id="DataTables_Table_0_next"><a href="#" aria-controls="DataTables_Table_0" data-dt-idx="2" tabindex="0">Next</a></li></ul></div></div> */ ?>
										</div>
										</div>                    
										</div>
                  </div>
               </div>
               <div class="col-md-7 small-table-right-col">
                  <div id="proposal" class="hide">
                  </div>
               </div>
            </div>
         </div>
      </div>
      <div class="btn-bottom-pusher"></div>
   </div>
</div>
<?php //$this->load->view('admin/expenses/expense_category'); ?>
<?php init_tail(); ?>

</body>
</html>
