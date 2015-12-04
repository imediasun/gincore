<?php


$modulename[100] = 'statistics';
$modulemenu[100] = l('Статистика');
$moduleactive[100] = !$ifauth['is_1'];

class statistics
{

    function __construct($all_configs)
    {
        global $input_html, $ifauth;
        
        $this->all_configs = &$all_configs;
        if ($ifauth['is_1']) return false;

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }


        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

    private function genmenu()
    {
        $start = isset($_GET['ds']) && strtotime($_GET['ds']) > 0 ? date("j/n/y", strtotime($_GET['ds'])) : date("1/n/y");
        $end = isset($_GET['de']) && strtotime($_GET['de']) > 0 ? date("j/n/y", strtotime($_GET['de'])) : date("j/n/y");

        $out =
            '<div id="daterange" class="btn btn-info">
                <span>' . $start . ' - ' . $end . '</span> <b class="caret"></b>
            </div>';

        return $out;
    }

    private function gencontent()
    {
        $out = '<div id="graph" class="graph"></div>';

        $out .=
            '<table id="report" class="tablesorter">
                <thead>
                    <tr>
                        <th class="{ sorter: false }"></th>
                        <th>'.l('Дата').'</th>
                        <th class="{ sorter: false }">Показы</th>
                        <th class="{ sorter: false }">Клики</th>
                        <th class="{ sorter: false }">CTR</th>
                        <th class="{ sorter: false }">Просмотры</th>
                        <th class="{ sorter: false }">Новые пользователи</th>
                        <th class="{ sorter: false }">Новые пользователи %</th>
                        <th class="{ sorter: false }">% отказа н.п.</th>
                        <th class="{ sorter: false }">Звонки.</th>
                        <th class="{ sorter: false }">Заявки</th>
                        <th class="{ sorter: false }">Заказы по заявкам</th>
                        <th class="{ sorter: false }">Заказы без заявок</th>
                        <th class="{ sorter: false }">Общее кол-во заказов</th>
                        <th class="{ sorter: false }">Кол-во оплат</th>
                        <th class="{ sorter: false }">Создано на сумму</th>
                        <th class="{ sorter: false }">Сумма</th>
                        <th class="{ sorter: false }">Ср. чек</th>
                        <th class="{ sorter: false }">CPO</th>
                        <th class="{ sorter: false }">ROI</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="loading">
                        <td colspan="20" style="text-align: center">
                            <div class="progress progress-striped active">
                                <div class="bar" style="width: 100%;"></div>
                            </div>
                        </td>
                    </tr>
                    <tr class="nodata">
                        <td colspan="20" style="text-align: center">
                            <div class="message">
                                <div class="alert alert-block">
                                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                                    <h4>Пусто!</h4>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
                <tfoot></tfoot>
            </table>';

        return $out;
    }
    
    // статистика текстовая (не в таблице)
    private function statistic($start, $end){
        
    }
    
    private function make_filters($filters, $date_field){
        global $db;
        $query = '';
        if ($filters && !empty($filters['date_start']) && strtotime($filters['date_start']) > 0) {
            $query = $db->makeQuery('?query AND DATE_FORMAT('.$date_field.', "%Y-%m-%d")>=?',
                array($query, $filters['date_start']));
        }

        if ($filters && !empty($filters['date_end']) && strtotime($filters['date_end']) > 0) {
            $query = $db->makeQuery('?query AND DATE_FORMAT('.$date_field.', "%Y-%m-%d")<=?',
                array($query, $filters['date_end']));
        }

        if ($filters && isset($filters['group_id'])) {
            $query = $db->makeQuery('?query AND IFNULL(r.group_id, 0)=?i',
                array($query, intval($filters['group_id'])));
        }
//        echo $query;
        return $query;
    }
    
