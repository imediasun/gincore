<?php
require_once __DIR__ . '/../Core/AModel.php';

class MOrdersGoods extends AModel
{
    public $table = 'orders_goods';

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'user_id',
            'goods_id',
            'article',
            'date_add',
            'attachment',
            'title',
            'content',
            'price',
            'discount',
            'type',
            'count',
            'order_id',
            'secret_title',
            'url',
            'foreign_warehouse',
            'manager_id',
            'item_id',
            'last_item_id',
            'unbind_request',
            'warehouse_type',
        );
    }
}