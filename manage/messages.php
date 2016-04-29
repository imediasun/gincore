<?php

include 'inc_config.php';
include 'inc_func.php';
include 'inc_settings.php';

global $all_configs;

if (!isset($_SESSION)) {
    session_start();
}

if (!isset($_SESSION['id']) || $_SESSION['id'] == 0) {
    return false;
}

$user_id = $_SESSION['id'];

// помечаем
if (isset($_POST['act']) && $_POST['act'] == 'marked-object') {
    if (!isset($_POST['object_id']) || $_POST['object_id'] == 0 || !isset($_POST['type']) || empty($_POST['type'])) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Побробуйте еще раз', 'error' => true));
        exit;
    }

    $ar = $all_configs['db']->query('DELETE FROM {users_marked} WHERE user_id=?i AND object_id=?i AND type=?',
        array($_SESSION['id'], $_POST['object_id'], trim($_POST['type'])))->ar();

    if ($ar) {
        $count_marked = $all_configs['db']->query('SELECT COUNT(id) FROM {users_marked}
              WHERE user_id=?i AND type=?',
            array($_SESSION['id'], trim($_POST['type'])))->el();

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Успешно', 'count-marked' => $count_marked));
        exit;
    }

    $all_configs['db']->query('INSERT INTO {users_marked} (user_id, object_id, type) VALUES (?i, ?i, ?)',
        array($_SESSION['id'], $_POST['object_id'], trim($_POST['type'])));

    $count_marked = $all_configs['db']->query('SELECT COUNT(id) FROM {users_marked} WHERE user_id=?i AND type=?',
        array($_SESSION['id'], trim($_POST['type'])))->el();

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode(array('message' => 'Успешно', 'count-marked' => $count_marked));
    exit;
}

