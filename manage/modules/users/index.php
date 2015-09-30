<?php


$modulename[] = 'users';
$modulemenu[] = 'Сотрудники';
$moduleactive[] = !$ifauth['is_2'];

class users
{
    protected $all_configs;
    
    function __construct($all_configs)
    {
        $this->all_configs = &$all_configs;
        global $input_html, $ifauth;

        if ( !$this->all_configs['oRole']->hasPrivilege('edit-users') ) {
            return $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p  class="text-error">У Вас нет прав для просмотра пользователей</p></div>';
        }
        
        if($ifauth['is_2']) return false;

        
        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
            return true;
        }
        
        // если отправлена форма изменения продукта
        if (count($_POST)>0 ){
            $this->check_post($_POST);
        }

        $input_html['mcontent'] = $this->gencontent();
    }

    
    private function can_show_module()
    {
        if ($this->all_configs['oRole']->hasPrivilege('edit-users')) {
            return true;
        } else {
            return false;
        }
    }
    
    private function count_resize_vars($width, $height, $out_w, $out_h, $no_stretch = false){
        $vars = array();
        $vertical = true;
        if($width >= $height){
            $vertical = false;
        }
        if($no_stretch){
            $out_w = $out_w > $width ? $width : $out_w;
            $out_h = $out_h > $height ? $height : $out_h;
        }
        if(!$out_h){
            $out_h = $out_w * ($height / $width);
        }
        if($width > $out_w || $height > $out_h){
            if(!$vertical){
                $h = $out_h;
                $w = $out_h * ($width / $height);
                if($w < $out_w){
                    $w = $out_w;
                    $h = $out_w * ($height / $width);
                }
            }else{
                $w = $out_w;
                $h = $out_w * ($height / $width);
                if($h < $out_h){
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
        }else{
            $vars['resize'] = '';
            $vars['crop'] = '';
        }
        return $vars;
    }

    public function resize_image($image_path, $data){
        if($data){
            $img = new LiveImage($image_path);
            $img->resize($data['width'], $data['height'], false);
            $img->output(null, $image_path);
            chmod($image_path, 0777);
        }
    }
    
    public function crop_image($image_path, $crop){
        if($crop){
            $img = new LiveImage($image_path);
            $img->crop($crop['width'], $crop['height'], $crop['start_width'], $crop['start_height']);
            $img->output(null, $image_path);
            chmod($image_path, 0777);
        }
    }
    
    public function new_filename($file){
        $file_parts = pathinfo($file);
        return md5(microtime(true)).'.'.$file_parts['extension'];
    }
    
    private function ajax()
    {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        $mod_id = $this->all_configs['configs']['users-manage-page'];
        $data = array(
            'state' => false
        );

        $act = isset($_GET['act']) ? $_GET['act'] : '';

        // загрузка аватарки
        if($act == 'upload_avatar'){
            include_once 'qqfileuploader.php';
            include_once 'class_image.php';
            $uid = isset($_GET['uid']) ? (int)$_GET['uid'] : 0;
            if($uid){
                $uploader = new qqFileUploader(array('jpg', 'jpeg', 'png', 'JPG', 'JPEG', 'PNG'), 3145728);
                $path_avatars = $this->all_configs['path'].$this->all_configs['configs']['users-avatars-path'];
                $result = $uploader->handleUpload($path_avatars);
                if($result['success']){
                    $file = $this->new_filename($result['file']);
                    rename($path_avatars.$result['file'], $path_avatars.$file);
                    $result['file'] = $file;
                    $image_path = $path_avatars.$file;
                    $out_w = 75;
                    $out_h = 75;
                    $image_info = getimagesize($image_path);
                    $image_vars = $this->count_resize_vars($image_info[0], $image_info[1], $out_w, $out_h, false);
                    $this->resize_image($image_path, $image_vars['resize']);
                    $this->crop_image($image_path, $image_vars['crop']);
                    $result['success'] = true;
                    $result['path'] = $this->all_configs['prefix'].$this->all_configs['configs']['users-avatars-path'];
                    $result['avatar'] = $result['path'].$file;
                    $result['msg'] = '';
                    $result['uid'] = $uid;
                    $this->all_configs['db']->query("UPDATE {users} SET avatar = ? "
                                                   ."WHERE id = ?i", array($file, $uid));
                }else{
                    $result['filename'] = '';
                    $result['path'] = '';
                    $result['msg'] = '';
                    $result['file'] = '';
                }
                echo json_encode($result);
                exit;
            }
        }
        
        // проверка доступа
        if ($this->can_show_module() == false) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Нет прав', 'state' => false));
            exit;
        }
        
        
        // изменить пароль
        if ($act == 'change-admin-password') {

            if (isset($_POST['pk']) && is_numeric($_POST['pk']) && isset($_POST['value'])) {
                
                $ar = $this->all_configs['db']->query('UPDATE {users} SET pass=?
                    WHERE id=?i LIMIT 1', array($_POST['value'], $_POST['pk']))->ar();
                
                if (intval($ar) > 0) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i', array($user_id, 'update-password', $mod_id, $_POST['pk']));
                }
                
                header("Content-Type: application/json; charset=UTF-8");
                exit;
            }

        }
        
        exit;
    }

    
    function check_post ($post)
    {
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';

        $mod_id = $this->all_configs['configs']['users-manage-page'];

        if ( isset($post['change-roles']) ) { // изменяем роли пользователям

            foreach ( $post['roles'] as $uid=>$role ) {
                $avail = 0;$cert_avail = 0;
                if ( isset($post['avail_user'][$uid]) )
                    $avail = 1;
                if ( isset($post['auth_cert_only'][$uid]) )
                    $cert_avail = 1;
                $ar = 0;
                if ( intval($uid) > 0 ) {
                    $ar = $this->all_configs['db']->query('UPDATE {users} SET role=?i, avail=?i, fio=?, position=?, phone=?, email=?,
                            auth_cert_serial=?, auth_cert_only=?
                        WHERE id=?i',
                        array(intval($role), $avail, trim($post['fio'][$uid]), trim($post['position'][$uid]), trim($post['phone'][$uid]),
                            trim($post['email'][$uid]), trim($post['auth_cert_serial'][$uid]), $cert_avail, intval($uid)))->ar();
                }
                if ( intval($ar) > 0 ) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                        array($user_id, 'update-user', $mod_id, intval($uid)));
                }
            }
        } elseif ( isset($post['create-roles']) ) { // изменяем возможности ролям

            foreach( $post['exist-box'] as $role_id=>$s) {

                $exist = explode(',', $s);

                foreach ( $post['permissions'] as $uid=>$on ) {

                    $id = explode('-', $uid);
                    if ( intval($role_id) != intval($id[0]) )
                        continue;

                    foreach ( $exist as $k=>$v) {
                        if( intval($v) == intval($id[1])) {
                            unset($exist[$k]);
                        }
                    }

                    $i = array_search(intval($id[1]), explode(',',$s) );

                    if ( $i === false && intval($id[0]) > 0 && intval($id[1]) > 0 ) {
                        //unset($exist[$i]);
                        $this->all_configs['db']->query('INSERT INTO {users_role_permission} (role_id, permission_id) VALUES (?i, ?i)',
                            array(intval($id[0]), intval($id[1]))
                        );
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                            array($user_id, 'add-to-role-per', $mod_id, intval($id[0])));
                    }
                }
                if ( count($exist) > 0 ) {
                    foreach( $exist as $v ) {
                        $this->all_configs['db']->query('DELETE FROM {users_role_permission} WHERE role_id=?i AND permission_id=?i',
                            array( intval($role_id), intval($v)) );
                        $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                            array($user_id, 'delete-from-role-per', $mod_id, intval($role_id)));
                    }

                }
                $date = '0000-00-00 00:00:00';
                $active = '0';
                //if ( $post['date_end'][$role_id])
                $date_from_post = strtotime($post['date_end'][$role_id]);
                $date = $date_from_post > 0 ? date('Y-m-d H:i:s', $date_from_post) : '0000-00-00 00:00:00';

                if (isset($post['active'][$role_id]) && $post['active'][$role_id])
                    $active = 1;


                $ar = $this->all_configs['db']->query('UPDATE {users_roles} SET avail=?i, date_end=? WHERE id=?i',
                    array( $active, $date, intval($role_id)))->ar();
                if ( intval($ar) > 0 ) {
                    $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                        array($user_id, 'update-role', $mod_id, intval($role_id)));
                }
            }
        } elseif ( isset($post['add-role']) ) { // добавляем новую группу ролей
            $name = trim($post['name']);
            $role_id = 0;
            if ( !empty($name) )
                $role_id = $this->all_configs['db']->query('INSERT INTO {users_roles} (name) VALUES (?)', array($name), 'id');
            if (isset($post['permissions'])) {
                foreach ( $post['permissions'] as $uid=>$role ) {
                    $id = explode('-',$uid);
                    if ( intval($id[1]) > 0 && intval($role_id) > 0 ) {
                        $this->all_configs['db']->query('INSERT INTO {users_role_permission} (role_id, permission_id) VALUES (?i, ?i)',
                            array(intval($role_id), intval($id[1])));
                    }
                }
            }
            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                array($user_id, 'add-new-role', $mod_id, intval($role_id)));
        } else
        if ( isset($post['create-user']) ) { // добавление нового пользователя
            $avail = 0;
            if ( isset($post['avail']) )
                $avail = 1;
            $id = $this->all_configs['db']->query('INSERT INTO {users} (login, pass, fio, position, phone, avail,role, email) VALUES (?,?,?,?,?i,?,?,?)',
                array($post['login'], $post['pass'], $post['fio'], $post['position'], $post['phone'], $avail, $post['role'], $post['email']), 'id');
            $this->all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                array($user_id, 'add-user', $mod_id, intval($id)));
        }

        header("Location:". $_SERVER['REQUEST_URI']);
    }

    function avatar($avatar_img){
        return avatar($avatar_img);
    }
    
    function gencontent()
    {
        $users_html = '';

        // проверка на сортировку
        $sort = '';
        $sort_position = '<a href="?sort=position">Должность';
        if ( isset($_GET['sort']) ) {
            switch ($_GET['sort']) {
                case 'position':
                    $sort = 'u.position,';
                    $sort_position = '<a href="?sort=rposition">Должность<i class="glyphicon glyphicon-chevron-down"></i>';
                    break;
                case 'rposition':
                    $sort = 'u.position DESC,';
                    $sort_position = '<a href="?sort=position">Должность<i class="glyphicon glyphicon-chevron-up"></i>';
                    break;
            }
        }

        // достаём всех пользователей и их роли
        $users = $this->get_users($sort);

        // достаём все роли
        $pers = $this->get_all_roles();

        $users_html .= '<div class="tabbable">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#edit_tab_users">Список пользователей</a></li>
                <li><a data-toggle="tab" href="#edit_tab_roles">Управления ролями</a></li>
                <li><a data-toggle="tab" href="#edit_tab_create">Создать роль</a></li>
                <li><a data-toggle="tab" href="#create_tab_user">Создать пользователя</a></li>
            </ul>
            <div class="tab-content">';

        // список пользователей и ихние роля
        $users_html .= '<div id="edit_tab_users" class="tab-pane active"><form enctype="multipart/form-data" method="post" id="users-form">';
        $users_html .= '<table class="table table-striped"><thead><tr>'
                . '<td>ID</td>'
                . '<td>Фото</td>'
                . '<td><i class="glyphicon glyphicon-envelope"></i></td>'
                . '<td>Логин</td>'
                . '<td><i class="glyphicon glyphicon-off"></i></td>'
                . '<td>Пароль</td>'
                . '<td>Роль</td>'
                . '<td>ФИО</td><td>' . $sort_position . '</a></td>'
                . '<td>Телефон</td>'
                . '<td>Эл. почта</td>'
                . '<td title="Серийный номер сертификата">Номер<br>сертиф.</td>'
                . '<td title="Вход только по сертификату">Вход по<br>сертиф.</td>'
            . '</tr></thead><tbody>';

        // строим блок списка пользователей с ролями
        $yet = array();
        if ( count($users) > 0 ) {
            foreach ( $users as $user) {

                if ( array_key_exists($user['id'], $yet)) {
                } else {
                    
                    $checked = ''; $cert_checked = '';
                    if ( $user['avail'] )
                        $checked = 'checked';
                    if ( $user['auth_cert_only'] )
                        $cert_checked = 'checked';
                    
                    $users_html .= 
                         '<tr>'
                        .'<td>' . $user['id'] . '</td>'
                        .'<td>
                            <img class="upload_avatar_btn" data-uid="'.$user['id'].'" width="40" src="'.$this->avatar($user['avatar']).'">
                         </td>'
                        .'<td><input type="checkbox" name="send-mess-user[' . $user['id'] . ']" '
                               . 'class="send-mess-user" value="' . $user['id'] . '" /></td>'
                        .'<td>' . htmlspecialchars($user['login']) . '</td>'
                        .'<td><input ' . $checked . ' type="checkbox" name="avail_user[' . $user['id'] . ']" /></td>'
                        .'<td><i class="glyphicon glyphicon-warning-sign editable-click" data-type="text" '
                            . 'data-pk="'.$user['id'].'" '
                            . 'data-type="password" '
                            . 'data-url="'.$this->all_configs['arrequest'][0].'/ajax?act=change-admin-password" '
                            . 'data-title="Введите новый пароль" data-display="false"></i></td>'    
                        . '<td><select class="form-control input-sm" name="roles[' . $user['id'] . ']"><option value=""></option>';
                    
                    $yet1 = array();
                    foreach ( $pers as $per ) {
                        if ( array_key_exists($per['role_id'], $yet1)) {

                        } else {
                            if ( $per['role_id'] == $user['role_id'] ) {
                                $users_html .= '<option selected value="' . $per['role_id'] . '">' . htmlspecialchars($per['role_name']) . '</option>';
                            } else {
                                $users_html .= '<option value="' . $per['role_id'] . '">' . htmlspecialchars($per['role_name']) . '</option>';
                            }
                            $yet1[$per['role_id']] = $per['role_id'];
                        }
                    }
                    

                    $users_html .= '</select></td>'
                        . '<td><input placeholder="введите ФИО" class="form-control input-sm" '
                            .'name="fio[' . $user['id'] . ']" value="' . htmlspecialchars($user['fio']) . '" /></td>'
                        . '<td><input placeholder="введите должность" class="form-control input-sm" name="position[' . $user['id'] . ']" value="' . htmlspecialchars($user['position']) . '" /></td>'
                        . '<td><input placeholder="введите телефон" onkeydown="return isNumberKey(event)" class="form-control input-sm" name="phone[' . $user['id'] . ']" value="' . $user['phone'] . '" /></td>'
                        . '<td><input placeholder="введите email" class="form-control input-sm" name="email[' . $user['id'] . ']" value="' . $user['email'] . '" /></td>
                        <td><input placeholder="" class="form-control input-sm" name="auth_cert_serial[' . $user['id'] . ']" value="' . $user['auth_cert_serial'] . '" /></td>
                        <td><input ' . $cert_checked . ' type="checkbox" name="auth_cert_only[' . $user['id'] . ']" /></td>
  
                        </tr>';
                    $yet[$user['id']] = $user['id'];
                }
            }
        }

        $users_html .= '<a class="btn btn-success send-mess" href="#" >Отправить сообщение</a>';

        $users_html .= '</tbody></table>';
        //if ( $this->all_configs['oRole']->hasPrivilege('edit-user') ) {
            $users_html .= '<input type="submit" name="change-roles" value="Сохранить" class="btn btn-primary" />';
        //}
        $users_html .= '</form></div>
            <div id="upload_avatar" class="modal fade">
              <div class="modal-dialog modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
                      <h4 class="modal-title">Аватар</h4>
                    </div>
                    <div class="modal-body">
                        <div id="fileuploader"></div>
                    </div>
                </div>
              </div>
            </div>
        ';

        // список ролей и ихние доступы
        $users_html .= '<div id="edit_tab_roles" class="tab-pane"><form class="form-horizontal" method="post"><div style="display: inline-block; width: 100%;">';
        // дерево ролей и возможностей
        $aRoles = array();
        foreach ( $pers as $per ) {
            if ( array_key_exists($per['role_id'], $aRoles)) {

                $aRoles[$per['role_id']]['children'][$per['per_id']] = array(
                    'link' => $per['link'],
                    'name' => $per['per_name'],
                    'child' => $per['child'],
                    'checked' => $per['id']
                );

            } else {
                $aRoles[$per['role_id']] = array(
                    'name'=>$per['role_name'],
                    'all' => array(),
                    'date_end'=>$per['date_end'],
                    'avail'=>$per['avail'],
                    'children'=>array(
                        $per['per_id'] => array(
                            'link' => $per['link'],
                            'name' => $per['per_name'],
                            'child' => $per['child'],
                            'checked' => $per['id']
                        )
                    )
                );
            }
            if ( /*array_search($per['per_id'], $aRoles[$per['role_id']]['all']) >=0 &&*/ intval($per['id']) > 0 ) {
                $aRoles[$per['role_id']]['all'][] =  $per['per_id'];
                //array_push($aRoles[$per['role_id']]['all'], $per['per_id']);
            }
        }
        // блок управление ролями
        foreach ( $aRoles as $rid=>$v ) {
            $checked = '';
            if ( $v['avail'] )
                $checked = 'checked';
            $users_html .=  '<ul class="nav nav-list pull-left" style="padding:0 10px"><li class="nav-header"><br><h4>' . htmlspecialchars($v['name']) . '</h4>
                <div class="checkbox"><label><input type="checkbox" '.$checked.' name="active['.$rid.']" />активность</label></div></li>';
            $users_html .=  '<li>Дата конца активности группы</li>';
            $users_html .=  '<li><input class="form-control input-sm datepicker" name="date_end[' . $rid . ']" type="text" value="' . $v['date_end'] . '" ></li>';
            foreach ( $v['children'] as $pid=>$sv ) {
                $checked = '';
                if ( $sv['checked'] )
                    $checked = 'checked';
                $users_html .= '<li><div class="checkbox"><label><input id="per_id_' . $rid . '_' . $pid . '" class="del-' . $rid . '-' . $sv['child'] . '"
                        onchange="per_change(this, \'' . $rid . '-' . $sv['child'] . '\', \'' . $rid . '-' . $pid . '\')"
                        name="permissions[' . $rid . '-' . $pid . ']" ' . $checked . ' type="checkbox" />' .
                    $sv['name'] . '</label></div></li>';
            }
            $users_html .=  '</ul><input type="hidden" name="exist-box[' . $rid . ']" value="' . implode(",", $v['all']) . '" />';
        }
        $users_html .= '</div>';
        //if ( $this->all_configs['oRole']->hasPrivilege('edit-user') ) {
            $users_html .= '<input type="submit" name="create-roles" value="Сохранить" class="btn btn-primary" />';
        //}
        $users_html .= '</form></div>';

        // добавление новой роли
        $users_html .= '<div id="edit_tab_create" class="tab-pane">';
        $users_html .= '
            <form  method="post">
                <fieldset>
                    <legend>Добавление новой роли</legend>
                    <div class="form-group">
                        <label>Название:</label>
                        <input class="form-control" value="" name="name" placeholder="введите название">
                    </div>
                    <div class="form-group">
                        <label>Права доступа:</label>
                        отметьте нужные
                    </div>';
        $yet = 0;
        $roles = array();
        foreach ( $pers as $per ) {
            if ( $yet === 0 ) {
                $yet = $per['role_id'];
                $roles[$per['role_id']] = $per['role_name'];
            } elseif ( $yet != $per['role_id'] ) {
                $roles[$per['role_id']] = $per['role_name'];
                continue;
            }
            $users_html .= '
                <div class="checkbox">
                    <label><input id="per_id_a_' . $per['per_id'] . '" class="del-a-' . $per['child'] . '"
                        onchange="per_change(this, \'a-' . $per['child'] . '\', \'a-' . $per['per_id'] . '\')"
                        type="checkbox" name="permissions[a-' . $per['per_id'] . ']">' . htmlspecialchars($per['per_name']) . '</label>
                </div>';
        }
        //if ( $this->all_configs['oRole']->hasPrivilege('edit-user') ) {
            $users_html .= '<div class="control-group"><div class="controls">
                <input class="btn btn-primary" type="submit" name="add-role" value="создать"></div></div>';
        //}
        $users_html .= '</fieldset></form>';
        $users_html .= '</div>';

        $role_html = '';
        foreach ( $roles as $role_id=>$role_name ) {
            $role_html .= '<option value="' . $role_id . '">' . htmlspecialchars($role_name) . '</option>';
        }
        // добавление нового пользователя
        $users_html .= '<div id="create_tab_user" class="tab-pane">';
        $users_html .= '
            <form method="post">
                <fieldset>
                    <legend>Добавление нового пользователя</legend>
                    <div class="form-group">
                        <label>Логин:</label>
                        <input class="form-control" value="" name="login" placeholder="введите логин">
                    </div>
                    <div class="form-group">
                        <label>E-mail:</label>
                        <input class="form-control" value="" name="email" placeholder="введите e-mail">
                    </div>
                    <div class="form-group">
                        <label>Пароль:</label>
                        <input class="form-control" value="" name="pass" placeholder="введите пароль">
                    </div>
                    <div class="form-group">
                        <label>ФИО:</label>
                        <input class="form-control" value="" name="fio" placeholder="введите фио">
                    </div>
                    <div class="form-group">
                        <label>Должность</label>
                        <input class="form-control" value="" name="position" placeholder="введите должность">
                    </div>
                    <div class="form-group">
                        <label>Телефон</label>
                        <input onkeydown="return isNumberKey(event)" class="form-control" value="" name="phone" placeholder="введите телефон">
                    </div>
                    <div class="form-group">
                        <div class="checkbox">
                            <label><input type="checkbox" name="avail" />Активность</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Роль</label>
                        <select name="role" class="form-control">
                            <option value="">выберите роль</option>
                            ' . $role_html . '
                        </select>
                    </div>';

        //if ( $this->all_configs['oRole']->hasPrivilege('edit-user') ) {
            $users_html .= '<div class="control-group"><div class="controls">
                    <input class="btn btn-primary" type="submit" name="create-user" value="создать"></div></div>';
        //}
        $users_html .= '</fieldset></form>';
        $users_html .= '</div>';

        $users_html .= '</div>';

        return $users_html;
    }

    private function get_users($sort='')
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
            ORDER BY u.avail DESC," . $sort . " u.id
            ")->assoc();

        return $roles;
    }

    private function get_all_roles()
    {

        $per = $this->all_configs['db']->query("
            SELECT r.id as role_id, p.id as per_id, r.name as role_name, r.avail, r.date_end, per.id,
              p.name as per_name, p.link, p.child
            FROM {users_roles} as r
            CROSS JOIN {users_permissions} as p
            LEFT JOIN (SELECT * FROM {users_role_permission})per ON per.role_id=r.id AND per.permission_id=p.id
            ORDER BY role_id, per_id
        ")->assoc();

        return $per;
    }

}