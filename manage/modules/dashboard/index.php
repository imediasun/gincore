<?php

require_once __DIR__.'/../../View.php';
require_once __DIR__.'/../../Session.php';

class dashboard
{
    const PREPAYMENT_TRANSACTION_TYPE = 10;
    /** @var View  */
    protected $view;

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
     * @return array
     */
    private function get_filters()
    {
        $date_start = isset($_GET['ds']) && strtotime($_GET['ds']) > 0 ? $_GET['ds'] : date('Y-m-01');
        $date_end = isset($_GET['de']) && strtotime($_GET['de']) > 0 ? $_GET['de'] : date('Y-m-d');
        return array(
            'date_start' => $date_start,
            'date_end' => $date_end
        );
    }

    /**
     * @param $date_field
     * @return string
     */
    private function make_filters($date_field)
    {
        $filters = $this->get_filters();
        $query = '';
        if ($filters && !empty($filters['date_start']) && strtotime($filters['date_start']) > 0) {
            $query = $this->db->makeQuery('?query AND DATE_FORMAT(' . $date_field . ', "%Y-%m-%d")>=?',
                array($query, $filters['date_start']));
        }

        if ($filters && !empty($filters['date_end']) && strtotime($filters['date_end']) > 0) {
            $query = $this->db->makeQuery('?query AND DATE_FORMAT(' . $date_field . ', "%Y-%m-%d")<=?',
                array($query, $filters['date_end']));
        }
        return ' 1=1 ' . $query;
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
        $input['init_visitors'] = $conv_chart['init_visitors'] ? 'true' : 'false';

        $input_html['branch_chart'] = $this->get_branch_chart();
        $input_html['repair_chart'] = $this->get_repair_chart();

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
     * @return DatePeriod
     */
    private function get_date_period()
    {
        $filters = $this->get_filters();
        $a = new DateTime($filters['date_start']);
        $b = new DateTime($filters['date_end']);
        $b->modify('+1 day');
        $period = new DatePeriod($a, new DateInterval('P1D'), $b);
        return $period;
    }

    /**
     * @return array
     */
    private function get_conv_chart()
    {
        $calls = $this->db->query("SELECT DATE_FORMAT(date, '%Y-%m-%d') as d, count(*) as c "
            . "FROM {crm_calls} "
            . "WHERE ?q GROUP BY d", array($this->make_filters('date')))->vars();
        $visitors = $this->db->query("SELECT date as d, SUM(users) as c FROM {crm_analytics} "
            . "WHERE ?q GROUP BY d", array($this->make_filters('date')))->vars();
        $orders = $this->db->query("SELECT DATE_FORMAT(date_add, '%Y-%m-%d') as d, count(*) as c "
            . "FROM {orders} "
            . "WHERE ?q AND type = 0 GROUP BY d", array($this->make_filters('date_add')))->vars();
        $calls_js = array();
        $orders_js = array();
        $visitors_js = array();
        $period = $this->get_date_period();
        $init_visitors = false;
        foreach ($period as $dt) {
            $date = $dt->format('Y-m-d');
            if (!empty($visitors[$date])) {
                $init_visitors = true;
            }
            $d_js = 'gd(' . $dt->format('Y') . ',' . $dt->format('n') . ',' . $dt->format('j') . ')';
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
     * @param $dt
     * @param $orders
     * @param $result
     * @return mixed
     */
    private function formatForChart($dt, $orders, $result)
    {
        $date = $dt->format('Y-m-d');
        $d_js = 'gd' . $dt->format('(Y,n,j)') ;
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
     * @return string
     */
    private function get_branch_chart()
    {
        $branches = $this->db->query('SELECT id, `name` as title FROM {warehouses_groups}', array())->assoc('id');

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
        $orders = $this->prepare($this->db->query("SELECT DATE_FORMAT(date_add, '%Y-%m-%d') as d, count(*) as c, wrh.group_id as wh "
            . " FROM {orders} o, {warehouses} wrh"
            . " WHERE ?q ?q AND wrh.id = o.wh_id GROUP BY wh, d ",
            array($this->make_filters('date_add'), $query))->assoc(), 'wh');

        $period = $this->get_date_period();
        $result = array();
        foreach ($period as $dt) {
            $result = $this->formatForChart($dt, $orders, $result);
        }
        return $this->view->renderFile('dashboard/branch_chart', array(
            'orders' => $result,
            'branches' => $branches,
            'selected' => $selected
        ));
    }

    /**
     * @return string
     */
    private function get_repair_chart()
    {
        $categories = $this->db->query('SELECT id, title FROM {categories} WHERE parent_id=0 AND avail=1',
            array())->assoc('id');
        $model = $this->db->query('SELECT id, title FROM {categories} WHERE parent_id > 0 AND avail=1',
            array())->assoc('id');
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

        $orders = array();
        $ordersByCategory = array();
        $ordersByModels = array();
        if (!empty($selectedItems)) {
            $orders = $this->prepare($this->db->query("SELECT DATE_FORMAT(o.date_add, '%Y-%m-%d') as d, count(*) as c, goods_id as good "
                . " FROM {orders} o "
                . " JOIN {orders_goods} as og ON og.order_id = o.id "
                . " WHERE ?q AND goods_id in (?li) GROUP BY good, d ",
                array($this->make_filters('o.date_add'), $selectedItems))->assoc(), 'good');
        }
        if (!empty($selectedCategories)) {
            $ordersByCategory = $this->prepare($this->db->query("SELECT DATE_FORMAT(o.date_add, '%Y-%m-%d') as d, count(*) as c, if(cat.parent_id = 0, cat.id, cat.parent_id) as p_id "
                . " FROM {orders} o, {categories} cat "
                . " WHERE ?q AND cat.id = o.category_id AND (cat.id in (?li) OR cat.parent_id in (?li)) GROUP BY p_id, d ",
                array($this->make_filters('o.date_add'), $selectedCategories, $selectedCategories))->assoc(), 'p_id');
        }
        if (!empty($selectedModels)) {
            $ordersByModels = $this->prepare($this->db->query("SELECT DATE_FORMAT(o.date_add, '%Y-%m-%d') as d, count(*) as c, category_id as category_id "
                . " FROM {orders} o "
                . " WHERE ?q AND category_id in (?li) GROUP BY category_id, d ",
                array($this->make_filters('date_add'), $selectedModels))->assoc(), 'category_id');
        }

        $period = $this->get_date_period();
        $resultByItems = array();
        $resultByCategories = array();
        $resultByModels = array();
        foreach ($period as $dt) {
            $resultByItems = $this->formatForChart($dt, $orders, $resultByItems);
            $resultByModels = $this->formatForChart($dt, $ordersByModels, $resultByModels);
            $resultByCategories = $this->formatForChart($dt, $ordersByCategory, $resultByCategories);
        }
        return $this->view->renderFile('dashboard/repair_chart', array(
            'categories' => $categories,
            'models' => $model,
            'items' => $items,
            'byItems' => $resultByItems,
            'byModels' => $resultByModels,
            'byCategories' => $resultByCategories,
            'selectedItems' => $selectedItems,
            'selectedModels' => $selectedModels,
            'selectedCategories' => $selectedCategories
        ));
    }

    /**
     * @return array
     */
    private function get_conversion()
    {
        $calls = $this->db->query("SELECT count(*) FROM {crm_calls} "
            . "WHERE ?q", array($this->make_filters('date')))->el();
        $visitors = $this->db->query("SELECT SUM(users) FROM {crm_analytics} "
            . "WHERE ?q", array($this->make_filters('date')))->el();
        $orders = $this->db->query("SELECT count(*) FROM {orders} "
            . "WHERE ?q", array($this->make_filters('date_add')))->el();
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
        $query_filter = $this->make_filters('o.date_add');
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
        $query_filter = $this->make_filters('date_add');
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
        $query_filter = $this->make_filters('o.date_add');
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
        $query_filter = $this->make_filters('date_transaction');
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
            . "DATE_FORMAT(date_transaction, '%Y-%m-%d') as d, "
            . "SUM((IF(transaction_type=2,value_to,0))-IF(transaction_type=1,value_from,0))/100 as c "
            . "FROM {cashboxes_transactions} "
            . "WHERE ?q AND transaction_type = 2 "
            . "AND type NOT IN (1, 2, 3, 4, 6) "
            . "AND client_order_id > 0 "
            . "GROUP BY d", array($query_filter))->vars();
        $cash_chart_js = array();
        $period = $this->get_date_period();
        $period_cash = 0;
        foreach ($period as $dt) {
            $date = $dt->format('Y-m-d');
            $d_js = 'gd(' . $dt->format('Y') . ',' . $dt->format('n') . ',' . $dt->format('j') . ')';
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
    private function price_format($price, $show_dec = true)
    {
        return str_replace('.00', '', number_format($price, $show_dec ? 2 : 0, '.', ' '));
    }

    /**
     * @param $p
     * @return mixed
     */
    private function percent_format($p)
    {
        return str_replace('.0', '', number_format($p, 1, '.', ''));
    }
}