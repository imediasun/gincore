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
            $this->editor = true;

            // товары и услуги
            $goods = $this->all_configs['db']->query('SELECT og.title, og.price, g.type
                      FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND og.goods_id=g.id',
                array($object))->assoc();
            if ($goods) {
                foreach ($goods as $good) {

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
                'manager' => array('value' => htmlspecialchars($order['manager']), 'name' => l('Менеджер')),
                'product' => array('value' => $good['title'], 'name' => l('Товар')),
                'price' => array('value' => $good['price'], 'name' => l('Цена')),
                'price_with_discount' => array('value' => $good['price'] * (1 - $good['discount']/100), 'name' => l('Цена со скидкой')),
                'serial' => array('value' => htmlspecialchars($order['serial']), 'name' => l('Серийный номер')),
                'company' => array(
                    'value' => htmlspecialchars($this->all_configs['settings']['site_name']),
                    'name' => l('Название компании')
                ),
            );
                    $print_html .= $this->generate_template($arr, 'sale_warranty');
                }
            }

        }
        return $print_html;
    }
}
