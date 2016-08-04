<?php
require_once __DIR__ . '/../Core/AModel.php';

class MOrdersGoods extends AModel
{
    public $table = 'orders_goods';

    /**
     * @param      $orderProductId
     * @param      $order
     * @param bool $closeSupplierOrder
     * @return array
     * @throws ExceptionWithMsg
     */
    public function remove($orderProductId, $order, $closeSupplierOrder = false)
    {
        $data = array();
        $item_id = $this->query(
            'SELECT item_id FROM ?t WHERE id=?i AND item_id IS NOT NULL',
            array($this->table, $orderProductId))->el();
        if ($item_id > 0) {
            throw new ExceptionWithMsg('Отвяжите серийный номер');
        }
        // удаляем
        $ar = $this->query('DELETE FROM ?t WHERE id=?i',
            array($this->table, $orderProductId));
        $supplier_order = $this->query("
                            SELECT supplier_order_id as id, o.count, o.supplier "
            . "FROM {orders_suppliers_clients} as c "
            . "LEFT JOIN {contractors_suppliers_orders} as o ON o.id = c.supplier_order_id "
            . "WHERE order_goods_id=?i", array($orderProductId), 'row');
        $this->query('DELETE FROM {orders_suppliers_clients} WHERE order_goods_id=?i',
            array($orderProductId));
        // удалить заказ поставщику
        // если он для одного устройства
        if ($closeSupplierOrder) {
            $this->query("UPDATE {contractors_suppliers_orders} SET avail = 0 "
                . "WHERE id = ?i", array($supplier_order['id']));
        }
        // поменять статус заказа с ожидает запчастей на принят в ремонт
        // если запчастей все запчасти отвязаны c заказа
        $orders_goods = $this->query("SELECT count(*) FROM ?t WHERE order_id = ?i", array($this->table, $order['id']),
            'el');
        if (empty($orders_goods)) {
            update_order_status($order, $this->all_configs['configs']['order-status-new']);
            $data['reload'] = 1;
        }
        if (!$ar) {
            throw new ExceptionWithMsg('Изделие не найдено');
        }
        return $data;
    }

    /**
     * @param $key
     * @return bool
     */
    public function isHash($key)
    {
        return strlen($key) == 32;
    }

    /**
     * @param $product
     * @return string
     */
    public function calculateHash($product)
    {
        $string = $product['goods_id'] . $product['price'] . $product['discount'] . $product['url'] . $product['item_id'] . $product['warranty'] . $product['discount_type'] . $product['so_id'];
        return md5($string);
    }

    /**
     * @param $products
     * @return array
     */
    public function productsGroup($products)
    {
        $result = array();
        foreach ($products as $product) {
            $hash = $this->calculateHash($product);
            if (empty($result[$hash])) {
                $result[$hash] = $product;
                $result[$hash]['id'] = $hash;
                $result[$hash]['group'] = array();
            }
            $result[$hash]['group'][] = $product;
        }
        return $result;
    }

    /**
     * @param $products
     * @param $hash
     * @return array
     */
    public function getProductsIdsByHash($products, $hash)
    {
        $result = array();
        foreach ($products as $product) {
            if($hash == $this->calculateHash($product)) {
                $result[] =  $product['id'];
            };
        }
        return $result;
    }

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
            'warranty',
            'discount_type',
            'price_type'
        );
    }

    /**
     * @param $orderProductId
     * @return array
     */
    public function getWithTitle($orderProductId)
    {
        return $this->query('SELECT o.*, g.title FROM ?t as o JOIN {goods} as g ON g.id=o.goods_id WHERE o.id=?i', array($this->table, $orderProductId))->row();
    }
}