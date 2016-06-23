<?php

require_once __DIR__ . '/abstract_orders_template.php';

class order_barcode extends AbstractOrdersTemplate
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
            $print_html = $this->view->renderFile('prints/order_barcode', array(
                'order' => $order
            ));
        }
        return $print_html;
    }
}
