<?php namespace services\crm;

class requests extends \service{
    
    private static $instance = null;
    // установить этот статус если заявка привязывается к ремонту
    const request_in_order_status = 2;
    private $statuses = array(
        0 => array(
            'name' => 'Новая',
            'active' => 1
        ),
        1 => array(
            'name' => 'Пока нет денег',
            'active' => 1
        ),
        11 => array(
            'name' => 'Не нашли запчасть',
            'active' => 1 
        ),
        2 => array(
            'name' => 'Сдал в ремонт',
            'active' => 0 //  0 - закрывалка заявки - поставив этот статус, заявку редактировать больше нельзя
        ),
        3 => array(
            'name' => 'Отказ без объяснений',
            'active' => 1 
        ),
        4 => array(
            'name' => 'Нашел дешевле',
            'active' => 1 
        ),
        5 => array(
            'name' => 'Нашел ближе к дому',
            'active' => 1 
        ),
        6 => array(
            'name' => 'Передумал ремонтировать',
            'active' => 1 
        ),
        7 => array(
            'name' => 'В другом городе, не хочет отправлять',
            'active' => 1
        ),
        8 => array(
            'name' => 'Не устроили сроки ожидания запчасти',
            'active' => 1 
        ),
        9 => array(
            'name' => 'Не устроили сроки выполнения ремонта',
            'active' => 1 
        ),
        10 => array(
            'name' => 'Закрыта',
            'active' => 0 
        ),
    );
    
    public function get_statuses(){
        return $this->statuses;
    }
    
    // вытягуем заявки
    public function get_requests($client_id = null, $product_id = null, 
                                  $only_active = null, $no_order = null,
                                  $order_id = null, $use_pages = false, $ids = null){
        $p_query = $c_query = $a_query = $o_query = $oid_query = $order = $ids_query = $limit_query = '';
        $scheme = 'assoc:rid';
        // по клиенту
        if($client_id){
            $c_query = $this->all_configs['db']->makeQuery(" c.client_id = ?i ", array($client_id));
        }else{
            $c_query = ' 1=1 ';
        }
        // только по товару
        if($product_id){
            $p_query = $this->all_configs['db']->makeQuery(" AND r.product_id = ?i ", array($product_id));
        }
        // только активные
        if($only_active){
            $a_query = $this->all_configs['db']->makeQuery(" AND r.active = 1 ", array());
        }else{
            // сортируем в начале активные
//            $order = 'r.active DESC,';
            // сортируем по айди
            $order = 'r.id DESC,';
        }
        // только не прикрепленные к заказу
        if($no_order){
            $o_query = $this->all_configs['db']->makeQuery(" AND r.order_id IS NULL ", array());
        }
        // по айди заказа (прикрепленная)
        if($order_id){
            $oid_query = $this->all_configs['db']->makeQuery(" AND r.order_id = ?i ", array($order_id));
            $scheme = 'row';
        }
        // разбивка по страницам
        if($use_pages){
            $count_on_page = count_on_page();
            $skip = (isset($_GET['p']) && $_GET['p'] > 0) ? ($count_on_page * ($_GET['p'] - 1)) : 0;
            $limit_query = $this->all_configs['db']->makeQuery(" LIMIT ?i, ?i", array($skip, $count_on_page));
        }
        // вытягуем по айдихам
        if($ids){
            if(is_array($ids)){
                $ids_query = $this->all_configs['db']->makeQuery(" AND r.id IN(?li) ", array($ids));
            }else{
                $scheme = 'row';
                $ids_query = $this->all_configs['db']->makeQuery(" AND r.id = ?i ", array($ids));
            }
        }
        $fitlers_query = $this->make_reqeusts_fitlers_query();
        $where = $c_query.$p_query.$a_query.$o_query.$oid_query.$ids_query.$fitlers_query;
        $req = $this->all_configs['db']->query("SELECT r.*, c.code, c.referer_id, c.client_id, "
                                                     ."r.id as rid, IF(u.fio = '',u.email,u.fio) as operator_fio, "
                                                    . "c.date as call_date, rf.name as rf_name "
                                              ."FROM {crm_requests} as r "
                                              ."LEFT JOIN {crm_calls} as c ON c.id = r.call_id "
                                              ."LEFT JOIN {users} as u ON u.id = r.operator_id "
                                              ."LEFT JOIN {crm_referers} as rf ON rf.id = c.referer_id "
                                              ."WHERE ".$where
                                              ."ORDER BY ".$order."r.date DESC".$limit_query, array(), $scheme);
        if($use_pages){
            $count = $this->all_configs['db']->query("SELECT count(*) "
                                              ."FROM {crm_requests} as r "
                                              ."LEFT JOIN {crm_calls} as c ON c.id = r.call_id "
                                              ."WHERE ".$where
                                              , array(), 'el');
            return array($req, $count);
        }else{
            return $req;
        }
    }
    
