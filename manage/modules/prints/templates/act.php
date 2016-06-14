<?php

require_once __DIR__ . '/abstract_template.php';

// акт 
class act extends AbstractTemplate
{
    public function draw_one($object)
    {
        $print_html = '';
        $order = $this->all_configs['db']->query(
            'SELECT o.*, a.fio as a_fio, w.title as wh_title, wa.print_address, wa.title as wa_title,
                        wa.print_phone, wa.title as wa_title, wag.address as accept_address
                FROM {orders} as o
                LEFT JOIN {users} as a ON a.id=o.accepter
                LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                LEFT JOIN {warehouses} as wa ON wa.id=o.accept_wh_id
                LEFT JOIN {warehouses_groups} as wag ON wa.group_id=wa.id
                WHERE o.id=?i', array($object))->row();
        if ($order) {
            $this->editor = true;

            // товары и услуги
            $products_rows = array();
            $summ = $sum_by_products_and_services = $sum_by_products = $sum_by_services = 0;

            $products = $products_cost = $services = '';
            $services_cost = array();
            $goods = $this->all_configs['db']->query('SELECT og.title, og.price, g.type
                      FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND og.goods_id=g.id',
                array($object))->assoc();
            if ($goods) {
                foreach ($goods as $product) {
                    $products_rows[] = array(
                        'title' => htmlspecialchars($product['title']),
                        'price_view' => ($product['price'] / 100) . ' ' . viewCurrency()
                    );
                    $sum_by_products_and_services += $product['price'];
                    if ($product['type'] == 0) {
                        $products .= htmlspecialchars($product['title']) . '<br/>';
                        $products_cost .= ($product['price'] / 100) . ' ' . viewCurrency() . '<br />';
                        $sum_by_products += $product['price'];
                    }
                    if ($product['type'] == 1) {
                        $services .= htmlspecialchars($product['title']) . '<br/>';
                        $services_cost[] = ($product['price'] / 100) . ' ' . viewCurrency();
                        $sum_by_services += $product['price'];
                    }
                }
            }

            $products_html_parts = array();
            $num = 1;
            foreach ($products_rows as $prod) {
                $products_html_parts[] = '
                        ' . $num . '</td>
                        <td>' . $prod['title'] . '</td>
                        <td>1</td>
                        <td>' . $prod['price_view'] . '</td>
                        <td>' . $prod['price_view'] . '
                    ';
                $num++;
            }
            $products_html = implode('</td></tr><tr><td>', $products_html_parts);

            $str_date = $this->dateAsWord();

            $arr = array(
                'id' => array('value' => intval($order['id']), 'name' => l('ID заказа на ремонт')),
                'now' => array('value' => $str_date, 'name' => l('Текущая дата')),
                'sum' => array('value' => $order['sum'] / 100, 'name' => l('Сумма за ремонт')),
                'sum_by_products_and_services' => array(
                    'value' => $sum_by_products_and_services / 100,
                    'name' => l('Сумма за запчасти и услуги')
                ),
                'currency' => array('value' => viewCurrency(), 'name' => l('Валюта')),
                'phone' => array('value' => htmlspecialchars($order['phone']), 'name' => l('Телефон клиента')),
                'fio' => array('value' => htmlspecialchars($order['fio']), 'name' => l('ФИО клиента')),
                'product' => array(
                    'value' => htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['note']),
                    'name' => l('Устройство')
                ),
                'color' => array(
                    'value' => $order['color'] ? htmlspecialchars($this->all_configs['configs']['devices-colors'][$order['color']]) : '',
                    'name' => l('Устройство')
                ),
                'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => l('Серийный номер')),
                'company' => array(
                    'value' => htmlspecialchars($this->all_configs['settings']['site_name']),
                    'name' => l('Название компании')
                ),
                'wh_phone' => array('value' => htmlspecialchars($order['print_phone']), 'name' => l('Телефон склада')),
                'products' => array('value' => $products, 'name' => l('Установленные запчасти')),
                'products_cost' => array('value' => $products_cost, 'name' => l('Установленные запчасти')),
                'sum_by_products' => array('value' => $sum_by_products / 100, 'name' => l('Сумма за запчасти')),
                'services' => array('value' => $services, 'name' => l('Услуги')),
                'services_cost' => array(
                    'value' => implode(' ' . viewCurrency() . '<br />', $services_cost),
                    'name' => l('Стоимость услуг')
                ),
                'sum_by_services' => array('value' => $sum_by_services / 100, 'name' => l('Сумма за услуги')),
                'products_and_services' => array(
                    'value' => $products_html,
                    'name' => l('Товары и услуги (вставляется внутрь таблицы)')
                ),

            );
            $print_html = $this->generate_template($arr, 'act');
        }
        return $print_html;
    }
}
