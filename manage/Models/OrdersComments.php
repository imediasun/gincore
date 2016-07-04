<?php
require_once __DIR__ . '/../Core/AModel.php';

/**
 * Class MOrdersComments
 *
 */
class MOrdersComments extends AModel
{
    public $table = 'orders_comments';

    /**
     * @param $orderId
     * @param $commenterId
     * @param $field
     * @param $value
     * @return bool|int
     */
    public function addPublic($orderId, $commenterId, $field, $value)
    {
        $text = implode(' ', array(l('Изменил'), l($field), l('на'), $value));
        return $this->insert(array(
            'order_id' => $orderId,
            'user_id' => $commenterId,
            'text' => $text,
        ));
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'date_add',
            'text',
            'user_id',
            'auto',
            'order_id',
            'private',
        );
    }
}
