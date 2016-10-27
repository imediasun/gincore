<?php
require_once __DIR__ . '/../Core/AModel.php';

class MWarehouses extends AModel
{
    public $table = 'warehouses';
    const WH_CLIENT_TYPE = 4;

    /**
     * @return mixed
     * @throws ExceptionWithMsg
     */
    public function getWriteOffWarehouseId()
    {
        $warehouse = $this->all_configs['db']->query('SELECT * FROM ?t  WHERE `type` = 2 LIMIT 1',
            array($this->table))->row();
        if (empty($warehouse)) {
            throw new ExceptionWithMsg(l('Склад списания не найден'));
        }
        return $warehouse['id'];
    }

    /**
     * @return \go\DB\Result
     */
    public function getClientWarehouses()
    {
        return $this->query("SELECT w.id as w_id,l.id as l_id FROM ?t as w "
            . "LEFT JOIN {warehouses_locations} as l ON l.wh_id = w.id "
            . "WHERE w.type = ?i LIMIT 1", array($this->table, self::WH_CLIENT_TYPE) , 'row');
    }

    /**
     * @param $warehouseId
     * @return mixed
     * @throws ExceptionWithMsg
     */
    public function getLocationId($warehouseId)
    {
        $location = $this->all_configs['db']->query('SELECT * FROM {warehouses_locations}  WHERE wh_id=?i LIMIT 1',
            array($warehouseId))->row();
        if (empty($location)) {
            throw new ExceptionWithMsg(l('Локация списания не найдена'));
        }
        return $location['id'];
    }

    public function getLocations($warehouseId)
    {
        $location = array();
        $result = $this->all_configs['db']->query('SELECT * FROM {warehouses_locations}  WHERE wh_id=?i',
            array($warehouseId))->assoc();
        if (!empty($result)) {
            foreach ($result as $row){
                $location[$row['id']] = $row;
            }
        }
        return $location;
    }

    /**
     * @param $itemIds
     * @return array
     */
    public function getAvailableItems($itemIds)
    {
        if(empty($itemIds)) {
            return array();
        }
        return $this->query('SELECT i.wh_id, i.goods_id, i.id, m.user_id, i.price as price
                    FROM ?t as w, {warehouses_goods_items} as i
                    LEFT JOIN {users_goods_manager} as m ON m.goods_id=i.goods_id
                    WHERE i.id IN (?li) AND w.id=i.wh_id AND w.consider_all=?i AND i.order_id IS NULL GROUP BY i.id',
            array($this->table, $itemIds, 1))->assoc();
    }
    
    /**
     * @param $itemIds
     * @return array
     */
    public function getAvailableItemsByGoodsId($itemIds, $group_by_warehouse = false)
    {
        if(empty($itemIds)) {
            return array();
        }
        $result = $this->query('SELECT i.wh_id, i.goods_id, i.id, m.user_id, i.price as price, i.serial, i.location_id, i.order_id
                    FROM ?t as w, {warehouses_goods_items} as i
                    LEFT JOIN {users_goods_manager} as m ON m.goods_id=i.goods_id
                    WHERE i.goods_id IN (?li) AND w.id=i.wh_id AND w.consider_all=?i AND i.order_id IS NULL GROUP BY i.id',
            array($this->table, $itemIds, 1))->assoc();

        if(!$group_by_warehouse) {
            return $result;
        } else {
            $grouped_result = array();
            foreach ($result as $row){
                $grouped_result[$row['wh_id']]['items'][] = $row;
            }
            return $grouped_result;
        }

    }

    public function getAvailableItemsByGoodsIdWarehouse($itemIds, $id_warehouse, $limit = 0)
    {
        if(empty($itemIds) || empty($id_warehouse)) {
            return array();
        }

        $result = $this->query('SELECT i.wh_id, i.goods_id, i.id, m.user_id, i.price as price, i.serial, i.location_id, i.order_id
                    FROM ?t as w, {warehouses_goods_items} as i
                    LEFT JOIN {users_goods_manager} as m ON m.goods_id=i.goods_id
                    WHERE i.goods_id IN (?li) AND i.wh_id=?i AND w.id=i.wh_id AND w.consider_all=?i AND i.order_id IS NULL GROUP BY i.id'.
                    ($limit ? ' LIMIT '.$limit : ''),
            array($this->table, $itemIds, $id_warehouse, 1))->assoc();

        return $result;
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'consider_all',
            'consider_store',
            'code_1c',
            'title',
            'print_address',
            'print_phone',
            'type',
            'group_id',
            'type_id',
        );
    }
}
