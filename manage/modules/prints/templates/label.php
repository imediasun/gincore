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

        $result = '';
        if ($product) {
            $result .= '<div class="label-box">';

            $src = $this->all_configs['prefix'] . 'print.php?bartype=sn&barcode=' . suppliers_order_generate_serial($product);
            $result .= '<div class="label-box-code"><img src="' . $src . '" alt="S/N" title="S/N" /></div>';

            $result .= '<div class="label-box-title">' . htmlspecialchars($product['title']) . '</div>';

            $num = $this->all_configs['suppliers_orders']->supplier_order_number($product, null, false);
            $result .= '<div class="label-box-order">' . $num . '</div>';

            $result .= '</div>';
        }
        return $result;
    }
}