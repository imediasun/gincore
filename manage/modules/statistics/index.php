<?php
require_once __DIR__ . '/../../Core/Object.php';
require_once __DIR__ . '/../../Core/View.php';
require_once __DIR__ . '/../../Core/Response.php';

$modulename[100] = 'statistics';
$modulemenu[100] = l('Статистика');
global $all_configs;
$moduleactive[100] = $all_configs['oRole']->hasPrivilege('edit-users');

class statistics extends Object
{
    protected $view;
    protected $all_configs;

    /**
     * statistics constructor.
     * @param $all_configs
     */
    function __construct($all_configs)
    {
        global $input_html, $ifauth;

        $this->all_configs = &$all_configs;
        if ($ifauth['is_1']) {
            return false;
        }
        $this->view = new View($all_configs);

        if ($this->can_show_module() == false) {
            $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p  class="text-error">' . l('У Вас не достаточно прав') . '</p></div>';
        } else {

            if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
                $this->ajax();
            }

            $input_html['mcontent'] = $this->gencontent();
        }
        $input_html['mmenu'] = $this->genmenu();
    }

    /**
     * @return mixed
     */
    function can_show_module()
    {
        return ($this->all_configs['oRole']->hasPrivilege('edit-users'));
    }

    /**
     * @return string
     */
    private function genmenu()
    {
        return $this->view->renderFile('statistics/genmenu');
    }

    /**
     * @return string
     */
    private function gencontent()
    {
        return $this->view->renderFile('statistics/gencontent');
    }

    // статистика текстовая (не в таблице)
    /**
     * @param $start
     * @param $end
     * @return array
     */
    private function statistic($start, $end)
    {
        return array();
    }

    /**
     * @param $filters
     * @param $date_field
     * @return string
     */
    private function make_filters($filters, $date_field)
    {
        global $db;
        $query = '';
        if ($filters && !empty($filters['date_start']) && strtotime($filters['date_start']) > 0) {
            $query = $db->makeQuery('?query AND DATE_FORMAT(' . $date_field . ', "%Y-%m-%d")>=?',
                array($query, $filters['date_start']));
        }

        if ($filters && !empty($filters['date_end']) && strtotime($filters['date_end']) > 0) {
            $query = $db->makeQuery('?query AND DATE_FORMAT(' . $date_field . ', "%Y-%m-%d")<=?',
                array($query, $filters['date_end']));
        }

        if ($filters && isset($filters['group_id'])) {
            $query = $db->makeQuery('?query AND IFNULL(r.group_id, 0)=?i',
                array($query, intval($filters['group_id'])));
        }
        return $query;
    }

    // затраты
    /**
     * @param      $filters
     * @param null $group_by
     * @param null $cursor
     * @return mixed
     */
    private function get_expense($filters, $group_by = null, $cursor = null)
    {
        $query = $this->make_filters($filters, 'e.date_add');

        $fetch = 'row';
        if ($group_by !== null) {
            $query = $this->all_configs['db']->makeQuery('?query GROUP BY ?query', array($query, $group_by));
            $fetch = 'assoc';
        }

        $expenses = $this->all_configs['db']->query('SELECT IFNULL(r.name, "") as referer_name,
          IFNULL(r.group_id, 0) as group_referers,
          IFNULL(r.id, 0) as group_referer,
          SUM(e.clicks) as clicks,
          SUM(e.visits) as visits,
          SUM(e.sum_uah) as expense,
          SUM(IF(e.referer_id>0, e.sum_uah, 0)) as referer_expense,
          CONCAT(YEAR(e.date_add), "-", WEEK(e.date_add, 1)) as yearweek

          FROM {crm_expenses} as e
          LEFT JOIN {crm_referers} as r ON r.id=e.referer_id
          WHERE 1=1 ?query ORDER BY e.date_add', array($query))->$fetch($cursor);

        return $expenses;
    }

    // статистика по гугл аналитике
    /**
     * @param      $filters
     * @param null $group_by
     * @param null $cursor
     * @return mixed
     */
    private function get_analitics($filters, $group_by = null, $cursor = null)
    {
        global $db;

        $query = $this->make_filters($filters, 'a.date');

        $fetch = 'row';
        if ($group_by !== null) {
            $query = $db->makeQuery('?query GROUP BY ?query', array($query, $group_by));
            $fetch = 'assoc';
        }

        $analitics = $db->query('SELECT IFNULL(r.name, "") as referer_name,
            IFNULL(r.group_id, 0) as group_referers,
            IFNULL(r.id, 0) as group_referer,
            CONCAT(YEAR(a.date), "-", WEEK(a.date, 1)) as yearweek,
            SUM(a.sessions) as views,
            SUM(a.newUsers) as new_clients,
            SUM(a.users) as users,
            SUM(a.bounceRate) as rate,
            SUM(IF(bounces>0, 1, 0)) as bounces,
            (SUM(a.bounceRate) / SUM(IF(bounces>0, 1, 0))) as rate_percent,
            (SUM(a.newUsers) / SUM(a.users) * 100) as new_clients_percent

            FROM {crm_analytics} as a
            LEFT JOIN {crm_referers} as r ON r.id=a.referer_id
            WHERE 1=1 ?query ORDER BY a.date', array($query))->$fetch($cursor);

        return $analitics;
    }

    // статистика по заказам, заявкам, звонкам и оплатам 
    /**
     * @param      $filters
     * @param null $group_by
     * @param null $cursor
     * @return array
     */
    private function get_invoices($filters, $group_by = null, $cursor = null)
    {
        global $db;

        $query_calls = $this->make_filters($filters, 'c.date');
        $fetch = 'row';
        if ($group_by !== null) {
            $query_calls = $db->makeQuery('?query GROUP BY ?query', array($query_calls, $group_by));
            $fetch = 'assoc';
        }
        $calls = $db->query("
            SELECT 
                IFNULL(r.name, '') as referer_name,
                IFNULL(r.group_id, 0) as group_referers,
                IFNULL(r.id, 0) as group_referer,
                count(*) as qty_calls,
                CONCAT(YEAR(c.date), '-', WEEK(c.date, 1)) as yearweek
            FROM {crm_calls} as c
            LEFT JOIN {crm_referers} as r ON r.id = c.referer_id
            WHERE 1=1 ?q ORDER BY c.date
        ", array($query_calls))->$fetch($cursor);

        $query_requests = $this->make_filters($filters, 'req.date');
        $fetch = 'row';
        if ($group_by !== null) {
            $query_requests = $db->makeQuery('?query GROUP BY ?query', array($query_requests, $group_by));
            $fetch = 'assoc';
        }
        $requests = $db->query("
            SELECT 
                IFNULL(r.name, '') as referer_name,
                IFNULL(r.group_id, 0) as group_referers,
                IFNULL(r.id, 0) as group_referer,
                count(*) as qty_requests,
                COUNT(IF(req.order_id>0, req.id, NULL)) as qty_orders_by_requests,
                CONCAT(YEAR(req.date), '-', WEEK(req.date, 1)) as yearweek
            FROM {crm_requests} as req
            LEFT JOIN {crm_calls} as c ON c.id = req.call_id
            LEFT JOIN {crm_referers} as r ON r.id = c.referer_id
            WHERE 1=1 ?q ORDER BY req.date
        ", array($query_requests))->$fetch($cursor);

        $query_requests = $this->make_filters($filters, 'o.date_add');
        $fetch = 'row';
        if ($group_by !== null) {
            $query_requests = $db->makeQuery('?query GROUP BY ?query', array($query_requests, $group_by));
            $fetch = 'assoc';
        }
        $orders = $db->query("
            SELECT 
                IFNULL(r.name, '') as referer_name,
                IFNULL(r.group_id, 0) as group_referers,
                IFNULL(r.id, 0) as group_referer,
                count(*) as qty_all_orders,
                COUNT(IF(req.order_id IS NULL, o.id, NULL)) as qty_orders_wo_requests,
                COUNT(IF(o.sum_paid > 0, o.id, NULL)) as qty_orders_payments,
                SUM(o.sum) / 100 as created_orders_summ,
                SUM(o.sum_paid) / 100 as orders_summ,
                (SUM(IF(o.sum_paid > 0 AND o.status = 40, o.sum_paid, NULL)) / COUNT(IF(o.sum_paid > 0 AND o.status = 40, o.id, NULL))) / 100 as avg_check,
                CONCAT(YEAR(o.date_add), '-', WEEK(o.date_add, 1)) as yearweek
            FROM {orders} as o
            LEFT JOIN {crm_requests} as req ON req.order_id = o.id
            LEFT JOIN {crm_calls} as c ON c.id = req.call_id
            LEFT JOIN {crm_referers} as r ON r.id = c.referer_id OR r.id = o.referer_id
            WHERE 1=1 ?q ORDER BY o.date_add
        ", array($query_requests))->$fetch($cursor);


        return $this->assoc_array_merge($calls, $requests, $orders);
    }

    /**
     *
     */
    private function ajax()
    {
        $data = array(
            'state' => false
        );

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        if ($act == 'get-kpi-groups') {

            $date_start = isset($_POST['start']) && strtotime($_POST['start']) > 0 ? $_POST['start'] : date('Y-m-01');
            $date_end = isset($_POST['end']) && strtotime($_POST['end']) > 0 ? $_POST['end'] : date('Y-m-d');

            $filters = array('date_start' => $date_start, 'date_end' => $date_end);
            if (isset($_POST['group_id'])) {
                $filters['group_id'] = intval($_POST['group_id']);
            }
            $group_by = $cursor = isset($_POST['group_id']) ? 'group_referer' : 'group_referers';

            $invoices = $this->get_invoices($filters, $group_by, $cursor);
            $analitics = $this->get_analitics($filters, $group_by, $cursor);
            $expense = $this->get_expense($filters, $group_by, $cursor);

            $keys = array(
                0 => array('group' => l('Затраты')),
                1 => array('group' => l('Context')),
                2 => array('group' => l('Remarketing')),
                3 => array('group' => l('Search')),
                4 => array('group' => l('Тизер')),
                5 => array('group' => l('Сайты сателиты')),
                6 => array('group' => l('Sk1dka')),
                7 => array('group' => l('Офлайн пешеходы')),
                8 => array('group' => l('Офлайн реклама')),
                9 => array('group' => l('Клиенты')),
                10 => array('group' => 'group 10'),
                11 => array('group' => 'group 11'),
                12 => array('group' => 'group 12'),
                13 => array('group' => 'group 13'),
                14 => array('group' => 'group 14'),
                15 => array('group' => 'group 15'),
                16 => array('group' => 'group 16'),
                17 => array('group' => 'group 17'),
                18 => array('group' => 'group 18'),
                19 => array('group' => 'group 19'),
                20 => array('group' => 'group 20'),
            );
            $data = $this->assoc_array_merge($analitics, $invoices, $expense, $keys);
        }

        if ($act == 'get-kpi') {
            $start = strtotime(isset($_POST['start']) && strtotime($_POST['start']) > 0 ? $_POST['start'] : date('Y-m-01'));
            $end = strtotime(isset($_POST['end']) && strtotime($_POST['end']) > 0 ? $_POST['end'] : date('Y-m-d'));

            $date_start = date('Y-m-d', strtotime(date('N', $start) == 1 ? 'this Monday' : 'previous Monday', $start));
            $date_end = date('Y-m-d', strtotime(date('N', $end) == 7 ? 'this Sunday' : 'next Sunday', $end));

            $filters = array('date_start' => $date_start, 'date_end' => $date_end);
            $group_by = $cursor = 'yearweek';

            $invoices = $this->get_invoices($filters, $group_by, $cursor);
            $analitics = $this->get_analitics($filters, $group_by, $cursor);
            $expense = $this->get_expense($filters, $group_by, $cursor);

            $data = $this->assoc_array_merge($analitics, $invoices, $expense);
        }

        if ($act == 'get-statistics') {

            $start = isset($_POST['start']) && strtotime($_POST['start']) > 0 ? $_POST['start'] : date('Y-m-01');
            $end = isset($_POST['end']) && strtotime($_POST['end']) > 0 ? $_POST['end'] : date('Y-m-d');

            $data = $this->statistic($start, $end);
        }

        Response::json($data);
    }

    /**
     * @return array
     */
    function assoc_array_merge()
    {
        $array = array();
        $arg_list = func_get_args();

        foreach ((array)$arg_list as $v) {
            foreach ((array)$v as $sk => $sv) {
                $array[$sk] = $sv + (array_key_exists($sk, $array) ? $array[$sk] : array());
            }
        }

        return $array;
    }
}
