<?php

if (!isLocalRequest()) {
	header("HTTP/1.0 404 Not Found");
	exit;
}

/**
 *  Проверка локальности выполнения запроса (предотвращение вызова скрипта GET запросом не из крона)
 * @return bool
 */
function isLocalRequest()
{
	return $_SERVER['SERVER_ADDR'] == $_SERVER['REMOTE_ADDR'];
}

echo "<meta http-equiv='Content-Type' content='text/html; charset=utf-8'>";

include 'inc_config.php';
include 'inc_func.php';
include 'mail.php';
include 'configs.php';

set_time_limit(14400); // 4 часа

$date_begin = date("Y-m-d H:i:s");

$error = '';

$all_configs = all_configs();

$function = isset($_GET['act']) ? trim($_GET['act']) : '';
if (function_exists($function)) {
    try {
        $error = $function($_GET);
    } catch (Exception $e) {
        $error = $e->getMessage();
        send_mail('kv@fon.in.ua', 'Ошибка крон restore', $e->getMessage());
    }
} else {
    $error = 'Fatal error: Call to undefined function ' . $function . '() in ' . $all_configs['path'] . 'cron.php on line 22';
}

function monitoring_del_old_data($params = array()){
    global $all_configs;
    $db = $all_configs['db'];
    
    $interval = 7; // все что больше 7 дней удаляем
    
    $db->query("DELETE FROM {monitoring_diff_history} WHERE date < DATE_ADD(NOW(), INTERVAL -?i DAY)", array($interval));
    $db->query("DELETE FROM {monitoring_data} WHERE date < DATE_ADD(NOW(), INTERVAL -?i DAY)", array($interval));
}

function monitoring_start_all_processes(){
    global $all_configs;
    $db = $all_configs['db'];
    $sites = $db->query("SELECT * FROM {monitoring}")->assoc();
    
    $qty = 0;
    $site_stream = array();
    $qty_all = 0;
    $sites_qty = count($sites);
    $curl = array();
    foreach($sites as $site){
        $qty_all ++;
        $qty ++;
        $site_stream[$site['id']] = $site;
        if($qty == 3 || $qty_all == $sites_qty){
            $postvars = http_build_query(array(
               'sites' => $site_stream
            ));
            $curl[$qty_all] = curl_init();
            curl_setopt($curl[$qty_all], CURLOPT_URL, 'http://'.$_SERVER['HTTP_HOST'].$all_configs['prefix'].'monitoring_service.php');
            curl_setopt($curl[$qty_all], CURLOPT_POSTFIELDS, $postvars);
            $qty = 0;
            $site_stream = array();
        }
    }
    $mh = curl_multi_init();
    foreach($curl as $ch){
        curl_multi_add_handle($mh, $ch);
    }
    curl_multi_exec($mh, $still_running);
}

function monitoring_get_links($params){
    
}

/*// Уведомлять менеджера об отсутствии новых записей в статусе заказа более Х дней.
//cron.php?act=orders_comments&days=10
function orders_comments($params)
{
    global $all_configs;

    $days = isset($params['days']) ? intval($params['days']) : $all_configs['settings']['orders_comments_days'];

    $data = $all_configs['db']->query('SELECT o.id, o.manager, oc.date_add FROM {orders} as o
        LEFT JOIN (SELECT order_id, MAX(date_add) as date_add
        FROM {orders_comments} WHERE private=0 GROUP BY order_id) as oc ON oc.order_id=o.id
        WHERE (oc.order_id IS NULL || (oc.order_id IS NOT NULL && oc.date_add < DATE_ADD(NOW(), INTERVAL -?i DAY)))
          AND status NOT IN (?li) AND o.manager>?i GROUP BY o.id',
    array($days, $all_configs['configs']['order-statuses-nocomments'], 0))->assoc('id');

    if ($data) {
        $messages = new Mailer($all_configs);
        foreach ($data as $d) {
            if ($d['id'] > 0 && $d['manager'] > 0) {
                $href = $all_configs['manageprefix'] . 'orders/create/' . $d['id'];
                $content = 'Уведомление об отсутствии новых записей в статусе заказа ';
                $content .= '<a href="' . $href . '">№' . $d['id'] . '</a> более ' . $days . ' дней';
                $messages->send_message($content, 'Отсутствие новых записей в заказе', $d['manager'], 1);
            }
        }
    }
}*/

