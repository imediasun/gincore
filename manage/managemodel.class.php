<?php

class manageModel
{
    protected $all_configs;

    public function __construct($all_configs)
    {
        $this->all_configs = $all_configs;
    }

    public function get_count_warehouses_clients_orders($type, $chains)
    {
        if (!$this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders') && $type == 1)
            return 0;

        //$q = $chains->query_warehouses();
        //$query_for_my_warehouses = 'AND b.' . trim($q['query_for_my_warehouses']);

        if ($type == 2) {
            $query = $this->all_configs['db']->makeQuery('AND (b.type=?i OR b.type=?i) AND user_id_issued IS NULL AND user_id_accept>0',
                array($chains->chain_bind_item, $chains->chain_warehouse));
        } elseif ($type == 3) {
            $query = $this->all_configs['db']->makeQuery('AND b.type=?i AND user_id_accept IS NULL AND previous_issued=1',
                array($chains->chain_warehouse));
        } else {
            $query = $this->all_configs['db']->makeQuery('AND b.type=?i AND b.user_id_issued IS NULL AND b.previous_issued=1',
                array($chains->chain_bind_item));
        }

        return $this->all_configs['db']->query('SELECT COUNT(DISTINCT g.order_id)
                FROM {orders_suppliers_clients} as l, {orders} as o, {orders_goods} as g
                LEFT JOIN {warehouses_goods_items} as i ON i.id=g.item_id
                WHERE o.id=g.order_id AND l.order_goods_id=g.id', array())->el();
    }

    function get_count_tradein_orders($all = false)
    {
        if ($all == true) {
            return $this->all_configs['db']->query('SELECT COUNT(DISTINCT id) FROM {tradein}',
                array())->el();
        } else {
            return $this->all_configs['db']->query('SELECT COUNT(DISTINCT id) FROM {tradein}
                WHERE status IS NULL OR status=0', array())->el();
        }
    }

    function get_count_callback($all = false)
    {
        if ($all == true) {
            return $this->all_configs['db']->query('SELECT COUNT(DISTINCT id) FROM {callback}',
                array())->el();
        } else {
            return $this->all_configs['db']->query('SELECT COUNT(DISTINCT id) FROM {callback}
                WHERE status IS NULL OR status=0', array())->el();
        }
    }

    public function get_count_clients_orders($query, $icon_type = 'co')
    {
        return $this->all_configs['db']->query('SELECT COUNT(DISTINCT o.id)
                FROM {orders} AS o
                LEFT JOIN {orders_goods} as og ON og.order_id=o.id
                LEFT JOIN {users} as u ON u.id=o.manager
                LEFT JOIN {clients} as c ON c.id=o.user_id
                LEFT JOIN {users_marked} as m ON m.object_id=o.id AND m.user_id=?i AND m.type=?
                LEFT JOIN {category_goods} as cg ON cg.goods_id=og.goods_id
                ?query',
            array($_SESSION['id'], $icon_type, $query))->el();
    }

    public function get_count_suppliers_orders($query, $icon_type = 'so')
    {
        return $this->all_configs['db']->query('SELECT COUNT(DISTINCT o.id)
                FROM {contractors_suppliers_orders} as o
                LEFT JOIN {warehouses_goods_items} as i ON i.supplier_order_id=o.id
                LEFT JOIN {goods} as g ON g.id=o.goods_id
                LEFT JOIN {contractors} as s ON s.id=o.supplier
                LEFT JOIN {users} as u ON o.user_id=u.id
                LEFT JOIN {users_marked} as m ON m.object_id=o.id AND m.type=? AND m.user_id=?i
                LEFT JOIN {warehouses} as w ON o.wh_id=w.id ?query',
            array($icon_type, $_SESSION['id'], $query))->el();
    }

    public function get_count_logistics_clients_orders($query = '')
    {
        return $this->all_configs['db']->query('SELECT COUNT(DISTINCT h.id)
                FROM {chains_headers} as h, {orders} as o
                WHERE h.order_id > 0 AND o.id=h.order_id AND o.status IN (?li) ?query
                  AND ((h.date_closed IS NULL AND h.return=0) OR (h.date_return IS NULL AND h.return=1))',
            array($this->all_configs['configs']['order-statuses-logistic'], $query))->el();
    }

    public function get_count_logistics_orders($all = false)
    {
        if ($all == true) {
            $query = '';
        } else {
            $query = 'h.date_closed IS NULL AND';
        }

        return $this->all_configs['db']->query('SELECT COUNT(DISTINCT h.id) FROM {chains_headers} as h
              WHERE ?query h.parent IS NOT NULL AND (h.order_id=0 OR h.order_id IS NULL)',
            array($query))->el();
    }

    public function get_count_accounting_clients_orders($query)
    {
        return $this->all_configs['db']->query('SELECT COUNT(DISTINCT o.id)
                FROM {orders} as o LEFT JOIN {orders_goods} as og ON og.order_id=o.id WHERE 1=1 ?query',
            array($query))->el();

    }

    /*public function get_count_accounting_suppliers_orders()
    {
        //(((o.price*o.count_come)-o.sum_paid)<>0 OR o.date_paid IS NULL)
        return $this->all_configs['db']->query('SELECT COUNT(DISTINCT o.id) FROM {contractors_suppliers_orders} as o
              WHERE (((o.price*o.count_come)-o.sum_paid)<>0 OR o.date_paid IS NULL) AND o.count_come>0
                AND (o.confirm=0)',
            array())->el();
    }*/

    public function get_count_accountings_noncash_orders_pre()
    {
        return $this->all_configs['db']->query('SELECT COUNT(DISTINCT o.id) FROM {orders} as o WHERE o.status=?i OR o.status=?i',
            array($this->all_configs['configs']['order-status-wait-pay'],
                $this->all_configs['configs']['order-status-part-pay']))->el();
    }

    public function get_count_accountings_credit_orders_pre()
    {
        return $this->all_configs['db']->query('SELECT COUNT(DISTINCT o.id) FROM {orders} as o WHERE o.status=?i',
            array($this->all_configs['configs']['order-status-loan-wait']))->el();
    }

    public function global_filters($filters = array(), $use = array())
    {
        //$use = array('date', 'category', 'product', 'operators', client, )
        $query = '';

        // фильтр по дате
        $day_from = null;//1 . date(".m.Y") . ' 00:00:00';
        $day_to = null;//31 . date(".m.Y") . ' 23:59:59';
        if (array_key_exists('df', $filters) && strtotime($filters['df']) > 0)
            $day_from = $_GET['df'] . ' 00:00:00';
        if (array_key_exists('dt', $filters) && strtotime($filters['dt']) > 0)
            $day_to = $_GET['dt'] . ' 23:59:59';
        if (in_array('date', $use)) {
            if ($day_from && $day_to) {
                $query = $this->all_configs['db']->makeQuery('AND DATE(o.date_add) BETWEEN STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")
              AND STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")', array($day_from, $day_to));
            } elseif ($day_from) {
                $query = $this->all_configs['db']->makeQuery('AND DATE(o.date_add)>=STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")',
                    array($day_from));
            } elseif ($day_to) {
                $query = $this->all_configs['db']->makeQuery('AND DATE(o.date_add)<=STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")',
                    array($day_to));
            }
        }
        // фильтр по категории товара
        if (isset($filters['g_cg']) && $filters['g_cg'] > 0 && in_array('category', $use)) {
            $query = $this->all_configs['db']->makeQuery('?query AND cg.category_id=?i',
                array($query, $filters['g_cg']));
        }
        // фильтр по товару
        if (isset($filters['by_gid']) && $filters['by_gid'] > 0 && in_array('product', $use)) {
            $query = $this->all_configs['db']->makeQuery('?query AND og.goods_id=?i',
                array($query, $filters['by_gid']));
        }
        // фильтр по оператору
        if (array_key_exists('op', $filters) && count(array_filter(explode(',', $filters['op']))) > 0 && in_array('operators', $use)) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.manager IN (?li)',
                array($query, array_filter(explode(',', $filters['op']))));
        }
        // фильтр по
        if (array_key_exists('co_id', $filters) && $filters['co_id'] > 0 && in_array('client_orders_id', $use)) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.id=?i',
                array($query, $filters['co_id']));
        }

        return $query;
    }

    public function suppliers_orders_query($filters = array())
    {
        $count_on_page = count_on_page();//20;
        $skip = (isset($filters['p']) && $filters['p'] > 0) ? ($count_on_page * ($filters['p'] - 1)) : 0;

        // фильтр по дате
        $day_from = null;//1 . date(".m.Y") . ' 00:00:00';
        $day_to = null;//31 . date(".m.Y") . ' 23:59:59';
        if (array_key_exists('df', $filters) && strtotime($filters['df']) > 0)
            $day_from = $filters['df'] . ' 00:00:00';
        if (array_key_exists('dt', $filters) && strtotime($filters['dt']) > 0)
            $day_to = $filters['dt'] . ' 23:59:59';

        if ($day_from && $day_to) {
            $query = $this->all_configs['db']->makeQuery('WHERE DATE(o.date_come) BETWEEN STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")
              AND STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")', array($day_from, $day_to));
        } elseif ($day_from) {
            $query = $this->all_configs['db']->makeQuery('WHERE DATE(o.date_come)>=STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")',
                array($day_from));
        } elseif ($day_to) {
            $query = $this->all_configs['db']->makeQuery('WHERE DATE(o.date_come)<=STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")',
                array($day_to));
        } else {
            $query = $this->all_configs['db']->makeQuery('WHERE 1=1', array());
        }

        if (isset($filters['sp']) && count(array_filter(explode(',', $filters['sp']))) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.supplier IN (?li)',
                array($query, array_filter(explode(',', $filters['sp']))));
        }

        if (isset($filters['sst']) && $filters['sst'] > 0) {
            if ($filters['sst'] == 1) {
                $query = $this->all_configs['db']->makeQuery('?query AND (o.count_come IS NULL OR o.count_come=?i) AND o.avail=?i',
                    array($query, 0, 1));
            }
            if ($filters['sst'] == 2) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.avail=?i', array($query, 0));
            }
            if ($filters['sst'] == 3) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.date_wait<NOW() AND (o.count_come IS NULL OR o.count_come=?i) AND o.avail=?i',
                    array($query, 0, 1));
            }
            if ($filters['sst'] == 4) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.count_come>?i AND o.count_come<o.count_debit',
                    array($query, 0));
            }
        }

        if (isset($filters['pso_id']) && $filters['pso_id'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND (o.num LIKE "%?e%" OR o.id LIKE "%?e%" OR o.parent_id LIKE "%?e%")',
                array($query, $filters['pso_id'], $filters['pso_id'], $filters['pso_id']));
        }

        if (isset($filters['co']) && $filters['co'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.id = ?i',
                array($query, $filters['co']));
        }

        if (isset($filters['so_id']) && $filters['so_id'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND (o.num=?i OR o.id=?i OR o.parent_id=?i)',
                array($query, $filters['so_id'], $filters['so_id'], $filters['so_id']));
        }

        if (isset($filters['gds']) && is_array($filters['gds']) && count(array_filter((array)$filters['gds'])) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.goods_id IN (?li)',
                array($query, (array)$filters['gds']));
        }

        if (isset($filters['whk']) || isset($filters['wha'])) {
            $types = array();
            if (isset($filters['whk'])) {
                $types[] = 1;
            }
            if (isset($filters['wha'])) {
                $types[] = 2;
            }
            $query = $this->all_configs['db']->makeQuery('?query AND warehouse_type IN (?li)',
                array($query, $types));
        }

        if (isset($filters['wait'])) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.date_check IS NOT NULL AND i.date_checked IS NULL',
                array($query));
        }

        if (isset($filters['marked'])) {
            $query = $this->all_configs['db']->makeQuery('?query AND m.user_id=?i AND m.type=?',
                array($query, $_SESSION['id'], trim($filters['marked'])));
        }

        if (isset($filters['by_gid']) && $filters['by_gid'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.goods_id=?i',
                array($query, $filters['by_gid']));
        }

        if (isset($filters['my']) && $filters['my'] == true) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.user_id=?i', array($query, $_SESSION['id']));
        }

        if (isset($filters['avail']) && $filters['avail'] == 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.avail=?i', array($query, 0));
        }

        if (isset($filters['type']) && $filters['type'] == 'pay') {
            $query = $this->all_configs['db']->makeQuery('?query AND (((o.price*o.count_come)-o.sum_paid)<>0
                    OR o.date_paid IS NULL) AND o.count_come>0 ', // AND o.confirm=0
                array($query));
        }

        if (isset($filters['type']) && $filters['type'] == 'debit') {
            $query = $this->all_configs['db']->makeQuery('?query AND o.wh_id>0 AND o.count_come > 0',
                array($query));
        }
        if (isset($filters['type']) && $filters['type'] == 'debit-work') {
            $query = $this->all_configs['db']->makeQuery('?query AND o.wh_id>0 AND o.count_come > 0 AND o.count_come <> o.count_debit',
                array($query));
        }
        if ((isset($filters['opened']) && $filters['opened'] == true) || (isset($filters['fco']) && $filters['fco'] == 'unworked')) {
            if (!array_key_exists('manage-qty-so-only-debit', $this->all_configs['configs'])
                    || $this->all_configs['configs']['manage-qty-so-only-debit'] == false) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.confirm=0',
                    array($query));
            } else {
                $query = $this->all_configs['db']->makeQuery('?query AND o.count_come<>o.count_debit AND o.count_come > 0',
                    array($query));
            }
        }

        return array(
            'query' => $query,
            'skip' => $skip,
            'count_on_page' => $count_on_page
        );
    }

    public function get_clients_orders($query, $skip, $count_on_page, $icon_type = 'co')
    {
        $orders = array();
        $orders_goods = null;

        $ids = $this->all_configs['db']->query('SELECT DISTINCT o.id
                FROM {orders} AS o
                LEFT JOIN {orders_goods} as og ON og.order_id=o.id
                LEFT JOIN {users} as u ON u.id=o.manager
                LEFT JOIN {users} as a ON a.id=o.accepter
                LEFT JOIN {clients} as c ON c.id=o.user_id
                LEFT JOIN {users_marked} as m ON m.object_id=o.id AND m.type=? AND m.user_id=?i
                LEFT JOIN {category_goods} as cg ON cg.goods_id=og.goods_id
                ?query ORDER BY o.date_add DESC LIMIT ?i, ?i',//o.status, o.date_add DESC
            array($icon_type, $_SESSION['id'], $query, $skip, $count_on_page))->vars();

        if ($ids) {
            $orders_goods = $this->all_configs['db']->query('SELECT o.id as order_id, o.status, o.title as product,
                  o.date_add, o.user_id, m.id as m_id, mi.id as mi_id, o.date_add as date, o.manager, o.sum_paid, o.sum, o.comment,
                  o.np_accept, og.goods_id, og.title, og.id as order_goods_id, og.count, o.note, l.location, o.courier,
                  o.discount, u.fio as h_fio, u.login as h_login, o.urgent, o.wh_id, w.title as wh_title, aw.title as aw_wh_title, og.type,
                  a.fio as a_fio, a.email as a_email, a.phone as a_phone, a.login as a_login, u.email as h_email,
                  o.fio as o_fio, o.email as o_email, o.phone as o_phone, sc.supplier_order_id, co.supplier,
                  gr.color, tp.icon
                FROM {orders} AS o
                LEFT JOIN {orders_goods} as og ON og.order_id=o.id AND og.type=0
                LEFT JOIN {orders_suppliers_clients} as sc ON sc.order_goods_id=og.id AND sc.client_order_id=o.id
                LEFT JOIN {contractors_suppliers_orders} as co ON co.id=sc.supplier_order_id
                LEFT JOIN {users} as u ON u.id=o.manager
                LEFT JOIN {users} as a ON a.id=o.accepter
                LEFT JOIN {users_marked} as m ON m.object_id=o.id AND m.type=? AND m.user_id=?i
                LEFT JOIN {users_marked} as mi ON mi.object_id=o.id AND mi.type="oi"
                LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                LEFT JOIN {warehouses_locations} as l ON l.id=o.location_id
                LEFT JOIN {warehouses} as aw ON aw.id=o.accept_wh_id
                LEFT JOIN {warehouses_groups} as gr ON gr.id=aw.group_id
                LEFT JOIN {warehouses_types} as tp ON tp.id=aw.type_id
                WHERE o.id IN (?li) ORDER BY o.date_add DESC',//o.status, o.date_add DESC
                array($icon_type, $_SESSION['id'], array_keys($ids)))->assoc();

            if ($orders_goods) {
                foreach ($orders_goods as $order) {
                    if (!array_key_exists($order['order_id'], $orders)) {
                        $orders[$order['order_id']] = $order;
                        $orders[$order['order_id']]['goods'] = array();
                        $orders[$order['order_id']]['ordered'] = array();
                        $orders[$order['order_id']]['finish'] = array();
                    }
                    if ($order['supplier'] > 0) {
                        $orders[$order['order_id']]['finish'][] = $order['supplier'];
                    }
                    if ($order['supplier_order_id'] > 0) {
                        $orders[$order['order_id']]['ordered'][] = $order['supplier_order_id'];
                    }
                    if ($order['order_goods_id'] > 0) {
                        $orders[$order['order_id']]['goods'][$order['order_goods_id']] = array();
                    }
                    /*if ($order['goods_id'] > 0) {
                        if (!array_key_exists($order['goods_id'], $orders[$order['order_id']]['goods'])) {
                            $orders[$order['order_id']]['goods'][$order['goods_id']]['title'] = $order['title'];
                            $orders[$order['order_id']]['goods'][$order['goods_id']]['goods_id'] = $order['goods_id'];
                            $orders[$order['order_id']]['goods'][$order['goods_id']]['count'] = 0;
                        }
                    }*/
                }
            }
        }

        return $orders;
    }

    public function clients_orders_query($filters = array())
    {
        $count_on_page = count_on_page();//20;
        $skip = (isset($filters['p']) && $filters['p'] > 0) ? ($count_on_page * ($filters['p'] - 1)) : 0;

        // фильтр по дате
        $day_from = null;//1 . '.' . 1 . date(".Y") . ' 00:00:00';
        $day_to = null;//31 . '.' . 12 . date(".Y") . ' 23:59:59';Y-m-d H:i:s
        if (array_key_exists('df', $filters) && strtotime($filters['df']) > 0)
            $day_from = $filters['df'] . ' 00:00:00';
        if (array_key_exists('dt', $filters) && strtotime($filters['dt']) > 0)
            $day_to = $filters['dt'] . ' 23:59:59';

        $date_query = '';
        if ($day_from && $day_to) {
            $query = $this->all_configs['db']->makeQuery('WHERE DATE(o.date_add) BETWEEN STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")
              AND STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")', array($day_from, $day_to));
            $date_query = $this->all_configs['db']->makeQuery('DATE(date) BETWEEN STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")
              AND STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")', array($day_from, $day_to));
        } elseif ($day_from) {
            $query = $this->all_configs['db']->makeQuery('WHERE DATE(o.date_add)>=STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")',
                array($day_from));
            $date_query = $this->all_configs['db']->makeQuery('DATE(date)>=STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")',
                array($day_from));
        } elseif ($day_to) {
            $query = $this->all_configs['db']->makeQuery('WHERE DATE(o.date_add)<=STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")',
                array($day_to));
            $date_query = $this->all_configs['db']->makeQuery('DATE(date)<=STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")',
                array($day_to));
        } else {
            $query = $this->all_configs['db']->makeQuery('WHERE 1=1', array());
        }

        if (array_key_exists('type', $filters)) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.type=?i',
                array($query, intval($filters['type'])));
        }

        if (isset($filters['c_id']) && $filters['c_id'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.user_id=?i',
                array($query, intval($filters['c_id'])));
        }

        // не оплаченные
        if (isset($filters['nm']) && $filters['nm'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.status = ?i AND (o.sum > o.sum_paid OR o.sum = 0)',
                array($query, $this->all_configs['configs']['order-status-issued']));
        }

        // принимались на доработку
        if (isset($filters['ar']) && $filters['ar'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND (SELECT id FROM {order_status} '
                                                                    .'WHERE order_id = o.id AND status = ?i ?q LIMIT 1) IS NOT NULL',
                array($query, $this->all_configs['configs']['order-status-rework'], $date_query ? ' AND '.$date_query : ''));
        }
        
        if (isset($filters['co']) && !empty($filters['co'])) {
            $query = $this->all_configs['db']->makeQuery('?query AND (o.id=?i OR c.fio LIKE "%?e%" OR c.phone LIKE "%?e%")',
                array($query, intval($filters['co']), trim($filters['co']), trim($filters['co'])));
        }

        if (isset($filters['o_id']) && !empty($filters['o_id'])) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.id LIKE "%?e%"',
                array($query, trim($filters['o_id'])));
        }

        if (isset($filters['c_phone']) && !empty($filters['c_phone'])) {
            $query = $this->all_configs['db']->makeQuery('?query AND c.phone LIKE "%?e%"',
                array($query, trim($filters['c_phone'])));
        }

        if (isset($filters['c_fio']) && !empty($filters['c_fio'])) {
            $query = $this->all_configs['db']->makeQuery('?query AND c.fio LIKE "%?e%"',
                array($query, trim($filters['c_fio'])));
        }

        if (isset($filters['o_serial']) && $filters['o_serial']) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.serial LIKE "%?e%"',
                array($query, $filters['o_serial']));
        }

        if (isset($filters['cl']) && !empty($filters['cl'])) {
            $query = $this->all_configs['db']->makeQuery('?query AND (c.fio LIKE "%?e%" OR c.phone LIKE "%?e%")',
                array($query, trim($filters['cl']), trim($filters['cl'])));
        }

        if (isset($filters['fco'])) {
            if ($filters['fco'] == 'unworked') {
                $query = $this->all_configs['db']->makeQuery('?query AND (o.manager IS NULL OR o.manager="")',
                    array($query));
            }
        }
        if (isset($filters['marked'])) {
            $query = $this->all_configs['db']->makeQuery('?query AND m.user_id=?i AND m.type=?',
                array($query, $_SESSION['id'], trim($filters['marked'])));
        }
        if (isset($filters['mg']) && count(array_filter(explode(',', $filters['mg']))) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.manager IN (?li)',
                array($query, array_filter(explode(',', $filters['mg']))));
        }
        if (!empty($filters['manager'])) {
            $query = $this->all_configs['db']->makeQuery('
                LEFT JOIN {users} as man ON man.id = o.manager
                ?query AND man.fio LIKE "%?e%"',
                array($query, $filters['manager']));
        }
        if (isset($filters['eng']) && count(array_filter(explode(',', $filters['eng']))) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.engineer IN (?li)',
                array($query, array_filter(explode(',', $filters['eng']))));
        }
        if (!empty($filters['engineer'])) {
            $query = $this->all_configs['db']->makeQuery('
                LEFT JOIN {users} as eng ON eng.id = o.engineer
                ?query AND eng.fio LIKE "%?e%"',
                array($query, $filters['engineer']));
        }
        if (isset($filters['acp']) && count(array_filter(explode(',', $filters['acp']))) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.accepter IN (?li)',
                array($query, array_filter(explode(',', $filters['acp']))));
        }
        if (!empty($filters['accepter'])) {
            $query = $this->all_configs['db']->makeQuery('
                LEFT JOIN {users} as acp ON acp.id = o.accepter
                ?query AND acp.fio LIKE "%?e%"',
                array($query, $filters['accepter']));
        }
        if (isset($filters['st']) && count(explode(',', $filters['st'])) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.status IN (?li)',
                array($query, explode(',', $filters['st'])));
        }

        if (isset($filters['wh']) && count(explode(',', $filters['wh'])) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.accept_wh_id IN (?li)',
                array($query, explode(',', $filters['wh'])));
        }

        if (isset($filters['by_gid']) && $filters['by_gid'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND og.goods_id=?i',
                array($query, $filters['by_gid']));
        }

        if (isset($filters['dev']) && $filters['dev'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.category_id=?i',
                array($query, $filters['dev']));
        }
        if (!empty($filters['device'])) {
            $query = $this->all_configs['db']->makeQuery('
                LEFT JOIN {categories} as cats ON cats.id = o.category_id
                ?query AND cats.title LIKE "%?e%"',
                array($query, $filters['device']));
        }

        if (isset($filters['serial']) && $filters['serial']) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.serial=?',
                array($query, $filters['serial']));
        }

        if (isset($filters['g_cg']) && $filters['g_cg'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND cg.category_id=?i',
                array($query, $filters['g_cg']));
        }

        if (isset($filters['co_id']) && $filters['co_id'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.id=?i',
                array($query, $filters['co_id']));
        }

        if (isset($filters['np']) && $filters['np'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.np_accept=?i',
                array($query, 1));
        }

        if (isset($filters['rf']) && $filters['rf'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.is_replacement_fund=?i',
                array($query, 1));
        }

        if (isset($filters['with_manager']) && $filters['with_manager'] == true) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.manager IS NULL',
                    array($query));
        }

        if (isset($filters['prepay']) && $filters['prepay'] == true) {
            $payments = array();
            foreach ($this->all_configs['configs']['payment-msg'] as $payment=>$values) {
                if ($values['pay'] == 'pre') {
                    $payments[] = $payment;
                }
            }
            if (count($payments) > 0) {
                $query = $this->all_configs['db']->makeQuery('RIGHT JOIN {chains_headers} as h ON h.order_id=o.id
                        ?query AND (o.payment IN (?list) OR o.status IN (?li))',
                    array($query, array_values($payments), array($this->all_configs['configs']['order-status-wait-pay'],
                            $this->all_configs['configs']['order-status-part-pay'])));
            } else {
                $query = $this->all_configs['db']->makeQuery('RIGHT JOIN {chains_headers} as h ON h.order_id=o.id
                        ?query AND o.status IN (?li)',
                    array($query, array($this->all_configs['configs']['order-status-wait-pay'],
                        $this->all_configs['configs']['order-status-part-pay'])));
            }

        }

        if (isset($filters['chains']) && $filters['chains'] == true) {
            $query = $this->all_configs['db']->makeQuery('RIGHT JOIN {chains_headers} as h ON h.order_id=o.id
                    ?query AND o.status IN (?li)',
                array($query, $this->all_configs['configs']['order-statuses-logistic']));

            if (isset($filters['bodies']) && is_array($filters['bodies']) && count($filters['bodies']) > 0) {
                $query = $this->all_configs['db']->makeQuery('RIGHT JOIN {chains_bodies} as b ON h.id=b.chain_id
                        LEFT JOIN {warehouses_goods_items} as i ON i.id=h.item_id
                        ?query AND b.type IN (?li)',
                    array($query, array_values($filters['bodies'])));
            }
        }

        return array(
            'query' => $query,
            'count_on_page' => $count_on_page,
            'skip' => $skip,
        );
    }

    public function get_suppliers_orders($query, $skip, $count_on_page, $icon_type = 'so')
    {
        $orders = array();

        if (array_key_exists('erp-contractors-use-for-suppliers-orders', $this->all_configs['configs']) && count($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders']) > 0) {
            if (isset($_GET['wg']) && !empty($_GET['wg'])) {
                 
                $wga = explode(',', $_GET['wg']);
                 
                $orders_ids = $this->all_configs['db']->query('SELECT DISTINCT o.id
                    FROM {contractors_suppliers_orders} as o
                    LEFT JOIN {users_marked} as m ON m.object_id=o.id AND m.type=? AND m.user_id=?i
                    LEFT JOIN {warehouses_goods_items} as i ON i.supplier_order_id=o.id '
                    . 'LEFT JOIN {orders_suppliers_clients} AS osc ON osc.supplier_order_id=o.id '
                    . 'LEFT JOIN {orders} AS oo ON osc.client_order_id=oo.id '
                    . 'LEFT JOIN {warehouses} AS w ON oo.accept_wh_id=w.id '
                    . 'INNER JOIN {warehouses_groups} AS wg ON wg.id=w.group_id AND w.group_id IN (?li) '
                    .'?query GROUP BY o.id ORDER BY o.date_add DESC LIMIT ?i, ?i',//o.parent_id DESC,
                array($icon_type, $_SESSION['id'], $wga, $query, $skip, $count_on_page))->vars();

            } else {
                $orders_ids = $this->all_configs['db']->query('SELECT DISTINCT o.id
                    FROM {contractors_suppliers_orders} as o
                    LEFT JOIN {users_marked} as m ON m.object_id=o.id AND m.type=? AND m.user_id=?i
                    LEFT JOIN {warehouses_goods_items} as i ON i.supplier_order_id=o.id
                    ?query GROUP BY o.id ORDER BY o.date_add DESC LIMIT ?i, ?i',//o.parent_id DESC,
                array($icon_type, $_SESSION['id'], $query, $skip, $count_on_page))->vars();
            }
            
            if ($orders_ids) {
                $data = $this->all_configs['db']->query('SELECT o.id, o.goods_id, o.date_add, o.date_wait, o.count,
                          o.count_come, mi.id as mi_id, o.price, o.comment, o.sum_paid, o.number, o.date_come, o.parent_id, o.confirm,
                          g.title as goods_title, o.user_id, g.secret_title, o.count_debit, o.wh_id, o.user_id_accept,
                          u.fio, u.email, u.login, o.wh_id, w.title as wh_title, o.supplier, s.title as stitle, o.num,
                          ac.fio as accept_fio, ac.email as accept_email, ac.login as accept_login, i.id as item_id,
                          o.group_parent_id, m.id as m_id, o.avail, o.unavailable, l.location, o.location_id,
                          o.date_check, osc.client_order_id, i.date_checked, i.serial
                        FROM {contractors_suppliers_orders} as o
                        LEFT JOIN {orders_suppliers_clients} as osc ON o.id=osc.supplier_order_id
                        LEFT JOIN {goods} as g ON g.id=o.goods_id
                        LEFT JOIN {contractors} as s ON s.id=o.supplier AND s.type IN (?li)
                        LEFT JOIN {users} as u ON o.user_id=u.id
                        LEFT JOIN {users} as ac ON o.user_id_accept=ac.id
                        LEFT JOIN {warehouses} as w ON o.wh_id=w.id
                        LEFT JOIN {warehouses_locations} as l ON l.id=o.location_id
                        LEFT JOIN {warehouses_goods_items} as i ON i.supplier_order_id=o.id
                        LEFT JOIN {users_marked} as m ON m.object_id=o.id AND m.type=? AND m.user_id=?i
                        LEFT JOIN {users_marked} as mi ON mi.object_id=o.id AND mi.type="woi"
                        WHERE o.id IN (?li) ORDER BY o.date_add DESC',//o.parent_id DESC,
                    array(array_values($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders']),
                        $icon_type, $_SESSION['id'], array_keys($orders_ids)))->assoc();


                foreach ($data as $o) {
                    if (!array_key_exists($o['id'], $orders)) {
                        $orders[$o['id']] = $o;
                        $orders[$o['id']]['items'] = array();
                    }
                    if ($o['item_id'] > 0) {
                        $orders[$o['id']]['items'][$o['item_id']] = array('item_id' => $o['item_id'], 'date_checked' => $o['date_checked'], 'serial' => $o['serial']);
                    }
                    /*if ($o['client_order_id'] > 0) {
                        $url = $this->all_configs['prefix'] . 'orders/create/' . $o['client_order_id'];
                        $orders[$o['id']]['orders'][$o['client_order_id']] = '<a href="' . $url . '">' . $o['client_order_id'] . '</a>';
                    }*/
                }
            }
        }

        return $orders;
    }

    public function profit_margin($filters = array()/*, $return_table = false*/)
    {
        // фильтры

        // фильтр по дате
        $day_from = 1 . date(".m.Y") . ' 00:00:00';
        $day_to = 31 . date(".m.Y") . ' 23:59:59';
        if (array_key_exists('df', $filters) && strtotime($filters['df']) > 0)
            $day_from = $filters['df'] . ' 00:00:00';
        if (array_key_exists('dt', $filters) && strtotime($filters['dt']) > 0)
            $day_to = $filters['dt'] . ' 23:59:59';
        $query = '';

        // фильтр по категориям товаров
        /*if (array_key_exists('g_cg', $filters) && count(array_filter(explode(',', $filters['g_cg']))) > 0) {
            $goods = $this->all_configs['db']->query('SELECT goods_id FROM {category_goods} as WHERE category_id IN (?li)',
                array(array_filter(explode(',', $filters['g_cg']))))->vars();
            if (count($goods) > 0) {
                $query = $this->all_configs['db']->makeQuery('?query AND og.goods_id IN (?li)',
                    array($query, array_keys($goods)));
            } else {
                $query = $this->all_configs['db']->makeQuery('?query AND og.goods_id=?i', array($query, 0));
            }
        }*/
        // фильтр по менеджерам
        if (array_key_exists('mg', $filters) && count(array_filter(explode(',', $filters['mg']))) > 0) {
            /*$cos = $this->all_configs['db']->query('SELECT DISTINCT order_id
                FROM {users_goods_manager} as m, {orders_goods} as g WHERE m.user_id IN (?li) AND m.goods_id=g.goods_id',
                array(array_filter(explode(',', $filters['mg']))))->vars();
            if (count($cos) > 0) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.id IN (?li)',
                    array($query, array_keys($cos)));
            } else {
                $query = $this->all_configs['db']->makeQuery('?query AND o.id=?i',  array($query, 0));
            }*/
            $query = $this->all_configs['db']->makeQuery('?query AND o.manager IN (?li)',
                array($query, array_filter(explode(',', $filters['mg']))));
        }
        // фильтр по приемщику
        if (array_key_exists('acp', $filters) && count(array_filter(explode(',', $filters['acp']))) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.accepter IN (?li)',
                array($query, array_filter(explode(',', $filters['acp']))));
        }
        // фильтр по Инженер
        if (array_key_exists('eng', $filters) && count(array_filter(explode(',', $filters['eng']))) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.engineer IN (?li)',
                array($query, array_filter(explode(',', $filters['eng']))));
        }
        // фильтр по оператору
        if (array_key_exists('op', $filters) && count(array_filter(explode(',', $filters['op']))) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.manager IN (?li)',
                array($query, array_filter(explode(',', $filters['op']))));
        }
        // фильтр по товару
        if (array_key_exists('by_gid', $filters) && $filters['by_gid'] > 0) {
            //$query = $this->all_configs['db']->makeQuery('?query AND t.goods_id=?i',
            //    array($query, intval($filters['by_gid'])));
            $cos = $this->all_configs['db']->query('SELECT DISTINCT order_id FROM {orders_goods} WHERE goods_id=?i',
                array(intval($filters['by_gid'])))->vars();
            if (count($cos) > 0) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.id IN (?li)',
                    array($query, array_keys($cos)));
            } else {
                $query = $this->all_configs['db']->makeQuery('?query AND o.id=?i',  array($query, 0));
            }
        }
        // принято через новую почту
        if (array_key_exists('np', $filters) && $filters['np'] == 1) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.np_accept=?i',
                array($query, 1));
        }
        // гарантийный
        if (array_key_exists('wrn', $filters) && $filters['wrn'] == 1 && (!array_key_exists('nowrn', $filters) || $filters['nowrn'] <> 1)) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.repair=?i',
                array($query, 1));
        }
        // негарантийный
        if (array_key_exists('nowrn', $filters) && $filters['nowrn'] == 1 && (!array_key_exists('wrn', $filters) || $filters['wrn'] <> 1)) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.repair<>?i',
                array($query, 1));
        }
        // не учитывать возвраты поставщикам
        if (array_key_exists('rtrn', $filters) && $filters['rtrn'] == 1) {
            $query = $this->all_configs['db']->makeQuery('?query AND t.type NOT IN (?li)',
                array($query, array(1, 2, 3, 4)));
        }
        // не учитывать доставку
        if (array_key_exists('dlv', $filters)) {
            $query = $this->all_configs['db']->makeQuery('?query AND t.type<>?i', array($query, 7));
        }
        // не учитывать комиссию
        if (array_key_exists('cms', $filters)) {
            $query = $this->all_configs['db']->makeQuery('?query AND t.type<>?i', array($query, 6));
        }
        // категория
        if (array_key_exists('dev', $filters) && intval($filters['dev']) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND cg.id=?i',
                array($query, intval($filters['dev'])));
        }

        $profit = $turnover = $avg = $purchase = $purchase2 = $sell = $buy = 0;
        $orders = $this->all_configs['db']->query('SELECT o.id as order_id, t.type, o.course_value, t.transaction_type,
              SUM(IF(t.transaction_type=2, t.value_to, 0)) as value_to, t.order_goods_id as og_id, o.category_id,
              SUM(IF(t.transaction_type=1, t.value_from, 0)) as value_from, cg.title, SUM(IF(t.type=?i, 0, 1)) as qty,
              SUM(IF(t.transaction_type=1, 1, 0)) as has_from, SUM(IF(t.transaction_type=2, 1, 0)) as has_to
            FROM {categories} as cg, {orders} as o, {cashboxes_transactions} as t
            WHERE o.id=t.client_order_id AND t.type<>?i AND cg.id=o.category_id AND
              DATE(t.date_transaction) BETWEEN STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s")
              AND STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s") ?query GROUP BY o.id HAVING qty>0 ORDER BY o.id',
            array(10, 8, $day_from, $day_to, $query))->assoc('order_id');

        if ($orders) {
            $prices = $this->all_configs['db']->query('SELECT i.order_id, SUM(i.price) as price
                FROM {warehouses_goods_items} as i WHERE i.order_id IN (?li) GROUP BY i.order_id',
                array(array_keys($orders)))->vars();

            $goods = array();
            $data = $this->all_configs['db']->query(
                'SELECT title, price, order_id, `type`, goods_id, id FROM {orders_goods} WHERE order_id IN (?li)',
                array(array_keys($orders)))->assoc();

            if ($data) {
                foreach ($data as $p) {
                    $goods[$p['order_id']][$p['type'] == 1 ? 'services' : 'goods'][$p['id']] = $p;
                }
            }
            foreach ($orders as $order_id=>$order) {
                $orders[$order_id]['goods'] = isset($goods[$order_id]) && isset($goods[$order_id]['goods']) ? $goods[$order_id]['goods'] : array();
                $orders[$order_id]['services'] = isset($goods[$order_id]) && isset($goods[$order_id]['services']) ? $goods[$order_id]['services'] : array();

                $price = $prices && isset($prices[$order_id]) ? intval($prices[$order_id]) : 0;
                $orders[$order_id]['turnover'] = $order['value_to'] - $order['value_from'];
                $orders[$order_id]['purchase'] = $price * ($order['course_value'] / 100);
                $orders[$order_id]['profit'] = 0;

                if ($order['has_to'] > 0) {
                    $orders[$order_id]['profit'] = $orders[$order_id]['value_to']/* - $orders[$order_id]['purchase']*/;
                }
                if ($order['has_from'] > 0) {
                    $orders[$order_id]['profit'] -= ($orders[$order_id]['value_from']/* - $orders[$order_id]['purchase']*/);
                }
                $orders[$order_id]['profit'] -= $orders[$order_id]['purchase'];

                $orders[$order_id]['avg'] = 0;
                if ($orders[$order_id]['purchase'] == 0)
                    $orders[$order_id]['avg'] = '&infin;';
                if ($orders[$order_id]['purchase'] > 0 && $orders[$order_id]['turnover'] > 0)
                    $orders[$order_id]['avg'] = $orders[$order_id]['profit'] / $orders[$order_id]['purchase'] * 100;

                $sell += $order['value_to'];
                $buy += $order['value_from'];
                $purchase += $orders[$order_id]['purchase'];
                $turnover += $orders[$order_id]['turnover'];
                $profit += $orders[$order_id]['profit'];
                $purchase2 += ($orders[$order_id]['turnover'] > 0 ? $orders[$order_id]['purchase'] : 0);
            }
        }

        if ($purchase2 == 0)
            $avg = '&infin;';
        if ($purchase2 > 0 && $turnover > 0)
            $avg = ($profit) / $purchase2 * 100;

        return array(
            'profit' => $profit,
            'turnover' => $turnover,
            'avg' => $avg,
            'purchase' => $purchase,
            'purchase2' => $purchase2,
            'sell' => $sell,
            'orders' => $orders,
        );

    }

    function orders_product($p_id)
    {
        $c = $this->all_configs['configs'];

        // актуальные заказы на товар
        return $this->all_configs['db']->query('SELECT o.id as order_id, SUM(og.count) as count
                FROM {orders} as o
                LEFT JOIN {orders_goods} as og ON og.order_id=o.id
                LEFT JOIN {warehouses_goods_items} as i ON i.order_id=o.id AND og.goods_id=i.goods_id
                WHERE o.status NOT IN (?li) AND i.id IS NULL AND og.goods_id=?i GROUP BY o.id',
            array(array($c['order-status-returned'], $c['order-status-client-failure']), $p_id))->vars();
    }

    /*function free_balance($product_id, $wh_id = null)
    {
        $query = '';
        if ($wh_id > 0) {
            $query = $this->all_configs['db']->makeQuery('AND w.id=?i', array($wh_id));
        }

        return (int)$this->all_configs['db']->query('SELECT COUNT(i.id)
                FROM {warehouses} as w, {warehouses_goods_items} as i

                WHERE i.goods_id=?i AND i.order_id IS NULL AND w.id=i.wh_id AND w.consider_store=1 ?query',
            array($product_id, $query))->el();
    }*/

    /**
     * вызываем при любом движении изделия
     * */
    // $move_type: 1 - Перемещение на склад, 2 - Перемещен на склад, 0 - не определено
    function move_product_item($wh_id, $location_id, $goods_id = null, $item_id = null, $order_id = null, $chain_id = null, $msg = '', $chain_body_id = null, $move_type = 0)
    {
        // есть товар
        if ($goods_id > 0 && $wh_id > 0) {
            // добавляем/обновляем сумму товаров по складам и ихнее количество
            $this->all_configs['db']->query('INSERT INTO {warehouses_goods_amount} (goods_id, wh_id, qty, amount)
                    SELECT ?i, w.id, COUNT(i.goods_id) as qty, SUM(i.price) as amount
                    FROM {warehouses} as w
                    LEFT JOIN {warehouses_goods_items} as i ON w.id=i.wh_id AND i.goods_id=?i
                    WHERE w.id=?i GROUP BY w.id, i.goods_id
                    ON DUPLICATE KEY UPDATE qty=VALUES(qty), amount=VALUES(amount)',
                array($goods_id, $goods_id, $wh_id));
        }

        // обновление свободных остатков товара
        $this->update_product_free_qty($goods_id);

        // обновление остатков товара на складе
        $this->update_product_wh_qty($goods_id);

        $this->stock_moves($item_id, $order_id, $wh_id, $location_id, $chain_id, $msg, $chain_body_id, $move_type);
    }

    // $move_type: 1 - Перемещение на склад(Перемещение на склад к заказу), 2 - Перемещен на склад(Перемещен на склад к заказу), 0 - не определено
    function stock_moves($item_id, $order_id, $wh_id, $location_id, $chain_id, $msg, $chain_body_id, $move_type = 0)
    {
        if (($item_id > 0 || $order_id > 0) && $wh_id > 0) {
            // история перемещений
            $move_id = $this->all_configs['db']->query('INSERT INTO {warehouses_stock_moves}
                (item_id, user_id, wh_id, comment, chain_id, chain_body_id, order_id, location_id) VALUES (?n, ?i, ?i, ?, ?n, ?n, ?n, ?n)',
                array($item_id, $_SESSION['id'], $wh_id, $msg, $chain_id, $chain_body_id, $order_id, $location_id), 'id');

            if ($order_id > 0) {
                $this->all_configs['db']->query('UPDATE {orders} SET location_id=?n, wh_id=?i WHERE id=?i',
                    array($location_id, $wh_id, $order_id));
            }
            
//            echo 'order_id: '.$order_id.', item_id: '.$item_id;
            // смотрим привязку к цепочке перемещений - новая логистика
            // инициируем item_move только когда операция "Перемещен на склад"
            if($move_type == 2){
                // может быть сразу $order_id и $item_id
                if($order_id){
                    get_service('logistic')->item_move($order_id, 1, $move_id, $wh_id, $location_id);
                }
                if($item_id){
                    get_service('logistic')->item_move($item_id, 2, $move_id, $wh_id, $location_id);
                }
            }
        }
    }

    function update_product_free_qty($goods_id)
    {
        // есть товар
        $query_product = '';

        // есть товар
        if ($goods_id > 0) {
            $query_product = $this->all_configs['db']->makeQuery('AND g.id=?i', array($goods_id));
        }

        $this->all_configs['db']->query('UPDATE {goods} g LEFT JOIN(SELECT i.goods_id,
            COUNT(DISTINCT i.id) - COUNT(DISTINCT l.id) as qty_store FROM {warehouses} as w, {warehouses_goods_items} as i
            LEFT JOIN {orders_suppliers_clients} as l ON i.supplier_order_id=l.supplier_order_id AND l.order_goods_id IN
            (SELECT id FROM {orders_goods} WHERE item_id IS NULL) WHERE w.consider_store=1 AND i.wh_id=w.id AND i.order_id IS NULL
            GROUP BY i.goods_id) as v ON g.id=v.goods_id SET g.qty_store=v.qty_store,
            g.foreign_warehouse=IF(g.foreign_warehouse_auto=1, IF(v.qty_store>0, 0, 1), g.foreign_warehouse)
            WHERE g.id IS NOT NULL ?query',
            array($query_product));

        //$this->update_product_wait($goods_id);
    }

    function update_product_wh_qty($goods_id)
    {
        $query_product = '';

        // есть товар
        if ($goods_id > 0) {
            $query_product = $this->all_configs['db']->makeQuery('AND g.id=?i', array($goods_id));
        }

        // обновляем товарам количество на складах
        $this->all_configs['db']->query('UPDATE {goods} g LEFT JOIN(SELECT i.goods_id, COUNT(i.goods_id) as qty_wh
            FROM {warehouses} as w, {warehouses_goods_items} as i WHERE w.id=i.wh_id AND w.consider_all=1
            GROUP BY i.goods_id) as v ON g.id=v.goods_id SET g.qty_wh=v.qty_wh WHERE g.id IS NOT NULL ?query',
            array($query_product));

        // пересчет даты ожидания товара
        $this->update_product_wait($goods_id);
    }

    function update_product_wait($goods_id)
    {
        $query_product = '';

        // есть товар
        if ($goods_id > 0) {
            $query_product = $this->all_configs['db']->makeQuery('AND ug.id=?i', array($goods_id));
        }

        $this->all_configs['db']->query('UPDATE {goods} ug LEFT JOIN(
                SELECT o.goods_id, o.date_wait, IF (o.date_wait > NOW(), 0, 1) order_date
                FROM {contractors_suppliers_orders} as o, {goods} as g
                WHERE (o.count_come>o.count_debit OR o.count_come is null OR o.count_come=0)
                  AND g.id=o.goods_id AND g.qty_store=0 ORDER BY order_date, o.date_wait LIMIT 1) as w
              ON w.goods_id=ug.id SET ug.wait=w.date_wait WHERE ug.id IS NOT NULL ?query',
            array($query_product));

    }

    function order_goods($order_id, $type = null, $order_product_id = null)
    {
        $goods = null;
        $orders = array_unique(array_filter((array)$order_id));

        if (count($orders) > 0) {
            $where = '';
            if ($type !== null) {
                $where = $this->all_configs['db']->makeQuery('AND g.type=?i ?query', array($type, $where));
            }
            if ($order_product_id !== null) {
                $where = $this->all_configs['db']->makeQuery('AND g.id=?i ?query', array($order_product_id, $where));
            }

            $goods = $this->all_configs['db']->query('SELECT g.*, l.supplier_order_id as so_id, i.id as item_id,
                      i.serial, o.unavailable, o.date_come, o.date_wait, o.count_debit, o.date_come, o.supplier,
                      o.count as count_order, o.date_add, o.count_come, i.date_add as date_debit, co.manager,
                      g.warehouse_type, o.warehouse_type as order_warehouse_type
                    FROM {orders} as co, {orders_goods} as g
                    LEFT JOIN {orders_suppliers_clients} as l ON l.order_goods_id=g.id
                    LEFT JOIN {warehouses_goods_items} as i ON i.id=g.item_id
                    LEFT JOIN {contractors_suppliers_orders} as o ON o.id=l.supplier_order_id
                    WHERE g.order_id IN (?li) AND co.id=g.order_id ?query',
                array($orders, $where))->assoc('id');

            if ($order_product_id !== null && $goods) {
                $goods = isset($goods[$order_product_id]) ? $goods[$order_product_id] : null;
            }
            /*if ($count_ordered == true && $goods) {
                $goods = current($goods);
            }*/
        }

        return $goods;
    }

}
