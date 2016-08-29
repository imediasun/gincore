<?php

require_once __DIR__ . '/Core/Object.php';
require_once __DIR__ . '/Core/View.php';
require_once __DIR__ . '/Core/Exceptions.php';
require_once __DIR__ . '/Core/Log.php';

/**
 * @property MHistory                    History
 * @property MOrders                     Orders
 * @property MSettings                   Settings
 * @property MWarehouses                 Warehouses
 * @property MClients                    Clients
 * @property MOrdersGoods                OrdersGoods
 * @property MCashboxesTransactions      CashboxesTransactions
 * @property MContractorsSuppliersOrders ContractorsSuppliersOrders
 * @property MOrdersSuppliersClients     OrdersSuppliersClients
 * @property MHomeMasterRequests         HomeMasterRequests
 * @property  MContractorsCategoriesLinks ContractorsCategoriesLinks
 */
class Chains extends Object
{
    /** @var View */
    protected $view = null;
    protected $all_configs;
    public $uses = array(
        'History',
        'Orders',
        'Settings',
        'Warehouses',
        'Clients',
        'OrdersGoods',
        'ContractorsSuppliersOrders',
        'OrdersSuppliersClients',
        'CashboxesTransactions',
        'HomeMasterRequests',
        'ContractorsCategoriesLinks'
    );

    //////* типы перемещений *//////
    public $chain_types = array(1, 2, 3, 4);
    public $chain_bind_item = 1;// 1 - кладовщик привязывает серийник и выдает
    public $chain_warehouse = 2;// 2 - склад откуда
    public $chain_accounting_from = 3;// 3 - бухгалтер внесение
    public $chain_accounting_to = 4;// 4 - бухгалтерия выдача
    public $chain_warehouse_mir = 5;// 5 - склад в мир (в бд не храним)
    public $chain_logistic = 6;// 6 - склад логистика

    // менять можно только значения, ключи завязаны в коде
    public $transactions_types = array(
        0 => 0,
        // по умолчанию
        1 => 1,
        // списание
        2 => 2,
        // возврат списания
        3 => 3,
        // возврат поставщику UPDATE `yabloko_cashboxes_transactions` t JOIN `yabloko_contractors_categories_links` cc ON cc.`contractors_categories_id`=91 AND cc.id=t.`contractor_category_link` SET `type`=3
        4 => 4,
        // возврат возврата поставщику UPDATE `yabloko_cashboxes_transactions` t JOIN `yabloko_contractors_categories_links` cc ON cc.`contractors_categories_id`=92 AND cc.id=t.`contractor_category_link` SET `type`=4
        5 => 5,
        // конвертация средств UPDATE `yabloko_cashboxes_transactions` SET type=5 WHERE (cashboxes_currency_id_from=254 AND cashboxes_currency_id_to=255) OR cashboxes_currency_id_from=255 AND cashboxes_currency_id_to=254
        6 => 6,
        // оплата за комиссию UPDATE `yabloko_cashboxes_transactions` SET type=6 WHERE `contractor_category_link`=1752
        7 => 7,
        // оплата за доставку UPDATE `yabloko_cashboxes_transactions` SET type=7 WHERE `contractor_category_link`=1868
        8 => 8,
        // выплата за заказ поставщику UPDATE `yabloko_cashboxes_transactions` SET `type`=8 WHERE `supplier_order_id` > 0
        9 => 9,
        // продажа
        10 => 10,
        // предоплата
    );

    /**
     * Chains constructor.
     * @param $all_configs
     */
    public function __construct($all_configs)
    {
        $this->all_configs = $all_configs;
        $this->view = new View($all_configs);
        $this->applyUses();
    }

    /**
     * @param $order_id
     * @param $mod_id
     * @return bool
     */
    public function close_order($order_id, $mod_id)
    {
        $status = false;

        if ($order_id > 0) {
            $order = $this->Orders->getClosed($order_id);
            $wh_client = $this->Warehouses->getClientWarehouses();
            // продажа
            $arr = array(
                'order_id' => $order_id,
                'wh_id_destination' => $wh_client['w_id'] ?: $this->all_configs['configs']['erp-warehouse-type-mir'],
                'location' => $wh_client['l_id'] ?: $this->all_configs['configs']['erp-location-type-mir'],
            );

            if ($order && $order['location_id'] != $arr['location']) {
                // списание
                if ($order['type'] == 2) {
                    $arr['wh_id_destination'] = $this->Warehouses->getWriteOffWarehouseId();
                    $arr['location'] = $this->Warehouses->getLocationId($arr['wh_id_destination']);
                }
                // пробуем переместить
                $result = $this->move_item_request($arr, $mod_id);
                // достаем заказ
                $order = $this->Orders->getClosed($order_id);
            }

            if ($order && $order['location_id'] == $arr['location']) {
                $status = true;
            }
        }

        return $status;
    }

    /**
     * @param      $post
     * @param null $mod_id
     * @return array
     */
    function move_item_request($post, $mod_id = null)
    {
        $data = array('state' => false);

        // перемещаем изделие на склад если без логистики
        if (isset($post['wh_id_destination']) && (!isset($post['logistic']) || $post['logistic'] != 1)) {
            if (!isset($post['item_id']) && !isset($post['order_id'])) {
                if (!isset($post['item_id'])) {
                    $data['message'] = l('Укажите номер изделия или ремонта');
                }
            } else {
                // использовать логистику
                if ($this->all_configs['configs']['erp-move-item-logistics'] == false) {
                    $data = $this->move_item(
                        (array_key_exists('item_id', $post) && $post['item_id'] > 0) ? $post['item_id'] : null,
                        (array_key_exists('order_id', $post) && $post['order_id'] > 0) ? $post['order_id'] : null,
                        $post['wh_id_destination'],
                        (array_key_exists('location', $post) && $post['location'] > 0) ? $post['location'] : null,
                        $mod_id
                    );
                } else {
                    // цепочка
                    $data = $this->create_chain_header(
                        array(
                            'wh_id' => (array_key_exists('wh_id', $post) && $post['wh_id'] > 0) ? $post['wh_id'] : null,
                            'item_id' => (array_key_exists('item_id',
                                    $post) && $post['item_id'] > 0) ? $post['item_id'] : null,
                            'goods_id' => (array_key_exists('goods_id',
                                    $post) && $post['goods_id'] > 0) ? $post['goods_id'] : null,
                            'wh_id_destination' => $post['wh_id_destination'],
                        ), $mod_id
                    );
                    if (isset($data['chain_id']) && $data['chain_id'] > 0) {
                        // склад куда
                        $this->create_chain_body(array(
                            'chain_id' => $data['chain_id'],
                            'wh_id' => $post['wh_id_destination'],
                            'type' => $this->chain_warehouse,
                        ), $mod_id);
                    }
                }
            }
        }

        $goods_id = (array_key_exists('goods_id', $post) && $post['goods_id'] > 0) ? $post['goods_id'] : null;
        // проверяем галочку логистики или запрос на перемещение с товара или если не кладовщик(администратор)
        if ((isset($post['logistic']) && $post['logistic'] == 1)) {

            $count = array_key_exists('count', $post) && $post['count'] > 0 ? intval($post['count']) : 1;
            $parent = null;

            for ($i = 1; $i <= $count; $i++) {
                // запрос на перемещение и создание цепочки
                $data = $this->create_chain_header(
                    array(
                        'wh_id' => (array_key_exists('wh_id', $post) && $post['wh_id'] > 0) ? $post['wh_id'] : null,
                        'item_id' => (array_key_exists('item_id',
                                $post) && $post['item_id'] > 0) ? $post['item_id'] : null,
                        'goods_id' => $goods_id,
                        'wh_id_destination' => (array_key_exists('wh_id_destination',
                                $post) && $post['wh_id_destination'] > 0) ? $post['wh_id_destination'] : null,
                    ), $mod_id
                );

                if (isset($data['chain_id']) && $data['chain_id'] > 0) {
                    $parent = $parent == null ? $data['chain_id'] : $parent;

                    $this->all_configs['db']->query('UPDATE {chains_headers} SET parent=?i WHERE id=?i',
                        array($parent, $data['chain_id']));
                }
            }
        }

        return $data;
    }

    /**
     * @param $chain
     * @return mixed
     */
    function chain_price($chain)
    {
        return $chain['price'] + $chain['warranties_cost'];
    }

