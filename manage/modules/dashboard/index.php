<?php

require_once __DIR__.'/../../View.php';
require_once __DIR__.'/../../Session.php';

class dashboard
{
    const PREPAYMENT_TRANSACTION_TYPE = 10;
    /** @var View  */
    protected $view;
    /** @var ChartUtils  */
    protected $utils;

    /**
     * dashboard constructor.
     * @param $all_configs
     * @param $lang
     * @param $def_lang
     */
    function __construct($all_configs, $lang, $def_lang)
    {
        global $input;
        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->all_configs = $all_configs;
        $this->db = $this->all_configs['db'];
        $this->arrequest = $this->all_configs['arrequest'];
        $this->prefix = $this->all_configs['prefix'];
        $this->view = new View($all_configs);
        $this->utils = new ChartUtils($all_configs);

        if ($this->all_configs['oRole']->hasPrivilege('dashboard')) {
            $this->gen_filter_block();
            $this->gen_content();
        } else {
            $input['dashboard_class'] = 'hidden';
            $input['mcontent'] = '<p class="text-center m-t-lg">' . l('Администрирование') . '</p>';
        }
    }

    /**
     *
     */
    private function gen_filter_block()
    {
        global $input;
        $start = isset($_GET['ds']) && strtotime($_GET['ds']) > 0 ? date("j/n/y",
            strtotime($_GET['ds'])) : date("1/n/y");
        $end = isset($_GET['de']) && strtotime($_GET['de']) > 0 ? date("j/n/y", strtotime($_GET['de'])) : date("j/n/y");
        $input['filter'] = $this->view->renderFile('dashboard/get_filter_block', array(
            'start' => $start,
            'end' => $end
        ));
    }

    /**
     *
     */
    private function gen_content()
    {
        global $input;
        global $input_html;

        $conversion = $this->get_conversion();
        $input['conversion_1'] = $conversion[0];
        $input['conversion_2'] = $conversion[1];
        $input['conversion_3'] = $conversion[2];

        $conv_chart = $this->get_conv_chart();
        $input['line_chart_data_orders'] = $conv_chart['orders'];
        $input['line_chart_data_calls'] = $conv_chart['calls'];
        $input['line_chart_data_visitors'] = $conv_chart['visitors'];
        $input['tick_size'] = $this->utils->tickSize();
        $input['init_visitors'] = $conv_chart['init_visitors'] ? 'true' : 'false';

        $input_html['branch_chart'] = $this->get_branch_chart();
        $input_html['repair_chart'] = $this->get_repair_chart();
        $input_html['order_types_filter'] = $this->view->renderFile('dashboard/order_types_filter', array(
            'current' => $this->utils->getOrderOptions()
        ));

        $input['currency'] = viewCurrency('symbol');
        $input['avg_check'] = $this->get_avg_check();
        $input['workshops_stats'] = $this->get_workshops_stats();
        $input['engineers_stats'] = $this->get_engineer_stats();
        $cash = $this->get_cash();
        $input['today_cash'] = $cash['today'];
        $input['period_cash'] = $cash['period'];
        $input['cash_chart'] = $cash['cash_chart'];
    }

    /**
     * @return array
     */
    private function get_conv_chart()
    {
        $calls = $this->db->query("SELECT ?q, count(*) as c "
            . "FROM {crm_calls} "
            . "WHERE ?q GROUP BY d", array($this->utils->selectDate('', 'date'), $this->utils->makeFilters('date')))->vars();
        $visitors = $this->db->query("SELECT ?q, SUM(users) as c FROM {crm_analytics} "
            . "WHERE ?q GROUP BY d", array($this->utils->selectDate('', 'date'), $this->utils->makeFilters('date')))->vars();
        list($warrantyQuery, $typeQuery) = $this->utils->makeQueryForTypeAndWarranty();
        $orders = $this->db->query("SELECT ?q, count(*) as c "
            . "FROM {orders} "
            . "WHERE ?q ?q ?q GROUP BY d", array(
            $this->utils->selectDate(),
            $this->utils->makeFilters('date_add'),
            $typeQuery,
            $warrantyQuery
        ))->vars();
        $calls_js = array();
        $orders_js = array();
        $visitors_js = array();
        $period = $this->utils->getDatePeriod();
        $init_visitors = false;
        foreach ($period as $dt) {
            $date = $dt->format($this->utils->getDateFormat());
            if (!empty($visitors[$date])) {
                $init_visitors = true;
            }
            $d_js = $this->utils->getDJs($dt);
            $calls_js[$date] = '[' . $d_js . ',' . (isset($calls[$date]) ? $calls[$date] : 0) . ']';
            $orders_js[$date] = '[' . $d_js . ',' . (isset($orders[$date]) ? $orders[$date] : 0) . ']';
            $visitors_js[$date] = '[' . $d_js . ',' . (isset($visitors[$date]) ? $visitors[$date] : 0) . ']';
        }
        return array(
            'calls' => implode(',', $calls_js),
            'orders' => implode(',', $orders_js),
            'visitors' => implode(',', $visitors_js),
            'init_visitors' => $init_visitors
        );
    }

