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
     * @param $invoice
     * @param $data
     * @return bool|int
     */
    public function updateInvoice($invoice, $data)
    {
       $update = array();
        if($invoice['supplier_id'] != $data['warehouse-supplier']) {
            $update['supplier_id'] = $data['warehouse-supplier'];
        }
        if($invoice['warehouse_id'] != $data['warehouse']) {
            $update['warehouse_id'] = $data['warehouse'];
        }
        if($invoice['location_id'] != $data['location']) {
            $update['location_id'] = $data['location'];
        }
        if($invoice['type'] != $data['warehouse-type']) {
            $update['type'] = $data['warehouse-type'];
        }
        if($invoice['description'] != $data['comment-supplier']) {
            $update['description'] = $data['comment-supplier'];
        }
        if($invoice['date'] != date('Y-m-d H:s:i', strtotime($data['warehouse-order-date']))) {
            $update['date'] = date('Y-m-d H:s:i', strtotime($data['warehouse-order-date']));
        }
        return $this->update($update, array(
            'id' => $invoice['id']
        ));
    }

    /**
     * @param $id
     * @param $items
     */
    public function updateItems($id, $items)
    {
        $this->PurchaseInvoiceGoods->updateItems($items);
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
