<?php

require_once __DIR__ . '/../../Core/Controller.php';
// настройки
$modulename[132] = 'widgets';
$modulemenu[132] = l('Виджеты');  //карта сайта

global $all_configs;
$moduleactive[132] = $all_configs['oRole']->hasPrivilege('edit-users');

/**
 * @property  MSettings Settings
 */
class widgets extends Controller
{
    protected $current;
    private $lang;
    private $def_lang;
    private $langs;

    public $uses = array(
        'Settings'
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
        return $this->view->renderFile('widgets/genmenu', array(
            'current' => $this->current
        ));
    }

    /**
     * @return mixed|string
     */
    public function gencontent()
    {
        $widget = '';
        $sendSms = null;
        $host = null;
        $sendEmail = null;
        $bg_color = '';
        $fg_color = '';
        switch ($this->current) {
            case 'status':
                $title = l('Виджет «Статус заказа»');
                $bg_color = $this->Settings->getByName('widget-order-state-bg-color');
                $fg_color = $this->Settings->getByName('widget-order-state-fg-color');
                $widget = $this->current;
                break;
            case 'feedback':
                $title = l('Виджет «Отзывы о работе сотрудников»');
                $sendSms = $this->Settings->getByName('order-send-sms-with-client-code');
                $host = $this->Settings->getByName('site-for-add-rating');
                $sendEmail = $this->Settings->getByName('email-to-receive-new-comments');
                $bg_color = $this->Settings->getByName('widget-order-feedback-bg-color');
                $fg_color = $this->Settings->getByName('widget-order-feedback-fg-color');
                $widget = $this->current;
                break;
            default:
                $title = l('Виджеты');
        }

        return $this->view->renderFile('widgets/gencontent', array(
            'title' => $title,
            'widget' => $widget,
            'sendSms' => $sendSms,
            'sendEmail' => $sendEmail,
            'host' => $host,
            'bg_color' => $bg_color,
            'fg_color' => $fg_color,
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
        if (isset($post['feedback-form'])) {
            if (isset($post['send_sms'])) {
                $config = db()->query("SELECT count(*) FROM {settings} WHERE name='order-send-sms-with-client-code'")->el();
                if (empty($config)) {
                    $this->Settings->insert(array(
                        'name' => 'order-send-sms-with-client-code',
                        'value' => $post['send_sms'],
                        'title' => lq('Отправлять клиентам смс с кодом'),
                        'description' => lq('Отправлять клиентам смс с кодом'),
                    ));
                } else {
                    $this->Settings->update(array('value' => $post['send_sms']),
                        array('name' => 'order-send-sms-with-client-code'));
                }
            } else {
                $this->Settings->deleteAll(array('name' => 'order-send-sms-with-client-code'));
            }
            if (isset($post['host'])) {
                $config = db()->query("SELECT count(*) FROM {settings} WHERE name='site-for-add-rating'")->el();
                if (empty($config)) {
                    $this->Settings->insert(array(
                        'name' => 'site-for-add-rating',
                        'value' => $post['host'],
                        'title' => lq('Сайт на котором установлен виджет (будет отправляться в смс клиенту)'),
                        'description' => lq('Сайт на котором установлен виджет (будет отправляться в смс клиенту)'),
                    ));
                } else {
                    $this->Settings->update(array('value' => $post['host']),
                        array('name' => 'site-for-add-rating'));
                }
            }
            if (isset($post['send_email'])) {
                $config = db()->query("SELECT count(*) FROM {settings} WHERE name='email-to-receive-new-comments'")->el();
                if (empty($config)) {
                    $this->Settings->insert(array(
                        'name' => 'email-to-receive-new-comments',
                        'value' => $post['send_email'],
                        'title' => lq('Уведомлять о новых отзывах на почту'),
                        'description' => lq('Уведомлять о новых отзывах на почту'),
                    ));
                } else {
                    $this->Settings->update(array('value' => $post['send_email']),
                        array('name' => 'email-to-receive-new-comments'));
                }
            } else {
                $this->Settings->deleteAll(array('name' => 'email-to-receive-new-comments'));
            }
            $value = isset($post['bg-color']) ? $post['bg-color'] : array();
            $this->saveSetting('widget-order-feedback-bg-color', $value, lq('Цвет фона виджета отзывов о работе сервиса'),
                'Цвет фона виджета отзывов о работе сервиса');
            $value = isset($post['fg-color']) ? $post['fg-color'] : array();
            $this->saveSetting('widget-order-feedback-fg-color', $value, lq('Цвет текста виджета отзывов о работе сервиса'),
                lq('Цвет текста виджета отзывов о работе сервиса'));
            FlashMessage::set(lq('Настройки сохранены'), FlashMessage::SUCCESS);
        }
        if (isset($post['status-form'])) {
            $value = isset($post['bg-color']) ? $post['bg-color'] : array();
            $this->saveSetting('widget-order-state-bg-color', $value, lq('Цвет фона виджета статуса заказов'),
                'Цвет фона виджета статуса заказов');
            $value = isset($post['fg-color']) ? $post['fg-color'] : array();
            $this->saveSetting('widget-order-state-fg-color', $value, lq('Цвет текста виджета статуса заказов'),
                lq('Цвет текста виджета статуса заказов'));
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

    /**
     * @param $name
     * @param $value
     * @param $title
     * @param $description
     */
    private function saveSetting($name, $value, $title, $description)
    {
        if(empty($name)) {
            return;
        }
        if (!empty($value)) {
            $config = db()->query("SELECT count(*) FROM {settings} WHERE name=?", array($name))->el();
            if (empty($config)) {
                $this->Settings->insert(array(
                    'name' => $name,
                    'value' => $value,
                    'title' => lq($title),
                    'description' => lq($description),
                ));
            } else {
                $this->Settings->update(array('value' => $value),
                    array('name' => $name));
            }
        } else {
            $this->Settings->deleteAll(array('name' => $name));
        }
    }
}
