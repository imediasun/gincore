<?php 


//New version 3


db()->query(
"
INSERT INTO {categories} 
(`id`, `title`, `parent_id`, `avail`, `url`, `prio`, `content`, `thumbs`, `image`, `cat-image`, `page_content`, `page_title`, `page_description`, `page_keywords`, `date_add`, `warehouses_suppliers`, `information`, `rating`, `votes`, `deleted`) 
VALUES
(1, '".lq("".lq("Продажа")."")."', 0, b'1', 'prodazha', 269, 'продажа', NULL, NULL, NULL, '', '', '', '', '2014-07-03 02:14:32', '', '', 5.00, 0, 0),
(2, '".lq("Списание")."', 0, b'1', 'spisanie', 268, 'Для списания ', NULL, NULL, NULL, '', '', '', '', '2014-07-01 07:38:09', '', '', 5.00, 0, 0),
(3, '".lq("Возврат поставщику")."', 0, b'1', 'vozvrat-postavschiku', 270, 'Возврат поставщику', NULL, NULL, NULL, '', '', '', '', '2014-07-28 07:25:56', '', '', 5.00, 0, 0),
(4, '".lq("Корзина")."', 0, b'1', 'recycle-bin', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-06-17 09:31:27', '', '', 0.00, 0, 0),
(5, '".lq("Товары и услуги")."', 0, b'1', 'tovaryi-i-uslugi', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:24', '', '', 0.00, 0, 0),
(6, '".lq("Мобильные телефоны")."', 5, b'1', 'mobilnyie-telefonyi', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:24', '', '', 0.00, 0, 0),
(7, '".lq("Apple iPhone")."', 6, b'1', 'apple-iphone', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:24', '', '', 0.00, 0, 0),
(8, '".lq("iPhone 5")."', 7, b'1', 'iphone-5', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:24', '', '', 0.00, 0, 0),
(9, '".lq("Планшеты")."', 5, b'1', 'planshetyi', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:24', '', '', 0.00, 0, 0),
(10, '".lq("Apple IPad")."', 9, b'1', 'apple-ipad', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:24', '', '', 0.00, 0, 0),
(11, '".lq("iPad Mini")."', 10, b'1', 'ipad-mini', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:24', '', '', 0.00, 0, 0),
(12, '".lq("IPhone 5S")."', 7, b'1', 'iphone-5s', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:24', '', '', 0.00, 0, 0),
(13, '".lq("IPhone 4S")."', 7, b'1', 'iphone-4s', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:24', '', '', 0.00, 0, 0),
(14, '".lq("IPhone 4")."', 7, b'1', 'iphone-4', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:24', '', '', 0.00, 0, 0),
(16, '".lq("Аксессуары")."', 5, b'1', 'aksessuaryi', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:24', '', '', 0.00, 0, 0),
(17, '".lq("Авто-аксессуары")."', 16, b'1', 'avto-aksessuaryi', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:24', '', '', 0.00, 0, 0),
(18, '".lq("Зарядные устройства")."', 16, b'1', 'zaryadnyie-ustroystva', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:24', '', '', 0.00, 0, 0),
(19, '".lq("Зарядные устройства для ноутбуков")."', 18, b'1', 'zaryadnyie-ustroystva-dlya-noutbukov', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:24', '', '', 0.00, 0, 0),
(20, '".lq("IPhone 6")."', 7, b'1', 'iphone-6', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:25', '', '', 0.00, 0, 0),
(22, '".lq("Ноутбуки")."', 5, b'1', 'noutbuki', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:25', '', '', 0.00, 0, 0),
(23, '".lq("Ноутбук Apple")."', 22, b'1', 'noutbuk-apple', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:25', '', '', 0.00, 0, 0),
(24, '".lq("MacBook Air")."', 23, b'1', 'macbook-air', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:25', '', '', 0.00, 0, 0),
(25, '".lq("Macbook Pro")."', 23, b'1', 'macbook-pro', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:25', '', '', 0.00, 0, 0),
(26, '".lq("Телефон Samsung")."', 6, b'1', 'telefon-samsung', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:26', '', '', 0.00, 0, 0),
(27, '".lq("Samsung I8160 Galaxy Ace II")."', 26, b'1', 'samsung-i8160-galaxy-ace-ii', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:26', '', '', 0.00, 0, 0),
(28, '".lq("Samsung i9500 Galaxy S4")."', 26, b'1', 'samsung-i9500-galaxy-s4', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:26', '', '', 0.00, 0, 0),
(29, '".lq("Samsung i8190 Galaxy S3 mini")."', 26, b'1', 'samsung-i8190-galaxy-s3-mini', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:26', '', '', 0.00, 0, 0),
(30, '".lq("Телефон Sony")."', 6, b'1', 'telefon-sony', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:26', '', '', 0.00, 0, 0),
(31, '".lq("Sony Xperia Z3 (D6603)")."', 30, b'1', 'sony-xperia-z3-d6603', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:26', '', '', 0.00, 0, 0),
(32, '".lq("Sony Xperia Z3 Dual(D6633)")."', 30, b'1', 'sony-xperia-z3-duald6633', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:26', '', '', 0.00, 0, 0),
(33, '".lq("Sony Xperia Z3 Compact﻿ (D5803)")."', 30, b'1', 'sony-xperia-z3-compact-d5803', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:26', '', '', 0.00, 0, 0),
(34, '".lq("Sony Xperia M2 Aqua (D2403)")."', 30, b'1', 'sony-xperia-m2-aqua-d2403', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:26', '', '', 0.00, 0, 0),
(38, '".lq("IPhone 6 Plus")."', 7, b'1', 'iphone-6-plus', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:27', '', '', 0.00, 0, 0),
(39, '".lq("Кабели и переходники")."', 16, b'1', 'kabeli-i-perehodniki', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:27', '', '', 0.00, 0, 0),
(41, '".lq("Телефон HTC")."', 6, b'1', 'telefon-htc', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:28', '', '', 0.00, 0, 0),
(42, '".lq("HTC One")."', 41, b'1', 'htc-one', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:28', '', '', 0.00, 0, 0),
(43, '".lq("HTC One M7 Dual Sim")."', 41, b'1', 'htc-one-m7-dual-sim', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:28', '', '', 0.00, 0, 0),
(44, '".lq("Телефон Lenovo")."', 6, b'1', 'telefon-lenovo', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:28', '', '', 0.00, 0, 0),
(45, '".lq("Lenovo A850")."', 44, b'1', 'lenovo-a850', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:28', '', '', 0.00, 0, 0),
(46, '".lq("Lenovo A820")."', 44, b'1', 'lenovo-a820', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:28', '', '', 0.00, 0, 0),
(47, '".lq("Lenovo A760")."', 44, b'1', 'lenovo-a760', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:28', '', '', 0.00, 0, 0),
(51, '".lq("Samsung i9300 Galaxy S3")."', 26, b'1', 'samsung-i9300-galaxy-s3', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:29', '', '', 0.00, 0, 0),
(52, '".lq("Телефон LG")."', 6, b'1', 'telefon-lg', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:29', '', '', 0.00, 0, 0),
(53, '".lq("LG E960 Nexus 4")."', 52, b'1', 'lg-e960-nexus-4', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:29', '', '', 0.00, 0, 0),
(54, '".lq("Ноутбуки Sony")."', 22, b'1', 'noutbuki-sony', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:29', '', '', 0.00, 0, 0),
(60, '".lq("Чехлы")."', 16, b'1', 'chehlyi', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:29', '', '', 0.00, 0, 0),
(61, '".lq("Чехлы для iPhone")."', 60, b'1', 'chehlyi-dlya-iphone', NULL, '', NULL, NULL, NULL, '', '', '', '', '2016-09-19 09:54:29', '', '', 0.00, 0, 0);
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
(1675, '".lq("".lq("Корзина")."")."', 0, b'0', 'recycle-bin', NULL, '', NULL, NULL, NULL, '', '', '', '', '2015-10-08 07:13:21', '', '', 0.00, 0, 0)");


*/