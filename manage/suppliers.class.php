<?php

require_once __DIR__.'/View.php';
class Suppliers
{
    protected $all_configs;

    public $currencies = null;

    public $currency_suppliers_orders; // валюта заказов поставщикам
    public $currency_clients_orders; // валюта заказов клиентов
    /** @var View  */
    protected $view;

    /**
     * Suppliers constructor.
     * @param $all_configs
     */
    function __construct($all_configs)
    {
        $this->all_configs = $all_configs;
        $this->view = new View($all_configs);
        $this->currency_suppliers_orders = $this->all_configs['settings']['currency_suppliers_orders'];
        $this->currencies = $this->all_configs['configs']['currencies'];
        $currencies = $this->currencies;
//        foreach ($currencies as $k=>$currency) {
//            if ($currency['currency-name'] == $this->all_configs['configs']['default-currency']) {
                $this->currency_clients_orders = $this->all_configs['settings']['currency_orders'];
//            }
//        }
    }

    /**
     * @param $mod_id
     * @param $post
     * @return array
     */
    function edit_order($mod_id, $post)
    {
        $data = array('state' => true);

        $count = isset($post['warehouse-order-count']) && $post['warehouse-order-count'] > 0 ? $post['warehouse-order-count'] : 1;
        $supplier = isset($post['warehouse-supplier']) && $post['warehouse-supplier'] > 0 ? $post['warehouse-supplier'] : null;
        $date = 86399+strtotime(isset($post['warehouse-order-date']) ? $post['warehouse-order-date'] : date("d.m.Y"));
        $price = isset($post['warehouse-order-price']) ? intval($post['warehouse-order-price'] * 100) : 0;
        $comment = isset($post['comment-supplier']) ? $post['comment-supplier'] : '';
        $orders = isset($post['so_co']) && is_array($post['so_co']) ? array_filter(array_unique($post['so_co'])) : array();
        $product_id = isset($post['goods-goods']) ? $post['goods-goods'] : 0;
        $order_id = isset($post['order_id']) ? $post['order_id'] : 0;
        //$order['sum_paid'] == 0 && $order['count_debit'] != $order['count_come']
        $warehouse = isset($post['warehouse']) && $post['warehouse'] > 0 ? $post['warehouse'] : null;
        $location = isset($post['location']) && $post['location'] > 0 ? $post['location'] : null;
        $num = isset($post['warehouse-order-num']) && mb_strlen(trim($post['warehouse-order-num']), 'UTF-8') > 0 ? trim($post['warehouse-order-num']) : null;
        $warehouse_type = isset($post['warehouse_type']) ? intval($post['warehouse_type']) : 0;
        $its_warehouse = null;
        $links = array();

        // достаем заказ
        // достаем заказ поставщику
        /*$so = $this->all_configs['db']->query('SELECT o.*, g.title, g.type
                FROM {contractors_suppliers_orders} as o, {goods} as g WHERE o.id=?i AND g.id=o.goods_id',
            array($so_id))->row();*/
        $order = $this->all_configs['db']->query('SELECT * FROM {contractors_suppliers_orders} WHERE id=?i',
            array($order_id))->row();
        $user_id = $supplier && $order && $order['user_id'] == 0 ? $_SESSION['id'] : ($order['user_id'] > 0 ? $order['user_id'] : null);
        // достаем товар
        $product = $this->all_configs['db']->query('SELECT * FROM {goods} WHERE id=?i', array($product_id))->row();

        if (!$this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
            $data['state'] = false;
            $data['msg'] = 'У Вас нет прав';
        }
        if ($data['state'] == true && !$product) {
            $data['state'] = false;
            $data['msg'] = 'Укажите деталь';
        }
        if ($data['state'] == true && !$order) {
            $data['state'] = false;
            $data['msg'] = 'Заказ не найден';
        }
        /*// проверяем доступ
        if (($order['user_id'] == $_SESSION['id'] && $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')
                && $order['sum_paid'] == 0 && $order['count_come'] == 0 && $order['confirm'] != 1) ||
                ($this->all_configs['oRole']->hasPrivilege('site-administration') && $order['confirm'] != 1) ) {
        }*/
        if ($data['state'] == true && $order['confirm'] == 1) {
            $data['state'] = false;
            $data['msg'] = 'Заказ закрыт';
        }
        if ($order['avail'] == 0) {
            $data['state'] = false;
            $data['msg'] = 'Заказ отменен';
        }
        if ($data['state'] == true && $product['type'] == 1) {
            $data['state'] = false;
            $data['msg'] = 'На услугу заказ создать нельзя';
        }
        if ($data['state'] == true && count($orders) > $count) {
            $data['state'] = false;
            $data['msg'] = 'Ремонтов не может быть больше чем количество в заказе';
        }
        // проверка на создание заказа с ценой 0
        if ($price == 0 && $this->all_configs['configs']['suppliers-orders-zero'] == false) {
            $data['state'] = false;
            $data['msg'] = 'Укажите цену больше 0';
        }

        if ($data['state'] == true) {
            // редактируем заказ
            try {
                $this->all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET price=?i, date_wait=?, supplier=?n,
                    its_warehouse=?n, goods_id=?i, user_id=?n, count=?i, comment=?, wh_id=?n, location_id=?n, num=?n, warehouse_type=?i WHERE id=?i',
                    array($price, date("Y-m-d H:i:s", $date), $supplier, $its_warehouse, $product_id,
                        $user_id, $count, $comment, $warehouse, $location, $num, $warehouse_type, $order_id));
            } catch(Exception $e) {
                $data['state'] = false;
                $data['msg'] = 'Заказ с таким номером уже существует';
            }
            if ($data['state'] == true) {
                $this->exportSupplierOrder($order_id, 3);

                // обновляем дату поставки товара
                $this->all_configs['manageModel']->update_product_wait($product);
                // история
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($_SESSION['id'], 'edit-warehouse-order', $mod_id, $order_id));
            }
        }

        if ($data['state'] == true) {
            // связь между заказами
            $result = $this->orders_link($order_id, $orders, intval($order['supplier']));

            if (!isset($result['state']) || $result['state'] == false) {
                $data['msg'] = isset($result['msg']) ? $result['msg'] : 'Заявка уже создана';
                $data['state'] = false;
            } else {
                $links = $result['links'];
            }

            // Уведомлять менеджера, который ответственный за ремонт о том что сроки поставки запчасти изменились
            if (strtotime($order['date_wait']) != $date && isset($result['links']) && count($result['links']) > 0) {
                include_once $this->all_configs['sitepath'] . 'mail.php';
                $messages = new Mailer($this->all_configs);

                foreach($result['links'] as $link) {
                    if (isset($link['id']) && $link['id'] > 0 && isset($link['manager']) && $link['manager'] > 0) {
                        $href = $this->all_configs['prefix'] . 'orders/create/' . $link['id'];
                        $content = 'Сроки поставки запчасти "' . htmlspecialchars($link['title']) . '" заказа <a href="' . $href . '">№' . $link['id'] . '</a> изменились';
                        $messages->send_message($content, 'Сроки поставки запчасти изменились', $link['manager'], 1);
                    }
                }
            }

            // сообщение что типа сохранено
            $_SESSION['suppliers_edit_order_msg'] = 'Сохранено успешно';
        }

        return $data;
    }

    /**
     * @param $mod_id
     * @param $post
     * @return array
     */
    function create_order($mod_id, $post)
    {
        $data = array('state' => true, 'id' => 0);

        $count = isset($post['warehouse-order-count']) && $post['warehouse-order-count'] > 0 ? $post['warehouse-order-count'] : 1;
        $supplier = isset($post['warehouse-supplier']) && $post['warehouse-supplier'] > 0 ? $post['warehouse-supplier'] : null;
        $user_id = $supplier ? $_SESSION['id'] : null;
        $date = isset($post['warehouse-order-date']) ? $post['warehouse-order-date'] : date("d.m.Y");
        $price = isset($post['warehouse-order-price']) ? intval($post['warehouse-order-price'] * 100) : 0;
        $comment = isset($post['comment-supplier']) ? $post['comment-supplier'] : '';
        $warehouse_type = isset($post['warehouse_type']) ? intval($post['warehouse_type']) : 0;
        $orders = isset($post['so_co']) && is_array($post['so_co']) ? array_filter(array_unique($post['so_co'])) : array();
        $product_id = isset($post['goods-goods']) ? $post['goods-goods'] : 0;
        $its_warehouse = $group_parent_id = null;
        $num = isset($post['warehouse-order-num']) && mb_strlen(trim($post['warehouse-order-num']), 'UTF-8') > 0 ? trim($post['warehouse-order-num']) : null;

        // достаем товар
        $product = $this->all_configs['db']->query('SELECT * FROM {goods} WHERE id=?i', array($product_id))->row();

        //if (!$this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
        //    $data['state'] = false;
        //    $data['msg'] = 'У Вас нет прав';
        //}
        if ($data['state'] == true && !$product) {
            $data['state'] = false;
            $data['msg'] = 'Укажите деталь';
        }
        if ($data['state'] == true && $product['type'] == 1) {
            $data['state'] = false;
            $data['msg'] = 'На услугу заказ создать нельзя';
        }
        if ($data['state'] == true && count($orders) > $count) {
            $data['state'] = false;
            $data['msg'] = 'Ремонтов не может быть больше чем количество в заказе';
        }
        // проверка на создание заказа с ценой 0
        if ($price == 0 && $this->all_configs['configs']['suppliers-orders-zero'] == false) {
            $data['state'] = false;
            $data['msg'] = 'Укажите цену больше 0';
        }

        if ($data['state'] == true && $product) {
            // создаем заказ
            try {
                $id = $this->all_configs['db']->query('INSERT INTO {contractors_suppliers_orders}
                        (price, date_wait, supplier, its_warehouse, goods_id, user_id, count, comment, group_parent_id, num, warehouse_type)
                        VALUES (?i, ?, ?n, ?n, ?i, ?n, ?i, ?, ?n, ?n, ?i)',
                    array($price, date("Y-m-d H:i:s", 86399 + strtotime($date)), $supplier, $its_warehouse,
                        $product_id, $user_id, $count, $comment, $group_parent_id, $num, $warehouse_type), 'id');
                FlashMessage::set(l('Заказ успешно создан'));
            } catch (Exception $e) {
                $data['state'] = false;
                $data['msg'] = 'Заказ с таким номером уже существует';
                $id = 0;
            }
            if ($data['state'] == true && $id > 0) {
                /*if ($num == 0) {
                    try {
                        $this->all_configs['db']->query(
                            'UPDATE {contractors_suppliers_orders} SET num=?i WHERE id=?i', array($id, $id));
                    } catch(Exception $e) {}
                }*/
                $data['id'] = $id;
                $data['state'] = true;
                /*if ($group_parent_id === null) {
                    $this->all_configs['db']->query('UPDATE {contractors_suppliers_orders}
                            SET group_parent_id=?i WHERE id=?i', array($id, $id));
                    $group_parent_id = $id;
                }

                $this->all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET parent_id=?i WHERE id=?i',
                    array($id, $id));*/

                // обновляем дату поставки товара
                $this->all_configs['manageModel']->update_product_wait($product_id);

                // связь между заказами
                $this->orders_link($id, $orders);

                // изменения
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($_SESSION['id'], 'add-warehouse-order', $mod_id, intval($id)));

                $this->buildESO($id);
            }
        }

        return $data;
    }

    /**
     * @param     $client_order_id
     * @param     $text
     * @param int $private
     * @return null
     */
    function add_client_order_comment($client_order_id, $text, $private = 0)
    {
        $result = null;
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : null;
        $order = $this->all_configs['db']->query('SELECT * FROM {orders} WHERE id=?i', array($client_order_id))->row();

        $ok = true;
        // дополнительная проверка - один раз в день
        if ($text == 'Запчасть заказана') {
            $id = $this->all_configs['db']->query('SELECT id FROM {orders_comments}
                WHERE DATE(date_add)=DATE(NOW()) AND text=? AND order_id=?i AND private=0',
                array($text, $client_order_id))->el();
            $ok = $id ? false : $ok;
        }

        if ($order && trim($text) && $ok == true) {
            $result = $this->all_configs['db']->query(
                'INSERT INTO {orders_comments} (text, user_id, private, order_id) VALUES (?, ?n, ?i, ?i)',
                array(trim($text), $user_id, $private, $order['id']), 'id');

            // смс
            if (isset($order['notify']) && $order['notify'] == 1 && $private == 0) {
                $data = send_sms($order['phone'], $text);
                //$return['msg'] = $result['msg'];
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    function show_filter_service_center(){
        $wh_groups = $this->all_configs['db']->query('SELECT id, name FROM {warehouses_groups} ORDER BY id', array())->assoc();
        $orders_html = '<div class="input-group"><p class="form-control-static">' . l('Сервисный Центр') . ':</p> ';
        $orders_html .= '<span class="input-group-btn"><select class="multiselect form-control" multiple="multiple" name="wh_groups[]">';
        $wg_get = isset($_GET['wg']) ? explode(',', $_GET['wg']) :
                  (isset($_GET['wh_groups']) ? $_GET['wh_groups'] : array());
        foreach ($wh_groups as $wh_group) {
            $orders_html .= '<option ' . ($wg_get && in_array($wh_group['id'], $wg_get) ? 'selected' : '');
            $orders_html .= ' value="' . $wh_group['id'] . '">' . $wh_group['name'] . '</option>';
        }
        $orders_html .= '</select></span></div>';
        return $orders_html;
    }

    /**
     * @param bool   $show_my
     * @param bool   $show_nav
     * @param bool   $inner_wrapper
     * @param string $hash
     * @return string
     */
    function show_filters_suppliers_orders($show_my = false ,$show_nav = true,$inner_wrapper = true,$hash='show_suppliers_orders')
    {
        $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : '')
            . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
            . (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : '');

        $count = $this->all_configs['db']->query('SELECT COUNT(id) FROM {contractors_suppliers_orders}', array())->el();
        $query = !array_key_exists('manage-qty-so-only-debit', $this->all_configs['configs']) || $this->all_configs['configs']['manage-qty-so-only-debit'] == false ? 'confirm=0' : 'count_come<>count_debit AND count_come > 0';
        $count_unworked = $this->all_configs['db']->query('SELECT COUNT(id) FROM {contractors_suppliers_orders}
            WHERE ?query', array($query))->el();
        $count_marked = $this->all_configs['db']->query('SELECT COUNT(id) FROM {users_marked}
            WHERE user_id=?i AND type=?', array($_SESSION['id'], 'so'))->el();
            $suppliers = $this->all_configs['db']->query('SELECT id, title FROM {contractors} WHERE type IN (?li)',
                array($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders']))->assoc();

        return $this->view->renderFile('suppliers.class/show_filters_suppliers_orders', array(
            'show_my' => $show_my,
            'inner_wrapper' => $inner_wrapper,
            'show_nav' => $show_nav,
            'controller' => $this,
            'suppliers' => $suppliers,
            'count' => $count,
            'count_marked' => $count_marked,
            'count_unworked' => $count_unworked,
            'date' => $date, 
            'hash' => $hash
        ));
    }

    /**
     * @param      $orders
     * @param bool $only_debit
     * @param bool $only_pay
     * @return string
     */
    function show_suppliers_orders($orders, $only_debit = false, $only_pay = false)
    {
        return $this->view->renderFile('suppliers.class/show_suppliers_orders', array(
            'controller' => $this,
            'orders' => $orders,
            'only_debit' => $only_debit,
            'only_pay' => $only_pay,
            
        ));
    }

    /**
     * @param      $so_id
     * @param      $co_id
     * @param null $last_so_supplier
     * @return array
     */
    function orders_link($so_id, $co_id, $last_so_supplier = null)
    {
        $data = array('state' => false, 'msg' => 'Заказ не найден', 'links' => array());
        $co_ids = array_filter(array_unique((array)$co_id));

        // достаем заказ поставщику
        $so = $this->all_configs['db']->query('SELECT o.*, g.title, g.type
                FROM {goods} as g, {contractors_suppliers_orders} as o
                WHERE o.id=?i AND g.id=o.goods_id GROUP BY o.id',
            array($so_id))->row();

        // заявки
        $links = (array)$this->all_configs['db']->query(
            'SELECT l.client_order_id, i.id FROM {orders_suppliers_clients} as l
            LEFT JOIN {warehouses_goods_items} as i ON i.supplier_order_id=l.supplier_order_id AND i.order_id=l.client_order_id
            WHERE l.supplier_order_id=?i',
            array($so_id))->vars();


        if (count($co_ids) == 0) {
            $data['msg'] = 'Введите номер заказа';
            $data['state'] = true;
        }

        if ($so && count($co_ids) > 0) {

            if ($so['avail'] == 0) {
                return array('msg' => 'Заказ отменен', 'state' => false);
            }
            // достаем заказ(ы) клиента(ов)
            $cos = $this->all_configs['db']->query('SELECT o.*, og.item_id, og.id as order_goods_id
                  FROM {orders} as o, {orders_goods} as og WHERE o.id IN (?li) AND og.goods_id=?i AND o.id=og.order_id',
                array($co_ids, $so['goods_id']))->assoc('id');

            if ($cos) {
                //if (count($cos) > ($so['count'] - count($links))) {
                if (count($links) > $so['count']) {
                    $data['msg'] = 'Осталась ' . ($so['count'] - count($links)) . ' свободных заявок';
                } else {
                    $data['state'] = true;
                    $data['msg'] = 'Успешно сохранено';

                    foreach ($cos as $co) {

                        if (array_key_exists($co['id'], $links)) {
                            $data['links'][$co['id']] = $co;
                            if ($last_so_supplier == 0 && $so['supplier'] > 0) {
                                $text = 'Запчасть заказана';
                                $this->add_client_order_comment(intval($co['id']), $text);
                            }
                        } else {
                            $cso_id = $this->all_configs['db']->query('INSERT IGNORE INTO {orders_suppliers_clients}
                                    (client_order_id, supplier_order_id, goods_id, order_goods_id) VALUES (?i, ?i, ?i, ?i)',
                                array($co['id'], $so['id'], $so['goods_id'], $co['order_goods_id']), 'id');

                            update_order_status($co, $this->all_configs['configs']['order-status-waits']);

                            // добавляем публичный комментарий заказу клитента
                            if ($cso_id) {
                                $data['links'][$co['id']] = $co;
                                if ($so['supplier'] > 0) {
                                    if ($so['count_debit'] > 0) {
                                        $text = 'Запчасть была оприходована ';
                                    } elseif ($so['count_come'] > 0) {
                                        $text = 'Запчасть была принята ';
                                    } else {
                                        $text = 'Запчасть заказана';
                                    }
                                } else {
                                    $text = 'Отправлен запрос на покупку запчасти. Ожидаем ответ.';
                                }
                                $this->add_client_order_comment(intval($co['id']), $text);
                            } else {
                                $data['msg'] = 'Заявка уже создана';
                            }
                        }
                    }
                }
            } else {
                $data['msg'] = 'В заявке нет необходимости';
            }
        }

        if ($so) {

            $query = '';

            $items = array_keys(array_filter($links));

            if (count($items) > count($co_ids)) {
                $data['state'] = false;
                $data['msg'] = 'Отвяжите серийный номер в заказах: ' . implode(', ', $items);
            }

            $co_ids += $items;

            if (count($co_ids) > 0) {
                $query = $this->all_configs['db']->makeQuery('?query AND client_order_id NOT IN (?li)',
                    array($query, $co_ids));
            }

            // удаляем
            $ar = $this->all_configs['db']->query(
                'DELETE FROM {orders_suppliers_clients} WHERE supplier_order_id=?i ?query',
                array($so['id'], $query), 'ar');

            if ($ar) {
                $data['state'] = true;
                $data['msg'] = 'Успешно сохранено';

                $diff = array_diff(array_keys($links), $co_ids);
                if (count($diff) > 0) {
                    $links = $this->all_configs['db']->query('SELECT o.manager, g.order_id, g.title
                            FROM {orders} as o, {orders_goods} as g
                            WHERE o.id=g.order_id AND g.goods_id=?i AND o.id IN (?li)',
                        array($so['goods_id'], $diff))->assoc();

                    if ($links) {
                        include_once $this->all_configs['sitepath'] . 'mail.php';
                        $messages = new Mailer($this->all_configs);

                        foreach ($links as $link) {
                            $href = $this->all_configs['prefix'] . 'orders/create/' . $link['order_id'];
                            $content = 'Необходимо заказать запчасть "' . htmlspecialchars($link['title']) . '" для заказа <a href="' . $href . '">№' . $link['order_id'] . '</a>';
                            $messages->send_message($content, 'Необходимо заказать запчасть', $link['manager'], 1);
                        }
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param $order_id
     */
    function operations($order_id)
    {
        $data = array();
        $data['state'] = true;
        $data['content'] = '<h5 class="text-danger">Заказ не найден</h5>';

        if ($order_id > 0) {
            $order = $this->all_configs['db']->query('SELECT * FROM {contractors_suppliers_orders} WHERE id=?i',
                array($order_id))->row();

            if ($order) {
                $data['btns'] = '<input onclick="orders_link(this, \'.btn-open-orders-link-' . $order_id . '\')" class="btn" type="button" value="'.l('Сохранить').'" />';

                $data['content'] = '<h6>' . l('Ремонты ожидающие данную запчасть') . '</h6>';
                $data['content'] .= '<form id="form-orders-links" method="post">';
                $data['content'] .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';

                // звязки заказов
                $clients_orders = (array)$this->all_configs['db']->query(
                    'SELECT id, client_order_id FROM {orders_suppliers_clients} WHERE supplier_order_id=?i',
                    array($order_id))->vars();

                $data['content'] .= '<div class="form-group"><label class="control-label">' . l('Номер ремонта') . ': </label>';
                for ($i = 0; $i < ($order['count_come'] > 0 ? $order['count_come'] : $order['count']); $i++) {
                    $co_id = current($clients_orders);
                    $data['content'] .= '
                        <div class="'.($co_id ? 'input-group ' : '').'form-group">
                            <input class="clone_clear_val form-control" type="text" value="' . $co_id . '" name="so_co[]">
                            '.($co_id ? '
                                <span class="input-group-addon">
                                    <a target="_blank" href="'.$this->all_configs['prefix'].'orders/create/'.$co_id.'">' . l('перейти в заказ клиента') . '</a>
                                </span>'
                              : '').'
                        </div>
                    ';
                    next($clients_orders);
                }
                $data['content'] .= '</div></form>';
            }
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

    /**
     * @return string
     */
    public function append_js()
    {
        return "<script type='text/javascript' src='{$this->all_configs['prefix']}js/suppliers-orders.js?4'></script>";
    }

    /**
     * @param $product
     */
    function exportProduct($product)
    {

        if ($this->all_configs['configs']['onec-use'] == false)
            return;

        if (array_key_exists('rounding-goods', $this->all_configs['configs']) && $this->all_configs['configs']['rounding-goods'] > 0) {
            $sum1 = round((($product['price']/100)*(getCourse($this->all_configs['settings']['currency_suppliers_orders'])/100))/$this->all_configs['configs']['rounding-goods'])*$this->all_configs['configs']['rounding-goods'];
            $sum2 = round((($product['price_purchase']/100)*(getCourse($this->all_configs['settings']['currency_suppliers_orders'])/100))/$this->all_configs['configs']['rounding-goods'])*$this->all_configs['configs']['rounding-goods'];
            $sum3 = round((($product['price_wholesale']/100)*(getCourse($this->all_configs['settings']['currency_suppliers_orders'])/100))/$this->all_configs['configs']['rounding-goods'])*$this->all_configs['configs']['rounding-goods'];
        } else {
            $sum1 = round(($product['price']/100)*(getCourse($this->all_configs['settings']['currency_suppliers_orders'])))/100;
            $sum2 = round(($product['price_purchase']/100)*(getCourse($this->all_configs['settings']['currency_suppliers_orders'])))/100;
            $sum3 = round(($product['price_wholesale']/100)*(getCourse($this->all_configs['settings']['currency_suppliers_orders'])))/100;
        }

        $doc = array(
            'Предложения'  =>   array(
                'Предложение'   =>  array(
                    'Ид'            =>  $product['code_1c'],
                    'Штрихкод'      =>  $product['barcode'],
                    'Наименование'  =>  $product['title'],
                    'Цены'          => array(
                        0   =>  array(
                            'Цена'          => array(
                                'Представление' =>  $sum1 . ' '.viewCurrency().' за шт',
                                'ИдТипаЦены'    =>  $this->all_configs['configs']['onec-code-price'],
                                'ЦенаЗаЕдиницу' =>  $sum1,
                                'Валюта'        =>  ''.viewCurrency().'',
                                'Единица'       =>  'шт',
                                'Коэффициент'   =>  1,
                                'Курс'          =>  (getCourse($this->all_configs['settings']['currency_suppliers_orders'])/100),
                            ),
                        ),
                        1   =>  array(
                            'Цена'          => array(
                                'Представление' =>  $sum2 . ' '.viewCurrency().' за шт',
                                'ИдТипаЦены'    =>  $this->all_configs['configs']['onec-code-price_purchase'],
                                'ЦенаЗаЕдиницу' =>  $sum2,
                                'Валюта'        =>  ''.viewCurrency().'',
                                'Единица'       =>  'шт',
                                'Коэффициент'   =>  1,
                                'Курс'          =>  (getCourse($this->all_configs['settings']['currency_suppliers_orders'])/100),
                            ),
                        ),
                        2   =>  array(
                            'Цена'          => array(
                                'Представление' =>  $sum3 . ' '.viewCurrency().' за шт',
                                'ИдТипаЦены'    =>  $this->all_configs['configs']['onec-code-price_wholesale'],
                                'ЦенаЗаЕдиницу' =>  $sum3,
                                'Валюта'        =>  ''.viewCurrency().'',
                                'Единица'       =>  'шт',
                                'Коэффициент'   =>  1,
                                'Курс'          =>  (getCourse($this->all_configs['settings']['currency_suppliers_orders'])/100),
                            ),
                        ),
                    ),
                    'Количество'    =>  $product['exist'],
                )
            )
        );

        $xml = $this->assocArrayToXML($doc);

        $f = fopen($this->all_configs['sitepath'] . '1c/goods/offers_' . $product['id'] . '.xml', 'w+');
        fwrite($f, "\xEF\xBB\xBF" .$xml);
        fclose($f);

        $doc = array(
            'Предложения'  =>   array(
                'Предложение'   =>  array(
                    "Ид"                    =>  $product['code_1c'],
                    "Артикул"               =>  $product['article'],
                    "Наименование"          =>  $product['title'],
                    "БазоваяЕдиница"        =>  "шт",
                    "ПолноеНаименование"    =>  $product['title'],
                    'Описание'              =>  $product['content'],
                )
            ),
        );
        if ( $product['avail'] != 1 )
            $doc['Предложения']['Предложение']['Статус'] = "Удален";
        if ( isset($product['hotline_url']) )
            $doc['Предложения']['Предложение']["ЗначенияСвойств"] = array(
                "ЗначенияСвойства"          =>  array(
                    "Ид"                        =>  $this->all_configs['configs']['onec-code-hotline'],
                    "Значение"                  =>  $product['hotline_url'],
                )
            );


        $xml = $this->assocArrayToXML($doc);

        $f = fopen($this->all_configs['sitepath'] . '1c/goods/import_' . $product['id'] . '.xml', 'w+');
        fwrite($f, "\xEF\xBB\xBF" . $xml);
        fclose($f);
    }

    /**
     * @param      $wh_id
     * @param null $location_id
     * @return string
     */
    public function gen_locations($wh_id, $location_id = null)
    {
        $out = '';
        $wh_id = array_filter(is_array($wh_id) ? $wh_id : explode(',', $wh_id));
        $location_id = $location_id ? (array_filter(is_array($location_id) ? $location_id : explode(',', $location_id))) : array();

        if (count($wh_id) > 0) {
            $locations = $this->all_configs['db']->query(
                'SELECT id, location FROM {warehouses_locations} WHERE wh_id IN (?li)', array($wh_id))->vars();
            if ($locations) {
                foreach ($locations as $id=>$location) {
                    $out .= '<option ' . (in_array($id, $location_id) ? 'selected' : '') . ' value="' . $id . '">' . htmlspecialchars($location) . '</option>';
                }
            }
        }

        return $out;
    }

    /**
     * @param null $goods
     * @param null $order_id
     * @param bool $all
     * @param int  $typeahead
     * @param bool $is_modal
     * @return string
     */
    function create_order_block($goods = null, $order_id = null, $all = true, $typeahead = 0, $is_modal = false)
    {
        $new_device_form = '<div id="new_device_form" class="typeahead_add_form_box theme_bg new_device_form p-md"></div>';
        $new_supplier_form = '<div id="new_supplier_form" class="typeahead_add_form_box theme_bg new_supplier_form p-md"></div>';
        $suppliers = null;
        if (array_key_exists('erp-contractors-use-for-suppliers-orders', $this->all_configs['configs'])
                && count($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders']) > 0) {
            $suppliers = $this->all_configs['db']->query('SELECT id, title FROM {contractors} WHERE type IN (?li) ORDER BY title',
                array(array_values($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders'])))->assoc();
        }
        $goods_html = '';
        if(isset($_SESSION['suppliers_edit_order_msg'])){
            $goods_html .=
                '<div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    '.$_SESSION['suppliers_edit_order_msg'].'
                </div>
            ';
            unset($_SESSION['suppliers_edit_order_msg']);
        }
        if($order_id){
            $goods_html .= '<h3>' . l('Редактирование заказа поставщику') . ' №'.$order_id.'</h3>';
        }else{
            $goods_html .= '<h3>' . l('Создание нового заказа поставщику') . '</h3>';
        }
        $goods_html .= '<br><div class="row row-15"><div class="col-sm-'.($is_modal ? '12' : '6').'"><form data-validate="parsley" id="suppliers-order-form" method="post">';
        $disabled = '';
        $info_html = '';
        $so_co = '<div class="relative"><input type="text" name="so_co[]" class="form-control clone_clear_val" /><i class="glyphicon glyphicon-plus cloneAndClear"></i></div>';
        $has_orders = false;
        if ($suppliers) {
            $order = array(
                'price'     =>  '',
                'count'     =>  '',
                'date_wait' =>  '',//date("d.m.Y"),
                'supplier'  =>  '',
                'goods_id'  =>  '',
                'title'     =>  'Создать заказ',
                'btn'       =>  '<input type="button" class="btn submit-from-btn" onclick="create_supplier_order(this)" value="' . l('Создать') . '" />',
                'product'   =>  '',
                'comment'   =>  '',
                'unavailable' => 0,
                'location'  => '',
                'id'        => '',
                'num'       => '',
                'avail'     => 1,
                'warehouse_type'=> 0,
            );
            if ( $order_id ) {
                $order = $this->all_configs['db']->query('SELECT o.id, o.price, o.`count`, o.date_wait, o.supplier, o.location_id,
                      o.goods_id, o.comment, o.user_id, o.sum_paid, o.count_come, o.count_debit, u.email, u.fio, o.avail,
                      u.login, o.wh_id, w.title as wh_title, o.confirm, o.sum_paid, o.unavailable, l.location, o.num,
                      GROUP_CONCAT(i.id) as items, o.warehouse_type
                    FROM {contractors_suppliers_orders} as o
                    LEFT JOIN {users} as u ON o.user_id=u.id
                    LEFT JOIN {warehouses} as w ON o.wh_id=w.id
                    LEFT JOIN {warehouses_locations} as l ON l.id=o.location_id
                    LEFT JOIN {warehouses_goods_items} as i ON i.supplier_order_id=o.id
                    WHERE o.id=?i GROUP BY o.id', array($order_id))->row();
                if ($order) {
                    $so_co = '';
                    $cos = (array)$this->all_configs['db']->query('SELECT id, client_order_id FROM {orders_suppliers_clients}
                        WHERE supplier_order_id=?i', array($order_id))->vars();
                    if($cos){
                        $has_orders = true;
                    }
                    for ($i = 0; $i < ($order['count_come'] > 0 ? $order['count_come'] : $order['count']); $i++) {
                        $co = current($cos);
                        $so_co .= '<input type="text" name="so_co[]" readonly class="form-control" value="' . $co . '" />';
                        next($cos);
                    }
                    $order['title']     =  'Редактировать заказ';
                    $order['date_wait']  =  date("d.m.Y", strtotime($order['date_wait']));
                    $order['price']    /=  100;
                    if ($order['confirm'] == 0 && $order['avail'] == 1 && ((/*$order['user_id'] == $_SESSION['id'] &&*/ $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') && $order['sum_paid'] == 0 && $order['count_come'] == 0) || $this->all_configs['oRole']->hasPrivilege('site-administration'))) {
                        $order['btn']   =  '<input type="button" class="btn btn-mini btn-success" onclick="edit_supplier_order(this)" value="'.l('Сохранить').'" />';
                        //$order['btn']  .=  ' <input type="button" class="btn btn-mini btn-primary" onclick="close_supplier_order(this, \'' . $order_id . '\')" value="Закрыть" />';
                        //$order['btn']  .=  ' <input type="button" class="btn btn-mini btn-danger" onclick="remove_supplier_order(this, \'' . $order_id . '\')" value="' . l(value='" . l('Удалить') . "') . '" />';
                        $order['btn']  .=  ' <input ' . ($order['avail'] == 1 ? '' : 'disabled') . ' type="button" class="btn btn-mini btn-warning" onclick="avail_supplier_order(this, \'' . $order_id . '\', 0)" value="Отменить" />';
                        $order['btn']  .=  ' <input ' . ($order['unavailable'] == 1 ? 'disabled' : '') . ' type="button" class="btn btn-mini" onclick="end_supplier_order(this, \'' . $order_id . '\')" value="Запчасть не доступна к заказу" />';
                    } else {
                        $order['btn']   =  '';
                        $disabled = 'disabled';
                    }
                    $order['product']   =   $this->all_configs['db']->query('SELECT title FROM {goods} WHERE id=?i', array($order['goods_id']))->el();

                    if ($order['count_debit'] > 0) {
                        $info_html .= '<div class="form-group"><label>' . l('Создал') .':&nbsp;</label>'
                            . '' . get_user_name($order) . '</div>';
                    }
                    if ($order['count_come'] > 0) {
                        $info_html .= '<div class="form-group"><label>' . l('Принято') . ':&nbsp;</label>'
                            . '' . $order['count_come'] . ' ' . l('шт.') . '</div>';
                    }
                    if ($order['count_debit'] > 0) {
                        $url = $this->all_configs['prefix'] . 'print.php?act=label&object_id=' . $order['items'];
                        $print_btn = '<a target="_blank" title="Печать" href="' . $url . '"><i class="fa fa-print"></i></a>';

                        $info_html .= '<div class="form-group"><label>' . l('Оприходовано') . ':&nbsp;</label>'
                            . '' . $order['count_debit'] . ' ' . l('шт.') . ' ' . $print_btn . '</div>';
                    }
                    if ($order['wh_id'] > 0) {
                        $info_html .= '<div class="form-group"><label>' . l('Склад') . ':&nbsp;</label>'
                            . '<a class="hash_link" href="' . $this->all_configs['prefix'] . 'warehouses?whs=' . $order['wh_id'] . '#show_items">' . $order['wh_title'] . '</a></div>';
                        $info_html .= '<div class="form-group"><label>' . l('Локация') . ':&nbsp;</label>'
                            . '' . $order['location'] . '</div>';
                    }
                    $info_html .= '</div><h4>' . l('Операции') . '</h4>';
                    $info_html .= $this->get_transactions($this->currencies, false, null, true, array('supplier_order_id' => $order_id), false);

                    if ($order['sum_paid'] == 0 /*&& $order['count_come'] > 0*/ && $order['count_debit'] != $order['count_come']/* && $order['wh_id'] > 0*/) {
                        $goods_html .= '<div class="form-group"><label>' . l('Склад') . ': </label>'
                            . '<select name="warehouse" ' . $disabled . ' onchange="change_warehouse(this)" class="select-warehouses-item-move form-control"><option value=""></option>';
                        // список складов
                        //if ($warehouses == null)
                        $warehouses = $this->all_configs['db']->query('SELECT id, title FROM {warehouses} as w WHERE consider_store=1 ORDER BY title', array())->assoc();
                        if ($warehouses) {
                            foreach ($warehouses as $warehouse) {
                                if ($warehouse['id'] == $order['wh_id'])
                                    $goods_html .= '<option selected value="' . $warehouse['id'] . '">' . $warehouse['title'] . '</option>';
                                else
                                    $goods_html .= '<option value="' . $warehouse['id'] . '">' . $warehouse['title'] . '</option>';

                            }
                        }
                        $goods_html .= '</select></div>';
                        $goods_html .= '<div class="form-group"><label>' . l('Локация') . ':</label><select class="form-control select-location" name="location">';
                        $goods_html .= $this->gen_locations($order['wh_id'], $order['location_id']);
                        $goods_html .= '</select></div>';
                    }
                } else {
                    $order = array(
                        'price'     =>  '',
                        'count'     =>  '',
                        'date_wait' =>  '',
                        'supplier'  =>  '',
                        'goods_id'  =>  '',
                        'title'     =>  'Создать заказ',
                        'btn'       =>  '<input type="submit" class="btn btn-primary submit-from-btn" name="new-order" value="Создать заказ поставщику" />',
                        'product'   =>  '',
                        'comment'   =>  '',
                        'unavailable' => 0,
                        'location'  => '',
                        'id'        => '',
                        'num'       => '',
                        'avail'     => 1,
                        'warehouse_type'=> 0,
                    );
                }
                $goods_html .= '<input type="hidden" name="order_id" value="' . $order_id . '"/>';

            }

            if ($all == true) {
                $goods_html .= '
                    <div class="form-group relative">
                        <label>' . l('Поставщик') . '<b class="text-danger">*</b>: </label>
                        <div class="input-group">
                            <select class="form-control" data-required="true" name="warehouse-supplier" ' . $disabled . '><option value=""></option>';
                            foreach ( $suppliers as $supplier ) {
                                if ($order['supplier'] == $supplier['id'])
                                    $goods_html .= '<option selected value="' . $supplier['id'] . '">' . $supplier['title'] . '</option>';
                                else
                                    $goods_html .= '<option value="' . $supplier['id'] . '">' . $supplier['title'] . '</option>';
                            }
//                                <button type="button" onclick="alert_box(this, false, \'create-contractor-form\',{callback: \'quick_create_supplier_callback\'},null,\'accountings/ajax\')" class="btn btn-info">'.l('Добавить').'</button>
                $goods_html .= '
                            </select>
                            <div class="input-group-btn">
                                <button type="button" data-form_id="new_supplier_form" data-action="accountings/ajax?act=create-contractor-form-no-modal" class="typeahead_add_form btn btn-info" data-id="supplier_creator">'.l('Добавить').'</button>
                            </div>
                        </div>
                        '.($is_modal ? $new_supplier_form : '').'
                    </div>
                ';
                $goods_html .= '<div class="form-group"><label>' . l('Дата поставки') . '<b class="text-danger">*</b>: </label>
                                <input class="datetimepicker form-control" ' . $disabled . ' data-format="yyyy-MM-dd" type="text" name="warehouse-order-date" data-required="true" value="'.($order['date_wait'] ? date('Y-m-d', strtotime($order['date_wait'])) : '').'" />
                                </div>';
            }
            if ($goods) {
                //$categories_html = $this->gen_categories_selector('5', $disabled);
                $goods_html .= '<div class="form-group relative"'.($has_orders ? ' onclick="alert(\''.l('Данный заказ поставщику создан на основании заказа клиенту. Вы не можете изменить запчасть в данном заказе.').'\');return false;"' : '').'><label>' . l('Запчасть') . '<b class="text-danger">*</b>: </label>'
                    .typeahead($this->all_configs['db'], 'goods-goods', true, $order['goods_id'],
                               (15 + $typeahead), 'input-xlarge', 'input-medium', '', false, false, '', false, l('Введите'),
                               array('name' => l('Добавить'),
                                     'action' => 'products/ajax/?act=create_form',
                                     'form_id' => 'new_device_form'), $has_orders)
                    .($is_modal ? $new_device_form : '') . '</div>';
            }
            $goods_html .= '<div class="form-group"><label for="warehouse_type">' . l('Тип поставки') . '<b class="text-danger">*</b>: </label>'
                . '<div class="radio"><label><input data-required="true" type="radio" name="warehouse_type" value="1" ' . ($order['warehouse_type'] == 1 ? 'checked' : '') . ' />' . l('Локально') . '</label></div>'
                . '<div class="radio"><label><input type="radio" name="warehouse_type" data-required="true" value="2" ' . ($order['warehouse_type'] == 2 ? 'checked' : '') . ' />' . l('Заграница') . '</label></div></div>';

            $goods_html .= '<div class="form-group"><label>' . l('Номер') . ': </label>'
                . '<input type="text" ' . $disabled . ' name="warehouse-order-num" class="form-control" value="' . $order['num'] . '"/></div>';
            $goods_html .= '<div class="form-group"><label>' . l('Количество') . '<b class="text-danger">*</b>: </label>'
                . '<input type="text" ' . $disabled . ' data-required="true" onkeydown="return isNumberKey(event)" name="warehouse-order-count" class="form-control" value="' . htmlspecialchars($order['count']) . '"/></div>';
            $goods_html .= '<div class="form-group"><label>' . l('Цена за один') . ' ('.viewCurrencySuppliers('shortName').')<b class="text-danger">*</b>: </label>'
                . '<input type="text" ' . $disabled . ' data-required="true" onkeydown="return isNumberKey(event, this)" name="warehouse-order-price" class="form-control" value="' . htmlspecialchars($order['price']) . '"/></div>';
            $goods_html .= '<div class="form-group"><label>' . l('Примечание') . ': </label>'
                . '<textarea ' . $disabled . ' name="comment-supplier" class="form-control">' . htmlspecialchars($order['comment']) . '</textarea></div>';

            $goods_html .= '<div class="form-group"><label>' . l('номер ремонта') . '</label> ('. l('если запчасть заказывается под конкретный ремонт') . '): ' . $so_co . '</div>';
            $goods_html .= '<div id="for-new-supplier-order"></div>';
            if ($all == true) {
                $goods_html .= '<div class="form-group">' . $order['btn'] . '</div>';
            }
        } else {
            $goods_html .= '<p  class="text-danger">' . l('Нет поставщиков') . '</p>';
        }

        $goods_html .= '
                    </form>
                </div>
                <div class="col-sm-6 relative">
                    '.(!$is_modal ? $new_supplier_form : '').'
                    '.(!$is_modal ? $new_device_form : '').'
                </div>
            </div>
            '.$info_html.'
        ';
        $goods_html .= $this->append_js();

        return $goods_html;
    }

    /**
     * @param $order_id
     * @param $type
     * @return bool|void
     */
    function exportSupplierOrder($order_id, $type)
    {
        if ($this->all_configs['configs']['onec-use'] == false)
            return;

        $order = null;

        if (array_key_exists('erp-contractors-use-for-suppliers-orders', $this->all_configs['configs']) && count($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders']) > 0) {
            $order = $this->all_configs['db']->query('SELECT o.id, o.price, o.count, o.count_come, o.date_add, o.date_come, o.date_wait,
                    o.its_warehouse, o.goods_id, o.user_id, s.code_1c, s.title, u.fio
                FROM {contractors_suppliers_orders} as o
                LEFT JOIN (SELECT `code_1c`, `id`, `title`, `type` FROM {contractors})s ON s.id=o.supplier AND s.type IN (?li)
                LEFT JOIN (SELECT `fio`, `id` FROM {users}) u ON u.id=o.user_id
                WHERE o.id=?i', array(array_values($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders']), $order_id))->row();
        }

        if (!$order)
            return false;

        $code_1c = $this->all_configs['db']->query('SELECT code_1c FROM {goods} WHERE id=?i', array($order['id']))->el();
        $order['goods_code_1c'] = trim($code_1c);

        //$its_warehouse = 'Чужой склад';
        //if ( $order['its_warehouse'] == 1 )
        //    $its_warehouse = 'Свой склад';

        $types = array(
            1   =>  'Создан заказ поставщику со статусом "Согласован"',
            2   =>  'Статус заказа меняется с "согласован" на "к поступлению"',
            3   =>  'Заказ поставщику редактируется',
            4   =>  'Заказ поставщику удаляется',
            5   =>  'Заказ поставщику удаляется. Создается 2 новых заказа: один со статусом "к поступлению" и сегодняшней датой, второй со статусом "согласован" и датой на когда ожидаются',
        );

        $status = $types[$type];

        $doc = array(
            'Документы' =>  array(
                'Документ'  =>  array(
                    'Ид'            =>  $order['id'],
                    'Номер'         =>  $order['id'],
                    'ДатаСоздания'  =>  date('Y-m-d', strtotime($order['date_add'])),
                    'ВремяСоздания' =>  date('H:i:s', strtotime($order['date_add'])),
                    'ДатаПрихода'   =>  date('Y-m-d', strtotime($order['date_wait'])),
                    'ВремяПрихода'  =>  date('H:i:s', strtotime($order['date_wait'])),
                    'ХозОперация'   =>  "Заказ Поставщику",
                    'Роль'          =>  "Продавец",
                    'Сумма'         =>  (($order['price']/100)*$order['count']),
                    'ЦенаЗаЕдиницу' =>  $order['price']/100,
                    'Количество'    =>  $order['count'],
                    'Валюта'        =>  "USD",
                    'ТоварИд'       =>  $order['goods_code_1c'],
                    'Контрагенты'   =>  array(
                        'Контрагент'    =>  array(
                            'Ид'                    =>  $order['code_1c'],//$order['user_id'],
                            'Наименование'          =>  $order['title'],//$order['fio'],
                            'Роль'                  =>  "Покупатель",
                            'ПолноеНаименование'    =>  $order['title'],//$order['fio'],
                        )
                    ),
                    //'ПоставщикИд'   =>  $order['code_1c'],
                    //'Поставщик'     =>  $order['title'],
                    //'Склад'         =>  $its_warehouse
                ),
                "ЗначенияРеквизитов"  =>  array(
                    "ЗначениеРеквизита"         =>  array(
                        "Наименование"              =>  "Статус заказа",
                        "Значение"                  =>  $status
                    )
                )
            )
        );

        $xml = $this->assocArrayToXML($doc);

        $f = fopen($this->all_configs['sitepath'] . '1c/orders_to_suppliers/order_' . $order['id'] . '.xml', 'w+');
        fwrite($f, "\xEF\xBB\xBF" .$xml);
        fclose($f);
    }

    /**
     * @param $order
     */
    function exportOrder($order) {

        if ($this->all_configs['configs']['onec-use'] == false)
            return;

        //if ( $configs['rounding-goods']==true )
        //    $sum = round(($order['sum']*$order['course_value'])/5)*5;
        //else
        $sum = $order['sum']/100;

        $doc = array(
            'Документ'  =>  array(
                'Ид'            =>  $order['id'],
                'Номер'         =>  $order['id'],
                'Дата'          =>  date('Y-m-d', strtotime($order['date'])),
                'ХозОперация'   =>  "Заказ товара",
                'Роль'          =>  "Продавец",
                'Курс'          =>  $order['course_value'],
                'Сумма'         =>  $sum,
                'Валюта'        =>  viewCurrency(),
                'Время'         =>  date('H:i:s', strtotime($order['date'])),
                'Комментарий'   =>  $order['comment'],
                'Контрагенты'   =>  array(
                    'Контрагент'    =>  array(
                        'Ид'                    =>  $order['user_id'],
                        'Наименование'          =>  $order['fio'],
                        'Роль'                  =>  "Покупатель",
                        'ПолноеНаименование'    =>  $order['fio'],
                    )
                )
            )
        );

        if ( isset($order['goods']) ) {
            foreach ($order['goods'] as $product) {

                if (array_key_exists('rounding-goods', $this->all_configs['configs']) && $this->all_configs['configs']['rounding-goods'] > 0) {
                    $sum = round(($product['price']/100*$order['course_value']/100)/$this->all_configs['configs']['rounding-goods'])*$this->all_configs['configs']['rounding-goods'];
                    $wsum = round(($product['warranties_cost']/100*$order['course_value']/100)/$this->all_configs['configs']['rounding-goods'])*$this->all_configs['configs']['rounding-goods'];
                } else {
                    $wsum = $product['warranties_cost']*$order['course_value']/100;
                    $sum = $product['price']*$order['course_value']/100;
                }

                $doc['Документ']['Товары'][]['Товар'] = array(
                    'Ид'                    =>  $product['code_1c'],
                    'Наименование'          =>  $product['title'],
                    'ЦенаЗаЕдиницу'         =>  $sum,
                    'Количество'            =>  $product['count'],
                    'Сумма'                 =>  $sum*$product['count']+$wsum*$product['count'],
                    'Единица'               =>  'шт',
                    'Коэффициент'           =>  1,
                    'Гарантия'              =>  array(
                        'Цена'                  =>  $wsum,
                        'Количество'            =>  $product['count'],
                        'КоличествоМесяцев'     =>  $product['warranties'],
                    ),
                    'ЗначенияРеквизитов'    =>  array(
                        array(
                            'ЗначениеРеквизита'    =>  array(
                                'Наименование'          =>  "ВидНоменклатуры",
                                'Значение'              =>  "Товар (пр. ТМЦ)",
                            ),
                        ),
                        array(
                            'ЗначениеРеквизита'     =>  array(
                                'Наименование'          =>  "ТипНоменклатуры",
                                'Значение'              =>  "Товар",
                            )
                        )
                    )
                );
            }
        }
        $doc['Документ']["ЗначенияРеквизитов"]  =  array(
            "ЗначениеРеквизита"         =>  array(
                "Наименование"              =>  "Статус заказа",
                "Значение"                  =>  "[N] Принят"
            )
        );

        $xml = $this->assocArrayToXML($doc);

        $f = fopen($this->all_configs['sitepath'] . '1c/orders/order_' . $order['id'] . '.xml', 'w+');
        fwrite($f, "\xEF\xBB\xBF" .$xml);
        fclose($f);
    }

    /**
     * @param $ar
     * @return mixed
     */
    function assocArrayToXML($ar)
    {
        $a ="КоммерческаяИнформация ВерсияСхемы=\"2.04\" ДатаФормирования=\"" . date('Y-m-d') . "\"";
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\"?><{$a}></КоммерческаяИнформация>");
        $f = create_function('$f,$c,$a','
            foreach($a as $k=>$v) {
                if(is_array($v)) {
                    if( !is_int($k) ){
                        $ch=$c->addChild($k);
                        $f($f,$ch,$v);
                    } else {
                        foreach ( $v as $sk=>$sv ) {
                            $ch=$c->addChild($sk);
                            $f($f,$ch,$sv);
                        }
                    }
                } else {
                    $c->addChild($k,$v);
                }
            }');
        $f($f,$xml,$ar);
        return $xml->asXML();
    }

    /**
     * @param     $id
     * @param int $type
     */
    function buildESO($id, $type=1)
    {
        if ($this->all_configs['configs']['onec-use'] == false)
            return;

        $uploaddir = $this->all_configs['sitepath'] . '1c/orders_to_suppliers/';
        if ( !is_dir($uploaddir) ) {
            if( mkdir($uploaddir))  {
                chmod( $uploaddir, 0777 );
            } else {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => 'Нет доступа к директории ' . $uploaddir, 'error'=>true));
                exit;
            }
        }

        /*
        if ( !$order ) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Такого заказа не существует', 'error'=>true));
            exit;
        }
        $code_1c = $this->all_configs['db']->query('SELECT code_1c FROM {goods} WHERE id=?i', array($order['id']))->el();
        if ( mb_strlen(trim($code_1c), 'UTF-8') == 0 ) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'У выбранного Вами товара нет кода 1с', 'error'=>true));
            exit;
        }
        $order['goods_code_1c'] = trim($code_1c);
        */
        $this->exportSupplierOrder($id, $type);
    }

    /**
     * @param string $num
     * @param string $disabled
     * @return string
     */
    function gen_categories_selector($num = '', $disabled = '')
    {
        $categories = $this->all_configs['db']->query('SELECT title,url,id FROM {categories} WHERE avail=1 AND parent_id=0 GROUP BY title ORDER BY title')->assoc();
        $categories_html = '<select ' . $disabled . ' class="input-small searchselect" id="searchselect-' . $num . '"';
        $categories_html .= ' onchange="javascript:$(\'#goods-'.$num.'\').attr(\'data-cat\', this.value);"';
        $categories_html .= '><option value="0">' . l('Все разделы') . '</option>';

        foreach ( $categories as $category ) {
            $categories_html .= '<option value="' . $category['id'] . '">' . $category['title'] . '</option>';
        }
        $categories_html .= '</select>';

        return $categories_html;
    }

    /**
     *
     */
    function avail_order()
    {
        // права
        if (!$this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'У Вас недостаточно прав', 'error' => true));
            exit;
        }
        // заказ ид
        if ( !isset($_POST['order_id']) ||  $_POST['order_id'] == 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Не существующий заказ', 'error'=>true));
            exit;
        }
        // достаем заказ
        $order = $this->all_configs['db']->query('SELECT * FROM {contractors_suppliers_orders} WHERE id=?i',
            array($_POST['order_id']))->row();
        // если уже принят то удалить нельзя
        if (!$order) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Не существующий заказ', 'error' => true));
            exit;
        }
        // права
        if ((/*$order['user_id'] == $_SESSION['id'] && */$this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') && $order['sum_paid'] == 0
                && $order['count_come'] == 0) || ($this->all_configs['oRole']->hasPrivilege('site-administration') && $order['confirm'] == 0)) {
        } else {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Заказ отменить нельзя', 'error' => true));
            exit;
        }
        // если уже принят то удалить нельзя
        if ($order['count_come'] > 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Заказ отменить уже нельзя', 'error' => true));
            exit;
        }

        // заявки
        $items = (array)$this->all_configs['db']->query(
            'SELECT order_id FROM {warehouses_goods_items} WHERE supplier_order_id=?i AND order_id IS NOT NULL',
            array(intval($_POST['order_id'])))->vars();

        if ($items) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Отвяжите серийный номер в заказах: ' . implode(' ,', $items), 'error' => true));
            exit;
        }

        //$this->buildESO($_POST['order_id'], 4);

        $this->all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET avail=?i WHERE id=?i',
            array(0, $_POST['order_id']));

        $links = $this->all_configs['db']->query('SELECT l.id, o.manager, g.order_id, g.title
                FROM {orders_suppliers_clients} as l, {orders_goods} as g, {orders} as o
                WHERE g.item_id IS NULL AND g.id=l.order_goods_id AND l.supplier_order_id=?i AND o.id=g.order_id',
            array(intval($_POST['order_id'])))->assoc();

        if ($links) {
            foreach ($links as $link) {
                if ($link['manager']) {
                    include_once $this->all_configs['sitepath'] . 'mail.php';
                    $messages = new Mailer($this->all_configs);
                    $href = $this->all_configs['prefix'] . 'orders/create/' . $link['order_id'];
                    $content = 'Необходимо заказать запчасть "' . htmlspecialchars($link['title']) . '" для заказа <a href="' . $href . '">№' . $link['order_id'] . '</a>';
                    $messages->send_message($content, 'Необходимо заказать запчасть', $link['manager'], 1);
                }
            }
        }
        $this->all_configs['db']->query('DELETE FROM {orders_suppliers_clients} WHERE supplier_order_id=?i',
            array(intval($_POST['order_id'])));

        //$this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
        //    array($_SESSION['id'], 'remove-supplier-order', $mod_id, intval($_POST['order_id'])));

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message'=>'Заказ успешно удален'));
        exit;
    }

    /**
     * @param $mod_id
     */
    function remove_order($mod_id)
    {
        // права
        if (!$this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'У Вас недостаточно прав', 'error' => true));
            exit;
        }
        // заказ ид
        if ( !isset($_POST['order_id']) ||  $_POST['order_id'] == 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Не существующий заказ', 'error'=>true));
            exit;
        }
        // достаем заказ
        $order = $this->all_configs['db']->query('SELECT * FROM {contractors_suppliers_orders} WHERE id=?i',
            array($_POST['order_id']))->row();
        // если уже принят то удалить нельзя
        if (!$order) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Не существующий заказ', 'error' => true));
            exit;
        }
        // права
        if ((/*$order['user_id'] == $_SESSION['id'] && */$this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') && $order['sum_paid'] == 0
                && $order['count_come'] == 0) || ($this->all_configs['oRole']->hasPrivilege('site-administration') && $order['confirm'] == 0)) {
        } else {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Заказ удалить нельзя', 'error' => true));
            exit;
        }
        // если уже принят то удалить нельзя
        if ($order['count_come'] > 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Заказ уже нельзя удалить', 'error' => true));
            exit;
        }

        // заявки
        $items = (array)$this->all_configs['db']->query(
            'SELECT order_id FROM {warehouses_goods_items} WHERE supplier_order_id=?i AND order_id IS NOT NULL',
            array(intval($_POST['order_id'])))->vars();

        if ($items) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Отвяжите серийный номер в заказах: ' . implode(' ,', $items), 'error' => true));
            exit;
        }

        $this->buildESO($_POST['order_id'], 4);

        $this->all_configs['db']->query('DELETE FROM {contractors_suppliers_orders} WHERE id=?i',
            array(intval($_POST['order_id'])));

        $links = $this->all_configs['db']->query('SELECT l.id, o.manager, g.order_id, g.title
                FROM {orders_suppliers_clients} as l, {orders_goods} as g, {orders} as o
                WHERE g.item_id IS NULL AND g.id=l.order_goods_id AND l.supplier_order_id=?i AND o.id=g.order_id',
            array(intval($_POST['order_id'])))->assoc();

        if ($links) {
            foreach ($links as $link) {
                if ($link['manager']) {
                    include_once $this->all_configs['sitepath'] . 'mail.php';
                    $messages = new Mailer($this->all_configs);
                    $href = $this->all_configs['prefix'] . 'orders/create/' . $link['order_id'];
                    $content = 'Необходимо заказать запчасть "' . htmlspecialchars($link['title']) . '" для заказа <a href="' . $href . '">№' . $link['order_id'] . '</a>';
                    $messages->send_message($content, 'Необходимо заказать запчасть', $link['manager'], 1);
                }
            }
        }
        $this->all_configs['db']->query('DELETE FROM {orders_suppliers_clients} WHERE supplier_order_id=?i',
            array(intval($_POST['order_id'])));

        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
            array($_SESSION['id'], 'remove-supplier-order', $mod_id, intval($_POST['order_id'])));

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message'=>'Заказ успешно удален'));
        exit;
    }

    /**
     * @param $mod_id
     * @param $chains
     */
    function accept_order($mod_id, $chains)
    {
        // права
        if (!$this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') && $this->all_configs['configs']['erp-use'] == false) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'У Вас недостаточно прав', 'error' => true));
            exit;
        }
        // количество
        if ( !isset($_POST['count']) || $_POST['count'] == 0 ) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Введите количество', 'error'=>true));
            exit;
        }
        // заказ ид
        if ( !isset($_POST['order_id']) || $_POST['order_id'] == 0 ) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Не существующий заказ', 'error'=>true));
            exit;
        }
        // достаем информацию о заказе
        $order = $this->all_configs['db']->query('SELECT o.*, g.title as product
            FROM {contractors_suppliers_orders} as o LEFT JOIN {goods} as g ON g.id=o.goods_id WHERE o.id=?i',
            array($_POST['order_id']))->row();

        if ( !$order ) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Не существующий заказ', 'error'=>true));
            exit;
        }
        // уже принят
        if ($order['count_come'] > 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Заказ уже принят', 'error'=>true));
            exit;
        }

        // отменен
        if ($order['avail'] == 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Заказ отменен', 'error'=>true));
            exit;
        }

        // количество пришло больше чем в заказе
        if ( $order['count'] < $_POST['count'] ) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('error' => true,'message' => 'Количество не может быть больше чем в заказе'));
            exit;
        }
        // склад
        if ( !isset($_POST['wh_id']) || $_POST['wh_id'] == 0 ) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => l('Выберите склад'), 'error'=>true));
            exit;
        }
        if ($order['supplier'] == 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'У заказа не найден поставщик', 'error' => true));
            exit;
        }
        // проверяем склад
        $wh_id = $this->all_configs['db']->query('SELECT id FROM {warehouses} WHERE id=?i AND consider_store=?i',
            array($_POST['wh_id'], 1))->el();
        if (!$wh_id || $wh_id == 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => l('Выберите склад'), 'error' => true));
            exit;
        }
        // локация
        if (!isset($_POST['location']) || $_POST['location'] == 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Выберите локацию', 'error' => true));
            exit;
        }
        // дата проверки
        if ((!isset($_POST['date_check']) || strtotime($_POST['date_check']) == 0) && !isset($_POST['without_check'])) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Укажите дату проверки', 'error' => true));
            exit;
        }
        // проверяем локацию
        $location_wh_id = $this->all_configs['db']->query('SELECT wh_id FROM {warehouses_locations} WHERE id=?i',
            array($_POST['location']))->el();
        if ($location_wh_id != $wh_id) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Выберите локацию', 'error'=>true));
            exit;
        }

        $new_order_id = null;
        // количество пришло меньше чем в заказе
        if ($order['count'] > $_POST['count']) {
            // если нет даты прихода
            if (!isset($_POST['date_come']) || empty($_POST['date_come']) || strtotime($_POST['date_come']) == 0) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array(
                    'new_date'=>1,
                    'message'=>'Вы приняли на склад не все количество товара, укажите дату, '
                              .'на когда ожидать поставку оставшегося в заказе товара?'
                ));
                exit;
            }
            if ($order['parent_id'] > 0)
                $id = $order['parent_id'];
            else
                $id = $order['id'];

            // создаем новый заказ поставщику
            $new_order_id = $this->all_configs['db']->query('INSERT INTO {contractors_suppliers_orders} (price, `count`, date_wait, supplier,
                    its_warehouse, goods_id, user_id, parent_id, number, comment) VALUES (?i, ?i, ?, ?n, ?n, ?i, ?i, ?i, ?i, ?)',
                array($order['price'], ($order['count']-$_POST['count']), date("Y-m-d H:i:s", (strtotime($_POST['date_come'])+86399)),
                    $order['supplier'], $order['its_warehouse'], $order['goods_id'], $_SESSION['id'], $id, ($order['number']+1),
                    trim($order['comment'])), 'id');
        }

        $date_check = isset($_POST['date_check']) && !isset($_POST['without_check']) ? $_POST['date_check'] : null;

        // обновляем заказ поставщику
        $this->all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET count_come=?i, date_come=NOW(),
                wh_id=?i, location_id=?i, user_id_accept=?i, date_check=?n WHERE id=?i',
            array($_POST['count'], $_POST['wh_id'], $_POST['location'], $_SESSION['id'], $date_check, $_POST['order_id']));

        // история
        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
            array($_SESSION['id'], 'accept-supplier-order', $mod_id, intval($_POST['order_id'])));

        $cos_id = $this->all_configs['db']->query(
            'SELECT id, client_order_id FROM {orders_suppliers_clients} WHERE supplier_order_id=?i AND goods_id=?i',
            array($_POST['order_id'], $order['goods_id']))->vars();

        // добавляем публичный комментарий заказу клиента
        if ($cos_id) {
            $i = 1;
            $send = array();
            foreach ($cos_id as $l_id=>$co_id) {
                if ($i > $_POST['count']) {
                    if ($new_order_id) {
                        $this->all_configs['db']->query('UPDATE {orders_suppliers_clients} SET supplier_order_id=?i WHERE id=?i',
                            array($new_order_id, $l_id));
                    } else {
                        $this->all_configs['db']->query('DELETE FROM {orders_suppliers_clients} WHERE id=?i', array($l_id));
                    }
                } else {
                    if (!isset($send[intval($co_id)])) {
                        $text = 'Ожидаемая запчасть была принята';
                        $this->add_client_order_comment(intval($co_id), $text);
                    }
                    $send[intval($co_id)] = intval($co_id);
                }
                $i++;
            }
        }
        include_once $this->all_configs['sitepath'] . 'mail.php';
        // отправляем уведомление
        // бухгалтеру
        $messages = new Mailer($this->all_configs);
        $content = 'Необходимо оплатить заказ поставщику ';
        $content .= '<a href="' . $this->all_configs['prefix'] . 'accountings?so_id=' . $order['id'] . '#a_orders-suppliers">№' . $order['id'] . '</a>';
        $messages->send_message($content, 'Оплатите заказ поставщику', 'mess-accountings-suppliers-orders', 1);
        // кладовщику
        //$messages = new Mailer($this->all_configs);
        $content = 'Необходимо оприходовать заказ поставщику ';
        $content .= '<a href="' . $this->all_configs['prefix'] . 'warehouses?so_id=' . $order['id'] . '#orders-suppliers">№' . $order['id'] . '</a>';
        /*$q = $chains->query_warehouses();
        $query_for_my_warehouses = $this->all_configs['db']->makeQuery('RIGHT JOIN {warehouses_users} as wu ON wu.'
            . trim($q['query_for_my_warehouses']) . ' AND u.id=wu.user_id AND wu.wh_id=?i', array($_POST['wh_id']));*/
        $query_for_my_warehouses = $this->all_configs['db']->makeQuery(
            'RIGHT JOIN {warehouses_users} as wu ON u.id=wu.user_id AND wu.wh_id=?i', array($_POST['wh_id']));

        $messages->send_message($content, 'Оприходуйте заказ поставщику', 'mess-warehouses-suppliers-orders', 1, $query_for_my_warehouses);
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Успешно'));
        exit;
    }

    /**
     * @param bool $show_debit_form_after
     */
    function accept_form($show_debit_form_after = false)
    {
        $data = array();
        $order_id = isset($_POST['object_id']) ? $_POST['object_id'] : 0;
        $data['state'] = true;
        $data['content'] = '<form id="form-accept-so" method="post">';

        $data['content'] .= '<div class="form-group"><label">Количество: </label><div class="controls">';
        $data['content'] .= '<input class="form-control" type="text" name="count" placeholder="количество" /></div></div>';

        $data['content'] .= '<div class="form-group"><label">' . l('Склад') . ': </label><div class="controls">';
        // список складов
        $warehouses = $this->all_configs['db']->query('SELECT id, title FROM {warehouses} WHERE consider_store=1',
            array())->vars();
        $order = $this->all_configs['db']->query('SELECT * FROM {contractors_suppliers_orders} WHERE id=?i', array($order_id))->row();
        if ($warehouses) {
            $data['content'] .= '<select name="wh_id" onchange="change_warehouse(this)" class="form-control select-warehouses-item-move"><option value=""></option>';
            foreach ($warehouses as $wh_id=>$wh_title) {
                $selected = $order && $wh_id == $order['wh_id'] ? 'selected' : '';
                $data['content'] .= '<option ' . $selected . ' value="' . $wh_id . '">' . htmlspecialchars($wh_title) . '</option>';
            }
            $data['content'] .= '</select>';
        } else {
            $data['content'] .= '<p class="text-danger">Нет складов</p>';
        }
        $data['content'] .= '</div></div>';

        $data['content'] .= '<div class="form-group"><label>' . l('Локация') . ':</label><div class="controls">';
        $data['content'] .= '<select class="multiselect select-location form-control" name="location">';
        $data['content'] .= $this->gen_locations($order ? $order['wh_id'] : 0);
        $data['content'] .= '</select></div></div>';

        $data['content'] .= '<div class="form-group"><label>Дата проверки: </label><div class="controls">';
        $data['content'] .= '<input class="form-control datetimepicker" placeholder="Дата проверки" data-format="yyyy-MM-dd hh:mm:ss" type="text" name="date_check" value="" />';
        $data['content'] .= '</div></div>';

        $data['content'] .= '<div class="form-group"><div class="checkbox"><label class="">';
        $data['content'] .= '<input type="checkbox" name="without_check" value="1" /> Без проверки</label></div></div>';

        $data['content'] .= '<div id="order_supplier_date_wait" style="display:none;" class="form-group"><label class="control-label">Дата поставки оставшегося в заказе товара: </label>
                    <div class="controls">
                    <input class="form-control datetimepicker" placeholder="дата" data-format="yyyy-MM-dd" type="text" name="date_come" value="" />
                    </div></div>';

        $data['content'] .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';
        $data['content'] .= '</form>';
        //alert_box(this, false, \'form-debit-so\')
        $callback = '';
        if($show_debit_form_after){
            $callback = ',(form_debit=function(_this){alert_box(_this,false,\'form-debit-so\',{object_id:'.$order_id.'},null,\'warehouses/ajax/\')})';
        }
        $data['btns'] =
            '<input class="btn btn-success" onclick="accept_supplier_order(this'.$callback.')" type="button" value="Принять" />';
        $data['functions'] = array('reset_multiselect()');

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

    /**
     * @param      $order_id
     * @param bool $forcibly
     * @return array
     */
    function end_order($order_id, $forcibly = false)
    {
        $data = array('state' => true, 'msg' => 'Успешно закрыт');

        $order = $this->all_configs['db']->query('SELECT * FROM {contractors_suppliers_orders} WHERE id=?i',
            array($order_id))->row();

        if ($data['state'] == true && !$order) {
            $data['state'] = false;
            $data['msg'] = 'Заказ не найден';
        }
        if ($data['state'] == true && $order['confirm'] == 0) {
            //$data['state'] = false;
            $data['msg'] = 'Заказ уже закрыт';
        }

        // отменен
        if ($data['state'] == true && $order['avail'] == 0) {
            $data['msg'] = 'Заказ отменен';
            $data['state'] = false;
        }

        if ($data['state'] == true && $order) {
            // запчастей больше не будет
            $this->all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET unavailable=?i WHERE id=?i',
                array(1, $order['id']));

            // достаем заявки
            $links = $this->all_configs['db']->query('SELECT g.order_id, o.manager, g.title
                    FROM {orders_suppliers_clients} as l, {orders_goods} as g, {orders} as o
                    WHERE l.supplier_order_id=?i AND l.order_goods_id=g.id AND g.order_id=o.id AND o.id=l.client_order_id AND o.manager > 0',
                array($order['id']))->assoc();

            if ($links) {
                include_once $this->all_configs['sitepath'] . 'mail.php';
                $messages = new Mailer($this->all_configs);

                foreach ($links as $link) {
                    $href = $this->all_configs['prefix'] . 'orders/create/' . $link['order_id'];
                    $content = 'Запчасть "' . htmlspecialchars($link['title']) . '" не доступна к заказу <a href="' . $href . '">№' . $link['order_id'] . '</a> изменились';
                    $messages->send_message($content, 'Запчасть не доступна к заказу', $link['manager'], 1);
                }
            }
        }

        return $data;
    }

    /**
     * @param      $order_id
     * @param bool $forcibly
     * @return array
     */
    function close_order($order_id, $forcibly = false)
    {
        $data = array('state' => true, 'msg' => 'Успешно закрыт');

        $order = $this->all_configs['db']->query('SELECT * FROM {contractors_suppliers_orders} WHERE id=?i',
            array($order_id))->row();

        if ($data['state'] == true && !$order) {
            $data['state'] = false;
            $data['msg'] = 'Заказ не найден';
        }
        if ($data['state'] == true && $order['confirm'] == 0) {
            //$data['state'] = false;
            $data['msg'] = 'Заказ уже закрыт';
        }
        if ($data['state'] == true && $order['avail'] == 0) {
            $data['msg'] = 'Заказ отменен';
            $data['state'] = false;
        }
        if ($data['state'] == true && $forcibly == false && (($order['count_come'] - $order['count_debit']) <> 0
                || ($order['price'] * $order['count_come'] - $order['sum_paid']) <> 0)) {
            $data['state'] = false;
            $data['msg'] = 'Заказ еще нельзя закрыть';
        }

        if ($data['state'] == true && $order) {

            // закрываем заказ
            $this->all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET confirm=?i WHERE id=?i',
                array(1, $order['id']));

            $limit = $order['count_come'] > 0 ? ($order['count_come'] - $order['count_debit']) : $order['count'];
            if ($limit > 0) {

                // достаем серийники в заказах
                $links = $this->all_configs['db']->query('SELECT l.id, i.id as item_id, l.client_order_id as co_id
                        FROM {orders_suppliers_clients} as l
                        LEFT JOIN {warehouses_goods_items} as i ON i.supplier_order_id=l.supplier_order_id
                        WHERE l.supplier_order_id=?i AND (i.order_id IS NOT NULL OR i.id IS NULL)
                        ORDER BY l.date_add LIMIT ?i',
                    array($order_id, $limit))->assoc('id');

                if ($links) {
                    // удаляем заявки
                    $this->all_configs['db']->query('DELETE FROM {orders_suppliers_clients} WHERE id IN (?li)',
                        array(array_keys($links)));

                    $orders = array();
                    foreach ($links as $link) {
                        if ($link['co_id'] > 0) {
                            $href = $this->all_configs['prefix'] . 'orders/create/' . $link['co_id'];
                            $orders[$link['co_id']] = '<a href="' . $href . '">' . $link['co_id'] . '</a>';
                        }
                    }
                    if (array_filter($orders) > 0) {
                        include_once $this->all_configs['sitepath'] . 'mail.php';
                        $messages = new Mailer($this->all_configs);
                        $content = 'Освободились заказы клиентов: ' . implode(', ', $orders);
                        $content .= '. Привяжите к другому заказу поставщику либо создайте новый';
                        $messages->send_message($content, 'Освободились заказы клиентов', 'edit-clients-orders', 1);
                    }
                }
            }
        }

        return $data;
    }

    /**
     * @param $post
     * @param $mod_id
     */
    function debit_order($post, $mod_id)
    {
        if (!$this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders') && $this->all_configs['configs']['erp-use'] == false) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('msg' => 'У Вас недостаточно прав', 'state' => false));
            exit;
        }

        $order_id = isset($post['order_id']) ? intval($post['order_id']) : 0;

        // достаем информацию о заказе
        $order = $this->all_configs['db']->query(
            'SELECT o.*, w.title as wh_title, g.title as g_title FROM {contractors_suppliers_orders} as o
            LEFT JOIN {warehouses} as w ON o.wh_id=w.id LEFT JOIN {goods} as g ON o.goods_id=g.id
            WHERE o.id=?i AND (o.count_come-o.count_debit)>0', array($order_id))->row();

        $serials = isset($post['serial']) ? (array)$post['serial'] : array();
        $auto = isset($post['auto']) ? (array)$post['auto'] : array();
        $print = isset($post['print']) ? (array)$post['print'] : array();

        if (count($serials) == 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('msg' => 'Ведите серийный номер или установите галочку сгенерировать', 'state' => false));
            exit;
        }

        if (!$order || $order['count_come'] - $order['count_debit'] == 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('msg' => 'Заказ уже полностю приходован', 'state' => false));
            exit;
        }

        if ($order['supplier'] == 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('msg' => 'У заказа не найден поставщик', 'state' => false));
            exit;
        }

        if ($order['avail'] == 0) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('msg' => 'Заказ отменен', 'state' => false));
            exit;
        }

        $clear_serials = array_filter($serials);
        if (isset($post['serial']) && count($clear_serials) > 0) {
            $s = $this->all_configs['db']->query(
                'SELECT GROUP_CONCAT(serial) FROM {warehouses_goods_items} WHERE serial IN (?li)',
                array($clear_serials))->el();
            if ($s) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('msg' => 'Серийники уже используются: ' . $s, 'state' => false));
                exit;
            }
        }

        if (count($clear_serials) + count($auto) != $order['count_come']) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('msg' => 'Частичное приходование запрещено', 'state' => false));
            exit;
        }

        $html = '';
        $msg = $debit_items = $print_items = array();

        foreach ($serials as $k=>$serial) {
            $item_id = null;
            if ($order['count_debit'] + count($serials) > $order['count_come']) {
                break;
            }
            if (isset($auto[$k])) {
                $item_id = $this->add_item($order, null, $mod_id);
            } elseif (mb_strlen(trim($serial), 'UTF-8') > 0) {
                $item_id = $this->add_item($order, trim($serial), $mod_id);
            } else {
                $msg[$k] = array('state' => false, 'msg' => 'Ведите серийный номер или установите галочку сгенерировать');
            }
            if (!isset($msg[$k])) {
                if ($item_id > 0) {
                    if (isset($print[$k])) {
                        $print_items[$k] = $item_id;
                    }
                    $debit_items[$k] = suppliers_order_generate_serial(array('item_id' => $item_id, 'serial' => trim($serial)));
                    $msg[$k] = array('state' => true, 'msg' => 'Серийник ' . $debit_items[$k] . ' успешно добавлен');
                } else {
                    $msg[$k] = array('state' => false, 'msg' => 'Серийник уже используется');
                }
            }
        }

        if (count($debit_items) > 0) {
            // количество не обработанных заявок на этот товар
            $qty = $this->all_configs['db']->query('SELECT COUNT(l.id)
                    FROM {orders_suppliers_clients} as l, {goods} as p, {orders_goods} as g
                    WHERE l.order_goods_id=g.id AND p.id=g.goods_id AND p.qty_store>?i AND g.item_id IS NULL AND p.id=?i',
                array(0, $order['goods_id']))->el();
            if ($qty > 0) {
                // ссылка на выдачу изделий
                $url = $this->all_configs['prefix'] . 'warehouses?by_gid=' . $order['goods_id'] . '#orders-clients_bind';
                $html .= '<p><a href="' . $url . '"> Выдать изделия</a> под заказы на ремонт <a target="_blank" href="' . $url . '" class="btn">Ok</a></p>';
            }
            // достаем связку заказов и менеджера
            $links = $this->all_configs['db']->query(
                    'SELECT l.client_order_id, o.manager FROM {orders_suppliers_clients} as l
                    LEFT JOIN {orders} as o ON o.id=l.client_order_id
                    WHERE l.supplier_order_id=?i AND l.goods_id=?i LIMIT ?i, ?i',
                array($order['id'], $order['goods_id'], $order['count_debit'], count($debit_items)))->vars();

            if ($links) {
                include_once $this->all_configs['sitepath'] . 'mail.php';
                $messages = new Mailer($this->all_configs);

                $text = 'Ожидаемая запчасть поступила на склад';
                foreach($links as $co_id=>$manager_id) {
                    // добавляем комментарий
                    $this->add_client_order_comment(intval($co_id), $text);

                    // отправляем уведомление менеджерам
                    if ($manager_id > 0) {
                        $content = 'Запчасть только что была оприходована, под заказ ';
                        $content .= '<a href="' . $this->all_configs['prefix'] . 'orders/create/' . $co_id . '">№' . $co_id . '</a>';
                        $messages->send_message($content, 'Запчасть оприходована', $manager_id, 1);
                    }

                    // отправляем уведомление кладовщикам
                    $content = 'Запчасть только что была оприходована, отгрузите ее под заказ ';
                    $content .= '<a href="' . $this->all_configs['prefix'] . 'warehouses?con=' . $co_id . '#orders-clients_bind">№' . $co_id . '</a>';
                    $messages->send_message($content, 'Отгрузите запчасть под заказ', 'mess-debit-clients-orders', 1);
                }
            }
            // обновляем количество в заказе поставщику
            $this->all_configs['db']->query(
                'UPDATE {contractors_suppliers_orders} SET count_debit=count_debit+?i WHERE id=?i',
                array(count($debit_items), $order['id']));
            // обновление цены закупки в товаре
            $this->all_configs['db']->query('UPDATE {goods} SET price_purchase=?i WHERE id=?i',
                array($order['price'], $order['goods_id']));
        }

        // печать
        $print_link = false;
        if (count($print_items) > 0) {
            $print_link = $this->all_configs['prefix']  . 'print.php?act=label&object_id=' . implode(',', $print_items);
        }

        // пробуем закрыть заказ
        $this->close_order($order_id, $mod_id);

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('result' => $msg, 'print_link' => $print_link, 'html' => $html));
        exit;

        // цепочки которым нужна привязка товара
        /*$inform_chains = $this->all_configs['db']->query('SELECT DISTINCT h.id, b.wh_id, h.order_id
                FROM {chains_headers} as h, {chains_bodies} as b
                LEFT JOIN {warehouses_goods_items} as i ON i.wh_id=b.wh_id
                WHERE h.goods_id=?i AND h.item_id IS NULL AND b.chain_id=h.id AND b.type=?i
                  AND ((h.date_closed IS NULL AND h.return=0) OR (h.date_return IS NULL AND h.return=1)) ',
            array($order['goods_id'], $chains->chain_bind_item))->assoc();*/

        /*$items = array();
        $count_error_item = 0;
        $result = array();
        $print_items = array();
        $result_html = '<div class="alert fade in"><button type="button" class="close" data-dismiss="alert">x</button>';
        // проверка серийные номера
        if (isset($post['serials']) && mb_strlen($post['serials'], 'UTF-8') > 0) {
            $serials = json_decode($post['serials'], true);
            $auto_serials = (isset($post['auto_serials']) && !empty($post['auto_serials'])) ? json_decode($post['auto_serials'], true) : array();
            $print_serials = (isset($post['print_serials']) && !empty($post['print_serials'])) ? json_decode($post['print_serials'], true) : array();

            foreach ($serials as $k=>$serial) {
                // проверяем количество
                if (count($items) == $order['count_come'] || $order['count_come'] == $order['count_debit'])
                    break;

                $serial = trim($serial);
                if (mb_strlen($serial, 'UTF-8') > 0 && (!array_key_exists($k, $auto_serials) || $auto_serials[$k] == 0)) {
                    if (preg_match('#' . $this->all_configs['configs']['erp-serial-prefix'] . '[0-9]{' . $this->all_configs['configs']['erp-serial-count-num'] . '}#', $serial) == 0) {
                        // добавляем товар на склад
                        $item_id = $this->all_configs['db']->query('INSERT IGNORE INTO {warehouses_goods_items} (goods_id, wh_id, supplier_id, serial, price, supplier_order_id)
                            VALUES (?i, ?i, ?i, ?, ?i, ?i)', array($order['goods_id'], $order['wh_id'], $order['supplier'], $serial, $order['price'], $order['id']), 'id');

                        if ($item_id && $item_id > 0) {
                            $items[] = htmlspecialchars($serial);
                            if (array_key_exists($k, $print_serials) && $print_serials[$k] == 1)
                                $print_items[] = $item_id;
                            $this->item_is_added($order, $mod_id, $item_id, $serial);
                            // обновляем количество в заказе поставщику
                            $this->all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET count_debit=count_debit+1 WHERE id=?i', array($order['id']));
                            //$result_html .= '<p>Успешно добавлен</p>';
                            $result[$k] = array('status' => 1, 'msg' => 'Успешно добавлен');
                        } else {
                            $result[$k] = array('status' => 0, 'msg' => 'Серийник уже используется');
                            $count_error_item++;
                        }
                    } else {
                        $result[$k] = array('status' => 0, 'msg' => 'Серийник уже используется');
                        $count_error_item++;
                    }
                } else {//print_r($auto_serials);
                    if (array_key_exists($k, $auto_serials) && $auto_serials[$k] == 1) {
                        // добавляем товар на склад
                        $item_id = $this->all_configs['db']->query('INSERT IGNORE INTO {warehouses_goods_items} (goods_id, wh_id, supplier_id, serial, price, supplier_order_id)
                          VALUES (?i, ?i, ?i, NULL, ?i, ?i)', array($order['goods_id'], $order['wh_id'], $order['supplier'], $order['price'], $order['id']), 'id');

                        if ($item_id && $item_id > 0) {
                            $items[] = htmlspecialchars(suppliers_order_generate_serial(array('item_id' => $item_id, 'serial' => '')));
                            if (array_key_exists($k, $print_serials) && $print_serials[$k] == 1)
                                $print_items[] = $item_id;
                            $this->item_is_added($order, $mod_id, $item_id);
                            // обновляем количество в заказе поставщику
                            $this->all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET count_debit=count_debit+1 WHERE id=?i', array($order['id']));
                            $result[$k] = array('status' => 1, 'msg' => 'Успешно добавлен');
                        } else {
                            $result[$k] = array('status' => 0, 'msg' => 'Серийник уже используется');
                            $count_error_item++;
                        }
                    }
                }
            }
        }

        if (count($print_items) > 0) {
            $result_html .= '<p><a target="_blank" href="' . $this->all_configs['prefix']  . 'print.php?act=label&object_id=';
            $result_html .= implode(',', $print_items) . '" class="btn">Печать</a></p>';
        }

        if ($count_error_item > 0) {
            $result_html .= '<p>Не добавлено ' . $count_error_item . ' ' . l('шт.') . '</p>';
        }

        if (count($items) > 0) {
            $result_html .= '<p>Добавлено ' . count($items) . ' ' . l('шт.') . '<br />' . implode('<br />', $items) . '</p>';

            // обновление заказов у которых все товары в наличии
            $this->all_configs['db']->query('UPDATE {orders} as o SET o.status=?i WHERE o.id IN (
                    SELECT o.id FROM {orders_goods} as og, {warehouses_goods_items} as i
                    WHERE o.id=og.order_id AND o.status=?i AND i.goods_id=og.goods_id
                    AND (i.order_id IS NULL OR i.order_id=?i))',
                array($this->all_configs['configs']['order-status-new'], $this->all_configs['configs']['order-status-preorder'], 0));
            // обновление цены закупки в товаре
            $this->all_configs['db']->query('UPDATE {goods} SET price_purchase=?i WHERE id=?i',
                array($order['price'], $order['goods_id']));

            include_once $this->all_configs['sitepath'] . 'mail.php';
            $messages = new Mailer($this->all_configs);

            /* // уведомление товар появился в наличии
            $user_goods = $this->all_configs['db']->query('SELECT c.email, n.id, g.url, n.object_id, g.title
                    FROM {clients_notices} as n
                    LEFT JOIN (SELECT id, url, qty_store as exist, foreign_warehouse, wait, title FROM {goods}
                    )g ON g.id=n.object_id AND (g.exist > 0 OR g.foreign_warehouse=1)
                    LEFT JOIN (SELECT email, id FROM {clients})c ON c.id=n.user_id
                    WHERE n.type="supply-inform" AND n.date_send IS NULL AND g.id=?i AND c.id IS NOT NULL',
                array($order['goods_id']))->assoc();

            if ($user_goods && count($user_goods) > 0) {
                foreach ( $user_goods as $val ) {
                    $url = $this->all_configs['configs']['host'] . $this->all_configs['siteprefix'] . $val['url']
                        . '/' . $this->all_configs['configs']['product-page'] . '/' . $val['object_id'];
                    $messages->group('went-on-sale', trim($val['email']), array('url' => $url, 'title' => $val['title']));
                    $messages->go();

                    $this->all_configs['db']->query('UPDATE {clients_notices} SET date_send=NOW() WHERE id=?i',
                        array($val['id']));
                }
            }*/

            /*// уведомления кладовщикам
            if ($inform_chains && count($inform_chains) > 0) {
                $q = $chains->query_warehouses();
                foreach ($inform_chains as $inform_chain) {

                    $query_for_my_warehouses = $this->all_configs['db']->makeQuery('RIGHT JOIN {warehouses_users} as wu ON wu.'
                        . trim($q['query_for_my_warehouses']) . ' AND u.id=wu.user_id AND wu.wh_id=?i',
                        array($inform_chain['wh_id']));

                    $content = 'Необходимо привязать серийник в цепочке ';
                    $content .= '<a href="' . $this->all_configs['prefix'] . 'warehouses#orders-clients_bind">№' . $inform_chain['order_id'] . '</a>';
                    $messages->send_message($content, 'Привязать серийник в цепочке', 'mess-debit-clients-orders', 1, $query_for_my_warehouses);
                }
            }*/
        /*}

        if ($count_error_item == 0 && count($items) == 0) {
            $result_html .= '<p>Ведите серийный номер или установите галочку сгенерировать</p>';
        }

        $result_html .= '<div>';

        // закрываем заказ
        $o = $this->all_configs['db']->query('SELECT (count_come-count_debit) as count, (price*count_come-sum_paid) as sum, date_paid
            FROM {contractors_suppliers_orders} WHERE id=?i', array($order['id']))->row();
        if ($o['count'] == 0 && $o['sum'] == 0 && $o['date_paid'] > 0) {
            $this->all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET confirm=?i WHERE id=?i', array(1, $order['id']));
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Результат', 'result' => $result, 'count' => $o['count'], 'result_html' => $result_html));
        exit;*/
    }

    /**
     * @param      $order
     * @param null $serial
     * @param      $mod_id
     * @return mixed
     */
    function add_item($order, $serial = null, $mod_id)
    {
        $item_id = $this->all_configs['db']->query('INSERT IGNORE INTO {warehouses_goods_items}
              (goods_id, wh_id, location_id, supplier_id, serial, price, supplier_order_id, user_id)
              VALUES (?i, ?i, ?i, ?i, ?n, ?i, ?i, ?i)', array($order['goods_id'], $order['wh_id'], $order['location_id'],
            $order['supplier'], $serial, $order['price'], $order['id'], $_SESSION['id']), 'id');

        if ($item_id) {
            $this->all_configs['manageModel']->move_product_item(
                $order['wh_id'],
                $order['location_id'],
                $order['goods_id'],
                $item_id,
                null,
                null,
                l('Товар приходован на склад')
            );

            // связка между контрагентом и категорией
            $this->all_configs['db']->query('INSERT IGNORE INTO {contractors_categories_links} (contractors_categories_id, contractors_id) VALUES (?i, ?i)',
                array($this->all_configs['configs']['erp-so-contractor_category_id_from'], $order['supplier']));
            $contractor_category_link = $this->all_configs['db']->query('SELECT id FROM {contractors_categories_links}
                WHERE contractors_categories_id=?i AND contractors_id=?i',
                array($this->all_configs['configs']['erp-so-contractor_category_id_from'], $order['supplier']))->el();

            // транзакция контрагенту и зачисление ему сумы
            $this->add_contractors_transaction(
                array(
                    'transaction_type' => 2,
                    'value_to' => ($order['price'] / 100),
                    'comment' => 'Товар ' . $order['g_title'] . ' приходован на склад ' . $order['wh_title']. '. Заказ поставщика ' .
                        $this->supplier_order_number($order) . ', серийник ' . suppliers_order_generate_serial(array('serial' => $serial, 'item_id' => $item_id)) .
                        ', сумма ' . ($order['price'] / 100) . '$, ' . date("Y-m-d H:i:s", time()),
                    'contractor_category_link' => $contractor_category_link,
                    'supplier_order_id' => $order['id'],
                    'item_id' => $item_id,
                    'goods_id' => $order['goods_id'],

                    'contractors_id' => $order['supplier'],
                )
            );

            //include_once $this->all_configs['sitepath'] . 'mail.php';
            //$messages = new Mailer($this->all_configs);

            /*// уведомления кладовщикам
            if ($inform_chains && count($inform_chains) > 0) {
                $q = $chains->query_warehouses();
                foreach ($inform_chains as $inform_chain) {

                    $query_for_my_warehouses = $this->all_configs['db']->makeQuery('RIGHT JOIN {warehouses_users} as wu ON wu.'
                        . trim($q['query_for_my_warehouses']) . ' AND u.id=wu.user_id AND wu.wh_id=?i',
                        array($inform_chain['wh_id']));

                    $content = 'Необходимо привязать серийник в цепочке ';
                    $content .= '<a href="' . $this->all_configs['prefix'] . 'warehouses#orders-clients_bind">№' . $inform_chain['order_id'] . '</a>';
                    $messages->send_message($content, 'Привязать серийник в цепочке', 'mess-debit-clients-orders', 1, $query_for_my_warehouses);
                }
            }*/

            // история
            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                array($_SESSION['id'], 'debit-supplier-order', $mod_id, intval($order['id'])));
        }

        return $item_id;
    }

    /*function item_is_added($order, $mod_id, $item_id, $serial = '')
    {
        $this->all_configs['manageModel']->move_product_item(
            $order['wh_id'],
            $order['location_id'],
            $order['goods_id'],
            $item_id,
            null,
            null,
            'Товар приходован на склад'
        );

        // связка между контрагентом и категорией
        $this->all_configs['db']->query('INSERT IGNORE INTO {contractors_categories_links} (contractors_categories_id, contractors_id) VALUES (?i, ?i)',
            array($this->all_configs['configs']['erp-so-contractor_category_id_from'], $order['supplier']));
        $contractor_category_link = $this->all_configs['db']->query('SELECT id FROM {contractors_categories_links}
            WHERE contractors_categories_id=?i AND contractors_id=?i',
            array($this->all_configs['configs']['erp-so-contractor_category_id_from'], $order['supplier']))->el();

        // транзакция контрагенту и зачисление ему сумы
        $this->add_contractors_transaction(
            array(
                'transaction_type' => 2,
                'value_to' => ($order['price'] / 100),
                'comment' => 'Товар ' . $order['g_title'] . ' приходован на склад ' . $order['wh_title']. '. Заказ поставщика ' .
                    $this->supplier_order_number($order) . ', серийник ' . suppliers_order_generate_serial(array('serial' => $serial, 'item_id' => $item_id)) .
                    ', сумма ' . ($order['price'] / 100) . '$, ' . date("Y-m-d H:i:s", time()),
                'contractor_category_link' => $contractor_category_link,
                'supplier_order_id' => $order['id'],
                'item_id' => $item_id,
                'goods_id' => $order['goods_id'],

                'contractors_id' => $order['supplier'],
            )
        );

        // история
        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
            array($_SESSION['id'], 'debit-supplier-order', $mod_id, intval($_POST['order_id'])));

    }*/

    /**
     * @param      $order
     * @param null $title
     * @param bool $link
     * @return null|string
     */
    function supplier_order_number ($order, $title = null, $link = true)
    {
        if (!array_key_exists('parent_id', $order) || !array_key_exists('number', $order) || !array_key_exists('num', $order)) {
            $order = $this->all_configs['db']->query('SELECT number, parent_id, id, num FROM {contractors_suppliers_orders} WHERE id=?i', array($order['id']))->row();
        }
        $number = ($order['parent_id'] > 0 && $order['parent_id'] != $order['id']) ? $order['parent_id'] . '/' . $order['number'] : $order['num'];

        if ($number != $order['id']) {
            $out = $number . ' (' . $order['id'] . ')';
        } else {
            $out = $order['id'];
        }
        if (!$title) {
            $title = '№' . $out;
        }

        if ($link == true) {
            $href = $this->all_configs['prefix'] . 'orders/edit/' . $order['id'] . '#create_supplier_order';
            return '<a class="hash_link" href="' . $href . '">' . $title . '</a>';
        } else {
            return $title;
        }
    }

    /**
     * транзакция контрагенту
     * */
    function add_contractors_transaction($data)
    {
        $array = array(
            'transaction_type' => array_key_exists('transaction_type', $data) ? $data['transaction_type'] : 0,
            'cashboxes_currency_id_from' => array_key_exists('cashboxes_currency_id_from', $data) ? $data['cashboxes_currency_id_from'] : null,
            'cashboxes_currency_id_to' => array_key_exists('cashboxes_currency_id_to', $data) ? $data['cashboxes_currency_id_to'] : null,
            'value_from' => array_key_exists('value_from', $data) ? $data['value_from'] : 0,
            'value_to' => array_key_exists('value_to', $data) ? $data['value_to'] : 0,
            'comment' => array_key_exists('comment', $data) ? $data['comment'] : '',
            'contractor_category_link' => array_key_exists('contractor_category_link', $data) ? $data['contractor_category_link'] : null,
            'date_transaction' => array_key_exists('date_transaction', $data) ? $data['date_transaction'] : date("Y-m-d H:i:s", time()),
            'user_id' => array_key_exists('user_id', $data) ? $data['user_id'] : $_SESSION['id'],
            'supplier_order_id' => array_key_exists('supplier_order_id', $data) ? $data['supplier_order_id'] : null,
            'client_order_id' => array_key_exists('client_order_id', $data) ? $data['client_order_id'] : null,
            'transaction_id' => array_key_exists('transaction_id', $data) ? $data['transaction_id'] : null,
            'item_id' => array_key_exists('item_id', $data) ? $data['item_id'] : null,
            'goods_id' => array_key_exists('goods_id', $data) ? $data['goods_id'] : null,
            'type' => array_key_exists('type', $data) ? $data['type'] : 0,

            'contractors_id' => array_key_exists('contractors_id', $data) ? $data['contractors_id']: 0,
        );

        // добавляем транзакцию контрагенту
        $this->all_configs['db']->query('INSERT INTO {contractors_transactions} (transaction_type, cashboxes_currency_id_from,
                            cashboxes_currency_id_to, value_from, value_to, comment, contractor_category_link, date_transaction,
                            user_id, supplier_order_id, client_order_id, transaction_id, item_id, goods_id, type)
                        VALUES (?i, ?n, ?n, ?i, ?i, ?, ?n, ?, ?i, ?n, ?n, ?n, ?n, ?n, ?i)',
            array($array['transaction_type'], $array['cashboxes_currency_id_from'], $array['cashboxes_currency_id_to'],
                round((float)$array['value_from'] * 100), round((float)$array['value_to'] * 100),
                trim($array['comment']), $array['contractor_category_link'],
                date("Y-m-d H:i:s", strtotime($array['date_transaction'])), $array['user_id'], $array['supplier_order_id'],
                $array['client_order_id'], $array['transaction_id'], $array['item_id'], $array['goods_id'], $array['type']
            ), 'id');

        $this->set_amount_contractor($data);
    }

    /**
     * списывание/зачисление сумы контрагенту
     * */
    function set_amount_contractor($data)
    {
        $array = array(
            'transaction_type' => array_key_exists('transaction_type', $data) ? $data['transaction_type']: 0,
            'value_from' => array_key_exists('value_from', $data) ? $data['value_from']: 0,
            'value_to' => array_key_exists('value_to', $data) ? $data['value_to']: 0,

            'contractors_id' => array_key_exists('contractors_id', $data) ? $data['contractors_id']: 0,
        );

        // обновляем суму у контрагента
        // выдача
        if ($array['transaction_type'] == 1) {
            $this->all_configs['db']->query('UPDATE {contractors} SET amount=amount-?i WHERE id=?i',
                array(round((float)$array['value_from'] * 100), intval($array['contractors_id'])));
        }
        // внесение
        if ($array['transaction_type'] == 2) {
            $this->all_configs['db']->query('UPDATE {contractors} SET amount=amount+?i WHERE id=?i',
                array(round((float)$array['value_to'] * 100), intval($array['contractors_id'])));
        }
    }

    /**
     * @param       $currencies
     * @param bool  $by_day
     * @param null  $limit
     * @param bool  $contractors
     * @param array $filters
     * @param bool  $show_balace
     * @param bool  $return_array
     * @return array|string
     */
    function get_transactions($currencies, $by_day = false, $limit = null, $contractors = false, $filters = array(), $show_balace = true, $return_array = false)
    {
        $result = array();
        $query_end = '';

        if ($by_day == true) {
            // сегодня
            $day = date("d.m.Y", time());
            if (isset($_GET['d']) && !empty($_GET['d'])) {
                $days = explode('-', $_GET['d']);
                $day = $days[0];
            }
            $query_where = $this->all_configs['db']->makeQuery('DATE_FORMAT(t.date_transaction, "%d.%m.%Y")=? AND', array($day));
            $query_balance = $this->all_configs['db']->makeQuery('DATE_FORMAT(t.date_transaction, "%d.%m.%Y")<? AND', array($day));
        } else {
            // текущий месяц
            $day_from = 1 . date(".m.Y", time()) . ' 00:00:00';
            $day_before = 1 . date(".m.Y", time());
            $day_to = 31 . date(".m.Y", time()) . ' 23:59:59';

            if (isset($_GET['df']) && !empty($_GET['df'])) {
                $day_from = $_GET['df'] . ' 00:00:00';
                $day_before = $_GET['df'];
            }

            if (isset($_GET['dt']) && !empty($_GET['dt']))
                $day_to = $_GET['dt'] . ' 23:59:59';

            //if (!isset($_GET['o_id']) && !isset($_GET['t_id']) && !isset($_GET['s_id'])) {
                $query_where = $this->all_configs['db']->makeQuery('t.date_transaction BETWEEN STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")
                        AND STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s") AND',
                    array($day_from, $day_to));
                $query_balance = $this->all_configs['db']->makeQuery('DATE_FORMAT(t.date_transaction, "%d.%m.%Y")<? AND',
                    array($day_before));
            //}

            // фильтры вручную
            if (count($filters) > 0) {
                $query_where = '';

                if (array_key_exists('supplier_order_id', $filters) && $filters['supplier_order_id'] > 0) {
                    $query_where = $this->all_configs['db']->makeQuery('t.supplier_order_id=?i AND ?query', array($filters['supplier_order_id'], $query_where));
                }
            }

            // фильтр по категориям
            if (isset($_GET['cg']) && !empty($_GET['cg'])) {
                // исключающее
                if (isset($_GET['cge']) && $_GET['cge'] == -1) {
                    $query_where = $this->all_configs['db']->makeQuery('?query (t.contractor_category_link IS NULL OR t.contractor_category_link NOT IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_categories_id IN (?li))) AND',
                        array($query_where, explode(',', $_GET['cg'])));
                    $query_balance = $this->all_configs['db']->makeQuery('?query (t.contractor_category_link IS NULL OR t.contractor_category_link NOT IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_categories_id IN (?li))) AND',
                        array($query_balance, explode(',', $_GET['cg'])));
                } else {
                    $query_where = $this->all_configs['db']->makeQuery('?query t.contractor_category_link IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_categories_id IN (?li)) AND',
                        array($query_where, explode(',', $_GET['cg'])));
                    $query_balance = $this->all_configs['db']->makeQuery('?query t.contractor_category_link IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_categories_id IN (?li)) AND t.transaction_type<>3 AND',
                        array($query_balance, explode(',', $_GET['cg'])));
                }
            }

            // фильтр по контрагентам
            if (isset($_GET['ct']) && !empty($_GET['ct'])) {
                // исключающее
                if (isset($_GET['cte']) && $_GET['cte'] == -1) {
                    $query_where = $this->all_configs['db']->makeQuery('?query (t.contractor_category_link IS NULL OR t.contractor_category_link NOT IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_id IN (?li))) AND',
                        array($query_where, explode(',', $_GET['ct'])));
                    $query_balance = $this->all_configs['db']->makeQuery('?query (t.contractor_category_link IS NULL OR t.contractor_category_link NOT IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_id IN (?li))) AND',
                        array($query_balance, explode(',', $_GET['ct'])));
                } else {
                    $query_where = $this->all_configs['db']->makeQuery('?query t.contractor_category_link IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_id IN (?li)) AND',
                        array($query_where, explode(',', $_GET['ct'])));
                    $query_balance = $this->all_configs['db']->makeQuery('?query t.contractor_category_link IN (SELECT id FROM {contractors_categories_links}
                            WHERE contractors_id IN (?li)) AND t.transaction_type<>3 AND',
                        array($query_balance, explode(',', $_GET['ct'])));
                }
            }

            // фильтр по заказку поставщика
            if (isset($_GET['s_id']) && $_GET['s_id'] > 0) {
                $query_where = $this->all_configs['db']->makeQuery('?query t.supplier_order_id=?i AND',
                    array($query_where, $_GET['s_id']));
                $query_balance = $this->all_configs['db']->makeQuery('?query t.supplier_order_id=?i AND',
                    array($query_balance, $_GET['s_id']));
            }

            // фильтр по заказку клиента
            if (isset($_GET['o_id']) && $_GET['o_id'] > 0) {
                $query_where = $this->all_configs['db']->makeQuery('?query t.client_order_id=?i AND',
                    array($query_where, $_GET['o_id']));
                $query_balance = $this->all_configs['db']->makeQuery('?query t.client_order_id=?i AND',
                    array($query_balance, $_GET['o_id']));
            }

            // фильтр по кассам
            if (isset($_GET['cb']) && !empty($_GET['cb'])) {
                // исключающее
                if (isset($_GET['cbe']) && $_GET['cbe'] == -1) {
                    $query_balance = $this->all_configs['db']->makeQuery('?query
                            ((cc_to.cashbox_id NOT IN (?li) OR cc_to.cashbox_id IS NULL) AND
                            (cc_from.cashbox_id NOT IN (?li) OR cc_from.cashbox_id IS NULL)) AND',
                        array($query_balance, explode(',', $_GET['cb']), explode(',', $_GET['cb'])));
                } else {
                    $query_balance = $this->all_configs['db']->makeQuery('?query
                            (cc_to.cashbox_id IN (?li) OR cc_from.cashbox_id IN (?li)) AND',
                        array($query_balance, explode(',', $_GET['cb']), explode(',', $_GET['cb'])));
                }
            }
        }

        // лимит
        if ($limit != null && $limit > 0)
            $query_end = $this->all_configs['db']->makeQuery('?query LIMIT ?i', array($query_end, $limit));

        // какую табличку доставать
        if ($contractors == false) {
            // фильтр по транзакции касс
            if (isset($_GET['t_id']) && $_GET['t_id'] > 0) {
                $query_where = $this->all_configs['db']->makeQuery('?query t.id=?i AND',
                    array($query_where, $_GET['t_id']));
                $query_balance = $this->all_configs['db']->makeQuery('?query t.id=?i AND',
                    array($query_balance, $_GET['t_id']));
            }
        } else {
            // фильтр по транзакции касс
            if (isset($_GET['t_id']) && $_GET['t_id'] > 0) {
                $query_where = $this->all_configs['db']->makeQuery('?query t.transaction_id=?i AND',
                    array($query_where, $_GET['t_id']));
                $query_balance = $this->all_configs['db']->makeQuery('?query t.transaction_id=?i AND',
                    array($query_balance, $_GET['t_id']));
            }
        }

        // все транзакции
        $transactions = $this->all_configs['db']->query('SELECT t.id, t.date_transaction, t.comment, t.transaction_type, '
                    . (((isset($_GET['grp']) && $_GET['grp'] == 1) && $by_day == false) ?
                        'IF(t.transaction_type=1 OR t.transaction_type=3, -t.value_from, 0) as value_from,
                        IF(t.transaction_type=2 OR t.transaction_type=3, t.value_to, 0) as value_to, '
                        : 'SUM(IF(t.transaction_type=1 OR t.transaction_type=3, -t.value_from, 0)) as value_from,
                            SUM(IF(t.transaction_type=2 OR t.transaction_type=3, t.value_to, 0)) as value_to, COUNT(t.id) as count_t, ')
                    . 't.cashboxes_currency_id_from, t.cashboxes_currency_id_to, cc.currency, cb.name, cc.id as c_id,
                    cc.cashbox_id, ct.name as category_name, c.title as contractor_name, c.id as contractor_id,
                    t.user_id, u.email, u.fio, t.supplier_order_id, t.client_order_id,
                    ' . ($contractors == true ?
                        't.transaction_id, t.item_id, IFNULL(t.supplier_order_id, UUID()) as unq_supplier_order_id' :
                        't.chain_id, IFNULL(t.client_order_id, UUID()) as unq_client_order_id') . '
                FROM {' . ($contractors == false ? 'cashboxes_transactions' : 'contractors_transactions') . '} as t
                LEFT JOIN (SELECT currency, id, cashbox_id FROM {cashboxes_currencies})cc ON (cc.id=t.cashboxes_currency_id_from || cc.id=t.cashboxes_currency_id_to)
                LEFT JOIN (SELECT name, id FROM {cashboxes})cb ON cb.id=cc.cashbox_id
                LEFT JOIN (SELECT id, contractors_categories_id, contractors_id FROM {contractors_categories_links})l ON l.id=t.contractor_category_link
                LEFT JOIN (SELECT id, name FROM {contractors_categories})ct ON ct.id=l.contractors_categories_id
                LEFT JOIN (SELECT id, title FROM {contractors})c ON c.id=l.contractors_id
                LEFT JOIN (SELECT id, email, fio FROM {users})u ON u.id=t.user_id
                WHERE ?query 1=1 '
                . (((isset($_GET['grp']) && $_GET['grp'] == 1) && $by_day == false) ? '' :
                (($contractors == false) ? 'GROUP BY unq_client_order_id' : 'GROUP BY unq_supplier_order_id'))
                . ' ORDER BY DATE(t.date_transaction) DESC, t.id DESC ?query',
            array($query_where, $query_end))->assoc();

        if ($transactions) {
            foreach ($transactions as $transaction) {

                if (array_key_exists($transaction['id'], $result)) {
                    if ($transaction['c_id'] == $transaction['cashboxes_currency_id_from']) {
                        $result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_from']] = array(
                            'name' => $transaction['name'],
                            'currency' => $transaction['currency'],
                            'cashbox_id' => $transaction['cashbox_id'],
                        );
                    }
                    if ($transaction['c_id'] == $transaction['cashboxes_currency_id_to']) {
                        $result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_to']] = array(
                            'name' => $transaction['name'],
                            'currency' => $transaction['currency'],
                            'cashbox_id' => $transaction['cashbox_id'],
                        );
                    }
                } else {
                    $result[$transaction['id']] = array(
                        'date_transaction' => $transaction['date_transaction'],
                        'comment' => $transaction['comment'],
                        'email' => $transaction['email'],
                        'fio' => $transaction['fio'],
                        'user_id' => $transaction['user_id'],
                        'category_name' => $transaction['category_name'],
                        'contractor_name' => $transaction['contractor_name'],
                        'transaction_type' => $transaction['transaction_type'],
                        'value_from' => $transaction['value_from'],
                        'value_to' => $transaction['value_to'],
                        'cashboxes_currency_id_from' => $transaction['cashboxes_currency_id_from'],
                        'cashboxes_currency_id_to' => $transaction['cashboxes_currency_id_to'],
                        'client_order_id' => $transaction['client_order_id'],
                        'supplier_order_id' => $transaction['supplier_order_id'],
                        'transaction_id' => array_key_exists('transaction_id', $transaction) ? $transaction['transaction_id'] : '',
                        'item_id' => array_key_exists('item_id', $transaction) ? $transaction['item_id'] : '',
                        'chain_id' => array_key_exists('chain_id', $transaction) ? $transaction['chain_id'] : '',
                        'contractor_id' => $transaction['contractor_id'],
                        'count_t' => array_key_exists('count_t', $transaction) ? $transaction['count_t'] : '',
                    );

                    if ($transaction['c_id'] == $transaction['cashboxes_currency_id_from']) {
                        $result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_from']] = array(
                            'name' => $transaction['name'],
                            'currency' => $transaction['currency'],
                            'cashbox_id' => $transaction['cashbox_id'],
                        );
                    }
                    if ($transaction['c_id'] == $transaction['cashboxes_currency_id_to']) {
                        $result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_to']] = array(
                            'name' => $transaction['name'],
                            'currency' => $transaction['currency'],
                            'cashbox_id' => $transaction['cashbox_id'],
                        );
                    }
                }

                // фильтра по кассам
                if (isset($_GET['cb']) && !empty($_GET['cb']) && $by_day == false) {
                    // исключающее
                    if (isset($_GET['cbe']) && $_GET['cbe'] == -1) {
                        if (($transaction['transaction_type'] == 1 &&
                                array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_from']]['cashbox_id'], explode(',', $_GET['cb'])) !== false
                            ) ||
                            ($transaction['transaction_type'] == 2 &&
                                array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_to']]['cashbox_id'], explode(',', $_GET['cb'])) !== false
                            ) ||
                            ($transaction['transaction_type'] == 3 &&
                                array_key_exists($transaction['cashboxes_currency_id_from'], $result[$transaction['id']]['cashboxes']) &&
                                array_key_exists($transaction['cashboxes_currency_id_to'], $result[$transaction['id']]['cashboxes']) &&
                                (array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_from']]['cashbox_id'], explode(',', $_GET['cb'])) !== false
                                    ||
                                    array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_to']]['cashbox_id'], explode(',', $_GET['cb'])) !== false)
                            )
                        ) {
                            unset($result[$transaction['id']]);
                        }
                    } else {
                        if (($transaction['transaction_type'] == 1 &&
                                array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_from']]['cashbox_id'], explode(',', $_GET['cb'])) === false
                            ) ||
                            ($transaction['transaction_type'] == 2 &&
                                array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_to']]['cashbox_id'], explode(',', $_GET['cb'])) === false
                            ) ||
                            ($transaction['transaction_type'] == 3 &&
                                array_key_exists($transaction['cashboxes_currency_id_from'], $result[$transaction['id']]['cashboxes']) &&
                                array_key_exists($transaction['cashboxes_currency_id_to'], $result[$transaction['id']]['cashboxes']) &&
                                array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_from']]['cashbox_id'], explode(',', $_GET['cb'])) === false
                                &&
                                array_search($result[$transaction['id']]['cashboxes'][$transaction['cashboxes_currency_id_to']]['cashbox_id'], explode(',', $_GET['cb'])) === false
                            )
                        ) {
                            unset($result[$transaction['id']]);
                        }
                    }
                }
            }
        }

        return $this->show_transactions($result, $currencies, $contractors , $by_day, $query_balance, $show_balace, $return_array);
    }

    /**
     * @param      $query_balance
     * @param      $contractors
     * @param      $currencies
     * @param null $balance
     * @param bool $by_day
     * @return string
     */
    function balance($query_balance, $contractors, $currencies, $balance = null, $by_day = false)
    {
        // достаем суммы транзакций по валютам
        $balances_begin = $this->all_configs['db']->query('SELECT cc_to.currency as currency_to,
                    cc_from.currency as currency_from, t.transaction_type,
                    IF(t.transaction_type=1 OR t.transaction_type=3, -t.value_from, 0) as value_from,
                    IF(t.transaction_type=2 OR t.transaction_type=3, t.value_to, 0) as value_to

                FROM {' . ($contractors == false ? 'cashboxes_transactions' : 'contractors_transactions') . '} as t
                LEFT JOIN (SELECT currency, id, cashbox_id FROM {cashboxes_currencies})cc_to ON cc_to.id=t.cashboxes_currency_id_to
                LEFT JOIN (SELECT currency, id, cashbox_id FROM {cashboxes_currencies})cc_from ON cc_from.id=t.cashboxes_currency_id_from
                WHERE ?query ((t.transaction_type=3 && cc_to.id IS NOT NULL && cc_from.id IS NOT NULL) || t.transaction_type<>3)',
            array($query_balance))->assoc();

        //print_r($balances_begin);exit;

        $balance_begin = array();
        if ($balances_begin && is_array($balances_begin)) {
            foreach ($balances_begin as $b) {
                if ($b['currency_to'] != 0) {
                    if (!array_key_exists($b['currency_to'], $balance_begin))
                        $balance_begin[$b['currency_to']] = 0;
                    $balance_begin[$b['currency_to']] += $b['value_to'];
                }
                if ($b['currency_from'] != 0) {
                    if (!array_key_exists($b['currency_from'], $balance_begin))
                        $balance_begin[$b['currency_from']] = 0;
                    $balance_begin[$b['currency_from']] += $b['value_from'];
                }
            }
        }
        $balance_html = '<div class="well">';
        if ($by_day == false) {
            $transaction_type = $contractors == false ? 'cashboxes_transactions' : 'contractors_transactions';
            $onclick = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/export/?act=' . $transaction_type . '&' . get_to_string();
            $balance_html .= '<a class="btn btn-default pull-right" target="_blank" href="' . $onclick . '">' . l('Выгрузить') . '</a>';
        }
        $balance_html .= '<p>' . l('Баланс на начало периода') . ': ';
        ksort($balance_begin, SORT_NUMERIC);
        $balance_begin_html = '';
        foreach ($balance_begin as $c=>$b) {
            if ($b != 0)
                $balance_begin_html .= show_price($b) . ' ' . $currencies[$c]['shortName'] . ', ';
        }
        $balance_html .= empty($balance_begin_html) ? '0, ' : $balance_begin_html;
        $date_from = isset($_GET['df']) ? date('Y-m-01 00:00:00', strtotime($_GET['df'])) : date('Y-m-01 00:00:00');
        $balance_html .=  l('Дата начала периода') . ': <span title="' . do_nice_date($date_from, false, false) . '">' . do_nice_date($date_from, true, false) . '</span>';
        $balance_html .= '</p><p>' . l('Баланс на конец периода') . ': ';
        $balance_end_html = '';
        if ($balance && is_array($balance)) {
            ksort($balance, SORT_NUMERIC);
            foreach ($balance as $k=>$b) {
                if ($b != 0) {
                    if (array_key_exists($k, $balance_begin)) {
                        $balance_end_html .= show_price($b + $balance_begin[$k]) . ' ' . $currencies[$k]['shortName'] . ', ';
                    } else {
                        $balance_end_html .= show_price($b) . ' ' . $currencies[$k]['shortName'] . ', ';
                    }
                }
            }
        } else {
            $balance_end_html = $balance_begin_html;
        }
        $balance_html .= empty($balance_end_html) ? '0, ' : $balance_end_html;
        $date_to = isset($_GET['dt']) ? date('Y-m-t 23:59:59', strtotime($_GET['dt'])) : date('Y-m-t 23:59:59');
        $balance_html .= l('Дата конца периода') . ': <span title="' . do_nice_date($date_to, false, false) . '">' . do_nice_date($date_to, true, false) . '</span>';
        $balance_html .= '</p></div>';

        return $balance_html;
    }

    /**
     * @param $transactions
     * @param $currencies
     * @param $contractors
     * @param $by_day
     * @param $query_balance
     * @param $show_balace
     * @param $return_array
     * @return array|string
     */
    function show_transactions($transactions, $currencies, $contractors, $by_day, $query_balance, $show_balace, $return_array)
    {
        $out = '';

        if ($transactions) {
            if ($return_array == true) {
                $out = array();

                $total = $total_inc = $total_exp = $total_tr_inc = $total_tr_exp =/* $balance =*/ array_fill_keys(array_keys($currencies), '');
                foreach ($transactions as $transaction_id=>$transaction) {
                    //$sum = 'Неизвестный перевод';
                    $cashbox_info = 'Неизвестная операция';
                    $exp = $inc = 0;

                    // без группировки
                    // расход
                    if ($transaction['transaction_type'] == 1 && $transaction['count_t'] == 0) {
                        // с кассы
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'], $transaction['cashboxes'])) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['name'];
                        }
                        // в категорию
                        $cashbox_info .= ' -> ' . $transaction['category_name'];
                        // сумма
                        //$sum = show_price($transaction['value_from']);
                        $exp = show_price($transaction['value_from']);
                        if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'], $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'], $currencies)) {
                            //$sum .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $exp .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                            //$balance[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                            $total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                        } else {
                            //$sum .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $exp .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_from'];
                            //$balance[$this->currency_suppliers_orders] += $transaction['value_from'];;
                            $total_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                        }
                    }
                    // доход
                    if ($transaction['transaction_type'] == 2 && $transaction['count_t'] == 0) {
                        // в кассу
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'], $transaction['cashboxes'])) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['name'];
                        }
                        // с категории
                        $cashbox_info .= ' <- ' . $transaction['category_name'];
                        // сумма
                        //$sum = show_price($transaction['value_to']);
                        $inc = show_price($transaction['value_to']);
                        if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'], $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'], $currencies)) {
                            //$sum .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $inc .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                            //$balance[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                            $total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                        } else {
                            //$sum .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $inc .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_to'];
                            //$balance[$this->currency_suppliers_orders] += $transaction['value_to'];
                            $total_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                        }
                    }
                    // перевод
                    if ($transaction['transaction_type'] == 3 /*&& $transaction['count_t'] == 0*/) {
                        // с кассы
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'], $transaction['cashboxes'])) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['name'];
                        }
                        $cashbox_info .= ' -> ';
                        // в кассу
                        if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'], $transaction['cashboxes'])) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['name'];
                        }
                        // сумма
                        //$sum = show_price($transaction['value_from']);
                        $exp = show_price($transaction['value_from']);
                        if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'], $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'], $currencies)) {
                            //$sum .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $exp .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $total_tr_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                            //$balance[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                        } else {
                            //$sum .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $exp .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total_tr_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                            //$balance[$this->currency_suppliers_orders] += $transaction['value_from'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_from'];
                        }
                        //$sum .= ' -> ';
                        //$sum .= show_price($transaction['value_to']);
                        $inc = show_price($transaction['value_to']);
                        if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'], $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'], $currencies)) {
                            //$sum .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $inc .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $total_tr_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                            //$balance[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                        } else {
                            //$sum .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $inc .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total_tr_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                            //$balance[$this->currency_suppliers_orders] += $transaction['value_to'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_to'];
                        }
                    }
                    // группировано
                    // расход
                    if ($transaction['transaction_type'] == 1 && $transaction['count_t'] > 0) {
                        // с кассы
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'], $transaction['cashboxes'])) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['name'];
                        }
                        // в категорию
                        $cashbox_info .= ' -> ' . $transaction['category_name'];
                        // сумма
                        //$sum = show_price($transaction['value_from'] + $transaction['value_to']);
                        $exp = show_price($transaction['value_from']);
                        $inc = show_price($transaction['value_to']);
                        if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'], $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'], $currencies)) {
                            //$sum .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $exp .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $inc .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
                            $total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                            $total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_to'];
                            //$balance[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
                        } else {
                            //$sum .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $exp .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $inc .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'];
                            $total_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                            $total_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                            //$balance[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'];
                        }
                    }
                    // доход
                    if ($transaction['transaction_type'] == 2 && $transaction['count_t'] > 0) {
                        // в кассу
                        $cashbox_info = '';
                        if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'], $transaction['cashboxes'])) {
                            $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['name'];
                        }
                        // с категории
                        $cashbox_info .= ' <- ' . $transaction['category_name'];
                        // сумма
                        //$sum = show_price($transaction['value_from'] + $transaction['value_to']);
                        $exp = show_price($transaction['value_from']);
                        $inc = show_price($transaction['value_to']);
                        if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'], $transaction['cashboxes']) &&
                            array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'], $currencies)) {
                            //$sum .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $inc .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $exp .= ' ' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                            $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
                            $total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'];
                            $total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                            //$balance[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
                        } else {
                            //$sum .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $inc .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $exp .= ' ' . $currencies[$this->currency_suppliers_orders]['shortName'];
                            $total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'];
                            $total_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                            $total_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                            //$balance[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'];
                        }
                    }
                    $group = $transaction['count_t'] . ' транз.';

                    $out[$transaction_id]['id'] = $transaction_id;
                    $out[$transaction_id]['Дата'] = $transaction['date_transaction'];
                    $out[$transaction_id]['Касса'] = ($transaction['count_t'] > 1 ? $group : $cashbox_info);
                    $out[$transaction_id]['Контрагент'] = $transaction['contractor_name'];
                    if ($transaction['client_order_id'] > 0) {
                        $out[$transaction_id]['Заказ клиента'] =$transaction['client_order_id'];
                    }
                    $out[$transaction_id]['Заказ поставщика'] = ($transaction['supplier_order_id'] > 0 ? $this->supplier_order_number(array('id'=>$transaction['supplier_order_id'])) : '');
                    if ($contractors == true) {
                        $out[$transaction_id]['Транзакция'] = $transaction['transaction_id'];
                        $out[$transaction_id]['Доход'] = (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : '&#931; ') . $inc;
                        $out[$transaction_id]['Расход'] = (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : '&#931; ') . $exp;

                        if (array_key_exists('count_t', $transaction) && $transaction['count_t'] > 1) {
                            $out[$transaction_id]['Серийник'] = $group;
                        } else {
                            if ($transaction['item_id'] > 0) {
                                $item = $this->all_configs['db']->query('SELECT serial, id as item_id FROM {warehouses_goods_items} WHERE id=?i',
                                    array($transaction['item_id']))->row();
                                $out[$transaction_id]['Серийник'] = suppliers_order_generate_serial($item, true, true);
                            }
                        }
                    } else {
                        if (array_key_exists('count_t', $transaction) && $transaction['count_t'] > 1) {
                            $out[$transaction_id][''] = $group;
                        } else {
                            $out[$transaction_id]['Цепочка'] = $transaction['chain_id'];
                        }
                        $out[$transaction_id]['Доход'] = (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : 'Σ ') . $inc;
                        $out[$transaction_id]['Расход'] = (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : 'Σ ') . $exp;
                    }
                    $out[$transaction_id]['Ответственный'] = get_user_name($transaction);
                    $out[$transaction_id]['Примечание'] = $transaction['comment'];
                }
                // итого
                /*$out[]['Итого'] = ;
                $set = false;
                foreach ($total as $k => $t) {
                    $show = false;
                    if ($total_inc[$k] > 0 || $total_tr_inc[$k] > 0 || $total_exp[$k] < 0 || $total_tr_exp[$k] < 0) {
                        $class = 'data-toggle="tooltip" title="Итого" class="' . ($t > 0 ? 'text-success' : 'text-warning') . '"';
                        $out .= '<br /><span ' . $class . '>' . show_price($t) . '&nbsp;' . $currencies[$k]['shortName'] . '</span>';
                        $set = $show = true;
                    }
                    if ($total_inc[$k] > 0 || $show == true) {
                        $out_inc .= '<br /><span title="Доход" class="text-success">';
                        $out_inc .= show_price($total_inc[$k]) . '&nbsp;' . $currencies[$k]['shortName'] . '</span>';
                    }
                    if ($total_tr_inc[$k] > 0 || $total_tr_exp[$k] < 0) {
                        $out_trans .= '<br /><span data-original-title="Перевод" class="popover-info" data-content="';
                        $out_trans .= show_price($total_tr_inc[$k]) . '; ' . show_price($total_tr_exp[$k]);
                        $out_trans .= '">' . show_price($total_tr_inc[$k] + $total_tr_exp[$k]) . '&nbsp;';
                        $out_trans .= $currencies[$k]['shortName'] . '</span>';
                    }
                    if ($total_exp[$k] < 0 || $show == true) {
                        $out_exp .= '<br /><span title="Расход" class="text-warning">';
                        $out_exp .= show_price($total_exp[$k]) . '&nbsp;' . $currencies[$k]['shortName'] . '</span>';
                    }
                }
                if ($set == false) $out .= 0;
                $out .= $out_inc . $out_exp . $out_trans . '</td><td colspan="0"></td></tr>';
                $out .= '</tbody></table>';
                if ($show_balace == true) {
                    $out = $this->balance($query_balance, $contractors, $currencies, $total) . $out;
                }
                $out .= '</div>';*/
            } else {
            $out .= '<div class="out-transaction"><table class="table table-striped table-compact"><thead><tr><td></td><td>'.l('Дата').'</td>';
            $out .= '<td>' . l('Касса') . '</td><td>' . l('Контрагент') . '</td><td>' . l('Заказ клиента') . '</td><td>' . l('Заказ поставщика') . '</td>';
            if ($contractors == true) {
                $out .= '<td>' . l('Транзакция') . '</td><td>' . l('Доход') . '</td><td>' . l('Расход') . '</td><td>' . l('Серийник') . '</td>';
            } else {
                $out .= '<td>' . l('Цепочка') . '</td><td>' . l('Доход') . '</td><td>' . l('Расход') . '</td>';
            }
            $out .= '<td>' . l('Ответственный') . '</td><td>' . l('Примечание') . '</td></tr></thead><tbody>';
            $total = $total_inc = $total_exp = $total_tr_inc = $total_tr_exp =/* $balance =*/ array_fill_keys(array_keys($currencies), '');
            foreach ($transactions as $transaction_id=>$transaction) {
                //$sum = 'Неизвестный перевод';
                $cashbox_info = l('Неизвестная операция');
                $exp = $inc = 0;

                // без группировки
                // расход
                if ($transaction['transaction_type'] == 1 && $transaction['count_t'] == 0) {
                    // с кассы
                    $cashbox_info = '';
                    if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'], $transaction['cashboxes'])) {
                        $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['name'];
                    }
                    // в категорию
                    $cashbox_info .= ' &rarr; ' . $transaction['category_name'];
                    // сумма
                    //$sum = show_price($transaction['value_from']);
                    $exp = show_price($transaction['value_from']);
                    if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'], $transaction['cashboxes']) &&
                        array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'], $currencies)) {
                        //$sum .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                        $exp .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                        $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                        //$balance[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                        $total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                    } else {
                        //$sum .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                        $exp .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                        $total[$this->currency_suppliers_orders] += $transaction['value_from'];
                        //$balance[$this->currency_suppliers_orders] += $transaction['value_from'];;
                        $total_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                    }
                }
                // доход
                if ($transaction['transaction_type'] == 2 && $transaction['count_t'] == 0) {
                    // в кассу
                    $cashbox_info = '';
                    if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'], $transaction['cashboxes'])) {
                        $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['name'];
                    }
                    // с категории
                    $cashbox_info .= ' &larr; ' . $transaction['category_name'];
                    // сумма
                    //$sum = show_price($transaction['value_to']);
                    $inc = show_price($transaction['value_to']);
                    if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'], $transaction['cashboxes']) &&
                        array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'], $currencies)) {
                        //$sum .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                        $inc .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                        $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                        //$balance[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                        $total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                    } else {
                        //$sum .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                        $inc .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                        $total[$this->currency_suppliers_orders] += $transaction['value_to'];
                        //$balance[$this->currency_suppliers_orders] += $transaction['value_to'];
                        $total_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                    }
                }
                // перевод
                if ($transaction['transaction_type'] == 3 /*&& $transaction['count_t'] == 0*/) {
                    // с кассы
                    $cashbox_info = '';
                    if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'], $transaction['cashboxes'])) {
                        $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['name'];
                    }
                    $cashbox_info .= ' &rarr; ';
                    // в кассу
                    if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'], $transaction['cashboxes'])) {
                        $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['name'];
                    }
                    // сумма
                    //$sum = show_price($transaction['value_from']);
                    $exp = show_price($transaction['value_from']);
                    if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'], $transaction['cashboxes']) &&
                        array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'], $currencies)) {
                        //$sum .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                        $exp .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                        $total_tr_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                        //$balance[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                        $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                    } else {
                        //$sum .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                        $exp .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                        $total_tr_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                        //$balance[$this->currency_suppliers_orders] += $transaction['value_from'];
                        $total[$this->currency_suppliers_orders] += $transaction['value_from'];
                    }
                    //$sum .= ' &rarr; ';
                    //$sum .= show_price($transaction['value_to']);
                    $inc = show_price($transaction['value_to']);
                    if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'], $transaction['cashboxes']) &&
                        array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'], $currencies)) {
                        //$sum .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                        $inc .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                        $total_tr_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                        //$balance[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                        $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                    } else {
                        //$sum .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                        $inc .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                        $total_tr_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                        //$balance[$this->currency_suppliers_orders] += $transaction['value_to'];
                        $total[$this->currency_suppliers_orders] += $transaction['value_to'];
                    }
                }
                // группировано
                // расход
                if ($transaction['transaction_type'] == 1 && $transaction['count_t'] > 0) {
                    // с кассы
                    $cashbox_info = '';
                    if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'], $transaction['cashboxes'])) {
                        $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['name'];
                    }
                    // в категорию
                    $cashbox_info .= ' &rarr; ' . $transaction['category_name'];
                    // сумма
                    //$sum = show_price($transaction['value_from'] + $transaction['value_to']);
                    $exp = show_price($transaction['value_from']);
                    $inc = show_price($transaction['value_to']);
                    if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_from'], $transaction['cashboxes']) &&
                        array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency'], $currencies)) {
                        //$sum .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                        $exp .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                        $inc .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']]['shortName'];
                        $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
                        $total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'];
                        $total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_to'];
                        //$balance[$transaction['cashboxes'][$transaction['cashboxes_currency_id_from']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
                    } else {
                        //$sum .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                        $exp .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                        $inc .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                        $total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'];
                        $total_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                        $total_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                        //$balance[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'];
                    }
                }
                // доход
                if ($transaction['transaction_type'] == 2 && $transaction['count_t'] > 0) {
                    // в кассу
                    $cashbox_info = '';
                    if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'], $transaction['cashboxes'])) {
                        $cashbox_info .= $transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['name'];
                    }
                    // с категории
                    $cashbox_info .= ' &larr; ' . $transaction['category_name'];
                    // сумма
                    //$sum = show_price($transaction['value_from'] + $transaction['value_to']);
                    $exp = show_price($transaction['value_from']);
                    $inc = show_price($transaction['value_to']);
                    if (array_key_exists('cashboxes', $transaction) && array_key_exists($transaction['cashboxes_currency_id_to'], $transaction['cashboxes']) &&
                        array_key_exists($transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency'], $currencies)) {
                        //$sum .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                        $inc .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                        $exp .= '&nbsp;' . $currencies[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']]['shortName'];
                        $total[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
                        $total_exp[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'];
                        $total_inc[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_to'];
                        //$balance[$transaction['cashboxes'][$transaction['cashboxes_currency_id_to']]['currency']] += $transaction['value_from'] + $transaction['value_to'];
                    } else {
                        //$sum .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                        $inc .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                        $exp .= '&nbsp;' . $currencies[$this->currency_suppliers_orders]['shortName'];
                        $total[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'];
                        $total_exp[$this->currency_suppliers_orders] += $transaction['value_from'];
                        $total_inc[$this->currency_suppliers_orders] += $transaction['value_to'];
                        //$balance[$this->currency_suppliers_orders] += $transaction['value_from'] + $transaction['value_to'];
                    }
                }
                $group = '<a class="hash_link" href="' . $this->all_configs['prefix'] . 'accountings?';
                if ($transaction['supplier_order_id'] > 0) {
                    $group .= 's_id=' . $transaction['supplier_order_id'] . '&grp=1#transactions-contractors">(';
                } else if($transaction['client_order_id'] > 0) {
                    $group .= 'o_id=' . $transaction['client_order_id'] . '&grp=1#transactions-cashboxes">(';
                }
                $group .= $transaction['count_t'] . ' транз.)</a>';

                $out .= '<tr>';
                $out .= '<td>' . $transaction_id . '</td>';
                $out .= '<td><span title="' . do_nice_date($transaction['date_transaction'], false, false) . '">' . do_nice_date($transaction['date_transaction'], true, false) . '</span></td>';
                $out .= '<td>' . ($transaction['count_t'] > 1 ? $group : $cashbox_info) . '</td>';
                $out .= '<td><a class="hash_link" href="' . $this->all_configs['prefix'] . 'accountings?ct=' . $transaction['contractor_id'] . '#transactions-contractors">' . $transaction['contractor_name'] . '</a></td>';
                $out .= '<td>';
                if ($transaction['client_order_id'] > 0) {
                    $out .= '<a class="hash_link" href="' . $this->all_configs['prefix'] . 'orders/create/' . $transaction['client_order_id'] . '">№' . $transaction['client_order_id'] . '</a>';
                    /*if (array_key_exists('count_t', $transaction) && $transaction['count_t'] > 0) {
                        $out .= ' <a class="hash_link" href="' . $this->all_configs['prefix'] . 'accountings?';
                        if ($contractors == false)
                            $out .= 'o_id';
                        else
                            $out .= 's_id';
                        $out .= '=' . $transaction['client_order_id'] . '&grp=1#transactions-';
                        if ($contractors == true)
                            $out .= 'contractors';
                        else
                            $out .= 'cashboxes';
                        $out .= '"> (' . $transaction['count_t'] . ' транз.)</a>';
                    }*/
                }
                $out .= '</td>';
                $out .= '<td>' . ($transaction['supplier_order_id'] > 0 ? '<a class="hash_link" href="' . $this->all_configs['prefix'] . 'orders/edit/' . $transaction['supplier_order_id'] . '#create_supplier_order">' . $this->supplier_order_number(array('id'=>$transaction['supplier_order_id'])). '</a>' : '') . '</td>';
                if ($contractors == true) {
                    $out .= '<td><a class="hash_link" href="' . $this->all_configs['prefix'] . 'accountings?t_id=' . $transaction['transaction_id'] . '#transactions-cashboxes">' . $transaction['transaction_id'] . '</td>';
                    $out .= '<td>' . (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : '&#931;&nbsp;') . $inc . '</td>';
                    $out .= '<td>' . (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : '&#931;&nbsp;') . $exp . '</td>';

                    if (array_key_exists('count_t', $transaction) && $transaction['count_t'] > 1) {
                        $out .= '<td>' . $group . '</td>';
                    } else {
                        if ($transaction['item_id'] > 0) {
                        $item = $this->all_configs['db']->query('SELECT serial, id as item_id FROM {warehouses_goods_items} WHERE id=?i',
                            array($transaction['item_id']))->row();
                        $out .= '<td>' . suppliers_order_generate_serial($item, true, true) . '</td>';
                        } else {
                            $out .= '<td></td>';
                        }
                    }
                } else {
                    if (array_key_exists('count_t', $transaction) && $transaction['count_t'] > 1) {
                        $out .= '<td>' . $group . '</td>';
                    } else {
                        $out .= '<td>' . $transaction['chain_id'] . '</td>';
                    }
                    $out .= '<td>' . (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : '&#931;&nbsp;') . $inc . '</td>';
                    $out .= '<td>' . (((isset($_GET['grp']) && $_GET['grp'] == 1) || $transaction['count_t'] < 2) ? '' : '&#931;&nbsp;') . $exp . '</td>';
                }
                $out .= '<td>' . get_user_name($transaction) . '</td>';
                $out .= '<td>' . cut_string($transaction['comment']) . '</td>';
                $out .= '</tr>';
            }
            // итого
            $out .= '<tr><td colspan="5"></td><td colspan="2">' . l('Итого') . ': ';
            $out_inc = $out_exp = $out_trans ='</td><td>';
            $set = false;
            foreach ($total as $k => $t) {
                $show = false;
                if ($total_inc[$k] > 0 || $total_tr_inc[$k] > 0 || $total_exp[$k] < 0 || $total_tr_exp[$k] < 0) {
                    $class = 'data-toggle="tooltip" title="Итого" class="' . ($t > 0 ? 'text-success' : 'text-warning') . '"';
                    $out .= '<br /><span ' . $class . '>' . show_price($t) . '&nbsp;' . $currencies[$k]['shortName'] . '</span>';
                    $set = $show = true;
                }
                if ($total_inc[$k] > 0 || $show == true) {
                    $out_inc .= '<br /><span title="Доход" class="text-success">';
                    $out_inc .= show_price($total_inc[$k]) . '&nbsp;' . $currencies[$k]['shortName'] . '</span>';
                }
                if ($total_tr_inc[$k] > 0 || $total_tr_exp[$k] < 0) {
                    $out_trans .= '<br /><span data-original-title="Перевод" class="popover-info" data-content="';
                    $out_trans .= show_price($total_tr_inc[$k]) . '; ' . show_price($total_tr_exp[$k]);
                    $out_trans .= '">' . show_price($total_tr_inc[$k] + $total_tr_exp[$k]) . '&nbsp;';
                    $out_trans .= $currencies[$k]['shortName'] . '</span>';
                }
                if ($total_exp[$k] < 0 || $show == true) {
                    $out_exp .= '<br /><span title="Расход" class="text-warning">';
                    $out_exp .= show_price($total_exp[$k]) . '&nbsp;' . $currencies[$k]['shortName'] . '</span>';
                }
            }
            if ($set == false) $out .= 0;
            $out .= $out_inc . $out_exp . $out_trans . '</td><td colspan="0"></td></tr>';
            $out .= '</tbody></table>';
            if ($show_balace == true) {
                $out = $this->balance($query_balance, $contractors, $currencies, $total, $by_day) . $out;
            }
            $out .= '</div>';
            }
        } else {
            if ($show_balace == true)
                $out .= $this->balance($query_balance, $contractors, $currencies, null, $by_day);
            $out .= '<p class="text-danger">' . l('Нет транзакций по Вашему запросу') . '.</p>';
        }

        return $out;
    }

    /**
     * @param $order
     * @return string
     */
    public function getClientIcons($order)
    {
        $client_orders = $this->all_configs['db']->query('SELECT  wt.icon, wt.name AS wt_name, wg.color, wg.name AS wg_name '
            . 'FROM {orders_suppliers_clients} AS osc '
            . 'LEFT JOIN {orders} AS o ON osc.client_order_id=o.id '
            . 'LEFT JOIN {warehouses} AS w ON o.accept_wh_id=w.id '
            . 'LEFT JOIN {warehouses_types} AS wt ON wt.id=w.type '
            . 'LEFT JOIN {warehouses_groups} AS wg ON wg.id=w.group_id '
            . 'WHERE osc.supplier_order_id=?i GROUP BY w.group_id', array($order['id']))->assoc();
        $icon = '';
        if ($client_orders) {
            foreach ($client_orders as $co) {
                $color = preg_match('/^#[a-f0-9]{6}$/i', trim($co['color'])) ? trim($co['color']) : '#000000';
                $icon .= '<i style="color:' . $color . ';" title="Принято в ' . htmlspecialchars($co['wg_name']) . '" class="' . htmlspecialchars($co['icon']) . '"></i>';
            }
        }
        return $icon;
    }
}


