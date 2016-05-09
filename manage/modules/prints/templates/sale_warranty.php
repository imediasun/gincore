<?php

require_once __DIR__ . '/abstract_template.php';

// гарантийный талон на продажу
class sale_warranty extends AbstractTemplate
{
    public function draw_one($object)
    {
        $print_html = '';

        $order = $this->all_configs['db']->query('SELECT o.*, e.fio as engineer, w.title as wh_title, aw.title as aw_title,
                                                aw.print_address,aw.print_phone 
                                                FROM {orders} as o
                                                LEFT JOIN {users} as e ON e.id=o.engineer 
                                                LEFT JOIN {warehouses} as w ON w.id=o.wh_id 
                                                LEFT JOIN {warehouses} as aw ON aw.id=o.accept_wh_id 
                                                WHERE o.id=?i',
            array($object))->row();

        if ($order) {
            $products = $products_cost = $services = '';
            $services_cost = array();


            // товары и услуги
            $goods = $this->all_configs['db']->query('SELECT og.title, og.price, g.type
                      FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND og.goods_id=g.id',
                array($object))->assoc();
            if ($goods) {
                foreach ($goods as $product) {
                    if ($product['type'] == 0) {
                        $products .= htmlspecialchars($product['title']) . '<br/>';
                        $products_cost .= ($product['price'] / 100) . ' ' . viewCurrency() . '<br />';
                    }
                    if ($product['type'] == 1) {
                        $services .= htmlspecialchars($product['title']) . '<br/>';
                        $services_cost[] = ($product['price'] / 100);
                    }
                }
            }

            $this->editor = true;
            $arr = array(
                'id' => array('value' => intval($order['id']), 'name' => 'ID заказа на ремонт'),
                'date' => array(
                    'value' => date("d/m/Y", strtotime($order['date_add'])),
                    'name' => 'Дата создания заказа на ремонт'
                ),
                'now' => array('value' => date("d/m/Y", time()), 'name' => 'Текущая дата'),
                'warranty' => array(
                    'value' => $order['warranty'] > 0 ? $order['warranty'] . ' ' . l('мес') . '' : 'Без гарантии',
                    'name' => 'Гарантия'
                ),
                'fio' => array('value' => htmlspecialchars($order['fio']), 'name' => 'ФИО клиента'),
                'phone' => array('value' => htmlspecialchars($order['phone']), 'name' => 'Телефон клиента'),
                'defect' => array('value' => htmlspecialchars($order['defect']), 'name' => 'Неисправность'),
                'engineer' => array('value' => htmlspecialchars($order['engineer']), 'name' => 'Инженер'),
                'comment' => array('value' => htmlspecialchars($order['comment']), 'name' => 'Внешний вид'),
                'sum' => array('value' => $order['sum'] / 100, 'name' => 'Сумма за ремонт'),
                'sum_paid' => array('value' => $order['sum_paid'] / 100, 'name' => 'Оплаченная сумма'),
                'products' => array('value' => $products, 'name' => 'Установленные запчасти'),
                'products_cost' => array('value' => $products_cost, 'name' => 'Установленные запчасти'),
                'services' => array('value' => $services, 'name' => 'Услуги'),
                'services_cost' => array(
                    'value' => implode(' ' . viewCurrency() . '<br />', $services_cost),
                    'name' => 'Стоимость услуг'
                ),
                'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => 'Серийный номер'),
                'product' => array(
                    'value' => htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['note']),
                    'name' => 'Устройство'
                ),
                'warehouse' => array('value' => htmlspecialchars($order['wh_title']), 'name' => 'Название склада'),
                'warehouse_accept' => array(
                    'value' => htmlspecialchars($order['aw_title']),
                    'name' => 'Название склада приема'
                ),
                'wh_address' => array(
                    'value' => htmlspecialchars($order['print_address']),
                    'name' => 'Адрес склада'
                ),
                'wh_phone' => array('value' => htmlspecialchars($order['print_phone']), 'name' => 'Телефон склада'),
                'company' => array(
                    'value' => htmlspecialchars($this->all_configs['settings']['site_name']),
                    'name' => 'Название компании'
                ),
                'currency' => array('value' => viewCurrency(), 'name' => 'Валюта'),
            );

            $print_html = generate_template($arr, 'sale_warranty');
        }
        return $print_html;
    }
}