    // затраты
    private function get_expense($filters, $group_by = null, $cursor = null)
    {
        global $db;

        $query = $this->make_filters($filters, 'e.date_add');

        $fetch = 'row';
        if ($group_by !== null) {
            $query = $db->makeQuery('?query GROUP BY ?query', array($query, $group_by));
            $fetch = 'assoc';
        }

        $expenses = $db->query('SELECT IFNULL(r.name, "") as referer_name,
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
        

//        $invoices = $db->query('SELECT o.period_id, IFNULL(r.name, "") as referer_name,
//            IFNULL(r.group_id, 0) as group_referers,
//            IFNULL(r.id, 0) as group_referer,
//            COUNT(DISTINCT IF(i.summ_uah>0, i.id, NULL)) as invoices,
//            COUNT(DISTINCT IF(o.state>0 AND i.state>0 AND i.summ_uah>0, i.id, NULL)) as paid,
//            COUNT(DISTINCT IF(o.state>0 AND i.state>0 AND i.summ_uah>0 AND (SELECT si.id FROM {pay_invoices} as si, {pay_orders} as so WHERE c.id=so.user_id AND si.order_id=so.id AND so.id<o.id AND so.state>0 AND si.state>0 AND si.summ_uah>0 LIMIT 1) IS NULL, c.id, NULL)) as paid1,
//            COUNT(DISTINCT IF(o.state>0 AND i.state>0 AND t.paid_from<NOW() AND t.paid_by>(NOW() AND t.off_date IS NULL), i.id, NULL)) as now_paid,
//            COUNT(DISTINCT IF(o.state>0 AND i.state>0 AND i.summ_uah>0, o.id, null)) as tariffs,
//            AVG(IF(o.state>0 AND i.state>0 AND i.summ_uah>0, UNIX_TIMESTAMP(i.payed)-UNIX_TIMESTAMP(c.date_reg), NULL)) as avg_sec,
//            SUM(IF(o.state>0 AND i.state>0, i.summ_uah, 0)) as income,
//            SUM(IF(o.state>0 AND i.state>0, i.summ_uah, 0)) / COUNT(DISTINCT IF(o.state>0 AND i.state>0 AND i.summ_uah>0, i.id, NULL)) as avg_bill,
//            MAX(IF(o.state>0 AND i.state>0 AND i.summ_uah>0, i.payed, null)) as max_payed,
//            CONCAT(YEAR(i.payed), "-", WEEK(i.payed, 1)) as yearweek,
//            AVG(IF(o.state>0 AND i.state>0 AND i.summ_uah>0, (SELECT IF(id, UNIX_TIMESTAMP(i.payed)-UNIX_TIMESTAMP(`by`), null) FROM {clients_tariffs_history} WHERE tariff_id>0 AND order_id<>o.id AND i.payed>`by` AND client_id=c.id ORDER BY `by` DESC LIMIT 1), null)) as avg_after,
//            COUNT(DISTINCT IF(o.state>0 AND i.state>0 AND i.summ_uah>0 AND i.payed+10<(SELECT `from` FROM {clients_tariffs_history} WHERE tariff_id>0 AND order_id=o.id AND client_id=c.id), i.id, null)) as invoices_in
//
//            FROM {clients} AS c
//            LEFT JOIN {crm_referers} as r ON r.id=c.referer_id
//            LEFT JOIN {pay_orders} as o ON c.id=o.user_id
//            LEFT JOIN {pay_invoices} as i ON i.order_id=o.id
//            LEFT JOIN {clients_tariff} as t ON t.order_id=o.id AND t.client_id=c.id
//            WHERE c.superuser=0 ?query ORDER BY i.payed',
//
//            array($query))->$fetch($cursor);
//
        $data = $this->assoc_array_merge($calls, $requests, $orders);
        return $data;
    }

    private function ajax()
    {
        $data = array(
            'state' => false
        );

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        if ($act == 'get-kpi-groups') {

//            if(isset($_POST['start']) && $_POST['start'] == 'all' && $_POST['end'] == 'all'){
//                $date_start = null;
//                $date_end = null;
//            }else{
                $date_start = isset($_POST['start']) && strtotime($_POST['start']) > 0 ? $_POST['start'] : date('Y-m-01');
                $date_end = isset($_POST['end']) && strtotime($_POST['end']) > 0 ? $_POST['end'] : date('Y-m-d');
//            }

            $filters = array('date_start' => $date_start, 'date_end' => $date_end);
            if (isset($_POST['group_id'])) {
                $filters['group_id'] = intval($_POST['group_id']);
            }
            $group_by = $cursor = isset($_POST['group_id']) ? 'group_referer' : 'group_referers';

            $invoices = $this->get_invoices($filters, $group_by, $cursor);
            $analitics = $this->get_analitics($filters, $group_by, $cursor);
            $expense = $this->get_expense($filters, $group_by, $cursor);

            $keys = array(
                0 => array('group' => 'Затраты'),
                1 => array('group' => 'Context'),
                2 => array('group' => 'Remarketing'),
                3 => array('group' => 'Search'),
                4 => array('group' => 'Тизер'),
                5 => array('group' => 'Сайты сателиты'),
                6 => array('group' => 'Sk1dka'),
                7 => array('group' => 'Офлайн пешеходы'),
                8 => array('group' => 'Офлайн реклама'),
                9 => array('group' => 'Клиенты'),
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
//            print_r($analitics);
//            print_r($invoices);
//            print_r($expense);
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

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

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
