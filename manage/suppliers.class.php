<?php

require_once __DIR__ . '/Core/View.php';
require_once __DIR__ . '/Core/Object.php';
require_once __DIR__ . '/Core/Response.php';

/**
 * @property MGoods                      Goods
 * @property MContractorsSuppliersOrders ContractorsSuppliersOrders
 * @property MHistory                    History
 */
class Suppliers extends Object
{
    protected $all_configs;

    public $currencies = null;

    public $currency_suppliers_orders; // валюта заказов поставщикам
    public $currency_clients_orders; // валюта заказов клиентов
    /** @var View */
    protected $view;
    public $uses = array(
        'Goods',
        'ContractorsSuppliersOrders',
        'History'
    );

    /**
     * Suppliers constructor.
     * @param $all_configs
     */
    function __construct($all_configs)
    {
        $this->all_configs = $all_configs;
        $this->view = new View($all_configs);
        $this->currencies = $this->all_configs['configs']['currencies'];
        $this->currency_clients_orders = $this->all_configs['settings']['currency_orders'];
        $this->currency_suppliers_orders = $this->all_configs['settings']['currency_suppliers_orders'];
        if (empty($this->currency_suppliers_orders)) {
            $this->currency_suppliers_orders = $this->currency_clients_orders;
        }

        $this->applyUses();
    }

    /**
     * @param $mod_id
     * @param $post
     * @return array
     */
    function edit_order($mod_id, $post)
    {

        $count = isset($post['warehouse-order-count']) && $post['warehouse-order-count'] > 0 ? $post['warehouse-order-count'] : 1;
        $supplier = isset($post['warehouse-supplier']) && $post['warehouse-supplier'] > 0 ? $post['warehouse-supplier'] : null;
        $date = 86399 + strtotime(isset($post['warehouse-order-date']) ? $post['warehouse-order-date'] : date("d.m.Y"));
        $price = isset($post['warehouse-order-price']) ? intval($post['warehouse-order-price'] * 100) : 0;
        $comment = isset($post['comment-supplier']) ? $post['comment-supplier'] : '';
        $orders = isset($post['so_co']) && is_array($post['so_co']) ? array_filter(array_unique($post['so_co'])) : array();
        $product_id = isset($post['goods-goods']) ? $post['goods-goods'] : 0;
        $order_id = isset($post['order_id']) ? $post['order_id'] : 0;
        //$order['sum_paid'] == 0 && $order['count_debit'] != $order['count_come']
        $warehouse = isset($post['warehouse']) && $post['warehouse'] > 0 ? $post['warehouse'] : null;
        $location = isset($post['location']) && $post['location'] > 0 ? $post['location'] : null;
        $num = isset($post['warehouse-order-num']) && mb_strlen(trim($post['warehouse-order-num']),
            'UTF-8') > 0 ? trim($post['warehouse-order-num']) : null;
        $warehouse_type = isset($post['warehouse_type']) ? intval($post['warehouse_type']) : 0;
        $its_warehouse = null;
        $links = array();

        // достаем заказ
        // достаем заказ поставщику
        $order = $this->ContractorsSuppliersOrders->getByPk($order_id);
        $user_id = $supplier && $order && $order['user_id'] == 0 ? $_SESSION['id'] : ($order['user_id'] > 0 ? $order['user_id'] : null);
        // достаем товар
        $product = $this->Goods->getByPk($product_id);

        try {
            if (!$this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
                throw new ExceptionWithMsg(l('У Вас нет прав'));
            }
            if (!$product) {
                throw new ExceptionWithMsg(l('Укажите деталь'));
            }
            if (!$order) {
                throw new ExceptionWithMsg(l('Заказ не найден'));
            }
            if ($order['confirm'] == 1) {
                throw new ExceptionWithMsg(l('Заказ закрыт'));
            }
            if ($order['avail'] == 0) {
                throw new ExceptionWithMsg(l('Заказ отменен'));
            }
            if ($product['type'] == 1) {
                throw new ExceptionWithMsg(l('На услугу заказ создать нельзя'));
            }
            if (count($orders) > $count) {
                throw new ExceptionWithMsg(l('Ремонтов не может быть больше чем количество в заказе'));
            }
            // проверка на создание заказа с ценой 0
            if ($price == 0 && $this->all_configs['configs']['suppliers-orders-zero'] == false) {
                throw new ExceptionWithMsg(l('Укажите цену больше 0'));
            }

            // редактируем заказ
            try {
                $data = array(
                    'price' => $price,
                    'date_wait' => date("Y-m-d H:i:s", $date),
                    'supplier' => $supplier,
                    'its_warehouse' => $its_warehouse,
                    'goods_id' => $product_id,
                    'user_id' => $user_id,
                    '`count`' => $count,
                    'comment' => $comment,
                    'num' => $num,
                    'warehouse_type' => $warehouse_type,
                );
                if (!empty($warehouse)) {
                    $data['wh_id'] = $warehouse;
                }
                if (!empty($location)) {
                    $data['location_id'] = $location;
                }
                $this->ContractorsSuppliersOrders->update($data, array('id' => $order_id));
            } catch (Exception $e) {
                throw new ExceptionWithMsg(l('Неизвестная ошибка при изменении заказа'));
            }
            $this->exportSupplierOrder($order_id, 3);

            // обновляем дату поставки товара
            $this->all_configs['manageModel']->update_product_wait($product);
            $this->History->save('edit-warehouse-order', $mod_id, $order_id);

            // связь между заказами
            $result = $this->orders_link($order_id, $orders, intval($order['supplier']));

            if (!isset($result['state']) || $result['state'] == false) {
                throw new ExceptionWithMsg(isset($result['msg']) ? $result['msg'] : l('Заявка уже создан'));
            }

            // Уведомлять менеджера, который ответственный за ремонт о том что сроки поставки запчасти изменились
            if (strtotime($order['date_wait']) != $date && isset($result['links']) && count($result['links']) > 0) {
                include_once $this->all_configs['sitepath'] . 'mail.php';
                $messages = new Mailer($this->all_configs);

                foreach ($result['links'] as $link) {
                    if (isset($link['id']) && $link['id'] > 0 && isset($link['manager']) && $link['manager'] > 0) {
                        $href = $this->all_configs['prefix'] . 'orders/create/' . $link['id'];
                        $content = l('Сроки поставки запчасти') . ' "' . htmlspecialchars($link['title']) . '" ' . l('заказа') . ' <a href="' . $href . '">№' . $link['id'] . '</a> ' . l('изменились');
                        $messages->send_message($content, l('Сроки поставки запчасти изменились'), $link['manager'], 1);
                    }
                }
            }

            // сообщение что типа сохранено
            $_SESSION['suppliers_edit_order_msg'] = l('Сохранено успешно');
            $data = array(
                'state' => true
            );
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'message' => $e->getMessage(),
                'msg' => $e->getMessage(),
            );
        }