// Уведомлять менеджера о том что осталось 7 дней, осталось 3 дня,
// остался 1 день до конца 14ти дневного срока гарантийного обслуживания (для гарантийных аппаратов Яблока).
//cron.php?act=warranties_left_days&days=1,3,7
function warranties_left_days($params)
{
    global $all_configs;

    $days = isset($params['days']) ? $params['days'] : $all_configs['settings']['warranties_left_days'];
    $days = array_unique(array_filter(explode(',', $days)));

    if (count($days) > 0) {
        $data = $all_configs['db']->query('SELECT manager, date_add, id FROM {orders}
            WHERE repair=?i AND manager>0 AND date_add > DATE_ADD(NOW(), INTERVAL -?i DAY) AND status NOT IN (?li)',
            array(1, 14, $all_configs['configs']['order-statuses-closed']))->assoc('id');

        if ($data) {
            $messages = new Mailer($all_configs);
            foreach ($data as $d) {
                $day = 14 - date('d', time() - strtotime($d['date_add']));

                if ($d['id'] > 0 && $d['manager'] > 0 && in_array($day, $days)) {
                    $href = $all_configs['manageprefix'] . 'orders/create/' . $d['id'];
                    $day = $day . ($day == 1 ? ' день' : ($day < 5 ? ' дня' : ' дней'));
                    $content = 'Уведомление о том что осталось ' . $day . ' до конца 14ти дневного срока гарантийного обслуживания заказа ';
                    $content .= '<a href="' . $href . '">№' . $d['id'] . '</a>';
                    $messages->send_message($content, '14ти дневный срок гарантии', $d['manager'], 1);
                }
            }
        }
    }
}

// Уведомлять менеджера по закупкам о нарушении оборачиваемости
// (указываем максимальный срок, который изделие может находится на складе)
//cron.php?act=unsold_items&days=10
function unsold_items($params)
{
    /*global $all_configs;

    $days = isset($params['days']) ? intval($params['days']) : $all_configs['settings']['unsold_items_days'];

    $data = $all_configs['db']->query('SELECT serial, id as item_id FROM {warehouses_goods_items}
        WHERE date_add < DATE_ADD(NOW(), INTERVAL -?i DAY) AND order_id IS NULL',
        array($days))->assoc();

    if ($data) {
        $messages = new Mailer($all_configs);
        foreach ($data as $d) {
            $content = 'Уведомление о нарушении оборачиваемости изделия ';
            $content .= suppliers_order_generate_serial($d, true, true);
            $messages->send_message($content, 'Нарушена оборачиваемость', 'edit-suppliers-orders', 1);
        }
    }*/
}
//генерация кода на скидук клиентам
//cron.php?act=generate_codes
function generate_codes() {
    global $all_configs;
    
    $a = rand(1, 9);
    $b = rand(0, 9);
    
    $all_configs['db']->query(
                    'UPDATE {settings} SET `value` = ? WHERE `name` = ?',
                    array($a . $a .$b . $b, 'price_code_client'));
    
    $all_configs['db']->query(
                    'UPDATE {settings} SET `value` = ? WHERE `name` = ?',
                    array(str_repeat(rand(0, 9), 4), 'price_code_pseudoclient'));
}

