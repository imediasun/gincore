 <?php

$moduleactive[10] = !$ifauth['is_2'];
$modulename[10] = 'orders';
$modulemenu[10] = 'Заказы';

class orders
{
    public static $mod_submenu = array(
        array(
            'click_tab' => true,
            'url' => '#show_orders',
            'name' => 'Заказы клиентов'
        ), 
        array(
            'click_tab' => true,
            'url' => '#create_order',
            'name' => 'Создать заказ'
        ), 
        array(
            'click_tab' => true,
            'url' => '#show_suppliers_orders',
            'name' => 'Заказы поставщику'
        ), 
        array(
            'click_tab' => true,
            'url' => '#create_supplier_order',
            'name' => 'Создать заказ поставщику'
        ), 
        array(
            'click_tab' => true,
            'url' => '#orders_manager',
            'name' => 'Менеджер заказов'
        ), 
    );
    protected $all_configs;
    public $count_on_page;
    
    function __construct(&$all_configs, $gen_module = true)
    {
        $this->all_configs = $all_configs;
        
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
                    <div class="span9"><p  class="text-danger">У Вас нет прав для управления заказами</p></div>';
            }

            // если отправлена форма
            if ( !empty($_POST) )
                $this->check_post($_POST);

            //if($ifauth['is_2']) return false;

            if ( isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'create' && isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] > 0 ) {
                $input_html['mcontent'] = $this->genorder();
            } else {
                $input_html['mcontent'] = $this->gencontent();
            }
        }
    }

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

    function check_post ($post)
    {
        $mod_id = $this->all_configs['configs']['orders-manage-page'];
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';


        // комментарии к заказам
        if ((isset($post['add_public_comment']) || isset($post['add_private_comment'])) && isset($this->all_configs['arrequest'][2])) {
            if ($this->all_configs['oRole']->hasPrivilege('add-comment-to-clients-orders')) {
                $type = isset($post['add_private_comment']) ? 1 : 0;
                $text = isset($post['add_private_comment']) ? trim($post['private_comment']) : trim($post['public_comment']);
                $this->all_configs['suppliers_orders']->add_client_order_comment($this->all_configs['arrequest'][2], $text, $type);
            }
        }

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

        header("Location:" . $_SERVER['REQUEST_URI']);
        exit;
    }

    function show_filter_manager($compact = false){
        $out = '<div class="'.($compact ? 'input-group' : 'form-group').'">';
        if(!$compact){
            $out .= '<label>Менеджер:</label> ';
        }else{
            $out .= '<p class="form-control-static">Менеджер:</p><span class="input-group-btn">';
        }
        $out .= '<select'.($compact ? ' data-numberDisplayed="0"' : '').' class="multiselect form-control'.($compact ? ' btn-sm ' : '').'" name="managers[]" multiple="multiple">';
        // менеджеры
        $managers = $this->all_configs['db']->query(
            'SELECT DISTINCT u.id, CONCAT(u.fio, " ", u.login) as name FROM {users} as u, {users_permissions} as p, {users_role_permission} as r
            WHERE (p.link=? OR p.link=?) AND r.role_id=u.role AND r.permission_id=p.id',
            array('edit-clients-orders', 'site-administration'))->assoc();
        $mg_get = isset($_GET['mg']) ? explode(',', $_GET['mg']) : 
                  (isset($_GET['managers']) ? $_GET['managers'] : array());
        foreach ($managers as $manager) {
            $out .= '<option ' . ($mg_get && in_array($manager['id'], $mg_get) ? 'selected' : '');
            $out .= ' value="' . $manager['id'] . '">' . htmlspecialchars($manager['name']) . '</option>';
        }
        $out .= '</select>'.($compact ? '</span>' : '').'</div>';
        return $out;
    }
    
    function clients_orders_menu()
    {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : ''/*date('01.m.Y', time())*/)
            . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
            . (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : ''/*date('t.m.Y', time())*/);

        $count = $this->all_configs['db']->query('SELECT COUNT(id) FROM {orders}', array())->el();
        $count_unworked = $this->all_configs['db']->query('SELECT COUNT(id) FROM {orders}
            WHERE manager IS NULL OR manager=""', array())->el();
        $count_marked = $this->all_configs['db']->query('SELECT COUNT(id) FROM {users_marked}
            WHERE user_id=?i AND type=?', array($_SESSION['id'], 'co'))->el();

        $out = '';
        // индинеры
        $engineers = $this->all_configs['db']->query(
            'SELECT DISTINCT u.id, CONCAT(u.fio, " ", u.login) as name FROM {users} as u, {users_permissions} as p, {users_role_permission} as r
            WHERE p.link=? AND r.role_id=u.role AND r.permission_id=p.id',
            array('engineer'))->assoc();
        $engineer_options = '';
        foreach ($engineers as $engineer) {
            $engineer_options .= '<option ' . ((isset($_GET['eng']) && in_array($engineer['id'], explode(',', $_GET['eng']))) ? 'selected' : '');
            $engineer_options .= ' value="' . $engineer['id'] . '">' . htmlspecialchars($engineer['name']) . '</option>';
        }
        // приемщики
        $accepter_options = '';
        $accepters = $this->all_configs['db']->query(
            'SELECT DISTINCT u.id, CONCAT(u.fio, " ", u.login) as name FROM {users} as u, {users_permissions} as p, {users_role_permission} as r
            WHERE (p.link=? OR p.link=?) AND r.role_id=u.role AND r.permission_id=p.id',
            array('create-clients-orders', 'site-administration'))->assoc();
        foreach ($accepters as $accepter) {
            $selected = (($this->all_configs['oRole']->hasPrivilege('partner') && !$this->all_configs['oRole']->hasPrivilege('site-administration') && $user_id == $accepter['id']) || (isset($_GET['acp']) && in_array($accepter['id'], explode(',', $_GET['acp'])))) ? 'selected' : '';
            $accepter_options .= '<option ' . $selected . ' value="' . $accepter['id'] . '">' . htmlspecialchars($accepter['name']) . '</option>';
        }
        // статусы
        $status_options = '';
        foreach ($this->all_configs['configs']['order-status'] as $os_id=>$os_v) {
            $status_options .= '<option ' . ((isset($_GET['st']) && in_array($os_id, explode(',', $_GET['st']))) ? 'selected' : '');
            $status_options .= ' value="' . $os_id . '">' . htmlspecialchars($os_v['name']) . '</option>';
        }
        $out .= '
        <form method="post" class="">
        <div class="clearfix theme_bg filters-box p-sm m-b-md">
            <div class="row row-15">
                <div class="col-sm-2 b-r">
                    <div class="btn-group-vertical">
                        <a class="btn btn-default ' . (!isset($_GET['fco']) && !isset($_GET['marked']) && count($_GET) <= 3 ? 'disabled' : '') . ' text-left" 
                           href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '">
                               Всего: <span id="count-clients-orders">' . $count . '</span>
                        </a>
                        <a class="btn btn-default ' . (isset($_GET['fco']) && $_GET['fco'] == 'unworked' ? 'disabled' : '') . ' text-left" href="
                            '.$this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?fco=unworked">
                                Необработано: <span id="count-clients-untreated-orders">' . $count_unworked . '</span>
                        </a>
                        <a class="btn btn-default ' . (isset($_GET['marked']) && $_GET['marked'] == 'co' ? 'disabled' : '') . ' text-left" href="
                            '.$this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?marked=co#show_orders">
                            Отмеченные: <span class="icons-marked star-marked-active"> </span> <span id="count-marked-co">' . $count_marked . '</span>
                        </a>
                    </div> <br><br>
                    <input type="submit" name="filter-orders" class="btn btn-primary" value="Фильтровать">
                </div>
                <div class="col-sm-2 b-r">
                    <div class="form-group">
                        <input type="text" placeholder="Дата" name="date" class="daterangepicker form-control" value="' . $date . '" />
                    </div>
                    <div class="form-group">
                        <input name="client" value="'.(isset($_GET['cl']) && !empty($_GET['cl']) ? trim(htmlspecialchars($_GET['cl'])) : '').'" type="text" class="form-control" placeholder="телефон/ФИО клиента">
                    </div>
                    <div class="form-group">
                        <input name="order_id" value="'.(isset($_GET['co_id']) && $_GET['co_id'] > 0 ? intval($_GET['co_id']) : '').'" type="text" class="form-control" placeholder="№ заказа">
                    </div>
                    <input type="text" name="serial" class="form-control" value="' . (isset($_GET['serial']) ? $_GET['serial'] : '') . '" placeholder="Серийный номер">
                </div>
                <div class="col-sm-3 b-r">
                    '.typeahead($this->all_configs['db'], 'categories-last', true, isset($_GET['dev']) && $_GET['dev'] ? $_GET['dev'] : '', 5, 'input-small', 'input-mini', '', false, false, '', false, 'Модель').'
                    '.typeahead($this->all_configs['db'], 'goods-goods', true, isset($_GET['by_gid']) && $_GET['by_gid'] ? $_GET['by_gid'] : 0, 6, 'input-small', 'input-mini', '', false, false, '', false, 'Запчасть').'
                    <div class="checkbox">
                        <label><input type="checkbox" name="np" ' . (isset($_GET['np']) ? 'checked' : '') . ' /> Принято через почту</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="rf" '.(isset($_GET['rf']) ? 'checked' : '').' /> Выдан подменный фонд</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="nm" '.(isset($_GET['nm']) ? 'checked' : '').' /> Не оплаченные</label>
                    </div>
                    <div class="checkbox">
                        <label><input type="checkbox" name="ar" '.(isset($_GET['ar']) ? 'checked' : '').' /> Принимались на доработку</label>
                    </div>
                </div>
                <div class="col-sm-2 b-r">
                    <div>
                        <div class="input-group">
                            <p class="form-control-static">Инженер:</p>
                            <span class="input-group-btn">
                                <select data-numberDisplayed="0" class="multiselect btn-sm" name="engineers[]" multiple="multiple">
                                '.$engineer_options.'
                                </select>
                            </span>
                        </div>
                    </div>
                    '.$this->show_filter_manager(true).'
                    <div>
                        <div class="input-group">
                            <p class="form-control-static">Приемщик:</p> 
                            <span class="input-group-btn">
                                <select data-numberDisplayed="0" ' . ($this->all_configs['oRole']->hasPrivilege('partner') && !$this->all_configs['oRole']->hasPrivilege('site-administration')
                                    ? 'disabled' : '') . ' class="multiselect btn-sm" name="accepter[]" multiple="multiple">
                                    '.$accepter_options.'
                                </select>
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="input-group">
                            <p class="form-control-static">Статус:</p>
                            <span class="input-group-btn">
                                <select data-numberDisplayed="0" class="multiselect btn-sm" name="status[]" multiple="multiple">
                                    '.$status_options.'
                                </select>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3" style="overflow:hidden">
        ';
        // фильтр по складам (дерево)
        $data = $this->all_configs['db']->query('SELECT w.id, w.title, gr.name, gr.color, tp.icon, w.group_id
            FROM {orders} as o, {warehouses} as w LEFT JOIN {warehouses_groups} as gr ON gr.id=w.group_id
            LEFT JOIN {warehouses_types} as tp ON tp.id=w.type_id WHERE o.accept_wh_id=w.id', array())->assoc();
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
            $sw = isset($_GET['wh']) ? explode(',', $_GET['wh']) : array();
            $out .= '<ul class="nav nav-list well" id="tree">';
            foreach ($wfs['groups'] as $wf) {
                $out .= '<li><label class="checkbox">';
                $out .= '<input type="checkbox" />' . $wf['name'] . '</label><ul class="nav nav-list">';
                $i = 1;
                foreach ($wf['warehouses'] as $wh_id=>$wh) {
                    $out .= '<li><label class="checkbox">' . $i . ' <i style="color:' . $wh['color'] . ';" class="' . $wh['icon'] . '"></i>&nbsp;';
                    $out .= '<input ' . (in_array($wh_id, $sw) ? 'checked' : '') . ' name="warehouse[]" value="' . $wh_id . '" type="checkbox" />' . $wh['title'] . '</label></li>';
                    $i++;
                }
                $out .= '</ul></li>';
            }
            foreach ($wfs['nogroups'] as $wh_id=>$wh) {
                $out .= '<li><label class="checkbox"><i style="color:' . $wh['color'] . ';" class="' . $wh['icon'] . '"></i>&nbsp;';
                $out .= '<input ' . (in_array($wh_id, $sw) ? 'checked' : '') . ' name="warehouse[]" value="' . $wh_id . '" type="checkbox" />' . $wh['title'] . '</label></li>';
            }
            $out .= '</ul>';
        }
        
        $out .= '
                </div>
            </div>
        </div>
        </form>';

        return $out;
    }

    function gencontent()
    {
        $orders_html = '';

        $orders_html .= '<div class="tabbable"><ul class="nav nav-tabs">';
        if ($this->all_configs['oRole']->hasPrivilege('show-clients-orders')) {
            $orders_html .= '<li><a class="click_tab default" data-open_tab="orders_show_orders" onclick="click_tab(this, event)" data-toggle="tab" href="'.self::$mod_submenu[0]['url'].'">'.self::$mod_submenu[0]['name'].'<span class="tab_count hide tc_clients_orders"></span></a></li>';
        }
        if ($this->all_configs['oRole']->hasPrivilege('create-clients-orders')) {
            $orders_html .= '<li><a class="click_tab" data-open_tab="orders_create_order" onclick="click_tab(this, event)" data-toggle="tab" href="'.self::$mod_submenu[1]['url'].'">'.self::$mod_submenu[1]['name'].'</a></li>';
        }
        if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
            $orders_html .= '<li><a class="click_tab" data-open_tab="orders_show_suppliers_orders" onclick="click_tab(this, event)" data-toggle="tab" href="'.self::$mod_submenu[2]['url'].'">'.self::$mod_submenu[2]['name'].'<span class="tab_count hide tc_suppliers_orders"></span></a></li>';
            $orders_html .= '<li><a class="click_tab" data-open_tab="orders_create_supplier_order" onclick="click_tab(this, event)" data-toggle="tab" href="'.self::$mod_submenu[3]['url'].'">'.self::$mod_submenu[3]['name'].'</a></li>';
        }
        if ($this->all_configs['oRole']->hasPrivilege('orders-manager')) {
            $orders_html .= '<li><a class="click_tab default" data-open_tab="orders_manager" onclick="click_tab(this, event)" data-toggle="tab" href="'.self::$mod_submenu[4]['url'].'">'.self::$mod_submenu[4]['name'].'</a></li>';
        }

        $orders_html .= '</ul><div class="tab-content">';

        // вывод заказов
        if ($this->all_configs['oRole']->hasPrivilege('show-clients-orders')) {
            $orders_html .= '<div id="show_orders" class="tab-pane clearfix"></div>';
        }
        // создать заказ клиента
        if ($this->all_configs['oRole']->hasPrivilege('create-clients-orders')) {
            $orders_html .= '<div id="create_order" class="tab-pane clearfix">';
            $orders_html .= '</div>';
        }
        // менеджер заказов
        if ($this->all_configs['oRole']->hasPrivilege('orders-manager')) {
            $orders_html .= '<div id="orders_manager" class="tab-pane clearfix">';
            $orders_html .= '</div>';
        }
        // заказ поставщику
        if ( $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') ) {
            $orders_html .= '<div id="show_suppliers_orders" class="tab-pane clearfix"></div>';

            $orders_html .= '<div id="create_supplier_order" class="tab-pane clearfix">';
            $orders_html .= '</div>';
        }

        $orders_html .= '</div></div>';//?

