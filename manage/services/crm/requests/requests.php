<?php

namespace services\crm;

class requests extends \service
{
    private static $instance = null;
    // установить этот статус если заявка привязывается к ремонту
    const request_in_order_status = 2;
    private $statuses;
    protected $view;

    /**
     *
     */
    private function set_statuses()
    {
        $this->statuses = array(
            0 => array(
                'name' => l('Новая'),
                'active' => 1
            ),
            1 => array(
                'name' => l('Пока нет денег'),
                'active' => 1
            ),
            11 => array(
                'name' => l('Не нашли запчасть'),
                'active' => 1
            ),
            2 => array(
                'name' => l('Сдал в ремонт'),
                'active' => 0 //  0 - закрывалка заявки - поставив этот статус, заявку редактировать больше нельзя
            ),
            3 => array(
                'name' => l('Отказ без объяснений'),
                'active' => 1
            ),
            4 => array(
                'name' => l('Нашел дешевле'),
                'active' => 1
            ),
            5 => array(
                'name' => l('Нашел ближе к дому'),
                'active' => 1
            ),
            6 => array(
                'name' => l('Передумал ремонтировать'),
                'active' => 1
            ),
            7 => array(
                'name' => l('В другом городе, не хочет отправлять'),
                'active' => 1
            ),
            8 => array(
                'name' => l('Не устроили сроки ожидания запчасти'),
                'active' => 1
            ),
            9 => array(
                'name' => l('Не устроили сроки выполнения ремонта'),
                'active' => 1
            ),
            10 => array(
                'name' => l('Закрыта'),
                'active' => 0
            )
        );
    }

    /**
     * @return mixed
     */
    public function get_statuses()
    {
        return $this->statuses;
    }

    // вытягуем заявки
    /**
     * @param null $client_id
     * @param null $product_id
     * @param null $only_active
     * @param null $no_order
     * @param null $order_id
     * @param bool $use_pages
     * @param null $ids
     * @return array
     */
    public function get_requests(
        $client_id = null,
        $product_id = null,
        $only_active = null,
        $no_order = null,
        $order_id = null,
        $use_pages = false,
        $ids = null
    ) {
        $p_query = $c_query = $a_query = $o_query = $oid_query = $order = $ids_query = $limit_query = '';
        $scheme = 'assoc:rid';
        // по клиенту
        if ($client_id) {
            $c_query = $this->all_configs['db']->makeQuery(" c.client_id = ?i ", array($client_id));
        } else {
            $c_query = ' 1=1 ';
        }
        // только по товару
        if ($product_id) {
            $p_query = $this->all_configs['db']->makeQuery(" AND r.product_id = ?i ", array($product_id));
        }
        // только активные
        if ($only_active) {
            $a_query = $this->all_configs['db']->makeQuery(" AND r.active = 1 ", array());
        } else {
            // сортируем в начале активные
//            $order = 'r.active DESC,';
            // сортируем по айди
            $order = 'r.id DESC,';
        }
        // только не прикрепленные к заказу
        if ($no_order) {
            $o_query = $this->all_configs['db']->makeQuery(" AND r.order_id IS NULL ", array());
        }
        // по айди заказа (прикрепленная)
        if ($order_id) {
            $oid_query = $this->all_configs['db']->makeQuery(" AND r.order_id = ?i ", array($order_id));
            $scheme = 'row';
        }
        // разбивка по страницам
        if ($use_pages) {
            $count_on_page = count_on_page();
            $skip = (isset($_GET['p']) && $_GET['p'] > 0) ? ($count_on_page * ($_GET['p'] - 1)) : 0;
            $limit_query = $this->all_configs['db']->makeQuery(" LIMIT ?i, ?i", array($skip, $count_on_page));
        }
        // вытягуем по айдихам
        if ($ids) {
            if (is_array($ids)) {
                $ids_query = $this->all_configs['db']->makeQuery(" AND r.id IN(?li) ", array($ids));
            } else {
                $scheme = 'row';
                $ids_query = $this->all_configs['db']->makeQuery(" AND r.id = ?i ", array($ids));
            }
        }
        $fitlers_query = $this->make_reqeusts_fitlers_query();
        $where = $c_query . $p_query . $a_query . $o_query . $oid_query . $ids_query . $fitlers_query;
        $req = $this->all_configs['db']->query("SELECT r.*, c.code, c.referer_id, c.client_id, "
            . "r.id as rid, IF(u.fio = '',u.email,u.fio) as operator_fio, "
            . "IF(c.phone = '' OR c.phone IS NULL, cp.phone, c.phone) as phone, "
            . "c.date as call_date, rf.name as rf_name "
            . "FROM {crm_requests} as r "
            . "LEFT JOIN {crm_calls} as c ON c.id = r.call_id "
            . "LEFT JOIN {users} as u ON u.id = r.operator_id "
            . "LEFT JOIN {crm_referers} as rf ON rf.id = c.referer_id "
            . "LEFT JOIN {clients} as cp ON cp.id = c.client_id "
            . "WHERE " . $where
            . "ORDER BY " . $order . "r.date DESC" . $limit_query, array(), $scheme);
        if ($use_pages) {
            $count = $this->all_configs['db']->query("SELECT count(*) "
                . "FROM {crm_requests} as r "
                . "LEFT JOIN {crm_calls} as c ON c.id = r.call_id "
                . "WHERE " . $where
                , array(), 'el');
            return array($req, $count);
        } else {
            return $req;
        }
    }

