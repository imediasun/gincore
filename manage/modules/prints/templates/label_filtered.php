<?php

require_once __DIR__ . '/abstract_template.php';

class label_filtered extends AbstractTemplate
{
    public function draw_one($object, $template='')
    {
        $query = '1=1';
        if (isset($_GET['whs']) && array_filter(explode(',', $_GET['whs'])) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND wgi.wh_id IN (?li)',
                array($query, explode(',', $_GET['whs'])));
        }
        if (isset($_GET['lcs']) && array_filter(explode(',', $_GET['lcs'])) > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND wgi.location_id IN (?li)',
                array($query, explode(',', $_GET['lcs'])));
        }

        if (isset($_GET['pid']) && $_GET['pid'] > 0) {
            $query = $this->all_configs['db']->makeQuery('?query AND g.id=?i', array($query, $_GET['pid']));
        }
        $products = $this->all_configs['db']->query(
            'SELECT g.barcode, g.title, wgi.serial, wgi.id as item_id,
                  o.number, o.parent_id, o.id, o.num
                FROM {warehouses_goods_items} as wgi
                LEFT JOIN {goods} as g ON wgi.goods_id=g.id
                LEFT JOIN {contractors_suppliers_orders} as o ON o.id=wgi.supplier_order_id
                WHERE ?query', array($query))->assoc();
        return $this->view->renderFile('prints/label_location', array(
            'products' => $products
        ));
    }
}