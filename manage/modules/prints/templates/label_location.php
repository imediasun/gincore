<?php

require_once __DIR__ . '/abstract_template.php';

class label_location extends AbstractTemplate
{
    public function draw_one($object)
    {
        $products = $this->all_configs['db']->query(
            'SELECT g.barcode, g.title, wgi.serial, wgi.id as item_id,
                  o.number, o.parent_id, o.id, o.num
                FROM {warehouses_goods_items} as wgi
                LEFT JOIN {goods} as g ON wgi.goods_id=g.id
                LEFT JOIN {contractors_suppliers_orders} as o ON o.id=wgi.supplier_order_id
                WHERE wgi.location_id=?i', array($object))->assoc();
        return $this->view->renderFile('prints/label_location', array(
            'products' => $products
        ));
    }
}