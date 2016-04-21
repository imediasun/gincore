<?php

require_once __DIR__ . '/FlashMessage.php';
require_once __DIR__ . '/View.php';
require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/Response.php';

abstract class Controller
{
    public $count_on_page;
    protected $all_configs;
    protected $mod_submenu;
    /** @var View */
    protected $view;

    abstract public function ajax();

    abstract public function check_post(Array $post);

    abstract public function can_show_module();

    abstract public function gencontent();

    /**
     * @param array $arrequest
     */
    public function routing(Array $arrequest)
    {
        if ($this->isAjax($arrequest)) {
            $this->ajax();
        }

        // если отправлена форма
        if (count($_POST) > 0) {
            $this->check_post($_POST);
        }
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

        global $input_html;

        if ($this->can_show_module() == false) {
            if ($this->isAjax($this->all_configs['arrequest'])) {
                Response::json(array('message' => l('Нет прав'), 'state' => false));
            } else {
                return $input_html['mcontent'] = $this->renderCanShowModuleError();
            }
        }


        $this->routing($this->all_configs['arrequest']);

        if (empty($input_html['mcontent'])) {
            $input_html['mcontent'] = $this->gencontent();
        }
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
     * @return string
     */
    public function getUserId()
    {
        return isset($_SESSION['id']) ? $_SESSION['id'] : '';
    }
}