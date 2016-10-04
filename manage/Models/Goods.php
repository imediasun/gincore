<?php
require_once __DIR__ . '/../Core/AModel.php';

/**
 * @property  MHistory       History
 * @property  MCategories    Categories
 * @property  MCategoryGoods CategoryGoods
 */
class MGoods extends AModel
{
    const PERCENT_FROM_PROFIT = 0;
    const FIXED_PAYMENT = 1;
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

            $this->History->save('delete-product', $mod_id, $product['id'], l('Удален') . ' ' . $product['title']);
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
     * @param $goodId
     * @return bool
     */
    public function isUsed($goodId)
    {
        $onWarehouses = $this->query('SELECT count(*) FROM {warehouses_goods_items} WHERE goods_id=?i',
            array($goodId))->el();
        $inOrders = $this->query('SELECT count(*) FROM {contractors_suppliers_orders} WHERE goods_id=?i',
            array($goodId))->el();
        $inClientOrders = $this->query('SELECT count(*) FROM {orders_goods} WHERE goods_id=?i',
            array($goodId))->el();
        return $onWarehouses || $inOrders || $inClientOrders;
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
            'deleted',
            'vendor_code',
            'use_minimum_balance',
            'minimum_balance',
            'use_automargin',
            'automargin_type',
            'automargin',
            'wholesale_automargin_type',
            'wholesale_automargin',
            'percent_from_profit',
            'fixed_payment'
        );
    }

    public function getPayments($id)
    {
        $product = $this->getByPk($id);
        $result = array();
        if(empty($product)) {
            return $result;
        }
        if(!empty($product['fixed_payment']) || !empty($product['percent_from_profit'])) {
            $result = array(
                'fixed_payment' =>$product['fixed_payment'],
                'percent_from_profit' => $product['percent_from_profit']
            );
        } else {

        }
        return $result;
    }

}
