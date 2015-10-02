<?php

class dashboard{

    function __construct($all_configs, $lang, $def_lang){
        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->all_configs = $all_configs;
        $this->db = $this->all_configs['db'];
        $this->arrequest = $this->all_configs['arrequest'];
        $this->prefix = $this->all_configs['prefix'];
        
        $this->gen_filter_block();
        $this->gen_content();
    }
    
    private function gen_filter_block(){
        global $input;
        $start = isset($_GET['ds']) && strtotime($_GET['ds']) > 0 ? date("j/n/y", strtotime($_GET['ds'])) : date("1/n/y");
        $end = isset($_GET['de']) && strtotime($_GET['de']) > 0 ? date("j/n/y", strtotime($_GET['de'])) : date("j/n/y");
        $input['filter'] =
            '<div id="daterange" class="btn btn-info">
                <span>' . $start . ' - ' . $end . '</span> <b class="caret"></b>
            </div>';
    }

    private function get_filters(){
        $date_start = isset($_GET['ds']) && strtotime($_GET['ds']) > 0 ? $_GET['ds'] : date('Y-m-01');
        $date_end = isset($_GET['de']) && strtotime($_GET['de']) > 0 ? $_GET['de'] : date('Y-m-d');
        return array(
            'date_start' => $date_start, 
            'date_end' => $date_end
        );
    }
    
    private function make_filters($date_field){
        $filters = $this->get_filters();
        $query = '';
        if ($filters && !empty($filters['date_start']) && strtotime($filters['date_start']) > 0) {
            $query = $this->db->makeQuery('?query AND DATE_FORMAT('.$date_field.', "%Y-%m-%d")>=?',
                array($query, $filters['date_start']));
        }

        if ($filters && !empty($filters['date_end']) && strtotime($filters['date_end']) > 0) {
            $query = $this->db->makeQuery('?query AND DATE_FORMAT('.$date_field.', "%Y-%m-%d")<=?',
                array($query, $filters['date_end']));
        }
        return ' 1=1 '.$query;
    }
    
    private function gen_content(){
        global $input;
        $conversion = $this->get_conversion();
        $input['conversion_1'] = $conversion[0];
        $input['conversion_2'] = $conversion[1];
        $input['conversion_3'] = $conversion[2];
        $conv_chart = $this->get_conv_chart();
        $input['line_chart_data_orders'] = $conv_chart['orders'];
        $input['line_chart_data_calls'] = $conv_chart['calls'];
        $input['line_chart_data_visitors'] = $conv_chart['visitors'];
        $input['currency'] = viewCurrency('symbol');
        $input['avg_check'] = $this->get_avg_check();
        $input['workshops_stats'] = $this->get_workshops_stats();
        $input['engineers_stats'] = $this->get_engineer_stats();
        $cash = $this->get_cash();
        $input['today_cash'] = $cash['today'];
        $input['period_cash'] = $cash['period'];
        $input['cash_chart'] = $cash['cash_chart'];
    }
    
    private function get_date_period(){
        $filters = $this->get_filters();
        $a = new DateTime($filters['date_start']);
        $b = new DateTime($filters['date_end']);
        $b->modify( '+1 day' );
        $period = new DatePeriod($a, new DateInterval('P1D'), $b);
        return $period;
    }
    
    private function get_conv_chart(){
        $calls = $this->db->query("SELECT DATE_FORMAT(date, '%Y-%m-%d') as d, count(*) as c "
                                 ."FROM {crm_calls} "
                                 ."WHERE ?q GROUP BY d", array($this->make_filters('date')))->vars();
        $visitors = $this->db->query("SELECT date as d, SUM(users) as c FROM {crm_analytics} "
                                    ."WHERE ?q GROUP BY d", array($this->make_filters('date')))->vars();
        $orders = $this->db->query("SELECT DATE_FORMAT(date_add, '%Y-%m-%d') as d, count(*) as c "
                                  ."FROM {orders} "
                                  ."WHERE ?q AND type = 0 GROUP BY d", array($this->make_filters('date_add')))->vars();
        $calls_js = array();
        $orders_js = array();
        $visitors_js = array();
        $period = $this->get_date_period();
        foreach($period as $dt) {
            $date = $dt->format('Y-m-d');
            $d_js = 'gd('.$dt->format('Y').','.$dt->format('n').','.$dt->format('j').')';
            $calls_js[$date] = '['.$d_js.','.(isset($calls[$date]) ? $calls[$date] : 0).']';
            $orders_js[$date] = '['.$d_js.','.(isset($orders[$date]) ? $orders[$date] : 0).']';
            $visitors_js[$date] = '['.$d_js.','.(isset($visitors[$date]) ? $visitors[$date] : 0).']';
        }
        return array(
            'calls' => implode(',', $calls_js),
            'orders' => implode(',', $orders_js),
            'visitors' => implode(',', $visitors_js)
        );
    }
    
