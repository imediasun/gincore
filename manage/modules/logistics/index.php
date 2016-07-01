<?php

require_once __DIR__.'/../../Core/View.php';

$modulename[50] = 'logistics';
$modulemenu[50] = l('Логистика');
$moduleactive[50] = !$ifauth['is_2'];


class logistics
{
    private $mod_submenu;
    protected $all_configs;
    protected $db;
    /** @var View  */
    protected $view;

    public $count_on_page;

    /**
     * logistics constructor.
     * @param $all_configs
     */
    function __construct(&$all_configs)
    {
        $this->mod_submenu = self::get_submenu();
        $this->all_configs = $all_configs;
        $this->db = $all_configs['db'];
        $this->count_on_page = count_on_page();
        $this->view = new View($all_configs);

        global $input_html;

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }

        // проверяем вкл склады
        if ($this->can_show_module() == false) {
            return $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p  class="text-error">У Вас не достаточно прав</p></div>';
        }


        $input_html['mcontent'] = $this->gencontent();

    }

    /**
     * @return bool
     */
    function can_show_module()
    {
        return ($this->all_configs['configs']['erp-use'] && ($this->all_configs['oRole']->hasPrivilege('logistics')));
    }

    /**
     * @return string
     */
    function gencontent()
    {
        $out = '<div class="tabbable"><ul class="nav nav-tabs">';
        $out .= '<li><a class="click_tab default" data-open_tab="logistics_motions" onclick="click_tab(this, event)" ';
        $out .= 'data-toggle="tab" href="' . $this->mod_submenu[0]['url'] . '">' . $this->mod_submenu[0]['name'] . '</a></li>';
        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
            $out .= '<li><a class="click_tab" data-open_tab="logistics_settings" onclick="click_tab(this, event)" ';
            $out .= 'data-toggle="tab" href="' . $this->mod_submenu[1]['url'] . '">' . $this->mod_submenu[1]['name'] . '</a></li>';
        }
        $out .= '</ul><div class="tab-content">';

        // управление перемещениями
        $out .= '<div id="motions" class="tab-pane">';
        $out .= '</div><!--#motions-->';

        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
            $out .= '<div id="settings" class="tab-pane">';
            $out .= '</div><!--#settings-->';
        }

        $out .= '</div><!--.tab-content-->';
        $out .= '</div><!--.tabbable-->';

        $out .= $this->all_configs['chains']->append_js();

        return $out;
    }

    /**
     * @return string
     * @throws Exception
     */
    private function filters_block()
    {
        $warehouses = get_service('wh_helper')->get_warehouses();

        $whfrom = isset($_GET['whfrom']) ? $_GET['whfrom'] : array();
        $whto = isset($_GET['whto']) ? $_GET['whto'] : array();
        $warehouses_select = $warehouses_select_to = '';
        foreach ($warehouses as $wh) {
            $warehouses_select .= '<option value="' . $wh['id'] . '"' . (in_array($wh['id'],
                    $whfrom) ? ' selected' : '') . '>' . $wh['title'] . '</option>';
            $warehouses_select_to .= '<option value="' . $wh['id'] . '"' . (in_array($wh['id'],
                    $whto) ? ' selected' : '') . '>' . $wh['title'] . '</option>';
        }
        $out = '
            <form>
                <input type="text" placeholder="' . l('Дата') . '" name="date" class="daterangepicker form-control" value="' . (isset($_GET['date']) ? htmlspecialchars($_GET['date']) : '') . '" />
                <input name="o_id" value="' . (isset($_GET['o_id']) ? htmlspecialchars($_GET['o_id']) : '') . '" type="text" class="form-control" placeholder="' . l('номер заказа') . '">
                <input name="i_id" value="' . (isset($_GET['i_id']) ? htmlspecialchars($_GET['i_id']) : '') . '" type="text" class="form-control" placeholder="' . l('номер изделия') . '">
                <label>' . l('Откуда') . ': <br>
                <select class="multiselect form-control" name="whfrom[]" multiple="multiple">' . $warehouses_select . '</select></label>
                <label>' . l('Куда') . ': <br>
                <select class="multiselect form-control" name="whto[]" multiple="multiple">' . $warehouses_select_to . '</select></label>
                <div class="checkbox">
                    <label>
                        <input' . (isset($_GET['serials_in_orders']) ? ' checked' : '') . ' value="1" type="checkbox" name="serials_in_orders"> 
                            ' . l('разгрупировать') . '
                    </label>
                </div>
                <input type="button" onclick="send_get_form(this)" value="' . l('Фильтровать') . '" class="btn btn-primary" />
            </form>
        ';
        return $out;
    }

    /**
     * @return array
     */
    private function make_filters()
    {
        $filters_query = array('select' => '', 'from' => '', 'where' => array(), 'limit' => '');

        $date = isset($_GET['date']) ? explode('-', $_GET['date']) : '';
        $order_id = isset($_GET['o_id']) ? $_GET['o_id'] : '';
        $item_id = isset($_GET['i_id']) ? $_GET['i_id'] : '';
        $whfrom = isset($_GET['whfrom']) ? $_GET['whfrom'] : array();
        $whto = isset($_GET['whto']) ? $_GET['whto'] : array();
        $serials_in_order = isset($_GET['serials_in_orders']) ? 1 : 0;

        // фильтр по айдихе айтема
        if ($item_id || $order_id) {
            $item_order_filter = '';
            if ($item_id) {
                $item_id = suppliers_order_generate_serial(array(
                    'serial' => $item_id
                ), false);
                $item_order_filter .= $this->all_configs['db']->makeQuery(" (ch.item_id = ?i AND ch.item_type = 2) ",
                    array($item_id));
            }
            if ($item_id && $order_id) {
                $item_order_filter .= ' OR ';
            }
            if ($order_id) {
                $item_order_filter .= $this->all_configs['db']->makeQuery(" (ch.item_id = ?i AND ch.item_type = 1) ",
                    array($order_id));
            }
            $filters_query['where'][] = $item_order_filter;
        }

        // фильтр по дате
        if ($date) {
            $date_between = 'NOW()';
            if (isset($date[1])) {
                $date_between = $this->all_configs['db']->makeQuery(" ? ",
                    array(date('Y-m-d 23:59:59', strtotime($date[1]))));
            }
            $filters_query['where'][] = $this->all_configs['db']->makeQuery(" (from_m.date_move BETWEEN ? AND ?q) ",
                array(date('Y-m-d 00:00:00', strtotime($date[0])), $date_between));
        }

        // фильтр по складам
        if ($whfrom || $whto) {
            $filters_query['from'] .= ' LEFT JOIN {chains} as c ON c.id = ch.chain_id ';
            if ($whfrom) {
                $filters_query['where'][] = $this->all_configs['db']->makeQuery(" c.from_wh_id IN(?l) ",
                    array($whfrom));
            }
            if ($whto) {
                $filters_query['where'][] = $this->all_configs['db']->makeQuery(" c.to_wh_id IN(?l) ", array($whto));
            }
        }

        // вывод с изделиями которые привязаны к заказам 
        if (!$serials_in_order) {
            $filters_query['where'][] = "(item_type = 1 OR from_m.order_id IS NULL)";
        }

        // страницы
        $p = isset($_GET['p']) && $_GET['p'] > 1 ? $_GET['p'] - 1 : 0;
        $filters_query['limit'] = $this->all_configs['db']->makeQuery("LIMIT ?i, ?i",
            array($p * $this->count_on_page, $this->count_on_page));

        $filters_query['where'] = implode(' AND ', $filters_query['where']);
        return $filters_query;
    }

    /**
     * @return array
     * @throws Exception
     */
    function logistics_motions()
    {
        $out = '';

        $warehouses = get_service('wh_helper')->get_warehouses();
        $chains = $this->db->query("SELECT * FROM {chains} ORDER BY avail DESC")->assoc('id');

        $filter_query = $this->make_filters();

        // сортировка по дате последнего телодвижения:)
        $chains_moves = $this->all_configs['db']->query(
            "SELECT ch.*, from_m.date_move as from_date_move, log_m.date_move as log_date_move,
                    to_m.date_move as to_date_move, from_m.order_id as from_order_id
                    " . ($filter_query['select'] ? ',' . $filter_query['select'] : '') . "
             FROM {chains_moves} as ch
             " . $filter_query['from'] . "
             LEFT JOIN {warehouses_stock_moves} as from_m ON from_m.id = ch.from_move_id
             LEFT JOIN {warehouses_stock_moves} as log_m ON log_m.id = ch.logistics_move_id
             LEFT JOIN {warehouses_stock_moves} as to_m ON to_m.id = ch.to_move_id
             " . ($filter_query['where'] ? " WHERE " . $filter_query['where'] : '') . "
             ORDER BY COALESCE(to_date_move, log_date_move, from_date_move) DESC,
                      COALESCE(log_date_move, from_date_move) DESC,
                      from_date_move DESC
             " . $filter_query['limit']
        )->assoc();
        // для постраничной навигации
        $chains_moves_count_all = $this->all_configs['db']->query(
            "SELECT count(*)
             FROM {chains_moves} as ch
             " . $filter_query['from'] . "
             LEFT JOIN {warehouses_stock_moves} as from_m ON from_m.id = ch.from_move_id
             " . ($filter_query['where'] ? " WHERE " . $filter_query['where'] : ''))->el();

        $rows = '';
        if ($chains_moves) {

            $i = 1;
            foreach ($chains_moves as $move) {
                switch ($move['item_type']) {
                    case 1: // order
                        $link = '<a href="' . $this->all_configs['prefix'] . 'orders/create/' . $move['item_id'] . '">Заказ №' . $move['item_id'] . '</a>';
                        break;
                    case 2: // изделие (серийник)
                        $move['serial'] = '';
                        $link = suppliers_order_generate_serial($move, true, true);
                        // значит что изделие (запчасть) двигается в заказе
                        if ($move['from_order_id']) {
                            $link .= ' 
                                <span style="color:#666">
                                    (в заказе 
                                     <a style="color:#666" href="' . $this->all_configs['prefix'] . 'orders/create/' . $move['from_order_id'] . '">' .
                                '№' . $move['from_order_id'] .
                                '</a>)
                                </span>';
                        }
                        break;
                }
                $row_class = '';
                switch ($move['state']) {
                    case -1: // не закрыта
                        $row_class = 'error';
                        break;
                    case 0: // закрыта
                        $row_class = 'info';
                        break;
                    case 1: // открыта
                        break;
                }
                $rows .= '
                    <tr class="' . $row_class . '">
                        <td>' . ($i++) . '</td>
                        <td class="well">
                            ' . $link . '
                        </td>
                        <td' . ($move['from_move_id'] ? ' class="success"' : '') . '>
                            ' . $warehouses[$chains[$move['chain_id']]['from_wh_id']]['title'] . ' (' . $warehouses[$chains[$move['chain_id']]['from_wh_id']]['locations'][$chains[$move['chain_id']]['from_wh_location_id']]['name'] . ')' . '
                            <span title="' . $move['from_date_move'] . '">' . do_nice_date($move['from_date_move']) . '</span>
                        </td>
                        <td class="chain-body-arrow"></td>
                        <td' . ($move['logistics_move_id'] ? ' class="success"' : '') . '>
                            ' . $warehouses[$chains[$move['chain_id']]['logistic_wh_id']]['title'] . ($chains[$move['chain_id']]['logistic_wh_location_id'] ? ' (' . $warehouses[$chains[$move['chain_id']]['logistic_wh_id']]['locations'][$move['logistic_wh_location_id']]['name'] . ')' : '') . '
                            <span title="' . $move['log_date_move'] . '">' . do_nice_date($move['log_date_move']) . '</span>
                        </td>
                        <td class="chain-body-arrow"></td>
                        <td' . ($move['to_move_id'] ? ' class="success"' : '') . '>
                            ' . $warehouses[$chains[$move['chain_id']]['to_wh_id']]['title'] . ' (' . $warehouses[$chains[$move['chain_id']]['to_wh_id']]['locations'][$chains[$move['chain_id']]['to_wh_location_id']]['name'] . ')
                            <span title="' . $move['to_date_move'] . '">' . do_nice_date($move['to_date_move']) . '</span>
                        </td>
                    </tr>
                    <tr><td colspan="7"></td></tr>
                ';
            }
            $count_page = ceil($chains_moves_count_all / $this->count_on_page);
            $pages = page_block($count_page, $chains_moves_count_all, '#motions');
        } else {
            $rows = l('Нет цепочек');
            $pages = '';
        }

        $out = '
            <div class="row-fluid">
                <div class="span2">
                    ' . $this->filters_block() . '
                </div>
                <div class="span10">
                    <table class="table chains table-compact">
                        <tbody>
                            ' . $rows . '
                        </tbody>
                    </table>
                    ' . $pages . '
                </div>
            </div>
        ';

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    /**
     * @return array
     * @throws Exception
     */
    function logistics_settings()
    {
        $out = '';
        if ($this->all_configs["oRole"]->hasPrivilege("site-administration")) {
            // вывод существующих
            $chains = $this->db->query("SELECT * FROM {chains} ORDER BY avail DESC", array())->assoc();
            $out = $this->view->renderFile('logistics/logistics_settings', array(
                'chains' => $chains,
                'warehouses' => get_service('wh_helper')->get_warehouses()
            ));
        }

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
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
        $mod_id = $this->all_configs['configs']['logistics-manage-page'];

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        // проверка доступа
        if ($this->can_show_module() == false) {
            header("Content-Type: applicpreloadation/json; charset=UTF-8");
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
                        array(
                            (isset($_POST['hashs']) && mb_strlen(trim($_POST['hashs'],
                                    'UTF-8')) > 0) ? trim($_POST['hashs']) : null
                        )
                    );
                    echo json_encode(array(
                        'html' => $function['html'],
                        'state' => true,
                        'functions' => $function['functions']
                    ));
                } else {
                    echo json_encode(array('message' => l('Не найдено'), 'state' => false));
                }
                exit;
            }
        }

        // удаляем цепочку
        if ($act == 'remove-chain') {
            if (isset($_POST['chain_id']) && intval($_POST['chain_id']) > 0) {
                $this->all_configs['db']->query('UPDATE {chains} SET avail=?i WHERE id=?i',
                    array(0, intval($_POST['chain_id'])));
                $data['state'] = true;
            } else {
                $data['msg'] = l('Цепочка не найдена');
            }
        }

        // создаем цепочку
        if ($act == 'create-chain') {

            $whs = isset($_POST['wh_id_destination']) ? (array)$_POST['wh_id_destination'] : array();
            $locs = isset($_POST['location']) ? (array)$_POST['location'] : array();

            $wh_from_id = isset($whs[0]) ? intval($whs[0]) : null;
            $location_from_id = isset($locs[0]) ? intval($locs[0]) : null;

            $wh_to_id = isset($whs[2]) ? intval($whs[2]) : null;
            $location_to_id = isset($locs[2]) ? intval($locs[2]) : null;

            $logistic = isset($whs[1]) ? intval($whs[1]) : null;

            $data['state'] = true;

            if ($data['state'] == true && $wh_from_id == 0) {
                $data['state'] = false;
                $data['msg'] = l('Укажите склад откуда');
            }
            if ($data['state'] == true && $location_from_id == 0) {
                $data['state'] = false;
                $data['msg'] = l('Укажите локацию откуда');
            }
            if ($data['state'] == true && $wh_to_id == 0) {
                $data['state'] = false;
                $data['msg'] = l('(Укажите склад куда');
            }
            if ($data['state'] == true && $location_to_id == 0) {
                $data['state'] = false;
                $data['msg'] = l('Укажите локацию куда');
            }
            if ($data['state'] == true && $logistic == 0) {
                $data['state'] = false;
                $data['msg'] = l('Укажите логистику');
            }
            if ($data['state'] == true && $location_to_id == $location_from_id) {
                $data['state'] = false;
                $data['msg'] = l('Локация откуда не может совпадать с локацией куда');
            }
            if ($data['state'] == true) {
                $isset = $this->db->query('SELECT id FROM {chains} '
                    . 'WHERE from_wh_id = ? AND from_wh_location_id = ?i AND avail = 1',
                    array($wh_from_id, $location_from_id))->el();
                if ($isset) {
                    $data['state'] = false;
                    $data['msg'] = l('Такая локация уже существует');
                }
            }
            if ($data['state'] == true) {
                $this->db->query('INSERT INTO {chains}(date_add, user_id, avail, '
                    . 'from_wh_id, from_wh_location_id, '
                    . 'logistic_wh_id, logistic_wh_location_id, '
                    . 'to_wh_id, to_wh_location_id) '
                    . 'VALUES (NOW(), ?i, 1, '
                    . '?i, ?i, '
                    . '?i, ?q, '
                    . '?i, ?i)',
                    array(
                        $user_id,
                        $wh_from_id,
                        $location_from_id,
                        $logistic,
                        'null',
                        $wh_to_id,
                        $location_to_id
                    ));
            }
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

    /**
     *
     */
    function preload()
    {
    }

    /**
     * @return array
     */
    public static function get_submenu()
    {
        return array(
            array(
                'click_tab' => true,
                'url' => '#motions',
                'name' => l('Логистика')
            ),
            array(
                'click_tab' => true,
                'url' => '#settings',
                'name' => l('Настройки')
            )
        );
    }

}