    /**
     * @param        $request
     * @param string $id
     * @param bool   $extended
     * @return string
     * @throws \Exception
     */
    private function form($request, $id = '', $extended = false)
    {
        $ex_fields = '';
        if ($extended) { // в таблице со всеми заявками
            $ex_fields = '
                <td><a href="' . $this->all_configs['prefix'] . 'clients/create/' . $request['client_id'] . '">' .
                $request['client_id'] . '</a></td>
            ';
        }
        $form = '
            <tr' . (!$request['active'] ? ' class="warning"' : '') . '>
                <td>' . $request['id'] . '</td>
                <td>' . $request['operator_fio'] . '</td>
                ' . $ex_fields . '
                <td>' . do_nice_date($request['date'], true) . '</td>
                <td>
                    <div class="input-prepend">
                      <span class="add-on">
                        <span class="cursor-pointer icon-list" onclick="alert_box(this, false, 1, {service:\'crm/requests\',action:\'changes_history\',type:\'crm-request-change-status\'}, null, \'services/ajax.php\')" data-o_id="' . $id . '" title="' . l('История изменений') . '"></span>
                      </span>
                      ' . $this->get_statuses_list($request['status'], $id, false, !$request['active']) . '
                    </div>
                </td>
                <td><a href="' . $this->all_configs['siteprefix'] . gen_full_link(getMapIdByProductId($request['product_id'])) . '" target="_blank">на&nbsp;сайте</a></td>
                <td>
                    <div class="input-prepend">
                      <span class="add-on">
                       <span class="cursor-pointer icon-list" onclick="alert_box(this, false, 1, {service:\'crm/requests\',action:\'changes_history\',type:\'crm-request-change-product_id\'}, null, \'services/ajax.php\')" data-o_id="' . $id . '" title="' . l('История изменений') . '"></span>'
            . '</span>'
            . str_replace('<input', !$request['active'] ? '<input disabled' : '<input',
                typeahead($this->all_configs['db'], 'categories-goods', false, $request['product_id'], $id,
                    'input-medium', '', '', true, false, $id)
            )
            . '</div>'
            . '</td>
                <td>
                    <div class="input-prepend">
                      <span class="add-on">
                        <span class="pull-left cursor-pointer icon-list" onclick="alert_box(this, false, 1, {service:\'crm/requests\',action:\'changes_history\',type:\'crm-request-change-comment\'}, null, \'services/ajax.php\')" data-o_id="' . $id . '" title="' . l('История изменений') . '"></span>
                      </span>
                        <textarea' . (!$request['active'] ? ' disabled' : '') . ' name="comment[' . $id . ']" style="width: 140px" class="form-control" rows="2">' . htmlspecialchars($request['comment']) . '</textarea>
                    </div>
                </td>
                <td>' . ($request['order_id'] ?
                '<a href="' . $this->all_configs['prefix'] . 'orders/create/' . $request['order_id'] . '">'
                . '№' . $request['order_id'] .
                '</a>' :
                '<nobr>' . l('не принято') . '</nobr>&nbsp;<a href="#add_order_to_request" class="add_order_to_request_btn" data-id="' . $request['id'] . '" data-toggle="modal"><i class="fa fa-plus"></i></a>') . '
                </td>
                <td style="text-align:center">
                    ' . get_service('crm/sms')->get_form_btn($request['phone'], $request['id'], 'requests') . '
                </td>
            </tr>
        ';
        return $form;
    }

    /**
     * @param int    $active
     * @param string $multi
     * @param bool   $multiselect
     * @param bool   $disabled
     * @return string
     */
    public function get_statuses_list($active = 0, $multi = '', $multiselect = false, $disabled = false)
    {
        $statuses_opts = '';
        foreach ($this->statuses as $s_id => $s) {
            $statuses_opts .= '<option' . ((is_numeric($active) && (int)$active === $s_id) || (is_array($active) && in_array($s_id,
                        $active)) ? ' selected' : '') . ' value="' . $s_id . '">' . $s['name'] . '</option>';
        }
        return '<select' . ($disabled ? ' disabled' : '') . ($multiselect ? ' class="multiselect input-small form-control" multiple="multiple"' : ' class="form-control"') .
        ' style="" name="status_id' . ($multi || $multiselect ? '[' . $multi . ']' : '') . '">' .
        $statuses_opts .
        '</select>';
    }

    // вытягуем заявку по ордер айди
    /**
     * @param $order_id
     * @return array
     */
    public function get_request_by_order($order_id)
    {
        return $this->get_requests(null, null, null, null, $order_id);
    }

    // вытягуем заявку по айди
    /**
     * @param $id
     * @return array
     */
    public function get_request_by_id($id)
    {
        return $this->get_requests(null, null, null, null, null, false, $id);
    }

    // собираем запрос фильтров для списка всех заявок
    /**
     * @return string
     */
    public function make_reqeusts_fitlers_query()
    {
        $db = $this->all_configs['db'];
        $query_parts = array();
        $query = '';
        // статусы
        $statuses = !empty($_GET['status_id']) ? (array)$_GET['status_id'] : null;
        if ($statuses) {
            $query_parts[] = $db->makeQuery("r.status IN(?li)", array($statuses));
        }
        // операторы
        $operators = !empty($_GET['operators']) ? (array)$_GET['operators'] : null;
        if ($operators) {
            $query_parts[] = $db->makeQuery("c.operator_id IN(?li)", array($operators));
        }
        // дата
        $date = !empty($_GET['date']) ? $_GET['date'] : null;
        if ($date) {
            list($date_from, $date_to) = explode('-', $date);
            $query_parts[] = $db->makeQuery("(DATE(r.date) BETWEEN ? AND ?)", array(
                date('Y-m-d', strtotime($date_from)),
                date('Y-m-d', strtotime($date_to))
            ));
        }
        // клиент
        $client = !empty($_GET['clients']) ? (int)$_GET['clients'] : null;
        if ($client) {
            $query_parts[] = $db->makeQuery("c.client_id = ?i", array($client));
        }
        // заявка 
        $id = !empty($_GET['request_id']) ? (int)$_GET['request_id'] : null;
        if ($id) {
            $query_parts[] = $db->makeQuery("r.id = ?i", array($id));
        }
        // устройство
        $product = isset($_GET['categories-goods']) ? (int)$_GET['categories-goods'] : null;
        if ($product) {
            $query_parts[] = $db->makeQuery("r.product_id = ?i", array($product));
        }
        if ($query_parts) {
            $query = ' AND ' . implode(' AND ', $query_parts) . ' ';
        }
        return $query;
    }

    // юзается так же в crm/statistics
    /**
     * @return string
     */
    public function get_operators()
    {
        $operators = '
            <select class="multiselect form-control" multiple="multiple" name="operators[]">
        ';
        $managers = $this->all_configs['db']->query(
            'SELECT DISTINCT u.id, CONCAT(u.fio, " ", u.login) as name FROM {users} as u, {users_permissions} as p, {users_role_permission} as r
            WHERE (p.link=? OR p.link=?) AND r.role_id=u.role AND r.permission_id=p.id',
            array('edit-clients-orders', 'site-administration'))->assoc();
        foreach ($managers as $manager) {
            $operators .= '
                <option ' . ((isset($_GET['operators']) && in_array($manager['id'],
                        $_GET['operators'])) ? 'selected' : '') . '
                    value="' . $manager['id'] . '">' . htmlspecialchars($manager['name']) . '</option>';
        }
        $operators .= '</select>';
        return $operators;
    }

    // блок фильтров для списка всех заявок
    /**
     * @return string
     */
    public function all_requests_list_filters_block()
    {
        // операторы
        $operators = $this->get_operators();
        // дата
        $date = (isset($_GET['date']) ? htmlspecialchars(urldecode($_GET['date'])) : '');
        return $this->view->renderFile('services/crm/requests/all_requests_list_filters_block', array(
           'operators' => $operators,
            'date' => $date,
            'controller' => $this
        ));
    }

    // список всех заявок с фильтрами
    /**
     * @return string
     * @throws \Exception
     */
    public function get_all_requests_list()
    {
        $req_data = $this->get_requests(null, null, null, null, null, true);
        $list = '';
        foreach ($req_data[0] as $r) {
            $list .= $this->form($r, $r['id'], true);
        }
        $count_on_page = count_on_page();
        $count_pages = ceil($req_data[1] / $count_on_page);
        return $this->view->renderFile('services/crm/requests/get_all_requests_list', array(
            'count_pages' => $count_pages,
            'count_on_page' => $count_on_page,
            'req_data' => $req_data,
            'controller' => $this,
            'list' => $list
        ));
    }

    /**
     * @param $client_id
     * @return string
     * @throws \Exception
     */
    public function requests_list($client_id)
    {
        $req = $this->get_requests($client_id);
        $list_items = '';
        foreach ($req as $r) {
            $list_items .= $this->form($r, $r['id']);
        }

        if ($list_items) {
            $requests = '
                <form method="post" class="ajax_form" action="' . $this->all_configs['prefix'] . 'services/ajax.php">
                    <input type="hidden" name="service" value="crm/requests">
                    <input type="hidden" name="action" value="save_requests">
                    <input type="hidden" name="requests_ids" value="' . implode(',', array_keys($req)) . '">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>id</th>
                                <th>' . l('оператор') . '</th>
                                <th>' . l('дата заявки') . '</th>
                                <th>' . l('Статус') . '</th>
                                <th>' . l('ссылка') . '</th>
                                <th>' . l('устройство') . '</th>
                                <th>' . l('комментарий') . '</th>
                                <th>' . l('№ ремонта') . '</th>
                                <th style="text-align:center">SMS</th>
                            </tr>
                        </thead>
                        <tbody>
                            ' . $list_items . '
                        </tbody>
                    </table>
                    <input id="save_all_fixed" class="btn btn-primary" type="submit" value="' . l('Сохранить изменения') . '">
                </form>
                ' . $this->request_to_order_form() . '
                ' . get_service('crm/sms')->get_form('requests') . '
            ';
        } else {
            $requests = '<br><div class="center">' . l('Заявок нет') . '</div>';
        }
        $list = '
            <div class="row-fluid">
                <div class="span12">
                ' . $requests . '
                </div>
            </div>
        ';
        return $list;
    }

    /**
     * @return string
     */
    public function request_to_order_form()
    {
        return $this->view->renderFile('services/crm/requests/request_to_order_form', array()). $this->assets();
    }

    // генерит форму (строку) добавления заявки на странице нового звонка
    /**
     * @param      $client_id
     * @param      $call_id
     * @param null $request_data
     * @return string
     */
    public function get_new_request_row_form_for_call($client_id, $call_id, $request_data = null)
    {
        return '
            ' . $this->assets() . '
            <form class="ajax_form" data-callback="new_call_add_request_callback" method="post" data-submit_on_blur="categories-last-value,comment,status_id" data-on_success_set_value_for="request_id" action="' . $this->all_configs['prefix'] . 'services/ajax.php">
                <input type="hidden" name="request_id" value="' . ($request_data ? $request_data['id'] : '') . '">
                <input type="hidden" name="no_redirect" value="1">
                <input type="hidden" name="action" value="new_request">
                <input type="hidden" name="service" value="crm/requests">
                <input type="hidden" name="client_id" value="' . $client_id . '">
                <input type="hidden" name="call_id" value="' . $call_id . '">
                <div class="row-fluid new_request_row">
                    <div class="span4">'
        . typeahead($this->all_configs['db'], 'categories-last', true, $request_data ? $request_data['product_id'] : 0,
            3, 'input-medium popover-info', '')
        . '<span class="request_product">'
        . ($request_data['product_id'] ? ' <a href="' . $this->all_configs['siteprefix'] . gen_full_link(getMapIdByProductId($request_data['product_id'])) . '" target="_blank">' . l('на сайте') . '</a>' : '')
        . '</span>'
        . '</div>
                    <div class="span4">
                        ' . $this->get_statuses_list($request_data ? $request_data['status'] : null) . '
                    </div>
                    <div class="span4">
                        <textarea class="form-control" name="comment" rows="2" cols="35">' . ($request_data ? htmlspecialchars($request_data['comment']) : '') . '</textarea>
                    </div>
                    ' . ($request_data['id'] && !$request_data['order_id'] ? '
                            <a href="' . $this->all_configs['prefix'] . 'orders?on_request=' . $request_data['id'] . '#create_order" class="create_order_on_request btn btn-success btn-small">' . l('Создать заказ') . '</a>
                    ' : ($request_data['order_id'] ? '<a href="' . $this->all_configs['prefix'] . 'orders/create/' . $request_data['order_id'] . '" class="create_order_on_request">Заказ №' . $request_data['order_id'] . '</a>' : '')) . '
                </div>
            </form>
        ';
    }

    // генерит форму добавления заявок на странице звонка
    /**
     * @param $client_id
     * @param $call_id
     * @return string
     */
    public function get_new_request_form_for_call($client_id, $call_id)
    {
        $exists_requests = $this->get_requests_for_call_form($call_id);
        return '
            ' . $this->assets() . '
            <h3>Заявки</h3>
            <div class="row-fluid">
                <div class="span4">
                    <b>' . l('Устройство') . '</b>
                </div>
                <div class="span4">
                    <b>' . l('Статус') . '</b>
                </div>
                <div class="span4">
                    <b>' . l('Комментарий') . '</b>
                </div>
            </div>
            ' . $exists_requests . '
            ' . $this->get_new_request_row_form_for_call($client_id, $call_id) . '
        ';
    }

    // форма создания заявок
    /**
     * @param      $client_id
     * @param null $call_id
     * @return string
     */
    public function get_new_request_form($client_id, $call_id = null)
    {
        return '';
    }

    // список заявок на странице создания звонка
    // если обновили страницу чтобы отображались те, что уже есть
    /**
     * @param $call_id
     * @return string
     */
    public function get_requests_for_call_form($call_id)
    {
        $req = $this->all_configs['db']->query("SELECT r.*, c.client_id "
            . "FROM {crm_requests} as r "
            . "LEFT JOIN {crm_calls} as c ON c.id = r.call_id "
            . "WHERE call_id = ?i "
            . "ORDER BY r.date", array($call_id), 'assoc:id');
        $list = '';
        foreach ($req as $r) {
            $list .= $this->get_new_request_row_form_for_call($r['client_id'], $r['id'], $r);
        }
        return $list;
    }

    // прикрепляем заявку к заказу
    /**
     * @param $order_id
     * @param $request_id
     */
    public function attach_to_order($order_id, $request_id)
    {
        $this->all_configs['db']->query("UPDATE {crm_requests} SET order_id = ?i, status = ?i, active = ?i WHERE id = ?i",
            array(
                $order_id,
                self::request_in_order_status,
                $this->statuses[self::request_in_order_status]['active'],
                $request_id
            ));
        // добавить коммент 
        $operator_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $comment = $this->all_configs['db']->query("SELECT comment FROM {crm_requests} WHERE id = ?i",
            array($request_id), 'el');
        $this->all_configs['db']->query(
            "INSERT INTO {orders_comments}(date_add,text,user_id,auto,order_id,private) "
            . "VALUES(NOW(),?,?i,0,?i,1)",
            array($comment, $operator_id, $order_id)
        );
    }

    // список заявок при создании заказа по айди клиента и товара
    /**
     * @param      $client_id
     * @param      $product_id
     * @param null $active_request
     * @param bool $return_count
     * @return array|string
     */
    public function get_requests_list_by_order_client(
        $client_id,
        $product_id,
        $active_request = null,
        $return_count = false
    ) {
        $requests = $this->get_requests($client_id, $product_id, true, true);
        $response = '';
        if ($requests) {
            $txt = $client_id ? ' ' . l('клиенту') . '' : '';
            $txt = $product_id ? ' ' . l('устройству') . '' : $txt;
            $txt = $product_id && $client_id ? ' ' . l('устройству у клиента') . '' : $txt;
            $list = '' . l('Заявки по данному') . ' ' . $txt . ':<br>
                     <table class="table table-bordered table-condensed table-hover" style="max-width: 1100px">
                         <thead><tr>
                            <td>
                                <div class="radio">
                                    <label>
                                        <input' . (!$active_request ? ' checked' : '') . ' type="radio" name="crm_request" value="0">
                                        ' . l('без заявки') . '
                                    </label>
                                </div>
                            </td>
                            <!--<td>Звонок</td>-->
                            <td>' . l('Клиент') . '</td>
                            <td>' . l('Устройство') . '</td>
                            <td>' . l('Оператор') . '</td>
                            <td>' . l('Комментарий') . '</td>
                        </tr></thead><tbody>';
            foreach ($requests as $req) {
                $client = $this->all_configs['db']->query(
                    'SELECT GROUP_CONCAT(COALESCE(c.fio, ""), ", ", COALESCE(c.email, ""),
                                  ", ", COALESCE(c.phone, ""), ", ", COALESCE(p.phone, "") separator ", " ) as data, c.fio
                                FROM {clients} as c
                                LEFT JOIN {clients_phones} as p ON p.client_id=c.id AND p.phone<>c.phone
                                WHERE c.id = ?i', array($req['client_id']), 'row');
                $product = $this->all_configs['db']->query("SELECT title FROM {categories} "
                    . "WHERE id = ?i", array($req['product_id']), 'el');
                $list .=
                    '<tr>
                        <td>
                            <div class="radio">
                                <label>
                                    <input type="radio"' . ($active_request == $req['id'] ? ' checked' : '') . ' name="crm_request"  
                                        data-client_fio="' . $client['fio'] . '"
                                        data-client_id="' . $req['client_id'] . '" 
                                        data-product_id="' . $req['product_id'] . '" 
                                        data-referer_id="' . $req['referer_id'] . '" 
                                        data-code="' . $req['code'] . '" 
                                        value="' . $req['id'] . '">
                                    №' . $req['id'] . ' от ' . do_nice_date($req['date'], true, true, 0, true) . '        
                                </label>
                            </div>
                        </td>
                        <!--<td>
                            ' . do_nice_date($req['call_date'], true, true, 0, true) . '<br>
                        </td>-->
                        <td>
                            ' . $client['data'] . '
                        </td>
                        <td>
                            <a href = "' . $this->all_configs['siteprefix'] . gen_full_link(getMapIdByProductId($req['product_id'])) . '" target="_blank">' . $product . '</a>
                        </td>
                        <td>
                            <i>' . getUsernameById($req['operator_id']) . '</i><br>
                        </td>
                        <td>
                            <i>' . $req['comment'] . '</i><br>
                        </td>
                    </tr>';
            }
            $count = count($requests);
            $response = $list . '</tbody></table>' .
                // показываем алерт с введите фио есть у выбранной заявки нету фио клиента
                ($active_request ? '<script>check_active_request()</script>' : '');
        } else {
            $count = 0;
            $response = 'Заявок нет.';
        }
        if ($return_count) {
            return array($count, $response);
        } else {
            return $response;
        }
    }

    /**
     * @param $data
     * @return array
     */
    public function ajax($data)
    {
        $operator_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $response = array();
        switch ($data['action']) {
            // создаем заявку
            case 'new_request':
                $response['state'] = true;
                $client_id = $data['client_id'];
                $request_id = isset($data['request_id']) ? $data['request_id'] : null;
                $call_id = isset($data['call_id']) ? $data['call_id'] : null;
                $product_id = isset($data['categories-last']) ? $data['categories-last'] : null;
                $status_id = isset($data['status_id']) ? $data['status_id'] : null;
                $comment = isset($data['comment']) ? $data['comment'] : null;
                if (!$call_id) {
                    $response['state'] = false;
                    $response['msg'] = l('Выберите звонок');
                }
                if ($response['state'] && !$product_id) {
                    $response['state'] = false;
                    $response['msg'] = l('Выберите устройство');
                }
                if ($response['state']) {
                    if ($request_id) {
                        // обновляем (например, на странице нового звонка. вроде больше нигде не используется такое)
                        $this->all_configs['db']->query(
                            "UPDATE {crm_requests}"
                            . "SET product_id = ?i, comment = ?, status = ?i, active = ?i "
                            . "WHERE id = ?i", array(
                                $product_id,
                                $comment,
                                $status_id,
                                $this->statuses[$status_id]['active'],
                                $request_id
                            )
                        );
                    } else {
                        // добавляем
                        $response['request_id'] = $this->all_configs['db']->query(
                            "INSERT INTO {crm_requests}(call_id,operator_id,product_id,comment,status,active,date) "
                            . "VALUES(?i,?i,?i,?,?i,?i,NOW())", array(
                            $call_id,
                            $operator_id,
                            $product_id,
                            $comment,
                            $status_id,
                            $this->statuses[$status_id]['active']
                        ), 'id'
                        );
                        $response['create_order_btn'] = '
                            <a href="' . $this->all_configs['prefix'] . 'orders?on_request=' . $response['request_id'] . '#create_order" class="create_order_on_request btn btn-success btn-small">' . l('Создать заказ') . '</a>
                        ';
                        if (!isset($data['no_redirect'])) {
                            $response['redirect'] = $this->all_configs['prefix'] . 'clients/create/' . $data['client_id'] . '?update=' . time() . '#requests';
                        } else {
                            $response['after'] = $this->get_new_request_row_form_for_call($client_id, $call_id);
                        }
                    }
                    if ($product_id) {
                        $response['product_site_url'] = ' <a href="' . $this->all_configs['siteprefix'] . gen_full_link(getMapIdByProductId($product_id)) . '" target="_blank">' . l('на сайте') . '</a>';
                    }
                    $response['state'] = true;
                }
                break;
            // сохраняем изменения в заявках
            case 'save_requests':
                if (isset($data['status_id'])) {
                    $requests_ids = explode(',', $data['requests_ids']);
                    $req = $this->get_requests(null, null, null, null, null, false, $requests_ids);
                    $response['state'] = true;
                    foreach ($data['status_id'] as $req_id => $status) {
                        if (!isset($req[$req_id]) || !$req[$req_id]['active']) {
                            continue;
                        }
                        $new_status = $status;
                        $new_product_id = $data['categories-goods'][$req_id];
                        $new_comment = $data['comment'][$req_id];
                        $changes = array();
                        if ($new_status != $req[$req_id]['status']) {
                            $changes[] = $this->all_configs['db']->makeQuery(
                                '(?i, ?, null, ?i, ?)',
                                array(
                                    $operator_id,
                                    'crm-request-change-status',
                                    $req_id,
                                    $this->statuses[$req[$req_id]['status']]['name'] . ' ==> ' . $this->statuses[$new_status]['name']
                                )
                            );
                        }
                        if ($new_product_id != $req[$req_id]['product_id']) {
                            $current_product_name = $this->all_configs['db']->query("SELECT title FROM {categories} "
                                . "WHERE id = ?i", array($req[$req_id]['product_id']), 'el');
                            $changes[] = $this->all_configs['db']->makeQuery(
                                '(?i, ?, null, ?i, ?)',
                                array(
                                    $operator_id,
                                    'crm-request-change-product_id',
                                    $req_id,
                                    $current_product_name . ' ==> ' . $data['categories-goods-value'][$req_id]
                                )
                            );
                        }
                        if ($new_comment != $req[$req_id]['comment']) {
                            $changes[] = $this->all_configs['db']->makeQuery(
                                '(?i, ?, null, ?i, ?)',
                                array(
                                    $operator_id,
                                    'crm-request-change-comment',
                                    $req_id,
                                    $req[$req_id]['comment'] . ' ==> ' . $new_comment
                                )
                            );
                        }
                        if ($changes) {
                            $this->all_configs['db']->query(
                                'INSERT INTO {changes}(user_id, work, map_id, object_id, `change`) VALUES ?q',
                                array(implode(',', $changes))
                            );
                        }
                        $this->all_configs['db']->query(
                            "UPDATE {crm_requests} SET product_id = ?i, comment = ?, status = ?i, active = ?i WHERE id = ?i",
                            array(
                                $new_product_id,
                                $new_comment,
                                $new_status,
                                $this->statuses[$new_status]['active'],
                                $req_id
                            )
                        );
                    }
                } else {
                    $response['state'] = false;
                    $response['msg'] = 'no data';
                }
                break;
            // история изменений для заявок и звонков
            case 'changes_history':
                if (isset($data['object_id'])) {
                    $response['state'] = true;
                    $changes = $this->all_configs['db']->query(
                        'SELECT u.login, u.email, u.fio, u.phone, ch.change, ch.date_add 
                         FROM {changes} as ch
                         LEFT JOIN {users} as u ON u.id=ch.user_id 
                         WHERE ch.object_id=?i AND work=? ORDER BY ch.date_add DESC',
                        array($data['object_id'], $data['type']))->assoc();
                    if ($changes) {
                        $c = '<table class="table"><thead><tr><td>' . l('manager') . '</td><td>' . l('Дата') . '</td><td>' . l('Изменение') . '</td></tr></thead><tbody>';
                        foreach ($changes as $change) {
                            $c .= '<tr><td>' . get_user_name($change) . '</td>' .
                                '<td><span title="' . do_nice_date($change['date_add'],
                                    false) . '">' . do_nice_date($change['date_add']) . '</span></td>' .
                                '<td>' . htmlspecialchars($change['change']) . '</td></tr>';
                        }
                        $c .= '</tbody></table>';
                        $response['content'] = $c;
                    } else {
                        $response['content'] = l('История не найдена');
                    }
                }
                break;
            // достаем заявки при создании заказа
            case 'get_request_fro_order':
                $client_id = isset($data['client_id']) ? (int)$data['client_id'] : 0;
                $product_id = isset($data['product_id']) ? (int)$data['product_id'] : 0;
                $response['state'] = true;
                if ($client_id || $product_id) {
                    $get = $this->get_requests_list_by_order_client($client_id, $product_id, null, true);
                    $response['has_requests'] = $get[0];
                    $response['content'] = $get[1];
                } else {
                    $response['has_requests'] = 0;
                    $response['content'] = l('Заявок нет.');
                }
                break;
            // привязываем заявку к заказу
            case 'requests_to_order':
                $request_id = isset($_POST['request_id']) ? (int)$_POST['request_id'] : null;
                $order_id = isset($_POST['order_id']) ? (int)$_POST['order_id'] : null;
                $request = $this->get_requests(null, null, null, null, null, null, $request_id);
                if ($request/* && $request['active']*/) {
                    $order_data = $this->all_configs['db']->query(
                        "SELECT o.user_id, o.category_id FROM {orders} as o "
                        . "WHERE (SELECT id FROM {crm_requests} WHERE order_id = o.id) IS NULL"
                        . " AND o.id = ?i", array($order_id), 'row');
                    if ($order_data) {
                        if ($order_data['user_id'] == $request['client_id']) {
                            if ($request['product_id'] == $order_data['category_id']) {
                                $response['state'] = true;
                                $this->attach_to_order($order_id, $request_id);
                                $response['redirect'] = $this->all_configs['prefix'] . 'orders/create/' . $order_id;
                            } else {
                                $response['msg'] = l('Устройство в заказе и заявке должно быть одно и то же');
                            }
                        } else {
                            $response['msg'] = l('Клиент заявки не совпадает с клиентом в заказе');
                        }
                    } else {
                        $response['msg'] = l('Заказ не найден или уже имеет заявку');
                    }
                } else {
                    $response['msg'] = l('Заявка не найдена');// или закрыта';
                }
                break;
        }
        return $response;
    }

    /**
     * @return string
     */
    private function assets()
    {
        if (!isset($this->assets_added)) {
            $this->assets_added = true;
            return '
                <link rel="stylesheet" href="' . $this->all_configs['prefix'] . 'services/crm/requests/css/main.css">
                <script type="text/javascript" src="' . $this->all_configs['prefix'] . 'services/crm/requests/js/main.js?3"></script>
            ';
        }
        return '';
    }

    /**
     * @return null|requests
     */
    public static function getInstanse()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * requests constructor.
     */
    private function __construct()
    {
        global $all_configs;
        $this->set_statuses();
        $this->view = new \View($all_configs);
    }
}
