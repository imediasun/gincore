<?php
require_once __DIR__ . '/Core/Object.php';

class manageModel extends Object
{
    protected $all_configs;

    /**
     * manageModel constructor.
     * @param $all_configs
     */
    public function __construct($all_configs)
    {
        $this->all_configs = $all_configs;
    }

    /**
     * @param $type
     * @param $chains
     * @return int
     */
    public function get_count_warehouses_clients_orders($type, $chains)
    {
        if (!$this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders') && $type == 1) {
            return 0;
        }

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

    /**
     * @param bool $all
     * @return mixed
     */
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

    /**
     * @param bool $all
     * @return mixed
     */
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

    /**
     * @param        $query
     * @param string $icon_type
     * @return mixed
     */
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

    /**
     * @param        $query
     * @param string $icon_type
     * @return mixed
     */
    public function get_count_suppliers_orders($query, $icon_type = 'so')
    {
        return $this->all_configs['db']->query('SELECT COUNT(DISTINCT o.id)
                FROM {contractors_suppliers_orders} as o
                LEFT JOIN {orders_suppliers_clients} AS osc ON osc.supplier_order_id=o.id 
                LEFT JOIN {orders} AS oo ON osc.client_order_id=oo.id 
                LEFT JOIN {warehouses_goods_items} as i ON i.supplier_order_id=o.id
                LEFT JOIN {goods} as g ON g.id=o.goods_id
                LEFT JOIN {contractors} as s ON s.id=o.supplier
                LEFT JOIN {users} as u ON o.user_id=u.id
                LEFT JOIN {users_marked} as m ON m.object_id=o.id AND m.type=? AND m.user_id=?i
                LEFT JOIN {warehouses} as w ON o.wh_id=w.id ?query',
            array($icon_type, $_SESSION['id'], $query))->el();
    }

    /**
     * @param string $query
     * @return mixed
     */
    public function get_count_logistics_clients_orders($query = '')
    {
        return $this->all_configs['db']->query('SELECT COUNT(DISTINCT h.id)
                FROM {chains_headers} as h, {orders} as o
                WHERE h.order_id > 0 AND o.id=h.order_id AND o.status IN (?li) ?query
                  AND ((h.date_closed IS NULL AND h.return=0) OR (h.date_return IS NULL AND h.return=1))',
            array($this->all_configs['configs']['order-statuses-logistic'], $query))->el();
    }

    /**
     * @param bool $all
     * @return mixed
     */
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

    /**
     * @param $query
     * @return mixed
     */
    public function get_count_accounting_clients_orders($query)
    {
        return $this->all_configs['db']->query('SELECT COUNT(DISTINCT o.id)
                FROM {orders} as o LEFT JOIN {orders_goods} as og ON og.order_id=o.id WHERE 1=1 ?query',
            array($query))->el();

    }

    /**
     * @return mixed
     */
    public function get_count_accountings_noncash_orders_pre()
    {
        return $this->all_configs['db']->query('SELECT COUNT(DISTINCT o.id) FROM {orders} as o WHERE o.status=?i OR o.status=?i',
            array(
                $this->all_configs['configs']['order-status-wait-pay'],
                $this->all_configs['configs']['order-status-part-pay']
            ))->el();
    }

    /**
     * @return mixed
     */
    public function get_count_accountings_credit_orders_pre()
    {
        return $this->all_configs['db']->query('SELECT COUNT(DISTINCT o.id) FROM {orders} as o WHERE o.status=?i',
            array($this->all_configs['configs']['order-status-loan-wait']))->el();
    }

    /**
     * @param array $filters
     * @param array $use
     * @return string
     */
    public function global_filters($filters = array(), $use = array())
    {
        $query = '';

        // фильтр по дате
        $day_from = null;//1 . date(".m.Y") . ' 00:00:00';
        $day_to = null;//31 . date(".m.Y") . ' 23:59:59';
        if (array_key_exists('df', $filters) && strtotime($filters['df']) > 0) {
            $day_from = $_GET['df'] . ' 00:00:00';
        }
        if (array_key_exists('dt', $filters) && strtotime($filters['dt']) > 0) {
            $day_to = $_GET['dt'] . ' 23:59:59';
        }
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
        if (array_key_exists('op', $filters) && count(array_filter(explode(',',
                $filters['op']))) > 0 && in_array('operators', $use)
        ) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.manager IN (?li)',
                array($query, array_filter(explode(',', $filters['op']))));
        }
        // фильтр по
        if (array_key_exists('co_id', $filters) && $filters['co_id'] > 0 && in_array('client_orders_id', $use)) {
            if (preg_match('/^[zZ]-/', trim($filters['co_id'])) === 1) {
                $orderId = preg_replace('/^[zZ]-/', '', trim($filters['co_id']));
            } else {
                $orderId = trim($filters['co_id']);
            }
            if (intval($orderId) > 0) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.id=?i',
                    array($query, intval($orderId)));
            }
        }
        // фильтр по
        if (array_key_exists('co', $filters) && !empty($filters['co']) && in_array('client', $use)) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.fio LIKE "%?q%"',
                array($query, $filters['co']));
        }

        if (array_key_exists('cashless', $filters) && is_numeric($filters['cashless']) && in_array('cashless', $use)) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.cashless=?i',
                array($query, (int)$filters['cashless']));
        }

        return $query;
    }

    /**
     * @param array $filters
     * @return array
     */
    public function suppliers_orders_query($filters = array())
    {
        $count_on_page = count_on_page();//20;
        $skip = (isset($filters['p']) && $filters['p'] > 0) ? ($count_on_page * ($filters['p'] - 1)) : 0;

        // фильтр по дате
        $day_from = null;//1 . date(".m.Y") . ' 00:00:00';
        $day_to = null;//31 . date(".m.Y") . ' 23:59:59';
        if (array_key_exists('df', $filters) && strtotime($filters['df']) > 0) {
            $day_from = $filters['df'] . ' 00:00:00';
        }
        if (array_key_exists('dt', $filters) && strtotime($filters['dt']) > 0) {
            $day_to = $filters['dt'] . ' 23:59:59';
        }

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
                $query = $this->all_configs['db']->makeQuery('?query AND (o.count_come IS NULL OR o.count_come=?i) AND o.avail=?i AND NOT o.supplier is NULL',
                    array($query, 0, 1));
            }
            if ($filters['sst'] == 2) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.avail=?i', array($query, 0));
            }
            if ($filters['sst'] == 3) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.date_wait<NOW() AND (o.count_come IS NULL OR o.count_come=?i) AND o.avail=?i AND NOT o.supplier is NULL',
                    array($query, 0, 1));
            }
            if ($filters['sst'] == 4) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.avail=1 AND NOT o.supplier is NULL AND NOT o.date_wait is NULL AND o.count_come > o.count_debit AND NOT o.confirm = 1',
                    array($query));
            }
            if ($filters['sst'] == 5) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.supplier is NULL AND o.avail=1 AND o.confirm=0 AND o.wh_id is NULL AND o.count_come=0',
                    array($query));
            }
        }

        if (isset($filters['pso_id']) && $filters['pso_id'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND (o.num LIKE "%?e%" OR o.id LIKE "%?e%" OR o.parent_id LIKE "%?e%")',
                array($query, $filters['pso_id'], $filters['pso_id'], $filters['pso_id']));
        }

        if (isset($filters['co']) && $filters['co'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND oo.id = ?i',
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
                || $this->all_configs['configs']['manage-qty-so-only-debit'] == false
            ) {
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

    /**
     * @param        $query
     * @param        $skip
     * @param        $count_on_page
     * @param string $icon_type
     * @return array
     */
    public function get_clients_orders($query, $skip = 0, $count_on_page = 0, $icon_type = 'co')
    {
        $orders = array();
        $orders_goods = null;
        $limit = '';
        if (!empty($count_on_page)) {
            $limit = $this->all_configs['db']->makeQuery('LIMIT ?i, ?i', array(
                $skip,
                $count_on_page
            ));
        }

        $ids = $this->all_configs['db']->query('SELECT DISTINCT o.id
                FROM {orders} AS o
                LEFT JOIN {orders_goods} as og ON og.order_id=o.id
                LEFT JOIN {users} as u ON u.id=o.manager
                LEFT JOIN {users} as a ON a.id=o.accepter
                LEFT JOIN {clients} as c ON c.id=o.user_id
                LEFT JOIN {users_marked} as m ON m.object_id=o.id AND m.type=? AND m.user_id=?i
                LEFT JOIN {category_goods} as cg ON cg.goods_id=og.goods_id
                ?query ORDER BY o.date_add DESC ?query',
            array($icon_type, $_SESSION['id'], $query, $limit))->vars();

        if ($ids) {
            $orders_goods = $this->all_configs['db']->query('SELECT o.id as order_id, o.status, o.sale_type, o.title as product,
                  o.date_add, o.user_id, m.id as m_id, mi.id as mi_id, o.date_add as date, o.manager, o.sum_paid, o.sum, o.comment,
                  o.np_accept, og.goods_id, og.title, og.id as order_goods_id, og.count, o.note, l.location, o.courier, o.home_master_request,
                  o.discount, u.fio as h_fio, u.login as h_login, o.urgent, o.wh_id, w.title as wh_title, aw.title as aw_wh_title, og.type,
                  a.fio as a_fio, a.email as a_email, a.phone as a_phone, a.login as a_login, u.email as h_email,
                  o.fio as o_fio, o.email as o_email, o.phone as o_phone, sc.supplier_order_id, co.supplier,
                  gr.color, tp.icon, o.cashless, o.delivery_by, o.sale_type, hmr.address as hmr_address, hmr.date as hmr_date,
                  e.fio as e_fio, e.email as e_email, e.phone as e_phone, e.login as e_login, o.serial, o.referer_id, o.date_readiness, o.warranty, o.repair, o.brand_id, og.type as og_type, og.price as og_price
                FROM {orders} AS o
                LEFT JOIN {orders_goods} as og ON og.order_id=o.id
                LEFT JOIN {orders_suppliers_clients} as sc ON sc.order_goods_id=og.id AND sc.client_order_id=o.id
                LEFT JOIN {contractors_suppliers_orders} as co ON co.id=sc.supplier_order_id
                LEFT JOIN {users} as u ON u.id=o.manager
                LEFT JOIN {users} as a ON a.id=o.accepter
                LEFT JOIN {users} as e ON e.id=o.engineer
                LEFT JOIN {users_marked} as m ON m.object_id=o.id AND m.type=? AND m.user_id=?i
                LEFT JOIN {users_marked} as mi ON mi.object_id=o.id AND mi.type="oi"
                LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                LEFT JOIN {warehouses_locations} as l ON l.id=o.location_id
                LEFT JOIN {warehouses} as aw ON aw.id=o.accept_wh_id
                LEFT JOIN {warehouses_groups} as gr ON gr.id=aw.group_id
                LEFT JOIN {warehouses_types} as tp ON tp.id=aw.type_id
                LEFT JOIN {home_master_requests} as hmr ON hmr.order_id=o.id
                WHERE o.id IN (?li) ORDER BY o.date_add DESC',//o.status, o.date_add DESC
                array($icon_type, $_SESSION['id'], array_keys($ids)))->assoc();

            if ($orders_goods) {
                foreach ($orders_goods as $order) {
                    if (!array_key_exists($order['order_id'], $orders)) {
                        $orders[$order['order_id']] = $order;
                        $orders[$order['order_id']]['goods'] = array();
                        $orders[$order['order_id']]['services'] = array();
                        $orders[$order['order_id']]['ordered'] = array();
                        $orders[$order['order_id']]['finish'] = array();
                        $orders[$order['order_id']]['items'] = array();
                    }
                    if ($order['og_type'] == GOODS_TYPE_SERVICE) {
                        if (!isset($orders[$order['order_id']]['services'])) {
                            $orders[$order['order_id']]['services'] = array();
                        }
                        if ($order['goods_id'] > 0) {
                            $orders[$order['order_id']]['services'][] = array(
                                'title' => $order['title'],
                                'price' => $order['og_price'] / 100
                            );
                        }
                    } else {
                        if ($order['supplier'] > 0) {
                            $orders[$order['order_id']]['finish'][] = $order['supplier'];
                        }
                        if ($order['supplier_order_id'] > 0) {
                            $orders[$order['order_id']]['ordered'][] = $order['supplier_order_id'];
                        }
                        if ($order['order_goods_id'] > 0) {
                            $orders[$order['order_id']]['goods'][$order['order_goods_id']] = array();
                        }
                        if ($order['goods_id'] > 0) {
                            if (!array_key_exists($order['goods_id'], $orders[$order['order_id']]['items'])) {
                                $orders[$order['order_id']]['items'][$order['goods_id']]['title'] = $order['title'];
                                $orders[$order['order_id']]['items'][$order['goods_id']]['count'] = 0;
                            }
                            $orders[$order['order_id']]['items'][$order['goods_id']]['count'] += 1;
                        }
                    }
                }
            }
        }

        return $orders;
    }

    /**
     * @param array $filters
     * @return array
     */
    public function clients_orders_query($filters = array())
    {
        $count_on_page = count_on_page();//20;
        $skip = (isset($filters['p']) && $filters['p'] > 0) ? ($count_on_page * ($filters['p'] - 1)) : 0;

        // фильтр по дате
        $day_from = null;//1 . '.' . 1 . date(".Y") . ' 00:00:00';
        $day_to = null;//31 . '.' . 12 . date(".Y") . ' 23:59:59';Y-m-d H:i:s
        if (array_key_exists('df', $filters) && strtotime($filters['df']) > 0) {
            $day_from = $filters['df'] . ' 00:00:00';
        }
        if (array_key_exists('dt', $filters) && strtotime($filters['dt']) > 0) {
            $day_to = $filters['dt'] . ' 23:59:59';
        }

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
                . 'WHERE order_id = o.id AND status = ?i ?q LIMIT 1) IS NOT NULL',
                array(
                    $query,
                    $this->all_configs['configs']['order-status-rework'],
                    $date_query ? ' AND ' . $date_query : ''
                ));
        }

        if (isset($filters['co']) && !empty($filters['co'])) {
            $query = $this->all_configs['db']->makeQuery('?query AND (o.id=?i OR c.fio LIKE "%?e%" OR c.phone LIKE "%?e%")',
                array($query, intval($filters['co']), trim($filters['co']), trim($filters['co'])));
        }

        if (isset($filters['o_id']) && !empty($filters['o_id'])) {
            if (preg_match('/^[zZ]-/', trim($filters['o_id'])) === 1) {
                $orderId = preg_replace('/^[zZ]-/', '', trim($filters['o_id']));
            } else {
                $orderId = trim($filters['o_id']);
            }
            $query = $this->all_configs['db']->makeQuery('?query AND o.id=?i',
                array($query, $orderId));
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
        $mg = array_filter(explode(',', $filters['mg']));
        if (isset($filters['mg']) && count($mg) > 0) {
            if (count($mg) > 1 || !in_array(-1, $mg)) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.manager IN (?li)',
                    array($query, array_filter(explode(',', $filters['mg']))));
            }
            if (in_array(-1, $mg)) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.manager IS NULL',
                    array($query));
            }
        }
        if (isset($filters['person']) && count(array_filter(explode(',', $filters['person']))) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND c.person IN (?li)',
                array($query, array_filter(explode(',', $filters['person']))));
        }
        if (!empty($filters['manager'])) {
            $query = $this->all_configs['db']->makeQuery('
                LEFT JOIN {users} as man ON man.id = o.manager
                ?query AND man.fio LIKE "%?e%"',
                array($query, $filters['manager']));
        }
        $eng = array_filter(explode(',', $filters['eng']));
        if (isset($filters['eng']) && count($eng) > 0) {
            if (count($eng) > 1 || !in_array(-1, $eng)) {
                $orderIds = $this->all_configs['db']->query('
                    SELECT order_id 
                    FROM {orders_goods}
                    WHERE engineer in (?li)
                ', array(array_filter(explode(',', $filters['eng']))))->col();
                if (empty($orderIds)) {
                    $query = $this->all_configs['db']->makeQuery('?query AND o.engineer IN (?li)',
                        array($query, array_filter(explode(',', $filters['eng']))));
                } else {
                    $query = $this->all_configs['db']->makeQuery('?query AND (o.engineer IN (?li) OR o.id in (?li))',
                        array($query, array_filter(explode(',', $filters['eng'])), $orderIds));
                }
            }
            if (in_array(-1, $eng)) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.engineer IS NULL',
                    array($query));
            }
        }
        if (!empty($filters['engineer'])) {
            $query = $this->all_configs['db']->makeQuery('
                LEFT JOIN {users} as eng ON eng.id = o.engineer
                ?query AND eng.fio LIKE "%?e%"',
                array($query, $filters['engineer']));
        }
        $acp = array_filter(explode(',', $filters['acp']));
        if (isset($filters['acp']) && count($acp) > 0) {
            if (count($acp) > 1 || !in_array(-1, $acp)) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.accepter IN (?li)',
                    array($query, array_filter(explode(',', $filters['acp']))));
            }
            if (in_array(-1, $acp)) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.accepter IS NULL',
                    array($query));
            }
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
        if (isset($filters['rep']) && count(explode(',', $filters['rep'])) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.repair IN (?li)',
                array($query, explode(',', $filters['rep'])));
        }
        if (isset($filters['brands']) && count(explode(',', $filters['brands'])) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.brand_id IN (?li)',
                array($query, explode(',', $filters['brands'])));
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
            if (preg_match('/^[zZ]-/', trim($filters['co_id'])) === 1) {
                $orderId = preg_replace('/^[zZ]-/', '', trim($filters['co_id']));
            } else {
                $orderId = trim($filters['co_id']);
            }
            if (intval($orderId) > 0) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.id=?i',
                    array($query, intval($orderId)));
            }
            $query = $this->all_configs['db']->makeQuery('?query AND o.id=?i',
                array($query, intval($orderId)));
        }

        if (isset($filters['np']) && $filters['np'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.np_accept=?i',
                array($query, 1));
        }

        if (isset($filters['other']) && count(explode(',', $filters['other'])) > 0) {
            $other = explode(',', $filters['other']);
            if (in_array('hmr', $other)) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.home_master_request=?i',
                    array($query, 1));
            }
            if (in_array('cgd', $other)) {
                $query = $this->all_configs['db']->makeQuery('?query AND NOT o.courier IS NULL',
                    array($query));
            }
            if (in_array('urgent', $other)) {
                $query = $this->all_configs['db']->makeQuery('?query AND o.urgent=1 AND NOT o.status in (?li)',
                    array($query, $this->all_configs['configs']['order-statuses-urgent-not-show']));
            }
            if (in_array('pay', $other)) {
                $query = $this->all_configs['db']->makeQuery('?query AND (o.sum_paid + o.discount) < o.sum AND o.status in (?li)',
                    array($query, $this->all_configs['configs']['order-statuses-debts']));
            }
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
            foreach ($this->all_configs['configs']['payment-msg'] as $payment => $values) {
                if ($values['pay'] == 'pre') {
                    $payments[] = $payment;
                }
            }
            if (count($payments) > 0) {
                $query = $this->all_configs['db']->makeQuery('RIGHT JOIN {chains_headers} as h ON h.order_id=o.id
                        ?query AND (o.payment IN (?list) OR o.status IN (?li))',
                    array(
                        $query,
                        array_values($payments),
                        array(
                            $this->all_configs['configs']['order-status-wait-pay'],
                            $this->all_configs['configs']['order-status-part-pay']
                        )
                    ));
            } else {
                $query = $this->all_configs['db']->makeQuery('RIGHT JOIN {chains_headers} as h ON h.order_id=o.id
                        ?query AND o.status IN (?li)',
                    array(
                        $query,
                        array(
                            $this->all_configs['configs']['order-status-wait-pay'],
                            $this->all_configs['configs']['order-status-part-pay']
                        )
                    ));
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
        if (isset($filters['selfdelivery'])) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.delivery_by=?i ',
                array($query, DELIVERY_BY_SELF));
        }
        if (isset($filters['cashless'])) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.cashless=1 ',
                array($query));
        }
        if (isset($filters['courier'])) {
            $query = $this->all_configs['db']->makeQuery('?query AND o.delivery_by=?i ',
                array($query, DELIVERY_BY_COURIER));
        }
        if (isset($filters['cats'])) {
            $cats = $this->get_models(explode('-', $filters['cats']));
            if (empty($cats)) {
                $cats = explode('-', $filters['cats']);
            }
            $query = $this->all_configs['db']->makeQuery('?query AND o.category_id in (?li)',
                array($query, $cats));
        }

        $userId = $this->getUserId();
        $onlyHisOrders = $this->all_configs['db']->query('SELECT show_only_his_orders FROM {users} WHERE id=?i',
            array($userId))->el();
        if ($onlyHisOrders) {
            $query = $this->all_configs['db']->makeQuery('?query AND (o.manager=?i OR o.accepter=?i OR o.engineer=?i)',
                array($query, $userId, $userId, $userId));
        }
        return array(
            'query' => $query,
            'count_on_page' => $count_on_page,
            'skip' => $skip,
        );
    }

    /**
     * @param $parents
     * @return mixed
     */
    public function get_models($parents)
    {
        $child = $this->get_all_child($parents);
        return $this->all_configs['db']->query('
        SELECT id
        FROM {categories}
        WHERE avail=1 AND parent_id in (?li)
        ', array($child))->col();
    }

    /**
     * @param $parents
     * @return array
     */
    public function get_all_child_with_models($parents)
    {
        $all = $this->all_configs['db']->query('
        SELECT id, parent_id 
        FROM {categories}
        WHERE avail=1 
        ', array())->assoc();
        return array_merge($parents, $this->get_child($all, $parents));
    }

    /**
     * @param $parents
     * @return array
     */
    public function get_all_child($parents)
    {
        $all = $this->all_configs['db']->query('
        SELECT id, parent_id 
        FROM {categories}
        WHERE avail=1 AND id in (SELECT distinct(parent_id) FROM {categories} )
        ', array())->assoc();
        return array_merge($parents, $this->get_child($all, $parents));
    }

    /**
     * @param $array
     * @param $parents
     * @return array
     */
    private function get_child($array, $parents)
    {
        $child = array();
        foreach ($array as $item) {
            if (!in_array($item['parent_id'], $parents)) {
                continue;
            }
            $child[] = $item['id'];
            $child = array_merge($child, $this->get_child($array, array($item['id'])));
        }
        return $child;
    }

    /**
     * @param        $query
     * @param        $skip
     * @param        $count_on_page
     * @param string $icon_type
     * @return array
     */
    public function get_suppliers_orders($query, $skip, $count_on_page, $icon_type = 'so')
    {
        $orders = array();

        if (array_key_exists('erp-contractors-use-for-suppliers-orders',
                $this->all_configs['configs']) && count($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders']) > 0
        ) {
            if (isset($_GET['wg']) && !empty($_GET['wg'])) {

                $wga = explode(',', $_GET['wg']);

                $orders_ids = $this->all_configs['db']->query('SELECT DISTINCT o.id
                    FROM {contractors_suppliers_orders} as o
                    LEFT JOIN {users_marked} as m ON m.object_id=o.id AND m.type=? AND m.user_id=?i
                    LEFT JOIN {warehouses_goods_items} as i ON i.supplier_order_id=o.id '
                    . 'LEFT JOIN {orders_suppliers_clients} AS osc ON osc.supplier_order_id=o.id '
                    . 'LEFT JOIN {orders} AS oo ON osc.client_order_id=oo.id '
                    . 'LEFT JOIN {warehouses} AS w ON i.wh_id=w.id '
                    . 'INNER JOIN {warehouses_groups} AS wg ON wg.id=w.group_id AND w.group_id IN (?li) '
                    . '?query GROUP BY o.id ORDER BY o.date_add DESC LIMIT ?i, ?i',//o.parent_id DESC,
                    array($icon_type, $_SESSION['id'], $wga, $query, $skip, $count_on_page))->vars();

            } else {
                $orders_ids = $this->all_configs['db']->query('SELECT DISTINCT o.id
                    FROM {contractors_suppliers_orders} as o
                    LEFT JOIN {orders_suppliers_clients} AS osc ON osc.supplier_order_id=o.id 
                    LEFT JOIN {orders} AS oo ON osc.client_order_id=oo.id 
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
                    array(
                        array_values($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders']),
                        $icon_type,
                        $_SESSION['id'],
                        array_keys($orders_ids)
                    ))->iassoc();


                foreach ($data as $o) {
                    if (!array_key_exists($o['id'], $orders)) {
                        $orders[$o['id']] = $o;
                        $orders[$o['id']]['items'] = array();
                    }
                    if ($o['item_id'] > 0) {
                        $orders[$o['id']]['items'][$o['item_id']] = array(
                            'item_id' => $o['item_id'],
                            'date_checked' => $o['date_checked'],
                            'serial' => $o['serial']
                        );
                    }
                }
            }
        }

        return $orders;
    }


    /**
     * @param $p_id
     * @return mixed
     */
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

    /**
     * вызываем при любом движении изделия
     * */
    // $move_type: 1 - Перемещение на склад, 2 - Перемещен на склад, 0 - не определено
    /**
     * @param        $wh_id
     * @param        $location_id
     * @param null   $goods_id
     * @param null   $item_id
     * @param null   $order_id
     * @param null   $chain_id
     * @param string $msg
     * @param null   $chain_body_id
     * @param int    $move_type
     */
    function move_product_item(
        $wh_id,
        $location_id,
        $goods_id = null,
        $item_id = null,
        $order_id = null,
        $chain_id = null,
        $msg = '',
        $chain_body_id = null,
        $move_type = 0
    ) {
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
    /**
     * @param     $item_id
     * @param     $order_id
     * @param     $wh_id
     * @param     $location_id
     * @param     $chain_id
     * @param     $msg
     * @param     $chain_body_id
     * @param int $move_type
     * @throws Exception
     */
    function stock_moves($item_id, $order_id, $wh_id, $location_id, $chain_id, $msg, $chain_body_id, $move_type = 0)
    {
        if (($item_id > 0 || $order_id > 0) && $wh_id > 0) {
            // история перемещений
            $move_id = $this->all_configs['db']->query('INSERT INTO {warehouses_stock_moves}
                (item_id, user_id, wh_id, comment, chain_id, chain_body_id, order_id, location_id) VALUES (?n, ?i, ?i, ?, ?n, ?n, ?n, ?n)',
                array($item_id, $_SESSION['id'], $wh_id, $msg, $chain_id, $chain_body_id, $order_id, $location_id),
                'id');

            if ($order_id > 0) {
                $this->all_configs['db']->query('UPDATE {orders} SET location_id=?n, wh_id=?i WHERE id=?i',
                    array($location_id, $wh_id, $order_id));
            }

            // смотрим привязку к цепочке перемещений - новая логистика
            // инициируем item_move только когда операция "Перемещен на склад"
            if ($move_type == 2) {
                // может быть сразу $order_id и $item_id
                if ($order_id) {
                    get_service('logistic')->item_move($order_id, LOGISTIC_TYPE_IS_ORDER, $move_id, $wh_id, $location_id);
                }
                if ($item_id) {
                    get_service('logistic')->item_move($item_id, LOGISTIC_TYPE_IS_ITEM, $move_id, $wh_id, $location_id);
                }
            }
        }
    }

    /**
     * @param $goods_id
     */
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

    }

    /**
     * @param $goods_id
     */
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

    /**
     * @param $goods_id
     */
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

    /**
     * @param      $order_id
     * @param null $type
     * @param null $order_product_id
     * @return null
     */
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
                      g.warehouse_type, o.warehouse_type as order_warehouse_type, g.warranty as warranty
                    FROM {orders} as co, {orders_goods} as g
                    LEFT JOIN {orders_suppliers_clients} as l ON l.order_goods_id=g.id
                    LEFT JOIN {warehouses_goods_items} as i ON i.id=g.item_id
                    LEFT JOIN {contractors_suppliers_orders} as o ON o.id=l.supplier_order_id
                    WHERE g.order_id IN (?li) AND co.id=g.order_id ?query ORDER by id DESC',
                array($orders, $where))->assoc('id');

            if ($order_product_id !== null && $goods) {
                $goods = isset($goods[$order_product_id]) ? $goods[$order_product_id] : null;
            }
        }

        return $goods;
    }
}