    /**
     * @param $orders
     * @param $by
     * @return array
     */
    private function prepare($orders, $by)
    {
        $result = array();
        foreach ($orders as $order) {
            if (!isset($result[$order[$by]])) {
                $result[$order[$by]] = array();
            }
            $result[$order[$by]][$order['d']] = $order['c'];
        }
        return $result;
    }


    /**
     * @return string
     */
    private function get_branch_chart()
    {
        $branches = $this->db->query('SELECT id, `name` as title, color FROM {warehouses_groups}', array())->assoc('id');
        $period = $this->utils->getDatePeriod();

        $query = '';
        if (empty($_POST['branches_id'])) {
            $selected = (array)Session::getInstance()->get('chart.selected.branches');
        } else {
            $selected = $_POST['branches_id'];
            Session::getInstance()->set('chart.selected.branches', $selected);
        }
        if (!empty($selected)) {
            $query = $this->db->makeQuery('AND wrh.group_id in (?li)', array($selected));
        }
        list($warrantyQuery, $typeQuery) = $this->utils->makeQueryForTypeAndWarranty('o');
        $orders = $this->prepare($this->db->query("SELECT ?q, count(*) as c, wrh.group_id as wh"
            . " FROM {orders} o"
            . " RIGHT JOIN {warehouses} wrh ON wrh.id = o.accept_wh_id"
            . " WHERE ?q ?q ?q ?q AND o.accept_wh_id IS NOT NULL  GROUP BY wh, d ",
            array($this->utils->selectDate(), $this->utils->makeFilters('date_add'), $query, $warrantyQuery, $typeQuery))->assoc(), 'wh');

        $result = array();
        $ticks = array();
        foreach ($period as $dt) {
            $result = $this->utils->formatForChart($dt, $orders, $result);
            $ticks = $this->utils->getTicks($dt, $ticks);
        }
        return $this->view->renderFile('dashboard/branch_chart', array(
            'tickSize' => $this->utils->tickSize(),
            'orders' => $result,
            'branches' => $branches,
            'selected' => $selected,
            'ticks' => $ticks
        ));
    }

