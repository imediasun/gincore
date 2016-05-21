<?php
require_once __DIR__ . '/../Core/AModel.php';

class MUsersRolePermission extends AModel
{
    public $table = 'users_role_permission';

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'role_id',
            'permission_id',

        );
    }
}

