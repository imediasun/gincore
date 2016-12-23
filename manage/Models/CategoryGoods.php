<?php
require_once __DIR__ . '/../Core/AModel.php';

class MCategoryGoods extends AModel
{
    public $table = 'category_goods';

    /**
     * @param $goodId
     * @param $categoryId
     * @return bool|int
     */
    public function moveGoodTo($goodId, $categoryId)
    {
        $this->deleteAll(array(
            'goods_id' => $goodId
        ));
        return $this->insert(array(
            'goods_id' => $goodId,
            'category_id' => $categoryId
        ));
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'goods_id',
            'category_id',
        );
    }
}
