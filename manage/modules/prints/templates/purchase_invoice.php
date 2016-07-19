<?php

require_once __DIR__ . '/abstract_template.php';

class purchase_invoice extends AbstractTemplate
{
    public function draw_one($object)
    {
        $parentId = $this->all_configs['db']->query('SELECT parent_id FROM {contractors_suppliers_orders} WHERE id=?i',
            array($object))->el();
        $orderId = empty($parentId) ? $object : $parentId;
        $orders = $this->all_configs['db']->query('SELECT o.*, c.title as supplier, w.title as wh_title, wl.location as wh_location,
                                                w.print_address,w.print_phone, g.title
                                                FROM {contractors_suppliers_orders} as o
                                                LEFT JOIN {contractors} as c ON c.id=o.supplier 
                                                LEFT JOIN {warehouses} as w ON w.id=o.wh_id 
                                                LEFT JOIN {warehouses_locations} as wl ON wl.id=o.location_id 
                                                LEFT JOIN {goods} as g ON g.id = o.goods_id
                                                WHERE o.id=?i OR o.parent_id=?i ORDER by id ASC',
            array($orderId, $orderId))->assoc();

        $print_html = '';
        if ($orders) {
            $this->editor = true;
            $amount = 0;
            $parent = current($orders);
            $view = new View($this->all_configs);

            foreach ($orders as $order) {
                $amount += ($order['count'] * $order['price'] / 100);
            }

            $products = $view->renderFile('prints/purchase_invoice_products', array(
                'orders' => $orders,
                'amount' => $amount
            ));

            $arr = array(
                'id' => array('value' => intval($orderId), 'name' => l('ID заказа')),
                'date' => array(
                    'value' => date("d/m/Y", strtotime($parent['date_add'])),
                    'name' => l('Дата создания заказа поставщику')
                ),
                'now' => array('value' => date("d/m/Y", time()), 'name' => l('Текущая дата')),
                'supplier' => array('value' => h($parent['supplier']), 'name' => l('Поставщик')),
                'warehouse' => array('value' => h($parent['wh_title']), 'name' => l('Название склада')),
                'location' => array('value' => h($parent['wh_location']), 'name' => l('Название локации')),
                'warehouse_accept' => array(
                    'value' => h($parent['w_title']),
                    'name' => l('Название склада приема')
                ),
                'wh_address' => array(
                    'value' => h($parent['print_address']),
                    'name' => l('Адрес склада')
                ),
                'wh_phone' => array('value' => h($parent['print_phone']), 'name' => l('Телефон склада')),
                'company' => array(
                    'value' => h($this->all_configs['settings']['site_name']),
                    'name' => l('Название компании')
                ),
                'currency' => array('value' => viewCurrencySuppliers(), 'name' => l('Валюта')),
                'products' => array('value' => $products, 'name' => l('Товары')),
                'amount' => array('value' => $amount, 'name' => l('Полная стоимость')),
                'amount_in_words' => array(
                    'value' => $this->amountAsWord(max(0, $amount), $this->all_configs['configs']['currencies'][$this->all_configs['settings']['currency_suppliers_orders']]['rutils']),
                    'name' => l('Полная стоимость прописью')
                ),
                'qty_all' => array('value' => count($orders), 'name' => l('Количество товаров')),
            );

            $print_html = $this->generate_template($arr, 'purchase_invoice');
        }
        return $print_html;
    }
}
