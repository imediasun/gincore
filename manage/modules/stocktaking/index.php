<?php

require_once __DIR__ . '/../../Core/Controller.php';

$modulename[220] = 'stocktaking';
$modulemenu[220] = l('Инвентаризация');
$moduleactive[220] = !$ifauth['is_2'];

define('CHECK_ERROR', 0);
define('CHECK_BOTH', 1);
define('CHECK_DEFICIT', 2);

/**
 * Class stocktaking
 *
 * @property MStocktaking Stocktaking
 */
class stocktaking extends Controller
{
    protected $lastCheck = array();
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
                if ($function['reload']) {
                    $data = array(
                        'state' => true,
                        'reload' => $function['reload']
                    );
                } else {
                    $data = array(
                        'html' => $function['html'],
                        'state' => true,
                        'functions' => $function['functions']
                    );
                }
                if (isset($function['menu'])) {
                    $data['menu'] = $function['menu'];
                }
            } else {
                $data = array('message' => l('Не найдено'), 'state' => false);
            }
        }
        if ($act == 'exports-stocktaking') {
            $this->exportStocktaking($_POST);
        }
        Response::json($data);
    }

    /**
     * @param array $post
     * @return string
     */
    public function check_post(Array $post)
    {
        if (isset($post['new-stocktaking'])) {
            $this->createStocktaking($post);
        }
        if (isset($post['save-stocktaking'])) {
            $this->saveStocktaking($post);
        }
        if (isset($post['filter-serial'])) {
            $lastCheck = $this->checkSerial($post);
            Session::getInstance()->set('last_check', $lastCheck);
        }
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

        if ($stocktaking['history'] == MStocktaking::BACKUP) {
            $stocktaking = $this->Stocktaking->restore($stocktaking['id']);
            return array(
                'state' => true,
                'reload' => $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?stocktaking=' . $stocktaking['id'],
            );
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
            ORDER BY history ASC, created_at DESC, saved_at DESC
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

    /**
     * @param $stocktaking
     * @return mixed
     */
    protected function createQueryByStocktaking($stocktaking)
    {
        $query = $this->all_configs['db']->makeQuery('l.id = ?i',
            array($stocktaking['location_id']));
        $query = $this->all_configs['db']->makeQuery('?query AND w.id = ?i',
            array($query, $stocktaking['warehouse_id']));
        return $query;
    }

    /**
     * @param $stocktaking
     * @return array
     * @throws Exception
     */
    public function stocktaking($stocktaking)
    {
        $warehouses = get_service('wh_helper')->get_warehouses();
        $locations = isset($warehouses[$stocktaking['warehouse_id']]['locations']) ? $warehouses[$stocktaking['warehouse_id']]['locations'] : array();

        $count_on_page = $this->count_on_page;//30;
        $skip = (isset($_GET['p']) && $_GET['p'] > 0) ? ($count_on_page * ($_GET['p'] - 1)) : 0;


        $goods = $this->getItems($stocktaking, $count_on_page, $skip);

        $query = $this->createQueryByStocktaking($stocktaking);

        $count = $this->all_configs['db']->query('SELECT COUNT(DISTINCT i.id)
                    FROM {warehouses} as w
                    JOIN {warehouses_goods_items} as i ON i.wh_id=w.id 
                    JOIN {goods} as g ON g.id=i.goods_id
                    JOIN {contractors} as u ON u.id=i.supplier_id
                    JOIN {warehouses_locations} as l ON l.id=i.location_id 
                    WHERE ?query',
            array($query))->el();
        $count_page = $count / $count_on_page;


        $this->view->load('Numbers');
        return array(
            'html' => $this->view->renderFile('stocktaking/stocktaking', array(
                'filters' => $this->filter_block($warehouses, $locations, $stocktaking, $count),
                'count_page' => $count_page,
                'goods' => $goods,
                'stocktaking' => $stocktaking
            )),
            'functions' => array('multiselect()'),
        );
    }

    /**
     * @param     $warehouses
     * @param     $locations
     * @param     $stocktaking
     * @param     $count
     * @return string
     */
    public function filter_block($warehouses, $locations, $stocktaking, $count)
    {
        return $this->view->renderFile('stocktaking/filter_block', array(
            'warehouses' => $warehouses,
            'current_warehouse' => $stocktaking['warehouse_id'],
            'locations' => $locations,
            'current_location' => $stocktaking['location_id'],
            'stocktaking' => $stocktaking,
            'count' => $count,
            'last' => Session::getInstance()->get('last_check')
        ));
    }

    /**
     * @param array $stocktaking
     * @param null  $count_on_page
     * @param null  $skip
     * @return array
     */
    private function getItems($stocktaking, $count_on_page = null, $skip = null)
    {
        $query = $this->createQueryByStocktaking($stocktaking);

        $limit = '';
        if ($count_on_page || $skip) {
            $limit = $this->all_configs['db']->makeQuery('LIMIT ?i, ?i', array(intval($skip), intval($count_on_page)));
        }

        $select = $this->all_configs['db']->makeQuery('w.id, w.title, w.code_1c, w.consider_all,
                    w.consider_store, g.title as product_title, i.id as item_id, i.date_add, i.goods_id,
                    i.order_id, i.serial, i.date_sold, i.price, i.supplier_id as user_id,
                    u.title as contractor_title, i.supplier_order_id, l.location, i.location_id', array());
        return $this->all_configs['db']->query('SELECT ?query
                    FROM {warehouses} as w
                    JOIN {warehouses_goods_items} as i ON i.wh_id=w.id 
                    JOIN {goods} as g ON g.id=i.goods_id
                    JOIN {contractors} as u ON u.id=i.supplier_id
                    JOIN {warehouses_locations} as l ON l.id=i.location_id 
                    WHERE ?query ?query',
            array($select, $query, $limit))->assoc('item_id');
    }

    /**
     * @param $post
     * @return bool
     */
    protected function createStocktaking($post)
    {
        try {
            if (empty($post['warehouses']) || empty($post['locations'])) {
                throw new ExceptionWithMsg(l('Склад или локация не заданы'));
            }
            $warehouse = $this->all_configs['db']->query('SELECT id FROM {warehouses} WHERE id =?i',
                array(current($post['warehouses'])))->el();
            $location = $this->all_configs['db']->query('SELECT id FROM {warehouses_locations} WHERE id =?i',
                array(current($post['locations'])))->el();
            if (empty($warehouse) || empty($location)) {
                throw new ExceptionWithMsg(l('Склад или локация не найдены'));
            }
            $id = $this->Stocktaking->newStocktaking($warehouse, $location);

            Response::redirect($this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?stocktaking=' . $id);
        } catch (ExceptionWithMsg $e) {
            FlashMessage::set($e->getMessage(), FlashMessage::DANGER);
        }
        return false;
    }

    /**
     * @param $post
     * @return bool
     */
    protected function saveStocktaking($post)
    {
        try {
            if (empty($post['stocktaking-id'])) {
                throw new ExceptionWithMsg(l('Номер инвентаризации не задан'));
            }
            $this->Stocktaking->backup($post['stocktaking-id']);
            FlashMessage::set(l('Резервная копия успешно создана'), FlashMessage::SUCCESS);
        } catch (ExceptionWithMsg $e) {
            FlashMessage::set($e->getMessage(), FlashMessage::DANGER);
        }
        return false;

    }

    /**
     * @param $post
     * @return bool
     */
    protected function checkSerial($post)
    {
        $result = array();
        try {
            $stocktaking = isset($post['stocktaking']) && is_numeric($post['stocktaking']) ? $this->Stocktaking->load($post['stocktaking']) : array();
            if (empty($stocktaking)) {
                throw new ExceptionWithMsg(l('Инвентаризация не найдена'));
            }
            if (empty($post['serial'])) {
                throw new ExceptionWithMsg(l('Серийный номер не задан'));
            }
            if (!in_array($post['serial'], $stocktaking['checked_serials']['both']) && !in_array($post['serial'],
                    $stocktaking['checked_serials']['deficit'])
            ) {
                if ($this->searchBySerial($post['serial'], $stocktaking)) {
                    $this->Stocktaking->appendSerialToBoth($post['serial'], $stocktaking);
                    $result['message'] = "<span style='color: green'>{$post['serial']}</span>";
                } else {
                    $this->Stocktaking->appendSerialToDeficit($post['serial'], $stocktaking);
                    $result['message'] = "<span style='color: yellow'>{$post['serial']}</span>";
                }
            }
            if (in_array($post['serial'], $stocktaking['checked_serials']['both'])) {
                $result['result'] = CHECK_BOTH;
                $result['message'] = "<span style='color: green'>{$post['serial']}</span>";
            }
            if (in_array($post['serial'], $stocktaking['checked_serials']['deficit'])) {
                $result['result'] = CHECK_DEFICIT;
                $result['message'] = "<span style='color: red'>{$post['serial']}</span>";
            }
        } catch (ExceptionWithMsg $e) {
            return array(
                'result' => CHECK_ERROR,
                'message' => "<span style='color: red'>" . $e->getMessage() . "</span>"
            );
        }
        return $result;
    }

    /**
     * @param $serial
     * @param $stocktaking
     * @return bool
     */
    protected function searchBySerial($serial, $stocktaking)
    {
        if (preg_match("/{$this->all_configs['configs']['erp-serial-prefix']}0*/", $serial)) {
            list($prefix, $length) = prepare_for_serial_search($this->all_configs['configs']['erp-serial-prefix'],
                $serial,
                $this->all_configs['configs']['erp-serial-count-num']);
            $query = $this->all_configs['db']->makeQuery('i.id REGEXP "^?e[0-9]?e$"', array($prefix, "{0,{$length}}"));
        } else {
            $query = $this->all_configs['db']->makeQuery('i.id LIKE "%?e%"',
                array(intval(preg_replace('/[^0-9]/', '', $serial))));
        }
        $whereWhAndLocation = $this->createQueryByStocktaking($stocktaking);

        return (bool)$this->all_configs['db']->query('
                    SELECT COUNT(*) 
                    FROM {warehouses_goods_items} as i
                    JOIN {warehouses} as w ON i.wh_id=w.id 
                    JOIN {warehouses_locations} as l ON l.id=i.location_id 
                    WHERE ((serial LIKE "%?e%" OR (?query AND serial IS NULL) AND order_id IS NULL)) AND ?query',
            array($serial, $query, $whereWhAndLocation))->el();
    }

    protected function exportStocktaking($post)
    {
        try {
            if (empty($post['stocktaking-id'])) {
                throw new ExceptionWithMsg(l('Номер инвентаризации не задан'));
            }
            $stocktaking = $this->Stocktaking->load($post['stocktaking-id']);
            if (empty($stocktaking)) {
                throw new ExceptionWithMsg(l('Инвентаризация не найдена'));
            }
            $goods = $this->getItems($stocktaking);
            $this->makeXLS(array(
                lq('Серийный номер'),
                lq('Наименование'),
                lq('Склад'),
                lq('Локация'),
                lq('Заказ клиента'),
                lq('Заказ поставщику'),
                lq('Результат')
            ), $goods, $stocktaking);

        } catch (ExceptionWithMsg $e) {
            FlashMessage::set($e->getMessage(), FlashMessage::DANGER);
        }
    }
}
