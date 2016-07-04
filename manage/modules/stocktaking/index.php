<?php

require_once __DIR__ . '/../../Core/Controller.php';

$modulename[220] = 'stocktaking';
$modulemenu[220] = l('Инвентаризация');
$moduleactive[220] = !$ifauth['is_2'];

/**
 * Class stocktaking
 *
 * @property MStocktaking Stocktaking
 */
class stocktaking extends Controller
{
    public $uses = array(
        'Stocktaking'
    );

    /**
     * warehouses constructor.
     * @param $all_configs
     */
    public function __construct(&$all_configs)
    {
        parent::__construct($all_configs);
    }

    public function ajax()
    {
        $data = array(
            'state' => false,
            'message' => l('Handler not found')
        );
        $mod_id = $this->all_configs['configs']['warehouses-manage-page'];

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        // проверка доступа
        if ($this->can_show_module() == false) {
            Response::json(array('message' => l('Нет прав'), 'state' => false));
        }

        // грузим табу
        if ($act == 'tab-load') {
            if (isset($_POST['tab']) && !empty($_POST['tab']) && method_exists($this, $_POST['tab'])) {
                $function = call_user_func_array(
                    array($this, $_POST['tab']),
                    array(
                        (isset($_POST['hashs']) && mb_strlen(trim($_POST['hashs'],
                                'UTF-8')) > 0) ? trim($_POST['hashs']) : null
                    )
                );
                $data = array(
                    'html' => $function['html'],
                    'state' => true,
                    'functions' => $function['functions']
                );
                if (isset($function['menu'])) {
                    $data['menu'] = $function['menu'];
                }
            } else {
                $data = array('message' => l('Не найдено'), 'state' => false);
            }
        }
        Response::json($data);
    }

    public function check_post(Array $post)
    {
        return '';
    }

    /**
     * @return mixed
     */
    public function can_show_module()
    {
        return $this->all_configs['oRole']->hasPrivilege('create-goods');
    }

    /**
     * @return string
     */
    public function gencontent()
    {
        return $this->view->renderFile('stocktaking/gencontent', array(
            'mod_submenu' => $this->mod_submenu
        ));
    }

    /**
     * @inheritdoc
     */
    public static function get_submenu($oRole = null)
    {
        return array(
            array(
                'click_tab' => true,
                'url' => '#warehouses_stocktaking',
                'name' => l('Инвентаризация')
            ),
        );
    }

    /**
     * @return array|string
     */
    protected function warehouses_stocktaking()
    {
        $stocktaking = isset($_GET['stocktaking']) && is_numeric($_GET['stocktaking']) ? $this->Stocktaking->load($_GET['stocktaking']) : array();

        if (empty($stocktaking)) {
            return $this->select_or_new();
        }

        return $this->stocktaking($stocktaking);
    }

    /**
     * @return string
     */
    public function select_or_new()
    {
        $warehouses = $this->all_configs['chains']->warehouses();
        $stocktakings = $this->Stocktaking->query('
            SELECT s.id, s.history, s.created_at, s.saved_at, w.title as warehouse, l.location as location 
            FROM {stocktaking} as s 
            JOIN {warehouses} as w ON w.id = s.warehouse_id
             JOIN {warehouses_locations} as l ON l.id = s.location_id
            ORDER BY history ASC, created_at DESC
        ', array())->assoc();
        return array(
            'html' =>
                $this->view->renderFile('stocktaking/select_or_new', array(
                    'warehouses' => $warehouses,
                    'stocktakings' => $stocktakings
                )),
            'functions' => array('multiselect()'),
        );
    }

    public function stocktaking($stocktaking)
    {
        $warehouses = $this->all_configs['chains']->warehouses();
        $query = '';
        $query_for_noadmin = '';
        $count_on_page = $this->count_on_page;//30;
        $count_page = 1;
        $skip = (isset($_GET['p']) && $_GET['p'] > 0) ? ($count_on_page * ($_GET['p'] - 1)) : 0;
        $warehouses_selected = (isset($_GET['whs']) && !empty($_GET['whs'])) ? explode(',', $_GET['whs']) : array();

        if (isset($_GET['lcs']) && array_filter(explode(',', $_GET['lcs'])) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND l.id IN (?li)',
                array($query, explode(',', $_GET['lcs'])));
        }

        if (count($warehouses_selected) > 0) {
            $goods = $this->getItems($_GET, $count_on_page, $skip);

            $count_page = $this->all_configs['db']->query('SELECT COUNT(DISTINCT i.id)
                            FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {contractors} as u, {warehouses_locations} as l
                            WHERE i.wh_id=w.id AND g.id=i.goods_id AND u.id=i.supplier_id AND l.id=i.location_id ?query ?query',
                    array($query, $query_for_noadmin))->el() / $count_on_page;
        }


        $this->view->load('Numbers');
        return array(
            'html' => $this->view->renderFile('stocktaking/stocktaking', array(
                'filters' => $this->filter_block($warehouses, $warehouses_selected),
                'count_page' => $count_page,
                'wh_selected' => $warehouses_selected,
                'goods' => $goods
            )),
            'functions' => array('multiselect()'),
        );
    }

    /**
     * @param     $warehouses
     * @param     $warehouses_selected
     * @param int $i
     * @return string
     */
    public function filter_block($warehouses, $warehouses_selected, $i = 1)
    {
        $wh_select = '';
        if (isset($_GET['whs'])) {
            $wh_select = $this->all_configs['suppliers_orders']->gen_locations($_GET['whs'],
                isset($_GET['lcs']) ? $_GET['lcs'] : null);
        }
        // фильтр по серийнику
        return $this->view->renderFile('stocktaking/filter_block', array(
            'warehouses' => $warehouses,
            'warehouses_selected' => $warehouses_selected,
            'i' => $i,
            'whSelect' => $wh_select
        ));
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
        $warehouses_selected = (isset($filters['whs']) && !empty($filters['whs'])) ? explode(',',
            $filters['whs']) : array();
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
        if (count($warehouses_selected) > 0) {
            if (count($warehouses_selected) > 0) {
                $query = $this->all_configs['db']->makeQuery('?query AND w.id IN (?li)',
                    array($query, array_values($warehouses_selected)));
            }

            $select = $this->all_configs['db']->makeQuery('w.id, w.title, w.code_1c, w.consider_all,
                    w.consider_store, g.title as product_title, i.id as item_id, i.date_add, i.goods_id,
                    i.order_id, i.serial, i.date_sold, i.price, i.supplier_id as user_id,
                    u.title as contractor_title, i.supplier_order_id, l.location, i.location_id', array());
            $goods = $this->all_configs['db']->query('SELECT ?query
                    FROM {warehouses} as w, {warehouses_goods_items} as i, {goods} as g, {contractors} as u, {warehouses_locations} as l
                    WHERE i.wh_id=w.id AND g.id=i.goods_id AND u.id=i.supplier_id AND l.id=i.location_id ?query ?query ?query',
                array($select, $query, $query_for_noadmin, $limit))->assoc();
        }

        return $goods;
    }
}
