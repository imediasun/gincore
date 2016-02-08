<?php
ini_set('memory_limit','512M');

// настройки
$modulename[140] = 'translates';
$modulemenu[140] = l('trans_modulemenu');  //карта сайта

$moduleactive[140] = $ifauth['is_1'];

class translates{
    
    protected $tbl_prefix;
    protected $config = null;
    protected $url = null;
    
    protected $all_configs;
    protected $lang;
    protected $def_lang;
    protected $langs;
    function __construct($all_configs, $lang, $def_lang, $langs){
        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->langs = $langs;
        $this->all_configs = $all_configs;
                
        global $input_html, $ifauth, $dbcfg;
        
        $this->tbl_prefix = $dbcfg['_prefix'];
        
        $this->set_config();
        
        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax'){
            $this->ajax();
        }
        
        if(!$ifauth['is_1']) return false;

        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

    private function set_config(){
        if(is_null($this->config)){
            $this->url = __CLASS__;
            $this->config = array(
                $this->tbl_prefix.'map' => array(
                    'name' => l('Переводы для карты сайта'),
        //            'var' => 'url',
                    'key' => 'map_id',
                    'fields' => array(
                        'name' => l('Название'),
                        'fullname' => l('Заголовок'),
                        'content' => l('Контент')
                    )
                ),
                $this->tbl_prefix.'forms' => array(
                    'name' => l('Переводы для форм'),
        //            'var' => 'url',
                    'key' => 'forms_id',
                    'fields' => array(
                        'name' => l('Название'),
                        'user_result_text' => l('Текст письма пользователю'),
                        'user_result_title' => l('Заголовок письма пользователю')
                    )
                ),
                $this->tbl_prefix.'reviews_marks' => array(
                    'name' => l('Переводы оценок отзывов'),
        //            'var' => 'url',
                    'key' => 'mark_id',
                    'fields' => array(
                        'name' => l('Название')
                    )
                ),
                $this->tbl_prefix.'template_vars' => array(
                    'name' => l('Переводы для шаблона сайта'),
                    'var' => 'var',
                    'key' => 'var_id',
                    'fields' => array(
                        'text' => l('Значение')
                    )
                ),
                $this->tbl_prefix.'sms_templates' => array(
                    'name' => l('Шаблоны смс'),
                    'key' => 'sms_templates_id',
                    'fields' => array(
                        'body' => l('Текст')
                    )
                ),
            );
        }
    }
    protected function genmenu(){
        $out = '<h4>' . l('Таблицы') . '</h4><ul>';
        foreach($this->config as $table => $conf){
            $out .= '
                <li><a'.(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == $table ? ' class="active"' : '').
                      ' href="'.$this->all_configs['prefix'].''.$this->url.'/'.$table.'">'.$conf['name'].'</a>
                  '.(isset($conf['add_link']) ? $conf['add_link'] : '').'
              </li>
            ';
        }
        $out .= '<li style="margin-top:15px"><a'.(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'changes' ? ' class="active"' : '').
                        ' href="'.$this->all_configs['prefix'].''.$this->url.'/changes">' . l('Изменения') . '</a></li>';
        $out .= '</ul>';

        return $out;
    }

    
    protected function gencontent(){

//        $languages = $this->all_configs['db']->query("SELECT * FROM {langs}")->assoc('url');
//        foreach($this->langs as $lnge){
//            if($lnge['default']){
//                $def_lang = $lnge['url'];
//                break;
//            }
//        }
        $languages = $this->langs;
        $def_lang = $this->def_lang;
        
        $out = l('Выберите таблицу слева.');
        if(isset($this->all_configs['arrequest'][1])){
            
            if(isset($this->config[$this->all_configs['arrequest'][1]])){
                
                $config = $this->config[$this->all_configs['arrequest'][1]];
                
                $filter_query = '';
                $filter_query_2 = '';
                if(isset($this->all_configs['arrequest'][2]) && is_numeric($this->all_configs['arrequest'][2])){
                    $filter_query = $this->all_configs['db']->makeQuery(" WHERE id = ?i ", array($this->all_configs['arrequest'][2]));
                    $filter_query_2 = $this->all_configs['db']->makeQuery(" WHERE ?q = ?i ", array($config['key'], $this->all_configs['arrequest'][2]));
                }
                $table = $this->all_configs['db']->query("SELECT * FROM ?q ?q", array($this->all_configs['arrequest'][1], $filter_query), 'assoc:id');
                $table_translates = $this->all_configs['db']->query("SELECT * FROM ?q_strings ?q", array($this->all_configs['arrequest'][1], $filter_query_2), 'assoc');

                $translates = array();
                foreach($table_translates as $trans){
                    if(!isset($translates[$trans[$config['key']]])){
                        $translates[$trans[$config['key']]] = array();
                    }
                    $translates[$trans[$config['key']]][$trans['lang']] = $trans;
                }
                // добвим поля c недостающими языками
                foreach($languages as $l => $v){
                    foreach($translates as $id=>$t){
                        if(!isset($translates[$id][$l])){
                            $translates[$id][$l] = array_map('clear_values_in_array', isset($translates[$id][$def_lang]) ? $translates[$id][$def_lang] : array_shift($translates[$id]));
                        }
                    }
                }
                
                if(isset($this->all_configs['arrequest'][2]) && !is_numeric($this->all_configs['arrequest'][2])){
                    
                    if($this->all_configs['arrequest'][2] == 'copy'){
                        if(isset($this->all_configs['arrequest'][3]) && $this->all_configs['arrequest'][3] == 'make_magic'){
                            $from = isset($_POST['from']) ? $_POST['from'] : '';
                            $to = isset($_POST['to']) ? $_POST['to'] : '';
                            if(isset($languages[$from]) && isset($languages[$to])){
                                $tbl_fields = array_keys($config['fields']);
                                $tbl_fields_1 = array();
                                $update = array();
                                foreach($tbl_fields as $tbl_field){
                                    $tbl_fields_1[] = '`'.$tbl_field.'` as '.$tbl_field.'1';
                                    $update[] = '`'.$tbl_field.'` = IF(`'.$tbl_field.'` = "", VALUES(`'.$tbl_field.'`), `'.$tbl_field.'`)';
                                }
                                $this->all_configs['db']->query("INSERT INTO ?q:tbl(?q:key, ?q:fields, lang) 
                                            SELECT * FROM (SELECT ?q:key, ?q:fields1, ?:lang_to FROM ?q:tbl as t WHERE lang = ?:lang_from) as t
                                            ON DUPLICATE KEY UPDATE ?q:update", 
                                        array(
                                            'tbl' => $this->all_configs['arrequest'][1].'_strings',
                                            'lang_from' => $from,
                                            'lang_to' => $to,
                                            'key' => $config['key'],
                                            'fields1' => implode(',', $tbl_fields_1),
                                            'fields' => implode(',', $tbl_fields),
                                            'update' => implode(',', $update)
                                        ));
                                $out = '<h2>' . l('Копирование языка') .' '.$from.' в '.$to.'</h2>';
                                $out .= l('Скопировано успешно') . '. '.
                                        '<a href="'.$this->all_configs['prefix'].''.$this->url.'/'.$this->all_configs['arrequest'][1].'/copy">Вернуться назад</a>'; 
                            }else{
                                header('Location: '.$this->all_configs['prefix'].''.$this->url.'/'.$this->all_configs['arrequest'][1].'/copy');
                                exit;
                            }
                        }else{
                            $out = '<h2>' . l('Скопировать язык в пустые ячеки другого языка') .'</h2>';
                            $options = '<option value=""> --- </option>';
                            foreach($languages as $l => $lng){
                                $options .= '<option value="'.$l.'">'.$lng['name'].'</option>';
                            }
                            $out .= '
                                <form onSubmit="return confirm(\'' . l('Вы абсолютно уверены в том что хотите скопировать') .'?\')" '.
                                'action="'.$this->all_configs['prefix'].''.$this->url.'/'.$this->all_configs['arrequest'][1].'/copy/make_magic" method="post">
                                    <div class="form-group">
                                        <label>' . l('Копировать') .'</label>
                                        <select name="from" class="form-control">
                                            '.$options.'
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label>' . l('Куда') . '</label>
                                        <select class="form-control" name="to">
                                            '.$options.'
                                        </select>
                                    </div>
                                    <input class="btn btn-primary" type="submit" value="' . l('Копировать') .'">
                                </form>
                            ';
                        }
                    }
                    
                    // add to table
                    if($this->all_configs['arrequest'][2] == 'add'){
                        if(isset($this->all_configs['arrequest'][3]) && $this->all_configs['arrequest'][3] == 'save'){
                            if($_POST['data']){
                                $f = implode(',', array_keys($_POST['data']));
                                $v = array();
                                foreach($_POST['data'] as $fld=>$d){
                                    $v[] = $this->all_configs['db']->makeQuery("?", array($d));
                                }
                                $id = $this->all_configs['db']->query("INSERT INTO ?q(?q) VALUES (?q)", array($this->all_configs['arrequest'][1], $f, implode(',', $v)), 'id');
                            }else{
                                $id = $this->all_configs['db']->query("INSERT INTO ?q() VALUES ()", array($this->all_configs['arrequest'][1]), 'id');
                            }
                            $all_fields = array();
                            $values = array();
                            foreach($_POST['translates']  as $lng => $fields){
                                if(!$all_fields){
                                    $all_fields[] = implode(',', array_keys($fields));
                                }
                                $vals = array();
                                foreach($fields as $field => $translate){
                                    $vals[] = $this->all_configs['db']->makeQuery('?', array($translate));
                                }
                                $values[] = $this->all_configs['db']->makeQuery('(?, ?q, ?)', array($id, implode(',', $vals), $lng));
                            }
                            
                            $this->all_configs['db']->query("INSERT INTO ?q_strings(?q, ?q, lang) VALUES ?q", 
                                        array($this->all_configs['arrequest'][1], $config['key'], implode(',', $all_fields), implode(',', $values)));
                            header('Location: '.$this->all_configs['prefix'].''.$this->url.'/'.$this->all_configs['arrequest'][1]);
                            exit;
                            
                        }else{
                            $out = '<h3>'.$config['name'].'</h3>';
                            $columns = $this->all_configs['db']->query("SHOW COLUMNS FROM ?q", array($this->all_configs['arrequest'][1]), 'assoc');
    //                        print_r($columns);
    //                        exit;
                            $out .= '
                                <form action="'.$this->all_configs['prefix'].''.$this->url.'/'.$this->all_configs['arrequest'][1].'/add/save" method="post">
                                <fieldset>
                                    <legend>' . l('Данные') . '</legend>
                            ';
                            foreach($columns as $column){
                                if($column['Field'] != 'id'){
                                    $out .= '
                                        <div class="from-control">
                                            <label>'.$column['Field'].'</label>
                                            <input class="form-control" name="data['.$column['Field'].']" type="text">
                                        </div>
                                    ';
                                }
                            }
                            $out .= '
                                </fieldset><br><br>
                            ';

                            $out .= '
                                <fieldset>
                                    <legend>' . l('Переводы') . '</legend>
                            ';
                            foreach($config['fields'] as $field => $field_name){
                                foreach($languages as $lng => $l){
//                                    if($l['state']){
                                        $out .= '
                                            <div class="from-control">
                                                <label>'.$field_name.', '.$lng.'</label>
                                                <input class="form-control" name="translates['.$lng.']['.$field.']" type="text">
                                            </div>
                                        ';
//                                    }
                                }
                            }
                            $out .= '
                                </fieldset>
                                 <input type="submit" class="save-btn btn btn-primary" value="'.l('save').'">
                                </form>
                            ';
                        }
                        
                    }
                    
                    // saving
                    if($this->all_configs['arrequest'][2] == 'save'){

    //                    print_r($_POST['data']);
    //                    exit;

                        $all_fields = array();
                        $values = array();

                        foreach($_POST['data'] as $id => $transl){
                            foreach($transl as $lng => $fields){
                                $all_fields = array();
                                $vals = array();
                                $update_vals = array();
                                foreach($fields as $field => $translate){
                                    // обновляем поля только на которых были изменения
                                    if(isset($translates[$id][$lng][$field]) && $translates[$id][$lng][$field] != $translate){
                                        $update_vals[] = $this->all_configs['db']->makeQuery($field.' = ?', array($translate));
                                        $vals[] = $this->all_configs['db']->makeQuery('?', array($translate));
                                        $all_fields[] = $field;
                                    }
                                }
                                if($update_vals){
                                    $data_q = $this->all_configs['db']->makeQuery('(?, ?q, ?)', array($id, implode(',', $vals), $lng));
                                    $this->all_configs['db']->query("INSERT INTO ?q_strings(?q, ?q, lang) VALUES ?q 
                                              ON DUPLICATE KEY UPDATE ?q", 
                                        array($this->all_configs['arrequest'][1], $config['key'], implode(',', $all_fields), $data_q, 
                                                implode(',', $update_vals)));
                                }
                            }
                        }

                        // !!!
//                        $this->all_configs['db']->query("DELETE FROM ?q_strings", array($this->all_configs['arrequest'][1]));

//                        $this->all_configs['db']->query("INSERT INTO ?q_strings(?q, ?q, lang) VALUES ?q", 
//                                    array($this->all_configs['arrequest'][1], $config['key'], implode(',', $all_fields), implode(',', $values)));
                        header('Location: '.$this->all_configs['prefix'].''.$this->url.'/'.$this->all_configs['arrequest'][1]);
                        exit;
                    }
                    
                }else{
                    $out = '<small><a href="'.$this->all_configs['prefix'].''.$this->url.'/'.$this->all_configs['arrequest'][1].'/copy">' . l('скопировать языки') . '</a></small>';
                    $out .= '<h3>'.$config['name'].' <a href="'.$this->all_configs['prefix'].''.$this->url.'/'.$this->all_configs['arrequest'][1].'/add">+</a></h3>';
                    $out .= '<form action="'.$this->all_configs['prefix'].''.$this->url.'/'.$this->all_configs['arrequest'][1].'/save" method="post">';
                
    //                print_r($table);
    //                print_r($translates);
    //                exit;

                    foreach($translates as $id => $langs){
                        $out .= '<fieldset class="main">
                                    <legend>(id '.$id.')</legend>
                                    <div>
                                    ';
                        if(isset($this->config[$this->all_configs['arrequest'][1]]['var'])){
                            if(is_array($this->config[$this->all_configs['arrequest'][1]]['var'])){
                                $vars_vals = array();
                                foreach($this->config[$this->all_configs['arrequest'][1]]['var'] as $var){
                                    $vars_vals[] = $table[$id][$var];
                                }
                                $out .= '<span class="muted">'.implode(', ', $vars_vals).'</span>';
                            }else{
                                $out .= '<span class="muted">'.$table[$id][$this->config[$this->all_configs['arrequest'][1]]['var']].'</span>';
                            }
                        }
                        foreach($config['fields'] as $field => $field_name){
                            $out .= '<fieldset>
                                        
                                        <legend>'.$field_name.' </legend>
                                        <p class="text-muted">'.$field.'</p>';

                            foreach($langs as $lng => $translate){
//                                if(isset($languages[$lng]) && $languages[$lng]['state']){
                                    $value = htmlspecialchars($translate[$field]);
                                    $out .= '
                                        <span class="form-group" style="display:block">
                                            <label>'.$languages[$lng]['name'].', '.$lng.'</label>
                                    ';
                                    $f_name = 'data['.$id.']['.$lng.']['.$field.']';
                                    if(strlen($value) > 50){
                                        $out .= '
                                            <textarea class="form-control" style="height: 150px" name="'.$f_name.'">'.$value.'</textarea>
                                        </span>
                                        ';
                                    }else{
                                        $out .= '
                                            <input class="form-control" type="text" name="'.$f_name.'" value="'.$value.'">
                                        </span>
                                        ';
                                    }
//                                }
                            }

                            $out .= '</fieldset>';
                        }

                        $out .= '</div></fieldset><br><br>';
                    }
                    $out .= '
                            <input type="submit" class="save-btn btn btn-primary" value="'.l('save').'">
                        </form>
                    ';
                
                }
            }else{
                switch($this->all_configs['arrequest'][1]){
                    
                    // история изменений контента
                    case 'changes':
                        $query = '';
                        $query_parts = array();
                        foreach($this->config as $table => $conf){
                            $query_parts[] = 
                                $this->all_configs['db']->makeQuery(
                                        "(SELECT ".$conf['key']." as id,lang,change_date,'".$table."' as tbl "
                                        ."FROM ".$table."_strings WHERE change_date >= DATE_ADD(CURDATE(), INTERVAL -21 DAY))", array());
                        }
                        $query .= implode(' UNION ', $query_parts).' ORDER BY change_date DESC';
                        
                        $data = $this->all_configs['db']->plainQuery($query)->assoc();
                        
                        $out = '
                            <h3>' . l('Изменения за последние 3 недели') . '</h3>
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>'.l('Дата').'</th>
                                        <th>' . l('Секция') . '</th>
                                        <th>' . l('Язык') . '</th>
                                        <th>ID</th>
                                        <th>' . l('данные') . '</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                        ';
                        foreach($data as $change){
                            $tbl_no_prefix = substr($change['tbl'], strlen($this->tbl_prefix), strlen($change['tbl']));
                            switch($tbl_no_prefix){
                                case 'map':
                                    $data = $this->all_configs['db']->query("SELECT url FROM {map} WHERE id = ?i", array($change['id']), 'el');
                                    $link = 'map/'.$change['id'];
                                break;
                                case 'template_vars':
                                    $data = $this->all_configs['db']->query("SELECT var FROM {template_vars} WHERE id = ?i", array($change['id']), 'el');
                                    $link = ''.$this->url.'/'.$change['tbl'].'/'.$change['id'];
                                break;
                                default: 
                                    $data = '';
                                    $link = ''.$this->url.'/'.$change['tbl'].'/'.$change['id'];
                                break;
                            }
                            $out .= '<tr>
                                        <td title="'.do_nice_date($change['change_date'], false).'">'.
                                            do_nice_date($change['change_date']).
                                       '</td>
                                        <td>'.(isset($this->config[$change['tbl']]['name']) ? 
                                               $this->config[$change['tbl']]['name'] : $change['tbl']).'</td>
                                        <td>'.$change['lang'].'</td>
                                        <td>'.$change['id'].'</td>
                                        <td>'.$data.'</td>
                                        <td><a href="'.$this->all_configs['prefix'].$link.'" target="_blank">' . l('Редактировать') . '</a></td>
                                     </tr>';
                        }
                        $out .= '</tbody></table>';
                        
                    break;
                
                }
            }
            
        }
        
        return $out;
    }

    protected function ajax(){

        $data = array(
            'state' => false
        );

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

}
