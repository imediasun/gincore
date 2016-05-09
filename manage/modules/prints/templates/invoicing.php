<?php

require_once __DIR__ . '/abstract_template.php';

// Счет на оплату
// чек
class invoicing extends AbstractTemplate
{
    public function draw_one($object)
    {
        $print_html = '';
        $type = $this->all_configs['db']->query("SELECT type FROM {orders} WHERE id = ?i", array($object), 'el');
        $products_rows = array();
        $summ = 0;
        if ($type == 0) {
            $order = $this->all_configs['db']->query(
                'SELECT o.*, a.fio as a_fio, w.title as wh_title, wa.print_address, wa.title as wa_title,
                            wa.print_phone, wa.title as wa_title, wag.address as accept_address
                    FROM {orders} as o
                    LEFT JOIN {users} as a ON a.id=o.accepter
                    LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                    LEFT JOIN {warehouses} as wa ON wa.id=o.accept_wh_id
                    LEFT JOIN {warehouses_groups} as wag ON wa.group_id=wa.id
                    WHERE o.id=?i', array($object))->row();
        }

        if ($type == 3) {
            $order = $this->all_configs['db']->query(
                "SELECT o.*, g.title as g_title, g.item_id, wag.address as accept_address,wa.print_phone FROM {orders} as o
                    LEFT JOIN {orders_goods} as g ON g.order_id = o.id
                    LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                    LEFT JOIN {warehouses} as wa ON wa.id=o.accept_wh_id
                    LEFT JOIN {warehouses_groups} as wag ON wa.group_id=wa.id
                    WHERE o.id = ?i", array($object))->row();
        }

        if ($order) {

            // товары и услуги
            $goods = $this->all_configs['db']->query('SELECT og.title, og.price, g.type
                      FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND og.goods_id=g.id',
                array($object))->assoc();
            if ($goods) {
                foreach ($goods as $product) {
                    $products_rows[] = array(
                        'title' => htmlspecialchars($product['title']),
                        'price_view' => ($product['price'] / 100) . ' ' . viewCurrency()
                    );
                }
            }
            $summ = $order['sum'];

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
            $qty_all = $num - 1;
            $products_html = implode('</td></tr><tr><td>', $products_html_parts);

            $this->editor = true;
            require_once __DIR__ . '/../../../classes/php_rutils/struct/TimeParams.php';
            require_once __DIR__ . '/../../../classes/php_rutils/Dt.php';
            require_once __DIR__ . '/../../../classes/php_rutils/Numeral.php';
            require_once __DIR__ . '/../../../classes/php_rutils/RUtils.php';
            $sum_in_words = \php_rutils\RUtils::numeral()->getRubles($summ / 100, false,
                $this->all_configs['configs']['currencies'][$this->all_configs['settings']['currency_orders']]['rutils']['gender'],
                $this->all_configs['configs']['currencies'][$this->all_configs['settings']['currency_orders']]['rutils']['words']);
            $params = new \php_rutils\struct\TimeParams();
            $params->date = null;
            $params->format = 'd F Y';
            $params->monthInflected = true;
            $str_date = \php_rutils\RUtils::dt()->ruStrFTime($params);


            if ($order['type'] == 0) {
                $arr = array(
                    'id' => array('value' => intval($order['id']), 'name' => 'ID заказа на ремонт'),
                    'sum' => array('value' => $summ / 100, 'name' => 'Сумма за ремонт'),
                    'qty_all' => array('value' => $qty_all, 'name' => 'Количество наименований'),
                    'sum_in_words' => array('value' => $sum_in_words, 'name' => 'Сумма за ремонт прописью'),
                    'address' => array('value' => htmlspecialchars($order['accept_address']), 'name' => 'Адрес'),
                    'now' => array('value' => $str_date, 'name' => 'Текущая дата'),
                    'wh_phone' => array(
                        'value' => htmlspecialchars($order['print_phone']),
                        'name' => 'Телефон склада'
                    ),
                    'currency' => array('value' => viewCurrency(), 'name' => 'Валюта'),
                    'phone' => array('value' => htmlspecialchars($order['phone']), 'name' => 'Телефон клиента'),
                    'fio' => array('value' => htmlspecialchars($order['fio']), 'name' => 'ФИО клиента'),
                    'product' => array(
                        'value' => htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['note']),
                        'name' => 'Устройство'
                    ),
                    'products_and_services' => array('value' => $products_html, 'name' => 'Товары и услуги'),
                    'color' => array(
                        'value' => $order['color'] ? htmlspecialchars($this->all_configs['configs']['devices-colors'][$order['color']]) : '',
                        'name' => 'Устройство'
                    ),
                    'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => 'Серийный номер'),
                    'company' => array(
                        'value' => htmlspecialchars($this->all_configs['settings']['site_name']),
                        'name' => 'Название компании'
                    ),
                    'order' => array('value' => $order['id'], 'name' => 'Номер заказа'),
                    'order_data' => array(
                        'value' => date('d/m/Y', $order['date_add']),
                        'name' => 'Дата создания заказа'
                    ),
                );
            }

            if ($order['type'] == 3) {

                $arr = array(
                    'id' => array('value' => intval($order['id']), 'name' => 'ID заказа на ремонт'),
                    'sum' => array('value' => $summ / 100, 'name' => 'Сумма за ремонт'),
                    'qty_all' => array('value' => $qty_all, 'name' => 'Количество наименований'),
                    'products_and_services' => array('value' => $products_html, 'name' => 'Товары и услуги'),
                    'product' => array(
                        'value' => htmlspecialchars($order['g_title']) . ' ' . htmlspecialchars($order['note']),
                        'name' => 'Устройство'
                    ),
                    'serial' => array(
                        'value' => suppliers_order_generate_serial($order),
                        'name' => 'Серийный номер'
                    ),
                    'company' => array(
                        'value' => htmlspecialchars($this->all_configs['settings']['site_name']),
                        'name' => 'Название компании'
                    ),
                    'address' => array('value' => htmlspecialchars($order['accept_address']), 'name' => 'Адрес'),
                    'wh_phone' => array(
                        'value' => htmlspecialchars($order['print_phone']),
                        'name' => 'Телефон склада'
                    ),
                    'now' => array('value' => $str_date, 'name' => 'Текущая дата'),
                    'currency' => array('value' => viewCurrency(), 'name' => 'Валюта'),
                    'sum_in_words' => array('value' => $sum_in_words, 'name' => 'Сумма за ремонт прописью'),
                    'order' => array('value' => $order['id'], 'name' => 'Номер заказа'),
                    'order_data' => array(
                        'value' => date('d/m/Y', $order['date_add']),
                        'name' => 'Дата создания заказа'
                    ),
                );
            }


            $print_html = generate_template($arr, 'invoicing');
        }
    }
}
