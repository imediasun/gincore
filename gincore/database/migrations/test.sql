CREATE TABLE IF NOT EXISTS `restore4_users_ratings` (
`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
    `user_id` int(10) UNSIGNED DEFAULT NULL,
    `order_id` int(10) UNSIGNED DEFAULT NULL,
    `client_id` int(10) UNSIGNED DEFAULT NULL,
    `rating` int(10) UNSIGNED DEFAULT 0,
    `created_at` timestamp default CURRENT_TIMESTAMP,
    `updated_at` timestamp default CURRENT_TIMESTAMP
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
    `updated_at` timestamp default CURRENT_TIMESTAMP
PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
ALTER TABLE `restore4_feedback` ADD INDEX order_id(order_id);
ALTER TABLE `restore4_feedback` ADD INDEX client_id(client_id);

ALTER TABLE {users} ADD COLUMN rating float UNSIGNED DEFAULT 10;

ALTER TABLE {clients} ADD COLUMN sms_code varchar(10) DEFAULT '';
ALTER TABLE {clients} ADD COLUMN client_code varchar(10) DEFAULT '';