        return $data;
    }

    /**
     * @param $mod_id
     * @param $post
     * @return array
     */
    public function create_order($mod_id, $post)
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
        $num = isset($post['warehouse-order-num']) && mb_strlen(trim($post['warehouse-order-num']),
            'UTF-8') > 0 ? trim($post['warehouse-order-num']) : null;

        // достаем товар
        $product = $this->Goods->getByPk($product_id);

        try {
            if (!$product) {
                throw new ExceptionWithMsg(l('Укажите деталь'));
            }
            if ($product['type'] == 1) {
                throw new ExceptionWithMsg(l('На услугу заказ создать нельзя'));
            }
            if (count($orders) > $count) {
                throw new ExceptionWithMsg(l('Ремонтов не может быть больше чем количество в заказе'));
            }
            // проверка на создание заказа с ценой 0
            if ($price == 0 && $this->all_configs['configs']['suppliers-orders-zero'] == false) {
                throw new ExceptionWithMsg(l('Укажите цену больше 0'));
            }

            if ($product) {
                // создаем заказ
                try {
                    $id = $this->ContractorsSuppliersOrders->insert(array(
                        'price' => $price,
                        'date_wait' => date("Y-m-d H:i:s", 86399 + strtotime($date)),
                        'supplier' => $supplier,
                        'its_warehouse' => $its_warehouse,
                        'goods_id' => $product_id,
                        'user_id' => $user_id,
                        '`count`' => $count,
                        'comment' => $comment,
                        'group_parent_id' => $group_parent_id,
                        'num' => $num,
                        'warehouse_type' => $warehouse_type
                    ));
                    FlashMessage::set(l('Заказ успешно создан'));
                } catch (Exception $e) {
                    throw new ExceptionWithMsg(l('Неизвестная ошибка при создании заказа'));
                }
                if ($id > 0) {
                    $data['id'] = $id;
                    $data['state'] = true;

                    // обновляем дату поставки товара
                    $this->all_configs['manageModel']->update_product_wait($product_id);

                    // связь между заказами
                    $this->orders_link($id, $orders);

                    $this->History->save('add-warehouse-order', $mod_id, intval($id));

                    $this->buildESO($id);
                }
            }
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'message' => $e->getMessage(),
                'msg' => $e->getMessage(),
            );

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
        if ($text == lq('Запчасть заказана')) {
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
            }
        }

        return $result;
    }

    /**
     * @return string
     */
    function show_filter_service_center()
    {
        $wh_groups = $this->all_configs['db']->query('SELECT id, name FROM {warehouses_groups} ORDER BY id',
            array())->assoc();
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
    function show_filters_suppliers_orders(
        $show_my = false,
        $show_nav = true,
        $inner_wrapper = true,
        $hash = 'show_suppliers_orders'
    ) {
        $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : '')
            . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
            . (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : '');

        $count = $this->all_configs['db']->query('SELECT COUNT(id) FROM {contractors_suppliers_orders}', array())->el();
        $query = !array_key_exists('manage-qty-so-only-debit',
            $this->all_configs['configs']) || $this->all_configs['configs']['manage-qty-so-only-debit'] == false ? 'confirm=0' : 'count_come<>count_debit AND count_come > 0';
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
        $data = array('state' => false, 'msg' => l('Заказ не найден'), 'links' => array());
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
            $data['msg'] = l('Введите номер заказа');
            $data['state'] = true;
        }

        if ($so && count($co_ids) > 0) {

            if ($so['avail'] == 0) {
                return array('msg' => l('Заказ отменен'), 'state' => false);
            }
            // достаем заказ(ы) клиента(ов)
            $cos = $this->all_configs['db']->query('SELECT o.*, og.item_id, og.id as order_goods_id
                  FROM {orders} as o, {orders_goods} as og WHERE o.id IN (?li) AND og.goods_id=?i AND o.id=og.order_id',
                array($co_ids, $so['goods_id']))->assoc('id');

            if ($cos) {
                if (count($links) > $so['count']) {
                    $data['msg'] = l('Осталась') . ' ' . ($so['count'] - count($links)) . ' ' . l('свободных заявок');
                } else {
                    $data['state'] = true;
                    $data['msg'] = l('Успешно сохранено');

                    foreach ($cos as $co) {

                        if (array_key_exists($co['id'], $links)) {
                            $data['links'][$co['id']] = $co;
                            if ($last_so_supplier == 0 && $so['supplier'] > 0) {
                                $text = lq('Запчасть заказана');
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
                                        $text = lq('Запчасть была оприходована') . ' ';
                                    } elseif ($so['count_come'] > 0) {
                                        $text = lq('Запчасть была принята') . '';
                                    } else {
                                        $text = lq('Запчасть заказана');
                                    }
                                } else {
                                    $text = lq('Отправлен запрос на покупку запчасти. Ожидаем ответ.');
                                }
                                $this->add_client_order_comment(intval($co['id']), $text);
                            } else {
                                $data['msg'] = l('Заявка уже создана');
                            }
                        }
                    }
                }
            } else {
                $data['msg'] = l('В заявке нет необходимости');
            }
        }

        if ($so) {

            $query = '';

            $items = array_keys(array_filter($links));

            if (count($items) > count($co_ids)) {
                $data['state'] = false;
                $data['msg'] = l('Отвяжите серийный номер в заказах') . ': ' . implode(', ', $items);
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
                $data['msg'] = l('Успешно сохранено');

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
                            $content = l('Необходимо заказать запчасть') . ' "' . htmlspecialchars($link['title']) . '" ' . l('для заказа') . ' <a href="' . $href . '">№' . $link['order_id'] . '</a>';
                            $messages->send_message($content, l('Необходимо заказать запчасть'), $link['manager'], 1);
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
        $data['content'] = '<h5 class="text-danger">' . l('Заказ не найден') . '</h5>';

        if ($order_id > 0) {
            $order = $this->ContractorsSuppliersOrders->getByPk($order_id);

            if ($order) {
                $data['btns'] = '<input onclick="orders_link(this, \'.btn-open-orders-link-' . $order_id . '\')" class="btn" type="button" value="' . l('Сохранить') . '" />';


                // звязки заказов
                $clients_orders = (array)$this->all_configs['db']->query(
                    'SELECT id, client_order_id FROM {orders_suppliers_clients} WHERE supplier_order_id=?i',
                    array($order_id))->vars();

                $data['content'] = '<h6>' . l('Ремонты ожидающие данную запчасть') . '</h6>';
                $data['content'] .= '<form id="form-orders-links" method="post">';
                $data['content'] .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';
                $data['content'] .= '<div class="form-group"><label class="control-label">' . l('Номер ремонта') . ': </label>';
                for ($i = 0; $i < ($order['count_come'] > 0 ? $order['count_come'] : $order['count']); $i++) {
                    $co_id = current($clients_orders);
                    $data['content'] .= '
                        <div class="' . ($co_id ? 'input-group ' : '') . 'form-group">
                            <input class="clone_clear_val form-control" type="text" value="' . $co_id . '" name="so_co[]">
                            ' . ($co_id ? '
                                <span class="input-group-addon">
                                    <a target="_blank" href="' . $this->all_configs['prefix'] . 'orders/create/' . $co_id . '">' . l('перейти в заказ клиента') . '</a>
                                </span>'
                            : '') . '
                        </div>
                    ';
                    next($clients_orders);
                }
                $data['content'] .= '</div></form>';
            }
        }

        Response::json($data);
    }

    /**
     * @return string
     */
    public function append_js()
    {
        return "<script type='text/javascript' src='{$this->all_configs['prefix']}js/suppliers-orders.js?5'></script>";
    }

    /**
     * @param $product
     */
    function exportProduct($product)
    {

        if ($this->all_configs['configs']['onec-use'] == false) {
            return;
        }

        if (array_key_exists('rounding-goods',
                $this->all_configs['configs']) && $this->all_configs['configs']['rounding-goods'] > 0
        ) {
            $sum1 = round((($product['price'] / 100) * (getCourse($this->all_configs['settings']['currency_suppliers_orders']) / 100)) / $this->all_configs['configs']['rounding-goods']) * $this->all_configs['configs']['rounding-goods'];
            $sum2 = round((($product['price_purchase'] / 100) * (getCourse($this->all_configs['settings']['currency_suppliers_orders']) / 100)) / $this->all_configs['configs']['rounding-goods']) * $this->all_configs['configs']['rounding-goods'];
            $sum3 = round((($product['price_wholesale'] / 100) * (getCourse($this->all_configs['settings']['currency_suppliers_orders']) / 100)) / $this->all_configs['configs']['rounding-goods']) * $this->all_configs['configs']['rounding-goods'];
        } else {
            $sum1 = round(($product['price'] / 100) * (getCourse($this->all_configs['settings']['currency_suppliers_orders']))) / 100;
            $sum2 = round(($product['price_purchase'] / 100) * (getCourse($this->all_configs['settings']['currency_suppliers_orders']))) / 100;
            $sum3 = round(($product['price_wholesale'] / 100) * (getCourse($this->all_configs['settings']['currency_suppliers_orders']))) / 100;
        }

        $doc = array(
            'Предложения' => array(
                'Предложение' => array(
                    'Ид' => $product['code_1c'],
                    'Штрихкод' => $product['barcode'],
                    'Наименование' => $product['title'],
                    'Цены' => array(
                        0 => array(
                            'Цена' => array(
                                'Представление' => $sum1 . ' ' . viewCurrency() . ' за шт',
                                'ИдТипаЦены' => $this->all_configs['configs']['onec-code-price'],
                                'ЦенаЗаЕдиницу' => $sum1,
                                'Валюта' => '' . viewCurrency() . '',
                                'Единица' => 'шт',
                                'Коэффициент' => 1,
                                'Курс' => (getCourse($this->all_configs['settings']['currency_suppliers_orders']) / 100),
                            ),
                        ),
                        1 => array(
                            'Цена' => array(
                                'Представление' => $sum2 . ' ' . viewCurrency() . ' за шт',
                                'ИдТипаЦены' => $this->all_configs['configs']['onec-code-price_purchase'],
                                'ЦенаЗаЕдиницу' => $sum2,
                                'Валюта' => '' . viewCurrency() . '',
                                'Единица' => 'шт',
                                'Коэффициент' => 1,
                                'Курс' => (getCourse($this->all_configs['settings']['currency_suppliers_orders']) / 100),
                            ),
                        ),
                        2 => array(
                            'Цена' => array(
                                'Представление' => $sum3 . ' ' . viewCurrency() . ' за шт',
                                'ИдТипаЦены' => $this->all_configs['configs']['onec-code-price_wholesale'],
                                'ЦенаЗаЕдиницу' => $sum3,
                                'Валюта' => '' . viewCurrency() . '',
                                'Единица' => 'шт',
                                'Коэффициент' => 1,
                                'Курс' => (getCourse($this->all_configs['settings']['currency_suppliers_orders']) / 100),
                            ),
                        ),
                    ),
                    'Количество' => $product['exist'],
                )
            )
        );

        $xml = $this->assocArrayToXML($doc);

        $f = fopen($this->all_configs['sitepath'] . '1c/goods/offers_' . $product['id'] . '.xml', 'w+');
        fwrite($f, "\xEF\xBB\xBF" . $xml);
        fclose($f);

        $doc = array(
            'Предложения' => array(
                'Предложение' => array(
                    "Ид" => $product['code_1c'],
                    "Артикул" => $product['article'],
                    "Наименование" => $product['title'],
                    "БазоваяЕдиница" => "шт",
                    "ПолноеНаименование" => $product['title'],
                    'Описание' => $product['content'],
                )
            ),
        );
        if ($product['avail'] != 1) {
            $doc['Предложения']['Предложение']['Статус'] = "Удален";
        }
        if (isset($product['hotline_url'])) {
            $doc['Предложения']['Предложение']["ЗначенияСвойств"] = array(
                "ЗначенияСвойства" => array(
                    "Ид" => $this->all_configs['configs']['onec-code-hotline'],
                    "Значение" => $product['hotline_url'],
                )
            );
        }


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
        $location_id = $location_id ? (array_filter(is_array($location_id) ? $location_id : explode(',',
            $location_id))) : array();

        if (count($wh_id) > 0) {
            $locations = $this->all_configs['db']->query(
                'SELECT id, location FROM {warehouses_locations} WHERE wh_id IN (?li)', array($wh_id))->vars();
            if ($locations) {
                foreach ($locations as $id => $location) {
                    $out .= '<option ' . (in_array($id,
                            $location_id) ? 'selected' : '') . ' value="' . $id . '">' . htmlspecialchars($location) . '</option>';
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
            && count($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders']) > 0
        ) {
            $suppliers = $this->all_configs['db']->query('SELECT id, title FROM {contractors} WHERE type IN (?li) ORDER BY title',
                array(array_values($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders'])))->assoc();
        }
        $goods_html = '';
        if (isset($_SESSION['suppliers_edit_order_msg'])) {
            $goods_html .=
                '<div class="alert alert-success alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span></button>
                    ' . $_SESSION['suppliers_edit_order_msg'] . '
                </div>
            ';
            unset($_SESSION['suppliers_edit_order_msg']);
        }
        if ($order_id) {
            $goods_html .= '<h3>' . l('Редактирование заказа поставщику') . ' №' . $order_id . '</h3>';
        } else {
            $goods_html .= '<h3>' . l('Создание нового заказа поставщику') . '</h3>';
        }
        $goods_html .= '<br><div class="row row-15"><div class="col-sm-' . ($is_modal ? '12' : '6') . '"><form data-validate="parsley" id="suppliers-order-form" method="post">';
        $disabled = '';
        $info_html = '';
        $so_co = '<div class="relative"><input type="text" name="so_co[]" class="form-control clone_clear_val" /><i class="glyphicon glyphicon-plus cloneAndClear"></i></div>';
        $has_orders = false;
        if ($suppliers) {
            $order = array(
                'price' => '',
                'count' => '',
                'date_wait' => '',//date("d.m.Y"),
                'supplier' => '',
                'goods_id' => '',
                'title' => l('Создать заказ'),
                'btn' => '<input type="button" class="btn submit-from-btn" onclick="create_supplier_order(this)" value="' . l('Создать') . '" />',
                'product' => '',
                'comment' => '',
                'unavailable' => 0,
                'location' => '',
                'id' => '',
                'num' => '',
                'avail' => 1,
                'warehouse_type' => 0,
            );
            if ($order_id) {
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
                    if ($cos) {
                        $has_orders = true;
                    }
                    for ($i = 0; $i < ($order['count_come'] > 0 ? $order['count_come'] : $order['count']); $i++) {
                        $co = current($cos);
                        $so_co .= '<input type="text" name="so_co[]" readonly class="form-control" value="' . $co . '" />';
                        next($cos);
                    }
                    $order['title'] = l('Редактировать заказ');
                    $order['date_wait'] = date("d.m.Y", strtotime($order['date_wait']));
                    $order['price'] /= 100;
                    if ($order['confirm'] == 0 && $order['avail'] == 1 && ((/*$order['user_id'] == $_SESSION['id'] &&*/
                                $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') && $order['sum_paid'] == 0 && $order['count_come'] == 0) || $this->all_configs['oRole']->hasPrivilege('site-administration'))
                    ) {
                        $order['btn'] = '<input type="button" class="btn btn-mini btn-success" onclick="edit_supplier_order(this)" value="' . l('Сохранить') . '" />';
                        $order['btn'] .= ' <input ' . ($order['avail'] == 1 ? '' : 'disabled') . ' type="button" class="btn btn-mini btn-warning" onclick="avail_supplier_order(this, \'' . $order_id . '\', 0)" value="' . l('Отменить') . '" />';
                        $order['btn'] .= ' <input ' . ($order['unavailable'] == 1 ? 'disabled' : '') . ' type="button" class="btn btn-mini" onclick="end_supplier_order(this, \'' . $order_id . '\')" value="' . l('Запчасть не доступна к заказу') . '" />';
                    } else {
                        $order['btn'] = '';
                        $disabled = 'disabled';
                    }
                    $order['product'] = $this->all_configs['db']->query('SELECT title FROM {goods} WHERE id=?i',
                        array($order['goods_id']))->el();

                    if ($order['count_debit'] > 0) {
                        $info_html .= '<div class="form-group"><label>' . l('Создал') . ':&nbsp;</label>'
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

                    $Transactions = new Transactions($this->all_configs);
                    $info_html .= $Transactions->get_transactions($this->currencies, false, null, true,
                        array('supplier_order_id' => $order_id), false);

                    if ($order['sum_paid'] == 0 /*&& $order['count_come'] > 0*/ && $order['count_debit'] != $order['count_come']/* && $order['wh_id'] > 0*/) {
                        $goods_html .= '<div class="form-group"><label>' . l('Склад') . ': ' . InfoPopover::getInstance()->createQuestion('l_suppliers_order_wh_info') . ' </label>'
                            . '<select name="warehouse" ' . $disabled . ' onchange="change_warehouse(this)" class="select-warehouses-item-move form-control"><option value=""></option>';
                        // список складов
                        //if ($warehouses == null)
                        $warehouses = $this->all_configs['db']->query('SELECT id, title FROM {warehouses} as w WHERE consider_store=1 ORDER BY title',
                            array())->assoc();
                        if ($warehouses) {
                            foreach ($warehouses as $warehouse) {
                                if ($warehouse['id'] == $order['wh_id']) {
                                    $goods_html .= '<option selected value="' . $warehouse['id'] . '">' . $warehouse['title'] . '</option>';
                                } else {
                                    $goods_html .= '<option value="' . $warehouse['id'] . '">' . $warehouse['title'] . '</option>';
                                }

                            }
                        }
                        $goods_html .= '</select></div>';
                        $goods_html .= '<div class="form-group"><label>' . l('Локация') . ':</label><select class="form-control select-location" name="location">';
                        $goods_html .= $this->gen_locations($order['wh_id'], $order['location_id']);
                        $goods_html .= '</select></div>';
                    }
                } else {
                    $order = array(
                        'price' => '',
                        'count' => '',
                        'date_wait' => '',
                        'supplier' => '',
                        'goods_id' => '',
                        'title' => l('Создать заказ'),
                        'btn' => '<input type="submit" class="btn btn-primary submit-from-btn" name="new-order" value="Создать заказ поставщику" />',
                        'product' => '',
                        'comment' => '',
                        'unavailable' => 0,
                        'location' => '',
                        'id' => '',
                        'num' => '',
                        'avail' => 1,
                        'warehouse_type' => 0,
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
                foreach ($suppliers as $supplier) {
                    if ($order['supplier'] == $supplier['id']) {
                        $goods_html .= '<option selected value="' . $supplier['id'] . '">' . $supplier['title'] . '</option>';
                    } else {
                        $goods_html .= '<option value="' . $supplier['id'] . '">' . $supplier['title'] . '</option>';
                    }
                }
                $goods_html .= '
                            </select>
                            <div class="input-group-btn">
                                <button type="button" data-form_id="new_supplier_form" data-action="accountings/ajax?act=create-contractor-form-no-modal" class="typeahead_add_form btn btn-info" data-id="supplier_creator">' . l('Добавить') . '</button>
                            </div>
                        </div>
                        ' . ($is_modal ? $new_supplier_form : '') . '
                    </div>
                ';
                $goods_html .= '<div class="form-group"><label>' . l('Дата поставки') . '<b class="text-danger">*</b>: ' . InfoPopover::getInstance()->createQuestion('l_suppliers_order_date_info') . '</label>
                                <input class="datetimepicker form-control" ' . $disabled . ' data-format="yyyy-MM-dd" type="text" name="warehouse-order-date" data-required="true" value="' . ($order['date_wait'] ? date('Y-m-d',
                        strtotime($order['date_wait'])) : '') . '" />
                                </div>';
            }
            if ($goods) {
                $goods_html .= '<div class="form-group relative"' . ($has_orders ? ' onclick="alert(\'' . l('Данный заказ поставщику создан на основании заказа клиенту. Вы не можете изменить запчасть в данном заказе.') . '\');return false;"' : '') . '><label>' . l('Запчасть') . '<b class="text-danger">*</b>: </label>'
                    . typeahead($this->all_configs['db'], 'goods-goods', true, $order['goods_id'],
                        (15 + $typeahead), 'input-xlarge', 'input-medium', '', false, false, '', false, l('Введите'),
                        array(
                            'name' => l('Добавить'),
                            'action' => 'products/ajax/?act=create_form',
                            'form_id' => 'new_device_form'
                        ), $has_orders)
                    . ($is_modal ? $new_device_form : '') . '</div>';
            }
            $goods_html .= '<div class="form-group"><label for="warehouse_type">' . l('Тип поставки') . '<b class="text-danger">*</b>: ' . InfoPopover::getInstance()->createQuestion('l_suppliers_order_type_info') . '</label>'
                . '<div class="radio"><label><input data-required="true" type="radio" name="warehouse_type" value="1" ' . ($order['warehouse_type'] == 1 ? 'checked' : '') . ' />' . l('Локально') . '</label></div>'
                . '<div class="radio"><label><input type="radio" name="warehouse_type" data-required="true" value="2" ' . ($order['warehouse_type'] == 2 ? 'checked' : '') . ' />' . l('Заграница') . '</label></div></div>';

            $goods_html .= '<div class="form-group"><label>' . l('Номер') . ': ' . InfoPopover::getInstance()->createQuestion('l_suppliers_order_number_info') . '</label>'
                . '<input type="text" ' . $disabled . ' name="warehouse-order-num" class="form-control" value="' . $order['num'] . '"/></div>';
            $goods_html .= '<div class="form-group"><label>' . l('Количество') . '<b class="text-danger">*</b>: </label>'
                . '<input type="text" ' . $disabled . ' data-required="true" onkeydown="return isNumberKey(event)" name="warehouse-order-count" class="form-control" value="' . htmlspecialchars($order['count']) . '"/></div>';
            $goods_html .= '<div class="form-group"><label>' . l('Цена за один') . ' (' . viewCurrencySuppliers('shortName') . ')<b class="text-danger">*</b>: </label>'
                . '<input type="text" ' . $disabled . ' data-required="true" onkeydown="return isNumberKey(event, this)" name="warehouse-order-price" class="form-control" value="' . htmlspecialchars($order['price']) . '"/></div>';
            $goods_html .= '<div class="form-group"><label>' . l('Примечание') . ': </label>'
                . '<textarea ' . $disabled . ' name="comment-supplier" class="form-control">' . htmlspecialchars($order['comment']) . '</textarea></div>';

            $goods_html .= '<div class="form-group"><label>' . l('номер ремонта') . '</label> (' . l('если запчасть заказывается под конкретный ремонт') . '): ' . InfoPopover::getInstance()->createQuestion('l_suppliers_order_order_info') . '' . $so_co . '</div>';
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
                    ' . (!$is_modal ? $new_supplier_form : '') . '
                    ' . (!$is_modal ? $new_device_form : '') . '
                </div>
            </div>
            ' . $info_html . '
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
        if ($this->all_configs['configs']['onec-use'] == false) {
            return;
        }

        $order = null;

        if (array_key_exists('erp-contractors-use-for-suppliers-orders',
                $this->all_configs['configs']) && count($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders']) > 0
        ) {
            $order = $this->all_configs['db']->query('SELECT o.id, o.price, o.count, o.count_come, o.date_add, o.date_come, o.date_wait,
                    o.its_warehouse, o.goods_id, o.user_id, s.code_1c, s.title, u.fio
                FROM {contractors_suppliers_orders} as o
                LEFT JOIN (SELECT `code_1c`, `id`, `title`, `type` FROM {contractors})s ON s.id=o.supplier AND s.type IN (?li)
                LEFT JOIN (SELECT `fio`, `id` FROM {users}) u ON u.id=o.user_id
                WHERE o.id=?i', array(
                array_values($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders']),
                $order_id
            ))->row();
        }

        if (!$order) {
            return false;
        }

        $code_1c = $this->all_configs['db']->query('SELECT code_1c FROM {goods} WHERE id=?i',
            array($order['id']))->el();
        $order['goods_code_1c'] = trim($code_1c);

        $types = array(
            1 => 'Создан заказ поставщику со статусом "Согласован"',
            2 => 'Статус заказа меняется с "согласован" на "к поступлению"',
            3 => 'Заказ поставщику редактируется',
            4 => 'Заказ поставщику удаляется',
            5 => 'Заказ поставщику удаляется. Создается 2 новых заказа: один со статусом "к поступлению" и сегодняшней датой, второй со статусом "согласован" и датой на когда ожидаются',
        );

        $status = $types[$type];

        $doc = array(
            'Документы' => array(
                'Документ' => array(
                    'Ид' => $order['id'],
                    'Номер' => $order['id'],
                    'ДатаСоздания' => date('Y-m-d', strtotime($order['date_add'])),
                    'ВремяСоздания' => date('H:i:s', strtotime($order['date_add'])),
                    'ДатаПрихода' => date('Y-m-d', strtotime($order['date_wait'])),
                    'ВремяПрихода' => date('H:i:s', strtotime($order['date_wait'])),
                    'ХозОперация' => "Заказ Поставщику",
                    'Роль' => "Продавец",
                    'Сумма' => (($order['price'] / 100) * $order['count']),
                    'ЦенаЗаЕдиницу' => $order['price'] / 100,
                    'Количество' => $order['count'],
                    'Валюта' => "USD",
                    'ТоварИд' => $order['goods_code_1c'],
                    'Контрагенты' => array(
                        'Контрагент' => array(
                            'Ид' => $order['code_1c'],//$order['user_id'],
                            'Наименование' => $order['title'],//$order['fio'],
                            'Роль' => "Покупатель",
                            'ПолноеНаименование' => $order['title'],//$order['fio'],
                        )
                    ),
                ),
                "ЗначенияРеквизитов" => array(
                    "ЗначениеРеквизита" => array(
                        "Наименование" => "Статус заказа",
                        "Значение" => $status
                    )
                )
            )
        );

        $xml = $this->assocArrayToXML($doc);

        $f = fopen($this->all_configs['sitepath'] . '1c/orders_to_suppliers/order_' . $order['id'] . '.xml', 'w+');
        fwrite($f, "\xEF\xBB\xBF" . $xml);
        fclose($f);
    }

    /**
     * @param $order
     */
    function exportOrder($order)
    {

        if ($this->all_configs['configs']['onec-use'] == false) {
            return;
        }

        $sum = $order['sum'] / 100;

        $doc = array(
            'Документ' => array(
                'Ид' => $order['id'],
                'Номер' => $order['id'],
                'Дата' => date('Y-m-d', strtotime($order['date'])),
                'ХозОперация' => "Заказ товара",
                'Роль' => "Продавец",
                'Курс' => $order['course_value'],
                'Сумма' => $sum,
                'Валюта' => viewCurrency(),
                'Время' => date('H:i:s', strtotime($order['date'])),
                'Комментарий' => $order['comment'],
                'Контрагенты' => array(
                    'Контрагент' => array(
                        'Ид' => $order['user_id'],
                        'Наименование' => $order['fio'],
                        'Роль' => "Покупатель",
                        'ПолноеНаименование' => $order['fio'],
                    )
                )
            )
        );

        if (isset($order['goods'])) {
            foreach ($order['goods'] as $product) {

                if (array_key_exists('rounding-goods',
                        $this->all_configs['configs']) && $this->all_configs['configs']['rounding-goods'] > 0
                ) {
                    $sum = round(($product['price'] / 100 * $order['course_value'] / 100) / $this->all_configs['configs']['rounding-goods']) * $this->all_configs['configs']['rounding-goods'];
                    $wsum = round(($product['warranties_cost'] / 100 * $order['course_value'] / 100) / $this->all_configs['configs']['rounding-goods']) * $this->all_configs['configs']['rounding-goods'];
                } else {
                    $wsum = $product['warranties_cost'] * $order['course_value'] / 100;
                    $sum = $product['price'] * $order['course_value'] / 100;
                }

                $doc['Документ']['Товары'][]['Товар'] = array(
                    'Ид' => $product['code_1c'],
                    'Наименование' => $product['title'],
                    'ЦенаЗаЕдиницу' => $sum,
                    'Количество' => $product['count'],
                    'Сумма' => $sum * $product['count'] + $wsum * $product['count'],
                    'Единица' => 'шт',
                    'Коэффициент' => 1,
                    'Гарантия' => array(
                        'Цена' => $wsum,
                        'Количество' => $product['count'],
                        'КоличествоМесяцев' => $product['warranties'],
                    ),
                    'ЗначенияРеквизитов' => array(
                        array(
                            'ЗначениеРеквизита' => array(
                                'Наименование' => "ВидНоменклатуры",
                                'Значение' => "Товар (пр. ТМЦ)",
                            ),
                        ),
                        array(
                            'ЗначениеРеквизита' => array(
                                'Наименование' => "ТипНоменклатуры",
                                'Значение' => "Товар",
                            )
                        )
                    )
                );
            }
        }
        $doc['Документ']["ЗначенияРеквизитов"] = array(
            "ЗначениеРеквизита" => array(
                "Наименование" => "Статус заказа",
                "Значение" => "[N] Принят"
            )
        );

        $xml = $this->assocArrayToXML($doc);

        $f = fopen($this->all_configs['sitepath'] . '1c/orders/order_' . $order['id'] . '.xml', 'w+');
        fwrite($f, "\xEF\xBB\xBF" . $xml);
        fclose($f);
    }

    /**
     * @param $ar
     * @return mixed
     */
    function assocArrayToXML($ar)
    {
        $a = "КоммерческаяИнформация ВерсияСхемы=\"2.04\" ДатаФормирования=\"" . date('Y-m-d') . "\"";
        $xml = new SimpleXMLElement("<?xml version=\"1.0\" encoding=\"utf-8\"?><{$a}></КоммерческаяИнформация>");
        $f = create_function('$f,$c,$a', '
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
        $f($f, $xml, $ar);
        return $xml->asXML();
    }

    /**
     * @param     $id
     * @param int $type
     */
    function buildESO($id, $type = 1)
    {
        if ($this->all_configs['configs']['onec-use'] == false) {
            return;
        }

        $uploaddir = $this->all_configs['sitepath'] . '1c/orders_to_suppliers/';
        if (!is_dir($uploaddir)) {
            if (mkdir($uploaddir)) {
                chmod($uploaddir, 0777);
            } else {
                Response::json(array('message' => 'Нет доступа к директории ' . $uploaddir, 'error' => true));
            }
        }

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
        $categories_html .= ' onchange="javascript:$(\'#goods-' . $num . '\').attr(\'data-cat\', this.value);"';
        $categories_html .= '><option value="0">' . l('Все разделы') . '</option>';

        foreach ($categories as $category) {
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
            Response::json(array('message' => 'У Вас недостаточно прав', 'error' => true));
        }
        // заказ ид
        if (!isset($_POST['order_id']) || $_POST['order_id'] == 0) {
            Response::json(array('message' => 'Не существующий заказ', 'error' => true));
        }
        // достаем заказ
        $order = $this->all_configs['db']->query('SELECT * FROM {contractors_suppliers_orders} WHERE id=?i',
            array($_POST['order_id']))->row();
        // если уже принят то удалить нельзя
        if (!$order) {
            Response::json(array('message' => 'Не существующий заказ', 'error' => true));
        }
        // права
        if ((/*$order['user_id'] == $_SESSION['id'] && */
                $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') && $order['sum_paid'] == 0
                && $order['count_come'] == 0) || ($this->all_configs['oRole']->hasPrivilege('site-administration') && $order['confirm'] == 0)
        ) {
        } else {
            Response::json(array('message' => 'Заказ отменить нельзя', 'error' => true));
        }
        // если уже принят то удалить нельзя
        if ($order['count_come'] > 0) {
            Response::json(array('message' => 'Заказ отменить уже нельзя', 'error' => true));
        }

        // заявки
        $items = (array)$this->all_configs['db']->query(
            'SELECT order_id FROM {warehouses_goods_items} WHERE supplier_order_id=?i AND order_id IS NOT NULL',
            array(intval($_POST['order_id'])))->vars();

        if ($items) {
            Response::json(array(
                'message' => 'Отвяжите серийный номер в заказах: ' . implode(' ,', $items),
                'error' => true
            ));
        }


        $this->ContractorsSuppliersOrders->update(array('avail' => 0), array('id' => $_POST['order_id']));

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

        Response::json(array('message' => 'Заказ успешно удален'));
    }

    /**
     * @param $mod_id
     */
    function remove_order($mod_id)
    {
        // права
        if (!$this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
            Response::json(array('message' => 'У Вас недостаточно прав', 'error' => true));
        }
        // заказ ид
        if (!isset($_POST['order_id']) || $_POST['order_id'] == 0) {
            Response::json(array('message' => 'Не существующий заказ', 'error' => true));
        }
        // достаем заказ
        $order = $this->all_configs['db']->query('SELECT * FROM {contractors_suppliers_orders} WHERE id=?i',
            array($_POST['order_id']))->row();
        // если уже принят то удалить нельзя
        if (!$order) {
            Response::json(array('message' => 'Не существующий заказ', 'error' => true));
        }
        // права
        if ((
                $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') && $order['sum_paid'] == 0
                && $order['count_come'] == 0) || ($this->all_configs['oRole']->hasPrivilege('site-administration') && $order['confirm'] == 0)
        ) {
        } else {
            Response::json(array('message' => 'Заказ удалить нельзя', 'error' => true));
        }
        // если уже принят то удалить нельзя
        if ($order['count_come'] > 0) {
            Response::json(array('message' => 'Заказ уже нельзя удалить', 'error' => true));
        }

        // заявки
        $items = (array)$this->all_configs['db']->query(
            'SELECT order_id FROM {warehouses_goods_items} WHERE supplier_order_id=?i AND order_id IS NOT NULL',
            array(intval($_POST['order_id'])))->vars();

        if ($items) {
            Response::json(array(
                'message' => 'Отвяжите серийный номер в заказах: ' . implode(' ,', $items),
                'error' => true
            ));
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

        $this->History->save('remove-supplier-order', $mod_id, intval($_POST['order_id']));

        Response::json(array('message' => 'Заказ успешно удален'));
    }

    /**
     * @param $mod_id
     * @param $chains
     */
    function accept_order($mod_id, $chains)
    {
        // права
        if (!$this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') && $this->all_configs['configs']['erp-use'] == false) {
            Response::json(array('message' => l('У Вас недостаточно прав'), 'error' => true));
        }
        // количество
        if (!isset($_POST['count']) || $_POST['count'] == 0) {
            Response::json(array('message' => l('Введите количество'), 'error' => true));
        }
        // заказ ид
        if (!isset($_POST['order_id']) || $_POST['order_id'] == 0) {
            Response::json(array('message' => l('Не существующий заказ'), 'error' => true));
        }
        // достаем информацию о заказе
        $order = $this->all_configs['db']->query('SELECT o.*, g.title as product
            FROM {contractors_suppliers_orders} as o LEFT JOIN {goods} as g ON g.id=o.goods_id WHERE o.id=?i',
            array($_POST['order_id']))->row();

        if (!$order) {
            Response::json(array('message' => l('Не существующий заказ'), 'error' => true));
        }
        // уже принят
        if ($order['count_come'] > 0) {
            Response::json(array('message' => l('Заказ уже принят'), 'error' => true));
        }

        // отменен
        if ($order['avail'] == 0) {
            Response::json(array('message' => l('Заказ отменен'), 'error' => true));
        }

        // количество пришло больше чем в заказе
        if ($order['count'] < $_POST['count']) {
            Response::json(array('error' => true, 'message' => l('Количество не может быть больше чем в заказе')));
        }
        // склад
        if (!isset($_POST['wh_id']) || $_POST['wh_id'] == 0) {
            Response::json(array('message' => l('Выберите склад'), 'error' => true));
        }
        if ($order['supplier'] == 0) {
            Response::json(array('message' => l('У заказа не найден поставщик'), 'error' => true));
        }
        // проверяем склад
        $wh_id = $this->all_configs['db']->query('SELECT id FROM {warehouses} WHERE id=?i AND consider_store=?i',
            array($_POST['wh_id'], 1))->el();
        if (!$wh_id || $wh_id == 0) {
            Response::json(array('message' => l('Выберите склад'), 'error' => true));
        }
        // локация
        if (!isset($_POST['location']) || $_POST['location'] == 0) {
            Response::json(array('message' => l('Выберите локацию'), 'error' => true));
        }
        // дата проверки
        if ((!isset($_POST['date_check']) || strtotime($_POST['date_check']) == 0) && !isset($_POST['without_check'])) {
            Response::json(array('message' => l('Укажите дату проверки'), 'error' => true));
        }
        // проверяем локацию
        $location_wh_id = $this->all_configs['db']->query('SELECT wh_id FROM {warehouses_locations} WHERE id=?i',
            array($_POST['location']))->el();
        if ($location_wh_id != $wh_id) {
            Response::json(array('message' => l('Выберите локацию'), 'error' => true));
        }

        $new_order_id = null;
        // количество пришло меньше чем в заказе
        if ($order['count'] > $_POST['count']) {
            // если нет даты прихода
            if (!isset($_POST['date_come']) || empty($_POST['date_come']) || strtotime($_POST['date_come']) == 0) {
                Response::json(array(
                    'new_date' => 1,
                    'message' => l('Вы приняли на склад не все количество товара, укажите дату, '
                        . 'на когда ожидать поставку оставшегося в заказе товара?')
                ));
            }
            if ($order['parent_id'] > 0) {
                $id = $order['parent_id'];
            } else {
                $id = $order['id'];
            }

            // создаем новый заказ поставщику
            $new_order_id = $this->all_configs['db']->query('INSERT INTO {contractors_suppliers_orders} (price, `count`, date_wait, supplier,
                    its_warehouse, goods_id, user_id, parent_id, number, comment) VALUES (?i, ?i, ?, ?n, ?n, ?i, ?i, ?i, ?i, ?)',
                array(
                    $order['price'],
                    ($order['count'] - $_POST['count']),
                    date("Y-m-d H:i:s", (strtotime($_POST['date_come']) + 86399)),
                    $order['supplier'],
                    $order['its_warehouse'],
                    $order['goods_id'],
                    $_SESSION['id'],
                    $id,
                    ($order['number'] + 1),
                    trim($order['comment'])
                ), 'id');
        }

        $date_check = isset($_POST['date_check']) && !isset($_POST['without_check']) ? $_POST['date_check'] : null;

        // обновляем заказ поставщику
        $this->all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET count_come=?i, date_come=NOW(),
                wh_id=?i, location_id=?i, user_id_accept=?i, date_check=?n WHERE id=?i',
            array(
                $_POST['count'],
                $_POST['wh_id'],
                $_POST['location'],
                $_SESSION['id'],
                $date_check,
                $_POST['order_id']
            ));

        // история
        $this->History->save('accept-supplier-order', $mod_id, intval($_POST['order_id']));

        $cos_id = $this->all_configs['db']->query(
            'SELECT id, client_order_id FROM {orders_suppliers_clients} WHERE supplier_order_id=?i AND goods_id=?i',
            array($_POST['order_id'], $order['goods_id']))->vars();

        // добавляем публичный комментарий заказу клиента
        if ($cos_id) {
            $i = 1;
            $send = array();
            foreach ($cos_id as $l_id => $co_id) {
                if ($i > $_POST['count']) {
                    if ($new_order_id) {
                        $this->all_configs['db']->query('UPDATE {orders_suppliers_clients} SET supplier_order_id=?i WHERE id=?i',
                            array($new_order_id, $l_id));
                    } else {
                        $this->all_configs['db']->query('DELETE FROM {orders_suppliers_clients} WHERE id=?i',
                            array($l_id));
                    }
                } else {
                    if (!isset($send[intval($co_id)])) {
                        $text = lq('Ожидаемая запчасть была принята');
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
        $content = l('Необходимо оплатить заказ поставщику') . ' ';
        $content .= '<a href="' . $this->all_configs['prefix'] . 'accountings?so_id=' . $order['id'] . '#a_orders-suppliers">№' . $order['id'] . '</a>';
        $messages->send_message($content, l('Оплатите заказ поставщику'), 'mess-accountings-suppliers-orders', 1);
        // кладовщику
        $content = l('Необходимо оприходовать заказ поставщику') . ' ';
        $content .= '<a href="' . $this->all_configs['prefix'] . 'warehouses?so_id=' . $order['id'] . '#orders-suppliers">№' . $order['id'] . '</a>';
        $query_for_my_warehouses = $this->all_configs['db']->makeQuery(
            'RIGHT JOIN {warehouses_users} as wu ON u.id=wu.user_id AND wu.wh_id=?i', array($_POST['wh_id']));

        $messages->send_message($content, l('Оприходуйте заказ поставщику'), 'mess-warehouses-suppliers-orders', 1,
            $query_for_my_warehouses);
        Response::json(array(
            'message' => l('Успешно'),
            'infopopover_modal' => InfoPopover::getInstance()->createInfoModal('l_accept_supplier_order_info')
        ));
    }

    /**
     * @param bool $show_debit_form_after
     */
    function accept_form($show_debit_form_after = false)
    {
        $data = array();
        $order_id = isset($_POST['object_id']) ? $_POST['object_id'] : 0;
        // список складов
        $warehouses = $this->all_configs['db']->query('SELECT id, title FROM {warehouses} WHERE consider_store=1',
            array())->vars();
        $order = $this->all_configs['db']->query('SELECT * FROM {contractors_suppliers_orders} WHERE id=?i',
            array($order_id))->row();
        $data['state'] = true;
        $data['content'] = $this->view->renderFile('suppliers.class/accept_form', array(
            'warehouses' => $warehouses,
            'order' => $order,
            'controller' => $this
        ));
        $callback = '';
        if ($show_debit_form_after) {
            $callback = ',(form_debit=function(_this){alert_box(_this,false,\'form-debit-so\',{object_id:' . $order_id . '},null,\'warehouses/ajax/\')})';
        }
        $data['btns'] =
            '<input class="btn btn-success" onclick="accept_supplier_order(this' . $callback . ')" type="button" value="' . l('Принять') . '" />';
        $data['functions'] = array('reset_multiselect()');

        Response::json($data);
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

        try {
            if (empty($order)) {
                throw new ExceptionWithMsg(l('Заказ не найден'));
            }
            if ($order['confirm'] == 0) {
                //$data['state'] = false;
                $data['msg'] = l('Заказ уже закрыт');
            }

            // отменен
            if ($order['avail'] == 0) {
                throw new ExceptionWithMsg(l('Заказ отменен'));
            }

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
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'msg' => $e->getMessage()
            );
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
        $data = array('state' => true, 'msg' => l('Успешно закрыт'));

        try {
            $order = $this->all_configs['db']->query('SELECT * FROM {contractors_suppliers_orders} WHERE id=?i',
                array($order_id))->row();
            if (empty($order)) {
                throw  new ExceptionWithMsg(l('Заказ не найден'));
            }
            if ($order['confirm'] == 0) {
                //$data['state'] = false;
                $data['msg'] = 'Заказ уже закрыт';
            }
            if ($order['avail'] == 0) {
                throw  new ExceptionWithMsg(l('Заказ отменен'));
            }
            if ($forcibly == false && (($order['count_come'] - $order['count_debit']) <> 0
                    || ($order['price'] * $order['count_come'] - $order['sum_paid']) <> 0)
            ) {
                throw  new ExceptionWithMsg(l('Заказ еще нельзя закрыть'));
            }

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
                        $content = l('Освободились заказы клиентов: ') . implode(', ', $orders);
                        $content .= '. ' . l('Привяжите к другому заказу поставщику либо создайте новый');
                        $messages->send_message($content, l('Освободились заказы клиентов'), 'edit-clients-orders',
                            1);
                    }
                }
            }
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'msg' => $e->getMessage()
            );
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
            Response::json(array('msg' => l('У Вас недостаточно прав'), 'state' => false));
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
            Response::json(array(
                'msg' => l('Ведите серийный номер или установите галочку сгенерировать'),
                'state' => false
            ));
        }

        if (!$order || $order['count_come'] - $order['count_debit'] == 0) {
            Response::json(array('msg' => l('Заказ уже полностю приходован'), 'state' => false));
        }

        if ($order['supplier'] == 0) {
            Response::json(array('msg' => l('У заказа не найден поставщик'), 'state' => false));
        }

        if ($order['avail'] == 0) {
            Response::json(array('msg' => l('Заказ отменен'), 'state' => false));
        }

        $clear_serials = array_filter($serials);
        if (isset($post['serial']) && count($clear_serials) > 0) {
            $s = $this->all_configs['db']->query(
                'SELECT GROUP_CONCAT(serial) FROM {warehouses_goods_items} WHERE serial IN (?li)',
                array($clear_serials))->el();
            if ($s) {
                Response::json(array('msg' => l('Серийники уже используются: ') . $s, 'state' => false));
            }
        }

        if (count($clear_serials) + count($auto) != $order['count_come']) {
            Response::json(array('msg' => l('Частичное приходование запрещено'), 'state' => false));
        }

        $html = '';
        $msg = $debit_items = $print_items = array();

        foreach ($serials as $k => $serial) {
            $item_id = null;
            if ($order['count_debit'] + count($serials) > $order['count_come']) {
                break;
            }
            if (isset($auto[$k])) {
                $item_id = $this->add_item($order, null, $mod_id);
            } elseif (mb_strlen(trim($serial), 'UTF-8') > 0) {
                $item_id = $this->add_item($order, trim($serial), $mod_id);
            } else {
                $msg[$k] = array(
                    'state' => false,
                    'msg' => l('Ведите серийный номер или установите галочку сгенерировать')
                );
            }
            if (!isset($msg[$k])) {
                if ($item_id > 0) {
                    if (isset($print[$k])) {
                        $print_items[$k] = $item_id;
                    }
                    $debit_items[$k] = suppliers_order_generate_serial(array(
                        'item_id' => $item_id,
                        'serial' => trim($serial)
                    ));
                    $msg[$k] = array(
                        'state' => true,
                        'msg' => l('Серийник ') . ' ' . $debit_items[$k] . ' ' . l(' успешно добавлен')
                    );
                } else {
                    $msg[$k] = array('state' => false, 'msg' => l('Серийник уже используется'));
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
                $html .= '<p><a href="' . $url . '">' . l('Выдать изделия') . '</a>' . l('под заказы на ремонт') . '<a target="_blank" href="' . $url . '" class="btn">Ok</a></p>';
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

                $text = lq('Ожидаемая запчасть поступила на склад');
                foreach ($links as $co_id => $manager_id) {
                    // добавляем комментарий
                    $this->add_client_order_comment(intval($co_id), $text);

                    // отправляем уведомление менеджерам
                    if ($manager_id > 0) {
                        $content = l('Запчасть только что была оприходована, под заказ') . ' ';
                        $content .= '<a href="' . $this->all_configs['prefix'] . 'orders/create/' . $co_id . '">№' . $co_id . '</a>';
                        $messages->send_message($content, l('Запчасть оприходована'), $manager_id, 1);
                    }

                    // отправляем уведомление кладовщикам
                    $content = l('Запчасть только что была оприходована, отгрузите ее под заказ') . ' ';
                    $content .= '<a href="' . $this->all_configs['prefix'] . 'warehouses?con=' . $co_id . '#orders-clients_bind">№' . $co_id . '</a>';
                    $messages->send_message($content, l('Отгрузите запчасть под заказ'), 'mess-debit-clients-orders',
                        1);
                }
            }
            // обновляем количество в заказе поставщику
            $this->ContractorsSuppliersOrders->increase('count_debit', count($debit_items),
                array('id' => $order['id']));
            // обновление цены закупки в товаре
            $this->all_configs['db']->query('UPDATE {goods} SET price_purchase=?i WHERE id=?i',
                array($order['price'], $order['goods_id']));
        }

        // печать
        $print_link = false;
        if (count($print_items) > 0) {
            $print_link = $this->all_configs['prefix'] . 'print.php?act=label&object_id=' . implode(',', $print_items);
        }

        // пробуем закрыть заказ
        $this->close_order($order_id, $mod_id);
        Response::json(array('result' => $msg, 'print_link' => $print_link, 'html' => $html));
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
              VALUES (?i, ?i, ?i, ?i, ?n, ?i, ?i, ?i)', array(
            $order['goods_id'],
            $order['wh_id'],
            $order['location_id'],
            $order['supplier'],
            $serial,
            $order['price'],
            $order['id'],
            $_SESSION['id']
        ), 'id');

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
            $Transactions = new Transactions($this->all_configs);
            $Transactions->add_contractors_transaction(
                array(
                    'transaction_type' => 2,
                    'value_to' => ($order['price'] / 100),
                    'comment' => 'Товар ' . $order['g_title'] . ' приходован на склад ' . $order['wh_title'] . '. Заказ поставщика ' .
                        $this->supplier_order_number($order) . ', серийник ' . suppliers_order_generate_serial(array(
                            'serial' => $serial,
                            'item_id' => $item_id
                        )) .
                        ', сумма ' . ($order['price'] / 100) . '$, ' . date("Y-m-d H:i:s", time()),
                    'contractor_category_link' => $contractor_category_link,
                    'supplier_order_id' => $order['id'],
                    'item_id' => $item_id,
                    'goods_id' => $order['goods_id'],

                    'contractors_id' => $order['supplier'],
                )
            );

            // история
            $this->History->save('debit-supplier-order', $mod_id, intval($order['id']));
        }

        return $item_id;
    }

    /**
     * @param      $order
     * @param null $title
     * @param bool $link
     * @return null|string
     */
    function supplier_order_number($order, $title = null, $link = true)
    {
        if (!array_key_exists('parent_id', $order) || !array_key_exists('number', $order) || !array_key_exists('num',
                $order)
        ) {
            $order = $this->all_configs['db']->query('SELECT number, parent_id, id, num FROM {contractors_suppliers_orders} WHERE id=?i',
                array($order['id']))->row();
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