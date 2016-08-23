<?php
require_once __DIR__ . '/../Core/AModel.php';

class MPurchaseInvoiceGoods extends AModel
{
    public $table = 'purchase_invoice_goods';

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'invoice_id',
            'good_id',
            'price',
            'quantity',
            'not_found',
        );
    }
}
