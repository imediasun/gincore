<?php

require_once __DIR__ . '/abstract_template.php';

// чек 
class invoice extends AbstractTemplate
{
    public function draw_one($object)
    {
        $print_html = '';
        $type = $this->all_configs['db']->query("SELECT type FROM {orders} WHERE id = ?i", array($object), 'el');
        $products_rows = array();
        $summ = 0;
        $order = array();
        $arr = array();
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
            $goods = $this->all_configs['db']->query('SELECT og.*, g.type
                      FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND og.goods_id=g.id',
                array($object))->assoc();

            $view = new View($this->all_configs);
            $summ = $order['sum'];
            $products_html = $view->renderFile('prints/waybill_products', array(
                'goods' => $goods,
                'amount' => $summ / 100
            ));
            $qty_all = count($goods);

            $this->editor = true;
            $sum_in_words = $this->amountAsWord($summ / 100);
            $str_date = $this->dateAsWord();


            $sum_with_discount = $order['sum'] - $order['discount'];

            if ($order['type'] == 0) {
                $arr = array(
                    'id' => array('value' => intval($order['id']), 'name' => l('ID заказа на ремонт')),
                    'sum' => array('value' => $summ / 100, 'name' => l('Сумма за ремонт')),
                    'discount' => array(
                        'value' => $order['discount'] > 0? ($order['discount']/ 100) . viewCurrency(): '',
                        'name' => l('Скидка на заказ')
                    ),
                    'sum_with_discount' => array(
                        'value' => $sum_with_discount / 100,
                        'name' => l('Сумма за ремонт с учетом скидки')
                    ),
                    'qty_all' => array('value' => $qty_all, 'name' => l('Количество наименований')),
                    'sum_in_words' => array('value' => $sum_in_words, 'name' => l('Сумма за ремонт прописью')),
                    'sum_paid' => array(
                        'value' => $order['sum_paid'] > 0 ? $order['sum_paid'] : '',
                        'name' => l('Оплачено')
                    ),
                    'sum_paid_in_words' => array(
                        'value' => $order['sum_paid'] > 0 ? $this->amountAsWord($order['sum_paid'] / 100) : '',
                        'name' => l('Оплачено прописью')
                    ),
                    'address' => array('value' => htmlspecialchars($order['accept_address']), 'name' => l('Адрес')),
                    'now' => array('value' => $str_date, 'name' => l('Текущая дата')),
                    'wh_phone' => array(
                        'value' => htmlspecialchars($order['print_phone']),
                        'name' => l('Телефон склада')
                    ),
                    'currency' => array('value' => viewCurrency(), 'name' => l('Валюта')),
                    'phone' => array('value' => htmlspecialchars($order['phone']), 'name' => l('Телефон клиента')),
                    'fio' => array('value' => htmlspecialchars($order['fio']), 'name' => l('ФИО клиента')),
                    'product' => array(
                        'value' => htmlspecialchars($order['title']) . ' ' . htmlspecialchars($order['note']),
                        'name' => l('Устройство')
                    ),
                    'products_and_services' => array('value' => $products_html, 'name' => l('Товары и услуги')),
                    'color' => array(
                        'value' => $order['color'] ? htmlspecialchars($this->all_configs['configs']['devices-colors'][$order['color']]) : '',
                        'name' => l('Устройство')
                    ),
                    'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => l('Серийный номер')),
                    'company' => array(
                        'value' => htmlspecialchars($this->all_configs['settings']['site_name']),
                        'name' => l('Название компании')
                    ),
                    'order' => array('value' => $order['id'], 'name' => l('Номер заказа')),
                    'order_data' => array(
                        'value' => date('d/m/Y', strtotime($order['date_add'])),
                        'name' => l('Дата создания заказа')
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
                );
            }


            $print_html = $this->generate_template($arr, 'invoice');
        }
        return $print_html;
    }
}
