<?php namespace services\widgets;

if (file_exists(__DIR__ . '/../../View.php')) {
    require __DIR__ . '/../../View.php';
}


class feedback extends \service
{

    private static $instance = null;
    /** @var \View */
    private $view = null;
    private $widgets = null;

    /**
     * @return string
     */
    public function load_widget()
    {
        $loader = '';
        $loader .= $this->widgets->attach_css('feedback/css/main.css');
        $loader .= $this->widgets->attach_js('feedback/js/main.js');
        $html = $this->widget_html();
        $loader .= $this->widgets->add_html($html);
        return $loader;
    }

    /**
     * @return string
     */
    private function widget_html()
    {
        return $this->view->renderFile('services/widgets/feedback/widget', array(
            'widgets' => $this->widgets
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
        try {

            switch ($action) {
                case 'add':
                    if (empty($post['code']) && empty($post['sms'])) {
                        throw new \Exception(l('Форма заполнена не корректно. Введите код клиента или код из sms'));
                    }
                    $html = $this->add($post);
                    if (empty($html)) {
                        throw new \Exception(l('Ремонты не найдены'));
                    }
                    $response['state'] = true;
                    $response['html'] = $html;
                    break;
                case 'send_sms':
                    if (empty($post['phone'])) {
                        throw new \Exception(l('Недопустимый номер телефона'));
                    }
                    $html = $this->sendSMS($post);
                    if (empty($html)) {
                        throw new \Exception(l('Ремонты не найдены'));
                    }
                    $response['state'] = true;
                    $response['html'] = $html;
                    break;
                default:
            }
        } catch (\Exception $e) {
            $response['msg'] = $e->getMessage();

        }
        return $response;
    }

    /**
     * @param $post
     * @return string
     * @throws \Exception
     */
    private function add($post)
    {
        if (!empty($post['code'])) {
            $client = db()->query('SELECT * FROM {clients} WHERE client_code=? ', array($post['code']))->row();
        }
        if (!empty($post['sms'])) {
            $client = db()->query('SELECT * FROM {clients} WHERE sms_code=? ', array($post['sms']))->row();
        }
        if (empty($client)) {
            throw new \Exception(l('Клиент не найден в базе'));
        }
        return $this->view->renderFile('services/widgets/feedback/add');
    }

    /**
     * @param $post
     * @return string
     * @throws \Exception
     */
    private function sendSMS($post)
    {
        $client = db()->query('SELECT * FROM {clients} WHERE phone LIKE "%?li%"', array($post['phone']))->row();
        if (empty($client)) {
            throw new \Exception(l('Номер не найден в базе'));
        }
        $code = mt_rand(10000, 99999);
        $result = send_sms($client['phone'], l('Vash kod dlya otsiva') . ':' . $code);
        if (!$result['state']) {
            throw new \Exception(l('Проблемы с отправкой sms. Попробуйте повторить попытку позже.'));
        }
        db()->query('UPDATE {clients} SET sms_code=?l WHERE id = ?i', array($code, $client['id']));
        return $this->view->renderFile('services/widgets/feedback/wait_sms', array());
    }

    protected function findUser($phone)
    {

    }

    /**
     * @return null|feedback
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
     * feedback constructor.
     */
    private function __construct()
    {
    }
}