// автоматическое добавление комментарий
//cron.php?act=auto_orders_comments
function auto_orders_comments($params)
{
    global $all_configs;

    $days = array( // ключ должен быть цифрой и больше 0
        1 => array('msg' => 'Запчасть ожидает консолидации', 'interval' => array(2, 3, 4)),
        2 => array('msg' => 'Запчасть консолидирована в грузи и ожидает отправки', 'interval' => array(2, 3, 4)),
        3 => array('msg' => 'Запчасть покинута склад поставщика', 'interval' => array(2, 3, 4)),
        4 => array('msg' => 'Транзит к пункту назначения', 'interval' => array(2)),
        5 => array('msg' => 'Борт прибыл, в стадии разгрузки', 'interval' => array(5)),
        6 => array('msg' => 'Груз передан для прохождения контроля', 'interval' => array(2, 3)),
        7 => array('msg' => 'Задержан на этапе оформления', 'interval' => array(5, 6)),
        8 => array('msg' => 'Завершена проверка, груз передан в пункт назначения', 'interval' => array(6)),
        9 => array('msg' => 'Запчасть оприходована на склад', 'interval' => array(3)),
    );

    end($days);
    $last = key($days);
    reset($days);
    $first = key($days);

    $data = $all_configs['db']->query('SELECT o.id, oc.date_add, oc.last FROM {orders} as o
            LEFT JOIN (SELECT MAX(auto) as last, order_id, date_add FROM {orders_comments}
            WHERE auto>=?i AND auto<=?i AND private=?i GROUP BY order_id) as oc ON oc.order_id=o.id WHERE o.status=?i',
        array($first, $last, 0, $all_configs['configs']['order-status-waits']))->assoc('id');

    if ($data) {
        foreach ($data as $d) {
            $text = $interval = $date = null;
            $auto = (intval($d['last']) + 1);

            if (isset($days[$auto]) && (intval($d['last']) == 0 || (intval($d['last']) < $last))) {
                $text = $days[$auto]['msg'];
                $interval = $days[$auto]['interval'][array_rand($days[$auto]['interval'])];
                $date = strtotime($d['date_add']) + ($interval * 60 * 60 * 24) < time() ? false : true;
            }

            if ($d['id'] > 0 && $text && $interval && $date) {
                $date_add = date("Y-m-d H:i:s", (time() - rand(1, (60*60*4)))); // сейчас - rand(0 - 4 часа)
                $all_configs['db']->query(
                    'INSERT INTO {orders_comments} (text, order_id, auto, date_add) VALUES (?, ?i, ?i, ?)',
                    array($text, $d['id'], $auto, $date_add));
            }
        }
    }
    
    

    
}