    /**
     * @param      $data
     * @param      $mod_id
     * @param bool $send
     * @return array
     */
    function bind_item_serial($data, $mod_id, $send = true)
    {
        $result = array('state' => true, 'message' => l('Серийник привязан'));

        $order_product_id = isset($data['order_product_id']) ? $data['order_product_id'] : 0;
        $item_id = isset($data['item_id']) && $data['item_id'] != 'undefined' ? $data['item_id'] : null;

        // поиск по id изделия
        $query = $this->all_configs['db']->makeQuery('i.id=?i', array($item_id));

        // поиск по серийнику
        if (!$item_id && isset($data['serial']) && $data['serial'] != 'undefined') {
            $serial = suppliers_order_generate_serial(array('serial' => $data['serial']), false);
            if (gettype($serial) === 'integer') {
                // поиск по id
                $query = $this->all_configs['db']->makeQuery('i.id=?i', array($serial));
            } else {
                // поиск по серийнику
                $query = $this->all_configs['db']->makeQuery('i.serial=?', array($serial));
            }
        }

        $item = $this->all_configs['db']->query('SELECT i.*, o.user_id, o.date_check FROM {warehouses_goods_items} as i
            LEFT JOIN {contractors_suppliers_orders} as o ON o.id=i.supplier_order_id WHERE ?query',
            array($query))->row();

        try {
            // проверяем ид изделия
            if (empty($item)) {
                throw new ExceptionWithMsg(l('Укажите существующее изделие'));
            }
            if (!empty($item['order_id'])) {
                throw new ExceptionWithMsg(l('Серийный номер привязан к другому заказу'));
            }

            $order_product = $this->all_configs['db']->query(
                'SELECT g.id as order_goods_id, o.wh_id, o.location_id, g.order_id as id, g.goods_id, l.id as link,
              l.supplier_order_id, o.status, o.phone, o.manager, g.title
            FROM {orders} as o, {orders_goods} as g
            LEFT JOIN {orders_suppliers_clients} as l ON l.order_goods_id=g.id
            WHERE g.id=?i AND g.order_id=o.id',
                array($order_product_id))->row();

            // проверяем ид изделия
            if ((!$order_product || $order_product_id == 0)) {
                throw new ExceptionWithMsg(l('Заказ не найден'));
            }

            // проверяем есть ли заявка
            if ($order_product['link'] == 0 && (!isset($data['unlink']) || $data['unlink'] == false)) {
                throw new ExceptionWithMsg(l('Заявка не найдена'));
            }

            // проверяем есть ли заявки на изделие
            $count_free = $this->all_configs['db']->query('SELECT COUNT(DISTINCT i.id) - COUNT(DISTINCT l.id) as qty,
                GROUP_CONCAT(l.client_order_id) as orders FROM {warehouses} as w, {warehouses_goods_items} as i
                LEFT JOIN {orders_suppliers_clients} as l ON i.supplier_order_id=l.supplier_order_id AND l.order_goods_id IN
                (SELECT id FROM {orders_goods} WHERE item_id IS NULL) AND l.client_order_id<>?i
                WHERE w.consider_store=?i AND i.wh_id=w.id AND i.order_id IS NULL AND i.supplier_order_id=?i
                GROUP BY i.goods_id', array($order_product['id'], 1, $item['supplier_order_id']))->row();

            if ($count_free && $count_free['qty'] < 1) {
                throw new ExceptionWithMsg(l('Изделие зарезервировано под другие заказы на ремонт: ') . $count_free['orders']);
            } elseif ($order_product['supplier_order_id'] > 0 && $order_product['supplier_order_id'] != $item['supplier_order_id']) {
                if (isset($data['confirm']) && $data['confirm'] == 1) {
                    // замена партии
                    $this->OrdersSuppliersClients->update(array('supplier_order_id' => $item['supplier_order_id']),
                        array('order_goods_id' => $order_product['order_goods_id']));
                    return $this->bind_item_serial($data, $mod_id, $send);
                } else {
                    return array(
                        'message' => l('Запчасть предназначена для другого ремонта, заменить партию?'),
                        'state' => false,
                        'class' => '',
                        'confirm' => true
                    );
                }
            }

            // проверяем доступ
            if (!$this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders')
                && !$this->all_configs['oRole']->hasPrivilege('edit-clients-orders')
                && !$this->all_configs['oRole']->hasPrivilege('logistics')
                && !$this->all_configs['oRole']->hasPrivilege('scanner-moves')
            ) {
                throw new ExceptionWithMsg(l('У Вас нет доступа'));
            }

            // проверяем не привязан ли этот серийник в какуюто цепочку
            if (!$this->can_use_item($item['id'], $order_product['id'])) {
                throw new ExceptionWithMsg(l('Серийный номер привязан к другому заказу на ремонт. Возможно не оприходован заказ поставщику.'));
            }

            // проверяем не привязан ли этот серийник в какуюто цепочку
            if (!$item) {
                throw new ExceptionWithMsg(l('Выберите серийник'));
            }
            if ($order_product) {

                // устанавливаем дату проверки если необходимо
                $query = strtolower($item['date_check']) > 0 ? $this->all_configs['db']->makeQuery(', date_checked=NOW()',
                    array()) : '';

                $ar1 = $this->all_configs['db']->query(
                    'UPDATE {warehouses_goods_items} SET order_id=?i, date_sold=NOW() ?query WHERE id=?i',
                    array($order_product['id'], $query, $item['id']));
                $this->all_configs['db']->query(
                    'UPDATE {orders_goods} SET item_id=?i, last_item_id=?i, unbind_request=null WHERE id=?i',
                    array($item['id'], $item['id'], $order_product['order_goods_id']));

                // обновляем местонахождение изделия
                $ar2 = $this->all_configs['db']->query(
                    'UPDATE {warehouses_goods_items} SET wh_id=?n, location_id=?n WHERE id=?i',
                    array($order_product['wh_id'], $order_product['location_id'], $item['id']))->ar();

                if ($ar1 || $ar2) {
                    if ($order_product['manager'] && $send == true) {
                        $href = $this->all_configs['prefix'] . 'orders/create/' . $order_product['id'];
                        $content = l('Запчасть только что была отгружена, под заказ').' <a href="' . $href . '">№' . $order_product['id'] . '</a>';
                        $this->notification('Запчасть отгружена под ремонт', $content, $order_product['manager']);
                    }

                    // уведомлять о каждой продаже этого товара
                    $users = $this->all_configs['db']->query(
                        'SELECT user_id FROM {users_notices} WHERE goods_id=?i AND each_sale=?i',
                        array($order_product['goods_id'], 1))->vars();
                    if ($users) {
                        $href = $this->all_configs['prefix'] . 'products/create/' . $order_product['goods_id'];
                        $content = 'Запчасть <a href="' . $href . '">№' . $order_product['title'] . '</a> только что была продана';
                        foreach ($users as $user) {
                            $this->notification(l('Продана запчасть'), $content, $user);
                        }
                    }

                    // добавляем комментарий
                    $text = l('Запчасть отгружена под ремонт');
                    $this->all_configs['suppliers_orders']->add_client_order_comment($order_product['id'], $text);

                    $this->all_configs['manageModel']->move_product_item(
                        $item['wh_id'],
                        $item['location_id'],
                        $order_product['goods_id'],
                        $item['id'],
                        null,//$order_product['id'],
                        null,
                        l('Перемещение на склад к заказу'),
                        null,
                        1
                    );
                    $this->all_configs['manageModel']->move_product_item(
                        $order_product['wh_id'],
                        $order_product['location_id'],
                        $order_product['goods_id'],
                        $item['id'],
                        $order_product['id'],
                        null,
                        l('Перемещен на склад к заказу'),
                        null,
                        2
                    );
                    // если заявка на другой заказ поставщику
                    if ($order_product['supplier_order_id'] != $item['supplier_order_id']) {
                        // обновляем заявку на другой заказ поставщику
                        $ar = $this->all_configs['db']->query(
                            'UPDATE {orders_suppliers_clients} SET supplier_order_id=?i WHERE order_goods_id=?i',
                            array($item['supplier_order_id'], $order_product['order_goods_id']))->ar();

                        if ($ar) {
                            // достаем заказ поставщику и количество заявок
                            $so = $this->all_configs['db']->query(
                                'SELECT COUNT(l.id) as count_ordered, IF(o.count_come>0, o.count_come, o.count) as count_free
                            FROM {contractors_suppliers_orders} as o
                            LEFT JOIN {orders_suppliers_clients} as l ON l.supplier_order_id=o.id
                            WHERE o.id=?i', array($item['supplier_order_id']))->row();

                            if ($so && $so['count_ordered'] > $so['count_free']) {
                                // обновляем заявку
                                $this->all_configs['db']->query('UPDATE {orders_suppliers_clients} as l
                                  SET l.supplier_order_id=?i WHERE l.order_goods_id=(SELECT g.id FROM {orders_goods} as g
                                  WHERE g.id=l.order_goods_id AND g.item_id IS NULL AND l.supplier_order_id=?i LIMIT ?i)',
                                    array($order_product['supplier_order_id'], $item['supplier_order_id'], 1));
                            }
                        }
                    }
                }

                $products = $this->all_configs['db']->query(
                    'SELECT count(id) as goods, count(item_id) as items FROM {orders_goods} WHERE order_id=?i AND type=0',
                    array($order_product['id']))->row();

                if ($products && $products['goods'] == $products['items']) {
                    update_order_status($order_product, $this->all_configs['configs']['order-status-work']);
                }

                // уведомление о продаже более одной запчасти под ремонт
                if ($products && $products['items'] > 1) {
                    $href = $this->all_configs['prefix'] . 'orders/create/' . $order_product['id'];
                    $content = l('Продажа более одной запчасти на ремонт').' <a href="' . $href . '">№' . $order_product['id'] . '</a>';
                    $this->notification(l('Продажа более одной запчасти на ремонт'), $content, 'site-administration');
                }

                $this->History->save('chain-body-update-serial', $mod_id, $item['id']);
            }

        } catch (ExceptionWithMsg $e) {
            $result = array(
                'state' => false,
                'message' => $e->getMessage(),
                'class' => ''
            );
        }

        return $result;
    }

    /**
     * @param      $data
     * @param      $mod_id
     * @param bool $send
     * @return array
     */
    function unbind_item_serial($data, $mod_id, $send = true)
    {
        $result = array('state' => true, 'message' => 'Серийник отвязан');

        $item_id = isset($data['item_id']) ? $data['item_id'] : null;

        $item = $this->all_configs['db']->query(
            'SELECT serial, id as item_id, goods_id, order_id, wh_id, location_id FROM {warehouses_goods_items} WHERE id=?i',
            array($item_id))->row();

        $product = $this->all_configs['db']->query(
            'SELECT unbind_request, order_id FROM {orders_goods} WHERE item_id=?i && unbind_request IS NOT NULL',
            array($item_id))->row();

        try {
            if (!$product) {
                throw  new ExceptionWithMsg(l('Заявка на отвязку этого серийника не найдена'));
            }

            if ($product && $item && $product['order_id'] != $item['order_id']) {
                throw  new ExceptionWithMsg(l('Заявка из другого заказа'));
            }

            // проверяем не привязан ли этот серийник в какуюто цепочку
            if (!$item) {
                throw  new ExceptionWithMsg(l('Серийник не найден'));
            }

            if (!isset($data['location']) || $data['location'] == 0) {
                throw  new ExceptionWithMsg(l('Укажите локацию'));
            }

            if (!isset($data['wh_id_destination']) || $data['wh_id_destination'] == 0) {
                $data['wh_id_destination'] = $this->all_configs['db']->query(
                    'SELECT wh_id FROM {warehouses_locations} WHERE id=?i', array($data['location']))->el();
            }

            if ($data['wh_id_destination'] == 0) {
                throw  new ExceptionWithMsg(l('Укажите склад'));
            }

            $this->all_configs['db']->query(
                'UPDATE {warehouses_goods_items} SET order_id=null, date_sold=null WHERE id=?i', array($item_id));
            $this->all_configs['db']->query('UPDATE {orders_goods} SET item_id=null WHERE item_id=?i', array($item_id));

            $this->move_item($item_id, null, $data['wh_id_destination'], $data['location'], $mod_id);

            // привяжите запчасть, потом поставьте статус "готов", потом "примите на доработку" 
            // отвяжите запчасть и вуаля- статус не изменился.  
            // По факту устройство ожидает отгрузки запчасти, а статус "принят на доработку"
            // меняем статус ожидает запчастей
            update_order_status(
                $this->all_configs['db']->query("SELECT id,phone,notify,status "
                    . "FROM {orders} WHERE id = ?i", array($item['order_id']), 'row')
                , $this->all_configs['configs']['order-status-waits']);


            // обновление свободных остатков товара
            $this->all_configs['manageModel']->update_product_free_qty($item['goods_id']);

        } catch (ExceptionWithMsg $e) {
            $result = array(
                'state' => false,
                'message' => $e->getMessage()
            );
        }

        return $result;
    }

    /**
     * @param      $type
     * @param      $filters
     * @param bool $only_count
     * @param null $goods
     * @return null
     */
    function get_operations($type, $filters, $only_count = false, $goods = null)
    {
        $count_on_page = count_on_page();

        // фильтры
        $filters_query = '';
        // по товары
        if (isset($filters['by_gid']) && $filters['by_gid'] > 0) {
            $filters_query = $this->all_configs['db']->makeQuery('?query AND g.goods_id=?i',
                array($filters_query, $filters['by_gid']));
        }
        // по серийнику
        if (isset($filters['serial']) && !empty($filters['serial'])) {
            $serial = suppliers_order_generate_serial($filters, false);
            $filters_query = $this->all_configs['db']->makeQuery('?query AND (g.item_id=?i OR g.last_item_id=?i)',
                array($filters_query, $serial, $serial));
        }
        // по фио
        if (isset($filters['c_id']) && $filters['c_id'] > 0) {
            $filters_query = $this->all_configs['db']->makeQuery('?query AND o.user_id=?i',
                array($filters_query, $filters['c_id']));
        }
        // по номеру заказа
        if (isset($filters['con']) && $filters['con'] > 0) {
            $filters_query = $this->all_configs['db']->makeQuery('?query AND o.id=?i',
                array($filters_query, $filters['con']));
        }
        // !Без изделий (в общем остатке)
        if (!isset($filters['noi']) && $type == 1) {
            if ($goods) {
                $filters_query = $this->all_configs['db']->makeQuery('?query AND g.goods_id IN (?li)',
                    array($filters_query, array_keys($goods)));
            } elseif ($only_count) {
                $so_goods = $this->stockman_operations_goods();
                $goods = $so_goods['goods'];
                if (!$goods) {
                    $goods = array(0);
                }
                $filters_query = $this->all_configs['db']->makeQuery('?query AND g.goods_id IN (?li)',
                    array($filters_query, array_keys($goods)));
            } else {
                return null;
            }
        }
        // открытый
        if ($type == 1) {
            $filters_query = $this->all_configs['db']->makeQuery('?query AND i.id IS NULL', array($filters_query));
        }
        if ($type == 4) {
            $filters_query = $this->all_configs['db']->makeQuery('?query AND i.id IS NOT NULL', array($filters_query));
        }
        $operations = null;
        $skip = (isset($filters['p']) && $filters['p'] > 0) ? ($count_on_page * ($filters['p'] - 1)) : 0;

        if ($only_count == true) {
            if ($type == 1) {
                $operations = $this->all_configs['db']->query('SELECT COUNT(DISTINCT g.order_id)
                    FROM {orders_suppliers_clients} as l, {orders} as o, {orders_goods} as g
                    LEFT JOIN {warehouses_goods_items} as i ON i.id=g.item_id
                    WHERE o.id=g.order_id AND l.order_goods_id=g.id ?query',
                    array($filters_query))->el();
            }
            if ($type == 4) {
                $operations = $this->all_configs['db']->query('SELECT COUNT(DISTINCT g.order_id)
                    FROM {orders} as o, {orders_goods} as g
                    LEFT JOIN {warehouses_goods_items} as i ON i.id=g.item_id
                    WHERE o.id=g.order_id AND g.unbind_request IS NOT NULL ?query',
                    array($filters_query))->el();
            }
        } else {
            if ($type == 1) {
                $operations = $this->all_configs['db']->query('SELECT g.title, g.order_id, g.goods_id, i.serial,
                      o.comment, o.fio, o.phone, g.item_id, g.id, g.last_item_id, l.date_add,
                      t.location, l.supplier_order_id, g.warehouse_type, wg.name, wg.color, wt.icon
                    FROM {orders_suppliers_clients} as l, {orders} as o
                    LEFT JOIN {orders_goods} as g ON o.id=g.order_id
                    LEFT JOIN {warehouses_goods_items} as i ON i.id=g.item_id
                    LEFT JOIN {warehouses_locations} as t ON t.id=i.location_id
                    LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                    LEFT JOIN {warehouses_groups} as wg ON wg.id=w.group_id
                    LEFT JOIN {warehouses_types} as wt ON wt.id=w.type_id
                    WHERE o.id=g.order_id AND l.order_goods_id=g.id ?query
                    ORDER BY IF(i.id IS NULL, 0, 1), l.date_add DESC LIMIT ?i, ?i',

                    array($filters_query, $skip, $count_on_page))->assoc();
            }
            if ($type == 4) {
                $operations = $this->all_configs['db']->query('SELECT g.title, g.order_id, g.goods_id, i.serial,
                      o.comment, o.fio, o.phone, g.item_id, g.id, g.last_item_id, g.unbind_request as date_add,
                      t.location, g.warehouse_type, wg.name, wg.color, wt.icon
                    FROM {orders} as o
                    LEFT JOIN {orders_goods} as g ON o.id=g.order_id
                    LEFT JOIN {warehouses_goods_items} as i ON i.id=g.last_item_id
                    LEFT JOIN {warehouses_locations} as t ON t.id=i.location_id
                    LEFT JOIN {warehouses} as w ON w.id=o.accept_wh_id
                    LEFT JOIN {warehouses_groups} as wg ON wg.id=w.group_id
                    LEFT JOIN {warehouses_types} as wt ON wt.id=w.type_id
                    WHERE o.id=g.order_id AND g.unbind_request IS NOT NULL ?query
                    ORDER BY IF(i.id IS NULL, 1, 0), g.unbind_request DESC LIMIT ?i, ?i',

                    array($filters_query, $skip, $count_on_page))->assoc();
            }
        }

        return $operations;
    }

    /**
     * @param null $goods_id
     * @return array
     */
    function stockman_operations_goods($goods_id = null)
    {
        $serials = array();
        $goods = array();
        $prod_query = '';
        if ($goods_id) {
            $prod_query = db()->makeQuery(" AND i.goods_id = ?i:g AND l.goods_id = ?i:g ", array('g' => $goods_id));
        }
        $data = $this->all_configs['db']->query(
            'SELECT i.id as item_id, i.order_id, i.serial, i.goods_id,
                   w.title as wh_title, t.location, i.wh_id, 
                   i.location_id, i.supplier_order_id
            FROM {warehouses_goods_items} as i, 
                 {warehouses} as w, 
                 {warehouses_locations} as t, 
                 {orders_suppliers_clients} as l
            WHERE w.id=i.wh_id AND w.consider_store=?i AND t.id=i.location_id AND l.goods_id=i.goods_id ?q ',
            array(1, $prod_query))->assoc();
        if ($data) {
            foreach ($data as $i) {
                if ($i['order_id'] == 0) {
                    $goods[$i['goods_id']] = 1 + (isset($supliers_orders[$i['goods_id']]) ? $supliers_orders[$i['goods_id']] : 0);
                }
                $serials[$i['goods_id']]['serials'][$i['item_id']] = $i;
                if (!isset($serials[$i['goods_id']]['count'][$i['wh_id']])) {
                    $serials[$i['goods_id']]['count'][$i['wh_id']]['title'] = $i['wh_title'];
                    $serials[$i['goods_id']]['count'][$i['wh_id']]['supplier_order_id'] = $i['supplier_order_id'];
                    $serials[$i['goods_id']]['count'][$i['wh_id']]['locations'] = array();
                }
                if (!isset($serials[$i['goods_id']]['count'][$i['wh_id']]['locations'][$i['location_id']])) {
                    $serials[$i['goods_id']]['count'][$i['wh_id']]['title'] = $i['wh_title'];
                    $serials[$i['goods_id']]['count'][$i['wh_id']]['locations'][$i['location_id']]['title'] = $i['location'];
                    $serials[$i['goods_id']]['count'][$i['wh_id']]['locations'][$i['location_id']]['items'] = array();
                }
                $serials[$i['goods_id']]['count'][$i['wh_id']]['locations'][$i['location_id']]['items'][$i['item_id']] = $i['serial'];
            }
        }
        return array(
            'goods' => $goods,
            'serials' => $serials
        );
    }

    /**
     * @param int    $type
     * @param string $hash
     * @return array
     */
    function show_stockman_operations($type = 1, $hash = '#orders-clients_bind')
    {
        $so_goods = $this->stockman_operations_goods();
        $goods = $so_goods['goods'];
        $serials = $so_goods['serials'];
        /*
         * $type = 1 привязка серийного номера
         * $type = 2 выдача изделия
         * $type = 3 принятие изделия
         * $type = 4 отвязка серийного номера
         * */
        $count_on_page = count_on_page();//20;
        $items = $this->get_operations($type, $_GET, false, $goods);
        $count = $this->get_operations($type, $_GET, true, $goods);

        if (!$this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders') &&
            !$this->all_configs['oRole']->hasPrivilege('logistics')
        ) {
            return false;
        }

        $this->view->load('LockButton');
        $filters = $this->view->renderFile('chains.class/show_stockman_operations_filters');

        return array(
            'html' => $this->view->renderFile('chains.class/show_stockman_operations', array(
                'items' => $items,
                'count_on_page' => $count_on_page,
                'count' => $count,
                'type' => $type,
                'serials' => $serials,
                'controller' => $this
            )),
            'menu' => $filters
        );
    }

    /**
     * @param      $item
     * @param      $type
     * @param      $serials
     * @param bool $compact
     * @param bool $isGroup
     * @return string
     */
    function show_stockman_operation($item, $type, $serials, $compact = false, $isGroup = false)
    {
        $global_class = null;

        if (!$compact) {
            $selected = $this->all_configs['db']->query(
                'SELECT COUNT(id) FROM {users_marked}
                      WHERE user_id = ?i AND type = ? AND object_id = ?i',
                array($_SESSION['id'], 'wso' . $type, $item['order_id']))->el();
            $selected_oi = $this->all_configs['db']->query(
                'SELECT COUNT(id) FROM {users_marked}
                      WHERE type = ? AND object_id = ?i',
                array('woi', $item['order_id']))->el();
            $state = 'Нет на складе';
            if (isset($serials[$item['goods_id']]) && isset($serials[$item['goods_id']]['count'])) {
                $state = '';
                foreach ($serials[$item['goods_id']]['count'] as $warehouse) {
                    $state .= htmlspecialchars($warehouse['title']);
                    foreach ($warehouse['locations'] as $location) {
                        $state .= ' - ' . htmlspecialchars($location['title']) . ' - ' . count($location['items']) . '<br />';
                    }
                }
            }
        }

        return $this->view->renderFile('chains.class/show_stockman_operation', array(
            'item' => $item,
            'type' => $type,
            'global_class' => $global_class,
            'compact' => $compact,
            'selected' => isset($selected) ? $selected : null,
            'selected_oi' => isset($selected_oi) ? $selected_oi : null,
            'state' => isset($state) ? $state : '',
            'controller' => $this,
            'serials' => $serials,
            'isGroup' => $isGroup
        ));
    }

    /**
     * used in view 'show_stockman_operation.php'
     *
     * @param $item
     * @param $type
     * @param $serials
     * @return int|mixed|string
     */
    public function select_bind_item_wh($item, $type, $serials)
    {
        if ($type == 4 || $item['item_id'] > 0) {
            return suppliers_order_generate_serial($item, true, true);
        }

        $result = array();
        $hasItems = false;
        if (isset($serials[$item['goods_id']]['serials']) && count($serials[$item['goods_id']]['serials']) > 0) {
            $hasItems = true;
            foreach ($serials[$item['goods_id']]['serials'] as $serial) {
                if ($serial['order_id'] > 0) {
                    continue;
                }
                if (isset($item['supplier_order_id']) && $serial['supplier_order_id'] == $item['supplier_order_id']) {
                    $result['current'][] = $serial;
                } else {
                    $result['another'][] = $serial;
                }
            }
        }

        return $this->view->renderFile('chains.class/select_bind_item_wh', array(
            'serials' => $result,
            'hasItems' => $hasItems,
            'data' => $item
        ));
    }

    /**
     * @param bool $logistic
     * @param null $wh_id
     * @param null $exclude
     * @param bool $chain
     * @param null $goods_id
     * @param null $h_id
     * @param bool $only_logistic
     * @return string
     */
    public function get_options_for_move_item_form(
        $logistic = false,
        $wh_id = null,
        $exclude = null,
        $chain = false,
        $goods_id = null,
        $h_id = null,
        $only_logistic = false
    ) {
        $q = $this->query_warehouses($goods_id);

        if ($chain) {
            $query = $q['query_for_create_chain_body_logistic'];
        } else {
            if ($logistic) {
                $query = $q['query_for_move_item_logistic'];
            } else {
                $query = $q['query_for_move_item'];
            }
            if ($only_logistic) {
                $query .= ' AND w.type=3';
            }
        }

        return $this->view->renderFile('chains.class/get_options_for_move_item_form', array(
            'warehouses' => $this->warehouses($query),
            'wh_id' => $wh_id,
            'exclude' => $exclude
        ));
    }

    /**
     * @param null $item_id
     * @param null $status
     * @return string
     */
    public function form_write_off_items($item_id = null, $status = null)
    {
        $out = '';

        if ($this->all_configs['configs']['erp-use'] == true && $this->all_configs['oRole']->hasPrivilege('write-off-items')) {
            $out = $this->view->renderFile('chains.class/form_write_off_items', array(
                'can' => $item_id > 0 ? $this->can_use_item($item_id) : true,
                'item_id' => $item_id
            ));
        }

        return $out;
    }

    /**
     * @param null $item_id
     * @param null $status
     * @return string
     */
    public function form_sold_items($item_id = null, $status = null)
    {
        return $this->view->renderFile('chains.class/form_sold_items', array(
            'db' => $this->all_configs['db'],
            'can' => $item_id > 0 ? $this->can_use_item($item_id) : true,
            'item_id' => $item_id
        ));
    }

    /**
     * @param      $post
     * @param      $mod_id
     * @return array
     * @internal param null $order_class
     * @internal param bool $appendToSuppliersOrder
     */
    public function remove_product_order($post, $mod_id)
    {
        $data = array('state' => true);
        try {
            $order_id = isset($post['order_id']) ? $post['order_id'] : ($this->all_configs['arrequest'][2] ? $this->all_configs['arrequest'][2] : 0);
            $product = null;

            $order = $this->Orders->getByPk($order_id);

            if (empty($order)) {
                throw new ExceptionWithMsg('Заказ не найден');
            }
            if (!$this->all_configs['oRole']->hasPrivilege('edit-clients-orders')
                && !$this->all_configs['oRole']->hasPrivilege('scanner-moves')
            ) {
                throw new ExceptionWithMsg('У Вас недостаточно прав');
            }
            if (in_array($order['status'], $this->all_configs['configs']['order-statuses-orders'])) {
                throw new ExceptionWithMsg(l('Вы не можете добавить или удалить запчасть/работу из закрытого заказа. Предварительно измение его статус.'));
            }
            if ((!isset($post['product_id']) || $post['product_id'] == 0)) {
                throw new ExceptionWithMsg('Выберите товар');
            }
            $product = $this->all_configs['db']->query(
                'SELECT g.id as goods_id, g.* FROM {goods} as g WHERE g.id=?i',
                array($post['product_id']))->row();
            if (!$product && !isset($post['remove'])) {
                throw new ExceptionWithMsg(l('Товар не активен.') . ' ' . l('Зайдите в товар и поставьте галочку "активность"'));
            }

            if ($product && $order) {
                if ($this->OrdersGoods->isHash($post['order_product_id'])) {
                    $products = $this->all_configs['manageModel']->order_goods($order_id, 0);
                    $ids = $this->OrdersGoods->getProductsIdsByHash($products, $post['order_product_id']);
                } else {
                    $ids = array(
                        $post['order_product_id']
                    );
                }
                foreach ($ids as $id) {
                    $post['order_product_id'] = $id;
                    $data = $this->removeSpareOrder($post, $order, $data, $mod_id);
                }
                // сумма товаров
                $this->Orders->setOrderSum($order, $mod_id);
                $data['product-total'] = $this->all_configs['db']->query(
                        'SELECT SUM(`count` * price) FROM {orders_goods} WHERE order_id=?i',
                        array($order_id))->el() / 100;
            }
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'msg' => $e->getMessage(),
                'state' => false
            );
        }

        return $data;
    }

    /**
     * @param      $post
     * @param      $mod_id
     * @param null $order_class
     * @param bool $appendToSuppliersOrder
     * @return array
     */
    public function add_product_order($post, $mod_id, $order_class = null, $appendToSuppliersOrder = false)
    {
        $data = array('state' => true);
        try {
            $order_id = isset($post['order_id']) ? $post['order_id'] : ($this->all_configs['arrequest'][2] ? $this->all_configs['arrequest'][2] : 0);
            $product = null;

            $order = $this->Orders->getByPk($order_id);

            if (empty($order)) {
                throw new ExceptionWithMsg('Заказ не найден');
            }
            if (!$this->all_configs['oRole']->hasPrivilege('edit-clients-orders')
                && !$this->all_configs['oRole']->hasPrivilege('scanner-moves')
            ) {
                throw new ExceptionWithMsg('У Вас недостаточно прав');
            }
            if (in_array($order['status'], $this->all_configs['configs']['order-statuses-orders'])) {
                throw new ExceptionWithMsg(l('Вы не можете добавить или удалить запчасть/работу из закрытого заказа. Предварительно измение его статус.'));
            }
            if ((!isset($post['product_id']) || $post['product_id'] == 0)) {
                throw new ExceptionWithMsg('Выберите товар');
            }
            $product = $this->all_configs['db']->query(
                'SELECT g.id as goods_id, g.* FROM {goods} as g WHERE g.id=?i AND g.avail=?i',
                array($post['product_id'], 1))->row();
            if (!$product && !isset($post['remove'])) {
                throw new ExceptionWithMsg(l('Товар не активен.') . ' ' . l('Зайдите в товар и поставьте галочку "активность"'));
            }
            if (!isset($post['confirm']) && $product['type'] == 0 && $product['qty_store'] == 0
                && $product['foreign_warehouse'] != 1
            ) {
                $qty = $this->all_configs['db']->query('SELECT SUM(IF(o.warehouse_type=1, 1, 0)) as qty_1,
                    SUM(IF(o.warehouse_type=2, 1, 0)) as qty_2 FROM {contractors_suppliers_orders} as o
                WHERE o.count_debit=0 AND o.goods_id=?i AND (o.supplier IS NULL OR
                (SELECT COUNT(id) FROM {orders_suppliers_clients} as l WHERE l.supplier_order_id=o.id) < IF(o.count_come>0, o.count_come, o.count))',
                    array($product['goods_id']))->row();
                $data['confirm']['content'] = l('Товара нет в наличии, подтвердить?') . ' ' . InfoPopover::getInstance()->createQuestion('l_part_for_order_not_in_stock_info');
                $data['confirm']['btns'] = $this->view->renderFile('chains.class/add_product_order_confirm', array(
                    'qty' => $qty,
                    'product' => $product
                ));
                $data['state'] = false;
                return $data;
            }

            if ($product && $order) {
                if (isset($post['remove'])) {
                    $data = $this->removeSpareOrder($post, $order, $data, $mod_id);
                } else {
                    $data = $this->addSpareToOrder($post, $product, $order_id, $data);

                    if ($data['id'] > 0) {
                        $wh_type = isset($post['confirm']) ? intval($post['confirm']) : 0;
                        // делаем сразу заказ поставщику (если товара нету на складе)
                        if ($wh_type) {
                            $dt = array(
                                'order_id' => $order_id,
                                'order_product_id' => $data['id'],
                                'append' => $appendToSuppliersOrder
                            );
                            $create_supplier_order = $this->order_item($this->all_configs['configs']['orders-manage-page'],
                                $dt);
                            if (!$create_supplier_order['state']) {
                                throw new ExceptionWithMsg($create_supplier_order['msg']);
                            }
                        }
                        // достаем товар в корзине
                        $product = $this->all_configs['manageModel']->order_goods($order['id'], $product['type'],
                            $data['id']);

                        $this->History->save(
                            'update-order-cart',
                            $mod_id,
                            $order['id'],
                            l('Добавлен') . ' ' . $product['title']
                        );
                        if ($product && $order_class) {
                            // выводим
                            $data[($product['type'] == 0 ? 'goods' : 'service')] = $order_class->show_product($product);
                            $data['reload'] = 1;
                        }
                    }
                }
                // сумма товаров
                $this->Orders->setOrderSum($order, $mod_id);
                $data['product-total'] = $this->all_configs['db']->query(
                        'SELECT SUM(`count` * price) FROM {orders_goods} WHERE order_id=?i',
                        array($order_id))->el() / 100;
            }
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'msg' => $e->getMessage(),
                'state' => false
            );
        }

        return $data;
    }

