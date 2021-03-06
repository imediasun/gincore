<?php

include 'inc_config.php';
include 'inc_func.php';
include 'inc_settings.php';
require_once __DIR__ . '/Core/Log.php';
require_once __DIR__ . '/Core/Response.php';

global $all_configs;

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['id'] == 0) {
    Response::json(array(
        'message' => l('Сессия была прервана, выполните вход в Gincore'),
        'error' => true,
        'reload' => true,
    ));
    return false;
}

$user_id = $_SESSION['id'];

// помечаем
if (isset($_POST['act']) && $_POST['act'] == 'marked-object') {
    if (!isset($_POST['object_id']) || $_POST['object_id'] == 0 || !isset($_POST['type']) || empty($_POST['type'])) {
        Response::json(array('message' => l('Побробуйте еще раз'), 'error' => true));
    }

    $ar = $all_configs['db']->query('DELETE FROM {users_marked} WHERE user_id=?i AND object_id=?i AND type=?',
        array($_SESSION['id'], $_POST['object_id'], trim($_POST['type'])))->ar();

    if ($ar) {
        $count_marked = $all_configs['db']->query('SELECT COUNT(id) FROM {users_marked}
              WHERE user_id=?i AND type=?',
            array($_SESSION['id'], trim($_POST['type'])))->el();

        Response::json(array('message' => l('Успешно'), 'count-marked' => $count_marked));
    }

    $all_configs['db']->query('INSERT INTO {users_marked} (user_id, object_id, type) VALUES (?i, ?i, ?)',
        array($_SESSION['id'], $_POST['object_id'], trim($_POST['type'])));

    $count_marked = $all_configs['db']->query('SELECT COUNT(id) FROM {users_marked} WHERE user_id=?i AND type=?',
        array($_SESSION['id'], trim($_POST['type'])))->el();

    Response::json(array('message' => l('Успешно'), 'count-marked' => $count_marked));
}

