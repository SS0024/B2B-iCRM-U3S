/* date :- 13/11/2019 */
ALTER TABLE `tblitems_in` ADD `fab_no` VARCHAR(255) NOT NULL AFTER `long_description`;
ALTER TABLE `tblitems_in` ADD `installation_date` DATE NULL DEFAULT NULL AFTER `item_status`;
ALTER TABLE `tblitems_in` ADD `type_of_machine` TINYINT(4) NOT NULL AFTER `installation_date`;
ALTER TABLE `tblitems_in` ADD `category_type` VARCHAR(255) NOT NULL AFTER `type_of_machine`;