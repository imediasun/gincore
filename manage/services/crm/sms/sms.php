<?php namespace services\crm;

class sms extends \service{
    
    private static $instance = null;

    private $sms_types = array(
        'requests' => 1
    );
    
    private function get_templates($type){
        $tempaltes = $this->all_configs['db']->query("SELECT t.id, body, lang "
                                                    ."FROM {sms_templates_strings} as ts "
                                                    ."LEFT JOIN {sms_templates} as t ON t.id = ts.sms_templates_id "
                                                    ."WHERE t.type = ?i AND ts.body != ''", array($type), 'assoc');
        return $tempaltes;
    }
    
    private function get_seneders($type){
        $tempaltes = $this->all_configs['db']->query("SELECT * "
                                                    ."FROM {sms_senders} "
                                                    ."WHERE type IN (?i,0,null)", array($type), 'assoc');
        return $tempaltes;
    }

    public function get_form_btn($phone, $object, $type){
        if(!$this->already_sent($this->sms_types[$type], $object)){
            return '
                <button id="sms_modal_btn" data-object="'.$object.'" data-phone="'.$phone.'" data-toggle="modal" data-target="#request_sms" class="btn btn-sm btn-small">
                    <i class="fa fa-envelope-o"></i>
                </button>
            ';
        }else{
            return '<i class="fa fa-check"></i>';
        }
    }
    public function get_form($type){
        $templates = $this->get_templates($this->sms_types[$type]);
        $templates_options = '<option disabled selected>Выберите</option>';
        foreach($templates as $template){
            $templates_options .= '<option data-body="'.htmlspecialchars($template['body']).'" value="'.$template['id'].'">'.htmlspecialchars($template['body']).' ('.$template['lang'].')</option>';
        }
        $senders = $this->get_seneders($this->sms_types[$type]);
        $senders_options = '<option disabled selected>Выберите</option>';
        foreach($senders as $sender){
            $senders_options .= '<option value="'.$sender['id'].'">'.htmlspecialchars($sender['sender']).'</option>';
        }
        return '
            <div id="request_sms" class="modal fade">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <form data-callback="send_sms_callback" method="post" class="ajax_form" action="'.$this->all_configs['prefix'].'services/ajax.php">
                            <input type="hidden" name="service" value="crm/sms">
                            <input type="hidden" name="action" value="send_sms">
                            <input type="hidden" name="type" value="'.$this->sms_types[$type].'">
                            <input type="hidden" name="object_id" id="sms_object" value="">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal">×</button>
                                <h3>Отправить смс</h3>
                            </div>
                            <div class="modal-body">
                                <div class="form-group">
                                    <label>Отправитель</label>
                                    <select class="form-control" name="sender_id" id="sms_sender_select">
                                        '.$senders_options.'
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Телефон</label>
                                    <input id="sms_phone" type="text" class="form-control" name="phone">
                                </div>
                                <div class="form-group">
                                    <label>Шаблон</label>
                                    <select class="form-control" id="sms_template_select" name="template_id">
                                        '.$templates_options.'
                                    </select>
                                    <textarea id="sms_body" name="body" style="min-width:80%" class="form-control" rows="5"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Отправить</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        '.$this->assets();
    }
    
    private function send_sms($phone, $body, $sender_id, $type, $object_id){
        if(!$this->already_sent($type, $object_id)){
            $sender = $this->all_configs['db']->query("SELECT sender FROM {sms_senders} "
                                                     ."WHERE id = ?i", array($sender_id), 'el');
            $send = send_sms($phone, $body, $sender);
            $this->log($phone, $body, $sender_id, $type, $object_id, $send['state'], $send['msg']);
            return $send;
        }else{
            return array('state' => false, 'msg' => 'Данное сообщение уже отправлено');
        }
    }
    
    private function log($phone, $body, $sender_id, $type, $object_id, $status, $message){
        $this->all_configs['db']
                ->query("INSERT INTO {sms_log}(type,sender_id,object_id,phone,body,date,success,message) "
                       ."VALUES (?i,?i,?i,?,?,NOW(),?i,?)", array($type,$sender_id,$object_id,$phone,$body,$status?1:0,$message));
    }
    
    private function already_sent($type, $object_id){
        return $this->all_configs['db']
                        ->query("SELECT id FROM {sms_log} "
                               ."WHERE type = ?i "
                                . "AND object_id = ?i AND success = 1", array($type, $object_id), 'el');
    }
    
    public function ajax($data){
        $operator_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $response = array();
        switch($data['action']){
            // отправить смс
            case 'send_sms':
                $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
                $sender_id = isset($_POST['sender_id']) ? (int)$_POST['sender_id'] : '';
                $body = isset($_POST['body']) ? trim($_POST['body']) : '';
                $object_id = isset($_POST['object_id']) ? (int)$_POST['object_id'] : '';
                $type = isset($_POST['type']) ? (int)$_POST['type'] : '';
                $response['state'] = true;
                if(!$phone){
                    $response['state'] = false;
                    $response['msg'] = 'Введите телефон';
                }
                if(!$sender_id){
                    $response['state'] = false;
                    $response['msg'] = 'Выберите отправителя';
                }
                if(!$body){
                    $response['state'] = false;
                    $response['msg'] = 'Укажите текст смс';
                }
                if($response['state']){
                    $send = $this->send_sms($phone, $body, $sender_id, $type, $object_id);
                    if($send['state']){
                        $response['msg'] = 'Отправлено успешно';
                    }else{
                        $response['state'] = false;
                        $response['msg'] = $send['msg'];
                    }
                }
            break;
        }
        return $response;
    }
    
    private function assets(){
        if(!isset($this->assets_added)){
            $this->assets_added = true;
            return '
                <link rel="stylesheet" href="'.$this->all_configs['prefix'].'services/crm/sms/css/main.css">
                <script type="text/javascript" src="'.$this->all_configs['prefix'].'services/crm/sms/js/main.js"></script>
            ';
        }
        return '';
    }
    
    public static function getInstanse(){
        if(is_null(self::$instance)){
            self::$instance = new self();
        }
        return self::$instance;
    }
    private function __construct(){}
}
