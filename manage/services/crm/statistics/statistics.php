<?php namespace services\crm;

class statistics extends \service{
    
    private static $instance = null;

    // статистика в табе Отчеты в модуле клиентов
    public function get_stats(){
        $stats_rows = '';
        $requests_service = get_service('crm/requests');
        $statuses = $requests_service->get_statuses();
        // там же и фильтры срабатывают
        $all_requests = $requests_service->get_requests();
        $all_requests_qty = count($all_requests);
        $clients_by_status = array();
        foreach($all_requests as $r){
            if(!isset($clients_by_status[$r['status']])){
                $clients_by_status[$r['status']] = 0;
            }
            // если был сдан в ремонт, но не установлен статус "Сдал в ремонт", то считаем на этот статус
            if($r['order_id'] && $r['status'] != $requests_service::request_in_order_status){
                if(!isset($clients_by_status[$requests_service::request_in_order_status])){
                    $clients_by_status[$requests_service::request_in_order_status] = 0;
                }
                $clients_by_status[$requests_service::request_in_order_status] ++;
            }else{
                $clients_by_status[$r['status']] ++;
            }
        }
        foreach($statuses as $status_id => $status){
            if(isset($clients_by_status[$status_id])){
                $clients = $clients_by_status[$status_id];
                $conversion = round($clients / $all_requests_qty * 100, 2);
            }else{
                $clients = 0;
                $conversion = 0;
            }
            $stats_rows .= '
                <tr>
                    <td>'.$status['name'].'</td>
                    <td>'.$clients.'</td>
                    <td>'.$conversion.'%</td>
                </tr>
            ';
        }
        
        return '
            <form class="form-inline" method="get" action="'.$this->all_configs['prefix'].'clients">
                <input type="hidden" name="tab" value="statistics">
                <div class="form-group">
                    Оператор:
                    '.get_service('crm/requests')->get_operators().'
                </div>
                <div class="form-group">
                    Период: 
                    <input type="text" placeholder="Дата" name="date" class="daterangepicker form-control" value="' . (isset($_GET['date']) ? htmlspecialchars(urldecode($_GET['date'])) : '') . '" />
                </div>
                <div class="form-group">
                    Устройство: 
                    '.typeahead($this->all_configs['db'], 'categories-goods', false, isset($_GET['categories-goods'])?(int)$_GET['categories-goods']:0, '', 'input-xlarge', '', '', false, false, '').'
                </div>
                <input type="submit" class="btn btn-primary btn-sm" value="Применить">
            </form>
            <br>
            Всего заявок: '.$all_requests_qty.' <br><br>
            <table class="table table-bordered table-striped table-hover" style="max-width:600px">
                <thead>
                    <tr>
                        <th>Статус</th>
                        <th>Клиенты</th>
                        <th>Конверсия</th>
                    </tr>
                </thead>
                <tbody>
                    '.$stats_rows.'
                </tbody>
            </table>
        ';
    }
    
    public static function getInstanse(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct(){}
}
