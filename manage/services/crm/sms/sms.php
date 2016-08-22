<?php

namespace services\crm;

require_once __DIR__ . '/../../../Core/View.php';
require_once __DIR__ . '/../../../Core/Log.php';

class sms extends \service
{
    protected $view;
    private static $instance = null;

    private $sms_types;

    /**
     * @param $type
     * @return mixed
     */
    public function get_templates($type)
    {
        $type = $this->sms_types[$type];
        return $this->all_configs['db']->query("SELECT t.id, t.var, body, lang "
            . "FROM {sms_templates_strings} as ts "
            . "LEFT JOIN {sms_templates} as t ON t.id = ts.sms_templates_id "
            . "WHERE t.type = ?i AND ts.body != ''", array($type), 'assoc');
    }

    /**
     * @param $templates
     * @param $vars
     * @return mixed
     */
    public function replace_vars($templates, $vars)
    {
        $result = array();
        if (!empty($templates) && !empty($vars)) {
            foreach ($templates as $id => $template) {
                $result[$id] = $template;
                foreach ($vars as $var => $value) {
                    $result[$id]['body'] = str_replace($var, $value, $result[$id]['body']);
                }
            }
        }
        return $result;
    }

    /**
     * @param        $type
     * @param        $vars
     * @param string $default_template
     * @return mixed
     */
    public function get_templates_with_vars($type, $vars, $default_template = '')
    {
        $template = $this->get_templates($type);
        if (empty($template) && !empty($default_template)) {
            $template = array(
                array(
                    'body' => $default_template
                )
            );
        }
        return $this->replace_vars($template, $vars);
    }

    /**
     * @param $type
     * @return mixed
     */
    private function get_senders($type)
    {
        return $this->all_configs['db']->query("SELECT * "
            . "FROM {sms_senders} "
            . "WHERE type IN (?i,0,null)", array($type), 'assoc');
    }

    /**
     * @param $phone
     * @param $object
     * @param $type
     * @return string
     */
    public function get_form_btn($phone, $object, $type)
    {
        if ($this->already_sent($this->sms_types[$type], $object)) {
            return '<i class="fa fa-check"></i>';
        }
        return '
                <button id="sms_modal_btn" data-object="' . $object . '" data-phone="' . $phone . '" data-toggle="modal" data-target="#request_sms" class="btn btn-sm btn-small">
                    <i class="fa fa-envelope-o"></i>
                </button>
            ';
    }

    /**
     * @param $type
     * @return string
     */
    public function get_form($type)
    {
        $templates = $this->get_templates($type);
        $senders = $this->get_senders($this->sms_types[$type]);
        return $this->view->renderFile('services/crm/sms/send_sms_form', array(
            'sms_type' => $this->sms_types[$type],
            'type' => $type,
            'templates' => $templates,
            'senders' => $senders
        )) . $this->assets();
    }

    /**
     * @param $phone
     * @param $body
     * @param $type
     * @param $object_id
     * @return array
     */
    private function send_sms($phone, $body, $type, $object_id)
    {
        if ($this->already_sent($type, $object_id)) {
            return array('state' => false, 'msg' => l('Данное сообщение уже отправлено'));
        }
        $sender_id = isset($all_configs['settings']['turbosms-from']) ? trim($all_configs['settings']['turbosms-from']) : '';
        $send = send_sms($phone, $body, $sender_id);
        $this->log($phone, $body, $sender_id, $type, $object_id, $send['state'], $send['msg']);
        return $send;
    }

    /**
     * @param $phone
     * @param $body
     * @param $sender_id
     * @param $type
     * @param $object_id
     * @param $status
     * @param $message
     */
    private function log($phone, $body, $sender_id, $type, $object_id, $status, $message)
    {
        $this->all_configs['db']
            ->query("INSERT INTO {sms_log}(type,sender_id,object_id,phone,body,date,success,message) "
                . "VALUES (?i,?i,?i,?,?,NOW(),?i,?)",
                array($type, $sender_id, $object_id, $phone, $body, $status ? 1 : 0, $message));
    }

    /**
     * @param $type
     * @param $object_id
     * @return mixed
     */
    private function already_sent($type, $object_id)
    {
        return $this->all_configs['db']
            ->query("SELECT id FROM {sms_log} "
                . "WHERE type = ?i "
                . "AND object_id = ?i AND success = 1", array($type, $object_id), 'el');
    }

    /**
     * @param $data
     * @return array
     */
    public function ajax($data)
    {
        $response = array();
        switch ($data['action']) {
            // отправить смс
            case 'send_sms':
                $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
                $body = isset($_POST['body']) ? trim($_POST['body']) : '';
                $object_id = isset($_POST['object_id']) ? (int)$_POST['object_id'] : '';
                $type = isset($_POST['type']) ? (int)$_POST['type'] : '';
                $response['state'] = true;
                if (!$phone) {
                    $response['state'] = false;
                    $response['msg'] = l('Введите телефон');
                }
                if (!$body) {
                    $response['state'] = false;
                    $response['msg'] = l('Укажите текст смс');
                }
                if ($response['state']) {
                    $send = $this->send_sms($phone, $body, $type, $object_id);
                    if ($send['state']) {
                        $response['msg'] = l('Отправлено успешно');
                    } else {
                        $response['state'] = false;
                        $response['msg'] = $send['msg'];
                    }
                }
                break;
        }
        return $response;
    }

    /**
     * @return string
     */
    private function assets()
    {
        if (!isset($this->assets_added)) {
            $this->assets_added = true;
            return '
                <link rel="stylesheet" href="' . $this->all_configs['prefix'] . 'services/crm/sms/css/main.css">
                <script type="text/javascript" src="' . $this->all_configs['prefix'] . 'services/crm/sms/js/main.js"></script>
            ';
        }
        return '';
    }

    /**
     * @return null|sms
     */
    public static function getInstanse()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * sms constructor.
     */
    private function __construct()
    {
        global $all_configs;
        $this->all_configs = $all_configs;
        $this->view = new \View($this->all_configs);
        $this->sms_types = $this->all_configs['configs']['sms-types'];
    }
}
