<?php
require_once __DIR__ . '/../Core/AModel.php';

class MContractorsSuppliersOrders extends AModel
{
    public $table = 'contractors_suppliers_orders';

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'price',
            'sum_paid',
            'count',
            'count_come',
            'date_add',
            'user_id_accept',
            'date_come',
            'date_wait',
            'date_paid',
            'supplier',
            'its_warehouse',
            'goods_id',
            'user_id',
            'parent_id',
            'group_parent_id',
            'number',
            'confirm',
            'isset_goods',
            'comment',
            'count_debit',
            'wh_id',
            'location_id',
            'unavailable',
            'avail',
            'num',
            'date_check',
            'warehouse_type',

        );
    }
}
