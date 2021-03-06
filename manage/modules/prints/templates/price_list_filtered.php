<?php

require_once __DIR__ . '/abstract_template.php';

class price_list_filtered extends AbstractTemplate
{
    public function draw_one($object, $template='')
    {
        $print_html = '';
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
            $query = $this->all_configs['db']->makeQuery('?query AND g.id=?i', array($_GET['pid']));
        }
        $goods = $this->all_configs['db']->query(
            'SELECT g.*
                FROM {warehouses_goods_items} as wgi
                LEFT JOIN {goods} as g ON wgi.goods_id=g.id
                WHERE ?query GROUP by g.id', array($query))->assoc();
        if ($goods) {
            $this->editor = false;

            foreach ($goods as $good) {
                $arr = array(
                    'title' => array('value' => h($good['title']), 'name' => l('Название товара')),
                    'price' => array(
                        'value' => ($good['price'] / 100) . '&nbsp;' . viewCurrency(),
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
