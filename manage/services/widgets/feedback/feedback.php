<?php namespace services\widgets;

if (file_exists(__DIR__ . '/../../View.php')) {
    require __DIR__ . '/../../View.php';
}

require_once(ROOT_DIR . '/shop/access.class.php');

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
                    break;
                case 'send_sms':
                    if (empty($post['phone'])) {
                        throw new \Exception(l('Недопустимый номер телефона'));
                    }
                    $html = $this->sendSMS($post);
                    break;
                default:
            }
            if (empty($html)) {
                throw new \Exception(l('Ремонты не найдены'));
            }
            $response['state'] = true;
            $response['html'] = $html;
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
            $client = db()->query('SELECT * FROM {clients} WHERE client_code=?i ', array($post['code']))->row();
        }
        if (!empty($post['sms'])) {
            $client = db()->query('SELECT * FROM {clients} WHERE sms_code=? ', array($post['sms']))->row();
        }
        if (empty($client)) {
            throw new \Exception(l('Клиент не найден в базе'));
        }
        if (!$this->isRatingAccessible($client)) {
            throw new \Exception(l('Вы уже оставляли отзыв'));
        }
        $order = $this->getOrder($client);
        if (empty($order)) {
            throw new \Exception(l('Номер не найден в базе'));
        }
        $this->saveRatings($client, $order, $post);
        db()->query('UPDATE {clients} SET sms_code=0 WHERE id=?i', array($client['id']));
        return $this->view->renderFile('services/widgets/feedback/add');
    }

    /**
     * @param $post
     * @return string
     * @throws \Exception
     */
    private function sendSMS($post)
    {
        $access = new \access($this->all_configs, false);
        $phone = $access->is_phone($post['phone']);
        if(is_array($phone)) {
            $phone = $phone[0];
        }
        if (empty($phone)) {
            throw new \Exception(l('Номер не найден в базе'));
        }
        $client = $this->getClient($phone);

        if (empty($client) || empty($client['phone'])) {
            throw new \Exception(l('Указанный номер не закреплен ни за одним заказом'));
        }
        if (!$this->isRatingAccessible($client)) {
            throw new \Exception(l('С вашего номера уже оставлен отзыв'));
        }
        $code = mt_rand(10000, 99999);
        $result = send_sms($phone, l('Vash kod dlya otsiva') . ':' . $code);
        if (!$result['state']) {
            throw new \Exception(l('Проблемы с отправкой sms. Попробуйте повторить попытку позже.'));
        }
        db()->query('UPDATE {clients} SET sms_code=?i WHERE id = ?i', array($code, $client['id']))->ar();
        return $this->view->renderFile('services/widgets/feedback/wait_sms', array());
    }

    /**
     * @param $phone
     * @return array
     * @internal param $post
     */
    private function getClient($phone)
    {
        $access = new \access($this->all_configs, false);
        $client = $access->get_client(null, $phone);
        if (empty($client)) {
            $record = db()->query("SELECT * FROM {changes} WHERE work='update-order-phone' AND `change` like '%?e%' LIMIT 1",
                array($phone))->row();
            if (!empty($record)) {
                $client = db()->query("SELECT * FROM {clients} WHERE id in (SELECT user_id FROM {orders} WHERE id=?i)",
                    array($record['object_id']))->row();
            }
        }
        return $client;
    }

    /**
     * @param $client
     * @return bool
     */
    private function isRatingAccessible($client)
    {
        $order = $this->getOrder($client);
        $rating = db()->query('SELECT count(*) FROM {users_ratings}'
            . ' WHERE client_id=?i AND order_id=?i ', array(
            $client['id'],
            $order['id']
        ))->el();
        return $rating == 0;
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

    /**
     * @param $client
     * @param $order
     * @param $post
     * @throws \Exception
     */
    private function saveRatings($client, $order, $post)
    {
        if (!empty($order['manager'])) {
            $this->saveRating($order['manager'], $order['id'], $client['id'], $post['manager']);
            $this->recalculateRating($order['manager']);
        }
        if (!empty($order['engineer'])) {
            $this->saveRating($order['engineer'], $order['id'], $client['id'], $post['engineer']);
            $this->recalculateRating($order['engineer']);
        }
        if (!empty($order['accepter'])) {
            $this->saveRating($order['accepter'], $order['id'], $client['id'], $post['acceptor']);
            $this->recalculateRating($order['accepter']);
        }
        db()->query('INSERT INTO {feedback} (manager, acceptor, engineer, comment, client_id, order_id, created_at, updated_at)'
        .  ' VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP )',
            array(
                $post['manager'],
                $post['acceptor'],
                $post['engineer'],
                empty($post['comment']) ? '' : $post['comment'],
                $client['id'],
                $order['id'],
            ));
    }

    /**
     * @param $client
     * @return mixed
     */
    private function getOrder($client)
    {
        $order = db()->query('SELECT * FROM {orders} WHERE user_id=?i ORDER BY date_add DESC LIMIT 1',
            array($client['id']))->row();
        return $order;
    }

    /**
     * @param $userId
     * @param $orderId
     * @param $clientId
     * @param $rating
     */
    public function saveRating($userId, $orderId, $clientId, $rating)
    {
        db()->query('INSERT INTO {users_ratings} (user_id, order_id, client_id, rating, created_at, updated_at)'
            . ' VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP )', array(
            $userId,
            $orderId,
            $clientId,
            $rating,
        ))->ar();

    }

    /**
     * @param $userId
     */
    public function recalculateRating($userId)
    {
        $ratings = db()->query("SELECT user_id, "
            . " (SUM(ur.rating) / COUNT(ur.id)) as avg_rating "
            . " FROM {users_ratings} as ur "
            . " LEFT JOIN {users} as u ON u.id = ur.user_id "
            . " WHERE u.id = ?"
            . " GROUP BY user_id "
            . " ORDER BY avg_rating DESC", array($userId),
            'assoc');

        if (!empty($ratings)) {
            foreach ($ratings as $rating) {
                db()->query("UPDATE {users} SET rating=?i WHERE id=?i ",
                    array($rating['avg_rating'], $rating['user_id']));
            }
        }
    }
}
