<?php

require_once __DIR__ . '/abstract_orders_template.php';

// накладаная на отгрузку товара при продаже
class waybill extends AbstractOrdersTemplate
{
    public function draw_one($object, $template='')
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
            $this->editor = true;
            $amount = 0;

            // товары и услуги
            $goods = $this->all_configs['db']->query('SELECT og.*, g.type
                      FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND og.goods_id=g.id',
                array($object))->assoc();
            $view = new View($this->all_configs);

            if (!empty($goods)) {
                foreach ($goods as $good) {
                    $amount += sum_with_discount($good);
                }
            }
            
            $products = $view->renderFile('prints/waybill_products', array(
                'goods' => $goods, 
                'amount' => $amount
            ));

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
                'amount_in_words' => array(
                    'value' => $this->amountAsWord(max(0, $amount)),
                    'name' => l('Полная стоимость прописью')
                ),
            );

            $print_html = $this->generate_template($this->addUsersFieldsValues($order, $arr), 'waybill');
        }
        return $print_html;
    }
}