/*
 * выгрузка заказа

    if ( !isset($_POST['order_id']) || $_POST['order_id'] < 1 ) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Такого заказа не существует', 'error'=>true));
        exit;
    }

    $uploaddir = $sitepath . '1c/orders_to_suppliers/';
    if ( !is_dir($uploaddir) ) {
        if( mkdir($uploaddir))  {
            chmod( $uploaddir, 0777 );
        } else {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Нет доступа к директории ' . $uploaddir, 'error'=>true));
            exit;
        }
    }

    $order = $this->all_configs['db']->query('SELECT o.id, o.price, o.count, o.count_debit, o.date_add, o.date_come, o.date_wait,
            o.its_warehouse, o.goods_id, o.user_id, s.code_1c, s.title, u.fio
        FROM {contractors_suppliers_orders} as o
        LEFT JOIN (SELECT `code_1c`, `id`, `title`, `type` FROM {contractors})s ON s.id=o.supplier AND s.type=1
        LEFT JOIN (SELECT `fio`, `id` FROM {users}) u ON u.id=o.user_id
        WHERE o.id=?i', array($_POST['order_id']))->row();

    if ( !$order ) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Такого заказа не существует', 'error'=>true));
        exit;
    }
    $code_1c = $this->all_configs['db']->query('SELECT code_1c FROM {goods} WHERE id=?i', array($order['id']))->el();
    if ( mb_strlen(trim($code_1c), 'UTF-8') == 0 ) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'У выбранного Вами товара нет кода 1с', 'error'=>true));
        exit;
    }
    $order['goods_code_1c'] = trim($code_1c);

    require_once($sitepath . 'configs.php');
    $configs = Configs::getInstance()->get();

    //$suppliers = new Suppliers($this->all_configs['db'], $this->all_configs['prefix'], $oRole);
    //$suppliers->exportSupplierOrder($sitepath, $order, $configs);
    $this->exportSupplierOrder($sitepath, $order, $configs);

    $mod_id = $configs['orders-manage-page'];

    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
        array($_SESSION['id'], 'export-order', $mod_id, $order['id']));

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode(array('message'=>'Заказ успешно выгружен'));
    exit;
*/