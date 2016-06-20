<?php

require_once __DIR__ . '/abstract_template.php';

class label extends AbstractTemplate
{
    public function draw_one($object)
    {
        $product = $this->all_configs['db']->query('SELECT g.barcode, g.title, i.serial, i.id as item_id,
                  o.number, o.parent_id, o.id, o.num
                FROM {goods} as g, {warehouses_goods_items} as i, {contractors_suppliers_orders} as o
                WHERE i.goods_id=g.id AND i.id=?i AND o.id=i.supplier_order_id', array($object))->row();

        return $this->view->renderFile('prints/label', array(
            'product' => $product
        ));
    }
}