<?php namespace services\crm;

class calls extends \service{
    
    private static $instance = null;
    
    // id c АТС
    private $call_types = array(
        null => 'Завершен',
        0 => 'Новый',
        1 => 'Разговор',
        2 => 'Принят',
        3 => 'Пропущен',
    );
    
    // форма и кнопка создания нового звонка
    public function create_call_form(){
        $form = '
            <button data-target="#create_call" data-toggle="modal" type="button" class="create_call_btn btn btn-success" style="padding: 5px 10px"><i class="fa fa-phone"></i></button>
            <div id="create_call" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                            <h4 class="modal-title">Создать новый звонок</h4>
                        </div>
                        <form autocomplete="off" method="post" action="'.$this->all_configs['prefix'].'services/ajax.php" class="ajax_form">
                            <input type="hidden" name="service" value="crm/calls">
                            <input type="hidden" name="action" value="new_call">
                            <div class="modal-body">
                                Номер телефона: <br>
                                '.typeahead($this->all_configs['db'], 'clients', false, 0, 1001, 'input-xlarge', 'input-medium', '', false, false, '', true).'
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Сохранить</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        ';
        return $this->view($form);
    }
    
    // таблиица звонков юзера
    public function calls_list_table($client_id){
        $list_items = $this->calls_list($client_id, function($call){
            return '
                <tr>
                    <td>'.$call['id'].'</td>
                    <td>'.$call['operator_fio'].'</td>
                    <td>'.$this->call_types[$call['type']].'</td>
                    <td>
                        <span class="pull-left cursor-pointer icon-list" onclick="alert_box(this, false, 1, {service:\'crm/requests\',action:\'changes_history\',type:\'crm-call-change-referer_id\'}, null, \'services/ajax.php\')" data-o_id="'.$call['id'].'" title="История изменений"></span>
                        '.$this->get_referers_list($call['referer_id'], $call['id']).'</td>
                    <td>
                        <span class="pull-left cursor-pointer icon-list" onclick="alert_box(this, false, 1, {service:\'crm/requests\',action:\'changes_history\',type:\'crm-call-change-code\'}, null, \'services/ajax.php\')" data-o_id="'.$call['id'].'" title="История изменений"></span>
                        <input type="text" class="form-control call_code_mask" name="code['.$call['id'].']" value="'.htmlspecialchars($call['code']).'"></td>
                    <td>'.do_nice_date($call['date'], true).'</td>
                </tr>
            ';
        });
        return '
            <form method="post" class="ajax_form" action="'.$this->all_configs['prefix'].'services/ajax.php">
                <input type="hidden" name="service" value="crm/calls">
                <input type="hidden" name="action" value="save_calls">
                <input type="hidden" name="client_id" value="'.$client_id.'">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>id</th>
                            <th>Оператор</th>
                            <th>Статус</th>
                            <th>Канал</th>
                            <th>Код</th>
                            <th>Дата</th>
                        </tr>
                    </thead>
                    <tbody>'.$list_items.'</tbody>
                </table>
                <input id="save_all_fixed" class="btn btn-primary" type="submit" value="Сохранить изменения">
            </form>
        ';
    }
    
