<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Core/View.php';
require_once __DIR__ . '/../../Core/FlashMessage.php';

$modulename[40] = 'warehouses';
$modulemenu[40] = l('Склады');
$moduleactive[40] = !$ifauth['is_2'];

/**
 * @property  MLockFilters LockFilters
 * @property MPurchaseInvoices PurchaseInvoices
 * @property MWarehouses Warehouses
 */
class warehouses extends Controller
{
    protected $warehouses;
    protected $errors;

    public $count_on_page;

    public $uses = array(
        'LockFilters',
        'PurchaseInvoices',
        'Warehouses'
    );

    /**
     * warehouses constructor.
     * @param $all_configs
     */
    public function __construct(&$all_configs)
    {
        $this->mod_submenu = self::get_submenu();
        parent::__construct($all_configs);
    }

    /**
     * @return bool
     */
    public function can_show_module()
    {
        return (($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders')
                || $this->all_configs['oRole']->hasPrivilege('scanner-moves'))
            && $this->all_configs['configs']['erp-use']
        );
    }

    /**
     * @param $post
     * @throws Exception
     */
    public function check_post(array $post)
    {
        $mod_id = $this->all_configs['configs']['warehouses-manage-page'];
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';

        if (isset($post['filter-orders'])) {
            $this->createUrlForFilterOrders($post);
        }
        // фильтруем
        if (isset($post['filters'])) {
            $this->createUrlForFilters($post);
        }
        // фильтруем
        if (isset($post['bind-filters'])) {
            $this->createUrlForBindFilters($post);
        }
        if (isset($post['filter-warehouses'])) {
            $this->createUrlForFilterWarehouses($post);
        }
        // привязка администратора к складу
        if (isset($post['set-warehouses_users'])) {

            $this->all_configs['db']->query('TRUNCATE TABLE {warehouses_users}');

            $values = array();
            if (isset($post['locations']) && is_array($post['locations'])) {
                foreach ($post['locations'] as $user_id => $location_id) {
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
                    'INSERT IGNORE INTO {warehouses_users} (wh_id, location_id, user_id, main) VALUES ?v',
                    array($values));
            }
            $values = array();
            if (isset($post['warehouses_users']) && is_array($post['warehouses_users'])) {
                foreach ($post['warehouses_users'] as $user_id => $warehouses) {
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


        } elseif (isset($post['warehouse-add']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // создать склад
            $consider_all = 0;
            if (isset($post['consider_all'])) {
                $consider_all = 1;
            }
            $consider_store = 0;
            if (isset($post['consider_store'])) {
                $consider_store = 1;
            }
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
                    $checkByTitle = $this->all_configs['db']->query('SELECT count(*) FROM {warehouses} WHERE title=?',
                        array($post['title']))->el();
                    if (empty($checkByTitle)) {
                        $warehouse_id = $this->all_configs['db']->query('INSERT INTO {warehouses}
                (consider_all, consider_store, code_1c, title, print_address, print_phone, type, group_id, type_id, is_system) VALUES (?i, ?i, ?, ?, ?, ?, ?i, ?n, ?n, 0)',
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
                        FlashMessage::set(l('Склад успешно добавлен'), FlashMessage::SUCCESS);

                    } else {
                        FlashMessage::set(l('Склад с таким названием уже существует'), FlashMessage::DANGER);
                    }
                    if (isset($post['modal'])) {
                        Response::json(array(
                            'state' => true,
                            'reload' => true
                        ));
                    }
                } else {
                    if (isset($post['modal'])) {
                        Response::json(array(
                            'state' => false,
                            'message' => l('Заполните обязательные поля')
                        ));
                    } else {
                        FlashMessage::set(l('Заполните обязательные поля'), FlashMessage::DANGER);
                    }
                }
            } else {
                FlashMessage::set(l('Склад типа "Надостача" может существовать только в единственном экземпляре'),
                    FlashMessage::DANGER);
            }

        } elseif (isset($post['warehouse-edit']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // редактировать склад
            if (!isset($post['warehouse-id']) || $post['warehouse-id'] == 0) {
                Response::redirect($_SERVER['REQUEST_URI']);
            }

            $consider_all = 0;
            if (isset($post['consider_all'])) {
                $consider_all = 1;
            }
            $consider_store = 0;
            if (isset($post['consider_store'])) {
                $consider_store = 1;
            }
            $group_id = isset($post['group_id']) && intval($post['group_id']) > 0 ? intval($post['group_id']) : null;
            $type_id = isset($post['type_id']) && intval($post['type_id']) > 0 ? intval($post['type_id']) : null;
            $checkByTitle = $this->all_configs['db']->query('SELECT count(*) FROM {warehouses} WHERE title=? AND NOT id=?i',
                array($post['title'], $post['warehouse-id']))->el();
            if (!empty($checkByTitle)) {
                FlashMessage::set(l('Склад с таким названием уже существует'), FlashMessage::DANGER);
                Response::redirect($_SERVER['REQUEST_URI']);
            }
            //заблокировал обновления типа (4 - Клиент), при сохраниении сбрасывался в "1". 16.06.16
            $update = array(
                'code_1c' => trim($post['code_1c']),
                'print_address' => trim($post['print_address']),
                'print_phone' => trim($post['print_phone']),
            );
            $warehouse = $this->Warehouses->getByPk($post['warehouse-id']);
            if (!in_array($warehouse['title'], array(
                lq('Брак'),
                lq('Клиент'),
                lq('Логистика'),
                lq('Недостача'),
            ))
            ) {
                $update = $update + array(
                        'consider_all' => $consider_all,
                        'consider_store' => $consider_store,
                        'title' => trim($post['title']),
                        'group_id' => $group_id,
                        'type_id' => $type_id
                    );
            }
            $this->Warehouses->update($update, array(
                'id' => $post['warehouse-id']
            ));
            if (isset($_POST['location']) && is_array($_POST['location'])) {
                foreach ($_POST['location'] as $location) {
                    if (empty($location)) {
                        continue;
                    }
                    $this->all_configs['db']->query(
                        'INSERT IGNORE INTO {warehouses_locations} (wh_id, location) VALUES (?i, ?)',
                        array($post['warehouse-id'], trim($location)), 'id');

                    get_service('wh_helper')->clear_cache();
                }
            }
            if (!empty($_POST['location-id']) && is_array($_POST['location-id'])) {
                foreach ($_POST['location-id'] as $location_id => $location) {
                    if ($location_id > 0) {
                        if (mb_strlen(trim($location), 'UTF-8') > 0) {
                            $this->all_configs['db']->query('UPDATE {warehouses_locations} SET location=? WHERE id=?i',
                                array(trim($location), intval($location_id)));
                        } else {
                            if ($this->catDeleteLocation($post['warehouse-id'], $location_id)) {
                                $this->all_configs['db']->query(
                                    'DELETE FROM {warehouses_locations} WHERE wh_id=?i AND id=?i', array($post['warehouse-id'], $location_id));
                            }
                        }
                    }
                }
            }
            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                array($user_id, 'edit-warehouse', $mod_id, $post['warehouse-id']));

            // пересчет остатков на складе
            $this->all_configs['manageModel']->move_product_item($post['warehouse-id'], null);
        } elseif (isset($post['warehouse-group-add']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            if (isset($post['name']) && mb_strlen(trim($post['name']), 'UTF-8') > 0) {
                $color = preg_match('/^#[a-f0-9]{6}$/i', trim($post['color'])) ? trim($post['color']) : '#000000';
                $this->all_configs['db']->query(
                    'INSERT IGNORE INTO {warehouses_groups} (name, color, user_id, address, phone) VALUES (?, ?, ?i, ?, ?)',
                    array(trim($post['name']), $color, $user_id, trim($post['address']), trim($post['phone'])));
                $link = '<a href="' . $this->all_configs['prefix'] . 'warehouses#settings-warehouses" class="btn btn-primary js-go-to" data-goto_id="#add_warehouses">' . l('Перейти') . '</a>';
                FlashMessage::set(l('Вы добавили отделение') . ' ' . $post['name'] . '. ' . l('Теперь необходимо добавить склады и локации для данного отделения.') . $link,
                    FlashMessage::SUCCESS);
            }
        } elseif (isset($post['warehouse-type-add']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            if (isset($post['name']) && mb_strlen(trim($post['name']), 'UTF-8') > 0) {
                $this->all_configs['db']->query(
                    'INSERT IGNORE INTO {warehouses_types} (name, user_id) VALUES (?, ?i)',
                    array(trim($post['name']), $user_id));
            }
        } elseif (isset($post['warehouse-group-edit']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {

            if (isset($post['warehouse-group-id']) && isset($post['name']) && mb_strlen(trim($post['name']),
                    'UTF-8') > 0
            ) {
                try {
                    $color = preg_match('/^#[a-f0-9]{6}$/i', trim($post['color'])) ? trim($post['color']) : '#000000';
                    $this->all_configs['db']->query('UPDATE {warehouses_groups} SET name=?, color=?, address=?, phone=? WHERE id=?i',
                        array(
                            trim($post['name']),
                            $color,
                            trim($post['address']),
                            trim($post['phone']),
                            intval($post['warehouse-group-id'])
                        ));
                } catch (Exception $e) {
                }
            }
        } elseif (isset($post['warehouse-type-edit']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {

            if (isset($post['warehouse-type-id']) && isset($post['name']) && mb_strlen(trim($post['name']),
                    'UTF-8') > 0
            ) {
                try {
                    $this->all_configs['db']->query('UPDATE {warehouses_types} SET name=?, icon=? WHERE id=?i',
                        array(trim($post['name']), trim($post['icon']), intval($post['warehouse-type-id'])));
                } catch (Exception $e) {
                }
            }
        } elseif (isset($post['warehouse-delete']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $this->warehouseDelete($_POST);
        } elseif (isset($post['create-purchase-invoice'])) {
            $data = array(
                'state' => false,
                'message' => l('У вас нет прав на работу с приходными накладными')
            );
            if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders')) {

                $data = $this->createPurchaseInvoice($_POST);
            }
            Response::json($data);
        } elseif (isset($post['create-purchase-invoice-and-posting'])) {
            $data = array(
                'state' => false,
                'message' => l('У вас нет прав на работу с приходными накладными')
            );
            if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders')) {
                $data = $this->createPurchaseInvoice($_POST);
            }
            Response::json($data);
        } elseif (isset($post['edit-purchase-invoice']) && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $data = $this->editPurchaseInvoice($_POST);
            Response::json($data);
        }

        // чистим кеш складов
        get_service('wh_helper')->clear_cache();

        Response::redirect($_SERVER['REQUEST_URI']);
    }

    /**
     * @return array
     */
    public function get_warehouses_options()
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
    public function preload()
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
    public function gencontent()
    {
        $this->preload();
        return $this->view->renderFile('warehouses/gencontent', array(
            'mod_submenu' => $this->mod_submenu
        ));

    }

    /**
     * @return array
     */
    public function warehouses_scanner_moves()
    {
        return array(
            'html' => $this->view->renderFile('warehouses/warehouses_scanner_moves'),
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    public function warehouses_warehouses()
    {
        // всего денег по кассам которые consider_all == 1
        $cost_of = cost_of($this->warehouses, $this->all_configs['settings'], $this->all_configs['suppliers_orders']);
        $wh = $this->get_warehouses_options();
        $warehouses_options = $wh['wo'];
        // фильтрация


        return array(
            'html' => $this->view->renderFile('warehouses/warehouses_warehouses', array(
                'warehouses' => $this->warehouses,
                'cost_of' => $cost_of,
                'filters' => $this->filter_block($warehouses_options),
                'controller' => $this
            )),
            'functions' => array('multiselect()'),
        );
    }

    /**
     * @return array
     */
    public function warehouses_show_items()
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
        $open_item_in_sidebar = false;

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
                $goods = $this->all_configs['db']->query('SELECT g.title as product_title, g.vendor_code, i.goods_id, COUNT(g.id) as qty_wh
                        FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {warehouses_locations} as l
                        WHERE i.wh_id=w.id AND g.id=i.goods_id AND w.id IN (?li) AND l.id=i.location_id ?query ?query
                        GROUP BY g.id LIMIT ?i, ?i',
                    array(
                        array_values($warehouses_selected),
                        $query,
                        $query_for_noadmin,
                        $skip,
                        $count_on_page
                    ))->assoc();

                $count_page = $this->all_configs['db']->query('SELECT COUNT(DISTINCT i.goods_id)
                        FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {warehouses_locations} as l
                        WHERE i.wh_id=w.id AND g.id=i.goods_id AND w.id IN (?li) AND l.id=i.location_id ?query ?query',
                        array(array_values($warehouses_selected), $query, $query_for_noadmin))->el() / $count_on_page;
            }
        } else { // по изделию
            if (isset($_GET['serial'])) { // по серийнику
                $serial = suppliers_order_generate_serial($_GET, false);

                $goods = $this->all_configs['db']->query('SELECT w.id, w.title, w.code_1c, w.consider_all, w.consider_store, g.title as product_title,
                        i.goods_id, i.order_id, i.supplier_order_id, i.serial, i.date_sold, i.price, i.supplier_id as user_id, u.title as contractor_title,
                        i.id as item_id, i.date_add, i.serial_old, l.location, i.location_id, g.vendor_code
                        FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {contractors} as u, {warehouses_locations} as l
                        WHERE i.wh_id=w.id AND g.id=i.goods_id AND u.id=i.supplier_id AND l.id=i.location_id AND i.serial=? ?query
                    ', array($_GET['serial'], $query_for_noadmin))->assoc();
                if (empty($goods) && $this->all_configs['configs']['erp-serial-prefix'] == substr(trim($_GET['serial']), 0,
                        strlen($this->all_configs['configs']['erp-serial-prefix']))
                ) {
                    $goods = $this->all_configs['db']->query('SELECT w.id, w.title, w.code_1c, w.consider_all, w.consider_store, g.title as product_title,
                        i.goods_id, i.order_id, i.supplier_order_id, i.serial, i.date_sold, i.price, i.supplier_id as user_id, u.title as contractor_title,
                        i.id as item_id, i.date_add, i.serial_old, l.location, i.location_id, g.vendor_code
                        FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {contractors} as u, {warehouses_locations} as l
                        WHERE i.wh_id=w.id AND g.id=i.goods_id AND u.id=i.supplier_id AND l.id=i.location_id AND i.id=?i ?query
                        ', array($serial, $query_for_noadmin))->assoc();
                }
                $show_item_type = 1;
                $open_item_in_sidebar = true;
            } else {
                $goods = $this->getItems($_GET, $count_on_page, $skip);
                if (isset($filters['so_id']) && $filters['so_id'] > 0) {
                    $query = $this->all_configs['db']->makeQuery('?query AND i.supplier_order_id=?i',
                        array($query, intval($filters['so_id'])));
                } elseif (count($warehouses_selected) > 0) {
                    $query = $this->all_configs['db']->makeQuery('?query AND w.id IN (?li)',
                        array($query, array_values($warehouses_selected)));
                }

                $count_page = $this->all_configs['db']->query('SELECT COUNT(DISTINCT i.id)
                            FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {contractors} as u, {warehouses_locations} as l
                            WHERE i.wh_id=w.id AND g.id=i.goods_id AND u.id=i.supplier_id AND l.id=i.location_id ?query ?query',
                        array($query, $query_for_noadmin))->el() / $count_on_page;

                $show_item_type = 1;
            }
        }

        if (count($goods)) {
            $out .= $this->show_goods($goods, $query_for_noadmin, $show_item_type, $count_page, $open_item_in_sidebar);
        } else {
            $out .= '<p class="text-error">' . l('Выберите склад') . '</p>';
        }

        $out .= '</div>';

        if (count($warehouses_selected) == 0) {
            $functions_arr = array('multiselect(true)');
        } else {
            $functions_arr = array('multiselect(false)');
        }

        return array(
            'html' => $out,
            'functions' => $functions_arr,
        );
    }

    /**
     * @param array $filters
     * @param null $count_on_page
     * @param null $skip
     * @param bool $select_name
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
        if (isset($filters['so_id']) && $filters['so_id'] > 0) {
            $query = $this->all_configs['db']->makeQuery('AND i.supplier_order_id=?i',
                array(intval($filters['so_id'])));
        } elseif (count($warehouses_selected) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND w.id IN (?li)',
                array($query, array_values($warehouses_selected)));
        }

        if ($select_name) {
            $select = $this->all_configs['db']->makeQuery('i.id as `№ изделия`, i.serial as `Серийный номер`,
                    g.title as `Наименование`, g.vendor_code as `Артикул`, i.date_add as `' . l('Дата') . '`, w.title as `Склад`, w.id as `№ склада`,
                    l.location as `Локация`, l.id as `№ локации`, i.order_id as `Заказ клиента`,
                    i.supplier_order_id as `Заказ поставщику`, i.price/100 as `Цена`,
                    u.title as `Поставщик`, i.supplier_id as `№ поставщика`', array());
        } else {
            $select = $this->all_configs['db']->makeQuery('w.id, w.title, w.code_1c, w.consider_all,
                    w.consider_store, g.title as product_title, i.id as item_id, i.date_add, i.goods_id, g.vendor_code,
                    i.order_id, i.serial, i.date_sold, i.price, i.supplier_id as user_id,
                    u.title as contractor_title, i.supplier_order_id, l.location, i.location_id', array());
        }
        $goods = $this->all_configs['db']->query('SELECT ?query
                    FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {contractors} as u, {warehouses_locations} as l
                    WHERE i.wh_id=w.id AND g.id=i.goods_id AND u.id=i.supplier_id AND l.id=i.location_id ?query ?query ?query',
            array($select, $query, $query_for_noadmin, $limit))->assoc();

        return $goods;
    }

    /**
     * @param string $hash
     * @return array
     */
    public function warehouses_orders($hash = '#orders-clients_issued')
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
    public function warehouses_orders_suppliers()
    {
        $out = '';
        $saved = $this->LockFilters->load('warehouse-orders-filters');
        if (count($_GET) <= 2 && $saved) {
            $_GET += $saved;
        }

        if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders')) {
            $_GET['type'] = 'debit';
            $queries = $this->all_configs['manageModel']->suppliers_orders_query($_GET);
            $query = $queries['query'];
            $skip = $queries['skip'];

            $count_on_page = $this->count_on_page;

            $out .= '<div>';
            $out .= '<h4>' . l('Заказы поставщику, которые ждут приходования') . '</h4>';
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
            'menu' => $this->all_configs['suppliers_orders']->show_filters_suppliers_orders(false, false, false,
                'orders-suppliers'),
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @return array
     */
    public function warehouses_orders_clients_bind()
    {
        $saved = $this->LockFilters->load('warehouse-bind-order-filters');
        if (count($_GET) <= 2 && $saved) {
            $_GET += $saved;
        }
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
    public function warehouses_orders_clients_issued()
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
    public function warehouses_orders_clients_accept()
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
    public function warehouses_orders_clients_unbind()
    {
        $saved = $this->LockFilters->load('warehouse-filters');
        if (count($_GET) <= 2 && $saved) {
            $_GET += $saved;
        }
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
    public function warehouses_settings($hash = '')
    {
        if (trim($hash) == '#settings' || (trim($hash) != '#settings-warehouses' && trim($hash) != '#settings-warehouses_groups'
                && trim($hash) != '#settings-warehouses_types' && trim($hash) != '#settings-warehouses_users')
        ) {
            $hash = '#settings-warehouses';
        }

        return array(
            'html' => $this->view->renderFile('warehouses/warehouses_settings'),
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    /**
     * @return array
     */
    public function warehouses_settings_warehouses()
    {
        $admin_out = '';

        if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
            // форма для создания склада
            $create_form = $this->form_warehouse();
            $edit_forms = '';
            if ($this->warehouses && count($this->warehouses) > 0) {
                $i = 1;
                foreach ($this->warehouses as $warehouse) {
                    $i++;
                    $edit_forms .= $this->form_warehouse($warehouse, $i);
                }
            }

            $admin_out = $this->view->renderFile('warehouses/warehouses_settings_warehouses', array(
                'warehouses' => $this->warehouses,
                'create_form' => $create_form,
                'edit_forms' => $edit_forms,
            ));
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
    public function warehouses_settings_warehouses_types_form($type = null)
    {
        return $this->view->renderFile('warehouses/warehouses_settings_warehouses_types_form', array(
            'type' => $type
        ));
    }

    /**
     * @return array
     */
    public function warehouses_settings_warehouses_types()
    {
        $admin_out = '';

        if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $admin_out .= "<div class='panel-group row-fluid' id='accordion_warehouses_types'><div class='col-sm-6'>";
            $admin_out .= $this->warehouses_settings_warehouses_types_form();
            $types = (array)$this->all_configs['db']->query('SELECT * FROM {warehouses_types}')->assoc();
            foreach ($types as $type) {
                $admin_out .= $this->warehouses_settings_warehouses_types_form($type);
            }
            $admin_out .= '</div></div>';
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
    public function warehouses_settings_warehouses_groups_form($group = null)
    {
        return $this->view->renderFile('warehouses/warehouses_settings_warehouses_groups_form', array(
            'group' => $group
        ));
    }

    /**
     * @return array
     */
    public function warehouses_settings_warehouses_groups()
    {
        $admin_out = '';

        if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $admin_out .= "<div class='panel-group row-fluid' id='accordion_warehouses_groups'><div class='col-sm-6'>";
            $admin_out .= $this->warehouses_settings_warehouses_groups_form();
            $groups = (array)$this->all_configs['db']->query('SELECT * FROM {warehouses_groups}')->assoc();
            foreach ($groups as $group) {
                $admin_out .= $this->warehouses_settings_warehouses_groups_form($group);
            }
            $admin_out .= '</div></div><!--#accordion_warehouses_groups-->';
        }
        return array(
            'html' => $admin_out,
            'functions' => array(),
        );
    }

    /**
     * @return array
     */
    public function warehouses_settings_warehouses_users()
    {
        $admin_out = '';

        if ($this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $query = 'WHERE u.deleted = 0';
            if (count($this->all_configs['configs']['erp-warehouses-permiss']) > 0) {
                $query = $this->all_configs['db']->makeQuery(
                    ', {users_role_permission} as p WHERE p.permission_id IN (?li) AND u.role=p.role_id AND u.deleted=0 GROUP BY u.id',
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

                $admin_out = $this->view->renderFile('warehouses/warehouses_settings_warehouses_users', array(
                    'users' => $users,
                    'warehouses' => $this->warehouses,
                    'wh_users' => $wh_users,
                    'wh_mains' => $wh_mains
                ));
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
    public function warehouses_inventories($hash = '#inventories-list')
    {
        if (trim($hash) == '#inventories' || (trim($hash) != '#inventories-list' && trim($hash) != '#inventories-journal'
                && trim($hash) != '#inventories-listinv' && trim($hash) != '#inventories-writeoff')
        ) {
            $hash = '#inventories-list';
        }

        return array(
            'html' => $this->view->renderFile('warehouses/warehouses_inventories'),
            'functions' => array('click_tab(\'a[href="' . trim($hash) . '"]\')'),
        );
    }

    /**
     * @return array
     */
    public function warehouses_inventories_list()
    {
        // запрос для складов за которыми закреплен юзер
        $q = $this->all_configs['chains']->query_warehouses();
        $query = $q['query_for_move_item'];

        // список инвентаризаций
        $list = $this->all_configs['db']->query('SELECT inv.date_start, inv.date_stop, w.title, inv.id,
                    u.login, u.email, u.fio FROM {warehouses} as w, {users} as u, {inventories} as inv
                ?query AND w.id=inv.wh_id AND inv.user_id=u.id GROUP BY inv.id ORDER BY inv.date_start DESC',
            array($query))->assoc('id');
        $counts_items = array();
        $counts_inv_items = array();

        if ($list) {
            $counts_items = (array)$this->all_configs['db']->query('SELECT inv.id, COUNT(DISTINCT i.id)
                FROM {inventories} as inv, {warehouses_goods_items} as i, {inventories_goods} as invg
                WHERE inv.id IN (?li) AND i.wh_id=inv.wh_id AND inv.id=invg.inv_id AND i.goods_id=invg.goods_id
                GROUP BY inv.id', array(array_keys($list)))->vars();
            $counts_inv_items = (array)$this->all_configs['db']->query('SELECT inv.id, COUNT(DISTINCT i.id)
                FROM {inventories} as inv, {warehouses_goods_items} as i, {inventory_journal} as invj
                WHERE inv.id IN (?li) AND i.wh_id=inv.wh_id AND i.id=invj.item_id AND inv.id=invj.inv_id GROUP BY inv.id',
                array(array_keys($list)))->vars();
        }

        return array(
            'html' => $this->view->renderFile('warehouses/warehouses_inventories_list', array(
                'list' => $list,
                'counts_items' => $counts_items,
                'counts_inv_items' => $counts_inv_items
            )),
            'functions' => array(),
        );
    }

    /**
     * @param int $id
     * @return string
     */
    public function scan_serial_form($id = 1)
    {
        return $this->view->renderFile('warehouses/scan_serial_form', array(
            'id' => $id
        ));
    }

    /**
     * @return array
     */
    public function warehouses_inventories_journal()
    {
        $left_html = '';
        $inventories = $left = array();

        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {

            // левое меню
            $left = $this->inventories_left_menu(1);
            $left_html .= $left['html'];
            // форма сканирования

            // журнал сканирований
            $inventories = $this->all_configs['db']->query('SELECT it_j.id as item_id, it_j.date_scan, g.title as gtitle,
                      w.title as wtitle, it_j.scanned, u.login, u.fio, u.email, it_j.goods_id, it_j.wh_id, it_j.wh_id
                    FROM {inventory_journal} as it_j, {warehouses} as w, {goods} as g, {users} as u
                    WHERE w.id=it_j.wh_id AND it_j.inv_id=?i AND g.id=it_j.goods_id AND u.id=it_j.user_id
                    ORDER BY it_j.date_scan DESC',
                array($this->all_configs['arrequest'][2]))->assoc();
        }
        $right_html = $this->view->renderFile('warehouses/warehouses_inventories_journal', array(
            'left_html' => $left_html,
            'inventories' => $inventories,
            'left' => $left,
            'controller' => $this
        ));

        return array(
            'html' => '<div class="span2">' . $left_html . '</div><div class="span10">' . $right_html . '</div>',
            'functions' => array('multiselect_goods(1)'),
        );
    }

    /**
     * @return array
     */
    public function warehouses_inventories_listinv()
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

            // список инвентаризаций
            $inventories = $this->all_configs['db']->query('SELECT DISTINCT invg.goods_id, g.title as gtitle, invg.inv_id as id
                  FROM {goods} as g, {inventories_goods} as invg WHERE invg.goods_id=g.id AND invg.inv_id=?i',
                array($this->all_configs['arrequest'][2]))->assoc('id');

            if ($inventories) {
                $counts_items = (array)$this->all_configs['db']->query('SELECT inv.id, COUNT(DISTINCT i.id)
                      FROM {inventories} as inv, {warehouses_goods_items} as i, {inventories_goods} as invg
                      WHERE inv.id=?i AND i.wh_id=inv.wh_id AND inv.id=invg.inv_id AND i.goods_id=invg.goods_id GROUP BY i.goods_id',
                    array($this->all_configs['arrequest'][2]))->vars();
                $counts_inv_items = (array)$this->all_configs['db']->query('SELECT inv.id, COUNT(DISTINCT i.id)
                      FROM {inventories} as inv, {warehouses_goods_items} as i, {inventory_journal} as invj
                      WHERE inv.id=?i AND i.wh_id=inv.wh_id AND i.id=invj.item_id AND inv.id=invj.inv_id GROUP BY i.goods_id',
                    array($this->all_configs['arrequest'][2]))->vars();
            } else {
                $counts_items = array();
                $counts_inv_items = array();
            }
            $right_html = $this->view->renderFile('warehouses/warehouses_inventories_listinv', array(
                'inventories' => $inventories,
                'counts_items' => $counts_items,
                'counts_inv_items' => $counts_inv_items,
                'controller' => $this
            ));

        }

        return array(
            'html' => '<div class="span2">' . $left_html . '</div><div class="span10">' . $right_html . '</div>',
            'functions' => array('multiselect_goods(2)'),
        );
    }


    /**
     * @return array
     */
    public function warehouses_inventories_writeoff()
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

            $right_html = $this->view->renderFile('warehouses/warehouses_inventories_writeoff', array(
                'left' => $left,
                'controller' => $this,
                'inventories' => $inventories,
            ));
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
    public function display_scanned_item($inv, $wh_id)
    {
        return $this->view->renderFile('warehouses/display_scanned_item', array(
            'inv' => $inv,
            'wh_id' => $wh_id
        ));
    }

    /**
     * @param $active_btn
     * @return array
     */
    public function inventories_left_menu($active_btn)
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

            $left_html = $this->view->renderFile('warehouses/inventories_left_menu', array(
                'user_id' => $this->getUserId(),
                'inventory' => $inventory,
                'active_btn' => $active_btn
            ));
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
    public function filter_block($warehouses_options, $i = 1)
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
     * @param $goods
     * @param $query_for_noadmin
     * @param null $type
     * @param int $count_page
     * @param bool $open_item_in_sidebar
     * @return string
     */
    public function show_goods($goods, $query_for_noadmin, $type = null, $count_page = 1, $open_item_in_sidebar = false)
    {
        return $this->view->renderFile('warehouses/show_goods', array(
            'goods' => $goods,
            'type' => $type,
            'count_page' => $count_page,
            'query_for_noadmin' => $query_for_noadmin,
            'controller' => $this,
            $open_item_in_sidebar
        ));
    }

    /**
     * @param $post
     * @param $mod_id
     * @return array
     */
    public function sidebar_load_item($post, $mod_id)
    {

        $error = '';

        try {
            // запросы для касс для разных привилегий
            $q = $this->all_configs['chains']->query_warehouses();
            $query_for_noadmin = $q['query_for_noadmin'];

            $item_id = suppliers_order_generate_serial($post, false);

            if ($this->all_configs['configs']['erp-serial-prefix'] == substr(trim($_POST['serial']), 0,
                    strlen($this->all_configs['configs']['erp-serial-prefix']))
            ) {
                $item = $this->all_configs['db']->query('SELECT w.id, w.title, w.code_1c, w.consider_all, w.consider_store, g.title as product_title,
                        i.goods_id, i.order_id, i.supplier_order_id, i.serial, i.date_sold, i.price, i.supplier_id as user_id, u.title as contractor_title,
                        i.id as item_id, i.date_add, i.serial_old, l.location, i.location_id, g.vendor_code
                        FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {contractors} as u, {warehouses_locations} as l
                        WHERE i.wh_id=w.id AND g.id=i.goods_id AND u.id=i.supplier_id AND l.id=i.location_id AND i.id=?i ?query
                        ', array($item_id, $query_for_noadmin))->row();
                if (empty($item)) {
                    $item = $this->all_configs['db']->query('SELECT w.id, w.title, w.code_1c, w.consider_all, w.consider_store, g.title as product_title,
                        i.goods_id, i.order_id, i.supplier_order_id, i.serial, i.date_sold, i.price, i.supplier_id as user_id, u.title as contractor_title,
                        i.id as item_id, i.date_add, i.serial_old, l.location, i.location_id, g.vendor_code
                        FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {contractors} as u, {warehouses_locations} as l
                        WHERE i.wh_id=w.id AND g.id=i.goods_id AND u.id=i.supplier_id AND l.id=i.location_id AND i.serial=? ?query
                        ', array($_POST['serial'], $query_for_noadmin))->row();
                }
            } else {
                $item = $this->all_configs['db']->query('SELECT w.id, w.title, w.code_1c, w.consider_all, w.consider_store, g.title as product_title,
                        i.goods_id, i.order_id, i.supplier_order_id, i.serial, i.date_sold, i.price, i.supplier_id as user_id, u.title as contractor_title,
                        i.id as item_id, i.date_add, i.serial_old, l.location, i.location_id, g.vendor_code
                        FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {contractors} as u, {warehouses_locations} as l
                        WHERE i.wh_id=w.id AND g.id=i.goods_id AND u.id=i.supplier_id AND l.id=i.location_id AND i.serial=? ?query
                    ', array($item_id, $query_for_noadmin))->row();
            }

            $html = $this->view->renderFile('warehouses/sidebar/item', array(
                'item' => $item,
                'controller' => $this,
                'query_for_noadmin' => $query_for_noadmin,
            ));
        } catch (Exception $e) {
            $error = $e->getMessage();
        }


        return array(
            'html' => $html,
            'error' => $error,
            'debug' => $item
        );
    }

    /**
     * @param null $warehouse
     * @param int $i
     * @return string
     */
    public function form_warehouse($warehouse = null, $i = 1)
    {
        $groups = (array)$this->all_configs['db']->query('SELECT * FROM {warehouses_groups}')->assoc();
        $types = (array)$this->all_configs['db']->query('SELECT * FROM {warehouses_types}')->assoc();

        if (!empty($warehouse)) {
            try {
                $warehouse['can_deleted'] = $this->checkWarehouseForDelete($warehouse['id']);
            } catch (ExceptionWithMsg $e) {
                $warehouse['can_deleted'] = false;
            }
        }

        return $this->view->renderFile('warehouses/form_warehouse', array(
            'i' => $i,
            'warehouse' => $warehouse,
            'groups' => $groups,
            'types' => $types
        ));
    }

    /**
     *
     */
    public function ajax()
    {
        $data = array(
            'state' => false
        );

        $mod_id = $this->all_configs['configs']['warehouses-manage-page'];

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        // проверка доступа
        if ($this->can_show_module() == false) {
            Response::json(array('message' => l('Нет прав'), 'state' => false));
        }

        // грузим табу
        if ($act == 'tab-load') {
            if (isset($_POST['tab']) && !empty($_POST['tab'])) {
                $this->preload();
                if (method_exists($this, $_POST['tab'])) {
                    $function = call_user_func_array(
                        array($this, $_POST['tab']),
                        array(
                            (isset($_POST['hashs']) && mb_strlen(trim($_POST['hashs'],
                                    'UTF-8')) > 0) ? trim($_POST['hashs']) : null
                        )
                    );
                    $return = array(
                        'html' => $function['html'],
                        'state' => true,
                        'functions' => $function['functions']
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
        // форма создания нового склада
        if ($act == 'create-warehouse') {
            $groups = (array)$this->all_configs['db']->query('SELECT * FROM {warehouses_groups}')->assoc();
            $types = (array)$this->all_configs['db']->query('SELECT * FROM {warehouses_types}')->assoc();
            $data = array(
                'state' => true,
                'content' => $this->view->renderFile('warehouses/create_warehouse', array(
                    'groups' => $groups,
                    'types' => $types
                )),
                'title' => l('Создать склад')
            );
            Response::json($data);
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
            $data = $this->scannerMoves($data, $mod_id);
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
        if ($act == 'edit-purchase-invoice-form') {
            $data = $this->editPurchaseInvoiceForm($_GET);
        }

        if ($act == 'create-purchase-invoice-form') {
            $data = $this->createPurchaseInvoiceForm($data);
        }

        // очистка серийника
        if ($act == 'clear-serial' && isset($_POST['item_id'])) {
            $data = $this->clearSerial($data);
        }

        // форма приходования заказа поставщику
        if ($act == 'form-debit-so') {
            $data = $this->form_debit_so($data);
        }

        if ($act == 'form-debit-purchase-invoice') {
            $data = $this->form_debit_purchase_invoice($data);
        }

        //
        if ($act == 'add-goods-to-inv') {
            $data = $this->addGoodsToInv($data);
        }

        //
        if ($act == 'goods-in-warehouse') {
            $data = $this->goodsInWarehouse($data);
        }

        // Закрытие инвентаризации
        if ($act == 'close-inventory') {
            $data = $this->closeInventory($data);
        }
        if ($act == 'posting-one') {
            $data = $this->postingStepOne($data);
        }

        // сканирование
        if ($act == 'scan-serial') {
            $data = $this->scanSerial($data);
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
                $data['content'] = $this->all_configs['chains']->moving_item_form(intval($_POST['object_id']), null,
                    null, null, false, $rand);
                $data['content'] .= '<div style="height: 200px"></div>';
                $data['btns'] = '<input type="button" class="btn" value="' . l('Сохранить') . '" onclick="btn_unbind_item_serial(this, ' . $rand . ')" />';
                $data['state'] = true;
                $data['functions'] = array('reset_multiselect()');
            }
        }

        // загрузка изделия в сайдбар
        if ($act == 'sidebar-load-item') {
            $data = $this->sidebar_load_item($_POST, $mod_id);
        }

        // массовая привязка серийников к заказу
        if ($act == 'bind-serials-to-order') {
            $data = $this->all_configs['chains']->bind_serials_to_order($_POST, $mod_id);
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

        // приходование накладной
        if ($act == 'debit-purchase-invoice') {
            $data = $this->debit_purchase_invoice($_POST, $mod_id);
        }

        // принятие заказа
        if ($act == 'accept-supplier-order') {
            $this->all_configs['suppliers_orders']->accept_order($mod_id, $this->all_configs['chains']);
        }
        if ($act == 'purchase-invoice-import-form' && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $data = $this->purchaseInvoiceImportForm();
        }
        if ($act == 'purchase-invoice-import' && $this->all_configs['oRole']->hasPrivilege('site-administration')) {
            $data = $this->purchaseInvoiceImport($_POST);
        }
        Response::json($data);
    }

    /**
     * @param string $num
     * @return string
     */
    public function gen_categories_selector($num = '')
    {
        $categories = $this->all_configs['db']->query('SELECT title,url,id FROM {categories} WHERE avail=1 AND parent_id=0 GROUP BY title ORDER BY title')->assoc();

        return $this->view->renderFile('warehouses/gen_categories_selector', array(
            'categories' => $categories,
            'num' => $num
        ));
    }

    /**
     * @param      $price
     * @param int $zero
     * @param null $course
     * @return string
     */
    public function show_price($price, $zero = 2, $course = null)
    {
        // делим на курс
        if ($course > 0) {
            $price = $price * ($course / 100);
        }

        // округляем и переводим с копеек
        $price = round($price / 100, 2);

        return number_format($price, $zero, '.', '');
    }


    /**
     * @inheritdoc
     */
    public static function get_submenu($oRole = null)
    {
        global $all_configs;
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
            /**
             * array(
             * 'click_tab' => true,
             * 'url' => '#purchase_invoices',
             * 'name' => l('Приходные накладные')
             * ),
             */
            array(
                'click_tab' => true,
                'another_module' => true,
                'url' => $all_configs['prefix'] . 'stocktaking',
                'name' => l('Инвентаризация')
            ),
        );
    }

    /**
     * @param $data
     * @return mixed
     */
    private function form_debit_so($data)
    {
        $order_id = isset($_POST['object_id']) ? intval($_POST['object_id']) : 0;

        $order = $this->all_configs['db']->query('SELECT o.*, w.title, l.location, g.title as item
                FROM {contractors_suppliers_orders} as o
                LEFT JOIN {goods} as g ON o.goods_id=g.id
                LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                LEFT JOIN {warehouses_locations} as l ON l.id=o.location_id
                WHERE o.id=?i',
            array($order_id))->row();
        $data['state'] = true;
        $data['btns'] = '<input class="btn" onclick="debit_supplier_order(this)" type="button" value="' . l('Приходовать') . '" />';
        $data['content'] = $this->view->renderFile('warehouses/form_debit_so', array(
            'order' => $order,
            'order_id' => $order_id,
            // необходимое количество приходования
            'count' => $order ? $order['count_come'] - $order['count_debit'] : 0
        ));
        return $data;
    }

    /**
     * @param array $post
     */
    private function createUrlForFilterOrders(array $post)
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

        if (isset($post['goods']) && $post['goods'] > 0) {
            // фильтр по товару
            $url['by_gid'] = intval($post['goods']);
        }

        if (isset($post['managers']) && !empty($post['managers'])) {
            // фильтр по менеджерам
            $url['mg'] = implode(',', $post['managers']);
        }

        if (isset($post['suppliers']) && !empty($post['suppliers'])) {
            // фильтр по поставщикам
            $url ['sp'] = implode(',', $post['suppliers']);
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

        if (isset($post['so_st']) && $post['so_st'] > 0) {
            // фильтр клиенту/заказу
            $url['so_st'] = $post['so_st'];
        }

        if (isset($post['my']) && !empty($post['my'])) {
            // фильтр клиенту/заказу
            $url['my'] = 1;
        }
        if (isset($post['lock-button'])) {
            // фильтр клиенту/заказу
            $url['lock-button'] = intval($post['lock-button']);
        }

        $this->LockFilters->toggle('warehouse-orders-filters', $url);

        $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . http_build_query($url));
        Response::redirect($url);
    }

    /**
     * @param array $post
     */
    private function createUrlForFilters(array $post)
    {
        $url = array();

        if (isset($post['noitems'])) {
            // фильтр по без "изделий нет"
            $url['noi'] = 1;
        }

        if (isset($post['goods']) && $post['goods'] > 0) {
            // фильтр по товару
            $url['by_gid'] = intval($post['goods']);
        }

        if (isset($post['clients']) && $post['clients'] > 0) {
            // фильтр клиенту/заказу
            $url['c_id'] = intval($post['clients']);
        }

        if (isset($post['client-order-number']) && !empty($post['client-order-number'])) {
            // фильтр клиенту/заказу
            if (preg_match('/^[zZ]-/', trim($post['client-order-number'])) === 1) {
                $orderId = preg_replace('/^[zZ]-/', '', trim($post['client-order-number']));
            } else {
                $orderId = trim($post['client-order-number']);
            }
            $url['con'] = intval($orderId);
        }

        if (isset($post['serial']) && !empty($post['serial'])) {
            // фильтр клиенту/заказу
            $url['serial'] = urlencode(trim($post['serial']));
        }
        if (isset($post['lock-button'])) {
            // фильтр клиенту/заказу
            $url['lock-button'] = intval($post['lock-button']);
        }

        $this->LockFilters->toggle('warehouse-filters', $url);

        $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . http_build_query($url));
        Response::redirect($url);
    }

    /**
     * @param array $post
     */
    private function createUrlForFilterWarehouses(array $post)
    {
// фильтруем
        $url = array();

        if (isset($post['warehouses']) && is_array($post['warehouses']) && count($post['warehouses']) > 0) {
            $url['whs'] = implode(',', $post['warehouses']);
        }

        if (isset($post['locations']) && is_array($post['locations']) && count($post['locations']) > 0) {
            $url['lcs'] = implode(',', $post['locations']);
        }

        if (isset($post['goods']) && $post['goods'] > 0) {
            $url['pid'] = intval($post['goods']);
        }

        if (isset($post['display']) && $post['display'] == 'amount') {
            $url ['d'] = 'a';
        }

        // первычные ключи
        if (isset($post['serial']) && !empty($post['serial'])) {
            $url['serial'] = urlencode($post['serial']);
        }

        if (isset($post['so_id']) && $post['so_id'] > 0) {
            $url ['so_id'] = intval($post['so_id']);
        }
        if (isset($post['lock-button'])) {
            // фильтр клиенту/заказу
            $url['lock-button'] = intval($post['lock-button']);
        }
        $this->LockFilters->toggle('warehouse-warehouse-filters', $url);

        $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . http_build_query($url)) . '#show_items';

        Response::redirect($url);
    }

    /**
     * @param $data
     * @return mixed
     */
    private function scanSerial($data)
    {
        if (isset($_POST['serial']) && array_key_exists(2,
                $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0
        ) {
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
                    $data['state'] = true;
                }
                if (!$item) {
                    $data['message'] = '<div class="alert alert-error fade in"><button class="close" type="button" data-dismiss="alert">×</button>';
                    $data['message'] .= l('Серийник') . ' <strong>' . htmlspecialchars($serial) . '</strong> ' . l('не найден') . '</div>';
                }
            } else {
                $this->all_configs['db']->query('INSERT IGNORE INTO {inventories_goods} (goods_id, inv_id)
                        VALUES (?i, ?i)', array($item['goods_id'], $this->all_configs['arrequest'][2]));
                $inv_id = $this->all_configs['db']->query('INSERT INTO {inventory_journal}
                            (inv_id, user_id, scanned, item_id, goods_id, wh_id) VALUES (?i, ?i, ?, ?i, ?i, ?i)',
                    array(
                        $this->all_configs['arrequest'][2],
                        $_SESSION['id'],
                        $serial,
                        $item['id'],
                        $item['goods_id'],
                        $item['wh_id']
                    ), 'id');

                if ($inv_id > 0) {
                    $data['state'] = true;
                }
            }
        }
        return $data;
    }

    /**
     * @param $data
     * @return mixed
     */
    private function closeInventory($data)
    {
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
        return $data;
    }

    /**
     * @param $data
     * @return mixed
     */
    private function goodsInWarehouse($data)
    {
        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0) {
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
                foreach ($goods as $id => $title) {
                    $data['html'] .= '<option value="' . $id . '">' . $title . '</option>';
                    $data['options'][$id] = $title;
                }
            }
        }
        return $data;
    }

    /**
     * @param $data
     * @return mixed
     */
    private function addGoodsToInv($data)
    {
        if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0
            && isset($_POST['goods'])
        ) {
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
        return $data;
    }

    /**
     * @param $data
     * @return mixed
     */
    private function clearSerial($data)
    {
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
        return $data;
    }

    /**
     * @param $data
     * @param $mod_id
     * @return mixed
     */
    private function scannerMoves($data, $mod_id)
    {
        $scan = isset($_POST['scanned'][1]) ? '"' . $_POST['scanned'][1] . '"' : '';
        $from_sidebar = isset($_GET['from_sidebar']) ? true : false;
        $data['msg'] = l('Сканирование') . ' ' . htmlspecialchars($scan) . ' ' . l('не найдено');
        $data['error'] = true;

        $order = $item = $location = null;
        $order_prefix = 'Z-'; // префикс для заказа
        $item_prefix = ''; // префикс для  изделия
        $location_prefix = 'L-'; // префикс для локации

        if (isset($_POST['scanned']) && is_array($_POST['scanned'])) {
            foreach ($_POST['scanned'] as $scanned) {

                if (preg_match('/' . $order_prefix . '((?!' . $location_prefix . ').+)?/', trim($scanned),
                    $matches)) {
                    $data['msg'] = l('Заказ') . ' ' . htmlspecialchars($scan) . ' ' . l('на ремонт не найден');
                    if (isset($matches[1]) && intval($matches[1]) > 0) {
                        if ($item) {
                            $item = null;
                        }
                        $order = $this->all_configs['db']->query('SELECT id FROM {orders} WHERE id=?i',
                            array(intval($matches[1])))->row();
                        if ($order) {
                            $data['msg'] = l('Заказ') . ' №' . $order['id'];
                            $data['state'] = true;
                            $data['error'] = false;
                        }
                    }
                }

                if (preg_match('/' . $item_prefix . '((?!' . $order_prefix . '|' . $location_prefix . ').+)?/',
                    trim($scanned), $matches)) {
                    if (isset($matches[1]) && suppliers_order_generate_serial(array('serial' => $matches[1]),
                            false) > 0
                    ) {
                        $data['msg'] = l('Изделие') . ' ' . htmlspecialchars($scan) . ' ' . l('не найдено');
                        $item = $this->all_configs['db']->query(
                            'SELECT id as item_id, serial, order_id, goods_id, supplier_order_id FROM {warehouses_goods_items} WHERE id=?i',
                            array(suppliers_order_generate_serial(array('serial' => $matches[1]), false)))->row();
                        if (empty($item)) {
                            $item = $this->all_configs['db']->query(
                                'SELECT id as item_id, serial, order_id, goods_id, supplier_order_id FROM {warehouses_goods_items} WHERE serial=?q OR id=?i',
                                array($scanned, $scanned))->row();
                        }
                        if ($item) {
                            $data['msg'] = $order ? l('Заказ') . ' №' . $order['id'] . '<br />' : '';
                            $data['msg'] .= l('Изделие') . ' ' . suppliers_order_generate_serial($item);
                            $data['state'] = true;
                            $data['error'] = false;
                        }
                    }
                }

                if (preg_match('/' . $location_prefix . '((?!' . $order_prefix . ').+)?/', trim($scanned),
                    $matches)) {
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
                            $data['msg'] .= l('Склад') . ' "' . htmlspecialchars($location['title']) . '", ' . l('локация') . ' "' . htmlspecialchars($location['location']) . '"';
                            $data['state'] = true;
                            $data['error'] = false;
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
                    $a = array(
                        'order_id' => $order['id'],
                        'order_product_id' => $order_product_id,
                        'supplier_order_id' => $item['supplier_order_id']
                    );
                    $response = $this->all_configs['chains']->order_item($mod_id, $a, false);
                    if ($response && $response['state'] == true) {
                        if (isset($data['id'])) {
                            $del_order_item = true;
                        }
                        // заказ - изделие (привязка)
                        $a = array(
                            'item_id' => $item['item_id'],
                            'order_product_id' => $order_product_id,
                            'confirm' => 1
                        );
                        $response = $this->all_configs['chains']->bind_item_serial($a, $mod_id);
                        if ($response && $response['state'] == true) {
                            $msg = 'Изделие ' . suppliers_order_generate_serial($item) . ' ' . l('успешно выдано под ремонт') . ' №' . $order['id'];
                            $del_order_item = $del_product = false;
                        }
                    }
                }
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
                $a = array(
                    'wh_id_destination' => $location['wh_id'],
                    'location' => $location['id'],
                    'order_id' => $order['id']
                );
                $response = $this->all_configs['chains']->move_item_request($a);
                $msg = l('Заказ') . ' №' . $order['id'] . ' ' . l('успешно перемещен на') . ' ' . $location['location'];
            } elseif ($item && !$order && $location) {
                if ($item['order_id'] > 0) {
                    // создаем заявку на отвязку
                    $a = array('item_id' => $item['item_id']);
                    $response = $this->all_configs['chains']->unbind_request($mod_id, $a);
                    if ($response && $response['state'] == true) {
                        // изделие - локация (отвязка)
                        $a = array('item_id' => $item['item_id'], 'location' => $location['id']);
                        $response = $this->all_configs['chains']->unbind_item_serial($a, $mod_id);
                        $msg = l('Изделие') . ' ' . suppliers_order_generate_serial($item) . ' ' . l('успешно отязано от ремонта') . ' №' . $item['order_id'];
                    }
                } else {
                    // изделие - локация (перемещение)
                    $a = array(
                        'wh_id_destination' => $location['wh_id'],
                        'location' => $location['id'],
                        'item_id' => $item['item_id']
                    );
                    $response = $this->all_configs['chains']->move_item_request($a);
                    $msg = l('Изделие') . ' ' . suppliers_order_generate_serial($item) . ' ' . l('успешно перемещено на') . ' ' . $location['location'];
                }
            }

            $data['state'] = true;
            if ($response && isset($response['state']) && $response['state'] == true) {
                $data['msg'] = $msg;
                $data['ok'] = true;
            } else {
                $data['msg'] .= '<br /> <span class="text-error">' . (isset($response['message']) ? $response['message'] : $msg) . '</span>';
            }
        } else {
            $alert_timer = l('в течение') . ' <span id="scanner-moves-alert-timer' . ($from_sidebar ? '-sidebar' : '') . '" class="text-error">30</span> ' . l('сек') . '.';
            if ($order || $item) {
                if ($order) {
                    $data['value'] = $order_prefix . $order['id'];
                    $data['msg'] .= '<br /> ' . l('Укажите локацию или изделие') . ' ' . $alert_timer;
                }
                if ($item) {
                    $data['value'] = $item_prefix . suppliers_order_generate_serial($item);
                    $data['msg'] .= '<br /> ' . l('Укажите локацию') . ' ' . $alert_timer;
                }
            }
            if ($location) {
                $data['value'] = $location_prefix . $location['id'];
                $data['msg'] .= '<br /> ' . l('Укажите изделие или заказ на ремонт') . ' ' . $alert_timer;
            }
        }
        return $data;
    }

    /**
     * @param $inv
     * @return mixed
     */
    public function getInventories($inv)
    {
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
        return $_inventories;
    }

    /**
     * @param $product
     * @param $query_for_noadmin
     * @return mixed
     */
    public function getItemHistory($product, $query_for_noadmin)
    {
        $item_history = $this->all_configs['db']->query('SELECT m.item_id, m.date_move, m.user_id, m.wh_id,
                              m.comment, w.title, u.fio, u.email, m.order_id, l.location
                            FROM {users} as u, {warehouses} as w, {warehouses_stock_moves} as m, {warehouses_locations} as l
                            WHERE m.item_id=?i AND u.id=m.user_id AND w.id=m.wh_id AND l.id=m.location_id ?query
                            ORDER BY m.date_move DESC, m.id DESC',
            array($product['item_id'], $query_for_noadmin))->assoc();
        return $item_history;
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getWarehousesItems($product)
    {
        $items = $this->all_configs['db']->query('SELECT i.id as item_id, serial, i.price,
                                          i.date_add, ct.title, w.title as wtitle, i.order_id
                                        FROM {warehouses_goods_items} as i
                                        LEFT JOIN {contractors} as ct ON i.supplier_id=ct.id
                                        LEFT JOIN {warehouses} as w ON w.id=i.wh_id
                                        WHERE i.goods_id=?i AND i.wh_id IN (?li)',
            array($product['goods_id'], explode(',', $_GET['whs'])))->assoc();
        return $items;
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getFilteredItems($product)
    {
        $items = $this->all_configs['db']->query('SELECT COUNT(i.id) as count, SUM(i.price) as sum
                                        FROM {warehouses_goods_items} as i
                                        WHERE i.goods_id=?i AND i.wh_id IN (?li)',
            array($product['goods_id'], explode(',', $_GET['whs'])))->row();
        return $items;
    }

    /**
     * @param $warehouseId
     * @return bool
     * @throws ExceptionWithMsg
     */
    private function checkWarehouseForDelete($warehouseId)
    {
        $count = $this->all_configs['db']->query('SELECT count(*) FROM {orders} WHERE wh_id=?i OR accept_wh_id=?i',
            array($warehouseId, $warehouseId))->el();
        if ($count > 0) {
            throw new ExceptionWithMsg(l('Не возможно удалить склад, привязаны заказы'));
        }
        $count = $this->all_configs['db']->query('SELECT count(*) FROM {warehouses_goods_items} WHERE wh_id=?i',
            array($warehouseId))->el();
        if ($count > 0) {
            throw new ExceptionWithMsg(l('Не возможно удалить склад, имеются товары'));
        }
        $count = $this->all_configs['db']->query('SELECT count(*) FROM {warehouses_users} WHERE wh_id=?i',
            array($warehouseId))->el();
        if ($count > 0) {
            throw new ExceptionWithMsg(l('Не возможно удалить склад, привязаны пользователи'));
        }
        $count = $this->all_configs['db']->query('SELECT count(*) FROM {contractors_suppliers_orders} WHERE wh_id=?i',
            array($warehouseId))->el();
        if ($count > 0) {
            throw new ExceptionWithMsg(l('Не возможно удалить склад, привязаны заказы поставщикам'));
        }
        $count = $this->all_configs['db']->query('SELECT count(*) FROM {chains} WHERE from_wh_id=?i OR to_wh_id=?i',
            array($warehouseId, $warehouseId))->el();
        if ($count > 0) {
            throw new ExceptionWithMsg(l('Не возможно удалить склад, привязаны транзакции'));
        }
        return true;
    }

    /**
     * @param $post
     * @return bool
     */
    private function warehouseDelete($post)
    {
        $warehouseId = $post['warehouse-id'];
        try {
            $warehouse = $this->all_configs['db']->query('SELECT * FROM {warehouses} WHERE id=?i',
                array($warehouseId))->row();
            if ($warehouse['is_system'] || in_array($warehouse['title'], array(
                    lq('Брак'),
                    lq('Клиент'),
                    lq('Логистика'),
                    lq('Недостача'),
                ))
            ) {
                throw new ExceptionWithMsg(l('Не возможно удалить системный склад'));
            }
            $this->checkWarehouseForDelete($warehouseId);
            $this->all_configs['db']->query('DELETE FROM {warehouses_locations} WHERE wh_id=?i', array($warehouseId));
            $this->all_configs['db']->query('DELETE FROM {warehouses} WHERE id=?i', array($warehouseId));
            FlashMessage::set(l('Склад удален'), FlashMessage::SUCCESS);

        } catch (ExceptionWithMsg $e) {
            FlashMessage::set($e->getMessage(), FlashMessage::DANGER);
            return false;
        }
        return true;
    }

    /**
     * @param string $hash
     * @return string
     */
    public function purchase_invoices($hash = '#purchase_invoices')
    {
        $invoices = $this->PurchaseInvoices->query('
            SELECT pi.*, u.fio, u.login, u.email, c.title as supplier, g.cnt as quantity, g.amount as amount, wh.title as warehouse, wl.location as location, wh.id as wh_id
            FROM {purchase_invoices} as pi
            JOIN {contractors} as c ON c.id = pi.supplier_id
            JOIN {users} as u ON u.id = pi.user_id
            JOIN {warehouses} as wh ON wh.id = pi.warehouse_id
            JOIN {warehouses_locations} as wl ON wl.id = pi.location_id
            JOIN (SELECT invoice_id, count(*) as cnt, sum(`price` * quantity) as amount FROM {purchase_invoice_goods} GROUP by invoice_id ) as g ON g.invoice_id=pi.id
        ')->assoc();
        return array(
            'html' => $this->view->renderFile('warehouses/purchase_invoices/purchase_invoices', array(
                'controller' => $this,
                'invoices' => $invoices
            )),
            'function' => '',
            'menu' => ''
        );
    }

    /**
     * @param $data
     * @return array
     */
    private function editPurchaseInvoiceForm($data)
    {
        $suppliers = null;
        if (array_key_exists('erp-contractors-use-for-suppliers-orders', $this->all_configs['configs'])
            && count($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders']) > 0
        ) {
            $suppliers = $this->all_configs['db']->query('SELECT id, title FROM {contractors} WHERE type IN (?li) ORDER BY title',
                array(array_values($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders'])))->assoc();
        }
        $invoice = $this->PurchaseInvoices->getByPk($data['id']);
        $goods = $this->PurchaseInvoices->query('
            SELECT pig.*, g.title as product 
            FROM {purchase_invoice_goods} as pig
            JOIN {goods} as g ON g.id=pig.good_id
            WHERE pig.invoice_id=?i
        ', array($data['id']))->assoc('id');
        return array(
            'state' => true,
            'content' => $this->view->renderFile('warehouses/purchase_invoices/edit_purchase_invoice', array(
                'suppliers' => $suppliers,
                'invoice' => $invoice,
                'goods' => $goods,
                'warehouses' => $this->all_configs['db']->query('SELECT id, title FROM {warehouses}')->assoc(),
                'controller' => $this
            )),
            'title' => '',
            'message' => ''
        );
    }

    /**
     * @param $data
     * @return array
     */
    private function createPurchaseInvoiceForm($data)
    {
        $suppliers = null;
        if (array_key_exists('erp-contractors-use-for-suppliers-orders', $this->all_configs['configs'])
            && count($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders']) > 0
        ) {
            $suppliers = $this->all_configs['db']->query('SELECT id, title FROM {contractors} WHERE type IN (?li) ORDER BY title',
                array(array_values($this->all_configs['configs']['erp-contractors-use-for-suppliers-orders'])))->assoc();
        }
        return array(
            'state' => true,
            'content' => $this->view->renderFile('warehouses/purchase_invoices/create_purchase_invoice', array(
                'suppliers' => $suppliers,
                'warehouses' => $this->all_configs['db']->query('SELECT id, title FROM {warehouses}')->assoc(),
                'controller' => $this
            )),
            'title' => '',
            'message' => ''
        );
    }

    /**
     * @param      $data
     * @param bool $withId
     * @return array
     */
    public function prepareItemsForInvoice($data, $withId = false)
    {
        $result = array();
        if (array_key_exists('item_ids', $data)) {
            foreach ($data['item_ids'] as $id => $itemId) {
                $good = array(
                    'good_id' => $itemId,
                    'price' => $data['amount'][$id] * 100,
                    'quantity' => $data['quantity'][$id],
                    'not_found' => $data['not_found'][$id] || ''
                );
                if ($withId) {
                    $good['id'] = $id;
                }
                $result[] = $good;
            }
        }
        return $result;
    }

    /**
     * @param $post
     * @return array
     */
    private function createPurchaseInvoice($post)
    {
        $result = array(
            'state' => true,
        );
        $items = $this->prepareItemsForInvoice($post);
        if (empty($items)) {
            return array(
                'state' => false,
                'message' => l('Коризна пустая')
            );
        }
        $data = array(
            'user_id' => $this->getUserId(),
            'supplier_id' => $post['warehouse-supplier'],
            'comment' => $post['comment-supplier'],
            'date' => date('Y-m-d H:i:s', strtotime($post['warehouse-order-date'])),
            'items' => $items,
            'type' => $post['warehouse-type'],
            'warehouse_id' => $post['warehouse'],
            'location_id' => $post['location']
        );
        $result['id'] = $this->PurchaseInvoices->add($data);
        if (!$result['id']) {
            $result = array(
                'state' => false,
                'message' => l('Что-то пошло не так')
            );
        }
        return $result;
    }

    /**
     * @param      $wh_id
     * @param null $location_id
     * @return string
     */
    public function gen_locations($wh_id, $location_id = null)
    {
        $out = '';
        $wh_id = array_filter(is_array($wh_id) ? $wh_id : explode(',', $wh_id));
        $location_id = $location_id ? (array_filter(is_array($location_id) ? $location_id : explode(',',
            $location_id))) : array();

        if (count($wh_id) > 0) {
            $locations = $this->all_configs['db']->query(
                'SELECT id, location FROM {warehouses_locations} WHERE wh_id IN (?li)', array($wh_id))->vars();
            $out = $this->view->renderFile('warehouses/purchase_invoices/_locations', array(
                'locations' => $locations,
                'location_id' => $location_id
            ));
        }

        return $out;
    }

    /**
     * @param $post
     * @return array
     */
    private function editPurchaseInvoice($post)
    {
        $result = array(
            'state' => true,
        );
        try {
            $invoice = $this->PurchaseInvoices->getByPk($post['invoice_id']);
            if (empty($invoice)) {
                throw new ExceptionWithMsg(l('Накладная не найдена'));
            }
            $this->PurchaseInvoices->updateInvoice($invoice, $post);
            $this->PurchaseInvoices->updateItems($invoice['id'], $this->prepareItemsForInvoice($post));
        } catch (ExceptionWithMsg $e) {
            $result = array(
                'state' => false,
                'message' => $e->getMessage()
            );

        }
        return $result;
    }

    /**
     * @param $data
     * @return array
     */
    private function form_debit_purchase_invoice($data)
    {
        $invoice_id = isset($_POST['object_id']) ? intval($_POST['object_id']) : 0;

        $invoice = $this->all_configs['db']->query('SELECT pi.*, w.title as warehouse, l.location
                FROM {purchase_invoices} as pi
                LEFT JOIN {warehouses} as w ON w.id=pi.warehouse_id
                LEFT JOIN {warehouses_locations} as l ON l.id=pi.location_id
                WHERE pi.id=?i',
            array($invoice_id))->row();

        $goods = $this->all_configs['db']->query('SELECT pig.*, g.title as item
                FROM {purchase_invoice_goods} as pig
                LEFT JOIN {goods} as g ON pig.good_id=g.id
                WHERE pig.invoice_id=?i AND NOT pig.good_id=0',
            array($invoice_id))->assoc('id');
        return array(
            'state' => true,
            'btns' => '<input class="btn" onclick="debit_purchase_invoice(this)" type="button" value="' . l('Приходовать') . '" />',
            'content' => $this->view->renderFile('warehouses/purchase_invoices/form_debit', array(
                'invoice' => $invoice,
                'invoice_id' => $invoice_id,
                'goods' => $goods,
            ))
        );
    }

    /**
     * @param $post
     * @param $mod_id
     * @return array|void
     */
    private function debit_purchase_invoice($post, $mod_id)
    {
        try {
            if (empty($post['invoice_id'])) {
                throw new ExceptionWithMsg(l('Накладная не найдена'));
            }
            $invoice = $this->PurchaseInvoices->getByPk($post['invoice_id']);
            if (empty($invoice)) {
                throw new ExceptionWithMsg(l('Накладная не найдена'));
            }
            if ($invoice['state'] == PURCHASE_INVOICE_STATE_CAPITALIZED) {
                throw new ExceptionWithMsg(l('Накладная уже оприходована'));
            }
            $orderId = empty($invoice['supplier_order_id']) ? $this->createOrderFromInvoice($invoice,
                $mod_id) : $invoice['supplier_order_id'];
            $debitResult = $this->debitOrderFromInvoice($orderId, $post, $mod_id);
            if (empty($debitResult)) {
                throw new ExceptionWithMsg(l('Возникли проблемы при оприходовании заказов'));
            }
            $this->PurchaseInvoices->update(array(
                'state' => PURCHASE_INVOICE_STATE_CAPITALIZED
            ), array('id' => $post['invoice_id']));
            $result = array(
                'state' => true,
                'result' => ''
            );
            foreach ($debitResult as $value) {
                $result['result'] .= $this->form_debit_invoice_result($value['order_for_result'], $value['msg']);
                if (!empty($value['print_link'])) {
                    $result['print_links'][] = $value['print_link'];
                }
            }
        } catch (ExceptionWithMsg $e) {
            $result = array(
                'state' => false,
                'message' => $e->getMessage()
            );
        }
        return $result;
    }

    /**
     * @param $order
     * @param $msg
     * @return string
     */
    public function form_debit_invoice_result($order, $msg)
    {
        return $this->view->renderFile('warehouses/purchase_invoices/form_debit_result', array(
            'order' => $order,
            'msg' => $msg
        ));
    }

    /**
     * @param $invoice
     * @param $mod_id
     * @return array
     * @throws ExceptionWithMsg
     */
    private function createOrderFromInvoice($invoice, $mod_id)
    {
        return $this->PurchaseInvoices->createOrderFromInvoice($invoice, $mod_id);
    }

    /**
     * @param $parentOrderId
     * @param $post
     * @param $mod_id
     * @return array
     * @throws ExceptionWithMsg
     */
    private function debitOrderFromInvoice($parentOrderId, $post, $mod_id)
    {
        $orders = $this->all_configs['db']->query('
            SELECT * 
            FROM {contractors_suppliers_orders} 
            WHERE id=?i OR parent_id=?i
        ', array($parentOrderId, $parentOrderId))->assoc('id');
        if (empty($orders)) {
            throw new ExceptionWithMsg(l('Договора с поставщиком не найдены'));
        }
        $goods = $this->all_configs['db']->query(' SELECT * FROM {purchase_invoice_goods} WHERE invoice_id=?i AND NOT good_id=0',
            array($post['invoice_id']))->assoc('good_id');
        $result = array();
        if (!empty($goods)) {
            foreach ($orders as $order) {
                $id = $goods[$order['goods_id']]['id'];
                $data = array(
                    'order_id' => $order['id'],
                    'serial' => $post['serial'][$id],
                    'auto' => $post['auto'][$id],
                    'print' => $post['print'][$id]
                );
                $result[] = $this->all_configs['suppliers_orders']->debit_supplier_order($data, $mod_id);
            }
        }
        return $result;
    }

    /**
     * @param $data
     * @return array
     */
    private function postingStepOne($data)
    {
        return array(
            'state' => true,
            'content' => $this->view->renderFile('warehouses/posting_from_step_one'),
        );
    }

    /**
     * @return array
     */
    private function purchaseInvoiceImportForm()
    {
        $contractors = db()->query('SELECT id, title FROM {contractors}')->vars();
        $warehouses = db()->query('
            SELECT w.id, w.title 
            FROM {warehouses} as w
        ')->vars();
        return array(
            'state' => true,
            'content' => $this->view->renderFile('warehouses/purchase_invoices/import_form', array(
                'contractors' => $contractors,
                'warehouses' => $warehouses
            )),
            'title' => l('Импорт из файла')
        );
    }

    private function purchaseInvoiceImport($post)
    {
        return array(
            'state' => true,
            'content' => 'test'
        );
    }

    /**
     * @param $post
     */
    private function createUrlForBindFilters($post)
    {
        $url = array();

        if (isset($post['noitems'])) {
            // фильтр по без "изделий нет"
            $url['noi'] = 1;
        }

        if (isset($post['goods']) && $post['goods'] > 0) {
            // фильтр по товару
            $url['by_gid'] = intval($post['goods']);
        }

        if (isset($post['warehouse']) && $post['warehouse'] > 0) {
            $url['warehouse'] = intval($post['warehouse']);
        }

        if (isset($post['location']) && $post['location'] > 0) {
            $url['location'] = intval($post['location']);
        }

        if (isset($post['client-order-number']) && !empty($post['client-order-number'])) {
            // фильтр клиенту/заказу
            if (preg_match('/^[zZ]-/', trim($post['client-order-number'])) === 1) {
                $orderId = preg_replace('/^[zZ]-/', '', trim($post['client-order-number']));
            } else {
                $orderId = trim($post['client-order-number']);
            }
            $url['con'] = intval($orderId);
        }

        if (isset($post['lock-button'])) {
            // фильтр клиенту/заказу
            $url['lock-button'] = intval($post['lock-button']);
        }

        $this->LockFilters->toggle('warehouse-bind-serial-filters', $url);

        $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . http_build_query($url));
        Response::redirect($url);
    }

    /**
     * @param $warehouseId
     * @param $locationId
     * @return bool
     */
    private function catDeleteLocation($warehouseId, $locationId)
    {
        $count = $this->Warehouses->query('SELECT count(*) FROM {warehouses_locations} WHERE wh_id=?i', array($warehouseId))->el();
        if ($count <= 1) {
            FlashMessage::set(l('Нельзя удалить последнюю локацию'), FlashMessage::DANGER);
            return false;
        }
        $used = $this->all_configs['db']->query('SELECT count(*) FROM {contractors_suppliers_orders} WHERE location_id=?i', array($locationId))->el();
        if ($used) {
            FlashMessage::set(l('Локация задействована в заказах поставщику'), FlashMessage::DANGER);
            return false;
        }
        $used = $this->all_configs['db']->query('SELECT count(*) FROM {warehouses_goods_items} WHERE location_id=?i', array($locationId))->el();
        if ($used) {
            FlashMessage::set(l('Локация задействована в складских операциях'), FlashMessage::DANGER);
            return false;
        }
        $used = $this->all_configs['db']->query('SELECT count(*) FROM {orders} WHERE location_id=?i', array($locationId))->el();
        if ($used) {
            FlashMessage::set(l('Локация задействована в заказах на ремонт'), FlashMessage::DANGER);
            return false;
        }
        return true;
    }
}