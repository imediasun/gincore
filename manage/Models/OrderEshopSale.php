<?php
require_once __DIR__ . '/Orders.php';

/**
 * @property  MWarehouses Warehouses
 */
class MOrderEshopSale extends MOrders
{
    public $uses = array(
        'Warehouses'
    );

    /**
     * @param $itemIds
     * @return array
     * @throws ExceptionWithMsg
     * @throws ExceptionWithURL
     */
    public function getAvailableItems($itemIds)
    {
        $items = $this->Warehouses->getAvailableItems($itemIds);

        if (!empty($items)) {
            foreach ($items as $k => $item) {
                // нет менеджера
                if ($item['user_id'] == 0) {
                    throw new ExceptionWithURL($this->all_configs['prefix'] . "products/create/" . $item['goods_id'] . "?error=manager#managers");
                }
            }
        }
        return $items;
    }
}
