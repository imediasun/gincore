<?php
require_once __DIR__ . '/../Core/AModel.php';

class MUsersMarked extends AModel
{
    public $table = 'users_marked';

    /**
     * @param $type
     * @return mixed
     */
    public function countMarkedAs($type)
    {
        return $this->all_configs['db']->query('
            SELECT COUNT(um.id) 
            FROM ?t um
            WHERE um.user_id=?i AND um.type=?
            ', array($this->table, $this->getUserId(), $type))->el();
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'object_id',
            'user_id',
            'type',
        );
    }
}
