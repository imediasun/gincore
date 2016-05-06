<?php
require_once __DIR__ . '/../Core/AModel.php';

class MOrdersSuppliersClients extends AModel
{
    public $table = 'orders_suppliers_clients';

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'client_order_id',
            'supplier_order_id',
            'goods_id',
            'date_add',
            'order_goods_id',

        );
    }
}
