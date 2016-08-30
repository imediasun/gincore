<?php

require_once __DIR__ . '/abstract_orders_template.php';

// гарантийный талон на продажу
class sale_warranty extends AbstractOrdersTemplate
{
    public function draw_one($object, $template='')
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
            $goods = $this->all_configs['db']->query('SELECT og.id as item_id, og.title, og.price, g.type, og.warranty
                      FROM {orders_goods} as og, {goods} as g WHERE og.order_id=?i AND og.goods_id=g.id AND og.warranty > 0',
                array($object))->assoc();
            if (empty($goods)) {
                throw new ExceptionWithMsg(l('В корзине нет товаров с установленным гарантийным сроком'));
            }
            foreach ($goods as $id => $good) {
                $arr = array(
                    'id' => array('value' => intval($order['id']), 'name' => l('ID заказа на ремонт')),
                    'date' => array(
                        'value' => date("d/m/Y", strtotime($order['date_add'])),
                        'name' => l('Дата создания заказа на ремонт')
                    ),
                    'now' => array('value' => date("d/m/Y", time()), 'name' => l('Текущая дата')),
                    'warranty' => array(
                        'value' => $good['warranty'] > 0 ? $good['warranty'] . ' ' . l('мес') . '' : l('Без гарантии'),
                        'name' => l('Гарантия')
                    ),
                    'fio' => array('value' => h($order['fio']), 'name' => l('ФИО клиента')),
                    'phone' => array('value' => h($order['phone']), 'name' => l('Телефон клиента')),
                    'manager' => array('value' => h($order['manager']), 'name' => l('Менеджер')),
                    'product' => array('value' => $good['title'], 'name' => l('Товар')),
                    'price' => array('value' => $good['price'], 'name' => l('Цена')),
                    'price_with_discount' => array(
                        'value' => price_with_discount($good),
                        'name' => l('Цена со скидкой')
                    ),
                    'serial' => array(
                        'value' => h(suppliers_order_generate_serial($good, true, false)),
                        'name' => l('Серийный номер')
                    ),
                    'company' => array(
                        'value' => h($this->all_configs['settings']['site_name']),
                        'name' => l('Название компании')
                    ),
                    'wh_phone' => array('value' => h($order['print_phone']), 'name' => 'Телефон склада'),
                );
                $print_html .= $this->generate_template($this->addUsersFieldsValues($order, $arr), 'sale_warranty', $id == 0);
            }
        }
        return $print_html;
    }
}