    /**
     * @param      $post
     * @param      $mod_id
     * @param bool $send
     * @return array
     * @throws Exception
     */
    public function add_order($post, $mod_id, $send = true)
    {
        $sum_paid = isset($post['sum_paid']) ? intval($post['sum_paid'] * 100) : 0;
        $approximate_cost = isset($post['approximate_cost']) ? intval($post['approximate_cost'] * 100) : 0;
        $note = isset($post['serials']) ? trim($post['serials']) : '';
        $repair = isset($post['repair']) ? intval($post['repair']) : 0;
        $status = $repair == 2 ? $this->all_configs['configs']['order-status-rework'] : $this->all_configs['configs']['order-status-new'];
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $data = array('state' => true, 'msg' => '', 'id' => null);
        $crm_request = !empty($post['crm_request']) ? $post['crm_request'] : null;
        $repair_part = !empty($post['repair_part']) ? trim($post['repair_part']) : '';
        $repair_part_quality = !empty($post['repair_part_quality']) ? $post['repair_part_quality'] : lq('Не согласовано');
        $warranty = (isset($post['warranty']) && intval($post['warranty']))
            ? intval($post['warranty'])
            : (isset($this->all_configs['settings']['default_order_warranty']) ? $this->all_configs['settings']['default_order_warranty'] : 0);

        $next = isset($post['next']) ? trim($post['next']) : '';

        $part_quality_comment = '';
        if ($repair_part) {
            $part_quality_comment .= lq('Замена') . ' ' . htmlspecialchars($repair_part) . '. ';
            $part_quality_comment .= lq('Качество') . ' ' . htmlspecialchars($repair_part_quality) . '. ';
        }

        try {
            $client = $this->Clients->getClient($post);

            // достаем категорию
            $category = $this->all_configs['db']->query('SELECT * FROM {categories} WHERE id=?i',
                array(isset($post['categories-last']) ? intval($post['categories-last']) : 0))->row();

            // склад менеджер
            $wh = $this->all_configs['db']->query(
                'SELECT wh_id, location_id FROM {warehouses_users} WHERE user_id=?i AND main=?i',
                array($user_id, 1))->row();

            if (empty($wh)) {
                throw new ExceptionWithMsg(l('Вы не закреплены ни за одним складом') . "\n\n"
                    . l('В разделе Склады, Настройки, Администраторы укажите склад и локацию по умолчанию для сотрудника'));
            }
            $order = null;
            // доработка
            if ($repair == 2) {
                if (isset($post['serial-id']) && intval($post['serial-id']) > 0) {
                    $order = $this->all_configs['db']->query('SELECT * FROM {orders} WHERE id=?i',
                        array(intval($post['serial-id'])))->row();

                    if ($order) {
                        update_order_status($order, $this->all_configs['configs']['order-status-rework']);
                    }
                }
                if ((!isset($post['serial']) || mb_strlen(trim($post['serial']), 'UTF-8') == 0)) {
                    throw new ExceptionWithMsg(l('Укажите серийный номер'));
                }
                if (!$order) {
                    $order = $this->all_configs['db']->query('SELECT * FROM {orders} WHERE serial=?',
                        array(trim($post['serial'])))->row();

                    if ($order) {
                        update_order_status($order, $this->all_configs['configs']['order-status-rework']);
                    } else {
                        $data['state'] = false;
                        $data['msg'] = '<p>' . l('Не найдено совпадений, укажите номер ремонта, по которому принимается доработка') . '</p>';
                        $data['msg'] .= '<p><input type="text" id="serial-order_id" value="" placeholder="' . l('Номер заказа на ремонт') . '" /></p>';
                        $onclick = '$(this).button(\'loading\');$(\'input#serial-id\').val($(\'input#serial-order_id\').val());$(\'input#add-client-order\').click();';
                        $data['btn'] = '<input onclick="' . $onclick . '" value="' . l('Сохранить') . '" type="button" class="btn" />';
                        $data['prompt'] = true;
                        return $data;
                    }
                }
                if (empty($order)) {
                    throw new ExceptionWithMsg(l('Заказ не найден'));
                }
                $data['location'] = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $order['id'];
            }

            if (!$category) {
                throw new ExceptionWithMsg(l('Выберите устройство'));
            }
            if (isset($post['is_replacement_fund']) && (!isset($post['replacement_fund']) || mb_strlen(trim($post['replacement_fund']),
                        'utf-8') == 0)
            ) {
                throw new ExceptionWithMsg(l('Укажите подменный фонд'));
            }
            if ($category['id'] == $this->all_configs['configs']['erp-co-category-return'] && !isset($post['returnings'])) {
                // возврат поставщику
                $post = array(
                    'clients' => $client['id'],
                    'items' => suppliers_order_generate_serial(array('serial' => $note), false),
                    'returnings' => true,
                );
                return $this->return_items($post, $mod_id);
            }
            if ((!isset($client) || !$client) && !$order) {
                throw new ExceptionWithMsg(l('Выберите клиента'));
            }
            $serial = null;
            if ($category['id'] == $this->all_configs['configs']['erp-co-category-sold'] && !isset($post['soldings'])) {
                // продажа
                $post = array(
                    'price' => $sum_paid / 100,
                    'clients' => $client['id'],
                    'items' => suppliers_order_generate_serial(array('serial' => $note), false),
                    'soldings' => true,
                    'warranty' => $warranty,
                );
                return $this->sold_items($post, $mod_id);
            }
            if ($category['id'] == $this->all_configs['configs']['erp-co-category-write-off'] && !isset($post['writeoffings'])) {
                // списание
                $post = array(
                    'clients' => $client['id'],
                    'items' => suppliers_order_generate_serial(array('serial' => $note), false),
                    'writeoffings' => true,
                );
                return $this->write_off_items($post, $mod_id);
            }

            if (isset($post['is_courier']) && (!isset($post['courier']) || mb_strlen($post['courier'], 'UTF-8') == 0)) {
                throw new ExceptionWithMsg(l('Введите адрес где курьер забрал устройство'));
            }

            if ($category && $client && $wh && !$order) {

                if (!$client['fio'] && !empty($post['client_fio'])) {
                    $this->all_configs['db']->query("UPDATE {clients} SET fio = ? WHERE id = ?i",
                        array($post['client_fio'], $client['id']));
                    $client['fio'] = $post['client_fio'];
                }

                if (!isset($post['id']) || intval($post['id']) == 0) {
                    $order_first_num = (isset($this->all_configs['settings']['order-first-number'])
                        && is_numeric($this->all_configs['settings']['order-first-number']))
                        ? intval($this->all_configs['settings']['order-first-number'])
                        : 0;
                    $post['id'] = $this->all_configs['db']->query('SELECT o.id+1
                    FROM (SELECT ?i as id UNION SELECT id FROM {orders} WHERE id > ?i) o
                    WHERE NOT EXISTS (SELECT 1 FROM {orders} su WHERE su.id=o.id+1) ORDER BY o.id LIMIT 1',
                        array($order_first_num, $order_first_num))->el();
                }

                $post['warranty'] = $warranty;
                $this->createNewOrder($post, $client, $category, $wh, $part_quality_comment);
                $data['id'] = $post['id'];

                if ($data['id'] > 0) {
                    $data = $this->updateOrderInfo(
                        $post,
                        $category,
                        $client,
                        $wh,
                        $data,
                        max($sum_paid, $approximate_cost),
                        $sum_paid,
                        $status,
                        $next,
                        $mod_id,
                        $send,
                        $part_quality_comment,
                        $crm_request
                    );
                }
            }
            $data['location'] = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $data['id'];

        } catch (ExceptionWithMsg $e) {
            $data = array(
                'msg' => $e->getMessage(),
                'state' => false
            );
        }
        return $data;
    }

    /**
     * @param      $items
     * @param null $order_id
     * @return bool
     */
    function can_use_item($items, $order_id = null)
    {
        $id = null;
        $query = '';

        if ($order_id > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND i.order_id<>?i', array($query, $order_id));
        }

        $items = (array)$items;
        if (count($items) > 0) {
            $id = $this->all_configs['db']->query('SELECT order_id FROM {warehouses_goods_items} as i WHERE i.id IN (?li) ?query',
                array($items, $query))->el();
        }

        return $id <= 0;
    }

    /**
     * @param $post
     * @param $mod_id
     * @return array
     */
    public function return_items($post, $mod_id)
    {
        $user_id = $this->getUserId();
        $data = array('state' => true);
        // изделия
        $items = isset($post['items']) && count(array_filter(explode(',',
            $post['items']))) > 0 ? array_filter(explode(',', $post['items'])) : null;
        if ($items) {
            $items = $this->all_configs['db']->query('SELECT i.wh_id, i.goods_id, i.id, cl.id as user_id, m.user_id as manager_id,
                      cl.contractor_id, ct.title as contractor_title, (i.price / 100) as price
                    FROM {warehouses} as w, {warehouses_goods_items} as i
                    LEFT JOIN {users_goods_manager} as m ON m.goods_id=i.goods_id
                    LEFT JOIN {clients} as cl ON cl.contractor_id=i.supplier_id
                    LEFT JOIN {contractors} as ct ON ct.id=i.supplier_id
                    WHERE i.id IN (?li) AND w.id=i.wh_id AND i.order_id IS NULL GROUP BY i.id',
                array($items))->assoc();
        }
        try {
            // права
            if (!$this->all_configs['oRole']->hasPrivilege('return-items-suppliers')) {
                throw new ExceptionWithMsg(l('У Вас нет прав'));
            }
            // изделий не найдено
            if (!$items) {
                throw new ExceptionWithMsg(l('Свободные изделия для возврата не найдены или они находятся не в общем остатке (на складе у которого не включена опция учета в свободном остатке)'));
            }

            if ($data['state'] == true) {
                foreach ($items as $k => $item) {
                    // нет менеджера
                    if ($item['manager_id'] == 0) {
                        throw new ExceptionWithURL($this->all_configs['prefix'] . "products/create/" . $item['goods_id'] . "?error=manager#managers");
                    }
                    // нет поставщика
                    if ($item['contractor_id'] == 0) {
                        throw new ExceptionWithMsg(l('Привяжите к клиенту контрагента "' . htmlspecialchars($item['contractor_title']) . '"'));
                    }
                }
            }
            $course_value = getCourse($this->all_configs['settings']['currency_suppliers_orders']);
            foreach ($items as $item) {
                // создаем заказ
                $arr = array(
                    'clients' => $item['user_id'],
                    'type' => 1,
                    'categories-last' => $this->all_configs['configs']['erp-co-category-return'],
                    'sum_paid' => ($course_value * $item['price']) / 100,
                    'returnings' => true,
                    'manager' => $user_id,
                );
                $order = $this->add_order($arr, $mod_id, false);

                // ошибка при создании заказа
                if (!isset($order['id']) || $order['id'] == 0) {
                    throw new ExceptionWithMsg($order && array_key_exists('msg',
                        $order) ? $order['msg'] : l('Заказ не создан'));
                }

                $_item = $item;
                $_item['price'] = 0;
                $this->addSpares(array($_item), $order['id'], $mod_id);
                // оплата
                $tr_data = array(
                    'transaction_type' => TRANSACTION_INPUT, // внесение
                    'cashbox_from' => $this->all_configs['configs']['erp-cashbox-transaction'],
                    'cashbox_to' => $this->all_configs['configs']['erp-cashbox-transaction'],
                    'amount_from' => 0,

                    // Первоначальный вариант:
                    // 'amount_to' => ($course_value * $item['price']) / 100,
                    //
                    // В реализации метода create_transaction это значение сравнивается с суммами, сохраненными в
                    // заказе в текущем методе выше
                    //
                    // if ( ... round((float)$post['amount_to'] * 100) > $order['sum'] - $order['sum_paid']) {
                    //
                    // Сумма заказа ($order['sum']) формируются не арифметическим округлением, а отбрасыванием
                    // незначащих знаков (3+ после нуля) в методе  $this->add_product_order, а amount_to может
                    // содержать более 2 знаков после запятой
                    // Учитывая то, что методы этого класса add_product_order , create_transaction
                    // используются (вызываются) из других методов (не только возврат товара), целесообразно сначала
                    // определиться с правильностью выбора и применения методики округлений, поэтому значение
                    // параметра amount_to приведено в соответствие со значениями сумм, сохраненных в заказе,
                    // соответствующем этому возврату (2 знака после запятой с простым отбрасыванием
                    // оставшихся знаков)
                    'amount_to' => (floor($course_value * $item['price']) * 100) / 10000,

                    'cashbox_currencies_from' => $this->all_configs['suppliers_orders']->currency_clients_orders,
                    'cashbox_currencies_to' => $this->all_configs['suppliers_orders']->currency_clients_orders,
                    'client_order_id' => $order['id'],
                    //'b_id' => $chain_body_a['b_id'],
                    'client_contractor' => 1,
                    'date_transaction' => date("Y-m-d H:i:s"),
                    'type' => 3,
                );
                if (isset($post['confirm'])) {
                    $tr_data['confirm'] = $post['confirm'];
                }
                $transaction = $this->create_transaction($tr_data, $mod_id);
                // ошибка при создании транзакции
                if (!$transaction && !isset($transaction['state']) || $transaction['state'] == false) {
                    $exception = new ExceptionWithMsg($transaction && array_key_exists('msg',
                        $transaction) ? $transaction['msg'] : l('Транзакция не создана'));
                    if (isset($transaction['confirm'])) {
                        $exception->confirm = $transaction['confirm'];
                    }
                    throw $exception;
                }
            }
            // статус выдан
            $status = update_order_status(array(
                'id' => $order['id'],
                'status' => $this->all_configs['configs']['order-status-new']
            ), $this->all_configs['configs']['order-status-issued']);
            if (!$status || !isset($status['closed']) || $status['closed'] == false) {
                throw new ExceptionWithMsg($status && array_key_exists('msg',
                    $status) ? $status['msg'] : l('Заказ не закрыт'));
            }
            $data['location'] = $this->all_configs['prefix'] . 'orders/create/' . $order['id'];
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'message' => $e->getMessage()
            );
            if (isset($e->confirm)) {
                $data['confirm'] = $e->confirm;
            }
            if (isset($order)) {
                $this->Orders->rollback($order);
            }
        } catch (ExceptionWithURL $e) {
            $data = array(
                'state' => false,
                'location' => $e->getMessage()
            );
            if (isset($order)) {
                $this->Orders->rollback($order);
            }
        }