if (isset($_POST['act']) && $_POST['act'] == 'global-typeahead') {
    $data = array();

    $limit = isset($_POST['act']) && $_POST['act'] > 0 && $_POST['act'] < 150 ? intval($_POST['limit']) : 30;
    $is_double = isset($_POST['double']) && $_POST['double'] ? true : false;

    if (isset($_POST['query']) && !empty($_POST['query']) && isset($_POST['table']) && !empty($_POST['table'])) {

        $s = str_replace(array("\xA0", '&nbsp;', ' '), '%',
            trim(preg_replace('/ {1,}/', ' ', mb_strtolower($_POST['query'], 'UTF-8'))));

        if ($_POST['table'] == 'categories' || $_POST['table'] == 'categories-last' || $_POST['table'] == 'categories-goods') {
            $query = '';
            $join = '';
            if (isset($_POST['fix']) && $_POST['fix'] > 0) {
                $query = $all_configs['db']->makeQuery('AND cg.id IN (?li)',
                    array(array_values(get_childs_categories($all_configs['db'], $_POST['fix']))));
                /*$query = $all_configs['db']->makeQuery('AND cg.id IN (SELECT id FROM {categories}
                            JOIN (SELECT @pv:=?i)tmp WHERE parent_id=@pv OR id=@pv)',
                    array($_POST['fix']));*/
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
                    WHERE cg.title LIKE "%?e%" ?query LIMIT ?i',
                array($join, $s, $query, $limit))->assoc();
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
                /*$query = $all_configs['db']->makeQuery('RIGHT JOIN {category_goods} as cg
                        ON cg.category_id IN (SELECT id FROM {categories}
                            JOIN (SELECT @pv:=?i)tmp WHERE parent_id=@pv OR id=@pv) AND g.id=cg.goods_id',
                    array($_POST['fix']));*/
            }

            $query_title = 'g.title';

            $data = $all_configs['db']->query('SELECT g.id, ?q as title FROM {goods} as g ?query
                    WHERE g.title LIKE "%?e%" AND g.avail=?i GROUP BY g.id LIMIT ?i',
                array($query_title, $query, $s, 1, $limit))->assoc();
        }
        if ($_POST['table'] == 'goods-goods') {
            $query = '';
            if (isset($_POST['fix']) && $_POST['fix'] > 0) {
                $query = $all_configs['db']->makeQuery('RIGHT JOIN {category_goods} as cg ON
                        cg.category_id IN (?li) AND g.id=cg.goods_id',
                    array(array_values(get_childs_categories($all_configs['db'], $_POST['fix']))));
            }
            $data = $all_configs['db']->query('SELECT g.id, g.title FROM {goods} as g ?query
                    WHERE (g.type IS NULL OR g.type=0) AND g.title LIKE "%?e%" AND g.avail=?i GROUP BY g.id LIMIT ?i',
                array($query, $s, 1, $limit))->assoc();
        }
        if ($_POST['table'] == 'goods-service') {
            $query = '';
            if (isset($_POST['fix']) && $_POST['fix'] > 0) {
                $query = $all_configs['db']->makeQuery('RIGHT JOIN {category_goods} as cg ON
                        cg.category_id IN (?li) AND g.id=cg.goods_id',
                    array(array_values(get_childs_categories($all_configs['db'], $_POST['fix']))));
            }
            $data = $all_configs['db']->query('SELECT g.id, g.title FROM {goods} as g ?query
                    WHERE g.type=1 AND g.title LIKE "%?e%" AND g.avail=?i GROUP BY g.id LIMIT ?i',
                array($query, $s, 1, $limit))->assoc();
        }
        if ($_POST['table'] == 'clients') {
            $data = $all_configs['db']->query('SELECT c.id, GROUP_CONCAT(COALESCE(c.fio, ""), ", ", COALESCE(c.email, ""),
                      ", ", COALESCE(c.phone, ""), ", ", COALESCE(p.phone, "") separator ", " ) as title, c.fio, c.phone, c.tag_id, t.title as t_title, t.color as t_color
                    FROM {clients} as c
                    LEFT JOIN {clients_phones} as p ON p.client_id=c.id AND p.phone<>c.phone
                    LEFT JOIN {tags} as t ON t.id=c.tag_id
                    WHERE (c.email LIKE "%?e%" OR c.fio LIKE "%?e%" OR c.phone LIKE "%?e%" OR p.phone LIKE "%?e%") AND c.id<>?i
                    GROUP BY c.id LIMIT ?i',
                array($s, $s, $s, $s, $all_configs['configs']['erp-write-off-user'], $limit))->assoc();
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
        if ($_POST['table'] == 'locations') {
            $data = $all_configs['db']->query('SELECT GROUP_CONCAT(w.title, " ", l.location) as title, l.id
                    FROM {warehouses_locations} as l, {warehouses} as w
                    WHERE w.id=l.wh_id GROUP BY l.id HAVING title LIKE "%?e%" LIMIT ?i',
                array($s, $limit))->assoc();
        }
        /*if ($_POST['table'] == 'orders') {
            $data = $all_configs['db']->query('SELECT o.id, GROUP_CONCAT(o.id, c.fio)
                    FROM {orders} as o WHERE title LIKE "%?e%" LIMIT ?i',
                array($s, $limit))->assoc();
        }*/
    }

    /*if ($data) {
        foreach ($data as $k=>$v) {
            $data[$k]['title'] = str_replace("\u00a0", ' ', trim($v['title']));
        }
    }*/

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

$act = isset($_POST['act']) ? trim($_POST['act']) : (isset($_GET['act']) ? trim($_GET['act']) : '');
$data = array('state' => false);