    private function form($request, $id = '', $extended = false){
        $ex_fields = '';
        if($extended){ // в таблице со всеми заявками
            $ex_fields = '
                <td><a href="'.$this->all_configs['prefix'].'clients/create/'.$request['client_id'].'">'.
                        $request['client_id'].'</a></td>
            ';
        }
        $form = '
            <tr'.(!$request['active'] ? ' class="warning"' : '').'>
                <td>'.$request['id'].'</td>
                <td>'.$request['operator_fio'].'</td>
                '.$ex_fields.'
                <td>'.do_nice_date($request['date'], true).'</td>
                <td>
                    <div class="input-prepend">
                      <span class="add-on">
                        <span class="cursor-pointer icon-list" onclick="alert_box(this, false, 1, {service:\'crm/requests\',action:\'changes_history\',type:\'crm-request-change-status\'}, null, \'services/ajax.php\')" data-o_id="'.$id.'" title="История изменений"></span>
                      </span>
                      '.$this->get_statuses_list($request['status'], $id, false, !$request['active']).'
                    </div>
                </td>
                <td><a href="'.$this->all_configs['siteprefix'].gen_full_link(getMapIdByProductId($request['product_id'])).'" target="_blank">на сайте</a></td>
                <td>
                    <div class="input-prepend">
                      <span class="add-on">
                       <span class="cursor-pointer icon-list" onclick="alert_box(this, false, 1, {service:\'crm/requests\',action:\'changes_history\',type:\'crm-request-change-product_id\'}, null, \'services/ajax.php\')" data-o_id="'.$id.'" title="История изменений"></span>'
                      .'</span>'
                      .str_replace('<input', !$request['active'] ? '<input disabled' : '<input',
                            typeahead($this->all_configs['db'], 'categories-goods', false, $request['product_id'], $id, 'input-medium', '', '', true, false, $id)
                      )
                    .'</div>'
                . '</td>
                <td>
                    <div class="input-prepend">
                      <span class="add-on">
                        <span class="pull-left cursor-pointer icon-list" onclick="alert_box(this, false, 1, {service:\'crm/requests\',action:\'changes_history\',type:\'crm-request-change-comment\'}, null, \'services/ajax.php\')" data-o_id="'.$id.'" title="История изменений"></span>
                      </span>
                        <textarea'.(!$request['active'] ? ' disabled' : '').' name="comment['.$id.']" style="width: 140px" class="form-control" rows="2">'.htmlspecialchars($request['comment']).'</textarea>
                    </div>
                </td>
                <td>'.($request['order_id'] ? 
                            '<a href="'.$this->all_configs['prefix'].'orders/create/'.$request['order_id'].'">'
                                .'№'.$request['order_id'].
                            '</a>' : 
//                                'не принято'.($request['active'] ? ' <a href="#add_order_to_request" class="add_order_to_request_btn" data-id="'.$request['id'].'" data-toggle="modal"><i class="fa fa-plus"></i></a>' : '')).'
                                'не принято <a href="#add_order_to_request" class="add_order_to_request_btn" data-id="'.$request['id'].'" data-toggle="modal"><i class="fa fa-plus"></i></a>').'
                </td>
            </tr>
        ';
        return $form;
    } 
    
    private function get_statuses_list($active = 0, $multi = '', $multiselect = false, $disabled = false){
        $statuses_opts = '';
        foreach($this->statuses as $s_id => $s){
            $statuses_opts .= '<option'.((is_numeric($active) && (int)$active === $s_id) || (is_array($active) && in_array($s_id, $active)) ? ' selected' : '').' value="'.$s_id.'">'.$s['name'].'</option>';
        }
        return '<select'.($disabled ? ' disabled' : '').($multiselect ? ' class="multiselect input-small form-control" multiple="multiple"' : ' class="form-control"').
                            ' style="width: 130px" name="status_id'.($multi || $multiselect ? '['.$multi.']' : '').'">'.
                    $statuses_opts.
               '</select>';
    }
    
    // вытягуем заявку по ордер айди
    public function get_request_by_order($order_id){
        return $this->get_requests(null,null,null,null,$order_id);
    }
    
    // вытягуем заявку по айди
    public function get_request_by_id($id){
        return $this->get_requests(null,null,null,null,null,false, $id);
    }
    
    // собираем запрос фильтров для списка всех заявок
    public function make_reqeusts_fitlers_query(){
        $db = $this->all_configs['db'];
        $query_parts = array();
        $query = '';
        // статусы
        $statuses = !empty($_GET['status_id']) ? (array)$_GET['status_id'] : null;
        if($statuses){
            $query_parts[] = $db->makeQuery("r.status IN(?li)", array($statuses));
        }
        // операторы
        $operators = !empty($_GET['operators']) ? (array)$_GET['operators'] : null;
        if($operators){
            $query_parts[] = $db->makeQuery("c.operator_id IN(?li)", array($operators));
        }
        // дата
        $date = !empty($_GET['date']) ? $_GET['date'] : null;
        if($date){
            list($date_from, $date_to) = explode('-', $date);
            $query_parts[] = $db->makeQuery("(DATE(r.date) BETWEEN ? AND ?)", array(
                                                                        date('Y-m-d', strtotime($date_from)), 
                                                                        date('Y-m-d', strtotime($date_to))));
        }
        // клиент
        $client = !empty($_GET['clients']) ? (int)$_GET['clients'] : null;
        if($client){
            $query_parts[] = $db->makeQuery("c.client_id = ?i", array($client));
        }
        // заявка 
        $id = !empty($_GET['request_id']) ? (int)$_GET['request_id'] : null;
        if($id){
            $query_parts[] = $db->makeQuery("r.id = ?i", array($id));
        }
        // устройство
        $product = isset($_GET['categories-goods']) ? (int)$_GET['categories-goods'] : null;
        if($product){
            $query_parts[] = $db->makeQuery("r.product_id = ?i", array($product));
        }
        if($query_parts){
            $query = ' AND '.implode(' AND ', $query_parts).' ';
        }
        return $query;
    }
    
    // юзается так же в crm/statistics
    public function get_operators(){
        $operators = '
            <select class="multiselect form-control" multiple="multiple" name="operators[]">
        ';
        $managers = $this->all_configs['db']->query(
            'SELECT DISTINCT u.id, CONCAT(u.fio, " ", u.login) as name FROM {users} as u, {users_permissions} as p, {users_role_permission} as r
            WHERE (p.link=? OR p.link=?) AND r.role_id=u.role AND r.permission_id=p.id',
            array('edit-clients-orders', 'site-administration'))->assoc();
        foreach ($managers as $manager) {
            $operators .= '
                <option ' . ((isset($_GET['operators']) && in_array($manager['id'], $_GET['operators'])) ? 'selected' : '').'
                    value="' . $manager['id'] . '">' . htmlspecialchars($manager['name']) . '</option>';
        }
        $operators .= '</select>';
        return $operators;
    }
    
    // блок фильтров для списка всех заявок
    private function all_requests_list_filters_block(){
        // операторы
        $operators = $this->get_operators();
        // дата
        $date = (isset($_GET['date']) ? htmlspecialchars(urldecode($_GET['date'])) : '');
        return '
            <style>
                .filter_block > input{
                    max-width: 100% !important;
                }
            </style>
            <form class="filter_block" method="get" action="'.$this->all_configs['prefix'].'clients">
                <input type="hidden" name="tab" value="requests">
                <div class="form-group">
                    <label>Оператор:</label><br>
                    '.$operators.'
                </div>
                <div class="form-group">
                    <label>Статус:</label><br>
                    '.$this->get_statuses_list(!empty($_GET['status_id'])?$_GET['status_id']:null, '', true).'
                </div>
                <div class="form-group">
                    <label>Дата:</label>
                    <input type="text" placeholder="Дата" name="date" class="form-control daterangepicker" value="' . $date . '" />
                </div>
                <div class="form-group">
                    <label>Клиент:</label>
                    '.typeahead($this->all_configs['db'], 'clients', false, (!empty($_GET['clients'])?$_GET['clients']:0), 2, 'input-xlarge', 'input-medium', '', false, false, '').'
                </div>
                <div class="form-group">
                    <label>№&nbsp;Заявки:</label>
                    <input type="text" name="request_id" class="form-control" placeholder="№ Заявки" value="'.(!empty($_GET['request_id'])?(int)$_GET['request_id']:'').'">
                </div>
                <div class="form-group">
                    <label>Устройство:</label>
                    '.typeahead($this->all_configs['db'], 'categories-goods', false, isset($_GET['categories-goods'])?(int)$_GET['categories-goods']:0, '', 'input-xlarge', '', '', false, false, '').'
                </div>
                <input type="submit" class="btn btn-primary" value="Фильтровать">
            </form>
        ';
    }
    
    // список всех заявок с фильтрами
    public function get_all_requests_list(){
        $req_data = $this->get_requests(null,null,null,null,null,true);
        $list = '';
        foreach($req_data[0] as $r){
            $list .= $this->form($r, $r['id'], true);
        }
        $count_on_page = count_on_page();
        $count_pages = ceil($req_data[1] / $count_on_page);
        return '
            <div class="row-fluid">
                <div class="span2">
                    '.$this->all_requests_list_filters_block().'
                </div>
                <div class="span10">
                    <form method="post" class="ajax_form" action="'.$this->all_configs['prefix'].'services/ajax.php">
                        <input type="hidden" name="service" value="crm/requests">
                        <input type="hidden" name="action" value="save_requests">
                        <input type="hidden" name="requests_ids" value="'.implode(',',array_keys($req_data[0])).'">
                        <table class="table table-hover table-striped">
                            <thead>
                                <tr>
                                    <th>id</th>
                                    <th>оператор</th>
                                    <th>клиент</th>
                                    <th>дата</th>
                                    <th>статус</th>
                                    <th>ссылка</th>
                                    <th>устройство</th>
                                    <th>комментарий</th>
                                    <th>№&nbsp;ремонта</th>
                                </tr>
                            </thead>
                            <tbody>
                                '.$list.'
                            </tbody>
                        </table>
                        <input id="save_all_fixed" class="btn btn-primary" type="submit" value="Сохранить изменения">
                    </form>
                    '.$this->request_to_order_form().'
                    '.page_block($count_pages).'
                </div>
            </div>
        ';
    }
    
    public function requests_list($client_id){
        $req = $this->get_requests($client_id);
        $list_items = '';
        foreach($req as $r){
            $list_items .= $this->form($r, $r['id']);
        }
        
        if($list_items){
            $requests = '
                <form method="post" class="ajax_form" action="'.$this->all_configs['prefix'].'services/ajax.php">
                    <input type="hidden" name="service" value="crm/requests">
                    <input type="hidden" name="action" value="save_requests">
                    <input type="hidden" name="requests_ids" value="'.implode(',',array_keys($req)).'">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th>оператор</th>
                                <th>дата заявки</th>
                                <th>статус</th>
                                <th>ссылка</th>
                                <th>устройство</th>
                                <th>комментарий</th>
                                <th>№ ремонта</th>
                            </tr>
                        </thead>
                        <tbody>
                            '.$list_items.'
                        </tbody>
                    </table>
                    <input id="save_all_fixed" class="btn btn-primary" type="submit" value="Сохранить изменения">
                </form>
                '.$this->request_to_order_form().'
            ';
        }else{
            $requests = '<br><div class="center">Заявок нет</div>';
        }
        /* //убрали форму создания звонка, убираем пустое место заменой шаблона
        $list = '
            <div class="row-fluid">
                <div class="span3">
                    '.$this->get_new_request_form($client_id, null).'
                </div>
                <div class="span9">
                '.$requests.'
                </div>
            </div>
        ';*/
        $list = '
            <div class="row-fluid">
                <div class="span12">
                '.$requests.'
                </div>
            </div>
        ';
        return $list;
    }

    private function request_to_order_form(){
        return '
            <div id="add_order_to_request" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form method="post" class="ajax_form" action="'.$this->all_configs['prefix'].'services/ajax.php">
                            <input type="hidden" name="service" value="crm/requests">
                            <input type="hidden" name="action" value="requests_to_order">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title">Привязать заявку к заказу</h4>
                            </div>
                            <div class="modal-body">
                                Введите номер заказа: <br>
                                <input type="hidden" name="request_id" id="order_to_request_id" value="">
                                <input type="text" name="order_id" class="form-control">
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Привязать</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            '.$this->assets().'
        ';
    }
    
    // генерит форму (строку) добавления заявки на странице нового звонка
    public function get_new_request_row_form_for_call($client_id, $call_id, $request_data = null){
        return '
            '.$this->assets().'
            <form class="ajax_form" data-callback="new_call_add_request_callback" method="post" data-submit_on_blur="categories-last-value,comment,status_id" data-on_success_set_value_for="request_id" action="'.$this->all_configs['prefix'].'services/ajax.php">
                <input type="hidden" name="request_id" value="'.($request_data ? $request_data['id'] : '').'">
                <input type="hidden" name="no_redirect" value="1">
                <input type="hidden" name="action" value="new_request">
                <input type="hidden" name="service" value="crm/requests">
                <input type="hidden" name="client_id" value="'.$client_id.'">
                <input type="hidden" name="call_id" value="'.$call_id.'">
                <div class="row-fluid new_request_row">
                    <div class="span4">'
                        .typeahead($this->all_configs['db'], 'categories-last', true, $request_data ? $request_data['product_id'] : 0, 3, 'input-medium popover-info', 'input-min')
                        .'<span class="request_product">'
                            . ($request_data['product_id'] ? ' <a href="'.$this->all_configs['siteprefix'].gen_full_link(getMapIdByProductId($request_data['product_id'])).'" target="_blank">на&nbsp;сайте</a>' : '')
                        .'</span>'
                    . '</div>
                    <div class="span4">
                        '.$this->get_statuses_list($request_data ? $request_data['status'] : null).'
                    </div>
                    <div class="span4">
                        <textarea class="form-control" name="comment" rows="2" cols="35">'.($request_data ? htmlspecialchars($request_data['comment']) : '').'</textarea>
                    </div>
                    '.($request_data['id'] && !$request_data['order_id'] ? '
                            <a href="'.$this->all_configs['prefix'].'orders?on_request='.$request_data['id'].'#create_order" class="create_order_on_request btn btn-success btn-small">Создать заказ</a>
                    ' : ($request_data['order_id'] ? '<a href="'.$this->all_configs['prefix'].'orders/create/'.$request_data['order_id'].'" class="create_order_on_request">Заказ №'.$request_data['order_id'].'</a>' : '')).'
                </div>
            </form>
        ';
    }
    
    // генерит форму добавления заявок на странице звонка
    public function get_new_request_form_for_call($client_id, $call_id){
        $exists_requests = $this->get_requests_for_call_form($call_id);
        return '
            '.$this->assets().'
            <h3>Заявки</h3>
            <div class="row-fluid">
                <div class="span4">
                    <b>Устройство</b>
                </div>
                <div class="span4">
                    <b>Статус</b>
                </div>
                <div class="span4">
                    <b>Комментарий</b>
                </div>
            </div>
            '.$exists_requests.'
            '.$this->get_new_request_row_form_for_call($client_id, $call_id).'
        ';
    }
    
    // форма создания заявок
    public function get_new_request_form($client_id, $call_id = null) {
        
        return '';// скрываем форму создания
//        return '
//            <form class="ajax_form" method="post" action="'.$this->all_configs['prefix'].'services/ajax.php">
//                <input type="hidden" name="action" value="new_request">
//                <input type="hidden" name="service" value="crm/requests">
//                <input type="hidden" name="client_id" value="'.$client_id.'">
//                Звонок: <br>
//                '.get_service('crm/calls')->calls_list_select($client_id, $call_id).'<br>
//                <b>Создать заявку:</b><br>
//                Устройство:<br>
//                '.typeahead($this->all_configs['db'], 'categories-last', true, 0, 3, 'input-medium popover-info', 'input-min').'<br>
//                Статус: <br>
//                '.$this->get_statuses_list().'<br>
//                Комментарий: <br>
//                <textarea name="comment" rows="3" cols="25"></textarea>
//                <br>
//                <button type="submit" class="btn btn-primary">Создать заявку</button>
//            </form>
//        ';
    }
    
    // список заявок на странице создания звонка
    // если обновили страницу чтобы отображались те, что уже есть
    public function get_requests_for_call_form($call_id){
        $req = $this->all_configs['db']->query("SELECT r.*, c.client_id "
                                              ."FROM {crm_requests} as r "
                                              ."LEFT JOIN {crm_calls} as c ON c.id = r.call_id "
                                              ."WHERE call_id = ?i "
                                              ."ORDER BY r.date", array($call_id), 'assoc:id');
        $list = '';
        foreach($req as $r){
            $list .= $this->get_new_request_row_form_for_call($r['client_id'], $r['id'], $r);
        }
        return $list;
    }
    
    // прикрепляем заявку к заказу
    public function attach_to_order($order_id, $request_id){
        $this->all_configs['db']->query("UPDATE {crm_requests} SET order_id = ?i, status = ?i, active = ?i WHERE id = ?i", 
                                        array($order_id, self::request_in_order_status, $this->statuses[self::request_in_order_status]['active'], $request_id));
        // добавить коммент 
        $operator_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $comment = $this->all_configs['db']->query("SELECT comment FROM {crm_requests} WHERE id = ?i", array($request_id), 'el');
        $this->all_configs['db']->query(
            "INSERT INTO {orders_comments}(date_add,text,user_id,auto,order_id,private) "
           ."VALUES(NOW(),?,?i,0,?i,1)", 
                array($comment,$operator_id,$order_id)
        );
    }
    
    // список заявок при создании заказа по айди клиента и товара
    public function get_requests_list_by_order_client($client_id, $product_id, $active_request = null){
        $requests = $this->get_requests($client_id, $product_id, true, true);
        $response = '';
        if($requests){
            $txt = $client_id ? ' клиенту' : '';
            $txt = $product_id ? ' устройству' : $txt;
            $txt = $product_id && $client_id ? ' устройству у клиента' : $txt;
            $list = 'Заявки по данному '.$txt.':<br>
                     <table class="table table-bordered table-condensed table-hover" style="max-width: 1100px">
                         <thead><tr>
                            <td>
                                <label class="radio">
                                    <input'.(!$active_request ? ' checked' : '').' type="radio" name="crm_request" value="0">
                                    без заявки
                                </label>
                            </td>
                            <!--<td>Звонок</td>-->
                            <td>Клиент</td>
                            <td>Устройство</td>
                            <td>Оператор</td>
                            <td>Комментарий</td>
                        </tr></thead><tbody>';
            foreach($requests as $req){
                $client = $this->all_configs['db']->query(
                                'SELECT GROUP_CONCAT(COALESCE(c.fio, ""), ", ", COALESCE(c.email, ""),
                                  ", ", COALESCE(c.phone, ""), ", ", COALESCE(p.phone, "") separator ", " ) as data, c.fio
                                FROM {clients} as c
                                LEFT JOIN {clients_phones} as p ON p.client_id=c.id AND p.phone<>c.phone
                                WHERE c.id = ?i', array($req['client_id']), 'row');
                $product = $this->all_configs['db']->query("SELECT title FROM {categories} "
                                                          ."WHERE id = ?i", array($req['product_id']), 'el');
                $list .= 
                    '<tr>
                        <td>
                            <label class="radio">
                                <input type="radio"'.($active_request == $req['id'] ? ' checked' : '').' name="crm_request"  
                                    data-client_fio="'.$client['fio'].'"
                                    data-client_id="'.$req['client_id'].'" 
                                    data-product_id="'.$req['product_id'].'" 
                                    data-referer_id="'.$req['referer_id'].'" 
                                    data-code="'.$req['code'].'" 
                                    value="'.$req['id'].'">
                                №'.$req['id'].' от '.do_nice_date($req['date'], true, true, 0, true).'        
                            </label>
                        </td>
                        <!--<td>
                            '.do_nice_date($req['call_date'], true, true, 0, true).'<br>
                        </td>-->
                        <td>
                            '.$client['data'].'
                        </td>
                        <td>
                            <a href = "' . $this->all_configs['siteprefix'].gen_full_link(getMapIdByProductId($req['product_id'])) . '" target="_blank">'.$product.'</a>
                        </td>
                        <td>
                            <i>'.getUsernameById($req['operator_id']).'</i><br>
                        </td>
                        <td>
                            <i>'.$req['comment'].'</i><br>
                        </td>
                    </tr>';
            }
            $response = $list.'</tbody></table>'.
                        // показываем алерт с введите фио есть у выбранной заявки нету фио клиента
                        ($active_request ? '<script>check_active_request()</script>' : '');
        }else{
            $response = 'Заявок нет.';
        }
        return $response;
    }
    
    public function ajax($data){
        $operator_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $response = array();
        switch($data['action']){
            // создаем заявку
            case 'new_request':
                $response['state'] = true;
                $client_id = $data['client_id'];
                $request_id = isset($data['request_id']) ? $data['request_id'] : null;
                $call_id = isset($data['call_id']) ? $data['call_id'] : null;
                $product_id = isset($data['categories-last']) ? $data['categories-last'] : null;
                $status_id = isset($data['status_id']) ? $data['status_id'] : null;
                $comment = isset($data['comment']) ? $data['comment'] : null;
                if(!$call_id){
                    $response['state'] = false;
                    $response['msg'] = 'Выберите звонок';
                }
                if($response['state'] && !$product_id){
                    $response['state'] = false;
                    $response['msg'] = 'Выберите устройство';
                }
                if($response['state']){
                    if($request_id){
                        // обновляем (например, на странице нового звонка. вроде больше нигде не используется такое)
                        $this->all_configs['db']->query(
                            "UPDATE {crm_requests}"
                           ."SET product_id = ?i, comment = ?, status = ?i, active = ?i "
                           ."WHERE id = ?i", array(
                               $product_id, $comment, $status_id, $this->statuses[$status_id]['active'], $request_id
                           )
                        );
                    }else{
                        // добавляем
                        $response['request_id'] = $this->all_configs['db']->query(
                            "INSERT INTO {crm_requests}(call_id,operator_id,product_id,comment,status,active,date) "
                           ."VALUES(?i,?i,?i,?,?i,?i,NOW())", array(
                               $call_id, $operator_id, $product_id, $comment, $status_id, $this->statuses[$status_id]['active']
                           ), 'id'
                        );
                        $response['create_order_btn'] = '
                            <a href="'.$this->all_configs['prefix'].'orders?on_request='.$response['request_id'].'#create_order" class="create_order_on_request btn btn-success btn-small">Создать заказ</a>
                        ';
                        if(!isset($data['no_redirect'])){
                            $response['redirect'] = $this->all_configs['prefix'].'clients/create/'.$data['client_id'].'?update='.time().'#requests';
                        }else{
                            $response['after'] = $this->get_new_request_row_form_for_call($client_id, $call_id);
                        }
                    }
                    if($product_id){
                        $response['product_site_url'] = ' <a href="'.$this->all_configs['siteprefix'].gen_full_link(getMapIdByProductId($product_id)).'" target="_blank">на&nbsp;сайте</a>';
                    }
                    $response['state'] = true;
                }
            break;
            // сохраняем изменения в заявках
            case 'save_requests':
                if(isset($data['status_id'])){
                    $requests_ids = explode(',', $data['requests_ids']);
                    $req = $this->get_requests(null,null,null,null,null,false,$requests_ids);
                    $response['state'] = true;
                    foreach($data['status_id'] as $req_id => $status){
                        if(!isset($req[$req_id]) || !$req[$req_id]['active']) continue;
                        $new_status = $status;
                        $new_product_id = $data['categories-goods'][$req_id];
                        $new_comment = $data['comment'][$req_id];
                        $changes = array();
                        if($new_status != $req[$req_id]['status']){
                            $changes[] = $this->all_configs['db']->makeQuery(
                                '(?i, ?, null, ?i, ?)', 
                                    array($operator_id, 'crm-request-change-status', $req_id,  
                                          $this->statuses[$req[$req_id]['status']]['name'].' ==> '.$this->statuses[$new_status]['name'])
                            );
                        }
                        if($new_product_id != $req[$req_id]['product_id']){
                            $current_product_name = $this->all_configs['db']->query("SELECT title FROM {categories} "
                                                                                    ."WHERE id = ?i", array($req[$req_id]['product_id']), 'el');
                            $changes[] = $this->all_configs['db']->makeQuery(
                                '(?i, ?, null, ?i, ?)', 
                                    array($operator_id, 'crm-request-change-product_id', $req_id, 
                                          $current_product_name.' ==> '.$data['categories-goods-value'][$req_id])
                            );
                        }
                        if($new_comment != $req[$req_id]['comment']){
                            $changes[] = $this->all_configs['db']->makeQuery(
                                '(?i, ?, null, ?i, ?)', 
                                    array($operator_id, 'crm-request-change-comment', $req_id, 
                                          $req[$req_id]['comment'].' ==> '.$new_comment)
                            );
                        }
                        if($changes){
                            $this->all_configs['db']->query(
                                'INSERT INTO {changes}(user_id, work, map_id, object_id, `change`) VALUES ?q',
                                    array(implode(',', $changes))
                            );
                        }
                        $this->all_configs['db']->query(
                            "UPDATE {crm_requests} SET product_id = ?i, comment = ?, status = ?i, active = ?i WHERE id = ?i",
                                array($new_product_id, $new_comment, $new_status, $this->statuses[$new_status]['active'], $req_id)
                        );
                    }
                }else{
                    $response['state'] = false;
                    $response['msg'] = 'no data';
                }
            break;
            // история изменений для заявок и звонков
            case 'changes_history':
                if(isset($data['object_id'])) {
                    $response['state'] = true;
                    $changes = $this->all_configs['db']->query(
                        'SELECT u.login, u.email, u.fio, u.phone, ch.change, ch.date_add 
                         FROM {changes} as ch
                         LEFT JOIN {users} as u ON u.id=ch.user_id 
                         WHERE ch.object_id=?i AND work=? ORDER BY ch.date_add DESC',
                        array($data['object_id'], $data['type']))->assoc();
                    if ($changes) {
                        $c = '<table class="table"><thead><tr><td>Менеджер</td><td>Дата</td><td>Изменение</td></tr></thead><tbody>';
                        foreach ($changes as $change) {
                            $c .= '<tr><td>' . get_user_name($change) . '</td>'.
                                  '<td><span title="' . do_nice_date($change['date_add'], false) . '">' . do_nice_date($change['date_add']) . '</span></td>'.
                                  '<td>' . htmlspecialchars($change['change']) . '</td></tr>';
                        }
                        $c .= '</tbody></table>';
                        $response['content'] = $c;
                    }else{
                        $response['content'] = 'История не найдена';
                    }
                }
            break;
            // достаем заявки при создании заказа
            case 'get_request_fro_order':
                $client_id = isset($data['client_id']) ? (int)$data['client_id'] : 0;
                $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
                $response['state'] = true;
                if($client_id || $product_id){
                    $response['content'] = $this->get_requests_list_by_order_client($client_id, $product_id);
                }else{
                    $response['content'] = 'Заявок нет.';
                }
            break;
            // привязываем заявку к заказу
            case 'requests_to_order':
                $request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : null;
                $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : null;
                $request = $this->get_requests(null, null, null, null, null, null, $request_id);
                if($request/* && $request['active']*/){
                    $order_data = $this->all_configs['db']->query(
                                        "SELECT o.user_id, o.category_id FROM {orders} as o "
                                       ."WHERE (SELECT id FROM {crm_requests} WHERE order_id = o.id) IS NULL"
                                       ." AND o.id = ?i", array($order_id), 'row');
                    if($order_data){
                        if($order_data['user_id'] == $request['client_id']){
                            if($request['product_id'] == $order_data['category_id']){
                                $response['state'] = true;
                                $this->attach_to_order($order_id, $request_id);
                                $response['redirect'] = $this->all_configs['prefix'].'orders/create/'.$order_id;
                            }else{
                                $response['msg'] = 'Устройство в заказе и заявке должно быть одно и то же';
                            }
                        }else{
                            $response['msg'] = 'Клиент заявки не совпадает с клиентом в заказе';
                        }
                    }else{
                        $response['msg'] = 'Заказ не найден или уже имеет заявку';
                    }
                }else{
                    $response['msg'] = 'Заявка не найдена';// или закрыта';
                }
            break;
        }
        return $response;
    }
    
    private function assets(){
        if(!isset($this->assets_added)){
            $this->assets_added = true;
            return '
                <link rel="stylesheet" href="'.$this->all_configs['prefix'].'services/crm/requests/css/main.css">
                <script type="text/javascript" src="'.$this->all_configs['prefix'].'services/crm/requests/js/main.js?3"></script>
            ';
        }
        return '';
    }
    
    public static function getInstanse(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct(){}
}