        return $data;
    }

    /**
     * заполняем цены товара в соответствии с данными запроса
     *
     * @param $items
     * @param $itemIds
     * @param $post
     * @return array
     */
    protected function prepareEshopSoldItems($items, $itemIds, $post)
    {
        $result = array();
        if (!empty($items)) {
            $ids = array_flip($itemIds);
            foreach ($items as $item) {
                $result[] = array_merge($item, array(
                    'price' => $post['amount'][$ids[$item['id']]],
                    'warranty' => $post['warranty'][$ids[$item['id']]],
                    'discount' => $post['discount'][$ids[$item['id']]],
                    'discount_type' => $post['discount_type'][$ids[$item['id']]],
                ));
            }
        }
        return $result;
    }


    /**
     * @param $post
     * @param $mod_id
     * @return array
     */
    public function eshop_sold_items($post, $mod_id)
    {
        require_once __DIR__ . '/Models/OrderEshopSale.php';
        $OrderModel = new MOrderEshopSale();

        try {
            $post['total_as_sum'] = 1;
            $post['sale_type'] = SALE_TYPE_ESHOP;
            $post['prices'] = $post['sum'];
            $post['price'] = $this->priceCalculate($post);
            $cart = $this->prepareCartInfo($post);
            if (empty($cart)) {
                throw new ExceptionWithMsg(l('Вы не добавили изделие в корзину. Нажмите "+" или "Добавить"'));
            }
            if (empty($post['amount']) || ($post['price'] == 0)) {
                throw new ExceptionWithMsg(l('Вы не добавили изделие в корзину. Нажмите "+" или "Добавить"'));
            }
            $client = $this->Clients->getClient($post);
            $order = $this->createOrder($post, $mod_id, $client['id'], $this->getUserId());

            $items = array();
            if (method_exists($OrderModel, 'getAvailableItems')) {
                $items = $this->prepareEshopSoldItems($OrderModel->getAvailableItems(array_values($post['item_ids'])),
                    $post['item_ids'],
                    $post['amount']);
            }

            $setStatus = $this->all_configs['configs']['order-status-new'];

            if (!empty($items)) {
                foreach ($items as $item) {
                    if (isset($cart[$item['id']]['quantity'])) {
                        $this->addSpares(array($item), $order['id'], $mod_id);
                        $cart[$item['id']]['quantity'] -= 1;
                        if ($cart[$item['id']]['quantity'] == 0) {
                            unset($cart[$item['id']]);
                        }
                    }
                }
            }
            if (!empty($cart)) {
                $this->addProducts($cart, $order['id'], $mod_id);
            }

            $this->changeOrderStatus($order, $setStatus, $post);

            $data = array(
                'state' => true,
                'location' => $this->all_configs['prefix'] . 'orders/create/' . $order['id'],
                'id' => $order['id']
            );
            if (isset($post['next'])) {
                $data = $this->andPrint($post['next'], $data, $client);
            }
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'message' => $e->getMessage(),
                'msg' => $e->getMessage(),
            );
            if (isset($order)) {
                $this->Orders->rollback($order);
            }
        } catch (ExceptionWithURL $e) {
            $data = array(
                'state' => false,
                'location' => $e->getMessage(),
            );
            if (isset($order)) {
                $this->Orders->rollback($order);
            }
        }

        return $data;
    }

    /**
     * @param $post
     * @param $mod_id
     * @return array
     */
    public function quick_sold_items($post, $mod_id)
    {
        $post['client_id'] = $this->all_configs['db']->query('SELECT id FROM {clients} WHERE phone="000000000002" LIMIT 1')->el();
        if (empty($post['client_id'])) {
            $post['client_id'] = $this->all_configs['db']->query('SELECT id FROM {clients} WHERE phone="000000000000" LIMIT 1')->el();
        }
        $post['clients'] = $post['client_id'];
        $post['manager'] = $this->getUserId();
        $post['sale_type'] = SALE_TYPE_QUICK;
        $post['total_as_sum'] = 1;

        return $this->sold_items($post, $mod_id);
    }

    /**
     * заполняем цены товара в соответствии с данными запроса
     *
     * @param $items
     * @param $itemIds
     * @param $post
     * @return array
     */
    protected function prepareQuickSoldItems($items, $itemIds, $post)
    {
        $result = array();
        if (!empty($items)) {
            $ids = array_flip($itemIds);
            foreach ($items as $item) {
                $result[] = array_merge($item, array(
                    'price' => $post['amount'][$ids[$item['id']]],
                    'warranty' => $post['warranty'][$ids[$item['id']]],
                    'discount' => $post['discount'][$ids[$item['id']]],
                    'discount_type' => $post['discount_type'][$ids[$item['id']]],
                ));
            }
        }
        return $result;
    }

    /**
     * @param $post
     * @return mixed
     */
    protected function priceCalculate($post)
    {
        if (empty($post['prices'])) {
            return 0;
        }
        $result = 0;
        foreach ($post['prices'] as $id => $value) {
            $quantity = isset($price['quantity'][$id]) ? $price['quantity'][$id] : 1;
            if ($post['discount_type'][$id] == 1) {
                $price = $value * (1 - $post['discount'][$id] / 100);
            } else {
                $price = $value - $post['discount'][$id];
            }
            $result += $price * $quantity;
        }
        return $result;
    }

    /**
     * @param $post
     * @param $mod_id
     * @return array
     */
    public function sold_items($post, $mod_id)
    {
        try {
            $post['prices'] = $post['amount'];
            $post['price'] = $this->priceCalculate($post);
            if (empty($post['amount']) || ($post['price'] == 0)) {
                throw new ExceptionWithMsg(l('Вы не добавили изделие в корзину'));
            }
            if (isset($post['auto-cash']) && $post['auto-cash'] == 'on' && empty($post['cashbox'])) {
                throw new ExceptionWithMsg(l('Выберите кассу, в которую вносить оплату'));
            }
            $client = $this->Clients->getClient($post);
            $order = $this->createOrder($post, $mod_id, $client['id'], $this->getUserId());

            $items = $this->prepareQuickSoldItems($this->Orders->getAvailableItems(array_values($post['item_ids'])),
                $post['item_ids'],
                $post);

            if (!empty($items)) {
                $this->addSpares($items, $order['id'], $mod_id);
            }
            $setStatus = isset($post['set-order-status']) ? intval($post['set-order-status']) : $this->all_configs['configs']['order-status-issued'];
            $this->changeOrderStatus($order, $setStatus, $post);

            $data = array(
                'state' => true,
                'location' => $this->all_configs['prefix'] . 'orders/create/' . $order['id'],
                'id' => $order['id']
            );
            if (isset($post['next'])) {
                $data = $this->andPrint($post['next'], $data, $client);
            }

            if (isset($post['auto-cash']) && $post['auto-cash'] == 'on' && !empty($post['cashbox'])) {
                $this->create_transaction(Array
                (
                    'transaction_type' => TRANSACTION_INPUT,
                    'supplier_order_id' => 0,
                    'client_order_id' => $order['id'],
                    'b_id' => 0,
                    'transaction_extra' => 0,
                    'cashbox_from' => $post['cashbox'],
                    'amount_from' => 0,
                    'cashbox_currencies_from' => $this->all_configs['settings']['currency_orders'],
                    'cashbox_course_from' => 1,
                    'cashbox_to' => $post['cashbox'],
                    'amount_to' => $post['price'],
                    'cashbox_currencies_to' => $this->all_configs['settings']['currency_orders'],
                    'cashbox_course_to' => 1,
                ), $mod_id);
            }
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'message' => $e->getMessage(),
                'msg' => $e->getMessage(),
            );
            // чистим если что-то произошло не так
            if (isset($order)) {
                $this->Orders->rollback($order);
            }
        } catch (ExceptionWithURL $e) {
            $data = array(
                'state' => false,
                'location' => $e->getMessage(),
            );
            // чистим если что-то произошло не так
            if (isset($order)) {
                $this->Orders->rollback($order);
            }
        }
        return $data;
    }

    /**
     * @param      $mod_id
     * @param      $post
     * @param bool $send_stockman
     * @return array
     */
    public function order_item($mod_id, $post, $send_stockman = true)
    {
        $data = array('state' => true);
        $order_id = isset($post['order_id']) ? $post['order_id'] : 0;
        $order_product_id = isset($post['order_product_id']) ? $post['order_product_id'] : 0;
        try {
            // достаем заказ
            $order = $this->Orders->getByPk($order_id);
            $product = $this->all_configs['manageModel']->order_goods($order_id, null, $order_product_id);

            if (!$order) {
                throw new ExceptionWithMsg('Заказ не найден');
            }
            if (!$product) {
                throw new ExceptionWithMsg('Запчасть не найдена');
            }
            if ($product['type'] == 1) {
                throw new ExceptionWithMsg('Это услуга');
            }

            if ($product && $order) {
                if ($product['so_id'] <= 0) {
                    // по конкретному заказу поставщика
                    $query = isset($post['supplier_order_id']) ? $this->all_configs['db']->makeQuery('AND o.id=?i',
                        array(intval($post['supplier_order_id']))) : '';

                    // ищем заказ со свободным изделием
                    $free_order = $this->all_configs['db']->query('SELECT o.*, COUNT(DISTINCT i.id) -
                      (SELECT COUNT(l.id) FROM {orders_suppliers_clients} as l WHERE i.supplier_order_id=l.supplier_order_id
                        AND l.order_goods_id IN (SELECT id FROM {orders_goods} WHERE item_id IS NULL)) as free_items
                    FROM {warehouses} as w, {warehouses_goods_items} as i, {contractors_suppliers_orders} as o
                    WHERE w.consider_store=1 AND i.wh_id=w.id AND i.order_id IS NULL AND i.goods_id=?i AND
                      o.id=i.supplier_order_id ?query
                    GROUP BY i.supplier_order_id ORDER BY free_items DESC, i.date_add LIMIT 1',
                        array($product['goods_id'], $query))->row();

                    if (!$free_order || $free_order['free_items'] == 0 || $free_order['id'] == 0) {
                        // ищем заказ со свободным местом для заявки
                        $free_order = $this->all_configs['db']->query('SELECT o.*, convert(IF(o.count_come>0, o.count_come, o.count), signed integer )-
                          (SELECT COUNT(l.id) FROM {orders_suppliers_clients} as l WHERE o.id=l.supplier_order_id
                            AND l.order_goods_id IN (SELECT id FROM {orders_goods} WHERE item_id IS NULL)) as free_items
                        FROM {contractors_suppliers_orders} as o
                        WHERE o.goods_id=?i AND unavailable=0 AND avail=1 AND o.count_debit=0 AND o.warehouse_type=?i ?query
                        GROUP BY o.id HAVING free_items>0 OR o.supplier IS NULL
                        ORDER BY o.count_debit DESC, o.date_wait, free_items DESC LIMIT 1',
                            array($product['goods_id'], $product['warehouse_type'], $query))->row();
                    }

                    if (isset($post['append']) && $post['append'] && (!$free_order || $free_order['id'] == 0)) {
                        // ищем заказ со для текущего ордера
                        $free_order = $this->all_configs['db']->query('SELECT o.*, -1 as free_items
                        FROM {contractors_suppliers_orders} as o
                        WHERE o.goods_id=?i AND unavailable=0 AND avail=1 AND o.count_debit=0 AND o.warehouse_type=?i
                         AND o.id IN (SELECT supplier_order_id FROM {orders_suppliers_clients} WHERE client_order_id=?i)
                        GROUP BY o.id 
                        ORDER BY o.count_debit DESC, o.date_wait DESC LIMIT 1',
                            array($product['goods_id'], $product['warehouse_type'], $order_id))->row();
                    }

                    if ($free_order && $free_order['id'] > 0) {
                        $data['order_id'] = $free_order['id'];
                        // увеличиваем количество в заказе
                        if ($free_order['supplier'] == 0 && $free_order['free_items'] < 1) {
                            $this->all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET count=1+count WHERE id=?i',
                                array($free_order['id']));
                        }
                        // связка заказов
                        $id = $this->OrdersSuppliersClients->insert(array(
                            'client_order_id' => $order_id,
                            'supplier_order_id' => $free_order['id'],
                            'goods_id' => $product['goods_id'],
                            'order_goods_id' => $product['id']
                        ));

                        // публичное сообщение
                        if ($id) {
                            if ($free_order['supplier'] > 0) {
                                if ($free_order['count_debit'] > 0) {
                                    $text = lq('Ожидание отгрузки запчасти');//'Запчасть была оприходована';
                                } elseif ($free_order['count_come'] > 0) {
                                    $text = lq('Запчасть была принята');
                                } else {
                                    $text = lq('Запчасть заказана');
                                }
                            } else {
                                $text = lq('Отправлен запрос на покупку. Ожидаем ответ.');
                            }
                            if ($send_stockman == true) {
                                // добавляем комментарий
                                $this->all_configs['suppliers_orders']->add_client_order_comment(intval($order_id),
                                    $text);
                                // отправляем уведомление кладовщику
                                $href = $this->all_configs['prefix'] . 'warehouses?con=' . intval($order_id) . '#orders-clients_bind';
                                $content = lq('При наличии запчасти на складе, отгрузите ее под заказ').' <a href="' . $href . '">№' . intval($order_id) . '</a>';
                                $this->notification(lq('Отгрузите запчасть под заказ'), $content,
                                    'mess-debit-clients-orders');
                            }
                        }
                    } else {
                        // создаем заказ поставщику
                        $arr = array(
                            'item_ids' => array(
                                'client' => $product['goods_id']
                            ),
                            'so_co' => array(
                                'client' => $order_id
                            ),
                            'comment-supplier' => $product['warehouse_type'] == 1 ? lq('Локально') : ($product['warehouse_type'] == 2 ? lq('Заграница') : ''),
                            'warehouse_type' => $product['warehouse_type'],
                            'warehouse-order-count' => isset($post['count']) ? $post['count'] : 1,
                            'from_client_order' => true,
                            'quantity' => array(
                                'client' => 1
                            ),
                            'amount' => array(
                                'client' => 0
                            )
                        );
                        $data = $this->all_configs['suppliers_orders']->create_order($mod_id, $arr);
                        if ($data['id'] > 0) {
                            $data['order_id'] = $data['id'];
                            // отправляем уведомление
                            $content = lq('Необходимо завершить закупку запчасти').' ';
                            $content .= '<a href="' . $this->all_configs['prefix'] . 'orders/edit/' . $data['id'] . '#create_supplier_order">№' . $data['id'] . '</a>';
                            $content .= ' '.lq('под ремонт').' №' . $order_id;
                            $this->notification(lq('Закупка запчасти'), $content, 'edit-suppliers-orders');
                        }
                    }

                    // меняем статус ожидает запчастей
                    update_order_status($order, $this->all_configs['configs']['order-status-waits']);
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
     * @param $mod_id
     * @param $post
     * @return array
     */
    public function unbind_request($mod_id, $post)
    {
        $data = array('state' => true);
        $item_id = isset($post['item_id']) ? $post['item_id'] : null;

        // достаем издели
        $item = $this->all_configs['db']->query(
            'SELECT serial, id as item_id FROM {warehouses_goods_items} WHERE id=?i', array($item_id))->row();
        // достаем товар с заказа
        $product = $this->all_configs['db']->query(
            'SELECT g.unbind_request, g.id, g.order_id, o.status FROM {orders_goods} as g, {orders} as o
            WHERE o.id=g.order_id AND g.item_id=?i', array($item_id))->row();

        try {
            if ($product && in_array($product['status'], $this->all_configs['configs']['order-statuses-orders'])) {
                throw new ExceptionWithMsg(l('Вы не можете отвязать запчасть, так как заказ закрыт. Предаврительно измените его статус.'));
            }
            if (empty($item)) {
                throw new ExceptionWithMsg(l('Изделие не найдено'));
            }
            if ($product && !strtotime($product['unbind_request'])) {
                // запрос отправлен
                $this->all_configs['db']->query('UPDATE {orders_goods} SET unbind_request=NOW() WHERE item_id=?i AND id=?i',
                    array($item['item_id'], $product['id']));

                // сообщение кладовщику принятия изделия
                $serial = suppliers_order_generate_serial($item, true, false);
                $href = $this->all_configs['prefix'] . 'warehouses?con=' . $product['order_id'] . '#orders-clients_unbind';
                $content = 'Изделие <a href="' . $href . '">' . $serial . '</a> освободилось, отгрузите его на склад';
                $this->notification(l('Необходимо принять изделие'), $content, 'mess-debit-clients-orders');
            }
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'msg' => $e->getMessage(),
            );
        }

        return $data;
    }

    /**
     * сбрасываем цену товара для возвратов
     *
     * @param $items
     * @return mixed
     */
    public function convertItemsPrice($items)
    {
        if (!empty($items)) {
            foreach ($items as &$item) {
                $item['price'] = 0;
            }
        }
        return $items;
    }

    /**
     * @param $post
     * @param $mod_id
     * @return array
     */
    public function write_off_items($post, $mod_id)
    {
        $user_id = $this->getUserId();
        $data = array('state' => true);

        try {
            // права
            if (($this->all_configs['configs']['erp-use'] == false || !$this->all_configs['oRole']->hasPrivilege('write-off-items'))) {
                throw new ExceptionWithMsg(l('У Вас нет прав'));
            }
            // изделия
            $itemIds = isset($post['items']) && count(array_filter(explode(',',
                $post['items']))) > 0 ? array_filter(explode(',', $post['items'])) : null;
            $items = $this->convertItemsPrice($this->Orders->getAvailableItems($itemIds));

            // склад куда списать
            $wh_id = $this->Warehouses->getWriteOffWarehouseId();

            // создаем заказ
            $post = array(
                'clients' => $this->all_configs['configs']['erp-write-off-user'],
                'type' => 2,
                'categories-last' => $this->all_configs['configs']['erp-co-category-write-off'],
                'manager' => $user_id,
                'writeoffings' => true,
                'wh_id' => $wh_id
            );
            $order = $this->add_order($post, $mod_id, false);

            // ошибка при создании заказа
            if (empty($order['id'])) {
                throw new ExceptionWithMsg($order && array_key_exists('msg',
                    $order) ? $order['msg'] : l('Заказ не создан'));
            }

            $this->addSpares($items, $order['id'], $mod_id);
            // статус выдан
            $status = update_order_status(array(
                'id' => $order['id'],
                'status' => $this->all_configs['configs']['order-status-new']
            ), $this->all_configs['configs']['order-status-issued']);
            if (!$status || !isset($status['closed']) || $status['closed'] == false) {
                throw new ExceptionWithMsg($status && array_key_exists('msg',
                    $status) ? $status['msg'] : l('Заказ не закрыт'));
            }
            // оплата
            $transaction = $this->create_transaction(array(
                'transaction_type' => TRANSACTION_INPUT, // внесение
                'cashbox_from' => $this->all_configs['configs']['erp-co-cashbox-write-off'],
                'cashbox_to' => $this->all_configs['configs']['erp-co-cashbox-write-off'],
                'amount_from' => 0,
                'amount_to' => 0,
                'cashbox_currencies_from' => null,
                'cashbox_currencies_to' => $this->all_configs['suppliers_orders']->currency_clients_orders,
                'client_order_id' => $order['id'],
                //'b_id' => $chain_body_a['b_id'],
                'date_transaction' => date("Y-m-d H:i:s"),
                'type' => 1,
            ), $mod_id);
            $data['location'] = $this->all_configs['prefix'] . 'orders/create/' . $order['id'];
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'message' => $e->getMessage()
            );
            // чистим если что-то произошло не так
            if (isset($order)) {
                $this->Orders->rollback($order);
            }
        } catch (ExceptionWithURL $e) {
            $data = array(
                'state' => false,
                'location' => $e->getMessage(),
            );
            // чистим если что-то произошло не так
            if (isset($order)) {
                $this->Orders->rollback($order);
            }
        }

        return $data;
    }

    /**
     * @param      $post
     * @param null $mod_id
     * @return array
     */
    public function create_transaction($post, $mod_id = null)
    {
        // допустимые валюты
        $currencies = $this->all_configs['suppliers_orders']->currencies;
        $data = array('state' => true);
        $cashboxes_currency_id_from = null;
        $cashboxes_currency_id_to = null;
        $supplier_order_id = null;
        $client_order_id = null;
        $order = null;

        if (isset($post['client_order_id']) && $post['client_order_id'] > 0
            && isset($post['client_contractor']) && $post['client_contractor'] == 1
        ) {
            // кассы списание на/с баланс/а контрагента
            $post['cashbox_to'] = $this->all_configs['configs']['erp-cashbox-transaction'];
            $post['cashbox_from'] = $this->all_configs['configs']['erp-cashbox-transaction'];
        }

        try {
            if (!isset($post['transaction_type']) || $post['transaction_type'] == 0 || $post['transaction_type'] > TRANSACTION_TRANSFER) {
                throw new ExceptionWithMsg(l('Выберите тип транзакции'));
            }

            if (($post['transaction_type'] == TRANSACTION_TRANSFER || $post['transaction_type'] == TRANSACTION_OUTPUT) && (!isset($post['cashbox_from']) || $post['cashbox_from'] == 0)) {
                throw new ExceptionWithMsg(l('Выберите с какой кассы'));
            }

            if (($post['transaction_type'] == TRANSACTION_TRANSFER || $post['transaction_type'] == TRANSACTION_OUTPUT) && (!isset($post['cashbox_currencies_from']) || $post['cashbox_currencies_from'] == 0)) {
                throw new ExceptionWithMsg(l('Выберите валюты для кассы'));
            }

            if ($post['transaction_type'] == TRANSACTION_TRANSFER || $post['transaction_type'] == TRANSACTION_OUTPUT || $post['transaction_type'] == TRANSACTION_INPUT) {
                $cashboxes_currency_id_from = $this->all_configs['db']->query('SELECT id FROM {cashboxes_currencies} WHERE cashbox_id=?i AND currency=?i',
                    array($post['cashbox_from'], $post['cashbox_currencies_from']))->el();

                if (!$cashboxes_currency_id_from) {
                    throw new ExceptionWithMsg(l('Такой валюты нет у кассы'));
                }
            }
            if ($post['transaction_type'] == TRANSACTION_INPUT || $post['transaction_type'] == TRANSACTION_TRANSFER) {
                $cashboxes_currency_id_to = $this->all_configs['db']->query('SELECT id FROM {cashboxes_currencies} WHERE cashbox_id=?i AND currency=?i',
                    array($post['cashbox_to'], $post['cashbox_currencies_to']))->el();

                if (!$cashboxes_currency_id_to) {
                    throw new ExceptionWithMsg(l('Такой валюты нет у кассы'));
                }
            }
            if (!isset($post['amount_from']) || !isset($post['amount_to'])) {
                throw new ExceptionWithMsg(l('Введите сумму'));
            }

            if ($post['amount_from'] < 0 || $post['amount_to'] < 0) {
                throw new ExceptionWithMsg(l('Сумма не может быть отрицательной'));
            }
            if (isset($post['amount_to']) && $post['transaction_type'] == TRANSACTION_OUTPUT) {
                $post['amount_to'] = 0;
            }
            if (isset($post['amount_from']) && $post['transaction_type'] == TRANSACTION_INPUT) {
                $post['amount_from'] = 0;
            }
            if (empty($post['amount_from']) && empty($post['amount_to'])) {
                throw new ExceptionWithMsg(l('Сумма не может быть нулевой'));
            }

            // если транзакция на заказ поставщику
            if (isset($post['supplier_order_id']) && $post['supplier_order_id'] > 0) {
                $order = $this->all_configs['db']->query('SELECT o.id, o.count_come, o.price, o.number, o.parent_id,
                      o.supplier, o.sum_paid, o.goods_id,
                      (o.count_come-o.count_debit) as count, o.wh_id, w.title as wh_title
                    FROM {contractors_suppliers_orders} as o
                    LEFT JOIN (SELECT id, title FROM {warehouses})w ON o.wh_id=w.id
                    WHERE (o.sum_paid=0 OR o.sum_paid IS NULL) AND o.id=?i',
                    array($post['supplier_order_id']))->row();

                if (empty($order)) {
                    throw new ExceptionWithMsg(l('Этот заказ уже оплачен'));
                }
                $post['amount_to'] = 0;
                $post['amount_from'] = intval($order['price']) * intval($order['count_come']) / 100;
                $supplier_order_id = $order['id'];
                $post['date_transaction'] = date("Y-m-d H:i:s", time());
                $supplierOrderCurrency = viewCurrencySuppliers();
                $post['comment'] = l("Выплата за заказ поставщика")." {$this->all_configs['suppliers_orders']->supplier_order_number($order)}, ".l("сумма")." {$post['amount_from']} {$supplierOrderCurrency}, ".l('склад')." {$order['wh_title']}, {$post['date_transaction']}";
                $post['contractor_category_id_to'] = $this->all_configs['configs']['erp-so-contractor_category_id_from'];
                $post['contractors_id'] = $order['supplier'];
                $this->ContractorsCategoriesLinks->addCategoryToContractors($post['contractor_category_id_to'], $post['contractors_id']);
            }
            // если транзакция на прием оплаты с заказа клиента
            if (isset($post['client_order_id']) && $post['client_order_id'] > 0) {

                $order = $this->all_configs['db']->query('SELECT o.*, cl.contractor_id FROM {orders} as o
                LEFT JOIN {clients} as cl ON cl.id=o.user_id WHERE o.id=?i', array($post['client_order_id']))->row();

                if (!$order) {
                    throw new ExceptionWithMsg(l('Заказ не найден'));
                }
                $post['date_transaction'] = date("Y-m-d H:i:s", time());
                $client_order_id = $post['client_order_id'];

                if (isset($post['client_contractor']) && $post['client_contractor'] == 1) {
                    if (!isset($order['contractor_id']) || $order['contractor_id'] == 0) {
                        throw new ExceptionWithMsg(l('Клиент не привязан к контрагенту'));
                    }
                    $post['contractors_id'] = $order['contractor_id'];
                } else {
                    $post['contractors_id'] = $this->all_configs['configs']['erp-co-contractor_id_from'];
                    if (array_key_exists('write_off', $order) && $order['write_off'] > 0
                        && $order['write_off'] == $this->Warehouses->getWriteOffWarehouseId()
                    ) {
                        $post['contractors_id'] = $this->all_configs['configs']['erp-co-contractor_off_id_from'];
                    }
                }
                if ($order['sum'] == $order['sum_paid']) {
                    throw new ExceptionWithMsg(l('Заказ уже оплачен'));
                }
                if ($post['transaction_type'] == TRANSACTION_INPUT) {
                    if (isset($post['transaction_extra']) && $post['transaction_extra'] === 'prepay') {
                        $post['contractor_category_id_from'] = $this->all_configs['configs']['erp-co-contractor_category_id_from_prepay'];
                        $post['comment'] = l("Внесение предоплаты клиентом за заказ") . " " . $post['client_order_id'] . ", " . l("сумма") . " " . $post['amount_to'] . ' ' . viewCurrency() . ', ' . $post['date_transaction'];
                        if (round((float)$post['amount_to'] * 100) > $order['prepay'] - $order['sum_paid']) {
                            throw new ExceptionWithMsg(l('Не больше чем') . " " . show_price(intval($order['prepay']) - intval($order['sum_paid'])));
                        }
                    } else {
                        $post['contractor_category_id_from'] = $this->all_configs['configs']['erp-co-contractor_category_id_from'];
                        $post['comment'] = l("Внесение денег клиентом за заказ") . " " . $post['client_order_id'] . ", " . l("сумма") . " " . $post['amount_to'] . ' ' . viewCurrency() . ', ' . $post['date_transaction'];
                        if (!isset($post['confirm']) && round((float)$post['amount_to'] * 100) > $order['sum'] - $order['sum_paid']) {
                            $exception = new ExceptionWithMsg(l('Сума для оплаты составляет') . " " . show_price(intval($order['sum']) - intval($order['sum_paid'])) . ". " . l('Подтверждаете?'));
                            $exception->confirm = 1;
                            throw $exception;
                        }
                    }
                }
                if ($post['transaction_type'] == TRANSACTION_OUTPUT) {
                    $post['contractor_category_id_to'] = $this->all_configs['configs']['erp-co-contractor_category_id_to'];
                    if (!isset($post['comment']) || mb_strlen(trim($post['comment']), 'UTF-8') == 0) {
                        $post['comment'] = l("Выдача денег клиенту за заказ"). " " . $post['client_order_id'] . ", " . l("сумма") . " " . $post['amount_from'] . ' ' . viewCurrency() . ', ' . $post['date_transaction'];
                    }
                    if (round((float)$post['amount_from'] * 100) > ($order['sum_paid'] - $order['sum'])) {
                        throw new ExceptionWithMsg(l('Не больше чем') . " " . show_price(intval($order['sum_paid']) - intval($order['sum'])));
                    }
                }

                if ($post['transaction_type'] == TRANSACTION_INPUT && (!array_key_exists($post['cashbox_currencies_to'],
                            $currencies)
                        || $post['cashbox_currencies_to'] != $this->all_configs['settings']['currency_orders'])
                ) {
                    throw new ExceptionWithMsg(l('Выбранная Вами валюта не совпадает с валютой в заказе'));
                }
                if ($post['transaction_type'] == TRANSACTION_OUTPUT && (!array_key_exists($post['cashbox_currencies_from'],
                            $currencies)
                        || $post['cashbox_currencies_from'] != $this->all_configs['settings']['currency_orders'])
                ) {
                    throw new ExceptionWithMsg(l('Выбранная Вами валюта не совпадает с основной валютой'));
                }
            }

            if (!array_key_exists('date_transaction', $post)) {
                throw new ExceptionWithMsg(l('Введите дату'));
            }
            if ($client_order_id == 0 && (($post['transaction_type'] == TRANSACTION_OUTPUT
                        && $this->all_configs['suppliers_orders']->currency_suppliers_orders != $post['cashbox_currencies_from'])
                    || ($post['transaction_type'] == TRANSACTION_INPUT && $this->all_configs['suppliers_orders']->currency_suppliers_orders != $post['cashbox_currencies_to']))
                && (!isset($post['without_contractor']) || $post['without_contractor'] == 0)
            ) {
                throw new ExceptionWithMsg(l('Оплата производится только в валюте') . " " . $this->all_configs['configs']['currencies'][$this->all_configs['suppliers_orders']->currency_suppliers_orders]['name']);
            }
            // таск 964
            // если тип контрагента - поставщик 2
            // сравниваем с валютой поставщиков
            // если другой тип контрагента с валютой клиентов
            // оказалось поведение ошибочное
            // @todo закоментил до окончательного решения руководства

//            if ($client_order_id  == 0 && (!isset($post['without_contractor']) || $post['without_contractor'] == 0)) {
//                $contractor = db()->query('SELECT * FROM {contractors} WHERE id=?i', array($post['contractors_id']))->row();
//                if ($contractor['type'] == CONTRACTOR_TYPE_PROVIDER) {
//                    if ($post['transaction_type'] == TRANSACTION_OUTPUT && $this->all_configs['suppliers_orders']->currency_suppliers_orders != $post['cashbox_currencies_from']) {
//                        throw new ExceptionWithMsg(l('Оплата производится только в валюте ') . $this->all_configs['configs']['currencies'][$this->all_configs['suppliers_orders']->currency_suppliers_orders]['name']);
//                    }
//                    if ($post['transaction_type'] == TRANSACTION_INPUT && $this->all_configs['suppliers_orders']->currency_suppliers_orders != $post['cashbox_currencies_to']) {
//                        throw new ExceptionWithMsg(l('Оплата производится только в валюте ') . $this->all_configs['configs']['currencies'][$this->all_configs['suppliers_orders']->currency_suppliers_orders]['name']);
//                    }
//                } else {
//                    if ($post['transaction_type'] == TRANSACTION_INPUT && $this->all_configs['suppliers_orders']->currency_clients_orders != $post['cashbox_currencies_to']) {
//                        throw new ExceptionWithMsg(l('Оплата производится только в валюте ') . $this->all_configs['configs']['currencies'][$this->all_configs['suppliers_orders']->currency_clients_orders]['name']);
//                    }
//                    if ($post['transaction_type'] == TRANSACTION_OUTPUT && $this->all_configs['suppliers_orders']->currency_clients_orders != $post['cashbox_currencies_from']) {
//                        throw new ExceptionWithMsg(l('Оплата производится только в валюте ') . $this->all_configs['configs']['currencies'][$this->all_configs['suppliers_orders']->currency_clients_orders]['name']);
//                    }
//                }
//            }

            if ($post['transaction_type'] == TRANSACTION_OUTPUT && (!isset($post['contractor_category_id_to']) || $post['contractor_category_id_to'] == 0)) {
                throw new ExceptionWithMsg(l('Выберите категорию'));
            }

            if ($post['transaction_type'] == TRANSACTION_INPUT && (!isset($post['contractor_category_id_from']) || $post['contractor_category_id_from'] == 0)) {
                throw new ExceptionWithMsg(l('Выберите категорию'));
            }

            if (($post['transaction_type'] == TRANSACTION_INPUT || $post['transaction_type'] == TRANSACTION_OUTPUT) && (!isset($post['contractors_id']) || $post['contractors_id'] == 0)) {
                throw new ExceptionWithMsg(l('Выберите контрагента'));
            }

            if ($post['transaction_type'] == TRANSACTION_TRANSFER && $post['cashbox_currencies_from'] == $post['cashbox_currencies_to']) {
                $post['amount_to'] = $post['amount_from'];
            }
            $contractor_category_link = $category_id = null;
            if (($post['transaction_type'] == TRANSACTION_OUTPUT || $post['transaction_type'] == TRANSACTION_INPUT)) {

                if ($post['transaction_type'] == TRANSACTION_INPUT && isset($post['contractor_category_id_from'])) {
                    $category_id = $post['contractor_category_id_from'];
                }
                if ($post['transaction_type'] == TRANSACTION_OUTPUT && isset($post['contractor_category_id_to'])) {
                    $category_id = $post['contractor_category_id_to'];
                }

                if ($category_id > 0) {
                    $this->ContractorsCategoriesLinks->addCategoryToContractors($category_id, intval($post['contractors_id']));

                    $contractor_category_link = $this->all_configs['db']->query('SELECT id
                      FROM {contractors_categories_links}
                      WHERE contractors_id=?i AND contractors_categories_id=?i',
                        array(intval($post['contractors_id']), $category_id))->el();
                }

                if (!$contractor_category_link) {
                    throw new ExceptionWithMsg(l('Выберите категорию и контрагента'));
                }
            }

            // проверка комментария
            if ($this->all_configs['configs']['manage-transact-comment'] == true && mb_strlen(trim($post['comment']),
                    'UTF-8') == 0
            ) {
                throw new ExceptionWithMsg(l('Введите комментарий'));
            }

            // проверка даты на будущее
            if (time() < strtotime($post['date_transaction'])) {
                throw new ExceptionWithMsg(l('Некорректная дата'));
            }
            // транзакция
            $this->add_transaction($cashboxes_currency_id_from, $cashboxes_currency_id_to, $client_order_id,
                $order, $mod_id, $contractor_category_link, $supplier_order_id, $post);

            $data['cashboxes_currency_id_from'] = $cashboxes_currency_id_from;
            $data['cashboxes_currency_id_to'] = $cashboxes_currency_id_to;

        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'msg' => $e->getMessage(),
            );
            if (isset($e->confirm)) {
                $data['confirm'] = $e->confirm;
            }
        }

        return $data;
    }

    /**
     * @param $cashboxes_currency_id_from
     * @param $cashboxes_currency_id_to
     * @param $client_order_id
     * @param $order
     * @param $mod_id
     * @param $contractor_category_link
     * @param $supplier_order_id
     * @param $post
     * @return mixed
     */
    private function add_transaction(
        $cashboxes_currency_id_from,
        $cashboxes_currency_id_to,
        $client_order_id,
        $order,
        $mod_id,
        $contractor_category_link,
        $supplier_order_id,
        $post
    ) {
        $item_id = $order && array_key_exists('item_id', $order) ? $order['item_id'] : null;
        $goods_id = $order && array_key_exists('goods_id', $order) ? $order['goods_id'] : null;
        $order_goods_id = $order && array_key_exists('order_goods_id', $order) ? $order['order_goods_id'] : null;
        $chain_id = $order && array_key_exists('chain_id', $order) ? $order['chain_id'] : null;
        // тип транзакции
        $type = isset($post['type']) && array_key_exists($post['type'], $this->transactions_types) ? $post['type'] : 0;
        // оплата комиссии
        $type = isset($post['transaction_extra']) && $post['transaction_extra'] == 'payment' ? 6 : $type;
        // оплата за доставку
        $type = isset($post['transaction_extra']) && $post['transaction_extra'] == 'delivery' ? 7 : $type;
        // предоплата
        $type = isset($post['transaction_extra']) && $post['transaction_extra'] == 'prepay' ? 10 : $type;
        // оплата за заказ поставщику
        $type = isset($post['supplier_order_id']) && $post['supplier_order_id'] > 0 ? 8 : $type;

        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : $this->all_configs['configs']['erp-so-user-terminal'];

        $client_order_id = $client_order_id == 0 && isset($post['_client_order_id']) ? $post['_client_order_id'] : $client_order_id;

        // добавляем транзакцию кассе
        $data = array(
            'transaction_type' => $post['transaction_type'],
            'value_from' => round((float)$post['amount_from'] * 100),
            'value_to' => round((float)$post['amount_to'] * 100),
            'comment' => trim($post['comment']),
            'contractor_category_link' => $contractor_category_link,
            'date_transaction' => date("Y-m-d H:i:s", strtotime($post['date_transaction'])),
            'user_id' => $user_id,
            'goods_id' => $goods_id,
            '`type`' => $type,
            'cashboxes_currency_id_from' => $cashboxes_currency_id_from,
            'cashboxes_currency_id_to' => $cashboxes_currency_id_to
        );
        if (!empty($supplier_order_id)) {
            $data['supplier_order_id'] = $supplier_order_id;
        }
        if (!empty($order_goods_id)) {
            $data['order_goods_id'] = $order_goods_id;
        }
        if (!empty($client_order_id)) {
            $data['client_order_id'] = $client_order_id;
        }
        if (!empty($chain_id)) {
            $data['chain_id'] = $chain_id;
        }
        if (!empty($item_id)) {
            $data['item_id'] = $item_id;
        }
        $transaction_id = $this->CashboxesTransactions->insert($data);


        // если транзакция на заказ поставщику
        if (isset($post['supplier_order_id']) && $post['supplier_order_id'] > 0) {
            // обновляем суму в заказе
            $this->all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET sum_paid=?i, date_paid=NOW()
                WHERE id=?i', array(round((float)$post['amount_from'] * 100), $supplier_order_id));

            $o = $this->all_configs['db']->query('SELECT (count_come-count_debit) as count, (price*count_come-sum_paid) as sum
                FROM {contractors_suppliers_orders} WHERE id=?i', array($order['id']))->row();
            // закрываем заказ
            if ($o['count'] == 0 && $o['sum'] == 0) {
                $this->ContractorsSuppliersOrders->update(array('confirm' => 1), array('id' => $order['id']));
            }
        }

        // при выдаче и внесении создаем транзакцию контрагенту
        if (($post['transaction_type'] == TRANSACTION_OUTPUT || $post['transaction_type'] == TRANSACTION_INPUT)
            && ((isset($post['client_contractor']) && $post['client_contractor'] == 1
                    && isset($post['client_order_id']) && $post['client_order_id'] > 0)
                || (!isset($post['client_order_id']) || $post['client_order_id'] == 0))
            && (!isset($post['without_contractor']) || $post['without_contractor'] == 0)
        ) {
            if (($post['transaction_type'] == TRANSACTION_OUTPUT
                    && $this->all_configs['suppliers_orders']->currency_suppliers_orders != $post['cashbox_currencies_from'])
                || ($post['transaction_type'] == TRANSACTION_INPUT
                    && $this->all_configs['suppliers_orders']->currency_suppliers_orders != $post['cashbox_currencies_to'])
            ) {

                if ($post['client_order_id'] > 0) {

                    $amount_from = $order['course_value'] > 0 ? $post['amount_from'] / ($order['course_value'] / 100) : 0;
                    $amount_to = $order['course_value'] > 0 ? $post['amount_to'] / ($order['course_value'] / 100) : 0;
                    $post['contractor_category_id_from'] = isset($post['contractor_category_id_from']) ? $post['contractor_category_id_from'] : '';
                    $post['contractor_category_id_to'] = isset($post['contractor_category_id_to']) ? $post['contractor_category_id_to'] : '';

                    $translate = $post;
                    $translate['type'] = 5;
                    $translate['transaction_type'] = TRANSACTION_TRANSFER;
                    $translate['cashbox_from'] = $this->all_configs['configs']['erp-cashbox-transaction'];
                    $translate['cashbox_to'] = $this->all_configs['configs']['erp-cashbox-transaction'];
                    $translate['amount_from'] = ($post['transaction_type'] == TRANSACTION_OUTPUT) ? $post['amount_from'] : $post['amount_to'];
                    $translate['amount_to'] = ($post['transaction_type'] == TRANSACTION_OUTPUT) ? $amount_from : $amount_to;
                    $translate['cashbox_currencies_from'] = $this->all_configs['suppliers_orders']->currency_clients_orders;
                    $translate['cashbox_currencies_to'] = $this->all_configs['suppliers_orders']->currency_suppliers_orders;
                    $translate['client_order_id'] = 0;
                    $translate['_client_order_id'] = $client_order_id;
                    $translate['comment'] = l('Конвертация средств по заказу') . ' ' . $client_order_id . ', ' . date("Y-m-d H:i:s");
                    // транзакция перевод валюты
                    $this->create_transaction($translate, $mod_id);

                    $transaction = $post;
                    if ($post['transaction_type'] == TRANSACTION_OUTPUT) {
                        $transaction['type'] = 4;
                        $transaction['transaction_type'] = TRANSACTION_INPUT;
                        //$transaction['comment'] = 'Списание с баланса контрагента, ' . date("Y-m-d H:i:s");
                    } else {
                        $transaction['type'] = 3;
                        $transaction['transaction_type'] = TRANSACTION_OUTPUT;
                        //$transaction['comment'] = 'На баланса контрагента, ' . date("Y-m-d H:i:s");
                    }
                    $transaction['comment'] = l('Списание с баланса контрагента, за заказ'). ' ' . $client_order_id . ', ' . date("Y-m-d H:i:s");
                    $transaction['cashbox_currencies_from'] = $this->all_configs['suppliers_orders']->currency_suppliers_orders;
                    $transaction['cashbox_currencies_to'] = $this->all_configs['suppliers_orders']->currency_suppliers_orders;
                    $transaction['amount_from'] = $amount_to;
                    $transaction['amount_to'] = $amount_from;
                    $transaction['cashbox_from'] = $this->all_configs['configs']['erp-cashbox-transaction'];
                    $transaction['cashbox_to'] = $this->all_configs['configs']['erp-cashbox-transaction'];
                    $transaction['client_order_id'] = 0;
                    $transaction['_client_order_id'] = $client_order_id;
                    $transaction['contractor_category_id_to'] = $this->all_configs['configs']['erp-co-contractor_category_return_id_from'];
                    $transaction['contractor_category_id_from'] = $this->all_configs['configs']['erp-co-contractor_category_return_id_to'];

                    // транзакция выдачи/внесения
                    $a = $this->create_transaction($transaction, $mod_id);
                }
            } elseif ($this->all_configs['suppliers_orders']->currency_suppliers_orders == $this->all_configs['suppliers_orders']->currency_clients_orders && $post['client_order_id'] > 0) {
                $amount_from = $order['course_value'] > 0 ? $post['amount_from'] / ($order['course_value'] / 100) : 0;
                $amount_to = $order['course_value'] > 0 ? $post['amount_to'] / ($order['course_value'] / 100) : 0;
                $post['contractor_category_id_from'] = isset($post['contractor_category_id_from']) ? $post['contractor_category_id_from'] : '';
                $post['contractor_category_id_to'] = isset($post['contractor_category_id_to']) ? $post['contractor_category_id_to'] : '';
                $transaction = $post;
                if ($post['transaction_type'] == TRANSACTION_OUTPUT) {
                    $transaction['type'] = 4;
                    $transaction['transaction_type'] = TRANSACTION_INPUT;
                    //$transaction['comment'] = 'Списание с баланса контрагента, ' . date("Y-m-d H:i:s");
                } else {
                    $transaction['type'] = 3;
                    $transaction['transaction_type'] = TRANSACTION_OUTPUT;
                    //$transaction['comment'] = 'На баланса контрагента, ' . date("Y-m-d H:i:s");
                }
                $transaction['comment'] = l('Списание с баланса контрагента, за заказ') . ' ' . $client_order_id . ', ' . date("Y-m-d H:i:s");
                $transaction['cashbox_currencies_from'] = $this->all_configs['suppliers_orders']->currency_suppliers_orders;
                $transaction['cashbox_currencies_to'] = $this->all_configs['suppliers_orders']->currency_suppliers_orders;
                $transaction['amount_from'] = $amount_to;
                $transaction['amount_to'] = $amount_from;
                $transaction['cashbox_from'] = $this->all_configs['configs']['erp-cashbox-transaction'];
                $transaction['cashbox_to'] = $this->all_configs['configs']['erp-cashbox-transaction'];
                $transaction['client_order_id'] = 0;
                $transaction['_client_order_id'] = $client_order_id;
                $transaction['contractor_category_id_to'] = $this->all_configs['configs']['erp-co-contractor_category_return_id_from'];
                $transaction['contractor_category_id_from'] = $this->all_configs['configs']['erp-co-contractor_category_return_id_to'];

                // транзакция выдачи/внесения
                $a = $this->create_transaction($transaction, $mod_id);
            } else {
                $Transactions = new Transactions($this->all_configs);
                // добавляем транзакцию контрагенту и обновляем суму у контрагента
                $Transactions->add_contractors_transaction(array(
                    'transaction_type' => $post['transaction_type'],
                    'cashboxes_currency_id_from' => $cashboxes_currency_id_from,
                    'cashboxes_currency_id_to' => $cashboxes_currency_id_to,
                    'value_from' => $post['amount_from'],
                    'value_to' => $post['amount_to'],
                    'comment' => trim($post['comment']),
                    'contractor_category_link' => $contractor_category_link,
                    'date_transaction' => date("Y-m-d H:i:s", strtotime($post['date_transaction'])),
                    'user_id' => $user_id,
                    'supplier_order_id' => $supplier_order_id,
                    'client_order_id' => $client_order_id,
                    'transaction_id' => $transaction_id,
                    'item_id' => $item_id,
                    'goods_id' => $goods_id,
                    'type' => $type,

                    'contractors_id' => $post['contractors_id'],
                ));
            }
        }
        // при внесении денег за заказ клиента
        if (isset($post['client_order_id']) && $post['client_order_id'] > 0 && $client_order_id > 0) {

            $paid = 0;
            // если выдача
            if ($post['transaction_type'] == TRANSACTION_INPUT) {
                $paid = round((float)($post['amount_to'] * 100));
            }
            // если возврат
            if ($post['transaction_type'] == TRANSACTION_OUTPUT) {
                $paid = -round((float)($post['amount_from'] * 100));
            }

            // вносим сумму в заказ
            $this->Orders->increase('sum_paid', $paid, array('id' => $client_order_id));

            // вносим сумму в заказ за доставку
            if (isset($post['transaction_extra']) && $post['transaction_extra'] == 'delivery') {
                $this->Orders->increase('delivery_paid', $paid, array('id' => $client_order_id));
            }

            // вносим сумму в заказ за комисию (способ оплаты)
            if (isset($post['transaction_extra']) && $post['transaction_extra'] == 'payment') {
                $this->Orders->increase('payment_paid', $paid, array('id' => $client_order_id));
            }

            // если не оплачуем доставку или комиссию(способ оплаты)
            if (!isset($post['transaction_extra'])
                || ($post['transaction_extra'] != 'payment' && $post['transaction_extra'] != 'delivery')
            ) {
                if ($chain_id > 0) {
                    // вносим сумму в цепочку
                    $this->all_configs['db']->query('UPDATE {chains_headers} SET paid=paid+?i WHERE id=?i',
                        array($paid, $chain_id));
                }
            }

            // пробуем закрыть цепочку/заказ
            $this->close_order($client_order_id, $mod_id);
        }

        // обновляем сумму в кассах
        if (isset($post['cashbox_from']) && $post['cashbox_from'] > 0 && isset($post['cashbox_currencies_from']) && $post['cashbox_currencies_from'] > 0) {
            $this->all_configs['db']->query('INSERT INTO {cashboxes_currencies} (cashbox_id, currency, amount)
                  VALUES (?i, ?i, ?) ON DUPLICATE KEY UPDATE amount=amount-VALUES(amount)',
                array($post['cashbox_from'], $post['cashbox_currencies_from'], intval($post['amount_from'] * 100)));
        }
        if (isset($post['cashbox_to']) && $post['cashbox_to'] > 0 && isset($post['cashbox_currencies_to']) && $post['cashbox_currencies_to'] > 0) {
            $this->all_configs['db']->query('INSERT INTO {cashboxes_currencies} (cashbox_id, currency, amount)
                  VALUES (?i, ?i, ?) ON DUPLICATE KEY UPDATE amount=amount+VALUES(amount)',
                array($post['cashbox_to'], $post['cashbox_currencies_to'], intval($post['amount_to'] * 100)));
        }

        $this->History->save('add-transaction', $mod_id, $transaction_id);

        return $order;
    }

    /**
     * @param null $item_id
     * @return string
     */
    public function return_supplier_order_form($item_id = null)
    {
        return $this->view->renderFile('chains.class/return_supplier_order_form', array(
            'canUse' => $item_id > 0 ? $this->can_use_item($item_id) : true,
            'item_id' => $item_id
        ));
    }

    /**
     * @param null $item_id
     * @param null $goods_id
     * @param null $wh_id
     * @param null $order
     * @param bool $show_btn
     * @param null $rand
     * @return string
     */
    public function moving_item_form(
        $item_id = null,
        $goods_id = null,
        $wh_id = null,
        $order = null,
        $show_btn = true,
        $rand = null
    ) {
        return $this->view->renderFile('chains.class/moving_item_form', array(
            'rand' => $rand ? $rand : rand(1000, 9999),
            'item_id' => $item_id,
            'goods_id' => $goods_id,
            'order' => $order,
            'with_logistic' => (!$this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders') || $goods_id > 0),
            'wh_id' => $wh_id,
            'controller' => $this,
            'show_btn' => $show_btn,
        ));
    }

    /**
     * @param $active
     * @return string
     */
    public function order_status($active)
    {
        return $this->view->renderFile('chains.class/order_status', array(
            'orderStates' => $this->all_configs['configs']['order-status'],
            'active' => $active
        ));
    }

    /**
     * @param $item_id
     * @param $order_id
     * @param $wh_id
     * @param $location_id
     * @param $mod_id
     * @return array
     */
    function move_item($item_id, $order_id, $wh_id, $location_id, $mod_id)
    {
        $data = array('state' => true);

        try {
            if ($item_id == 0 && $order_id == 0) {
                throw new ExceptionWithMsg(l('Укажите номер изделия или ремонта'));
            }

            if ($wh_id == 0) {
                throw new ExceptionWithMsg(l('Укажите склад куда'));
            }

            if ($location_id == 0) {
                throw new ExceptionWithMsg(l('Укажите локацию'));
            }

            if ($this->all_configs['configs']['erp-use'] == false) {
                throw new ExceptionWithMsg(l('Нет прав'));
            }

            if ($order_id > 0) {
                // достаем заказ
                $order = $this->all_configs['db']->query('SELECT * FROM {orders} WHERE id=?i',
                    array($order_id))->row();
                if (empty($order)) {
                    throw new ExceptionWithMsg(l('Заказ не найден.'));
                }
                $items = $this->all_configs['db']->query('SELECT id FROM {warehouses_goods_items} WHERE order_id=?i',
                    array($order_id))->vars();
                if ($items && !$this->can_use_item($items, $order_id)) {
                    // проверяем не привязан ли этот серийник в какуюто цепочку
                    throw new ExceptionWithMsg(l('Серийный номер привязан к другому заказу на ремонт.'));
                }

                $chain = $this->get_move_chain_id(null, $order_id, $order['wh_id'], $order['location_id'], $wh_id,
                    $location_id);
                $chain_id = $chain && isset($chain['chain_id']) && $chain['chain_id'] > 0 ? $chain['chain_id'] : null;
                $chain_body_id_to = $chain && isset($chain['chain_body_id_to']) && $chain['chain_body_id_to'] > 0 ? $chain['chain_body_id_to'] : null;
                // перемещаем заказ
                $this->all_configs['manageModel']->stock_moves(null, $order_id, $wh_id, $location_id, $chain_id,
                    'Перемещение на склад', $chain_body_id_to, 2);
                if ($this->all_configs['oRole']->hasPrivilege('logistics-mess')) {
                    // достаем цепочку
                    $chain_id = $this->all_configs['db']->query('SELECT h.id FROM {chains_headers} as h, {chains_bodies} as b
                            WHERE h.avail=1 AND h.id=b.chain_id AND b.number=1 AND b.type=?i AND b.wh_id=?i AND b.location_id=?i',
                        array($this->chain_warehouse, $wh_id, $location_id))->el();
                    if ($chain_id) {
                        $href1 = $this->all_configs['prefix'] . 'orders/create/' . $order['id'];
                        $href2 = $this->all_configs['prefix'] . 'logistics?o_id=' . $order['id'] . '#motions';
                        $content = l('Заказ'). ' <a href="' . $href1 . '">№' . $order['id'] . '</a> '.l('попал на склад и создалась').' <a href="' . $href2 . '">'.l('цепочка').'</a> '.l('(запрос) на перемещение');
                        $this->notification(l('Создалась цепочка на перемещение заказа'), $content,
                            'logistics-mess');
                    }
                }
            } else {
                $items = (array)$item_id;
            }
            if (is_array($items)) {
                foreach ($items as $item_id) {
                    // достаем инфу о изделии
                    $item = $this->all_configs['db']->query('SELECT i.goods_id, i.wh_id, i.location_id,
                            i.id as item_id, i.serial, i.supplier_order_id, i.user_id
                          FROM {warehouses_goods_items} as i WHERE i.id=?i', array($item_id))->row();

                    $data['serial'] = $item ? suppliers_order_generate_serial($item) : '';
                    // проверяем не привязан ли этот серийник в какуюто цепочку
                    if (!$this->can_use_item($item_id, $order_id)) {
                        throw new ExceptionWithMsg(l('Серийный номер привязан к другому заказу на ремонт. Возможно не оприходован заказ поставщику.'));
                    }
                    // двигаем товар
                    if ($item) {

                        $chain = $this->get_move_chain_id($item_id, null, $item['wh_id'], $item['location_id'], $wh_id,
                            $location_id);
                        $chain_id = $chain && isset($chain['chain_id']) && $chain['chain_id'] > 0 ? $chain['chain_id'] : null;
                        $chain_body_id_from = $chain && isset($chain['chain_body_id_from']) && $chain['chain_body_id_from'] > 0 ? $chain['chain_body_id_from'] : null;
                        $chain_body_id_to = $chain && isset($chain['chain_body_id_to']) && $chain['chain_body_id_to'] > 0 ? $chain['chain_body_id_to'] : null;

                        // обновляем местонахождение изделия
                        $ar = $this->all_configs['db']->query(
                            'UPDATE {warehouses_goods_items} SET wh_id=?n, location_id=?n WHERE id=?i',
                            array($wh_id, $location_id, $item_id))->ar();

                        // история перемещений
                        // обновляем передвижение (склад откуда)
                        $this->all_configs['manageModel']->move_product_item(
                            $item['wh_id'],
                            $item['location_id'],
                            $item['goods_id'],
                            $item_id,
                            $order_id,
                            $chain_id,
                            'Перемещение на склад',
                            $chain_body_id_from,
                            1
                        );

                        // обновляем передвижение (склад куда)
                        $this->all_configs['manageModel']->move_product_item(
                            $wh_id,
                            $location_id,
                            $item['goods_id'],
                            $item_id,
                            $order_id,
                            $chain_id,
                            'Перемещен на склад',
                            $chain_body_id_to,
                            2
                        );

                        if (!$order_id && $this->all_configs['oRole']->hasPrivilege('logistics-mess')) {
                            // достаем цепочку
                            $chain_id = $this->all_configs['db']->query('SELECT h.id FROM {chains_headers} as h, {chains_bodies} as b
                                  WHERE h.avail=1 AND h.id=b.chain_id AND b.number=1 AND b.type=?i AND b.wh_id=?i AND b.location_id=?i',
                                array($this->chain_warehouse, $wh_id, $location_id))->el();
                            if ($chain_id) {
                                $href1 = $this->all_configs['prefix'] . 'warehouses?serial=' . $data['serial'] . '#show_items';
                                $href2 = $this->all_configs['prefix'] . 'logistics?i_id=' . $data['serial'] . '#motions';
                                $content = l('Изделие').' <a href="' . $href1 . '">' . $data['serial'] . '</a> '.l('попало на склад и создалась').' <a href="' . $href2 . '">'.l('цепочка').'</a> '.l('(запрос) на перемещение');
                                $this->notification(l('Создалась цепочка на перемещение изделия'), $content,
                                    'logistics-mess');
                            }
                        }

                        // история
                        if ($mod_id) {
                            $this->History->save('move-item', $mod_id, $item_id);
                        }

                        // количество свободных изделий у заказа , количество не обработанных заявок на этот заказ
                        $del = $this->all_configs['db']->query('SELECT COUNT(DISTINCT l.id) as qty_orders,
                                  COUNT(DISTINCT i.id) as qty_free, l.id, o.manager, l.client_order_id, l.order_goods_id
                                FROM {orders} as o, {orders_goods} as g, {orders_suppliers_clients} as l
                                LEFT JOIN {warehouses_goods_items} as i ON i.supplier_order_id=l.supplier_order_id AND
                                  i.order_id IS NULL AND i.wh_id IN (SELECT id FROM {warehouses} WHERE consider_store=?i)
                                WHERE o.id=g.order_id AND l.supplier_order_id=?i AND l.order_goods_id=g.id AND g.item_id IS NULL
                                ORDER BY o.date_add DESC',
                            array(1, $item['supplier_order_id']))->row();

                        if ($del && $del['qty_orders'] > $del['qty_free'] && $del['client_order_id'] > 0 && $del['id'] > 0) {
                            $this->OrdersSuppliersClients->delete($del['id']);
                            $result = $this->order_item($mod_id, array(
                                'order_id' => $del['client_order_id'],
                                'order_product_id' => $del['order_goods_id']
                            ));
                            if ($del['manager'] > 0) {
                                $href = $this->all_configs['prefix'] . 'orders/create/' . $del['client_order_id'];
                                $href1 = $this->all_configs['prefix'] . 'orders/edit/' . (isset($result['order_id']) ? $result['order_id'] : '') . '#create_supplier_order';
                                $content = l('Заявка на').' <a href="' . $href1 . '">'.l('заказ поставщика').'</a> '.l('изменена').' <a href="' . $href . '">№' . $del['client_order_id'] . '</a>';
                                $this->notification(l('Заявка на заказ поставщика изменена'), $content,
                                    $del['manager']);
                            }
                        }
                        $data['state'] = true;
                    }
                }
            }
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'message' => $e->getMessage()
            );
        }

        return $data;
    }

    /**
     * @param $item_id
     * @param $order_id
     * @param $wh_from_id
     * @param $location_from_id
     * @param $wh_to_id
     * @param $location_to_id
     * @return mixed
     */
    public function get_move_chain_id($item_id, $order_id, $wh_from_id, $location_from_id, $wh_to_id, $location_to_id)
    {
        // order or item
        $query = $order_id ? $this->all_configs['db']->makeQuery('m.order_id=?i', array($order_id)) :
            $this->all_configs['db']->makeQuery('m.item_id=?i', array($item_id));

        // get chain_id from last move item
        $chain_id = $this->all_configs['db']->query('SELECT m.chain_id FROM {warehouses_stock_moves} as m
            WHERE ?query ORDER BY m.date_move DESC LIMIT 1', array($query))->el();

        $query = $chain_id > 0 ? '' : 'AND bf.number=' . 1;

        return $this->all_configs['db']->query('SELECT h.id as chain_id, bf.id as chain_body_id_from, bt.id as chain_body_id_to
                FROM {chains_headers} as h
                LEFT JOIN {chains_bodies} as bf ON bf.chain_id=h.id AND bf.wh_id=?i AND (bf.location_id=?i OR bf.location_id IS NULL)
                LEFT JOIN {chains_bodies} as bt ON bt.chain_id=h.id AND bt.wh_id=?i AND (bt.location_id=?i OR bt.location_id IS NULL) AND bt.number>bf.number
                WHERE h.avail=?i AND bf.id IS NOT NULL AND bt.id IS NOT NULL ?query',
            array($wh_from_id, $location_from_id, $wh_to_id, $location_to_id, 1, $query))->row();
    }

    /**
     * @param null $query
     * @return array|null
     */
    public function warehouses($query = null)
    {
        $warehouses = null;

        if ($query === null) {
            $q = $this->query_warehouses();
            $query = $q['query_for_move_item'];
        }

        $data = $this->all_configs['db']->query('SELECT w.id, w.title, w.print_address, w.print_phone, w.code_1c, w.consider_all, w.type,
              w.consider_store, a.sum_qty, a.all_amount, l.location, l.id as location_id, w.type_id, w.group_id
            FROM {warehouses} as w
            LEFT JOIN {warehouses_locations} as l ON l.wh_id=w.id
            LEFT JOIN (SELECT wh_id, SUM(qty) as sum_qty, SUM(amount) as all_amount
              FROM {warehouses_goods_amount} GROUP BY wh_id) a ON a.wh_id=w.id
            ?query', array($query))->assoc();

        if ($data) {
            $warehouses = array();
            foreach ($data as $w) {
                if (!array_key_exists($w['id'], $warehouses)) {
                    $warehouses[$w['id']] = $w;
                    $warehouses[$w['id']]['locations'] = array();
                }
                if ($w['location_id'] > 0) {
                    $warehouses[$w['id']]['locations'][$w['location_id']] = $w['location'];
                }
            }
        }

        return $warehouses;
    }

    /**
     * @param null $order_id
     * @param null $item_id
     * @return string
     */
    public function stock_moves($order_id = null, $item_id = null)
    {
        $where = '';

        $moves = array();
        if ($order_id > 0) {
            $where = $this->all_configs['db']->makeQuery(
                'm.order_id=?i AND l.id=m.location_id AND m.item_id IS NULL AND ?query',
                array($order_id, $where));
        }

        if ($item_id > 0) {
            $where = $this->all_configs['db']->makeQuery(
                'm.item_id=?i AND l.id=m.location_id AND ?query',
                array($item_id, $where));
        }

        if (!empty($where)) {
            $moves = $this->all_configs['db']->query(
                'SELECT m.date_move, m.comment, u.fio, u.login, u.phone, u.email, w.title, l.location, m.comment
                FROM {warehouses_locations} as l, {warehouses} as w, {warehouses_stock_moves} as m
                LEFT JOIN {users} as u ON u.id=m.user_id WHERE ?query w.id=m.wh_id ORDER BY m.date_move DESC',
                array($where))->assoc();

        }

        return $this->view->renderFile('chains.class/stock_moves', array(
            'moves' => $moves
        ));
    }

    /**
     * @param null $goods_id
     * @return array
     */
    public function query_warehouses($goods_id = null)
    {
        $query_for_noadmin = '';
        $query_for_suppliers_orders = '';
        $query_for_move_item = '';
        $query_for_move_item_logistic = '';
        $query_for_create_chain_body_logistic = '';

        // если есть ид товара то достаем склады где есть такой товар
        if ($goods_id > 0) {
            $query_for_move_item = $this->all_configs['db']->makeQuery('RIGHT JOIN (SELECT wh_id FROM {warehouses_goods_items}
                WHERE goods_id=?i GROUP BY wh_id)i ON i.wh_id=w.id', array($goods_id));
            $query_for_move_item_logistic = $this->all_configs['db']->makeQuery('RIGHT JOIN (SELECT wh_id FROM {warehouses_goods_items}
                WHERE goods_id=?i GROUP BY wh_id)i ON i.wh_id=w.id', array($goods_id));
        }

        // проверка на наличие касс которые видит только администратор
        if (array_key_exists('erp-show-warehouses',
                $this->all_configs['configs']) && count($this->all_configs['configs']['erp-show-warehouses']) > 0
        ) {
            if (!$this->all_configs['oRole']->hasPrivilege('site-administration')) {
                $query_for_noadmin = $this->all_configs['db']->makeQuery('AND (w.type NOT IN (?li) OR w.type IS NULL)',
                    array(array_values($this->all_configs['configs']['erp-show-warehouses'])));
            }
            $query_for_move_item = $this->all_configs['db']->makeQuery('?query WHERE (w.type NOT IN (?li) OR w.type IS NULL)',
                array($query_for_move_item, array_values($this->all_configs['configs']['erp-show-warehouses'])));
            $query_for_move_item_logistic = $this->all_configs['db']->makeQuery('?query WHERE (w.type NOT IN (?li) OR w.type IS NULL)',
                array(
                    $query_for_move_item_logistic,
                    array_values($this->all_configs['configs']['erp-show-warehouses'])
                ));

            $query_for_create_chain_body_logistic = $this->all_configs['db']->makeQuery('WHERE (w.type NOT IN (?li) OR w.type IS NULL)',
                array(array_values($this->all_configs['configs']['erp-show-warehouses'])));
        }

        // склады привязаны к текущему пользователю
        $wh_array = $this->all_configs['db']->query('SELECT wh_id FROM {warehouses_users} WHERE user_id=?i',
            array($_SESSION['id']))->vars();

        // закрепленные за админом склады
        if ($wh_array && count($wh_array) > 0) {
            $query_for_my_warehouses = $this->all_configs['db']->makeQuery('wh_id IN (?li)',
                array(array_values($wh_array)));
        } else {
            $query_for_my_warehouses = $this->all_configs['db']->makeQuery('wh_id=?i', array(0));
        }

        // если пользователь не логист и не администратор то показать только его склады
        if (!$this->all_configs['oRole']->hasPrivilege('logistics')) {

            // есть склады у кладовщика
            if ($wh_array && count($wh_array) > 0) {
                $query_for_suppliers_orders = $this->all_configs['db']->makeQuery('AND o.wh_id IN (?li)',
                    array(array_values($wh_array)));
                $query_for_noadmin = $this->all_configs['db']->makeQuery('?query AND w.id IN (?li)',
                    array($query_for_noadmin, array_values($wh_array)));
            } else {
                // нет склады у кладовщика
                $query_for_noadmin = $this->all_configs['db']->makeQuery('?query AND w.id=?i',
                    array($query_for_noadmin, 0));
            }
        }

        // если логист и не администратор или продукт менеджер
        if ($this->all_configs['oRole']->hasPrivilege('logistics') && (!$this->all_configs['oRole']->hasPrivilege('site-administration') && !$this->all_configs['oRole']->hasPrivilege('external-marketing'))) {

            //$query_for_noadmin = $this->all_configs['db']->makeQuery('?query AND w.consider_store=?i',
            //    array($query_for_noadmin, 1));
            $query_for_noadmin = $this->all_configs['db']->makeQuery('?query AND (w.consider_store=?i OR w.type=?i)',
                array($query_for_noadmin, 1, 3));

            if (empty($query_for_move_item)) {
                $query_for_move_item = $this->all_configs['db']->makeQuery('WHERE w.consider_store=?i', array(1));
            } else {
                $query_for_move_item = $this->all_configs['db']->makeQuery('?query AND w.consider_store=?i',
                    array($query_for_move_item, 1));
            }
        }

        return array(
            'query_for_noadmin' => $query_for_noadmin,
            'query_for_noadmin_w' => 'WHERE 1=1 ' . $query_for_noadmin,
            'query_for_suppliers_orders' => $query_for_suppliers_orders,
            'query_for_move_item' => $query_for_move_item,
            'query_for_move_item_logistic' => $query_for_move_item_logistic,
            'query_for_my_warehouses' => $query_for_my_warehouses,
            'array_for_my_warehouses' => $wh_array,
            'query_for_create_chain_body_logistic' => $query_for_create_chain_body_logistic,
        );
    }

    /**
     * @return string
     */
    public function append_js()
    {
        return "<script type='text/javascript' src='{$this->all_configs['prefix']}js/chains-orders.js?3'></script>";
    }

    /**
     * @param $post
     * @param $modId
     * @param $clientId
     * @param $userId
     * @return array
     * @throws ExceptionWithMsg
     */
    protected function createOrder($post, $modId, $clientId, $userId)
    {
        $arr = array(
            'clients' => $clientId,
            'type' => 3,
            'categories-last' => $this->all_configs['configs']['erp-co-category-sold'],
            'soldings' => true,
            'manager' => $userId,
            'warranty' => intval($post['warranty']),
            'cashless' => isset($post['cashless']) ? trim($post['cashless']) : '',
            'sale_type' => isset($post['sale_type']) ? $post['sale_type'] : 0,
            'delivery_by' => isset($post['delivery_by']) ? $post['delivery_by'] : 0,
            'delivery_to' => isset($post['delivery_to']) ? $post['delivery_to'] : '',
            'total_as_sum' => isset($post['total_as_sum']) ? $post['total_as_sum'] : 0,
            'private_comment' => isset($post['private_comment']) ? $post['private_comment'] : '',
            'code' => isset($post['code']) ? $post['code'] : '',
            'referer_id' => isset($post['referer_id']) ? $post['referer_id'] : 0
        );
        $order = $this->add_order($arr, $modId, false);
        // ошибка при создании заказа
        if (empty($order['id'])) {
            throw new ExceptionWithMsg($order && array_key_exists('msg',
                $order) ? $order['msg'] : l('Заказ не создан'));
        }
        return $order;
    }


    /**
     * @param $items
     * @param $orderId
     * @param $modId
     * @throws ExceptionWithMsg
     */
    protected function addSpares($items, $orderId, $modId)
    {
// добавляем запчасти
        foreach ($items as $item) {
            $arr = array(
                'confirm' => isset($item['confirm']) ? $item['confirm'] : 0,
                'order_id' => isset($orderId) ? $orderId : 0,
                'product_id' => $item['goods_id'],
                'price' => $item['price'],
                'warranty' => isset($item['warranty']) ? $item['warranty'] : 0,
                'discount' => isset($item['discount']) ? $item['discount'] : 0,
                'discount_type' => isset($item['discount_type']) ? $item['discount_type'] : 1,
            );
            $product = $this->add_product_order($arr, $modId);
            // ошибка при добавлении запчасти
            if (!$product || (!isset($product['id']) || $product['id'] == 0)) {
                throw new ExceptionWithMsg($product && array_key_exists('msg',
                    $product) ? $product['msg'] : l('Деталь на добавлена'));
            }
            // выдаем изделие
            $arr = array(
                'item_id' => $item['id'],
                'order_product_id' => $product['id'],
                'unlink' => true,
            );
            $bind = $this->bind_item_serial($arr, $modId, false);
            // ошибка при выдачи
            if (!$bind || (!isset($bind['state']) || $bind['state'] == false)) {
                throw new ExceptionWithMsg($bind && array_key_exists('message',
                    $bind) ? $bind['message'] : l('Деталь не выдана'));
            }

            // достаем заказ поставщику
            $so = $this->all_configs['db']->query('SELECT o.id, IF(o.count_come > 0, o.count_come, o.count) as `count`, COUNT(l.id) as count_ordered
                            FROM {warehouses_goods_items} as i, {contractors_suppliers_orders} as o
                            LEFT JOIN {orders_suppliers_clients} as l ON l.supplier_order_id=o.id
                            WHERE i.id=?i AND i.supplier_order_id=o.id GROUP BY o.id',
                array($item['id']))->row();

            if ($so) {
                // создаем заявку
                $ar = $this->OrdersSuppliersClients->insert(array(
                    'client_order_id' => $orderId,
                    'supplier_order_id' => $so['id'],
                    'goods_id' => $item['goods_id'],
                    'order_goods_id' => $product['id']
                ));
                $this->deleteOnePack($orderId, $ar, $so);
            }
            $this->Orders->setOrderSum($orderId, $modId);
        }
    }

    /**
     * @param $message
     * @param $orderId
     * @param $price
     */
    protected function accountantNotification($message, $orderId, $price)
    {
        $href = $this->all_configs['prefix'] . 'accountings?co_id=' . $orderId . '#a_orders-clients';
        $content = $message . ' ' . intval($price) . ' ' . viewCurrency() . ' ' . l('по заказу') . ' <a href="' . $href . '">№' . $orderId . '</a>';
        $this->notification($message, $content, 'mess-accountings-clients-orders');
    }

    /**
     * @param $manager
     * @param $orderId
     */
    protected function managerNotification($manager, $orderId)
    {
        $href = $this->all_configs['prefix'] . 'orders/create/' . $orderId;
        $content = l('Необходимо заказать запчасть для заказа') . '<a href="' . $href . '">№' . $orderId . '</a>';
        $this->notification(l('Необходимо заказать запчасть'), $content, $manager);
    }

    /**
     * @param $title
     * @param $content
     * @param $receiver
     */
    protected function notification($title, $content, $receiver)
    {
        require_once $this->all_configs['sitepath'] . 'mail.php';
        $mailer = new Mailer($this->all_configs);
        $mailer->send_message($content, $title, $receiver, 1);
    }

    /**
     * удаляем одну связку
     * @param $orderId
     * @param $ar
     * @param $so
     */
    protected function deleteOnePack($orderId, $ar, $so)
    {
        if ($ar && $so['count'] <= $so['count_ordered']) {
            $link = $this->all_configs['db']->query('SELECT l.id, o.manager, g.order_id
                                    FROM {orders_suppliers_clients} as l, {orders_goods} as g, {orders} as o
                                    WHERE g.item_id IS NULL AND g.id=l.order_goods_id AND l.supplier_order_id=?i
                                      AND l.client_order_id<>?i AND o.id=g.order_id LIMIT 1',
                array($so['id'], $orderId))->row();

            if ($link) {
                $this->OrdersSuppliersClients->delete($link['id']);

                if ($link['manager']) {
                    $this->managerNotification($link['manager'], $link['order_id']);
                }
            }
        }
    }

    /**
     * @param $post
     * @param $client
     * @param $category
     * @param $wh
     * @param $part_quality_comment
     * @return array
     * @throws ExceptionWithMsg
     */
    protected function createNewOrder($post, $client, $category, $wh, $part_quality_comment)
    {
        $approximate_cost = isset($post['approximate_cost']) ? intval($post['approximate_cost'] * 100) : 0;
        $sum_paid = isset($post['sum_paid']) ? intval($post['sum_paid'] * 100) : 0;
        $color = isset($post['color']) ? intval($post['color']) : -1;
        $code = !empty($post['code']) ? $post['code'] : null;
        $referer_id = !empty($post['referer_id']) ? $post['referer_id'] : null;
        $equipment = isset($post['equipment']) ? trim($post['equipment']) : '';
        $type = isset($post['type']) ? $post['type'] : 0;
        $params = array(
            'id' => $post['id'],
            'user_id' => intval($client['id']),
            'fio' => $client['fio'],
            'email' => isset($client['email']) && mb_strlen(trim($client['email']),
                'UTF-8') > 0 ? trim($client['email']) : null,
            'phone' => mb_strlen(trim($client['phone']), 'UTF-8') > 0 ? trim($client['phone']) : null,
            'comment' => isset($post['comment']) ? trim($post['comment']) : '',
            'category_id' => intval($category['id']),
            'accepter' => $this->getUserId(),
            'title' => trim($category['title']),
            'note' => isset($post['serials']) ? trim($post['serials']) : '',
            'serial' => isset($post['serial']) && mb_strlen(trim($post['serial']),
                'UTF-8') > 0 ? trim($post['serial']) : null,
            'battery' => isset($post['battery']) ? 1 : 0,
            'charger' => isset($post['charger']) ? 1 : 0,
            'cover' => isset($post['cover']) ? 1 : 0,
            'box' => isset($post['box']) ? 1 : 0,
            'repair' => isset($post['repair']) ? intval($post['repair']) : 0,
            'urgent' => isset($post['urgent']) ? 1 : 0,
            'np_accept' => isset($post['np_accept']) ? 1 : 0,
            'notify' => isset($post['notify']) ? 1 : 0,
            'partner' => isset($post['partner']) && intval($post['partner']) > 0 ? intval($post['partner']) : null,
            'approximate_cost' => $approximate_cost,
            '`sum`' => max($sum_paid, $approximate_cost),
            'defect' => $part_quality_comment . (isset($post['defect']) ? trim($post['defect']) : ''),
            'client_took' => isset($post['client_took']) ? 1 : 0,
            'date_readiness' => isset($post['date_readiness']) && strtotime($post['date_readiness']) > 0 ? date('Y-m-d H:i:s',
                strtotime($post['date_readiness'])) : null,
            'course_key' => $this->all_configs['configs']['default-course'],
            'course_value' => getCourse($this->all_configs['settings']['currency_suppliers_orders']),
            'type' => isset($post['type']) ? $post['type'] : 0,
            'prepay' => $sum_paid,
            'is_replacement_fund' => isset($post['is_replacement_fund']) ? 1 : 0,
            'replacement_fund' => isset($post['replacement_fund']) ? trim($post['replacement_fund']) : '',
            'manager' => isset($post['manager']) && $post['manager'] > 0 ? $post['manager'] : null,
            'engineer' => isset($post['engineer']) && $post['engineer'] > 0 ? $post['engineer'] : null,
            'prepay_comment' => isset($post['prepay_comment']) ? trim($post['prepay_comment']) : '',
            'nonconsent' => isset($post['nonconsent']) ? 1 : 0,
            'is_waiting' => isset($post['is_waiting']) ? 1 : 0,
            'courier' => isset($post['is_courier']) && isset($post['courier']) ? trim($post['courier']) : null,
            'accept_location_id' => $wh['location_id'],
            'accept_wh_id' => $wh['wh_id'],
            'code' => $code ? $this->all_configs['db']->makeQuery(" ? ", array($code)) : 'null',
            'referer_id' => $referer_id ? $this->all_configs['db']->makeQuery(" ?i ", array($referer_id)) : 'null',
            'color' => array_key_exists($color, $this->all_configs['configs']['devices-colors']) ? $color : 'null',
            'equipment' => $equipment ? $this->all_configs['db']->makeQuery(" ? ", array($equipment)) : 'null',
            'warranty' => isset($post['warranty']) ? intval($post['warranty']) : 0,
            'cashless' => isset($post['cashless']) && strcmp($post['cashless'], 'on') === 0 ? 1 : 0,
            'delivery_by' => isset($post['delivery_by']) ? intval($post['delivery_by']) : 0,
            'delivery_to' => isset($post['delivery_to']) ? $post['delivery_to'] : '',
            'sale_type' => isset($post['sale_type']) ? intval($post['sale_type']) : 0,
            'total_as_sum' => isset($post['total_as_sum']) ? intval($post['total_as_sum']) : 0,
            'home_master_request' => isset($post['home_master_request']) ? intval($post['home_master_request']) : 0,
        );

        // создаем заказ
        try {
            $id = $this->Orders->save($params);
            if (!empty($_POST['users_fields'])) {
                $this->saveUsersFields($params, $_POST['users_fields']);
            }
            $this->HomeMasterRequests->add($params['id'], $_POST);

            $config = $this->Settings->getByName('order-send-sms-with-client-code');
            $host = $this->Settings->getByName('site-for-add-rating');

            $client = $this->Clients->getByPk($client['id']);
            if (!empty($config) && $config == 'on' && $type === 0) {
                send_sms("+{$client['phone']}",
                    l('Prosim vas ostavit` otziv o rabote mastera na saite') . ' ' . $host . ' ' . l('Vash kod klienta:') . $this->Clients->getClientCode($client['id']));
            }
        } catch (Exception $e) {
            throw new ExceptionWithMsg(l('Неизвестная ошибка при создании заказа'));
        }
        return $id;
    }

    /**
     * @param $post
     * @param $category
     * @param $client
     * @param $wh
     * @param $data
     * @param $sum
     * @param $sum_paid
     * @param $status
     * @param $next
     * @param $mod_id
     * @param $send
     * @param $part_quality_comment
     * @param $crm_request
     * @return mixed
     * @throws Exception
     */
    protected function updateOrderInfo(
        $post,
        $category,
        $client,
        $wh,
        $data,
        $sum,
        $sum_paid,
        $status,
        $next,
        $mod_id,
        $send,
        $part_quality_comment,
        $crm_request
    ) {
// скрытый камент
        $private_comment = $part_quality_comment . (isset($post['private_comment']) ? trim($post['private_comment']) : '');
        if ($private_comment) {
            $this->all_configs['suppliers_orders']->add_client_order_comment($data['id'], $private_comment, 1);
        }
        // прикрепляем заявку к заказу
        if (!empty($crm_request)) {
            get_service('crm/requests')->attach_to_order($data['id'], $crm_request);
        }
        // предоплата
        if ($sum_paid > 0 && $send == true) {
            $this->accountantNotification(l('Необходимо принять предоплату'), $data['id'], $sum_paid / 100);
        }
        // адрес в скрытый комментарий
        if (isset($post['is_courier']) && isset($post['courier'])) {
            $this->all_configs['suppliers_orders']->add_client_order_comment($data['id'],
                l('Курьер забрал устройство у клиента по адресу') . ': ' . trim($post['courier']), 1);
        }
        // сумма
        if ($sum > 0) {
            $this->History->save('update-order-sum', $mod_id, $data['id'], ($sum / 100));
        }
        // подменный фонд
        if (isset($post['is_replacement_fund'])) {
            $this->History->save('update-order-replacement_fund', $mod_id, $data['id'], trim($post['replacement_fund']),
                1);
        }
        // устройство у клиента
        if (isset($post['client_took'])) {
            $this->History->save('update-order-client_took', $mod_id, $data['id'], l('Устройство у клиента'), 1);
        }
        // Неисправность со слов клиента
        if (isset($post['defect']) && mb_strlen(trim($post['defect']), 'UTF-8') > 0) {
            $this->History->save('update-order-defect', $mod_id, $data['id'], trim($post['defect']));
        }
        // Примечание/Внешний вид
        if (isset($post['comment']) && mb_strlen(trim($post['comment']), 'UTF-8') > 0) {
            $this->History->save('update-order-comment', $mod_id, $data['id'], trim($post['comment']));
        }
        // серийник
        if (isset($post['serial']) && mb_strlen(trim($post['serial']), 'UTF-8') > 0) {
            $this->History->save('update-order-serial', $mod_id, $data['id'], trim($post['serial']));
        }
        // фио
        if (mb_strlen($client['fio'], 'UTF-8') > 0) {
            $this->History->save('update-order-fio', $mod_id, $data['id'], trim($client['fio']));
        }
        // телефон
        if (mb_strlen($client['phone'], 'UTF-8') > 0) {
            $this->History->save('update-order-phone', $mod_id, $data['id'], trim($client['phone']));
        }
        // устройство
        $this->History->save('update-order-category', $mod_id, $data['id'], trim($category['title']),
            intval($category['id']));
        // статус
        update_order_status(array('id' => $data['id']), $status);

        // пробуем переместить
        $post = array(
            'wh_id_destination' => $wh['wh_id'],
            'order_id' => $data['id'],
            'location' => $wh['location_id']
        );
        $this->move_item_request($post, $mod_id);

        if (!empty($next)) {
            $data = $this->andPrint($next, $data, $client);
        }

        // достаем запчасти которые можно проверить по категории (устройству)
        $items = $this->all_configs['db']->query('SELECT i.id as item_id, i.serial, i.user_id
                    FROM {warehouses_goods_items} as i, {contractors_suppliers_orders} as o, {category_goods} as cg
                    WHERE o.id=i.supplier_order_id AND o.date_check IS NOT NULL AND i.date_checked IS NULL
                      AND o.goods_id=cg.goods_id AND cg.category_id=?i AND i.user_id IS NOT NULL',
            array($category['id']))->assoc();

        if ($items) {
            $serials = array();
            foreach ($items as $item) {
                $serials[$item['user_id']]['serials'][$item['item_id']] = suppliers_order_generate_serial($item);
            }
            foreach ($serials as $userId => $serial) {
                // уведомление автору приходования запчасти
                $content = l('Можно проверить запчасти') . ': <a href="' . $this->all_configs['prefix'] . 'orders#show_suppliers_orders-wait">' . (implode(', ',
                        $serial['serials'])) . '</a>';
                $content .= ' ' . l('в заказе на ремонт') . ' <a href="' . $this->all_configs['prefix'] . 'orders/create/' . $data['id'] . '">№' . $data['id'] . '</a>';
                $this->notification(l('Можно проверить запчасти'), $content, $userId);
            }
        }
        return $data;
    }

    /**
     * @param $post
     * @param $order
     * @param $data
     * @param $mod_id
     * @return mixed
     * @throws ExceptionWithMsg
     */
    protected function removeSpareOrder($post, $order, $data, $mod_id)
    {
        $order_product_id = isset($post['order_product_id']) ? $post['order_product_id'] : 0;
        $close_supplier_order = isset($post['close_supplier_order']) && $post['close_supplier_order'];
        $good = $this->OrdersGoods->getWithTitle($order_product_id);
        $result = $this->OrdersGoods->remove($order_product_id, $order, $close_supplier_order);
        $this->History->save(
            'update-order-cart',
            $mod_id,
            $order['id'],
            l('Удален') . ' ' . $good['title']
        );
        if (isset($result['reload'])) {
            $data['reload'] = 1;
        }
        return $data;
    }

    /**
     * @param $post
     * @param $product
     * @param $order_id
     * @param $data
     * @return mixed
     * @throws ExceptionWithMsg
     */
    protected function addSpareToOrder($post, $product, $order_id, $data)
    {
        /**
         * post = array(
         * count,
         * price,
         * warranty,
         * discount,
         * confirm
         * )
         */

        $count = isset($post['count']) && intval($post['count']) > 0 ? intval($post['count']) : 1;
        $wh_type = isset($post['confirm']) ? intval($post['confirm']) : 0;
        $price = $product['price'];
        $price_type = $this->getPriceType($post, $product);
        if (isset($post['price'])) {
            $price = $post['price'] * 100;
        } elseif ($price_type == ORDERS_GOODS_PRICE_TYPE_WHOLESALE) {
            $price = $product['price_wholesale'];
        }

        $arr = array(
            'warehouse_type' => $wh_type,
            'user_id' => $this->getUserId(),
            'goods_id' => $product['goods_id'],
            'article' => $product['article'],
            'title' => $product['title'],
            'content' => $product['content'],
            'price' => $price,
            '`count`' => $count,
            'order_id' => $order_id,
            'secret_title' => $product['secret_title'],
            'url' => $product['url'],
            'foreign_warehouse' => $product['foreign_warehouse'],
            '`type`' => (int)$product['type'],
            'warranty' => isset($post['warranty']) ? $post['warranty'] : 0,
            'discount' => isset($post['discount']) ? $post['discount'] : 0,
            'discount_type' => isset($post['discount_type']) ? $post['discount_type'] : 1,
            'price_type' => $price_type
        );

        // пытаемся добавить товар
        $data['id'] = $this->OrdersGoods->insert($arr);

        return $data;
    }

    /**
     * @param $items
     * @param $orderId
     * @param $modId
     */
    public function addProducts($items, $orderId, $modId)
    {
        foreach ($items as $item) {
            for (; $item['quantity'] > 0; $item['quantity']--) {
                $post = array(
                    'confirm' => 1,
                    'order_product_id' => null,
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'price' => $item['price'],
                    'warranty' => $item['warranty'],
                    'discount' => $item['discount'],
                    'discount_type' => $item['discount_type'],
                    'count' => 1
                );
                $this->add_product_order($post, $modId, null, true);
            }
        }
    }

    /**
     * @param $order
     * @param $setStatus
     * @param $post
     * @throws ExceptionWithMsg
     */
    protected function changeOrderStatus($order, $setStatus, $post)
    {
        $status = update_order_status(array(
            'id' => $order['id'],
        ), $setStatus);
        if (empty($status) || $status['state'] != 1) {
            if (!isset($status['closed']) || $status['closed'] == false) {
                throw  new ExceptionWithMsg($status && array_key_exists('msg',
                    $status) ? $status['msg'] : l('Заказ не закрыт'));
            }
        }
        $this->accountantNotification(l('Необходимо принять оплату'), $order['id'], $post['price']);
    }

    /**
     * Array
     * (
     * [amount] => Array
     * (
     * [590] => 100
     * )
     * [discount] => Array
     * (
     * [590] => 0
     * )
     * [quantity] => Array
     * (
     * [590] => 10
     * )
     * [item_ids] => Array
     * (
     * [590] => 18
     * )
     * [warranty] => Array
     * (
     * [590] =>
     * )
     * )
     */
    /**
     * @param $post
     * @return array
     */
    private function prepareCartInfo($post)
    {
        $cart = array();

        if (!emptY($post['item_ids'])) {
            foreach ($post['item_ids'] as $key => $item_id) {
                if (empty($cart[$item_id])) {
                    $cart[$item_id] = array(
                        'quantity' => 0,
                        'id' => $item_id
                    );
                }
                $cart[$item_id]['quantity'] += $post['quantity'][$key];
                $cart[$item_id]['price'] = $post['amount'][$key];
                $cart[$item_id]['warranty'] = $post['warranty'][$key];
                $cart[$item_id]['discount'] = $post['discount'][$key];
                $cart[$item_id]['discount_type'] = $post['discount_type'][$key];
            }
        }
        return $cart;
    }

    /**
     * @param $next
     * @param $data
     * @param $client
     * @return mixed
     */
    protected function andPrint($next, $data, $client)
    {
        switch ($next) {
            case 'print_waybill':
                $data['open_window'] = $this->all_configs['prefix'] . 'print.php?act=waybill&object_id=' . $data['id'];
                $data['location'] = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $data['id'];
                break;
            case 'print_sale_warranty':
                $data['open_window'] = $this->all_configs['prefix'] . 'print.php?act=sale_warranty&object_id=' . $data['id'];
                $data['location'] = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $data['id'];
                break;
            case 'print_check':
                $data['open_window'] = $this->all_configs['prefix'] . 'print.php?act=check&object_id=' . $data['id'];
                $data['location'] = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $data['id'];
                break;

            case 'print':
                $data['open_window'] = $this->all_configs['prefix'] . 'print.php?act=check&object_id=' . $data['id'];
                $data['location'] = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $data['id'];
                break;
            case 'new_order':
                $data['location'] = $this->all_configs['prefix'] . 'orders?c=' . $client['id'] . '#create_order';
                break;
            case 'print_and_new_order':
                $data['location'] = $this->all_configs['prefix'] . 'orders?c=' . $client['id'] . '#create_order';
                $data['open_window'] = $this->all_configs['prefix'] . 'print.php?act=check&object_id=' . $data['id'];
                break;
            default:
                $data['location'] = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $data['id'];
                break;
        }
        return $data;
    }

    /**
     * @param $order
     * @param $fields
     */
    private function saveUsersFields($order, $fields)
    {
        $usersFields = db()->query('SELECT * FROM {users_fields}')->assoc('name');
        foreach ($fields as $name => $value) {
            if (isset($usersFields[$name])) {
                db()->query('INSERT INTO {orders_users_fields} (order_id, users_field_id, value) VALUES (?i, ?i, ?)',
                    array($order['id'], $usersFields[$name]['id'], $value));
            }
        }
    }

    /**
     * есть вызовы, но в репе не смог найти сами методы
     *
     * @param $array
     * @param $mod_id
     * @return array
     */
    private function create_chain_header($array, $mod_id)
    {
        return array();
    }

    /**
     * есть вызовы, но в репе не смог найти сами методы
     *
     * @param $array
     * @param $mod_id
     * @return array
     */
    private function create_chain_body($array, $mod_id)
    {
        return array();
    }

    /**
     * @param $post
     * @param $product
     * @return int
     */
    private function getPriceType($post, $product)
    {
        $field = ($product['type'] == GOODS_TYPE_SERVICE) ? 'price_type_of_service' : 'price_type';
        return isset($post[$field]) && in_array($post[$field], array(
            ORDERS_GOODS_PRICE_TYPE_RETAIL,
            ORDERS_GOODS_PRICE_TYPE_MANUAL,
            ORDERS_GOODS_PRICE_TYPE_WHOLESALE
        )) && $product['type'] != GOODS_TYPE_SERVICE ? $post[$field] : ORDERS_GOODS_PRICE_TYPE_RETAIL;
    }
}

