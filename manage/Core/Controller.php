<?php

require_once __DIR__ . '/Object.php';
require_once __DIR__ . '/FlashMessage.php';
require_once __DIR__ . '/View.php';
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/Response.php';
require_once __DIR__ . '/Url.php';
require_once __DIR__ . '/../Models/History.php';
require_once __DIR__ . '/../Tariff.php';


abstract class Controller extends Object
{
    public $count_on_page;
    protected $all_configs;
    protected $mod_submenu;
    /** @var View */
    protected $view;
    /** @var MHistory */
    protected $History;
    public $uses = array();

    abstract public function ajax();

    abstract public function check_post(Array $post);

    abstract public function can_show_module();

    abstract public function gencontent();

    /**
     * @param $arrequest
     * @return string
     */
    public function withoutCheckPermission($arrequest)
    {
        return '';
    }

    /**
     * @param array $arrequest
     * @return string
     */
    public function routing(Array $arrequest)
    {
        if ($this->isAjax($arrequest)) {
            return $this->ajax();
        }

        // если отправлена форма
        if (count($_POST) > 0) {
            return $this->check_post($_POST);
        }

        return $this->check_get($_GET);
    }

    /**
     * @param null $oRole
     * @return array
     */
    public static function get_submenu($oRole = null)
    {
        return array();
    }

    public function __construct(&$all_configs)
    {
        $this->all_configs = $all_configs;
        $this->mod_submenu = static::get_submenu($this->all_configs['oRole']);
        $this->count_on_page = count_on_page();
        $this->view = new View($all_configs);
        $this->session = Session::getInstance();
        $this->History = new MHistory();
        $this->applyUses();
    }

    /**
     * @return string
     */
    public function render()
    {
        $result = $this->withoutCheckPermission($this->all_configs['arrequest']);

        if (!$this->can_show_module()) {
            if ($this->isAjax($this->all_configs['arrequest'])) {
                Response::json(array('message' => l('У Вас не достаточно прав'), 'state' => false));
            } else {
                return $this->renderCanShowModuleError();
            }
        }

        if (empty($result)) {
            $result = $this->routing($this->all_configs['arrequest']);
        }

        if (empty($result)) {
            $result = $this->gencontent();
        }
        return $result;
    }

    /**
     * @param $arrequest
     * @return bool
     */
    public function isAjax($arrequest)
    {
        return isset($arrequest[1]) && $arrequest[1] == 'ajax';
    }

    /**
     * @return string
     */
    public function renderCanShowModuleError()
    {
        return '<div class="span3"></div>
                <div class="span9"><p  class="text-error">' . l('У Вас не достаточно прав') . '</p></div>';

    }

    /**
     * @param $get
     * @return string
     */
    public function check_get($get)
    {
        return '';
    }

    /**
     * @param $act
     * @param $mod_id
     * @return array
     */
    public function getAllChanges($act, $mod_id)
    {
        $data = array('state' => false);
        preg_match('/changes:(.+)/', $act, $arr);
        if (count($arr) == 2 && isset($arr[1])) {
            $data['state'] = true;
            $data['content'] = l('История изменений не найдена');

            $changes = $this->History->getChangesByModId(trim($arr[1]), $mod_id);
            if ($changes) {
                $data['content'] = $this->view->renderFile('inc_func/get_changes', array(
                    'changes' => $changes
                ));
            }

        }
        return $data;
    }

    /**
     * @param $act
     * @param $post
     * @param $mod_id
     * @return array
     */
    public function getChanges($act, $post, $mod_id)
    {
        $data = array('state' => false);
        preg_match('/changes:(.+)/', $act, $arr);
        if (count($arr) == 2 && isset($arr[1])) {
            $data['state'] = true;
            $data['content'] = l('История изменений не найдена');

            if (!empty($post['object_id'])) {
                $object_id = $post['object_id'];
            }
            if (!isset($object_id) && !empty($this->all_configs['arrequest'][2])) {
                $object_id = $this->all_configs['arrequest'][2];
            }
            if (!empty($object_id)) {
                $changes = $this->History->getChanges(trim($arr[1]), $mod_id, $object_id);
                if ($changes) {
                    $data['content'] = $this->view->renderFile('inc_func/get_changes', array(
                        'changes' => $changes
                    ));
                }
            }

        }
        return $data;
    }
}