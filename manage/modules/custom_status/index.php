<?php

require_once __DIR__ . '/../../Core/Controller.php';
// настройки
$modulename[240] = 'custom_status';
$modulemenu[240] = l('Пользовательские статусы');  //карта сайта

global $all_configs;
$moduleactive[240] = $all_configs['oRole']->hasPrivilege('edit-users');

/**
 * @property  MStatus Status
 */
class custom_status extends Controller
{
    protected $current;
    private $lang;
    private $def_lang;
    private $langs;

    public $uses = array(
        'Status'
    );

    /**
     * widgets constructor.
     * @param $all_configs
     * @param $lang
     * @param $def_lang
     * @param $langs
     */
    public function __construct(&$all_configs, $lang, $def_lang, $langs)
    {
        parent::__construct($all_configs);
        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->langs = $langs;
        $this->current = isset($this->all_configs['arrequest'][1]) ? $this->all_configs['arrequest'][1] : '';
    }

    /**
     * @return string
     */
    public function render()
    {
        global $input_html;
        $result = parent::render();
        $input_html['mmenu'] = $this->genmenu();
        return $result;
    }

    /**
     * @return string
     */
    private function genmenu()
    {
        return $this->view->renderFile('custom_status/genmenu', array(
            'current' => $this->current
        ));
    }

    /**
     * @return mixed|string
     */
    public function gencontent()
    {

        return $this->view->renderFile('custom_status/gencontent', array(
            'title' => l('Пользовательские статусы'),
            'statuses' => $this->Status->getAll(),
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

        Response::json($data);
    }

    /**
     * @param $post
     * @return mixed|string
     */
    public function check_post(Array $post)
    {
        if (isset($post['status-form'])) {
            $value = isset($post['bg-color']) ? $post['bg-color'] : array();
            $this->saveSetting('widget-order-state-bg-color', $value, lq('Цвет фона виджета статуса заказов'),
                'Цвет фона виджета статуса заказов', 1);
            $value = isset($post['fg-color']) ? $post['fg-color'] : array();
            $this->saveSetting('widget-order-state-fg-color', $value, lq('Цвет текста виджета статуса заказов'),
                lq('Цвет текста виджета статуса заказов'), 1);
        }
        Response::redirect(Response::referrer());
    }

    /**
     * @return bool
     */
    public function can_show_module()
    {
        return ($this->all_configs['oRole']->hasPrivilege('edit-users'));
    }
}
