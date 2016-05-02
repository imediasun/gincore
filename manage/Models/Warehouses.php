<?php
require_once __DIR__ . '/../Core/AModel.php';

class MWarehouses extends AModel
{
    public $table = 'warehouses';

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
     * @param $groupId
     * @return \go\DB\Result
     */
    public function getByGroupId($groupId)
    {
        return $this->query("SELECT w.id as w_id,l.id as l_id FROM ?t as w "
            . "LEFT JOIN {warehouses_locations} as l ON l.wh_id = w.id "
            . "WHERE w.group_id = ?i AND w.type = 4", array($this->table, $groupId), 'row');
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
