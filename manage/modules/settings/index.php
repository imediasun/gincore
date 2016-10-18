<?php

require_once __DIR__ . '/../../Core/Controller.php';

// настройки
$modulename[110] = 'settings';
$modulemenu[110] = l('sets_modulemenu');  //карта сайта

global $all_configs;
$moduleactive[110] = $all_configs['oRole']->hasPrivilege('edit-users');

class settings extends Controller
{
    protected $dbcfg;

    /**
     * settings constructor.
     * @param $all_configs
     */
    public function __construct($all_configs)
    {
        global $dbcfg;
        $this->dbcfg = $dbcfg;

        parent::__construct($all_configs);

    }

    public function render()
    {
        global $input_html;
        $input_html['mmenu'] = $this->genmenu();
        return parent::render(); 
    }

    /**
     * @return string
     */
    protected function genmenu()
    {
        return $this->view->renderFile('settings/genmenu', MSettings::getMenuVars($this->all_configs));
        
    }

    /**
     * @return mixed|string
     */
    public function gencontent()
    {
        $out = '';

        if (!isset($this->all_configs['arrequest'][1])) {
            $out = l('sets_description');
        } else {

            $action = $this->all_configs['arrequest'][1];

            $tpl_name = 'settings/gencontent';
            $tpl_vars = [];

            switch ($action) {
                case 'add':
                    $tpl_name = 'settings/add_new_params_form';

                    break;
                case 'edit':
                    $setting_id = (int)$this->all_configs['arrequest'][2];

                    $pp = $this->all_configs['db']->query("SELECT * FROM {settings} WHERE id = ?i AND `ro` = 0",
                        array($setting_id), 'row');

                    if ( $pp['section']!= 1)
                        Response::redirect($this->all_configs['prefix'] . 'settings/section/' . $pp['section']);

                    $tpl_vars = array(
                        'settings' => [$pp],
                        'orderWarranties' => isset($this->all_configs['settings']['order_warranties']) ? explode(',',
                            $this->all_configs['settings']['order_warranties']) : array(),
                    );

                    if(strcmp($pp['name'], 'time_zone') === 0){
                        $list = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
                        $continents_zones = array();
                        foreach($list as $zone){
                            list($continent, ) = explode('/', $zone);
                            $continents_zones[$continent][] = $zone;
                        }
                        $tpl_vars['timeZones'] = $continents_zones;
                    }

                    break;
                case 'section':
                    $section_id = $this->all_configs['arrequest'][2];
                    $result = $this->all_configs['db']->query("SELECT * FROM {settings} WHERE `ro` = 0 AND `section`=?i ORDER BY `title`", [$section_id])->assoc();

                    $tpl_vars = [
                        'settings' => $result,
                        'orderWarranties' => isset($this->all_configs['settings']['order_warranties']) ? explode(',',
                            $this->all_configs['settings']['order_warranties']) : array(),
                    ];


                    break;

                case 'update':


                    break;

                default:
                    if (isset($this->all_configs['arrequest'][1]) && in_array($this->dbcfg['_prefix'] . $this->all_configs['arrequest'][1],
                            array_keys($this->genconfig()))
                    ) {
                        return $this->processTable($this->dbcfg['_prefix'] . $this->all_configs['arrequest'][1]);
                    }

                    break;

            }
            
            $tpl_vars['action'] = $action;
            $out = $this->view->renderFile($tpl_name, $tpl_vars);

        }

//        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'add') {
//            $out = $this->view->renderFile('settings/add_new_params_form');
//        }

###############################################################################
//        if (isset($this->all_configs['arrequest'][1]) && is_numeric($this->all_configs['arrequest'][1])) {
//            $pp = $this->all_configs['db']->query("SELECT * FROM {settings} WHERE id = ?i AND `ro` = 0",
//                array($this->all_configs['arrequest'][1]), 'row');
//
//            $tpl_vars = array(
//                'pp' => $pp,
//                'orderWarranties' => isset($this->all_configs['settings']['order_warranties']) ? explode(',',
//                    $this->all_configs['settings']['order_warranties']) : array(),
//            );
//
//            if(strcmp($pp['name'], 'time_zone') === 0){
//                $list = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
//                $continents_zones = array();
//                foreach($list as $zone){
//                    list($continent, ) = explode('/', $zone);
//                    $continents_zones[$continent][] = $zone;
//                }
//                $tpl_vars['timeZones'] = $continents_zones;
//            }
//
//            $out = $this->view->renderFile('settings/gencontent', $tpl_vars);
//        }


################################################################################
//        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'save') {
//            $out = $this->view->renderFile('settings/save');
//        }

################################################################################
//        if (isset($this->all_configs['arrequest'][1]) && in_array($this->dbcfg['_prefix'] . $this->all_configs['arrequest'][1],
//                array_keys($this->genconfig()))
//        ) {
//            $out = $this->processTable($this->dbcfg['_prefix'] . $this->all_configs['arrequest'][1]);
//        }

        return $out;
    }

