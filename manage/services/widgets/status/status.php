<?php namespace services\widgets;

require_once __DIR__ . '/../../../Core/View.php';

class status extends \service
{
    private static $instance = null;
    private $widgets = null;
    /** @var \View */
    private $view = null;

    /**
     * @return string
     */
    public function load_widget()
    {
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
    private function widget_html()
    {
        global $all_configs;
        return $this->view->renderFile('services/widgets/status/widget', array(
            'widgets' => $this->widgets,
            'bg_color' => $all_configs['settings']['widget-order-state-bg-color'],
            'fg_color' => $all_configs['settings']['widget-order-state-fg-color'],
        ));
    }

    /**
     * @param $post
     * @return array
     */
    public function ajax($post)
    {
        $response = array(
            'state' => false
        );
        $action = isset($post['action']) ? trim($post['action']) : null;
        switch ($action) {
            case 'status_by_phone':
                $phone = !empty($post['phone']) ? trim($post['phone']) : null;
                if ($phone) {
                    $html = $this->status_by_phone($phone);
                    if ($html) {
                        $response['state'] = true;
                        $response['html'] = $html;
                    } else {
                        $response['msg'] = l('Ремонты не найдены');
                    }
                } else {
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
    private function status_by_phone($phone)
    {
        include_once $this->all_configs['sitepath'] . 'shop/access.class.php';
        $access = new \access($this->all_configs, false);
        $phone = $access->is_phone($phone);
        if (!empty($phone[0])) {
            $orders = db()->query(
                'SELECT o.*, cg.title 
                 FROM {orders} as o, {categories} as cg
                 WHERE o.phone=? AND o.category_id=cg.id 
                 ORDER BY o.date_add DESC', array($phone[0]))->assoc();
            if ($orders) {
                foreach ($orders as &$order) {
                    $order['comments'] = db()->query('SELECT * FROM {orders_comments} WHERE order_id=?i AND private=0 ORDER BY date_add DESC',
                        array($order['id']))->assoc();
                }
            }
        }
        return $this->view->renderFile('services/widgets/status/by_phone', array(
            'orders' => isset($orders) ? $orders : array()
        ));
    }

    /**
     * @return null|status
     * @throws \Exception
     */
    public static function getInstanse()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->widgets = get_service('widgets');
            self::$instance->view = new \View();
        }
        return self::$instance;
    }

    /**
     * status constructor.
     */
    private function __construct()
    {
    }
}
