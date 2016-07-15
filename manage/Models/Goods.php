<?php
require_once __DIR__ . '/../Core/AModel.php';

/**
 * @property  MHistory History
 * @property  MCategories Categories
 * @property  MCategoryGoods CategoryGoods
 */
class MGoods extends AModel
{
    public $table = 'goods';
    public $uses = array(
        'History',
        'Categories',
        'CategoryGoods'
    );
    
    /**
     * @param $good
     * @param $mod_id
     */
    public function deleteProduct($good, $mod_id)
    {
        $product = $this->getByPk(intval($good['id']));
        if (!empty($product)) {
            $this->update(array(
                'deleted' => 1,
                'avail' => 0
            ), array('id' => $product['id']));
            $recycleBin = $this->Categories->getRecycleBin();
            if (!empty($recycleBin)) {
                $this->CategoryGoods->moveGoodTo(intval($good['id']), $recycleBin['id']);
            }

            $this->History->save('delete-product', $mod_id, $product['id']);
        }
    }

    /**
     * @param $good
     * @param $mod_id
     */
    public function restoreProduct($good, $mod_id)
    {
        $this->update(array(
            'deleted' => 0,
            'avail' => 1
        ), array('id' => intval($good['id'])));
        $this->CategoryGoods->deleteAll(array(
            'goods_id' => intval($good['id'])
        ));
        $this->History->save('restore-product', $mod_id, intval($good['id']));
    }

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
