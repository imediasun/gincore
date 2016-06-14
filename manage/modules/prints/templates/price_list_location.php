<?php

require_once __DIR__ . '/abstract_template.php';

class price_list_location extends AbstractTemplate
{
    public function draw_one($object)
    {
        $print_html = '';
        $goods = $this->all_configs['db']->query(
            'SELECT g.*
                FROM {warehouses_goods_items} as wgi
                LEFT JOIN {goods} as g ON wgi.goods_id=g.id
                WHERE wgi.location_id=?i GROUP BY g.id', array($object))->assoc();
        if ($goods) {
            $this->editor = false;

            foreach ($goods as $good) {
                $arr = array(
                    'title' => array('value' => h($good['title']), 'name' => l('Название товара')),
                    'price' => array(
                        'value' => intval($good['price'] / 100) . '&nbsp;' . viewCurrency(),
                        'name' => l('Цена')
                    ),
                    'article' => array('value' => h($good['article']), 'name' => l('Артикул')),
                    'barcode' => array('value' => h($good['barcode']), 'name' => l('Штрих код')),
                    'company' => array(
                        'value' => h($this->all_configs['settings']['site_name']),
                        'name' => l('Название компании')
                    ),
                );

                $print_html .= $this->generate_template($arr, 'price_list');
            }
        }
        return $print_html;
    }
}
