<?php

db()->query('SET FOREIGN_KEY_CHECKS=0');

// статьи расход-приход
db()->query('TRUNCATE TABLE {contractors_categories}');
db()->query("
    INSERT INTO `restore4_contractors_categories` 
    (`id`, `parent_id`, `avail`, `transaction_type`, `name`, `code_1c`, `date_add`, `comment`) VALUES
    (1, 0, b'1', 2, '".lq('Оплаты за заказы с сайта')."', '', NOW(), ''),
    (2, 0, b'1', 1, '".lq('Возврат денег за заказ')."', '', NOW(), ''),
    (3, 0, b'1', 1, '".lq('Контрагент')."', '', NOW(), ''),
    (4, 0, b'1', 1, '".lq('Списание')."', '', NOW(), ''),
    (5, 0, b'1', 2, '".lq('Списание с баланса за возврат поставщику')."', '', NOW(), ''),
    (6, 0, b'1', 1, '".lq('Списание денег с баланса за возврат поставщику')."', '', NOW(), ''),
    (7, 0, b'1', 1, '".lq('Оплата заказа поставщику')."', '', NOW(), ''),
    (8, 0, b'1', 1, '".lq('Оплата заказа поставщику (на баланс, без привязки к заказу)')."', '', NOW(), ''),
    (9, 0, b'1', 1, '".lq('Комиссионные взыскания банков')."', '', NOW(), ''),
    (12, 0, b'1', 1, '".lq('Оплата за аренду (недвижимости)')."', '', NOW(), ''),
    (14, 0, b'1', 1, '".lq('Оплата коммунальных услуг')."', '', NOW(), ''),
    (15, 0, b'1', 1, '".lq('Почтовые расходы')."', '', NOW(), ''),
    (16, 0, b'1', 1, '".lq('Расходы на телефонию и связь')."', '', NOW(), ''),
    (17, 16, b'1', 1, '".lq('Интернет')."', '', NOW(), ''),
    (18, 16, b'1', 1, '".lq('СМС отправки')."', '', NOW(), ''),
    (20, 0, b'1', 1, '".lq('Расходы на рекламу')."', '', NOW(), ''),
    (21, 20, b'1', 1, '".lq('Google Adwords')."', '', NOW(), ''),
    (22, 20, b'1', 1, '".lq('Яндекс Direct')."', '', NOW(), ''),
    (23, 20, b'1', 1, '".lq('SEO ( органическое продвижение)')."', '', NOW(), ''),
    (24, 20, b'1', 1, '".lq('Расходы на наружную рекламу')."', '', NOW(), ''),
    (25, 0, b'1', 1, '".lq('Обслуживание офиса')."', '', NOW(), ''),
    (26, 25, b'1', 1, '".lq('Расходы на хозтовары')."', '', NOW(), ''),
    (27, 25, b'1', 1, '".lq('Расходы канцтовары')."', '', NOW(), ''),
    (28, 25, b'1', 1, '".lq('Услуги пультовой охраны')."', '', NOW(), ''),
    (29, 0, b'1', 1, '".lq('Зарплаты')."', '', NOW(), ''),
    (30, 34, b'1', 1, '".lq('Инвентарь')."', '', NOW(), ''),
    (31, 0, b'1', 2, '".lq('Инвестиции')."', '', NOW(), ''),
    (32, 0, b'1', 2, '".lq('Ввод денежных остатков')."', '', NOW(), ''),
    (33, 0, b'1', 2, '".lq('Предоплата заказов на ремонт')."', '', NOW(), ''),
    (34, 0, b'1', 1, '".lq('Необоротные активы')."', '', NOW(), ''),
    (35, 34, b'1', 1, '".lq('Мебель')."', '', NOW(), ''),
    (36, 34, b'1', 1, '".lq('Оргтехника')."', '', NOW(), '')
");

// настройки
//db()->query('TRUNCATE TABLE {settings}');
$settingsArr = array();
$settingsArr[]=array('content_email', '', lq('Эл. адрес'), 0, '');
$settingsArr[]=array('site_name', lq('Сервисный центр'), lq('Название сайта'), 0, '');
$settingsArr[]=array('turbosms-from', '', lq('Смс от кого'), 0, '');
$settingsArr[]=array('turbosms-login', '', lq('Смс логин'), 0, '');
$settingsArr[]=array('turbosms-password', '', lq('Смс пароль'), 0, '');
$settingsArr[]=array('sms-provider', '', lq('Смс провайдер'), 0, lq('Укажите провайдера: turbosms или smsru'), '');
$settingsArr[]=array('orders_comments_days', '3', lq('Количество дней для уведомления менеджера об отсутствии новых записей в статусе заказа'), 0, '');
$settingsArr[]=array('warranties_left_days', '1,3,7', lq('Дни, для уведомлений менеджеру до конца 14ти дневного срока гарантийного обслуживания'), 0, '');
$settingsArr[]=array('unsold_items_days', '10', lq('Количество дней для уведомления менеджера о нарушении оборачиваемости'), 0, '');
$settingsArr[]=array('cat-non-all-ext', '2, 7, 8, 6, 5', lq('Статьи не используемые в выдачах'), 0, '');
$settingsArr[]=array('cat-non-current-assets', '34', lq('Статьи используемые для вычисления необоротных активов'), 0, lq('Укажите номер статьи'), '');
$settingsArr[]=array('order_warranties', '1,3,6,12', lq('Гарантии в заказ на ремонт'), 0, lq('Укажите доступные сроки гарантии через запятую'), '');
$settingsArr[]=array('default_order_warranty', 0, lq('Гарантии по умолчанию'), 0, lq('Укажите гарандию по умолчанию'), '');
$settingsArr[]=array('demand-factor', '0.33', lq('Коэффициент спроса'), 0, '');
$settingsArr[]=array('currency_suppliers_orders', '1', lq('Валюта заказов поставщикам'), 1, '');
$settingsArr[]=array('currency_orders', '1', lq('Валюта заказов'), 1, '');
$settingsArr[]=array('complete-master', '0', lq('Пройден мастер настройки'), 1, '');
$settingsArr[]=array('country', '', lq('Страна'), 1, '');
$settingsArr[]=array('account_phone', '', lq('Ваш телефон'), 1, '');
$settingsArr[]=array('account_business', '', lq('Ваш бизнес'), 1, '');
$settingsArr[]=array('lang', '', lq('Язык системы'), 1, '');
$settingsArr[]=array('time_zone', 'Europe/Kiev', lq('Временная зона'), 0, lq('Временная зона, например Europe/Kiev'));
$settingsArr[]=array('need_send_login_log', '0', lq('Отправлять ежедневные логи входа на email'), 0, lq('Отправлять ежедневные логи входа на email'));
$settingsArr[]=array('email_for_send_login_log', '', lq('email на который будут отправлять логи входов в систему'), 0, lq('email на который будут отправлять логи входов в систему'));

$settingsArr[]=array('ga-profile-id', '', lq('GA id профиля'), 0, lq('дентификатор вашего профиля из Гугл Аналитики'));
$settingsArr[]=array('ga-service-account-email', '', lq('GA сервисный эл. адрес'), 0, '');
$settingsArr[]=array('ga-private-key', '', lq('GA закрытый ключ API'), 0, '');
$settingsArr[]=array('order-first-number', '', lq('Начало нумарации заказов'), 0, lq('Укажите последний номер заказа, который у вас был ранее'));

foreach ($settingsArr as $ar) {
    $value = '';
    if($ar[0] != 'time_zone'){
        $value = "`value` = '".$ar[1]."',";
    }
    db()->query("INSERT INTO `restore4_settings` (`name`, `value`, `ro`, `title`, `description`) 
            VALUES ('$ar[0]', '$ar[1]', '$ar[3]', '$ar[2]', '$ar[4]')
            ON DUPLICATE KEY UPDATE `name` = '$ar[0]', ".$value." "
                    . "`ro` = '$ar[3]', `title` = '$ar[2]', "
            . "`description` = '$ar[4]'; ");
}


db()->query("UPDATE {goods} SET date_add = NOW()");
db()->query(
    "INSERT IGNORE INTO {clients}(phone,pass,fio,date_add,person) "
   ."VALUES('000000000000','-','".lq('Списание товара')."',NOW(),1)");
// права доступа
db()->query('TRUNCATE TABLE {users_permissions_groups}');
db()->query("
    INSERT INTO {users_permissions_groups} (`id`, `name`, `prio`) VALUES 
    (1, '".lq('Администрирование')."', '0'), 
    (2, '".lq('Управление контентом')."', '1'), 
    (3, '".lq('Просмотр контента')."', '2'), 
    (4, '".lq('Заказы клиентов')."', '3'), 
    (5, '".lq('Заказы поставщикам')."', '4'), 
    (6, '".lq('Бухгалтерия')."', '5'), 
    (7, '".lq('Логистика')."', '6'), 
    (8, '".lq('Доступ для инженера')."', '7'), 
    (9, '".lq('Постановка задач сотрудникам')."', '8'),
    (10, '".lq('Доступ для партнеров компании')."', '9'),
    (11, '".lq('Доступ для оборудования')."', '10')
");
db()->query('TRUNCATE TABLE {users_permissions}');
db()->query("
    INSERT INTO {users_permissions} (`id`, `name`, `link`, `child`, `group_id`) VALUES
    (1, '".lq('Распределение прав доступа')."', 'edit-users', 0, 1),
    (2, '".lq('Создание товара/детали')."', 'create-goods', 6, 1),
    (3, '".lq('Редактирование товарных позиций')."', 'edit-goods', 6, 2),
    (4, '".lq('Создание фильтров и категорий')."', 'create-filters-categories', 7, 2),
    (5, '".lq('Редактирование фильтров и категорий')."', 'edit-filters-categories', 7, 2),
    (6, '".lq('Просмотр товарной позиции')."', 'show-goods', 0, 3),
    (7, '".lq('Просмотр категорий и фильтров')."', 'show-categories-filters', 0, 3),
    (9, '".lq('Супер роль (доступ ко всему)')."', 'site-administration', 0, 1),
    (14, '".lq('Новый заказ (сообщение)')."', 'mess-new-order', 0, 4),
    (18, '".lq('Управление заказами клиентов')."', 'edit-clients-orders', 0, 4),
    (19, '".lq('Редактирование заказов поставщику')."', 'edit-suppliers-orders', 0, 5),
    (25, '".lq('Бухгалтерия (полный доступ)')."', 'accounting', 0, 6),
    (26, '".lq('Приходование заказов поставщику')."', 'debit-suppliers-orders', 0, 5),
    (27, '".lq('Логистика')."', 'logistics', 0, 7),
    (28, '".lq('Оплата заказов поставщику (сообщение)')."', 'mess-accountings-suppliers-orders', 25, 5),
    (29, '".lq('Оприходование заказов поставщику (сообщение)')."', 'mess-warehouses-suppliers-orders', 26, 5),
    (30, '".lq('Принять/выдать/привязать серийник (сообщение)')."', 'mess-debit-clients-orders', 26, 5),
    (34, '".lq('Возврат поставщику товара/детали')."', 'return-items-suppliers', 0, 1),
    (39, '".lq('Доступ к разделу "Контрагенты" в Бухгалтерии')."', 'accounting-contractors', 0, 6),
    (40, '".lq('Доступ к разделу "Оборот" в Бухгалтерии')."', 'accounting-reports-turnover', 0, 6),
    (41, '".lq('Доступ к разделу "Транзакции контрагентов" в Бухгалтерии')."', 'accounting-transactions-contractors', 0, 6),
    (43, '".lq('Создание заказов клиента')."', 'create-clients-orders', 0, 4),
    (44, '".lq('Добавление комментарий к заказу клиента')."', 'add-comment-to-clients-orders', 0, 4),
    (45, '".lq('Просмотр заказов клиентов')."', 'show-clients-orders', 0, 4),
    (46, '".lq('Редактирование фотографий с вебкамеры')."', 'client-order-photo', 0, 4),
    (47, '".lq('Инженер')."', 'engineer', 0, 8),
    (50, '".lq('Сканер штрихкодов')."', 'scanner-moves', 0, 11),
    (51, '".lq('Логистика (уведомления)')."', 'logistics-mess', 0, 7),
    (52, '".lq('Партнер')."', 'partner', 0, 10),
    (53, '".lq('Доступ к приложению "Менеджер заказов"')."', 'orders-manager', 0, 4),
    (54, '".lq('Мониторинг конкурентов')."', 'monitoring', 0, 1),
    (55, '".lq('Создать задачу')."', 'create-task', 0, 9),
    (56, '".lq('Доступ к статистике на главной странице')."', 'dashboard', 0, 1),
    (57, '".lq('Внешний маркетинг')."', 'external-marketing', 0, 2),
    (58, '".lq('Просмотр и редактирование чужих заказов поставщику')."', 'read-other-suppliers-orders', 19, 5),
    (59, '".lq('Добавление клиента в черный список')."', 'add-client-to-blacklist', 0, 1),
    (60, '".lq('Списание изделия')."', 'write-off-items', 0, 1),
    (61, '".lq('Доступ к разделу "Клиенты"')."', 'show-client-section', 0, 4),
    (62, '".lq('Доступ к экспорту базы клиентов и заказов')."', 'export-clients-and-orders', 0, 4)
    
");
db()->query('TRUNCATE TABLE {users_role_permission}');
db()->query("
    INSERT INTO {users_role_permission} (`id`, `role_id`, `permission_id`) VALUES
    (2, 3, 18),(3, 4, 25),(4, 4, 28),(7, 6, 43),(8, 2, 26),(9, 2, 29),(10, 2, 30),(23, 1, 14),(27, 1, 18),(52, 1, 43),(53, 1, 44),(54, 7, 45),(56, 6, 2),(57, 6, 6),(58, 10, 1),(59, 10, 2),(60, 10, 3),(61, 10, 4),(62, 10, 5),(63, 10, 6),(64, 10, 7),(66, 10, 9),(70, 10, 14),(74, 10, 18),(75, 10, 19),(81, 10, 25),(82, 10, 26),(83, 10, 27),(90, 10, 34),(95, 10, 39),(96, 10, 40),(97, 10, 41),(99, 10, 43),(100, 10, 44),(101, 10, 45),(103, 11, 2),(104, 11, 3),(105, 11, 4),(106, 11, 5),(107, 11, 6),(108, 11, 7),(114, 11, 14),(118, 11, 18),(119, 11, 19),(126, 11, 26),(127, 11, 27),(129, 11, 29),(130, 11, 30),(139, 11, 39),(141, 11, 41),(143, 11, 43),(144, 11, 44),(147, 12, 2),(148, 12, 3),(149, 12, 4),(150, 12, 5),(151, 12, 6),(152, 12, 7),(187, 12, 43),(188, 12, 44),(193, 7, 44),(194, 6, 3),(197, 6, 44),(198, 12, 18),(199, 7, 47),(200, 10, 46),(202, 6, 18),(203, 6, 45),(204, 6, 4),(205, 6, 7),(218, 13, 50),(219, 3, 2),(220, 3, 3),(221, 3, 6),(222, 3, 7),(224, 5, 2),(225, 5, 3),(226, 5, 4),(227, 5, 5),(228, 5, 6),(229, 5, 7),(231, 8, 27),(232, 5, 44),(234, 5, 18),(235, 9, 43),(236, 9, 44),(237, 9, 45),(238, 9, 52),(239, 8, 51),(240, 9, 18),(242, 1, 45),(243, 5, 45),(244, 11, 45),(245, 12, 45),(246, 6, 53),(247, 1, 53),(248, 11, 53),(249, 10, 54),(250, 8, 45),(252, 2, 19),(253, 2, 2),(254, 2, 3),(255, 2, 5),(256, 2, 6),(257, 2, 7),(259, 2, 18),(260, 2, 43),(261, 2, 44),(262, 2, 45),(263, 8, 44),(265, 8, 2),(266, 8, 3),(267, 8, 6),(268, 8, 43),(269, 5, 43),(270, 5, 53),(271, 5, 54),(273, 5, 27),(275, 1, 6),(276, 10, 55),(278, 11, 55),(279, 3, 19),(280, 3, 43),(281, 3, 44),(282, 3, 45),(283, 6, 27),(284, 1, 3),(285, 1, 2),(286, 1, 4),(287, 1, 5),(288, 1, 7),(289, 10, 57),(290, 3, 57),(291, 11, 57), (292, 1, 61), (293, 5, 61),(294, 4, 61), (295, 11, 61), (296, 10, 61), (297, 10, 62), (298, 11, 62), (299, 1, 62)
");
db()->query('TRUNCATE TABLE {users_roles}');
db()->query("
    INSERT INTO {users_roles} (`id`, `name`, `avail`, `date_end`) VALUES
    (1, '".lq('Руководитель')."', 1, '0000-00-00 00:00:00'),
    (2, '".lq('Кладовщик')."', 1, '0000-00-00 00:00:00'),
    (3, '".lq('Менеджер по закупкам')."', 1, '0000-00-00 00:00:00'),
    (4, '".lq('Бухгалтер')."', 0, '0000-00-00 00:00:00'),
    (5, '".lq('Менеджер по продажам')."', 1, '0000-00-00 00:00:00'),
    (6, '".lq('Приемщик')."', 1, '0000-00-00 00:00:00'),
    (7, '".lq('Инженер')."', 1, '0000-00-00 00:00:00'),
    (8, '".lq('Курьер')."', 1, '0000-00-00 00:00:00'),
    (9, '".lq('Партнер')."', 1, '0000-00-00 00:00:00'),
    (10, '".lq('Учредитель')."', 1, '0000-00-00 00:00:00'),
    (11, '".lq('Директор')."', 1, '0000-00-00 00:00:00'),
    (12, '".lq('Оператор- кладовщик')."', 1, '0000-00-00 00:00:00'),
    (13, '".lq('Сканер')."', 1, '0000-00-00 00:00:00')
");

 // создаем системных контрагентов
// покупатель
$pid = db()->query('INSERT IGNORE INTO {contractors}
                    (title, type, comment) VALUES (?, ?i, ?)',
                array(lq('Клиент'), 3, 'system'), 'id');
db()->query(
    "INSERT IGNORE INTO {clients}(phone,pass,fio,date_add,person, contractor_id) "
    ."VALUES('000000000002','-','".lq('Клиент')."',NOW(),1, ?i)", array($pid));
// покупатель списания
db()->query('INSERT IGNORE INTO {contractors}
                    (title, type, comment) VALUES (?, ?i, ?)',
                array(lq('Покупатель списания'), 3, 'system'));
// ввод денежных остатков
$id = db()->query('INSERT IGNORE INTO {contractors}
                            (title, type, comment) VALUES (?, ?i, ?)',
                        array(lq('Ввод денежных остатков'), 1, 'system'), 'id');
db()->query('INSERT IGNORE INTO {contractors_categories_links}
                    (contractors_categories_id, contractors_id) VALUES (?i, ?i)',
                    array(32, $id));
// поставщик
$pid = db()->query('INSERT IGNORE INTO {contractors}
                            (title, type, comment) VALUES (?, ?i, ?)',
                        array(lq('Поставщик'), 2, ''), 'id');

db()->query(
    "INSERT IGNORE INTO {clients}(phone,pass,fio,date_add,person, contractor_id) "
    ."VALUES('000000000001','-','".lq('Поставщик')."',NOW(),1, ?i)", array($pid));

$s_values = array();
foreach($this->all_configs['configs']['erp-contractors-type-categories'][2][1] as $sid){
    $s_values[] = db()->makeQuery("(?i, ?i)", array($sid,$pid));
}
foreach($this->all_configs['configs']['erp-contractors-type-categories'][2][2] as $sid){
    $s_values[] = db()->makeQuery("(?i, ?i)", array($sid,$pid));
}
//привязываем Клиента к возврату за заказ #683
db()->query('INSERT IGNORE INTO {contractors_categories_links}
                        (contractors_categories_id, contractors_id) VALUES (2, 1)',
                        array());
        
db()->query('INSERT IGNORE INTO {contractors_categories_links}
                        (contractors_categories_id, contractors_id) VALUES ?q',
                        array(implode(',',$s_values)));

// категории
db()->query("TRUNCATE TABLE {categories}");
require_once 'categories.php';

// товары
db()->query("TRUNCATE TABLE {goods}");
require_once 'goods.php';

// связи категорий и товаров
db()->query("TRUNCATE TABLE {category_goods}");
db()->query("INSERT INTO {category_goods} (`id`, `goods_id`, `category_id`) VALUES
(1, 1, 8),
(2, 2, 11),
(3, 3, 12),
(4, 4, 8),
(5, 5, 13),
(6, 6, 14),
(7, 7, 9),
(8, 8, 17),
(9, 9, 17),
(10, 10, 17),
(11, 11, 19),
(12, 12, 19),
(13, 13, 20),
(14, 14, 9),
(15, 15, 24),
(16, 16, 25),
(17, 17, 8),
(18, 18, 12),
(19, 19, 13),
(20, 20, 8),
(21, 21, 8),
(22, 22, 8),
(23, 23, 8),
(24, 24, 12),
(25, 25, 8),
(26, 26, 13),
(27, 27, 20),
(28, 28, 27),
(29, 29, 28),
(30, 30, 29),
(31, 31, 31),
(32, 32, 8),
(33, 33, 8),
(34, 34, 8),
(35, 35, 12),
(36, 36, 12),
(37, 37, 13),
(38, 38, 20),
(39, 39, 20),
(40, 40, 32),
(41, 41, 33),
(42, 42, 34),
(43, 43, 9),
(44, 44, 13),
(45, 45, 9),
(46, 46, 28),
(47, 47, 28),
(48, 48, 28),
(49, 49, 28),
(50, 50, 28),
(51, 51, 28),
(52, 52, 28),
(53, 53, 28),
(54, 54, 28),
(55, 55, 28),
(56, 56, 28),
(57, 57, 13),
(58, 58, 13),
(59, 59, 8),
(60, 60, 8),
(61, 61, 12),
(62, 62, 12),
(63, 63, 9),
(64, 64, 12),
(65, 65, 13),
(66, 66, 20),
(67, 67, 20),
(68, 68, 38),
(69, 69, 38),
(70, 70, 20),
(71, 71, 20),
(72, 72, 20),
(73, 73, 38),
(74, 74, 39),
(75, 75, 24),
(76, 76, 25),
(77, 77, 39),
(78, 78, 39),
(79, 79, 9),
(80, 80, 12),
(81, 81, 25),
(82, 82, 25),
(83, 83, 8),
(84, 84, 8),
(85, 85, 42),
(86, 86, 43),
(87, 87, 45),
(88, 88, 46),
(89, 89, 47),
(90, 90, 14),
(91, 91, 28),
(92, 92, 28),
(93, 93, 28),
(94, 94, 28),
(95, 95, 28),
(96, 96, 12),
(97, 97, 12),
(98, 98, 20),
(99, 99, 20),
(100, 100, 42),
(101, 101, 42),
(102, 102, 42),
(103, 103, 42),
(104, 104, 9),
(105, 105, 9),
(106, 106, 9),
(107, 107, 20),
(108, 108, 51),
(109, 109, 51),
(110, 110, 53),
(111, 111, 54),
(112, 112, 9),
(113, 113, 9),
(114, 114, 9),
(115, 115, 9),
(116, 116, 24),
(117, 117, 24),
(118, 118, 9),
(119, 119, 61),
(120, 120, 61),
(121, 121, 61),
(122, 122, 61),
(123, 123, 61),
(124, 124, 61),
(125, 125, 61),
(126, 126, 61),
(127, 127, 61),
(128, 128, 8),
(129, 129, 8),
(130, 130, 8),
(131, 131, 9),
(132, 132, 9),
(133, 133, 9),
(134, 134, 9),
(135, 135, 9),
(136, 136, 9),
(137, 137, 9),
(138, 138, 9),
(139, 139, 9),
(140, 140, 9),
(141, 141, 12),
(142, 142, 12),
(143, 143, 9),
(144, 144, 9),
(145, 145, 25),
(146, 146, 25),
(147, 147, 8),
(148, 148, 12),
(149, 149, 13),
(150, 150, 9),
(151, 151, 20),
(152, 152, 9),
(153, 153, 8),
(154, 154, 9),
(155, 155, 77),
(156, 156, 77),
(157, 157, 77),
(158, 158, 77),
(159, 159, 77),
(160, 160, 77),
(161, 161, 77),
(162, 162, 77),
(163, 163, 77),
(164, 164, 77),
(165, 165, 77),
(166, 166, 77),
(167, 167, 77),
(168, 168, 77),
(169, 169, 77),
(170, 170, 77),
(171, 171, 77),
(172, 172, 77),
(173, 173, 77),
(174, 174, 77),
(175, 175, 77),
(176, 176, 77),
(177, 177, 77);");


/**
 * Если template_vars пустой, формы берутся из admin_translates
 * 
// добавляем шаблоны печатных документов
//print_template_warranty
db()->query("UPDATE {template_vars_strings} as s "
          ."LEFT JOIN {template_vars} as t ON t.id = s.var_id "
          ."SET s.text = ? "
          ."WHERE s.lang = 'kiev' AND t.var = 'print_template_warranty'", array(lq('print_template_warranty')));
//print_template_check
db()->query("UPDATE {template_vars_strings} as s "
          ."LEFT JOIN {template_vars} as t ON t.id = s.var_id "
          ."SET s.text = ? "
          ."WHERE s.lang = 'kiev' AND t.var = 'print_template_check'", array(lq('print_template_check')));
//print_template_invoice
db()->query("UPDATE {template_vars_strings} as s "
          ."LEFT JOIN {template_vars} as t ON t.id = s.var_id "
          ."SET s.text = ? "
          ."WHERE s.lang = 'kiev' AND t.var = 'print_template_invoice'", array(lq('print_template_invoice')));
//print_template_act
db()->query("UPDATE {template_vars_strings} as s "
          ."LEFT JOIN {template_vars} as t ON t.id = s.var_id "
          ."SET s.text = ? "
          ."WHERE s.lang = 'kiev' AND t.var = 'print_template_act'", array(lq('print_template_act')));
//print_template_sale_warranty
db()->query("UPDATE {template_vars_strings} as s "
    ."LEFT JOIN {template_vars} as t ON t.id = s.var_id "
    ."SET s.text = ? "
    ."WHERE s.lang = 'kiev' AND t.var = 'print_template_sale_warranty'", array(lq('print_template_sale_warranty')));
//print_template_waybill
db()->query("UPDATE {template_vars_strings} as s "
    ."LEFT JOIN {template_vars} as t ON t.id = s.var_id "
    ."SET s.text = ? "
    ."WHERE s.lang = 'kiev' AND t.var = 'print_template_waybill'", array(lq('print_template_waybill')));

*/

db()->query('SET FOREIGN_KEY_CHECKS=1');

db()->query("TRUNCATE TABLE {tags}");
db()->query("INSERT IGNORE INTO {tags} (id, title, color)
            VALUES
                (1, 'VIP', '#3F48CC' ),
                (2, 'regular', '#22B14C' ),
                (3, 'discount', '#B5E61D' ),
                (4, 'blacklist', '#000000' ),
                (5, '-5%',  '#C3C3C3' ),
                (6, '-10%', '#C3C3C3' ),
                (7, '-20%', '#C3C3C3' ),
                (8, '-30%', '#C3C3C3' )", array());

db()->query("TRUNCATE TABLE {migrations}");
db()->query("INSERT IGNORE INTO {migrations} (migration, batch)
            VALUES
('2016_03_03_085457_update_users', 1),
('2016_03_09_094135_add_goods_extended', 2),
('2016_03_22_140618_tags', 3),
('2016_03_25_082910_add_total_as_sum_to_orders', 4),
('2016_03_25_121307_add_cashless_to_orders', 5),
('2016_03_29_081346_users_ratings', 5),
('2016_03_29_135756_users_sms_code', 5),
('2016_03_31_081346_cashboxes_users', 5)
                ", array());

db()->query("TRUNCATE TABLE {crm_referers}");
db()->query("
    INSERT INTO {crm_referers} (`id`, `name`, `group_id`) VALUES
    (1, '".lq('Google Adwords')."', 1),
    (2, '".lq('Google Organic')."', 3),
    (3, '".lq('Yandex Direct')."', 1),
    (4, '".lq('Yandex Organic')."', 3),
    (5, '".lq('VK')."', 3),
    (6, '".lq('VK Ad')."', 1),
    (7, '".lq('Twitter')."', 3),
    (8, '".lq('Forum, Blog')."', 3),
    (9, '".lq('Facebook')."', 3),
    (10, '".lq('Facebook Ad')."', 1),
    (11, '".lq('(Direct)')."', 3),
    (12, '".lq('Other')."', 3),
    (13, '".lq('Email')."', 3),
    (14, '".lq('Youtube')."', 3),
    (15, '".lq('Other organic')."', 3),
    (16, '".lq('Тизер')."', 4);
");

db()->query("TRUNCATE TABLE {warehouses_types}");
db()->query("
    INSERT INTO {warehouses_types} (`id`, `name`, `user_id`, `date_add`, `icon`) VALUES
    (1, '".lq('Сервисный центр')."', 10, NOW(), 'fa fa-home'),
    (2, '".lq('Точка приема-выдачи')."', 10, NOW(), 'fa fa-flag'),
    (3, '".lq('Курьер')."', 10, NOW(), 'fa fa-arrows');
");