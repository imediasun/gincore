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
                    if (!$this->all_configs['oRole']->isSuperuserRole(intval($role)) && $this->all_configs['oRole']->isLastSuperuser(intval($uid))) {
                        FlashMessage::set(l('Не возможно изменить роль последнего суперпользователя'),
                            FlashMessage::DANGER);
                    } else {
                        $ar = $this->all_configs['db']->query('UPDATE {users} SET role=?i, avail=?i, fio=?, position=?, phone=?, email=?,
                            auth_cert_serial=?, auth_cert_only=?
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
                                intval($uid)
                            ))->ar();
                        if (intval($ar) > 0) {
                            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                                array($user_id, 'update-user', $mod_id, intval($uid)));
                        }
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
//                    $_SESSION['create-user-error'] = l('Пользователь с указанным логинои или эл. адресом уже существует');
//                    $_SESSION['create-user-post'] = $post;
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
                        Tariff::addUser($this->all_configs['configs']['api_url'],
                            $this->all_configs['configs']['host']);
                        // добавляем локацию и склад для перемещения заказа при приемке
                        if (!empty($post['location']) && !empty($post['warehouse'])) {
                            $wh_id = $post['warehouse'];
                            $location_id = $post['location'];
                            $this->all_configs['db']->query(
                                'INSERT IGNORE INTO {warehouses_users} (wh_id, location_id, user_id, main) '
                                . 'VALUES (?i,?i,?i,?i)', array($wh_id, $location_id, $id, 1));
                        }
                        // добавляем склады
                        if (!empty($post['warehouses'])) {
                            foreach ($post['warehouses'] as $wh) {
                                $this->all_configs['db']->query(
                                    'INSERT IGNORE INTO {warehouses_users} (wh_id, user_id, main) '
                                    . 'VALUES (?i,?i,?i)', array($wh, $id, 0));
                            }
                        }
                        FlashMessage::set(l('Добавлен новый пользователь'));
                    }
                }
            }
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
        $users = $this->get_users($sort);

        // достаём все роли
        $pers = $this->get_all_roles();
        $activeRoles = $this->get_active_roles();

        $users_html .= '<div class="tabbable">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="' . $this->mod_submenu[0]['url'] . '">' . $this->mod_submenu[0]['name'] . '</a></li>
                <li><a data-toggle="tab" href="' . $this->mod_submenu[1]['url'] . '">' . $this->mod_submenu[1]['name'] . '</a></li>
                <li><a data-toggle="tab" href="' . $this->mod_submenu[2]['url'] . '">' . $this->mod_submenu[2]['name'] . '</a></li>
                <li><a data-toggle="tab" href="' . $this->mod_submenu[3]['url'] . '">' . $this->mod_submenu[3]['name'] . '</a></li>
            </ul>
            <div class="tab-content">';

        $users_html .= $this->view->renderFile('users/users', array(
            'users' => $users,
            'activeRoles' => $activeRoles,
            'sortPosition' => $sort_position,
            'controller' => $this
        ));
        // список ролей и ихние доступы
        $users_html .= '<div id="edit_tab_roles" class="tab-pane"><form class="form-horizontal" method="post"><div style="display: inline-block; width: 100%;">';
        // дерево ролей и возможностей
        $aRoles = array();
        foreach ($pers as $per) {
            if (array_key_exists($per['role_id'], $aRoles)) {

                $aRoles[$per['role_id']]['children'][$per['per_id']] = array(
                    'link' => $per['link'],
                    'name' => $per['per_name'],
                    'group_id' => $per['group_id'],
                    'child' => $per['child'],
                    'checked' => $per['id']
                );

            } else {
                $aRoles[$per['role_id']] = array(
                    'name' => $per['role_name'],
                    'all' => array(),
                    'date_end' => $per['date_end'],
                    'avail' => $per['avail'],
                    'children' => array(
                        $per['per_id'] => array(
                            'link' => $per['link'],
                            'name' => $per['per_name'],
                            'group_id' => $per['group_id'],
                            'child' => $per['child'],
                            'checked' => $per['id']
                        )
                    )
                );
            }
            if ( /*array_search($per['per_id'], $aRoles[$per['role_id']]['all']) >=0 &&*/
                intval($per['id']) > 0
            ) {
                $aRoles[$per['role_id']]['all'][] = $per['per_id'];
                //array_push($aRoles[$per['role_id']]['all'], $per['per_id']);
            }
        }
        $groups = $this->get_permissions_groups();
        // блок управление ролями
        foreach ($aRoles as $rid => $v) {
            $checked = '';
            if ($v['avail']) {
                $checked = 'checked';
            }
            $users_html .= '<ul class="nav nav-list pull-left" style="width:33%;padding:0 10px">
                <li class="nav-header"><br><h4 class="text-info">' . htmlspecialchars($v['name']) . '</h4>
                <div class="checkbox"><label><input type="checkbox" ' . $checked . ' name="active[' . $rid . ']" />' . l('активность') . '</label></div></li>';
            $users_html .= '<li>' . l('Дата конца активности группы') . '</li>';
            $users_html .= '<li><input class="form-control input-sm datepicker" name="date_end[' . $rid . ']" type="text" value="' . $v['date_end'] . '" ></li>';
            $group_html = array();
            foreach ($v['children'] as $pid => $sv) {
                $checked = '';
                if ($sv['checked']) {
                    $checked = 'checked';
                }
                $group_html[(int)$sv['group_id']][] = '
                    <li><div class="checkbox"><label><input id="per_id_' . $rid . '_' . $pid . '" class="del-' . $rid . '-' . $sv['child'] . '"
                        onchange="per_change(this, \'' . $rid . '-' . $sv['child'] . '\', \'' . $rid . '-' . $pid . '\')"
                        name="permissions[' . $rid . '-' . $pid . ']" ' . $checked . ' type="checkbox" />' .
                    mb_ucfirst(mb_strtolower($sv['name'])) . '</label></div></li>';
            }
            foreach ($groups as $group_id => $name) {
                if (!empty($group_html[$group_id])) {
                    $users_html .= '
                        <li>
                            <label class="m-t-sm">' . $name . '</label>
                        </li>
                        ' . implode('', $group_html[$group_id]) . '
                    ';
                }
            }
            $users_html .= '</ul><input type="hidden" name="exist-box[' . $rid . ']" value="' . implode(",",
                    $v['all']) . '" />';
        }
        $users_html .= '</div>';
        //if ( $this->all_configs['oRole']->hasPrivilege('edit-user') ) {
        $users_html .= '<input type="submit" name="create-roles" value="' . l('Сохранить') . '" class="btn btn-primary" />';
        //}
        $users_html .= '</form></div>';

        // добавление новой роли
        $users_html .= '<div id="edit_tab_create" class="tab-pane">';
        $users_html .= '
            <form  method="post">
                <fieldset>
                    <legend>' . l('Добавление новой роли') . '</legend>
                    <div class="form-group">
                        <label>' . l('Название') . ':</label>
                        <input class="form-control" value="" name="name" placeholder="' . l('введите название') . '">
                    </div>
                    <div class="form-group">
                        <label>' . l('Права доступа') . ':</label>
                        ' . l('отметьте нужные') . '
                    </div>';
        $yet = 0;
        $roles = array();
        $group_html = array();
        foreach ($pers as $per) {
            if ($yet === 0) {
                $yet = $per['role_id'];
                $roles[$per['role_id']] = $per['role_name'];
            } elseif ($yet != $per['role_id']) {
                $roles[$per['role_id']] = $per['role_name'];
                continue;
            }
            $group_html[(int)$per['group_id']][] = '
                <div class="checkbox">
                    <label><input id="per_id_a_' . $per['per_id'] . '" class="del-a-' . $per['child'] . '"
                        onchange="per_change(this, \'a-' . $per['child'] . '\', \'a-' . $per['per_id'] . '\')"
                        type="checkbox" name="permissions[a-' . $per['per_id'] . ']">' . mb_ucfirst(mb_strtolower(htmlspecialchars($per['per_name']))) . '</label>
                </div>';
        }
        foreach ($groups as $group_id => $name) {
            if (!empty($group_html[$group_id])) {
                $users_html .= '
                    <div class="form-group">
                        <label>' . $name . '</label>
                        ' . implode('', $group_html[$group_id]) . '
                    </div>
                ';
            }
        }
        $users_html .= '<div class="control-group"><div class="controls">
            <input class="btn btn-primary" type="submit" name="add-role" value="' . l('Создать') . '"></div></div>';
        $users_html .= '</fieldset></form>';
        $users_html .= '</div>';

        $form_data = array();
        $msg = '';
        if (!empty($_SESSION['create-user-error'])) {
            $msg = '
                <div class="alert alert-danger alert-dismissible" role="alert">
                  <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  ' . $_SESSION['create-user-error'] . '
                </div>
            ';
            $form_data = $_SESSION['create-user-post'];
            unset($_SESSION['create-user-error']);
            unset($_SESSION['create-user-post']);
        }

        $role_html = '';
        foreach ($roles as $role_id => $role_name) {
            $sel = !empty($form_data['role']) && $role_id == $form_data['role'] ? ' selected' : '';
            $role_html .= '<option' . $sel . ' value="' . $role_id . '">' . htmlspecialchars($role_name) . '</option>';
        }

        // добавление нового пользователя
        $users_html .= '<div id="create_tab_user" class="tab-pane">';
        $warehouses = '';
        $q = $this->all_configs['chains']->query_warehouses();
        // списсок складов с общим количеством товаров
        $warehouses_arr = $this->all_configs['chains']->warehouses($q['query_for_noadmin_w']);
        foreach ($warehouses_arr as $warehouse) {
            $sel = !empty($form_data['warehouses']) && in_array($warehouse['id'],
                $form_data['warehouses']) ? ' selected' : '';
            $warehouses .= '<option' . $sel . ' value="' . $warehouse['id'] . '">' . htmlspecialchars($warehouse['title']) . '</option>';
        }
        $warehouses_options = '';