// обновление количества товаров и закупочной суммы по складам и по товарам
//cron.php?act=warehouses_goods_remains
function warehouses_goods_remains($params)
{
    global $all_configs;

    // проверяем на включенность использования системы учета
    if ($all_configs['configs']['erp-use'] == false)
        return;

    // чистим табличку количества товаров на складе и ихнюю сумму
    $all_configs['db']->query('TRUNCATE TABLE {warehouses_goods_amount}');

    // добавляем сумму товаров по складам и ихнее количество
    $all_configs['db']->query('INSERT INTO {warehouses_goods_amount} (goods_id, wh_id, qty, amount)
        SELECT i.goods_id, w.id, COUNT(i.goods_id) as qty, SUM(i.price) as amount
        FROM {warehouses} as w LEFT JOIN {warehouses_goods_items} as i ON w.id=i.wh_id
        WHERE w.id=i.wh_id AND i.goods_id > 0
        GROUP BY w.id, i.goods_id ON DUPLICATE KEY UPDATE qty=VALUES(qty), amount=VALUES(amount)');

    // ставим всем товаром 0 на складе и 0 в магазине
    $all_configs['db']->query('UPDATE {goods} SET qty_wh=0, qty_store=0, wait=null');

    // обновляем товарам количество на складах
    $all_configs['db']->query('UPDATE {goods} g LEFT JOIN(SELECT i.goods_id, COUNT(i.goods_id) as qty_wh
        FROM {warehouses} as w, {warehouses_goods_items} as i WHERE w.id=i.wh_id AND w.consider_all=1
        GROUP BY i.goods_id) as v ON g.id=v.goods_id SET g.qty_wh=v.qty_wh WHERE g.id IS NOT NULL', array());

    // обновляем товарам свободное количество в магазине
    $all_configs['db']->query('UPDATE {goods} g LEFT JOIN(SELECT i.goods_id,
        COUNT(DISTINCT i.id) - COUNT(DISTINCT l.id) as qty_store FROM {warehouses} as w, {warehouses_goods_items} as i
        LEFT JOIN {orders_suppliers_clients} as l ON i.supplier_order_id=l.supplier_order_id AND l.order_goods_id IN
        (SELECT id FROM {orders_goods} WHERE item_id IS NULL) WHERE w.consider_store=1 AND i.wh_id=w.id AND i.order_id IS NULL
        GROUP BY i.goods_id) as v ON g.id=v.goods_id SET g.qty_store=v.qty_store,
        g.foreign_warehouse=IF(g.foreign_warehouse_auto=1, IF(v.qty_store>0, 0, 1), g.foreign_warehouse)
        WHERE g.id IS NOT NULL', array());

    // обновляем дату ожидания у всех товаров
    $all_configs['db']->query('UPDATE {goods} ug
        LEFT JOIN(SELECT o.goods_id, o.date_wait, IF (o.date_wait > NOW(), 0, 1) order_date
        FROM {contractors_suppliers_orders} as o, {goods} as g
        WHERE (o.count_come>o.count_debit OR o.count_come is null OR o.count_come=0) AND g.id=o.goods_id AND
          g.qty_store=0 ORDER BY order_date, o.date_wait LIMIT 1) as w ON w.goods_id=ug.id
        SET ug.wait=w.date_wait WHERE ug.id IS NOT NULL', array());

    $all_configs['db']->query('INSERT INTO {goods_amount}
        (goods_id, qty, qty_store, qty_wh, qty_orders_store, qty_orders_wh, qty_wait, qty_request, date)
        SELECT i.goods_id,
        COUNT(DISTINCT i.id) as qty,
        COUNT(DISTINCT IF(w.consider_store=1, i.id, null)) as qty_store,
        COUNT(DISTINCT IF(w.consider_all=1, i.id, null)) as qty_wh,
        COUNT(DISTINCT IF(w.consider_store=1 AND i.order_id IS NOT NULL, i.id, null)) as qty_orders_store,
        COUNT(DISTINCT IF(w.consider_all=1 AND i.order_id IS NOT NULL, i.id, null)) as qty_orders_wh,
        IFNULL(o.qty_wait, 0) as qty_wait,
        COUNT(DISTINCT l.id) as qty_request,
        date(NOW()) as date

        FROM {warehouses} as w, {warehouses_goods_items} as i
        LEFT JOIN {orders_suppliers_clients} as l ON i.supplier_order_id=l.supplier_order_id
          AND l.order_goods_id IN (SELECT id FROM {orders_goods} WHERE item_id IS NULL)
        LEFT JOIN (SELECT goods_id, SUM(IF(count_come>0, count_come, count)) as qty_wait
        FROM {contractors_suppliers_orders} WHERE avail=1 AND count_debit=0 GROUP BY goods_id) as o ON o.goods_id=i.goods_id
        WHERE i.wh_id=w.id GROUP BY i.goods_id

        ON DUPLICATE KEY UPDATE qty=VALUES(qty), qty_store=VALUES(qty_store), qty_wh=VALUES(qty_wh), qty_orders_store=VALUES(qty_orders_store),
        qty_orders_wh=VALUES(qty_orders_wh), qty_wait=VALUES(qty_wait), qty_request=VALUES(qty_request)', array());
}

// уведомление о просроченном заказе поставщику (раз в день после 00:00)
//cron.php?act=overdue_orders_suppliers
function overdue_orders_suppliers($params)
{
    global $all_configs;

    $orders = $all_configs['db']->query('SELECT id, user_id, parent_id FROM {contractors_suppliers_orders} as o
            WHERE o.date_wait<NOW() AND user_id IS NOT NULL AND confirm<>?i AND unavailable<>?i AND avail=?i',
        array(1, 1, 1))->assoc();

    if ($orders) {
        $messages = new Mailer($all_configs);

        foreach ($orders as $order) {
            $id = $order['id'];
            //if ($order['parent_id'] != $order['id']) $id = $order['parent_id'];
            $href = $all_configs['manageprefix'] . 'orders/edit/' . $id . '#create_supplier_order';
            $content = 'Просрочен заказ <a href="' . $href . '">№' . $id . '</a>';
            $messages->send_message($content, 'Просрочен заказ', $order['user_id'], 1);
        }
    }
}

// обновление суммы в кассах и зароботок менеджерами за текущий день
//cron.php?act=amount_by_day
function amount_by_day($params)
{
    global $all_configs;

    // обновление суммы в кассах учитывая в балансе
    $amounts = $all_configs['db']->query(
        'SELECT SUM(cr.amount) as `sum`, cr.currency, d.id FROM {cashboxes} as cb, {cashboxes_currencies} as cr
        LEFT JOIN {cashboxes_amount_by_day} as d ON d.cashboxes_currency_id=cr.currency AND DATE(d.date_add) = CURDATE()
        WHERE cr.cashbox_id=cb.id AND cb.avail_in_balance=1 AND cr.currency>0 GROUP BY cr.currency', array())->assoc();

    if ($amounts) {
        foreach ($amounts as $amount) {
            if ($amount['id'] > 0) {
                $all_configs['db']->query('UPDATE {cashboxes_amount_by_day} SET amount=?i WHERE id=?i',
                    array($amount['sum'], $amount['id']));
            } else {
                $all_configs['db']->query(
                    'INSERT INTO {cashboxes_amount_by_day} (amount, cashboxes_currency_id) VALUES (?i, ?i)',
                    array($amount['sum'], $amount['currency']));
            }
        }
    }

    /*// история продаж по менеджерам

    // учитывать оплату за доставку и за комиссию в марже
    $query = '';
    if ($all_configs['configs']['manage-prefit-commission'] == false)
        $query = $all_configs['db']->makeQuery('t.chain_id>0 AND', array());

    $profits = $all_configs['db']->query('SELECT h.goods_user_id,
              SUM(IF(t.transaction_type=2, t.value_to, 0)) as `to`,
              SUM(IF(t.transaction_type=1, t.value_from, 0)) as `from`,
              (SELECT SUM(i.price * (o.course_value / 100)) FROM {warehouses_goods_items} as i, {orders} as o
                WHERE i.id=t.item_id AND t.client_order_id=o.id AND t.client_order_id=i.order_id) as `purchase`
            FROM {cashboxes_transactions} as t, {chains_headers} as h
            WHERE ?query DATE(t.date_add)=CURDATE() AND t.item_id=h.item_id',
        array($query))->assoc();

    if ($profits) {
        foreach ($profits as $profit) {
            if ($profit['goods_user_id'] == 0)
                continue;

            $margin = $profit['to'] - $profit['purchase'];

            if ($profit['from'] > 0) {
                $margin = $profit['to'] - $profit['from'];
            }

            $u_p_id = $all_configs['db']->query('SELECT id FROM {users_profit_by_day} WHERE user_id=?i
                    AND DATE(date_add) = CURDATE()', array($profit['goods_user_id']))->el();

            if ($u_p_id > 0) {
                $all_configs['db']->query('UPDATE {users_profit_by_day} SET amount=?i WHERE id=?i',
                    array($margin, $u_p_id));
            } else {
                $all_configs['db']->query('INSERT INTO {users_profit_by_day} (amount, user_id) VALUES (?i, ?i)',
                    array($margin, $profit['goods_user_id']));
            }
        }
    }*/
}

// уведомлять об остатке X или менее единиц (раз в день)
//cron.php?act=balance_goods
function balance_goods($params)
{
    global $all_configs;

    $goods = $all_configs['db']->query('
        SELECT n.balance, n.by_balance, n.user_id, g.title, g.qty_store, n.goods_id, n.id FROM {users_notices} as n
        RIGHT JOIN {goods} as g ON g.id=n.goods_id AND g.avail=1 AND g.qty_store<=n.balance
        WHERE n.balance>=0 AND n.by_balance=1 AND n.goods_id>0 #AND n.last_balance_send<>g.qty_store', array())->assoc();

    if ($goods && count($goods) > 0) {
        foreach ($goods as $product) {
            $messages = new Mailer($all_configs);
            $content = 'Осталось ' . $product['qty_store'] . ' тов. в наличии. Товар - ';
            $content .= '<a href="' . $all_configs['manageprefix'] . 'products/create/' . $product['goods_id'] . '">';
            $content .= htmlspecialchars($product['title']) . '</a>. ';
            $messages->send_message($content, 'Остаток товара', $product['user_id'], 1);

            //$all_configs['db']->query('UPDATE {users_notices} SET last_balance_send=?i WHERE id=?i',
            //    array($product['qty_store'], $product['id']));
        }
    }
}

// уведомление о напоминании (каждых 1-5 минут)
//cron.php?act=alarm
function alarm($params)
{
    global $all_configs;

    $alarms = $all_configs['db']->query('SELECT * FROM {alarm_clock} WHERE send=0 AND date_alarm<NOW()', array())->assoc();

    if ($alarms) {
        include_once $all_configs['path'] . 'mail.php';
        $messages = new Mailer($all_configs);

        foreach ($alarms as $alarm) {
            $content = htmlspecialchars($alarm['text']);
            if ($alarm['order_id'] > 0) {
                $content .= ' по заказу <a href="' . $all_configs['prefix'] . 'orders/create/' . $alarm['order_id'] . '">№' . $alarm['order_id'] . '</a>';
            }
            $messages->send_message($content, 'Напоминание', ($alarm['for_user_id'] > 0 ? $alarm['for_user_id'] : 'alarm'), 1, '', 1);

            $all_configs['db']->query('UPDATE {alarm_clock} SET send=1 WHERE id=?i', array($alarm['id']));
        }
    }
}

/**
 * генерирование/разгенерирование серийника заказа поставщика
 * */
function suppliers_order_generate_serial($order, $generate = true, $link = false, $class = '')
{
    global $all_configs;

    if ($generate == true) {
        $serial = trim($order['serial']);

        if (mb_strlen($serial, 'UTF-8') == 0) {
            if ($order['item_id'] > 0) {
                $serial = $all_configs['configs']['erp-serial-prefix'] . str_pad('', (7 - strlen($order['item_id'])), 0) . $order['item_id'];
            } elseif (array_key_exists('last_item_id', $order) && $order['last_item_id'] > 0) {
                $order = $all_configs['db']->query(
                    'SELECT i.id as item_id, i.serial FROM {warehouses_goods_items} as i WHERE i.id=?i',
                    array($order['last_item_id']))->row();

                return suppliers_order_generate_serial($order, $generate, $link, 'muted');
            }
        }
        $serial = htmlspecialchars(urldecode($serial));
    } else {
        $serial = trim($order['serial']);
        //if ($all_configs['configs']['erp-serial-prefix'] == substr($serial, 0, strlen($all_configs['configs']['erp-serial-prefix']))) {
        if (preg_match('/^(' . $all_configs['configs']['erp-serial-prefix'] . ')([0-9]{' . $all_configs['configs']['erp-serial-count-num'] . '})$/', $serial) == 1) {
            $serial = preg_replace("|[^0-9]|i", "", $serial);
            $serial = intval($serial);
        } else {
            $serial = urldecode($order['serial']);
        }
    }

    if ($link == true && $generate == true)
        return '<a class="' . $class . '" href="' . $all_configs['manageprefix'] . 'warehouses?serial=' . $serial . '#show_items">' . $serial . '</a>';
    else
        return $serial;
}


/**
 * Установка режима нормальных/аварийных телефонов
 * @param $params array
 */
function alert_set_phone($params) {
	global $all_configs;

	if (isset($params['set'])) {
		switch ($params['set']) {
			case 0:
				$all_configs['db']->query('UPDATE {settings} SET value=? WHERE name=?',array('0','content_alarm'));
				break;
			case 1:
				$all_configs['db']->query('UPDATE {settings} SET value=? WHERE name=?',array('1','content_alarm'));
				break;
		}
	}
}

// сохраняем статитстику заказов /manage/orders#orders_manager
// каждый день в 23:59
function orders_manager_stats(){
    global $all_configs;
    
    $ifauth = null;
    require $all_configs['path'].'manage/modules/orders/index.php';
    $orders_class = new orders($all_configs, false);
    
    $orders = $orders_class->get_orders_for_orders_manager();
    $save_query = array();
    foreach($orders as $order){
        $status = $order['status'];
        if($orders_class->check_if_order_fail_in_orders_manager($order)){
            $status = -1;
        }
        $save_query[] = $all_configs['db']->makeQuery("(?i,NOW(),?i,?i,?i)", 
                                                        array($status,$order['id'],$order['manager'],$order['group_id']));
    }
    if($save_query){
        $all_configs['db']->query("INSERT IGNORE INTO {orders_manager_history}(status,date,`order`,manager,group_id) "
                                 ."VALUES ?q", array(implode(',',$save_query)));
    }
}

// имитация конфига
function all_configs()
{
    global $db, $prefix, $path;

    $configs = Configs::get();
    $settings = $db->query("SELECT name, value FROM {settings}", array())->vars();

    return array(
        'db' => $db,
        'prefix' => $prefix,
        'manageprefix' => $prefix . 'manage/',
        'path' => $path,
        'managepath' => $path . 'manage/',
        'settings' => $settings,
        'configs' => $configs,
    );
}


$url = trim((string)$_SERVER['REQUEST_URI']);
$errors = trim((string)is_array($error) ? implode(' ', $error) : $error);

$all_configs['db']->query('INSERT INTO {cron_history} (date_begin, url, errors) VALUES (?, ?, ?)',
    array($date_begin, $url, $errors));