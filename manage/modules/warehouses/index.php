<?php

require_once __DIR__ . '/../../Core/View.php';
require_once __DIR__ . '/../../Core/FlashMessage.php';

$modulename[40] = 'warehouses';
$modulemenu[40] = l('Склады');
$moduleactive[40] = !$ifauth['is_2'];

class warehouses
{
    /** @var View */
    protected $view;
    private $mod_submenu;
    protected $warehouses;
    protected $all_configs;
    protected $errors;

    public $count_on_page;

    /**
     * warehouses constructor.
     * @param $all_configs
     */
    function __construct(&$all_configs)
    {
        $this->mod_submenu = self::get_submenu();
        $this->all_configs = $all_configs;
        $this->count_on_page = count_on_page();
        $this->view = new View($all_configs);

        global $input_html;

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }

        if ($this->can_show_module() == false) {
            return $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p  class="text-error">' . l('У Вас не достаточно прав') .'</p></div>';
        }

        // если отправлена форма
        if (!empty($_POST) && count($_POST) > 0)
            $this->errors = $this->check_post($_POST);

        //if ($ifauth['is_2']) return false;

        $input_html['mcontent'] = $this->gencontent();

    }

    /**
     * @return bool
     */
    function can_show_module()
    {
        if (($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders')
                /*|| $this->all_configs['oRole']->hasPrivilege('logistics')*/
                || $this->all_configs['oRole']->hasPrivilege('scanner-moves'))
            && $this->all_configs['configs']['erp-use'] == true) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $post
     * @throws Exception
     */
    function check_post($post)
    {
        $mod_id = $this->all_configs['configs']['warehouses-manage-page'];
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';


        //echo '<pre>';print_r($post);exit;

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

            if (isset($post['goods']) && $post['goods'] > 0) {
                // фильтр по товару
                if (!empty($url))
                    $url .= '&';
                $url .= 'by_gid=' . intval($post['goods']);
            }

            if (isset($post['managers']) && !empty($post['managers'])) {
                // фильтр по менеджерам
                if (!empty($url))
                    $url .= '&';
                $url .= 'mg=' . implode(',', $post['managers']);
            }

            if (isset($post['suppliers']) && !empty($post['suppliers'])) {
                // фильтр по поставщикам
                if (!empty($url))
                    $url .= '&';
                $url .= 'sp=' . implode(',', $post['suppliers']);
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

            if (isset($post['so_st']) && $post['so_st'] > 0) {
                // фильтр клиенту/заказу
                if (!empty($url))
                    $url .= '&';
                $url .= 'so_st=' . $post['so_st'];
            }

            if (isset($post['my']) && !empty($post['my'])) {
                // фильтр клиенту/заказу
                if (!empty($url))
                    $url .= '&';
                $url .= 'my=1';
            }

            $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . $url);
            header('Location: ' . $url);
            exit;
        }

        // фильтруем
        if (isset($post['filters'])) {

            $url = '';

            if (isset($post['noitems'])) {
                // фильтр по без "изделий нет"
                if (!empty($url))
                    $url .= '&';
                $url .= 'noi=1';
            }

            if (isset($post['goods']) && $post['goods'] > 0) {
                // фильтр по товару
                if (!empty($url))
                    $url .= '&';
                $url .= 'by_gid=' . intval($post['goods']);
            }

            if (isset($post['clients']) && $post['clients'] > 0) {
                // фильтр клиенту/заказу
                if (!empty($url))
                    $url .= '&';
                $url .= 'c_id=' . intval($post['clients']);
            }

            if (isset($post['client-order-number']) && $post['client-order-number'] > 0) {
                // фильтр клиенту/заказу
                if (!empty($url))
                    $url .= '&';
                $url .= 'con=' . intval($post['client-order-number']);
            }

            if (isset($post['serial']) && !empty($post['serial'])) {
                // фильтр клиенту/заказу
                if (!empty($url))
                    $url .= '&';
                $url .= 'serial=' . urlencode(trim($post['serial']));
            }

            $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . $url);
            header('Location: ' . $url);
            exit;
        }

        // привязка администратора к складу
        if (isset($post['set-warehouses_users'])) {

            $this->all_configs['db']->query('TRUNCATE TABLE {warehouses_users}');

            $values = array();
            if (isset($post['locations']) && is_array($post['locations'])) {
                foreach ($post['locations'] as $user_id=>$location_id) {
                    if (intval($location_id) > 0 && $user_id > 0) {
                        $wh_id = $this->all_configs['db']->query(
                            'SELECT wh_id FROM {warehouses_locations} WHERE id=?i', array($location_id))->el();

                        if (intval($wh_id) > 0) {
                            $values[] = array(intval($wh_id), intval($location_id), $user_id, 1);
                        }
                    }
                }
            }
            if (count($values) > 0) {
                $this->all_configs['db']->query(
                    'INSERT IGNORE INTO {warehouses_users} (wh_id, location_id, user_id, main) VALUES ?v', array($values));
            }
            $values = array();
            if (isset($post['warehouses_users']) && is_array($post['warehouses_users'])) {
                foreach ($post['warehouses_users'] as $user_id=>$warehouses) {
                    if ($user_id > 0 && is_array($warehouses)) {
                        foreach ($warehouses as $wh_id) {
                            if (intval($wh_id) > 0) {
                                $values[] = array(intval($wh_id), $user_id, 0);
                            }
                        }
                    }
                }
            }
            if (count($values) > 0) {
                $this->all_configs['db']->query(
                'INSERT IGNORE INTO {warehouses_users} (wh_id, user_id, main) VALUES ?v', array($values));
            }

        } elseif (isset($post['filter-warehouses'])) {
            // фильтруем
            $url = '';

            if (isset($post['warehouses']) && is_array($post['warehouses']) && count($post['warehouses']) > 0) {
                if (!empty($url))
                    $url .= '&';
                $url .= 'whs=' . implode(',', $post['warehouses']);
            }

            if (isset($post['locations']) && is_array($post['locations']) && count($post['locations']) > 0) {
                if (!empty($url))
                    $url .= '&';
                $url .= 'lcs=' . implode(',', $post['locations']);
            }

            if (isset($post['goods']) && $post['goods'] > 0) {
                if (!empty($url))
                    $url .= '&';
                $url .= 'pid=' . intval($post['goods']);
            }

            if (isset($post['display']) && $post['display'] == 'amount') {
                if (!empty($url))
                    $url .= '&';
                $url .= 'd=a';
            }

            // первычные ключи
            if (isset($post['serial']) && !empty($post['serial'])) {
                $url = 'serial=' . urlencode($post['serial']);
            }

            if (isset($post['so_id']) && $post['so_id'] > 0) {
                $url = 'so_id=' . intval($post['so_id']);
            }

            $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . $url) . '#show_items';

            header("Location:" . $url);
            exit;
        } elseif (isset($post['warehouse-add']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // создать склад
            $consider_all = 0;
            if (isset($post['consider_all']))
                $consider_all = 1;
            $consider_store = 0;
            if (isset($post['consider_store']))
                $consider_store = 1;
            $group_id = isset($post['group_id']) && intval($post['group_id']) > 0 ? intval($post['group_id']) : null;
            $type_id = isset($post['type_id']) && intval($post['type_id']) > 0 ? intval($post['type_id']) : null;
            if ($post['type'] != 2) {
                $empty = function ($locations) {
                    if (empty($locations) || !is_array($locations)) {
                        return true;
                    }
                    return !array_reduce($locations, function ($carry, $item) {
                        return $carry || !empty($item);
                    }, 0);
                };
                if (!empty($post['title']) && !$empty($_POST['location'])) {
                    $warehouse_id = $this->all_configs['db']->query('INSERT INTO {warehouses}
                (consider_all, consider_store, code_1c, title, print_address, print_phone, type, group_id, type_id) VALUES (?i, ?i, ?, ?, ?, ?, ?i, ?n, ?n)',
                        array(
                            $consider_all,
                            $consider_store,
                            trim($post['code_1c']),
                            trim($post['title']),
                            trim($post['print_address']),
                            trim($post['print_phone']),
                            $post['type'],
                            $group_id,
                            $type_id
                        ), 'id');


                    if ($warehouse_id && isset($_POST['location']) && is_array($_POST['location'])) {
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                            array($user_id, 'add-warehouse', $mod_id, $warehouse_id), 'id');
                        foreach ($_POST['location'] as $location) {
                            if (mb_strlen(trim($location), 'UTF-8') > 0) {
                                $this->all_configs['db']->query(
                                    'INSERT IGNORE INTO {warehouses_locations} (wh_id, location) VALUES (?i, ?)',
                                    array($warehouse_id, trim($location)));
                            }
                        }
                    }
                }
            } else {
                FlashMessage::set(l('Склад типа "Надостача" может существовать только в единственном экземпляре'), FlashMessage::DANGER);
            }

        } elseif (isset($post['warehouse-edit']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // редактировать склад
            if (!isset($post['warehouse-id']) || $post['warehouse-id'] == 0) {
                header("Location:" . $_SERVER['REQUEST_URI']);
                exit;
            }

            $consider_all = 0;
            if (isset($post['consider_all']))
                $consider_all = 1;
            $consider_store = 0;
            if (isset($post['consider_store']))
                $consider_store = 1;
            $group_id = isset($post['group_id']) && intval($post['group_id']) > 0 ? intval($post['group_id']) : null;
            $type_id = isset($post['type_id']) && intval($post['type_id']) > 0 ? intval($post['type_id']) : null;

            $this->all_configs['db']->query('UPDATE {warehouses} SET consider_all=?i, consider_store=?i, code_1c=?, title=?, print_address = ?, print_phone = ?, type=?i, group_id=?n, type_id=?n WHERE id=?i',
                array($consider_all, $consider_store, trim($post['code_1c']), trim($post['title']), trim($post['print_address']), trim($post['print_phone']), $post['type'], $group_id, $type_id, $post['warehouse-id']));
            $query = '';
            if (isset($_POST['location-id']) && is_array($_POST['location-id'])) {
                foreach ($_POST['location-id'] as $location_id=>$location) {
                    if ($location_id > 0 && mb_strlen(trim($location), 'UTF-8') > 0) {
                        $this->all_configs['db']->query('UPDATE {warehouses_locations} SET location=? WHERE id=?i',
                            array(trim($location), intval($location_id)));
                        $query = $this->all_configs['db']->makeQuery('?query AND id<>?i', array($query, $location_id));
                    }
                }
            }
            if (isset($_POST['location']) && is_array($_POST['location'])) {
                foreach ($_POST['location'] as $location) {
                    $location_id = $this->all_configs['db']->query(
                        'INSERT IGNORE INTO {warehouses_locations} (wh_id, location) VALUES (?i, ?)',
                        array($post['warehouse-id'], trim($location)), 'id');

                    get_service('wh_helper')->clear_cache();
                    if ($location_id > 0) {
                        $query = $this->all_configs['db']->makeQuery('?query AND id<>?i', array($query, $location_id));
                    }
                }
            }
            try {
                $this->all_configs['db']->query(
                    'DELETE FROM {warehouses_locations} WHERE wh_id=?i ?query', array($post['warehouse-id'], $query));
            } catch(Exception $e) {}
            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                array($user_id, 'edit-warehouse', $mod_id, $post['warehouse-id']));

            // пересчет остатков на складе
            $this->all_configs['manageModel']->move_product_item($post['warehouse-id'], null);
        } elseif(isset($post['warehouse-group-add']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            if (isset($post['name']) && mb_strlen(trim($post['name']), 'UTF-8') > 0) {
                $color = preg_match('/^#[a-f0-9]{6}$/i', trim($post['color'])) ? trim($post['color']) : '#000000';
                $this->all_configs['db']->query(
                    'INSERT IGNORE INTO {warehouses_groups} (name, color, user_id, address) VALUES (?, ?, ?i, ?)',
                    array(trim($post['name']), $color, $user_id, trim($post['address'])));
                $link = '<a href="'.$this->all_configs['prefix'].'warehouses#settings-warehouses" class="btn btn-primary js-go-to" data-goto_id="#add_warehouses">' . l('Перейти') . '</a>';
                FlashMessage::set(l('Вы добавили отделение') . ' ' . $post['name'] . '. ' . l('Теперь необходимо добавить склады и локации для данного отделения.') . $link,
                    FlashMessage::SUCCESS);
            }
        } elseif(isset($post['warehouse-type-add']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            if (isset($post['name']) && mb_strlen(trim($post['name']), 'UTF-8') > 0) {
                $this->all_configs['db']->query(
                    'INSERT IGNORE INTO {warehouses_types} (name, user_id) VALUES (?, ?i)',
                    array(trim($post['name']), $user_id));
            }
        } elseif(isset($post['warehouse-group-edit']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {

            if (isset($post['warehouse-group-id']) && isset($post['name']) && mb_strlen(trim($post['name']), 'UTF-8') > 0) {
                try {
                    $color = preg_match('/^#[a-f0-9]{6}$/i', trim($post['color'])) ? trim($post['color']) : '#000000';
                    $this->all_configs['db']->query('UPDATE {warehouses_groups} SET name=?, color=?, address=? WHERE id=?i',
                        array(trim($post['name']), $color, trim($post['address']), intval($post['warehouse-group-id'])));
                } catch(Exception $e) {}
            }
        } elseif(isset($post['warehouse-type-edit']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {

            if (isset($post['warehouse-type-id']) && isset($post['name']) && mb_strlen(trim($post['name']), 'UTF-8') > 0) {
                try {
                    $this->all_configs['db']->query('UPDATE {warehouses_types} SET name=?, icon=? WHERE id=?i',
                        array(trim($post['name']), trim($post['icon']), intval($post['warehouse-type-id'])));
                } catch(Exception $e) {}
            }
        }

        // чистим кеш складов
        get_service('wh_helper')->clear_cache();

        header("Location:" . $_SERVER['REQUEST_URI']);
        exit;
    }

    /**
     * @return array
     */
    function get_warehouses_options()
    {
        // списсок выбранных складов для вывода
        $warehouses_selected = (isset($_GET['whs']) && !empty($_GET['whs'])) ? explode(',', $_GET['whs']) : array();

        $warehouses_options = '';
        if ($this->warehouses && count($this->warehouses) > 0) {
            foreach ($this->warehouses as $warehouse) {
                $r = array_search($warehouse['id'], $warehouses_selected);
                if ($r === false) {
                    $warehouses_options .= '<option value="' . $warehouse['id'] . '">' . $warehouse['title'] . '</option>';
                } else {
                    $warehouses_options .= '<option selected value="' . $warehouse['id'] . '">' . $warehouse['title'] . '</option>';
                }
            }
        }

        return array('wo' => $warehouses_options, 'ws' => $warehouses_selected);
    }

    /**
     *
     */
    function preload()
    {
        // запросы для касс для разных привилегий
        $q = $this->all_configs['chains']->query_warehouses();
        $query_for_noadmin_w = $q['query_for_noadmin_w'];
        // списсок складов с общим количеством товаров
        $this->warehouses = $this->all_configs['chains']->warehouses($query_for_noadmin_w);
    }

    /**
     * @return string
     */
    function gencontent()
    {
        $this->preload();

        $out = '<div class="tabbable"><ul class="nav nav-tabs">';
        if ($this->all_configs["oRole"]->hasPrivilege("debit-suppliers-orders") || $this->all_configs["oRole"]->hasPrivilege("logistics"))
            $out .= '<li><a class="click_tab default" data-open_tab="warehouses_warehouses" onclick="click_tab(this, event)" data-toggle="tab" href="'.$this->mod_submenu[0]['url'].'">'.$this->mod_submenu[0]['name'].'</a></li>';
        if ($this->all_configs["oRole"]->hasPrivilege("scanner-moves"))
            $out .= '<li><a class="click_tab default" data-open_tab="warehouses_scanner_moves" onclick="click_tab(this, event)" data-toggle="tab" href="'.$this->mod_submenu[1]['url'].'">'.$this->mod_submenu[1]['name'].'</a></li>';
        if ($this->all_configs["oRole"]->hasPrivilege("debit-suppliers-orders") || $this->all_configs["oRole"]->hasPrivilege("logistics"))
            $out .= '<li><a class="click_tab" data-open_tab="warehouses_show_items" onclick="click_tab(this, event)" data-toggle="tab" href="'.$this->mod_submenu[2]['url'].'">'.$this->mod_submenu[2]['name'].'</a></li>';
        if ($this->all_configs["oRole"]->hasPrivilege("debit-suppliers-orders") || $this->all_configs["oRole"]->hasPrivilege("logistics"))
            $out .= '<li><a class="click_tab" data-open_tab="warehouses_orders" onclick="click_tab(this, event)" data-toggle="tab" href="'.$this->mod_submenu[3]['url'].'">'.$this->mod_submenu[3]['name'].'<span class="tab_count hide tc_sum_warehouses_orders"></span></a></li>';
        if ($this->all_configs["oRole"]->hasPrivilege("site-administration"))
            $out .= '<li><a class="click_tab" data-open_tab="warehouses_settings" onclick="click_tab(this, event)" data-toggle="tab" href="'.$this->mod_submenu[4]['url'].'">'.$this->mod_submenu[4]['name'].'</a></li>';
        //if ($this->all_configs["oRole"]->hasPrivilege("debit-suppliers-orders"))
        //    $out .= '<li><a class="click_tab" data-open_tab="warehouses_inventories" onclick="click_tab(this, event)" data-toggle="tab" href="#inventories">Инвентаризация</a></li>';
        $out .= '</ul><div class="tab-content">';

        // если администратор
        if ($this->all_configs['oRole']->hasPrivilege('scanner-moves')) {
            $out .= '<div id="scanner_moves" class="tab-pane">';
            $out .= "</div><!--#settings-warehouses-->";
        }
        if ($this->all_configs["oRole"]->hasPrivilege("debit-suppliers-orders") || $this->all_configs["oRole"]->hasPrivilege("logistics")) {
            // склады
            $out .= '<div id="warehouses" class="tab-pane clearfix">';
            $out .= '</div><!--#warehouses-->';
        }
        // только кладовщик
        if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders') || $this->all_configs['oRole']->hasPrivilege('logistics')) {
            // приходование заказа
            $out .= '<div id="orders" class="tab-pane clearfix">';
            $out .= '</div><!--#orders-->';
        }

        if ($this->all_configs["oRole"]->hasPrivilege("debit-suppliers-orders") || $this->all_configs["oRole"]->hasPrivilege("logistics")) {
            // изделия
            $out .= '<div id="show_items" class="tab-pane">';
            $out .= '</div><!--#show_items-->';
        }

        // если администратор
        if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $out .= '<div id="settings" class="tab-pane">';
            $out .= "</div><!--#settings-warehouses-->";
        }


        // изделия
        $out .= '<div id="inventories" class="tab-pane">';
        $out .= '</div><!--#show_items-->';


        $out .= '</div><!--.tab-content-->';
        $out .= '</div><!--.tabbable-->';

        $out .= $this->all_configs['suppliers_orders']->append_js();
        $out .= $this->all_configs['chains']->append_js();

        return $out;
    }

    /**
     * @return array
     */
    function warehouses_scanner_moves()
    {
        $out = '';
        if ($this->all_configs['oRole']->hasPrivilege('scanner-moves')) {
            $out .= '<div id="scanner-moves-alert" class="alert fade"><button type="button" class="close" data-dismiss="alert">&times;</button><div id="scanner-moves-alert-body"></div></div>';
            $out .= '
                <label>' . l('Укажите номер заказа, изделия или локации. После чего нажмите Enter. Или используйте сканер.') .'</label>
                <input value="" id="scanner-moves" type="text" placeholder="' . l('заказ, изделие или локация') . '" class="form-control" />';
            $out .= '<input value="" id="scanner-moves-old" type="hidden" placeholder="' . l('заказ или локация') .'" class="form-control" />';
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    function warehouses_warehouses()
    {
        // всего денег по кассам которые consider_all == 1
        $cost_of = cost_of($this->warehouses,  $this->all_configs['settings'], $this->all_configs['suppliers_orders']);
        $out = '<div class="well">' . l('Всего') . ': ';
        if ($this->all_configs['oRole']->hasPrivilege('logistics')) {
            $out .= $cost_of['cur_price'] . ' (' . $cost_of['html'] .  '), ';
        }
        $out .= $cost_of['count'] . ' ' . l('шт.') . '</div>';
        $wh = $this->get_warehouses_options();
        $warehouses_options = $wh['wo'];
        // фильтрация
        $out .= $this->filter_block($warehouses_options);
        // списсок складов
        $out .= '<div id="warehouses_content">';

        if ($this->warehouses && count($this->warehouses) > 0) {
            $out .= '<div class="pull-left vertical-line"></div>';
            foreach ($this->warehouses as $warehouse) {
                $out .= '<div class="show_warehouse">';
                $print_link = print_link(array_keys($warehouse['locations']), 'location');
                $out .= '<h5><a class="hash_link" href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?whs=' . $warehouse['id'] . '#show_items">' . $warehouse['title'] . '</a> ' . $print_link . '</h5>';
                $out .= '<div>' . l('Общий остаток') . ': ' . intval($warehouse['sum_qty']) . ' ' . l('шт.') . '</div>';
                if ($this->all_configs['oRole']->hasPrivilege('logistics')) {
                    $out .= '<div>' . l('Общая сумма') . ': ';
                    $out .= $this->show_price($warehouse['all_amount'], 2, getCourse($this->all_configs['settings']['currency_suppliers_orders']));
                    $out .= ' '.viewCurrency().' (' . $this->show_price($warehouse['all_amount']) .viewCurrencySuppliers() .' )</div>';
                }
                $out .= '</div>';
                $out .= '<div class="pull-left vertical-line"></div>';
            }
        }

        $out .= '</div><!--#warehouses_content-->';

        return array(
            'html' => $out,
            'functions' => array('multiselect()'),
        );
    }

    /**
     * @return array
     */
    function warehouses_show_items()
    {
        // фильтрация
        $wh = $this->get_warehouses_options();
        $warehouses_options = $wh['wo'];
        $warehouses_selected = $wh['ws'];
        // запросы для касс для разных привилегий
        $q = $this->all_configs['chains']->query_warehouses();
        $query_for_noadmin = $q['query_for_noadmin'];

        $out = $this->filter_block($warehouses_options, 2);
        $out .= '<div>';

        $goods = null;
        $show_item_type = null;

        $query = '';
        // товар ид
        if (isset($_GET['pid']) && $_GET['pid'] > 0) {
            $query = $this->all_configs['db']->makeQuery('AND g.id=?i', array($_GET['pid']));
        }

        $count_on_page = $this->count_on_page;//30;
        $count_page = 1;
        $skip = (isset($_GET['p']) && $_GET['p'] > 0) ? ($count_on_page * ($_GET['p'] - 1)) : 0;

        if (isset($_GET['lcs']) && array_filter(explode(',', $_GET['lcs'])) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND l.id IN (?li)',
                array($query, explode(',', $_GET['lcs'])));
        }

        // тип вывода
        if (isset($_GET['d']) && $_GET['d'] == 'a') {// вывод по наименованию

            // проверяем количество складов
            if (count($warehouses_selected) > 0) {
                $goods = $this->all_configs['db']->query('SELECT g.title as product_title, i.goods_id, COUNT(g.id) as qty_wh
                        FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {warehouses_locations} as l
                        WHERE i.wh_id=w.id AND g.id=i.goods_id AND w.id IN (?li) AND l.id=i.location_id ?query ?query
                        GROUP BY g.id LIMIT ?i, ?i',
                    array(array_values($warehouses_selected), $query, $query_for_noadmin, $skip, $count_on_page))->assoc();

                $count_page = $this->all_configs['db']->query('SELECT COUNT(DISTINCT i.goods_id)
                        FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {warehouses_locations} as l
                        WHERE i.wh_id=w.id AND g.id=i.goods_id AND w.id IN (?li) AND l.id=i.location_id ?query ?query',
                    array(array_values($warehouses_selected), $query, $query_for_noadmin))->el() / $count_on_page;
            }
        } else { // по изделию
            if (isset($_GET['serial'])) { // по серийнику
                $serial = suppliers_order_generate_serial($_GET, false);

                if ($this->all_configs['configs']['erp-serial-prefix'] == substr(trim($_GET['serial']), 0, strlen($this->all_configs['configs']['erp-serial-prefix']))) {
                    $goods = $this->all_configs['db']->query('SELECT w.id, w.title, w.code_1c, w.consider_all, w.consider_store, g.title as product_title,
                        i.goods_id, i.order_id, i.supplier_order_id, i.serial, i.date_sold, i.price, i.supplier_id as user_id, u.title as contractor_title,
                        i.id as item_id, i.date_add, i.serial_old, l.location, i.location_id
                        FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {contractors} as u, {warehouses_locations} as l
                        WHERE i.wh_id=w.id AND g.id=i.goods_id AND u.id=i.supplier_id AND l.id=i.location_id AND i.id=?i ?query
                        ', array($serial, $query_for_noadmin))->assoc();
                } else {
                    $goods = $this->all_configs['db']->query('SELECT w.id, w.title, w.code_1c, w.consider_all, w.consider_store, g.title as product_title,
                        i.goods_id, i.order_id, i.supplier_order_id, i.serial, i.date_sold, i.price, i.supplier_id as user_id, u.title as contractor_title,
                        i.id as item_id, i.date_add, i.serial_old, l.location, i.location_id
                        FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {contractors} as u, {warehouses_locations} as l
                        WHERE i.wh_id=w.id AND g.id=i.goods_id AND u.id=i.supplier_id AND l.id=i.location_id AND i.serial=? ?query
                    ', array($serial, $query_for_noadmin))->assoc();
                }
                $show_item_type = 2;
            } else {
                if (count($warehouses_selected) > 0 || (isset($_GET['so_id']) && $_GET['so_id'] > 0)) {
                    $goods = $this->getItems($_GET, $count_on_page, $skip);
                    /*$goods = $this->all_configs['db']->query('SELECT w.id, w.title, w.code_1c, w.consider_all,
                              w.consider_store, g.title as product_title, i.id as item_id, i.date_add, i.goods_id,
                              i.order_id, i.serial, i.date_sold, i.price, i.supplier_id as user_id,
                              u.title as contractor_title, i.supplier_order_id, l.location, i.location_id
                            FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {contractors} as u, {warehouses_locations} as l
                            WHERE i.wh_id=w.id AND g.id=i.goods_id AND u.id=i.supplier_id AND l.id=i.location_id ?query ?query LIMIT ?i, ?i',
                        array($query, $query_for_noadmin, $skip, $count_on_page))->assoc();*/

                    $count_page = $this->all_configs['db']->query('SELECT COUNT(DISTINCT i.id)
                            FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {contractors} as u, {warehouses_locations} as l
                            WHERE i.wh_id=w.id AND g.id=i.goods_id AND u.id=i.supplier_id AND l.id=i.location_id ?query ?query',
                        array($query, $query_for_noadmin))->el() / $count_on_page;
                }

                $show_item_type = 1;
            }
        }

        if (($show_item_type == null || $show_item_type == 1) && (count($warehouses_selected) > 0
                || (isset($_GET['so_id']) && $_GET['so_id'] > 0)) || $show_item_type == 2) {
            $out .= $this->show_goods($goods, $query_for_noadmin, $show_item_type, $count_page);
        } else {
            $out .= '<p class="text-error">' . l('Выберите склад') . '</p>';
        }

        $out .= '</div>';

        return array(
            'html' => $out,
            'functions' => array('multiselect()'),
        );
    }

    /**
     * @param array $filters
     * @param null  $count_on_page
     * @param null  $skip
     * @param bool  $select_name
     * @return null
     */
    private function getItems($filters = array(), $count_on_page = null, $skip = null, $select_name = false)
    {
        // фильтрация
        $wh = $this->get_warehouses_options();
        $warehouses_selected = $wh['ws'];
        // запросы для касс для разных привилегий
        $q = $this->all_configs['chains']->query_warehouses();
        $query_for_noadmin = $q['query_for_noadmin'];

        $goods = null;
        $show_item_type = null;

        $query = '';
        // товар ид
        if (isset($filters['pid']) && $filters['pid'] > 0) {
            $query = $this->all_configs['db']->makeQuery('AND g.id=?i', array($filters['pid']));
        }

        if (isset($filters['lcs']) && array_filter(explode(',', $filters['lcs'])) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND l.id IN (?li)',
                array($query, explode(',', $filters['lcs'])));
        }

        $limit = '';
        if ($count_on_page || $skip) {
            $limit = $this->all_configs['db']->makeQuery('LIMIT ?i, ?i', array(intval($skip), intval($count_on_page)));
        }
        if (count($warehouses_selected) > 0 || (isset($filters['so_id']) && $filters['so_id'] > 0)) {
            if (isset($filters['so_id']) && $filters['so_id'] > 0) {
                $query = $this->all_configs['db']->makeQuery('AND i.supplier_order_id=?i',
                    array(intval($filters['so_id'])));
            } elseif (count($warehouses_selected) > 0) {
                $query = $this->all_configs['db']->makeQuery('?query AND w.id IN (?li)',
                    array($query, array_values($warehouses_selected)));
            }

            if ($select_name) {
                $select = $this->all_configs['db']->makeQuery('i.id as `№ изделия`, i.serial as `Серийный номер`,
                    g.title as `Наименование`, i.date_add as `'.l('Дата').'`, w.title as `Склад`, w.id as `№ склада`,
                    l.location as `Локация`, l.id as `№ локации`, i.order_id as `Заказ клиента`,
                    i.supplier_order_id as `Заказ поставщику`, i.price/100 as `Цена`,
                    u.title as `Поставщик`, i.supplier_id as `№ поставщика`', array());
            } else {
                $select = $this->all_configs['db']->makeQuery('w.id, w.title, w.code_1c, w.consider_all,
                    w.consider_store, g.title as product_title, i.id as item_id, i.date_add, i.goods_id,
                    i.order_id, i.serial, i.date_sold, i.price, i.supplier_id as user_id,
                    u.title as contractor_title, i.supplier_order_id, l.location, i.location_id', array());
            }
            $goods = $this->all_configs['db']->query('SELECT ?query
                    FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {contractors} as u, {warehouses_locations} as l
                    WHERE i.wh_id=w.id AND g.id=i.goods_id AND u.id=i.supplier_id AND l.id=i.location_id ?query ?query ?query',
                array($select, $query, $query_for_noadmin, $limit))->assoc();
        }

        return $goods;
    }

    /**
     * @param string $hash
     * @return array
     */
    function warehouses_orders($hash = '#orders-clients_issued')
    {
        if (trim($hash) == '#orders' || (trim($hash) != '#orders-suppliers' && trim($hash) != '#orders-clients_bind'
                && trim($hash) != '#orders-clients_accept' && trim($hash) != '#orders-clients_issued' && trim($hash) != '#orders-clients_unbind')
        ) {
            $hash = $this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders') ? '#orders-clients_bind' : '#orders-clients_issued';
        }

        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders') || $this->all_configs['oRole']->hasPrivilege('logistics')) {
            $out .= $this->view->renderFile('warehouses/warehouses_orders');
        }

        return array(
            'html' => $out,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    /**
     * @return array
     */
    function warehouses_orders_suppliers()
    {
        $out = '';

        if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders')) {
            //$my = $this->all_configs['oRole']->hasPrivilege('site-administration') ? false : true;
            //$_GET['my'] = $my || (isset($_GET['my']) && $_GET['my'] == 1) ? true : false;
            $_GET['type'] = 'debit';
            $queries = $this->all_configs['manageModel']->suppliers_orders_query($_GET);
            $query = $queries['query'];
            $skip = $queries['skip'];

            $count_on_page = $this->count_on_page;//$queries['count_on_page'];

            //$q = $this->all_configs['chains']->query_warehouses();
            //$query .= ' AND o.' . trim($q['query_for_my_warehouses']);

            $out .= '<div>';
            $out .= '<h4>' . l('Заказы поставщику, которые ждут приходования') .'</h4>';
            $orders = $this->all_configs['manageModel']->get_suppliers_orders($query, $skip, $count_on_page);
            $out .= $this->all_configs['suppliers_orders']->show_suppliers_orders($orders, true);
            // количество заказов
            $count = $this->all_configs['manageModel']->get_count_suppliers_orders($query);
            $count_page = ceil($count / $this->count_on_page);
            $out .= page_block($count_page, $count, '#orders-suppliers');
            $out .= '</div>';
        }

        return array(
            'html' => $out,
            'menu' => $this->all_configs['suppliers_orders']->show_filters_suppliers_orders(false,false,false,'orders-suppliers'),
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @return array
     */
    function warehouses_orders_clients_bind()
    {
        $out = $this->all_configs['chains']->show_stockman_operations();

        return array(
            'html' => $out['html'],
            'menu' => $out['menu'],
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    function warehouses_orders_clients_issued()
    {
        $out = $this->all_configs['chains']->show_stockman_operations(2, '#orders-clients_issued');

        return array(
            'html' => $out['html'],
            'menu' => $out['menu'],
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    function warehouses_orders_clients_accept()
    {
        $out = $this->all_configs['chains']->show_stockman_operations(3, '#orders-clients_accept');

        return array(
            'html' => $out['html'],
            'menu' => $out['menu'],
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    function warehouses_orders_clients_unbind()
    {
        $out = $this->all_configs['chains']->show_stockman_operations(4, '#orders-clients_unbind');

        return array(
            'html' => $out['html'],
            'menu' => $out['menu'],
            'functions' => array(),
        );
    }

    /**
     * @param string $hash
     * @return array
     */
    function warehouses_settings($hash = '')
    {
        if (trim($hash) == '#settings' || (trim($hash) != '#settings-warehouses' && trim($hash) != '#settings-warehouses_groups'
                && trim($hash) != '#settings-warehouses_types' && trim($hash) != '#settings-warehouses_users'))
            $hash = '#settings-warehouses';

        $admin_out = '';

        if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // настройка
//            $admin_out .= '<div id="settings" class="tab-pane">';

            $admin_out .= '<ul class="nav nav-pills">';
            $admin_out .= '<li><a class="click_tab" data-open_tab="warehouses_settings_warehouses_groups" onclick="click_tab(this, event)" href="#settings-warehouses_groups" title="' . l('Создать') . '/' . l('редактировать группу склада') . '">' . l('Сервисные центры') . '</a></li>';
            $admin_out .= '<li><a class="click_tab" id="add_warehouses" data-open_tab="warehouses_settings_warehouses" onclick="click_tab(this, event)" href="#settings-warehouses" title="Создать/редактировать склад">' . l('Склады') . '</a></li>';
            $admin_out .= '<li><a class="click_tab" data-open_tab="warehouses_settings_warehouses_types" onclick="click_tab(this, event)" href="#settings-warehouses_types" title="' . l('Создать') . '/' . l('редактировать категорию склада') . '">' . l('Категории') . '</a></li>';
            $admin_out .= '<li><a class="click_tab" data-open_tab="warehouses_settings_warehouses_users" onclick="click_tab(this, event)" href="#settings-warehouses_users" title="' . l('Закрепить администратора за кассой') . '">' . l('Администраторы') . '</a></li>';
            $admin_out .= '</ul>';
            $admin_out .= '<div class="pill-content">';

            $admin_out .= '<div id="settings-warehouses" class="pill-pane">';
            $admin_out .= "</div><!--#settings-warehouses-->";

            // форма привязки пользователей к складу
            $admin_out .= '<div id="settings-warehouses_users" class="pill-pane">';
            $admin_out .= '</div><!--#settings-warehouses_users-->';

            $admin_out .= '<div id="settings-warehouses_groups" class="pill-pane">';
            $admin_out .= '</div><!--#settings-warehouses_groups-->';

            $admin_out .= '<div id="settings-warehouses_types" class="pill-pane">';
            $admin_out .= '</div><!--#settings-warehouses_types-->';

//            $admin_out .= '</div><!--.pill-content--></div><!--#settings-->';
        }

        return array(
            'html' => $admin_out,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    /**
     * @return array
     */
    function warehouses_settings_warehouses()
    {
        $admin_out = '';

        if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // склады
            $admin_out .= "<div class='panel-group' id='accordion_warehouses'>";
            // форма для создания склада
            $admin_out .= $this->form_warehouse();
            //$warehouses_options = '';
            if ($this->warehouses && count($this->warehouses) > 0) {
                $i = 1;
                foreach ($this->warehouses as $warehouse) {
                    //$r = array_search($warehouse['id'], $warehouses_selected);
                    //if ($r === false) {
                    //    $warehouses_options .= '<option value="' . $warehouse['id'] . '">' . $warehouse['title'] . '</option>';
                    //} else {
                    //    $warehouses_options .= '<option selected value="' . $warehouse['id'] . '">' . $warehouse['title'] . '</option>';
                    //}

                    $i++;
                    $admin_out .= $this->form_warehouse($warehouse, $i);
                }
            }
            $admin_out .= '</div><!--#accordion_warehouses-->';
        }

        return array(
            'html' => $admin_out,
            'functions' => array(),
        );
    }

    /**
     * @param null $type
     * @return string
     */
    function warehouses_settings_warehouses_types_form($type = null)
    {
        if ($type) {
            $i = $type['id'];
            $btn = "<input type='hidden' name='warehouse-type-id' value='{$type['id']}' /><input type='submit' class='btn' name='warehouse-type-edit' value='" . l('Редактировать') . "' />";
            $accordion_title = l('Редактировать категорию склада') .  ' "' . htmlspecialchars($type['name']) . '"';
            $name = htmlspecialchars($type['name']);
            $icon = htmlspecialchars($type['icon']);
        } else {
            $i = 0;
            $btn = "<input type='submit' class='btn' name='warehouse-type-add' value='" . l('Создать') . "' />";
            $accordion_title = l('Создать категорию склада');
            $name = $icon = '';
        }

        return "
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    <a class='accordion-toggle' data-toggle='collapse' data-parent='#accordion_warehouses_types' href='#collapse_warehouse_type_{$i}'>{$accordion_title}</a>
                </div>
                <div id='collapse_warehouse_type_{$i}' class='panel-collapse collapse'>
                    <div class='panel-body'>
                        <form method='POST'>
                            <div class='form-group'><label>" . l('Название') . ": </label>
                                <input placeholder='" . l('введите название') . "' class='form-control' name='name' value='{$name}' /></div>
                            <div class='form-group'><label'>" . l('Иконка') . " (fa fa-home): </label>
                                <input placeholder='" . l('введите иконку') . "' class='form-control' name='icon' value='{$icon}' /></div>
                            <div class='form-group'><label></label>{$btn}</div>
                        </form>
                    </div>
                </div>
            </div>
        ";
    }

    /**
     * @return array
     */
    function warehouses_settings_warehouses_types()
    {
        $admin_out = '';

        if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $admin_out .= "<div class='panel-group' id='accordion_warehouses_types'>";
            $admin_out .= $this->warehouses_settings_warehouses_types_form();
            $types = (array)$this->all_configs['db']->query('SELECT * FROM {warehouses_types}')->assoc();
            foreach ($types as $type) {
                $admin_out .= $this->warehouses_settings_warehouses_types_form($type);
            }
            $admin_out .= '</div><!--#accordion_warehouses_types-->';
        }

        return array(
            'html' => $admin_out,
            'functions' => array(),
        );
    }

    /**
     * @param null $group
     * @return string
     */
    function warehouses_settings_warehouses_groups_form($group = null)
    {
        if ($group) {
            $i = $group['id'];
            $btn = "<input type='hidden' name='warehouse-group-id' value='{$group['id']}' /><input type='submit' class='btn' name='warehouse-group-edit' value='" . l('Редактировать') . "' />";
            $accordion_title = l('Редактировать группу склада') . ' "' . htmlspecialchars($group['name']) . '"';
            $name = htmlspecialchars($group['name']);
            $color = htmlspecialchars($group['color']);
            $address = htmlspecialchars($group['address']);
        } else {
            $i = 0;
            $btn = "<input type='submit' class='btn' name='warehouse-group-add' value='" . l('Создать') . "' />";
            $accordion_title = l('Создать  сервисный центр') . ' (' . l('группу складов') . ')';
            $name = $color = $address = '';
        }

        return "
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    <a class='accordion-toggle' data-toggle='collapse' data-parent='#accordion_warehouses_groups' href='#collapse_warehouse_group_{$i}'>{$accordion_title}</a>
                </div>
                <div id='collapse_warehouse_group_{$i}' class='panel-collapse collapse'>
                    <div class='panel-body'>
                        <form method='POST'>
                            <div class='form-group'><label>" . l('Название') . ": </label>
                                <input placeholder='" . l('введите название') . "' class='form-control' name='name' value='{$name}' /></div>
                            <div class='form-group'><label>" . l('Цвет') . " (#000000): </label>
                                <input placeholder='" . l('введите цвет') . "' class='colorpicker form-control' name='color' value='{$color}' /></div>
                            <div class='form-group'><label>" . l('Адрес') . ": </label>
                                <input placeholder='" . l('введите адрес') . "' class='form-control' name='address' value='{$address}' /></div>
                            <div class='form-group'><label></label>{$btn}</div>
                        </form>
                    </div>
                </div>
            </div>
        ";
    }

    /**
     * @return array
     */
    function warehouses_settings_warehouses_groups()
    {
        $admin_out = '';

        if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $admin_out .= "<div class='panel-group' id='accordion_warehouses_groups'>";
            $admin_out .= $this->warehouses_settings_warehouses_groups_form();
            $groups = (array)$this->all_configs['db']->query('SELECT * FROM {warehouses_groups}')->assoc();
            foreach ($groups as $group) {
                $admin_out .= $this->warehouses_settings_warehouses_groups_form($group);
            }
            $admin_out .= '</div><!--#accordion_warehouses_groups-->';
        }
        return array(
            'html' => $admin_out,
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    function warehouses_settings_warehouses_users()
    {
        $admin_out = '';

        if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $query = '';
            if (count($this->all_configs['configs']['erp-warehouses-permiss']) > 0) {
                $query = $this->all_configs['db']->makeQuery(
                    ', {users_role_permission} as p WHERE p.permission_id IN (?li) AND u.role=p.role_id GROUP BY u.id',
                array($this->all_configs['configs']['erp-warehouses-permiss']));
            }
            // достаем всех пользователей
            $users = $this->all_configs['db']->query('SELECT u.id, u.login, u.fio, u.phone, u.email
                FROM {users} as u ?query', array($query))->assoc('id');

            if ($users) {
                // достаем связку пользователь - склад
                $wh_mains = $this->all_configs['db']->query(
                    'SELECT u.user_id, u.location_id FROM {warehouses_users} as u WHERE u.main=?i', array(1))->vars();

                $wh_users = $this->all_configs['db']->query(
                    'SELECT u.user_id, GROUP_CONCAT(u.wh_id) FROM {warehouses_users} as u GROUP BY u.user_id')->vars();

                $admin_out .= '<form method="post"><table class="table"><thead><tr><td>' . l('Сотрудник') . '</td><td>' . l('Укажите склады к которым сотрудник имеет доступ') .'</td><td>' . l('Укажите склад и локацию, на которую по умолчанию перемещается устройство принятое на ремонт данным сотрудником') .'</td></tr></thead><tbody>';
                foreach ($users as $user_id=>$user) {
                    $admin_out .= '<tr><td>' . get_user_name($user) . '</td>';
                    $admin_out .= '<td><select class="multiselect" name="warehouses_users[' . $user_id . '][]" multiple="multiple">';
                    $whs = $wh_users && isset($wh_users[$user_id]) ? explode(',', $wh_users[$user_id]) : array();
                    foreach ($this->warehouses as $warehouse) {
                        $selected = in_array($warehouse['id'], $whs) ? 'selected' : '';
                        $admin_out .= '<option ' . $selected . ' value="' . $warehouse['id'] . '">' . htmlspecialchars($warehouse['title']) . '</option>';
                    }
                    $admin_out .= '</select></td><td>';
                    $selected = $wh_mains && isset($wh_mains[$user_id]) ? $wh_mains[$user_id] :  '';
                    $admin_out .= typeahead($this->all_configs['db'], 'locations', false, $selected, $user_id, 'input-large', '', '', true, false, $user_id);
                    $admin_out .= '</td></tr>';
                }
                $admin_out .= '<tr><td colspan="3"><input type="submit" class="btn" name="set-warehouses_users" value="'.l('Сохранить').'" /></td></tr></tbody></table></form>';
            }
        }

        return array(
            'html' => $admin_out,
            'functions' => array('multiselect()'),
        );
    }

    /**
     * @param string $hash
     * @return array
     */
    function warehouses_inventories($hash = '#inventories-list')
    {
        if (trim($hash) == '#inventories' || (trim($hash) != '#inventories-list' && trim($hash) != '#inventories-journal'
                && trim($hash) != '#inventories-listinv' && trim($hash) != '#inventories-writeoff'))
            $hash = '#inventories-list';

        $out = '';

        $out .= '<ul class="nav nav-pills hide">';
        $out .= '<li><a class="click_tab" data-open_tab="warehouses_inventories_list" onclick="click_tab(this, event)" href="#inventories-list" title=""></a></li>';
        $out .= '<li><a class="click_tab" data-open_tab="warehouses_inventories_journal" onclick="click_tab(this, event)" href="#inventories-journal" title=""></a></li>';
        $out .= '<li><a class="click_tab" data-open_tab="warehouses_inventories_listinv" onclick="click_tab(this, event)" href="#inventories-listinv" title=""></a></li>';
        $out .= '<li><a class="click_tab" data-open_tab="warehouses_inventories_writeoff" onclick="click_tab(this, event)" href="#inventories-writeoff" title=""></a></li>';
        $out .= '</ul>';
        $out .= '<div class="pill-content">';

        $out .= '<div id="inventories-list" class="pill-pane">';
        $out .= "</div><!--#inventories-list-->";

        $out .= '<div id="inventories-journal" class="pill-pane">';
        $out .= "</div><!--#inventories-journal-->";

        $out .= '<div id="inventories-listinv" class="pill-pane">';
        $out .= "</div><!--#inventories-listinv-->";

        $out .= '<div id="inventories-writeoff" class="pill-pane">';
        $out .= "</div><!--#inventories-writeoff-->";

        return array(
            'html' => $out,
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    /**
     * @return array
     */
    function warehouses_inventories_list()
    {
        $out = '';

        // список инвентаризаций
        // форма новой
        $out .= '<div id="create_inventories" class="input-append">';
        $out .= '<select id="create-inventory-wh_id" name="warehouse">';
        $out .= $this->all_configs['chains']->get_options_for_move_item_form(false);
        $out .= '</select>';
        $out .= '<button class="btn" onclick="create_inventories(this)" type="button">' . l('Начать') .'</button>';
        $out .= '</div>';

        // запрос для складов за которыми закреплен юзер
        $q = $this->all_configs['chains']->query_warehouses();
        $query = $q['query_for_move_item'];

        // список инвентаризаций
        $list = $this->all_configs['db']->query('SELECT inv.date_start, inv.date_stop, w.title, inv.id,
                    u.login, u.email, u.fio FROM {warehouses} as w, {users} as u, {inventories} as inv
                ?query AND w.id=inv.wh_id AND inv.user_id=u.id GROUP BY inv.id ORDER BY inv.date_start DESC',
            array($query))->assoc('id');

        if ($list) {
            $counts_items = (array)$this->all_configs['db']->query('SELECT inv.id, COUNT(DISTINCT i.id)
                FROM {inventories} as inv, {warehouses_goods_items} as i, {inventories_goods} as invg
                WHERE inv.id IN (?li) AND i.wh_id=inv.wh_id AND inv.id=invg.inv_id AND i.goods_id=invg.goods_id
                GROUP BY inv.id', array(array_keys($list)))->vars();
            $counts_inv_items = (array)$this->all_configs['db']->query('SELECT inv.id, COUNT(DISTINCT i.id)
                FROM {inventories} as inv, {warehouses_goods_items} as i, {inventory_journal} as invj
                WHERE inv.id IN (?li) AND i.wh_id=inv.wh_id AND i.id=invj.item_id AND inv.id=invj.inv_id GROUP BY inv.id',
                array(array_keys($list)))->vars();

            $out .= '<table class="table table-hover"><thead><tr><td></td><td>' . l('Дата начала') .'</td><td>' . l('Склад') . '</td>';
            $out .= '<td>' . l('Кладовщик') .' (' . l('создатель') .')</td><td>' . l('Дата завершения') .'</td><td>' . l('Кол-во на складе') .'</td>';
            $out .= '<td>' . l('Кол-во проинвентаризовано') .'</td><td>' . l('Недостача') .'</td></tr></thead><tbody>';
            foreach ($list as $l) {
                $l['count_items'] = isset($counts_items[$l['id']]) ? $counts_items[$l['id']] : 0;
                $l['count_inv_items'] = isset($counts_inv_items[$l['id']]) ? $counts_inv_items[$l['id']] : 0;
                if ($l['id'] == 0) continue;
                $out .= '<tr class="inventory-row" onclick="open_inventory(this, \'' . $l['id'] . '\')"><td>' . $l['id'] . '</td>';
                $out .= '<td><span title="' . do_nice_date($l['date_start'], false) . '">' . do_nice_date($l['date_start']) . '</span></td>';
                $out .= '<td>' . htmlspecialchars($l['title']) . '</td>';
                $out .= '<td>' . get_user_name($l) . '</td>';
                $out .= '<td><span title="' . do_nice_date($l['date_stop'], false) . '">' . do_nice_date($l['date_stop']) . '</span></td>';
                $out .= '<td>' . $l['count_items'] . '</td>';
                $out .= '<td>' . $l['count_inv_items'] . '</td>';
                $out .= '<td>' . ($l['count_items'] - $l['count_inv_items']) . '</td></tr>';
            }
            $out .= '</tbody></table>';
        } else {
            $out .= '<p class="text-error">' . l('Инвентаризаций нет') .'</p>';
        }

        return array(
            'html' => $out,
            'functions' => array(),
        );
    }

    /**
     * @param int $id
     * @return string
     */
    function scan_serial_form($id = 1)
    {
        // форма сканирования
        $html = '<div class="input-append scan-serial-block">';
        $html .= '<div class="scan-serial-error"></div>';
        $html .= '<input id="scan-serial-' . $id . '" onkeyup="is_enter($(\'.btn-scan_serial\'), event, ' . $id . ', \'scan_serial\')" class="scan-serial focusin"';
        $html .= ' type="text" placeholder="' . l('Серийный номер') . '">';
        $html .= '<button class="btn-scan_serial btn" onclick="scan_serial(this, \'' . $id . '\')" type="button">';
        $html .= '"'.l('Добавить').'"</button>';
        $html .= '</div>';

        return $html;
    }

    /**
     * @return array
     */
    function warehouses_inventories_journal()
    {
        $left_html = $right_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {

            // левое меню
            $left = $this->inventories_left_menu(1);
            $left_html .= $left['html'];
            // форма сканирования
            if ($left['open'] == true) {
                $right_html = $this->scan_serial_form(1);
            }

            // журнал сканирований
            $inventories = $this->all_configs['db']->query('SELECT it_j.id as item_id, it_j.date_scan, g.title as gtitle,
                      w.title as wtitle, it_j.scanned, u.login, u.fio, u.email, it_j.goods_id, it_j.wh_id, it_j.wh_id
                    FROM {inventory_journal} as it_j, {warehouses} as w, {goods} as g, {users} as u
                    WHERE w.id=it_j.wh_id AND it_j.inv_id=?i AND g.id=it_j.goods_id AND u.id=it_j.user_id
                    ORDER BY it_j.date_scan DESC',
                array($this->all_configs['arrequest'][2]))->assoc();

            $right_html .= '<table class="table table-striped"><thead><tr><td></td><td>Сер. №</td><td>'.l('Дата').'</td>';
            $right_html .= '<td>' . l('Наименование') . '</td><td>Кладовщик</td><td>' . l('Склад') . '</td></tr></thead><tbody>';
            if ($inventories) {
                $i = 1;
                foreach ($inventories as $inv) {
                    $inv['i'] = $i;$i++;
                    $right_html .= $this->display_scanned_item($inv, $left['inv']['wh_id']);
                }
            } else {
                $right_html .= '<td colspan="6">' . l('Сканирований нет') .'</td>';
            }
            $right_html .= '</tbody></table>';
        }

        if (empty($left_html)) {
            $right_html = '<p class="text-error">' . l('Инвентаризация не найдена') .'</p>';
        }

        return array(
            'html' => '<div class="span2">' . $left_html . '</div><div class="span10">' . $right_html . '</div>',
            'functions' => array('multiselect_goods(1)'),
        );
    }

    /**
     * @return array
     */
    function warehouses_inventories_listinv()
    {
        $left_html = $right_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {
            // левое меню
            $left = $this->inventories_left_menu(2);
            $left_html .= $left['html'];
            // форма сканирования
            if ($left['open'] == true) {
                $right_html .= $this->scan_serial_form(2);
            }

            /*$inventories = $this->all_configs['db']->query('SELECT invg.goods_id, g.title as gtitle,

                    COUNT(DISTINCT IF(i.wh_id=inv.wh_id AND i.goods_id=invg.goods_id, i.id, null)) as count_items,
                    COUNT(DISTINCT IF(i.id=invj.item_id, i.id, null)) as count_inv_items

                  FROM {goods} as g, {inventories_goods} as invg
                  LEFT JOIN (SELECT id, wh_id FROM {inventories})inv ON inv.id=invg.inv_id
                  LEFT JOIN (SELECT id, wh_id, goods_id FROM {warehouses_goods_items})i ON i.wh_id=inv.wh_id
                  LEFT JOIN (SELECT item_id, goods_id, inv_id FROM {inventory_journal}
                    )invj ON invj.inv_id=invg.inv_id AND invj.goods_id=invg.goods_id AND invj.item_id=i.id

                  WHERE invg.goods_id=g.id AND invg.inv_id=?i
                  GROUP BY g.id',
                array($this->all_configs['arrequest'][2]))->assoc();*/

            // список инвентаризаций
            $inventories = $this->all_configs['db']->query('SELECT DISTINCT invg.goods_id, g.title as gtitle, invg.inv_id as id
                  FROM {goods} as g, {inventories_goods} as invg WHERE invg.goods_id=g.id AND invg.inv_id=?i',
                array($this->all_configs['arrequest'][2]))->assoc('id');

            $right_html .= '<table class="table table-striped"><thead><tr><td></td><td>' . l('Наименование') . '</td>';
            $right_html .= '<td>' . l('Кол-во на складе') .'</td><td>' . l('Кол-во проинвентаризовано') .'</td><td>' . l('Недостача') .'</td></tr></thead><tbody>';
            if ($inventories) {

                $counts_items = (array)$this->all_configs['db']->query('SELECT inv.id, COUNT(DISTINCT i.id)
                      FROM {inventories} as inv, {warehouses_goods_items} as i, {inventories_goods} as invg
                      WHERE inv.id=?i AND i.wh_id=inv.wh_id AND inv.id=invg.inv_id AND i.goods_id=invg.goods_id GROUP BY i.goods_id',
                    array($this->all_configs['arrequest'][2]))->vars();
                $counts_inv_items = (array)$this->all_configs['db']->query('SELECT inv.id, COUNT(DISTINCT i.id)
                      FROM {inventories} as inv, {warehouses_goods_items} as i, {inventory_journal} as invj
                      WHERE inv.id=?i AND i.wh_id=inv.wh_id AND i.id=invj.item_id AND inv.id=invj.inv_id GROUP BY i.goods_id',
                    array($this->all_configs['arrequest'][2]))->vars();

                foreach ($inventories as $inv) {
                    $inv['count_items'] = isset($counts_items[$inv['id']]) ? $counts_items[$inv['id']] : 0;
                    $inv['count_inv_items'] = isset($counts_inv_items[$inv['id']]) ? $counts_inv_items[$inv['id']] : 0;
                    $right_html .= '<tr><td></td>';
                    $right_html .= '<td class="open-product-inv" onclick="open_product_inventory(this, ' . $inv['goods_id'] . ')">';
                    $right_html .= '<i class="' . ((isset($_GET['inv_p']) && $_GET['inv_p'] == $inv['goods_id']) ? 'glyphicon glyphicon-chevron-up' : 'glyphicon glyphicon-chevron-down' ) . '"></i>';
                    $right_html .= htmlspecialchars($inv['gtitle']) . '</td>';
                    $right_html .= '<td>' . $inv['count_items'] . '</td>';
                    $right_html .= '<td>' . $inv['count_inv_items'] . '</td>';
                    $right_html .= '<td>' . ($inv['count_items'] - $inv['count_inv_items']) . '</td>';
                    $right_html .= '<tr><td colspan="5" class="product-inventory">';// id="product-inventory-' . $inv['goods_id'] . '"

                    if (isset($_GET['inv_p']) && $_GET['inv_p'] == $inv['goods_id']) {
                        // журнал сканирований
                        $_inventories = $this->all_configs['db']->query('SELECT w.title as wtitle, inv.wh_id as inv_wh_id,
                              i.order_id, i.wh_id, i.id as item_id, invj.date_scan, i.serial, u.email, u.login, u.fio, i.price
                            FROM {goods} as g, {warehouses} as w, {warehouses_goods_items} as i
                            LEFT JOIN {inventories} as inv ON inv.id=?i
                            LEFT JOIN (SELECT date_scan, inv_id, item_id, scanned, user_id, wh_id FROM {inventory_journal}
                              ORDER BY date_scan DESC)invj ON invj.inv_id=inv.id AND i.id=invj.item_id
                            LEFT JOIN {users} as u ON u.id=invj.user_id
                            WHERE i.goods_id=g.id AND i.goods_id=?i AND w.id=i.wh_id
                              AND (inv.wh_id=i.wh_id OR invj.date_scan IS NOT NULL) GROUP BY i.id',
                            array($this->all_configs['arrequest'][2], $inv['goods_id']))->assoc();

                        $not_on_this_stock = '';
                        if ($_inventories) {
                            $right_html .= '<table class="table table-striped"><thead><tr><td></td><td>' . l('Сер. номер') .'</td><td>'.l('Дата').'</td>';
                            $right_html .= '<td>' . l('Кладовщик') .'</td><td>' . l('Склад') . '</td><td>' . l('Заказ') . '</td><td>' . l('Цена') .', ';
                            $right_html .= $this->all_configs['suppliers_orders']->currencies[$this->all_configs['suppliers_orders']->currency_suppliers_orders]['shortName'];
                            $right_html .= '</td><td></td></tr></thead><tbody>';
                            $i = 1;$j = 1;
                            foreach ($_inventories as $_inv) {
                                $_inv['scanned'] = suppliers_order_generate_serial($_inv);
                                if ($_inv['inv_wh_id'] != $_inv['wh_id']) {
                                    $_inv['i'] = $i;$i++;
                                    $not_on_this_stock .= $this->display_scanned_item($_inv, $_inv['inv_wh_id']);
                                } else {
                                    $_inv['i'] = $j;$j++;
                                    $right_html .= $this->display_scanned_item($_inv, $_inv['inv_wh_id']);
                                }
                            }
                            $right_html .= '</tbody></table>';
                            if (!empty($not_on_this_stock)) {
                                $right_html .= '<table class="table table-striped"><thead><tr><td></td><td>' . l('Сер. номер') .'</td><td>'.l('Дата').'</td>';
                                $right_html .= '<td>' . l('Кладовщик') .'</td><td>' . l('Склад') . '</td><td>' . l('Заказ') . '</td><td>' . l('Цена') .', ';
                                $right_html .= $this->all_configs['suppliers_orders']->currencies[$this->all_configs['suppliers_orders']->currency_suppliers_orders]['shortName'];
                                $right_html .= '</td></tr></thead><tbody>';
                                $right_html .= $not_on_this_stock;
                                $right_html .= '</tbody></table>';
                            }
                        } else {
                            $right_html .= l('Изделий нет на складе');
                        }
                    }
                    $right_html .= '</td></tr>';
                }
            } else {
                $right_html .= '<td colspan="5">' . l('Нет изделий') .'</td>';
            }
            $right_html .= '</tbody></table>';
        }

        return array(
            'html' => '<div class="span2">' . $left_html . '</div><div class="span10">' . $right_html . '</div>',
            'functions' => array('multiselect_goods(2)'),
        );
    }

    /**
     * @return array
     */
    function warehouses_inventories_writeoff()
    {
        $left_html = $right_html = '';

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {
            // левое меню
            $left = $this->inventories_left_menu(3);
            $left_html .= $left['html'];

            // журнал списаний
            $inventories = $this->all_configs['db']->query('SELECT g.title as gtitle, i.price, w.title as wtitle,
                      g.id as goods_id, i.wh_id, i.id as item_id, i.serial, inv.wh_id as inv_wh_id, i.id as write_off_item_id
                    FROM {goods} as g, {warehouses} as w, {warehouses_goods_items} as i
                    LEFT JOIN {inventories} as inv ON inv.id=?i
                    RIGHT JOIN {inventories_goods} as invg ON invg.inv_id=inv.id AND i.goods_id=invg.goods_id
                    LEFT JOIN {inventory_journal} as invj ON invj.inv_id=inv.id AND i.id=invj.item_id
                    WHERE i.goods_id=g.id AND w.id=i.wh_id AND inv.wh_id=i.wh_id AND invj.id IS NULL GROUP BY i.id',
                array($this->all_configs['arrequest'][2]))->assoc();

            $right_html .= '<table class="table table-striped"><thead><tr><td></td><td>' . l('Сер. номер') .'</td>';
            $right_html .= '<td>' . l('Наименование') . '</td><td>' . l('Склад') . '</td><td>' . l('Цена') . ', ';
            $right_html .= $this->all_configs['suppliers_orders']->currencies[$this->all_configs['suppliers_orders']->currency_suppliers_orders]['shortName'];
            $right_html .= '</td><td><input type="checkbox" class="checked_all_writeoff" onchange="checked_all_writeoff(this)" /></td></tr></thead><tbody>';

            if ($inventories) {
                $i = 1;
                foreach ($inventories as $inv) {
                    $inv['scanned'] = suppliers_order_generate_serial($inv);
                    $inv['i'] = $i;$i++;
                    $right_html .= $this->display_scanned_item($inv, $inv['inv_wh_id']);

                }
                $right_html .= '<tr><td colspan="6"><input class="btn" onclick="write_off_item(this)" value="' . l('Списать') .'" type="button" /></td>';
            } else {
                $right_html .= '<td colspan="6">' . l('Нет сканированых изделий') .'</td>';
            }
            $right_html .= '</tr></tbody></table>';
        }

        return array(
            'html' => '<div class="span2">' . $left_html . '</div><div class="span10">' . $right_html . '</div>',
            'functions' => array('multiselect_goods(3)'),
        );
    }

    /**
     * @param $inv
     * @param $wh_id
     * @return string
     */
    function display_scanned_item($inv, $wh_id)
    {
        $out = '';

        if ($inv) {
            $class = ($wh_id != $inv['wh_id']) ? 'error' : (array_key_exists('date_scan', $inv) && $inv['date_scan'] > 0 ? 'success' : '');
            $out = '<tr class="' . $class . '"><td>' . $inv['i'] . '</td>';
            if (array_key_exists('scanned', $inv)) {
                $out .= '<td><a href="' . $this->all_configs['prefix'] . 'warehouses?serial=' . $inv['scanned'] . '#show_items">';
                $out .= htmlspecialchars($inv['scanned']) . '</a></td>';
            }
            if (array_key_exists('date_scan', $inv)) {
                $out .= '<td><span title="' . do_nice_date($inv['date_scan'], false) . '">' . do_nice_date($inv['date_scan']) . '</span></td>';
            }
            if (array_key_exists('gtitle', $inv)) {
                $out .= '<td><a href="' . $this->all_configs['prefix'] . 'products/create/' . $inv['goods_id'] . '">';
                $out .= htmlspecialchars($inv['gtitle']) . '</a></td>';
            }
            if (array_key_exists('fio', $inv)) {
                $out .= '<td>' . get_user_name($inv) . '</td>';
            }
            $class = (empty($class) ? 'assumption' : '');
            $out .= '<td class="' . $class . '">' . htmlspecialchars($inv['wtitle']) . '</td>';
            if (array_key_exists('order_id', $inv)) {
                $out .= '<td><a href="' . $this->all_configs['prefix'] . 'orders/create/' . $inv['order_id'] . '">' . $inv['order_id'] . '</a></td>';
            }
            if (array_key_exists('price', $inv)) {
                $out .= '<td>' . show_price($inv['price']) . '</td>';
            }
            if (array_key_exists('write_off_item_id', $inv)) {
                $out .= '<td><input type="checkbox" value="' . $inv['item_id'] . '" class="writeoff-items" /></td>';
            }
            $out .= '</tr>';
        }

        return $out;
    }

    /**
     * @param $acive_btn
     * @return array
     */
    function inventories_left_menu($acive_btn)
    {
        $left_html = '';
        $inventory = null;

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {
            // запрос для складов за которыми закреплен юзер
            $q = $this->all_configs['chains']->query_warehouses();
            $query = $q['query_for_move_item'];

            // список инвентаризаций
            $inventory = $this->all_configs['db']->query('SELECT it.id, it.date_start, it.date_stop, w.title, it.wh_id, it.user_id
                    FROM {inventories} as it, {warehouses} as w ?query AND w.id=it.wh_id AND it.id=?i',
                array($query, $this->all_configs['arrequest'][2]))->row();

            if ($inventory) {
                $left_html .= '<a href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '#inventories-list">&#8592; ' . l('к списку') .'</a>';
                $left_html .= '<p>' . l('Инвентаризация номер') .'' . $inventory['id'] . '</p>';
                $left_html .= '<p>' . l('Склад') . ': <a href="' . $this->all_configs['prefix'] . 'warehouses?whs=' . $inventory['wh_id'] . '#show_items">';
                $left_html .= htmlspecialchars($inventory['title']) . '</a></p>';
                $left_html .= '<p>' . l('Дата открытия') .': <span title="' . do_nice_date($inventory['date_start'], false) . '">' . do_nice_date($inventory['date_start']) . '</span></p>';
                if ($inventory['date_stop'] > 0) {
                    $left_html .= '<p>' . l('Дата закрытия') .': <span title="' . do_nice_date($inventory['date_stop'], false) . '">' . do_nice_date($inventory['date_stop']) . '</span></p>';
                } else {
                    $left_html .= '<input onclick="close_inventory(this, \'' . $inventory['id'] . '\')" type="button" ';
                    $left_html .= ($_SESSION['id'] == $inventory['user_id'] ? '' : 'disabled') . ' value="' . l('Закрыть') .'" class="btn close-inv" />';
                }
                $left_html .= '<div class="btn-group">';
                $left_html .= '<select class="multiselect-goods multiselect-goods-tab-' . $acive_btn . '" multiple="multiple"></select>';
                $left_html .= '<button onclick="add_goods_to_inv(this, ' . $acive_btn . ')" class="btn btn-primary">' . l('Ок') .'</button></div>';
                $left_html .= '<br /><br /><div class="btn-group" data-toggle="buttons-radio">';
                $left_html .= '<div><button type="button" onclick="click_tab_hash(\'#inventories-journal\')" class="btn ' . ($acive_btn == 1 ? 'active' : '') . '">' . l('Журнал.') .'</button></div>';
                $left_html .= '<div><button type="button" onclick="click_tab_hash(\'#inventories-listinv\')" class="btn ' . ($acive_btn == 2 ? 'active' : '') . '">' . l('Лист инв.') .'</button></div>';
                $left_html .= '<div><button type="button" onclick="click_tab_hash(\'#inventories-writeoff\')" class="btn ' . ($acive_btn == 3 ? 'active' : '') . '">' . l('Списание') .'</button></div></div>';
            }
        }

        return array(
            'html' => $left_html,
            'open' => ($inventory && $inventory['date_stop'] == 0 ? true : false),
            'inv' => $inventory,
        );
    }

    /**
     * @param     $warehouses_options
     * @param int $i
     * @return string
     */
    function filter_block($warehouses_options, $i = 1)
    {
        $wh_select = '';
        if (isset($_GET['whs'])) {
            $wh_select = $this->all_configs['suppliers_orders']->gen_locations($_GET['whs'],
                isset($_GET['lcs']) ? $_GET['lcs'] : null);
        }
        // фильтр по серийнику
        return $this->view->renderFile('warehouses/filter_block', array(
            'warehousesOptions' => $warehouses_options,
            'i' => $i,
            'whSelect' => $wh_select
        ));
    }

    /**
     * @param      $goods
     * @param      $query_for_noadmin
     * @param null $type
     * @param int  $count_page
     * @return string
     */
    function show_goods($goods, $query_for_noadmin, $type = null, $count_page = 1)
    {
        $out = '';

        $out .= $this->all_configs['suppliers_orders']->append_js();
        $out .= $this->all_configs['chains']->append_js();

        if ($goods && count($goods) > 0) {

            switch($type) {

                case (1):

                    $out .= '<table class="table table-striped"><thead><tr><td>' . l('Серийный номер') . '</td><td>' . l('Наименование') . '</td><td>'.l('Дата').'</td>';
                    $out .= '<td>' . l('Склад') . '</td><td>' . l('Локация') . '</td><td>' . l('Заказ клиента') . '</td><td>' . l('Заказ поставщику') . '</td><td>' . l('Цена') . '</td><td>' . l('Поставщик') . '</td></tr></thead><tbody>';

                    foreach ($goods as $product) {
                        $out .= '<tr>' .
                            '<td>' . suppliers_order_generate_serial($product, true, true) . '</td>' .
                            '<td><a class="hash_link" href="' . $this->all_configs['prefix'] . 'products/create/' . $product['goods_id'] . '#financestock-stock">' . htmlspecialchars($product['product_title']) . '</a></td>' .
                            '<td><span title="' . do_nice_date($product['date_add'], false) . '">' . do_nice_date($product['date_add']) . '</span></td>' .
                            '<td><a class="hash_link" href="' . $this->all_configs['prefix'] . 'warehouses?whs=' . $product['id'] . '#show_items">' . htmlspecialchars($product['title']) . '</a></td>' .
                            '<td><a class="hash_link" href="' . $this->all_configs['prefix'] . 'warehouses?whs=' . $product['id'] . '&lcs=' . $product['location_id'] . '#show_items">' . htmlspecialchars($product['location']) . '</a></td>' .
                            '<td>' . ($product['order_id'] > 0 ? '<a class="hash_link" href="' . $this->all_configs['prefix'] . 'orders/create/' . $product['order_id'] . '">' . $product['order_id'] . '</a>' : '') . '</td>' .
                            '<td><a class="hash_link" href="' . $this->all_configs['prefix'] . 'warehouses?so_id=' . $product['supplier_order_id'] . '#show_items">' . $product['supplier_order_id'] . '</a></td>' .
                            '<td>' . $this->show_price($product['price']) . '</td>' .
                            '<td>' . htmlspecialchars($product['contractor_title']) . '</td>' .
                            '</tr>';
                    }

                    $out .= '<tr><td colspan="7"></td><td colspan="2">';
                    $url = $this->all_configs['prefix'] . (isset($this->all_configs['arrequest'][0]) ? $this->all_configs['arrequest'][0] . '/' : '') . 'ajax';
                    $out .= '<form target="_blank" method="get" action="' . $url . '" class="form-horizontal">';
                    $out .= '<input name="act" value="exports-items" type="hidden" />';
                    if (isset($_GET['whs']))
                        $out .= '<input name="whs" value="' . $_GET['whs'] . '" type="hidden" />';
                    if (isset($_GET['lcs']))
                        $out .= '<input name="lcs" value="' . $_GET['lcs'] . '" type="hidden" />';
                    if (isset($_GET['pid']))
                        $out .= '<input name="pid" value="' . $_GET['pid'] . '" type="hidden" />';
                    if (isset($_GET['d']))
                        $out .= '<input name="d" value="' . $_GET['d'] . '" type="hidden" />';
                    $out .= '<input type="submit" value="' . l('Выгрузить данные') . '" class="btn btn-small btn-primary"></form>';
                    $out .= '</td></tr>';
                    $out .= '</tbody></table>';

                    break;


                case (2):

                    foreach ($goods as $product) {

                        $out .= '<h4>' . l('Наименование') .'</h4>';
                        $out .= '<table class="table table-striped"><tbody>';
                        $out .= '<tr><td><b>' . l('Серийный номер') .'</b> ';
                        if ($this->all_configs['oRole']->hasPrivilege('site-administration') && mb_strlen($product['serial'], 'UTF-8') > 0
                                && $product['id'] == $this->all_configs['configs']['erp-warehouse-type-mir']) {
                            $out .= '<input class="btn btn-small btn-danger" onclick="clear_serial(this, ' . $product['item_id'] . ')" type="button" value="' . l('Удалить серийник') . '" />';
                        }
                        $out .= '</td><td>' . suppliers_order_generate_serial($product);
                        $out .= print_link($product['item_id'], 'label') . '</td></tr>';
                        if (mb_strlen(trim($product['serial_old']), 'utf-8') > 0) {
                            $out .= '<tr><td><b>' . l('Серийный номер') .' (' . l('старый') . ')</b></td><td>' . htmlspecialchars($product['serial_old']) . '</td></tr>';
                        }
                        $out .= '<tr><td><b>' . l('Наименование') . '</b></td><td><a class="hash_link" href="' . $this->all_configs['prefix'] . 'products/create/' . $product['goods_id'] . '#financestock-stock">';
                        $out .= htmlspecialchars($product['product_title']) . '</a></td></tr>';
                        $out .= '<tr><td><b>' . l('Поставщик') .'</b></td><td>' . htmlspecialchars($product['contractor_title']) . '</td></tr>';
                        $out .= '<tr><td><b>' . l('Заказ поставщика') . '</b></td><td>';
                        $out .= (($product['supplier_order_id'] > 0) ? '<a class="hash_link" href="' . $this->all_configs['prefix'] . 'orders/edit/' . $product['supplier_order_id'] . '#create_supplier_order">'
                                . $this->all_configs['suppliers_orders']->supplier_order_number(array('id' => $product['supplier_order_id'])) . '</a>' : '') . '</td></tr>';
                        $out .= '<tr><td><b>' . l('Дата приходования') . '</b></td><td><span title="' . do_nice_date($product['date_add'], false) . '">' . do_nice_date($product['date_add']) . '</span></td></tr>';
                        $out .= '<tr><td><b>' . l('Цена') . '</b></td><td>' . $this->show_price($product['price']) . '</td></tr>';
                        $out .= '<tr><td><b>' . l('Склад') . '</b></td><td><a class="hash_link" href="' . $this->all_configs['prefix'];
                        $out .= $this->all_configs['arrequest'][0] . '?whs=' . $product['id'] . '#show_items">' . htmlspecialchars($product['title']) . '</td></tr>';

                        $out .= '<tr><td><b>' . l('Локация') . '</b></td><td><a class="hash_link" href="' . $this->all_configs['prefix'];
                        $out .= $this->all_configs['arrequest'][0] . '?whs=' . $product['id'] . '&lcs=' . $product['location_id'] . '#show_items">' . htmlspecialchars($product['location']) . '</td></tr>';

                        $out .= '<tr><td><b>' . l('Заказ') . '</b></td><td>' . (($product['order_id']) > 0 ? '<a class="hash_link" href="' . $this->all_configs['prefix'] . 'orders/create/' . $product['order_id'] . '">' . $product['order_id'] . '</a>' : '') . '</td></tr>';
                        $out .= '<tr><td><b>' . l('Дата продажи') . '</b></td><td><span title="' . do_nice_date($product['date_sold'], false) . '">' . do_nice_date($product['date_sold']) . '</span></td>';
                        $out .= '</tbody></table>';

                        $out .= '<div class="span12"><div class="span4 well">';
                        // форма перемещения изделий на склад
                        $out .= '<h4>' . l('Запрос на перемещение') . '</h4>';
                        $out .= $this->all_configs['chains']->moving_item_form($product['item_id']/*, null, $product['id']*/);
                        $out .= '</div><div class="span4">';
                        // форма продажи
                        $out .= $this->all_configs['chains']->form_sold_items($product['item_id'], $this->errors);
                        $out .= '</div><div class="span3">';
                        // форма списания изделия
                        $out .= $this->all_configs['chains']->form_write_off_items($product['item_id'], $this->errors);
                        // форма возврата изделия поставщику
                        $out .= $this->all_configs['chains']->return_supplier_order_form($product['item_id']);
                        $out .= '</div></div>';
                        $out .= '<h4>' . l('История перемещений') . '</h4>';

                        $item_history = $this->all_configs['db']->query('SELECT m.item_id, m.date_move, m.user_id, m.wh_id,
                              m.comment, w.title, u.fio, u.email, m.order_id, l.location
                            FROM {users} as u, {warehouses} as w, {warehouses_stock_moves} as m, {warehouses_locations} as l
                            WHERE m.item_id=?i AND u.id=m.user_id AND w.id=m.wh_id AND l.id=m.location_id ?query
                            ORDER BY m.date_move DESC, m.id DESC',
                        array($product['item_id'], $query_for_noadmin))->assoc();

                        if (count($item_history) > 0) {
                            $out .= '<table class="table"><thead><tr><td>' . l('Склад') . '</td><td>' . l('Локация') . '</td><td>' . l('Ответственный') . '</td><td>'.l('Дата').'</td>';
                            $out .= '<td>' . l('Операция') . '</td><td>' . l('На основании') . ' (' . l('номер заказа') . ')</td></tr></thead><tbody>';
                            foreach ($item_history as $history) {
                                $out .= '<tr><td><a class="hash_link" href="' . $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?whs=' . $history['wh_id'] . '#show_items">' . htmlspecialchars($history['title'])  . '</a></td>';
                                $out .= '<td>' . htmlspecialchars($history['location']) . '</td>';
                                $out .= '<td>' . get_user_name($history) . '</td>';
                                $out .= '<td><span title="' . do_nice_date($history['date_move'], false) . '">' . do_nice_date($history['date_move']) . '</span></td>';
                                $out .= '<td>' . htmlspecialchars($history['comment']) . '</td>';
                                //if ($history['order_id'] > 0) {
                                    $out .= '<td><a href="' . $this->all_configs['prefix'] . 'orders/create/' . $history['order_id'] . '">';
                                    $out .= $history['order_id'] . '</a></td></tr>';
                                //} else {
                                //    $out .= '<td><a href="' . $this->all_configs['prefix'] . 'logistics#motions">';
                                //    $out .= $history['h_id'] . '</a></td></tr>';
                                //}
                            }
                            $out .= '</tbody></table>';
                        } else {
                            $out .= l('История перемещений не найдена');
                        }
                        $out .= '</td></tr>';

                        // сканироавания
                        /*$out .= '<h4>Сканирования</h4>';
                        // журнал сканирований
                        $inventories = $this->all_configs['db']->query('SELECT invj.id as item_id, invj.date_scan,
                                  w.title as wtitle, u.login, u.fio, u.email, invj.wh_id, inv.wh_id as inv_wh_id
                                FROM {inventory_journal} as invj, {warehouses} as w, {goods} as g, {users} as u, {inventories} as inv
                                WHERE w.id=invj.wh_id AND invj.item_id=?i AND g.id=invj.goods_id AND u.id=invj.user_id AND inv.id=invj.inv_id
                                ORDER BY invj.date_scan DESC',
                            array($product['item_id']))->assoc();

                        $out .= '<table class="table table-striped"><thead><tr><td></td><td>'.l('Дата').'</td>';
                        $out .= '<td>Кладовщик</td><td>' . l('Склад') . '</td></tr></thead><tbody">';
                        if ($inventories) {
                            $i = 1;
                            foreach ($inventories as $inv) {
                                $inv['i'] = $i;$i++;
                                $out .= $this->display_scanned_item($inv, $inv['inv_wh_id']);
                            }
                        } else {
                            $out .= '<td colspan="6">Сканирований нет</td>';
                        }
                        $out .= '</tbody></table>';*/

                        // список цепочки
                        /*$chains = $this->all_configs['chains']->get_chains(null, $product['item_id']);

                        $out .= '<h4>Бизнес цепочки</h4>';
                        if (count($chains) > 0) {
                            $out .= '<table class="table table-bordered"><thead><tr><td></td><td>'.l('Дата').'</td><td>' . l('Наименование') . '</td>';
                            //if (array_key_exists('order_id', $chains[key($chains)]))
                            //    $out .= '<td>' . l('Заказ') . '</td>';
                            $out .= '<td>' . l('Заказ') . '</td><td>Склад куда</td><td>Серийник</td><td>Продажа</td><td></td></tr></thead><tbody>';
                            foreach ($chains as $h_id => $chain) {
                                $out .= $this->all_configs['chains']->show_chain($chain, false);
                            }
                            $out .= '</tbody></table>';
                        } else {
                            $out .= '<p class="text-error">Нет цепочек</p>';
                        }*/
                    }

                    break;


                    default:

                        $out .= '<table class="table table-hover table-medium"><thead><tr><td></td><td>' . l('Серийный номер') . '</td>';
                        $out .= '<td>' . l('Наименование') . '</td><td>'.l('Дата').'</td><td>' . l('Склад') . '</td><td>' . l('Заказ') . '</td>';
                        if ($this->all_configs['oRole']->hasPrivilege('logistics')) {
                            $out .= '<td>' . l('Цена') . '</td>';
                        }
                        $out .= '<td>' . l('Кол-во') .'</td><td>' . l('Поставщик') . '</td></tr></thead><tbody>';

                        $queryString = array();
                        foreach ($_GET as $key => $value) {
                            if ($key != 'act' && $key != 'goods')
                                $queryString[] = $key . '=' . $value;
                        }
                        $queryString = $this->all_configs['prefix'] . 'warehouses?' . implode('&', $queryString) . '&goods=';

                        foreach ($goods as $product) {
                            $f_goods = $_f_goods =isset($_GET['goods']) ? array_filter(explode('-', $_GET['goods'])) : array();
                            if (in_array($product['goods_id'], $f_goods)) {
                                $pos = array_search($product['goods_id'], $f_goods);
                                if ($pos !== false) {
                                    unset($_f_goods[$pos]);
                                }
                                $url = $queryString . implode('-', $_f_goods);
                                $out .= '<tr class="border-top well cursor-pointer" onclick="window.location.href=\'' . $url . '\' + window.location.hash">';
                                $out .= '<td><i class="glyphicon glyphicon-chevron-up"></i></td>';
                            } else {
                                array_push($_f_goods, $product['goods_id']);
                                $url = $queryString . implode('-', $_f_goods);
                                $out .= '<tr class="border-top cursor-pointer" onclick="window.location.href=\'' . $url . '\' + window.location.hash">';
                                $out .= '<td><i class="glyphicon glyphicon-chevron-down"></i></td>';
                            }
                            $out .= '<td>' . '</td>';
                            $out .= '<td><a class="hash_link" href="' . $this->all_configs['prefix'] . 'products/create/' . $product['goods_id'] . '#financestock-stock">' . htmlspecialchars($product['product_title']) . '</a></td>';                            $out .= '<td>' . '</td>';
                            $out .= '<td>' . '</td>';
                            $out .= '<td>' . '</td>';
                            $out .= '<td>' . '</td>';
                            $out .= '<td>' . $product['qty_wh'] . '</td>';
                            $out .= '<td>' . '</td>';
                            $out .= '</tr>';
                            if (in_array($product['goods_id'], $f_goods)) {
                                $items = $this->all_configs['db']->query('SELECT i.id as item_id, serial, i.price,
                                          i.date_add, ct.title, w.title as wtitle, i.order_id
                                        FROM {warehouses_goods_items} as i
                                        LEFT JOIN {contractors} as ct ON i.supplier_id=ct.id
                                        LEFT JOIN {warehouses} as w ON w.id=i.wh_id
                                        WHERE i.goods_id=?i AND i.wh_id IN (?li)',
                                    array($product['goods_id'], explode(',', $_GET['whs'])))->assoc();
                                if ($items) {
                                    foreach ($items as $item) {
                                        // можем ли мы использовать изделие
                                        $can = $this->all_configs['chains']->can_use_item($item['item_id']);
                                        $out .= '<tr><td>';
                                        if ($can)
                                            $out .= '<input onclick="checked_item()" type="checkbox" class="check-item" value="' . $item['item_id'] . '" />';
                                        $out .= '</td>';
                                        $out .= '<td>' . suppliers_order_generate_serial($item, true, true) . '</td>';
                                        $out .= '<td><a class="hash_link" href="' . $this->all_configs['prefix'] . 'products/create/' . $product['goods_id'] . '#financestock-stock">' . htmlspecialchars($product['product_title']) . '</a></td>';
                                        $out .= '<td><span title="' . do_nice_date($item['date_add'], false) . '">' . do_nice_date($item['date_add']) . '</span></td>';
                                        $out .= '<td>' . htmlspecialchars($item['wtitle']) . '</td>';
                                        $out .= '<td>' . $item['order_id'] .'</td>';
                                        if ($this->all_configs['oRole']->hasPrivilege('logistics')) {
                                            $out .= '<td>' . show_price($item['price']) . '</td>';
                                        }
                                        $out .= '<td>' . ($can ? 1 : 0) . '</td>';
                                        $out .= '<td>' . htmlspecialchars($item['title']) . '</td>';
                                        $out .= '</tr>';
                                    }
                                } else {
                                    $out .= '<tr><td colspan="9">' . l('Изделий нет') .'</td></tr>';
                                }
                            }
                        }

                        $out .= '</tbody></table>';

                        $items = $this->all_configs['db']->query('SELECT COUNT(i.id) as count, SUM(i.price) as sum
                                        FROM {warehouses_goods_items} as i
                                        WHERE i.goods_id=?i AND i.wh_id IN (?li)',
                            array($product['goods_id'], explode(',', $_GET['whs'])))->row();
                        $out .= '<p>' . l('Всего отфильтровано') .': ' . ($items ? 1 * $items['count'] : 0) . ' ' . l('шт.') . '</p>';
                        if ($this->all_configs['oRole']->hasPrivilege('logistics')) {
                            $out .= '<p>' . l('На сумму') .': ' . show_price($items['sum']) . ' ';
                            $currency_suppliers_orders = $this->all_configs['suppliers_orders']->currency_suppliers_orders;
                            $currencies = $this->all_configs['suppliers_orders']->currencies;
                            $out .= $currencies[$currency_suppliers_orders]['shortName']. '</p>';
                        }
                        $out .= '<p>' . l('Печать') .': <a onclick="global_print_labels()"><i class="cursor-pointer fa fa-print"></i></a></p>';
//                        $out .= '<div class="span4 well"><h4>' . l('Запрос на перемещение') .'</h4>' . $this->all_configs['chains']->moving_item_form(0) . '</div>';
//                        $out .= '<div class="span4">' . $this->all_configs['chains']->form_sold_items(0) . '</div>';
//                        $out .= '<div class="span3">' . $this->all_configs['chains']->form_write_off_items(0) . '</div>';
//                        $out .= '<div class="span3">' . $this->all_configs['chains']->return_supplier_order_form(0) . '</div>';
            }

        } else {
            $out .= '<p class="text-error">' . l('Товаров нет') .'</p>';
        }
        $out .= page_block($count_page, count($goods), '#show_items');

        return $out;
    }

    /**
     * @param null $warehouse
     * @param int  $i
     * @return string
     */
    function form_warehouse($warehouse = null, $i = 1)
    {
        $consider_all = $warehouses_locations = $consider_store = '';

        $groups = (array)$this->all_configs['db']->query('SELECT * FROM {warehouses_groups}')->assoc();
        $types = (array)$this->all_configs['db']->query('SELECT * FROM {warehouses_types}')->assoc();
        $warehouses_type = '<select name="type" class="form-control"><option value=""></option>';
        $warehouses_types = '<select name="type_id" class="form-control"><option value=""></option>';
        $warehouses_groups = '<select name="group_id" class="form-control"><option value=""></option>';
        if ($warehouse == null) {
            foreach ($this->all_configs['configs']['erp-warehouses-types'] as $w_id=>$w_name) {
                // если не тип "недостача"
                if($w_id != 2) {
                    $warehouses_type .= '<option value="' . $w_id . '">' . $w_name . '</option>';
                }
            }
            foreach ($types as $type) {
                $warehouses_types .= '<option value="' . $type['id'] . '">' . $type['name'] . '</option>';
            }
            foreach ($groups as $group) {
                $warehouses_groups .= '<option value="' . $group['id'] . '">' . $group['name'] . '</option>';
            }

            $btn = "<input type='submit' class='btn' name='warehouse-add' value='" . l('Создать') . "' />";
            $accordion_title = l('Создать склад');
            $consider_store = 'checked';
            $consider_all = 'checked';
            $title = '';
            $code_1c = '';
            $print_address = '';
            $print_phone = '';
        } else {
            foreach ($this->all_configs['configs']['erp-warehouses-types'] as $w_id=>$w_name) {
                if ($w_id == $warehouse['type'])
                    $warehouses_type .= '<option selected value="' . $w_id . '">' . $w_name . '</option>';
                else
                    $warehouses_type .= '<option value="' . $w_id . '">' . $w_name . '</option>';
            }
            foreach ($types as $type) {
                $selected = $type['id'] == $warehouse['type_id'] ? 'selected' : '';
                $warehouses_types .= '<option ' . $selected . ' value="' . $type['id'] . '">' . $type['name'] . '</option>';
            }
            foreach ($groups as $group) {
                $selected = $group['id'] == $warehouse['group_id'] ? 'selected' : '';
                $warehouses_groups .= '<option ' . $selected . ' value="' . $group['id'] . '">' . $group['name'] . '</option>';
            }

            $btn = "<input type='hidden' name='warehouse-id' value='{$warehouse['id']}' /><input type='submit' class='btn' name='warehouse-edit' value='" . l('Редактировать') . "' />";
            $accordion_title = l('Редактировать склад') . ' "' . $warehouse['title'] . '"';
            $title = htmlspecialchars($warehouse['title']);
            $code_1c = htmlspecialchars($warehouse['code_1c']);
            $print_address = htmlspecialchars($warehouse['print_address']);
            $print_phone = htmlspecialchars($warehouse['print_phone']);
            if ($warehouse['consider_all'] == 1)
                $consider_all = 'checked';
            if ($warehouse['consider_store'] == 1)
                $consider_store = 'checked';
            if (count($warehouse['locations']) > 0) {
                foreach ($warehouse['locations'] as $location_id=>$location) {
                    $warehouses_locations .= '<input type="text" class="form-control" value="' . htmlspecialchars($location) . '" name="location-id[' . $location_id . ']">';
                }
            }
        }
        $warehouses_type .= '</select>';
        $warehouses_types .= '</select>';
        $warehouses_groups .= '</select>';

        $warehouses_locations .= '<input type="text" name="location[]" class="form-control" required>';
        $onclick = '$(\'<input>\').attr({type: \'text\', name: \'location[]\', class: \'form-control\'}).insertBefore(this);';
        $warehouses_locations .= '<i onclick="' . $onclick . '" class="glyphicon glyphicon-plus cursor-pointer"></i>';

        if ($i == 1) {
            $in = 'in';
        } else {
            $in = '';
        }

        return "
            <div class='panel panel-default'>
                <div class='panel-heading'>
                    <a class='accordion-toggle' data-toggle='collapse' data-parent='#accordion_warehouses' href='#collapse_warehouse_{$i}'>{$accordion_title}</a>
                </div>
                <div id='collapse_warehouse_{$i}' class='panel-body collapse {$in}'>
                    <div class='panel-body'>
                        <form method='POST'>
                            <div class='form-group'><label>" . l('Название') . ": </label>
                                <input placeholder='" . l('введите название') . "' class='form-control' name='title' value='{$title}' required /></div>
                            <!--<div class='form-group'><label>" . l('Код 1с') . ": </label>
                                <input placeholder='" . l('введите код 1с') . "' class='form-control' name='code_1c' value='{$code_1c}' /></div>
                            --><div class='form-group'>
                                <div class='checkbox'><label><input data-consider={$i} {$consider_store} type='checkbox' onclick='consider(this, \"{$i}\")' class='btn consider_{$i}' name='consider_store' value='1' />" . l('Учитывать в свободном остатке') . "</label></div>
                                <div class='checkbox'><label><input {$consider_all} type='checkbox' class='btn consider_{$i}' onclick='consider(this, \"{$i}\")' name='consider_all' value='1' />" . l('Учитывать в общем остатке') . "</label></div>
                            </div>
                            <div class='form-group'>
                            <input type='hidden' value='1' name='type' />
                            </div>
                            <div class='form-group'><label>" . l('Принадлежность к Сервисному центру') . ": </label>
                                {$warehouses_groups}</div>
                            <div class='form-group'><label>" . l('Категория') . ": </label>
                                {$warehouses_types}</div>
                            <div class='form-group'><label>
                                " . l('Адрес для квитанции') . ": </label>
                                <input class='form-control' name='print_address' value='{$print_address}' />
                            </div>
                            <div class='form-group'><label>
                                " . l('Телефон для квитанции') . ": </label>
                                <input class='form-control' name='print_phone' value='{$print_phone}' />
                            </div>
                            <div class='form-group'><label>" . l('Локации') . ": </label>
                                {$warehouses_locations}</div>
                            <div class='form-group'>{$btn}</div>
                        </form>
                    </div>
                </div>
            </div>
        ";
    }

    /**
     *
     */
    function ajax()
    {
        $data = array(
            'state' => false
        );

        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $mod_id = $this->all_configs['configs']['warehouses-manage-page'];

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        // проверка доступа
        if ($this->can_show_module() == false) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => l('Нет прав'), 'state' => false));
            exit;
        }

        // грузим табу
        if ($act == 'tab-load') {
            if (isset($_POST['tab']) && !empty($_POST['tab'])) {
                header("Content-Type: application/json; charset=UTF-8");
                $this->preload();
                if (method_exists($this, $_POST['tab'])) {
                    $function = call_user_func_array(
                        array($this, $_POST['tab']),
                        array((isset($_POST['hashs']) && mb_strlen(trim($_POST['hashs'], 'UTF-8')) > 0) ? trim($_POST['hashs']) : null)
                    );
                    $return = array(
                        'html' =>  $function['html'],
                        'state' => true,
                        'functions' => $function['functions']
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

        // експорт изделий
        if ($act == 'exports-items') {
            include_once $this->all_configs['sitepath'] . 'shop/exports.class.php';
            $exports = new Exports();
            $goods = $this->getItems($_GET, null, null, true);
            $exports->build($goods);
        }

        // перемещение сканером
        if ($act == 'scanner-moves' && $this->all_configs['oRole']->hasPrivilege('scanner-moves')) {
            $scan = isset($_POST['scanned'][1]) ? '"' . $_POST['scanned'][1] . '"' : '';
            $data['msg'] =  l('Сканирование') . ' ' . htmlspecialchars($scan) . ' ' .  l('не найдено');

            $order = $item = $location = null;
            $order_prefix = 'Z-'; // префикс для заказа
            $item_prefix = ''; // префикс для  изделия
            $location_prefix = 'L-'; // префикс для локации

            if (isset($_POST['scanned']) && is_array($_POST['scanned'])) {
                foreach ($_POST['scanned'] as $scanned) {

                    if (preg_match('/' . $order_prefix . '((?!' . $location_prefix . ').+)?/', trim($scanned), $matches)) {
                        $data['msg'] = l('Заказ') .  ' ' . htmlspecialchars($scan) . ' ' . l('на ремонт не найден');
                        if (isset($matches[1]) && intval($matches[1]) > 0) {
                            if ($item) {
                                $item = null;
                            }
                            $order = $this->all_configs['db']->query('SELECT id FROM {orders} WHERE id=?i',
                                array(intval($matches[1])))->row();
                            if ($order) {
                                $data['msg'] = l('Заказ') . ' №' . $order['id'];
                                $data['state'] = true;
                            }
                        }
                    }

                    if (preg_match('/' . $item_prefix . '((?!' . $order_prefix . '|' . $location_prefix . ').+)?/', trim($scanned), $matches)) {
                        if (isset($matches[1]) && suppliers_order_generate_serial(array('serial' => $matches[1]), false) > 0) {
                            $data['msg'] = l('Изделие') .  ' ' . htmlspecialchars($scan) . ' ' .  l('не найдено');
                            /*if ($order) {
                                $order = null;
                            }*/
                            $item = $this->all_configs['db']->query(
                                'SELECT id as item_id, serial, order_id, goods_id, supplier_order_id FROM {warehouses_goods_items} WHERE id=?i',
                                array(suppliers_order_generate_serial(array('serial' => $matches[1]), false)))->row();
                            if ($item) {
                                $data['msg'] = $order ? l('Заказ') . ' №' . $order['id'] . '<br />' : '';
                                $data['msg'] .= l('Изделие') . ' ' . suppliers_order_generate_serial($item);
                                $data['state'] = true;
                            }
                        }
                    }

                    if (preg_match('/' . $location_prefix . '((?!' . $order_prefix . ').+)?/', trim($scanned), $matches)) {
                        $data['msg'] = l('Локация не найдена');
                        if (isset($matches[1]) && intval($matches[1]) > 0) {
                            // сперва нужно указать заказ или изделие
                            if (!$order && !$item) {
                                $location = null;
                                $data['msg'] = l('Укажите заказ или изделие');
                            } else {
                                $location = $this->all_configs['db']->query('SELECT l.location, w.title, l.id, l.wh_id
                                    FROM {warehouses_locations} as l, {warehouses} as w WHERE l.id=?i AND l.wh_id=w.id',
                                    array($matches[1]))->row();
                            }
                            if ($location) {
                                $data['msg'] = $order ? 'Заказ №' . $order['id'] . '<br />' : '';
                                $data['msg'] .= $item ? l('Изделие') . ' ' . suppliers_order_generate_serial($item) . '<br />' : '';
                                $data['msg'] .= l('Склад') . ' "' . htmlspecialchars($location['title']) . '", ' . l('локация') .' "' . htmlspecialchars($location['location']) . '"';
                                $data['state'] = true;
                            }
                        }
                    }
                }
            }

            if (($order && $item) || (($item || $order) && $location)) {
                $response = null;
                $msg = l('Произошла ошибка');

                if ($order && $item) {
                    $del_product = $del_order_item = false;
                    // достаем запчасть в заказе
                    $order_product_id = $this->all_configs['db']->query(
                        'SELECT id FROM {orders_goods} WHERE order_id=?i AND goods_id=?i AND (item_id IS NULL OR item_id=?i)',
                        array($order['id'], $item['goods_id'], $item['item_id']))->el();
                    if (!$order_product_id) {
                        // добавляем запчасть в заказ
                        $a = array('order_id' => $order['id'], 'product_id' => $item['goods_id'], 'confirm' => 0);
                        $response = $this->all_configs['chains']->add_product_order($a, $mod_id);
                        if ($response && $response['state'] == true && isset($response['id'])) {
                            $order_product_id = $response['id'];
                            $del_product = true;
                        } else {
                            $response['message'] = isset($response['msg']) ? $response['msg'] : $msg;
                        }
                    }
                    if ($order_product_id) {
                        // создаем заявку на запчасть
                        $a = array('order_id' => $order['id'], 'order_product_id' => $order_product_id, 'supplier_order_id' => $item['supplier_order_id']);
                        $response = $this->all_configs['chains']->order_item($mod_id, $a, false);
                        if ($response && $response['state'] == true) {
                            if (isset($data['id'])) {
                                $del_order_item = true;
                            }
                            // заказ - изделие (привязка)
                            $a = array('item_id' => $item['item_id'], 'order_product_id' => $order_product_id, 'confirm' => 1);
                            $response = $this->all_configs['chains']->bind_item_serial($a, $mod_id);
                            if ($response && $response['state'] == true) {
                                $msg = 'Изделие ' . suppliers_order_generate_serial($item) . ' ' . l('успешно выдано под ремонт') .' №' . $order['id'];
                                $del_order_item = $del_product = false;
                            }
                        }
                    }/* else {
                        $response = array('state' => false, 'message' => 'В заказе нет такой запчасти');
                    }*/
                    if ($order_product_id && ($del_order_item == true || $del_product == true)) {
                        // удаляем заявку
                        $this->all_configs['db']->query(
                            'DELETE FROM {orders_suppliers_clients} WHERE order_goods_id=?i', array($order_product_id));
                        if ($del_product == true) {
                            // удаляем товар из заказа на ремонт
                            $this->all_configs['db']->query('DELETE FROM {orders_goods} WHERE id=?i',
                                array($order_product_id));
                        }
                    }
                } elseif ($order && !$item && $location) {
                    // заказ - локация (перемещение)
                    $a = array('wh_id_destination' => $location['wh_id'], 'location' => $location['id'], 'order_id' => $order['id']);
                    $response = $this->all_configs['chains']->move_item_request($a);
                    $msg = l('Заказ') . ' №' . $order['id'] . ' ' . l('успешно перемещен на') .' ' . $location['location'];
                } elseif ($item && !$order && $location) {
                    if ($item['order_id'] > 0) {
                        // создаем заявку на отвязку
                        $a = array('item_id' => $item['item_id']);
                        $response = $this->all_configs['chains']->unbind_request($mod_id, $a);
                        if ($response && $response['state'] == true) {
                            // изделие - локация (отвязка)
                            $a = array('item_id' => $item['item_id'], 'location' => $location['id']);
                            $response = $this->all_configs['chains']->unbind_item_serial($a, $mod_id);
                            $msg = l('Изделие') . ' ' . suppliers_order_generate_serial($item) . ' ' . l('успешно отязано от ремонта') .' №' . $item['order_id'];
                        }
                    } else {
                        // изделие - локация (перемещение)
                        $a = array('wh_id_destination' => $location['wh_id'], 'location' => $location['id'], 'item_id' => $item['item_id']);
                        $response = $this->all_configs['chains']->move_item_request($a);
                        $msg = l('Изделие') . ' ' . suppliers_order_generate_serial($item) . ' ' . l('успешно перемещено на') .' ' . $location['location'];
                    }
                }

                $data['state'] = true;
                if ($response && isset($response['state']) && $response['state'] == true) {
                    $data['msg'] = $msg;
                    $data['ok'] = true;
                } else {
                    $data['msg'] .= '<br /> <span class="text-error">' . (isset($response['message']) ? $response['message'] : $msg) . '</span>';
                    //$data['state'] = false;
                }
            } else {
                $alert_timer = l('в течение') . ' <span id="scanner-moves-alert-timer" class="text-error">30</span> ' . l('сек') .'.';
                if ($order || $item) {
                    //$data['msg'] .= '<br /> Укажите локацию ' . $alert_timer;
                    if ($order) {
                        $data['value'] = $order_prefix . $order['id'];
                        $data['msg'] .= '<br /> ' . l('Укажите локацию или изделие') .' ' . $alert_timer;
                    }
                    if ($item) {
                        $data['value'] = $item_prefix . suppliers_order_generate_serial($item);
                        $data['msg'] .= '<br /> ' . l('Укажите локацию') .' ' . $alert_timer;
                    }
                }
                if ($location) {
                    $data['value'] = $location_prefix . $location['id'];
                    $data['msg'] .= '<br /> ' . l('Укажите изделие или заказ на ремонт') .' ' . $alert_timer;
                }
            }
        }

        // управление заказами поставщика
        if ($act == 'so-operations') {
            $this->all_configs['suppliers_orders']->operations(isset($_POST['object_id']) ? $_POST['object_id'] : 0);
        }

        // заявки
        if ($act == 'orders-link') {
            $so_id = isset($_POST['order_id']) ? $_POST['order_id'] : 0;
            $co_id = isset($_POST['so_co']) ? $_POST['so_co'] : 0;
            $data = $this->all_configs['suppliers_orders']->orders_link($so_id, $co_id);
        }

        // очистка серийника
        if ($act == 'clear-serial' && isset($_POST['item_id'])) {
            if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
                $item = $this->all_configs['db']->query('SELECT wh_id, serial FROM {warehouses_goods_items} WHERE id=?i',
                    array($_POST['item_id']))->row();
                if ($item && $item['wh_id'] == $this->all_configs['configs']['erp-warehouse-type-mir'] && !empty($item['serial'])) {
                    $this->all_configs['db']->query('UPDATE {warehouses_goods_items} SET serial=null, serial_old=? WHERE id=?i',
                        array($item['serial'], $_POST['item_id']));
                    $data['state'] = true;
                    $serial = suppliers_order_generate_serial(array('item_id' => $_POST['item_id'], 'serial' => ''));
                    $data['href'] = $this->all_configs['prefix'] . 'warehouses?serial=' . $serial . '#show_items';
                } else {
                    $data['message'] = l('Изделие не продано');
                }
            } else {
                $data['message'] = l('Нет прав');
            }
        }

        // форма приходования заказа поставщику
        if ($act == 'form-debit-so') {
            $order_id = isset($_POST['object_id']) ? intval($_POST['object_id']) : 0;

            $order = $this->all_configs['db']->query('SELECT o.*, w.title, l.location
                FROM {contractors_suppliers_orders} as o
                LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                LEFT JOIN {warehouses_locations} as l ON l.id=o.location_id
                WHERE o.id=?i',
                array($order_id))->row();
            $data['state'] = true;
            $data['btns'] = '<input class="btn" onclick="debit_supplier_order(this)" type="button" value="' . l('Приходовать') .'" />';
            $data['content'] = '<form method="POST" id="debit-so-form">';
            $data['content'] .= '<input type="hidden" value="' . $order_id . '" name="order_id" />';

            $data['content'] .= '<div class="form-group"><label class="control-label"><center><b>' . l('Серийный номер') . '</b><br>';
            if ($order) {
                $data['content'] .= htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['location']);
            }
            $data['content'] .= '</center></label>';
            $onchange = '$(\'#debit-so-form input.dso_serial\').val(\'\');$(\'#debit-so-form input.dso_auto_serial\').prop(\'checked\', $(this).is(\':checked\') ? true : true);';
            $data['content'] .= '<div class="pull-right"><label class="checkbox"><input id="dso_auto_serial_all" onchange="' . $onchange . '" type="checkbox" /> <b>Создать все</b></label>';
            $onchange = '$(\'#debit-so-form input.dso_print\').prop(\'checked\', $(this).is(\':checked\') ? true : false);';
            $data['content'] .= '<label class="checkbox"><input type="checkbox" id="dso_print_all" onchange="' . $onchange . '" /> <b>' . l('Распечатать все') .'</b></label></div></div><hr>';

            // необходимое количество приходования
            $count = $order ? $order['count_come'] - $order['count_debit'] : 0;

            if ($count > 0) {
                for ($i = 1; $i <= $count; $i++) {
                    $data['content'] .= '<div class="form-group" id="dso-group-' . $i . '">';
                    $onkeyup = 'if(this.value.trim()== \'\'){$(\'#dso-group-' . $i . ' input.dso_auto_serial, #dso_auto_serial_all\').prop(\'checked\', true);}else{$(\'#dso-group-' . $i . ' input.dso_auto_serial, #dso_auto_serial_all\').prop(\'checked\', false);}';
                    $data['content'] .= '<input onkeyup="' . $onkeyup . '" type="text" class="form-control input-large dso_serial" placeholder="' . l('серийный номер') .'" name="serial[' . $i . ']" />';
                    $onchange = '$(\'#dso_auto_serial_all\').prop(\'checked\', false);$(\'#dso-group-' . $i . ' input.dso_serial\').val(\'\');this.checked=true;';
                    $data['content'] .= '<div class="checkbox"><label class=""><input checked onchange="' . $onchange . '" type="checkbox" class="dso_auto_serial" name="auto[' . $i . ']" /> ' . l('Сгенерировать серийник') .'</label>';
                    $onchange = '$(\'#dso_print_all\').prop(\'checked\', false);';
                    $data['content'] .= '</div><div class="checkbox"><label class=""><input onchange="' . $onchange . '" type="checkbox" name="print[' . $i . ']" class="dso_print" /> ' . l('Распечатать серийник') .'</label>';
                    $data['content'] .= '</div><div class="dso-msg center"></div></div>';
                }
            } else {
                $data['content'] .= '<p class="center text-error">' . l('Все изделия оприходованы') .'</p>';
            }
            $data['content'] .= '</form>';
        }

        //
        if ($act == 'add-goods-to-inv') {
            if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0
                    && isset($_POST['goods'])) {
                $goods = explode(',', $_POST['goods']);
                if (count($goods) > 0) {
                    foreach ($goods as $id) {
                        if ($id > 0) {
                        $this->all_configs['db']->query('INSERT IGNORE INTO {inventories_goods}
                                (goods_id, inv_id) VALUES (?i, ?i)',
                            array($id, $this->all_configs['arrequest'][2]));
                        }
                    }
                    $data['state'] = true;
                }
            }
        }

        //
        if ($act == 'goods-in-warehouse') {
            if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {
                /*$goods = $this->all_configs['db']->query('SELECT g.id, g.title
                        FROM {inventories} as inv
                        LEFT JOIN {warehouses_goods_items} as i ON inv.wh_id=i.wh_id
                        LEFT JOIN {goods} as g ON g.id=i.goods_id
                        LEFT JOIN FROM {inventories_goods} as invg ON invg.inv_id=inv.id AND invg.goods_id=g.id
                        WHERE inv.id=?i AND invg.id IS NULL AND g.id IS NOT NULL GROUP BY g.id',
                    array($this->all_configs['arrequest'][2]))->vars();*/
                $goods = null;
                $ids = $this->all_configs['db']->query('SELECT i.goods_id FROM {inventories} as inv
                        RIGHT JOIN {warehouses_goods_items} as i ON inv.wh_id=i.wh_id
                        LEFT JOIN {inventories_goods} as invg ON invg.inv_id=inv.id AND invg.goods_id=i.goods_id
                        WHERE inv.id=?i AND invg.id IS NULL',
                    array($this->all_configs['arrequest'][2]))->vars();
                if ($ids) {
                    $goods = $this->all_configs['db']->query('SELECT DISTINCT g.id, g.title
                        FROM {goods} as g WHERE g.id IN (?li)', array(array_keys($ids)))->vars();
                }

                if ($goods) {
                    $data['html'] = '';
                    $data['options'] = array();
                    foreach ($goods as $id=>$title) {
                        $data['html'] .= '<option value="' . $id . '">' . $title . '</option>';
                        $data['options'][$id] = $title;
                    }
                }
            }
        }
        /*// открытие инвентаризации по товару
        if ($act == 'open-product-inventory') {

            if (array_key_exists(2, $this->all_configs['arrequest']) && isset($_POST['goods_id'])
                    && $_POST['goods_id'] > 0 && $this->all_configs['arrequest'][2] > 0) {

                // журнал сканирований
                $inventories = $this->all_configs['db']->query('SELECT i.price, w.title as wtitle,
                          i.order_id, i.wh_id, i.id as item_id, invj.date_scan, i.serial, u.email,
                          u.login, u.fio, inv.wh_id as inv_wh_id
                        FROM {goods} as g, {warehouses} as w, {warehouses_goods_items} as i
                        LEFT JOIN (SELECT id, wh_id, date_start FROM {inventories})inv ON inv.id=?i #AND inv.wh_id=i.wh_id
                        LEFT JOIN (SELECT date_scan, inv_id, item_id, scanned, user_id, wh_id FROM {inventory_journal}
                          ORDER BY date_scan DESC)invj ON invj.inv_id=inv.id AND i.id=invj.item_id
                        LEFT JOIN (SELECT id, fio, email, login FROM {users})u ON u.id=invj.user_id
                        WHERE i.goods_id=g.id AND i.goods_id=?i AND w.id=i.wh_id
                          AND (inv.wh_id=i.wh_id OR invj.date_scan IS NOT NULL)
                        GROUP BY i.id',
                    array($this->all_configs['arrequest'][2], $_POST['goods_id']))->assoc();

                $not_on_this_stock = '';
                if ($inventories) {
                    $data['out'] = '<table class="table table-striped"><thead><tr><td></td><td>Сер. №</td><td>'.l('Дата').'</td>';
                    $data['out'] .= '<td>Кладовщик</td><td>' . l('Склад') . '</td><td>' . l('Заказ') . '</td><td>Цена, ';
                    $data['out'] .= $this->all_configs['suppliers_orders']->currencies[$this->all_configs['suppliers_orders']->currency_suppliers_orders]['shortName'];
                    $data['out'] .= '</td><td></td></tr></thead><tbody>';
                    $i = 1;$j = 1;
                    foreach ($inventories as $inv) {
                        $inv['scanned'] = suppliers_order_generate_serial($inv);
                        if ($inv['inv_wh_id'] != $inv['wh_id']) {
                            $inv['i'] = $i;$i++;
                            $not_on_this_stock .= $this->display_scanned_item($inv, $inv['inv_wh_id']);
                        } else {
                            $inv['i'] = $j;$j++;
                            $data['out'] .= $this->display_scanned_item($inv, $inv['inv_wh_id']);
                        }
                    }
                    $data['out'] .= '</tbody></table>';
                    if (!empty($not_on_this_stock)) {
                        $data['out'] .= '<table class="table table-striped"><thead><tr><td></td><td>Сер. №</td><td>'.l('Дата').'</td>';
                        $data['out'] .= '<td>Кладовщик</td><td>' . l('Склад') . '</td><td>' . l('Заказ') . '</td><td>Цена, ';
                        $data['out'] .= $this->all_configs['suppliers_orders']->currencies[$this->all_configs['suppliers_orders']->currency_suppliers_orders]['shortName'];
                        $data['out'] .= '</td></tr></thead><tbody>';
                        $data['out'] .= $not_on_this_stock;
                        $data['out'] .= '</tbody></table>';
                    }

                } else {
                    //$data['message'] = 'Не найдено';
                    $data['out'] = '&nbsp;&nbsp;Изделий нет на складе';
                }
                $data['state'] = true;
            } else {
                $data['message'] = 'Инвентаризация не найдена';
                //$data['out'] = 'Изделий нет на складе';
            }
        }*/

        // Закрытие инвентаризации
        if ($act == 'close-inventory') {
            if (isset($_POST['inv_id']) && $_POST['inv_id'] > 0) {
                $inv = $this->all_configs['db']->query('SELECT date_stop, user_id FROM {inventories} WHERE id=?i',
                    array($_POST['inv_id']))->row();

                if (!$inv) {
                    $data['message'] = l('Инвентаризация не найдена');
                } else {
                    if ($inv['date_stop'] > 0) {
                        $data['state'] = true;
                    } elseif ($inv['user_id'] != $_SESSION['id']) {
                        $data['message'] = l('Вы не можете закрыть инвентаризацию');
                    } else {
                        $this->all_configs['db']->query('UPDATE {inventories} SET date_stop=NOW() WHERE id=?i',
                            array($_POST['inv_id']));
                        $data['state'] = true;
                    }
                }
            } else {
                $data['message'] = l('Инвентаризация не найдена');
            }
        }

        // сканирование
        if ($act == 'scan-serial') {
            if (isset($_POST['serial']) && array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {
                $serial = trim($_POST['serial']);
                $serial_id = suppliers_order_generate_serial(array('serial' => $_POST['serial']), false);

                if (gettype($serial_id) == 'integer') {
                    $query = $this->all_configs['db']->makeQuery('WHERE i.id=?i', array($serial_id));
                } else {
                    $query = $this->all_configs['db']->makeQuery('WHERE i.serial=?', array($serial_id));
                }

                $date_stop = $this->all_configs['db']->query('SELECT date_stop FROM {inventories} WHERE id=?i',
                    array($this->all_configs['arrequest'][2]))->el();

                $item = $this->all_configs['db']->query('SELECT i.goods_id, i.wh_id, i.id
                      FROM {warehouses_goods_items} as i ?query',
                    array($query))->row();

                if (!$item || $date_stop > 0) {
                    if ($date_stop > 0) {
                        //$data['message'] = '<div class="alert alert-error fade in"><button class="close" type="button" data-dismiss="alert">×</button>Инвентаризация закрыта</div>';
                        $data['state'] = true;
                    }
                    if (!$item) {
                        $data['message'] = '<div class="alert alert-error fade in"><button class="close" type="button" data-dismiss="alert">×</button>';
                        $data['message'] .= l('Серийник') . ' <strong>' . htmlspecialchars($serial) . '</strong> ' . l('не найден') .'</div>';
                    }
                } else {
                    $this->all_configs['db']->query('INSERT IGNORE INTO {inventories_goods} (goods_id, inv_id)
                        VALUES (?i, ?i)', array($item['goods_id'], $this->all_configs['arrequest'][2]));
                    $inv_id = $this->all_configs['db']->query('INSERT INTO {inventory_journal}
                            (inv_id, user_id, scanned, item_id, goods_id, wh_id) VALUES (?i, ?i, ?, ?i, ?i, ?i)',
                        array($this->all_configs['arrequest'][2], $_SESSION['id'], $serial, $item['id'],
                            $item['goods_id'], $item['wh_id']), 'id');

                    if ($inv_id > 0) {
                        $data['state'] = true;
                        /*// журнал сканирований
                        $inv = $this->all_configs['db']->query('SELECT it_j.id, it_j.date_scan, g.title as gtitle,
                                  w.title, it_j.scanned, u.login, u.fio, u.email, it_j.goods_id
                                FROM {inventory_journal} as it_j, {warehouses} as w, {goods} as g, {users} as u
                                WHERE w.id=it_j.wh_id AND it_j.id=?i AND g.id=it_j.goods_id AND u.id=it_j.user_id',
                            array($inv_id))->row();

                        $data['item'] = $this->display_scanned_item($inv);
                        //$this->show_inv_scan($inv);//id,date_scan,scanned,gtitle,login,email,fio*/
                    }
                }
            }
        }

        // создание инвентаризации
        if ($act == 'create-inventory') {
            if (isset($_POST['wh_id']) && $_POST['wh_id'] > 0) {
                // проверяем привязан ли юзер к складу
                $wh_id = $this->all_configs['db']->query('SELECT w.id FROM {warehouses} as w, {warehouses_users} as u
                        WHERE u.user_id=?i AND u.wh_id=w.id AND w.id=?i',
                    array($_SESSION['id'], $_POST['wh_id']))->el();

                if ($wh_id > 0) {
                    // создаем инвентаризацию
                    $i_id = $this->all_configs['db']->query('INSERT INTO {inventories} (user_id, wh_id)
                            VALUES (?i, ?i)', array($_SESSION['id'], $_POST['wh_id']), 'id');
                    if ($i_id > 0) {
                        $data['state'] = true;
                        $data['it'] = $i_id;
                    }
                } else {
                    $data['message'] = l('Вы не привязаны к этому складу');
                }
            } else {
                $data['message'] = l('Выберите склад');
            }
        }

        // продаем изделие
        if ($act == 'sold-item') {
            /** add to cart */
            $_POST['item_ids'] = array(
                $_POST['items']
            );
            $_POST['amount'] = array(
                $_POST['price']
            );
            $data = $this->all_configs['chains']->sold_items($_POST, $mod_id);
        }

        // списываем изделие
        if ($act == 'write-off-item') {
            $data = $this->all_configs['chains']->write_off_items($_POST, $mod_id);
        }

        // возвращаем изделие
        if ($act == 'return-item') {
            $data = $this->all_configs['chains']->return_items($_POST, $mod_id);
        }

        // перемещаем изделие
        if ($act == 'move-item') {
            $data = $this->all_configs['chains']->move_item_request($_POST, $mod_id);
        }

        if ($act == 'get-options-for-item-move') {
            if (isset($_POST['logistic']) && $_POST['logistic'] == 1) {
                $options_html = $this->all_configs['chains']->get_options_for_move_item_form(true);
            } else {
                $options_html = $this->all_configs['chains']->get_options_for_move_item_form();
            }
            $data['options'] = $options_html;
            $data['state'] = true;
        }

        // отвязка серийного номера
        if ($act == 'unbind-item-serial') {
            $data = $this->all_configs['chains']->unbind_item_serial($_POST, $mod_id);
            if ($data['state'] == true) {
                $data = $this->all_configs['chains']->move_item_request($_POST, $mod_id);
            }
        }

        // форма перемещение и принятия
        if ($act == 'bind-move-item-form') {
            if (isset($_POST['object_id'])) {
                $rand = rand(1000, 9999);
                $data['content'] = $this->all_configs['chains']->moving_item_form(intval($_POST['object_id']), null, null, null, false, $rand);
                $data['content'] .= '<div style="height: 200px"></div>';
                $data['btns'] = '<input type="button" class="btn" value="'.l('Сохранить').'" onclick="btn_unbind_item_serial(this, ' . $rand . ')" />';
                $data['state'] = true;
                $data['functions'] = array('reset_multiselect()');
            }
        }

        // привязка серийного номера
        if ($act == 'bind-item-serial') {
            $data = $this->all_configs['chains']->bind_item_serial($_POST, $mod_id);
        }

        // принятие изделия на склад
        if ($act == 'accept-chain-body') {
            $data = $this->all_configs['chains']->accept_chain_body($_POST, $mod_id);
        }

        // выдача изделия со склада
        if ($act == 'issued-chain-body') {
            $data = $this->all_configs['chains']->issued_chain_body($_POST, $mod_id);
        }

        // удаление заказа поставщика
        if ($act == 'remove-supplier-order') {
            $this->all_configs['suppliers_orders']->remove_order($mod_id);
        }

        // приходование заказа
        if ($act == 'debit-supplier-order') {
            $this->all_configs['suppliers_orders']->debit_order($_POST, $mod_id);
        }

        // принятие заказа
        if ($act == 'accept-supplier-order') {
            $this->all_configs['suppliers_orders']->accept_order($mod_id, $this->all_configs['chains']);
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

    /**
     * @param string $num
     * @return string
     */
    function gen_categories_selector($num='')
    {
        $categories = $this->all_configs['db']->query('SELECT title,url,id FROM {categories} WHERE avail=1 AND parent_id=0 GROUP BY title ORDER BY title')->assoc();

        $categories_html = '<select class="input-small searchselect" id="searchselect-'.$num.'"';
        $categories_html .= ' onchange="javascript:$(\'#goods-'.$num.'\').attr(\'data-cat\', this.value);"';
        $categories_html .= '><option value="0">' . l('Все разделы') . '</option>';
        foreach ( $categories as $category ) {
            $categories_html .= '<option value="' . $category['id'] . '">' . $category['title'] . '</option>';
        }
        $categories_html .= '</select>';
        return $categories_html;
    }

    /**
     * @param      $price
     * @param int  $zero
     * @param null $course
     * @return string
     */
    function show_price($price, $zero = 2, $course = null)
    {
        // делим на курс
        if ($course > 0)
            $price = $price * ($course / 100);

        // округляем и переводим с копеек
        $price = round($price / 100, 2);

        return number_format($price, $zero, '.', '');
    }


    /**
     * @return array
     */
    public static function get_submenu()
    {
        return array(
            array(
                'click_tab' => true,
                'url' => '#warehouses',
                'name' => l('Склады')
            ),
            array(
                'click_tab' => true,
                'url' => '#scanner_moves',
                'name' => l('Перемещения')
            ),
            array(
                'click_tab' => true,
                'url' => '#show_items',
                'name' => l('Товары')
            ),
            array(
                'click_tab' => true,
                'url' => '#orders',
                'name' => l('Заказы')
            ),
            array(
                'click_tab' => true,
                'url' => '#settings',
                'name' => l('Настройки')
            ),
        );
    }
}