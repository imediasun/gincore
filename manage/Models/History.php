<?php
require_once __DIR__ . '/../Core/AModel.php';

class MHistory extends AModel
{
    public $table = 'changes';

    /**
     * @param      $work
     * @param      $mapId
     * @param      $objectId
     * @param null $change
     * @param null $changeId
     * @return int
     */
    public function save($work, $mapId, $objectId, $change = null, $changeId = null)
    {
        $params = array(
            $this->getUserId(),
            $work,
            $mapId,
            $objectId,
        );
        $fields = $this->makeQuery('user_id=?i, work=?, map_id=?i, object_id=?i', $params);
        if (!empty($change)) {
            $fields = $this->makeQuery('?q, `change`=?', array($fields, $change));
        }
        if (!empty($changeId)) {
            $fields = $this->makeQuery('?q, `change_id`=?i', array($fields, $changeId));
        }
        return $this->query('INSERT INTO ?t SET ?q', array($this->table, $fields))->ar();
    }

    /**
     * @param $work
     * @param $mapId
     * @param $objectId
     * @return array
     */
    public function getChanges($work, $mapId, $objectId)
    {
        return $this->query(
            'SELECT u.login, u.email, u.fio, u.phone, ch.change, ch.date_add 
              FROM ?t as ch
              LEFT JOIN {users} as u ON u.id=ch.user_id 
              WHERE ch.object_id=?i AND ch.map_id=?i AND work=? 
              ORDER BY ch.date_add DESC',
            array($this->table, $objectId, $mapId, $work))->assoc();
    }

    /**
     * @param $objectId
     * @return mixed
     */
    public function getProductsManagersChanges($objectId)
    {
        $modIds = array(
            'update-goods-title-image',
            'update-goods-image-prio',
            'edit-product-avail',
            'edit-ym_id',
            'edit-warranties-add',
            'add-image-goods',
            'add-market-category',
            'add-image-goods',
            'export-order',
            'create-goods',
            'add-manager',
            'delete-goods-image',
            'edit-goods',
            'update-goods'
        );
        return $this->all_configs['db']->query(
            'SELECT c.date_add, c.work, u.login, u.fio, c.change 
              FROM ?t as c
              LEFT JOIN (SELECT id, login, fio FROM {users})u ON u.id=c.user_id
              WHERE c.work in (?l) AND c.object_id=?i 
              ORDER BY c.date_add DESC',
            array($this->table, $modIds, $objectId))->assoc();
    }

    /**
     * @param $work
     * @param $mapId
     * @return array
     */
    public function getChangesByModId($work, $mapId)
    {
        return $this->query(
            'SELECT u.login, u.email, u.fio, u.phone, ch.change, ch.date_add 
              FROM ?t as ch
              LEFT JOIN {users} as u ON u.id=ch.user_id 
              WHERE ch.map_id=?i AND work=? 
              ORDER BY ch.date_add DESC',
            array($this->table, $mapId, $work))->assoc();
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'user_id',
            'date_add',
            'work',
            'map_id',
            'object_id',
            'change',
            'change_id'
        );

    }

}
