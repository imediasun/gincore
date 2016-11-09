<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Models/Cashboxes.php';

$moduleactive[10] = !$ifauth['is_2'];
$modulename[10] = 'orders';
$modulemenu[10] = l('orders');

/**
 * @property  MOrders         Orders
 * @property  MOrdersGoods    OrdersGoods
 * @property  MOrdersComments OrdersComments
 * @property  MLockFilters    LockFilters
 * @property  MTemplateVars   TemplateVars
 * @property  MUsers          Users
 * @property  MStatus         Status
 */
class orders extends Controller
{
    public $uses = array(
        'Orders',
        'OrdersGoods',
        'OrdersComments',
        'LockFilters',
        'TemplateVars',
        'Users',
        'Status'
    );

    public $engineer_colors = array();
    public $colors = array(
        'red',
        'blue',
        'green',
        'yellow',
        'magenta',
        'lime',
        'orange',
        'pink',
        'indigo',
        'teal'
    );

    /**
     * orders constructor.
     * @param      $all_configs
     * @param bool $gen_module
     */
    public function __construct(&$all_configs, $gen_module = true)
    {
        parent::__construct($all_configs);

        require_once($this->all_configs['sitepath'] . 'shop/model.class.php');
        require_once($this->all_configs['sitepath'] . 'mail.php');

        if (!$gen_module) {
            return;
        }
    }