    /**
     * @return string
     */
    private function get_repair_chart()
    {
        $models = $this->db->query('SELECT cat.id, cat.title FROM {categories} AS cat'
        .' LEFT JOIN ( SELECT DISTINCT parent_id FROM {categories} ) AS sub ON cat.id = sub.parent_id'
        .' WHERE cat.avail=1 AND (sub.parent_id IS NULL OR sub.parent_id = 0)',
            array())->assoc('id');
        $categories = $this->db->query('SELECT id, title FROM {categories} WHERE NOT id in (?li) AND avail=1',
            array(array_keys($models)))->assoc('id');
        $items = $this->db->query('SELECT id, title FROM {goods} WHERE avail=1', array())->assoc('id');

        if (empty($_POST['categories_id']) && empty($_POST['models_id']) && empty($_POST['goods_id'])) {
            $selectedItems = (array)Session::getInstance()->get('chart.selected.items');
            $selectedModels = (array)Session::getInstance()->get('chart.selected.models');
            $selectedCategories = (array)Session::getInstance()->get('chart.selected.categories');
        } else {
            $selectedItems = empty($_POST['goods_id']) ? array() : $_POST['goods_id'];
            Session::getInstance()->set('chart.selected.items', $selectedItems);
            $selectedModels = empty($_POST['models_id']) ? array() : $_POST['models_id'];
            Session::getInstance()->set('chart.selected.models', $selectedModels);
            $selectedCategories = empty($_POST['categories_id']) ? array() : $_POST['categories_id'];
            Session::getInstance()->set('chart.selected.categories', $selectedCategories);
        }

        list($warrantyQuery, $typeQuery) = $this->utils->makeQueryForTypeAndWarranty('o');
        $orders = array();
        $ordersByCategory = array();
        $ordersByModels = array();
        if (!empty($selectedItems)) {
            $orders = $this->prepare($this->db->query("SELECT ?q, count(*) as c, goods_id as good "
                . " FROM {orders} o "
                . " JOIN {orders_goods} as og ON og.order_id = o.id "
                . " WHERE ?q ?q ?q AND goods_id in (?li) GROUP BY good, d ",
                array(
                    $this->utils->selectDate('o'),
                    $this->utils->makeFilters('o.date_add'),
                    $warrantyQuery,
                    $typeQuery,
                    $selectedItems
                ))->assoc(), 'good');
        }
        if (!empty($selectedCategories)) {
            $ordersByCategory = $this->prepare($this->db->query("SELECT ?q, count(*) as c, if(cat.parent_id = 0, cat.id, cat.parent_id) as p_id "
                . " FROM {orders} o"
                . " JOIN {orders_goods} as og ON og.order_id = o.id "
                . " JOIN {category_goods} as cg ON og.goods_id = cg.goods_id "
                . " JOIN {categories} as cat ON cat.id = cg.category_id "
                . " WHERE ?q ?q ?q AND cat.id = o.category_id AND (cat.id in (?li) OR cat.parent_id in (?li)) GROUP BY p_id, d ",
                array(
                    $this->utils->selectDate('o'),
                    $this->utils->makeFilters('o.date_add'),
                    $warrantyQuery,
                    $typeQuery,
                    $selectedCategories,
                    $selectedCategories
                ))->assoc(), 'p_id');
        }
        if (!empty($selectedModels)) {
            $ordersByModels = $this->prepare($this->db->query("SELECT ?q, count(*) as c, o.category_id as category_id "
                . " FROM {orders} o "
                . " WHERE ?q ?q ?q AND o.category_id in (?li) GROUP BY category_id, d ",
                array(
                    $this->utils->selectDate('o'),
                    $this->utils->makeFilters('o.date_add'),
                    $warrantyQuery,
                    $typeQuery,
                    $selectedModels
                ))->assoc(),
                'category_id');
        }

        $period = $this->utils->getDatePeriod();
        $resultByItems = array();
        $resultByCategories = array();
        $resultByModels = array();
        $ticks = array();
        foreach ($period as $dt) {
            $resultByItems = $this->utils->formatForChart($dt, $orders, $resultByItems);
            $resultByModels = $this->utils->formatForChart($dt, $ordersByModels, $resultByModels);
            $resultByCategories = $this->utils->formatForChart($dt, $ordersByCategory, $resultByCategories);
            $ticks = $this->utils->getTicks($dt, $ticks);
        }
        return $this->view->renderFile('dashboard/repair_chart', array(
            'categories' => $categories,
            'models' => $models,
            'items' => $items,
            'byItems' => $resultByItems,
            'byModels' => $resultByModels,
            'byCategories' => $resultByCategories,
            'selectedItems' => $selectedItems,
            'selectedModels' => $selectedModels,
            'selectedCategories' => $selectedCategories,
            'tickSize' => $this->utils->tickSize(),
            'ticks' => $ticks
        ));
    }

    /**
     * @return array
     */
    private function get_conversion()
    {
        $calls = $this->db->query("SELECT count(*) FROM {crm_calls} "
            . "WHERE ?q", array($this->utils->makeFilters('date')))->el();
        $visitors = $this->db->query("SELECT SUM(users) FROM {crm_analytics} "
            . "WHERE ?q", array($this->utils->makeFilters('date')))->el();
        $orders = $this->db->query("SELECT count(*) FROM {orders} "
            . "WHERE ?q", array($this->utils->makeFilters('date_add')))->el();
        // посетители / звонки
        $conv_1 = $visitors ? $calls / $visitors : 0;
        // звонки / заказы 
        $conv_2 = $calls ? $orders / $calls : 0;
        // посетители / заказы 
        $conv_3 = $visitors ? $orders / $visitors : 0;
        return array(
            $this->percent_format($conv_1 * 100),
            $this->percent_format($conv_2 * 100),
            $this->percent_format($conv_3 * 100)
        );
    }

