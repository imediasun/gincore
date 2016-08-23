<?php
require_once __DIR__ . '/../Core/AModel.php';

class MPurchaseInvoiceGoods extends AModel
{
    public $table = 'purchase_invoice_goods';

    /**
     * @param $id
     * @param $items
     */
    public function addItems($id, $items)
    {
        if(!empty($items)) {
            foreach ($items as $item) {
                $item['invoice_id'] = $id;
                $this->insert($item);
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
            'invoice_id',
            'good_id',
            'price',
            'quantity',
            'not_found',
        );
    }
}
