<?php
require_once __DIR__ . '/../Core/AModel.php';

define('ORDER_COMMENT_PUBLIC', 0);
define('ORDER_COMMENT_PRIVATE', 1);
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
            'private' => ORDER_COMMENT_PUBLIC
        ));
    }

    /**
     * @param $orderId
     * @param $text
     * @return bool|int
     */
    public function addPrivate($orderId, $text)
    {
        return $this->insert(array(
            'order_id' => $orderId,
            'user_id' => $this->getUserId(),
            'private' => ORDER_COMMENT_PRIVATE,
            'text' => $text,
        ));
    }

    /**
     * @param     $orderId
     * @param int $private
     * @return array
     */
    protected function getComments($orderId, $private = ORDER_COMMENT_PUBLIC)
    {
        return $this->query('
            SELECT oc.date_add, oc.text, u.fio, u.phone, u.login, u.email, oc.id
            FROM ?t as oc 
            LEFT JOIN {users} as u ON u.id=oc.user_id
            WHERE oc.order_id=?i AND oc.private=?i ORDER BY oc.date_add DESC', array($this->table, $orderId, $private))->assoc();
    }
    /**
     * @param $orderId
     * @return array
     */
    public function getPublic($orderId)
    {
        return $this->getComments($orderId, ORDER_COMMENT_PUBLIC);
    }

    /**
     * @param $orderId
     * @return array
     */
    public function getPrivate($orderId)
    {
        return $this->getComments($orderId, ORDER_COMMENT_PRIVATE);
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
