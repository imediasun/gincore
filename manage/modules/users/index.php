<?php

require_once __DIR__ . '/../../Response.php';
require_once __DIR__ . '/../../FlashMessage.php';
require_once __DIR__ . '/../../View.php';
require_once __DIR__ . '/../../Tariff.php';

$modulename[80] = 'users';
$modulemenu[80] = l('Сотрудники');
$moduleactive[80] = !$ifauth['is_2'];

class users
{
    /** @var View */
    protected $view;
    private $mod_submenu;
    protected $all_configs;

    /**
     * users constructor.
     * @param $all_configs
     */
    function __construct($all_configs)
    {
        $this->mod_submenu = self::get_submenu();
        $this->all_configs = &$all_configs;
        $this->view = new View($all_configs);
        global $input_html, $ifauth;

        if (!$this->all_configs['oRole']->hasPrivilege('edit-users')) {
            return $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p  class="text-error">' . l('У Вас нет прав для просмотра пользователей') . '</p></div>';
        }

        if ($ifauth['is_2']) {
            return false;
        }


        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
            return true;
        }

        // если отправлена форма изменения продукта
        if (count($_POST) > 0) {
            $this->check_post($_POST);
        }

        $input_html['mcontent'] = $this->gencontent();
    }

    /**
     * @return bool
     */
    private function can_show_module()
    {
        return ($this->all_configs['oRole']->hasPrivilege('edit-users'));
    }

    /**
     * @param      $width
     * @param      $height
     * @param      $out_w
     * @param      $out_h
     * @param bool $no_stretch
     * @return array
     */
    private function count_resize_vars($width, $height, $out_w, $out_h, $no_stretch = false)
    {
        $vars = array();
        $vertical = true;
        if ($width >= $height) {
            $vertical = false;
        }
        if ($no_stretch) {
            $out_w = $out_w > $width ? $width : $out_w;
            $out_h = $out_h > $height ? $height : $out_h;
        }
        if (!$out_h) {
            $out_h = $out_w * ($height / $width);
        }
        if ($width > $out_w || $height > $out_h) {
            if (!$vertical) {
                $h = $out_h;
                $w = $out_h * ($width / $height);
                if ($w < $out_w) {
                    $w = $out_w;
                    $h = $out_w * ($height / $width);
                }
            } else {
                $w = $out_w;
                $h = $out_w * ($height / $width);
                if ($h < $out_h) {
                    $h = $out_h;
                    $w = $out_h * ($width / $height);
                }
            }
            $vars['resize'] = array(
                'width' => $w,
                'height' => $h
            );
            $vars['crop'] = array(
                'width' => $out_w,
                'height' => $out_h,
                'start_width' => ($w - $out_w) / 2,
                'start_height' => ($h - $out_h) / 2
            );
        } else {
            $vars['resize'] = '';
            $vars['crop'] = '';
        }
        return $vars;
    }

    /**
     * @param $image_path
     * @param $data
     */
    public function resize_image($image_path, $data)
    {
        if ($data) {
            $img = new LiveImage($image_path);
            $img->resize($data['width'], $data['height'], false);
            $img->output(null, $image_path);
            chmod($image_path, 0777);
        }
    }

    /**
     * @param $image_path
     * @param $crop
     */
    public function crop_image($image_path, $crop)
    {
        if ($crop) {
            $img = new LiveImage($image_path);
            $img->crop($crop['width'], $crop['height'], $crop['start_width'], $crop['start_height']);
            $img->output(null, $image_path);
            chmod($image_path, 0777);
        }
    }

    /**
     * @param $file
     * @return string
     */
    public function new_filename($file)
    {
        $file_parts = pathinfo($file);
        return md5(microtime(true)) . '.' . $file_parts['extension'];
    }

    /**
     *
     */
    private function ajax()
    {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $mod_id = $this->all_configs['configs']['users-manage-page'];
        $data = array(
            'state' => false
        );

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        // загрузка аватарки
        if ($act == 'upload_avatar') {
            include_once 'qqfileuploader.php';
            include_once 'class_image.php';
            $uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
            if ($uid) {
                $uploader = new qqFileUploader(array('jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG'), 3145728);
                $path_avatars = $this->all_configs['path'] . $this->all_configs['configs']['users-avatars-path'];
                $result = $uploader->handleUpload($path_avatars);
                if ($result['success']) {
                    $file = $this->new_filename($result['file']);
                    rename($path_avatars . $result['file'], $path_avatars . $file);
                    $result['file'] = $file;
                    $image_path = $path_avatars . $file;
                    $out_w = 75;
                    $out_h = 75;
                    $image_info = getimagesize($image_path);
                    $image_vars = $this->count_resize_vars($image_info[0], $image_info[1], $out_w, $out_h, false);
                    $this->resize_image($image_path, $image_vars['resize']);
                    $this->crop_image($image_path, $image_vars['crop']);
                    $result['success'] = true;
                    $result['path'] = $this->all_configs['prefix'] . $this->all_configs['configs']['users-avatars-path'];
                    $result['avatar'] = $result['path'] . $file;
                    $result['msg'] = '';
                    $result['uid'] = $uid;
                    $this->all_configs['db']->query("UPDATE {users} SET avatar = ? "
                        . "WHERE id = ?i", array($file, $uid));
                } else {
                    $result['filename'] = '';
                    $result['path'] = '';
                    $result['msg'] = '';
                    $result['file'] = '';
                }
                echo json_encode($result);
                exit;
            }
        }

        if($act == 'ratings') {
            $ratings = $this->all_configs['db']->query('SELECT ur.*, f.comment '
            .' FROM {users_ratings} ur'
            .' JOIN {feedback} f ON ur.order_id=f.order_id'
            .' WHERE user_id=?i ORDER BY created_at DESC',
                array($user_id))->assoc();
            if (empty($ratings)) {
                $data = array(
                    'state' => false,
                    'message' => l('Записи об отзывах клиентов не найдены')
                );
            } else {
                $data = array(
                    'state' => true,
                    'content' => $this->view->renderFile('users/ratings', array(
                        'ratings' => $ratings,
                    ))
                );
            }
            Response::json($data);
        }
        // проверка доступа
        if ($this->can_show_module() == false) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => l('Нет прав'), 'state' => false));
            exit;
        }

        if ($act == 'delete_user') {
            $uid = isset($_POST['uid']) ? (int)$_POST['uid'] : 0;
            $result = array(
                'success' => false,
                'msg' => l('Что-то пошло не так')
            );
            if ($uid && $uid != $user_id) {
                if ($this->all_configs['oRole']->isLastSuperuser(intval($uid))) {
                    FlashMessage::set(l('Не возможно удалить последнего суперпользователя'), FlashMessage::DANGER);
                    $result['msg'] = l('Не возможно удалить последнего суперпользователя');
                } else {
                    if ($this->all_configs['db']->query("UPDATE {users} SET deleted = 1 " . "WHERE id = ?i",
                        array($uid))->ar()
                    ) {
                        $result['success'] = true;
                        FlashMessage::set(l('Пользователь удален'));
                        $result['uid'] = $uid;
                    }
                }

            } else {
                $result['msg'] = l('Пользователь не найден');
            }
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($result);
            exit;
        }

        if ($act == 'edit-user') {
            $result = array(
                'state' => false,
                'message' => l('Что-то пошло не так')
            );
            $uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
            try {
                if (empty($_GET['uid']) || empty($uid)) {
                    $result['message'] = l('Пользователь не найден');
                }

                $result['html'] = $this->editUserForm($uid);
                unset($result['message']);
                $result['state'] = true;
            } catch (Exception $e) {
                $result['message'] = $e->getMessage();
            }
            Response::json($result);
        }
        if ($act == 'update-user') {
            $result = array(
                'state' => false,
                'message' => l('Что-то пошло не так')
            );
            $uid = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            try {
                if (empty($_POST['id']) || empty($uid)) {
                    $result['message'] = l('Пользователь не найден');
                }

                $this->updateUser($uid, $_POST, $mod_id);
                unset($result['message']);
                $result['state'] = true;
            } catch (Exception $e) {
                $result['message'] = $e->getMessage();
            }
            Response::json($result);
        }

        // изменить пароль
        if ($act == 'change-admin-password') {
            if (isset($_POST['pk']) && is_numeric($_POST['pk']) && isset($_POST['value'])) {
                $ar = $this->all_configs['db']->query('UPDATE {users} SET pass=?
                    WHERE id=?i LIMIT 1', array($_POST['value'], $_POST['pk']))->ar();

                if (intval($ar) > 0) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                        array($user_id, 'update-password', $mod_id, $_POST['pk']));
                }

                header("Content-Type: application/json; charset=UTF-8");
                exit;
            }
        }

        exit;
    }

    /**
     * @param $userId
     * @return string
     * @throws Exception
     */
    protected function editUserForm($userId)
    {
        $permissions = $this->get_all_roles();
        $user = $this->all_configs['db']->query('SELECT * FROM {users} WHERE id=?i', array($userId))->row();
        $user['cashboxes'] = $this->all_configs['db']->query('SELECT cashbox_id FROM {cashboxes_users} WHERE user_id=?i',
            array($userId))->col();
        $user['cashboxes'] = $this->all_configs['db']->query('SELECT cashbox_id FROM {cashboxes_users} WHERE user_id=?i',
            array($userId))->col();
            $warehouses = $this->all_configs['db']->query('SELECT wh_id, location_id FROM {warehouses_users} WHERE main=1 AND user_id=?i',
            array($userId))->row();
        list($user['warehouse'], $user['location']) = array('', '');
        if(!empty($warehouses)) {
            $user['warehouse'] = $warehouses['wh_id'];
            $user['location'] = $warehouses['location_id'];
        }
        $user['warehouses'] = $this->all_configs['db']->query('SELECT wh_id FROM {warehouses_users} WHERE main=0 AND user_id=?i',
            array($userId))->col();
        if (empty($user)) {
            throw new Exception(l('Пользователь не найден'));
        }
        $roles = array();
        $yet = 0;
        foreach ($permissions as $permission) {
            if ($yet === 0) {
                $yet = $permission['role_id'];
                $roles[$permission['role_id']] = $permission['role_name'];
            } elseif ($yet != $permission['role_id']) {
                $roles[$permission['role_id']] = $permission['role_name'];
            }
        }
        return $this->createUserForm($user, $roles, true);
    }

    /**
     * @param $post
     */
    function check_post($post)
    {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';

        $mod_id = $this->all_configs['configs']['users-manage-page'];

        if (isset($post['change-roles'])) { // изменяем роли пользователям

            foreach ($post['roles'] as $uid => $role) {
                $avail = 0;
                $cert_avail = 0;
                if (isset($post['avail_user'][$uid])) {
                    $avail = 1;
                }
                if (isset($post['auth_cert_only'][$uid])) {
                    $cert_avail = 1;
                }
                if (intval($uid) > 0) {
                    $isLastSuperuser = $this->all_configs['oRole']->isLastSuperuser(intval($uid));
                    if (!$this->all_configs['oRole']->isSuperuserRole(intval($role)) && $isLastSuperuser) {
                        FlashMessage::set(l('Не возможно изменить роль последнего суперпользователя'),
                            FlashMessage::DANGER);
                        continue;
                    }
                    $isBlocked = !$avail ? USER_DEACTIVATED_BY_TARIFF_MANUAL: USER_ACTIVATED_BY_TARIFF;
                    if($isBlocked && $isLastSuperuser) {
                        FlashMessage::set(l('Не возможно блокировать последнего суперпользователя'),
                            FlashMessage::DANGER);
                        $isBlocked = 0;
                        $avail = 1;
                    }
                    if($isBlocked && $uid == $user_id) {
                        FlashMessage::set(l('Нельзя заблокировать текущую учетную запись'),
                            FlashMessage::DANGER);
                        $isBlocked = 0;
                        $avail = 1;
                    }
                    $ar = $this->all_configs['db']->query('UPDATE {users} SET role=?i, avail=?i, fio=?, position=?, phone=?, email=?,
                            auth_cert_serial=?, auth_cert_only=?, blocked_by_tariff=?i
                        WHERE id=?i',
                        array(
                            intval($role),
                            $avail,
                            trim($post['fio'][$uid]),
                            trim($post['position'][$uid]),
                            trim($post['phone'][$uid]),
                            trim($post['email'][$uid]),
                            trim($post['auth_cert_serial'][$uid]),
                            $cert_avail,
                            $isBlocked,
                            intval($uid)
                        ))->ar();
                    if (intval($ar) > 0) {
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                            array($user_id, 'update-user', $mod_id, intval($uid)));
                    }
                }
            }
        } elseif (isset($post['create-roles'])) { // изменяем возможности ролям

            foreach ($post['exist-box'] as $role_id => $s) {

                $exist = explode(',', $s);

                foreach ($post['permissions'] as $uid => $on) {
                    $id = explode('-', $uid);
                    if (intval($role_id) != intval($id[0])) {
                        continue;
                    }

                    foreach ($exist as $k => $v) {
                        if (intval($v) == intval($id[1])) {
                            unset($exist[$k]);
                        }
                    }

                    $i = array_search(intval($id[1]), explode(',', $s));

                    if ($i === false && intval($id[0]) > 0 && intval($id[1]) > 0) {
                        //unset($exist[$i]);
                        $this->all_configs['db']->query('INSERT INTO {users_role_permission} (role_id, permission_id) VALUES (?i, ?i)',
                            array(intval($id[0]), intval($id[1]))
                        );
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                            array($user_id, 'add-to-role-per', $mod_id, intval($id[0])));
                    }
                }
                if (count($exist) > 0) {
                    foreach ($exist as $v) {
                        if ($this->all_configs['oRole']->isSuperuserPermission(intval($v)) && $this->all_configs['oRole']->isLastSuperuserRole($role_id)) {
                            FlashMessage::set(l('Не возможно удалить права суперюзера', FlashMessage::DANGER));
                        } else {
                            $this->all_configs['db']->query('DELETE FROM {users_role_permission} WHERE role_id=?i AND permission_id=?i',
                                array(intval($role_id), intval($v)));
                            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                                array($user_id, 'delete-from-role-per', $mod_id, intval($role_id)));
                        }
                    }
                }
                $date = '0000-00-00 00:00:00';
                $active = '0';
                //if ( $post['date_end'][$role_id])
                $date_from_post = strtotime($post['date_end'][$role_id]);
                $date = $date_from_post > 0 ? date('Y-m-d H:i:s', $date_from_post) : '0000-00-00 00:00:00';

                if (isset($post['active'][$role_id]) && $post['active'][$role_id]) {
                    $active = 1;
                }

                if (!$active && $this->all_configs['oRole']->isLastSuperuserRole(intval($role_id))) {
                    FlashMessage::set(l('Не возможно удалить последнюю роль с правами суперюзера'),
                        FlashMessage::DANGER);
                } else {
                    $ar = $this->all_configs['db']->query('UPDATE {users_roles} SET avail=?i, date_end=? WHERE id=?i',
                        array($active, $date, intval($role_id)))->ar();
                    if (intval($ar) > 0) {
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                            array($user_id, 'update-role', $mod_id, intval($role_id)));
                    }
                }
            }
        } elseif (isset($post['add-role'])) { // добавляем новую группу ролей
            $name = trim($post['name']);
            $role_id = 0;
            if (!empty($name)) {
                $role_id = $this->all_configs['db']->query('INSERT INTO {users_roles} (name, avail) VALUES (?, ?)',
                    array($name, 1),
                    'id');
            }
            if (isset($post['permissions'])) {
                foreach ($post['permissions'] as $uid => $role) {
                    $id = explode('-', $uid);
                    if (intval($id[1]) > 0 && intval($role_id) > 0) {
                        $this->all_configs['db']->query('INSERT INTO {users_role_permission} (role_id, permission_id) VALUES (?i, ?i)',
                            array(intval($role_id), intval($id[1])));
                    }
                }
            }
            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                array($user_id, 'add-new-role', $mod_id, intval($role_id)));
            FlashMessage::set(l('Роль успешно создана'));
        } elseif (isset($post['create-user'])) { // добавление нового пользователя
            if(!Tariff::isAddUserAvailable($this->all_configs['configs']['api_url'], $this->all_configs['configs']['host'])) {
                FlashMessage::set(l('Вы достигли предельного количества активных пользователей. Попробуйте изменить пакетный план.'), FlashMessage::DANGER);
            } else {
                $avail = 0;
                if (isset($post['avail'])) {
                    $avail = 1;
                }
                if (empty($post['login']) || empty($post['pass']) || empty($post['email'])) {
                    $_SESSION['create-user-error'] = l('Пожалуйста, заполните пароль, логин и эл. адрес');
                    $_SESSION['create-user-post'] = $post;
                } else {
                    $email_or_login_exists =
                        $this->all_configs['db']->query("SELECT 1 FROM {users} "
                            . "WHERE login = ? OR email = ?", array($post['login'], $post['email']), 'el');
                    if ($email_or_login_exists) {
                        FlashMessage::set(l('Пользователь с указанным логинои или эл. адресом уже существует'),
                            FlashMessage::DANGER);
                    } else {
                        $id = $this->all_configs['db']->query('INSERT INTO {users} (login, pass, fio, position, phone, avail,role, email) VALUES (?,?,?,?,?i,?,?,?)',
                            array(
                                $post['login'],
                                $post['pass'],
                                $post['fio'],
                                $post['position'],
                                $post['phone'],
                                $avail,
                                $post['role'],
                                $post['email']
                            ), 'id');
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                            array($user_id, 'add-user', $mod_id, intval($id)));
                        $this->saveUserRelations($id, $post);
                        FlashMessage::set(l('Добавлен новый пользователь'));
                    }
                }
            }
        } elseif (isset($post['update-user'])) {
            $this->updateUser($post, $user_id, $mod_id);
        }

        header("Location:" . $_SERVER['REQUEST_URI']);
        exit;
    }

    /**
     * @param $avatar_img
     * @return string
     */
    function avatar($avatar_img)
    {
        return avatar($avatar_img);
    }

    /**
     * @return string
     * @throws Exception
     */
    function gencontent()
    {
        $users_html = '';

        // проверка на сортировку
        $sort = '';
        $sort_position = '<a href="?sort=position">' . l('Должность');
        if (isset($_GET['sort'])) {
            switch ($_GET['sort']) {
                case 'position':
                    $sort = 'u.position,';
                    $sort_position = '<a href="?sort=rposition">' . l('Должность') . '<i class="glyphicon glyphicon-chevron-down"></i>';
                    break;
                case 'rposition':
                    $sort = 'u.position DESC,';
                    $sort_position = '<a href="?sort=position">' . l('Должность') . '<i class="glyphicon glyphicon-chevron-up"></i>';
                    break;
            }
        }

        // достаём всех пользователей и их роли

        $users_html .= '<div class="tabbable">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="' . $this->mod_submenu[0]['url'] . '">' . $this->mod_submenu[0]['name'] . '</a></li>
                <li><a data-toggle="tab" href="' . $this->mod_submenu[1]['url'] . '">' . $this->mod_submenu[1]['name'] . '</a></li>
                <li><a data-toggle="tab" href="' . $this->mod_submenu[2]['url'] . '">' . $this->mod_submenu[2]['name'] . '</a></li>
                <li><a data-toggle="tab" href="' . $this->mod_submenu[3]['url'] . '">' . $this->mod_submenu[3]['name'] . '</a></li>
            </ul>
            <div class="tab-content">';


        $users = $this->get_users($sort);
        $users_html .= $this->view->renderFile('users/users', array(
            'users' => $users,
            'activeRoles' => $this->get_active_roles(),
            'sortPosition' => $sort_position,
            'controller' => $this,
            'tariff' => Tariff::current()
        ));
        
        // достаём все роли
        $permissions = $this->get_all_roles();
        $aRoles = $this->getRolesTree($permissions);
        $groups = $this->get_permissions_groups();
        // список ролей и ихние доступы
        $users_html .= $this->view->renderFile('users/roles_list', array(
            'aRoles' => $aRoles,
            'groups' => $groups
        ));

        $users_html .= $this->view->renderFile('users/create_new_role', array(
            'groups' => $groups,
            'permissions' => $permissions
        ));

        $roles = array();
        $yet = 0;
        foreach ($permissions as $permission) {
            if ($yet === 0) {
                $yet = $permission['role_id'];
                $roles[$permission['role_id']] = $permission['role_name'];
            } elseif ($yet != $permission['role_id']) {
                $roles[$permission['role_id']] = $permission['role_name'];
            }
        }
        $users_html .= $this->createUserForm(array(), $roles);

        $users_html .= '</div>';

        return $users_html;
    }

    /**
     * @param string $sort
     * @return mixed
     */
    private function get_users($sort = '')
    {

        $roles = $this->all_configs['db']->query("
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
     * @return mixed
     */
    private function get_all_roles()
    {
        return $this->all_configs['db']->query("
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
    private function get_active_roles()
    {

        return $this->all_configs['db']->query("
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
     * @return mixed
     */
    private function get_permissions_groups()
    {
        $per = $this->all_configs['db']->query("
            SELECT id, name
            FROM {users_permissions_groups}
            ORDER BY prio
        ")->vars();
        $per[0] = 'Без группы';

        return $per;
    }

    /**
     * @return array
     */
    public static function get_submenu()
    {
        return array(
            array(
                'click_tab' => true,
                'url' => '#edit_tab_users',
                'name' => l('Список пользователей')
            ),
            array(
                'click_tab' => true,
                'url' => '#edit_tab_roles',
                'name' => l('Управление ролями')
            ),
            array(
                'click_tab' => true,
                'url' => '#edit_tab_create',
                'name' => l('Создать роль')
            ),
            array(
                'click_tab' => true,
                'url' => '#create_tab_user',
                'name' => l('Создать пользователя')
            ),
        );
    }

    /**
     * @param $permissions
     * @return array
     */
    private function getRolesTree($permissions)
    {
        $aRoles = array();
        foreach ($permissions as $permission) {
            if (!array_key_exists($permission['role_id'], $aRoles)) {
                $aRoles[$permission['role_id']] = array(
                    'name' => $permission['role_name'],
                    'all' => array(),
                    'date_end' => $permission['date_end'],
                    'avail' => $permission['avail'],
                    'children' => array()
                );
            }
            $aRoles[$permission['role_id']]['children'][$permission['per_id']] = array(
                'link' => $permission['link'],
                'name' => $permission['per_name'],
                'group_id' => $permission['group_id'],
                'child' => $permission['child'],
                'checked' => $permission['id']
            );
            if (intval($permission['id']) > 0) {
                $aRoles[$permission['role_id']]['all'][] = $permission['per_id'];
            }
        }
        return $aRoles;
    }

    /**
     * @param      $user
     * @param      $roles
     * @param bool $isEdit
     * @return string
     * @throws Exception
     */
    private function createUserForm($user, $roles, $isEdit = false)
    {
        $error = '';
        if (!empty($_SESSION['create-user-error'])) {
            $error = $_SESSION['create-user-error'];
            unset($_SESSION['create-user-error']);
        }

        $q = $this->all_configs['chains']->query_warehouses();
        // списсок складов с общим количеством товаров
        $warehouses_arr = $this->all_configs['chains']->warehouses($q['query_for_noadmin_w']);

        $warehouses = get_service('wh_helper')->get_warehouses();

        $firstWarehouse = reset($warehouses);
        return $this->view->renderFile('users/create', array(
            'error' => $error,
            'form_data' => $user,
            'warehouses' => $warehouses,
            'warehouses_locations' => isset($firstWarehouse['locations']) ? $firstWarehouse['locations'] : array(),
            'warehouses_arr' => $warehouses_arr,
            'cashboxes' => $this->getCashboxes(),
            'roles' => $roles,
            'controller' => $this,
            'isEdit' => $isEdit,
            'available' => !empty($user) || Tariff::isAddUserAvailable($this->all_configs['configs']['api_url'], $this->all_configs['configs']['host']),
        ));
    }

    /**
     * @return mixed
     */
    private function getCashboxes()
    {
        return $this->all_configs['db']->query("SELECT * FROM {cashboxes} WHERE 1=1", array())->assoc('id');
    }

    /**
     * @param $post
     * @param $userId
     * @param $modId
     */
    private function updateUser($userId, $post, $modId)
    {
        $avail = 0;
        if (isset($post['avail'])) {
            $avail = 1;
        }
        if (empty($post['login']) || empty($post['email'])) {
            FlashMessage::set(l('Пожалуйста, заполните логин и эл. адрес'), FlashMessage::DANGER);
        } else {
            $id = intval($post['user_id']);
            $user = $this->all_configs['db']->query('SELECT * FROM {users} WHERE id=?i', array($id));
            if (!empty($user)) {
                require_once($this->all_configs['sitepath'] . 'shop/access.class.php');
//                $access = new access($this->all_configs, false);
//                $password = empty($post['pass']) ? $user['pass']: $access->wrap_pass(trim($post['pass']));
                $password = empty($post['pass']) ? $user['pass']: trim($post['pass']);

                $this->all_configs['db']->query('UPDATE {users} SET login=?, fio=?, position=?, phone=?, avail=?,role=?, email=?, pass=? WHERE id=?i',
                    array(
                        $post['login'],
                        $post['fio'],
                        $post['position'],
                        $post['phone'],
                        $avail,
                        $post['role'],
                        $post['email'],
                        $password,
                        $id
                    ), 'id');
                $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                    array($userId, 'edit-user', $modId, intval($id)));
                $this->saveUserRelations($id, $post);

                FlashMessage::set(l('Данные пользователя обновлены'));
            }
        }
    }

    /**
     * @param $userId
     * @param $post
     */
    private function saveUserRelations($userId, $post)
    {
// добавляем локацию и склад для перемещения заказа при приемке
        if (!empty($post['location']) && !empty($post['warehouse'])) {
            $wh_id = $post['warehouse'];
            $location_id = $post['location'];
            $this->all_configs['db']->query('DELETE FROM {warehouses_users} WHERE main=1 AND user_id=?i',
                array($userId));
            $this->all_configs['db']->query(
                'INSERT IGNORE INTO {warehouses_users} (wh_id, location_id, user_id, main) '
                . 'VALUES (?i,?i,?i,?i)', array($wh_id, $location_id, $userId, 1));
        }
        // добавляем склады
        if (!empty($post['warehouses'])) {
            $this->all_configs['db']->query('DELETE FROM {warehouses_users} WHERE main=0 AND user_id=?i',
                array($userId));
            foreach ($post['warehouses'] as $wh) {
                $this->all_configs['db']->query(
                    'INSERT IGNORE INTO {warehouses_users} (wh_id, user_id, main) '
                    . 'VALUES (?i,?i,?i)', array($wh, $userId, 0));
            }
        }
        // добавляем кассы
        $this->all_configs['db']->query('DELETE FROM {cashboxes_users} WHERE user_id=?i', array($userId));
        if (!empty($post['cashboxes']) && $post['cashboxes'] != -1) {
            foreach ($post['cashboxes'] as $cashbox) {
                $this->all_configs['db']->query(
                    'INSERT IGNORE INTO {cashboxes_users} (cashbox_id, user_id) '
                    . 'VALUES (?i,?i)', array($cashbox, $userId));
            }
        }
    }
}