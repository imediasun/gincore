<?php
require_once __DIR__ . '/../Core/AModel.php';

class MGoods extends AModel
{
    public $table = 'goods';

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'action',
            'material',
            'weight',
            'size',
            'price',
            'old_price',
            'author',
            'title',
            'secret_title',
            'content',
            'avail',
            'date_add',
            'url',
            'prio',
            'related',
            'type',
            'warranties',
            'no_warranties',
            'qty_store',
            'qty_wh',
            'article',
            'code_1c',
            'wait',
            'rating',
            'votes',
            'trade',
            'barcode',
            'price_purchase',
            'price_wholesale',
            'foreign_warehouse',
            'foreign_warehouse_auto',
            'page_content',
            'page_title',
            'page_keywords',
            'page_description',
            'image_set',
            'search_keywords',
            'search_keywords_categories',
            'serach_clicks',
            'search_weight',
            'deleted'
        );
    }
}