    private function get_conversion(){
        $calls = $this->db->query("SELECT count(*) FROM {crm_calls} "
                                 ."WHERE ?q", array($this->make_filters('date')))->el();
        $visitors = $this->db->query("SELECT SUM(users) FROM {crm_analytics} "
                                    ."WHERE ?q", array($this->make_filters('date')))->el();
        $orders = $this->db->query("SELECT count(*) FROM {orders} "
                                  ."WHERE ?q", array($this->make_filters('date_add')))->el();
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
    
    private function get_avg_check(){
        $query_filter = $this->make_filters('o.date_add');
        $avg_check = $this->db->query("
            SELECT 
                (SUM(IF(o.sum_paid > 0 AND o.status = 40, o.sum_paid, NULL)) / COUNT(IF(o.sum_paid > 0 AND o.status = 40, o.id, NULL))) / 100 as avg_check
            FROM {orders} as o
            WHERE ?q 
        ", array($query_filter))->el();
        $avg_check = $this->price_format($avg_check);
        return $avg_check;
    }
    
    private function get_workshops_stats(){
        $stats = '';
        $statuses = array(
            40 => $this->all_configs['configs']['order-status'][40]['name'], // выдан
            25 => $this->all_configs['configs']['order-status'][25]['name'], // выдан без ремонта
            15 => $this->all_configs['configs']['order-status'][15]['name'], // клиент отказался
            50 => $this->all_configs['configs']['order-status'][50]['name']  // переведен в доноры
        );
        $query_filter = $this->make_filters('date_add');
        $all_orders = $this->db->query("SELECT count(*) FROM {orders} "
                                      ."WHERE ?q AND status IN(?l)", array($query_filter, array_keys($statuses)), 'el');
        foreach($statuses as $status => $name){
            $orders = $this->db->query("SELECT count(*) "
                                      ."FROM {orders} WHERE ?q AND status = ?i", array($query_filter, $status), 'el');
            $p = $all_orders > 0 ? $this->percent_format($orders / $all_orders * 100) : 0;
            $stats .= '
                <div class="m-t-xs">
                    <span class="font-bold no-margins">
                        '.$name.' <span class="pull-right">'.$orders.' ('.$p.'%)</span>
                    </span>
                    <div class="progress m-t-xs full progress-small">
                        <div style="width:'.$p.'%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="55" role="progressbar" class="'.(!$orders ? 'hidden ' : '').'progress-bar progress-bar-success">
                            <span class="sr-only"></span>
                        </div>
                    </div>
                </div>
            ';
        }
        return $stats; 
    }
    
    private function get_engineer_stats(){
        $query_filter = $this->make_filters('o.date_add');
        $orders = $this->db->query("SELECT engineer, IF(u.fio!='',u.fio,u.login) as fio, "
                                         ."count(o.id) as orders "
                                  ."FROM {orders} as o "
                                  ."LEFT JOIN {users} as u ON u.id = o.engineer "
                                  ."WHERE ?q AND engineer > 0 AND status = ?i AND sum_paid > 0 GROUP BY engineer "
                                  ."ORDER BY orders DESC", array($query_filter, $this->all_configs['configs']['order-status-issued']), 'assoc');
        $all_orders = 0;
        foreach($orders as $ord){
            $all_orders += $ord['orders'];
        }
        $stats = '';
        foreach($orders as $i => $o){
            $p = $this->percent_format($o['orders'] / $all_orders * 100);
            $stats .= '
                <div class="clearfix m-t-sm">
                    <span class="font-bold no-margins">
                        '.($o['fio'] ?: ('id '.$o['engineer'])).'<span class="pull-right text-success">'.$o['orders'].' ('.$p.'%)</span>
                    </span>
                </div>
            ';
        }
        return $stats ?: 'За выбранный период нет статистики';
    }
    
    private function get_cash(){
        $query_filter = $this->make_filters('date_transaction');
        $today_cash = $this->db->query("SELECT SUM(value_to) / 100 "
                                      ."FROM {cashboxes_transactions} "
                                      ."WHERE date_transaction >= ? AND transaction_type = 2", array(date('Y-m-d 00:00:00')), 'el');
        $chart_cash = $this->db->query("SELECT "
                                        ."DATE_FORMAT(date_transaction, '%Y-%m-%d') as d, "
                                        ."SUM((IF(transaction_type=2,value_to,0))-IF(transaction_type=1,value_from,0))/100 as c "
                                      ."FROM {cashboxes_transactions} "
                                      ."WHERE ?q AND transaction_type = 2 "
                                        ."AND type NOT IN (1, 2, 3, 4) AND type NOT IN (6,10) "
                                        ."AND client_order_id > 0 "
                                      ."GROUP BY d", array($query_filter))->vars();
        $cash_chart_js = array();
        $period = $this->get_date_period();
        $period_cash = 0;
        foreach($period as $dt) {
            $date = $dt->format('Y-m-d');
            $d_js = 'gd('.$dt->format('Y').','.$dt->format('n').','.$dt->format('j').')';
            $cash = isset($chart_cash[$date]) ? number_format($chart_cash[$date],2,'.','') : 0;
            $period_cash += $cash;
            $cash_chart_js[] = '['.$d_js.','.$cash.']';
        }
        return array(
            'today' => $this->price_format($today_cash),
            'period' => $this->price_format($period_cash,false),
            'cash_chart' => implode(',', $cash_chart_js)
        );
    }
    
    private function price_format($price, $show_dec = true){
        return str_replace('.00', '', number_format($price,$show_dec?2:0,'.',' '));
    }
    
    private function percent_format($p){
        return str_replace('.0', '', number_format($p, 1, '.', ''));
    }
}