if (isset($_POST['act']) && $_POST['act'] == 'global-typeahead') {
    $data = array();

    $limit = isset($_POST['act']) && $_POST['act'] > 0 && $_POST['act'] < 150 ? intval($_POST['limit']) : 30;
    $is_double = isset($_POST['double']) && $_POST['double'] ? true : false;

    if (isset($_POST['query']) && !empty($_POST['query']) && isset($_POST['table']) && !empty($_POST['table'])) {

        $s = str_replace(array("\xA0", '&nbsp;', ' '), '%',
            trim(preg_replace('/ {1,}/', ' ', mb_strtolower($_POST['query'], 'UTF-8'))));

        if ($_POST['table'] == 'categories' || $_POST['table'] == 'categories-last' || $_POST['table'] == 'categories-goods') {
            $query = $all_configs['db']->makeQuery('NOT cg.url in (?l)', array(
                array(
                    'recycle-bin',
                    'prodazha',
                    'spisanie',
                    'vozvrat-postavschiku',
                )
            ));
            $join = '';
            if (isset($_POST['fix']) && $_POST['fix'] > 0) {
                $query = $all_configs['db']->makeQuery('?query AND cg.id IN (?li)',
                    array($query, array_values(get_childs_categories($all_configs['db'], $_POST['fix']))));
            }
            if ($_POST['table'] == 'categories-last') {
                $_POST['table'] = 'categories';
                $query = $all_configs['db']->makeQuery('?query AND scg.id IS NULL AND cg.avail=1', array($query));
                $join = $all_configs['db']->makeQuery('LEFT JOIN {categories} as scg ON scg.parent_id=cg.id', array());
            }
            if ($_POST['table'] == 'categories-goods') {
                $_POST['table'] = 'categories';
                $query = $all_configs['db']->makeQuery('?query AND scg.id IS NULL AND cg.id NOT IN (?li) AND cg.avail=1',
                    array(
                        $query,
                        array(
                            $all_configs['configs']['erp-co-category-write-off'],
                            $all_configs['configs']['erp-co-category-return'],
                            $all_configs['configs']['erp-co-category-sold']
                        )
                    ));
                $join = $all_configs['db']->makeQuery('LEFT JOIN {categories} as scg ON scg.parent_id=cg.id', array());
            }
            $data = $all_configs['db']->query('SELECT cg.id, cg.title FROM {categories} as cg ?query
                    WHERE cg.deleted=0 AND cg.title LIKE "%?e%" AND ?query LIMIT ?i',
                array($join, $s, $query, $limit))->assoc();
        }
        if ($_POST['table'] == 'categories-parent') {
            $query = $all_configs['db']->makeQuery('NOT cg.url in (?l)', array(
                array(
                    'recycle-bin',
                    'prodazha',
                    'spisanie',
                    'vozvrat-postavschiku',
                )
            ));
            $data = $all_configs['db']->query('
            SELECT cg.id, cg.title 
            FROM {categories} as cg
            LEFT JOIN (SELECT DISTINCT parent_id FROM {categories}) AS sub ON cg.id = sub.parent_id
            WHERE cg.deleted=0 AND cg.title LIKE "%?e%" AND cg.avail=1 AND NOT (sub.parent_id IS NULL OR sub.parent_id = 0) AND ?query LIMIT ?i
            ', array($s, $limit, $query))->assoc();
        }
        if ($_POST['table'] == 'users') {
            $query = '';
            //if (isset($_POST['fix']) && $_POST['fix'] > 0) {}
            $data = $all_configs['db']->query('SELECT u.id, GROUP_CONCAT(COALESCE(u.fio, ""), ", ", COALESCE(u.email, ""),
                      ", ", COALESCE(u.phone, ""), ", ", COALESCE(u.login, "") separator ", " ) as title FROM {users} as u ?query
                    WHERE (u.fio LIKE "%?e%" OR u.email LIKE "%?e%" OR u.phone LIKE "%?e%" OR u.login LIKE "%?e%") GROUP BY u.id LIMIT ?i',
                array($query, $s, $s, $s, $s, $limit))->assoc();
        }
        if ($_POST['table'] == 'goods') {
            $query = '';
            if (isset($_POST['fix']) && $_POST['fix'] > 0) {
                $query = $all_configs['db']->makeQuery('RIGHT JOIN {category_goods} as cg ON
                        cg.category_id IN (?li) AND g.id=cg.goods_id',
                    array(array_values(get_childs_categories($all_configs['db'], $_POST['fix']))));
            }

            $query_title = 'g.title';

            $data = $all_configs['db']->query('SELECT g.id, ?q as title FROM {goods} as g ?query
                    WHERE (g.title LIKE "%?e%" OR g.vendor_code LIKE "%?e%") AND g.avail=?i GROUP BY g.id LIMIT ?i',
                array($query_title, $query, $s, $s, 1, $limit))->assoc();
        }
        if ($_POST['table'] == 'goods-goods' || $_POST['table'] == 'new-goods') {
            $query = '';
            if (isset($_POST['fix']) && $_POST['fix'] > 0) {
                $query = $all_configs['db']->makeQuery('RIGHT JOIN {category_goods} as cg ON
                        cg.category_id IN (?li) AND g.id=cg.goods_id',
                    array(array_values(get_childs_categories($all_configs['db'], $_POST['fix']))));
            }
            $data = $all_configs['db']->query('SELECT g.id, g.title, g.price, g.price_wholesale FROM {goods} as g ?query
                    WHERE (g.type IS NULL OR g.type=0) AND (g.title LIKE "%?e%"  OR g.vendor_code LIKE "%?e%") AND g.avail=?i GROUP BY g.id LIMIT ?i',
                array($query, $s, $s, 1, $limit))->assoc();
        }
        if ($_POST['table'] == 'goods-service') {
            $query = '';
            if (isset($_POST['fix']) && $_POST['fix'] > 0) {
                $query = $all_configs['db']->makeQuery('RIGHT JOIN {category_goods} as cg ON
                        cg.category_id IN (?li) AND g.id=cg.goods_id',
                    array(array_values(get_childs_categories($all_configs['db'], $_POST['fix']))));
            }
            $data = $all_configs['db']->query('SELECT g.id, g.title FROM {goods} as g ?query
                    WHERE g.type=1 AND (g.title LIKE "%?e%" OR g.vendor_code LIKE "%?e%") AND g.avail=?i GROUP BY g.id LIMIT ?i',
                array($query, $s, $s, 1, $limit))->assoc();
        }
        if ($_POST['table'] == 'clients') {
            if ($all_configs['configs']['can_see_client_infos']) {
                $title_query = $all_configs['db']->makeQuery('GROUP_CONCAT(COALESCE(c.fio, ""), ", ", COALESCE(c.email, ""),
                      ", ", COALESCE(c.phone, ""), ", ", COALESCE(p.phone, "") separator ", " ) as title');
            } else {
                $title_query = $all_configs['db']->makeQuery('GROUP_CONCAT(COALESCE(c.fio, "")) as title', array());
            }
            $data = $all_configs['db']->query('SELECT c.id,  ?query, c.fio, c.phone, c.tag_id, t.title as t_title, t.color as t_color
                    FROM {clients} as c
                    LEFT JOIN {clients_phones} as p ON p.client_id=c.id AND p.phone<>c.phone
                    LEFT JOIN {tags} as t ON t.id=c.tag_id
                    WHERE (c.email LIKE "%?e%" OR c.fio LIKE "%?e%" OR c.phone LIKE "%?e%" OR p.phone LIKE "%?e%") AND c.id<>?i
                    GROUP BY c.id LIMIT ?i',
                array($title_query, $s, $s, $s, $s, $all_configs['configs']['erp-write-off-user'], $limit))->assoc();
        }
        if ($_POST['table'] == 'fvalues') {
            $data = $all_configs['db']->query('SELECT value as title, id FROM {filter_value}
                    WHERE value LIKE "%?e%" LIMIT ?i',
                array($s, $limit))->assoc();
        }
        if ($_POST['table'] == 'fnames') {
            $data = $all_configs['db']->query('SELECT title, id FROM {filter_name}
                    WHERE title LIKE "%?e%" LIMIT ?i',
                array($s, $limit))->assoc();
        }
        if ($_POST['table'] == 'serials') {
            if (preg_match("/{$all_configs['configs']['erp-serial-prefix']}0*/", $s)) {
                list($prefix, $length) = prepare_for_serial_search($all_configs['configs']['erp-serial-prefix'], $s,
                    $all_configs['configs']['erp-serial-count-num']);
                $query = $all_configs['db']->makeQuery('id REGEXP "^?e[0-9]?e$"', array($prefix, "{0,{$length}}"));
            } else {
                $query = $all_configs['db']->makeQuery('id LIKE "%?e%"',
                    array(intval(preg_replace('/[^0-9]/', '', $s))));
            }

            $data = $all_configs['db']->query('SELECT id as item_id, serial FROM {warehouses_goods_items}
                    WHERE ((serial LIKE "%?e%" AND serial IS NOT NULL) OR (?query AND serial IS NULL) AND order_id IS NULL) LIMIT ?i',
                array($s, $query, $limit))->assoc();
            if ($data) {
                foreach ($data as $k => $v) {
                    $data[$k] = array('title' => suppliers_order_generate_serial($v), 'id' => $v['item_id']);
                }
            }
        }
        if ($_POST['table'] == 'not-bind-serials') {
            if (preg_match("/{$all_configs['configs']['erp-serial-prefix']}0*/", $s)) {
                list($prefix, $length) = prepare_for_serial_search($all_configs['configs']['erp-serial-prefix'], $s,
                    $all_configs['configs']['erp-serial-count-num']);
                $query = $all_configs['db']->makeQuery('id REGEXP "^?e[0-9]?e$"', array($prefix, "{0,{$length}}"));
            } else {
                $query = $all_configs['db']->makeQuery('id LIKE "%?e%"',
                    array(intval(preg_replace('/[^0-9]/', '', $s))));
            }

            $data = $all_configs['db']->query('SELECT id as item_id, serial, supplier_order_id FROM {warehouses_goods_items}
                    WHERE ((serial LIKE "%?e%" AND serial IS NOT NULL) OR (?query AND serial IS NULL) AND order_id IS NULL) LIMIT ?i',
                array($s, $query, $limit))->assoc();

            if (!empty($data)) {
                foreach ($data as $id => $item) {
                    // проверяем есть ли заявки на изделие
                    $count_free = $all_configs['db']->query('SELECT COUNT(DISTINCT i.id) - COUNT(DISTINCT l.id) as qty,
                GROUP_CONCAT(l.client_order_id) as orders FROM {warehouses} as w, {warehouses_goods_items} as i
                LEFT JOIN {orders_suppliers_clients} as l ON i.supplier_order_id=l.supplier_order_id AND l.order_goods_id IN
                (SELECT id FROM {orders_goods} WHERE item_id IS NULL)
                WHERE w.consider_store=1 AND i.wh_id=w.id AND i.order_id IS NULL AND i.supplier_order_id=?i
                GROUP BY i.goods_id', array($item['supplier_order_id']))->row();

                    $data[$id]['reserve'] = ($count_free && $count_free['qty'] < 1);
                }
            }

            if ($data) {
                foreach ($data as $k => $v) {
                    $data[$k] = array(
                        'title' => suppliers_order_generate_serial($v) . ($v['reserve'] ? ' ' . l('(бронь)') : ''),
                        'class' => $v['reserve'] ? 'reserved' : '',
                        'id' => $v['item_id']
                    );
                }
            }
        }
        if ($_POST['table'] == 'locations') {
            $data = $all_configs['db']->query('SELECT GROUP_CONCAT(w.title, " ", l.location) as title, l.id
                    FROM {warehouses_locations} as l, {warehouses} as w
                    WHERE w.id=l.wh_id GROUP BY l.id HAVING title LIKE "%?e%" LIMIT ?i',
                array($s, $limit))->assoc();
        }
    }

    Response::json($data);
}

$act = isset($_POST['act']) ? trim($_POST['act']) : (isset($_GET['act']) ? trim($_GET['act']) : '');
$data = array('state' => false);

// отмечаем что сообщение прочтено
if ($act == 'read-message') {
    if (!isset($_POST['mess_id']) || intval($_POST['mess_id']) == 0) {
        // если нет ид сообщения
        $data['msg'] = l('Произошла ошибка');
    } else {
        $data['state'] = true;
        $data['msg'] = l('Сообщение прочтено');
        $all_configs['db']->query('UPDATE {messages} SET is_read=1, date_read=NOW() WHERE id=?i',
            array(intval($_POST['mess_id'])));
        $data['qty'] = count_unread_messages();
    }
    Response::json($data);
}

// удаляем сообщение
if ($act == 'remove-message') {
    if (!isset($_POST['mess_id']) && !isset($_POST['type'])) {
        // если нет ид сообщения
        $data['msg'] = l('Произошла ошибка');
    } else {
        $query = '';
        if ($_POST['mess_id'] != 'all') {
            // by id
            $query = $all_configs['db']->makeQuery('AND id=?i', array(intval($_POST['mess_id'])));
        }
        $data['state'] = true;
        $data['msg'] = l('Успешно удалено');
        $data['qty'] = count_unread_messages();//
        $all_configs['db']->query('UPDATE {messages} SET remove=1 WHERE `type`=?i AND user_id_destination=?i ?query',
            array(intval($_POST['type']), $user_id, $query));
    }
    Response::json($data);
}

// удаление напоминания
if ($act == 'remove-alarm') {
    if (isset($_POST['id']) && $all_configs['oRole']->hasPrivilege('alarm')) {
        $data['state'] = true;
        $id = $all_configs['db']->query('DELETE FROM {alarm_clock} WHERE id=?i', array($_POST['id']));
    } else {
        $data['state'] = false;
        $data['msg'] = l('Напоминание не найдено');
    }
    Response::json($data);
}

// добавление напоминаний
if ($act == 'add-alarm') {
    $data = array('state' => true);

    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;
    if (!empty($_POST['users']) && is_array($_POST['users'])) {
        $id = 0;
        $text = isset($_POST['text']) ? trim($_POST['text']) : '';
        foreach ($_POST['users'] as $for_user_id) {
            $date = isset($_POST['date_alarm']) ? trim($_POST['date_alarm']) : '';

            if ($data['state'] == true && strtotime($date) < time()) {
                $date = date('Y-m-d H:i:s', strtotime('+ 1 minutes'));
            }

            if ($data['state'] == true) {
                $id = $all_configs['db']->query(
                    'INSERT INTO {alarm_clock} (date_alarm, user_id, for_user_id, text, order_id, closed) VALUES (?, ?i, ?n, ?, ?n, 0)',
                    array($date, $user_id, $for_user_id, $text, $order_id), 'id');

            }
        }
        if ($id) {
            if (isset($_POST['text-to-private-comment']) && $order_id > 0 && mb_strlen($text, 'UTF-8') > 0) {
                $all_configs['suppliers_orders']->add_client_order_comment($order_id, $text, 1);
            }
        }
    }

    Response::json($data);
}

// форма напоминаний
if ($act == 'alarm-clock') {
    $data['state'] = true;

    $order_id = isset($_POST['object_id']) ? $_POST['object_id'] : 0;
    $userId = $_SESSION['id'];

    require_once __DIR__ . '/Core/View.php';
    $view = new View($all_configs);
    $users = array();
    $query = '1=1';
    if ($order_id) {
        $first = $all_configs['db']->query('SELECT accepter, manager, engineer FROM {orders} WHERE id=?i',
            array($order_id))->row();
        $ids[] = $userId;
        if (!empty($first['manager']) && $first['manager'] != $userId) {
            $ids[] = $first['manager'];
        }
        if (!empty($first['accepter']) && $first['accepter'] != $userId) {
            $ids[] = $first['accepter'];
        }
        if (!empty($first['engineer']) && $first['engineer'] != $userId) {
            $ids[] = $first['engineer'];
        }
        $users = array_merge(
            $users,
            $all_configs['db']->query('SELECT fio, login, id FROM {users} WHERE avail=1 AND deleted=0 AND id in (?li)',
                array($ids))->assoc('id')
        );
        $query = $all_configs['db']->makeQuery('NOT id in (?li)', array($ids));
    }
    $users = array_merge(
        $users,
        $all_configs['db']->query('SELECT fio, login, id FROM {users} WHERE avail=1 AND deleted=0 AND ?q',
            array($query))->assoc('id')
    );

    $data['content'] = $view->renderFile('messages/alarm_clock_form', array(
        'order_id' => $order_id,
        'user_id' => $user_id,
        'users' => $users
    ));

    Response::json($data);
}

function show_alarms($all_configs, $user_id, $old = false)
{
    require_once __DIR__ . '/Core/View.php';
    $view = new View($all_configs);

    $alarms = $all_configs['db']->query('
        SELECT a.*, 
        u.fio, u.phone, u.login, u.email,
        fu.fio as fu_fio, fu.phone as fu_phone, fu.login as fu_login, fu.email as fu_email, a.date_alarm as date
        FROM {alarm_clock} as a
        LEFT JOIN {users} as u ON u.id=a.user_id 
        LEFT JOIN {users} as fu ON fu.id=a.for_user_id 
        WHERE (IF(a.for_user_id>0, a.for_user_id=?i, true) OR a.user_id=?i) AND a.date_alarm ?query NOW()
        ORDER BY date_alarm DESC
    ', array($user_id, $user_id, $old === false ? '>' : '<'))->assoc();


    return $view->renderFile('messages/show_alarms', array(
        'alarms' => $alarms,
        'user_id' => $user_id
    ));
}

// редактирование комментария заказа поставщику
if ($act == 'edit-supplier-order-comment') {
    if (isset($_POST['pk']) && isset($_POST['value'])) {
        $data['element_id'] = 'supplier-order-comment-' . $_POST['pk'];
        $data['element_value'] = cut_string(trim($_POST['value']), 25);
        $all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET comment=? WHERE id=?i',
            array(trim($_POST['value']), intval($_POST['pk'])));
    }
    Response::json($data);
}
// редактирование комментария приходной накладной
if ($act == 'edit-purchase-invoice-comment') {
    if (isset($_POST['pk']) && isset($_POST['value'])) {
        $data['element_id'] = 'supplier-order-comment-' . $_POST['pk'];
        $data['element_value'] = cut_string(trim($_POST['value']), 25);
        $all_configs['db']->query('UPDATE {purchase_invoices} SET description=? WHERE id=?i',
            array(trim($_POST['value']), intval($_POST['pk'])));
    }
    Response::json($data);
}

// перемещаем заказ
if ($act == 'move-order') {
    $data['message'] = '';
    $data['state'] = true;
    $serials = isset($_POST['serials']) && is_array($_POST['serials']) ? array_filter(array_unique($_POST['serials'])) : array();

    if (count($serials) > 0) {
        if ($all_configs['oRole']->hasPrivilege('edit-clients-orders') || $all_configs['oRole']->hasPrivilege('engineer')) {
            foreach ($serials as $item_id) {
                if ($item_id > 0) {
                    $response = $all_configs['chains']->move_item_request(array('item_id' => $item_id) + $_POST);
                    $serial = $response && isset($response['serial']) ? $response['serial'] . ' - ' : '';
                    if (is_array($response) && isset($response['state']) && $response['state'] == true) {
                    } else {
                        $data['state'] = false;
                        $data['message'] .= $serial . ($response && isset($response['message']) ? $response['message'] : 'Изделие не перемещено') . "\r\n";
                    }
                }
            }
        }
    }

    $order_id = isset($_POST['order_id']) && intval($_POST['order_id']) > 0 ? intval($_POST['order_id']) : null;

    if ($order_id > 0) {
        $order = $all_configs['db']->query('SELECT * FROM {orders} WHERE id=?i', array($order_id))->row();

        $data['reload'] = false;

        if ($order) {
            // обновляем статус заказа
            if ($all_configs['oRole']->hasPrivilege('edit-clients-orders') && isset($_POST['status'])
                && intval($_POST['status']) >= 0 && intval($_POST['status']) != $order['status']
            ) {
                $reload = true;
                $response = update_order_status($order, intval($_POST['status']));
                if (!isset($response['state']) || $response['state'] == false) {
                    $data['state'] = false;
                    $data['message'] .= (isset($response['msg']) ? $response['msg'] : 'Статус не изменился') . "\r\n";
                }
            }
            // добавляем публичный комментарий
            if (isset($_POST['public_comment']) && mb_strlen(trim($_POST['public_comment']), 'UTF-8') > 0) {
                $data['reload'] = true;
                $all_configs['suppliers_orders']->add_client_order_comment($order['id'], trim($_POST['public_comment']),
                    0);
            }
            // добавляем приватный комментарий
            if (isset($_POST['private_comment']) && mb_strlen(trim($_POST['private_comment']), 'UTF-8') > 0) {
                $data['reload'] = true;
                $all_configs['suppliers_orders']->add_client_order_comment($order['id'],
                    trim($_POST['private_comment']), 1);
            }
            if ($all_configs['oRole']->hasPrivilege('edit-clients-orders') || $all_configs['oRole']->hasPrivilege('engineer')) {
                // пробуем переместить
                $response = $all_configs['chains']->move_item_request($_POST);
                if (is_array($response) && isset($response['state']) && $response['state'] == true) {

                } else {
                    $data['state'] = false;
                    $data['message'] .= ($response && isset($response['message']) ? $response['message'] : 'Заказ не перемещен') . "\r\n";
                }
            }
        } else {
            $data['message'] .= "Заказ не найден\r\n";
            $data['state'] = false;
        }
    }

    if (count($serials) == 0 && $order_id == 0) {
        $data['state'] = false;
    }
    if (isset($_GET['from_sidebar']) && $data['state'] == true) {
        $data['message'] = "Заказ успешно перемещен";
    }

    $data['message'] = empty($data['message']) ? l('Укажите номер ремонта или серийный номер изделия') : $data['message'];

    Response::json($data);
}

// название товара по серийнику
if ($act == 'get-product-title' || $act == 'get-product-title-and-price') {
    $item_ids = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $product = $all_configs['db']->query('SELECT g.title, g.price, g.price_wholesale as price_wholesale FROM {goods} as g, {warehouses_goods_items} as i
        WHERE g.id=i.goods_id AND i.id=?i', array($item_ids))->row();

    if ($product) {
        $data['state'] = true;
        $data['msg'] = htmlspecialchars($product['title']) . ' ' . ($product['price'] / 100) . '(' . ($product['price_wholesale'] / 100) . ')';
        if ($act == 'get-product-title-and-price') {
            $data['price'] = $product['price'] / 100;
            $data['price_wholesale'] = $product['price_wholesale'] / 100;
            $data['id'] = $item_ids;
        }
    }

    Response::json($data);
}

// закрытие заказа
if ($act == 'close-supplier-order') {
    $data = $all_configs['suppliers_orders']->close_order(isset($_POST['order_id']) ? $_POST['order_id'] : 0, true);
    Response::json($data);
}

// запчасти не будет
if ($act == 'end-supplier-order') {
    $data = $all_configs['suppliers_orders']->end_order(isset($_POST['order_id']) ? $_POST['order_id'] : 0, true);
    Response::json($data);
}

// список локаций по складу
if ($act == 'get_locations') {
    $data['msg'] = l('Локаций не найдено');
    if (isset($_POST['wh_id'])) {
        $warehouses = get_service('wh_helper')->get_warehouses();
        if (isset($warehouses[$_POST['wh_id']]['locations'])) {
            $i = 0;
            $out = '<option value="0">' . l('Выберите локацию на складе') . '</option>';
            foreach ($warehouses[$_POST['wh_id']]['locations'] as $id => $location) {
                if (trim($location['name'])) {
                    $out .= '<option' . (!$i ? ' selected="selected"' : '') . ' value="' . $id . '">' .
                        htmlspecialchars($location['name']) .
                        '</option>';
                    $i++;
                }
            }
            unset($data['msg']);
            $data['html'] = $out;
        }
    }

    Response::json($data);
}

// форма перемещения заказа
if ($act == 'stock_move-order') {
    $order_id = isset($_POST['object_id']) && $_POST['object_id'] > 0 ? $_POST['object_id'] : 0;
    $order = $all_configs['db']->query('SELECT * FROM {orders} WHERE id=?i', array($order_id))->row();
    $order = array('id' => $order ? intval($order['id']) : '', 'status' => $order ? intval($order['status']) : '');
    $rand = rand(1000, 9999);
    $data['content'] = $all_configs['chains']->moving_item_form(0, null, null, $order, false, $rand);
    $data['btns'] = '<input onclick="move_order(this, ' . $rand . ')" type="button" value="' . l('Сохранить') . '" class="btn" />';
    $data['state'] = true;
    $data['functions'] = array('reset_multiselect()');

    Response::json($data);
}

// форма перемещения заказа для сайдбара
if ($act == 'stock_move-order_sidebar') {
    require_once __DIR__ . '/Helpers/InfoPopover.php';
    $order_id = isset($_POST['object_id']) && $_POST['object_id'] > 0 ? $_POST['object_id'] : 0;
    $order = $all_configs['db']->query('SELECT * FROM {orders} WHERE id=?i', array($order_id))->row();
    $order = array('id' => $order ? intval($order['id']) : '', 'status' => $order ? intval($order['status']) : '');
    $rand = rand(1000, 9999);
    $data['html'] = $all_configs['chains']->moving_item_form_sidebar(0, null, null, $order, false, $rand);
    $data['btns'] = '<input onclick="move_order(this, ' . $rand . ')" type="button" value="' . l('Сохранить') . '" class="btn" />';
    $data['state'] = true;
    $data['functions'] = array('reset_multiselect()');

    Response::json($data);
}

// смена контактных телефонов
if ($act == 'show_contact_phones') {
    $data['state'] = true;
    // detect current state
    if ($all_configs['settings']['content_alarm']) {
        $html = 'включено отображение аварийных контактных телефонов :<br>';
        $html .= ($all_configs['settings']['content_phone_mob_alarm'] ? ', ' . $all_configs['settings']['content_phone_mob_alarm'] : '');
        $data['btns'] = '<input onclick="alert_box(this, false, \'usually_contact_phones\', undefined, undefined, \'messages.php\')" type="button" value="Включить обычные телефоны " class="btn" />';
    } else {
        $html = 'включено отображение обычных контактных телефонов :<br>';
        $data['btns'] = '<input onclick="alert_box(this, false, \'alarm_contact_phones\', undefined, undefined, \'messages.php\')" type="button" value="Включить аварийные телефоны " class="btn" />';
    }
    $data['content'] = $html;
    Response::json($data);
}

// переключение на аварийные телефоны
if ($act == 'alarm_contact_phones') {
    $data['state'] = true;
    $all_configs['db']->query('UPDATE {settings} SET value=? WHERE name=?', array('1', 'content_alarm'));
    $data['content'] = 'Телефон переключен на аварийный';
    Response::json($data);
}

// переключение на обычные телефоны
if ($act == 'usually_contact_phones') {
    $data['state'] = true;
    $all_configs['db']->query('UPDATE {settings} SET value=? WHERE name=?', array('0', 'content_alarm'));
    $data['content'] = 'Телефон переключен на обычный';
    Response::json($data);
}

// количество не прочтенных сообщений
function count_unread_messages($type = null)
{
    global $all_configs;
    $user_id = $_SESSION['id'];

    $query = '';
    if ($type !== null) {
        $query = 'AND type=' . intval($type);
    }

    return intval($all_configs['db']->query(
        'SELECT COUNT(id) FROM {messages} WHERE user_id_destination=?i AND remove=?i AND is_read=?i ?query',
        array($user_id, 0, 0, $query))->el());
}

// достаем сообщения
function get_messages($type = null)
{
    global $all_configs;
    $query = '';

    if ($type !== null) {
        $query = 'AND type=' . intval($type);
    }

    // сообщения
    $messages = $all_configs['db']->query('SELECT id, date_add, title, INET_NTOA(ip) as ip, content, is_read, auto, type
          FROM {messages} WHERE user_id_destination=?i AND remove=0 ?query ORDER BY `date_add` DESC',
        array($_SESSION['id'], $query))->assoc();

    $html = l('Сообщений нет');
    if ($messages) {
        $html = '<br/><div class="accordion-group" id="accordion-messages">';
        foreach ($messages as $message) {
            $content = $message['auto'] == 1 ? $message['content'] : htmlspecialchars($message['content']);
            $read = $message['is_read'] == 1 ? 'class="accordion-toggle muted"' : 'class="accordion-toggle" onClick="read_mess(this, ' . $message['id'] . ')"';
            $html .=
                '<div class="panel panel-default" style="margin-bottom:5px;">
                    <div class="panel-heading ' . ($message['is_read'] == 1 ? 'panel-white' : '') . '">
                        <div class="pull-right">
                            <span title="' . do_nice_date($message['date_add'],
                    false) . '">' . do_nice_date($message['date_add']) . '</span>
                            <i onclick="remove_message(this, ' . $message['id'] . ', ' . $message['type'] . ')" class="glyphicon glyphicon-remove cursor-pointer"></i>
                        </div>
                        <a ' . $read . ' data-toggle="collapse" data-parent="#accordion-messages" href="#collapse-messages-' . $message['id'] . '">' .
                htmlspecialchars($message['title']) .
                '</a>
                    </div>
                    <div id="collapse-messages-' . $message['id'] . '" class="panel-collapse collapse">
                        <div class="panel-body">' .
                $content .
                '</div>
                    </div>
                </div>';
        }
        $html .= '</div>';
    }

    return $html;
}

// достаем сообщения
if ($act == 'get-messages') {
    $data['state'] = true;
    $type = isset($_POST['object_id']) ? intval($_POST['object_id']) : null;
    $data['content'] = get_messages($type);
    $onclick = 'onclick="remove_message(this, \'all\'' . ($type === null ? '' : ', ' . $type) . ')"';
    $data['btns'] = '<input ' . $onclick . ' type="button" class="btn btn-danger" value="' . l('Удалить все') . '" />';

    Response::json($data);
}

// аякс
if (isset($_POST['act']) && $_POST['act'] == 'global-ajax') {

    if (!isset($_SESSION['id']) || $_SESSION['id'] == 0) {
        return false;
    }

    $is_new = 0;
    $qty_unread = 0;
    $data = array();
    if (isset($_GET['last_seconds']) && $_GET['last_seconds'] > 0) {
        $qty_unread = $all_configs['db']->query(
            'SELECT COUNT(id) FROM {messages} WHERE UNIX_TIMESTAMP(date_add)>? AND is_read=?i AND user_id=?i',
            array($_GET['last_seconds'], 0, $user_id))->el();
    }

    $q3 = $all_configs['manageModel']->suppliers_orders_query(array('opened' => true) + $_GET +
        ($all_configs['oRole']->hasPrivilege('site-administration') ? array() : array('my' => true)));
    $queries = $all_configs['manageModel']->suppliers_orders_query(array('type' => 'debit-work') + $_GET);

    $query = $all_configs['manageModel']->global_filters($_GET,
        array('date', 'category', 'product', 'operators', 'client', 'client_orders_id'));
    $query = $all_configs['db']->makeQuery('?query AND (o.sum>(o.sum_paid + o.discount) OR o.sum<o.sum_paid)',
        array($query));
    $data['tc_accountings_clients_orders'] = $all_configs['manageModel']->get_count_accounting_clients_orders($query);
    $q1 = $all_configs['manageModel']->suppliers_orders_query($_GET + array('type' => 'pay'));
    $data['tc_accountings_suppliers_orders'] = $all_configs['manageModel']->get_count_suppliers_orders($q1['query']);
    $data['tc_sum_accountings_orders'] = $data['tc_accountings_suppliers_orders'] + $data['tc_accountings_clients_orders'];
    $data['tc_warehouses_clients_orders_bind'] = $all_configs['chains']->get_operations(1,
        array('open' => true) + $_GET, true);
    $data['tc_warehouses_clients_orders_unbind'] = $all_configs['chains']->get_operations(4,
        array('open' => true) + $_GET, true);
    $data['tc_debit_suppliers_orders'] = $all_configs['manageModel']->get_count_suppliers_orders($queries['query']);
    $data['tc_sum_warehouses_orders'] = $data['tc_warehouses_clients_orders_bind'] + $data['tc_warehouses_clients_orders_unbind'] + $data['tc_debit_suppliers_orders'];
    $data['tc_suppliers_orders'] = $data['tc_suppliers_orders_all'] = $all_configs['manageModel']->get_count_suppliers_orders($q3['query']);

    // напоминания к заказам
    $alarms = $all_configs['db']->query('SELECT ac.id, UNIX_TIMESTAMP(ac.date_alarm) as date, ac.order_id, COUNT(*) as qty,
        GROUP_CONCAT(ac.text, " <a href=\'' . $all_configs['prefix'] . 'orders/create/", ac.order_id, "\'>", ac.order_id, "</a><span  class=\'from\'>(", if(not u.fio = NULL, u.fio, u.login), ")</span>") as text
        FROM {alarm_clock} ac'
        . ' JOIN {users} u ON u.id=ac.user_id'
        . ' WHERE for_user_id=?i AND date_alarm>NOW()
        GROUP BY order_id ORDER BY date_alarm', array($user_id))->assoc('order_id');

    if ($alarms && $alarms[key($alarms)] != 0) {
        $alarms[0] = $alarms[key($alarms)];
    }
    $data['new-count-mess'] = count_unread_messages(0); // количество новых сообщений
    $data['count-alarm-timer'] = count_unread_messages(1); // количество новых напоминаний
    $data['new-count-statuses'] = count_unread_messages(2); // количество новых запросов о статусе

    $result = array(
        //'messages' => get_messages(0, false), // проверяем пришло ли новое сообщение
        'counts' => $data,
        'new_comments' => intval($qty_unread),
        'alarms' => $alarms,
    );
    require_once __DIR__ . '/Core/FlashMessage.php';
    $flash = FlashMessage::show();
    if (!empty($flash)) {
        $result['flash'] = $flash;
    }
    Response::json($result);
}

// закрываем аларм
if (isset($_POST['act']) && $_POST['act'] == 'close-alarm') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : '';
    if ($id) {
        $all_configs['db']->query("UPDATE {alarm_clock} SET closed = 1 "
            . "WHERE id = ?i AND for_user_id = ?i", array($id, $user_id));
    }
}

// отправляем сообщение
if (isset($_POST['act']) && $_POST['act'] == 'send_message') {

    if (!isset($_POST['send-mess-user']) || !is_array($_POST['send-mess-user']) || count($_POST['send-mess-user']) < 1) {
        Response::json(array('message' => l('Выберите пользователя'), 'error' => true));
    }

    if (!isset($_POST['text'])) {
        Response::json(array('message' => l('Введите текст'), 'error' => true));
    }
    if (!isset($_POST['title'])) {
        Response::json(array('message' => l('Введите заглавие'), 'error' => true));
    }
    $text = trim($_POST['text']);
    $title = trim($_POST['title']);
    if (empty($text)) {
        Response::json(array('message' => l('Введите сообщение'), 'error' => true));
    }
    if (empty($title)) {
        Response::json(array('message' => l('Введите заглавие'), 'error' => true));
    }
    $array = array();
    foreach ($_POST['send-mess-user'] as $user) {
        $all_configs['db']->query('INSERT INTO {messages} (content, ip, user_id, user_id_destination, title)
              VALUES (?, INET_ATON(?), ?i, ?i, ?)',
            array($text, get_ip(), $_SESSION['id'], $user, $title));
    }

    Response::json(array('message' => l('Успешно отправлено')));
}


// работа с infobox
if (isset($_POST['act']) && $_POST['act'] == 'infobox' && isset($_POST['do'])
    && isset($_POST['hash'])
) {
    require_once 'classes/infoblock.class.php';
    $infoblock = new Infoblock($all_configs);

    if ($_POST['do'] == 'get') {
        $arr = $infoblock->getinfo(nl2br(strip_tags($_POST['hash'])));
        Response::json(array(
            'text' => $arr['text'],
            'title' => $arr['title']
        ));
    }
}

if (isset($_POST['pk']['act']) && $_POST['pk']['act'] == 'infobox'
    && isset($_POST['pk']['do']) && isset($_POST['value']) && isset($_POST['pk']['hash'])
) {
    require_once 'classes/infoblock.class.php';
    $infoblock = new Infoblock($all_configs);

    if ($_POST['pk']['do'] == 'set') {
        $infoblock->setinfo($_POST['pk']['hash'], $_POST['value']);
        Response::json(array("msg" => ''));
    }

}

if ($act == 'hide-infopopover') {
    $var = !empty($_POST['id']) ? $_POST['id'] : '';
    if ($var) {
        include_once __DIR__ . '/Helpers/InfoPopover.php';
        InfoPopover::getInstance()->oneTimePopoverToggle($var);
    }
}

if ($act == 'hide-toggle-infopopover') {
    $var = !empty($_POST['id']) ? $_POST['id'] : '';
    $state = !empty($_GET['state']) ? $_GET['state'] : 0;
    if ($var) {
        include_once __DIR__ . '/Helpers/InfoPopover.php';
        InfoPopover::getInstance()->oneTimePopoverToggle($var, $state);
    }
}