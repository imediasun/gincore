<?php

require_once __DIR__ . '/abstract_template.php';

abstract class AbstractOrdersTemplate extends AbstractTemplate
{

    /**
     * @param $order
     * @param $arr
     * @return mixed
     */
    protected function addUsersFieldsValues($order, $arr)
    {
        $usersFieldsValues = $this->getUsersFieldsValues($order['id']);
        if (!empty($usersFieldsValues)) {
            foreach ($usersFieldsValues as $name => $field) {
                if (!empty($field['value'])) {
                    $arr[$name] = array(
                        'value' => h($field['value']),
                        'name' => h($name)
                    );
                }
            }
        }
        return $arr;
    }

    /**
     * @param $orderId
     * @return mixed
     */
    private function getUsersFieldsValues($orderId)
    {
        return db()->query('
            SELECT ouf.*, uf.*, uf.id as uf_id, ouf.id as ouf_id 
            FROM {users_fields} uf 
            LEFT JOIN {orders_users_fields} ouf ON uf.id=ouf.users_field_id AND  ouf.order_id=? 
            WHERE uf.deleted=0',
            array($orderId))->assoc('name');
    }
}
