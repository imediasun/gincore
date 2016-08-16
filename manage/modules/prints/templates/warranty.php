<?php

require_once __DIR__ . '/abstract_orders_template.php';

// гарантийный талон 
class warranty extends AbstractOrdersTemplate
{
    public function draw_one($object, $template='')
    {
        $print_html = '';

        $order = $this->all_configs['db']->query(
            'SELECT o.*, a.fio as a_fio, e.fio as engineer, w.title as wh_title, wa.print_address, wa.title as wa_title,
                        wa.print_phone, wa.title as wa_title, wag.address as accept_address
                FROM {orders} as o
                LEFT JOIN {users} as a ON a.id=o.accepter
                LEFT JOIN {users} as e ON e.id=o.engineer 
                LEFT JOIN {warehouses} as w ON w.id=o.wh_id
                LEFT JOIN {warehouses} as wa ON wa.id=o.accept_wh_id
                LEFT JOIN {warehouses_groups} as wag ON wa.group_id=wa.id
                WHERE o.id=?i', array($object))->row();

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
