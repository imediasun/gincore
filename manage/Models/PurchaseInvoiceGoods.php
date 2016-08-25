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
        if (!empty($items)) {
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

    /**
     * @param $invoiceId
     * @param $items
     */
    public function updateItems($invoiceId, $items)
    {
        $goods = $this->query('SELECT * FROM ?t WHERE invoice_ic=?i', array($this->table, $invoiceId))->assoc('id');
        if (!empty($goods)) {
            foreach ($items as $id => $item) {
                $update = array();
                if (!array_key_exists($id, $goods)) {
                    continue;
                }
                if($goods[$id]['good_id'] != $item['good_id']) {
                    $update['good_id'] = $item['good_id'];
                }
                if($goods[$id]['price']/100 != $item['price']) {
                    $update['price'] = $item['price'] * 100;
                }
                if($goods[$id]['quantity'] != $item['quantity']) {
                    $update['quantity'] = $item['quantity'];
                }
                if($goods[$id]['not_found'] != $item['not_found']) {
                    $update['not_found'] = $item['not_found'];
                }
                if (!empty($update)) {
                    $this->update($update, array('id' => $id));
                }
            }
        }
    }
}
