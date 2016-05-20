<?php

require_once __DIR__ . '/../../Core/Controller.php';
require_once __DIR__ . '/../../Tariff.php';

$modulename[80] = 'users';
$modulemenu[80] = l('Сотрудники');
$moduleactive[80] = !$ifauth['is_2'];

/**
 * @property  MUsers    Users
 * @property  MSettings Settings
 * @property  MUsersRoles UsersRoles
 * @property  MUsersRolePermission UsersRolePermission
 */
class users extends Controller
{
    public $uses = array(
        'Users',
        'UsersRoles',
        'Settings',
        'UsersRolePermission'
    );

    /**
     * @inheritdoc
     */
    public function routing(Array $arrequest)
    {
        /**
         * должно быть доступно всем юзерам, независимо от прав доступа
         */
        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $act = isset($_GET['act']) ? $_GET['act'] : '';
            if (!empty($act) && $act == 'ratings') {
                $data = $this->getRatings($this->getUserId());
                Response::json($data);
            }
        }
        $result = parent::routing($arrequest);
        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'generate_log_file') {
            $this->generateLogFile();
            exit();
        }
        return $result;
    }

    /**
     * @return string
     */
    public function renderCanShowModuleError()
    {
        return '<div class="span3"></div>
                <div class="span9"><p  class="text-error">' . l('У Вас нет прав для просмотра пользователей') . '</p></div>';

    }

    /**
     * @return bool
     */
    public function can_show_module()
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
    public function ajax()
    {
        $user_id = $this->getUserId();
        $mod_id = $this->all_configs['configs']['users-manage-page'];

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        // загрузка аватарки
        if ($act == 'upload_avatar') {
            $this->uploadAvatar();
        }

        if ($act == 'delete_user') {
            $this->deleteUser($user_id);
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
            );
            try {
                if (empty($_POST['id'])) {
                    $result['message'] = l('Пользователь не найден');
                }

                $this->updateUser($_POST, $mod_id);
                unset($result['message']);
                $result['state'] = true;
            } catch (Exception $e) {
                $result['message'] = l('Что-то пошло не так');
            }
            Response::json($result);
        }

        // изменить пароль
        if ($act == 'change-admin-password') {
            if (isset($_POST['pk']) && is_numeric($_POST['pk']) && isset($_POST['value'])) {
                $ar = $this->Users->update(array('pass' => $_POST['value']), array($this->Users->pk() => $_POST['pk']));

                if (intval($ar) > 0) {
                    $this->History->save('update-password', $mod_id, $_POST['pk']);
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
        $permissions = $this->UsersRoles->getAllRoles();
        $user = $this->Users->getByPk($userId);
        $user['cashboxes'] = $this->all_configs['db']->query('SELECT cashbox_id FROM {cashboxes_users} WHERE user_id=?i',
            array($userId))->col();
        $user['cashboxes'] = $this->all_configs['db']->query('SELECT cashbox_id FROM {cashboxes_users} WHERE user_id=?i',
            array($userId))->col();
        $warehouses = $this->all_configs['db']->query('SELECT wh_id, location_id FROM {warehouses_users} WHERE main=1 AND user_id=?i',
            array($userId))->row();
        list($user['warehouse'], $user['location']) = array('', '');
        if (!empty($warehouses)) {
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
    public function check_post(array $post)
    {
        $user_id = $this->getUserId();

        $mod_id = $this->all_configs['configs']['users-manage-page'];

        if (isset($post['change-roles'])) { // изменяем роли пользователям
            $this->changeRoles($user_id, $post, $mod_id);
        } elseif (isset($post['create-roles'])) { // изменяем возможности ролям
            $this->createRoles($post, $mod_id);
        } elseif (isset($post['add-role'])) { // добавляем новую группу ролей
            $this->addRole($post, $mod_id);
        } elseif (isset($post['create-user'])) { // добавление нового пользователя
            $this->createUser($post, $mod_id);
        } elseif (isset($post['update-user'])) {
            $this->updateUser($post, $mod_id);
        } elseif (isset($post['save-send-log-email'])) {
            $this->saveSendLogEmail($post);
        }

        Response::redirect($_SERVER['REQUEST_URI']);
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
        // проверка на сортировку
        $sort = '';
        if (isset($_GET['sort'])) {
            switch ($_GET['sort']) {
                case 'position':
                    $sort = 'u.position,';
                    break;
                case 'rposition':
                    $sort = 'u.position DESC,';
                    break;
            }
        }

        // достаём всех пользователей и их роли
        $users = $this->Users->getUsers($sort);

        // достаём все роли
        $permissions = $this->UsersRoles->getAllRoles();
        $aRoles = $this->getRolesTree($permissions);
        $groups = $this->get_permissions_groups();
        // список ролей и ихние доступы

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


        return $this->view->renderFile('users/gencontent', array(
            'users' => $this->view->renderFile('users/users', array(
                'users' => $users,
                'activeRoles' => $this->UsersRoles->getActiveRoles(),
                'controller' => $this,
                'tariff' => Tariff::current()
            )),
            'mod_submenu' => $this->mod_submenu,
            'role_list' => $this->view->renderFile('users/roles_list', array(
                'aRoles' => $aRoles,
                'groups' => $groups
            )),

            'create_new_role' => $this->view->renderFile('users/create_new_role', array(
                'groups' => $groups,
                'permissions' => $permissions
            )),
            'create_user_form' => $this->createUserForm(array(), $roles),
            'logins_log' => ($this->all_configs['oRole']->hasPrivilege('site-administration') && isset($this->mod_submenu[4])) ? $this->loginsLog() : ''
        ));
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
        $per[0] = l('Без группы');

        return $per;
    }

    /**
     * @return array
     */
    public static function get_submenu($oRole = null)
    {
        $submenu = array(
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
        global $all_configs;
        if ($all_configs['oRole']->hasPrivilege('site-administration')) {
            $submenu[] = array(
                'click_tab' => true,
                'url' => '#login_log',
                'name' => l('Статистика входов в систему')
            );
        }
        return $submenu;
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
            'available' => !empty($user) || Tariff::isAddUserAvailable($this->all_configs['configs']['api_url'],
                    $this->all_configs['configs']['host']),
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
     * @param $modId
     */
    private function updateUser($post, $modId)
    {
        $avail = 0;
        if (isset($post['avail'])) {
            $avail = 1;
        }
        if (empty($post['login']) || empty($post['email'])) {
            FlashMessage::set(l('Пожалуйста, заполните логин и эл. адрес'), FlashMessage::DANGER);
            return;
        }
        $id = intval($post['user_id']);
        $user = $this->Users->getByPk($id);
        if (!empty($user)) {
            require_once($this->all_configs['sitepath'] . 'shop/access.class.php');
            $password = empty($post['pass']) ? $user['pass'] : trim($post['pass']);
            $access = new \access($this->all_configs, false);
            $phones = $access->is_phone($post['phone']);

            $this->Users->update(array(
                'login' => $post['login'],
                'fio' => $post['fio'],
                'position' => $post['position'],
                'phone' => empty($phones[0]) ? $user['phone'] : $phones[0],
                'avail' => $avail,
                'role' => $post['role'],
                'email' => $post['email'],
                'pass' => $password,
                'send_over_sms' => isset($post['over_sms']) && $post['over_sms'] == 'on',
                'send_over_email' => isset($post['over_email']) && $post['over_email'] == 'on',
                'salary_from_repair' => isset($post['salary_from_repair']) ? $post['salary_from_repair'] : 0,
                'salary_from_sale' => isset($post['salary_from_repair']) ? $post['salary_from_sale'] : 0,
            ), array(
                $this->Users->pk() => $id
            ));
            $this->History->save('edit-user', $modId, intval($id));
            $this->saveUserRelations($id, $post);

            FlashMessage::set(l('Данные пользователя обновлены'));
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

    /**
     * @return string
     */
    private function loginsLog()
    {
        $users = $this->all_configs['db']->query('SELECT id, login, email, fio FROM {users} WHERE avail=1 AND deleted=0')->assoc();
        foreach ($users as $id => $user) {
            $users[$id]['logs'] = $this->all_configs['db']->query('SELECT * FROM {users_login_log} WHERE user_id=?i ORDER by created_at DESC LIMIT 200',
                array($user['id']))->assoc();
        }
        $emailSettings = $this->Settings->query('SELECT `name`, `value` FROM {settings} WHERE `name`=? OR `name`=?',
            array(
                'email_for_send_login_log',
                'need_send_login_log'
            ))->assoc('name');
        return $this->view->renderFile('users/logins_log', array(
            'users' => $users,
            'emailSettings' => $emailSettings
        ));
    }

    /**
     *
     */
    public function generateLogFile()
    {
        $objWriter = generate_xls_with_login_logs();
        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="report.xls"');
        $objWriter->save('php://output');
        exit();
    }

    /**
     * @return array
     */
    protected function uploadAvatar()
    {
        require_once 'qqfileuploader.php';
        require_once 'class_image.php';
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
                $this->Users->update(array('avatar' => $file), array($this->Users->pk() => $uid));
            } else {
                $result['filename'] = '';
                $result['path'] = '';
                $result['msg'] = '';
                $result['file'] = '';
            }
            Response::json($result);
        }
    }

    /**
     * @param $user_id
     */
    protected function getRatings($user_id)
    {
        $ratings = $this->all_configs['db']->query('SELECT ur.*, f.comment '
            . ' FROM {users_ratings} ur'
            . ' JOIN {feedback} f ON ur.order_id=f.order_id'
            . ' WHERE user_id=?i ORDER BY created_at DESC',
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

    /**
     * @param $user_id
     * @return array
     */
    protected function deleteUser($user_id)
    {
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
                if ($this->Users->update(array('deleted' => 1), array($this->Users->pk() => $uid))) {
                    $result['success'] = true;
                    FlashMessage::set(l('Пользователь удален'));
                    $result['uid'] = $uid;
                }
            }

        } else {
            $result['msg'] = l('Пользователь не найден');
        }
        Response::json($result);
    }

    /**
     * @param       $user_id
     * @param array $post
     * @param       $mod_id
     */
    protected function changeRoles($user_id, array $post, $mod_id)
    {
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
                $isBlocked = !$avail ? USER_DEACTIVATED_BY_TARIFF_MANUAL : USER_ACTIVATED_BY_TARIFF;
                if ($isBlocked && $isLastSuperuser) {
                    FlashMessage::set(l('Не возможно блокировать последнего суперпользователя'),
                        FlashMessage::DANGER);
                    $isBlocked = 0;
                    $avail = 1;
                }
                if ($isBlocked && $uid == $user_id) {
                    FlashMessage::set(l('Нельзя заблокировать текущую учетную запись'),
                        FlashMessage::DANGER);
                    $isBlocked = 0;
                    $avail = 1;
                }
                $ar = $this->Users->update(array(
                    'role' => intval($role),
                    'avail' => $avail,
                    'fio' => trim($post['fio'][$uid]),
                    'position' => trim($post['position'][$uid]),
                    'phone' => trim($post['phone'][$uid]),
                    'email' => trim($post['email'][$uid]),
                    'auth_cert_serial' => trim($post['auth_cert_serial'][$uid]),
                    'auth_cert_only' => $cert_avail,
                    'blocked_by_tariff' => $isBlocked,
                ), array($this->Users->pk() => intval($uid)));
                if (intval($ar) > 0) {
                    $this->History->save('update-user', $mod_id, intval($uid));
                }
            }
        }
    }

    /**
     * @param array $post
     * @param       $mod_id
     */
    protected function createRoles(array $post, $mod_id)
    {
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
                    $this->UsersRolePermission->insert(array(
                        'role_id' => intval($id[0]),
                        'permission_id' => intval($id[1])
                    ));
                    $this->History->save('add-to-role-per', $mod_id, intval($id[0]));
                }
            }
            if (count($exist) > 0) {
                foreach ($exist as $v) {
                    if ($this->all_configs['oRole']->isSuperuserPermission(intval($v)) && $this->all_configs['oRole']->isLastSuperuserRole($role_id)) {
                        FlashMessage::set(l('Не возможно удалить права суперюзера', FlashMessage::DANGER));
                    } else {
                        $this->all_configs['db']->query('DELETE FROM {users_role_permission} WHERE role_id=?i AND permission_id=?i',
                            array(intval($role_id), intval($v)));
                        $this->History->save('delete-from-role-per', $mod_id, intval($role_id));
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
                $ar = $this->UsersRoles->update(array(
                    'avail' => $active,
                    'date_end' => $date
                ), array($this->UsersRoles->pk() => intval($role_id)));
                if (intval($ar) > 0) {
                    $this->History->save('update-role', $mod_id, intval($role_id));
                }
            }
        }
    }

    /**
     * @param array $post
     * @param       $mod_id
     */
    protected function addRole(array $post, $mod_id)
    {
        $name = trim($post['name']);
        $role_id = 0;
        if (!empty($name)) {
            $role_id = $this->UsersRoles->insert(array(
                'name' => $name,
                'avail' => 1
            ));
        }
        if (isset($post['permissions'])) {
            foreach ($post['permissions'] as $uid => $role) {
                $id = explode('-', $uid);
                if (intval($id[1]) > 0 && intval($role_id) > 0) {
                    $this->UsersRolePermission->insert(array(
                        'role_id' => intval($role_id),
                        'permission_id' => intval($id[1])
                    ));
                }
            }
        }
        $this->History->save('add-new-role', $mod_id, intval($role_id));
        FlashMessage::set(l('Роль успешно создана'));
    }

    /**
     * @param array $post
     * @param       $mod_id
     */
    protected function createUser(array $post, $mod_id)
    {
        if (!Tariff::isAddUserAvailable($this->all_configs['configs']['api_url'],
            $this->all_configs['configs']['host'])
        ) {
            FlashMessage::set(l('Вы достигли предельного количества активных пользователей. Попробуйте изменить пакетный план.'),
                FlashMessage::DANGER);
        } else {
            $avail = 0;
            if (isset($post['avail'])) {
                $avail = 1;
            }
            if (empty($post['login']) || empty($post['pass']) || empty($post['email'])) {
                $_SESSION['create-user-error'] = l('Пожалуйста, заполните пароль, логин и эл. адрес');
                $_SESSION['create-user-post'] = $post;
            } else {
                require_once($this->all_configs['sitepath'] . 'shop/access.class.php');
                $access = new \access($this->all_configs, false);
                $phones = $access->is_phone($post['phone']);

                $email_or_login_exists =
                    $this->all_configs['db']->query("SELECT 1 FROM {users} "
                        . "WHERE login = ? OR email = ?", array($post['login'], $post['email']), 'el');
                if ($email_or_login_exists) {
                    FlashMessage::set(l('Пользователь с указанным логинои или эл. адресом уже существует'),
                        FlashMessage::DANGER);
                } else {
                    $id = $this->Users->insert(array(
                        'login' => $post['login'],
                        'pass' => $post['pass'],
                        'fio' => $post['fio'],
                        'position' => $post['position'],
                        'phone' => empty($phones[0]) ? '' : $phones[0],
                        'avail' => $avail,
                        'role' => $post['role'],
                        'email' => $post['email'],
                        'send_over_sms' => isset($post['over_sms']) && $post['over_sms'] == 'on',
                        'send_over_email' => isset($post['over_email']) && $post['over_email'] == 'on'
                    ));
                    $this->History->save('add-user', $mod_id, intval($id));
                    $this->saveUserRelations($id, $post);
                    FlashMessage::set(l('Добавлен новый пользователь'));
                }
            }
        }
    }

    /**
     * @param array $post
     */
    protected function saveSendLogEmail(array $post)
    {
        require_once($this->all_configs['sitepath'] . 'shop/access.class.php');
        $access = new \access($this->all_configs, false);
        $email = $access->is_email($post['email']) ? $post['email'] : '';
        $send = (int)isset($post['send_email']);
        if (!$this->Settings->check('need_send_login_log')) {
            $this->Settings->insert(array(
                'name' => 'need_send_login_log',
                'value' => '0',
                'description' => lq('Отправлять ежедневные логи входа на email'),
                'ro' => 0,
                'title' => lq('Отправлять ежедневные логи входа на email')
            ));

        }
        if (!$this->Settings->check('email_for_send_login_log')) {
            $this->Settings->insert(array(
                'name' => 'email_for_send_login_log',
                'value' => '',
                'description' => lq('email на который будут отправлять логи входов в систему'),
                'ro' => 0,
                'title' => lq('email на который будут отправлять логи входов в систему')
            ));

        }
        $this->Settings->update(array('value' => $email), array('name' => 'email_for_send_login_log'));
        $this->Settings->update(array('value' => $send), array('name' => 'need_send_login_log'));
        if ($send && !empty($email)) {
            FlashMessage::set(l("Отчет будет отправляться ежедневно в 14-00"));
        }
    }
}