<?php

require_once __DIR__ . '/abstract_orders_template.php';

// чек 
class invoice extends AbstractOrdersTemplate
{
    public function draw_one($object, $template='')
    {
        $print_html = '';
        $type = $this->all_configs['db']->query("SELECT type FROM {orders} WHERE id = ?i", array($object), 'el');
        $order = array();
        if ($type == 0) {
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

            $this->editor = true;
            $arr = $this->getVariables($order, $goods);
            $print_html = $this->generate_template($this->addUsersFieldsValues($order, $arr), 'invoice');
        }
        return $print_html;
    }
}
