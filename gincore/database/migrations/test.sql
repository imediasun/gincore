/*
2016_03_29_081346_users_ratings.php
 */
CREATE TABLE IF NOT EXISTS `restore4_users_ratings` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(10) UNSIGNED DEFAULT NULL,
    `order_id` int(10) UNSIGNED DEFAULT NULL,
    `client_id` int(10) UNSIGNED DEFAULT NULL,
    `rating` int(10) UNSIGNED DEFAULT 0,
    `created_at` timestamp default CURRENT_TIMESTAMP,
    `updated_at` timestamp default 0
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
ALTER TABLE `restore4_users_ratings` ADD INDEX user_id(user_id);
ALTER TABLE `restore4_users_ratings` ADD INDEX client_id(client_id);

CREATE TABLE IF NOT EXISTS `restore4_feedback` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `order_id` int(10) UNSIGNED DEFAULT NULL,
    `client_id` int(10) UNSIGNED DEFAULT NULL,
    `engineer` int(10) UNSIGNED DEFAULT 0,
    `manager` int(10) UNSIGNED DEFAULT 0,
    `acceptor` int(10) UNSIGNED DEFAULT 0,
    `comment` varchar(255) DEFAULT '',
    `created_at` timestamp default CURRENT_TIMESTAMP,
    `updated_at` timestamp default 0
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
ALTER TABLE `restore4_feedback` ADD INDEX order_id(order_id);
ALTER TABLE `restore4_feedback` ADD INDEX client_id(client_id);

ALTER TABLE `restore4_users` ADD COLUMN rating float UNSIGNED DEFAULT 10;

/*
2016_03_29_135756_users_sms_code.php
*/
ALTER TABLE `restore4_clients` ADD COLUMN sms_code varchar(10) DEFAULT '';
ALTER TABLE `restore4_clients` ADD COLUMN client_code varchar(10) DEFAULT '';

/*
2016_03_31_081346_cashboxes_users.php
*/
CREATE TABLE IF NOT EXISTS `restore4_cashboxes_users` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `cashbox_id` int(10) UNSIGNED DEFAULT NULL,
    `user_id` int(10) UNSIGNED DEFAULT NULL,