    // таблица всех звонков
    public function get_all_calls_list(){
        $count_on_page = count_on_page();
        $referers = $this->get_referers();
        $list_data = $this->calls_list(null, function($call) use ($referers){
            return '
                <tr>
                    <td>
                        <a href="'.$this->all_configs['prefix'].'clients/create/'.$call['client_id'].
                                  '?new_call='.$call['id'].($call['type'] === '0' ? '&get_call' : '').'">'.$call['id'].'</a>'
                    .'</td>'
                    .'<td>'.
                        ($call['phone'])
                    .'</td>'
                    .'<td>'
                          .($call['open_requests'] ? 
                                '<a href="'.$this->all_configs['prefix'].'clients/create/'.$call['client_id'].'#requests">'.
                                ' <span title="Кол-во открытых заявок на звонок №'.$call['id'].'"> '.$call['open_requests'].'</span>'.
                                '</a>'
                                : '').'</td>
                    <td><a href="'.$this->all_configs['prefix'].'clients/create/'.$call['client_id'].'#calls">'.$call['client_id'].'</a></td>
                    <td>'.$call['operator_fio'].'</td>
                    <td>'.$this->call_types[$call['type']].'</td>
                    <td>
                        '.(isset($referers[$call['referer_id']]) ? $referers[$call['referer_id']] : '').'
                    </td>
                    <td>
                        <div style="position:relative">
                            '.htmlspecialchars($call['code']).'
                            <small style="top:100%;margin-top:-3px;left:0;line-height:8px;
                                          position:absolute" class="muted">
                                '.$call['visitor_id'].'
                            </small>
                        </div>
                    </td>
                    <td>'.do_nice_date($call['date'], true).'</td>
                </tr>
            ';
        }, true);
        $list_items = $list_data[0];
        $count_pages = ceil($list_data[1] / $count_on_page);
        return '
            <table class="table table-hover table-striped">
                <thead>
                    <tr>
                        <th>id</th>
                        <th>Телефон</th>
                        <th>Заявок</th>
                        <th>client id</th>
                        <th>Оператор</th>
                        <th>Статус</th>
                        <th>Канал</th>
                        <th>Код</th>
                        <th>Дата</th>
                    </tr>
                </thead>
                <tbody>'.$list_items.'</tbody>
            </table>
            '.page_block($count_pages).'
        ';
    }

    // массив источников
    public function get_referers(){
        $r = $this->all_configs['db']->query("SELECT id, name FROM {crm_referers} ORDER BY name")->vars();
        $r[0] = $r[''] = 'нет';
        return $r;
    }
    
    // выпадающий список источников
    public function get_referers_list($active = 0, $multi = '', $disabled = false){
        $statuses_opts = '<option value="0">нет</option>';
        $referers = $this->get_referers();
        foreach($referers as $id => $name){
            if($id){
                $statuses_opts .= '<option'.($active == $id ? ' selected' : '').' value="'.$id.'">'.$name.'</option>';
            }
        }
        return '<select'.($disabled ? ' disabled' : '').' name="referer_id'.($multi ? '['.$multi.']' : '').'" class="form-control">'.$statuses_opts.'</select>';
    }
    
    // выпадающий список звонков клиента
    public function calls_list_select($client_id, $call_id = null){
        $list_items = $this->calls_list($client_id, function($call) use ($call_id){
            return '<option'.($call_id == $call['id'] ? ' selected' : '').' value="'.$call['id'].'">id '.$call['id'].', '.$call['date'].'</option>';
        });
        return '<select name="call_id"><option value="0">Выберите</option>'.$list_items.'</select>';
    }

    // массив данных о звонке
    public function get_call($id){
        return $this->all_configs['db']->query("SELECT c.*, IF(u.fio = '',u.email,u.fio) as operator_fio "
                                              ."FROM {crm_calls} as c "
                                              ."LEFT JOIN {users} as u ON u.id = c.operator_id "
                                              ."WHERE c.id = ?i", array($id), 'row');
    }

