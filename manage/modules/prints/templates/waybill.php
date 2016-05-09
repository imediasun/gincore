<?php

require_once __DIR__ . '/abstract_template.php';

// накладаная на отгрузку товара при продаже
class waybill extends AbstractTemplate
{
    public function draw_one($object)
    {

        $order = $this->all_configs['db']->query('SELECT o.*, e.fio as manager, w.title as wh_title, aw.title as aw_title,
                                                aw.print_address,aw.print_phone 
                                                FROM {orders} as o
                                                LEFT JOIN {users} as e ON e.id=o.manager 
                                                LEFT JOIN {warehouses} as w ON w.id=o.wh_id 
                                                LEFT JOIN {warehouses} as aw ON aw.id=o.accept_wh_id 
                                                WHERE o.id=?i',
            array($object))->row();

        $print_html = '';
        if ($order) {
            $amount = 0;
            require_once __DIR__ . '/../../../classes/php_rutils/struct/TimeParams.php';
            require_once __DIR__ . '/../../../classes/php_rutils/Dt.php';
            require_once __DIR__ . '/../../../classes/php_rutils/Numeral.php';
            require_once __DIR__ . '/../../../classes/php_rutils/RUtils.php';
            $sum_in_words = \php_rutils\RUtils::numeral()->getRubles($order['sum'] / 100, false,
                $this->all_configs['configs']['currencies'][$this->all_configs['settings']['currency_orders']]['rutils']['gender'],
                $this->all_configs['configs']['currencies'][$this->all_configs['settings']['currency_orders']]['rutils']['words']);

            // товары и услуги
            $goods = $this->all_configs['db']->query('SELECT og.title, og.price, g.type
                      FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND og.goods_id=g.id',
                array($object))->assoc();
            $view = new View();
            $products = $view->renderFile('print/waybill_products', array(
                'goods' => $goods
            ));

            $amount_in_words = 0;
            $this->editor = true;

            $arr = array(
                'id' => array('value' => intval($order['id']), 'name' => l('ID заказа')),
                'date' => array(
                    'value' => date("d/m/Y", strtotime($order['date_add'])),
                    'name' => l('Дата создания заказа на продажу')
                ),
                'now' => array('value' => date("d/m/Y", time()), 'name' => l('Текущая дата')),
                'warranty' => array(
                    'value' => $order['warranty'] > 0 ? $order['warranty'] . ' ' . l('мес') . '' : l('Без гарантии'),
                    'name' => l('Гарантия')
                ),
                'fio' => array('value' => htmlspecialchars($order['fio']), 'name' => l('ФИО клиента')),
                'manager' => array('value' => htmlspecialchars($order['manager']), 'name' => l('Менеджер')),
                'phone' => array('value' => htmlspecialchars($order['phone']), 'name' => l('Телефон клиента')),
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
                'products' => array('value' => $products, 'name' => l('Товары')),
                'amount' => array('value' => $amount, 'name' => l('Полная стоимость')),
                'amount_in_words' => array('value' => $amount_in_words, 'name' => l('Полная стоимость прописью')),
            );

            $print_html = $this->generate_template($arr, 'waybill');
        }
        return $print_html;
    }
}