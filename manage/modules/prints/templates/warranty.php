<?php

require_once __DIR__ . '/abstract_orders_template.php';

// гарантийный талон 
class warranty extends AbstractOrdersTemplate
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


            $arr = $this->getVariables($order, $goods);

            $arr['date'] = array(
                'value' => date("d/m/Y", strtotime($order['date_add'])),
                'name' => l('Дата создания заказа на ремонт')
            );

            $print_html = $this->generate_template($this->addUsersFieldsValues($order, $arr), 'warranty');
        }
        return $print_html;
    }
}
