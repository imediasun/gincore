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
        $this->PurchaseInvoiceGoods->updateItems($id, $items);
    }

    /**
     * @param $id
     * @return array
     */
    public function getGoods($id)
    {
        return $this->PurchaseInvoiceGoods->getGoods($id);
    }

    /**
     * @param $invoice
     * @param $mod_id
     * @return array
     * @throws ExceptionWithMsg
     */
    public function createOrderFromInvoice($invoice, $mod_id)
    {
        $goods = $this->getGoods($invoice['id']);
        if (empty($goods)) {
            throw new ExceptionWithMsg(l('Товары не заданы'));
        }

        $data = array(
            'warehouse-supplier' => $invoice['supplier_id'],
            'warehouse-order-date' => $invoice['date'],
            'warehouse-type' => $invoice['type'],
            'comment-supplier' => $invoice['description'],
            'item_ids' => array(),
            'amount' => array(),
            'quantity' => array()
        );

        foreach ($goods as $id => $good) {
            $data['item_ids'][$id] = $good['good_id'];
            $data['amount'][$id] = $good['price'] / 100;
            $data['quantity'][$id] = $good['quantity'];
        }
        $order = $this->all_configs['suppliers_orders']->create_order($mod_id, $data);
        if (!isset($order['id']) || $order['id'] == 0) {
            throw new ExceptionWithMsg(l('Проблемы при создании заказа поставщику'));
        }
        $this->query('
            UPDATE {contractors_suppliers_orders} 
            SET wh_id=?i, location_id=?i, date_come=?, date_check=?, user_id_accept=user_id, count_come=`count`
            WHERE id=?i OR parent_id=?i',
            array(
                $invoice['warehouse_id'],
                $invoice['location_id'],
                date('Y-m-d H:i'),
                date('Y-m-d H:i'),
                $order['parent_order_id'],
                $order['parent_order_id']
            ))->ar();
        $this->update(array(
            'supplier_order_id' => $order['id'],
        ), array('id' => $invoice['id']));
        return $order['parent_order_id'];
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
            'purchase_date',
            'supplier_order_id'
        );
    }
}