PRIMARY KEY (`id`),
INDEX(`user_id`),
INDEX(`cashbox_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

/*
2016_04_20_055121_add_system_to_contractors_categories.php
 */

ALTER TABLE `restore4_contractors_categories` ADD COLUMN is_system tinyint(1) UNSIGNED DEFAULT 1;
UPDATE `restore4_contractors_categories` SET is_system=0 WHERE id > 36;

/*
2016_04_11_080606_block_user_by_tariff.php
*/
ALTER TABLE `restore4_users` ADD COLUMN blocked_by_tariff tinyint(1) UNSIGNED DEFAULT 0;
/*
2016_04_11_121328_add_over_email_to_users.php
 */
ALTER TABLE `restore4_users` ADD COLUMN send_over_email tinyint(1) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_users` ADD COLUMN send_over_sms tinyint(1) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_users` ADD INDEX send_over_email(send_over_email);
ALTER TABLE `restore4_users` ADD INDEX send_over_sms(send_over_sms);

/*
2016_04_21_134400_log_of_user_login.php
 */
CREATE TABLE IF NOT EXISTS `restore4_users_login_log` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(10) UNSIGNED NOT NULL,
    `ip` varchar(255) DEFAULT '' NOT NULL,
    `created_at` timestamp default CURRENT_TIMESTAMP,
    `updated_at` timestamp default 0,
PRIMARY KEY (`id`),
INDEX(`user_id`),
INDEX(`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

/*
2016_05_06_094627_add_warranty_to_orders_goods.ph
 */
ALTER TABLE `restore4_orders_goods` ADD COLUMN warranty int(10) UNSIGNED DEFAULT 0;

/*
2016_05_11_053602_add_fields_to_orders.ph
 */
ALTER TABLE `restore4_orders` ADD COLUMN delivery_by int(10) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_orders` ADD COLUMN sale_type int(10) UNSIGNED DEFAULT 0;

/*
2016_05_18_090846_add_salary_to_users.php
 */
ALTER TABLE `restore4_users` ADD COLUMN salary_from_sale int(10) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_users` ADD COLUMN salary_from_repair int(10) UNSIGNED DEFAULT 0;

/**
2016_05_12_094203_add_delivery_address_field_to_orders.php
 */
ALTER TABLE `restore4_orders` ADD COLUMN delivery_to VARCHAR (255) DEFAULT '';

/**
2016_05_12_101324_add_discount_type_field_to_orders_goods.php
 */
ALTER TABLE `restore4_orders_goods` ADD COLUMN discount_type int(10) UNSIGNED DEFAULT 1;
/**
2016_05_25_083531_remove_on_update.ph
 */
ALTER TABLE `restore4_warehouses_goods_items`
    CHANGE `date_add` `date_add` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP;
/*
2016_06_01_082402_add_number_to_course.php
 */
ALTER TABLE `restore4_cashboxes_courses`
    CHANGE `course` `course` DECIMAL(12, 3) DEFAULT 0;
/*
2016_06_06_114027_soft_delete_items_and_categories.php
 */
ALTER TABLE `restore4_categories` ADD COLUMN deleted int(10) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_goods` ADD COLUMN deleted int(10) UNSIGNED DEFAULT 0;
/*
2016_06_20_053344_users_fields.ph
 */
CREATE TABLE IF NOT EXISTS `restore4_users_fields` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` VARCHAR (255) NOT NULL,
    `title` VARCHAR (255) DEFAULT '' NOT NULL,
    `avail` int(10) UNSIGNED DEFAULT 1,
    `deleted` int(10) UNSIGNED DEFAULT 0,
PRIMARY KEY (`id`),
INDEX(`avail`),
INDEX(`deleted`),
UNIQUE INDEX (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
/*
2016_06_20_053352_orders_users_fields.ph
 */
CREATE TABLE IF NOT EXISTS `restore4_orders_users_fields` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `order_id` int(10) UNSIGNED NOT NULL,
    `users_field_id` int(10) UNSIGNED NOT NULL,
    `value` text default '',
PRIMARY KEY (`id`),
INDEX(`order_id`),
INDEX(`users_field_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
/*
2016_06_30_061857_add_system_to_warehouses.php
 */
ALTER TABLE `restore4_warehouses` ADD COLUMN is_system tinyint(4) UNSIGNED DEFAULT 0;
/*
2016_06_20_123041_add_title_to_sms_templates.php
 */
ALTER TABLE `restore4_sms_templates` ADD COLUMN var varchar(255) DEFAULT '';
/*
2016_07_04_091714_stocktaking.php
 */
CREATE TABLE IF NOT EXISTS `restore4_stocktaking` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `warehouse_id` int(10) UNSIGNED NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    saved_at TIMESTAMP,
    history tinyint(3) UNSIGNED NOT NULL DEFAULT 0,
    checked_serials TEXT NOT NULL,
PRIMARY KEY (`id`),
INDEX(`warehouse_id`),
INDEX(`created_at`),
INDEX(`saved_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
/*
2016_07_07_064255_stocktaking_locations.php
 */
CREATE TABLE IF NOT EXISTS `restore4_stocktaking_locations` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `stocktaking_id` int(10) UNSIGNED NOT NULL,
    `location_id` int(10) UNSIGNED NOT NULL,
PRIMARY KEY (`id`),
INDEX(`stocktaking_id`),
INDEX(`location_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
/*
2016_07_19_074755_add_individual_field_to_clients.php
 */
ALTER TABLE `restore4_clients` ADD COLUMN note VARCHAR (255) DEFAULT '';
ALTER TABLE `restore4_clients` ADD COLUMN reg_data_1 VARCHAR (255) DEFAULT '';
ALTER TABLE `restore4_clients` ADD COLUMN reg_data_2 VARCHAR (255) DEFAULT '';

/*
2016_07_06_090712_lock_filters
*/
CREATE TABLE IF NOT EXISTS `restore4_lock_filters` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `value` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `lock_filters_user_id_index` (`user_id`),
  KEY `lock_filters_name_index` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
/*
2016_07_22_061024_home_master_request.php
 */
CREATE TABLE IF NOT EXISTS `restore4_home_master_requests` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned NOT NULL,
  `address` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `date` TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `home_master_request_order_id_index` (`order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
ALTER TABLE `restore4_orders` ADD COLUMN home_master_request int(10) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_orders` ADD INDEX orders_home_master_request(home_master_request);
/*
2016_08_04_123634_add_price_type_to_order_goods.php
 */
ALTER TABLE `restore4_orders_goods` ADD COLUMN price_type int(1) UNSIGNED DEFAULT 3;
ALTER TABLE `restore4_orders_goods` ADD INDEX orders_goods_price_type(price_type);
/*
2016_08_05_122639_add_deleted_to_contractors_categories_links.php
 */
ALTER TABLE `restore4_contractors_categories_links` ADD COLUMN deleted int(1) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_contractors_categories_links` ADD INDEX contractors_categories_link_deleted(deleted);

/*
2016_08_16_053813_add_for_to_template_vars.php
 */
ALTER TABLE `restore4_template_vars` ADD COLUMN `for_view` VARCHAR (255) DEFAULT '';
ALTER TABLE `restore4_template_vars` ADD INDEX template_vars_for_view(for_view);
ALTER TABLE `restore4_template_vars` ADD COLUMN `description` VARCHAR (255) DEFAULT '';
/*
2016_08_17_064021_add_vendor_code_to_goods.php
 */
ALTER TABLE `restore4_goods` ADD COLUMN `vendor_code` VARCHAR (255) DEFAULT '';
ALTER TABLE `restore4_goods` ADD INDEX goods_vendor_code(vendor_code);
/*
2016_08_17_082613_add_show_client_infos_to_users.php
 */
ALTER TABLE `restore4_users` ADD COLUMN show_client_info int(1) UNSIGNED DEFAULT 1;
ALTER TABLE `restore4_users` ADD INDEX users_show_client_info(show_client_info);
/*
2016_08_19_061515_add_priority_to_template_vars.php
 */
ALTER TABLE `restore4_template_vars` ADD COLUMN priority int(10) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_template_vars` ADD INDEX template_vars_priority(priority);
/*
2016_08_23_070535_purchase_invoices.php
 */
CREATE TABLE IF NOT EXISTS `restore4_purchase_invoices` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `supplier_id` int(10) unsigned NOT NULL,
  `warehouse_id` int(10) unsigned NOT NULL,
  `location_id` int(10) unsigned NOT NULL,
  `type` int(10) unsigned NOT NULL,
  `state` int(10) unsigned NOT NULL,
  `comment` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `purchase_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `supplier_order_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_invoices_user_id_index` (`user_id`),
  KEY `purchase_invoices_supplier_id_index` (`supplier_id`),
  KEY `purchase_invoices_warehouse_id_index` (`warehouse_id`),
  KEY `purchase_invoices_location_id_index` (`location_id`),
  KEY `purchase_invoices_type_index` (`type`),
  KEY `purchase_invoices_state_index` (`state`),
  KEY `purchase_invoices_supplier_order_id_index` (`supplier_order_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `restore4_purchase_invoice_goods` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `invoice_id` int(10) unsigned NOT NULL,
  `good_id` int(10) unsigned NULL,
  `price` int(10) unsigned NOT NULL,
  `quantity` int(10) unsigned NOT NULL,
  `not_found` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `purchase_invoice_goods_invoice_id` (`invoice_id`),
  KEY `purchase_invoice_goods_good_id` (`good_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
/*
2016_08_25_123845_add_order_id_to_purchase_invoices.php
 */
/*ALTER TABLE `restore4_purchase_invoices` ADD COLUMN supplier_order_id int(10) UNSIGNED DEFAULT 0;*/
/*ALTER TABLE `restore4_purchase_invoices` ADD INDEX purchase_invoices_supplier_order_id(supplier_order_id);*/
/*
2016_08_30_113445_add_show_only_users_orders.php
 */
ALTER TABLE `restore4_users` ADD COLUMN show_only_his_orders int(1) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_users` ADD INDEX users_show_only_his_orders(show_only_his_orders);
/*
2016_09_13_065228_add_engineer_comment_to_orders.php
 */
ALTER TABLE `restore4_orders` ADD COLUMN engineer_comment varchar(255) DEFAULT '';
/*
2016_09_13_113635_brands.php
 */
CREATE TABLE IF NOT EXISTS `restore4_brands` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
ALTER TABLE `restore4_orders` ADD COLUMN brand_id int(10) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_orders` ADD INDEX orders_brand_id(brand_id);
INSERT INTO `restore4_brands` (title) VALUES ('LG'), ('Apple'), ('Samsung');
/*
2016_09_22_121620_custom_statuses.php
 */
CREATE TABLE IF NOT EXISTS `restore4_status` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `name` VARCHAR (255) NOT NULL,
    `from` VARCHAR (255) NOT NULL,
    `color` VARCHAR (6) NOT NULL,
    `status_id` int(10) UNSIGNED NOT NULL,
    `order_type` int(10) UNSIGNED DEFAULT 0,
    `system` SMALLINT UNSIGNED DEFAULT 0,
    `use_in_manager` SMALLINT UNSIGNED DEFAULT 1,
    `active` SMALLINT UNSIGNED DEFAULT 1,
PRIMARY KEY (`id`),
INDEX (`status_id`),
INDEX(`system`),
INDEX(`use_in_manager`),
INDEX(`active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
/*
2016_09_27_082521_add_margin_to_products.php
 */
ALTER TABLE `restore4_goods` ADD COLUMN minimum_balance int(10) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_goods` ADD COLUMN automargin int(10) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_goods` ADD COLUMN wholesale_automargin int(10) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_goods` ADD COLUMN use_minimum_balance tinyint(1) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_goods` ADD COLUMN use_automargin tinyint(1) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_goods` ADD COLUMN automargin_type tinyint(1) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_goods` ADD COLUMN wholesale_automargin_type tinyint(1) UNSIGNED DEFAULT 0;
/*
2016_09_28_062301_payment_for_profit.php
 */
ALTER TABLE `restore4_goods` ADD COLUMN percent_from_profit int(10) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_goods` ADD COLUMN fixed_payment float UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_categories` ADD COLUMN percent_from_profit int(10) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_categories` ADD COLUMN fixed_payment float UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_users` ADD COLUMN use_percent_from_profit tinyint(1) UNSIGNED DEFAULT 0;
ALTER TABLE `restore4_users` ADD COLUMN use_fixed_payment tinyint(1) UNSIGNED DEFAULT 0;