    /**
     * @throws Exception
     */
    public function ajax()
    {
        $data = array(
            'state' => false
        );
        if (!empty($_GET['act']) && $_GET['act'] == 'show-tariff') {
            try {
                $tariff = Tariff::load($this->all_configs['configs']['api_url'], $_SERVER['SERVER_NAME']);
                $usersCount = db()->query('SELECT count(*) FROM {users} WHERE deleted=0 AND blocked_by_tariff=0')->el();
                $orderCount = db()->query('SELECT count(*) FROM {orders} WHERE date_add > ?',
                    array($tariff['start']))->el();
            } catch (Exception $e) {
                $tariff = array();
                $usersCount = 0;
                $orderCount = 0;
            }

            $data = array(
                'state' => true,
                'title' => l('Текущий тариф'),
                'content' => $this->view->renderFile('settings/tariff', array(
                    'tariff' => $tariff,
                    'usersCount' => $usersCount,
                    'orderCount' => $orderCount
                ))
            );
        }
        Response::json($data);
    }

    /**
     * @param $table
     * @return bool
     */
    private function table_exists($table)
    {
        $config = $this->genconfig();
        return isset($config[$table]) && ($this->all_configs['db']->query("SHOW TABLES LIKE ?", array($table))->ar());
    }

    /**
     * @return array
     */
    private function genconfig()
    {
        return array(
            $this->dbcfg['_prefix'] . 'sources' => array(
                'settings' => array('name' => l('Источники рекламы и телефоны')),
                'columns' => array(
                    //hide, ro, realname, default
                    'id' => array('0', '1', 'ID', ''),
                    'source' => array('0', '0', '' . l('Источник') . '(city,adw)', ''),
                    'phone_mobile' => array('0', '0', l('Телефон мобильный'), ''),
                    'phone_static' => array('0', '0', l('Телефон стационарный'), '')
                )
            ),
            $this->dbcfg['_prefix'] . 'crm_referers' => array(
                'settings' => array('name' => l('Список каналов (источники продаж)')),
                'columns' => array(
                    //hide, ro, realname, default
                    'id' => array('0', '1', 'ID', ''),
                    'name' => array('0', '0', l('Название'), ''),
                    'group_id' => array(
                        '0',
                        '0',
                        l('Группа') . ' (0-' . l('Затраты') . ', 1-Context, 2-Remarketing, 3-Search)',
                        ''
                    ),
                )
            ),
            $this->dbcfg['_prefix'] . 'brands' => array(
                'settings' => array('name' => l('Список брендов')),
                'columns' => array(
                    'id' => array('0', '1', 'ID', ''),
                    'title' => array('0', '0', l('Название'), ''),
                )
            ),
        );
    }

