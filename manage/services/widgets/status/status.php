<?php namespace services\widgets;

require_once __DIR__.'/../../../View.php';

class status extends \service{
    
    private static $instance = null;
    private $widgets = null;
    /** @var \View */
    private $view = null;

    /**
     * @return string
     */
    public function load_widget(){
        $loader = '';
        $loader .= $this->widgets->attach_css('status/css/main.css');
        $loader .= $this->widgets->attach_js('status/js/main.js');
        $html = $this->widget_html();
        $loader .= $this->widgets->add_html($html);
        return $loader;
    }

    /**
     * @return mixed
     */
    private function widget_html(){
        return $this->view->renderFile('services/widgets/status/widget', array(
            'widgets' => $this->widgets
        ));
    }

    /**
     * @param $post
     * @return array
     */
    public function ajax($post){
        $response = array(
            'state' => false
        );
        $action = isset($post['action']) ? trim($post['action']) : null;
        switch($action){
            case 'status_by_phone':
                $phone = !empty($post['phone']) ? trim($post['phone']) : null;
                if($phone){
                    $html = $this->status_by_phone($phone);
                    if($html){
                        $response['state'] = true;
                        $response['html'] = $html;
                    }else{
                        $response['msg'] = l('Ремонты не найдены');
                    }
                }else{
                    $response['msg'] = l('Укажите номер телефона');
                }
            break;
        }
        return $response;
    }

    /**
     * @param $phone
     * @return string
     */
    private function status_by_phone($phone){
        include_once $this->all_configs['sitepath'].'shop/access.class.php';
        $access = new \access($this->all_configs, false);
        $phone = $access->is_phone($phone);
        $html = '';
        if(!empty($phone[0])){
            $orders = db()->query(
                                'SELECT o.*, cg.title 
                                 FROM {orders} as o, {categories} as cg
                                 WHERE o.phone=? AND o.category_id=cg.id 
                                 ORDER BY o.date_add DESC', array($phone[0]))->assoc();
            if($orders) {
                foreach ($orders as $order) {
                    $status = isset($this->all_configs['configs']['order-status'][$order['status']]) 
                                ? htmlspecialchars($this->all_configs['configs']['order-status'][$order['status']]['name']) 
                                    : '';
                    $html .= '<div class="gcw_status_order">';
                    $html .= '<h2>'.l('Ремонт').' №' . $order['id'] . '</h2>';
                    $html .= '<p><b>'.l('Дата').'</b>: ' . date("d/m/Y", strtotime($order['date_add'])) . '</p>';
                    $html .= '<p><b>'.l('Статус').'</b>: ' . $status . '</p>';
                    $html .= '<p><b>'.l('Устройство').'</b>: ' . htmlspecialchars($order['title']) . '</p>';
                    $html .= '<p><b>'.l('Серийный номер').'</b>: ' . htmlspecialchars($order['serial']) . '</p>';

                    $comments = db()->query('SELECT * FROM {orders_comments} WHERE order_id=?i AND private=0 ORDER BY date_add DESC',
                        array($order['id']))->assoc();
                    if ($comments) {
                        $html .= '<table class="gcw_table gcw_table_stripped">'
                                .'<thead><tr><td><center>'.l('Дата').'</center></td>'
                                .'<td>'.l('Текущий статус ремонта').'</td></tr></thead><tbody>';
                        foreach ($comments as $comment) {
                            $html .= '<tr><td><center>' . date("d.m.Y<b\\r/>H:i", strtotime($comment['date_add'])) . '</center></td>';
                            $html .= '<td>' . htmlspecialchars(wordwrap($comment['text'], 25, " ", true)) . '</td></tr>';
                        }
                        $html .= '</tbody></table>';
                    }
                    $html .= '</div>';

                    // обратный звонок
    //                $html .= '<br /><h3 class="center">Недостаточно информации или остались вопросы?<br />Мы перезвоним Вам!</h3>';
    //                $phone = '+380 ' . mb_substr($order_id, 3, 2) . ' ' . mb_substr($order_id, 5, 3) . '-' . mb_substr($order_id, 8, 2) . '-' . mb_substr($order_id, 10, 2);
    //                $html .= content_form('{-form_1-}', array(1 => array('phone' => $phone, 'hidden' => $order['id'])));
    //                $html .= '<p class="center muted">* График работы инженерной с 11.00 до 18.00.<br>Ожидайте звонка Вашего специалиста в указаное время.</p>';
    //                $html .= '<br /><br />';
                }
            }
        }
        return $html;
    }

    /**
     * @return null|status
     * @throws \Exception
     */
    public static function getInstanse(){
        if(is_null(self::$instance)){
            self::$instance = new self();
            self::$instance->widgets = get_service('widgets');
            self::$instance->view = new \View();
        }
        return self::$instance;
    }

    /**
     * status constructor.
     */
    private function __construct(){}
}
