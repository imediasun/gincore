<?php

// настройки
$modulename[170] = 'forms';
$modulemenu[170] = l('form_modulemenu');  //карта сайта

$moduleactive[170] = !$ifauth['is_2'];

class forms{

    protected $all_configs;
    private $lang;
    private $def_lang;
    private $langs;
    function __construct($all_configs, $lang, $def_lang, $langs, $init = true){
        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->langs = $langs;
        $this->all_configs = $all_configs;
        
        global $input_html, $ifauth;

        if ( !$this->all_configs['oRole']->hasPrivilege('site-administration') ) {
            return $input_html['mcontent'] = '<div class="span3"></div>
                <div class="span9"><p  class="text-error">У Вас нет прав для просмотра форм</p></div>';
        }

        if (isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax') {
            $this->ajax();
        }

        if ($ifauth['is_2']) {
            exit;
        }

        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

    private function form($form = array()){

        $html = '
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="active" value="1"'.
                            (($form && $form['active']) || !$form ? ' checked="checked"' : '').'> активно
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label>Название</label>
                <input type="text" class="form-control" name="name" value="'.($form ? htmlspecialchars($form['name']) : '').'">
            </div>
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="send_result" value="1"'.
                        (($form && $form['send_result']) || !$form ? ' checked="checked"' : '').'> отправить результат на эл. адрес
                    </label>
                </div>
            </div>
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="send_result_to_user" value="1"'.
                            (($form && $form['send_result_to_user']) || !$form ? ' checked="checked"' : '').'> отправить результат пользователю на эл. адрес
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label>Заголовок письма:</label>
                <input class="form-control" type="text" name="user_result_title" value="'.($form ? htmlspecialchars($form['user_result_title']) : '').'">
            </div>
            <div class="form-group">
                <label>Текст результата для пользователя:</label>
                <textarea class="form-control" name="user_result_text">'.($form ? htmlspecialchars($form['user_result_text']) : '').'</textarea>
            </div>
        ';

        $content = file_get_contents($this->all_configs['sitepath'] . 'ajax_forms.php');
        preg_match_all("/function {1,}ajax_forms_([a-zA-z0-9]+)/", $content, $matches);// {1,}\(.*\)
        if (count($matches) == 2 && isset($matches[1])) {
            $html .= '<div class="form-group"><label>Функция:</label><select name="function" class="form-control"><option value="">Выберите</option>';
            foreach ($matches[1] as $func) {
                $checked = ($form && $form['function'] == 'ajax_forms_' . trim($func) ? 'selected' : '');
                $html .= '<option ' . $checked . ' value="ajax_forms_' . trim($func) . '">' . $func . '</option>';
            }
            $html .= '</select></div>';
        }

        return $html;
    }

    private function genmenu(){

        $frms = $this->all_configs['db']->query("SELECT id, active FROM {forms}")->assoc('id');
        if($frms){
            $translates = get_few_translates(
                    'forms', 
                    'forms_id', 
                    $this->all_configs['db']->makeQuery("forms_id IN(?q)", array(implode(',', array_keys($frms))))
                );
            $out = '<h4>'.l('form_list').'</h4><ul>';
            foreach($frms as $form){
                $form = translates_for_page($this->lang, $this->def_lang, $translates[$form['id']], $form, true);
                $out .= '
                    <li'.(!$form['active'] ? ' style="text-decoration: line-through"' : '').'>
                        <a '.(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == $form['id'] ? 'style="font-weight: bold"' : '').' href="'.$this->all_configs['prefix'].'forms/'.$form['id'].'">
                            '.($form['name'] ?: 'id '.$form['id']).'
                        </a>
                    </li>';
            }
        }
        $out .= '</ul><br><a href="'.$this->all_configs['prefix'].'forms/add_new">'.l('create').'</a>';

        return $out;
    }

    private function field_form($field = array()){

        $form = '
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="active" value="1"'.
                            (($field && $field['active']) || !$field ? ' checked="checked"' : '').'> активно
                    </label>
                </div>
            </div>
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="required" value="1"'.
                            (($field && $field['active']) || !$field ? ' checked="checked"' : '').'> обязательное
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label>Тип поля</label>
                <select name="type" class="form-control">
                    <option value="text">text</option>
                    <option value="phone">phone</option>
                    <option value="textarea">textarea</option>
                    <option value="checkbox">checkbox</option>
                </select>
            </div>
            <div class="form-group">
                <label>Тип данных</label>
                <select name="data_type" class="form-control">
                    <option value="">обычные</option>
                    <option value="email">эл. адрес</option>
                </select>
            </div>
            <div class="form-group">
                <label>Название</label>
                <input class="form-control" type="text" name="name" value="'.($field ? htmlspecialchars($field['name']) : '').'">
            </div>
            <div class="form-group">
                <label>Приоритет</label>
                <input type="text" class="form-control" name="prio" value="'.($field ? $field['prio'] : '0').'">
            </div>
        ';

        return $form;
    }

    private function gencontent(){

        $out = '';
        if(isset($this->all_configs['arrequest'][1])){

            if(is_numeric($this->all_configs['arrequest'][1])){

                if(!isset($this->all_configs['arrequest'][2])){

                    $form = $this->all_configs['db']->query("SELECT * FROM {forms} WHERE id = ?i", array($this->all_configs['arrequest'][1]), 'row');
                    $form_langs = $this->all_configs['db']->query("SELECT *
                                              FROM {forms_strings} 
                                              WHERE forms_id = ?i", array($this->all_configs['arrequest'][1]), 'assoc:lang');
                    $form = translates_for_page($this->lang, $this->def_lang, $form_langs, $form);
                    
                    $fields_table = '';
                    $fields_arr = $this->all_configs['db']->query("SELECT * FROM {forms_fields} WHERE form_id = ?i ORDER BY prio", array($this->all_configs['arrequest'][1]), 'assoc:id');
                    $fields_header = '
                        <div class="table-responsive"><table class="table table-bordered table-hover table-condensed">
                            <thead>
                                <tr>
                                    <th> </th>
                    ';
                    if($fields_arr){
                        $translates = get_few_translates(
                            'forms_fields', 
                            'field_id', 
                            $this->all_configs['db']->makeQuery("field_id IN(?q)", array(implode(',', array_keys($fields_arr))))
                        );
                        $fields_table = '
                            <form method="post" action="'.$this->all_configs['prefix'].'forms/'.$this->all_configs['arrequest'][1].'/update_fields">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th width="14"> </th>
                                            <th>Название</th>
                                            <th>Тип поля</th>
                                            <th>Тип данных</th>
                                            <th>Обязательное</th>
                                            <th>активное</th>
                                            <th>приоритет</th>
                                        </tr>
                                    </thead>
                        ';
                        foreach($fields_arr as $field){
                            $field = translates_for_page($this->lang, $this->def_lang, $translates[$field['id']], $field);
                            $fields_header .= '
                                <th>'.$field['name'].'</th>
                            ';
                            $fields_table .= '
                                <tr>
                                    <td><a href="'.$this->all_configs['prefix'].'forms/'.$this->all_configs['arrequest'][1].'/del/'.$field['id'].'" class="glyphicon glyphicon-remove" onclick="return confirm(\'Удалить?\');"></a></td>
                                    <td><input class="form-control" type="text" name="fields['.$field['id'].'][name]" value="'.$field['name'].'"</td>
                                    <td>
                                        <select class="form-control" name="fields['.$field['id'].'][type]">
                                            <option value="text"'.($field['type'] == 'text' ? ' selected="selected"' : '').'>text</option>
                                            <option value="phone"'.($field['type'] == 'phone' ? ' selected="selected"' : '').'>phone</option>
                                            <option value="textarea"'.($field['type'] == 'textarea' ? ' selected="selected"' : '').'>textarea</option>
                                            <option value="checkbox"'.($field['type'] == 'checkbox' ? ' selected="selected"' : '').'>checkbox</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select class="form-control" name="fields['.$field['id'].'][data_type]">
                                            <option value=""'.(!$field['data_type'] ? ' selected="selected"' : '').'>обычные</option>
                                            <option value="email"'.($field['data_type'] == 'email' ? ' selected="selected"' : '').'>эл. адрес</option>
                                        </select>
                                    </td>
                                    <td><input name="fields['.$field['id'].'][required]" type="checkbox"'.($field['required'] ? ' checked="checked"' : '').'></td>
                                    <td><input name="fields['.$field['id'].'][active]" type="checkbox"'.($field['active'] ? ' checked="checked"' : '').'></td>
                                    <td><input class="form-control" style="width: 50px" type="text" name="fields['.$field['id'].'][prio]" value="'.$field['prio'].'"</td>
                                </tr>
                            ';
                        }
                        $fields_table .= '
                                </table>
                                <input type="submit" class="btn btn-primary" value="'.l('save').'">
                            </form>
                        ';
                    }

                    $fields_header .= '
                                <th>'.l('дата').'</th>
                                <th>со страницы</th>
                                <th>пришли на страницу с</th>
                            </tr>
                        </thead>
                    ';
                    // данные от юзверов
                    $user_data = '';
                    $users = $this->all_configs['db']->query("SELECT * FROM {forms_users} WHERE form_id = ?i ORDER BY date DESC", array($this->all_configs['arrequest'][1]), 'assoc:id');
                    if($users){
                        $user_data = $fields_header;

                        $form_data = $this->all_configs['db']->query("SELECT *, (SELECT prio FROM {forms_fields} WHERE id = fd.field_id) as prio
                                                 FROM {forms_data} as fd
                                                 WHERE user_id IN (?q) ORDER BY prio", array(implode(',', array_keys($users))), 'assoc');
                        foreach($users as $user){
                            $fdata = '';
                            foreach($form_data as $data){
                                if($data['user_id'] == $user['id']){
                                    $fdata .= '<td>'.htmlspecialchars($data['value']).'</td>';
                                }
                            }
                            $user_data .= '
                                <tr>
                                    <td><a href="'.$this->all_configs['prefix'].'forms/'.$this->all_configs['arrequest'][1].'/del_data/'.$user['id'].'" class="glyphicon glyphicon-remove" onclick="return confirm(\'Удалить?\');"></a></td>
                                    '.$fdata.'
                                    <td>'.$user['date'].'</td>
                                    <td><div>'.htmlspecialchars($user['from_page']).'</div></td>
                                    <td><div style="word-break:break-all">'.htmlspecialchars($user['referal']).'</div></td>
                                </tr>
                            ';
                        }
                        $user_data .= '</table></div>';
                    }


                    $out = '
                        <h3>Форма «'.$form['name'].'»</h3>
                        Код для вставки в контент: <span style="color: green">{-form_'.$form['id'].'-}</span> <br>
                        Вставте этот код в нужном месте на странице
                        <br><br>
                        <div class="tabbable">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#form" data-toggle="tab">Форма</a></li>
                                <li><a href="#fields" data-toggle="tab">Поля</a></li>
                                <li><a href="#user_data" data-toggle="tab">Введенные данные</a></li>
                            </ul>
                            <div class="tab-content">
                                <div class="tab-pane active" id="form">
                                    <form method="post" action="'.$this->all_configs['prefix'].'forms/'.$this->all_configs['arrequest'][1].'/update">
                                        '.$this->form($form).'
                                        <div class="form-group">
                                            <input type="submit" class="btn btn-primary" value="'.l('save').'">
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane" id="fields">
                                    '.$fields_table.'<br>
                                    <fieldset>
                                        <legend>Добавить новое</legend>
                                        <form action="'.$this->all_configs['prefix'].'forms/'.$this->all_configs['arrequest'][1].'/save_new_field" method="post">
                                            '.$this->field_form().'
                                            <div class="form-group">
                                                <input type="submit" class="btn btn-primary" value="'.l('Добавить').'">
                                            </div>
                                        </form>
                                    </fieldset>
                                </div>
                                <div class="tab-pane" id="user_data">
                                    '.$user_data.'
                                </div>
                            </div>
                        </div>
                    ';

                }else{

                    if($this->all_configs['arrequest'][2] == 'del_data' && isset($this->all_configs['arrequest'][3])){
                        $this->all_configs['db']->query("DELETE FROM {forms_users} WHERE id = ?i", array($this->all_configs['arrequest'][3]));
                        $this->all_configs['db']->query("DELETE FROM {forms_data} WHERE user_id = ?i", array($this->all_configs['arrequest'][3]));
                        header('Location: '.$this->all_configs['prefix'].'forms/'.$this->all_configs['arrequest'][1].'#user_data');
                    }

                    if($this->all_configs['arrequest'][2] == 'del' && isset($this->all_configs['arrequest'][3])){
                        $this->all_configs['db']->query("DELETE FROM {forms_fields} WHERE id = ?i", array($this->all_configs['arrequest'][3]));
                        header('Location: '.$this->all_configs['prefix'].'forms/'.$this->all_configs['arrequest'][1].'#fields');
                    }

                    if($this->all_configs['arrequest'][2] == 'update_fields'){
                        foreach($_POST['fields'] as $field_id => $field_data){
                            $active = isset($field_data['active']) ? 1 : 0;
                            $type = isset($field_data['type']) ? trim($field_data['type']) : '';
                            $data_type = isset($field_data['data_type']) ? trim($field_data['data_type']) : '';
                            $required = isset($field_data['required']) ? 1 : 0;
                            $this->all_configs['db']->query("UPDATE {forms_fields} SET type = ?, data_type = ?, required = ?i, active = ?i, prio = ?i
                                        WHERE id = ?i", array($type, $data_type, $required, $active, $field_data['prio'], $field_id));
                            $this->all_configs['db']->query("
                                INSERT INTO {forms_fields_strings}(field_id, name, lang)
                                VALUES(?i:id, ?:name, ?:lang)
                                ON DUPLICATE KEY UPDATE name = ?:name", 
                                array(
                                    'id' => $field_id,
                                    'lang' => $this->lang,
                                    'name' => $field_data['name'], 
                                )
                            ); // query
                        }
                        header('Location: '.$this->all_configs['prefix'].'forms/'.$this->all_configs['arrequest'][1].'#fields');
                    }

                    if($this->all_configs['arrequest'][2] == 'save_new_field'){
                        $form_id = $this->all_configs['arrequest'][1];
                        $active = isset($_POST['active']) ? 1 : 0;
                        $type = isset($_POST['type']) ? trim($_POST['type']) : '';
                        $data_type = isset($_POST['data_type']) ? trim($_POST['data_type']) : '';
                        $required = isset($_POST['required']) ? 1 : 0;
                        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
                        $prio = isset($_POST['prio']) ? trim($_POST['prio']) : '';

                        $id = $this->all_configs['db']->query("INSERT INTO {forms_fields}(form_id, type, data_type, required, prio, active)
                                          VALUES(?i, ?, ?, ?i, ?i, ?i)", array(
                                              $form_id, $type, $data_type, $required, $prio, $active
                                          ), 'id');
                        $this->all_configs['db']->query("
                            INSERT INTO {forms_fields_strings}(field_id, name, lang)
                            VALUES(?i:id, ?:name, ?:lang)", 
                            array(
                                'id' => $id,
                                'lang' => $this->lang,
                                'name' => $name, 
                            )
                        ); // query

                        header('Location: '.$this->all_configs['prefix'].'forms/'.$this->all_configs['arrequest'][1].'#fields');

                    }

                    if($this->all_configs['arrequest'][2] == 'update'){
                        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
                        $user_result_text = isset($_POST['user_result_text']) ? trim($_POST['user_result_text']) : '';
                        $user_result_title = isset($_POST['user_result_title']) ? trim($_POST['user_result_title']) : '';
                        $send_result = isset($_POST['send_result']) ? 1 : 0;
                        $send_result_to_user = isset($_POST['send_result_to_user']) ? 1 : 0;
                        $active = isset($_POST['active']) ? 1 : 0;
                        $function = isset($_POST['function']) ? trim($_POST['function']) : '';

                        $id = $this->all_configs['db']->query("UPDATE {forms} 
                                          SET send_result_to_user = ?i, active = ?i, function = ?
                                          WHERE id = ?i", array($send_result_to_user, $active, $function, $this->all_configs['arrequest'][1]), 'id');
                        
                        $this->all_configs['db']->query("
                                INSERT INTO {forms_strings}(forms_id, name, user_result_title, user_result_text,
                                                            lang)
                                VALUES(?i:id, ?:name, ?:user_result_title, ?:user_result_text, 
                                       ?:lang)
                                ON DUPLICATE KEY UPDATE name = ?:name, user_result_title = ?:user_result_title, 
                                                        user_result_text = ?:user_result_text", 
                                array(
                                    'id' => $this->all_configs['arrequest'][1],
                                    'lang' => $this->lang,
                                    'name' => $name, 
                                    'user_result_title' => $user_result_title, 
                                    'user_result_text' => $user_result_text, 
                                )
                        ); // query
                        header('Location: '.$this->all_configs['prefix'].'forms/'.$this->all_configs['arrequest'][1]);
                    }

                }


            }

            if($this->all_configs['arrequest'][1] == 'add_new'){


                if(isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'save'){

                    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
                    $user_result_text = isset($_POST['user_result_text']) ? trim($_POST['user_result_text']) : '';
                    $user_result_title = isset($_POST['user_result_title']) ? trim($_POST['user_result_title']) : '';
                    $send_result_to_user = isset($_POST['send_result_to_user']) ? 1 : 0;
                    $active = isset($_POST['active']) ? 1 : 0;

                    $id = $this->all_configs['db']->query("INSERT INTO {forms}(send_result_to_user, active)
                                      VALUES(?i, ?i)", array($send_result_to_user, $active), 'id');
                    
                    $this->all_configs['db']->query("
                            INSERT INTO {forms_strings}(forms_id, name, user_result_title, user_result_text,
                                                        lang)
                            VALUES(?i:id, ?:name, ?:user_result_title, ?:user_result_text, 
                                   ?:lang)",
                            array(
                                'id' => $id,
                                'lang' => $this->lang,
                                'name' => $name, 
                                'user_result_title' => $user_result_title, 
                                'user_result_text' => $user_result_text, 
                            )
                    ); // query
                    if($id){
                        header('Location: '.$this->all_configs['prefix'].'forms/'.$id);
                    }

                }else{

                    $out = '
                        <h3>Создание новой формы</h3>
                        <form method="post" action="'.$this->all_configs['prefix'].'forms/add_new/save">
                            '.$this->form().'
                                <br>
                                <br>
                            <input type="submit" class="btn btn-primary" value="'.l('create').'">
                        </form>
                    ';

                }

            }


        }

        return $out;
    }

    private function ajax(){

        $data = array(
            'state' => false
        );

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

}

