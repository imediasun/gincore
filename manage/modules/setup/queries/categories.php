<?php 


//New version 3

/*
 *  'erp-co-category-write-off' => 8,
    // категория на которую будет происходить списание
    'erp-co-category-sold' => 9,
    // категория на которую будет происходить продажа
    'erp-co-category-return' => 54,
    // категория на которую будет происходить возврат поставщику
 */

db()->query(
"
INSERT INTO {categories} 
(`id`, `title`, `parent_id`, `avail`, `url`, `prio`, `content`, `thumbs`, `image`, `cat-image`, `page_content`, `page_title`, `page_description`, `page_keywords`, `date_add`, `warehouses_suppliers`, `information`, `rating`, `votes`, `deleted`) 
VALUES
(8, '".lq("Списание")."', 0, b'1', 'spisanie', 268, 'Для списания ', NULL, NULL, NULL, '', '', '', '', '2014-07-01 07:38:09', '', '', 5.00, 0, 0),
(9, '".lq("Продажа")."', 0, b'1', 'prodazha', 269, 'продажа', NULL, NULL, NULL, '', '', '', '', '2014-07-03 02:14:32', '', '', 5.00, 0, 0),
(54, '".lq("Возврат поставщику")."', 0, b'1', 'vozvrat-postavschiku', 270, 'Возврат поставщику', NULL, NULL, NULL, '', '', '', '', '2014-07-28 07:25:56', '', '', 5.00, 0, 0),
(55, '".lq("Корзина")."', 0, b'1', 'recycle-bin', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-06-17 09:31:27', '', '', 0.00, 0, 0),
(56, '".lq("Товары и услуги")."', 0, b'1', 'tovaryi-i-uslugi', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:15', '', '', 0.00, 0, 0),
(57, '".lq("Мобильные телефоны")."', 56, b'1', 'mobilnyie-telefonyi', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:16', '', '', 0.00, 0, 0),
(58, '".lq("Apple iPhone")."', 57, b'1', 'apple-iphone', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:16', '', '', 0.00, 0, 0),
(59, '".lq("iPhone 5")."', 58, b'1', 'iphone-5', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:16', '', '', 0.00, 0, 0),
(60, '".lq("Планшеты")."', 56, b'1', 'planshetyi', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:16', '', '', 0.00, 0, 0),
(61, '".lq("Apple IPad")."', 60, b'1', 'apple-ipad', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:16', '', '', 0.00, 0, 0),
(62, '".lq("iPad Mini")."', 61, b'1', 'ipad-mini', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:16', '', '', 0.00, 0, 0),
(63, '".lq("IPhone 5S")."', 58, b'1', 'iphone-5s', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:16', '', '', 0.00, 0, 0),
(64, '".lq("IPhone 4S")."', 58, b'1', 'iphone-4s', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:16', '', '', 0.00, 0, 0),
(65, '".lq("IPhone 4")."', 58, b'1', 'iphone-4', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:16', '', '', 0.00, 0, 0),
(67, '".lq("IPhone 6")."', 58, b'1', 'iphone-6', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:16', '', '', 0.00, 0, 0),
(69, '".lq("Ноутбуки")."', 56, b'1', 'noutbuki', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:16', '', '', 0.00, 0, 0),
(70, '".lq("Ноутбук Apple")."', 69, b'1', 'noutbuk-apple', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:16', '', '', 0.00, 0, 0),
(71, '".lq("MacBook Air")."', 70, b'1', 'macbook-air', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:16', '', '', 0.00, 0, 0),
(72, '".lq("Macbook Pro")."', 70, b'1', 'macbook-pro', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:16', '', '', 0.00, 0, 0),
(73, '".lq("Телефон Samsung")."', 57, b'1', 'telefon-samsung', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:17', '', '', 0.00, 0, 0),
(74, '".lq("Samsung I8160 Galaxy Ace II")."', 73, b'1', 'samsung-i8160-galaxy-ace-ii', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:17', '', '', 0.00, 0, 0),
(75, '".lq("Samsung i9500 Galaxy S4")."', 73, b'1', 'samsung-i9500-galaxy-s4', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:17', '', '', 0.00, 0, 0),
(76, '".lq("Samsung i8190 Galaxy S3 mini")."', 73, b'1', 'samsung-i8190-galaxy-s3-mini', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:17', '', '', 0.00, 0, 0),
(77, '".lq("Телефон Sony")."', 57, b'1', 'telefon-sony', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:17', '', '', 0.00, 0, 0),
(78, '".lq("Sony Xperia Z3 (D6603)")."', 77, b'1', 'sony-xperia-z3-d6603', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:17', '', '', 0.00, 0, 0),
(79, '".lq("Sony Xperia Z3 Dual(D6633)")."', 77, b'1', 'sony-xperia-z3-duald6633', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:17', '', '', 0.00, 0, 0),
(80, '".lq("Sony Xperia Z3 Compact﻿ (D5803)")."', 77, b'1', 'sony-xperia-z3-compact-d5803', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:17', '', '', 0.00, 0, 0),
(81, '".lq("Sony Xperia M2 Aqua (D2403)")."', 77, b'1', 'sony-xperia-m2-aqua-d2403', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:17', '', '', 0.00, 0, 0),
(85, '".lq("IPhone 6 Plus")."', 58, b'1', 'iphone-6-plus', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:18', '', '', 0.00, 0, 0),
(87, '".lq("Телефон HTC")."', 57, b'1', 'telefon-htc', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:19', '', '', 0.00, 0, 0),
(88, '".lq("HTC One")."', 87, b'1', 'htc-one', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:19', '', '', 0.00, 0, 0),
(89, '".lq("HTC One M7 Dual Sim")."', 87, b'1', 'htc-one-m7-dual-sim', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:19', '', '', 0.00, 0, 0),
(90, '".lq("Телефон Lenovo")."', 57, b'1', 'telefon-lenovo', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:19', '', '', 0.00, 0, 0),
(91, '".lq("Lenovo A850")."', 90, b'1', 'lenovo-a850', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:19', '', '', 0.00, 0, 0),
(92, '".lq("Lenovo A820")."', 90, b'1', 'lenovo-a820', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:19', '', '', 0.00, 0, 0),
(93, '".lq("Lenovo A760")."', 90, b'1', 'lenovo-a760', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:19', '', '', 0.00, 0, 0),
(97, '".lq("Samsung i9300 Galaxy S3")."', 73, b'1', 'samsung-i9300-galaxy-s3', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:19', '', '', 0.00, 0, 0),
(98, '".lq("Телефон LG")."', 57, b'1', 'telefon-lg', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:19', '', '', 0.00, 0, 0),
(99, '".lq("LG E960 Nexus 4")."', 98, b'1', 'lg-e960-nexus-4', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:19', '', '', 0.00, 0, 0),
(100, '".lq("Ноутбуки Sony")."', 69, b'1', 'noutbuki-sony', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-20 08:03:19', '', '', 0.00, 0, 0);
");



/*
 * 
 * 
 * find
 * \(([0-9]+), '([^']+)'
 * 
 * repalce 
 * ($1, '".lq("$2")."'
 * 
 */


/*
db()->query("INSERT INTO {categories} (`id`, `title`, `parent_id`, `avail`, `url`, `prio`, `content`, `thumbs`, `image`, `cat-image`, `page_content`, `page_title`, `page_description`, `page_keywords`, `date_add`, `warehouses_suppliers`, `information`, `rating`, `votes`, `deleted`) VALUES
(1675, '.lq("Корзина")."")."")."', 0, b'0', 'recycle-bin', NULL, '', NULL, NULL, NULL, '', '', '', '', '2015-10-08 07:13:21', '', '', 0.00, 0, 0)");

*/