//        $whs = $this->all_configs['chains']->warehouses();
        $whs = get_service('wh_helper')->get_warehouses();
        $whs_first = 0;
        $i = 0;
        foreach ($whs as $warehouse) {
            $sel = !empty($form_data['warehouse']) && $form_data['warehouse'] == $warehouse['id'] ? ' selected' : '';
            if (!$i) {
                $whs_first = $warehouse['id'];
            }
            $warehouses_options .= '<option' . $sel . ' value="' . $warehouse['id'] . '">' . $warehouse['title'] . '</option>';
            $i++;
        }
        $warehouses_options_locations = '';
        if (isset($whs[$whs_first]['locations'])) {
            foreach ($whs[$whs_first]['locations'] as $id => $location) {
                if (trim($location['name'])) {
                    $sel = !empty($form_data['location']) && $form_data['location'] == $id ? ' selected' : '';
                    $warehouses_options_locations .=
                        '<option' . $sel . (!$i ? ' selected="selected"' : '') . ' value="' . $id . '">' .
                        htmlspecialchars($location['name']) .
                        '</option>';
                }
            }
        }
        $users_html .= '
            <form method="post">
                ' . $msg . '
                <fieldset>
                    <legend>' . l('Добавление нового пользователя') . '</legend>
                    <div class="form-group">
                        <label>' . l('Логин') . ' <b class="text-danger">*</b>:</label>
                        <input class="form-control" value="' . (isset($form_data['login']) ? htmlspecialchars($form_data['login']) : '') . '" name="login" placeholder="' . l('введите логин') . '">
                    </div>
                    <div class="form-group">
                        <label>' . l('E-mail') . ' <b class="text-danger">*</b>:</label>
                        <input class="form-control" value="' . (isset($form_data['email']) ? htmlspecialchars($form_data['email']) : '') . '" name="email" placeholder="' . l('введите e-mail') . '">
                    </div>
                    <div class="form-group">
                        <label>' . l('Пароль') . ' <b class="text-danger">*</b>:</label>
                        <input class="form-control" value="" name="pass" placeholder="' . l('введите пароль') . '">
                    </div>
                    <div class="form-group">
                        <label>' . l('ФИО') . ':</label>
                        <input class="form-control" value="' . (isset($form_data['fio']) ? htmlspecialchars($form_data['fio']) : '') . '" name="fio" placeholder="' . l('введите фио') . '">
                    </div>
                    <div class="form-group">
                        <label>' . l('Должность') . '</label>
                        <input class="form-control" value="' . (isset($form_data['position']) ? htmlspecialchars($form_data['position']) : '') . '" name="position" placeholder="' . l('введите должность') . '">
                    </div>
                    <div class="form-group">
                        <label>' . l('Телефон') . '</label>
                        <input onkeydown="return isNumberKey(event)" class="form-control" value="' . (isset($form_data['phone']) ? htmlspecialchars($form_data['phone']) : '') . '" name="phone" placeholder="' . l('введите телефон') . '">
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label><input ' . (!empty($form_data['avail']) || !$form_data ? 'checked' : '') . ' type="checkbox" name="avail" />' . l('Активность') . '</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>' . l('Укажите склад и локацию, на которую по умолчанию перемещается устройство принятое на ремонт данным сотрудником') . '</label>
                        <div class="clearfix">
                            <div class="pull-left m-r-lg">
                                <label>' . l('Склад') . ':</label><br>
                                <select onchange="change_warehouse(this)" class="multiselect form-control" name="warehouse">
                                    ' . $warehouses_options . '
                                </select>
                            </div>
                            <div class="pull-left">
                                <label>' . l('Локация') . ':</label><br>
                                <select class="multiselect form-control select-location" name="location">
                                    ' . $warehouses_options_locations . '
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>' . l('Укажите склады к которым сотрудник имеет доступ') . '</label><br>
                        <select class="multiselect" name="warehouses[]" multiple="multiple">
                            ' . $warehouses . '
                        </select>
                    </div>
                    <div class="form-group">
                        <label>' . l('Роль') . '</label>
                        <select name="role" class="form-control">
                            <option value="">' . l('выберите роль') . '</option>
                            ' . $role_html . '
                        </select>
                    </div>';
//                        '.typeahead($this->all_configs['db'], 'locations', false, 0, 0, 'input-large', '', '', false, false).'

        //if ( $this->all_configs['oRole']->hasPrivilege('edit-user') ) {
        $users_html .= '<div class="control-group"><div class="controls">
                    <input class="btn btn-primary" type="submit" name="create-user" onclick="return add_user_validation();" value="' . l('Создать') . '"></div></div>';
        //}
        $users_html .= '</fieldset></form>';
        $users_html .= '</div>';

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
}