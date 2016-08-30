<?php

require_once __DIR__ . '/abstract_template.php';

class price_list extends AbstractTemplate
{
    public function draw_one($object, $template='')
    {
        $print_html = '';
        $good = $this->all_configs['db']->query(
            'SELECT g.*
                FROM {goods} as g
                WHERE g.id=?i', array($object))->row();
        if ($good) {
            $this->editor = true;

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
            $print_html = $this->generate_template($arr, 'price_list');
        }
        return $print_html;
    }
}