    /**
     * @return mixed
     */
    private function get_avg_check()
    {
        $query_filter = $this->utils->makeFilters('o.date_add');
        $avg_check = $this->db->query("
            SELECT 
                (SUM(IF(o.sum_paid > 0 AND o.type <> 1, o.sum_paid, NULL)) / COUNT(IF(o.sum_paid > 0 AND o.type <> 1, o.id, NULL))) / 100 as avg_check
            FROM {orders} as o
            WHERE ?q 
        ", array($query_filter))->el();
        $avg_check = $this->price_format($avg_check);
        return $avg_check;
    }

    /**
     * @return string
     */
    private function get_workshops_stats()
    {
        $stats = '';
        $statuses = array(
            40 => $this->all_configs['configs']['order-status'][40]['name'], // выдан
            25 => $this->all_configs['configs']['order-status'][25]['name'], // выдан без ремонта
            15 => $this->all_configs['configs']['order-status'][15]['name'], // клиент отказался
            50 => $this->all_configs['configs']['order-status'][50]['name']  // переведен в доноры
        );
        $query_filter = $this->utils->makeFilters('date_add');
        $all_orders = $this->db->query("SELECT count(*) FROM {orders} "
            . "WHERE ?q AND status IN(?l)", array($query_filter, array_keys($statuses)), 'el');
        foreach ($statuses as $status => $name) {
            $name = l($name);
            $orders = $this->db->query("SELECT count(*) "
                . "FROM {orders} WHERE ?q AND status = ?i", array($query_filter, $status), 'el');
            $p = $all_orders > 0 ? $this->percent_format($orders / $all_orders * 100) : 0;
            $stats .= $this->view->renderFile('dashboard/get_workshops_stats', array(
                'name' => $name,
                'percents' => $p,
                'count' => $orders
            ));
        }
        return $stats;
    }

    /**
     * @return mixed|string
     */
    private function get_engineer_stats()
    {
        $query_filter = $this->utils->makeFilters('o.date_add');
        $orders = $this->db->query("SELECT engineer, IF(u.fio!='',u.fio,u.login) as fio, "
            . "count(o.id) as orders "
            . "FROM {orders} as o "
            . "LEFT JOIN {users} as u ON u.id = o.engineer "
            . "WHERE ?q AND engineer > 0 AND status = ?i AND sum_paid > 0 GROUP BY engineer "
            . "ORDER BY orders DESC", array($query_filter, $this->all_configs['configs']['order-status-issued']),
            'assoc');
        $all_orders = 0;
        foreach ($orders as $ord) {
            $all_orders += $ord['orders'];
        }
        return $this->view->renderFile('dashboard/get_engineer_stats', array(
            'allOrders' => $all_orders,
            'orders' => $orders,
            'constructor' => $this
        ));
    }

    /**
     * @return array
     */
    private function get_cash()
    {
        $query_filter = $this->utils->makeFilters('date_transaction');
        $today_date = date('Y-m-d 00:00:00');
        $today_date_to = date('Y-m-d 23:59:59');
        $today_cash = $this->db->query("SELECT SUM((IF(transaction_type=2,value_to,0))-IF(transaction_type=1,value_from,0))/100 as c "
            . "FROM {cashboxes_transactions} "
            . "WHERE date_transaction BETWEEN ? AND ? "
            . "AND transaction_type = 2 "
            . "AND type NOT IN (1, 2, 3, 4, 6) "
            . "AND client_order_id > 0 ",
            array($today_date, $today_date_to), 'el');
        $chart_cash = $this->db->query("SELECT "
            . "?q,"
            . "SUM((IF(transaction_type=2,value_to,0))-IF(transaction_type=1,value_from,0))/100 as c "
            . "FROM {cashboxes_transactions} "
            . "WHERE ?q AND transaction_type = 2 "
            . "AND type NOT IN (1, 2, 3, 4, 6) "
            . "AND client_order_id > 0 "
            . "GROUP BY d", array($this->utils->selectDate('', 'date_transaction'),$query_filter))->vars();
        $cash_chart_js = array();
        $period = $this->utils->getDatePeriod();
        $period_cash = 0;
        foreach ($period as $dt) {
            $date = $dt->format($this->utils->getDateFormat());
            $d_js = $this->utils->getDJs($dt);
            $cash = isset($chart_cash[$date]) ? number_format($chart_cash[$date], 2, '.', '') : 0;
            $period_cash += $cash;
            $cash_chart_js[] = '[' . $d_js . ',' . $cash . ']';
        }
        return array(
            'today' => $this->price_format($today_cash),
            'period' => $this->price_format($period_cash, false),
            'cash_chart' => implode(',', $cash_chart_js)
        );
    }

    /**
     * @param      $price
     * @param bool $show_dec
     * @return mixed
     */
    public function price_format($price, $show_dec = true)
    {
        return str_replace('.00', '', number_format($price, $show_dec ? 2 : 0, '.', ' '));
    }

    /**
     * @param $p
     * @return mixed
     */
    public function percent_format($p)
    {
        return str_replace('.0', '', number_format($p, 1, '.', ''));
    }
}

class ChartUtils
{
    protected $all_configs;
    protected $db;
    /** @var  DateTime */
    protected $start;
    /** @var  DateTime */
    protected $end;
    /** @var  DateInterval */
    protected $diff;

