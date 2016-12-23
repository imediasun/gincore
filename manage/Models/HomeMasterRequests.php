<?php
require_once __DIR__ . '/../Core/AModel.php';

/**
 * @property  MOrdersComments OrdersComments
 */
class MHomeMasterRequests extends AModel
{
    public $table = 'home_master_requests';
    public $uses = array(
        'OrdersComments'
    );

    /**
     * @param $orderId
     * @param $post
     */
    public function add($orderId, $post)
    {
        if (isset($post['home_master_request']) && $post['home_master_request'] == 1 && isset($post['home_master_date']) && isset($post['home_master_address'])) {
            $date = trim($post['home_master_date']);
            $address = trim($post['home_master_address']);
            if (!empty($date) && !empty($address)) {
                $this->insert(array(
                    'order_id' => $orderId,
                    'date' => $date,
                    'address' => $address
                ));
                $text = l('Вызов мастера на дом') . ":{$address}.\n";
                $text .= l('Время') . ":{$date}";
                $this->OrdersComments->addPrivate($orderId, $text);
            }
        }
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'order_id',
            'address',
            'date',
        );
    }
}
