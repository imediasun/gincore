<?php

class Role
{
    const ALL = 0;
    const ONLY_ACTIVE = 1;

    var $permissions; // Массив доступных свойст
    var $all_configs;
    var $ifadmin;

    /**
     * Role constructor.
     * @param $all_configs
     * @param $table_prefix
     */
    function __construct($all_configs, $table_prefix)
    {
        $this->all_configs = $all_configs;

        /*require_once($sitepath.'shop/model.class.php');
        $model = new Model;*/

        // если админ
        $auth = new Auth($this->all_configs['db']);
        $auth->cookie_session_name = $table_prefix . 'cid';
        $this->ifadmin = $auth->IfAuth($all_configs);

        if (isset($_SESSION) && isset($_SESSION['role']) && $this->ifadmin) {
            $permissions = $this->all_configs['db']->query(
                'SELECT r.name AS role_name, p.name AS per_name, p.link, r.date_end
                FROM {users_permissions} as p, {users_roles} as r, {users_role_permission} as rp
                WHERE r.id=?i AND r.avail=1 AND rp.role_id=r.id AND p.id=rp.permission_id',
                array(intval($_SESSION['role'])))->assoc();

            $today = new DateTime("now");
            $f_today = $today->format('Y-m-d'); //formated today = '2011-03-09'


            if (count($permissions) > 0) {
                $sql_date = substr($permissions[0]['date_end'], 0, 10); //'2008-10-17'
                if (intval($sql_date) < 1 || $f_today <= $sql_date) {
                    return $this->permissions = $permissions;
                }
            }
        }
    }

    /**
     * @return bool
     */
    public function is_active()
    {
        if (isset($_SESSION) && isset($_SESSION['role']) && intval($_SESSION['role']) > 0 && $this->ifadmin == true) {
            $id = $this->all_configs['db']->query('
                SELECT r.id FROM {users_roles} as r WHERE r.id=?i AND r.avail=1 ',
                array(intval($_SESSION['role'])))->el();

            if ($id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param          $arr
     * @param int $all
     * @return array
     */
    public function get_users_by_permissions($arr, $all = self::ALL)
    {
        $users = array();
        $arr = (array)$arr;
        $query = $all == self::ALL ? '' : $this->all_configs['db']->makeQuery('AND u.avail=1 AND u.deleted=0');
        if (count($arr) > 0) {
            $users = (array)$this->all_configs['db']->query('SELECT u.*, CONCAT(u.fio, " ", u.login) as name
                FROM {users} as u, {users_permissions} as p, {users_role_permission} as l
                WHERE p.link IN (?l) AND u.role=l.role_id AND l.permission_id=p.id ?query',
                array($arr, $query))->assoc('id');
        }

        return $users;
    }

    /**
     * @param null $perm
     * @return bool
     */
    public function hasPrivilege($perm = null)
    {
        if (count($this->permissions) < 1) {
            return false;
        }

        foreach ($this->permissions as $permission) {
            if ($permission['link'] == $perm || $permission['link'] == 'site-administration') {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $permissionId
     * @return bool
     */
    public function isSuperuserPermission($permissionId)
    {
        $count = $this->all_configs['db']->query("SELECT count(*) FROM {users_permissions} WHERE link in ('edit-users', 'site-administration') AND id=?i",
            array(intval($permissionId)))->el();
        return $count > 0;
    }

    /**
     * @param $roleId
     * @return bool
     */
    public function isLastSuperuserRole($roleId)
    {
        $count = $this->all_configs['db']->query('SELECT count(*)
                FROM {users_permissions} as p, {users_role_permission} as l
                WHERE p.link IN (?l) AND l.permission_id=p.id AND l.role_id=?',
            array(array('edit-users', 'site-administration'), $roleId))->el();
        return $count == 1;
    }

    /**
     * @param $roleId
     * @return bool
     */
    public function isSuperuserRole($roleId)
    {
        $count = $this->all_configs['db']->query('SELECT count(*)
                FROM {users_permissions} as p, {users_role_permission} as l
                WHERE p.link IN (?l) AND l.permission_id=p.id AND l.role_id=?',
            array(array('edit-users', 'site-administration'), $roleId))->el();
        return $count > 0;
    }

    /**
     * @return bool
     */
    public function isLastSuperuser($userId)
    {
        $users = $this->all_configs['db']->query('SELECT u.id
                FROM {users} as u, {users_permissions} as p, {users_role_permission} as l
                WHERE p.link IN (?l) AND l.permission_id=p.id AND u.role=l.role_id',
            array(array('edit-users', 'site-administration')))->assoc('id');
        if (!in_array($userId, array_keys($users))) {
            return false;
        }
        return count($users) == 1;
    }
}