    /**
     * @param $table
     * @return string
     */
    private function processTable($table)
    {
        $conf = $this->genconfig();

        $out = '';
        if (isset($this->all_configs['arrequest'][1]) && $this->table_exists($table)) {
            $columns = $this->all_configs['db']->query("SHOW COLUMNS FROM ?q", array($table))->assoc();
            if (!isset($this->all_configs['arrequest'][2])) {
                $cols = array();
                foreach ($columns as $pp) {
                    $cols[] = $pp['Field'];
                }

                $sql_order = ' ORDER BY id DESC';
                if (in_array('prio', $cols)) {
                    $sql_order = ' ORDER BY prio';
                }
                $rows = $this->all_configs['db']->query("SELECT * FROM ?q ?q LIMIT 1000",
                    array($table, $sql_order))->assoc();
                $out = $this->view->renderFile('settings/table/show', array(
                    'table' => $table,
                    'conf' => $conf,
                    'columns' => $columns,
                    'rows' => $rows,
                    'cols' => $cols
                ));
            }
###############################################################################
            if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'add') {
                $cols = array();
                $vars = array();
                foreach ($columns as $pp) {
                    $pp = array_values($pp);
                    if ($conf[$table]['columns'][$pp[0]][1] != 1) {
                        $cols[] = $pp[0];
                        if (isset($conf[$table]['columns'][$pp[0]][5])) {
                            $vars[$pp[0]] = $this->all_configs['db']->query('SELECT id, name FROM {?query}',
                                array($conf[$table]['columns'][$pp[0]][5]))->vars();
                        }
                    }
                }

                $out = $this->view->renderFile('settings/table/row_add_form', array(
                    'table' => $table,
                    'conf' => $conf,
                    'columns' => $columns,
                    'vars' => $vars,
                    'cols' => $cols
                ));
            }
###############################################################################
            if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'del' && is_numeric($this->all_configs['arrequest'][3])) {
                $this->all_configs['db']->query("DELETE FROM ?q WHERE id=?i LIMIT 1",
                    array($table, $this->all_configs['arrequest'][3]));
                Response::redirect($this->all_configs['prefix'] . 'settings/' . $this->all_configs['arrequest'][1]);
            }
###############################################################################
            if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'edit' && is_numeric($this->all_configs['arrequest'][3])) {
                $cols = array();
                $vars = array();

                $row = $this->all_configs['db']->query("SELECT * FROM ?q WHERE id = ?i",
                    array($table, $this->all_configs['arrequest'][3]), 'row');
                foreach ($row as $k => $pp) {
                    if (!isset($conf[$table]['columns'][$k][1]) || $conf[$table]['columns'][$k][1] != 1) {
                        $cols[] = $pp;
                        if (isset($conf[$table]['columns'][$k][5])) {
                            $vars[$pp] = $this->all_configs['db']->query('SELECT id, name FROM {?query}',
                                array($conf[$table]['columns'][$k][5]))->vars();
                        }
                    }
                }

                $out = $this->view->renderFile('settings/table/row_edit_form', array(
                    'table' => $table,
                    'conf' => $conf,
                    'columns' => $columns,
                    'vars' => $vars,
                    'cols' => $cols,
                    'row' => $row
                ));
            }
        }

        return $out;
    }

    /**
     * @inheritdoc
     */
    public function check_post(Array $post)
    {
        $out = '';
        if (isset($this->all_configs['arrequest'][1])) {
            $table = $this->dbcfg['_prefix'] . $this->all_configs['arrequest'][1];
        }

        $conf = $this->genconfig();


        if (isset($this->all_configs['arrequest'][1]) && $this->table_exists($table) && isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'update' && is_numeric($this->all_configs['arrequest'][3])) {
            $sql = $this->all_configs['db']->query("SHOW COLUMNS FROM ?q", array($table))->assoc();
            $sql_value = array();

            try {
                foreach ($sql as $pp) {
                    if (!isset($conf[$table]['columns'][$pp['Field']][1]) || $conf[$table]['columns'][$pp['Field']][1] != 1) { //не РО в конфиге
                        $value = trim($post[$pp['Field']]);
                        if (empty($value)) {
                            throw new Exception (l('Поле не может быть пустым'));
                        }
                        $sql_value[] = $this->all_configs['db']->makeQuery('?c = ?',
                            array($pp['Field'], $value));
                    }
                }
                $sql_values = implode(', ', $sql_value);
                $this->all_configs['db']->query("UPDATE ?q SET ?q WHERE id=?i",
                    array($table, $sql_values, $this->all_configs['arrequest'][3]));
            } catch (Exception $e) {
                FlashMessage::set($e->getMessage(), FlashMessage::DANGER);
            }
            
            Response::redirect($this->all_configs['prefix'] . 'settings/' . $this->all_configs['arrequest'][1]);
        }

        if (isset($this->all_configs['arrequest'][1]) && $this->table_exists($table) && isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'insert') {
            $sql = $this->all_configs['db']->query("SHOW COLUMNS FROM ?q", array($table))->assoc();
            $sql_cols = array();
            $sql_values = array();

            try {
                foreach ($sql as $pp) {
                    $pp = array_values($pp);
                    if($pp[0] == 'id') {
                        continue;
                    }
                    $value = trim($post[$pp[0]]);
                    if (empty($value)) {
                        if($this->all_configs['arrequest'][1] == 'crm_referers') {
                            $value = 0;
                        } else {
                            throw new Exception (l('Поле не может быть пустым'));
                        }
                    }
                    if ($conf[$table]['columns'][$pp[0]][1] != 1) { //не РО в конфиге
                        $sql_cols[] = $pp[0];
                        $sql_values[] = $value;
                    }
                }
                $this->all_configs['db']->query("INSERT INTO `?q` (?cols) VALUES (?l)",
                    array($table, $sql_cols, $sql_values));
            } catch (Exception $e) {
                FlashMessage::set($e->getMessage(), FlashMessage::DANGER);
            }

            Response::redirect($this->all_configs['prefix'] . 'settings/' . $this->all_configs['arrequest'][1]);
        }

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'add') {
            if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'ok' && isset($post['name']) && isset($post['value']) && isset($post['title'])) {
                $this->all_configs['db']->query("INSERT INTO {settings}(description, name, value, title, ro) 
                            VALUES(?, ?, ?, ?, ?i)", array(
                    $post['description'],
                    $post['name'],
                    $post['value'],
                    $post['title'],
                    isset($post['ro']) ? 1 : 0
                ));
                Response::redirect($this->all_configs['prefix'] . 'settings');
            }
        }
        ###############################################################################
        if (isset($this->all_configs['arrequest'][1]) && is_numeric($this->all_configs['arrequest'][1])) {
            $value = isset($post['value']) ? $post['value'] : '';
            if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'update') {
                
                // save time_zone settings
                if(isset($post['time_zone'])){
                    if(in_array($post['time_zone'], DateTimeZone::listIdentifiers(DateTimeZone::ALL))){
                        $value = $post['time_zone'];
                    }else{
                        FlashMessage::set(l('Указана несуществующая временная зона'), FlashMessage::DANGER);
                        Response::redirect($this->all_configs['prefix'].'settings/'.$this->all_configs['arrequest'][1]);
                    }
                }
                if(isset($post['crm-requests-statuses'])){
                    $newStatuses = $this->getNewRequestsStatus($_POST);
                    if(!empty($newStatuses)) {
                        $value = json_encode($newStatuses);
                    } else {
                        $value = db()->query('SELECT `value` FROM {settings} WHERE `name`="crm-requests-statuses"')->el();
                    }
                }
                $this->all_configs['db']->query("UPDATE {settings} SET value=?
                             WHERE id=?i AND ro=0 LIMIT 1", array($value, $this->all_configs['arrequest'][1]), 'ar');

                Response::redirect($this->all_configs['prefix'] . 'settings/save/' . $this->all_configs['arrequest'][1]);
            }
        }



        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'update') {

//            dd($this->all_configs['arrequest'][2]);

            $value = isset($post['value']) ? $post['value'] : '';

                // save time_zone settings
                if(isset($post['time_zone'])){
                    if(in_array($post['time_zone'], DateTimeZone::listIdentifiers(DateTimeZone::ALL))){
                        $value = $post['time_zone'];
                    }else{
                        FlashMessage::set(l('Указана несуществующая временная зона'), FlashMessage::DANGER);
                        Response::redirect($this->all_configs['prefix'].'settings/edit/'.$this->all_configs['arrequest'][2]);
                    }
                }
                if(isset($post['crm-requests-statuses'])){
                    $newStatuses = $this->getNewRequestsStatus($_POST);
                    if(!empty($newStatuses)) {
                        $value = json_encode($newStatuses);
                    } else {
                        $value = db()->query('SELECT `value` FROM {settings} WHERE `name`="crm-requests-statuses"')->el();
                    }
                }
                $this->all_configs['db']->query("UPDATE {settings} SET value=?
                             WHERE id=?i AND ro=0 LIMIT 1", array($value, $this->all_configs['arrequest'][2]), 'ar');

                Response::redirect($this->all_configs['prefix'] . 'settings/edit/' . $this->all_configs['arrequest'][2]);
        }



        return $out;
    }

    /**
     * @return mixed
     */
    function can_show_module()
    {
        return ($this->all_configs['oRole']->hasPrivilege('edit-users'));
    }

    /**
     * @inheritdoc
     */
    public function routing(Array $arrequest)
    {
        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'tariffs') {
            $tariffsUrl = Tariff::getURL($this->all_configs['configs']['api_url'],
                $this->all_configs['configs']['host']);
            Response::redirect($tariffsUrl);
        }
        parent::routing($arrequest);
    }

    /**
     * @param $post
     * @return array
     */
    private function getNewRequestsStatus($post)
    {
        $used = function($status) {
            return (bool) db()->query('SELECT count(*) FROM {crm_requests} WHERE status=?i', array($status))->el();
        };
        $status = array();
        if(!empty($post)) {
            $lastId = 0;
            foreach ($post['name'] as $id => $value) {
                $statusId = ($id === 'new')? $lastId + 1: $id;
                $lastId = max($lastId, $id);
                if((!isset($post['delete'][$id]) || $post['delete'][$id] != 'on' || $used($id)))  {
                    $status[$statusId] = array(
                        'name' => trim($value),
                        'active' => !(isset($post['close'][$id]) && $post['close'][$id] == 'on')
                    );
                }
            }
        }
        return $status;
    }
}

