<?php

require_once __DIR__ . '/abstract_template.php';

// гарантийный талон 
class warranty extends AbstractTemplate
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
                        $products .= h($product['title']) . '<br/>';
                        $products_cost .= ($product['price'] / 100) . ' ' . viewCurrency() . '<br />';
                    }
                    if ($product['type'] == 1) {
                        $services .= h($product['title']) . '<br/>';
                        $services_cost[] = ($product['price'] / 100);
                    }
                }
            }

            $this->editor = true;
            $arr = array(
                'id' => array('value' => intval($order['id']), 'name' => l('ID заказа на ремонт')),
                'date' => array(
                    'value' => date("d/m/Y", strtotime($order['date_add'])),
                    'name' => l('Дата создания заказа на ремонт')
                ),
                'now' => array('value' => date("d/m/Y", time()), 'name' => l('Текущая дата')),
                'warranty' => array(
                    'value' => $order['warranty'] > 0 ? $order['warranty'] . ' ' . l('мес') . '' : l('Без гарантии'),
                    'name' => l('Гарантия')
                ),
                'fio' => array('value' => htmlspecialchars($order['fio']), 'name' => l('ФИО клиента')),
                'phone' => array('value' => htmlspecialchars($order['phone']), 'name' => l('Телефон клиента')),
                'defect' => array('value' => htmlspecialchars($order['defect']), 'name' => l('Неисправность')),
                'engineer' => array('value' => htmlspecialchars($order['engineer']), 'name' => l('Инженер')),
                'comment' => array('value' => htmlspecialchars($order['comment']), 'name' => l('Внешний вид')),
                'sum' => array('value' => $order['sum'] / 100, 'name' => l('Сумма за ремонт')),
                'sum_paid' => array('value' => $order['sum_paid'] / 100, 'name' => l('Оплаченная сумма')),
                'products' => array('value' => $products, 'name' => l('Установленные запчасти')),
                'products_cost' => array('value' => $products_cost, 'name' => l('Установленные запчасти')),
                'services' => array('value' => $services, 'name' => l('Услуги')),
                'services_cost' => array(
                    'value' => implode(' ' . viewCurrency() . '<br />', $services_cost),
                    'name' => l('Стоимость услуг')
                ),
                'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => l('Серийный номер')),
                'product' => array(
                    'value' => htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['note']),
                    'name' => l('Устройство')
                ),
                'warehouse' => array('value' => htmlspecialchars($order['wh_title']), 'name' => l('Название склада')),
                'warehouse_accept' => array(
                    'value' => htmlspecialchars($order['aw_title']),
                    'name' => l('Название склада приема')
                ),
                'wh_address' => array(
                    'value' => htmlspecialchars($order['print_address']),
                    'name' => l('Адрес склада')
                ),
                'wh_phone' => array('value' => htmlspecialchars($order['print_phone']), 'name' => l('Телефон склада')),
                'company' => array(
                    'value' => htmlspecialchars($this->all_configs['settings']['site_name']),
                    'name' => l('Название компании')
                ),
                'currency' => array('value' => viewCurrency(), 'name' => l('Валюта')),
            );

            $print_html = $this->generate_template($arr, 'warranty');
        }
        return $print_html;
    }
}