    public function __construct($all_configs)
    {
        $this->all_configs = $all_configs;
        $this->db = $this->all_configs['db'];
        $this->prepareDate();
    }

    /**
     * @param string $prefix
     * @return array
     */
    public function makeQueryForTypeAndWarranty($prefix = '')
    {
        if (!empty($prefix)) {
            $prefix = $prefix . '.';
        }
        $options = $this->getOrderOptions();
        $warrantyQuery = '';
        if(isset($options['warranty']) && $options['warranty']) {
            $warrantyQuery .= $this->db->makeQuery(" AND {$prefix}warranty = 1", array());
        }
        if(isset($options['not-warranty']) && $options['not-warranty']) {
            $warrantyQuery .= $this->db->makeQuery(" AND ({$prefix}warranty = 0 OR {$prefix}warranty IS NULL)", array());
        }

        $typeQuery = empty($options['types']) ? $this->db->makeQuery("AND {$prefix}type = 0", array())
            : $this->db->makeQuery("AND {$prefix}type in (?li)", array($options['types']));
        return array(
            $warrantyQuery,
            $typeQuery
        );
    }

    /**
     * @param DateTime $dt
     * @param $orders
     * @param $result
     * @return mixed
     */
    public function formatForChart($dt, $orders, $result)
    {
        $date = $dt->format($this->getDateFormat());
        $d_js = $this->getDJs($dt);
        foreach ($orders as $wh => $order) {
            if(empty($result[$wh])) {
                $result[$wh] = array();
            }

            if (isset($order[$date])) {
                $result[$wh][$date] = '[' . $d_js . ',' . $order[$date] . ']';
            } else {
                $result[$wh][$date] = '[' . $d_js . ',' . 0 . ']';
            }
        }
        return $result;
    }

    /**
     * @param string $prefix
     * @return mixed
     */
    public function selectDate($prefix = '', $field = 'date_add')
    {
        if (!empty($prefix)) {
            $prefix = $prefix . '.';
        }
        switch (true) {
            case isset($_GET['month']):
                if ($this->diff > 2 * 30) {
                    return $this->db->makeQuery("DATE_FORMAT({$prefix}{$field}, '%Y-%m') as d", array());
                }
            case isset($_GET['week']):
                if ($this->diff >= 30) {
                    return $this->db->makeQuery("DATE_FORMAT({$prefix}{$field}, '%Y-%u') as d", array());
                }
        }
        return $this->db->makeQuery("DATE_FORMAT({$prefix}{$field}, '%Y-%m-%d') as d", array());
    }

    /**
     * @return string
     */
    public function getDateFormat()
    {
        switch (true) {
            case isset($_GET['month']):
                if ($this->diff > 2 * 30) {
                    return 'Y-m';
                }
            case isset($_GET['week']):
                if ($this->diff >= 30) {
                    return 'Y-W';
                }
        }
        return 'Y-m-d';
    }

