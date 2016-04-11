<?php

require_once __DIR__ . '/../../View.php';
require_once __DIR__ . '/../../Tariff.php';

// настройки
$modulename[110] = 'settings';
$modulemenu[110] = l('sets_modulemenu');  //карта сайта

$moduleactive[110] = !$ifauth['is_2'];

class settings
{

    protected $all_configs;
    /** @var View */
    protected $view;

    /**
     * settings constructor.
     * @param $all_configs
     */
    function __construct($all_configs)
    {
        global $input_html, $ifauth;

        $this->all_configs = &$all_configs;
        $this->view = new View($all_configs);

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }

        if ($ifauth['is_2']) {
            return false;
        }

        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

    /**
     * @return string
     */
    private function genmenu()
    {
        $sqls = $this->all_configs['db']->query("SELECT * FROM {settings} WHERE `ro` = 0 ORDER BY `title`")->assoc();

        return $this->view->renderFile('settings/genmenu', array(
            'sqls' => $sqls
        ));
    }

    /**
     * @return mixed|string
     */
    private function gencontent()
    {
        $id = isset($_POST['id']) ? $_POST['id'] : '';
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $value = isset($_POST['value']) ? $_POST['value'] : '';
        $out = '';

        if (!isset($this->all_configs['arrequest'][1])) {
            $out = l('sets_description');
        }

###############################################################################
        if (isset($this->all_configs['arrequest'][1]) && is_numeric($this->all_configs['arrequest'][1])) {
            $pp = $this->all_configs['db']->query("SELECT * FROM {settings} WHERE id = ?i AND `ro` = 0",
                array($this->all_configs['arrequest'][1]), 'row');

            $out = $this->view->renderFile('settings/gencontent', array(
                'pp' => $pp,
                'orderWarranties' => isset($this->all_configs['settings']['order_warranties']) ? explode(',',
                    $this->all_configs['settings']['order_warranties']) : array(),
            ));

            if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'update') {

                $sql = $this->all_configs['db']->query("UPDATE {settings} SET value=?
                             WHERE id=?i AND ro=0 LIMIT 1", array($value, $this->all_configs['arrequest'][1]), 'ar');

                header('Location: ' . $this->all_configs['prefix'] . 'settings/save/' . $this->all_configs['arrequest'][1]);
                exit;
            }//update
        }
################################################################################

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'add') {
            if (isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'ok' && isset($_POST['name']) && isset($_POST['value']) && isset($_POST['title'])) {
                $sql = $this->all_configs['db']->query("INSERT INTO {settings}(description, name, value, title, ro) 
                            VALUES(?, ?, ?, ?, ?i)", array(
                    $_POST['description'],
                    $_POST['name'],
                    $_POST['value'],
                    $_POST['title'],
                    isset($_POST['ro']) ? 1 : 0
                ));
                header('Location: ' . $this->all_configs['prefix'] . 'settings');
                exit;
            } else {
                $out = $this->view->renderFile('settings/add_new_params_form');
            }
        }

################################################################################
        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'save') {
            $out = l('sets_update_success') . ' <a href="' . $this->all_configs['prefix'] . 'settings/' . $this->all_configs['arrequest'][2] . '">' . l('continue') . '</a>';
        }
###############################################################################


        return $out;
    }

    /**
     * @throws Exception
     */
    private function ajax()
    {
        $data = array(
            'state' => false
        );

        if (!empty($_GET['act']) && $_GET['act'] == 'show-tariff') {

            $tariff = Tariff::load($this->all_configs['configs']['api_url'], $this->all_configs['configs']['host']);
            $usersCount = db()->query('SELECT count(*) FROM {users} WHERE deleted=0 AND blocked_by_tariff=0')->el();
            $orderCount = db()->query('SELECT count(*) FROM {orders} WHERE date_add > ?',
                array($tariff['start']))->el();
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
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

}

