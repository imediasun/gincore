<?php

require_once __DIR__ . '/../../View.php';
// настройки
$modulename[132] = 'widgets';
$modulemenu[132] = l('Виджеты');  //карта сайта

$moduleactive[132] = !$ifauth['is_2'];

class widgets
{

    protected $all_configs;
    /** @var View */
    protected $view;
    private $lang;
    private $def_lang;
    private $langs;

    /**
     * widgets constructor.
     * @param $all_configs
     * @param $lang
     * @param $def_lang
     * @param $langs
     */
    function __construct($all_configs, $lang, $def_lang, $langs)
    {
        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->langs = $langs;
        $this->all_configs = $all_configs;
        $this->view = new View($all_configs);

        global $input_html, $ifauth;

        if ($ifauth['is_1']) {
            return false;
        }

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }

        $this->current = isset($this->all_configs['arrequest'][1]) ? $this->all_configs['arrequest'][1] : '';

        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

    /**
     * @return string
     */
    private function genmenu()
    {
        return $this->view->renderFile('widgets/genmenu', array(
            'current' => $this->current
        ));
    }

    /**
     * @return mixed|string
     */
    private function gencontent()
    {
        $widget = '';
        switch ($this->current) {
            case 'status':
                $title = l('Виджет «Статус заказа»');
                $widget = $this->current;
                break;
            case 'feedback':
                $title = l('Виджет «Отзывы о работе сотрудников»');
                $widget = $this->current;
                break;
            default:
                $title = l('Виджеты');
        }

        return $this->view->renderFile('widgets/gencontent', array(
            'title' => $title,
            'widget' => $widget
        ));
    }

    /**
     *
     */
    private function ajax()
    {
        $data = array(
            'state' => false
        );

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }
}
