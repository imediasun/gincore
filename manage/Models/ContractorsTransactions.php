<?php
require_once __DIR__ . '/../Core/AModel.php';

class MContractorsTransactions extends AModel
{
    public $table = 'contractors_transactions';

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'transaction_type',
            'type',
            'cashboxes_currency_id_from',
            'cashboxes_currency_id_to',
            'value_from',
            'value_to',
            'contractor_category_link',
            'date_transaction',
            'date_add',
            'user_id',
            'comment',
            'client_order_id',
            'supplier_order_id',
            'transaction_id',
            'item_id',
            'goods_id',
        );
    }
}