    /**
     * @param array $arrequest
     * @return string
     */
    public function routing(Array $arrequest)
    {
        $result = parent::routing($arrequest);
        if (empty($result) && isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'create' && isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] > 0) {
            $result = $this->genorder();
        }
        return $result;
    }

    /**
     * @return string
     */
    public function renderCanShowModuleError()
    {
        return '<div class="span3"></div>
                    <div class="span9"><p  class="text-danger">' . l('У Вас нет прав для управления заказами') . '</p></div>';

    }

    /**
     * @return bool
     */
    function can_show_module()
    {
        return ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')
            || $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')
            || $this->all_configs['oRole']->hasPrivilege('edit-tradein-orders')
            || $this->all_configs['oRole']->hasPrivilege('show-clients-orders')
            || $this->all_configs['oRole']->hasPrivilege('orders-manager'))
        || $this->all_configs['oRole']->hasPrivilege('engineer');
    }

    /**
     * @param array $post
     */
    public function check_post(Array $post)
    {
        $mod_id = $this->all_configs['configs']['orders-manage-page'];
        $user_id = $this->getUserId();

        // фильтруем заказы клиентов
        if (isset($post['filter-orders'])) {

            $url = $this->filterOrders($post);

            switch (true) {
                case isset($post['sale-order']):
                    $hash = '#show_orders-sold';
                    $this->LockFilters->toggle('sale-orders', $url);
                    break;
                case isset($post['return-order']):
                    $hash = '#show_suppliers_orders-return';
                    break;
                case isset($post['supplier_order_id']):
                    $this->LockFilters->toggle('supplier-orders', $url);
                    $hash = '#show_suppliers_orders-all';
                    break;
                default:
                    $this->LockFilters->toggle('repair-orders', $url);
                    $hash = '#show_orders-orders';
            }
            Response::redirect($this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . http_build_query($url)) . $hash);
        }

        if (isset($post['repair-order-table-columns'])) {
            $this->LockFilters->toggle('repair-order-table-columns', $_POST);
            Response::redirect(Response::referrer());
        }

        // принимаем заказ
        if (isset($post['accept-manager']) == 1 && isset($post['id']) && $post['id'] > 0 && $this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
            $this->all_configs['db']->query('UPDATE {orders} SET manager=?i WHERE id=?i AND (manager IS NULL OR manager=0 OR manager="")',
                array($user_id, $post['id']));
            $this->History->save('manager-accepted-order', $mod_id, $post['id']);
        }

        // фильтрация рекомендаций к закупкам
        if (isset($_POST['procurement-filter'])) {
            $url = '';

            // фильтр по дате
            if (isset($post['date']) && !empty($post['date'])) {
                list($df, $dt) = explode('-', $post['date']);
                $url ['df'] = urlencode(trim($df));
                $url['dt'] = urlencode(trim($dt));
            }

            if (isset($post['ctg']) && is_array($post['ctg']) && count($post['ctg']) > 0) {
                $url['ctg'] = implode(',', $post['ctg']);
            }
            if (isset($post['tso']) && intval($post['tso']) > 0) {
                $url ['tso'] = intval($post['tso']);
            }
            if (isset($post['lock-button'])) {
                $url ['lock-button'] = intval($post['lock-button']);
            }
            $this->LockFilters->toggle('recommendation-procurement', $url);

            Response::redirect($this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . http_build_query($url)));
        }

        if (isset($_POST['save-hide-field-options'])) {
            $config = empty($_POST['config']) ? array() : $_POST['config'];
            $this->order_fields_setup($config);
            if (!empty($_POST['name'])) {
                $data = array(
                    'state' => false,
                    'msg' => l('Проблемы при добавлении пользовательского поля в заказ')
                );
                $this->addUsersField($_POST, $data);
            }
        }

        Response::redirect($_SERVER['REQUEST_URI']);
    }

    /**
     * @return string
     */
    function show_filter_manager_as_row()
    {
        $managers = $this->Users->getByPermission(array('edit-clients-orders', 'site-administration'));
        $mg_get = isset($_GET['mg']) ? explode(',', $_GET['mg']) :
            (isset($_GET['managers']) ? $_GET['managers'] : array());
        return $this->view->renderFile('orders/show_filter_manager_as_row', array(
            'mg_get' => $mg_get,
            'managers' => $managers
        ));
    }

    /**
     * @param bool $compact
     * @param bool $showWrapper
     * @return string
     */
    function show_filter_manager($compact = false, $showWrapper = true)
    {
        $managers = $this->Users->getByPermission(array('edit-clients-orders', 'site-administration'));
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
     * @return string
     */
    function show_filter_manager_small()
    {
        $managers = $this->Users->getByPermission(array('edit-clients-orders', 'site-administration'));
        $mg_get = isset($_GET['mg']) ? explode(',', $_GET['mg']) :
            (isset($_GET['managers']) ? $_GET['managers'] : array());
        return $this->view->renderFile('orders/show_filter_manager_small', array(
            'mg_get' => $mg_get,
            'managers' => $managers
        ));
    }

    /**
     * @param bool $full_link
     * @return string
     */
    function sale_orders_filters($full_link = false)
    {
        if ($full_link) {
            $link = $this->all_configs['prefix'] . 'orders';
        } else {
            $link = '';
        }
        $saved = $this->LockFilters->load('sale-orders');
        if (count($_GET) <= 2 && !empty($saved)) {
            $_GET += $saved;
        }
        $date = (isset($_GET['df']) ? h(urldecode($_GET['df'])) : ''/*date('01.m.Y', time())*/)
            . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
            . (isset($_GET['dt']) ? h(urldecode($_GET['dt'])) : ''/*date('t.m.Y', time())*/);

        $count = $this->all_configs['db']->query('SELECT COUNT(id) FROM {orders}', array())->el();
        $count_unworked = $this->all_configs['db']->query('SELECT COUNT(id) FROM {orders}
            WHERE manager IS NULL OR manager=""', array())->el();
        $count_marked = $this->all_configs['db']->query('SELECT COUNT(um.id) FROM {users_marked} um
            JOIN {orders} o ON o.id=um.object_id 
            WHERE um.user_id=?i AND um.type=? AND o.type=?i', array($_SESSION['id'], 'co', ORDER_SELL))->el();
        // индинеры
        $engineers = $this->Users->getByPermission(array('engineer'));
        $accepters = $this->Users->getByPermission(array('create-clients-orders', 'site-administration'));
        // фильтр по складам (дерево)->get
        $data = $this->all_configs['db']->query('SELECT w.id, w.title, gr.name, gr.color, tp.icon, w.group_id
            FROM {orders} as o, {warehouses} as w
            LEFT JOIN {warehouses_groups} as gr ON gr.id=w.group_id
            LEFT JOIN {warehouses_types} as tp ON tp.id=w.type_id
            WHERE o.accept_wh_id=w.id', array())->assoc();
        if ($data) {
            $wfs = array('groups' => array(), 'nogroups' => array());
            foreach ($data as $wf) {
                if ($wf['group_id'] > 0) {
                    $wfs['groups'][$wf['group_id']]['name'] = h($wf['name']);
                    $wfs['groups'][$wf['group_id']]['warehouses'][$wf['id']]['color'] = h($wf['color']);
                    $wfs['groups'][$wf['group_id']]['warehouses'][$wf['id']]['icon'] = h($wf['icon']);
                    $wfs['groups'][$wf['group_id']]['warehouses'][$wf['id']]['title'] = h($wf['title']);
                } else {
                    $wfs['nogroups'][$wf['id']]['title'] = h($wf['title']);
                    $wfs['nogroups'][$wf['id']]['icon'] = h($wf['icon']);
                    $wfs['nogroups'][$wf['id']]['color'] = h($wf['color']);
                    $wfs['nogroups'][$wf['id']]['icon'] .= ' text-danger';
                }
            }
        }

        $categories = $this->getParentCategories();
        $this->view->load('LockButton');
        return $this->view->renderFile('orders/sale_orders_filters', array(
            'accepters' => $accepters,
            'engineers' => $engineers,
            'filter_manager' => $this->show_filter_manager(true),
            'count' => $count,
            'count_marked' => $count_marked,
            'count_unworked' => $count_unworked,
            'date' => $date,
            'link' => $link,
            'wfs' => isset($wfs) ? $wfs : array(),
            'categories' => $categories

        ));
    }

    /**
     * @return mixed
     */
    public function getParentCategories()
    {
        $query = $this->all_configs['db']->makeQuery('NOT cg.url in (?l)', array(
            array(
                'recycle-bin',
                'prodazha',
                'spisanie',
                'vozvrat-postavschiku',
            )
        ));
        return $this->all_configs['db']->query('
            SELECT cg.* 
            FROM {categories} as cg
            LEFT JOIN (SELECT DISTINCT parent_id FROM {categories}) AS sub ON cg.id = sub.parent_id
            WHERE cg.deleted=0 AND cg.avail=1 AND NOT (sub.parent_id IS NULL OR sub.parent_id = 0) AND ?query 
            ', array($query))->assoc();
    }

    /**
     * @param bool   $full_link
     * @param string $type
     * @return string
     */
    function repair_orders_filters($full_link = false, $type = 'repair')
    {
        if ($full_link) {
            $link = $this->all_configs['prefix'] . 'orders';
        } else {
            $link = '';
        }
        $saved = $this->LockFilters->load('repair-orders');
        if (count($_GET) <= 2 && !empty($saved)) {
            $_GET = $saved;
        }
        $date = (isset($_GET['df']) ? h(urldecode($_GET['df'])) : ''/*date('01.m.Y', time())*/)
            . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
            . (isset($_GET['dt']) ? h(urldecode($_GET['dt'])) : ''/*date('t.m.Y', time())*/);

        $count = $this->all_configs['db']->query('SELECT COUNT(id) FROM {orders}', array())->el();
        $count_unworked = $this->all_configs['db']->query('SELECT COUNT(id) FROM {orders}
            WHERE manager IS NULL OR manager=""', array())->el();
        $count_marked = $this->all_configs['db']->query('SELECT COUNT(um.id) FROM {users_marked} um
            JOIN {orders} o ON o.id=um.object_id 
            WHERE um.user_id=?i AND um.type=? AND o.type in (?li)',
            array($_SESSION['id'], 'co', array(ORDER_REPAIR, ORDER_WRITE_OFF)))->el();
        // индинеры
        $engineers = $this->Users->getByPermission(array('engineer'));
        $accepters = $this->Users->getByPermission(array('create-clients-orders', 'site-administration'));
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
                    $wfs['groups'][$wf['group_id']]['name'] = h($wf['name']);
                    $wfs['groups'][$wf['group_id']]['warehouses'][$wf['id']]['color'] = h($wf['color']);
                    $wfs['groups'][$wf['group_id']]['warehouses'][$wf['id']]['icon'] = h($wf['icon']);
                    $wfs['groups'][$wf['group_id']]['warehouses'][$wf['id']]['title'] = h($wf['title']);
                } else {
                    $wfs['nogroups'][$wf['id']]['title'] = h($wf['title']);
                    $wfs['nogroups'][$wf['id']]['icon'] = h($wf['icon']);
                    $wfs['nogroups'][$wf['id']]['color'] = h($wf['color']);
                    $wfs['nogroups'][$wf['id']]['icon'] .= ' text-danger';
                }
            }
        }
        $categories = $this->getParentCategories();
        $this->view->load('LockButton');
        return $this->view->renderFile('orders/repair_orders_filters', array(
            'accepters' => $accepters,
            'engineers' => $engineers,
            'categories' => $categories,
            'filter_manager' => $this->show_filter_manager_as_row(),
            'count' => $count,
            'count_marked' => $count_marked,
            'count_unworked' => $count_unworked,
            'date' => $date,
            'link' => $link,
            'wfs' => isset($wfs) ? $wfs : array(),
            'brands' => $this->all_configs['db']->query('SELECT id, title FROM {brands}')->vars(),
            'type' => $type
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
    function clients_orders_navigation($full_link = false)
    {
        $link = ($full_link) ? $this->all_configs['prefix'] . 'orders' : '';
        return $this->view->renderFile('orders/clients_orders_navigation', array(
            'link' => $link,
            'repairOrdersFilters' => $this->repair_orders_filters($full_link),
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
            if (!empty($_GET['hash'])) {
                $parts = explode('_', $_GET['hash']);
                if (!empty($parts[2])) {
                    $hash = '#show_orders-' . $parts[2];
                }
            }
        }

        return array(
            'html' => $this->view->renderFile('orders/orders_show_orders', array(
                'clientsOrdersNavigation' => $this->clients_orders_navigation(),
                'hasPrivilege' => $this->all_configs['oRole']->hasPrivilege('show-clients-orders'),
            )),
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
        foreach (array('o_id', 'c_phone', 'o_serial', 'c_fio', 'device', 'manager', 'accepter', 'engineer') as $item) {
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
    public function getOrders($query, $skip = 0, $count_on_page = 0)
    {
        return $this->all_configs['manageModel']->get_clients_orders($query, $skip, $count_on_page, 'co');
    }

    /**
     * @return array
     */
    function show_orders_orders()
    {
        Session::getInstance()->set('current_order_show', ORDER_REPAIR);
        $user_id = $this->getUserId();
        $filters = array('type' => ORDER_REPAIR);
        if ($this->all_configs['oRole']->hasPrivilege('partner') && !$this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $filters['acp'] = $user_id;
        }
        $saved = $this->LockFilters->load('repair-orders');
        if (count($_GET) <= 2 && !empty($saved)) {
            $_GET += $saved;
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

        $this->view->load('DisplayOrder');
        $columns = $this->LockFilters->load('repair-order-table-columns');
        if (empty($columns) || count($columns) == 1) {
            $columns = array(
                'npp' => 'on',
                'notice' => 'on',
                'date' => 'on',
                'accepter' => 'on',
                'manager' => 'on',
                'status' => 'on',
                'device' => 'on',
                'components' => 'on',
                'amount' => 'on',
                'paid' => 'on',
                'client' => 'on',
                'phone' => 'on',
                'terms' => 'on',
                'location' => 'on',
            );
            $this->LockFilters->toggle('repair-order-table-columns', $columns);
        }

        // Спрятать ненужные поля
        if (isset($columns['services'])) {
            unset($columns['services']);
        }

        return array(
            'html' => $this->view->renderFile('orders/show_orders_orders', array(
                'count' => $count,
                'count_page' => $count_page,
                'orders' => $orders,
                'filters' => $filters,
                'repairOrdersFilters' => $this->repair_orders_filters(true),
                'urgent' => $this->Orders->getUrgentCount(),
                'debts' => $this->Orders->getDebts(),
                'columns' => $columns,
                'status' => $this->Status->getAll(ORDER_REPAIR, 'status_id')
            )),
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    public function show_orders_sold()
    {
        Session::getInstance()->set('current_order_show', ORDER_SELL);
        $filters = array('type' => ORDER_SELL);
        $saved = $this->LockFilters->load('sale-orders');
        if (count($_GET) <= 2 && !empty($saved)) {
            $_GET += $saved;
        }
        if (isset($_GET['simple'])) {
            $search = $_GET['simple'];
            unset($_GET['simple']);
            list($query, $orders) = $this->simpleSearch($search, $filters + $_GET);
        } else {
            $queries = $this->all_configs['manageModel']->clients_orders_query($filters + $_GET);
            $query = $queries['query'];
            // достаем заказы
            $orders = $this->getOrders($query, $queries['skip'], $this->count_on_page, 'co');
        }

        $this->view->load('DisplayOrder');
        return array(
            'html' => $this->view->renderFile('orders/show_orders_sold', array(
                'orders' => $orders,
                'count' => empty($orders) ? 0 : $this->all_configs['manageModel']->get_count_clients_orders($query,
                    'co'),
                'count_on_page' => $this->count_on_page,
                'saleOrdersFilters' => $this->sale_orders_filters(true),
                'debts' => $this->Orders->getDebts(ORDER_SELL),
                'status' => $this->Status->getAll(ORDER_SELL, 'status_id')
            )),
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    public function show_orders_return()
    {
        Session::getInstance()->set('current_order_show', ORDER_RETURN);
        $saved = $this->LockFilters->load('supplier-orders');
        if (count($_GET) <= 2 && !empty($saved)) {
            $_GET += $saved;
        }
        $queries = $this->all_configs['manageModel']->clients_orders_query(array('type' => ORDER_RETURN) + $_GET);
        $query = $queries['query'];

        // достаем заказы
        $orders = $this->all_configs['manageModel']->get_clients_orders($query, $queries['skip'], $this->count_on_page,
            'co');

        return array(
            'html' => $this->view->renderFile('orders/show_orders_return', array(
                'orders' => $orders,
                'count' => empty($orders) ? 0 : $this->all_configs['manageModel']->get_count_clients_orders($query,
                    'co'),
                'count_on_page' => $this->count_on_page,
                'status' => $this->Status->getAll(ORDER_REPAIR, 'status_id')
            )),
            'menu' => $this->repair_orders_filters(false, 'return'),
            'functions' => array('reset_multiselect()', 'gen_tree()'),
        );
    }

    /**
     * @return array
     */
    public function show_orders_writeoff()
    {
        Session::getInstance()->set('current_order_show', ORDER_WRITE_OFF);
        $saved = $this->LockFilters->load('repair-orders');
        if (count($_GET) <= 2 && !empty($saved)) {
            $_GET += $saved;
        }
        $queries = $this->all_configs['manageModel']->clients_orders_query(array('type' => ORDER_WRITE_OFF) + $_GET);
        $query = $queries['query'];

        // достаем заказы
        $orders = $this->all_configs['manageModel']->get_clients_orders($query, $queries['skip'], $this->count_on_page,
            'co');

        return array(
            'html' => $this->view->renderFile('orders/show_orders_return', array(
                'orders' => $orders,
                'count' => empty($orders) ? 0 : $this->all_configs['manageModel']->get_count_clients_orders($query,
                    'co'),
                'count_on_page' => $this->count_on_page,
                'repairOrdersFilters' => $this->repair_orders_filters(true),
                'status' => $this->Status->getAll(ORDER_REPAIR, 'status_id')
            )),
            'functions' => array(),
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    public function orders_create_order()
    {
        $orders_html = '';
        if ($this->all_configs['oRole']->hasPrivilege('create-clients-orders')) {

            // на основе заявки
            $order_data = null;
            if (!empty($_GET['on_request'])) {
                $order_data = get_service('crm/requests')->get_request_by_id($_GET['on_request']);
            }
            $cart = null;
            if (!empty($_GET['from_cart']) && Session::getInstance()->check('from_cart')) {
                $cart = Session::getInstance()->get('from_cart');
                Session::getInstance()->clear('from_cart');
            }

            $client_id = $order_data ? $order_data['client_id'] : 0;
            if (!$client_id) {
                $client_id = isset($_GET['c']) ? (int)$_GET['c'] : 0;
            }
            //вывод списска клиентов для создания нового заказа
            $this->view->load('HideField');
            $orders_html = $this->view->renderFile('orders/orders_create_order', array(
                'client' => client_double_typeahead($client_id, 'change_personal,get_requests'),
                'colorsSelect' => $this->view->renderFile('orders/_colors-select', array(
                    'colors' => $this->all_configs['configs']['devices-colors']
                )),
                'order' => $order_data,
                'orderForSaleForm' => $this->order_for_sale_form($client_id),
                'orderEshopForm' => $this->order_for_sale_over_eshop_form($client_id, $cart),
                'hide' => $this->getHideFieldsConfig(),
                'tag' => $this->getTag($client_id),
                'tags' => $this->getTags(),
                'order_data' => $order_data,
                'available' => Tariff::isAddOrderAvailable($this->all_configs['configs']['api_url'],
                    $this->all_configs['configs']['host']),
                'users_fields' => $this->getUsersFields(),
                'managers' => $this->all_configs['oRole']->get_users_by_permissions('edit-clients-orders'),
                'engineers' => $this->getEngineersWithWorkload(),
                'brands' => $this->all_configs['db']->query('SELECT id, title FROM {brands}')->vars(),
                'cart' => $cart
            ));
        }

        return array(
            'html' => $orders_html,
            'functions' => array(),
        );
    }

    /**
     * @param null $clientId
     * @param null $cart
     * @return string
     */
    public function order_for_sale_over_eshop_form($clientId = null, $cart = null)
    {
        $order_data = null;
        $client_id = $order_data ? $order_data['client_id'] : 0;
        if (!$client_id) {
            $client_id = isset($_GET['c']) ? (int)$_GET['c'] : 0;
        }
        $client_fields_for_sale = client_double_typeahead($client_id, 'get_requests_from_eshop');
        return $this->view->renderFile('orders/order_for_sale_over_eshop_form', array(
            'client' => $client_fields_for_sale,
            'orderWarranties' => isset($this->all_configs['settings']['order_warranties']) ? explode(',',
                $this->all_configs['settings']['order_warranties']) : array(),
            'tags' => $this->getTags(),
            'tag' => empty($clientId) ? array() : $this->getTag($clientId),
            'defaultWarranty' => isset($this->all_configs['settings']['default_order_warranty']) ? $this->all_configs['settings']['default_order_warranty'] : 0,
            'deliveryByList' => $this->Orders->getDeliveryByList(),
            'cart' => $cart
        ));
    }

    /**
     * @param null $clientId
     * @return string
     */
    public function order_for_sale_form($clientId = null)
    {
        $Cashboxes = new MCashboxes();
        $order_data = null;
        $client_fields_for_sale = client_double_typeahead();
        return $this->view->renderFile('orders/order_for_sale_form', array(
            'client' => $client_fields_for_sale,
            'orderWarranties' => isset($this->all_configs['settings']['order_warranties']) ? explode(',',
                $this->all_configs['settings']['order_warranties']) : array(),
            'tags' => $this->getTags(),
            'tag' => empty($clientId) ? array() : $this->getTag($clientId),
            'cashboxes' => $Cashboxes->getPreparedCashboxes($this->getUserId()),
            'defaultWarranty' => isset($this->all_configs['settings']['default_order_warranty']) ? $this->all_configs['settings']['default_order_warranty'] : 0
        ));
    }

    /**
     * @param string $hash
     * @return array
     */
    public function orders_show_suppliers_orders($hash = '#show_suppliers_orders')
    {
        $saved = $this->LockFilters->load('supplier-orders');
        if (count($_GET) <= 2 && !empty($saved)) {
            $_GET += $saved;
        }
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
    public function orders_show_suppliers_orders_all()
    {
        $orders_html = '';
        $saved = $this->LockFilters->load('supplier-orders');
        if (count($_GET) <= 2 && !empty($saved)) {
            $_GET += $saved;
        }
        if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
            $my = $this->all_configs['oRole']->hasPrivilege('site-administration') || $this->all_configs['oRole']->hasPrivilege('read-other-suppliers-orders') ? false : true;
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
    public function orders_show_suppliers_orders_wait()
    {
        $orders_html = '';
        $saved = $this->LockFilters->load('supplier-orders');
        if (count($_GET) <= 2 && !empty($saved)) {
            $_GET += $saved;
        }

        if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
            $my = $this->all_configs['oRole']->hasPrivilege('site-administration') || $this->all_configs['oRole']->hasPrivilege('read-other-suppliers-orders') ? false : true;
            $_GET['my'] = ($my || (isset($_GET['my']) && $_GET['my'] == 1));

            // заказы клиентов на которых можно проверить изделия
            $data = $this->all_configs['db']->query('SELECT i.goods_id, o.id
                FROM {warehouses_goods_items} as i, {orders} as o, {category_goods} as cg
                WHERE o.status NOT IN (?li) AND cg.goods_id=i.goods_id AND cg.category_id=o.category_id AND o.type=?i',
                array($this->all_configs['configs']['order-statuses-closed'], ORDER_REPAIR))->assoc();
            $serials = array();
            $g = array();
            if ($data) {
                foreach ($data as $s) {
                    $g[$s['goods_id']] = $s['goods_id'];
                    $url = $this->all_configs['prefix'] . 'orders/create/' . $s['id'];
                    $serials[$s['goods_id']][$s['id']] = '<a href="' . $url . '">' . $s['id'] . '</a>';
                }
            }
            $queries = $this->all_configs['manageModel']->suppliers_orders_query(array(
                    'wait' => true,
                    'gds' => $g
                ) + $_GET);
            $query = $queries['query'];

            $count = $this->all_configs['manageModel']->get_count_suppliers_orders($query);
            $orders = $this->all_configs['manageModel']->get_suppliers_orders($query, $queries['skip'],
                $this->count_on_page);

            $orders_html = $this->view->renderFile('orders/orders_show_suppliers_orders_wait', array(
                'orders' => $orders,
                'count' => $count,
                'count_on_page' => $this->count_on_page,
                'serials' => $serials
            ));
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
        $saved = $this->LockFilters->load('recommendation-procurement');
        if (count($_GET) <= 2 && !empty($saved)) {
            $_GET += $saved;
        }
        $date = (isset($_GET['df']) ? h(urldecode($_GET['df'])) : ''/*date('01.m.Y', time())*/)
            . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
            . (isset($_GET['dt']) ? h(urldecode($_GET['dt'])) : ''/*date('t.m.Y', time())*/);

        $this->view->load('LockButton');
        return $this->view->renderFile('orders/menu_recommendations_procurement', array(
            'date' => $date,
            'categories' => $this->all_configs['db']->query("SELECT * FROM {categories}")->assoc(),

        ));
    }

    /**
     * @param $year
     * @return int
     */
    public function getIsoWeeksInYear($year)
    {
        $date = new DateTime;
        $date->setISODate($year, 53);
        return ($date->format("W") === "53" ? 53 : 52);
    }

    /**
     * @param $caregories_id
     * @return array
     */
    public function childrens_categories($caregories_id)
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
    public function orders_recommendations_procurement()
    {
        $orders_html = '';
        $debug = '';

        $saved = $this->LockFilters->load('recommendation-procurement');
        if (count($_GET) <= 2 && !empty($saved)) {
            $_GET += $saved;
        }
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
                        $query = $cfg['db']->makeQuery('AND DATE(l.date_add)>=?',
                            array(date('Y-m-d', strtotime($_GET['df']))));
                    }
                    // количество заявок
                    $request = $cfg['db']->query('SELECT l.goods_id, COUNT(DISTINCT l.id)
                        FROM {orders_suppliers_clients} as l, {orders_goods} as g
                        WHERE l.order_goods_id=g.id AND g.item_id IS NULL AND g.goods_id IN (?li) ?query GROUP BY goods_id',
                        array(array_keys($amounts), $query))->vars();
                    // фильтр по дате от
                    $query = '';
                    if (isset($_GET['df']) && strtotime($_GET['df']) > 0) {
                        $query = $cfg['db']->makeQuery('AND DATE(o.date_add)>=?',
                            array(date('Y-m-d', strtotime($_GET['df']))));
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
                        $query = $cfg['db']->makeQuery('AND DATE(i.date_add)>=?',
                            array(date('Y-m-d', strtotime($_GET['df']))));
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
                        array(
                            $cfg['configs']['order-status-issued'],
                            array(
                                $cfg['configs']['erp-co-category-write-off'],
                                $cfg['configs']['erp-co-category-return']
                            ),
                            array_keys($amounts),
                            $query
                        ))->assoc('goods_id:yearweek');

                    // фильтр по дате от
                    $query = '';
                    if (isset($_GET['df']) && strtotime($_GET['df']) > 0) {
                        $query = $cfg['db']->makeQuery('AND DATE(d.date_add)>=?',
                            array(date('Y-m-d', strtotime($_GET['df']))));
                    }
                    // спрос
                    $demand = $cfg['db']->query('
                        SELECT d.goods_id, g.title, d.date_add, COUNT(DISTINCT d.id) as qty_demand,
                        YEARWEEK(d.date_add, 1) as yearweek
                        FROM {goods_demand} as d, {goods} as g
                        WHERE g.id=d.goods_id AND d.date_add IS NOT NULL AND g.id IN (?li) ?query
                        GROUP BY d.goods_id, yearweek ORDER BY d.goods_id, yearweek',
                        array(array_keys($amounts), $query))->assoc('goods_id:yearweek');

                    foreach ($amounts as $p_id => $p) {
                        $amounts[$p_id]['qty_wait_wh'] = isset($wait[$p_id]) ? $wait[$p_id] : 0;
                        $amounts[$p_id]['qty_wait_store'] = $amounts[$p_id]['qty_wait_wh'] - (isset($request[$p_id]) ? $request[$p_id] : 0);
                        $amounts[$p_id]['qty_wait_store'] = $amounts[$p_id]['qty_wait_store'] > 0 ? $amounts[$p_id]['qty_wait_store'] : 0;
                        // дата старта
                        if ((isset($consumption[$p_id]) && isset($demand[$p_id]) && strtotime($consumption[$p_id][key($consumption[$p_id])]['date_add']) > strtotime($demand[$p_id][key($demand[$p_id])]['date_add'])) || (isset($demand[$p_id]) && !isset($consumption[$p_id]))) {
                            $year = date('Y', strtotime($demand[$p_id][key($demand[$p_id])]['date_add']));
                            $week = date('W', strtotime($demand[$p_id][key($demand[$p_id])]['date_add']));
                        } elseif (isset($consumption[$p_id])) {
                            $year = date('Y', strtotime($consumption[$p_id][key($consumption[$p_id])]['date_add']));
                            $week = date('W', strtotime($consumption[$p_id][key($consumption[$p_id])]['date_add']));
                        } else {
                            $year = null;
                            $week = null;
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
                                            'yearweek' => $y . $w,
                                        );
                                    }
                                    // спрос - если расход ноль
                                    if (isset($demand[$p_id][$y . $w]) && $consumption[$p_id][$y . $w]['qty_consumption'] == 0) {
                                        $amounts[$p_id]['qty_demand'] += $demand[$p_id][$y . $w]['qty_demand'];
                                        $matrix[$y . $w] = $demand[$p_id][$y . $w]['qty_demand'] * $this->all_configs['settings']['demand-factor'];
                                    } else {
                                        $demand[$p_id][$y . $w] = array(
                                            'goods_id' => $p_id,
                                            'qty_demand' => 0,
                                            'yearweek' => $y . $w,
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
                            $amounts[$p_id]['qty_consumption'] = count($consumption[$p_id]) > 0 ? round($amounts[$p_id]['qty_consumption'] / count($consumption[$p_id]) * 4,
                                2) : 0;
                            $amounts[$p_id]['qty_consumption'] = '<span class="popover-info" data-content="' . $str . '" data-original-title="' . l('шт / к-во недель') . ' * 4">' . $amounts[$p_id]['qty_consumption'] . '</span>';

                            $str = $amounts[$p_id]['qty_demand'] . ' / ' . count($demand[$p_id]) . ' * ' . 4;
                            $amounts[$p_id]['qty_demand'] = count($demand[$p_id]) > 0 ? round($amounts[$p_id]['qty_demand'] / count($demand[$p_id]) * 4,
                                2) : 0;
                            $amounts[$p_id]['qty_demand'] = '<span class="popover-info" data-content="' . $str . '" data-original-title="' . l('шт / к-во недель') . ' * 4">' . $amounts[$p_id]['qty_demand'] . '</span>';


                            // вычисляем рекомендации к заказу
                            ksort($matrix, SORT_NUMERIC);
                            $k = $numerator = $denominator = $b = $prev = 0;


                            if (count($matrix) > 0) {

                                // определяем суммы за последний и предыдущий месяц (4 недели)
                                $matrixr = array_reverse($matrix);
                                $first_priv = $first_priv2 = 0;
                                for ($mi = 0; $mi <= 3; $mi++) {
                                    $first_priv += isset($matrixr[$mi]) ? $matrixr[$mi] : 0;
                                    $first_priv2 += isset($matrixr[$mi + 4]) ? $matrixr[$mi + 4] : 0;
                                }

                                $average = array_sum($matrix) / count($matrix); //среднее в неделю.
                                //прогноз за выбранный период * 2 (удвоенный)
                                if ($first_priv2 > 0 && ($first_priv2 + $first_priv2 >= 3)) {
                                    $percent = round($first_priv / $first_priv2, 2);
                                    if ($percent > 1.3) {
                                        $percent = 1.3;
                                    }
                                    if ($percent < 0.7) {
                                        $percent = 0.7;
                                    }
                                } else {
                                    $percent = 0;
                                }

                                $amounts[$p_id]['qty_forecast'] = $average * ($qty_weeks * 2) * $percent;

                                $debug .= "1m = " . $first_priv . ", 2m = " . $first_priv2 . "  diff=" . ($first_priv - $first_priv2) . " avr=" . ($average * $qty_weeks) . " \n";

                                $str = '% = ' . ($percent * 100) . '<br>week = ' . $qty_weeks
                                    . '<br>ave = ' . round($average, 2)
                                    . '<pre>' . print_r($matrix, true) . '</pre>';
                                $amounts[$p_id]['qty_recommended'] = $amounts[$p_id]['qty_forecast'] - $amounts[$p_id]['qty_store'] - $amounts[$p_id]['qty_wait_store'];
                                $amounts[$p_id]['qty_recommended'] = /*array_sum($matrix) == 1 ? '&ndash;' : */
                                    ($amounts[$p_id]['qty_recommended'] > 0 ? round($amounts[$p_id]['qty_recommended'],
                                        1) : 0);

                                $amounts[$p_id]['qty_forecast'] = $percent == 0 ? '&ndash;' : round($amounts[$p_id]['qty_forecast'],
                                    1);

                                $amounts[$p_id]['qty_forecast'] = '<span class="popover-info" data-content="' . $str . '" data-original-title="' . l('Среднее значение') . ' * %">' . $amounts[$p_id]['qty_forecast'] . '</span>';
                            }
                        }
                    }
                }
            }
            $orders_html .= $this->view->renderFile('orders/orders_recommendations_procurement', array(
                'amounts' => isset($amounts) ? $amounts : array(),
                'qty_weeks' => $qty_weeks,
                'cfg' => $cfg
            ));
        }
        if (!isset($debug)) {
            $debug = '';
        }
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
    public function orders_create_supplier_order()
    {
        $orders_html = '';
        if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
            $goods = null;
            if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] > 0) {
                $orders_html .= $this->all_configs['suppliers_orders']->create_order_block($goods,
                    $this->all_configs['arrequest'][2]);
            } else {
                switch (true) {
                    case isset($_GET['id_product']):
                        $goods = (int)$_GET['id_product'];
                        break;
                    case ($_GET['from_cart'] && Session::getInstance()->check('from_cart')):
                        $goods = Session::getInstance()->get('from_cart');
                        Session::getInstance()->clear('from_cart');
                        break;

                }
                $orders_html .= $this->all_configs['suppliers_orders']->create_order_block($goods);
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
    public function check_if_order_fail_in_orders_manager($order)
    {
        return check_if_order_fail_in_orders_manager($order);
    }

    /**
     * @param $order
     * @param $day
     * @return bool
     */
    protected function check_with_default_config($order, $day)
    {
        return check_with_default_config($order, $day);
    }

    /**
     * @param string $filters_query
     * @return mixed
     */
    public function get_orders_for_orders_manager($filters_query = '')
    {
        return get_orders_for_orders_manager($filters_query);
    }

    /**
     * @return float|int
     */
    public function get_orders_manager_fail_percent()
    {
        $user_id = $this->getUserId();
        $orders = $this->get_orders_manager_stats($user_id);
        if (empty($orders)) {
            return 0;
        }
        $qty_fail = 0;
        foreach ($orders as $order) {
            if ($this->check_if_order_fail_in_orders_manager($order)) {
                $qty_fail++;
            }
        }
        return round($qty_fail / count($orders) * 100, 2);
    }

    /**
     * @param $manager
     * @return mixed
     */
    public function get_orders_manager_stats($manager)
    {
        $q = $this->get_orders_manager_filter_by_manager_query(array($manager));
        return $this->get_orders_for_orders_manager($q);
    }

    /**
     * @param $mg
     * @return mixed
     */
    public function get_orders_manager_filter_by_manager_query($mg)
    {
        return db()->makeQuery(' (o.manager IN (?li) OR ((o.manager IS NULL OR o.manager = 0) AND o.date_add <= DATE_ADD(NOW(), INTERVAL -24 HOUR))) AND ',
            array($mg));
    }

    /**
     * @param      $colors_count
     * @param null $orders_summ
     * @param bool $as_array
     * @return array|string
     */
    public function gen_orders_manager_stats($colors_count, $orders_summ = null, $as_array = false)
    {
        $colors_percents = '';
        $data = array();
        if ($colors_count) {
            arsort($colors_count);
            if (!$orders_summ) {
                $orders_summ = array_sum($colors_count);
            }
            foreach ($colors_count as $color => $qty) {
                $p = round($qty / $orders_summ * 100, 2);
                $title = $this->get_order_status_name_by_color($color);
                $colors_percents .= '
                    <span ' . ($title ? ' data-toggle="tooltip" title="' . $title . '" ' : '') . ' style="border-radius:5px;margin-right:10px;color:#fff;padding:5px 10px;background-color:#' . $color . '">' .
                    $p . '%
                    </span>
                ';
                $data[$color] = $p;
            }
        } else {
            $colors_percents = '(' . l('статистика отсутствует') . ')';
        }

        if ($as_array) {
            return array(
                'html' => $colors_percents,
                'data' => $data
            );
        } else {
            return $colors_percents;
        }
    }

    private function get_order_status_name_by_color($color)
    {
        if ($color == 'FF0000') {
            return l('Просроченные');
        }
        foreach ($this->all_configs['configs']['order-status'] as $status_data) {
            if ($status_data['color'] == $color) {
                return $status_data['name'];
            }
        }
        return null;
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
            $get_date = isset($_GET['date']) ? h($_GET['date']) : '';
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
                        $manager_block .= '<div data-o_id="' . $order['id'] . '" onclick="edit_order_dialog(this, \'display-order\')" class="order-manager ' . $class . '" ' . $style . '>';
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

            $orders_html = $this->view->renderFile('orders/order_manager_filters', array(
                'service_filter' => $this->all_configs['suppliers_orders']->show_filter_service_center(),
                'manager_filter' => $this->show_filter_manager_small(),
                'get_date' => $get_date,
                'filter_stats' => $filter_stats
            ));

            $orders_html .= '
                <div id="orders-manager-block">
                    ' . $manager_block . '
                </div>
            ';
        }
        $orders_html .= '
            <script>
            jQuery(document).ready(function(){
                reset_multiselect();
            });
            </script>
        ';

        return array(
            'html' => $orders_html,
            //'functions' => array('reset_multiselect()'), //функция повторно вызывается и не работает фильтр
        );
    }

    /**
     * @param $engineers
     * @param $from_order
     */
    private function setEngineerColors($engineers, $from_order)
    {
        $usedColor = $this->all_configs['db']->query('SELECT color FROM {users}')->col();
        if (empty($this->engineer_colors)) {
            foreach ($engineers as $user) {
                if (empty($user['color'])) {
                    if (count($usedColor) >= count($this->colors)) {
                        $color = sprintf("#%06X\n", mt_rand(0, 0xFFFFFF));
                    } else {
                        $diff = array_diff($this->colors, $usedColor);
                        $color = current($diff);
                        $usedColor[] = $color;
                    }
                    $this->Users->update(array(
                        'color' => $color
                    ), array('id' => $user['id']));
                } else {
                    $color = $user['color'];
                }

                if ($user['id'] == $from_order) {
                    $this->engineer_colors[$user['id']] = '#ddd';
                } else {
                    $this->engineer_colors[$user['id']] = $color;
                }
            }
        }
    }

    /**
     * @param $product
     * @param $engineers
     * @param $engineer
     * @return string
     */
    public function show_product($product, $engineers, $engineer)
    {
        $this->setEngineerColors($engineers, $engineer);
        $supplier_order = $this->all_configs['db']->query("SELECT supplier_order_id as id, o.count, o.supplier, "
            . "o.confirm, o.avail, o.count_come, o.count_debit, o.wh_id "
            . "FROM {orders_suppliers_clients} as c "
            . "LEFT JOIN {contractors_suppliers_orders} as o ON o.id = c.supplier_order_id "
            . "WHERE c.client_order_id = ?i AND c.goods_id = ?i",
            array($product['order_id'], $product['goods_id']), 'row');

        return $this->view->renderFile('orders/show_product', array(
            'url' => $this->all_configs['prefix'] . 'products/create/' . $product['goods_id'],
            'product' => $product,
            'supplier_order' => $supplier_order,
            'controller' => $this,
            'engineers' => $engineers,
            'order_engineer' => $engineer,
            'colors' => $this->engineer_colors
        ));
    }

    /**
     * @param $product
     * @return string
     */
    public function show_quicksale_product($product)
    {
        $supplier_order = $this->all_configs['db']->query("SELECT supplier_order_id as id, o.count, o.supplier, "
            . "o.confirm, o.avail, o.count_come, o.count_debit, o.wh_id "
            . "FROM {orders_suppliers_clients} as c "
            . "LEFT JOIN {contractors_suppliers_orders} as o ON o.id = c.supplier_order_id "
            . "WHERE c.client_order_id = ?i AND c.goods_id = ?i",
            array($product['order_id'], $product['goods_id']), 'row');


        return $this->view->renderFile('orders/quicksaleorder/show_product', array(
            'url' => $this->all_configs['prefix'] . 'products/create/' . $product['goods_id'],
            'product' => $product,
            'supplier_order' => $supplier_order,
            'controller' => $this,
            'orderWarranties' => isset($this->all_configs['settings']['order_warranties']) ? explode(',',
                $this->all_configs['settings']['order_warranties']) : array(),
        ));
    }

    /**
     * @param      $product
     * @param int  $quantity
     * @param bool $hash
     * @param bool $group
     * @param bool $hide
     * @return string
     */
    public function show_eshop_product($product, $quantity = 1, $hash = false, $group = false, $hide = false)
    {
        $supplier_order = $this->all_configs['db']->query("SELECT supplier_order_id as id, o.count, o.supplier, "
            . "o.confirm, o.avail, o.count_come, o.count_debit, o.wh_id "
            . "FROM {orders_suppliers_clients} as c "
            . "LEFT JOIN {contractors_suppliers_orders} as o ON o.id = c.supplier_order_id "
            . "WHERE c.client_order_id = ?i AND c.goods_id = ?i",
            array($product['order_id'], $product['goods_id']), 'row');


        return $this->view->renderFile('orders/eshoporder/show_product', array(
            'url' => $this->all_configs['prefix'] . 'products/create/' . $product['goods_id'],
            'product' => $product,
            'supplier_order' => $supplier_order,
            'controller' => $this,
            'orderWarranties' => isset($this->all_configs['settings']['order_warranties']) ? explode(',',
                $this->all_configs['settings']['order_warranties']) : array(),
            'group' => $group,
            'hash' => $hash,
            'hide' => $hide,
            'quantity' => $quantity
        ));
    }

    /**
     * @param null $order_id
     * @param bool $modal
     * @return string
     * @throws Exception
     * @internal param bool $withFilters
     */
    public function genorder($order_id = null, $modal = false)
    {
        $show_btn = true;
        $order_id = ($order_id == 0) ? intval($this->all_configs['arrequest'][2]) : $order_id;
        // достаем заказ с прикрепленными к нему товарами
        $order = $this->all_configs['db']->query('SELECT o.*, o.color as o_color, l.location, w.title as wh_title, gr.color, tp.icon,
                u.fio as m_fio, u.phone as m_phone, u.login as m_login, u.email as m_email,
                a.fio as a_fio, a.phone as a_phone, a.login as a_login, a.email as a_email, aw.title as aw_title, c.tag_id as tag_id,
                c.legal_address as c_legal_address, c.email as c_email, o.engineer_comment
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
        $comments_public = $this->OrdersComments->getPublic($order['id']);
        $comments_private = $this->OrdersComments->getPrivate($order['id']);
        $home_master_request = $this->all_configs['db']->query('SELECT  hmr.*
                FROM {home_master_requests} hmr
                WHERE hmr.order_id=?i ORDER by `date` DESC LIMIT 1', array($order['id']))->row();

        $notSale = $order['type'] != 3;
        $goods = $this->all_configs['manageModel']->order_goods($order['id'], GOODS_TYPE_ITEM);
        $services = $notSale ? $this->all_configs['manageModel']->order_goods($order['id'], GOODS_TYPE_SERVICE) : null;

        $productTotal = 0;
        $price_type = $price_type_of_service = ORDERS_GOODS_PRICE_TYPE_RETAIL;
        if (!empty($goods)) {
            foreach ($goods as $product) {
                $productTotal += $product['price'] * $product['count'];
                if ($product['price_type'] == ORDERS_GOODS_PRICE_TYPE_WHOLESALE) {
                    $price_type = ORDERS_GOODS_PRICE_TYPE_WHOLESALE;
                }
            }
        }
        if (!empty($services)) {
            foreach ($services as $product) {
                $productTotal += $product['price'] * $product['count'];
                if ($product['price_type'] == ORDERS_GOODS_PRICE_TYPE_WHOLESALE) {
                    $price_type_of_service = ORDERS_GOODS_PRICE_TYPE_WHOLESALE;
                }
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
            $parts[] = h($order['equipment']);
        }

        $returns = $this->all_configs['db']->query('SELECT ct.id as id, value_from, currency
                FROM {cashboxes_transactions} ct
                JOIN {cashboxes_currencies} cc ON  ct.cashboxes_currency_id_from=cc.id
                WHERE transaction_type=?i 
                AND (client_order_id IS NULL OR client_order_id=?i OR client_order_id = 0)
                AND supplier_order_id IS NULL 
                AND contractor_category_link IN (SELECT id FROM {contractors_categories_links} WHERE contractors_categories_id = 2 AND deleted=0)',
            // возврат средст
            array(
                TRANSACTION_OUTPUT,
                $order['id']
            )
        )->assoc();
        $hasEditorPrivilege = $this->all_configs['oRole']->hasPrivilege('edit-clients-orders');
        $showUsersFields = false;
        $usersFields = $this->getUsersFieldsValues($order_id);
        $hide = $this->getHideFieldsConfig();
        switch ($order['sale_type']) {
            case 1:
                $template = 'orders/quicksaleorder/genorder';
                $print_templates = $this->TemplateVars->getUsersPrintTemplates('sale_order');
                $status = $this->Status->getAll(ORDER_SELL, 'status_id');
                break;
            case 2:
                $template = 'orders/eshoporder/genorder';
                $print_templates = $this->TemplateVars->getUsersPrintTemplates('sale_order');
                $status = $this->Status->getAll(ORDER_SELL, 'status_id');
                break;
            default:
                $template = $modal ? 'orders/genorder/genorder-modal' : 'orders/genorder/genorder';
                $print_templates = $this->TemplateVars->getUsersPrintTemplates('repair_order');
                $showUsersFields = $this->checkShowUsersFields($usersFields, $hide);
                $status = $this->Status->getAll(ORDER_REPAIR, 'status_id');
        }
        return $this->view->renderFile($template, array(
            'order' => $order,
            'onlyEngineer' => $this->all_configs['oRole']->hasPrivilege('engineer') && !$hasEditorPrivilege,
            'hasEditorPrivilege' => $hasEditorPrivilege,
            'notSale' => $notSale,
            'managers' => $this->all_configs['oRole']->get_users_by_permissions('edit-clients-orders'),
            'engineers' => $notSale ? $this->getEngineersWithWorkload() : null,
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
            'hide' => $hide,
            'tags' => $this->getTags(),
            'returns' => $returns,
            'deliveryByList' => $this->Orders->getDeliveryByList(),
            'repairOrdersFilters' => $this->repair_orders_filters(true),
            'saleOrdersFilters' => $this->sale_orders_filters(true),
            'users_fields' => $usersFields,
            'showUsersFields' => $showUsersFields,
            'homeMasterRequest' => $home_master_request,
            'price_type' => $price_type,
            'price_type_of_service' => $price_type_of_service,
            'print_templates' => $print_templates,
            'brands' => $this->all_configs['db']->query('SELECT id, title FROM {brands}')->vars(),
            'status' => $status
        ));
    }

    /**
     * @return array
     */
    public function getEngineersWithWorkload()
    {
        $callback = function ($element) {
            return $element['id'];
        };
        $users = $this->all_configs['oRole']->get_users_by_permissions('engineer');
        $result = array();
        if (!empty($users)) {
            $ids = array_map($callback, $users);

            $query = $this->all_configs['db']->makeQuery('u.avail=1 AND u.deleted=0 AND id IN (?li)', array($ids));
            $result = $this->all_configs['db']->query('
                SELECT u.*, CONCAT(u.fio, " ", u.login) as name,
                (SELECT count(*) FROM {orders} WHERE engineer=u.id AND NOT status in (?l)) as workload,
                (SELECT count(*) FROM {orders} WHERE engineer=u.id AND status in (?l)) as wait_parts
                FROM {users} as u
                WHERE  ?query',
                array(
                    $this->all_configs['configs']['order-statuses-engineer-not-workload'],
                    $this->all_configs['configs']['order-statuses-expect-parts'],
                    $query
                ))->assoc('id');
        }
        return $result;
    }

    /**
     * @param $products
     * @return array
     */
    public function productsGroup($products)
    {
        return $this->OrdersGoods->productsGroup($products);
    }

    /**
     * @param $products
     * @param $hash
     * @return array
     */
    public function getProductsIdsByHash($products, $hash)
    {
        return $this->OrdersGoods->getProductsIdsByHash($products, $hash);
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
    public static function toXml($data, $rootNodeName = 'data', $xml = null)
    {
        // включить режим совместимости, не совсем понял зачем это но лучше делать
        if (ini_get('zend.ze1_compatibility_mode') == 1) {
            ini_set('zend.ze1_compatibility_mode', 0);
        }

        if ($xml == null) {
            $xml = simplexml_load_string("<?xml version=\"1.0\" encoding=\"utf-8\"?><$rootNodeName />");
        }

        //цикл перебора массива
        foreach ($data as $key => $value) {
            // нельзя применять числовое название полей в XML
            if (is_numeric($key)) {
                // поэтому делаем их строковыми
                $key = "unknownNode_" . (string)$key;
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
                $xml->addChild($key, $value);
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
        $mod_id = $this->all_configs['configs']['orders-manage-page'];

        $img_id = $this->all_configs['db']->query(
            'INSERT INTO {orders_images} (image_name, order_id) VALUES (?, ?i)',
            array(trim($imgname), intval($order_id)), 'id');

        if ($img_id) {
            $this->History->save('add-image-goods', $mod_id, intval($order_id));
        }

        return $img_id;
    }

    /**
     * @param $item_id
     * @return string
     */
    function get_unbind_order_product_btn($item_id)
    {
        return $this->view->renderFile('orders/get_unbind_order_product_btn', array(
            'item_id' => $item_id
        ));
    }

    /**
     * @throws Exception
     */
    function ajax()
    {
        $user_id = $this->getUserId();
        $mod_id = $this->all_configs['configs']['orders-manage-page'];
        $act = isset($_GET['act']) ? trim($_GET['act']) : '';
        $data = array('state' => false);
        $data['modal'] = isset($_GET['show']) && $_GET['show'] == 'modal';

        // проверка доступа
        if ($this->can_show_module() == false) {
            Response::json(array('message' => l('Нет прав'), 'state' => false));
        }

        if ($act == 'manager-setup') {
            if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $this->manager_setup_form();
            } else {
                $this->manager_setup();
            }
        }

        // грузим табу
        if ($act == 'tab-load') {
            if (isset($_POST['tab']) && !empty($_POST['tab'])) {
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
                } else {
                    $return = array('message' => l('Не найдено'), 'state' => false);
                }
                Response::json($return);
            }
        }

        // вывод заказа
        if ($act == 'display-order') {
            $data['state'] = true;
            $data['width'] = true;
            $data['content'] = '<br />' . $this->genorder($_POST['object_id'], true);
        }

        // вывод заказа
        if ($act == 'export' && $this->all_configs['oRole']->hasPrivilege('export-clients-and-orders')) {
            return $this->exportOrders();
        }

        // история статусов заказа
        if ($act == 'order-statuses') {
            $data = $this->orderStatuses($data);
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
            require_once $this->all_configs['path'] . 'class_webcam.php';
            $data['content'] = $this->view->renderFile('orders/order_gallery', array(
                'images' => $images,
                'order_id' => $order_id,
                'webcam' => new Products_webcam($this->all_configs)
            ));

            $data['btns'] = '<input type="button" class="btn btn-info btn-show-webcam" value="' . l('Открыть вебкамеру') . '">';
            $data['btns'] .= '<input type="button" style="display: none;" class="btn btn-info btn-capture" value="' . l('Сфотографировать') . '" data-loading-text="' . l('Фотографирование') . '...">';
            $data['btns'] .= '<input data-order_id="' . $order_id . '" type="button" style="display: none;" class="btn btn-success" id="btn-upload-and-crop" value="' . l('Загрузить и прикрепить') . '">';
        }

        // фото
        if ($act == 'webcam_upload') {
            $data = $this->webcamUpload($data);
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
            if ($result['state']) {
                FlashMessage::set(l('Сообщение принято к отправке'), FlashMessage::SUCCESS);
            } else {
                $data['msg'] = $result['msg'];
            }
        }

        // отправить смс
        if ($act == 'sms-form') {
            if (!$this->all_configs['configs']['can_see_client_infos']) {
                $data['state'] = false;
                $data['message'] = l('У Вас нет доступа к контактным данным клиента. Вы не можете отправить ему смс.');
            } else {
                $data['state'] = true;
                $order_id = isset($_POST['object_id']) ? $_POST['object_id'] : 0;
                $order = $this->all_configs['db']->query('
                    SELECT o.*, c.fio, w.title, w.print_address, w.print_phone, l.location 
                    FROM {orders} o
                    LEFT JOIN {clients} as c ON c.id=o.user_id
                    LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                    LEFT JOIN {warehouses_locations} as l ON l.id=o.location_id 
                    WHERE o.id=?i',
                    array($order_id))->row();

                $data['content'] = $this->view->renderFile('orders/sms_form', array(
                    'order' => $order,
                    'order_id' => $order_id,
                    'templates' => get_service('crm/sms')->get_templates_with_vars('orders', array(
                        '{{order_id}}' => $order_id,
                        '{{pay}}' => (($order['sum'] - $order['sum_paid'] - $order['discount']) / 100) . ' ' . viewCurrency(),
                        '{{order_sum}}' => ($order['sum'] / 100) . ' ' . viewCurrency(),
                        '{{client}}' => $order['fio'],
                        '{{warehouse}}' => $order['title'],
                        '{{warehouse_address}}' => $order['print_address'],
                        '{{warehouse_phone}}' => $order['print_phone'],
                        '{{location}}' => $order['location']
                    ))
                ));
                if ($order) {
                    $data['btns'] = '<input type="button" onclick="send_sms(this)" class="btn" value="' . l('Отправить') . '" />';
                }
            }
        }

        // заказ на изделие
        if ($act == 'order-item') {
            $_POST['order_id'] = isset($this->all_configs['arrequest'][2]) ? $this->all_configs['arrequest'][2] : 0;
            $data = $this->all_configs['chains']->order_item($mod_id, $_POST);
        }

        // редактируем заказ поставщику
        if ($act == 'edit-supplier-order') {
            $data = $this->all_configs['suppliers_orders']->edit_order($mod_id, $_POST);
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
            $data = $this->changeVisiblePrices($data, $user_id, $mod_id);
            Response::json($data);
        }

        // создаем заказ поставщику
        if ($act == 'create-supplier-order') {
            $data = $this->all_configs['suppliers_orders']->create_order($mod_id, $_POST);
            if ($data['state'] == true && $data['id'] > 0) {
                $data['hash'] = '#show_suppliers_orders';
            }
        }

        if ($act == 'set-total-as-sum') {
            $data = $this->setTotalAsSum($user_id, $mod_id);
        }

        // редактировать заказ
        if ($act == 'update-order') {
            if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                $data = $this->updateOrder($data, $user_id, $mod_id);
            } elseif ($this->all_configs['oRole']->hasPrivilege('add-comment-to-clients-orders')) {
                $data = $this->updateComments($data);
            }
            if (empty($data['location'])) {
                $order_id = isset($this->all_configs['arrequest'][2]) ? $this->all_configs['arrequest'][2] : null;
                $data['location'] = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/create/' . $order_id;
            }
        }

        // создать заказ
        if ($act == 'add-order') {
            if (!Tariff::isAddOrderAvailable($this->all_configs['configs']['api_url'],
                $this->all_configs['configs']['host'])
            ) {
                FlashMessage::set(l('Вы достигли предельного количества заказов. Попробуйте изменить пакетный план.'),
                    FlashMessage::DANGER);
                $data['state'] = false;
            } else {
                Tariff::addOrder($this->all_configs['configs']['api_url'], $this->all_configs['configs']['host']);
                $data = $this->all_configs['chains']->add_order($_POST, $mod_id);
            }
        }

        // создать заказ на быструю продажу
        if ($act == 'quick-sale-order') {
            $data = $this->all_configs['chains']->quick_sold_items($_POST, $mod_id);
        }

        // создать заказ из интернет магазина
        if ($act == 'eshop-sale-order') {
            $data = $this->all_configs['chains']->eshop_sold_items($_POST, $mod_id);
        }
        if ($act == 'change-status' && is_numeric($_POST['order_id'])) {
            $order = $this->Orders->getByPk($_POST['order_id']);
            if (!empty($order) && $this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                $data = $this->changeStatus($order, array('state' => true), l('Статус не изменился'));
            } else {
                $data['msg'] = l('У вас нет прав на изменение статуса заказа');
            }

        }

        preg_match('/changes:(.+)/', $act, $arr);
        // история изменений инженера
        if (count($arr) == 2 && isset($arr[1])) {
            $data = $this->getChanges($act, $_POST, $mod_id);
        }

        // история перемещений заказа
        if ($act == 'stock_moves-order') {
            $data['state'] = true;
            $data['content'] = $this->all_configs['chains']->stock_moves(isset($_POST['object_id']) ? $_POST['object_id'] : 0);
        }

        // удаление комментария
        if ($act == 'remove-comment') {
            if (isset($_POST['comment_id'])) {
                $this->all_configs['db']->query('DELETE FROM {orders_comments} WHERE id=?i',
                    array($_POST['comment_id']));
                $data['state'] = true;
            }
        }

        // создание клента
        if ($act == 'create-client') {
            $data['state'] = true;
            $data['content'] = $this->view->renderFile('orders/create_client_form', array());
            $data['btns'] = '<input class="btn btn-success" onclick="create_client(this)" type="button" value="' . l('Создать') . '" />';
        }

        // добавление нового клиента
        if ($act == 'add_user') {
            if (!$this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                Response::json(array('message' => l('У Вас недостаточно прав'), 'error' => true));
            }

            require_once($this->all_configs['sitepath'] . 'shop/access.class.php');
            $access = new access($this->all_configs, false);
            $data = $access->registration($_POST);
            if ($data['id'] > 0) {
                $fio = isset($_POST['fio']) ? h($_POST['fio']) : '';
                $email = isset($_POST['email']) ? h($_POST['email']) : '';
                $phone = isset($_POST['phone']) ? h($_POST['phone']) : '';
                $data['name'] = $fio . ', ' . $email . ', ' . $phone;
            }
        }

        // важная информация при добавлении устройства в новый заказ на ремонт
        if ($act == 'service-information') {
            $data['state'] = true;
            $data['title'] = l('Важная информация по') . '  ';
            $data['content'] = '';

            if (isset($_POST['category_id'])) {
                // достаем категорию
                $category = $this->all_configs['db']->query('SELECT * FROM {categories} WHERE id=?i',
                    array(intval($_POST['category_id'])))->row();
                if ($category && $category['information'] && mb_strlen(trim($category['information']), 'utf-8') > 0) {
                    $data['content'] = nl2br(h($category['information']));
                    $data['title'] .= $category['title'] . InfoPopover::getInstance()->createQuestion('l_category_information');
                }
            }
            if (isset($_POST['goods_id'])) {
                $goods = $this->all_configs['db']->query('SELECT title, price,  price_wholesale FROM {goods} WHERE id=?i',
                    array(intval($_POST['goods_id'])))->row();
                if (!empty($goods)) {
                    $data['title'] = h(trim($goods['title']));
                    $data['price'] = $goods['price'] / 100;
                    $data['price_wholesale'] = $goods['price_wholesale'] / 100;
                }
            }
        }
        if ($act == 'category-information') {
            $data['state'] = true;
            $data['title'] = l('Важная информация');
            $data['content'] = l('Информация отсутствует');

            if (isset($_POST['category_id'])) {
                // достаем категорию
                $category = $this->all_configs['db']->query('SELECT * FROM {categories} WHERE id=?i',
                    array(intval($_POST['category_id'])))->row();
                if ($category && $category['information'] && mb_strlen(trim($category['information']), 'utf-8') > 0) {
                    $data['content'] = h(trim($category['information']));
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
                && $_POST['status'] == $this->all_configs['configs']['order-status-work']
            ) {

                $order = $this->all_configs['db']->query('SELECT payment, status FROM {orders} WHERE id=?i',
                    array($_POST['order_id']))->row();

                $order['payment'] = array_key_exists('payment', $_POST) ? $_POST['payment'] : $order['payment'];

                if ($order && array_key_exists($order['payment'], $this->all_configs['configs']['payment-msg'])
                    && $this->all_configs['configs']['payment-msg'][$order['payment']]['pay'] == 'pre'
                ) {
                    $data = array('status' => $order['status'], 'confirm' => true);
                }
            }
        }

        // добавляем форму заказа поставщику
        if ($act == 'add-supplier-form') {
            $data['state'] = true;
            $counter = isset($_POST['counter']) ? intval($_POST['counter']) : 0;
            $id = isset($_POST['id']) ? $_POST['id'] : null;
            $data['html'] = $this->all_configs['suppliers_orders']->create_order_block(null, $id, false, $counter);
        }

        if ($act == 'supplier-order-form') {
            $data['state'] = true;
            $counter = 0;
            $id = isset($_POST['id']) ? $_POST['id'] : null;
            $data['html'] = $this->all_configs['suppliers_orders']->create_order_block(null, $id, true, $counter, true);
        }
        // открываем форму привязки запчасти к ремонту array(product_id=29)
        if ($act == 'bind-group-product-to-order') {
            $data['state'] = true;
            $order_id = (int)$_POST['order_id'];
            if ($this->OrdersGoods->isHash($_POST['product_id'])) {

                $products = $this->all_configs['manageModel']->order_goods($order_id, 0);
                $ids = $this->OrdersGoods->getProductsIdsByHash($products, $_POST['product_id']);

                $product = new MGoods();
                $product = $product->getByPk($products[$ids[0]]['goods_id']);

                $warehouses = new MWarehouses();
                $warehouses_data = $warehouses->getAvailableItemsByGoodsId(array($product['id']), true);

                foreach ($warehouses_data as $id_warehouse => $row) {
                    $warehouses_data[$id_warehouse]['warehouse'] = $warehouses->getByPk($id_warehouse);
                    $warehouses_data[$id_warehouse]['warehouse']['locations'] = $warehouses->getLocations($id_warehouse);

                    foreach ($warehouses_data[$id_warehouse]['items'] as $row_id => $row_product) {
                        $warehouses_data[$id_warehouse]['items'][$row_id]['item_id'] = $row_product['id'];
                        $warehouses_data[$id_warehouse]['items'][$row_id]['serial'] = suppliers_order_generate_serial($warehouses_data[$id_warehouse]['items'][$row_id]);
                    }
                }

//                dd($warehouses_data);

                $data['title'] = l('Отгрузка товара со склада под заказ клиента №') . ' ' . $order_id .
                    '<br/>' . $product['title'] . ' ' . l('в количестве') . ' ' . count($ids) . ' ' . l('шт.');

                $data['html'] = $this->view->renderFile('orders/bind_goods_to_order', array(
                    'order_id' => $order_id,
                    'product' => $product,
                    'products_count' => count($ids),
                    'warehouses_data' => $warehouses_data,
                ));


//                $data['html'] = '<legend>'.l('Отгрузка товара со склада под заказ клиента №').' '.$order_id .
//                    '<br/>'.$product['title'].' '.l('в количестве').' '.count($ids).' '.l('шт.').'</legend><table class="">';
//                foreach ($ids as $position => $id) {
//                    $product_id = $products[$id]['goods_id'];
//                    $data_ops = $this->all_configs['chains']->stockman_operations_goods($product_id);
//                    $operations = $this->all_configs['chains']->get_operations(1, null, false, $data_ops['goods']);
//                    $ops = $this->all_configs['chains']->show_stockman_operation($operations[$position], 1,
//                        $data_ops['serials'],
//                        true, true);
//                    $data['html'] .= $ops;
//                }
//                $data['html'] .= '</table>';
            } else {
                $data['stat'] = false;
                $data['message'] = l('Группа не найдена');
            }
        }

        // открываем форму привязки запчасти к ремонту array(product_id=29)
        if ($act == 'bind-product-to-order') {
            $data['state'] = true;
            $product_id = $_POST['product_id'];
            $data_ops = $this->all_configs['chains']->stockman_operations_goods($product_id);
            $operations = $this->all_configs['chains']->get_operations(1, null, false, $data_ops['goods']);
            $ops = $this->all_configs['chains']->show_stockman_operation($operations[0], 1, $data_ops['serials'], true);
            $data['html'] = '
                <table class="table">
                    ' . $ops . '
                </table>
            ';
        }

        if ($act == 'client-bind') {

            if (!$this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                Response::json(array('message' => l('У Вас недостаточно прав'), 'error' => true));
            }
            if (!isset($_POST['user_id']) || $_POST['user_id'] < 1 || !isset($_POST['order_id']) || $_POST['order_id'] < 1) {
                Response::json(array('message' => l('Такого клиента не существует'), 'error' => true));
            }

            $u = $this->all_configs['db']->query('SELECT email, id FROM {clients}
                WHERE id=?i', array($_POST['user_id']))->row();

            $o = $this->all_configs['db']->query('SELECT email, user_id, id FROM {orders}
                WHERE id=?i', array($_POST['order_id']))->row();

            if (!$u || !$o || $u['email'] != $o['email']) {
                Response::json(array('message' => l('Такого клиента не существует'), 'error' => true));
            }
            $this->all_configs['db']->query('UPDATE {orders} SET user_id=?i WHERE id=?i',
                array($_POST['user_id'], $_POST['order_id']));
            $data['message'] = l('Заказ успешно привязан');
        }


        // удаление заказа поставщика
        if ($act == 'remove-supplier-order') {
            $data = $this->all_configs['suppliers_orders']->remove_order($mod_id);
        }

        // принятие заказа
        if ($act == 'accept-supplier-order') {
            $data = $this->all_configs['suppliers_orders']->accept_order($mod_id, $this->all_configs['chains']);
//            $data['state'] = true;
        }

        // запрос на отвязку серийного номера
        if ($act == 'unbind-request-item-serial') {
            $data = $this->all_configs['chains']->unbind_request($mod_id, $_POST);
            if ($data['state']) {
                $data['unbind'] = $this->get_unbind_order_product_btn((int)$_POST['item_id']);
            }
        }

        // статус заказа поставщику
        if ($act == 'avail-supplier-order') {
            $data = $this->all_configs['suppliers_orders']->avail_order($_POST);
        }

        // добавляем новый товар к заказу выводя его в таблицу
        if ($act == 'add_product') {
            $data = $this->all_configs['chains']->add_product_order($_POST, $mod_id, $this);
        }
        if ($act == 'change-price-type') {
            $data = $this->changePriceType($_POST, $mod_id, GOODS_TYPE_ITEM);
        }
        if ($act == 'change-price-type-service') {
            $data = $this->changePriceType($_POST, $mod_id, GOODS_TYPE_SERVICE);
        }
        if ($act == 'remove_product') {
            $_POST['remove'] = 1;
            $data = $this->all_configs['chains']->remove_product_order($_POST, $mod_id);
        }
        if ($act == 'issued-order') {
            $order = $this->Orders->getByPk($_POST['order_id']);
            $_POST['status'] = $this->all_configs['configs']['order-status-issued'];
            $data = array(
                'state' => true
            );
            if (empty($order)) {
                $data = array(
                    'state' => false,
                    'msg' => l('Заказ не найден')
                );
            } elseif ($order['status'] != $_POST['status']) {
                $data = $this->changeStatus($order, array('state' => true), l('Статус не изменился'));
            }
        }
        if ($act == 'add-users-field') {
            $data = array(
                'state' => false,
                'msg' => l('Проблемы при добавлении пользовательского поля в заказ')
            );
            if (!empty($_POST['name'])) {
                $data = $this->addUsersField($_POST, $data);
            }
        }
        if ($act == 'set-engineer-of-service') {
            $data = $this->setEngineerOfService($_POST, $mod_id);
        }

        Response::json($data);
    }

    /**
     * @inheritdoc
     */
    public static function get_submenu($oRole = null)
    {
        global $all_configs;
        $submenu = array(
            array(
                'click_tab' => true,
                'url' => '#show_orders',
                'name' => l('customer_orders')//'Заказы клиентов'
            ),
        );
        if ($all_configs['oRole']->hasPrivilege('create-clients-orders')) {
            $submenu[1] = array(
                'click_tab' => true,
                'url' => '#create_order',
                'name' => l('create_order')//'Создать заказ'
            );
        }
        if ($all_configs['oRole']->hasPrivilege('edit-suppliers-orders')
            || $all_configs['oRole']->hasPrivilege('debit-suppliers-orders')
            || $all_configs['oRole']->hasPrivilege('return-items-suppliers')
        ) {
            $submenu[2] = array(
                'click_tab' => true,
                'url' => '#show_suppliers_orders',
                'name' => l('suppliers_orders')//'Заказы поставщику'
            );
        }
        if ($all_configs['oRole']->hasPrivilege('edit-suppliers-orders')
        ) {
            $submenu[3] = array(
                'click_tab' => true,
                'url' => '#create_supplier_order',
                'name' => l('create_supplier_order')//'Создать заказ поставщику'
            );
        }
        if ($all_configs['oRole']->hasPrivilege('orders-manager')) {
            $submenu[4] = array(
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
            'current' => empty($current) ? array() : json_decode($current[0]['value'], true),
        ));
        $data['title'] = '<center>' . l('Укажите стандарты обслуживания для вашей компании') . ' '
            . InfoPopover::getInstance()->createQuestion('l_manager_setup_info') . '</center>';

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
                        l('Настройки менеджера заказов'),
                        l('Настройки менеджера заказов'),
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
            'addition-info' => 'on',
            'accountable' => 'on'
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
     * @param $config
     */
    private function order_fields_setup($config)
    {
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
            . ' JOIN {tags} t ON t.id = c.tag_id'
            . ' WHERE c.id = ?i', array($client_id))->row();
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getOrderSuppliersClientsDateAdd($product)
    {
        return $this->all_configs['db']->query(
            "SELECT date_add FROM {orders_suppliers_clients} "
            . "WHERE client_order_id = ?i AND supplier_order_id = ?i "
            . "AND goods_id = ?i AND order_goods_id = ?i", array(
            $product['order_id'],
            $product['so_id'],
            $product['goods_id'],
            $product['id']
        ), 'el');
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function updateComments($data)
    {
        $data['state'] = true;
        $data['reload'] = true;
        $order_id = isset($this->all_configs['arrequest'][2]) ? $this->all_configs['arrequest'][2] : null;

        // достаем заказ
        $order = $_order = $this->all_configs['db']->query('SELECT * FROM {orders} WHERE id=?',
            array($order_id))->row();
        if ((!empty($_POST['private_comment']) || !empty($_POST['public_comment']))) {
            $data = $this->updateOrderComments($order, $data);
        }
        return $data;
    }

    /**
     * @param $data
     * @param $user_id
     * @param $mod_id
     * @return array
     * @throws Exception
     */
    protected function updateOrder($data, $user_id, $mod_id)
    {
        $data['state'] = true;
        $data['reload'] = false;
        $order_id = isset($this->all_configs['arrequest'][2]) ? $this->all_configs['arrequest'][2] : null;

        // достаем заказ
        $order = $_order = $this->all_configs['db']->query('SELECT * FROM {orders} WHERE id=?',
            array($order_id))->row();

        try {
            if (!$order) {
                throw new ExceptionWithMsg(l('Заказ не найден'));
            }
            if (isset($_POST['is_replacement_fund']) && isset($_POST['replacement_fund']) && mb_strlen(trim($_POST['replacement_fund']),
                    'utf-8') == 0
            ) {
                throw new ExceptionWithMsg(l('Укажите подменный фонд'));
            }
            if (isset($_POST['categories-goods']) && intval($_POST['categories-goods']) == 0) {
                throw new ExceptionWithMsg(l('Укажите устройство'));
            }
            // принимаем заказ
            if (!empty($_POST['accept-manager'])) {
                $order['manager'] = $user_id;
                $this->History->save('manager-accepted-order', $mod_id, $order_id);
            }
            if ($order['status'] != $this->all_configs['configs']['order-status-issued'] && $_POST['status'] == $this->all_configs['configs']['order-status-issued'] && $order['sum'] > ($order['sum_paid'] + $order['discount'])) {
                $data['paid'] = true;
            }
            $oldStatus = $order['status'];
            $data = $this->changeStatus($order, $data);
            // устройство у клиента
            if ((isset($_POST['client_took']) && $order['client_took'] != 1) || (!isset($_POST['client_took']) && $order['client_took'] == 1)) {
                $this->History->save('update-order-client_took', $mod_id, $this->all_configs['arrequest'][2],
                    isset($_POST['client_took']) ? l('Устройство у клиента') : l('Устройство на складе'),
                    isset($_POST['client_took']) ? 1 : 0);
            }
            $order = $this->replacementFund($order, $mod_id);
            $order = $this->changeManager($order, $mod_id);
            $order = $this->changeEngineer($order, $mod_id);
            $order = $this->changeDefect($order, $mod_id);
            $order = $this->changeComment($order, $mod_id);
            $order = $this->changeSerial($order, $mod_id);
            $order = $this->changeFio($order, $mod_id);
            $order = $this->changePhone($order, $mod_id);
            $order = $this->changeWarranty($order, $mod_id);
            $order = $this->changeDevice($order, $mod_id);
            $order = $this->changeReturnId($order, $mod_id);
            $order = $this->changeRepairType($order, $mod_id);
            // комментарии к заказам
            if ((!empty($_POST['private_comment']) || !empty($_POST['public_comment']))) {
                $data = $this->updateOrderComments($order, $data);
            }

            unset($order['return_id']);
            if (isset($_POST['color']) && array_key_exists($_POST['color'],
                    $this->all_configs['configs']['devices-colors'])
            ) {
                $order['color'] = $_POST['color'];
            } else {
                unset($order['color']);
            }
            $order = $this->changeReplacement($order, $mod_id);
            $order = $this->changeClientTook($order, $mod_id);
            $order = $this->changeEngineerComment($order, $mod_id);

            $order['notify'] = isset($_POST['notify']) ? 1 : 0;
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
            unset($order['wh_id']);
            unset($order['location_id']);
            unset($order['status_id']);

            $order = $this->changeCode($order, $mod_id);
            $order = $this->changeReferer($order, $mod_id);
            $order = $this->changeDeliveryBy($order, $mod_id);
            $order = $this->changeDeliveryTo($order, $mod_id);
            $order = $this->changeProducts($order, $mod_id);
            $order = $this->changeCart($order, $mod_id);
            $order = $this->changeUsersFields($order, $mod_id);
            if ($order['total_as_sum']) {
                $order['sum'] = $this->Orders->getTotalSum($order);
            } else {
                $order['sum'] = isset($_POST['sum']) ? $_POST['sum'] * 100 : $order['sum'];
            }
            unset($order['id']);
            // обновляем заказ
            $ar = $this->all_configs['db']->query('UPDATE {orders} SET ?s WHERE id=?i',
                array($order, $this->all_configs['arrequest'][2]), 'ar');
            // история
            if ($ar) {
                // сумма
                if ($_order['sum'] != $order['sum']) {
                    $this->History->save(
                        'update-order-sum',
                        $mod_id,
                        $this->all_configs['arrequest'][2],
                        ($order['sum'] / 100)
                    );
                }
                $this->History->save('update-order', $mod_id, $this->all_configs['arrequest'][2]);

                $get = '?' . get_to_string($_GET);
                $data['location'] = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . $get . '#show_orders';
                $data['reload'] = true;
            }
            if ($oldStatus != $_POST['status'] && $order['type'] != ORDER_SELL && $_POST['status'] == $this->all_configs['configs']['order-status-ready']) {
                $data['sms'] = true;
            }
        } catch (ExceptionWithMsg $e) {
            $data = array(
                'state' => false,
                'msg' => $e->getMessage()
            );
        }

        return $data;
    }

    /**
     * @param $user_id
     * @param $mod_id
     * @return array
     */
    protected function setTotalAsSum($user_id, $mod_id)
    {
        $order = $this->all_configs['db']->query('SELECT * FROM {orders} WHERE id=?',
            array($_POST['id']))->row();
        $data = array('state' => false);
        if (!empty($order)) {
            $set = $_POST['total_set'] == 'true';
            $sum = $set ? $this->Orders->getTotalSum($order) : $order['sum'];
            $ar = $this->all_configs['db']->query('UPDATE {orders} SET total_as_sum=?i, `sum`=?i  WHERE id=?i',
                array((int)$set, $sum, $_POST['id']))->ar();
            if ($sum != $order['sum']) {
                $this->History->save(
                    'update-order-sum',
                    $mod_id,
                    $_POST['id'],
                    ($sum / 100)
                );
            }
            $data = array(
                'state' => $ar > 0,
                'set' => $set
            );
        }
        return $data;
    }

    /**
     * @param $data
     * @param $user_id
     * @param $mod_id
     * @return array
     */
    protected function changeVisiblePrices($data, $user_id, $mod_id)
    {
        $data['msg'] = l('Укажите новую цену');
        if (!empty($_POST['id']) && !empty($_POST['price']) && is_numeric($_POST['price'])) {
            $this->all_configs['db']->query('UPDATE {orders_goods} SET price=?, price_type=?i WHERE id=?i',
                array($_POST['price'] * 100, ORDERS_GOODS_PRICE_TYPE_MANUAL, $_POST['id']));
            $data['state'] = true;

            $order = $this->all_configs['db']->query('SELECT o.* FROM {orders} o, {orders_goods} og WHERE og.order_id=o.id AND og.id=?',
                array($_POST['id']))->row();
            if ($order['total_as_sum']) {
                $sum = $this->Orders->getTotalSum($order);
                if ($sum != $order['sum']) {
                    $this->all_configs['db']->query('UPDATE {orders} SET `sum`=?i  WHERE id=?i',
                        array($sum, $order['id']))->ar();
                    $this->History->save(
                        'update-order-sum',
                        $mod_id,
                        $order['id'],
                        ($sum / 100)
                    );
                }
            }
            $data['msg'] = l('Цена изменилась');
        }
        return $data;
    }

    /**
     * @param $data
     * @return mixed
     */
    protected function orderStatuses($data)
    {
        $data['state'] = true;
        $data['content'] = l('История изменения статусов не найдена');
        $order = $this->Orders->getByPk($_POST['object_id']);
        $statuses = $this->all_configs['db']->query('SELECT s.status, s.date, u.* FROM {order_status} as s
                LEFT JOIN {users} as u ON u.id=s.user_id WHERE s.order_id=?i ORDER BY `date` DESC',
            array(isset($_POST['object_id']) ? $_POST['object_id'] : 0))->assoc();
        if ($statuses) {
            $sts = $this->Status->getAll(!empty($order) ? $order['type'] : ORDER_REPAIR, 'status_id');
            $data['content'] = $this->view->renderFile('orders/order_statuses', array(
                'statuses' => $statuses,
                'sts' => $sts
            ));
        }
        return $data;
    }

    /**
     * @param $order
     * @param $data
     * @return mixed
     */
    protected function updateOrderComments($order, $data)
    {
        if ($this->all_configs['oRole']->hasPrivilege('add-comment-to-clients-orders')) {
            $private = !empty($_POST['private_comment']) ? trim($_POST['private_comment']) : '';
            $public = !empty($_POST['public_comment']) ? trim($_POST['public_comment']) : '';
            $type = $private ? 1 : 0;
            $text = $private ?: $public;
            $this->all_configs['suppliers_orders']->add_client_order_comment($order['id'], $text, $type);
//            $data['reload'] = true;
        }
        return $data;
    }

    /**
     * @param        $order
     * @param        $data
     * @param string $defaultMessage
     * @return mixed
     */
    protected function changeStatus($order, $data, $defaultMessage = '')
    {
// меняем статус
        if ($_POST['status'] == $order['status']) {
            return $data;
        }
        if ($_POST['status'] == $this->all_configs['configs']['order-status-issued']) {
            if ($order['status'] == $this->all_configs['configs']['order-status-refused']) {
                $_POST['status'] = $this->all_configs['configs']['order-status-nowork'];
            }
            if ($order['status'] == $this->all_configs['configs']['order-status-unrepairable']) {
                $_POST['status'] = $this->all_configs['configs']['order-status-nowork'];
            }
        }
        $response = update_order_status($order, $_POST['status']);
        if (!isset($response['state']) || $response['state'] == false) {
            $data['state'] = false;
            $_POST['status'] = $order['status'];
            $data['msg'] = isset($response['msg']) && !empty($response['msg']) ? $response['msg'] : $defaultMessage;
        } else {
            $value = '';
            if (!empty($this->all_configs['configs']['sale-order-status'][$_POST['status']])) {
                $value = $this->all_configs['configs']['sale-order-status'][$_POST['status']]['name'];
            }
            if (!empty($this->all_configs['configs']['order-status'][$_POST['status']])) {
                $value = $this->all_configs['configs']['order-status'][$_POST['status']]['name'];
            }
            $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'status', $value);
            if (!empty($response['msg'])) {
                FlashMessage::set($response['msg'], FlashMessage::WARNING);
            }
        }
        return $data;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    protected function replacementFund($order, $mod_id)
    {
// подменный фонд
        if ((isset($_POST['is_replacement_fund']) && isset($_POST['replacement_fund']) && $_POST['replacement_fund'] != $order['replacement_fund'])
            || (!isset($_POST['is_replacement_fund']) && $order['is_replacement_fund'] == 1)
        ) {
            $change_id = isset($_POST['is_replacement_fund']) ? 1 : 0;
            $change = $change_id == 1 ? $_POST['replacement_fund'] : '';
            $this->History->save('update-order-replacement_fund', $mod_id, $this->all_configs['arrequest'][2],
                $change, $change_id);
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return array
     */
    protected function changeManager($order, $mod_id)
    {
        if (isset($_POST['manager']) && intval($order['manager']) != intval($_POST['manager'])) {
            $user = $this->all_configs['db']->query('SELECT fio, email, login, phone, send_over_sms, send_over_email FROM {users} WHERE id=?i AND avail=1 AND deleted=0',
                array(intval($_POST['manager'])))->row();
            if (empty($user)) {
                FlashMessage::set(l('Менеджер не активен или удален'), FlashMessage::DANGER);
            } else {
                $this->History->save('update-order-manager', $mod_id, $this->all_configs['arrequest'][2],
                    get_user_name($user), $_POST['manager']);
                $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'manager', $user['fio']);
                $order['manager'] = intval($_POST['manager']);
                $this->all_configs['chains']->noticeManager($user, $order);
            }
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    protected function changeEngineer($order, $mod_id)
    {
// смена инженера
        if (isset($_POST['engineer']) && intval($order['engineer']) != intval($_POST['engineer'])) {
            $user = $this->all_configs['db']->query('SELECT fio, email, login, phone, send_over_sms, send_over_email  FROM {users} WHERE id=?i AND deleted=0 AND avail=1',
                array($_POST['engineer']))->row();
            if (empty($user)) {
                FlashMessage::set(l('Менеджер не активен или удален'), FlashMessage::DANGER);
            } else {
                $this->History->save(
                    'update-order-engineer',
                    $mod_id,
                    $this->all_configs['arrequest'][2],
                    get_user_name($user),
                    $_POST['engineer']
                );
                $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'engineer', $user['fio']);
                $host = 'https://' . $_SERVER['HTTP_HOST'] . $this->all_configs['prefix'];
                $this->all_configs['chains']->noticeEngineer($user, $order);

            }
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    protected function changeFio($order, $mod_id)
    {
// смена фио
        if (isset($_POST['fio']) && trim($order['fio']) != trim($_POST['fio'])) {
            $this->History->save(
                'update-order-fio',
                $mod_id,
                $this->all_configs['arrequest'][2],
                trim($_POST['fio'])
            );
            $order['fio'] = trim($_POST['fio']);
            // апдейтим также клиенту фио
            $this->all_configs['db']->query("UPDATE {clients} SET fio = ? WHERE id = ?i",
                array(trim($_POST['fio']), $order['user_id']));
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    protected function changePhone($order, $mod_id)
    {
// смена телефона
        if (isset($_POST['phone'])) {
            include_once $this->all_configs['sitepath'] . 'shop/access.class.php';
            $access = new access($this->all_configs, false);
            $phone = $access->is_phone($_POST['phone']);
            $phone = $phone ? current($phone) : '';

            if ($order['phone'] != $phone) {
                $this->History->save(
                    'update-order-phone',
                    $mod_id,
                    $this->all_configs['arrequest'][2],
                    $phone
                );
                $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'client_phone', $phone);
                $order['phone'] = $phone;
            }
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    protected function changeWarranty($order, $mod_id)
    {
// смена телефона
        if (isset($_POST['warranty']) && intval($order['warranty']) != intval($_POST['warranty'])) {
            $this->History->save(
                'update-order-warranty',
                $mod_id,
                $this->all_configs['arrequest'][2],
                trim($_POST['warranty'])
            );
            $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'warranty', $_POST['warranty']);
            $order['warranty'] = intval($_POST['warranty']);
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    protected function changeDevice($order, $mod_id)
    {
// смена устройства
        if (isset($_POST['categories-goods']) && intval($order['category_id']) != intval($_POST['categories-goods'])) {
            $category = $this->all_configs['db']->query('SELECT title FROM {categories} WHERE id=?i',
                array(intval($_POST['categories-goods'])))->el();
            if ($category) {
                $order['title'] = $category;
                $order['category_id'] = intval($_POST['categories-goods']);
                $this->History->save(
                    'update-order-category',
                    $mod_id,
                    $this->all_configs['arrequest'][2],
                    $category,
                    intval($_POST['categories-goods'])
                );
                $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'device', $category);
            }
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    protected function changeDefect($order, $mod_id)
    {
// смена Неисправность со слов клиента
        if (isset($_POST['defect']) && trim($order['defect']) != trim($_POST['defect'])) {
            $this->History->save(
                'update-order-defect',
                $mod_id,
                $this->all_configs['arrequest'][2],
                trim($_POST['defect'])
            );
            $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'defect', trim($_POST['defect']));
            $order['defect'] = trim($_POST['defect']);
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    protected function changeSerial($order, $mod_id)
    {
// смена серийника
        if (isset($_POST['serial']) && trim($order['serial']) != trim($_POST['serial'])) {
            $this->History->save('update-order-serial',
                $mod_id,
                $this->all_configs['arrequest'][2],
                trim($_POST['serial'])
            );
            $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'serial', trim($_POST['serial']));
            $order['serial'] = trim($_POST['serial']);
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    protected function changeComment($order, $mod_id)
    {
// смена Примечание/Внешний вид
        if (isset($_POST['comment']) && trim($order['comment']) != trim($_POST['comment'])) {
            $this->History->save(
                'update-order-comment',
                $mod_id,
                $this->all_configs['arrequest'][2],
                trim($_POST['comment'])
            );
            $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'comment', trim($_POST['comment']));
            $order['comment'] = trim($_POST['comment']);
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    protected function changeReturnId($order, $mod_id)
    {
        if ($this->all_configs['oRole']->hasPrivilege('edit_return_id') && isset($_POST['return_id']) && $_POST['return_id'] != $order['return_id']) {
            $this->all_configs['db']->query('UPDATE {cashboxes_transactions} SET client_order_id=NULL WHERE id=?i',
                array($order['return_id']));
            if ($_POST['return_id'] > 0) {
                $this->all_configs['db']->query('UPDATE {orders} SET return_id=?n WHERE id=?i',
                    array(
                        mb_strlen($_POST['return_id'], 'UTF-8') > 0 ? trim($_POST['return_id']) : null,
                        $this->all_configs['arrequest'][2]
                    ));
                $this->History->save(
                    'update-order-return_id',
                    $mod_id,
                    $this->all_configs['arrequest'][2],
                    trim($_POST['return_id'])
                );

                $this->all_configs['db']->query('UPDATE {cashboxes_transactions} SET client_order_id=?n WHERE id=?i',
                    array($this->all_configs['arrequest'][2], $_POST['return_id']));
            }
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    protected function changeCode($order, $mod_id)
    {
// смена кода
        if (isset($_POST['code']) && $_POST['code'] != $order['code']) {
            $this->History->save('update-order-code',
                $mod_id,
                $this->all_configs['arrequest'][2],
                $order['code'] . ' ==> ' . trim($_POST['code'])
            );
            $order['code'] = $_POST['code'];
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     * @throws Exception
     */
    protected function changeReferer($order, $mod_id)
    {
// смена источника
        if (isset($_POST['referer_id']) && $_POST['referer_id'] != $order['referer_id']) {
            $referrers = get_service("crm/calls")->get_referers();
            $this->History->save(
                'update-order-referer_id',
                $mod_id,
                $this->all_configs['arrequest'][2],
                $referrers[$order['referer_id']] . ' ==> ' . $referrers[$_POST['referer_id']]
            );
            $order['referer_id'] = $_POST['referer_id'];
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     * @throws Exception
     */
    protected function changeDeliveryBy($order, $mod_id)
    {
        if (isset($_POST['delivery_by']) && $_POST['delivery_by'] != $order['delivery_by']) {
            $deliveryByList = $this->Orders->getDeliveryByList();
            $this->History->save(
                'update-order-delivery_by',
                $mod_id,
                $this->all_configs['arrequest'][2],
                $deliveryByList[$order['delivery_by']] . ' ==> ' . $deliveryByList[$_POST['delivery_by']]
            );
            $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'delivery_by', $_POST['delivery_by']);
            $order['delivery_by'] = $_POST['delivery_by'];
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     * @throws Exception
     */
    protected function changeDeliveryTo($order, $mod_id)
    {
        if (isset($_POST['delivery_to']) && $_POST['delivery_to'] != $order['delivery_to']) {
            $this->History->save(
                'update-order-delivery_to',
                $mod_id,
                $this->all_configs['arrequest'][2],
                $order['delivery_to'] . ' ==> ' . $_POST['delivery_to']
            );
            $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'delivery_to', $_POST['delivery_to']);
            $order['delivery_to'] = $_POST['delivery_to'];
        }
        return $order;
    }


    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     * @throws Exception
     */
    protected function changeProducts($order, $mod_id)
    {
        $testSort = function ($a, $b) {
            if ($this->OrdersGoods->isHash($a) && !$this->OrdersGoods->isHash($b)) {
                return -1;
            }

            return ($a == $b) ? 0 : 1;
        };
        $orderId = $this->all_configs['arrequest'][2];
        if (isset($_POST['product'])) {
            $keys = array_keys($_POST['product']);
            usort($keys, $testSort);
            $products = $this->all_configs['manageModel']->order_goods($orderId, 0);
            foreach ($keys as $key) {
                if ($this->OrdersGoods->isHash($key)) {
                    $ids = $this->OrdersGoods->getProductsIdsByHash($products, $key);
                } else {
                    $ids = array($key);
                }
                foreach ($ids as $id) {
                    if (!isset($products[$id])) {
                        continue;
                    }
                    $product = $products[$id];
                    foreach ($_POST['product'][$key] as $field => $value) {
                        if ($field == 'price') {
                            $value = $value * 100;
                        }
                        if ($product[$field] != $value && !empty($value)) {
                            $this->OrdersGoods->update(array(
                                $field => $value
                            ), array($this->OrdersGoods->pk() => $id));
                            $this->History->save(
                                'update-order-cart',
                                $mod_id,
                                $this->all_configs['arrequest'][2],
                                l($field) . ':' . $product[$field] . ' ==> ' . $value
                            );
                        }
                    }
                }
            }
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    protected function changeCart($order, $mod_id)
    {
        if (!empty($_POST['new-goods'])) {
            $post = array(
                array(
                    'id' => $_POST['new-goods'],
                    'discount' => !empty($_POST['discount']) ? $_POST['discount'] : 0,
                    'discount_type' => !empty($_POST['discount_type']) ? $_POST['discount_type'] : DISCOUNT_TYPE_PERCENT,
                    'price' => !empty($_POST['price']) ? $_POST['price'] : 0,
                    'quantity' => !empty($_POST['quantity']) ? $_POST['quantity'] : 1,
                    'warranty' => isset($this->all_configs['settings']['default_order_warranty']) ? $this->all_configs['settings']['default_order_warranty'] : 0,
                )
            );
            $this->all_configs['chains']->addProducts($post, $order['id'], $mod_id);
        }
        return $order;
    }

    /**
     * @param $data
     * @return array
     */
    private function webcamUpload($data)
    {
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
        return $data;
    }

    /**
     * @param $post
     * @param $data
     * @return mixed
     */
    private function addUsersField($post, $data)
    {
        $title = trim($post['name']);
        if (empty($title)) {
            return array(
                'state' => false,
                'msg' => l('Название поля не может быть пустым')
            );
        }
        $name = transliturl($title);
        if (db()->query('SELECT count(*) FROM {users_fields} WHERE name=?', array($name))->el() == 0) {
            $ar = db()->query('INSERT INTO {users_fields} (name, title) VALUES (?, ?)',
                array($name, $title))->ar();
            if ($ar) {
                $config = $this->all_configs['db']->query("SELECT value FROM {settings} WHERE name = 'order-fields-hide'")->el();
                if (!empty($config)) {
                    $configAsArray = json_decode($config, true);
                    $configAsArray[$name] = 'on';
                    $this->order_fields_setup($configAsArray);
                }
                $data = array(
                    'state' => true,
                    'name' => $name,
                    'title' => $title
                );
            }
        }
        return $data;
    }

    /**
     * @return mixed
     */
    private function getUsersFields()
    {
        return db()->query('SELECT * FROM {users_fields} WHERE avail=1 AND deleted=0', array())->assoc();
    }

    /**
     * @param $orderId
     * @return mixed
     */
    private function getUsersFieldsValues($orderId)
    {
        return db()->query('
            SELECT ouf.*, uf.*, uf.id as uf_id, ouf.id as ouf_id 
            FROM {users_fields} uf 
            LEFT JOIN {orders_users_fields} ouf ON uf.id=ouf.users_field_id AND  ouf.order_id=? 
            WHERE uf.deleted=0',
            array($orderId))->assoc('name');
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    private function changeUsersFields($order, $mod_id)
    {
        if (!empty($_POST['users_fields'])) {
            $usersFieldsValues = $this->getUsersFieldsValues($order['id']);
            foreach ($_POST['users_fields'] as $name => $value) {
                if (isset($usersFieldsValues[$name])) {
                    if (empty($usersFieldsValues[$name]['value'])) {
                        db()->query('INSERT INTO {orders_users_fields} (order_id, users_field_id, value) VALUES (?i, ?i, ?)',
                            array($order['id'], $usersFieldsValues[$name]['uf_id'], $value));

                        $this->History->save(
                            'update-order-' . $name,
                            $mod_id,
                            $order['id'],
                            $usersFieldsValues[$name]['value']
                        );
                    } elseif ($usersFieldsValues[$name]['value'] != $value) {
                        db()->query('UPDATE {orders_users_fields} SET value=? WHERE id=?i',
                            array($value, $usersFieldsValues[$name]['ouf_id']));
                        $this->History->save(
                            'update-order-' . $name,
                            $mod_id,
                            $order['id'],
                            $usersFieldsValues[$name]['value']
                        );
                    }
                    if ($usersFieldsValues[$name]['value'] != $value) {
                        $this->OrdersComments->addPublic($order['id'], $this->getUserId(),
                            $usersFieldsValues[$name]['title'],
                            $value);
                    }
                }
            }
        }

        return $order;
    }

    /**
     * @param $usersFields
     * @param $hide
     * @return bool
     */
    private function checkShowUsersFields($usersFields, $hide)
    {
        $result = false;
        if (!empty($usersFields) && !empty($hide)) {
            foreach ($usersFields as $field) {
                if (isset($hide[$field['name']])) {
                    $result = true;
                }
            }
        }
        return $result;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    private function changeReplacement($order, $mod_id)
    {
        $order['is_replacement_fund'] = isset($_POST['is_replacement_fund']) ? 1 : 0;
        if (isset($_POST['replacement_fund']) && $_POST['replacement_fund'] != $order['replacement_fund']) {
            $fund = $order['is_replacement_fund'] == 1 ? (isset($_POST['replacement_fund']) ? $_POST['replacement_fund'] : $order['replacement_fund']) : '';
            $this->History->save(
                'update-order-replacement_fund',
                $mod_id,
                $order['id'],
                $_POST['replacement_fund']
            );
            $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'replacement_fund',
                $_POST['replacement_fund']);
            $order['replacement_fund'] = $fund;
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    private function changeClientTook($order, $mod_id)
    {
        $took = isset($_POST['client_took']) ? intval($_POST['client_took']) : 0;
        if ($took != $order['client_took']) {
            $this->History->save(
                'update-order-client_took',
                $mod_id,
                $order['id'],
                $took
            );
            $order['client_took'] = $took;
            $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'client_took', $order['client_took']);
        }
        return $order;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     */
    private function changeEngineerComment($order, $mod_id)
    {
        $took = isset($_POST['engineer_comment']) ? trim($_POST['engineer_comment']) : '';
        if ($took !== $order['engineer_comment']) {
            $this->History->save(
                'update-order-engineer_comment',
                $mod_id,
                $order['id'],
                $took
            );
            $order['engineer_comment'] = $took;
            $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'engineer_comment',
                $order['engineer_comment']);
        }
        return $order;
    }

    /**
     *
     */
    private function exportOrders()
    {
        $session = Session::getInstance();
        $currentOrderType = $session->check('current_order_show') ? $session->get('current_order_show') : ORDER_REPAIR;
        $user_id = $this->getUserId();
        $filters = array('type' => $currentOrderType);
        if ($this->all_configs['oRole']->hasPrivilege('partner') && !$this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $filters['acp'] = $user_id;
        }
        $saved = $this->LockFilters->load('repair-orders');
        if (count($_GET) <= 2 && !empty($saved)) {
            $_GET += $saved;
        }

        if (isset($_GET['simple'])) {
            $search = $_GET['simple'];
            unset($_GET['simple']);
            list($query, $orders) = $this->simpleSearch($search, $filters + $_GET);
        } else {
            $queries = $this->all_configs['manageModel']->clients_orders_query($filters + $_GET);
            $query = $queries['query'];
            // достаем заказы
            $orders = $this->getOrders($query);
        }

        require_once __DIR__ . '/exports.php';
        $export = new ExportOrdersToXLS();
        $xls = $export->getXLS(l('Заказы'));

        $columns = $this->LockFilters->load('repair-order-table-columns');
        // Экспорт работ обязательный
        if (!isset($columns['services'])) {
            $columns['services'] = 'on';
        }

        if (in_array($currentOrderType, array(ORDER_REPAIR, ORDER_WRITE_OFF, ORDER_RETURN))) {
            $title = array();

            foreach (array(
                'npp' => 'N',
                'date' => 'Дата',
                'accepter' => 'Приемщик',
                'manager' => 'Менеджер',
                'engineer' => 'Инженер',
                'status' => 'Статус',
                'components' => 'Запчасти',
                'services' => 'Работы',
                'device' => 'Устройство',
                'amount' => 'Стоимость',
                'paid' => 'Оплачено',
                'client' => 'Клиент',
                'phone' => 'Контактный телефон',
                'terms' => 'Сроки',
                'location' => 'Склад',
                'sn' => 'Серийный номер',
                'repair' => 'Тип ремонта',
                'date_end' => 'Дата готовности',
                'warranty' => 'Гарантия',
                'adv_channel' => 'Рекламный канал'
            ) as $item => $name) {
                if (isset($columns[$item])) {
                    $title[] = lq($name);
                }
            }
            $export->makeXLSTitle($xls, lq('Отфильтрованные заказы'), $title);
        } else {
            $export->makeXLSTitle($xls, lq('Отфильтрованные заказы'), array(
                lq('N'),
                lq('Дата'),
                lq('Приемщик'),
                lq('Менеджер'),
                lq('Способ оплаты'),
                lq('Статус'),
                lq('Способ доставки'),
                lq('Товары'),
                lq('Стоимость'),
                lq('Оплачено'),
                lq('Клиент'),
                lq('Контактный телефон'),
                lq('Примечание'),
            ));
        }
        if (!empty($orders)) {
            $export->makeXLSBody($xls, array(
                'orders' => $orders,
                'type' => $currentOrderType,
                'columns' => $columns
            ));
        }
        $export->outputXLS($xls);

    }

    /**
     * @param $post
     * @param $mod_id
     * @param $type
     * @return array
     */
    private function changePriceType($post, $mod_id, $type)
    {
        $result = array(
            'state' => true
        );
        try {
            if (!isset($post['order_id'])) {
                throw new ExceptionWithMsg(l('Номер заказа не задан'));
            }
            $order = $this->Orders->getByPk(intval($post['order_id']));
            if (empty($order)) {
                throw new ExceptionWithMsg(l('Заказ не найден'));
            }
            $price_type = isset($post['price_type']) && in_array($post['price_type'], array(
                ORDERS_GOODS_PRICE_TYPE_RETAIL,
                ORDERS_GOODS_PRICE_TYPE_MANUAL,
                ORDERS_GOODS_PRICE_TYPE_WHOLESALE
            )) ? $post['price_type'] : ORDERS_GOODS_PRICE_TYPE_RETAIL;
            if ($type == GOODS_TYPE_SERVICE) {
                $query = $this->OrdersGoods->makeQuery('AND g.type=?i', array(GOODS_TYPE_SERVICE));
            } else {
                $query = $this->OrdersGoods->makeQuery('AND (g.type is null OR g.type=?i)', array(GOODS_TYPE_ITEM));
            }
            $goods = $this->OrdersGoods->query('
                SELECT og.id, g.price, g.price_wholesale 
                FROM {orders_goods} og 
                LEFT JOIN {goods} g ON g.id = og.goods_id
                WHERE og.order_id=?i ?query',
                array($order['id'], $query))->assoc();
            if (!empty($goods)) {
                foreach ($goods as $good) {
                    $this->OrdersGoods->update(array(
                        'price_type' => $price_type,
                        'price' => $price_type == ORDERS_GOODS_PRICE_TYPE_WHOLESALE ? $good['price_wholesale'] : $good['price']
                    ), array(
                        'id' => $good['id']
                    ));
                }
            }
            if ($order['total_as_sum']) {
                $sum = $this->Orders->getTotalSum($order);
                if ($sum != $order['sum']) {
                    $this->Orders->update(array(
                        '`sum`' => $sum
                    ), array(
                        'id' => $order['id']
                    ));
                    $this->History->save(
                        'update-order-sum',
                        $mod_id,
                        $order['id'],
                        ($sum / 100)
                    );
                }
            }
        } catch (ExceptionWithMsg $e) {
            $result = array(
                'state' => false,
                'msg' => $e->getMessage()
            );
        }
        return $result;
    }

    /**
     * @param $order
     * @return mixed|string
     */
    private function getCurrentRepairType($order)
    {
        switch ($order['repair']) {
            case 1:
                $result = l('Гарантия');
                $brand = $this->all_configs['db']->query('SELECT title FROM {brands} WHERE id=?i',
                    array($order['brand_id']))->el();
                if (!empty($brand)) {
                    $result .= ' ' . $brand;
                }
                break;
            case 2:
                $result = l('Доработка');
                break;
            default:
                $result = l('Платный');
        }
        return $result;
    }

    /**
     * @param $order
     * @param $mod_id
     * @return mixed
     * @throws Exception
     */
    protected function changeRepairType($order, $mod_id)
    {
        $message = '';
        $current = $this->getCurrentRepairType($order);
        if (isset($_POST['repair'])) {
            if ($_POST['repair'] == 'pay' && $order['repair'] != 0) {
                $order['repair'] = 0;
                $message = l('Платный');
            }
            if ($_POST['repair'] == 'rework' && $order['repair'] != 2) {
                $order['repair'] = 2;
                $message = l('Доработка');
            }
            if (is_numeric($_POST['repair'])) {
                $brand = $this->all_configs['db']->query('SELECT title FROM {brands} WHERE id=?i',
                    array($_POST['repair']))->el();
                if ($order['repair'] != 1) {
                    $order['repair'] = 1;
                    $order['brand_id'] = $_POST['repair'];
                    $message = l('Гарантия') . ' ' . $brand;
                } elseif ($order['brand_id'] != $_POST['repair']) {
                    $order['brand_id'] = $_POST['repair'];
                    $message = l('Гарантия') . ' ' . $brand;
                }
            }
            if (!empty($message)) {
                $this->History->save(
                    'update-order-repair-type',
                    $mod_id,
                    $this->all_configs['arrequest'][2],
                    $current . '==>' . $message
                );
                $this->OrdersComments->addPublic($order['id'], $this->getUserId(), 'repair_type', $message);
            }
        }
        return $order;
    }

    /**
     * @param array $post
     * @return array
     */
    private function filterOrders(array $post)
    {
        $url = array();

        // фильтр по дате
        if (isset($post['date']) && !empty($post['date'])) {
            list($df, $dt) = explode('-', $post['date']);
            $url['df'] = urlencode(trim($df));
            $url['dt'] = urlencode(trim($dt));
        }

        if (isset($post['categories']) && $post['categories'] > 0) {
            // фильтр по категориям товаров
            $url['g_cg'] = intval($post['categories']);
        }

        if (isset($post['other']) && in_array('np', $post['other'])) {
            // фильтр принято через нп
            $url['np'] = 1;
        }

        if (isset($post['other']) && !empty($post['other'])) {
            $url['other'] = implode(',', $post['other']);
        }

        if (isset($post['wh-kiev'])) {
            // фильтр киев
            $url['whk'] = 1;
        }

        if (isset($post['wh-abroad'])) {
            // фильтр заграница
            $url['wha'] = 1;
        }

        if (isset($post['noavail'])) {
            // фильтр не активные
            $url['avail'] = 0;
        }

        if (isset($post['rf'])) {
            // фильтр выдан подменный фонд
            $url['rf'] = 1;
        }

        if (isset($post['nm'])) {
            // не оплаченные
            $url['nm'] = 1;
        }

        if (isset($post['ar'])) {
            // принимались на доработку
            $url['ar'] = 1;
        }

        if (isset($post['order_id']) && !empty($post['order_id'])) {
            // фильтр по id
            if (preg_match('/^[zZ]-/', trim($post['order_id'])) === 1) {
                $orderId = preg_replace('/^[zZ]-/', '', trim($post['order_id']));
            } else {
                $orderId = trim($post['order_id']);
            }
            $url['co_id'] = intval($orderId);
        }

        if (isset($post['categories-last']) && $post['categories-last'] > 0) {
            // фильтр по категориям (устройство)
            $url['dev'] = intval($post['categories-last']);
        }

        if (isset($post['so-status']) && $post['so-status'] > 0) {
            // фильтр по статусу
            $url['sst'] = intval($post['so-status']);
        }

        if (isset($post['goods-goods']) && $post['goods-goods'] > 0) {
            // фильтр по товару
            $url['by_gid'] = intval($post['goods-goods']);
        }

        if (isset($post['warehouse']) && !empty($post['warehouse'])) {
            // фильтр по инженерам
            $url['wh'] = implode(',', $post['warehouse']);
        }

        if (isset($post['engineers']) && !empty($post['engineers'])) {
            // фильтр по инженерам
            $url['eng'] = implode(',', $post['engineers']);
        }

        if (isset($post['managers']) && !empty($post['managers'])) {
            // фильтр по менеджерам
            $url['mg'] = implode(',', $post['managers']);
        }

        if (isset($post['accepter']) && !empty($post['accepter'])) {
            // фильтр по приемщикам
            $url['acp'] = implode(',', $post['accepter']);
        }

        if (isset($post['wh_groups']) && !empty($post['wh_groups'])) {
            // фильтр по поставщикам
            $url['wg'] = implode(',', $post['wh_groups']);
        }

        if (isset($post['suppliers']) && !empty($post['suppliers'])) {
            // фильтр по поставщикам
            $url['sp'] = implode(',', $post['suppliers']);
        }

        if (isset($post['status']) && !empty($post['status'])) {
            // фильтр по статусу
            $url['st'] = implode(',', $post['status']);
        }
        if (isset($post['repair']) && !empty($post['repair'])) {
            // фильтр по статусу
            $repair = array();
            if (in_array('pay', $post['repair'])) {
                $repair[] = 0;
                array_shift($post['repair']);
            }
            if (in_array('wa', $post['repair'])) {
                $repair[] = 1;
                array_shift($post['repair']);
            }
            if (!empty($repair)) {
                $url['rep'] = implode(',', $repair);
            }
            if (!empty($post['repair'])) {
                $url['brands'] = implode(',', $post['repair']);
            }
        }
        if (isset($post['person']) && !empty($post['person'])) {
            // фильтр по статусу
            $url['person'] = implode(',', $post['person']);
        }

        if (isset($post['client']) && !empty($post['client'])) {
            // фильтр клиенту/заказу
            $url['cl'] = trim($post['client']);
        }

        if (isset($post['client-order']) && !empty($post['client-order'])) {
            // фильтр клиенту/заказу
            if (preg_match('/^[zZ]-/', trim($post['client-order'])) === 1) {
                $orderId = preg_replace('/^[zZ]-/', '', trim($post['client-order']));
            } else {
                $orderId = trim($post['client-order']);
            }
            $url['co'] = urlencode(intval($orderId));
        }

        if (isset($post['supplier_order_id_part']) && $post['supplier_order_id_part'] > 0) {
            // фильтр по заказу частичный
            $url['pso_id'] = $post['supplier_order_id_part'];
        }

        if (isset($post['supplier_order_id']) && $post['supplier_order_id'] > 0) {
            // фильтр по заказу
            $url['so_id'] = $post['supplier_order_id'];
        }

        if (isset($post['my']) && !empty($post['my'])) {
            // фильтр по
            $url['my'] = 1;
        }

        if (isset($post['serial']) && !empty($post['serial'])) {
            // фильтр серийнику
            $url['serial'] = trim($post['serial']);
        }
        if (isset($post['lock-button'])) {
            $url['lock-button'] = trim($post['lock-button']);
        }
        if (isset($post['parent-categories']) && count($post['parent-categories']) > 0) {
            $url['cats'] = implode('-', $post['parent-categories']);
        }
        if (isset($post['sale-order'])) {
            if (isset($post['cashless']) && !empty($post['cashless'])) {
                //только безнал
                $url['cashless'] = 1;
            }
            if (isset($post['selfdelivery']) && !empty($post['selfdelivery'])) {
                // самовывоз
                $url['selfdelivery'] = 1;
            }
            if (isset($post['courier']) && !empty($post['courier'])) {
                // доставка курьером
                $url['courier'] = 1;
            }
        }
        return $url;
    }

    /**
     * @param $post
     * @param $mod_id
     * @return array
     */
    private function setEngineerOfService($post, $mod_id)
    {
        try {
            if (empty($post['engineer_id']) || empty($post['service_id'])) {
                throw new ExceptionWithMsg(l('Не задан инженер или сервис'));
            }
            if (!$this->Users->exists($post['engineer_id'])) {
                throw new ExceptionWithMsg(l('Инженер не существует'));
            }
            if (!$this->OrdersGoods->exists($post['service_id'])) {
                throw new ExceptionWithMsg(l('Сервис не существует'));
            }
            $this->OrdersGoods->update(array(
                'engineer' => $post['engineer_id']
            ), array(
                'id' => $post['service_id']
            ));
            $this->History->save('change-engineer-of-service', $mod_id, $post['service_id']);
            $result = array(
                'state' => true
            );
        } catch (ExceptionWithMsg $e) {
            $result = array(
                'state' => false,
                'message' => $e->getMessage()
            );
        }
        return $result;
    }
}
