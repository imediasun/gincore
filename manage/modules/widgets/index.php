<?php

require_once __DIR__ . '/../../View.php';
require_once __DIR__ . '/../../FlashMessage.php';
require_once __DIR__ . '/../../Response.php';
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
        // если отправлена форма
        if (count($_POST) > 0) {
            $this->check_post($_POST);
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
        $sendSms = null;
        $host = null;
        switch ($this->current) {
            case 'status':
                $title = l('Виджет «Статус заказа»');
                $widget = $this->current;
                break;
            case 'feedback':
                $title = l('Виджет «Отзывы о работе сотрудников»');
                $sendSms = db()->query("SELECT value FROM {settings} WHERE name='order-send-sms-with-client-code'")->el();
                $host = db()->query("SELECT value FROM {settings} WHERE name='site-for-add-rating'")->el();
                $widget = $this->current;
                break;
            default:
                $title = l('Виджеты');
        }

        return $this->view->renderFile('widgets/gencontent', array(
            'title' => $title,
            'widget' => $widget,
            'sendSms' => $sendSms,
            'host' => $host
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

    /**
     * @param $post
     * @return mixed|string
     */
    private function check_post($post)
    {
        if (isset($post['send_sms'])) {
            $config = db()->query("SELECT count(*) FROM {settings} WHERE name='order-send-sms-with-client-code'")->el();
            if (empty($config)) {
                db()->query("INSERT INTO {settings} (name, value, title, description) VALUES (?, ?, ?, ?)", array(
                    'order-send-sms-with-client-code',
                    $post['send_sms'],
                    l('Отправлять клиентам смс с кодом'),
                    l('Отправлять клиентам смс с кодом'),
                ));
            } else {
                db()->query("UPDATE {settings} SET value=?  WHERE  name='order-send-sms-with-client-code'", array(
                    $post['send_sms'],
                ));
            }
        } else {
            db()->query("DELETE FROM {settings}  WHERE name='order-send-sms-with-client-code'")->ar();
        }
        if (isset($post['host'])) {
            $config = db()->query("SELECT count(*) FROM {settings} WHERE name='site-for-add-rating'")->el();
            if (empty($config)) {
                db()->query("INSERT INTO {settings} (name, value, title, description) VALUES (?, ?, ?, ?)", array(
                    'site-for-add-rating',
                    $post['host'],
                    l('Сайт на котором установлен виджет (будет отправляться в смс клиенту)'),
                    l('Сайт на котором установлен виджет (будет отправляться в смс клиенту)'),
                ));
            } else {
                db()->query("UPDATE {settings} SET value=?  WHERE  name='site-for-add-rating'", array(
                    $post['host'],
                ));
            }
            FlashMessage::set(l('Настройки сохранены'), FlashMessage::SUCCESS);
        }
        Response::redirect(Response::referrer());
    }
}
