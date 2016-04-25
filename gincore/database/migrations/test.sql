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
