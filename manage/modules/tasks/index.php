<?php

$modulename[] = 'tasks';
$modulemenu[] = 'Задачи';
$moduleactive[] = !$ifauth['is_2'];

//ini_set('xdebug.trace_format', 1);

class tasks
{

    protected $tasks = array();
    protected $all_configs;
    protected $tasks_filer_statuses = array();
    public $count_on_page;

    function __construct(&$all_configs)
    {
        $this->all_configs = $all_configs;
        $this->count_on_page = count_on_page();
        //$this->tasks_filer_statuses = array(0 => 'Откр.', 1 => 'Реш.', 2 => 'Выполн.');
        $this->tasks_filer_statuses = array(0 => 'Открыта', 2 => 'Выполн.');

        //print_r($this->all_configs[oRole]->ifadmin[id]);


        global $input_html;

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'export') {
            $this->export();
        }

        if ($this->can_show_module() == false) {
            return $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p  class="text-error">У Вас не достаточно прав</p></div>';
        }

        // если отправлена форма
        if (count($_POST) > 0)
            $this->check_post($_POST);

        //if ($this->all_configs['ifauth']['is_2']) return false;

        $input_html['mcontent'] = $this->gencontent();
    }

    function can_show_module()
    {
        if ($this->all_configs['oRole']->hasPrivilege('create-task') || 1 == 1 // просмотр доступен всем, но только свои задачи
        ) {
            return true;
        } else {
            return false;
        }
    }

    function check_post($post)
    {
        /**
         * @todo добавить в конфиг 'tasks-manage-page'         =>  11, // для таблицы изменений, модуль управление перемещениями
         */
        $mod_id = $this->all_configs['configs']['tasks-manage-page'];
        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        if (!$user_id) {
            exit;
        }


        // тут может быть фильтр по дате
        if (isset($post['filter-all-tasks'])) {

            $url = '';

            // фильтр по дате
            if (isset($post['date']) && !empty($post['date'])) {
                list($df, $dt) = explode('-', $post['date']);
                $url .= 'df=' . urlencode(trim($df)) . '&dt=' . urlencode(trim($dt));
            }

            if (isset($post['managers']) && !empty($post['managers'])) {
                // фильтр по менеджерам
                if (!empty($url))
                    $url .= '&';
                $url .= 'mg=' . implode(',', $post['managers']);
            }

            if (isset($post['authors']) && !empty($post['authors'])) {
                // фильтр по менеджерам
                if (!empty($url))
                    $url .= '&';
                $url .= 'ath=' . implode(',', $post['authors']);
            }

            if (isset($post['status']) && !empty($post['status'])) {
                // фильтр по статусу
                if (!empty($url))
                    $url .= '&';
                $url .= 'st=' . implode(',', $post['status']);
            }

            $url = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . (empty($url) ? '' : '?' . $url);
            header('Location: ' . $url);
            exit;
        }
        
        header("Location:" . $_SERVER['REQUEST_URI']);
        exit;
    }

    function preload()
    {
        //var_dump($this->all_configs['oRole']->hasPrivilege('create-task'));
        //site-administration
        //exit;
    }

    function gencontent()
    {
        $this->preload();

        $out = '<div class="tabbable"><ul class="nav nav-tabs">';

        //if ($this->all_configs['oRole']->hasPrivilege('accounting')) {
        $out .= '<li><a class="click_tab default" data-open_tab="tasks_alltasks" onclick="click_tab(this, event)" data-toggle="tab" href="#alltasks">'
                . 'Задачи</a></li>';
        //}
        if ($this->all_configs['oRole']->hasPrivilege('create-task')) {
            $out .= '<li><a class="click_tab default" data-open_tab="tasks_newtask" onclick="click_tab(this, event)" data-toggle="tab" href="#newtask">'
                    . 'Создать задачу</a></li>';
        }


        $out .= '</ul><div class="tab-content">';

        $out .= '<div id="alltasks" class="content_tab tab-pane">';
        $out .= '</div><!--#alltasks-->';

        if ($this->all_configs['oRole']->hasPrivilege('create-task')) {
            $out .= '<div id="newtask" class="content_tab tab-pane">';
            $out .= '</div><!--#newtask-->';
        }



        return $out;
    }

    function export()
    {
        $array = array();
        $act = isset($_GET['act']) ? $_GET['act'] : '';

        /*
          // допустимые валюты
          $currencies = $this->all_configs['suppliers_orders']->currencies;

          if ($act == 'contractors_transactions')
          $array = $this->all_configs['suppliers_orders']->get_transactions($currencies, false, null, true, array(), true, true);
          if ($act == 'cashboxes_transactions')
          $array = $this->all_configs['suppliers_orders']->get_transactions($currencies, false, null, false, array(), true, true);
          if ($act == 'reports-turnover')
          $array = $this->accountings_reports_turnover_array();
          //if ($act == 'cashboxes_transactions')
          //    $array = $this->all_configs['suppliers_orders']->get_transactions($currencies, true, 30);
         */
        include_once $this->all_configs['sitepath'] . 'shop/exports.class.php';
        $exports = new Exports();
        $exports->build($array);
    }


    private function files_folder($conf_param){
        return $this->all_configs[$conf_param].'tasks_files/';
    }
    
    private function upload_file($task_id = null){
        $file_uploaded = false;
        $filename = '';
        $folder_path = $tmp_filename = $file_info = $target = '';
        if(isset($_FILES['file']['name']) && trim($_FILES['file']['name'])){
            $image_size = getimagesize($_FILES['file']['tmp_name']);
            if($image_size['mime'] != 'image/jpeg' && $image_size['mime'] != 'image/jpg' 
                    && $image_size['mime'] != 'image/png'){
                return false;
            }else{
                $folder_path = $this->files_folder('path');
                if(!is_dir($folder_path)){
                    mkdir($folder_path, 0777);
                }
                $file_info = pathinfo($_FILES['file']['name']);
                if ($task_id) {
                    $filename = $task_id . '.' . $file_info['extension'];
                    $target = $folder_path . $filename;
                } else {
                    $tmp_filename = md5_file($_FILES['file']['tmp_name']);
                    $target = $folder_path . $tmp_filename;
                }
                if (move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
//                        chmod($target, 0777);
                    $file_uploaded = true;
                }else{
                    $filename = '';
                }
            }
        }
        if ($task_id) {
            return $filename;
        } else {
            return array(
                'file_uploaded' => $file_uploaded,
                'folder_path' => $folder_path,
                'file_info' => $file_info,
                'target' => $target,
            );
        }
    }

    function ajax()
    {
        $data = array(
            'state' => false
        );

        $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
        //$mod_id = $this->all_configs['configs']['tasks-manage-page'];



        $act = isset($_GET['act']) ? $_GET['act'] : '';

        // проверка доступа
        if ($this->can_show_module() == false) {
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode(array('message' => 'Нет прав (238)', 'state' => false));
            exit;
        }

        $this->preload();

        // грузим табу
        if ($act == 'tab-load') {
            if (isset($_POST['tab']) && !empty($_POST['tab'])) {
                header("Content-Type: application/json; charset=UTF-8");

                //для отладки, теста
                //echo json_encode(array('message' => $_POST['tab'], 'state' => false)); exit;

                if (method_exists($this, $_POST['tab'])) {
                    $function = call_user_func_array(
                            array($this, $_POST['tab']), array((isset($_POST['hashs']) && mb_strlen(trim($_POST['hashs'], 'UTF-8')) > 0) ? trim($_POST['hashs']) : null)
                    );
                    echo json_encode(array('html' => $function['html'], 'state' => true, 'functions' => $function['functions']));
                } else {
                    echo json_encode(array('message' => 'Не найдено', 'state' => false));
                }
                exit;
            }
        }



        // добавление новой
        if ($act == 'task-create') {
            $data['state'] = true; // success trigger to true , and then turn to off if catch error
            // права
            if ($data['state'] == true && !$this->all_configs['oRole']->hasPrivilege('create-task')) {
                $data['state'] = false; // success trigger to false
                //$data['message'] = 'Нет прав';
                $data['message'] = $this->all_configs['oRole']->hasPrivilege('create-task');
            }

            // task title
            if ($data['state'] == true && !isset($_POST['task_title']) || mb_strlen(trim($_POST['task_title']), 'UTF-8') == 0) {
                $data['state'] = false; //success trigger to false
                $data['message'] = 'Введите заголовок';
            }

            // task text
            if ($data['state'] == true && !isset($_POST['task_text']) || mb_strlen(trim($_POST['task_text']), 'UTF-8') == 0) {
                $data['state'] = false; //success trigger to false
                $data['message'] = 'Введите описания задачи';
            }

            $upload_file = $this->upload_file();
            if($upload_file === false){
                $data['state'] = false;
                $data['message'] = 'Загружайте только изображения';
            }else{
                $file_uploaded = $upload_file['file_uploaded'];
                $folder_path = $upload_file['folder_path'];
                $file_info = $upload_file['file_info'];
                $target = $upload_file['target'];
            }
           
            if ($data['state'] == true) {
                // prepare variables 
                $worker_id = (int) ($_POST['task_worker']); // temp - from post form data ? 
                $author_id = $this->all_configs['oRole']->ifadmin['id']; //
                $date_deadline = date('Y-m-d 23:59:59', strtotime($_POST['task_deadline']));
                $task_title = trim($_POST['task_title']);
                $task_text = trim($_POST['task_text']);
                $task_price = ( isset($_POST['task_price']) ) ? (int) $_POST['task_price'] : null;

                $task_id = $this->all_configs['db']->query('INSERT IGNORE INTO {tasks}
					( date_add, date_update, date_deadline, author_id, worker_id, title, body, price, filename) VALUES (now(), now(), ?, ?, ?, ?, ?, ?, null)', array($date_deadline, $author_id, $worker_id, $task_title, $task_text, $task_price), 'id');

                if ($file_uploaded) {
                    $filename = $task_id . '.' . $file_info['extension'];
                    rename($target, $folder_path . $filename);
                    $this->all_configs['db']->query("UPDATE {tasks} SET filename = ? "
                            . "WHERE id = ?i", array($filename, $task_id));
                }
            }
        }


        // изменение задачи
        if ($act == 'task-save') {
            $data['state'] = true; // success trigger to true , and then turn to off if catch error
            // task id
            $task_id = null;
            if (isset($_POST['task_id'])) {
                $task_id = (int) $_POST['task_id'];
            }
            if (!$task_id) {
                $data['state'] = false; //success trigger to false
                $data['message'] = 'Не указан номер задачи';
                $task_id = null;
            }

            $task_state = (int) ( $_POST['task_state'] );
            $current_task_state = $this->get_task_state($task_id);


            // task title
            if ($data['state'] == true && !isset($_POST['task_title']) || mb_strlen(trim($_POST['task_title']), 'UTF-8') == 0) {
                $data['state'] = false; //success trigger to false
                $data['message'] = 'Нет описания задачи';
            }

            // task text
            if ($data['state'] == true && !isset($_POST['task_text']) || mb_strlen(trim($_POST['task_text']), 'UTF-8') == 0) {
                $data['state'] = false; //success trigger to false
                $data['message'] = 'Нет описания задачи';
            }

            // access to close task as "done" - only for admin
            if ($task_state == 2 && !$this->all_configs['oRole']->hasPrivilege('create-task')) {
                $data['state'] = false; //success trigger to false
                $data['message'] = 'Нет прав закрыть задачу';
            }

            if (!$this->all_configs['oRole']->hasPrivilege('create-task') && $current_task_state == 2) {
                $data['state'] = false;
                $data['message'] = 'Изменения невозможны . Обратитесь к руководителю';
            }
            
            $current_filename = $this->all_configs['db']->query("SELECT filename FROM {tasks} "
                                                                           ."WHERE id = ?i", array($task_id), 'el');
            $filename = '';
            if(!$current_filename){
                $file_upload = $this->upload_file($task_id);
                if($file_upload === false){
                    $data['state'] = false;
                    $data['message'] = 'Файл не изображение';
                }else{
                    $filename = $file_upload;
                }
            }else{
                $filename = $current_filename;
            }
            
            if ($data['state'] == true) {

                // prepare variables
                $current_task_state = $this->get_task_state($task_id);
                $worker_id = ( isset($_POST['task_worker']) ) ? (int) $_POST['task_worker'] : 'null';
                $author_id = $this->all_configs['oRole']->ifadmin['id'];
                $date_done = ( $task_state == 1 ) ? date("Y-m-d H:i:s") : 'null';
                $date_deadline = ( isset($_POST['task_deadline']) ) ? date('Y-m-d 23:59:59', strtotime($_POST['task_deadline'])) : 'null';
                $task_price = ( isset($_POST['task_price']) ) ? (int) $_POST['task_price'] : 'null';
                $task_title = trim($_POST['task_title']);
                $task_text = trim($_POST['task_text']);

                // admin can change whole task parameters
                if ($this->all_configs['oRole']->hasPrivilege('create-task')) {

                    if ($task_state == 1 || $task_state == 2) { //$task_state == 1
                        $sql = 'UPDATE {tasks} set  worker_id = ?, date_deadline = ? ,title = ?, body = ?, date_done= NOW(), state = ?,  price = ?, filename = ? WHERE id=?';
                    } else {
                        $sql = 'UPDATE {tasks} set  worker_id = ?, date_deadline = ?, title = ?, body = ?,  state = ?, price = ?, filename = ? WHERE id=?';
                    }
                    $sql_parameters = array($worker_id, $date_deadline, $task_title, $task_text, $task_state, $task_price, $filename, $task_id);
                }

                // user can change only task status
                else {
                    $sql = 'UPDATE {tasks} set state = ? WHERE id=? LIMIT 1';
                    $sql_parameters = array($task_state, (int) $_POST['task_id']);
                }

                $task_id = $this->all_configs['db']->query($sql, $sql_parameters, 'id');
            }
        }



        // task-edit 
        if ($act == 'task-edit') {
            $data['state'] = true;
            //preapere variables
            $task_id = isset($_POST['object_id']) ? intval($_POST['object_id']) : null;

            // fetch data from DB to $task array
            $sql = 'SELECT * FROM {tasks} where id=?';
            $tasks = $this->all_configs['db']->query($sql, array($task_id))->assoc();
            $task = $tasks[0];

            $date_add = do_nice_date($task['date_add']);
            $date_update = do_nice_date($task['date_update']);
            $date_deadline = ( $task['date_deadline'] != '0000-00-00 00:00:00') ? date("d.m.Y", strtotime($task['date_deadline'])) : '';

            // disable controls if no admin and task closed
            $disable_tag = '';
            if (!$this->all_configs['oRole']->hasPrivilege('create-task')) {
                $disable_tag = 'disabled="disabled"';
            }

            // show close day + disable task state when controls disabled
            $close_day = '';
            $disable_state_change = '';

            if ($task['state'] == 2 || $task['state'] == 1) {
                $close_day = ' Решена : <b>' . do_nice_date($task['date_done']) . "</b><br>";
            }
            if ($task['state'] == 2) {
                $disable_state_change = $disable_tag;
            }
            
            //modal
            $out = "<h3>Задача #" . $task_id . "</h3> " . $close_day;
            $out .= '<form id="edit_task_form">'
                    . '<input type="hidden" value="' . $task_id . '" name="task_id" >'
                    . '<div class="form-group">'
                        . '<label>Открыта:</label>'.$date_add.''
                    . '</div>'
                    . '<div class="form-group">'
                        . '<label>Обновлена:</label>'. $date_update
                    . '</div>'
                    . '<div class="form-group">'
                        . '<label for="taskTitle">Тема</label>'
                        . '<input ' . $disable_state_change . $disable_tag . ' type="text" value="' . htmlspecialchars($task['title']) . '" name="task_title" id="taskTitle" class="form-control" >'
                    . '</div>'
                    . '<div class="form-group">'
                        . '<label for="task_text">Сообщение</label>'
                        . '<textarea ' . $disable_state_change . $disable_tag . ' type="text" name="task_text" id="task_text" placeholder="" class="form-control" rows="3" >' . htmlspecialchars($task['body']) . '</textarea>'
                    . '</div>'
                    . '<div class="form-group">'
                        . '<label for="task_deadline">Дата исполнения</label>'
                        . '<input ' . $disable_tag . ' value="' . $date_deadline . '" class="form-control" data-provide="datepicker" data-date-format="dd.mm.yyyy" name="task_deadline" id="task_deadline" >'
                    . '</div>'
                    . '<div class="form-group">'
                        . '<label for="taskWorker">Исполнитель&nbsp;</label><br>'
                        . $this->worker_selector($task['worker_id'], $disable_tag)
                    . '</div>'
                    . '<div class="form-group">'
                        . '<label for="task_price">Цена задачи</label>'
                        . '<input ' . $disable_tag . ' value="' . $task['price'] . '" class="form-control" name="task_price" id="task_price"  >'
                    . '</div>'
                    . '<div class="form-group">'
                        . '<label for="taskState">Статус</label>'
                        . $this->get_state_selector($task['state'], $disable_tag)
                    . '</div>'
                    . '<div class="form-group">'
                        . '<label>Файл</label>';
            if ($task['filename']) {
                $out .= '<a class="file_link" href="' . $this->all_configs['prefix'] . 'tasks_files/' . $task['filename'] . '" target="_blank">посмотреть</a>';
            } else {
                $out .= '<input name="file" type="file" '.$disable_tag.'>';
            }
            
            $out .=   '</div>'
                    . '';
            $out .= '</form>';

            $data['content'] = $out;
            $data['btns'] = '<button ' . $disable_state_change . ' id="new_task_button" class="btn btn-success" onclick="task_save(this);">Сохранить</button>';
            $data['width'] = 'true';
            $data['message'] = '';
            $data['functions'] = array('reset_multiselect()');
        }

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

    function tasks_alltasks()
    {
        //нужно ли проверить права?
        $out = '<div class="row-fluid">';

        //filters
        $date = (isset($_GET['df']) ? htmlspecialchars(urldecode($_GET['df'])) : ''/*date('01.m.Y', time())*/)
                . (isset($_GET['df']) || isset($_GET['dt']) ? ' - ' : '')
                . (isset($_GET['dt']) ? htmlspecialchars(urldecode($_GET['dt'])) : ''/*date('t.m.Y', time())*/);
        
        if (!$date) {
            $date = date("01.m.Y") . ' - ' . date("d.m.Y", time()+60*24*3600);
        }
        
        $authors = $this->all_configs['oRole']->get_users_by_permissions('create-task');
        $managers = $this->all_configs['oRole']->get_users_by_permissions('edit-clients-orders');
        
        $out .= '<div class="span2">'
                . '<form method="post">';
        $out .= '<div class="form-group"><label>Статус:</label>';
        $out .= '<select class="multiselect form-control" name="status[]" multiple="multiple">';
        foreach ($this->tasks_filer_statuses as $os_id=>$os_v) {
            $out .= '<option ' . ((isset($_GET['st']) && in_array($os_id, explode(',', $_GET['st']))) ? 'selected' : '');
            $out .= ' value="' . $os_id . '">' . htmlspecialchars($os_v) . '</option>';
        }
        $out .= '</select></div>';
        if ($this->all_configs['oRole']->hasPrivilege('create-task')) {
            $out .= '<div class="form-group"><label>Автор:</label>';
            $out .= '<select class="multiselect form-control report-filter" name="authors[]" multiple="multiple">';
            $out .= build_array_tree($authors, ((isset($_GET['ath'])) ? explode(',', $_GET['ath']) : array()));
            $out .= '</select></div>';
            $out .= '<div class="form-group"><label>Менеджер:</label>';
            $out .= '<select class="multiselect form-control report-filter" name="managers[]" multiple="multiple">';
            $out .= build_array_tree($managers, ((isset($_GET['mg'])) ? explode(',', $_GET['mg']) : array()));
            $out .= '</select></div>';
        }
        $out .= '<div class="form-group"><label>Даты:</label>';
        $out .= '<input type="text" placeholder="Дата" name="date" class="daterangepicker form-control" value="' . $date . '" /></div>';
        $out .= '<input class="btn btn-primary" type="submit" name="filter-all-tasks" value="Фильтровать" />'
                . '</form>';
        $out .= '</div>'; //row-2, end filers
        
        
        // worker filter
        $author_id = $this->all_configs['oRole']->ifadmin['id'];
        $sql_filter = '';
        $sql_filter = (!$this->all_configs['oRole']->hasPrivilege('create-task')) ? ' AND worker_id = ' . $author_id : $sql_filter;

        
        // фильтр по дате
        $day_from = date("01.m.Y") . ' 00:00:00';
        $day_to = date("d.m.Y", time()+60*24*3600) . ' 23:59:59';
        if (array_key_exists('df', $_GET) && strtotime($_GET['df']) > 0)
            $day_from = $_GET['df'] . ' 00:00:00';
        if (array_key_exists('dt', $_GET) && strtotime($_GET['dt']) > 0)
            $day_to = $_GET['dt'] . ' 23:59:59';
        $query = $this->all_configs['db']->makeQuery('AND t.date_add BETWEEN STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s") '
                . 'AND STR_TO_DATE(?, "%d.%m.%Y %H:%i:%s") ', array($day_from, $day_to));
        
        //статус
        if (array_key_exists('st', $_GET)) {
            $st = explode(',', $_GET['st']);
            $query .= $this->all_configs['db']->makeQuery('AND t.state IN (?li) ', array($st));
        }
        //автор
        if (array_key_exists('ath', $_GET)) {
            $ath = explode(',', $_GET['ath']);
            $query .= $this->all_configs['db']->makeQuery('AND t.author_id IN (?li) ', array($ath));
        }
        //менеджер
        if (array_key_exists('mg', $_GET)) {
            $mg = explode(',', $_GET['mg']);
            $query .= $this->all_configs['db']->makeQuery('AND t.worker_id IN (?li) ', array($mg));
        }
        
        $sql = 'SELECT 
		t.id,
		t.state,
		t.price,
		t.date_add,
		t.date_done,
		t.date_deadline,
		t.title,
		t.body,
		t.filename,
		w.fio AS wfio,
		w.login AS wlogin,
		a.fio AS afio,
		a.login AS alogin
		FROM {tasks} as t
		INNER JOIN {users} as w ON w.id=t.worker_id
        INNER JOIN {users} as a ON a.id=t.author_id
        WHERE 1=1 
		' . $sql_filter. ' ' . $query . ' ORDER BY t.id DESC';

        $tasks = $this->all_configs['db']->query($sql, array())->assoc();

        $out .= '<div class="span10">';
        $out .= '<table class="tasks-table table table-hover">' //table-striped
                . '<thead><tr>'
                . '<td>№</td>'
                . '<td>Статус</td>'
                . '<td>Тема</td>'
                . '<td>Автор</td>'
                . '<td>Исполнитель</td>'
                . '<td>Создана</td>'
                . '<td>Дедлайн</td>'
                //. '<td>Выполнена</td>'
                //. '<td>Текст</td>'
                //. '<td> </td>'
                . '<td>Стоимость</td>'
                . '<td></td>'
                . '</tr></thead>'
                . '<tbody>';
        foreach ($tasks as $task) {

            $worker = ($task['wfio']) ? $task['wfio'] : $task['wlogin'];
            $author = ($task['afio']) ? $task['afio'] : $task['alogin'];

            //подсвечиваем строку: красная - просрочена, зеленая выполнена (но може и не вовремя выполенна)
            //проверяем вовремя ли выполнена задачи (ок); просрочена или не вовремя (Х)
            $state_color = '';
            $task_ontime_class = '';
            $state_now_deadline_delta = strtotime($task['date_deadline']) - time();
            if ($state_now_deadline_delta < 0 && $task['state'] == 0) {
                $state_color = 'error';
                $task_ontime_class = 'icon-remove';
            }
            if ($state_now_deadline_delta < 24 * 3600 && $state_now_deadline_delta > 0 && $task['state'] == 0) {
                $state_color = 'warning';
            }
            if ($task['state'] == 1) {$state_color = 'info';}
            if ($task['state'] == 2) {$state_color = 'success muted';}

            $state_done_deadline_delta = strtotime($task['date_deadline']) - strtotime($task['date_done']);
            if ($task['date_done'] && $state_done_deadline_delta < 0) {
                $task_ontime_class = 'icon-remove';
            }
            if ($task['date_done'] && $state_done_deadline_delta >= 0) {
                $task_ontime_class = 'icon-ok';
            }
           
            $out .= '<tr class="' . $state_color .' task_row" style="cursor: pointer;" data-o_id="' . $task['id'] . '" >'
                    . '<td>#'. $task['id'] . '</td>'
                    . '<td>' . $this->get_task_state_name($task['state']) . '</td>'
                    . '<td>' . htmlspecialchars($task['title']) . '</td>'
                    . '<td>' . $author . '</td>'
                    . '<td>' . $worker . '</td>'
                    . '<td>' . do_nice_date($task['date_add'], true, false) . '</td>'
                    . '<td>' . do_nice_date($task['date_deadline'], true, false) . '</td>'
                    //. '<td>' . do_nice_date($task['date_done']) . '</td>'
                    //. '<td>' . htmlspecialchars($task['body']) . '</td>'
                    //. '<td>' . ($task['filename'] ? '<a class="file_link" href="' . $this->all_configs['prefix'] . 'tasks_files/' . $task['filename'] . '" target="_blank">Файл</a>' : '') . '</td>'
                    . '<td>' . $task['price'] . '</td>'
                    . '<td> <i class="'.$task_ontime_class.'"></i> </td>'
                    . '</tr>';
        }

        $out .= '</tbody></table></div>'; // row-10
        $out .= '</div>';   // row     

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    function tasks_newtask()
    {
        //нужно ли проверить права? 
        // eanufriev - смысл проверки ? если активных действий никаких- а при вставке будет проверка .
        // form data 
        $out = '<form id="new_task_form" method="POST" enctype="multipart/form-data">';
        $out .= '<div class="form-group"><input type="text" name="task_title" id="task_title" placeholder="О чём задача" class="form-control"></div>';
        $out .= '<div class="form-group"><textarea type="text" name="task_text" id="task_text" placeholder="Опишите задачу" class="form-control" rows="3" ></textarea></div>';
        $out .= '<div class="form-group"><label>Дата исполнения</label><input class="form-control" data-provide="datepicker" data-date-format="dd.mm.yyyy" name="task_deadline" id="task_deadline" value="'.date('d.m.Y', time()).'"></div>';
        $out .= '<div class="form-group"><label>Цена задачи</label><input class="form-control" name="task_price" id="task_price" value="0"></div>';
        $out .= '<div class="form-group"><label>Файл</label><br><input name="file" type="file"></div>';
        $out .= '<div class="form-group">' . $this->worker_selector() . '</div><input type="button" id="new_task_button" value="создать новую задачу"  class="btn btn-success" onclick="task_create(this);">';
        $out .= '</form>';

        return array(
            'html' => $out,
            'functions' => array('reset_multiselect()'),
        );
    }

    function worker_selector($worker_id = null, $disable_tag = null)
    {
        // whole users from users table 
        $users = $this->all_configs['db']->query(
                        'SELECT DISTINCT u.id, CONCAT(u.fio, " ", u.login) as name FROM {users} as u', array())->assoc();

        $options = '';
        foreach ($users as $user) {
            $selected = ($user['id'] == $worker_id) ? 'selected' : '';
            $options .= '<option ' . $selected . ' value="' . $user['id'] . '">' . htmlspecialchars($user['name']) . '</option>';
        }

        $worker_selector = '<select class=" multiselect input-small" name="task_worker" id="taskWorker" ' . $disable_tag . '  >'
                . $options . '</select>';

        return $worker_selector;
    }

    function get_state_selector($status_id, $disable_tag = '')
    {
        $out = '<select class="form-control" ' . $disable_tag . ' name="task_state" id="taskState" >';
        foreach ($this->tasks_filer_statuses as $key => $value) {
            $out .= '<option ' . ($status_id == $key ? 'selected' : '') . ' value ="'.$key.'">'.$value.'</option>';
        }
        $out .= '</select>';
        return $out;
    }

    function get_task_state_name($state_id)
    {
        return $this->tasks_filer_statuses[$state_id];
    }

    function get_task_state($task_id)
    {
        return  $this->all_configs['db']->query('SELECT state FROM {tasks} where id=?', array($task_id))->el();
        
    }

}
