<?php
require_once __DIR__ . '/../Core/AModel.php';

class MUsers extends AModel
{
    public $table = 'users';

    /**
     * @param string $sort
     * @return mixed
     */
    public function getUsers($sort = '')
    {

        $roles = $this->query("
            SELECT r.name AS role_name, p.name AS per_name, r.id as role_id, p.link, p.id as per_id,
            p.child, r.avail as role_avail, r.date_end, u.*
            FROM {users} AS u
            LEFT JOIN (
            SELECT * FROM {users_roles}
            )r ON u.role=r.id
            LEFT JOIN (
            SELECT role_id, permission_id FROM {users_role_permission}
            )rp ON rp.role_id=r.id
            LEFT JOIN (
            SELECT id, name, link, child FROM {users_permissions}
            )p ON p.id=rp.permission_id
            WHERE u.deleted=0
            ORDER BY u.avail DESC," . $sort . " u.id
            ")->assoc();

        return $roles;
    }

    /**
     * @return array
     */
    public function columns()
    {
        return array(
            'id',
            'cid',
            'state',
            'login',
            'pass',
            'email',
            'fio',
            'avatar',
            'uxtreg',
            'uxt',
            'is_adm',
            'is_1',
            'is_2',
            'is_3',
            'is_4',
            'role',
            'avail',
            'position',
            'phone',
            'auth_cert_only',
            'auth_cert_serial',
            'deleted',
            'sms_code',
            'rating',
            'blocked_by_tariff',
            'send_over_email',
            'send_over_sms',
            'salary_from_repair',
            'salary_from_sale',
        );
    }
}
