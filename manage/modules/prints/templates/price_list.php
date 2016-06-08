<?php

require_once __DIR__ . '/abstract_template.php';

class price_list extends AbstractTemplate
{
    public function draw_one($object)
    {
        $print_html = '';
        $good = $this->all_configs['db']->query(
            'SELECT g.*
                FROM {goods} as g
                WHERE g.id=?i', array($object))->row();
        if ($good) {
            $this->editor = true;

            $arr = array(
                'id' => array('value' => intval($good['id']), 'name' => l('ID товара')),
                'title' => array('value' => intval($good['title']), 'name' => l('Название товара')),
                'price' => array('value' => intval($good['price']), 'name' => l('Цена')),
                'article' => array('value' => intval($good['article']), 'name' => l('Артикул')),
                'barcode' => array('value' => intval($good['barcode']), 'name' => l('Штрих код')),
                'company' => array(
                    'value' => htmlspecialchars($this->all_configs['settings']['site_name']),
                    'name' => l('Название компании')
                ),
            );
            $print_html = $this->generate_template($arr, 'price_list');
        }
        return $print_html;
    }
}