//        $orders_html .= $this->all_configs['chains']->append_js();
        $orders_html .= $this->all_configs['suppliers_orders']->append_js();

        return $orders_html;
    }

    function orders_show_orders($hash = '#show_orders-orders')
    {
        if (trim($hash) == '#show_orders' || (trim($hash) != '#show_orders-orders' && trim($hash) != '#show_orders-sold'
            && trim($hash) != '#show_orders-return' && trim($hash) != '#show_orders-writeoff'))
            $hash = '#show_orders-orders';

        $orders_html = '';
        if ($this->all_configs['oRole']->hasPrivilege('show-clients-orders')) {
            $orders_html .= '<div class="span12">';

            $orders_html .= '<ul class="list-unstyled inline clearfix m-b-md">';
            $orders_html .= '<li class=""><a class="click_tab btn btn-info" href="#show_orders-orders" title="" onclick="click_tab(this, event)" data-open_tab="show_orders_orders"><i class="fa fa-wrench"></i> РЕМОНТЫ</a></li>';
            $orders_html .= '<li class=""><a class="click_tab btn btn-primary" href="#show_orders-sold" title="" onclick="click_tab(this, event)" data-open_tab="show_orders_sold"><i class="fa fa-money"></i> ПРОДАЖИ</a></li>';
            $orders_html .= '<li class=""><a class="click_tab btn btn-danger" href="#show_orders-writeoff" title="" onclick="click_tab(this, event)" data-open_tab="show_orders_writeoff"><i class="fa fa-times"></i> СПИСАНИЯ</a></li>';
            $orders_html .= '<li class=""><button data-toggle="filters" type="button" class="toggle-hidden btn btn-default"><i class="fa fa-filter"></i> Фильтровать <i class="fa fa-caret-down"></i></button></li>';
            if ($this->all_configs['oRole']->hasPrivilege('create-clients-orders')) {
                $orders_html .= '<li class="pull-right"><a href="' . $this->all_configs['prefix'] . 'orders/#create_order" class="btn btn-success hash_link">Создать заказ</a></li>';
            }
            $orders_html .= '</ul>
                <div class="hidden" id="filters">'.$this->clients_orders_menu().'</div>
                <div class="pill-content">';
            $orders_html .= '<div id="show_orders-orders" class="pill-pane active">';
            $orders_html .= '</div>';

            $orders_html .= '<div id="show_orders-sold" class="pill-pane">';
            $orders_html .= '</div>';

            $orders_html .= '<div id="show_orders-writeoff" class="pill-pane">';
            $orders_html .= '</div>';

            $orders_html .= '</div>';
        }

        return array(
            'html' => $orders_html,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')', 'reset_multiselect()', 'gen_tree()'),
        );
    }

    function show_orders_orders()
    {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $orders_html = '';
        $filters = array('type' => 0);
        if ($this->all_configs['oRole']->hasPrivilege('partner') && !$this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $filters['acp'] = $user_id;
        }
        $queries = $this->all_configs['manageModel']->clients_orders_query($filters + $_GET);
        $query = $queries['query'];
        $skip = $queries['skip'];
        $count_on_page = $this->count_on_page;

        // достаем заказы
        $orders = $this->all_configs['manageModel']->get_clients_orders($query, $skip, $count_on_page, 'co');

        if ($orders && count($orders) > 0) {
            $orders_html .= '<table class="table"><thead><tr><td></td><td>№ заказа</td><td>Дата</td>';
            $orders_html .= '<td>Приемщик</td><td>Менеджер</td><td>Статус</td><td>Устройство</td>';
            if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                $orders_html .= '<td>Стоимость</td><td>Оплачено</td>';
            } else {
                $orders_html .= '<td>Оплата</td>';
            }
            $orders_html .= '<td>Клиент</td><td>Контактный тел</td>';
            $orders_html .= '<td>Сроки</td><td>Склад</td></tr></thead><tbody id="table_clients_orders">';

            foreach ($orders as $order) {
                $orders_html .= display_client_order($order);
            }
            $orders_html .= '</tbody></table>';

            // количество заказов клиентов
            $count = $this->all_configs['manageModel']->get_count_clients_orders($query, 'co');

            $count_page = ceil($count / $count_on_page);

            // строим блок страниц
            $orders_html .= page_block($count_page, '#show_orders');

        } else {
            $orders_html .= '<div class="span9"><p  class="text-danger">Заказов не найдено</p></div>';
        }

        return array(
            'html' => $orders_html,
            'functions' => array(),
        );
    }

    function show_orders_sold()
    {
        $orders_html = '';
        $queries = $this->all_configs['manageModel']->clients_orders_query(array('type' => 3) + $_GET);
        $query = $queries['query'];
        $skip = $queries['skip'];
        $count_on_page = $this->count_on_page;

        // достаем заказы
        $orders = $this->all_configs['manageModel']->get_clients_orders($query, $skip, $count_on_page, 'co');

        if ($orders && count($orders) > 0) {
            $orders_html .= '<table class="table"><thead><tr><td></td><td>№ заказа</td><td>Дата</td>';
            $orders_html .= '<td>Приемщик</td><td>Менеджер</td><td>Статус</td><td>Устройство</td>';
            if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                $orders_html .= '<td>Стоимость</td><td>Оплачено</td>';
            }
            $orders_html .= '<td>Клиент</td><td>Контактный тел</td>';
            $orders_html .= '<td>Сроки</td><td>Склад</td></tr></thead><tbody id="table_clients_orders">';

            foreach ($orders as $order) {
                $orders_html .= display_client_order($order);
            }
            $orders_html .= '</tbody></table>';

            // количество заказов клиентов
            $count = $this->all_configs['manageModel']->get_count_clients_orders($query, 'co');

            $count_page = ceil($count / $count_on_page);

            // строим блок страниц
            $orders_html .= page_block($count_page, '#show_orders');

        } else {
            $orders_html .= '<div class="span9"><p  class="text-danger">Заказов не найдено</p></div>';
        }

        return array(
            'html' => $orders_html,
            'functions' => array(),
        );
    }

    function show_orders_return()
    {
        $orders_html = '';
        $queries = $this->all_configs['manageModel']->clients_orders_query(array('type' => 1) + $_GET);
        $query = $queries['query'];
        $skip = $queries['skip'];
        $count_on_page = $this->count_on_page;

        // достаем заказы
        $orders = $this->all_configs['manageModel']->get_clients_orders($query, $skip, $count_on_page, 'co');

        if ($orders && count($orders) > 0) {
            $orders_html .= '<div id="show_orders"><table class="table"><thead><tr><td></td><td>№ заказа</td><td>Дата</td>';
            $orders_html .= '<td>Приемщик</td><td>Менеджер</td><td>Статус</td><td>Устройство</td>';
            if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                $orders_html .= '<td>Стоимость</td><td>Оплачено</td>';
            }
            $orders_html .= '<td>Клиент</td><td>Контактный тел</td>';
            $orders_html .= '<td>Сроки</td><td>Склад</td></tr></thead><tbody id="table_clients_orders">';

            foreach ($orders as $order) {
                $orders_html .= display_client_order($order);
            }
            $orders_html .= '</tbody></table></div>';

            // количество заказов клиентов
            $count = $this->all_configs['manageModel']->get_count_clients_orders($query, 'co');

            $count_page = ceil($count / $count_on_page);

            // строим блок страниц
            $orders_html .= page_block($count_page, '#show_orders');

        } else {
            $orders_html .= '<div class="span9"><p  class="text-danger">Заказов не найдено</p></div>';
        }

        return array(
            'html' => $orders_html,
            'menu' => $this->clients_orders_menu(),
            'functions' => array('reset_multiselect()','gen_tree()'),
        );
    }

    function show_orders_writeoff()
    {
        $orders_html = '';
        $queries = $this->all_configs['manageModel']->clients_orders_query(array('type' => 2) + $_GET);
        $query = $queries['query'];
        $skip = $queries['skip'];
        $count_on_page = $this->count_on_page;

        // достаем заказы
        $orders = $this->all_configs['manageModel']->get_clients_orders($query, $skip, $count_on_page, 'co');

        if ($orders && count($orders) > 0) {
            $orders_html .= '<table class="table"><thead><tr><td></td><td>№ заказа</td><td>Дата</td>';
            $orders_html .= '<td>Приемщик</td><td>Менеджер</td><td>Статус</td><td>Устройство</td>';
            if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                $orders_html .= '<td>Стоимость</td><td>Оплачено</td>';
            }
            $orders_html .= '<td>Клиент</td><td>Контактный тел</td>';
            $orders_html .= '<td>Сроки</td><td>Склад</td></tr></thead><tbody id="table_clients_orders">';

            foreach ($orders as $order) {
                $orders_html .= display_client_order($order);
            }
            $orders_html .= '</tbody></table>';

            // количество заказов клиентов
            $count = $this->all_configs['manageModel']->get_count_clients_orders($query, 'co');

            $count_page = ceil($count / $count_on_page);

            // строим блок страниц
            $orders_html .= page_block($count_page, '#show_orders');

        } else {
            $orders_html .= '<div class="span9"><p  class="text-danger">Заказов не найдено</p></div>';
        }

        return array(
            'html' => $orders_html,
            'functions' => array(),
        );
    }

    function orders_create_order()
    {
        $orders_html = '';
        if ($this->all_configs['oRole']->hasPrivilege('create-clients-orders')) {
            //вывод списска клиентов для создания нового заказа

            // на основе заявки
            $order_data = null;
            if(!empty($_GET['on_request'])){
                $order_data = get_service('crm/requests')->get_request_by_id($_GET['on_request']);
            }
            
            //$orders_html .= '<p class="text-error">Обязательно сообщить клиенту!<br />"Диагностика у нас бесплатная в случае последующего ремонта и в случае когда мы не можем сремонтировать устройство.<br />В случае отказа от ремонта со стороны клиента - диагностика составит 100 '.viewCurrency().'"</p>';
            //$orders_html .= '<div class="control-group"><label class="control-label">Номер заказа: </label>';
//            $orders_html .= '<div class="controls"><input type="text" class="input-xlarge" value="" name="id" /></div></div>';
            
            $client_id = $order_data ? $order_data['client_id'] : 0;
            $client_fields = client_double_typeahead($client_id, 'get_requests');
            $colors_select = '';
            foreach($this->all_configs['configs']['devices-colors'] as $i=>$c){
                $colors_select .= '<option value="'.$i.'">'.$c.'</option>';
            }
            $orders_html = '
                <ul class="nav nav-tabs default_tabs" role="tablist">
                    <li role="presentation" class="active">
                        <a href="#repair" role="tab" data-toggle="tab">Заказ на ремонт</a>
                    </li>
                    <li role="presentation">
                        <a href="#sale" role="tab" data-toggle="tab">Заказ на продажу</a>
                    </li>
                </ul>
                <div class="tab-content">
                    <div class="tab-pane active" id="repair">
                        <form method="post" id="order-form">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-sm-6">
                                        <fieldset>
                                            <legend>Клиент</legend>
                                            <div class="form-group">
                                                <label>Укажите данные клиента: </label>
                                                <div class="row row-15">
                                                    <div class="col-sm-6">
                                                        '.$client_fields['phone'].'
                                                    </div>
                                                    <div class="col-sm-6">
                                                        '.$client_fields['fio'].'
                                                    </div>
                                                </div>
                                                <!--<div class="input-group">
                                                    <input name="client_fio_hidden" type="hidden" id="client_fio_hidden" value="">
                                                    '.typeahead($this->all_configs['db'], 'clients', false, ($order_data ? $order_data['client_id'] : 0), 2, 'input-xlarge', 'input-medium', 'get_requests,check_fio')
                                                    .'<span class="input-group-btn">
                                                        <input class="btn btn-info" type="button" onclick="alert_box(this, false, \'create-client\')" value="Добавить">
                                                    </span>
                                                </div>-->
                                            </div>
                                            '.get_service('crm/calls')->assets().'
                                            <div class="form-group">
                                                <label style="padding-top:0">Код на скидку: </label>
                                                <input'.($order_data ? ' value="'.$order_data['code'].'" disabled' : '').' type="text" name="code" class="form-control call_code_mask" id="crm_order_code">
                                            </div>
                                            <div class="form-group">
                                                <label>Рекламный канал (источник): </label>
                                                <div id="crm_order_referer">
                                                    '.get_service('crm/calls')->get_referers_list($order_data ? $order_data['referer_id'] : 'null', '', !!$order_data).'
                                                </div>
                                            </div>
                                        </fieldset>
                                        <fieldset>
                                            <legend>Устройство</legend>
                                            <div class="form-group">
                                                <label class="control-label">Выберите устройство: </label>
                                                <div class="input-group">
                                                    '.typeahead($this->all_configs['db'], 'categories-last', false, ($order_data ? $order_data['product_id'] : 0), 3, 'input-medium popover-info', '', 'display_service_information,get_requests')
                                                    .'
                                                    <div class="input-group-btn">
                                                        <button type="button" id="show_add_device_form" class="btn btn-info">
                                                            Добавить новое
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label">Цвет: </label>
                                                <select class="form-control" name="color">'.$colors_select.'</select>
                                            </div>
                                            <!--<div class="form-group">
                                                <label class="control-label">Серийный номер запчасти: </label>
                                                '.typeahead($this->all_configs['db'], 'serials', false, '', 3, 'input-medium clone_clear_val', '', 'display_serial_product', false, true)
                                                .'<small class="clone_clear_html product-title"></small>
                                            </div>-->
                                            <div class="form-group">
                                                <label>Серийный номер: </label>
                                                <input type="text" class="form-control" value="" name="serial" />
                                            </div>
                                            <input type="hidden" value="" id="serial-id" name="serial-id" />
                                            <div class="form-group">
                                                <label>Комплектация:</label><br>
                                                <label class="checkbox-inline"><input type="checkbox" value="1" name="battery" /> Аккумулятор</label>
                                                <label class="checkbox-inline"><input type="checkbox" value="1" name="charger" /> Зарядное устройство/кабель</label>
                                                <label class="checkbox-inline"><input type="checkbox" value="1" name="cover" /> Задняя крышка</label>
                                                <label class="checkbox-inline"><input type="checkbox" value="1" name="box" /> Коробка</label>
                                            </div>
                                            <div class="form-group">
                                                <label>Вид ремонта: </label><br>
                                                <label class="radio-inline"><input type="radio" checked value="0" name="repair" /> Платный</label>
                                                <label class="radio-inline"><input type="radio" value="1" name="repair" /> Гарантийный</label>
                                                <label class="radio-inline"><input type="radio" value="2" name="repair" /> Доработка</label>
                                            </div>
                                            <div class="form-group">
                                                <label>Неисправность со слов клиента: </label>
                                                <div class="row row-15 form-group">
                                                    <div class="col-sm-6">
                                                        <label>Замена:</label> 
                                                        <input class="form-control" name="repair_part" placeholder="укажите деталь">
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <label>Качество детали:</label> 
                                                        <select class="form-control" name="repair_part_quality">
                                                            <option value="Не согласовано">Не согласовано</option>
                                                            <option value="Оригинал">Оригинал</option>
                                                            <option value="Копия">Копия</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <textarea class="form-control" name="defect"></textarea>
                                            </div>
                                            <div class="form-group">
                                                <label class="control-label">Внешний вид: </label>
                                                <textarea class="form-control" name="comment">Потертости, царапины</textarea>
                                            </div>
                                        </fieldset>
                                        <fieldset>
                                            <legend>Стоимость</legend>
                                            <div class="form-group">
                                                <label>Ориентировочная стоимость: </label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" value="" name="approximate_cost" />
                                                    <span class="input-group-addon">'.viewCurrency().'</span>
                                                </div>
                                            </div>
                                            <!--<div class="form-group">
                                                <label>Партнерская программа: </label>
                                                <select class="form-control" name="partner"><option value="0">Выберите</option>
                                                </select>
                                            </div>-->
                                            <div class="form-group">
                                                <label>Предоплата: </label>
                                                <div class="input-group">
                                                    <input type="text" placeholder="Введите сумму" class="form-control" value="" name="sum_paid" />  
                                                    <span class="input-group-addon">'.viewCurrency().'</span>
                                                    <input type="text" placeholder="Комментарий к предоплате" class="form-control" value="" name="prepay_comment" /> 
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Ориентировочная дата готовности: </label>
                                                <div class="input-group">
                                                    <input class="daterangepicker_single form-control" data-format="YYYY-MM-DD" type="text" name="date_readiness" value="" />
                                                    <span class="input-group-addon"><i class="glyphicon glyphicon-calendar" data-time-icon="glyphicon glyphicon-time" data-date-icon="glyphicon glyphicon-calendar"></i></span>
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label>Доп. информация</label> <br>
                                                <div class="form-group-row">
                                                    <div class="col-sm-6">
                                                        <div class="checkbox"><label><input type="checkbox" value="1" name="client_took" /> Устройство у клиента</label></div>
                                                        <div class="checkbox"><label><input type="checkbox" value="1" name="urgent" /> Срочный ремонт</label></div>
                                                        <div class="checkbox"><label><input type="checkbox" value="1" name="np_accept" /> Принято через почту</label></div>
                                                        <div class="checkbox"><label><input type="checkbox" value="1" name="nonconsent" /> Можно пускать в работу без согласования</label></div>
                                                        <div class="checkbox"><label><input type="checkbox" value="1" name="is_waiting" /> Клиент готов ждать 2-3 недели запчасть</label></div>
                                                    </div>
                                                    <div class="col-sm-6">
                                                        <div class="checkbox"><label>
                                                            <input onclick="if ($(this).prop(\'checked\')){$(\'.courier_address\').show();}else{$(\'.courier_address\').hide();}" type="checkbox" value="1" name="is_courier" />
                                                            Курьер забрал устройство у клиента
                                                            <input type="text" style="display:none;" placeholder="по адресу" class="form-control courier_address" value="" name="courier" />
                                                        </label></div>
                                                        <div class="checkbox"><label>
                                                            <input onclick="if ($(this).prop(\'checked\')){$(\'.replacement_fund\').show();}else{$(\'.replacement_fund\').hide();}" type="checkbox" value="1" name="is_replacement_fund" />
                                                            Выдан подменный фонд
                                                            <input type="text" style="display:none;" placeholder="Модель, серийный номер" class="form-control replacement_fund" value="" name="replacement_fund" />
                                                        </label></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                    <div class="col-sm-6 relative">
                                        <div id="new_device_form" class="theme_bg new_device_form p-md"></div>
                                        <fieldset>
                                            <legend>Заявки</legend>
                                                <div id="client_requests">
                                                    '.($order_data ? 
                                                        get_service('crm/requests')->get_requests_list_by_order_client($order_data['client_id'], $order_data['product_id'], $_GET['on_request']) 
                                                            : '<span class="muted">выберите клиента или устройство чтобы увидеть заявки</span>').'
                                                </div>
                                            </div><br>
                                        </fieldset>
                                    </div>
                                </div>
                                <input id="add-client-order" class="btn btn-primary" type="button" onclick="add_new_order(this)" value="Добавить" />
                            </form>
                        </div>
                        <div class="tab-pane" id="sale">
                            '.$this->order_for_sale_form().'
                        </div>
                    </div>
                </div>
            ';
        }

        return array(
            'html' => $orders_html,
            'functions' => array(),
        );
    }
    
    function order_for_sale_form(){
        $client_fields_for_sale = client_double_typeahead();
        $form = '
            <form method="post">
                <input type="hidden" name="type" value="3">
                <fieldset>
                    <legend>Клиент</legend>
                    <div class="form-group">
                        <label>Укажите данные клиента: </label>
                        <div class="row row-15">
                            <div class="col-sm-6">
                                '.$client_fields_for_sale['phone'].'
                            </div>
                            <div class="col-sm-6">
                                '.$client_fields_for_sale['fio'].'
                            </div>
                        </div>
                        <!--<input name="client_fio_hidden" type="hidden" id="client_fio_hidden" value="">
                        '.typeahead($this->all_configs['db'], 'clients', false, 0, 3, 'input-xlarge', 'input-medium', 'check_fio')
                        .'<span class="input-group-btn">
                            <input class="btn btn-info" type="button" onclick="alert_box(this, false, \'create-client\')" value="Добавить">
                        </span>
                        -->
                    </div>
                    <div class="form-group">
                        <label style="padding-top:0">Код на скидку: </label>
                        <input type="text" name="code" class="form-control call_code_mask">
                    </div>
                    <div class="form-group">
                        <label>Рекламный канал (источник): </label>
                        '.get_service('crm/calls')->get_referers_list().'
                    </div>
                </fieldset>
                <fieldset>
                    <legend>Товар</legend>
                    <div class="form-group">
                        <label class="control-label">Код товара (серийный номер): </label>
                        '.typeahead($this->all_configs['db'], 'serials', false, '', 4, 'input-medium clone_clear_val', '', 'display_serial_product_title_and_price', false, true)
                        .'<small class="clone_clear_html product-title"></small>
                        <input type="hidden" name="items" value="">
                    </div>
                    <div class="form-group">
                        <label>Цена продажи: </label>
                        <div class="input-group">
                            <input type="text" id="sale_poduct_cost" class="form-control" value="" name="price" />
                            <span class="input-group-addon">'.viewCurrency().'</span>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Скрытый комментарий к заказу: </label>
                        <textarea name="private_comment" class="form-control" rows="3"></textarea>
                    </div>
                </fieldset>
                <input class="btn btn-primary" type="button" onclick="sale_order(this)" value="Добавить" />
            </form>
        ';
        return $form;
    }

    function orders_show_suppliers_orders($hash = '#show_suppliers_orders')
    {
        if (trim($hash) == '#show_suppliers_orders' || (trim($hash) != '#show_suppliers_orders-all'
                && trim($hash) != '#show_suppliers_orders-wait' && trim($hash) != '#show_suppliers_orders-procurement'
                && trim($hash) != '#show_suppliers_orders-return'))
            $hash = '#show_suppliers_orders-all';

        $orders_html = '<div>';

        $orders_html .= '<ul class="list-unstyled inline clearfix">';
        $orders_html .= '<li class=""><a class="click_tab btn btn-info" href="#show_suppliers_orders-all" title="" onclick="click_tab(this, event)" data-open_tab="orders_show_suppliers_orders_all"><i class="fa fa-bolt"></i> Все заказы<span class="tab_count hide tc_suppliers_orders_all"></span></a></li>';
        $orders_html .= '<li class=""><a class="click_tab btn btn-danger" href="#show_suppliers_orders-wait" title="" onclick="click_tab(this, event)" data-open_tab="orders_show_suppliers_orders_wait"><i class="fa fa-clock-o"></i> Ожидают проверки</a></li>';
        $orders_html .= '<li class=""><a class="click_tab btn btn-warning" href="#show_suppliers_orders-return" title="" onclick="click_tab(this, event)" data-open_tab="show_orders_return"><i class="fa fa-exchange"></i> Возвраты поставщикам</a></li>';
        $orders_html .= '<li class=""><a class="click_tab btn btn-primary" href="#show_suppliers_orders-procurement" title="" onclick="click_tab(this, event)" data-open_tab="orders_recommendations_procurement">Рекомендации по закупкам</a></li>';
        $orders_html .= '<li class=""><button data-toggle="filters" type="button" class="toggle-hidden btn btn-default"><i class="fa fa-filter"></i> Фильтровать <i class="fa fa-caret-down"></i></button></li>';
        $orders_html .= '</ul>
            <div class="hidden" id="filters"><div id="show_suppliers_orders-menu"></div></div>
        <div class="pill-content">';

        $orders_html .= '<div id="show_suppliers_orders-all" class="pill-pane">';
        $orders_html .= '</div>';

        $orders_html .= '<div id="show_suppliers_orders-wait" class="pill-pane">';
        $orders_html .= '</div>';

        $orders_html .= '<div id="show_suppliers_orders-return" class="pill-pane">';
        $orders_html .= '</div>';
        
        $orders_html .= '<div id="show_suppliers_orders-procurement" class="pill-pane">';
        $orders_html .= '</div>';

        $orders_html .= '</div>';
        $orders_html .= '</div>';

        return array(
            'html' => $orders_html,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')', 'reset_multiselect()'),
        );

        return array(
            'html' => $orders_html,
            'functions' => array('reset_multiselect()'),
        );
    }

    function orders_show_suppliers_orders_all()
    {
        $orders_html = '';

        if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
            $my = $this->all_configs['oRole']->hasPrivilege('site-administration') || $this->all_configs['oRole']->hasPrivilege('edit-map')? false : true;
            $_GET['my'] = $my || (isset($_GET['my']) && $_GET['my'] == 1) ? true : false;
            $queries = $this->all_configs['manageModel']->suppliers_orders_query($_GET);
            $query = $queries['query'];
            $skip = $queries['skip'];
            $count_on_page = $this->count_on_page;//$queries['count_on_page'];

            $orders = $this->all_configs['manageModel']->get_suppliers_orders($query, $skip, $count_on_page);
            /*if (1==1) {
                include_once $this->all_configs['sitepath'] . 'shop/exports.class.php';
                $exports = new Exports();
                $exports->build($orders);
                exit;
            }*/
            $orders_html .= $this->all_configs['suppliers_orders']->show_suppliers_orders($orders);

            $count = $this->all_configs['manageModel']->get_count_suppliers_orders($query);

            $count_page = $count_on_page > 0 ? ceil($count / $count_on_page) : 0;

            // строим блок страниц
            $orders_html .= page_block($count_page, '#show_suppliers_orders-all');
        }

        return array(
            'html' => $orders_html,
            'menu' => $this->all_configs['suppliers_orders']->show_filters_suppliers_orders(true),
            'functions' => array('reset_multiselect()'),
        );
    }

    function orders_show_suppliers_orders_wait()
    {
        $orders_html = '';

        if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')) {
            $my = $this->all_configs['oRole']->hasPrivilege('site-administration') || $this->all_configs['oRole']->hasPrivilege('edit-map')? false : true;
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
                $orders_html .= '<table class="show-suppliers-orders table"><thead><tr><td></td><td>Дата созд.</td>
                    <td>Код</td><td>Наименование</td><td>Кол-во</td><td>Оприх.</td><td>Склад</td><td>Локация</td>
                    <td>Проверить до</td><td>Проверка</td><td>Номера ремонтов, на которых можно проверить запчасти</td>
                    <td>Комментарий</td></tr></thead><tbody>';
                foreach ($orders as $order) {
                    $print_btn = $items = '';
                    if (count($order['items']) > 0) {
                        $url = $this->all_configs['prefix'] . 'print.php?act=label&object_id=' . implode(',', array_keys($order['items']));
                        $print_btn = '<a target="_blank" title="Печать" href="' . $url . '"><i class="fa fa-print"></i></a>';
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
                                <input class="datetimepicker form-control input-xs" placeholder="Дата проверки" data-format="yyyy-MM-dd hh:mm:ss" type="text" name="date_check" value="' . $order['date_check'] . '" />
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
                $orders_html .= '<p  class="text-danger">Нет заказов</p>';
            }

            $count = $this->all_configs['manageModel']->get_count_suppliers_orders($query);

            $count_page = $count_on_page > 0 ? ceil($count / $count_on_page) : 0;

            // строим блок страниц
            $orders_html .= page_block($count_page, '#show_suppliers_orders-wait');
        }

        return array(
            'html' => $orders_html,
            'menu' => $this->all_configs['suppliers_orders']->show_filters_suppliers_orders(true),
            'functions' => array('reset_multiselect()'),
        );
    }

    function menu_recommendations_procurement()
    {
        $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : ''/*date('01.m.Y', time())*/)
            . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
            . (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : ''/*date('t.m.Y', time())*/);

        $out = '<form method="post"><div class="clearfix theme_bg filters-box p-sm m-b-md">';
        $out .= '<div class="form-group"><label>Категории</label>';
        $out .= '<select class="multiselect form-control" multiple="multiple" name="ctg[]">';
        $categories = $categories = $this->all_configs['db']->query("SELECT * FROM {categories}")->assoc();
        $out .= build_array_tree($categories, isset($_GET['ctg']) ? explode(',', $_GET['ctg']) : null);
        $out .= '</select></div>';
        $out .= '<div class="form-group"><label>Сроки доставки</label>';
        $s = isset($_GET['tso']) ? intval($_GET['tso']) : 0;
        $out .= '<select class="form-control" name="tso"><option ' . ($s == 4 ? 'selected' : '') . ' value="4">4</option>';
        $out .= '<option ' . ($s == 3 ? 'selected' : '') . ' value="3">3</option>';
        $out .= '<option ' . ($s == 2 ? 'selected' : '') . ' value="2">2</option>';
        $out .= '<option ' . ($s == 1 ? 'selected' : '') . ' value="1">1</option></select></div>';
        $out .= '<div class="form-group"><label>Дата от:</label>';
        $out .= '<input type="text" placeholder="Дата" name="date" class="daterangepicker form-control" value="' . $date . '" />';
        $out .= '</div><input type="submit" class="btn btn-primary" value="Применить" name="procurement-filter" />';
        $out .= '</div></form>';

        return $out;
    }

    function getIsoWeeksInYear($year) {
        $date = new DateTime;
        $date->setISODate($year, 53);
        return ($date->format("W") === "53" ? 53 : 52);
    }

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
                            $amounts[$p_id]['qty_consumption'] = '<span class="popover-info" data-content="' . $str . '" data-original-title="шт / к-во недель * 4">' . $amounts[$p_id]['qty_consumption'] . '</span>';
                            
                            $str = $amounts[$p_id]['qty_demand'] . ' / ' . count($demand[$p_id]) . ' * ' . 4;
                            $amounts[$p_id]['qty_demand'] = count($demand[$p_id]) > 0 ? round($amounts[$p_id]['qty_demand'] / count($demand[$p_id]) * 4, 2) : 0;
                            $amounts[$p_id]['qty_demand'] = '<span class="popover-info" data-content="' . $str . '" data-original-title="шт / к-во недель * 4">' . $amounts[$p_id]['qty_demand'] . '</span>';

                            
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

                                $amounts[$p_id]['qty_forecast'] = '<span class="popover-info" data-content="' . $str . '" data-original-title="Среднее значение * %">' . $amounts[$p_id]['qty_forecast'] . '</span>';
                            }
                        }
                    }
                }

                $orders_html .= '<table class="table" id="tablesorter"><thead><tr><th>Наименование</th><th>Общ.ост.</th><th>Своб.ост.</th>';
                $orders_html .= '<th>Ожид.пост.(общ.)</th><th>Ожид.пост.(своб.)</th><th>Расход (шт/мес)</th>';
                $orders_html .= '<th>Спрос (шт/мес)</th><th>Прогноз</th><th>Рекомендовано еще к заказу</th></tr></thead><tbody>';
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
                $orders_html .= '<p class="text-danger">Для правильности рассчетов укажите сроки доставки заказа поставщику</p>';
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

    function orders_create_supplier_order()
    {
        $orders_html = '';

        if ( $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') ) {
            if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] > 0) {
                $orders_html .= $this->all_configs['suppliers_orders']->create_order_block(1, $this->all_configs['arrequest'][2]);
            } else {
                $orders_html .= $this->all_configs['suppliers_orders']->create_order_block(1);
                //$orders_html .= '<div class="control-group"><label class="control-label"></label>';
                //$orders_html .= '<div title="Добавить еще товар" class="add_supplier_form" onclick="add_supplier_form(this)">';
                //$orders_html .= '<i class="icon-plus"></i><div class="controls"></div></div></div>';
            }
        }

        return array(
            'html' => $orders_html,
            'functions' => array(),
        );
    }

    // status -1
    function check_if_order_fail_in_orders_manager($order){
        $day = 60 * 60 * 24;
        //1 Запчасть заказана, оприходована, но не отгружена под ремонт больше 2-х дней
        //2 Заказ клиента подвязан к заказу поставщику, а указанная в заказе поставщику дата поставки просрочена.
        //3 По нормативу с момента создания заказа на закупку (пустышки) и создания заказа поставщику не должно пройти больше 3х дней.
        //4 У ремонта выставлен статус "Ожидает запчасть", а заказ на закупку не отправлен и не привязан никакой заказ поставщику
        if ($order['status'] == $this->all_configs['configs']['order-status-waits'] && $order['broken'] > 0) {
            return true;
        }
        // Принят в ремонт > 3 дней
        if ($order['status'] == $this->all_configs['configs']['order-status-new'] && strtotime($order['date']) + $day * 3 < time()) {
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
    function get_orders_for_orders_manager($filters_query = ''){
        $orders = $this->all_configs['db']->query(
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
                WHERE ?query o.type NOT IN (?li) AND o.status IN (?li) AND UNIX_TIMESTAMP(o.date_add)>? GROUP BY o.id ORDER BY o.date_add',
                array($this->all_configs['configs']['order-status-waits'], $filters_query, array(1),
                    $this->all_configs['configs']['order-statuses-manager'], (time() - 60*60*24*90)))->assoc();
        return $orders;
    }
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
            $colors_percents = '(статистика отсутствует)';
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
                $query .= $this->all_configs['db']->makeQuery(' o.manager IN (?li) AND ', array($mg));
            }
            // фильтр статистики по дате
            $get_date = isset($_GET['date']) ? htmlspecialchars($_GET['date']) : '';
            $date = isset($_GET['date']) && trim($_GET['date']) ? explode('-', $_GET['date']) : array();
            $filter_stats = '';
            if($date){
                $date_from = date('Y-m-d', strtotime($date[0]));
                $date_between = date('Y-m-d', strtotime($date[1]));
                $date_diff = date_diff(date_create($date_from), date_create($date_between));
                $date_query = $this->all_configs['db']->makeQuery(" date BETWEEN ? AND ? ", array($date_from, $date_between));
                $stats = $this->all_configs['db']->query("SELECT id, status, date, count(id) as qty_by_status "
                                                        ."FROM {orders_manager_history} "
                                                        ."WHERE ?q ?q GROUP BY date, status", array(str_replace(array('w.','o.'),'',$query), $date_query), 'assoc');
                $colors_count = array();
                if($stats){
                    $stats_by_dates = array();
                    foreach($stats as $stat){
                        $stats_by_dates[$stat['date']][$stat['status']] = $stat;
                    }
                    ksort($stats_by_dates);
                    $days_stats = '';
                    $all_stats = array();
                    $colors_stats_qty = array();
                    foreach($stats_by_dates as $date => $statuses){
                        $all_qty = 0;
                        $colors_count = array();
                        foreach($statuses as $status => $data){
                            if(isset($this->all_configs['configs']['order-status'][$status])){
                                $color = $this->all_configs['configs']['order-status'][$status]['color'];
                            }elseif($status == -1){
                                $color = 'FF0000';
                            }else{
                                $color = 'bebebe';
                            }
                            $all_qty += $data['qty_by_status'];
                            $colors_count[$color] = $data['qty_by_status'];
                            $colors_stats_qty[$color] = isset($colors_stats_qty[$color]) ? $colors_stats_qty[$color]+1 : 1;
                        }
                        $st = $this->gen_orders_manager_stats($colors_count, $all_qty, true);
                        foreach($st['data'] as $c => $p){
                            if(!isset($all_stats[$c])){$all_stats[$c] = 0;}
                            $all_stats[$c] += $p;
                        }
                        $days_stats .= '<strong style="display:inline-block;margin:5px 0">'.$date.'</strong> <br>
                                        '.$st['html'].'<br>';
                    }
                    $all_stats_results = array();
                    foreach($all_stats as $c => $s){
                        $all_stats_results[$c] = $s / (isset($colors_stats_qty[$c]) ? $colors_stats_qty[$c] : 1);
                    }
                    $all_stats_html = $this->gen_orders_manager_stats($all_stats_results, 100);
                }else{
                    $days_stats = $all_stats_html = '(нет статистики за выбранный период)';
                }
                $filter_stats = '
                    Средняя статистика за период '.$get_date.'. <br>
                    Cуммируются проценты по дням и делятся на количество дней у которых есть статистика, по каждому статусу отдельно.<br>
                    <div style="margin-top:5px">
                        '.$all_stats_html.'
                    </div>
                    <br>
                    Статистика по дням:<br>
                    '.$days_stats.'
                ';
                $orders = null;
            }else{
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
                        if($color || $class){
                            if($class == 'red-blink'){
                                $color = 'FF0000';
                            }
                            $colors_count[$color] = isset($colors_count[$color]) ? $colors_count[$color]+1 : 1;
                        }
                        $manager_block .= '<div data-o_id="' . $order['id'] . '" onclick="alert_box(this, null, \'display-order\')" class="order-manager ' . $class . '" ' . $style . '>';
                        $manager_block .= '<b>'. $order['id'] . '</b>';
                        $manager_block .= '<br /><span title="' . do_nice_date($order['date_add'], false) . '">' . do_nice_date($order['date_add']) . '</span></div>';
                    }
                    $filter_stats = $this->gen_orders_manager_stats($colors_count).' <br>';
                }else{
                    $manager_block = '<p>Заказов нет</p>';
                }
            }
            // -- фильтры
            
            $orders_html = '
                <div>
                    <form class="form-inline well">
                        '.$this->all_configs['suppliers_orders']->show_filter_service_center().'
                        '.$this->show_filter_manager().'
                        <input type="text" placeholder="Дата" name="date" class="daterangepicker form-control " value="'.$get_date.'" />
                        <input type="submit" class="btn btn-primary" value="Фильтровать">
                        <button type="button" class="btn fullscreen"><i class="fa fa-arrows-alt"></i></button>
                    </form>
                </div>
            ';

            $orders_html .= '
                '.$filter_stats.'
                <br>
                <div id="orders-manager-block">
                    '.$manager_block.'
                </div>
            ';
        }

        return array(
            'html' => $orders_html,
            'functions' => array('reset_multiselect()'),
        );
    }

    function show_product($product)
    {
        $qty = isset($product['count']) ? intval($product['count']) : 1;

        /*$count = '<select id="product_count-' . $product['goods_id'] . '" class="input-mini" onchange="order_products(this, ' . $product['goods_id'] . ', 1)">';
        for ($i = 1; $i <= 99; $i++) {
            $count .= '<option ' . ($i == $qty ? 'selected' : '') . ' value="' . $i . '">' . $i . '</option>';
        }
        $count .= '</select>';*/

        $url = $this->all_configs['prefix'] . 'products/create/' . $product['goods_id'];

        $order_html = '<tr><td><a href="' . $url . '">' . htmlspecialchars($product['title']) . '</a></td>';
        if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders') && $product['type'] == 0) {
            $order_html .= '<td>' . ($product['price'] / 100) . '</td>';
        }
        $order_html .= '<td>';
        //$order_html .= '<td>' . $count . '</td>';
        //$order_html .= '<td><span id="product_sum-' . $product['id'] . '">' . ($product['price'] * $qty / 100) . '</span></td>';
        if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
            $order_html .= '<i title="удалить" class="glyphicon glyphicon-remove remove-product" onclick="order_products(this, ' . $product['goods_id'] . ', ' . $product['id'] . ', 1, 1)"></i>';
        }
        $order_html .= '</td>';
        if ($product['type'] == 0) {
            $msg = '<td colspan="2">';
            if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                $msg .= '<input type="button" data-order_product_id="' . $product['id'] . '" class="btn btn-small" onclick="order_item(this)" value="Заказать" />';
            }
            $msg .= '<td colspan="2"></td>';
            $href = $this->all_configs['prefix'] . 'orders/edit/' . $product['so_id'] . '#create_supplier_order';
            $muted = $product['so_id'] > 0 ? ' <a href="' . $href . '"><small class="muted">№' . $product['so_id'] . '</small></a> ' : '';
            if ($product['item_id'] > 0) {
                $msg = '<td>' . suppliers_order_generate_serial($product, true, true) . ' ' . $muted . '</td><td>';
                if (!strtotime($product['unbind_request']) && $this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                    $msg .= '<i title="отвязать" class="glyphicon glyphicon-minus cursor-pointer" onclick="btn_unbind_request_item_serial(this, \'' . $product['item_id'] . '\')"></i>';
                }
                $msg .= '</td>';
            } else {
                if ($product['count_order'] > 0) {
                    $date_attach = $this->all_configs['db']->query(
                                        "SELECT date_add FROM {orders_suppliers_clients} "
                                       ."WHERE client_order_id = ?i AND supplier_order_id = ?i "
                                         ."AND goods_id = ?i AND order_goods_id = ?i", array(
                                             $product['order_id'],$product['so_id'],
                                             $product['goods_id'],$product['id']
                                         ), 'el');
                    $msg = '<td colspan="2"><div class="center info"><small><span title="' . do_nice_date($date_attach, false) . '">' . do_nice_date($date_attach) . '</span>
                        Отправлен запрос на закупку ' . $muted . ' от <span title="' . do_nice_date($product['date_add'], false) . '">' . do_nice_date($product['date_add']) . '</span></small></div></td>';
                }
                if ($product['supplier'] > 0) {
                    $msg = '<td colspan="2"><div class="center info"><small>Запчасть заказана (заказ поставщику №' . $product['so_id'] . ').
                        Дата поставки <span title="' . do_nice_date($product['date_wait'], false) . '">' . do_nice_date($product['date_wait']) . '</span></small></div></td>';
                }
                if ($product['count_come'] > 0) {
                    $msg = '<td colspan="2"><div class="center info"><small>Запчасть была принята
                        <span title="' . do_nice_date($product['date_come'], false) . '">' . do_nice_date($product['date_come']) . '</span> ' . $muted . '</small></div></td>';
                }
                if ($product['count_debit'] > 0) {
                    $msg = '<td colspan="2"><div class="center info"><small>Ожидание отгрузки запчасти
                        <span title="' . do_nice_date($product['date_debit'], false) . '">' . do_nice_date($product['date_debit']) . '</span> ' . $muted . '</small></div></td>';
                }
                    if ($product['unavailable'] == 1) {
                    $msg = '<td colspan="2"><div class="center black"><small>Запчасть не доступна к заказу ' . $muted . '</small></div></td>';
                }
            }
        } else {
            $msg = '<td colspan="2"></td>';
        }
        $order_html .= $msg . '</tr>';

        return $order_html;
    }

    function genorder($order_id = null)
    {
        $show_btn = $order_id > 0 ? false : true;
        $order_id = $order_id == 0 ? intval($this->all_configs['arrequest'][2]) : $order_id;
        $order_html = '';
        // достаем заказ с прикрепленными к нему товарами
        $order = $this->all_configs['db']->query('SELECT o.*, o.color as o_color, l.location, w.title as wh_title, gr.color, tp.icon,
                u.fio as m_fio, u.phone as m_phone, u.login as m_login, u.email as m_email,
                a.fio as a_fio, a.phone as a_phone, a.login as a_login, a.email as a_email, aw.title as aw_title
                FROM {orders} as o
                LEFT JOIN {users} as u ON u.id=o.manager
                LEFT JOIN {users} as a ON a.id=o.accepter
                LEFT JOIN {warehouses} as w ON o.wh_id=w.id
                LEFT JOIN {warehouses_locations} as l ON o.location_id=l.id
                LEFT JOIN {warehouses} as aw ON o.accept_wh_id=aw.id
                LEFT JOIN {warehouses_groups} as gr ON gr.id=aw.group_id
                LEFT JOIN {warehouses_types} as tp ON tp.id=aw.type_id
                WHERE o.id=?i',
            array($order_id))->row();

        if ($order) {

            /*if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')
                || $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')
                || $this->all_configs['oRole']->hasPrivilege('edit-tradein-orders')
                || $this->all_configs['oRole']->hasPrivilege('show-clients-orders')) {*/
            // только инженер
            $only_engineer = $this->all_configs['oRole']->hasPrivilege('engineer') &&
                !$this->all_configs['oRole']->hasPrivilege('edit-clients-orders') ? true : false;

            $order_html .= '<form method="post" id="order-form" class="container backgroud-white p-sm">';

            $order_html .= '<div class="row-fluid">';

            if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                $print_warranty = print_link($order['id'], 'warranty', '<i class="cursor-pointer fa fa-lock"></i><span>ГАРАН</span>', 'order_print_btn');
                $print_check = print_link($order['id'], 'check', '<i class="cursor-pointer fa fa-list-alt"></i><span>ЧЕК</span>', 'order_print_btn');
            } else {
                $print_check = '';
                $print_warranty = '';
            }
            //$order_html .= '<label><span class="muted">Принят: </span> ';
            //$order_html .= '</label>';
            $order_html .= '<div class="span6"><div class="span12"><div class="span6">';
            $order_html .= '
                <small style="font-size:10px" title="' . do_nice_date($order['date_add'], false) . '">
                    Принят: '.do_nice_date($order['date_add']).'
                </small><br>
                <h3>
                    №'.$order['id'].'
                    <a href="#" class="order_print_btn"><i class="cursor-pointer fa fa-file-text-o"></i><span>КВИТ</span></a>'
                    .$print_check
                    .$print_warranty 
                    .'<a href="#" class="order_print_btn"><i class="cursor-pointer fa fa-users"></i><span>АКТ</span></a>
                </h3>';
            
            
            
            if (!$only_engineer) {
                $icon = '<i class="glyphicon glyphicon-picture cursor-pointer" data-o_id="' . $order['id'] . '" onclick="alert_box(this, null, \'order-gallery\')"></i>';
                $order_html .= '
                    <div class="form-group">
                        <label>
                            <span class="cursor-pointer glyphicon glyphicon-list" onclick="alert_box(this, false, \'changes:update-order-fio\')" data-o_id="' . $order['id'] . '" title="История изменений"></span>
                            Заказчик: 
                        </label> 
                        <input type="text" value="' . htmlspecialchars($order['fio']) . '" name="fio" class="form-control" />
                    </div>
                    <div class="form-group">
                        <label>
                            <span class="cursor-pointer glyphicon glyphicon-list" onclick="alert_box(this, false, \'changes:update-order-phone\')" data-o_id="' . $order['id'] . '" title="История изменений"></span>
                            Телефон:
                        </label> 
                        <input type="text" value="' . htmlspecialchars($order['phone']) . '" name="phone" class="form-control" /></div>
                    <div class="form-group">
                        <label>
                            <span class="cursor-pointer glyphicon glyphicon-list" title="История изменений" data-o_id="' . $order['id'] . '" onclick="alert_box(this, false, \'changes:update-order-category\')"></span>
                            ' . $icon . ' Устройство: 
                        </label> ';
                $order_html .= typeahead($this->all_configs['db'], 'categories-goods', false, $order['category_id'], 4, 'input-medium').'';
                
                $colors_select = '';
                if(is_null($order['o_color'])){
                    $colors_select .= '<option value="-1" selected disabled>Не выбран</option>';
                }
                foreach($this->all_configs['configs']['devices-colors'] as $i=>$c){
                    $colors_select .= '<option'.(!is_null($order['o_color']) && $order['o_color'] == $i ? ' selected' : '').' value="'.$i.'">'.$c.'</option>';
                }
                $order_html .= 
                    '<div class="form-group">
                        <label class="control-label">Цвет: </label>
                        <select class="form-control" name="color">'.$colors_select.'</select>
                    </div>
                ';
                
                //$order_html .= typeahead($this->all_configs['db'], 'goods-goods', false, $order['title'], 8, 'input-medium', 'input-medium', 'order_products');
                $order_html .= /*htmlspecialchars($order['title']) . */' ' . htmlspecialchars($order['note']) . '</div>';
            }
            // не продажа
            if ($order['type'] != 3) {
                if (!$only_engineer) {
                    $order_html .= '<div class="form-group"><label><span class="cursor-pointer glyphicon glyphicon-list" onclick="alert_box(this, false, \'changes:update-order-serial\')" data-o_id="' . $order['id'] . '" title="История изменений"></span>';
                    $order_html .= ' S/N: </label> <input type="text" value="' . htmlspecialchars($order['serial']) . '" name="serial" class="form-control" /></div>';
                }
                $parts = array();
                if($order['battery']){
                    $parts[] = 'Аккумулятор';
                }
                if($order['charger']){
                    $parts[] = 'Зарядное устройство/кабель';
                }
                if($order['cover']){
                    $parts[] = 'Задняя крышка';
                }
                if($order['box']){
                    $parts[] = 'Коробка';
                }
                $order_html .= 
                    '<div class="form-group"><label>Комлектация:</label><br>'.
                    implode(', ', $parts).'</div>';
            }

            $product_total = 0;

            // не продажа
            if ($order['type'] != 3) {
                switch($order['repair']){
                    case 0: $order_type = 'Платный'; break;
                    case 1: $order_type = 'Гарантийный'; break;
                    case 2: $order_type = 'Доработка'; break;
                }
                $order_html .= '
                    <div class="form-group">
                        <label>Вид ремонта:</label>
                        '.$order_type.'
                    </div>
                ';
                $order_html .= '<div class="form-group"><label>Сроки:</label> ' . ($order['urgent'] == 1 ? 'Срочный' : 'Не срочный') . '</div>';
                //$order_html .= '<label><span class="muted">Оплата: </span> ?' . '</label>';
                $order_html .= '<div class="form-group"><label><span class="cursor-pointer glyphicon glyphicon-list" title="История изменений" data-o_id="' . $order['id'] . '" onclick="alert_box(this, false, \'changes:update-order-defect\')"></span>';
                $order_html .= ' Неисправность со слов клиента: </label> ';// . htmlspecialchars($order['defect']) . '</label>';
                $order_html .= '<textarea class="form-control" name="defect">' . htmlspecialchars($order['defect']) . '</textarea></div>';
                $order_html .= '<div class="form-group"><label><span class="cursor-pointer glyphicon glyphicon-list" title="История изменений" data-o_id="' . $order['id'] . '" onclick="alert_box(this, false, \'changes:update-order-comment\')"></span>';
                $order_html .= ' Примечание/Внешний вид: </label> ';// . htmlspecialchars($order['comment']) . '</label>';
                $order_html .= '<textarea class="form-control" name="comment">' . htmlspecialchars($order['comment']) . '</textarea></div>';
                $order_html .= '<div class="form-group"><label>Ориентировочная дата готовности: </label> ';
                $order_html .= '<span title="' . do_nice_date($order['date_readiness'], false) . '">' . do_nice_date($order['date_readiness']) . '</span></div>';
                if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                    $order_html .= '<div class="form-group"><label>Ориентировочная стоимость: </label> ' . ($order['approximate_cost'] / 100) . ' '.viewCurrency().'</div>';
                }
            }
            $order_html .= '</div><div class="span6">';
            
            $color = preg_match('/^#[a-f0-9]{6}$/i', trim($order['color'])) ? trim($order['color']) : '#000000';
            $accepted = mb_strlen($order['courier'], 'UTF-8') > 0 ? '<i style="color:' . $color . ';" title="Курьер забрал устройство у клиента" class="fa fa-truck"></i> ' : '';
            $accepted .= $order['np_accept'] == 1 ? 
                            '<i title="Принято через почту" class="fa fa-suitcase text-danger"></i> ' :
                            '<i style="color:' . $color . ';" title="Принято в сервисном центре" class="' . htmlspecialchars($order['icon']) . '"></i> ';
            $accepted .= $order['aw_title'].' ';
            $order_html .= '<div class="form-group center"><br>' . $accepted . timerout($order['id'], true) . '</div>';
            $order_html .= '<div class="form-group"><label><span onclick="alert_box(this, false, \'stock_moves-order\')" data-o_id="' . $order['id'] . '" class="cursor-pointer glyphicon glyphicon-list" title="История перемещений"></span>';
            $order_html .= ' Локации: </label> ' . htmlspecialchars($order['wh_title']) . ' ' . htmlspecialchars($order['location']);
            $order_html .= ' <i title="Переместить заказ" onclick="alert_box(this, false, \'stock_move-order\', undefined, undefined, \'messages.php\')" data-o_id="' . $order['id'] . '" class="glyphicon glyphicon-move cursor-pointer"></i></div>';

            $order_html .= '<div class="form-group"><label>Приемщик:</label> ' . get_user_name($order, 'a_') . '</div>';
            // не продажа
            if ($order['type'] != 3) {
                if ($order['manager'] == 0 && $this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                    $manager = '<input type="submit" name="accept-manager" class="btn btn-default btn-xs" value="Взять заказ" />'
                              .'<input type="hidden" name="id" value="' . $order['id'] . '" />';
                } else {
                    $manager = get_user_name($order, 'm_');
                }
                $order_html .= '<div class="form-group"><label>Менеджер: </label> ' . $manager . '</div>';
            }

            $style = isset($this->all_configs['configs']['order-status'][$order['status']]) ? 'style="color:#' . htmlspecialchars($this->all_configs['configs']['order-status'][$order['status']]['color']) . '"' : '';
            $order_html .= '<div class="form-group"><label><span ' . $style . '>';
            $order_html .= '<span class="cursor-pointer glyphicon glyphicon-list" title="История перемещений" data-o_id="' . $order['id'] . '" onclick="alert_box(this, false, \'order-statuses\')"></span>';
            $order_html .= ' Статус: </label> ' . $this->all_configs['chains']->order_status(intval($order['status'])) . '</div>';
            //$order_html .= '<label><span class="muted">Партнер: </span> ' . '</label>';
            // не продажа
            if ($order['type'] != 3) {
                // инженеры
                $engineers = $this->all_configs['oRole']->get_users_by_permissions('engineer');
                $html = '<select class="form-control" name="engineer"><option value="">Выбрать</option>';
                if ($engineers) {
                    foreach ($engineers as $engineer) {
                        $selected = $engineer['id'] == $order['engineer'] ? 'selected' : '';
                        $html .= '<option ' . $selected . ' value="' . $engineer['id'] . '">' . get_user_name($engineer) . '</option>';
                    }
                }
                $html .= '</select>';
                $order_html .= '<div class="form-group"><label><span class="cursor-pointer glyphicon glyphicon-list" title="История изменений" data-o_id="' . $order['id'] . '" onclick="alert_box(this, false, \'changes:update-order-engineer\')"></span>';
                $order_html .= ' Инженер: </label> ' . $html . '</div>';
                $order_html .= '<div class="form-group">'
                                  .'<span style="margin:4px 10px 0 0" class="pull-left cursor-pointer glyphicon glyphicon-list muted" onclick="alert_box(this, false, \'changes:update-order-client_took\')" data-o_id="' . $order['id'] . '" title="История изменений"></span>'
                                  .'<label class="checkbox-inline">'
                                      .'<input type="checkbox" value="1" ' . ($order['client_took'] == 1 ? 'checked' : '') . ' name="client_took"> Устройство у клиента'
                                  .'</label>'
                              .'</div>';
                $onclick = 'if ($(this).prop(\'checked\')){$(\'.replacement_fund\').val(\'\');$(\'.replacement_fund\').prop(\'disabled\', false);$(\'.replacement_fund\').show();$(this).parent().parent().addClass(\'warning\');}else{$(\'.replacement_fund\').hide();$(this).parent().parent().removeClass(\'warning\');}';
                $order_html .= '<div class="form-group' . ($order['is_replacement_fund'] == 1 ? ' warning' : '') . '">'
                                  .'<span style="margin:4px 10px 0 0" class="pull-left cursor-pointer glyphicon glyphicon-list muted" onclick="alert_box(this, false, \'changes:update-order-replacement_fund\')" data-o_id="' . $order['id'] . '" title="История изменений"></span>'
                                  .'<label class="checkbox-inline">  '
                                      .'<input onclick="' . $onclick . '" type="checkbox" value="1" ' . ($order['is_replacement_fund'] == 1 ? 'checked' : '') . ' name="is_replacement_fund" />'
                                      .'Подменный фонд'
                                  .'</label> '
                                  .'<input ' . ($order['is_replacement_fund'] == 1 ? 'disabled' : 'style="display:none;"') . ' type="text" placeholder="Модель, серийный номер" class="form-control replacement_fund" value="' . htmlspecialchars($order['replacement_fund']) . '" name="replacement_fund" />'
                              .'</div>';
                //$order_html .= '<label><span class="muted">Уведомлять клиента по смс о статусе ремонта: </span> ';
                //$order_html .= '<input type="checkbox" value="1" ' . ($order['notify'] == 1 ? 'checked' : '') . ' name="notify" /></label>';
                $order_html .= '<div class="form-group">'
                                  .'<label class="checkbox-inline">'
                                      .'<input type="checkbox" value="1" ' . ($order['nonconsent'] == 1 ? 'checked' : '') . ' name="nonconsent" />'
                                      .'Можно пускать в работу без согласования'
                                  .'</label>'
                              .'</div>';
                $order_html .= '<div class="form-group">'
                                  .'<label class="checkbox-inline">'
                                      .'<input type="checkbox" value="1" ' . ($order['is_waiting'] == 1 ? 'checked' : '') . ' name="is_waiting" />'
                                      .'Клиент готов ждать 2-3 недели запчасть'
                                  .'</label>'
                              .'</div>';

                if ($order['return_id'] > 0 || $this->all_configs['oRole']->hasPrivilege('edit_return_id')) {
                    $order_html .= '<div class="form-group">'
                                  .'<label>Номер возврата: </label> ';
                    if ($this->all_configs['oRole']->hasPrivilege('edit_return_id')) {
                        $order_html .= $order['id'] . '-' . '<input type="text" value="' . $order['return_id'] . '" name="return_id" class="form-control" />';
                    } else {
                        $order_html .= $order['id'] . '-' . $order['return_id'];
                    }
                    $order_html .= '</div>';
                }
                $order_html .= '<div class="form-group">'
                                  .'<span class="cursor-pointer glyphicon glyphicon-list muted" onclick="alert_box(this, false, \'changes:update-order-warranty\')" data-o_id="' . $order['id'] . '" title="История изменений"></span> '
                                  .'<label>Гарантия: </label> '
                                  .'<div class="input-group"> '
                                  .'<select class="form-control" name="warranty"><option value="">Без гарантии</option>';
                $order_warranties = isset($this->all_configs['settings']['order_warranties']) ? explode(',', $this->all_configs['settings']['order_warranties']) : array();
                foreach ($order_warranties as $warranty) {
                    $order_html .= '<option ' . ($order['warranty'] == intval($warranty) ? 'selected' : '') . ' value="' . intval($warranty) . '">' . intval($warranty) . '</option>';
                }
                $order_html .= '</select><div class="input-group-addon">мес.</div></div></div>';
            }
            
            // заказ на основе заявки
            $request = get_service('crm/requests')->get_request_by_order($order['id']);
            if($request){
                $order_html .= '<div class="from-group">'
                                . 'Заявка №'.$request['id'].' '.do_nice_date($request['date'], true).'<br> '
                                . 'Звонок №'.$request['call_id'].' '.do_nice_date($request['call_date'], true).' '
                                .($request['code'] ? '<br>Код: '.$request['code'] : '').'  '
                                .($request['rf_name'] ? '<br>Источник: '.$request['rf_name'].'' : '').'  '
                            . '</div>';
            }else{
                $priv = $this->all_configs['oRole']->hasPrivilege('edit-clients-orders');
                // если не на основе заявки, то выводим данные о канале и коде
                $order_html .= '<div class="from-group">'
                                    .'<span class="cursor-pointer glyphicon glyphicon-list" onclick="alert_box(this, false, \'changes:update-order-code\')" data-o_id="' . $order['id'] . '" title="История изменений"></span>'
                                    .' <label>Код скидки:</label> '
                                    .'<input'.(!$priv ? ' disabled' : '').' class="form-control" type="text" name="code" value="'.htmlspecialchars($order['code']).'"><br>'
                              .'</div>'
                              .'<div class="from-group">'
                                    .'<span class="cursor-pointer glyphicon glyphicon-list" onclick="alert_box(this, false, \'changes:update-order-referer_id\')" data-o_id="' . $order['id'] . '" title="История изменений"></span>'
                                    .' <label>Источник:</label> '
                                    .get_service('crm/calls')->get_referers_list($order['referer_id'], '', !$priv).'<br>'
                              .'</div>';
            }
            $order_html .= '</div></div>';
            if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')/* && $_SESSION['id'] == $order['manager']*/) {
                $order_html .= '<div class="row-fluid"><div class="span6">';
                $hide = in_array($order['status'], $this->all_configs['configs']['order-status-issue-btn']) ? '' : 'style="display:none;"';
                $status = $order['status'] == $this->all_configs['configs']['order-status-ready'] ? $this->all_configs['configs']['order-status-issued']
                    : ($order['status'] == $this->all_configs['configs']['order-status-refused'] || $order['status'] == $this->all_configs['configs']['order-status-unrepairable']
                        ? $this->all_configs['configs']['order-status-nowork'] : $order['status']);
                if ($show_btn == true) {
                    $order_html .= '<input id="close-order" ' . $hide . ' class="btn btn-success" onclick="issue_order(this)" data-status="' . $status . '" type="button" value="Выдать" />';
                    $order_html .= ' <input id="update-order" class="btn btn-info" onclick="update_order(this)" type="button" value="Сохранить" />';
                }
                $order_html .= '</div><div class="span6"><div class="from-control">';
                $order_html .= ' <span class="cursor-pointer glyphicon glyphicon-list" onclick="alert_box(this, false, \'changes:update-order-sum\')" data-o_id="' . $order['id'] . '" title="История изменений"></span>';
                $order_html .= ' 
                    <label>Стоимость ремонта: </label>
                    <div class="input-group">
                        <input type="text" id="order-total" class="form-control" value="' . ($order['sum'] / 100) . '" name="sum" />
                        <div class="input-group-addon">'.viewCurrency().'</div>
                    </div>';
                $order_html .= '<span class="text-success">Оплачено: ' . ($order['sum_paid'] / 100) . ' '.viewCurrency().' (из них предоплата ' . ($order['prepay'] / 100) . ' ' . htmlspecialchars($order['prepay_comment']) . ')</span>';
                $order_html .= ' <small id="product-total">' . ($product_total / 100) . '</small></div>';
                $order_html .= '<link type="text/css" rel="stylesheet" href="'.$this->all_configs['prefix'].'modules/accountings/css/main.css?1">';
                if($this->all_configs['oRole']->hasPrivilege('accounting')){
                    if (intval($order['prepay']) > 0 && intval($order['prepay']) > intval($order['sum_paid'])) {
                        $onclick = 'pay_client_order(this, 2, ' . $order['id'] . ', 0, \'prepay\')';
                        $order_html .= '<input type="button" class="btn btn-success btn-xs" value="Принять предоплату" onclick="' . $onclick . '" />';
                    } elseif (intval($order['sum']) > intval($order['sum_paid'])) {
                        $onclick = 'pay_client_order(this, 2, ' . $order['id'] . ')';
                        $order_html .= '<input type="button" class="btn btn-success btn-xs" value="Принять оплату" onclick="' . $onclick . '" />';
                    }
                }
                $order_html .= '<input id="send-sms" data-o_id="' . $order['id'] . '" onclick="alert_box(this, false, \'sms-form\')" class="hidden" type="button" />';
                $order_html .= '</div></div>';

            } elseif ($only_engineer && $order['sum'] == $order['sum_paid'] && $order['sum'] > 0) {
                $order_html .= '<b class="text-success">Заказ клиентом оплачен</b>';
            }
            $order_html .= '</div>';

            $order_html .= '<div class="span6">';

            $order_html .= '<div class="row-fluid well well-small">';
            $public_html = $private_html = '<div class="span6"><div class="div-table div-table-scroll"><div class="div-thead">
                <div class="div-table-row"><div class="div-table-col span3" align="center">Дата</div><div class="div-table-col span9">';
            $public_html .= 'Публичный комментарий</div></div></div><div class="div-tbody">';
            $private_html .= 'Скрытый комментарий</div></div></div><div class="div-tbody">';
            // достаем комментарии к заказу
            $comments_public = (array)$this->all_configs['db']->query('SELECT oc.date_add, oc.text, u.fio, u.phone, u.login, u.email, oc.id
                FROM {orders_comments} as oc LEFT JOIN {users} as u ON u.id=oc.user_id
                WHERE oc.order_id=?i AND oc.private=0 ORDER BY oc.date_add DESC', array($order['id']))->assoc();
            $comments_private = (array)$this->all_configs['db']->query('SELECT oc.date_add, oc.text, u.fio, u.phone, u.login, u.email, oc.id
                FROM {orders_comments} as oc LEFT JOIN {users} as u ON u.id=oc.user_id
                WHERE oc.order_id=?i AND oc.private=1 ORDER BY oc.date_add DESC', array($order['id']))->assoc();
            // перебор комментарий
            if (count($comments_public) > 0 || count($comments_private) > 0) {
                reset($comments_public);reset($comments_private);
                for ($i = 0; $i < count(max($comments_public, $comments_private)); $i++) {
                    $comment_public = current($comments_public);
                    $comment_private = current($comments_private);

                    if ($comment_public) {
                        $public_html .= '<div class="div-table-row"><div class="div-table-col span3"><small><span title="' . do_nice_date($comment_public['date_add'], false) . '">' . do_nice_date($comment_public['date_add']) . '</span></small></div>';
                        $public_html .= '<div class="div-table-col span9"><small>' . htmlspecialchars($comment_public['text']);
                        //$public_html .= $this->all_configs['oRole']->hasPrivilege('site-administration') ? '<span class="comment_user muted">' . get_user_name($comment_public) . '</span>' : '';
                        $public_html .= '<span class="comment_user muted">' . get_user_name($comment_public) . '</span></small></div></div>';
                        //<i onclick="remove_comment(this, ' . $comment_public['id'] . ')" class="icon-remove cursor-pointer"></i>
                    }
                    if ($comment_private) {
                        $private_html .= '<div class="div-table-row"><div class="div-table-col span3"><small><span title="' . do_nice_date($comment_private['date_add'], false) . '">' . do_nice_date($comment_private['date_add']) . '</span></small></div>';
                        $private_html .= '<div class="div-table-col span9"><small>' . htmlspecialchars($comment_private['text']);
                        //$private_html .= $this->all_configs['oRole']->hasPrivilege('site-administration') ? '<span class="comment_user muted">' . get_user_name($comment_private) . '</span>' : '';
                        $private_html .= '<span class="comment_user muted">' . get_user_name($comment_private) . '</span></small></div></div>';
                        //<i onclick="remove_comment(this, ' . $comment_private['id'] . ')" class="icon-remove cursor-pointer"></i>
                    }
                    next($comments_public);next($comments_private);
                }
            }
            $public_html .= '</div>';
            $private_html .= '</div>';
            if ($this->all_configs['oRole']->hasPrivilege('add-comment-to-clients-orders')) {
                if (!$only_engineer) {
                    $public_html .= '<div class="div-tfoot"><div class="div-table-row"><div class="div-table-col span12"><textarea class="form-control" name="public_comment"></textarea></div></div>';
                    $public_html .= '<div class="div-table-row"><div class="div-table-col span12"><input name="add_public_comment" class="btn" value="Добавить" type="submit"></div></div></div>';
                }
                $private_html .= '<div class="div-tfoot"><div class="div-table-row"><div class="div-table-col span12"><textarea class="form-control" name="private_comment"></textarea></div></div>';
                $private_html .= '<div class="div-table-row"><div class="div-table-col span12"><input name="add_private_comment" class="btn" value="Добавить" type="submit"></div></div></div>';
            }
            $public_html .= '</div></div>';
            $private_html .= '</div></div>';
            $order_html .= $public_html . $private_html;
            $order_html .= '</div>';

            $order_html .= '<div class="well well-small"><h4>Запчасти</h4>';//<td>Стоимость</td>
            $order_html .= '<table class="table"><thead><tr><td>Наименование</td>';
            if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                $order_html .= '<td>Цена</td>';
            }
            $order_html .= '<td></td><td></td><td></td></tr></thead><tbody id="goods-table">';
            $goods = $this->all_configs['manageModel']->order_goods($order['id'], 0);
            if ($goods) {
                foreach ($goods as $product) {
                    $product_total += $product['price'] * $product['count'];
                    $order_html .= $this->show_product($product);
                }
            }
            $order_html .= '</tbody></table>';
            // не продажа
            if ($order['type'] != 3) {
                if (!$only_engineer) {
                    $order_html .= '<div class="form-group"><label>Выберите запчасть</label>';
                    $order_html .= typeahead($this->all_configs['db'], 'goods-goods', true, 0, 6, 'input-xlarge', 'input-medium', 'order_products').'</div><br>';
                }
                $order_html .= '<hr/><h4>Работы</h4>';
                $order_html .= '<table class="table"><thead><tr><td>Наименование</td>';
                /*if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                    $order_html .= '<td>Цена</td>';
                }*/
                $order_html .= '<td></td><td></td></tr></thead><tbody id="service-table">';
                $goods = $this->all_configs['manageModel']->order_goods($order['id'], 1);
                if ($goods) {
                    foreach ($goods as $product) {
                        $product_total += $product['price'] * $product['count'];
                        $order_html .= $this->show_product($product);
                    }
                }
                $order_html .= '</tbody></table>';
                $order_html .= '<div class="form-group"><label>Укажите работу</label>';
                $order_html .= typeahead($this->all_configs['db'], 'goods-service', true, 0, 7, 'input-xlarge', 'input-medium', 'order_products').'</div><br>';
            }
            $order_html .= '</div>';

            $order_html .= '</div>';

            $order_html .= '</div>';

            $order_html .= '</form>';

        } else {
            $order_html .= '<div class="span3"></div><div class="span9"><p class="text-danger">Заказ №' . $this->all_configs['arrequest'][2] . ' не найден</p></div>';
        }
        $order_html .= $this->all_configs['chains']->append_js();
        $order_html .= $this->all_configs['suppliers_orders']->append_js();

        return $order_html;
    }

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

    function ajax()
    {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $mod_id = $this->all_configs['configs']['orders-manage-page'];
        $act = isset($_GET['act']) ? trim($_GET['act']) : '';
        $data = array('state' => false);

        // проверка доступа
        if ($this->can_show_module() == false) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Нет прав', 'state' => false));
            exit;
        }

        // грузим табу
        if ($act == 'tab-load') {
            if (isset($_POST['tab']) && !empty($_POST['tab'])) {
                //$this->preload();
                header("Content-Type: application/json; charset=UTF-8");

                if (method_exists($this, $_POST['tab'])) {
                    $function = call_user_func_array(
                        array($this, $_POST['tab']),
                        array((isset($_POST['hashs']) && mb_strlen(trim($_POST['hashs'], 'UTF-8')) > 0) ? trim($_POST['hashs']) : null)
                    );
                    if (!isset($function['debug'])) {$function['debug'] = '';}
                    $return = array('html' => $function['html'], 
                        'state' => true, 
                        'functions' => $function['functions'], 
                        'debug' => $function['debug']
                    );
                    if (isset($function['menu'])) {
                        $return['menu'] = $function['menu'];
                    }
                    echo json_encode($return);
                } else {
                    echo json_encode(array('message' => 'Не найдено', 'state' => false));
                }
                exit;
            }
        }

        // вывод заказа
        if ($act == 'display-order') {
            $data['state'] = true;
            $data['content'] = '<br />' . $this->genorder($_POST['object_id']);
            $data['width'] = true;
        }

        // история статусов заказа
        if ($act == 'order-statuses') {
            $data['state'] = true;
            $data['content'] = 'Истоия изменения статусов не найдена';
            $statuses = $this->all_configs['db']->query('SELECT s.status, s.date, u.* FROM {order_status} as s
                LEFT JOIN {users} as u ON u.id=s.user_id WHERE s.order_id=?i ORDER BY `date` DESC',
                array(isset($_POST['object_id']) ? $_POST['object_id'] : 0))->assoc();
            if ($statuses) {
                $sts = $this->all_configs['configs']['order-status'];
                $data['content'] = '<table class="table"><thead><tr><td>Статус</td><td>Автор</td><td>Дата</td></tr></thead><tbody>';
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
                $data['btns'] = '<input type="button" class="btn btn-info btn-show-webcam" value="Открыть вебкамеру">';
                $data['btns'] .= '<input type="button" style="display: none;" class="btn btn-info btn-capture" value="Сфотографировать" data-loading-text="Фотографирование...">';
                $data['btns'] .= '<input data-order_id="' . $order_id . '" type="button" style="display: none;" class="btn btn-success" id="btn-upload-and-crop" value="Загрузить и прикрепить">';
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
                        $data['msg'] = isset($data['msg']) ? $data['msg'] : 'Произошла ошибка при сохранении';
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
                $data['content'] .= '<div class="form-group"><label>Номер телефона: </label><div class="controls">';
                $data['content'] .= '<input class="form-control" name="phone" type="text" value="' . htmlspecialchars($order['phone']) . '" /></div></div>';
                $data['content'] .= '<div class="form-group"><label class="control-label">Текст: </label><div class="controls">';
                $data['content'] .= '<textarea class="form-control show-length" maxlength="69" name="text">Ваше устройство готово. Стоимость ремонта: ' . ($order['sum'] / 100) . ' '.viewCurrency().'.</textarea></div></div>';
                $data['content'] .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';
                $data['content'] .= '</form>';
                $data['btns'] = '<input type="button" onclick="send_sms(this)" class="btn" value="Отправить" />';
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
            if ($data['state'] == true) {
                //$data['location'] = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '#create_supplier_order';
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
                    $data['msg'] = 'Укажите дату';
                }
            } else {
                $data['msg'] = 'Нет прав';
            }
        }

        // создаем заказ поставщику
        if ($act == 'create-supplier-order') {
            $data = $this->all_configs['suppliers_orders']->create_order($mod_id, $_POST);
            if ($data['state'] == true && $data['id'] > 0) {
                $data['hash'] = '#show_suppliers_orders';
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

            if ($data['state'] == true && (!$this->all_configs['oRole']->hasPrivilege('edit-clients-orders') || !$order/* || $order['manager'] != $_SESSION['id']*/)) {
                //$data['msg'] = 'Вы не являетесь менеджером этого заказа';
                $data['msg'] = 'У Вас нет прав';
                $data['state'] = false;
            }
            if ($data['state'] == true && !$order) {
                $data['msg'] = 'Заказ не найден';
                $data['state'] = false;
            }
            if ($data['state'] == true && isset($_POST['is_replacement_fund']) && isset($_POST['replacement_fund']) && mb_strlen(trim($_POST['replacement_fund']), 'utf-8') == 0) {
                $data['msg'] = 'Укажите подменный фонд';
                $data['state'] = false;
            }
            if ($data['state'] == true && isset($_POST['categories-goods']) && intval($_POST['categories-goods']) == 0) {
                $data['msg'] = 'Укажите устройство';
                $data['state'] = false;
            }
            
            if ($data['state'] == true) {
                // меняем статус
                $response = update_order_status($order, $_POST['status']);
                if (!isset($response['state']) || $response['state'] == false) {
                    $data['state'] = false;
                    $_POST['status'] = $order['status'];
                    $data['msg'] = isset($response['msg']) ? $response['msg'] : 'Статус не изменился';
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
                        array($user_id, 'update-order-client_took', $mod_id, $this->all_configs['arrequest'][2], isset($_POST['client_took']) ? 'Устройство у клиента' : 'Устройство на складе', isset($_POST['client_took']) ? 1 : 0));
                }

                // смена инженера
                if (isset($_POST['engineer']) && intval($order['engineer']) != intval($_POST['engineer'])) {
                    $user = $this->all_configs['db']->query('SELECT fio, email, login, phone FROM {users} WHERE id=?i',
                        array($_POST['engineer']))->row();
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i, `change`=?, change_id=?i',
                        array($user_id, 'update-order-engineer', $mod_id, $this->all_configs['arrequest'][2], get_user_name($user), $_POST['engineer']));
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
                $order['sum'] = isset($_POST['sum']) ? $_POST['sum'] * 100 : $order['sum'];
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

        // создать заказ
        if ($act == 'add-order') {
            $data = $this->all_configs['chains']->add_order($_POST, $mod_id);
        }
        
        // создать заказ
        if ($act == 'sale-order') {
            $data = $this->all_configs['chains']->sold_items($_POST, $mod_id);
        }

        preg_match('/changes:(.+)/', $act, $arr);//print_r($arr);
        // история изменений инженера
        if (count($arr) == 2 && isset($arr[1])) {
            $data['state'] = true;
            $data['content'] = 'История изменений не найдена';

            if (isset($_POST['object_id'])) {
                $changes = $this->all_configs['db']->query(
                    'SELECT u.login, u.email, u.fio, u.phone, ch.change, ch.date_add FROM {changes} as ch
                     LEFT JOIN {users} as u ON u.id=ch.user_id WHERE ch.object_id=?i AND ch.map_id=?i AND work=? ORDER BY ch.date_add DESC',
                    array($_POST['object_id'], $mod_id, trim($arr[1])))->assoc();
                if ($changes) {
                    $data['content'] = '<table class="table"><thead><tr><td>Менеджер</td><td>Дата</td><td>Изменение</td></tr></thead><tbody>';
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
            $data['content'] .= '<div class="form-group"><label>Электронная почта: </label>';
            $data['content'] .= '<input type="text" class="form-control" name="email" value="" placeholder="Электронная почта" /></div>';
            $data['content'] .= '<div class="form-group"><label class="control-label">Ф.И.О: </label>';
            $data['content'] .= '<input class="form-control" type="text" name="fio" value="" placeholder="Ф.И.О" /></div>';
            $data['content'] .= '<div class="form-group"><label class="control-label">Телефон: </label>';
            $data['content'] .= '<input class="form-control" type="text" name="phone" value="" placeholder="Телефон" /></div>';
            $data['content'] .= '</form>';
            $data['btns'] = '<input class="btn btn-success" onclick="create_client(this)" type="button" value="Создать" />';
        }

        // добавление нового клиента
        if ($act =='add_user') {
            if (!$this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => 'У Вас недостаточно прав', 'error' => true));
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
            $data['title'] = 'Важная информация';
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
                $data['msg'] = 'Изделие не найдено';
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
            $data['html'] = $this->all_configs['suppliers_orders']->create_order_block(1, null, false, $counter);
        }

        if ($act == 'client-bind') {

            if (!$this->all_configs['oRole']->hasPrivilege('edit-clients-orders')) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => 'У Вас недостаточно прав', 'error' => true));
                exit;
            }
            if ( !isset($_POST['user_id']) || $_POST['user_id'] < 1 || !isset($_POST['order_id']) || $_POST['order_id'] < 1 ) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => 'Такого клиента не существует', 'error'=>true));
                exit;
            }

            $u = $this->all_configs['db']->query('SELECT email, id FROM {clients}
                WHERE id=?i', array($_POST['user_id']))->row();

            $o = $this->all_configs['db']->query('SELECT email, user_id, id FROM {orders}
                WHERE id=?i', array($_POST['order_id']))->row();

            if ( !$u || !$o || $u['email'] != $o['email'] ) {
                header("Content-Type: application/json; charset=UTF-8");
                echo json_encode(array('message' => 'Такого клиента не существует', 'error'=>true));
                exit;
            }
            $this->all_configs['db']->query('UPDATE {orders} SET user_id=?i WHERE id=?i', array($_POST['user_id'], $_POST['order_id']));
            $data['message'] = 'Заказ успешно привязан';
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
        }

        // запрос на отвязку серийного номера
        if ($act == 'unbind-request-item-serial') {
            $data = $this->all_configs['chains']->unbind_request($mod_id, $_POST);
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


}