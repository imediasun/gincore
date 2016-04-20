<?php

require_once __DIR__.'/../../Core/Response.php';
require_once __DIR__.'/../../Core/View.php';
require_once __DIR__.'/../../Core/FlashMessage.php';
require_once __DIR__ . '/../../Tariff.php';

$moduleactive[10] = !$ifauth['is_2'];
$modulename[10] = 'orders';
$modulemenu[10] = l('orders');

class orders
{
    /** @var View */
    protected $view = null;
    private $mod_submenu;
    protected $all_configs;
    public $count_on_page;

    /**
     * orders constructor.
     * @param      $all_configs
     * @param bool $gen_module
     */
    function __construct(&$all_configs, $gen_module = true)
    {
        $this->mod_submenu = self::get_submenu();

        $this->all_configs = $all_configs;
        $this->view = new View($this->all_configs);

        if($gen_module){
            $this->count_on_page = count_on_page();

            global $input_html;

            require_once($this->all_configs['sitepath'] . 'shop/model.class.php');
            require_once($this->all_configs['sitepath'] . 'shop/cart.class.php');
            require_once($this->all_configs['sitepath'] . 'mail.php');

            if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
                $this->ajax();
            }

            if ($this->can_show_module() == false) {
                return $input_html['mcontent'] = '<div class="span3"></div>
                    <div class="span9"><p  class="text-danger">'.l('У Вас нет прав для управления заказами').'</p></div>';
            }

            // если отправлена форма
            if (!empty($_POST))
                $this->check_post($_POST);


            if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'create' && isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] > 0 ) {
                $input_html['mcontent'] = $this->genorder();
            } else {
                $input_html['mcontent'] = $this->gencontent();
            }
        }
    }

    /**
     * @return bool
     */
    function can_show_module()
    {
        if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')
                || $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')
                || $this->all_configs['oRole']->hasPrivilege('edit-tradein-orders')
                || $this->all_configs['oRole']->hasPrivilege('show-clients-orders')
                || $this->all_configs['oRole']->hasPrivilege('orders-manager')) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $post
     */
    function check_post ($post)
    {
        $mod_id = $this->all_configs['configs']['orders-manage-page'];
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';



        // фильтруем заказы клиентов
        if (isset($post['filter-orders'])) {

            $url = '';

            // фильтр по дате
            if (isset($post['date']) && !empty($post['date'])) {
                list($df, $dt) = explode('-', $post['date']);
                $url .= 'df=' . urlencode(trim($df)) . '&dt=' . urlencode(trim($dt));
            }

            if (isset($post['categories']) && $post['categories'] > 0) {
                // фильтр по категориям товаров
                if (!empty($url))
                    $url .= '&';
                $url .= 'g_cg=' . intval($post['categories']);
            }

            if (isset($post['np'])) {
                // фильтр принято через нп
                if (!empty($url))
                    $url .= '&';
                $url .= 'np=1';
            }

            if (isset($post['wh-kiev'])) {
                // фильтр киев
                if (!empty($url))
                    $url .= '&';
                $url .= 'whk=1';
            }

            if (isset($post['wh-abroad'])) {
                // фильтр заграница
                if (!empty($url))
                    $url .= '&';
                $url .= 'wha=1';
            }

            if (isset($post['noavail'])) {
                // фильтр не активные
                if (!empty($url))
                    $url .= '&';
                $url .= 'avail=0';
            }

            if (isset($post['rf'])) {
                // фильтр выдан подменный фонд
                if (!empty($url))
                    $url .= '&';
                $url .= 'rf=1';
            }

            if (isset($post['nm'])) {
                // не оплаченные
                if (!empty($url))
                    $url .= '&';
                $url .= 'nm=1';
            }

            if (isset($post['ar'])) {
                // принимались на доработку
                if (!empty($url))
                    $url .= '&';
                $url .= 'ar=1';
            }

            if (isset($post['order_id']) && $post['order_id'] > 0) {
                // фильтр по id
                if (!empty($url))
                    $url .= '&';
                $url .= 'co_id=' . intval($post['order_id']);
            }

            if (isset($post['categories-last']) && $post['categories-last'] > 0) {
                // фильтр по категориям (устройство)
                if (!empty($url))
                    $url .= '&';
                $url .= 'dev=' . intval($post['categories-last']);
            }

            if (isset($post['so-status']) && $post['so-status'] > 0) {
                // фильтр по статусу
                if (!empty($url))
                    $url .= '&';
                $url .= 'sst=' . intval($post['so-status']);
            }

            if (isset($post['goods-goods']) && $post['goods-goods'] > 0) {
                // фильтр по товару
                if (!empty($url))
                    $url .= '&';
                $url .= 'by_gid=' . intval($post['goods-goods']);
            }

            if (isset($post['warehouse']) && !empty($post['warehouse'])) {
                // фильтр по инженерам
                if (!empty($url))
                    $url .= '&';
                $url .= 'wh=' . implode(',', $post['warehouse']);
            }

            if (isset($post['engineers']) && !empty($post['engineers'])) {
                // фильтр по инженерам
                if (!empty($url))
                    $url .= '&';
                $url .= 'eng=' . implode(',', $post['engineers']);
            }

            if (isset($post['managers']) && !empty($post['managers'])) {
                // фильтр по менеджерам
                if (!empty($url))
                    $url .= '&';
                $url .= 'mg=' . implode(',', $post['managers']);
            }

            if (isset($post['accepter']) && !empty($post['accepter'])) {
                // фильтр по приемщикам
                if (!empty($url))
                    $url .= '&';
                $url .= 'acp=' . implode(',', $post['accepter']);
            }

            if (isset($post['wh_groups']) && !empty($post['wh_groups'])) {
                // фильтр по поставщикам
                if (!empty($url))
                    $url .= '&';
                $url .= 'wg=' . implode(',', $post['wh_groups']);
            }

            if (isset($post['suppliers']) && !empty($post['suppliers'])) {
                // фильтр по поставщикам
                if (!empty($url))
                    $url .= '&';
                $url .= 'sp=' . implode(',', $post['suppliers']);
            }

            if (isset($post['status']) && !empty($post['status'])) {
                // фильтр по статусу
                if (!empty($url))
                    $url .= '&';
                $url .= 'st=' . implode(',', $post['status']);
            }

            if (isset($post['client']) && !empty($post['client'])) {
                // фильтр клиенту/заказу
                if (!empty($url))
                    $url .= '&';
                $url .= 'cl=' . urlencode(trim($post['client']));
            }

            if (isset($post['client-order']) && !empty($post['client-order'])) {
                // фильтр клиенту/заказу
                if (!empty($url))
                    $url .= '&';
                $url .= 'co=' . urlencode(trim($post['client-order']));
            }

            if (isset($post['supplier_order_id_part']) && $post['supplier_order_id_part'] > 0) {
                // фильтр по заказу частичный
                if (!empty($url))
                    $url .= '&';
                $url .= 'pso_id=' . $post['supplier_order_id_part'];
            }

            if (isset($post['supplier_order_id']) && $post['supplier_order_id'] > 0) {
                // фильтр по заказу
                if (!empty($url))
                    $url .= '&';
                $url .= 'so_id=' . $post['supplier_order_id'];
            }

            if (isset($post['my']) && !empty($post['my'])) {
                // фильтр по
                if (!empty($url))
                    $url .= '&';
                $url .= 'my=1';
            }

            if (isset($post['serial']) && !empty($post['serial'])) {
                // фильтр серийнику
                if (!empty($url))
                    $url .= '&';
                $url .= 'serial=' . trim($post['serial']);
            }

            $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . $url);
            header('Location: ' . $url);
            exit;
        }

        /*if ( isset($post['edit-callback']) && $this->all_configs['oRole']->hasPrivilege('mess-callback')) {
            // управление обратным звонком
            if ( isset($post['callback']) && is_array($post['callback']) && count($post['callback']) ) {
                foreach ( $post['callback'] as $order_id=>$status ) {
                    $this->all_configs['db']->query('UPDATE {callback} SET status=?i WHERE id=?i', array($status, $order_id));
                }
            }
        } elseif ( isset($post['edit-tradein']) && $this->all_configs['oRole']->hasPrivilege('edit-tradein-orders') ) {
            // управление скупками
            if ( isset($post['tradein']) && is_array($post['tradein']) && count($post['tradein']) ) {
                foreach ( $post['tradein'] as $order_id=>$status ) {
                    $this->all_configs['db']->query('UPDATE {tradein} SET status=?i WHERE id=?i', array($status, $order_id));
                }
            }
        }*/
        // принимаем заказ
        if (isset($post['accept-manager']) == 1 && isset($post['id']) && $post['id'] > 0 && $this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
            $this->all_configs['db']->query('UPDATE {orders} SET manager=?i WHERE id=?i AND (manager IS NULL OR manager=0 OR manager="")',
                array($user_id, $post['id']));
            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                array($user_id, 'manager-accepted-order', $mod_id, $post['id']));
        }

        // фильтрация рекомендаций к закупкам
        if (isset($_POST['procurement-filter'])) {
            $url = '';

            // фильтр по дате
            if (isset($post['date']) && !empty($post['date'])) {
                list($df, $dt) = explode('-', $post['date']);
                $url .= 'df=' . urlencode(trim($df)) . '&dt=' . urlencode(trim($dt));
            }

            if (isset($post['ctg']) && is_array($post['ctg']) && count($post['ctg']) > 0) {
                if (!empty($url))
                    $url .= '&';
                $url .= 'ctg=' . implode(',', $post['ctg']);
            }
            if (isset($post['tso']) && intval($post['tso']) > 0) {
                if (!empty($url))
                    $url .= '&';
                $url .= 'tso=' . intval($post['tso']);
            }

            $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . $url);
            header('Location: ' . $url);
            exit;
        }

        if(isset($_POST['hide-fields'])) {
            $this->order_fields_setup();
        }

        header("Location:" . $_SERVER['REQUEST_URI']);
        exit;
    }

    /**
     * @param bool $compact
     * @param bool $showWrapper
     * @return string
     */
    function show_filter_manager($compact = false, $showWrapper = true)
    {
        $managers = $this->all_configs['db']->query(
            'SELECT DISTINCT u.id, CONCAT(u.fio, " ", u.login) as name FROM {users} as u, {users_permissions} as p, {users_role_permission} as r
            WHERE (p.link=? OR p.link=?) AND r.role_id=u.role AND r.permission_id=p.id',
            array('edit-clients-orders', 'site-administration'))->assoc();
        $mg_get = isset($_GET['mg']) ? explode(',', $_GET['mg']) :
            (isset($_GET['managers']) ? $_GET['managers'] : array());
        return $this->view->renderFile('orders/show_filter_manager', array(
            'compact' => $compact,
            'showWrapper' => $showWrapper,
            'mg_get' => $mg_get,
            'managers' => $managers
        ));
    }

    /**
     * @param bool $full_link
     * @return string
     */
    function clients_orders_menu($full_link = false)
    {
        if($full_link){
            $link = $this->all_configs['prefix'].'orders';
        }else{
            $link = '';
        }
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : ''/*date('01.m.Y', time())*/)
            . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
            . (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : ''/*date('t.m.Y', time())*/);

        $count = $this->all_configs['db']->query('SELECT COUNT(id) FROM {orders}', array())->el();
        $count_unworked = $this->all_configs['db']->query('SELECT COUNT(id) FROM {orders}
            WHERE manager IS NULL OR manager=""', array())->el();
        $count_marked = $this->all_configs['db']->query('SELECT COUNT(id) FROM {users_marked}
            WHERE user_id=?i AND type=?', array($_SESSION['id'], 'co'))->el();
        // индинеры
        $engineers = $this->all_configs['db']->query(
            'SELECT DISTINCT u.id, CONCAT(u.fio, " ", u.login) as name FROM {users} as u, {users_permissions} as p, {users_role_permission} as r
            WHERE p.link=? AND r.role_id=u.role AND r.permission_id=p.id',
            array('engineer'))->assoc();
        $accepters = $this->all_configs['db']->query(
            'SELECT DISTINCT u.id, CONCAT(u.fio, " ", u.login) as name FROM {users} as u, {users_permissions} as p, {users_role_permission} as r
            WHERE (p.link=? OR p.link=?) AND r.role_id=u.role AND r.permission_id=p.id',
            array('create-clients-orders', 'site-administration'))->assoc();
        // фильтр по складам (дерево)
        $data = $this->all_configs['db']->query('SELECT w.id, w.title, gr.name, gr.color, tp.icon, w.group_id
            FROM {orders} as o, {warehouses} as w
            LEFT JOIN {warehouses_groups} as gr ON gr.id=w.group_id
            LEFT JOIN {warehouses_types} as tp ON tp.id=w.type_id
            WHERE o.accept_wh_id=w.id', array())->assoc();
        if ($data) {
            $wfs = array('groups' => array(), 'nogroups' => array());
            foreach ($data as $wf) {
                if ($wf['group_id'] > 0) {
                    $wfs['groups'][$wf['group_id']]['name'] = htmlspecialchars($wf['name']);
                    $wfs['groups'][$wf['group_id']]['warehouses'][$wf['id']]['color'] = htmlspecialchars($wf['color']);
                    $wfs['groups'][$wf['group_id']]['warehouses'][$wf['id']]['icon'] = htmlspecialchars($wf['icon']);
                    $wfs['groups'][$wf['group_id']]['warehouses'][$wf['id']]['title'] = htmlspecialchars($wf['title']);
                } else {
                    $wfs['nogroups'][$wf['id']]['title'] = htmlspecialchars($wf['title']);
                    $wfs['nogroups'][$wf['id']]['icon'] = htmlspecialchars($wf['icon']);
                    $wfs['nogroups'][$wf['id']]['color'] = htmlspecialchars($wf['color']);
                    $wfs['nogroups'][$wf['id']]['icon'] .= ' text-danger';
                }
            }
        }

        return $this->view->renderFile('orders/clients_orders_menu', array(
           'accepters' => $accepters,
            'engineers' => $engineers,
           'filter_manager' => $this->show_filter_manager(true),
            'count' => $count,
            'count_marked' => $count_marked,
            'count_unworked' => $count_unworked,
            'date' => $date,
            'link' => $link,
            'wfs' => isset($wfs)?$wfs:array()
        ));
    }

    /**
     * @return string
     */
    function gencontent()
    {
        return $this->view->renderFile('orders/gencontent', array(
            'mod_submenu' => $this->mod_submenu
        ));
    }

    /**
     * @param bool $full_link
     * @return string
     */
    function clients_orders_navigation($full_link = false){
        $link = ($full_link) ? $this->all_configs['prefix'] . 'orders' : '';
        return $this->view->renderFile('orders/clients_orders_navigation', array(
            'link' => $link,
            'clientsOrdersMenu' => $this->clients_orders_menu($full_link),
            'prefix' => $this->all_configs['prefix'],
            'hasPrivilege' => $this->all_configs['oRole']->hasPrivilege('create-clients-orders')
        ));
    }

    /**
     * @param string $hash
     * @return array
     */
    public function orders_show_orders($hash = '#show_orders-orders')
    {
        if (trim($hash) == '#show_orders' || (trim($hash) != '#show_orders-orders' && trim($hash) != '#show_orders-sold'
                && trim($hash) != '#show_orders-return' && trim($hash) != '#show_orders-writeoff')
        ) {
            $hash = '#show_orders-orders';
        }

        $orders_html = $this->view->renderFile('orders/orders_show_orders', array(
            'clientsOrdersNavigation' => $this->clients_orders_navigation(),
            'hasPrivilege' => $this->all_configs['oRole']->hasPrivilege('show-clients-orders')
        ));

        return array(
            'html' => $orders_html,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')', 'reset_multiselect()', 'gen_tree()'),
        );
    }

    /**
     * @param $search
     * @param $filters
     * @return array
     */
    public function simpleSearch($search, $filters)
    {
        $query = '';
        $orders = array();
        foreach (array('o_id', 'c_phone', 'o_serial' , 'c_fio','device', 'manager', 'accepter', 'engineer') as $item) {
            $queries = $this->all_configs['manageModel']->clients_orders_query($filters + array($item => $search));
            $query = $queries['query'];
            $orders = $this->getOrders($query, $queries['skip'], $this->count_on_page);
            if (!empty($orders)) {
                break;
            }
        }
        return array($query, $orders);
    }

    /**
     * @param $query
     * @param $skip
     * @param $count_on_page
     * @return mixed
     */
    public function getOrders($query, $skip, $count_on_page)
    {
        return $this->all_configs['manageModel']->get_clients_orders($query, $skip, $count_on_page, 'co');
    }

    /**
     * @return array
     */
    function show_orders_orders()
    {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $filters = array('type' => ORDER_REPAIR);
        if ($this->all_configs['oRole']->hasPrivilege('partner') && !$this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $filters['acp'] = $user_id;
        }
        if (isset($_GET['simple'])) {
            $search = $_GET['simple'];
            unset($_GET['simple']);
            list($query, $orders) = $this->simpleSearch($search, $filters + $_GET);
        } else {
            $queries = $this->all_configs['manageModel']->clients_orders_query($filters + $_GET);
            $query = $queries['query'];
            // достаем заказы
            $orders = $this->getOrders($query, $queries['skip'], $this->count_on_page);
        }

        $count = $this->all_configs['manageModel']->get_count_clients_orders($query, 'co');
        $count_page = ceil($count / $this->count_on_page);

        return array(
            'html' => $this->view->renderFile('orders/show_orders_orders', array(
                'count' => $count,
                'count_page' => $count_page,
                'orders' => $orders
            )),
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    function show_orders_sold()
    {
        $orders_html = '';
        $queries = $this->all_configs['manageModel']->clients_orders_query(array('type' => ORDER_SELL) + $_GET);
        $query = $queries['query'];
        $skip = $queries['skip'];
        $count_on_page = $this->count_on_page;

        // достаем заказы
        $orders = $this->all_configs['manageModel']->get_clients_orders($query, $skip, $count_on_page, 'co');

        if ($orders && count($orders) > 0) {
            $orders_html .= '<table class="table"><thead><tr><td></td><td>' . l('номер заказа') . '</td><td>'.l('Дата').'</td>';
            $orders_html .= '<td>'.l('Приемщик').'</td><td>' . l('manager') . '</td><td>'.l('Статус').'</td><td>' . l('Устройство') . '</td>';
            if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                $orders_html .= '<td>' . l('Стоимость') . '</td><td>' . l('Оплачено') . '</td>';
            }
            $orders_html .= '<td>' . l('Клиент') . '</td><td>' . l('Контактный тел') . '</td>';
            $orders_html .= '<td>' . l('Сроки') . '</td><td>' . l('Склад') . '</td></tr></thead><tbody id="table_clients_orders">';

            foreach ($orders as $order) {
                $orders_html .= display_client_order($order);
            }
            $orders_html .= '</tbody></table>';

            // количество заказов клиентов
            $count = $this->all_configs['manageModel']->get_count_clients_orders($query, 'co');

            $count_page = ceil($count / $count_on_page);

            // строим блок страниц
            $orders_html .= page_block($count_page, $count, '#show_orders');

        } else {
            $orders_html .= '<div class="span9"><p  class="text-danger">' . l('Заказов не найдено') . '</p></div>';
        }

        return array(
            'html' => $orders_html,
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    function show_orders_return()
    {
        $orders_html = '';
        $queries = $this->all_configs['manageModel']->clients_orders_query(array('type' => ORDER_RETURN) + $_GET);
        $query = $queries['query'];
        $skip = $queries['skip'];
        $count_on_page = $this->count_on_page;

        // достаем заказы
        $orders = $this->all_configs['manageModel']->get_clients_orders($query, $skip, $count_on_page, 'co');

        if ($orders && count($orders) > 0) {
            $orders_html .= '<div id="show_orders"><table class="table"><thead><tr><td></td><td>' . l('номер заказа') . '</td><td>'.l('Дата').'</td>';
            $orders_html .= '<td>'.l('Приемщик').'</td><td>' . l('manager') . '</td><td>'.l('Статус').'</td><td>' . l('Устройство') . '</td>';
            if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                $orders_html .= '<td>' . l('Стоимость') . '</td><td>' . l('Оплачено') . '</td>';
            }
            $orders_html .= '<td>' . l('Клиент') . '</td><td>Контактный тел</td>';
            $orders_html .= '<td>' . l('Сроки') . '</td><td>' . l('Склад') . '</td></tr></thead><tbody id="table_clients_orders">';

            foreach ($orders as $order) {
                $orders_html .= display_client_order($order);
            }
            $orders_html .= '</tbody></table></div>';

            // количество заказов клиентов
            $count = $this->all_configs['manageModel']->get_count_clients_orders($query, 'co');

            $count_page = ceil($count / $count_on_page);

            // строим блок страниц
            $orders_html .= page_block($count_page, $count, '#show_orders');

        } else {
            $orders_html .= '<div class="span9"><p  class="text-danger">' . l('Заказов не найдено') . '</p></div>';
        }

        return array(
            'html' => $orders_html,
            'menu' => $this->clients_orders_menu(),
            'functions' => array('reset_multiselect()','gen_tree()'),
        );
    }

    /**
     * @return array
     */
    function show_orders_writeoff()
    {
        $orders_html = '';
        $queries = $this->all_configs['manageModel']->clients_orders_query(array('type' => ORDER_WRITE_OFF) + $_GET);
        $query = $queries['query'];
        $skip = $queries['skip'];
        $count_on_page = $this->count_on_page;

        // достаем заказы
        $orders = $this->all_configs['manageModel']->get_clients_orders($query, $skip, $count_on_page, 'co');

        if ($orders && count($orders) > 0) {
            $orders_html .= '<table class="table"><thead><tr><td></td><td>' . l('номер заказа') . '</td><td>'.l('Дата').'</td>';
            $orders_html .= '<td>'.l('Приемщик').'</td><td>' . l('manager') . '</td><td>'.l('Статус').'</td><td>' . l('Устройство') . '</td>';
            if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                $orders_html .= '<td>' . l('Стоимость') . '</td><td>' . l('Оплачено') . '</td>';
            }
            $orders_html .= '<td>' . l('Клиент') . '</td><td>' . l('Контактный тел') . '</td>';
            $orders_html .= '<td>' . l('Сроки') . '</td><td>' . l('Склад') . '</td></tr></thead><tbody id="table_clients_orders">';

            foreach ($orders as $order) {
                $orders_html .= display_client_order($order);
            }
            $orders_html .= '</tbody></table>';

            // количество заказов клиентов
            $count = $this->all_configs['manageModel']->get_count_clients_orders($query, 'co');

            $count_page = ceil($count / $count_on_page);

            // строим блок страниц
            $orders_html .= page_block($count_page, $count, '#show_orders');

        } else {
            $orders_html .= '<div class="span9"><p  class="text-danger">' . l('Заказов не найдено') . '</p></div>';
        }

        return array(
            'html' => $orders_html,
            'functions' => array(),
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    function orders_create_order()
    {
        $orders_html = '';
        if ($this->all_configs['oRole']->hasPrivilege('create-clients-orders')) {

            // на основе заявки
            $order_data = null;
            if (!empty($_GET['on_request'])) {
                $order_data = get_service('crm/requests')->get_request_by_id($_GET['on_request']);
            }

            $client_id = $order_data ? $order_data['client_id'] : 0;
            if (!$client_id) {
                $client_id = isset($_GET['c']) ? (int)$_GET['c'] : 0;
            }
            //вывод списска клиентов для создания нового заказа
            $orders_html = $this->view->renderFile('orders/orders_create_order', array(
                'client' => client_double_typeahead($client_id, 'get_requests'),
                'colorsSelect' => $this->view->renderFile('orders/_colors-select', array(
                    'colors' => $this->all_configs['configs']['devices-colors']
                )),
                'order' => $order_data,
                'orderForSaleForm' => $this->order_for_sale_form($client_id),
                'hide' => $this->getHideFieldsConfig(),
                'tag' => $this->getTag($client_id),
                'tags' => $this->getTags(),
                'order_data' => $order_data,
                'available' => Tariff::isAddOrderAvailable($this->all_configs['configs']['api_url'], $this->all_configs['configs']['host']),
            ));
        }

        return array(
            'html' => $orders_html,
            'functions' => array(),
        );
    }

    /**
     * @param null $clientId
     * @return string
     */
    function order_for_sale_form($clientId = null)
    {
        $order_data = null;
        $client_fields_for_sale = client_double_typeahead();
        return $this->view->renderFile('orders/order_for_sale_form', array(
            'client' => $client_fields_for_sale,
            'orderWarranties' => isset($this->all_configs['settings']['order_warranties']) ? explode(',',
                $this->all_configs['settings']['order_warranties']) : array(),
            'tags' => $this->getTags(),
            'tag' => empty($clientId)? array():$this->getTag($clientId),
        ));
    }

    /**
     * @param string $hash
     * @return array
     */
    function orders_show_suppliers_orders($hash = '#show_suppliers_orders')
    {
        if (trim($hash) == '#show_suppliers_orders' || (trim($hash) != '#show_suppliers_orders-all'
                && trim($hash) != '#show_suppliers_orders-wait' && trim($hash) != '#show_suppliers_orders-procurement'
                && trim($hash) != '#show_suppliers_orders-return')
        ) {
            $hash = '#show_suppliers_orders-all';
        }

        return array(
            'html' => $this->view->renderFile('orders/orders_show_suppliers_orders'),
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')', 'reset_multiselect()'),
        );
    }

    /**
     * @return array
     */
    function orders_show_suppliers_orders_all()
    {
        $orders_html = '';

        if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
            $my = $this->all_configs['oRole']->hasPrivilege('site-administration') || $this->all_configs['oRole']->hasPrivilege('read-other-suppliers-orders')? false : true;
            $_GET['my'] = $my || (isset($_GET['my']) && $_GET['my'] == 1) ? true : false;
            $queries = $this->all_configs['manageModel']->suppliers_orders_query($_GET);
            $query = $queries['query'];
            $skip = $queries['skip'];
            $count_on_page = $this->count_on_page;//$queries['count_on_page'];

            $orders = $this->all_configs['manageModel']->get_suppliers_orders($query, $skip, $count_on_page);
            $orders_html .= $this->all_configs['suppliers_orders']->show_suppliers_orders($orders);

            $count = $this->all_configs['manageModel']->get_count_suppliers_orders($query);

            $count_page = $count_on_page > 0 ? ceil($count / $count_on_page) : 0;

            // строим блок страниц
            $orders_html .= page_block($count_page, $count, '#show_suppliers_orders-all');
        }

        return array(
            'html' => $orders_html,
            'menu' => $this->all_configs['suppliers_orders']->show_filters_suppliers_orders(true),
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @return array
     */
    function orders_show_suppliers_orders_wait()
    {
        $orders_html = '';

        if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
            $my = $this->all_configs['oRole']->hasPrivilege('site-administration') || $this->all_configs['oRole']->hasPrivilege('read-other-suppliers-orders')? false : true;
            $_GET['my'] = $my || (isset($_GET['my']) && $_GET['my'] == 1) ? true : false;

            // заказы клиентов на которых можно проверить изделия
            $data = $this->all_configs['db']->query('SELECT i.goods_id, o.id
                FROM {warehouses_goods_items} as i, {orders} as o, {category_goods} as cg
                WHERE o.status NOT IN (?li) AND cg.goods_id=i.goods_id AND cg.category_id=o.category_id',
                array($this->all_configs['configs']['order-statuses-closed']))->assoc();
            $serials = array();
            $g = array();
            if ($data) {
                foreach ($data as $s) {
                    $g[$s['goods_id']] = $s['goods_id'];
                    $url = $this->all_configs['prefix'] . 'orders/create/' . $s['id'];
                    $serials[$s['goods_id']][$s['id']] = '<a href="' . $url . '">' . $s['id'] . '</a>';
                }
            }
            $queries = $this->all_configs['manageModel']->suppliers_orders_query(array('wait' => true, 'gds' => $g) + $_GET);
            $query = $queries['query'];
            $skip = $queries['skip'];
            $count_on_page = $this->count_on_page;//$queries['count_on_page'];

            $orders = $this->all_configs['manageModel']->get_suppliers_orders($query, $skip, $count_on_page);

            //$orders_html .= $this->all_configs['suppliers_orders']->show_suppliers_orders($orders);
            if ($orders) {
                $orders_html .= '<table class="show-suppliers-orders table"><thead><tr><td></td><td>' . l('Дата созд.') . '</td>
                    <td>Код</td><td>' . l('Наименование') . '</td><td>' . l('Кол-во') . '</td><td>' . l('Оприх.') . '</td><td>' . l('Склад') . '</td><td>' . l('Локация') . '</td>
                    <td>' . l('Проверить до') . '</td><td>' . l('Проверка') . '</td><td>' . l('Номера ремонтов, на которых можно проверить запчасти') . '</td>
                    <td>' . l('Комментарий') . '</td></tr></thead><tbody>';
                foreach ($orders as $order) {
                    $print_btn = $items = '';
                    if (count($order['items']) > 0) {
                        $url = $this->all_configs['prefix'] . 'print.php?act=label&object_id=' . implode(',', array_keys($order['items']));
                        $print_btn = '<a target="_blank" title="' . l('Печать') .'" href="' . $url . '"><i class="fa fa-print"></i></a>';
                        foreach ($order['items'] as $item) {
                            if (strtotime($item['date_checked']) > 0) {
                                //
                            } else {
                                $items .= '<button onclick="check_item(this, ' . $item['item_id'] . ')" class="btn btn-default btn-xs">' . suppliers_order_generate_serial($item) . '</button> ';
                            }
                        }
                    }
                    $sec = strtotime($order['date_check']);
                    $class = $sec > 0 ? ($sec < time() ? 'danger' : ($sec < (time() + (2 * 60 * 60 * 24)) ? 'warning' : '')) : '';
                    $orders_html .= '<tr class=" ' . $class . '" id="supplier-wait-order_id-' . $order['id'] . '">
                        <td>' . show_marked($order['id'], 'so', $order['m_id']) . '</td>
                        <td><span title="' . do_nice_date($order['date_add'], false) . '">' . do_nice_date($order['date_add']) . '</span></td>
                        <td>' . $this->all_configs['suppliers_orders']->supplier_order_number($order) . '</td>
                        <td><a class="hash_link" title="' . $order['secret_title'] . '" href="' . $this->all_configs['prefix'] . 'products/create/' . $order['goods_id'] . '">' . $order['goods_title'] . '</a></td>
                        <td>' . $order['count'] . '</td>
                        <td>' . (($order['count_debit'] > 0) ? '<a href="' . $this->all_configs['prefix'] . 'warehouses?so_id=' . $order['id'] . '#show_items">' . $order['count_debit'] . '</a>' : $order['count_debit']) . ' ' . $print_btn . '</td>
                        <td>' . (($order['wh_id'] > 0) ? '<a class="hash_link" href="' . $this->all_configs['prefix'] . 'warehouses?whs=' . $order['wh_id'] . '#show_items">' . htmlspecialchars($order['wh_title']) . '</a>' : '') . '</td>
                        <td>' . (($order['wh_id'] > 0) ? '<a class="hash_link" href="' . $this->all_configs['prefix'] . 'warehouses?whs=' . $order['wh_id'] . '&lcs=' . $order['location_id'] . '#show_items">' . htmlspecialchars($order['location']) . '</a>' : '') . '</td>
                        <td>
                            <div class="input-group" style="width: 150px">
                                <input class="datetimepicker form-control input-xs" placeholder="' . l('Дата проверки') .'" data-format="yyyy-MM-dd hh:mm:ss" type="text" name="date_check" value="' . $order['date_check'] . '" />
                                <span class="input-group-btn">
                                    <button onclick="edit_so_date_check(this, event, ' . $order['id'] . ')" class="btn btn-info btn-xs" type="button"><i class="glyphicon glyphicon-ok"></i></button>
                                </span>
                            </div>
                        </td>
                        <td>' . $items . '</td>
                        <td>' . (isset($serials[$order['goods_id']]) ? implode(', ', $serials[$order['goods_id']]) : '') . '</td>
                        <td>' . cut_string($order['comment'], 40) . '</td></tr>';
                }
                $orders_html .= '</tbody></table>';
            } else {
                $orders_html .= '<p  class="text-danger">' . l('Нет заказов') . '</p>';
            }

            $count = $this->all_configs['manageModel']->get_count_suppliers_orders($query);

            $count_page = $count_on_page > 0 ? ceil($count / $count_on_page) : 0;

            // строим блок страниц
            $orders_html .= page_block($count_page, $count, '#show_suppliers_orders-wait');
        }

        return array(
            'html' => $orders_html,
            'menu' => $this->all_configs['suppliers_orders']->show_filters_suppliers_orders(true),
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @return string
     */
    public function menu_recommendations_procurement()
    {
        $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : ''/*date('01.m.Y', time())*/)
            . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
            . (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : ''/*date('t.m.Y', time())*/);

        $categories = $this->all_configs['db']->query("SELECT * FROM {categories}")->assoc();
        return $this->view->renderFile('orders/menu_recommendations_procurement', array(
            'date' => $date,
            'categories' => $categories,

        ));
    }

    /**
     * @param $year
     * @return int
     */
    function getIsoWeeksInYear($year) {
        $date = new DateTime;
        $date->setISODate($year, 53);
        return ($date->format("W") === "53" ? 53 : 52);
    }

    /**
     * @param $caregories_id
     * @return array
     */
    function childrens_categories($caregories_id)
    {
        $return = array_combine((array)$caregories_id, (array)$caregories_id);

        $categories = $this->all_configs['db']->query('SELECT id FROM {categories} WHERE parent_id IN (?li)',
            array($caregories_id))->vars();

        if ($categories) {
            $return += $this->childrens_categories($categories);
        }

        return $return;
    }

    /**
     * @return array
     */
    function orders_recommendations_procurement()
    {
        $orders_html = '';
        $debug = '';

        if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
            $cfg = &$this->all_configs;
            $query = '';
            if (isset($_GET['ctg']) && count($ctg = array_filter(explode(',', $_GET['ctg']))) > 0) {
                $query = $cfg['db']->makeQuery(', {category_goods} as cg
                    WHERE cg.goods_id=g.id AND cg.category_id IN (?li)',
                    array($this->childrens_categories($ctg)));
            }

            // сроки доставки заказа поставщику
            $qty_weeks = isset($_GET['tso']) ? intval($_GET['tso']) : 0;

            if ($qty_weeks > 0) {

                // остатки
                $amounts = $cfg['db']->query('SELECT g.id as goods_id, g.title, qty_store, qty_wh FROM {goods} as g ?query',
                    array($query))->assoc('goods_id');

                if ($amounts) {
                    // фильтр по дате от
                    $query = '';
                    if (isset($_GET['df']) && strtotime($_GET['df']) > 0) {
                        $query = $cfg['db']->makeQuery('AND DATE(l.date_add)>=?', array(date('Y-m-d', strtotime($_GET['df']))));
                    }
                    // количество заявок
                    $request = $cfg['db']->query('SELECT l.goods_id, COUNT(DISTINCT l.id)
                        FROM {orders_suppliers_clients} as l, {orders_goods} as g
                        WHERE l.order_goods_id=g.id AND g.item_id IS NULL AND g.goods_id IN (?li) ?query GROUP BY goods_id',
                        array(array_keys($amounts), $query))->vars();
                    // фильтр по дате от
                    $query = '';
                    if (isset($_GET['df']) && strtotime($_GET['df']) > 0) {
                        $query = $cfg['db']->makeQuery('AND DATE(o.date_add)>=?', array(date('Y-m-d', strtotime($_GET['df']))));
                    }

                    // количество заказано
                    $wait = $cfg['db']->query('SELECT o.goods_id, sum(IF(o.count_come>0, o.count_come, o.count))
                        FROM {contractors_suppliers_orders} as o 
                        WHERE o.avail=1 AND o.count_debit=0 AND o.goods_id IN (?li) ?query
                        GROUP BY o.goods_id',
                        array(array_keys($amounts), $query))->vars();

                    // фильтр по дате от
                    $query = '';
                    if (isset($_GET['df']) && strtotime($_GET['df']) > 0) {
                        $query = $cfg['db']->makeQuery('AND DATE(i.date_add)>=?', array(date('Y-m-d', strtotime($_GET['df']))));
                    }
                    // расход
                    $consumption = $cfg['db']->query('
                        SELECT g.goods_id, g.title, i.date_add, COUNT(DISTINCT g.id) as qty_consumption,
                        YEARWEEK(i.date_sold, 1) as yearweek
                        FROM {orders} as o, {orders_goods} as g, {warehouses_goods_items} as i
                        WHERE o.id=g.order_id 
                            AND i.id=g.item_id 
                            AND o.status=?i 
                            AND o.category_id NOT IN (?li) 
                            AND i.date_sold IS NOT NULL 
                            AND g.goods_id IN (?li) 
                        ?query
                        GROUP BY g.goods_id, yearweek ORDER BY g.goods_id, yearweek',
                        array($cfg['configs']['order-status-issued'], array($cfg['configs']['erp-co-category-write-off'],
                            $cfg['configs']['erp-co-category-return']), array_keys($amounts), $query))->assoc('goods_id:yearweek');

                    // фильтр по дате от
                    $query = '';
                    if (isset($_GET['df']) && strtotime($_GET['df']) > 0) {
                        $query = $cfg['db']->makeQuery('AND DATE(d.date_add)>=?', array(date('Y-m-d', strtotime($_GET['df']))));
                    }
                    // спрос
                    $demand = $cfg['db']->query('
                        SELECT d.goods_id, g.title, d.date_add, COUNT(DISTINCT d.id) as qty_demand,
                        YEARWEEK(d.date_add, 1) as yearweek
                        FROM {goods_demand} as d, {goods} as g
                        WHERE g.id=d.goods_id AND d.date_add IS NOT NULL AND g.id IN (?li) ?query
                        GROUP BY d.goods_id, yearweek ORDER BY d.goods_id, yearweek',
                        array(array_keys($amounts), $query))->assoc('goods_id:yearweek');

                    foreach ($amounts as $p_id=>$p) {
                        $amounts[$p_id]['qty_wait_wh'] = isset($wait[$p_id]) ? $wait[$p_id] : 0;
                        $amounts[$p_id]['qty_wait_store'] = $amounts[$p_id]['qty_wait_wh'] - (isset($request[$p_id]) ? $request[$p_id] : 0);
                        $amounts[$p_id]['qty_wait_store'] = $amounts[$p_id]['qty_wait_store'] > 0 ? $amounts[$p_id]['qty_wait_store'] : 0;
                        // дата старта
                        if ((isset($consumption[$p_id]) && isset($demand[$p_id]) && strtotime($consumption[$p_id][key($consumption[$p_id])]['date_add']) > strtotime($demand[$p_id][key($demand[$p_id])]['date_add'])) || (isset($demand[$p_id]) && !isset($consumption[$p_id]))) {
                            $year = date('Y', strtotime($demand[$p_id][key($demand[$p_id])]['date_add']));
                            $week = date('W', strtotime($demand[$p_id][key($demand[$p_id])]['date_add']));
                        } elseif(isset($consumption[$p_id])) {
                            $year = date('Y', strtotime($consumption[$p_id][key($consumption[$p_id])]['date_add']));
                            $week = date('W', strtotime($consumption[$p_id][key($consumption[$p_id])]['date_add']));
                        } else {
                            $year = NULL;
                            $week = NULL;
                        }
                        if ($year !== null && $week !== null) {
                            // текущий год
                            $cur_year = date('Y');

                            $amounts[$p_id]['qty_consumption'] = 0;
                            $amounts[$p_id]['qty_demand'] = 0;
                            $amounts[$p_id]['qty_recommended'] = 0;
                            $amounts[$p_id]['qty_forecast'] = 0;

                            // матрица для рекомндаций к заказу
                            $matrix = [];

                            // все года
                            for ($y = $year; $y <= $cur_year; $y++) {
                                $cur_week = $cur_year == $y ? date('W') : $this->getIsoWeeksInYear($cur_year);
                                // все недели
                                for ($w = $week; $w <= $cur_week; $w++) {
                                    if (isset($consumption[$p_id][$y . $w])) {
                                        $amounts[$p_id]['qty_consumption'] += $consumption[$p_id][$y . $w]['qty_consumption'];
                                        $matrix[$y . $w] = $consumption[$p_id][$y . $w]['qty_consumption'];
                                    } else {
                                        $consumption[$p_id][$y . $w] = array(
                                            'goods_id' => $p_id,
                                            'qty_consumption' => 0,
                                            //'qty_demand' => 0,
                                            'yearweek' => $y . $w,
                                            //[date] => 2014-10-13 09:56:48
                                        );
                                    }
                                    // спрос - если расход ноль
                                    if (isset($demand[$p_id][$y . $w]) && $consumption[$p_id][$y . $w]['qty_consumption'] == 0) {
                                        $amounts[$p_id]['qty_demand'] += $demand[$p_id][$y . $w]['qty_demand'];
                                        $matrix[$y . $w] = $demand[$p_id][$y . $w]['qty_demand'] * $this->all_configs['settings']['demand-factor'];
                                    } else {
                                        $demand[$p_id][$y . $w] = array(
                                            'goods_id' => $p_id,
                                            //'qty_consumption' => 0,
                                            'qty_demand' => 0,
                                            'yearweek' => $y . $w,
                                            //[date] => 2014-10-13 09:56:48
                                        );
                                    }
                                    if (!isset($matrix[$y . $w])) {
                                        $matrix[$y . $w] = 0;
                                    }
                                }
                                $week = 1;
                            }

                            //вывод расхода
                            $str = $amounts[$p_id]['qty_consumption'] . ' / ' . count($consumption[$p_id]) . ' * ' . 4;
                            $amounts[$p_id]['qty_consumption'] = count($consumption[$p_id]) > 0 ? round($amounts[$p_id]['qty_consumption'] / count($consumption[$p_id]) * 4, 2) : 0;
                            $amounts[$p_id]['qty_consumption'] = '<span class="popover-info" data-content="' . $str . '" data-original-title="' . l('шт / к-во недель') .' * 4">' . $amounts[$p_id]['qty_consumption'] . '</span>';

                            $str = $amounts[$p_id]['qty_demand'] . ' / ' . count($demand[$p_id]) . ' * ' . 4;
                            $amounts[$p_id]['qty_demand'] = count($demand[$p_id]) > 0 ? round($amounts[$p_id]['qty_demand'] / count($demand[$p_id]) * 4, 2) : 0;
                            $amounts[$p_id]['qty_demand'] = '<span class="popover-info" data-content="' . $str . '" data-original-title="' . l('шт / к-во недель') .' * 4">' . $amounts[$p_id]['qty_demand'] . '</span>';


                            //$debug = print_r($matrix, true);

                            // вычисляем рекомендации к заказу
                            ksort($matrix, SORT_NUMERIC);
                            $k = $numerator = $denominator = $b = $prev = 0;

                            /* #вариант 1
                            foreach ($matrix as $v) {
                                $k++; // i
                                $numerator += $k * $v; // i * y
                                $denominator += $k * $k; // i * x
                            }
                            $b = $denominator > 0 ? $numerator / $denominator : 0;
                            */

                            if (count($matrix) > 0) {

                                // определяем суммы за последний и предыдущий месяц (4 недели)
                                $matrixr = array_reverse($matrix);
                                $first_priv = $first_priv2 = 0;
                                for ($mi = 0; $mi <= 3; $mi++){
                                    $first_priv += isset($matrixr[$mi]) ? $matrixr[$mi] : 0;
                                    $first_priv2 += isset($matrixr[$mi+4]) ? $matrixr[$mi+4] : 0;
                                }

                                $average = array_sum($matrix)/count($matrix); //среднее в неделю.
                                //прогноз за выбранный период * 2 (удвоенный)
                                if ($first_priv2>0 && ($first_priv2 + $first_priv2 >= 3)) {
                                    $percent = round($first_priv/$first_priv2, 2);
                                    if ($percent > 1.3) $percent = 1.3;
                                    if ($percent < 0.7) $percent = 0.7;
                                } else {
                                    $percent = 0;
                                }

                                $amounts[$p_id]['qty_forecast'] = $average * ($qty_weeks * 2) * $percent;

                                $debug .= "1m = ".$first_priv. ", 2m = ".$first_priv2 . "  diff=".($first_priv-$first_priv2)." avr=".($average*$qty_weeks)." \n" ;

                                #Вариант 1 (не подходит)
                                /**
                                // if avg(b) < b ? - : +
                                $b = $denominator > 0 && ($numerator / count($matrix)) / ($denominator / count($matrix)) < $b ? $b : - $b;

                                $k++;
                                //reset($matrix);$x = 1;
                                //$y = current($matrix);
                                $y = array_sum($matrix) / count($matrix);
                                $x = round(count($matrix) / 2);
                                $a = $y - $b * $x;
                                $y = $a + $b * $k;
                                $amounts[$p_id]['qty_forecast'] = $y * $qty_weeks * 2;

                                $str = '<a href=\'https://www.google.com/webhp?q=y%3D' . $a . '%2B+' . $b . '*x#q=y%3D' . $a . '%2B' . $b . '*x\'>a = ' . round($a, 2) . '; b = ' . round($b, 2) . ';</a>';
                                $str .= '<br />x = ' . $k . '; y = ' . round($y, 2) . ';';

                                 */

                                #Варант 2 (не подходит)
                                /*if (array_sum($matrix) < 5 || array_sum($matrix) / count($matrix) * 4 < 1) {
                                    end($matrix);
                                    $x = count($matrix);
                                    $use_log = false;
                                    //
                                    $k++;
                                    $a = current($matrix) - $b ;
                                    $str = '<a href=\'https://www.google.com/webhp?q=y%3D' . $a . '%2B+' . $b . '*x#q=y%3D' . $a . '%2B' . $b . '*x\'>a = ' . round($a, 4) . '; b = ' . round($b, 4) . ';</a>';
                                    // обеспечиваем на следующие $qty_weeks * 2 недель
                                    for ($i = 1; $i <= $qty_weeks * 2; $i++) {
                                        $k++;
                                        // week < 1
                                        // y = a + b * i
                                        $y = $a + $b * ($use_log ? log($k) : $k);
                                        $amounts[$p_id]['qty_forecast'] += $y;
                                        $str .= '<br />x = ' . $k . '; y = ' . round($y, 2) . ';';
                                    }
                                } else {
                                    $x = round(count($matrix) / 2);
                                    //$x = 1;
                                    $y = array_sum($matrix) / count($matrix);
                                    reset($matrix);
                                    //$y = current($matrix);
                                    $use_log = true;

                                    // a = qty - b * 1
                                    $a = $y - $b ;
                                    $a = $a > 0 ? $a : 0;
                                    $str = '<a href=\'https://www.google.com/webhp?q=y%3D' . $a . '%2B+' . $b . '*ln(x)#q=y%3D' . $a . '%2B' . $b . '*ln(x)\'>a = ' . round($a, 4) . '; b = ' . round($b, 4) . ';</a>';
                                    // обеспечиваем на следующие $qty_weeks * 2 недель
                                    for ($i = 1; $i <= $qty_weeks * 2; $i++) {
                                        $k++;
                                        // week < 1
                                        // y = a + b * i
                                        $y = $a + $b * ($use_log ? log($k) : $k);
                                        $amounts[$p_id]['qty_forecast'] += $y;
                                        $str .= '<br />x = ' . $k . '; y = ' . round($y, 2) . ';';
                                    }
                                }*/

                                $str = '% = '.($percent*100).'<br>week = '.$qty_weeks
                                        .'<br>ave = '.round($average, 2)
                                        .'<pre>' . print_r($matrix, true) . '</pre>';
                                $amounts[$p_id]['qty_recommended'] = $amounts[$p_id]['qty_forecast'] - $amounts[$p_id]['qty_store'] - $amounts[$p_id]['qty_wait_store'];
                                $amounts[$p_id]['qty_recommended'] = /*array_sum($matrix) == 1 ? '&ndash;' : */($amounts[$p_id]['qty_recommended'] > 0 ? round($amounts[$p_id]['qty_recommended'], 1) : 0);

                                $amounts[$p_id]['qty_forecast'] = $percent == 0 ? '&ndash;' : round($amounts[$p_id]['qty_forecast'], 1);
                                //$amounts[$p_id]['qty_forecast'] = array_sum($matrix) == 1 ? '&ndash;' : ($amounts[$p_id]['qty_forecast'] > 0 ? round($amounts[$p_id]['qty_forecast'], 2) : 0);

                                $amounts[$p_id]['qty_forecast'] = '<span class="popover-info" data-content="' . $str . '" data-original-title="' . l('Среднее значение') .' * %">' . $amounts[$p_id]['qty_forecast'] . '</span>';
                            }
                        }
                    }
                }

                $orders_html .= '<table class="table" id="tablesorter"><thead><tr><th>' . l('Наименование') .'</th><th>' . l('Общ.ост.') .'</th><th>' . l('Своб.ост.') . '</th>';
                $orders_html .= '<th>' . l('Ожид.пост.(общ.)') . '</th><th>' . l('Ожид.пост.(своб.)') . '</th><th>' . l('Расход (шт/мес)') . '</th>';
                $orders_html .= '<th>' . l('Спрос (шт/мес)') . '</th><th>' . l('Прогноз') . '</th><th>' . l('Рекомендовано еще к заказу') . '</th></tr></thead><tbody>';
                $href = $cfg['prefix'] . 'products/create/';
                foreach ($amounts as $p_id=>$amount) {
                    $orders_html .= '<tr><td><a href="' . $href . $p_id . '">' . htmlspecialchars($amount['title']) . '</a></td>';
                    $orders_html .= '<td>' . (isset($amount['qty_wh']) ? $amount['qty_wh'] : 0) . '</td>';
                    $orders_html .= '<td>' . (isset($amount['qty_store']) ? $amount['qty_store'] : 0) . '</td>';
                    $orders_html .= '<td>' . (isset($amount['qty_wait_wh']) ? $amount['qty_wait_wh'] : 0) . '</td>';
                    $orders_html .= '<td>' . (isset($amount['qty_wait_store']) ? $amount['qty_wait_store'] : 0) . '</td>';
                    $orders_html .= '<td>' . (isset($amount['qty_consumption']) ? $amount['qty_consumption'] : 0) . '</td>';
                    $orders_html .= '<td>' . (isset($amount['qty_demand']) ? $amount['qty_demand'] : 0) . '</td>';
                    $orders_html .= '<td>' . (isset($amount['qty_forecast']) ? $amount['qty_forecast'] : 0) . '</td>';
                    $orders_html .= '<td>' . (isset($amount['qty_recommended']) ? $amount['qty_recommended'] : 0) . '</td></tr>';
                }
                $orders_html .= '</tbody></table>';
            } else {
                $orders_html .= '<p class="text-danger">' . l('Для правильности рассчетов укажите сроки доставки заказа поставщику') . '</p>';
            }
        }
        if (!isset($debug)) $debug = '';
        return array(
            'html' => $orders_html,
            'menu' => $this->menu_recommendations_procurement(),
            'functions' => array('reset_multiselect(), table_sorter()'),
            'debug' => $debug,
        );
    }

    /**
     * @return array
     */
    function orders_create_supplier_order()
    {
        $orders_html = '';

        if ( $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') ) {
            if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] > 0) {
                $orders_html .= $this->all_configs['suppliers_orders']->create_order_block(1, $this->all_configs['arrequest'][2]);
            } else {
                $orders_html .= $this->all_configs['suppliers_orders']->create_order_block(1);
            }
        }

        return array(
            'html' => $orders_html,
            'functions' => array(),
        );
    }

    // status -1
    /**
     * @param $order
     * @return bool
     */
    function check_if_order_fail_in_orders_manager($order)
    {
        $day = 60 * 60 * 24;
        $managerConfigs = $this->all_configs['db']->query("SELECT * FROM {settings} WHERE name = 'order-manager-configs'")->assoc();
        if (empty($managerConfigs)) {
            return $this->check_with_default_config($order, $day);
        } else {
            $config = json_decode($managerConfigs[0]['value'], true);
            foreach ($config as $id => $value) {
                if($id == 'status_repair' && $order['status'] == $this->all_configs['configs']['order-status-waits']) {
                    $items = $this->all_configs['db']->query('SELECT sum(1) as quantity, sum(if(item_id IS NULL,1,0)) as not_binded FROM {orders_goods} WHERE order_id=? GROUP BY order_id', array($order['id']))->assoc();
                    if(!empty($items) && $items[0]['quantity'] > 0 && $items[0]['not_binded'] && strtotime($order['date']) + $day * $value < time()) {
                        return true;
                    }
                }
                if($id == 'status_sold' && $order['status'] == $this->all_configs['configs']['order-status-waits']) {
                    $goods = $this->all_configs['manageModel']->order_goods($order['id'], 0);
                    if(!empty($goods)) {
                        foreach ($goods as $good) {
                            if($good['item_id'] <= 0 && $good['count_order'] > 0 && $good['supplier'] == 0 && strtotime($order['date']) + $day * $value < time()) {
                                return true;
                            }
                        }
                    }
                    if(!empty($items) && $items[0]['quantity'] > 0 && $items[0]['not_binded'] && strtotime($order['date']) + $day * $value < time()) {
                        return true;
                    }
                }
                //4 У ремонта выставлен статус "Ожидает запчасть", а заказ на закупку не отправлен и не привязан никакой заказ поставщику
                if ($order['status'] == $this->all_configs['configs']['order-status-waits'] && $order['broken'] > 0) {
                    return true;
                }
                // Принят в ремонт > 24 часов назад и никто из манагеров не взял
                if (!$order['manager'] && strtotime($order['date_add']) <= time() - 86400) {
                    return true;
                }
                if ($order['status'] == $id && strtotime($order['date']) + $day * $value < time()) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $order
     * @param $day
     * @return bool
     */
    protected function check_with_default_config($order, $day)
    {
        //1 Запчасть заказана, оприходована, но не отгружена под ремонт больше 2-х дней
        //2 Заказ клиента подвязан к заказу поставщику, а указанная в заказе поставщику дата поставки просрочена.
        //3 По нормативу с момента создания заказа на закупку (пустышки) и создания заказа поставщику не должно пройти больше 3х дней.
        //4 У ремонта выставлен статус "Ожидает запчасть", а заказ на закупку не отправлен и не привязан никакой заказ поставщику
        //5 На диагностику не более 2-х дней
        if ($order['status'] == $this->all_configs['configs']['order-status-waits'] && $order['broken'] > 0) {
            return true;
        }
        // Принят в ремонт > 24 часов назад и никто из манагеров не взял
        if (!$order['manager'] && strtotime($order['date_add']) <= time() - 86400) {
            return true;
        }
        // Принят в ремонт > 3 дней
        if ($order['status'] == $this->all_configs['configs']['order-status-new'] && strtotime($order['date']) + $day * 3 < time()) {
            return true;
        }
        // На диагностике > 2 дней
        if ($order['status'] == $this->all_configs['configs']['order-status-diagnosis'] && strtotime($order['date']) + $day * 2 < time()) {
            return true;
        }
        // В процессе ремонта > 3 дней
        if ($order['status'] == $this->all_configs['configs']['order-status-work'] && strtotime($order['date']) + $day * 3 < time()) {
            return true;
        }
        // В удаленном сервисе > 3 дней
        if ($order['status'] == $this->all_configs['configs']['order-status-service'] && strtotime($order['date']) + $day * 3 < time()) {
            return true;
        }
        // Принят на доработку > 3 дней
        if ($order['status'] == $this->all_configs['configs']['order-status-rework'] && strtotime($order['date']) + $day * 3 < time()) {
            return true;
        }
        // На согласовании > 10 дней
        if ($order['status'] == $this->all_configs['configs']['order-status-agreement'] && strtotime($order['date']) + $day * 10 < time()) {
            return true;
        }
        return false;
    }

    /**
     * @param string $filters_query
     * @return mixed
     */
    function get_orders_for_orders_manager($filters_query = ''){
        $orders = db()->query(
                'SELECT o.status, o.date_add, o.id, s.date, o.accept_wh_id, o.manager, w.group_id, SUM(IF ((
                    (l.id IS NOT NULL AND g.item_id IS NULL AND so.count_debit>0 AND DATE_ADD(l.date_add, INTERVAL 2 day)<NOW()) ||
                    (so.id IS NOT NULL AND so.date_wait<NOW() AND g.id IS NOT NULL AND g.item_id IS NULL AND so.supplier>0 AND so.count_debit=0) ||
                    (DATE_ADD(so.date_add, INTERVAL 3 day)<NOW() AND so.id IS NOT NULL AND so.count_debit=0 AND so.supplier IS NULL) ||
                    (l.id IS NULL AND g.id IS NOT NULL AND g.item_id IS NULL)) AND o.status=?i, 1, 0)) as broken
                FROM {orders} as o
                LEFT JOIN (SELECT order_id, date, id FROM {order_status} ORDER BY `date` DESC) as s ON s.order_id=o.id AND o.status_id=s.id
                LEFT JOIN {orders_goods} as g ON g.order_id=o.id AND g.type=0
                LEFT JOIN {orders_suppliers_clients} as l ON l.order_goods_id=g.id
                LEFT JOIN {contractors_suppliers_orders} as so ON so.id=l.supplier_order_id
                LEFT JOIN {warehouses} AS w ON o.accept_wh_id=w.id
                WHERE ?query o.type NOT IN (?li) AND o.status IN (?li) AND UNIX_TIMESTAMP(o.date_add)>? 
                GROUP BY o.id ORDER BY o.date_add',
                array($this->all_configs['configs']['order-status-waits'], $filters_query, array(1),
                    $this->all_configs['configs']['order-statuses-manager'], (time() - 60*60*24*90)))->assoc();
        return $orders;
    }

    /**
     * @return float|int
     */
    function get_orders_manager_fail_percent(){
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $orders = $this->get_orders_manager_stats($user_id);
        if($orders){
            $qty_fail = 0;
            foreach ($orders as $order) {
                if($this->check_if_order_fail_in_orders_manager($order)){
                    $qty_fail ++;
                }
            }
            return round($qty_fail / count($orders) * 100, 2);
        }else{
            return 0;
        }
    }

    /**
     * @param $manager
     * @return mixed
     */
    function get_orders_manager_stats($manager){
        $q = $this->get_orders_manager_filter_by_manager_query(array($manager));
        return $this->get_orders_for_orders_manager($q);
    }

    /**
     * @param $mg
     * @return mixed
     */
    function get_orders_manager_filter_by_manager_query($mg){
        return db()->makeQuery(' (o.manager IN (?li) OR ((o.manager IS NULL OR o.manager = 0) AND o.date_add <= DATE_ADD(NOW(), INTERVAL -24 HOUR))) AND ', array($mg));
    }

    /**
     * @param      $colors_count
     * @param null $orders_summ
     * @param bool $as_array
     * @return array|string
     */
    function gen_orders_manager_stats($colors_count, $orders_summ = null, $as_array = false){
        $colors_percents = '';
        $data = array();
        if($colors_count){
            arsort($colors_count);
            if(!$orders_summ){
                $orders_summ = array_sum($colors_count);
            }
            foreach($colors_count as $color => $qty){
                $p = round($qty / $orders_summ * 100, 2);
                $colors_percents .= '
                    <span style="border-radius:5px;margin-right:10px;color:#fff;padding:5px 10px;background-color:#'.$color.'">'.
                        $p.'%
                    </span>
                ';
                $data[$color] = $p;
            }
        }else{
            $colors_percents = '(' . l('статистика отсутствует') .')';
        }

        if($as_array){
            return array(
                'html' => $colors_percents,
                'data' => $data
            );
        }else{
            return $colors_percents;
        }
    }

    /**
     * @return array
     */
    function orders_manager()
    {
        $orders_html = '';
        $manager_block = '';
        if ($this->all_configs['oRole']->hasPrivilege('orders-manager')) {
            // фильтры
            $query = '';
            // сервис центр
            $wt = isset($_GET['wh_groups']) ? array_filter(array_unique($_GET['wh_groups'])) : array();
            if ($wt) {
                $query .= $this->all_configs['db']->makeQuery(' w.group_id IN (?li) AND ', array($wt));
            }
            // манагер
            $mg = isset($_GET['managers']) ? $_GET['managers'] : array();
            if ($mg) {
                // манагер или заказ который был принят 24 часа назад и никто не взял его
                $query .= $this->get_orders_manager_filter_by_manager_query($mg);
            }
            // фильтр статистики по дате
            $get_date = isset($_GET['date']) ? htmlspecialchars($_GET['date']) : '';
            $date = isset($_GET['date']) && trim($_GET['date']) ? explode('-', $_GET['date']) : array();
            $filter_stats = '';
            if ($date) {
                $date_from = date('Y-m-d', strtotime($date[0]));
                $date_between = date('Y-m-d', strtotime($date[1]));
                $date_diff = date_diff(date_create($date_from), date_create($date_between));
                $date_query = $this->all_configs['db']->makeQuery(" date BETWEEN ? AND ? ",
                    array($date_from, $date_between));
                $squery = str_replace(array('w.', 'o.'), '', $query);
                $squery = str_replace('date_add', 'o.date_add', $squery);
                $squery = str_replace('manager', 'h.manager', $squery);
                $stats = db()->query("SELECT h.id, h.status, h.date, count(h.id) as qty_by_status "
                    . "FROM {orders_manager_history} as h "
                    . "LEFT JOIN {orders} as o ON h.order = o.id "
                    . "WHERE ?q ?q GROUP BY h.date, h.status",
                    array($squery, $date_query), 'assoc');
                $colors_count = array();
                if ($stats) {
                    $stats_by_dates = array();
                    foreach ($stats as $stat) {
                        $stats_by_dates[$stat['date']][$stat['status']] = $stat;
                    }
                    ksort($stats_by_dates);
                    $days_stats = '';
                    $all_stats = array();
                    $colors_stats_qty = array();
                    foreach ($stats_by_dates as $date => $statuses) {
                        $all_qty = 0;
                        $colors_count = array();
                        foreach ($statuses as $status => $data) {
                            if (isset($this->all_configs['configs']['order-status'][$status])) {
                                $color = $this->all_configs['configs']['order-status'][$status]['color'];
                            } elseif ($status == -1) {
                                $color = 'FF0000';
                            } else {
                                $color = 'bebebe';
                            }
                            $all_qty += $data['qty_by_status'];
                            $colors_count[$color] = $data['qty_by_status'];
                            $colors_stats_qty[$color] = isset($colors_stats_qty[$color]) ? $colors_stats_qty[$color] + 1 : 1;
                        }
                        $st = $this->gen_orders_manager_stats($colors_count, $all_qty, true);
                        foreach ($st['data'] as $c => $p) {
                            if (!isset($all_stats[$c])) {
                                $all_stats[$c] = 0;
                            }
                            $all_stats[$c] += $p;
                        }
                        $days_stats .= '<strong style="display:inline-block;margin:5px 0">' . $date . '</strong> <br>
                                        ' . $st['html'] . '<br>';
                    }
                    $all_stats_results = array();
                    foreach ($all_stats as $c => $s) {
                        $all_stats_results[$c] = $s / (isset($colors_stats_qty[$c]) ? $colors_stats_qty[$c] : 1);
                    }
                    $all_stats_html = $this->gen_orders_manager_stats($all_stats_results, 100);
                } else {
                    $days_stats = $all_stats_html = '(' . l('нет статистики за выбранный период') . ')';
                }
                $filter_stats = '
                    ' . l('Средняя статистика за период') . ' ' . $get_date . '. <br>
                    ' . l('Cуммируются проценты по дням и делятся на количество дней у которых есть статистика, по каждому статусу отдельно.') . '<br>
                    <div style="margin-top:5px">
                        ' . $all_stats_html . '
                    </div>
                    <br>
                    ' . l('Статистика по дням') . ':<br>
                    ' . $days_stats . '
                ';
                $orders = null;
            } else {
                $orders = $this->get_orders_for_orders_manager($query);
                if ($orders) {
                    $colors_count = array();
                    foreach ($orders as $order) {
                        $class = $style = '';
                        if (isset($this->all_configs['configs']['order-status'][$order['status']])) {
                            $color = $this->all_configs['configs']['order-status'][$order['status']]['color'];
                            $style = 'style="background-color: #' . $color . ';"';
                        }
                        $class = $this->check_if_order_fail_in_orders_manager($order) ? 'red-blink' : '';
                        if ($color || $class) {
                            if ($class == 'red-blink') {
                                $color = 'FF0000';
                            }
                            $colors_count[$color] = isset($colors_count[$color]) ? $colors_count[$color] + 1 : 1;
                        }
                        $manager_block .= '<div data-o_id="' . $order['id'] . '" onclick="alert_box(this, null, \'display-order\')" class="order-manager ' . $class . '" ' . $style . '>';
                        $manager_block .= '<b>' . $order['id'] . '</b>';
                        $manager_block .= '<br /><span title="' . do_nice_date($order['date_add'],
                                false) . '">' . do_nice_date($order['date_add']) . '</span></div>';
                    }

                    $filter_stats = $this->gen_orders_manager_stats($colors_count) . ' <br>';
                } else {
                    $manager_block = '<p>' . l('Заказов нет') . '</p>';
                }
            }
            // -- фильтры

            $orders_html = '
                <div>
                    <form class="form-inline well">
                        ' . $this->all_configs['suppliers_orders']->show_filter_service_center() . '
                        ' . $this->show_filter_manager(true, false) . '
                        <input type="text" placeholder="' . l('Дата') . '" name="date" class="daterangepicker form-control " value="' . $get_date . '" />
                        <input type="submit" class="btn btn-primary" value="' . l('Фильтровать') . '">
                        <button type="button" class="btn fullscreen"><i class="fa fa-arrows-alt"></i></button>
                        <button type="button" class="btn btn-primary  pull-right " onclick="return manager_setup(this);">' . l('Настройки') . '</button>
                    </form>
                </div>
            ';

            $orders_html .= '
                ' . $filter_stats . '
                <br>
                <div id="orders-manager-block">
                    ' . $manager_block . '
                </div>
            ';
        }

        return array(
            'html' => $orders_html,
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @param $product
     * @return string
     */
    function show_product($product)
    {
        $qty = isset($product['count']) ? intval($product['count']) : 1;
        $supplier_order = $this->
                            all_configs['db']
                                ->query("SELECT supplier_order_id as id, o.count, o.supplier, "
                                              ."o.confirm, o.avail, o.count_come, o.count_debit, o.wh_id "
                                       ."FROM {orders_suppliers_clients} as c "
                                       ."LEFT JOIN {contractors_suppliers_orders} as o ON o.id = c.supplier_order_id "
                                       ."WHERE c.client_order_id = ?i AND c.goods_id = ?i",
                                            array($product['order_id'], $product['goods_id']), 'row');
        $confirm_remove_supplier_order = $supplier_order['count'] == 1 && $supplier_order['confirm'] != 1 ? ', 1' : '';
        /*$count = '<select id="product_count-' . $product['goods_id'] . '" class="input-mini" onchange="order_products(this, ' . $product['goods_id'] . ', 1)">';
        for ($i = 1; $i <= 99; $i++) {
            $count .= '<option ' . ($i == $qty ? 'selected' : '') . ' value="' . $i . '">' . $i . '</option>';
        }
        $count .= '</select>';*/

        $url = $this->all_configs['prefix'] . 'products/create/' . $product['goods_id'];

        $order_html = '<tr><td class="col-sm-5"><a href="' . $url . '">' . htmlspecialchars($product['title']) . '</a></td>';
        if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')/* && $product['type'] == 0*/) {
            $order_html .= '<td class="col-sm-2">';
            $order_html .= '<form method="POST"><div class="input-group floating-width">';
            $order_html .= '<input class="form-control global-typeahead input-medium popover-info visible-price" type="text"  onkeypress="change_input_width(this, this.value.length);" value="'.($product['price'] / 100) . '"/>';
            $order_html .= '<div class="input-group-btn" style="display:none" ><button class="btn btn-info" type="submit" onclick="change_visible_prices(this, ' . $product['id'] . ')"><span class="glyphicon glyphicon-ok"></span>&nbsp;</button></div>';
            $order_html .= '</div></form></td>';
        }
        $order_html .= '<td class="col-sm-1">';
        if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
            $order_html .= '<i title="' . l('удалить') .'" class="glyphicon glyphicon-remove remove-product" onclick="order_products(this, ' . $product['goods_id'] . ', ' . $product['id'] . ', 1, 1'.$confirm_remove_supplier_order.')"></i>';
        }
        $order_html .= '</td>';
        if ($product['type'] == 0) {
            $msg = '';
            if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                $msg .= '<input style="width:100%" type="button" data-order_product_id="' . $product['id'] . '" class="btn btn-small" onclick="order_item(this)" value="' . l('Заказать') . '" />';
            }
            $href = $this->all_configs['prefix'] . 'orders/edit/' . $product['so_id'] . '#create_supplier_order';
            $muted = $product['so_id'] > 0 ? ' <a href="' . $href . '"><small class="muted">№' . $product['so_id'] . '</small></a> ' : '';
            if ($product['item_id'] > 0) {
                $msg = '<td>' . suppliers_order_generate_serial($product, true, true) . ' ' . $muted . '</td><td>';
                if (!strtotime($product['unbind_request']) && $this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                    $msg .= '<i title="' . l('отвязать') .'" class="glyphicon glyphicon-minus cursor-pointer" onclick="btn_unbind_request_item_serial(this, \'' . $product['item_id'] . '\')"></i>';
                }else{
                    $msg .= $this->get_unbind_order_product_btn($product['item_id']);
                }
                $msg .= '</td>';
            } else{
                $create_role = $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders');
                $accept_role = $this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders');
                $bind_role = $this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders');
                $role_alert = "alert('" . l('У Вас недостаточно прав для этой операции') . "')";

                $avail_create = $avail_accept = $avail_bind = false;
                $accept_action = $bind_action = $create_action = '';
                $accept_data = '';

                if ($product['unavailable'] == 1) {
                    $msg =  l('Запчасть не доступна к заказу') . ' ' . $muted . '';
                }elseif($product['count_debit'] > 0) {
                    $avail_bind = true;
                    $bind_action = 'bind_product(this,'.$product['goods_id'].')';
                    $msg = l('Ожидание отгрузки запчасти') .
                    '<span title="' . do_nice_date($product['date_debit'], false) . '">' .
                            do_nice_date($product['date_debit']) . '</span> ' .
                            $muted . '';
                }elseif($product['count_come'] > 0) {
                    $avail_accept = true;
                    $accept_action = "alert_box(this,false,'form-debit-so',{},null,'warehouses/ajax/')";
                    $accept_data = ' data-o_id="'.$supplier_order['id'].'"';
                    $msg =  l('Запчасть была принята') . '
                            <span title="' . do_nice_date($product['date_come'], false) . '">' .
                            do_nice_date($product['date_come']) . '</span> ' .
                            $muted . '';
                }elseif($product['supplier'] > 0) {
                    $avail_accept = true;
                    $accept_action = "alert_box(this, false, 'form-accept-so-and-debit')";
                    $accept_data = ' data-o_id="'.$supplier_order['id'].'"';
                    $msg = l('Запчасть заказана') . ' (' . l('заказ поставщику') .' № <a href="'.$this->all_configs['prefix'].'orders/edit/'.$product['so_id'].'#create_supplier_order">
<small class="muted">' . $product['so_id'] . '</small> </a>). ' . l('Дата поставки') . ' <span title="' . do_nice_date($product['date_wait'],
                            false) . '">' .
                        do_nice_date($product['date_wait']) . '';
                }elseif($product['count_order'] > 0) {
                    $date_attach = $this->all_configs['db']->query(
                                        "SELECT date_add FROM {orders_suppliers_clients} "
                                       ."WHERE client_order_id = ?i AND supplier_order_id = ?i "
                                         ."AND goods_id = ?i AND order_goods_id = ?i", array(
                                             $product['order_id'],$product['so_id'],
                                             $product['goods_id'],$product['id']
                                         ), 'el');

                    $avail_create = true;
                    $create_action = 'show_suppliers_order(this, '.$supplier_order['id'].')';
                    $msg = '
                        <span title="'.do_nice_date($date_attach, false).'">'.
                            do_nice_date($date_attach).
                        '</span> '.
                        l('Отправлен запрос на закупку') . ' ' . $muted.' от '.
                        '<span title="'.do_nice_date($product['date_add'], false).'">'.
                            do_nice_date($product['date_add']).
                        '</span>
                    ';
                }

                $msg = '
                    <td colspan="2" class="col-sm-4">
                        <div class="order_product clearfix">
                            <div class="text-info">
                                '.$msg.'
                            </div>
                            <div class="order_product_menu">
                                <button style="min-width:30px" type="button" class="btn btn-primary btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <span class="caret"></span>
                                    <span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li>
                                        <a data-alert_box_not_disabled="true" class="'.(!$avail_create || !$create_role ? 'text-muted' : '').'" onclick="'.($create_role ? $create_action : $role_alert).';return false;">
                                            <i class="fa fa-pencil"></i> ' . l('Создать заказ поставщику') . '
                                        </a>
                                    </li>
                                    <li>
                                        <a data-alert_box_not_disabled="true" '.$accept_data.' class="'.(!$avail_accept || !$accept_role ? 'text-muted' : '').'" onclick="'.($accept_role ? $accept_action : $role_alert).';return false;">
                                            <i class="fa fa-wrench"></i> ' . l('Принять и оприходовать заказ') . '
                                        </a>
                                    </li>
                                    <li>
                                        <a data-alert_box_not_disabled="true" class="'.(!$avail_bind || !$bind_role ? 'text-muted' : '').'" onclick="'.($bind_role ? $bind_action : $role_alert).';return false;">
                                            <i class="fa fa-random"></i> ' . l('Отгрузить деталь под ремонт') . '
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </td>
                ';
            }
        } else {
            $msg = '<td colspan="2"></td>';
        }
        $order_html .= $msg . '</tr>';

        return $order_html;
    }

    /**
     * @param null $order_id
     * @return string
     * @throws Exception
     */
    function genorder($order_id = null)
    {
        $show_btn = true;
        $order_id = ($order_id == 0) ? intval($this->all_configs['arrequest'][2]) : $order_id;
        // достаем заказ с прикрепленными к нему товарами
        $order = $this->all_configs['db']->query('SELECT o.*, o.color as o_color, l.location, w.title as wh_title, gr.color, tp.icon,
                u.fio as m_fio, u.phone as m_phone, u.login as m_login, u.email as m_email,
                a.fio as a_fio, a.phone as a_phone, a.login as a_login, a.email as a_email, aw.title as aw_title, c.tag_id as tag_id
                FROM {orders} as o
                LEFT JOIN {clients} as c ON c.id=o.user_id
                LEFT JOIN {users} as u ON u.id=o.manager
                LEFT JOIN {users} as a ON a.id=o.accepter
                LEFT JOIN {warehouses} as w ON o.wh_id=w.id
                LEFT JOIN {warehouses_locations} as l ON o.location_id=l.id
                LEFT JOIN {warehouses} as aw ON o.accept_wh_id=aw.id
                LEFT JOIN {warehouses_groups} as gr ON gr.id=aw.group_id
                LEFT JOIN {warehouses_types} as tp ON tp.id=aw.type_id
                WHERE o.id=?i',
            array($order_id))->row();

        if (empty($order)) {
            return $this->view->renderFile('orders/genorder/_empty_orders');
        }
        // достаем комментарии к заказу
        $comments_public = (array)$this->all_configs['db']->query('SELECT oc.date_add, oc.text, u.fio, u.phone, u.login, u.email, oc.id
                FROM {orders_comments} as oc LEFT JOIN {users} as u ON u.id=oc.user_id
                WHERE oc.order_id=?i AND oc.private=0 ORDER BY oc.date_add DESC', array($order['id']))->assoc();
        $comments_private = (array)$this->all_configs['db']->query('SELECT oc.date_add, oc.text, u.fio, u.phone, u.login, u.email, oc.id
                FROM {orders_comments} as oc LEFT JOIN {users} as u ON u.id=oc.user_id
                WHERE oc.order_id=?i AND oc.private=1 ORDER BY oc.date_add DESC', array($order['id']))->assoc();

        $notSale = $order['type'] != 3;
        $goods = $this->all_configs['manageModel']->order_goods($order['id'], 0);
        $services = $notSale ? $this->all_configs['manageModel']->order_goods($order['id'], 1) : null;

        $productTotal = 0;
        if(!empty($goods)) {
            foreach ($goods as $product) {
                $productTotal += $product['price'] * $product['count'];
            }
        }
        if(!empty($services)) {
            foreach ($services as $product) {
                $productTotal += $product['price'] * $product['count'];
            }
        }
        $parts = array();
        if ($order['battery']) {
            $parts[] = l('Аккумулятор');
        }
        if ($order['charger']) {
            $parts[] = l('Зарядное устройство кабель');
        }
        if ($order['cover']) {
            $parts[] = l('Задняя крышка');
        }
        if ($order['box']) {
            $parts[] = l('Коробка');
        }
        if ($order['equipment']) {
            $parts[] = htmlspecialchars($order['equipment']);
        }


        $hasEditorPrivilege = $this->all_configs['oRole']->hasPrivilege('edit-clients-orders');
        return $this->view->renderFile('orders/genorder/genorder', array(
            'order' => $order,
            'onlyEngineer' => $this->all_configs['oRole']->hasPrivilege('engineer') && !$hasEditorPrivilege,
            'hasEditorPrivilege' => $hasEditorPrivilege,
            'notSale' => $notSale,
            'managers' => $notSale ? $this->all_configs['oRole']->get_users_by_permissions('edit-clients-orders',
                Role::ONLY_ACTIVE) : null,
            'engineers' => $notSale ? $this->all_configs['oRole']->get_users_by_permissions('engineer',
                Role::ONLY_ACTIVE) : null,
            'navigation' => $this->clients_orders_navigation(true),
            'orderWarranties' => isset($this->all_configs['settings']['order_warranties']) ? explode(',',
                $this->all_configs['settings']['order_warranties']) : array(),
            'request' => get_service('crm/requests')->get_request_by_order($order['id']),
            'showButtons' => $show_btn,
            'goods' => $goods,
            'services' => $services,
            'controller' => $this,
            'comments_public' => $comments_public,
            'comments_private' => $comments_private,
            'productTotal' => $productTotal,
            'parts' => $parts,
            'hide' => $this->getHideFieldsConfig(),
            'tags' => $this->getTags()
        ));
    }

    /**
     * @return mixed
     */
    private function getTags()
    {
        return $this->all_configs['db']->query('SELECT color, title, id FROM {tags} ORDER BY title',
            array())->assoc('id');
    }

    /**
     * @param        $data
     * @param string $rootNodeName
     * @param null   $xml
     * @return mixed
     */
    public static function toXml($data, $rootNodeName = 'data', $xml=null)
    {
        // включить режим совместимости, не совсем понял зачем это но лучше делать
        if (ini_get('zend.ze1_compatibility_mode') == 1) {
            ini_set ('zend.ze1_compatibility_mode', 0);
        }

        if ($xml == null) {
            $xml = simplexml_load_string("<?xml version=\"1.0\" encoding=\"utf-8\"?><$rootNodeName />");
        }

        //цикл перебора массива
        foreach($data as $key => $value) {
            // нельзя применять числовое название полей в XML
            if (is_numeric($key)) {
                // поэтому делаем их строковыми
                $key = "unknownNode_". (string) $key;
            }

            // удаляем не латинские символы
            $key = preg_replace('/[^a-z0-9]/i', '', $key);

            // если значение массива также является массивом то вызываем себя рекурсивно
            if (is_array($value)) {
                $node = $xml->addChild($key);
                // рекурсивный вызов
                orders::toXml($value, $rootNodeName, $node);
            } else {
                // добавляем один узел
                $value = htmlentities($value);
                $xml->addChild($key,$value);
            }

        }
        // возвратим обратно в виде строки  или просто XML-объект
        return $xml->asXML();
    }

    /**
     * @param $imgname
     * @param $order_id
     * @return mixed
     */
    private function insert_image_to_order($imgname, $order_id)
    {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $mod_id = $this->all_configs['configs']['orders-manage-page'];

        $img_id = $this->all_configs['db']->query(
            'INSERT INTO {orders_images} (image_name, order_id) VALUES (?, ?i)',
            array(trim($imgname), intval($order_id)), 'id');

        if ($img_id) {
            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                array($user_id, 'add-image-goods', $mod_id, intval($order_id)));
        }

        return $img_id;
    }

    /**
     * @param $item_id
     * @return string
     */
    function get_unbind_order_product_btn($item_id){
        $btn = '';
        if($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders') || $this->all_configs['oRole']->hasPrivilege('logistics')){
            $btn = '
                <input class="btn btn-xs" type="button" value="' . l('Отвязать') . '" onclick="alert_box(this,null,\'bind-move-item-form\',{object_id:'.$item_id.'},null,\'warehouses/ajax/\')">
            ';
        }
        return $btn;
    }

    /**
     * @throws Exception
     */
    function ajax()
    {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $mod_id = $this->all_configs['configs']['orders-manage-page'];
        $act = isset($_GET['act']) ? trim($_GET['act']) : '';
        $data = array('state' => false);
        $data['modal'] = isset($_GET['show']) && $_GET['show'] == 'modal';

        // проверка доступа
        if ($this->can_show_module() == false) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => l('Нет прав'), 'state' => false));
            exit;
        }

        if($act == 'manager-setup') {
            if($_SERVER['REQUEST_METHOD'] == 'GET') {
                $this->manager_setup_form();
            } else {
                $this->manager_setup();
            }
        }

        // грузим табу
        if ($act == 'tab-load') {
            if (isset($_POST['tab']) && !empty($_POST['tab'])) {
                //$this->preload();
                header("Content-Type: application/json; charset=UTF-8");

                if (method_exists($this, $_POST['tab'])) {
                    $function = call_user_func_array(
                        array($this, $_POST['tab']),
                        array(
                            (isset($_POST['hashs']) && mb_strlen(trim($_POST['hashs'],
                                    'UTF-8')) > 0) ? trim($_POST['hashs']) : null
                        )
                    );
                    if (!isset($function['debug'])) {
                        $function['debug'] = '';
                    }
                    $return = array(
                        'html' => $function['html'],
                        'state' => true,
                        'functions' => $function['functions'],
                        'debug' => $function['debug']
                    );
                    if (isset($function['menu'])) {
                        $return['menu'] = $function['menu'];
                    }
                    echo json_encode($return);
                } else {
                    echo json_encode(array('message' => l('Не найдено'), 'state' => false));
                }
                exit;
            }
        }

        // вывод заказа
        if ($act == 'display-order') {
            $data['state'] = true;
            $data['width'] = true;
            $data['content'] = '<br />' . $this->genorder($_POST['object_id']);
        }

        // история статусов заказа
        if ($act == 'order-statuses') {
            $data['state'] = true;
            $data['content'] = l('История изменения статусов не найдена');
            $statuses = $this->all_configs['db']->query('SELECT s.status, s.date, u.* FROM {order_status} as s
                LEFT JOIN {users} as u ON u.id=s.user_id WHERE s.order_id=?i ORDER BY `date` DESC',
                array(isset($_POST['object_id']) ? $_POST['object_id'] : 0))->assoc();
            if ($statuses) {
                $sts = $this->all_configs['configs']['order-status'];
                $data['content'] = '<table class="table"><thead><tr><td>'.l('Статус').'</td><td>' . l('Автор') . '</td><td>'.l('Дата').'</td></tr></thead><tbody>';
                foreach ($statuses as $status) {
                    $data['content'] .= '<tr><td>' . (isset($sts[$status['status']]) ? $sts[$status['status']]['name'] : '') . '</td>';
                    $data['content'] .= '<td>' . get_user_name($status) . '</td>';
                    $data['content'] .= '<td><span title="' . do_nice_date($status['date'], false) . '">' . do_nice_date($status['date']) . '</span></td></tr>';
                }
                $data['content'] .= '</tbody></table>';
            }
        }

        // удаляем фото-изображение
        if ($act == 'remove-order-image') {
            if (isset($_POST['order_image_id']) && $this->all_configs['oRole']->hasPrivilege('client-order-photo')) {
                $this->all_configs['db']->query('DELETE FROM {orders_images} WHERE id=?i',
                    array($_POST['order_image_id']));
            }
        }

        // изображения устройства
        if ($act == 'order-gallery') {
            $data['state'] = true;
            $order_id = isset($_POST['object_id']) ? $_POST['object_id'] : 0;
            $images = $this->all_configs['db']->query('SELECT * FROM {orders_images} WHERE order_id=?i',
                array($order_id))->assoc();
            $data['content'] = '<div class="row-fluid"><div class="span3 order-fotos ' . ($this->all_configs['oRole']->hasPrivilege('client-order-photo') ? 'can-remove' : '') . '">';
            if ($images) {
                $img_path = $this->all_configs['siteprefix'] . $this->all_configs['configs']['orders-images-path'];
                foreach ($images as $image) {
                    $src = $img_path . $image['order_id'] . '/' . urldecode($image['image_name']);
                    $data['content'] .= '<div class="order-foto"><i class="glyphicon glyphicon-remove cursor-pointer" onclick="remove_order_image(this, ' . $image['id'] . ')"></i>';
                    $data['content'] .= '<img data-toggle="lightbox" href="#order-image-' . $image['id'] . '" src="' . $src . '" />';
                    $data['content'] .= '<div id="order-image-' . $image['id'] . '" class="lightbox hide fade"  tabindex="-1" role="dialog" aria-hidden="true">';
                    $data['content'] .= '<div class="lightbox-content"><img src="' . $src . '"></div></div></div>';
                    //$data['content'] .= '<div class="lightbox-caption"></div>';
                }
            }
            $data['content'] .= '</div><div class="span8">';
            require_once $this->all_configs['path'] . 'class_webcam.php';
            $webcam = new Products_webcam($this->all_configs);
            $data['content'] .= $webcam->gen_html_body();
            $data['content'] .= '</div></div>';

            //if ($this->all_configs['oRole']->hasPrivilege('client-order-photo')) {
                $data['btns'] = '<input type="button" class="btn btn-info btn-show-webcam" value="' . l('Открыть вебкамеру') . '">';
                $data['btns'] .= '<input type="button" style="display: none;" class="btn btn-info btn-capture" value="' . l('Сфотографировать') .'" data-loading-text="' . l('Фотографирование') .'...">';
                $data['btns'] .= '<input data-order_id="' . $order_id . '" type="button" style="display: none;" class="btn btn-success" id="btn-upload-and-crop" value="' . l('Загрузить и прикрепить') .'">';
            //}
        }

        // фото
        if ($act == 'webcam_upload' ) {
            //if ($this->all_configs['oRole']->hasPrivilege('client-order-photo')) {
                require_once $this->all_configs['path'] . 'class_webcam.php';

                $webcam = new Products_webcam($this->all_configs);

                $w = isset($_GET['w']) ? $_GET['w'] : '';
                $h = isset($_GET['h']) ? $_GET['h'] : '';
                $x = isset($_GET['x']) ? $_GET['x'] : '';
                $y = isset($_GET['y']) ? $_GET['y'] : '';
                $base64dataUrl = isset($_POST['base64dataUrl']) ? $_POST['base64dataUrl'] : '';
                $order_id = isset($_GET['order_id']) && is_numeric($_GET['order_id']) ? $_GET['order_id'] : '';

                if ($order_id > 0) {
                    $data = $webcam->upload_image($base64dataUrl, $w, $h, $x, $y, $order_id);

                    if ($data && isset($data['state']) && $data['state'] == true && isset($data['imgname'])) {
                        $data['imgid'] = $this->insert_image_to_order($data['imgname'], $order_id);
                    } else {
                        $data['msg'] = isset($data['msg']) ? $data['msg'] : l('Произошла ошибка при сохранении');
                    }
                } else {
                    $data['msg'] = 'Заказ не найден';
                }
            /*} else {
                $data['msg'] = 'Нет прав';
            }*/
        }

        // управление заказами поставщика
        if ($act == 'so-operations') {
            $this->all_configs['suppliers_orders']->operations(isset($_POST['object_id']) ? $_POST['object_id'] : 0);
        }

        // форма принятия заказа поставщику
        if ($act == 'form-accept-so') {
            $this->all_configs['suppliers_orders']->accept_form();
        }

        // форма принятия заказа поставщику и приходования
        if ($act == 'form-accept-so-and-debit') {
            $this->all_configs['suppliers_orders']->accept_form(true);
        }

        // заявки
        if ($act == 'orders-link') {
            $so_id = isset($_POST['order_id']) ? $_POST['order_id'] : 0;
            $co_id = isset($_POST['so_co']) ? $_POST['so_co'] : 0;
            $data = $this->all_configs['suppliers_orders']->orders_link($so_id, $co_id);
        }

        // отправить смс
        if ($act == 'send-sms') {
            $text = isset($_POST['text']) ? trim($_POST['text']) : '';
            $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
            $result = send_sms($phone, $text);
            $data['msg'] = $result['msg'];
        }

        // отправить смс
        if ($act == 'sms-form') {
            $data['state'] = true;
            $order_id = isset($_POST['object_id']) ? $_POST['object_id'] : 0;
            $data['content'] = '<p>Заказ не найден</p>';
            $order = $this->all_configs['db']->query('SELECT * FROM {orders} WHERE id=?i',
                array($order_id))->row();

            if ($order) {
                $data['content'] = '<form method="POST" id="sms-form">';
                $data['content'] .= '<div class="form-group"><label>' . l('Номер телефона') . ': </label><div class="controls">';
                $data['content'] .= '<input class="form-control" name="phone" type="text" value="' . htmlspecialchars($order['phone']) . '" /></div></div>';
                $data['content'] .= '<div class="form-group"><label class="control-label">' . l('Текст') . ': </label><div class="controls">';
                $data['content'] .= '<textarea class="form-control show-length" maxlength="69" name="text">'.l('Ваш заказ').' №'.$order['id'].' ' . l('готов') .'. ' . l('Стоимость ремонта') . ': ' . ($order['sum'] / 100) . ' '. viewCurrency() .'</textarea></div></div>';
                $data['content'] .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';
                $data['content'] .= '</form>';
                $data['btns'] = '<input type="button" onclick="send_sms(this)" class="btn" value="' . l('Отправить') . '" />';
            }
        }

        // заказ на изделие
        if ($act == 'order-item') {
            $_POST['order_id'] = isset($this->all_configs['arrequest'][2]) ? $this->all_configs['arrequest'][2] : 0;
            $data = $this->all_configs['chains']->order_item($mod_id, $_POST);
        }

        // редактируем заказ поставщику
        if ($act == 'edit-supplier-order') {
            // проверка на создание заказа с ценой 0
            $price = isset($_POST['warehouse-order-price']) ? intval($_POST['warehouse-order-price'] * 100) : 0;
            if ($price == 0) {
                $data['state'] = false;
                $data['msg'] = 'Укажите цену больше 0';
            } else {
                $data = $this->all_configs['suppliers_orders']->edit_order($mod_id, $_POST);
                if ($data['state'] == true) {
                    //$data['location'] = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '#create_supplier_order';
                }
            }
        }

        // редактируем дату проверки заказа поставщику
        if ($act == 'edit-so-date_check') {
            if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
                if (isset($_POST['date_check']) && strtotime($_POST['date_check']) > 0 && isset($_POST['order_id'])) {
                    $this->all_configs['db']->query('UPDATE {contractors_suppliers_orders} SET date_check=? WHERE id=?i',
                        array($_POST['date_check'], $_POST['order_id']));
                    $data['state'] = true;
                } else {
                    $data['msg'] = l('Укажите дату');
                }
            } else {
                $data['msg'] = l('Нет прав');
            }
        }
        // изменяем видимую стоимость предмета или услуги в заказе
        if ($act == 'change-visible-prices') {
            if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
                $data['msg'] = l('Цена изменилась');
                if (!empty($_POST['id']) && !empty($_POST['price']) && is_numeric($_POST['price'])) {
                    $this->all_configs['db']->query('UPDATE {orders_goods} SET price=? WHERE id=?i',
                        array($_POST['price'] * 100, $_POST['id']));
                    $data['state'] = true;

                    $order = $this->all_configs['db']->query('SELECT o.* FROM {orders} o, {orders_goods} og WHERE og.order_id=o.id AND og.id=?',
                        array($_POST['id']))->row();
                    if ($order['total_as_sum']) {
                        $sum = $this->all_configs['chains']->getTotalSum($order);
                        if ($sum != $order['sum']) {
                            $this->all_configs['db']->query('UPDATE {orders} SET `sum`=?i  WHERE id=?i',
                                array($sum, $order['id']))->ar();
                            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?',
                                array(
                                    $user_id,
                                    'update-order-sum',
                                    $mod_id,
                                    $order['id'],
                                    ($sum / 100)
                                ));
                        }
                    }
                } else {
                    $data['msg'] = l('Укажите новую цену');
                }
            } else {
                $data['msg'] = l('Нет прав');
            }
        }


        // создаем заказ поставщику
        if ($act == 'create-supplier-order') {
            // проверка на создание заказа с ценой 0
            $price = isset($_POST['warehouse-order-price']) ? intval($_POST['warehouse-order-price'] * 100) : 0;
            if ($price == 0) {
                $data['state'] = false;
                $data['msg'] = 'Укажите цену больше 0';
            } else {
                $data = $this->all_configs['suppliers_orders']->create_order($mod_id, $_POST);
                if ($data['state'] == true && $data['id'] > 0) {
                    $data['hash'] = '#show_suppliers_orders';
                }
            }
        }

        if ($act == 'set-total-as-sum') {
            $order = $this->all_configs['db']->query('SELECT * FROM {orders} WHERE id=?',
                array($_POST['id']))->row();
            if (!empty($order)) {
                $set = $_POST['total_set'] == 'true';
                $sum = $set ? $this->all_configs['chains']->getTotalSum($order) : $order['sum'];
                $ar = $this->all_configs['db']->query('UPDATE {orders} SET total_as_sum=?i, `sum`=?i  WHERE id=?i',
                    array((int)$set, $sum, $_POST['id']))->ar();
                if ($sum != $order['sum']) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?',
                        array(
                            $user_id,
                            'update-order-sum',
                            $mod_id,
                            $_POST['id'],
                            ($sum / 100)
                        ));
                }
                $data = array(
                    'state' => $ar > 0,
                    'set' => $set
                );
            } else {
                $data = array('state' => false);
            }
        }

        // редактировать заказ
        if ($act == 'update-order') {

            $data['state'] = true;
            $data['reload'] = false;
            $order_id = isset($this->all_configs['arrequest'][2]) ? $this->all_configs['arrequest'][2] : null;

            // достаем заказ
            $order = $_order = $this->all_configs['db']->query('SELECT * FROM {orders} WHERE id=?',
                array($order_id))->row();

            // комментарии к заказам
            if ((!empty($_POST['private_comment']) || !empty($_POST['public_comment']))) {
                if ($this->all_configs['oRole']->hasPrivilege('add-comment-to-clients-orders')) {
                    $private = !empty($_POST['private_comment']) ? trim($_POST['private_comment']) : '';
                    $public = !empty($_POST['public_comment']) ? trim($_POST['public_comment']) : '';
                    $type = $private ? 1 : 0;
                    $text = $private ?: $public;
                    $this->all_configs['suppliers_orders']->add_client_order_comment($order_id, $text, $type);
                    $data['reload'] = true;
                }
            }else{

                if ($data['state'] == true && (!$this->all_configs['oRole']->hasPrivilege('edit-clients-orders') || !$order/* || $order['manager'] != $_SESSION['id']*/)) {
                    //$data['msg'] = 'Вы не являетесь менеджером этого заказа';
                    $data['msg'] = l('У Вас нет прав');
                    $data['state'] = false;
                }
                if ($data['state'] == true && !$order) {
                    $data['msg'] = l('Заказ не найден');
                    $data['state'] = false;
                }
                if ($data['state'] == true && isset($_POST['is_replacement_fund']) && isset($_POST['replacement_fund']) && mb_strlen(trim($_POST['replacement_fund']), 'utf-8') == 0) {
                    $data['msg'] = l('Укажите подменный фонд');
                    $data['state'] = false;
                }
                if ($data['state'] == true && isset($_POST['categories-goods']) && intval($_POST['categories-goods']) == 0) {
                    $data['msg'] = l('Укажите устройство');
                    $data['state'] = false;
                }

                if ($data['state'] == true) {

                // принимаем заказ
                if (!empty($_POST['accept-manager']) && $this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                    $order['manager'] = $user_id;
//                    $this->all_configs['db']->query('UPDATE {orders} SET manager=?i WHERE id=?i AND (manager IS NULL OR manager=0 OR manager="")',
//                        array($user_id, $order_id));
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                        array($user_id, 'manager-accepted-order', $mod_id, $order_id));
                }

                // меняем статус
                $response = update_order_status($order, $_POST['status']);
                if (!isset($response['state']) || $response['state'] == false) {
                    $data['state'] = false;
                    $_POST['status'] = $order['status'];
                    $data['msg'] = isset($response['msg']) ? $response['msg'] : l('Статус не изменился');
                }

                // подменный фонд
                if ((isset($_POST['is_replacement_fund']) && isset($_POST['replacement_fund']) && $_POST['replacement_fund'] != $order['replacement_fund'])
                    || (!isset($_POST['is_replacement_fund']) && $order['is_replacement_fund'] == 1)) {
                    $change_id = isset($_POST['is_replacement_fund']) ? 1 : 0;
                    $change = $change_id == 1 ? $_POST['replacement_fund'] : '';
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, `work`=?, map_id=?i, object_id=?i, `change`=?, change_id=?i',
                        array($user_id, 'update-order-replacement_fund', $mod_id, $this->all_configs['arrequest'][2], $change, $change_id));
                }

                // устройство у клиента
                if ((isset($_POST['client_took']) && $order['client_took'] != 1) || (!isset($_POST['client_took']) && $order['client_took'] == 1)) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?, change_id=?i',
                        array($user_id, 'update-order-client_took', $mod_id, $this->all_configs['arrequest'][2], isset($_POST['client_took']) ? l('Устройство у клиента') : l('Устройство на складе'), isset($_POST['client_took']) ? 1 : 0));
                }

                // смена менеджера
                if (isset($_POST['manager']) && intval($order['manager']) != intval($_POST['manager'])) {
                    $user = $this->all_configs['db']->query('SELECT fio, email, login, phone, send_over_sms, send_over_email FROM {users} WHERE id=?i AND avail=1 AND deleted=0',
                        array(intval($_POST['manager'])))->row();
                    if (empty($user)) {
                        FlashMessage::set(l('Менеджер не активен или удален'), FlashMessage::DANGER);
                    } else {
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?, change_id=?i',
                            array(
                                $user_id,
                                'update-order-manager',
                                $mod_id,
                                $this->all_configs['arrequest'][2],
                                get_user_name($user),
                                $_POST['manager']
                            ));
                        $order['manager'] = intval($_POST['manager']);
                        if ($user['send_over_sms']) {
                            send_sms("+{$user['phone']}",
                                'Vi naznacheni otvetstvennim po zakazu #' . $this->all_configs['arrequest'][2]);
                        }
                        if ($user['send_over_email']) {
                            require_once $this->all_configs['sitepath'] . 'mail.php';
                            $mailer = new Mailer($this->all_configs);
                            $mailer->group('order-manager', $user['email'], array('order_id' => $this->all_configs['arrequest'][2]));
                            $mailer->go();
                        }
                    }
                }

                // смена инженера
                if (isset($_POST['engineer']) && intval($order['engineer']) != intval($_POST['engineer'])) {
                    $user = $this->all_configs['db']->query('SELECT fio, email, login, phone, send_over_sms, send_over_email  FROM {users} WHERE id=?i AND deleted=0 AND avail=1',
                        array($_POST['engineer']))->row();
                    if (empty($user)) {
                        FlashMessage::set(l('Менеджер не активен или удален'), FlashMessage::DANGER);
                    } else {
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?, change_id=?i',
                            array(
                                $user_id,
                                'update-order-engineer',
                                $mod_id,
                                $this->all_configs['arrequest'][2],
                                get_user_name($user),
                                $_POST['engineer']
                            ));
                        if ($user['send_over_sms']) {
                            send_sms("+{$user['phone']}",
                                'Vi naznacheni otvetstvennim po zakazu #' . $this->all_configs['arrequest'][2]);
                        }
                        if ($user['send_over_email']) {
                            require_once $this->all_configs['sitepath'] . 'mail.php';
                            $mailer = new Mailer($this->all_configs);
                            $mailer->group('order-manager', $user['email'], array('order_id' => $this->all_configs['arrequest'][2]));
                            $mailer->go();

                        }
                    }
                }

                // смена Неисправность со слов клиента
                if (isset($_POST['defect']) && trim($order['defect']) != trim($_POST['defect'])) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?',
                        array($user_id, 'update-order-defect', $mod_id, $this->all_configs['arrequest'][2], trim($_POST['defect'])));
                    $order['defect'] = trim($_POST['defect']);
                }

                // смена Примечание/Внешний вид
                if (isset($_POST['comment']) && trim($order['comment']) != trim($_POST['comment'])) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?',
                        array($user_id, 'update-order-comment', $mod_id, $this->all_configs['arrequest'][2], trim($_POST['comment'])));
                    $order['comment'] = trim($_POST['comment']);
                }

                // смена серийника
                if (isset($_POST['serial']) && trim($order['serial']) != trim($_POST['serial'])) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?',
                        array($user_id, 'update-order-serial', $mod_id, $this->all_configs['arrequest'][2], trim($_POST['serial'])));
                    $order['serial'] = trim($_POST['serial']);
                }

                // смена фио
                if (isset($_POST['fio']) && trim($order['fio']) != trim($_POST['fio'])) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?',
                        array($user_id, 'update-order-fio', $mod_id, $this->all_configs['arrequest'][2], trim($_POST['fio'])));
                    $order['fio'] = trim($_POST['fio']);
                    // апдейтим также клиенту фио
                    $this->all_configs['db']->query("UPDATE {clients} SET fio = ? WHERE id = ?i", array(trim($_POST['fio']), $order['user_id']));
                }

                // смена телефона
                if (isset($_POST['phone'])) {
                    include_once $this->all_configs['sitepath'] . 'shop/access.class.php';
                    $access = new access($this->all_configs, false);
                    $phone = $access->is_phone($_POST['phone']);
                    $phone = $phone ? current($phone) : '';

                    if ($order['phone'] != $phone) {
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?',
                            array($user_id, 'update-order-phone', $mod_id, $this->all_configs['arrequest'][2], $phone));
                        $order['phone'] = $phone;
                    }
                }

                // смена телефона
                if (isset($_POST['warranty']) && intval($order['warranty']) != intval($_POST['warranty'])) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?',
                        array($user_id, 'update-order-warranty', $mod_id, $this->all_configs['arrequest'][2], trim($_POST['warranty'])));
                    $order['warranty'] = intval($_POST['warranty']);
                }

                // смена устройства
                if (isset($_POST['categories-goods']) && intval($order['category_id']) != intval($_POST['categories-goods'])) {
                    $category = $this->all_configs['db']->query('SELECT title FROM {categories} WHERE id=?i',
                        array(intval($_POST['categories-goods'])))->el();
                    if ($category) {
                        $order['title'] = $category;
                        $order['category_id'] = intval($_POST['categories-goods']);
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?, change_id=?i',
                            array($user_id, 'update-order-category', $mod_id, $this->all_configs['arrequest'][2], $category, intval($_POST['categories-goods'])));
                    }
                }

                if ($this->all_configs['oRole']->hasPrivilege('edit_return_id') && isset($_POST['return_id'])) {
                    $this->all_configs['db']->query('UPDATE {orders} SET return_id=?n WHERE id=?i',
                        array(mb_strlen($_POST['return_id'], 'UTF-8') > 0 ? trim($_POST['return_id']) : null, $this->all_configs['arrequest'][2]));
                }
                unset($order['return_id']);
                if(isset($_POST['color']) && array_key_exists($_POST['color'], $this->all_configs['configs']['devices-colors'])){
                    $order['color'] = $_POST['color'];
                }else{
                    unset($order['color']);
                }
                $order['is_replacement_fund'] = isset($_POST['is_replacement_fund']) ? 1 : 0;
                $order['replacement_fund'] = $order['is_replacement_fund'] == 1 ? (isset($_POST['replacement_fund']) ? $_POST['replacement_fund'] : $order['replacement_fund']) : '';
                if ($order['total_as_sum']) {
                    $order['sum'] = $this->all_configs['chains']->getTotalSum($order);
                } else {
                    $order['sum'] = isset($_POST['sum']) ? $_POST['sum'] * 100 : $order['sum'];
                }
                $order['notify'] = isset($_POST['notify']) ? 1 : 0;
                $order['client_took'] = isset($_POST['client_took']) ? 1 : 0;
                $order['nonconsent'] = isset($_POST['nonconsent']) ? 1 : 0;
                $order['is_waiting'] = isset($_POST['is_waiting']) ? 1 : 0;
                $order['engineer'] = isset($_POST['engineer']) ? $_POST['engineer'] : $order['engineer'];
                // если статус доработка то меняем вид ремонта
                $order['repair'] = isset($_POST['status']) && $_POST['status'] == $this->all_configs['configs']['order-status-rework'] ? 2 : $order['repair'];
                if (in_array($_POST['status'], $this->all_configs['configs']['order-status-issue-btn'])) {
                    $data['close'] = $_POST['status'] == $this->all_configs['configs']['order-status-ready'] ? $this->all_configs['configs']['order-status-issued']
                        : ($_POST['status'] == $this->all_configs['configs']['order-status-refused'] || $_POST['status'] == $this->all_configs['configs']['order-status-unrepairable']
                            ? $this->all_configs['configs']['order-status-nowork'] : $order['status']);
                }

                unset($order['date_readiness']);
                unset($order['courier']);
                unset($order['return_id']);

                unset($order['status']);
                unset($order['id']);
                unset($order['wh_id']);
                unset($order['location_id']);
                unset($order['status_id']);
                // смена кода
                if (isset($_POST['code']) && $_POST['code'] != $order['code']) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?',
                        array($user_id, 'update-order-code', $mod_id, $this->all_configs['arrequest'][2], $order['code'].' ==> '.trim($_POST['code'])));
                    $order['code'] = $_POST['code'];
                }
                // смена источника
                if (isset($_POST['referer_id']) && $_POST['referer_id'] != $order['referer_id']) {
                    $referers = get_service("crm/calls")->get_referers();
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?',
                        array($user_id, 'update-order-referer_id', $mod_id, $this->all_configs['arrequest'][2], $referers[$order['referer_id']].' ==> '.$referers[$_POST['referer_id']]));
                    $order['referer_id'] = $_POST['referer_id'];
                }
                // обновляем заказ
                $ar = $this->all_configs['db']->query('UPDATE {orders} SET ?s WHERE id=?i',
                    array($order, $this->all_configs['arrequest'][2]), 'ar');
                // история
                if ($ar) {
                    // сумма
                    if ($_order['sum'] != $order['sum']) {
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?',
                            array($user_id, 'update-order-sum', $mod_id, $this->all_configs['arrequest'][2], ($order['sum'] / 100)));
                    }
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                        array($user_id, 'update-order', $mod_id, $this->all_configs['arrequest'][2]));

                    $get = '?' . get_to_string($_GET);
                    $data['location'] = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . $get . '#show_orders';
                    $data['reload'] = true;
                }
                if ($_POST['status'] == $this->all_configs['configs']['order-status-ready']) {
                    $data['sms'] = true;
                }
            }

            }
        }

        // создать заказ
        if ($act == 'add-order') {
            if(!Tariff::isAddOrderAvailable($this->all_configs['configs']['api_url'], $this->all_configs['configs']['host'])) {
                FlashMessage::set(l('Вы достигли предельного количества заказов. Попробуйте изменить пакетный план.'), FlashMessage::DANGER);
                $data['state'] = false;
            } else {
                Tariff::addOrder($this->all_configs['configs']['api_url'], $this->all_configs['configs']['host']);
                $data = $this->all_configs['chains']->add_order($_POST, $mod_id);
            }
        }

        // создать заказ
        if ($act == 'sale-order') {
            $data = $this->all_configs['chains']->sold_items($_POST, $mod_id);
        }

        preg_match('/changes:(.+)/', $act, $arr);//print_r($arr);
        // история изменений инженера
        if (count($arr) == 2 && isset($arr[1])) {
            $data['state'] = true;
            $data['content'] = l('История изменений не найдена');

            if (isset($_POST['object_id'])) {
                $changes = $this->all_configs['db']->query(
                    'SELECT u.login, u.email, u.fio, u.phone, ch.change, ch.date_add FROM {changes} as ch
                     LEFT JOIN {users} as u ON u.id=ch.user_id WHERE ch.object_id=?i AND ch.map_id=?i AND work=? ORDER BY ch.date_add DESC',
                    array($_POST['object_id'], $mod_id, trim($arr[1])))->assoc();
                if ($changes) {
                    $data['content'] = '<table class="table"><thead><tr><td>' . l('manager') . '</td><td>'.l('Дата').'</td><td>' . l('Изменение') . '</td></tr></thead><tbody>';
                    foreach ($changes as $change) {
                        $data['content'] .= '<tr><td>' . get_user_name($change) . '</td>';
                        $data['content'] .= '<td><span title="' . do_nice_date($change['date_add'], false) . '">' . do_nice_date($change['date_add']) . '</span></td>';
                        $data['content'] .= '<td>' . htmlspecialchars($change['change']) . '</td></tr>';
                    }
                    $data['content'] .= '</tbody></table>';
                }
            }

        }

        // история перемещений заказа
        if ($act == 'stock_moves-order') {
            $data['state'] = true;
            $data['content'] = $this->all_configs['chains']->stock_moves(isset($_POST['object_id']) ? $_POST['object_id'] : 0);
        }

        // удаление комментария
        if ($act == 'remove-comment') {
            if (isset($_POST['comment_id'])) {
                $this->all_configs['db']->query('DELETE FROM {orders_comments} WHERE id=?i', array($_POST['comment_id']));
                $data['state'] = true;
            }
        }

        // создание клента
        if ($act == 'create-client') {
            $data['state'] = true;
            /*$orders_html .= '<div class="control-group"><label class="control-label">Выберите клиента: </label><div class="controls">';
            $orders_html .= typeahead($this->all_configs['db'], 'clients', false, 0, 2, 'input-xlarge', 'input-medium') . '</div></div>';
           */
            $data['content'] = '<form id="form-create-client" method="post">';
            $data['content'] .= '<div class="form-group"><label>' . l('Электронная почта') . ': </label>';
            $data['content'] .= '<input type="text" class="form-control" name="email" value="" placeholder="' . l('Электронная почта') . '" /></div>';
            $data['content'] .= '<div class="form-group"><label class="control-label">' . l('Ф.И.О') . ': </label>';
            $data['content'] .= '<input class="form-control" type="text" name="fio" value="" placeholder="' . l('Ф.И.О') . '" /></div>';
            $data['content'] .= '<div class="form-group"><label class="control-label">' . l('Телефон') . ': </label>';
            $data['content'] .= '<input class="form-control" type="text" name="phone" value="" placeholder="' . l('Телефон') . '" /></div>';
            $data['content'] .= '</form>';
            $data['btns'] = '<input class="btn btn-success" onclick="create_client(this)" type="button" value="' . l('Создать') . '" />';
        }

        // добавление нового клиента
        if ($act =='add_user') {
            if (!$this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('У Вас недостаточно прав'), 'error' => true));
                exit;
            }

            require_once($this->all_configs['sitepath'] . 'shop/access.class.php');
            $access = new access($this->all_configs, false);
            $data = $access->registration($_POST);
            if ($data['id'] > 0) {
                $fio = isset($_POST['fio']) ? htmlspecialchars($_POST['fio']) : '';
                $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
                $phone = isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '';
                $data['name'] = $fio . ', ' . $email . ', ' . $phone;
            }
        }

        // важная информация при добавлении устройства в новый заказ на ремонт
        if ($act == 'service-information') {
            $data['state'] = true;
            $data['title'] = l('Важная информация');
//            $data['content'] = trim($this->all_configs['settings']['service-page-information']);
            $data['content'] = '';

            if (isset($_POST['category_id'])) {
                // достаем категорию
                $category = $this->all_configs['db']->query('SELECT * FROM {categories} WHERE id=?i',
                    array(intval($_POST['category_id'])))->row();
                if ($category && $category['information'] && mb_strlen(trim($category['information']), 'utf-8') > 0) {
                    $data['content'] = trim($category['information']);
                }
            }
        }

        // изделие проверенно
        if ($act == 'check-item') {
            if (isset($_POST['item_id']) && intval($_POST['item_id']) > 0) {
                $data['state'] = true;
                $this->all_configs['db']->query('UPDATE {warehouses_goods_items} SET date_checked=NOW() WHERE id=?i',
                    array(intval($_POST['item_id'])));
            } else {
                $data['msg'] = l('Изделие не найдено');
            }
        }

        // подтверждение
        if ($act == 'confirm-without-prepay') {
            $data = array();

            if (isset($_POST['order_id']) && $_POST['order_id'] > 0 && isset($_POST['status'])
                    && $_POST['status'] == $this->all_configs['configs']['order-status-work']) {

                $order = $this->all_configs['db']->query('SELECT payment, status FROM {orders} WHERE id=?i',
                    array($_POST['order_id']))->row();

                $order['payment'] = array_key_exists('payment', $_POST) ? $_POST['payment'] : $order['payment'];

                if ($order && array_key_exists($order['payment'], $this->all_configs['configs']['payment-msg'])
                    && $this->all_configs['configs']['payment-msg'][$order['payment']]['pay'] == 'pre') {

                    $data = array('status' => $order['status'], 'confirm' => true);
                }
            }
        }

        // добавляем форму заказа поставщику
        if ($act == 'add-supplier-form') {
            $data['state'] = true;
            $counter = isset($_POST['counter']) ? intval($_POST['counter']) : 0;
            $id = isset($_POST['id']) ? $_POST['id'] : null;
            $data['html'] = $this->all_configs['suppliers_orders']->create_order_block(1, $id, false, $counter);
        }

        if ($act == 'supplier-order-form') {
            $data['state'] = true;
            $counter = 0;
            $id = isset($_POST['id']) ? $_POST['id'] : null;
            $data['html'] = $this->all_configs['suppliers_orders']->create_order_block(true, $id, true, $counter, true);
        }

        // открываем форму привязки запчасти к ремонту
        if($act == 'bind-product-to-order'){
            $data['state'] = true;
            $product_id = $_POST['product_id'];
            $data_ops = $this->all_configs['chains']->stockman_operations_goods($product_id);
            $operations = $this->all_configs['chains']->get_operations(1, null, false, $data_ops['goods']);
            $ops = $this->all_configs['chains']->show_stockman_operation($operations[0], 1, $data_ops['serials'], true);
            $data['html'] = '
                <table class="table">
                    '.$ops.'
                </table>
            ';
        }

        if ($act == 'client-bind') {

            if (!$this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('У Вас недостаточно прав'), 'error' => true));
                exit;
            }
            if ( !isset($_POST['user_id']) || $_POST['user_id'] < 1 || !isset($_POST['order_id']) || $_POST['order_id'] < 1 ) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Такого клиента не существует'), 'error'=>true));
                exit;
            }

            $u = $this->all_configs['db']->query('SELECT email, id FROM {clients}
                WHERE id=?i', array($_POST['user_id']))->row();

            $o = $this->all_configs['db']->query('SELECT email, user_id, id FROM {orders}
                WHERE id=?i', array($_POST['order_id']))->row();

            if ( !$u || !$o || $u['email'] != $o['email'] ) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => l('Такого клиента не существует'), 'error'=>true));
                exit;
            }
            $this->all_configs['db']->query('UPDATE {orders} SET user_id=?i WHERE id=?i', array($_POST['user_id'], $_POST['order_id']));
            $data['message'] = l('Заказ успешно привязан');
        }

        /*// выгрузка заказа
        if ( $act == 'export_order' && $this->all_configs['configs']['onec-use'] == true ) {
            if ( !isset($_POST['order_id']) || $_POST['order_id'] < 1 ) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => 'Такого заказа не существует', 'error'=>true));
                exit;
            }

            $uploaddir = $this->all_configs['sitepath'].'1c/orders/';
            if ( !is_dir($uploaddir) ) {
                if( mkdir($uploaddir))  {
                    chmod( $uploaddir, 0777 );
                } else {
                    header("Content-Type: application/json; charset=UTF-8");
                    echo json_encode(array('message' => 'Нет доступа к директории ' . $uploaddir, 'error'=>true));
                    exit;
                }
            }

            $order = $this->all_configs['db']->query('SELECT o.`id`, o.`sum`, o.`comment`, c.`fio`, c.`id` as user_id, o.`date_add` as date, o.`course_value`
                FROM {orders} as o

                LEFT JOIN (SELECT `fio`, `id` FROM {clients})c ON c.id=o.user_id

                WHERE o.id=?i', array($_POST['order_id']))->row();

            if ( !$order ) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => 'Такого заказа не существует', 'error'=>true));
                exit;
            }

            $goods = $this->all_configs['db']->query('SELECT `goods_id`, `title`, `price`, `count`, code_1c, warranties_cost, warranties FROM {orders_goods} WHERE order_id=?i', array($order['id']))->assoc();

            if ( $goods )
                $order['goods'] = $goods;

            $this->all_configs['suppliers_orders']->exportOrder($order);

            $mod_id = $this->all_configs['configs']['orders-manage-page'];

            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                array($user_id, 'export-order', $mod_id, $order['id']));

            $data['message'] = 'Заказ успешно выгружен';
        }*/

        // удаление заказа поставщика
        if ( $act == 'remove-supplier-order' ) {
            $data = $this->all_configs['suppliers_orders']->remove_order($mod_id);
        }

        // принятие заказа
        if ( $act == 'accept-supplier-order' ) {
            $data = $this->all_configs['suppliers_orders']->accept_order($mod_id, $this->all_configs['chains']);
//            $data['state'] = true;
        }

        // запрос на отвязку серийного номера
        if ($act == 'unbind-request-item-serial') {
            $data = $this->all_configs['chains']->unbind_request($mod_id, $_POST);
            if($data['state']){
                $data['unbind'] = $this->get_unbind_order_product_btn((int)$_POST['item_id']);
            }
        }

        // статус заказа поставщику
        if ($act == 'avail-supplier-order') {
            $data = $this->all_configs['suppliers_orders']->avail_order($_POST);
        }

        // добавляем новый товар к заказу выводя его в таблицу
        if ($act =='add_product') {
            $data = $this->all_configs['chains']->add_product_order($_POST, $mod_id, $this);
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

    /**
     * @return array
     */
    public static function get_submenu(){
        global $all_configs;
        $submenu = array(
            array(
                'click_tab' => true,
                'url' => '#show_orders',
                'name' => l('customer_orders')//'Заказы клиентов'
            ),
        );
        if ($all_configs['oRole']->hasPrivilege('site-administration') || $all_configs['oRole']->hasPrivilege('create-clients-orders')) {
            $submenu[] = array(
                'click_tab' => true,
                'url' => '#create_order',
                'name' => l('create_order')//'Создать заказ'
            );
        }
        if ($all_configs['oRole']->hasPrivilege('site-administration')
            || $all_configs['oRole']->hasPrivilege('edit-suppliers-orders')
            || $all_configs['oRole']->hasPrivilege('debit-suppliers-orders')
            || $all_configs['oRole']->hasPrivilege('return-items-suppliers')
        ) {
            $submenu[] = array(
                'click_tab' => true,
                'url' => '#show_suppliers_orders',
                'name' => l('suppliers_orders')//'Заказы поставщику'
            );
        }
        if ($all_configs['oRole']->hasPrivilege('site-administration')
            || $all_configs['oRole']->hasPrivilege('edit-suppliers-orders')
        ) {
            $submenu[] = array(
                'click_tab' => true,
                'url' => '#create_supplier_order',
                'name' => l('create_supplier_order')//'Создать заказ поставщику'
            );
        }
        if ($all_configs['oRole']->hasPrivilege('site-administration') || $all_configs['oRole']->hasPrivilege('orders-manager')) {
            $submenu[] = array(
                'click_tab' => true,
                'url' => '#orders_manager',
                'name' => l('orders_manager')//'Менеджер заказов'
            );
        }
        return $submenu;
    }

    /**
     *
     */
    private function manager_setup_form()
    {
        $data = array(
            'state' => true
        );
        $current = $this->all_configs['db']->query("SELECT * FROM {settings} WHERE name = 'order-manager-configs'")->assoc();
        $data['html'] = $this->view->renderFile('orders/manager_setup', array(
            'orderStatus' => $this->all_configs['configs']['order-status'],
            'shows' => array_keys($this->all_configs['configs']['show-status-in-manager-config']),
            'default' => $this->all_configs['configs']['show-status-in-manager-config'],
            'current' => empty($current) ? array() : json_decode($current[0]['value'], true)
        ));
        $data['title'] = '<center>' . l('Укажите стандарты обслуживания для вашей компании') . '</center>';

        Response::json($data);
    }

    /**
     *
     */
    private function manager_setup()
    {
        $data = array(
            'state' => true
        );
        try {
            if (empty($_POST) || empty($_POST['status'])) {
                throw new Exception(l('Заполните форму'));
            }
            $configs = $_POST['status'];
            $configs['status_repair'] = $_POST['status_repair'];
            $configs['status_sold'] = $_POST['status_sold'];
            $current = $this->all_configs['db']->query("SELECT * FROM {settings} WHERE name = 'order-manager-configs'")->assoc();
            if (empty($current)) {
                $this->all_configs['db']->query(" INSERT INTO {settings} (name, title, description, value, ro) VALUES ('order-manager-configs', ?, ?, ?, 1)",
                    array(
                        'Настройки менеджера заказов',
                        'Настройки менеджера заказов',
                        json_encode($configs)
                    ));

            } else {
                $this->all_configs['db']->query("UPDATE {settings} SET value = ? WHERE name = 'order-manager-configs'",
                    array(json_encode($configs)));
            }


        } catch (Exception $e) {
            $data = array(
                'state' => false,
                'msg' => $e->getMessage()
            );
        }
        Response::json($data);
    }

    /**
     * @return array
     */
    protected function setDefaultHideFieldsConfig()
    {
        $config = array(
            'crm-order-code' => 'on',
            'referrer' => 'on',
            'color' => 'on',
            'serial' => 'on',
            'equipment' => 'on',
            'repair-type' => 'on',
            'defect' => 'on',
            'defect-description' => 'on',
            'appearance' => 'on',
            'cost' => 'on',
            'prepaid' => 'on',
            'available-date' => 'on',
            'addition-info' => 'on'
        );
        $this->all_configs['db']->query(" INSERT INTO {settings} (name, title, description, value, ro) VALUES ('order-fields-hide', ?, ?, ?, 1)",
            array(
                l('Настройки видимости полей заказов'),
                l('Настройки видимости полей заказов'),
                json_encode($config)
            ));
        return $config;

    }

    /**
     *
     */
    private function order_fields_setup()
    {
        $config = empty($_POST['config']) ? array() : $_POST['config'];
        $current = $this->all_configs['db']->query("SELECT * FROM {settings} WHERE name = 'order-fields-hide'")->assoc();
        if (empty($current)) {
            $this->setDefaultHideFieldsConfig();
        } else {
            $this->all_configs['db']->query("UPDATE {settings} SET value = ? WHERE name = 'order-fields-hide'",
                array(json_encode($config)));
        }
    }

    /**
     * @return array
     */
    private function getHideFieldsConfig()
    {
        $current = $this->all_configs['db']->query("SELECT * FROM {settings} WHERE name = 'order-fields-hide'")->assoc();
        if (empty($current)) {
            $current[0]['value'] = json_encode($this->setDefaultHideFieldsConfig());
        }
        return empty($current[0]) ? array() : json_decode($current[0]['value'], true);
    }

    /**
     * @param $client_id
     * @return mixed
     */
    private function getTag($client_id)
    {
        return $this->all_configs['db']->query('SELECT t.color, t.title, t.id FROM {clients} c'
            .' JOIN {tags} t ON t.id = c.tag_id'
            .' WHERE c.id = ?i', array($client_id))->row();
    }
}