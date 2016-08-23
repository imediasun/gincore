<?php
require_once __DIR__ . '/../Core/AModel.php';

/**
 * Class MPurchaseInvoices
 * @property MPurchaseInvoiceGoods PurchaseInvoiceGoods
 */
class MPurchaseInvoices extends AModel
{
    public $table = 'purchase_invoices';
    public $uses = array(
        'PurchaseInvoiceGoods'
    );

    /**
     * @param $post
     * @return array
     */
    public function add($post)
    {
        $items = array();
        if (array_key_exists('items', $post)) {
            $items = $post['items'];
            unset($post['items']);
        }
        $id = $this->insert($post);
        $this->PurchaseInvoiceGoods->addItems($id, $items);
        return $id;
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'user_id',
            'supplier_id',
            'warehouse_id',
            'location_id',
            'type',
            'state',
            'description',
            'date',
            'purchase_date'
        );
    }
}