    // массив звонков клиента или всех если клиент нулл
    private function get_calls($client_id, $use_pages = false){
        if($use_pages){
            $count_on_page = count_on_page();
            $skip = (isset($_GET['p']) && $_GET['p'] > 0) ? ($count_on_page * ($_GET['p'] - 1)) : 0;
            $limit_query = $this->all_configs['db']->makeQuery(" LIMIT ?i, ?i", array($skip, $count_on_page));
        }else{
            $limit_query = '';
        }
        
        //$client_id_q = 'ORDER BY open_requests DESC, c.date DESC';
        $client_id_q = 'ORDER BY c.date DESC';
        if(!is_null($client_id)){
            $client_id_q = $this->all_configs['db']->makeQuery("WHERE client_id = ?i ORDER BY date DESC ", array($client_id));
        }
        $items = $this->all_configs['db']->query("SELECT c.*, IF(c.phone = '' OR c.phone IS NULL, cp.phone, c.phone) as phone, vc.visitor_id, "
                                                    . "IF(u.fio = '',u.email,u.fio) as operator_fio "
                                                    . ", (SELECT COUNT(*) FROM {crm_requests} WHERE call_id = c.id AND active = 1) as open_requests "
                                              ."FROM {crm_calls} as c "
                                              ."LEFT JOIN {clients} as cp ON cp.id = c.client_id "
                                              ."LEFT JOIN {visitors_code} as vc ON vc.code = c.code "
                                              ."LEFT JOIN {users} as u ON u.id = c.operator_id ?q".$limit_query,
                                              array($client_id_q), 'assoc:id');
        if($use_pages){
            $count = $this->all_configs['db']->query("SELECT COUNT(*) "
                                              ."FROM {crm_calls} as c "
                                              ."LEFT JOIN {users} as u ON u.id = c.operator_id ?q",
                                              array($client_id_q), 'el');
            return array($items, $count);
        }else{
            return $items;
        }
    }
    
    private function calls_list($client_id, callable $callback, $use_pages = false){
        $calls = $this->get_calls($client_id, $use_pages);
        if($use_pages){
            $count = $calls[1];
            $calls = $calls[0];
        }
        $list = '';
        foreach($calls as $call){
            $list .= $callback($call);
        }
        if($use_pages){
            return array($list, $count);
        }else{
            return $list;
        }
    }
    
    public function create_call($client_id, $call_type = 'null', $phone = '', $set_operator = true){
        $operator_id = $set_operator && isset($_SESSION['id']) ? $_SESSION['id'] : '';
        return $this->all_configs['db']->query(
                        "INSERT INTO {crm_calls}(phone,type,client_id,operator_id,date) "
                       ."VALUES (?,?q,?i,?i,NOW())", array($phone,$call_type,$client_id,$operator_id), 'id');
    }
    
    public function create_call_by_phone($phone, $call_type = 'null'){
        require_once($this->all_configs['sitepath'] . 'shop/access.class.php');
        $access = new \access($this->all_configs, false);
        $serach_phone = $access->is_phone($phone);
        if($serach_phone !== false){
            $serach_phone = $serach_phone[0];
            $client_id = $this->all_configs['db']->query(
                            "SELECT c.id "
                           ."FROM {clients} as c "
                           ."LEFT JOIN {clients_phones} as p ON p.client_id = c.id "
                           ."WHERE c.phone LIKE '%?e' OR p.phone LIKE '%?e'"
                           ."LIMIT 1", array($serach_phone, $serach_phone), 'el');
            if(!$client_id){
                $u = $access->registration(array('phone' => $phone));
                if($u['id'] > 0){
                    $client_id = $u['id'];
                }
            }
            if($client_id) {
                return $this->create_call($client_id, $call_type, $serach_phone, false);
            }
        }
        return false;
    }
    
    public function code_exists($code){
        $c = mb_strtoupper($code, 'UTF-8');
        $code_exists = $this->all_configs['db']->query(
                            "SELECT SUM(t.c) FROM ("
                               ."SELECT count(*) as c "
                               ."FROM {visitors_code} WHERE code = ? "
                               ."UNION "
                               ."SELECT count(*) as c "
                               ."FROM {visitors_system_codes} WHERE code = ?"
                            .") AS t ", array($c, $c), 'el');
        return !!$code_exists;
    }
    
