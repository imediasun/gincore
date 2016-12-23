<?php
require_once __DIR__ . '/../Core/AModel.php';

class MUsersRoles extends AModel
{
    public $table = 'users_roles';

    const ROLE_COURIER = 8;
    
    /**
     * @return mixed
     */
    public function getAllRoles()
    {
        return $this->query("
            SELECT r.id as role_id, p.id as per_id, r.name as role_name, r.avail, r.date_end, per.id,
              p.name as per_name, p.link, p.child, p.group_id
            FROM {users_roles} as r
            CROSS JOIN {users_permissions} as p
            LEFT JOIN (SELECT * FROM {users_role_permission})per ON per.role_id=r.id AND per.permission_id=p.id
            ORDER BY role_id, per_id
        ")->assoc();
    }

    /**
     * @return mixed
     */
    public function getActiveRoles()
    {

        return $this->query("
            SELECT r.id as role_id, p.id as per_id, r.name as role_name, r.avail, r.date_end, per.id,
              p.name as per_name, p.link, p.child, p.group_id
            FROM {users_roles} as r
            CROSS JOIN {users_permissions} as p
            LEFT JOIN (SELECT * FROM {users_role_permission})per ON per.role_id=r.id AND per.permission_id=p.id
            WHERE r.avail = 1
            ORDER BY role_id, per_id
        ")->assoc();
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'name',
            'avail',
            'date_end',
        );
    }
}
