<?php
require_once __DIR__ . '/../Core/AModel.php';

/**
 * Class MOrders
 *
 * @property  MHistory    $History
 * @property  MWarehouses Warehouses
 */
class MOrders extends AModel
{
    public $table = 'orders';

    public $uses = array(
        'History',
        'Warehouses'
    );

    /**
     * @return array
     */
    public function getDeliveryByList()
    {
        return array(
            DELIVERY_BY_SELF => l('Самовывоз'),
            DELIVERY_BY_COURIER => l('Курьером'),
            DELIVERY_BY_POST => l('Почтой')
        );
    }

    /**
     * @param $options
     * @return bool|int
     */
    public function save($options)
    {
        return $this->insert($options);
    }

    /**
     * @param null $orderId
     * @return array
     */
    public function getClosed($orderId = null)
    {
        $conditions = $this->makeQuery('status IN (?li)',
            array($this->all_configs['configs']['order-statuses-closed']));
        if (!empty($orderId)) {
            $conditions = $this->makeQuery('?q AND id=?i', array($conditions, $orderId));
        }
        // достаем заказ
        return $this->query('SELECT * FROM ?t WHERE ?q', array($this->table, $conditions))->row();
    }

    /**
     * @param $order
     * @param $modId
     * @return mixed
     */
    public function setOrderSum($order, $modId)
    {

        if (is_numeric($order)) {
            $order = $this->all_configs['db']->query('SELECT o.* FROM ?t o, {orders_goods} og WHERE og.order_id=o.id AND og.id=?',
                array($this->table, $order))->row();
        }
        if ($order['total_as_sum']) {
            $sum = $this->getTotalSum($order);
            if ($sum != $order['sum']) {
                $this->all_configs['db']->query('UPDATE ?t SET `sum`=?i  WHERE id=?i',
                    array($this->table, $sum, $order['id']))->ar();
                $this->History->save('update-order-sum', $modId, $order['id'], ($sum / 100));
                $order['sum'] = $sum;
            }
        }
        return $order;
    }

    /**
     * @param $order
     * @return int
     */
    public function getTotalSum($order)
    {
        $notSale = $order['type'] != 3;
        $goods = $this->all_configs['manageModel']->order_goods($order['id'], 0);
        $services = $notSale ? $this->all_configs['manageModel']->order_goods($order['id'], 1) : null;

        $productTotal = 0;
        if (!empty($goods)) {
            foreach ($goods as $product) {
                if ($product['discount_type'] == 1) {
                    $price = $product['price'] * (1 - $product['discount'] / 100);
                } else {
                    $price = $product['price'] - $product['discount'] * 100;
                }
                $productTotal += $price * $product['count'];
            }
        }
        if (!empty($services)) {
            foreach ($services as $product) {
                $productTotal += $product['price'] * $product['count'];
            }
        }
        return $productTotal;
    }

    /**
     * @todo по уму заменить бы на откат транзакции
     *
     * @param $order
     */
    public function rollback($order)
    {
        if (!empty($order) && array_key_exists('id', $order) && $order['id'] > 0) {
            // удаляем заявки
            $this->all_configs['db']->query('DELETE FROM {orders_suppliers_clients} WHERE client_order_id=?i',
                array($order['id']));
            // удаяем перемещения
            $this->all_configs['db']->query('DELETE FROM {warehouses_stock_moves} WHERE order_id=?i',
                array($order['id']));
            // удалить номер заказа с item
            $this->all_configs['db']->query('UPDATE {warehouses_goods_items} SET order_id=null WHERE order_id=?i',
                array($order['id']));
            // удаляем транзакции
            $this->all_configs['db']->query('DELETE FROM {cashboxes_transactions} WHERE client_order_id=?i',
                array($order['id']));
            // удаляем связку заказов
            $this->all_configs['db']->query('DELETE FROM {orders_suppliers_clients} WHERE client_order_id=?i',
                array($order['id']));
            // удаляем товары
            $this->all_configs['db']->query('DELETE FROM {orders_goods} WHERE order_id=?i', array($order['id']));
            // удаляем заказ
            $this->all_configs['db']->query('DELETE FROM ?t WHERE id=?i', array($this->table, $order['id']));
        }
    }

    /**
     * @param $itemIds
     * @return array
     * @throws ExceptionWithMsg
     * @throws ExceptionWithURL
     */
    public function getAvailableItems($itemIds)
    {
        $items = $this->Warehouses->getAvailableItems($itemIds);
        // изделий не найдено
        if (empty($items)) {
            throw  new ExceptionWithMsg(l('Свободные изделия не найдены'));
        }
        foreach ($items as $k => $item) {
            // нет менеджера
            if ($item['user_id'] == 0) {
                throw new ExceptionWithURL($this->all_configs['prefix'] . "products/create/" . $item['goods_id'] . "?error=manager#managers");
            }
        }
        return $items;
    }

    /**
     * @return string
     */
    public function getUrgentCount()
    {
        return $this->query('SELECT count(*) FROM ?t WHERE urgent=1 AND not status in (?li)', array(
            $this->table,
            $this->all_configs['configs']['order-statuses-urgent-not-show']
        ))->el();
    }

    /**
     * @param int $by
     * @return int
     */
    public function getDebts($by = ORDER_REPAIR)
    {
        return $this->query('SELECT sum(`sum`/100 - sum_paid/100 - discount/100) FROM ?t WHERE status in (?li) AND `type`=?i AND `sum` > (sum_paid + discount)', array($this->table, $this->all_configs['configs']['order-statuses-debts'], $by))->el();
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'status_id',
            'user_id',
            'fio',
            'email',
            'status',
            'type',
            'approximate_cost',
            'sum',
            'prepay',
            'prepay_comment',
            'discount',
            'manager',
            'accepter',
            'engineer',
            'comment',
            'phone',
            'date_add',
            'sum_paid',
            'title',
            'category_id',
            'serial',
            'note',
            'battery',
            'charger',
            'cover',
            'box',
            'repair',
            'urgent',
            'np_accept',
            'notify',
            'client_took',
            'partner',
            'date_readiness',
            'defect',
            'location_id',
            'wh_id',
            'send_sms',
            'course_key',
            'course_value',
            'date_pay',
            'replacement_fund',
            'is_replacement_fund',
            'return_id',
            'nonconsent',
            'is_waiting',
            'courier',
            'warranty',
            'accept_location_id',
            'accept_wh_id',
            'code',
            'referer_id',
            'color',
            'equipment',
            'total_as_sums',
            'total_as_sum',
            'cashless',
            'delivery_by',
            'delivery_to',
            'sale_type',
            'home_master_request',
            'engineer_comment',
            'brand_id'
        );
    }
}