    public function ajax($data){
        $response = array();
        $operator_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        switch($data['action']){
            // создаем звонок
            case 'new_call':
                $phone = !empty($data['clients-value']) ? $data['clients-value'] : null;
                $client_id = !empty($data['clients']) ? $data['clients'] : null;
//                $code = !empty($data['code']) ? $data['code'] : null;
//                $referer_id = !empty($data['referer_id']) ? $data['referer_id'] : null;
//                if($code || $referer_id){
                    // создаем нового клиента
                    if(!$client_id && $phone){
                        if (!$this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                            $response['state'] = false;
                            $response['msg'] = 'У Вас недостаточно прав для создания нового клиента';
                        }
                        require_once($this->all_configs['sitepath'] . 'shop/access.class.php');
                        $access = new \access($this->all_configs, false);
                        $_POST['phone'] = $phone;
                        $u = $access->registration($_POST);
                        if ($u['id'] > 0) {
                            $client_id = $u['id'];
                        }else{
                            $response['state'] = false;
                            $response['msg'] = 'Ошибка. Клиент не создан: '.$u['msg'];
                        }
                    }
                    if($client_id){
                        $id = $this->create_call($client_id/*, $code, $referer_id*/);
                        $response['state'] = true;
                        $response['redirect'] = $this->all_configs['prefix'].'clients/create/'.$client_id.'/?new_call='.$id;
                    }
//                }else{
//                    $response['state'] = false;
//                    $response['msg'] = 'Укажите код или канал';
//                }
            break;
            // апдейтим все звонки
            case 'save_calls':
                $client_id = !empty($data['client_id']) ? $data['client_id'] : null;
                $referer_ids = !empty($data['referer_id']) ? $data['referer_id'] : null;
                $codes = !empty($data['code']) ? $data['code'] : null;
                $calls = $this->get_calls($client_id);
                $referers = $this->get_referers();
                $response['state'] = true;
                foreach($referer_ids as $call_id => $referer_id){
                    if(!isset($calls[$call_id])) continue;
                    $new_referer_id = $referer_id;
                    $new_code = $codes[$call_id];
                    $changes = array();
                    if((int)$referer_id && $new_referer_id != $calls[$call_id]['referer_id']){
                        $changes[] = $this->all_configs['db']->makeQuery(
                            '(?i, ?, null, ?i, ?)', 
                                array($operator_id, 'crm-call-change-referer_id', $call_id,  
                                      $referers[$calls[$call_id]['referer_id']].' ==> '.$referers[$new_referer_id])
                        );
                    }
                    if($new_code != $calls[$call_id]['code']){
                        $changes[] = $this->all_configs['db']->makeQuery(
                            '(?i, ?, null, ?i, ?)', 
                                array($operator_id, 'crm-call-change-code', $call_id,  
                                      ($calls[$call_id]['code'] ?: 'нет').' ==> '.$new_code)
                        );
                    }
                    if($changes){
                        $this->all_configs['db']->query(
                            'INSERT INTO {changes}(user_id, work, map_id, object_id, `change`) VALUES ?q',
                                array(implode(',', $changes))
                        );
                    }
                    $new_code = $new_code ? $this->all_configs['db']->makeQuery(" ? ", array($new_code)) : 'null';
                    $new_referer_id = $new_referer_id ? $this->all_configs['db']->makeQuery(" ?i ", array($new_referer_id)) : 'null';
                    $this->all_configs['db']->query(
                        "UPDATE {crm_calls} SET code = ?q, referer_id = ?q WHERE id = ?i",
                            array($new_code, $new_referer_id, $call_id)
                    );
                }
            break;
            // проверка кода на существование при создании звонка
            case 'check_code':
                $code = isset($_POST['code']) ? trim($_POST['code']) : '';
                $response['state'] = $this->code_exists($code);
            break;
        }
        return $response;
    }
    
    private function view($content){
        if(!isset($this->assets_added)){
            $this->assets_added = true;
            $content .= $this->assets();
        }
        return $content;
    }
    
    public function assets(){
        return '
            <link rel="stylesheet" href="'.$this->all_configs['prefix'].'services/crm/calls/css/main.css?1">
            <script type="text/javascript" src="'.$this->all_configs['prefix'].'services/crm/calls/js/jquery.maskedinput.min.js"></script>
            <script type="text/javascript" src="'.$this->all_configs['prefix'].'services/crm/calls/js/main.js?1"></script>
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
