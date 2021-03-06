<?php
require_once __DIR__ . '/../Core/AModel.php';

class MUsers extends AModel
{
    public $table = 'users';

    /**
     * @param string $permission
     * @return array
     */
    public function getWithPermission($permission = '') {
        $query = '1=1';
        if(!empty($permission)) {
            $query = $this->makeQuery('?query AND p.link=?', array($permission));
        }
        return $this->query("
            SELECT  u.*
            FROM ?t AS u
            LEFT JOIN {users_roles} r ON u.role=r.id
            LEFT JOIN {users_role_permission} rp ON rp.role_id=r.id
            LEFT JOIN {users_permissions} p ON p.id=rp.permission_id
            WHERE u.deleted=0 AND u.avail=1 AND ?query
            GROUP by u.id
            ", array($this->table, $query))->assoc();
    }
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
     * @param $permissions
     * @return mixed
     */
    public function getByPermission($permissions)
    {
        $query = $this->makeQuery('AND u.avail=1 AND u.deleted=0', array());
        return $this->query(
            '
            SELECT DISTINCT u.id, if(u.fio is NULL OR u.fio="",  u.login, u.fio) as name 
            FROM {users} as u, {users_permissions} as p, {users_role_permission} as r
            WHERE p.link in (?l) AND r.role_id=u.role AND r.permission_id=p.id ?query',
            array($permissions, $query))->assoc();
    }

    /**
     * @param $email
     * @return mixed
     */
    public function isExistByEmail($email)
    {
        return $this->query('SELECT id FROM {users} WHERE email=?string LIMIT 1', array($email))->bool();
    }

    /**
     * @param $login
     * @return bool
     */
    public function isExistByLogin($login){
        return $this->query('SELECT id FROM {users} WHERE login=?string LIMIT 1', array($login))->bool();
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
            'show_client_info',
            'show_only_his_orders',
            'use_percent_from_profit',
            'use_fixed_payment',
            'color'
        );
    }
}