    /**
     * @return DatePeriod
     */
    public function getDatePeriod()
    {
        $di = function($diff) {
            $error = 0;
            switch (true) {
                case isset($_GET['month']):
                    if ($diff > 2 * 30) {
                        return new DateInterval('P1M');
                    }
                    FlashMessage::set(l('Шаг в 1 мес. можно применить на диапазоне не менее 60 дней. Увеличте диапазон дат'),
                        FlashMessage::WARNING);
                    $error = 1;
                case isset($_GET['week']):
                    if ($diff >= 30) {
                        return new DateInterval('P1W');
                    }
                    if ($error == 0) {
                        FlashMessage::set(l('Шаг в 1 неделю можно применить на диапазоне не менее 30 дней. Увеличте диапазон дат'),
                            FlashMessage::WARNING);
                    }
                default:
                    $di = new DateInterval('P1D');
            }
            return $di;
        };
        $period = new DatePeriod($this->start, $di($this->diff), $this->end);
        return $period;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        $date_start = isset($_GET['ds']) && strtotime($_GET['ds']) > 0 ? $_GET['ds'] : date('Y-m-01');
        $date_end = isset($_GET['de']) && strtotime($_GET['de']) > 0 ? $_GET['de'] : date('Y-m-d');
        return array(
            'date_start' => $date_start,
            'date_end' => $date_end
        );
    }

    /**
     * @return mixed
     */
    public function getOrderOptions()
    {
        if (!empty($_POST) && empty($_POST['types'])) {
            return Session::getInstance()->get('dashboard.order.types');
        } else {
            $options = array(
                'types' => array(),
                'warranty' => array(),
            );
            if(in_array('repair', $_POST['types'])) {
                $options['types'][] = 0;
            }
            if(in_array('sale', $_POST['types'])) {
                $options['types'][] = 3;
            }
            if(in_array('warranty', $_POST['types'])) {
                $options['warranty'][] = 1;
            }
            if(in_array('not-warranty', $_POST['types'])) {
                $options['not-warranty'][] = 1;
            }

            Session::getInstance()->set('dashboard.order.types', $options);
        }
        return $options;
    }

    private function prepareDate()
    {
        $filters = $this->getFilters();
        $this->start = new DateTime($filters['date_start']);
        $this->end = new DateTime($filters['date_end']);
        $this->end->modify('+1 day');
        $this->diff = $this->start->diff($this->end)->format('%a');
    }

    /**
     * @param $date_field
     * @return string
     */
    public function makeFilters($date_field)
    {
        $filters = $this->getFilters();
        $query = '';
        if ($filters && !empty($filters['date_start']) && strtotime($filters['date_start']) > 0) {
            $query = $this->db->makeQuery('?query AND DATE_FORMAT(' . $date_field . ', "%Y-%m-%d") > ?',
                array($query, $filters['date_start']));
        }

        if ($filters && !empty($filters['date_end']) && strtotime($filters['date_end']) > 0) {
            $query = $this->db->makeQuery('?query AND DATE_FORMAT(' . $date_field . ', "%Y-%m-%d") <= ?',
                array($query, $filters['date_end']));
        }
        return ' 1=1 ' . $query;
    }

    /**
     * @return int
     */
    public function tickSize()
    {
        switch (true) {
            case isset($_GET['month']):
                if ($this->diff > 2 * 30) {
                    return 30;
                }
            case isset($_GET['week']):
                if ($this->diff >= 30) {
                    return 7;
                }
        }
        return 1;
    }

    /**
     * @param DateTime $dt
     * @return string
     */
    public function getDJs($dt)
    {
        switch (true) {
            case isset($_GET['month']):
                if ($this->diff > 2 * 30) {
                    $timestamp = strtotime('last day of this month', $dt->getTimestamp());
                    break;
                }
            case isset($_GET['week']):
                if ($this->diff >= 30) {
                    $timestamp = strtotime('monday', $dt->getTimestamp());
                    break;
                }
            default:
                $timestamp = $dt->getTimestamp();
        }
        return 'gd' . date('(Y,n,j)', $timestamp);
    }

    public function getMonday()
    {
        return strtotime('monday', $this->start->getTimestamp());
    }

    /**
     * @param $dt
     * @param $ticks
     * @return array
     */
    public function getTicks($dt, $ticks)
    {
        switch (true) {
            case isset($_GET['month']):
                if ($this->diff > 2 * 30) {
                    $timestamp = strtotime('last day of this month', $dt->getTimestamp());
                    break;
                }
            case isset($_GET['week']):
                if ($this->diff >= 30) {
                    $timestamp = strtotime('monday', $dt->getTimestamp());
                    break;
                }
            default:
                $timestamp = $dt->getTimestamp();
        }
        $ticks[] = ($timestamp + 2*3600)* 1000 ;
        return $ticks;
    }
}