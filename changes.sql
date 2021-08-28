--

CREATE TABLE IF NOT EXISTS `tblserviceinvoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `datesend` datetime DEFAULT NULL,
  `clientid` int(11) NOT NULL,
  `deleted_customer_name` varchar(100) DEFAULT NULL,
  `number` int(11) NOT NULL,
  `prefix` varchar(50) DEFAULT NULL,
  `number_format` int(11) NOT NULL DEFAULT 0,
  `datecreated` datetime NOT NULL,
  `date` date NOT NULL,
  `duedate` date DEFAULT NULL,
  `currency` int(11) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `total_tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL,
  `adjustment` decimal(15,2) DEFAULT NULL,
  `addedfrom` int(11) DEFAULT NULL,
  `hash` varchar(32) NOT NULL,
  `status` int(11) DEFAULT 1,
  `clientnote` text DEFAULT NULL,
  `adminnote` text DEFAULT NULL,
  `last_overdue_reminder` date DEFAULT NULL,
  `cancel_overdue_reminders` int(11) NOT NULL DEFAULT 0,
  `allowed_payment_modes` mediumtext DEFAULT NULL,
  `token` mediumtext DEFAULT NULL,
  `discount_percent` decimal(15,2) DEFAULT 0.00,
  `discount_total` decimal(15,2) DEFAULT 0.00,
  `discount_type` varchar(30) NOT NULL,
  `recurring` int(11) NOT NULL DEFAULT 0,
  `recurring_type` varchar(10) DEFAULT NULL,
  `custom_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `cycles` int(11) NOT NULL DEFAULT 0,
  `total_cycles` int(11) NOT NULL DEFAULT 0,
  `is_recurring_from` int(11) DEFAULT NULL,
  `last_recurring_date` date DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `sale_agent` int(11) NOT NULL DEFAULT 0,
  `billing_street` varchar(200) DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_state` varchar(100) DEFAULT NULL,
  `billing_zip` varchar(100) DEFAULT NULL,
  `billing_country` int(11) DEFAULT NULL,
  `shipping_street` varchar(200) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(100) DEFAULT NULL,
  `shipping_country` int(11) DEFAULT NULL,
  `include_shipping` tinyint(1) NOT NULL,
  `show_shipping_on_invoice` tinyint(1) NOT NULL DEFAULT 1,
  `show_quantity_as` int(11) NOT NULL DEFAULT 1,
  `project_id` int(11) DEFAULT 0,
  `subscription_id` int(11) NOT NULL DEFAULT 0,
  `transportation` decimal(15,2) DEFAULT 0.00,
  `servicecharge` decimal(15,2) DEFAULT 0.00,
  `service_charge_tax_rate` decimal(15,2) NOT NULL,
  `packing_and_forwarding` decimal(15,2) DEFAULT 0.00,
  `devide_gst` tinyint(2) NOT NULL DEFAULT 0,
  `is_bulk` int(11) NOT NULL COMMENT '0-show, 1-hide	',
  PRIMARY KEY (`id`),
  KEY `currency` (`currency`),
  KEY `clientid` (`clientid`),
  KEY `project_id` (`project_id`),
  KEY `sale_agent` (`sale_agent`),
  KEY `total` (`total`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `tblinvoiceservicepaymentrecords`;
CREATE TABLE IF NOT EXISTS `tblinvoiceservicepaymentrecords` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoiceid` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paymentmode` varchar(40) DEFAULT NULL,
  `paymentmethod` varchar(200) DEFAULT NULL,
  `date` date NOT NULL,
  `daterecorded` datetime NOT NULL,
  `note` text NOT NULL,
  `transactionid` mediumtext DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `invoiceid` (`invoiceid`),
  KEY `paymentmethod` (`paymentmethod`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;



-- --------------------------------------------------------

--
-- Table structure for table `tblrequsiteform`
--

DROP TABLE IF EXISTS `tblrequsiteform`;
CREATE TABLE IF NOT EXISTS `tblrequsiteform` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `datesend` datetime DEFAULT NULL,
  `clientid` int(11) NOT NULL,
  `deleted_customer_name` varchar(100) DEFAULT NULL,
  `project_id` int(11) NOT NULL DEFAULT 0,
  `number` int(11) NOT NULL,
  `prefix` varchar(50) DEFAULT NULL,
  `number_format` int(11) NOT NULL DEFAULT 0,
  `hash` varchar(32) DEFAULT NULL,
  `datecreated` datetime NOT NULL,
  `date` date NOT NULL,
  `expirydate` date DEFAULT NULL,
  `currency` int(11) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `total_tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL,
  `adjustment` decimal(15,2) DEFAULT NULL,
  `addedfrom` int(11) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `clientnote` text DEFAULT NULL,
  `adminnote` text DEFAULT NULL,
  `discount_percent` decimal(15,2) DEFAULT 0.00,
  `discount_total` decimal(15,2) DEFAULT 0.00,
  `discount_type` varchar(30) DEFAULT NULL,
  `invoiceid` int(11) DEFAULT NULL,
  `invoiced_date` datetime DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `reference_no` varchar(100) DEFAULT NULL,
  `sale_agent` int(11) NOT NULL DEFAULT 0,
  `billing_street` varchar(200) DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_state` varchar(100) DEFAULT NULL,
  `billing_zip` varchar(100) DEFAULT NULL,
  `billing_country` int(11) DEFAULT NULL,
  `shipping_street` varchar(200) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(100) DEFAULT NULL,
  `shipping_country` int(11) DEFAULT NULL,
  `include_shipping` tinyint(1) NOT NULL,
  `show_shipping_on_estimate` tinyint(1) NOT NULL DEFAULT 1,
  `show_quantity_as` int(11) NOT NULL DEFAULT 1,
  `pipeline_order` int(11) NOT NULL DEFAULT 0,
  `is_expiry_notified` int(11) NOT NULL DEFAULT 0,
  `acceptance_firstname` varchar(50) DEFAULT NULL,
  `acceptance_lastname` varchar(50) DEFAULT NULL,
  `acceptance_email` varchar(100) DEFAULT NULL,
  `acceptance_date` datetime DEFAULT NULL,
  `acceptance_ip` varchar(40) DEFAULT NULL,
  `signature` varchar(40) DEFAULT NULL,
  `transportation` decimal(15,2) DEFAULT 0.00,
  `service_charge_tax_rate` decimal(15,2) DEFAULT NULL,
  `devide_gst` tinyint(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `clientid` (`clientid`),
  KEY `currency` (`currency`),
  KEY `project_id` (`project_id`),
  KEY `sale_agent` (`sale_agent`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tblrequsiteform_items`
--

DROP TABLE IF EXISTS `tblrequsiteform_items`;
CREATE TABLE IF NOT EXISTS `tblrequsiteform_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rel_id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `description` mediumtext NOT NULL,
  `long_description` mediumtext DEFAULT NULL,
  `qty` decimal(15,2) NOT NULL,
  `unit` varchar(255) NOT NULL,
  `rate` decimal(15,2) NOT NULL,
  `item_order` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id` (`id`),
  KEY `qty` (`qty`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;




ALTER TABLE `tblpurchases` ADD `prefix` VARCHAR(50) NOT NULL AFTER `devide_gst`, ADD `number` INT NOT NULL AFTER `prefix`, ADD `number_format` INT NOT NULL AFTER `number`, ADD `adminnote` TEXT NOT NULL AFTER `number_format`;

-- --------------------------------------------------------

--
-- Table structure for table `tblpurchaseitems`
--

DROP TABLE IF EXISTS `tblpurchaseitems`;
CREATE TABLE IF NOT EXISTS `tblpurchaseitems` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `purchase_id` int(11) DEFAULT NULL,
  `transfer_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL,
  `product_code` varchar(50) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `option_id` int(11) DEFAULT NULL,
  `net_unit_cost` decimal(25,4) NOT NULL,
  `quantity` decimal(15,4) NOT NULL,
  `warehouse_id` int(11) NOT NULL,
  `item_tax` decimal(25,4) DEFAULT NULL,
  `tax_rate_id` int(11) DEFAULT NULL,
  `tax` varchar(20) DEFAULT NULL,
  `discount` varchar(20) DEFAULT NULL,
  `item_discount` decimal(25,4) DEFAULT NULL,
  `expiry` date DEFAULT NULL,
  `subtotal` decimal(25,4) NOT NULL,
  `quantity_balance` decimal(15,4) DEFAULT 0.0000,
  `date` date NOT NULL,
  `status` varchar(50) NOT NULL,
  `unit_cost` decimal(25,4) DEFAULT NULL,
  `real_unit_cost` decimal(25,4) DEFAULT NULL,
  `quantity_received` decimal(15,4) DEFAULT NULL,
  `supplier_part_no` varchar(50) DEFAULT NULL,
  `purchase_item_id` int(11) DEFAULT NULL,
  `product_unit_id` int(11) DEFAULT NULL,
  `product_unit_code` varchar(10) DEFAULT NULL,
  `unit_quantity` decimal(15,4) NOT NULL,
  `gst` varchar(20) DEFAULT NULL,
  `cgst` decimal(25,4) DEFAULT NULL,
  `sgst` decimal(25,4) DEFAULT NULL,
  `igst` decimal(25,4) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_id` (`purchase_id`),
  KEY `product_id` (`product_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

ALTER TABLE `tblserviceinvoices` ADD `payment_concern_person` VARCHAR(255) NOT NULL AFTER `is_bulk`;
ALTER TABLE `tblpurchases` ADD `credit_period_days` VARCHAR(255) NOT NULL AFTER `adminnote`;

ALTER TABLE `tblstafftasks` ADD `is_requsite` TINYINT(4) NULL DEFAULT NULL AFTER `consumables`;

ALTER TABLE `tblstafftasks` ADD `requsite_form_id` INT NOT NULL DEFAULT '0' AFTER `is_requsite`, ADD `service_invoice_id` INT NOT NULL DEFAULT '0' AFTER `requsite_form_id`;
ALTER TABLE `tblpurchases` ADD `is_stock_in` TINYINT(2) NOT NULL DEFAULT '0' AFTER `credit_period_days`;
ALTER TABLE `tblinvoices` ADD `is_stock_out` TINYINT(4) NOT NULL DEFAULT '0' AFTER `is_bulk`;

--
-- Table structure for table `tblstock_transactions`
--

CREATE TABLE `tblstock_transactions` (
  `id` int(11) NOT NULL,
  `rel_type` varchar(50) NOT NULL,
  `rel_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `added_by` int(11) NOT NULL,
  `added_on` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblstock_transactions`
--
ALTER TABLE `tblstock_transactions`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblstock_transactions`
--
ALTER TABLE `tblstock_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tblitems` ADD `hold_stock` INT NOT NULL DEFAULT '0' AFTER `product_cost`;
ALTER TABLE `tblitems_in` ADD `hold_item` INT NOT NULL DEFAULT '0' AFTER `userId`;

ALTER TABLE `tblitems_in` ADD `converted_qty` INT NOT NULL DEFAULT '0' AFTER `hold_item`;

ALTER TABLE `tblinvoices` ADD `purchaseorder_id` INT NOT NULL DEFAULT '0' AFTER `is_stock_out`;



ALTER TABLE `tblitems_in` CHANGE `qty` `qty` INT(11) NOT NULL;

ALTER TABLE `tblpurchaseitems` CHANGE `quantity` `quantity` INT(11) NOT NULL, CHANGE `quantity_balance` `quantity_balance` INT(11) NULL DEFAULT '0.0000', CHANGE `quantity_received` `quantity_received` INT(11) NULL DEFAULT NULL, CHANGE `unit_quantity` `unit_quantity` INT(11) NOT NULL;

ALTER TABLE `tblitems`
  DROP `supplier1price`,
  DROP `supplier1_part_no`,
  DROP `supplier2price`,
  DROP `supplier2_part_no`,
  DROP `supplier3price`,
  DROP `supplier3_part_no`,
  DROP `supplier4price`,
  DROP `supplier4_part_no`,
  DROP `supplier5price`,
  DROP `supplier5_part_no`;

  ALTER TABLE `tblitems` CHANGE `quantity` `quantity` INT(11) NOT NULL;

  CREATE TABLE `tblservicepi` (
  `id` int(11) NOT NULL,
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  `datesend` datetime DEFAULT NULL,
  `clientid` int(11) NOT NULL,
  `deleted_customer_name` varchar(100) DEFAULT NULL,
  `number` int(11) NOT NULL,
  `prefix` varchar(50) DEFAULT NULL,
  `number_format` int(11) NOT NULL DEFAULT '0',
  `datecreated` datetime NOT NULL,
  `date` date NOT NULL,
  `duedate` date DEFAULT NULL,
  `currency` int(11) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `total_tax` decimal(15,2) NOT NULL DEFAULT '0.00',
  `total` decimal(15,2) NOT NULL,
  `adjustment` decimal(15,2) DEFAULT NULL,
  `addedfrom` int(11) DEFAULT NULL,
  `hash` varchar(32) NOT NULL,
  `status` int(11) DEFAULT '1',
  `clientnote` text,
  `adminnote` text,
  `last_overdue_reminder` date DEFAULT NULL,
  `cancel_overdue_reminders` int(11) NOT NULL DEFAULT '0',
  `allowed_payment_modes` mediumtext,
  `token` mediumtext,
  `discount_percent` decimal(15,2) DEFAULT '0.00',
  `discount_total` decimal(15,2) DEFAULT '0.00',
  `discount_type` varchar(30) NOT NULL,
  `recurring` int(11) NOT NULL DEFAULT '0',
  `recurring_type` varchar(10) DEFAULT NULL,
  `custom_recurring` tinyint(1) NOT NULL DEFAULT '0',
  `cycles` int(11) NOT NULL DEFAULT '0',
  `total_cycles` int(11) NOT NULL DEFAULT '0',
  `is_recurring_from` int(11) DEFAULT NULL,
  `last_recurring_date` date DEFAULT NULL,
  `terms` text,
  `sale_agent` int(11) NOT NULL DEFAULT '0',
  `billing_street` varchar(200) DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_state` varchar(100) DEFAULT NULL,
  `billing_zip` varchar(100) DEFAULT NULL,
  `billing_country` int(11) DEFAULT NULL,
  `shipping_street` varchar(200) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(100) DEFAULT NULL,
  `shipping_country` int(11) DEFAULT NULL,
  `include_shipping` tinyint(1) NOT NULL,
  `show_shipping_on_invoice` tinyint(1) NOT NULL DEFAULT '1',
  `show_quantity_as` int(11) NOT NULL DEFAULT '1',
  `project_id` int(11) DEFAULT '0',
  `subscription_id` int(11) NOT NULL DEFAULT '0',
  `transportation` decimal(15,2) DEFAULT '0.00',
  `servicecharge` decimal(15,2) DEFAULT '0.00',
  `service_charge_tax_rate` decimal(15,2) NOT NULL,
  `packing_and_forwarding` decimal(15,2) DEFAULT '0.00',
  `devide_gst` tinyint(2) NOT NULL DEFAULT '0',
  `is_bulk` int(11) NOT NULL COMMENT '0-show, 1-hide',
  `payment_concern_person` varchar(255) NOT NULL,
  `invoiced_date` int(11) DEFAULT NULL,
  `invoiceid` int(11) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

ALTER TABLE `tblservicepi` ADD `invoiced_date` INT NULL DEFAULT NULL AFTER `payment_concern_person`, ADD `invoiceid` INT NULL DEFAULT NULL AFTER `invoiced_date`;

ALTER TABLE `tblpurchaseitems`
  DROP `item_tax`,
  DROP `tax_rate_id`,
  DROP `tax`,
  DROP `discount`,
  DROP `item_discount`,
  DROP `expiry`,
  DROP `supplier_part_no`,
  DROP `purchase_item_id`,
  DROP `gst`,
  DROP `cgst`,
  DROP `sgst`,
  DROP `igst`;


  -- --------------------------------------------------------

--
-- Table structure for table `tblhold_items`
--

CREATE TABLE `tblhold_items` (
  `id` int(11) NOT NULL,
  `rel_type` varchar(50) NOT NULL,
  `rel_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `qty` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for table `tblhold_items`
--
ALTER TABLE `tblhold_items`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for table `tblhold_items`
--
ALTER TABLE `tblhold_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;


  ALTER TABLE `tblinvoices` ADD `payment_concern_person` VARCHAR(255) NOT NULL AFTER `purchaseorder_id`;


INSERT INTO `tblpermissions` (`permissionid`, `name`, `shortname`) VALUES (NULL, 'Quantity Adjustments', 'quantity_adjustments'), (NULL, 'Stock Counts', 'stock_counts'), (NULL, 'Purchases', 'purchases'), (NULL, 'Stock In', 'stock_in'), (NULL, 'Stock Out', 'stock_out'), (NULL, 'Purchaseorder', 'purchaseorder'), (NULL, 'Service Pi', 'service_pi'), (NULL, 'Service Invoices', 'service_invoices');

INSERT INTO `tblpermissions` (`permissionid`, `name`, `shortname`) VALUES (NULL, 'My Products', 'my_products'), (NULL, 'Suppliers', 'suppliers'), (NULL, 'Warehouses', 'warehouses'), (NULL, 'Print Barcode/Label', 'print_barcode'), (NULL, 'Requisite Forms', 'requisite_forms');



CREATE TABLE `tbldelivery_challans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sent` tinyint(1) NOT NULL DEFAULT 0,
  `datesend` datetime DEFAULT NULL,
  `clientid` int(11) NOT NULL,
  `deleted_customer_name` varchar(100) DEFAULT NULL,
  `number` int(11) NOT NULL,
  `prefix` varchar(50) DEFAULT NULL,
  `number_format` int(11) NOT NULL DEFAULT 0,
  `datecreated` datetime NOT NULL,
  `date` date NOT NULL,
  `duedate` date DEFAULT NULL,
  `currency` int(11) NOT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `total_tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `total` decimal(15,2) NOT NULL,
  `adjustment` decimal(15,2) DEFAULT NULL,
  `addedfrom` int(11) DEFAULT NULL,
  `hash` varchar(32) NOT NULL,
  `status` int(11) DEFAULT 1,
  `clientnote` text DEFAULT NULL,
  `adminnote` text DEFAULT NULL,
  `last_overdue_reminder` date DEFAULT NULL,
  `cancel_overdue_reminders` int(11) NOT NULL DEFAULT 0,
  `allowed_payment_modes` mediumtext DEFAULT NULL,
  `token` mediumtext DEFAULT NULL,
  `discount_percent` decimal(15,2) DEFAULT 0.00,
  `discount_total` decimal(15,2) DEFAULT 0.00,
  `discount_type` varchar(30) NOT NULL,
  `recurring` int(11) NOT NULL DEFAULT 0,
  `recurring_type` varchar(10) DEFAULT NULL,
  `custom_recurring` tinyint(1) NOT NULL DEFAULT 0,
  `cycles` int(11) NOT NULL DEFAULT 0,
  `total_cycles` int(11) NOT NULL DEFAULT 0,
  `is_recurring_from` int(11) DEFAULT NULL,
  `last_recurring_date` date DEFAULT NULL,
  `terms` text DEFAULT NULL,
  `sale_agent` int(11) NOT NULL DEFAULT 0,
  `billing_street` varchar(200) DEFAULT NULL,
  `billing_city` varchar(100) DEFAULT NULL,
  `billing_state` varchar(100) DEFAULT NULL,
  `billing_zip` varchar(100) DEFAULT NULL,
  `billing_country` int(11) DEFAULT NULL,
  `shipping_street` varchar(200) DEFAULT NULL,
  `shipping_city` varchar(100) DEFAULT NULL,
  `shipping_state` varchar(100) DEFAULT NULL,
  `shipping_zip` varchar(100) DEFAULT NULL,
  `shipping_country` int(11) DEFAULT NULL,
  `include_shipping` tinyint(1) NOT NULL,
  `show_shipping_on_invoice` tinyint(1) NOT NULL DEFAULT 1,
  `show_quantity_as` int(11) NOT NULL DEFAULT 1,
  `project_id` int(11) DEFAULT 0,
  `subscription_id` int(11) NOT NULL DEFAULT 0,
  `transportation` decimal(15,2) DEFAULT 0.00,
  `servicecharge` decimal(15,2) DEFAULT 0.00,
  `service_charge_tax_rate` decimal(15,2) NOT NULL,
  `packing_and_forwarding` decimal(15,2) DEFAULT 0.00,
  `devide_gst` tinyint(2) NOT NULL DEFAULT 0,
  `is_bulk` int(11) NOT NULL COMMENT '0-show, 1-hide	',
  `is_stock_out` tinyint(4) NOT NULL DEFAULT 0,
  `purchaseorder_id` int(11) NOT NULL DEFAULT 0,
  `payment_concern_person` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `currency` (`currency`),
  KEY `clientid` (`clientid`),
  KEY `project_id` (`project_id`),
  KEY `sale_agent` (`sale_agent`),
  KEY `total` (`total`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `tblpermissions` (`permissionid`, `name`, `shortname`) VALUES (NULL, 'Delivery challan', 'delivery_challan');

ALTER TABLE `tblrequsiteform` ADD `division_id` INT NOT NULL AFTER `clientid`;
ALTER TABLE `tblestimates` ADD `division_id` INT NOT NULL AFTER `clientid`;
ALTER TABLE `tblinvoices` ADD `division_id` INT NOT NULL AFTER `clientid`;
ALTER TABLE `tblpurchaseorder` ADD `division_id` INT NOT NULL AFTER `clientid`;
ALTER TABLE `tblservicepi` ADD `division_id` INT NOT NULL AFTER `clientid`;
ALTER TABLE `tblserviceinvoices` ADD `division_id` INT NOT NULL AFTER `clientid`;


DROP TABLE IF EXISTS `tblinquiries`;
CREATE TABLE `tblinquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subject` varchar(500) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `addedfrom` int(11) NOT NULL,
  `datecreated` datetime NOT NULL,
  `total` decimal(15,2) DEFAULT NULL,
  `subtotal` decimal(15,2) NOT NULL,
  `total_tax` decimal(15,2) NOT NULL DEFAULT 0.00,
  `adjustment` decimal(15,2) DEFAULT NULL,
  `discount_percent` decimal(15,2) NOT NULL,
  `discount_total` decimal(15,2) NOT NULL,
  `discount_type` varchar(30) DEFAULT NULL,
  `show_quantity_as` int(11) NOT NULL DEFAULT 1,
  `currency` int(11) NOT NULL,
  `open_till` date DEFAULT NULL,
  `date` date NOT NULL,
  `rel_id` int(11) DEFAULT NULL,
  `rel_type` varchar(40) DEFAULT NULL,
  `assigned` int(11) DEFAULT NULL,
  `hash` varchar(32) NOT NULL,
  `proposal_to` varchar(600) DEFAULT NULL,
  `country` int(11) NOT NULL DEFAULT 0,
  `zip` varchar(50) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `allow_comments` tinyint(1) NOT NULL DEFAULT 1,
  `status` int(11) NOT NULL,
  `lead_status` int(11) NOT NULL,
  `lead_source` int(11) NOT NULL,
  `estimate_id` int(11) DEFAULT NULL,
  `invoice_id` int(11) DEFAULT NULL,
  `date_converted` datetime DEFAULT NULL,
  `pipeline_order` int(11) NOT NULL DEFAULT 0,
  `is_expiry_notified` int(11) NOT NULL DEFAULT 0,
  `acceptance_firstname` varchar(50) DEFAULT NULL,
  `acceptance_lastname` varchar(50) DEFAULT NULL,
  `acceptance_email` varchar(100) DEFAULT NULL,
  `acceptance_date` datetime DEFAULT NULL,
  `acceptance_ip` varchar(40) DEFAULT NULL,
  `signature` varchar(40) DEFAULT NULL,
  `transportation` decimal(15,2) DEFAULT 0.00,
  `servicecharge` decimal(15,2) DEFAULT 0.00,
  `service_charge_tax_rate` decimal(15,2) DEFAULT NULL,
  `packing_and_forwarding` decimal(15,2) DEFAULT 0.00,
  `devide_gst` tinyint(2) NOT NULL DEFAULT 0,
  `is_bulk` int(11) NOT NULL COMMENT '0-show, 1-hide',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


DROP TABLE IF EXISTS `tblitems_in_div_con`;
CREATE TABLE `tblitems_in_div_con` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rel_id` int(11) NOT NULL,
  `rel_type` varchar(15) NOT NULL,
  `division_id` int(11) NOT NULL DEFAULT 0,
  `contact_id` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `rel_id` (`rel_id`),
  KEY `rel_type` (`rel_type`),
  KEY `id` (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;


ALTER TABLE `tblestimates` ADD `is_lost_invoice` TINYINT(1) NOT NULL DEFAULT '0' AFTER `date_converted`;





// lead changes

ALTER TABLE `tblleads`  ADD `cluster` VARCHAR(100) NOT NULL  AFTER `client_id`,  ADD `customer_classification` VARCHAR(100) NOT NULL  AFTER `cluster`,  ADD `type_of_customer` VARCHAR(100) NOT NULL  AFTER `customer_classification`,  ADD `industry` VARCHAR(100) NOT NULL  AFTER `type_of_customer`,  ADD `accessories` VARCHAR(255) NOT NULL  AFTER `industry`,  ADD `month_of_lead_closer` VARCHAR(255) NOT NULL  AFTER `accessories`,  ADD `existing_model_model` VARCHAR(255) NOT NULL  AFTER `month_of_lead_closer`,  ADD `existing_model_running_hrs` VARCHAR(255) NOT NULL  AFTER `existing_model_model`,  ADD `existing_model_loading_hrs` VARCHAR(255) NOT NULL  AFTER `existing_model_running_hrs`,  ADD `existing_model_amc_cost` VARCHAR(255) NOT NULL  AFTER `existing_model_loading_hrs`,  ADD `companies_competing` VARCHAR(500) NOT NULL  AFTER `existing_model_amc_cost`,  ADD `date` DATE NULL DEFAULT NULL  AFTER `companies_competing`,  ADD `cfm_required` TINYINT NOT NULL DEFAULT '0'  AFTER `date`,  ADD `pressure_required` TINYINT NOT NULL DEFAULT '0'  AFTER `cfm_required`,  ADD `machine_required` TINYINT NOT NULL DEFAULT '0'  AFTER `pressure_required`,  ADD `buy_back_potential` TINYINT NOT NULL DEFAULT '0'  AFTER `machine_required`,  ADD `buy_back_proposal` TINYINT NOT NULL DEFAULT '0'  AFTER `buy_back_potential`,  ADD `existing_model_oil_type` TINYINT NOT NULL DEFAULT '0'  AFTER `buy_back_proposal`,  ADD `consumable` TEXT NOT NULL  AFTER `existing_model_oil_type`,  ADD `existing_model_company` VARCHAR(255) NOT NULL  AFTER `consumable`;

ALTER TABLE `tblleads` CHANGE `cfm_required` `cfm_required` VARCHAR(255) NULL DEFAULT NULL, CHANGE `pressure_required` `pressure_required` VARCHAR(255) NULL DEFAULT NULL, CHANGE `machine_required` `machine_required` VARCHAR(255) NULL DEFAULT NULL;

ALTER TABLE `tblinquiries` ADD `reference_no` VARCHAR(100) NULL DEFAULT NULL AFTER `datecreated`, ADD `adminnote` TEXT NULL DEFAULT NULL AFTER `reference_no`;
ALTER TABLE `tblinquiries` ADD `clientnote` TEXT NULL DEFAULT NULL AFTER `adminnote`, ADD `terms` TEXT NULL DEFAULT NULL AFTER `clientnote`;
ALTER TABLE `tblinquiries` ADD `modifieddate` DATETIME NULL DEFAULT NULL  AFTER `clientnote`;
ALTER TABLE `tblinquiries` ADD `is_rfr` tinyint(1) NULL DEFAULT NULL AFTER `datecreated`;


ALTER TABLE `tblitems` ADD `warranty` INT NOT NULL DEFAULT '12' AFTER `unit`, ADD `upto` DATE NULL DEFAULT NULL AFTER `warranty`;

CREATE TABLE `tblrfr` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `mailto` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `staffid` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


ALTER TABLE `tblleads` CHANGE `month_of_lead_closer` `month_of_lead_closer` DATE NULL DEFAULT NULL;

ALTER TABLE `tblestimates` ADD `is_lost_invoice` TINYINT(1) NOT NULL DEFAULT '0' AFTER `datecreated`;




CREATE TABLE `tbltenders` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`subject` VARCHAR(500) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`content` LONGTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`addedfrom` INT(11) NOT NULL,
	`datecreated` DATETIME NOT NULL,
	`reference_no` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`adminnote` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`clientnote` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`modifieddate` DATETIME NULL DEFAULT NULL,
	`terms` TEXT(65535) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`total` DECIMAL(15,2) NULL DEFAULT NULL,
	`subtotal` DECIMAL(15,2) NOT NULL,
	`total_tax` DECIMAL(15,2) NOT NULL DEFAULT '0.00',
	`adjustment` DECIMAL(15,2) NULL DEFAULT NULL,
	`discount_percent` DECIMAL(15,2) NOT NULL,
	`discount_total` DECIMAL(15,2) NOT NULL,
	`discount_type` VARCHAR(30) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`show_quantity_as` INT(11) NOT NULL DEFAULT '1',
	`currency` INT(11) NOT NULL,
	`open_till` DATE NULL DEFAULT NULL,
	`date` DATE NOT NULL,
	`rel_id` INT(11) NULL DEFAULT NULL,
	`rel_type` VARCHAR(40) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`assigned` INT(11) NULL DEFAULT NULL,
	`hash` VARCHAR(32) NOT NULL COLLATE 'utf8_general_ci',
	`proposal_to` VARCHAR(600) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`country` INT(11) NOT NULL DEFAULT '0',
	`zip` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`state` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`city` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`address` VARCHAR(200) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`email` VARCHAR(150) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`phone` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`allow_comments` TINYINT(1) NOT NULL DEFAULT '1',
	`status` INT(11) NOT NULL,
	`lead_status` VARCHAR(255) NOT NULL COLLATE 'utf8_general_ci',
	`lead_source` VARCHAR(255) NOT NULL COLLATE 'utf8_general_ci',
	`estimate_id` INT(11) NULL DEFAULT NULL,
	`invoice_id` INT(11) NULL DEFAULT NULL,
	`date_converted` DATETIME NULL DEFAULT NULL,
	`is_lost_invoice` TINYINT(1) NOT NULL DEFAULT '0',
	`pipeline_order` INT(11) NOT NULL DEFAULT '0',
	`is_expiry_notified` INT(11) NOT NULL DEFAULT '0',
	`acceptance_firstname` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`acceptance_lastname` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`acceptance_email` VARCHAR(100) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`acceptance_date` DATETIME NULL DEFAULT NULL,
	`acceptance_ip` VARCHAR(40) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`signature` VARCHAR(40) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`transportation` DECIMAL(15,2) NULL DEFAULT '0.00',
	`servicecharge` DECIMAL(15,2) NULL DEFAULT '0.00',
	`service_charge_tax_rate` DECIMAL(15,2) NULL DEFAULT NULL,
	`packing_and_forwarding` DECIMAL(15,2) NULL DEFAULT '0.00',
	`devide_gst` TINYINT(2) NOT NULL DEFAULT '0',
	`is_bulk` INT(11) NOT NULL COMMENT '0-show, 1-hide',
	`tender_number` VARCHAR(100) NULL DEFAULT NULL,
	`tender_fees` DOUBLE(11,2) NULL DEFAULT NULL,
	`master_files` TEXT NULL DEFAULT NULL,
	`tender_title` VARCHAR(255) NULL DEFAULT NULL,
	PRIMARY KEY (`id`) USING BTREE
)
COLLATE='utf8_general_ci'
ENGINE=MyISAM
;



ALTER TABLE `tbltenders` ADD `tender_emd` DECIMAL(15,2) NOT NULL AFTER `tender_title`;


-- 9-11-2020

ALTER TABLE `tblpurchaseorder` ADD `estimate_id` INT NULL DEFAULT NULL AFTER `itemid`, ADD `inquiry_id` INT NULL DEFAULT NULL AFTER `estimate_id`;
ALTER TABLE `tblinquiries` CHANGE `lead_status` `lead_status` VARCHAR(255) NOT NULL, CHANGE `lead_source` `lead_source` VARCHAR(255) NOT NULL;

ALTER TABLE `tblinquiries` ADD `follow_up_date` DATE NULL DEFAULT NULL AFTER `open_till`;


INSERT INTO `tblpermissions` (`permissionid`, `name`, `shortname`) VALUES (NULL, 'Tenders', 'tenders');

UPDATE `tbloptions` SET `value` = '{\"aside_menu_active\":[{\"name\":\"als_dashboard\",\"url\":\"/\",\"permission\":\"\",\"icon\":\"fa fa-tachometer\",\"id\":\"dashboard\"},{\"name\":\"als_leads\",\"url\":\"leads\",\"permission\":\"is_staff_member\",\"icon\":\"fa fa-tty\",\"id\":\"leads\"},{\"name\":\"als_clients\",\"url\":\"clients\",\"permission\":\"customers\",\"icon\":\"fa fa-user-o\",\"id\":\"customers\"},{\"name\":\"als_suppliers\",\"url\":\"suppliers\",\"permission\":\"suppliers\",\"icon\":\"fa fa-building-o\",\"id\":\"suppliers\"},{\"name\":\"Stock\",\"url\":\"#\",\"permission\":\"\",\"icon\":\"fa fa-product-hunt\",\"id\":\"item\",\"children\":[{\"name\":\"items\",\"url\":\"invoice_items\",\"permission\":\"items\",\"icon\":\"\",\"id\":\"child-items\"},{\"name\":\"print_barcode\",\"url\":\"invoice_items/print_barcodes\",\"permission\":\"print_barcode\",\"icon\":\"\",\"id\":\"child-print_barcode\"},{\"name\":\"quantity_adjustment\",\"url\":\"invoice_items/quantity_adjustments\",\"permission\":\"quantity_adjustments\",\"icon\":\"\",\"id\":\"child-quantity_adjustment\"},{\"name\":\"stock_count\",\"url\":\"invoice_items/stock_counts\",\"permission\":\"stock_counts\",\"icon\":\"\",\"id\":\"child-stock_count\"},{\"name\":\"Purchases\",\"url\":\"purchases\",\"permission\":\"purchases\",\"icon\":\"\",\"id\":\"child-purchase\"},{\"name\":\"Stock In\",\"url\":\"purchases/stock_in_list\",\"permission\":\"stock_in\",\"icon\":\"\",\"id\":\"child-stock_in_list\"},{\"name\":\"Stock Out\",\"url\":\"invoices/stock_out_list\",\"permission\":\"stock_out\",\"icon\":\"\",\"id\":\"child-stock_out_list\"},{\"name\":\"Delivery Challan\",\"url\":\"delivery_challan\",\"permission\":\"delivery_challan\",\"icon\":\"\",\"id\":\"child-delivery_challan\"},{\"name\":\"DC Stock Out\",\"url\":\"delivery_challan/stock_out_list\",\"permission\":\"stock_out\",\"icon\":\"\",\"id\":\"child-stock_out_list\"}]},{\"name\":\"als_sales\",\"url\":\"#\",\"permission\":\"\",\"icon\":\"fa fa-balance-scale\",\"id\":\"sales\",\"children\":[{\"name\":\"Tenders\",\"url\":\"tenders\",\"permission\":\"tenders\",\"icon\":\"\",\"id\":\"child-tenders\"},{\"name\":\"Inquiries\",\"url\":\"inquiries\",\"permission\":\"proposals\",\"icon\":\"\",\"id\":\"child-inquiries\"},{\"name\":\"proposals\",\"url\":\"proposals\",\"permission\":\"proposals\",\"icon\":\"\",\"id\":\"child-proposals\"},{\"name\":\"Requsite Forms\",\"url\":\"requsite_forms\",\"permission\":\"requisite_forms\",\"icon\":\"\",\"id\":\"child-requsite_forms\"},{\"name\":\"estimates\",\"url\":\"estimates/list_estimates\",\"permission\":\"estimates\",\"icon\":\"\",\"id\":\"child-estimates\"},{\"name\":\"invoices\",\"url\":\"invoices/list_invoices\",\"permission\":\"invoices\",\"icon\":\"\",\"id\":\"child-invoices\"},{\"name\":\"Purchase Orders\",\"url\":\"purchaseorder\",\"permission\":\"purchaseorder\",\"icon\":\"\",\"id\":\"child-purchaseorder\"},{\"name\":\"payments\",\"url\":\"payments\",\"permission\":\"payments\",\"icon\":\"\",\"id\":\"child-payments\"},{\"name\":\"Deliveries\",\"url\":\"invoices/deliveries\",\"permission\":\"deliveries\",\"icon\":\"\",\"id\":\"child-deliveries\"},{\"name\":\"credit_notes\",\"url\":\"credit_notes\",\"permission\":\"credit_notes\",\"icon\":\"\",\"id\":\"credit_notes\"}]},{\"name\":\"Services\",\"url\":\"#\",\"permission\":\"\",\"icon\":\"fa fa-file\",\"id\":\"contracts\",\"children\":[{\"name\":\"als_contracts\",\"url\":\"contracts\",\"permission\":\"contracts\",\"icon\":\"\",\"id\":\"child-contracts\"},{\"name\":\"Service Tasks\",\"url\":\"tasks/list_service_tasks\",\"permission\":\"tasks\",\"icon\":\"\",\"id\":\"child-estimates\"},{\"name\":\"Service Pi\",\"url\":\"service_pi\",\"permission\":\"service_pi\",\"icon\":\"\",\"id\":\"child-service_pi\"},{\"name\":\"Service Invoice\",\"url\":\"service_invoices\",\"permission\":\"service_invoices\",\"icon\":\"\",\"id\":\"child-service_invoices\"},{\"name\":\"Service Payment\",\"url\":\"service_payments\",\"permission\":\"payments\",\"icon\":\"\",\"id\":\"child-service_payments\"}]},{\"name\":\"als_expenses\",\"url\":\"expenses/list_expenses\",\"permission\":\"expenses\",\"icon\":\"fa fa-file-text-o\",\"id\":\"expenses\"},{\"name\":\"als_tasks\",\"url\":\"tasks/list_tasks\",\"permission\":\"\",\"icon\":\"fa fa-tasks\",\"id\":\"tasks\"},{\"name\":\"als_kb\",\"url\":\"knowledge_base\",\"permission\":\"knowledge_base\",\"icon\":\"fa fa-folder-open-o\",\"id\":\"knowledge-base\"},{\"name\":\"als_utilities\",\"url\":\"#\",\"permission\":\"\",\"icon\":\"fa fa-cogs\",\"id\":\"utilities\",\"children\":[{\"name\":\"als_media\",\"url\":\"utilities/media\",\"permission\":\"\",\"icon\":\"\",\"id\":\"child-media\"},{\"name\":\"bulk_pdf_exporter\",\"url\":\"utilities/bulk_pdf_exporter\",\"permission\":\"bulk_pdf_exporter\",\"icon\":\"\",\"id\":\"child-bulk-pdf-exporter\"},{\"name\":\"als_calendar_submenu\",\"url\":\"utilities/calendar\",\"permission\":\"\",\"icon\":\"\",\"id\":\"child-calendar\"},{\"name\":\"als_goals_tracking\",\"url\":\"goals\",\"permission\":\"goals\",\"icon\":\"\",\"id\":\"child-goals-tracking\"},{\"name\":\"als_surveys\",\"url\":\"surveys\",\"permission\":\"surveys\",\"icon\":\"\",\"id\":\"child-surveys\"},{\"name\":\"als_announcements_submenu\",\"url\":\"announcements\",\"permission\":\"is_admin\",\"icon\":\"\",\"id\":\"child-announcements\"},{\"name\":\"utility_backup\",\"url\":\"utilities/backup\",\"permission\":\"is_admin\",\"icon\":\"\",\"id\":\"child-database-backup\"},{\"name\":\"als_activity_log_submenu\",\"url\":\"utilities/activity_log\",\"permission\":\"is_admin\",\"icon\":\"\",\"id\":\"child-activity-log\"},{\"name\":\"ticket_pipe_log\",\"url\":\"utilities/pipe_log\",\"permission\":\"is_admin\",\"icon\":\"\",\"id\":\"ticket-pipe-log\"}]},{\"name\":\"als_reports\",\"url\":\"#\",\"permission\":\"reports\",\"icon\":\"fa fa-area-chart\",\"id\":\"reports\",\"children\":[{\"name\":\"als_reports_sales_submenu\",\"url\":\"reports/sales\",\"permission\":\"\",\"icon\":\"\",\"id\":\"child-sales\"},{\"name\":\"Inquiries\",\"url\":\"reports/inquiries\",\"permission\":\"\",\"icon\":\"\",\"id\":\"child-inquiries_report\"},{\"name\":\"als_reports_expenses\",\"url\":\"reports/expenses\",\"permission\":\"\",\"icon\":\"\",\"id\":\"child-expenses\"},{\"name\":\"als_expenses_vs_income\",\"url\":\"reports/expenses_vs_income\",\"permission\":\"\",\"icon\":\"\",\"id\":\"child-expenses-vs-income\"},{\"name\":\"als_reports_leads_submenu\",\"url\":\"reports/leads\",\"permission\":\"\",\"icon\":\"\",\"id\":\"child-leads\"},{\"name\":\"timesheets_overview\",\"url\":\"staff/timesheets?view=all\",\"permission\":\"is_admin\",\"icon\":\"\",\"id\":\"reports_timesheets_overview\"},{\"name\":\"als_kb_articles_submenu\",\"url\":\"reports/knowledge_base_articles\",\"permission\":\"\",\"icon\":\"\",\"id\":\"child-kb-articles\"}]}]}' WHERE `tbloptions`.`name` LIKE 'aside_menu_active';
ALTER TABLE `tblestimates` ADD `follow_up_date` DATE NULL DEFAULT NULL AFTER `expirydate`;

ALTER TABLE `tblpurchases` ADD `expected_delivery_date` DATE NULL DEFAULT NULL AFTER `date`;
ALTER TABLE `tblpurchases` ADD `supply_date` DATE NULL DEFAULT NULL AFTER `expected_delivery_date`;
ALTER TABLE `tblpurchaseitems` ADD `item_discount` DATE NULL DEFAULT NULL AFTER `unit_cost`;


ALTER TABLE `tblitems_groups` ADD `parent_id` INT NOT NULL DEFAULT '0' AFTER `name`;

ALTER TABLE `tblitems_units` ADD `base_unit` INT NULL DEFAULT NULL AFTER `name`, ADD `operator` VARCHAR(1) NULL DEFAULT NULL AFTER `base_unit`, ADD `unit_value` VARCHAR(55) NULL DEFAULT NULL AFTER `operator`, ADD `operation_value` VARCHAR(55) NULL DEFAULT NULL AFTER `unit_value`;



CREATE TABLE `tblpurchaseorderpaymentrecords` (
	`id` INT(11) NOT NULL AUTO_INCREMENT,
	`purchaseorder_id` INT(11) NOT NULL,
	`amount` DECIMAL(15,2) NOT NULL,
	`paymentmode` VARCHAR(40) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`paymentmethod` VARCHAR(200) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`date` DATE NOT NULL,
	`daterecorded` DATETIME NOT NULL,
	`note` TEXT NOT NULL COLLATE 'utf8_general_ci',
	`transactionid` MEDIUMTEXT NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	PRIMARY KEY (`id`) USING BTREE,
	INDEX `purchaseorder_id` (`purchaseorder_id`) USING BTREE,
	INDEX `paymentmethod` (`paymentmethod`) USING BTREE
)

ALTER TABLE `tblcreditnotes`
	ADD COLUMN `packing_and_forwarding` DECIMAL(15,2) NULL DEFAULT '0.00' AFTER `discount_total`;

ALTER TABLE `tblcreditnotes`
	ADD COLUMN `devide_gst` TINYINT(1) ZEROFILL NOT NULL AFTER `packing_and_forwarding`;

ALTER TABLE `tblcreditnotes`
	ADD COLUMN `servicecharge` DECIMAL(15,2) NULL DEFAULT '0.00' AFTER `packing_and_forwarding`;


ALTER TABLE `tblclients` ADD `credit_facility` TINYINT(1) NULL DEFAULT '0' AFTER `discount`, ADD `credit_period` VARCHAR(255) NULL AFTER `credit_facility`, ADD `credit_amount` VARCHAR(255) NULL AFTER `credit_period`;

-- <<<<<<< changes.sql

-- //HARSH V1

ALTER TABLE `tblitems` ADD `rack_no` VARCHAR(50) NOT NULL AFTER `hold_stock`;
ALTER TABLE `tblitems` ADD `category` VARCHAR(254) NULL DEFAULT NULL AFTER `rack_no`, ADD `note` TEXT NOT NULL AFTER `category`;

-- //vijay Ghori 
-- //26/03/2021 manufacturing_item feilds at tblitems
ALTER TABLE `tblitems` ADD `manufacturing_item` VARCHAR(50) NULL DEFAULT 'false' AFTER `hold_stock`;

-- //vijay Ghori
-- //27/03/2021 Update side bar for add new Manufacturing and MFPurcheses options
UPDATE `tbloptions` SET `value`='{"aside_menu_active":[{"name":"als_dashboard","url":"/","permission":"","icon":"fa fa-tachometer","id":"dashboard"},{"name":"als_leads","url":"leads","permission":"is_staff_member","icon":"fa fa-tty","id":"leads"},{"name":"als_clients","url":"clients","permission":"customers","icon":"fa fa-user-o","id":"customers"},{"name":"als_suppliers","url":"suppliers","permission":"suppliers","icon":"fa fa-building-o","id":"suppliers"},{"name":"Stock","url":"#","permission":"","icon":"fa fa-product-hunt","id":"item","children":[{"name":"items","url":"invoice_items","permission":"items","icon":"","id":"child-items"},{"name":"print_barcode","url":"invoice_items/print_barcodes","permission":"print_barcode","icon":"","id":"child-print_barcode"},{"name":"quantity_adjustment","url":"invoice_items/quantity_adjustments","permission":"quantity_adjustments","icon":"","id":"child-quantity_adjustment"},{"name":"stock_count","url":"invoice_items/stock_counts","permission":"stock_counts","icon":"","id":"child-stock_count"},{"name":"Purchases","url":"purchases","permission":"purchases","icon":"","id":"child-purchase"},{"name":"Stock In","url":"purchases/stock_in_list","permission":"stock_in","icon":"","id":"child-stock_in_list"},{"name":"Stock Out","url":"invoices/stock_out_list","permission":"stock_out","icon":"","id":"child-stock_out_list"},{"name":"Delivery Challan","url":"delivery_challan","permission":"delivery_challan","icon":"","id":"child-delivery_challan"},{"name":"DC Stock Out","url":"delivery_challan/stock_out_list","permission":"stock_out","icon":"","id":"child-stock_out_list"}]},{"name":"Manufacturing","url":"#","permission":"","icon":"fa fa-industry","id":"manufacturing","children":[{"name":"MF Purchases","url":"mfpurchases","permission":"mfpurchases","icon":"","id":"child-mfpurchase"}]},{"name":"als_sales","url":"#","permission":"","icon":"fa fa-balance-scale","id":"sales","children":[{"name":"Tenders","url":"tenders","permission":"tenders","icon":"","id":"child-tenders"},{"name":"Inquiries","url":"inquiries","permission":"proposals","icon":"","id":"child-inquiries"},{"name":"proposals","url":"proposals","permission":"proposals","icon":"","id":"child-proposals"},{"name":"Requsite Forms","url":"requsite_forms","permission":"requisite_forms","icon":"","id":"child-requsite_forms"},{"name":"estimates","url":"estimates/list_estimates","permission":"estimates","icon":"","id":"child-estimates"},{"name":"invoices","url":"invoices/list_invoices","permission":"invoices","icon":"","id":"child-invoices"},{"name":"Purchase Orders","url":"purchaseorder","permission":"purchaseorder","icon":"","id":"child-purchaseorder"},{"name":"payments","url":"payments","permission":"payments","icon":"","id":"child-payments"},{"name":"Deliveries","url":"invoices/deliveries","permission":"deliveries","icon":"","id":"child-deliveries"},{"name":"credit_notes","url":"credit_notes","permission":"credit_notes","icon":"","id":"credit_notes"}]},{"name":"Services","url":"#","permission":"","icon":"fa fa-file","id":"contracts","children":[{"name":"als_contracts","url":"contracts","permission":"contracts","icon":"","id":"child-contracts"},{"name":"Service Tasks","url":"tasks/list_service_tasks","permission":"tasks","icon":"","id":"child-estimates"},{"name":"Service Pi","url":"service_pi","permission":"service_pi","icon":"","id":"child-service_pi"},{"name":"Service Invoice","url":"service_invoices","permission":"service_invoices","icon":"","id":"child-service_invoices"},{"name":"Service Payment","url":"service_payments","permission":"payments","icon":"","id":"child-service_payments"}]},{"name":"als_expenses","url":"expenses/list_expenses","permission":"expenses","icon":"fa fa-file-text-o","id":"expenses"},{"name":"als_tasks","url":"tasks/list_tasks","permission":"","icon":"fa fa-tasks","id":"tasks"},{"name":"als_kb","url":"knowledge_base","permission":"knowledge_base","icon":"fa fa-folder-open-o","id":"knowledge-base"},{"name":"als_utilities","url":"#","permission":"","icon":"fa fa-cogs","id":"utilities","children":[{"name":"als_media","url":"utilities/media","permission":"","icon":"","id":"child-media"},{"name":"bulk_pdf_exporter","url":"utilities/bulk_pdf_exporter","permission":"bulk_pdf_exporter","icon":"","id":"child-bulk-pdf-exporter"},{"name":"als_calendar_submenu","url":"utilities/calendar","permission":"","icon":"","id":"child-calendar"},{"name":"als_goals_tracking","url":"goals","permission":"goals","icon":"","id":"child-goals-tracking"},{"name":"als_surveys","url":"surveys","permission":"surveys","icon":"","id":"child-surveys"},{"name":"als_announcements_submenu","url":"announcements","permission":"is_admin","icon":"","id":"child-announcements"},{"name":"utility_backup","url":"utilities/backup","permission":"is_admin","icon":"","id":"child-database-backup"},{"name":"als_activity_log_submenu","url":"utilities/activity_log","permission":"is_admin","icon":"","id":"child-activity-log"},{"name":"ticket_pipe_log","url":"utilities/pipe_log","permission":"is_admin","icon":"","id":"ticket-pipe-log"}]},{"name":"als_reports","url":"#","permission":"reports","icon":"fa fa-area-chart","id":"reports","children":[{"name":"als_reports_sales_submenu","url":"reports/sales","permission":"","icon":"","id":"child-sales"},{"name":"Inquiries","url":"reports/inquiries","permission":"","icon":"","id":"child-inquiries_report"},{"name":"als_reports_expenses","url":"reports/expenses","permission":"","icon":"","id":"child-expenses"},{"name":"als_expenses_vs_income","url":"reports/expenses_vs_income","permission":"","icon":"","id":"child-expenses-vs-income"},{"name":"als_reports_leads_submenu","url":"reports/leads","permission":"","icon":"","id":"child-leads"},{"name":"timesheets_overview","url":"staff/timesheets?view=all","permission":"is_admin","icon":"","id":"reports_timesheets_overview"},{"name":"als_kb_articles_submenu","url":"reports/knowledge_base_articles","permission":"","icon":"","id":"child-kb-articles"}]}]}' WHERE `name` = 'aside_menu_active';

-- //vijay Ghori
-- //09/04/2021 Add or Edit Suppler Price in mfpurcheses item suppler
ALTER TABLE `tblmfitemssupplier` ADD `supplier_item_price` VARCHAR(11) NOT NULL AFTER `suppliers_id`;

-- =======
-- ALTER TABLE `tblitems_in`
-- 	ADD COLUMN `rack_no` VARCHAR(255) NULL DEFAULT NULL AFTER `fab_no`;
-- >>>>>>> changes.sql