// отмечаем что сообщение прочтено
if ($act == 'read-message') {
    if (!isset($_POST['mess_id']) || intval($_POST['mess_id']) == 0) {
        // если нет ид сообщения
        $data['msg'] = 'Произошла ошибка';
    } else {
        $data['state'] = true;
        $data['msg'] = 'Сообщение прочтено';
        $all_configs['db']->query('UPDATE {messages} SET is_read=1, date_read=NOW() WHERE id=?i',
            array(intval($_POST['mess_id'])));
        $data['qty'] = count_unread_messages();
    }
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

// удаляем сообщение
if ($act == 'remove-message') {
    if (!isset($_POST['mess_id']) && !isset($_POST['type'])) {
        // если нет ид сообщения
        $data['msg'] = 'Произошла ошибка';
    } else {
        $query = '';
        if ($_POST['mess_id'] != 'all') {
            // by id
            $query = $all_configs['db']->makeQuery('AND id=?i', array(intval($_POST['mess_id'])));
        }
        $data['state'] = true;
        $data['msg'] = 'Успешно удалено';
        $data['qty'] = count_unread_messages();//
        $all_configs['db']->query('UPDATE {messages} SET remove=1 WHERE `type`=?i AND user_id_destination=?i ?query',
            array(intval($_POST['type']), $user_id, $query));
    }
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

/*// теги
if ($act == 'tags') {
    $return = array();
    if (isset($_POST['table'])) {
        // склады
        if ($_POST['table'] == 'warehouses') {
            // запросы для касс для разных привилегий
            $q = $all_configs['chains']->query_warehouses();
            $query_for_noadmin_w = $q['query_for_noadmin_w'];
            // списсок складов с общим количеством товаров
            $warehouses = $all_configs['chains']->warehouses($query_for_noadmin_w);
            if ($warehouses) {
                foreach ($warehouses as $warehouse) {
                    $return[] = array('value' => $warehouse['id'], 'text' => htmlspecialchars($warehouse['title']));
                }
            }
        }
        if ($_POST['table'] == 'warehouses-locations') {
            // запросы для касс для разных привилегий
            $q = $all_configs['chains']->query_warehouses();
            $query_for_noadmin_w = $q['query_for_noadmin_w'];
            // списсок складов с общим количеством товаров
            $warehouses = $all_configs['chains']->warehouses($query_for_noadmin_w);

            if ($warehouses) {
                foreach ($warehouses as $warehouse) {
                    if (isset($warehouse['locations'])) {
                        foreach ($warehouse['locations'] as $location_id=>$location) {
                            $return[] = array(
                                'value' => $location_id,
                                'text' => htmlspecialchars($warehouse['title']) . ' (' . htmlspecialchars($location) . ')',
                            );
                        }
                    }
                }
            }
        }
    }
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($return);
    exit;
}*/

// удаление напоминания
if ($act == 'remove-alarm') {
    if (isset($_POST['id']) && $all_configs['oRole']->hasPrivilege('alarm')) {
        $data['state'] = true;
        $id = $all_configs['db']->query('DELETE FROM {alarm_clock} WHERE id=?i', array($_POST['id']));
    } else {
        $data['state'] = false;
        $data['msg'] = 'Напоминание не найдено';
    }
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

// добавление напоминаний
if ($act == 'add-alarm') {
    $data = array('state' => true);

    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : null;
    $for_user_id = isset($_POST['users']) && intval($_POST['users']) > 0 ? intval($_POST['users']) : null;
    $text = isset($_POST['text']) ? trim($_POST['text']) : '';
    $date = isset($_POST['date_alarm']) ? trim($_POST['date_alarm']) : '';

//    if (!$all_configs['oRole']->hasPrivilege('alarm')) {
//        $data['state'] = false;
//        $data['msg'] = 'Нет прав';
//    }
    if ($data['state'] == true && strtotime($date) < time()) {
        $data['state'] = false;
        $data['msg'] = 'Укажите дату (в будущем)';
    }

    if ($data['state'] == true) {
        $id = $all_configs['db']->query(
            'INSERT INTO {alarm_clock} (date_alarm, user_id, for_user_id, text, order_id, closed) VALUES (?, ?i, ?n, ?, ?n, 0)',
            array($date, $user_id, $for_user_id, $text, $order_id), 'id');

        if ($id) {
            if (isset($_POST['text-to-private-comment']) && $order_id > 0 && mb_strlen($text, 'UTF-8') > 0) {
                $all_configs['suppliers_orders']->add_client_order_comment($order_id, $text, 1);
            }
        }
    }

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

// форма напоминаний
if ($act == 'alarm-clock') {
    $data['state'] = true;

    $order_id = isset($_POST['object_id']) ? $_POST['object_id'] : 0;

    require_once __DIR__ . '/Core/View.php';
    $view = new View($all_configs);
    $data['content'] = $view->renderFile('messages/alarm_clock_form', array(
        'order_id' => $order_id,
        'user_id' => $user_id,
    ));

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

function show_alarms($all_configs, $user_id, $old = false)
{
    $html =
        '<table class="table">
            <thead><tr><td>' . l('Дата создания') . '</td><td>' . l('Дата напоминания') . '</td><td>' . l('Автор') . '</td><td>' . l('номер заказа') . '</td><td>' . l('Текст') . '</td><td></td></tr></thead>
            <tbody>';

    $alarms = $all_configs['db']->query('SELECT a.*, u.fio, u.phone, u.login, u.email FROM {alarm_clock} as a
        LEFT JOIN {users} as u ON u.id=a.user_id WHERE IF(a.for_user_id>0, a.for_user_id=?i, true) AND a.date_alarm ?query NOW()
        ORDER BY date_alarm DESC', array($user_id, $old === false ? '>' : '<'))->assoc();

    if ($alarms) {
        foreach ($alarms as $alarm) {
            $html .= '<tr><td><span title="' . do_nice_date($alarm['date_add'],
                    false) . '">' . do_nice_date($alarm['date_add']) . '</span></td>';
            $html .= '<td><span title="' . do_nice_date($alarm['date_alarm'],
                    false) . '">' . do_nice_date($alarm['date_alarm']) . '</span></td>';
            $html .= '<td>' . get_user_name($alarm) . '</td><td>';
            if ($alarm['order_id'] > 0) {
                $href = $all_configs['prefix'] . 'orders/create/' . $alarm['order_id'];
                $html .= '<a href="' . $href . '">' . $alarm['order_id'] . '</a>';
            }
            $html .= '</td><td>' . cut_string($alarm['text']) . '</td>';
            $html .= '<td><i onclick="remove_alarm(this, ' . $alarm['id'] . ')" class="glyphicon glyphicon-remove cursor-pointer"></i></td></tr>';
        }
    } else {
        $html .= '<tr><td colspan="5">' . l('Напоминаний нет') . '</td></tr>';
    }
    $html .= '</tbody></table>';

    return $html;
}

// редактирование комментария заказа поставщику
if ($act == 'edit-supplier-order-comment') {
    if (isset($_POST['pk']) && isset($_POST['value'])) {
        $data['element_id'] = 'supplier-order-comment-' . $_POST['pk'];
        $data['element_value'] = cut_string(trim($_POST['value']), 25);
        $all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET comment=? WHERE id=?i',
            array(trim($_POST['value']), intval($_POST['pk'])));
    }
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
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
                        //$data['message'] .= "Изделие " . $serial . "успешно перемещено\r\n";
                    } else {
                        $data['state'] = false;
                        $data['message'] .= $serial . ($response && isset($response['message']) ? $response['message'] : 'Изделие не перемещено') . "\r\n";
                        //break;
                    }
                }
            }
        }/* else {
            $data['message'] = "Нет прав для перемещения\r\n";
        }*/
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
                    //$data['message'] .= "Заказ успешно перемещен\r\n";
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
    $data['message'] = empty($data['message']) ? 'Укажите номер ремонта или серийный номер изделия' : $data['message'];

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

// название товара по серийнику
if ($act == 'get-product-title' || $act == 'get-product-title-and-price') {
    $item_ids = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $product = $all_configs['db']->query('SELECT g.title, g.price, i.price as wholesale FROM {goods} as g, {warehouses_goods_items} as i
        WHERE g.id=i.goods_id AND i.id=?i', array($item_ids))->row();

    if ($product) {
        $data['state'] = true;
        $data['msg'] = htmlspecialchars($product['title']) . ' ' . ($product['price'] / 100) . '(' . ($product['wholesale'] / 100) . ')';
        if ($act == 'get-product-title-and-price') {
            $data['price'] = $product['price'] / 100;
            $data['id'] = $item_ids;
        }
    }

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

// закрытие заказа
if ($act == 'close-supplier-order') {
    $data = $all_configs['suppliers_orders']->close_order(isset($_POST['order_id']) ? $_POST['order_id'] : 0, true);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

// запчасти не будет
if ($act == 'end-supplier-order') {
    $data = $all_configs['suppliers_orders']->end_order(isset($_POST['order_id']) ? $_POST['order_id'] : 0, true);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

// список локаций по складу
if ($act == 'get_locations') {
    $data['msg'] = 'Локаций не найдено';
    if (isset($_POST['wh_id'])) {
        $warehouses = get_service('wh_helper')->get_warehouses();
        if (isset($warehouses[$_POST['wh_id']]['locations'])) {
            $out = '';
            $i = 0;
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

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
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

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
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
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

// переключение на аварийные телефоны
if ($act == 'alarm_contact_phones') {
    $data['state'] = true;
    $all_configs['db']->query('UPDATE {settings} SET value=? WHERE name=?', array('1', 'content_alarm'));
    $data['content'] = 'Телефон переключен на аварийный';
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
}

// переключение на обычные телефоны
if ($act == 'usually_contact_phones') {
    $data['state'] = true;
    $all_configs['db']->query('UPDATE {settings} SET value=? WHERE name=?', array('0', 'content_alarm'));
    $data['content'] = 'Телефон переключен на обычный';
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
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

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($data);
    exit;
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
        /*$is_new = $all_configs['db']->query('SELECT COUNT(id) FROM {orders} WHERE UNIX_TIMESTAMP(date_add)>?',
            array($_GET['last_seconds']))->el();*/
        $qty_unread = $all_configs['db']->query(
            'SELECT COUNT(id) FROM {messages} WHERE UNIX_TIMESTAMP(date_add)>? AND is_read=?i AND user_id=?i',
            array($_GET['last_seconds'], 0, $user_id))->el();
    }

    $q3 = $all_configs['manageModel']->suppliers_orders_query(array('opened' => true) + $_GET +
        ($all_configs['oRole']->hasPrivilege('site-administration') ? array() : array('my' => true)));
    $queries = $all_configs['manageModel']->suppliers_orders_query(array('type' => 'debit-work') + $_GET);

    $query = $all_configs['manageModel']->global_filters($_GET,
        array('date', 'category', 'product', 'operators', 'client', 'client_orders_id'));
    $query = $all_configs['db']->makeQuery('?query AND (o.sum>o.sum_paid OR o.sum<o.sum_paid)', array($query));
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
        . ' WHERE IF(for_user_id>0, for_user_id=?i, true) AND date_alarm>NOW()
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
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode($result);
    exit;
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
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Выберите пользователя', 'error' => true));
        exit;
    }

    if (!isset($_POST['text'])) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Введите текст', 'error' => true));
        exit;
    }
    if (!isset($_POST['title'])) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Введите заглавие', 'error' => true));
        exit;
    }
    $text = trim($_POST['text']);
    $title = trim($_POST['title']);
    if (empty($text)) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Введите сообщение', 'error' => true));
        exit;
    }
    if (empty($title)) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Введите заглавие', 'error' => true));
        exit;
    }
    $array = array();
    foreach ($_POST['send-mess-user'] as $user) {
        $all_configs['db']->query('INSERT INTO {messages} (content, ip, user_id, user_id_destination, title)
              VALUES (?, INET_ATON(?), ?i, ?i, ?)',
            array($text, get_ip(), $_SESSION['id'], $user, $title));
    }

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode(array('message' => 'Успешно отправлено'));
    exit;
}


// работа с infobox
if (isset($_POST['act']) && $_POST['act'] == 'infobox' && isset($_POST['do'])
    && isset($_POST['hash'])
) {

    require_once 'classes/infoblock.class.php';
    $infoblock = new Infoblock($all_configs);

    if ($_POST['do'] == 'get') {
        $arr = $infoblock->getinfo(nl2br(strip_tags($_POST['hash'])));
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array(
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

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array("msg" => ''));
    }


}