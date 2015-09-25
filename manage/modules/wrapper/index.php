<?php

$modulename[] = 'wrapper';
$modulemenu[] = 'Таблицы';
$moduleactive[] = !$ifauth['is_2'];

class wrapper{

    protected $all_configs;

    function __construct($all_configs){
        global $input_html, $ifauth;

        $this->all_configs = &$all_configs;

        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax'){
            $this->ajax();
        }
        
        if($ifauth['is_2']) return false;

        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

    private function genconfig(){
        global $dbcfg;
        $conf = array(
            $dbcfg['_prefix'].'reviews' => array(
                'settings' => array('name' => 'Отзывы'),
                'columns' => array(
                    //hide, ro, realname, default
                    'id' => array('0', '1', 'ID', ''),
                    'uxt' => array('0', '1', 'Дата поста', ''),
                    'user' => array('0', '0', 'Автор', ''),
                    'email' => array('0', '0', 'email', ''),
                    'comment' => array('0', '0', 'Отзыв', ''),
                    'mark' => array('0', '0', '"Оценка" (1&nbsp;отлично - 5&nbsp;ужасно)', '1'),
                    'ip' => array('1', '1', 'ip', ''),
                    'service_id' => array('0', '0', 'id сервиса', '')
                )//columns
            ),
            $dbcfg['_prefix'].'sources' => array(
                'settings' => array('name' => 'Источники рекламы и телефоны'),
                'columns' => array(
                    //hide, ro, realname, default
                    'id' => array('0', '1', 'ID', ''),
                    'source' => array('0', '0', 'Источник(city,adw)', ''),
                    'phone_mobile' => array('0', '0', 'Телефон мобильный', ''),
                    'phone_static' => array('0', '0', 'Телефон стационарный', '')
                )//columns
            ),
            $dbcfg['_prefix'].'page_types' => array(
                'settings' => array('name' => 'Типы страниц'),
                'columns' => array(
                    //hide, ro, realname, default
                    'id' => array('0', '1', 'ID', ''),
                    'name' => array('0', '0', 'Название', '')
                )
            ),
            $dbcfg['_prefix'].'map_prices' => array(
                'settings' => array('name' => 'Все цены'),
                'columns' => array(
                    //hide, ro, realname, default
                    'id' => array('0', '1', 'ID', ''),
                    'map_id' => array('0', '0', 'id карты сайта', ''),
                    'table_type' => array('0', '0', '№ табл.', ''),
                    'name' => array('0', '0', 'Название', ''),
                    'price_copy_mark' => array('0', '0', 'Префикс копии', ''),
                    'price_copy' => array('0', '0', 'Цена копии', ''),
                    'price_mark' => array('0', '0', 'Префикс ориг', ''),
                    'price' => array('0', '0', 'Цена ориг', ''),
                    'time_required' => array('0', '0', 'Время', ''),
                    'prio' => array('0', '0', 'Приоритет', ''),
                )
            ),
            $dbcfg['_prefix'].'visitors' => array(
                'settings' => array('name' => 'посетители'),
                'columns' => array(
                    //hide, ro, realname, default
                    'id' => array('0', '1', 'ID', ''),
                    'uxt' => array('0', '1', 'Дата', ''),
                    'ip' => array('0', '1', 'IP', ''),
                    'visit_count' => array('0', '1', 'visits', ''),
                    'user_agent' => array('0', '1', 'user agent', ''),
                    'referer' => array('0', '1', 'referer', ''),
                    
                )
            ),
            $dbcfg['_prefix'].'crm_referers' => array(
                'settings' => array('name' => 'Список каналов (источники продаж)'),
                'columns' => array(
                    //hide, ro, realname, default
                    'id' => array('0', '1', 'ID', ''),
                    'name' => array('0', '0', 'Название', ''),
                    'group_id' => array('0', '0', 'Группа (0-Затраты, 1-Context, 2-Remarketing, 3-Search)', ''),
                )
            ),
            $dbcfg['_prefix'].'visitors_system_codes' => array(
                'settings' => array('name' => 'Системные коды на скидку'),
                'columns' => array(
                    //hide, ro, realname, default
                    'id' => array('0', '1', 'ID', ''),
                    'code' => array('0', '0', 'Код', ''),
                    'created_at' => array('0', '0', 'Добавлен', date('Y-m-d H:i:s')),
                    'referer_id' => array('0', '0', 'id источника', ''),
                    'description' => array('0', '0', 'описание', '')
                )
            ),
            $dbcfg['_prefix'].'crm_expenses' => array(
                'settings' => array('name' => 'Список затрат'),
                'columns' => array(
                    //hide, ro, realname, default, class, foreignkey to {table}.id
                    'id' => array('0', '1', 'ID', ''),
                    'sum_uah' => array('0', '0', 'Сумма '.viewCurrency().'', ''),
                    'referer_id' => array('0', '0', 'Канал', '', '', 'crm_referers'),
                    'date_add' => array('0', '0', 'Дата', date("Y-m-d"), 'datepicker'),
                    'comment' => array('0', '0', 'Коментарий', ''),
                    'visits' => array('0', '0', 'Показы', '0'),
                    'clicks' => array('0', '0', 'Клики', '0'),
                )
            ),
            $dbcfg['_prefix'].'sms_senders' => array(
                'settings' => array('name' => 'СМС: отправители'),
                'columns' => array(
                    //hide, ro, realname, default, class, foreignkey to {table}.id
                    'id' => array('0', '1', 'ID', ''),
                    'sender' => array('0', '0', 'Sender', ''),
                    'type' => array('0', '0', 'Тип', '0'),
                )
            ),
        );
        return $conf;
    }

    private function genconfig_tablename($table_name){
        $conf = $this->genconfig();
        return isset($conf[$table_name]['settings']['name']) && $conf[$table_name]['settings']['name'] ? $conf[$table_name]['settings']['name'] : $table_name;
    }

    private function genmenu(){
        global $dbcfg;

        $conf = $this->genconfig();

        $out = '<h4>Доступные таблицы</h4>';

        $sql = $this->all_configs['db']->query("SHOW TABLES FROM ?q", array($dbcfg['dbname']))->assoc();

        if(!$sql){
            $out.='Ошибка получения списка таблиц';
        }


        $tables_prefix = $dbcfg['_prefix'];
        $out.='<ul>';
        foreach($sql as $pp){
            if(isset($conf[$pp['Tables_in_'.$dbcfg['dbname']]]) && substr($pp['Tables_in_'.$dbcfg['dbname']], 0, strlen($tables_prefix)) == $tables_prefix){
                $out.='<li><a style="'.(isset($this->all_configs['arrequest'][1]) && $pp['Tables_in_'.$dbcfg['dbname']] == $this->all_configs['arrequest'][1] ? 'font-weight:bold' : '').'" href="'.$this->all_configs['prefix'].'wrapper/'.$pp['Tables_in_'.$dbcfg['dbname']].'">'.$this->genconfig_tablename($pp['Tables_in_'.$dbcfg['dbname']]).'</a> </li>';
            }
        }
        $out.='</ul>';
        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1]){
            $out.='<br><a href="'.$this->all_configs['prefix'].'wrapper/'.$this->all_configs['arrequest'][1].'/add">Добавить в текущую таблицу</a><br>';
        }

        $out.='<br>';

        return $out;
    }

    private function table_exists($table){
        if($this->all_configs['db']->query("SHOW TABLES LIKE ?", array($table))->ar())
            return true;
        else
            return false;
    }

    private function gencontent(){
        GLOBAL $ifauth;

        $conf = $this->genconfig();

        $id = isset($_POST['id']) ? $_POST['id'] : '';
        $prio = isset($_POST['prio']) ? $_POST['prio'] : '';
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $url = isset($_POST['url']) ? $_POST['url'] : '';
        $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '';
        $contacts = isset($_POST['contacts']) ? $_POST['contacts'] : '';
        $contacts2 = isset($_POST['contacts2']) ? $_POST['contacts2'] : '';
        $contacts3 = isset($_POST['contacts3']) ? $_POST['contacts3'] : '';

        $ok = isset($_POST['ok']) ? $_POST['ok'] : '';
        $cat = isset($_POST['cat']) ? $_POST['cat'] : '';

        $out = '';

        if(!isset($this->all_configs['arrequest'][1])){
            $out = '<h3>Редактор таблиц сайтa</h3><br>';
            if($ifauth['is_adm'] != 1){
                $out.='<br>Недостаточно прав для изменения!<br><br>';
            }
            $out.='';
        }
###############################################################################
        if(isset($this->all_configs['arrequest'][1]) && $this->table_exists($this->all_configs['arrequest'][1]) && !isset($this->all_configs['arrequest'][2])){
            $out.='<h3>Таблицa «'.$this->genconfig_tablename($this->all_configs['arrequest'][1]).'»</h3>';

            $sql = $this->all_configs['db']->query("SHOW COLUMNS FROM ".$this->all_configs['arrequest'][1])->assoc();
            $cols = array();
            $table = '<table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th width="14"> </th>
                        <th width="14"> </th>';
            foreach($sql as $pp){
                $col_humen_name = isset($conf[$this->all_configs['arrequest'][1]]['columns'][$pp['Field']]) && $conf[$this->all_configs['arrequest'][1]]['columns'][$pp['Field']] ? $conf[$this->all_configs['arrequest'][1]]['columns'][$pp['Field']][2] : $pp['Field'];

                $cols[] = $pp['Field'];
                if(isset($conf[$this->all_configs['arrequest'][1]]['columns'][$pp['Field']][0])&& $conf[$this->all_configs['arrequest'][1]]['columns'][$pp['Field']][0] != 1){
                    $table.='<th>'.$col_humen_name.'</th>';
                }
            }
            $table.='</tr></thead><tbody>';

            $sql_order = ' ORDER BY id DESC';
            if(in_array('prio', $cols)){
                $sql_order = ' ORDER BY prio';
            }
            $sql = $this->all_configs['db']->query("SELECT * FROM ".$this->all_configs['arrequest'][1].$sql_order." LIMIT 1000")->assoc();
            foreach($sql as $mm){
                $mm = array_values($mm);
                $table.='
                    <tr>
                        <td><a href="'.$this->all_configs['prefix'].'wrapper/'.$this->all_configs['arrequest'][1].'/edit/'.$mm[0].'" class="glyphicon glyphicon-pencil"></a></td>
                        <td><a href="'.$this->all_configs['prefix'].'wrapper/'.$this->all_configs['arrequest'][1].'/del/'.$mm[0].'" class="glyphicon glyphicon-remove" onclick="return confirm(\'Удалить?\');"></a></td>';
                
                for($i = 0; $i < count($cols); $i++){
                    if(isset($conf[$this->all_configs['arrequest'][1]]['columns'][$cols[$i]][0]) && $conf[$this->all_configs['arrequest'][1]]['columns'][$cols[$i]][0] == 1){
                    } else {
                        $table.='<td>'.$mm[$i].'</td>';
                    }
                }
                
                $table.='</tr>';
            }




            $table.='</tbody></table>';

            $out.=$table;
        }
###############################################################################
        if(isset($this->all_configs['arrequest'][1]) && isset($this->all_configs['arrequest'][2]) && $this->table_exists($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][2] == 'add'){
            $out.='<h3>Таблица «'.$this->genconfig_tablename($this->all_configs['arrequest'][1]).'»</h3>';

            $sql = $this->all_configs['db']->query("SHOW COLUMNS FROM ".$this->all_configs['arrequest'][1])->assoc();
            $cols = array();
            $form = '';
            foreach($sql as $pp){
                $pp = array_values($pp);
                
                $col_humen_name = $conf[$this->all_configs['arrequest'][1]]['columns'][$pp[0]] ? $conf[$this->all_configs['arrequest'][1]]['columns'][$pp[0]][2] : $pp[0];

                if($conf[$this->all_configs['arrequest'][1]]['columns'][$pp[0]][1] != 1){
                    $cols[] = $pp[0];
                    $form .= '<div class="form-group"><label>'.$col_humen_name.'</label>';
                    if (isset($conf[$this->all_configs['arrequest'][1]]['columns'][$pp[0]][5])) {
                        $vars = $this->all_configs['db']->query('SELECT id, name FROM {?query}',
                            array($conf[$this->all_configs['arrequest'][1]]['columns'][$pp[0]][5]))->vars();
                        $form .= '<select class="form-group" name="'.$pp[0].'"><option value="0">не выбрано</option>';
                        foreach ($vars as $var_id=>$var_value) {
                            $form .= '<option'.($var_id == $pp ? ' selected' : '').' value="' . intval($var_id) . '">' . htmlspecialchars($var_value) . '</option>';
                        }
                        $form .= '</select>';
                    } else {
                        $form .= '<input class="form-control" type="text" value="'.$conf[$this->all_configs['arrequest'][1]]['columns'][$pp[0]][3].'" name="'.$pp[0].'" size="70">';
                    }
                    $form.='</div>';
                }
                //echo $pp[0].' - '.$pp[1].'<br>';
            }

            $out.='<form action="'.$this->all_configs['prefix'].'wrapper/'.$this->all_configs['arrequest'][1].'/insert" method="POST">
                '.$form.'
                <input type="submit" value="Добавить" class="btn btn-primary" />
            </form>';
        }
###############################################################################
        if(isset($this->all_configs['arrequest'][1]) && isset($this->all_configs['arrequest'][2]) && $this->table_exists($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][2] == 'insert'){
            $out.='<h3>Таблица «'.$this->genconfig_tablename($this->all_configs['arrequest'][1]).'»</h3>';

            $sql = $this->all_configs['db']->query("SHOW COLUMNS FROM ".$this->all_configs['arrequest'][1])->assoc();
            $sql_cols = array();
            $sql_value = array();

            foreach($sql as $pp){
                $pp = array_values($pp);
                if($conf[$this->all_configs['arrequest'][1]]['columns'][$pp[0]][1] != 1){ //не РО в конфиге
                    $sql_cols[] = $pp[0];
                    $sql_values[] = $_POST[$pp[0]];
                }
            }

//            $sql_cols = implode(',', $sql_cols);
            //$sql_values = implode(', ', $sql_value);
            $this->all_configs['db']->query("INSERT INTO `?q` (?cols) VALUES (?l)", array($this->all_configs['arrequest'][1], $sql_cols, $sql_values));
            header('Location: '.$this->all_configs['prefix'].'wrapper/'.$this->all_configs['arrequest'][1].'');
            exit;
        }
###############################################################################
        if(isset($this->all_configs['arrequest'][1]) && isset($this->all_configs['arrequest'][2]) && $this->table_exists($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][2] == 'del' && is_numeric($this->all_configs['arrequest'][3])){
            $sql = $this->all_configs['db']->query("DELETE FROM ?q WHERE id=?i LIMIT 1", array($this->all_configs['arrequest'][1], $this->all_configs['arrequest'][3]));
            header('Location: '.$this->all_configs['prefix'].'wrapper/'.$this->all_configs['arrequest'][1].'');
            exit;
        }
###############################################################################
        if(isset($this->all_configs['arrequest'][1]) && isset($this->all_configs['arrequest'][2]) && $this->table_exists($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][2] == 'edit' && is_numeric($this->all_configs['arrequest'][3])){
            $out.='<h3>Таблица «'.$this->genconfig_tablename($this->all_configs['arrequest'][1]).'»</h3>';

            $sql = $this->all_configs['db']->query("SHOW COLUMNS FROM ".$this->all_configs['arrequest'][1]);
            $cols = array();

            $sqll = $this->all_configs['db']->query("SELECT * FROM ?q WHERE id = ?i", array($this->all_configs['arrequest'][1], $this->all_configs['arrequest'][3]), 'row');
            $form = '';
            foreach($sqll as $k => $pp){ 
                $col_humen_name = isset($conf[$this->all_configs['arrequest'][1]]['columns'][$k]) && $conf[$this->all_configs['arrequest'][1]]['columns'][$k] ? $conf[$this->all_configs['arrequest'][1]]['columns'][$k][2] : $pp;
           
                if(isset($conf[$this->all_configs['arrequest'][1]]['columns'][$k][1]) && $conf[$this->all_configs['arrequest'][1]]['columns'][$k][1] == 1){
                } else {
                    $cols[] = $pp;
                    //$conf[$this->all_configs['arrequest'][1]]['columns'][$pp[0]][0]
                    $form .= '<div class="form-group"><label>'.$col_humen_name.'</label>';
                    if (isset($conf[$this->all_configs['arrequest'][1]]['columns'][$k][5])) {
                        $vars = $this->all_configs['db']->query('SELECT id, name FROM {?query}',
                            array($conf[$this->all_configs['arrequest'][1]]['columns'][$k][5]))->vars();
                        $form.='<select class="form-control" name="'.$k.'"><option value="0">не выбрано</option>';
                        foreach ($vars as $var_id=>$var_value) {
                            $form.='<option'.($var_id == $pp ? ' selected' : '').' value="' . intval($var_id) . '">' . htmlspecialchars($var_value) . '</option>';
                        }
                        $form.='</select>';
                    } else {
                        if(strlen($pp) > 100){
                            $form.='<textarea class="form-control" name="'.$k.'" rows="9" cols="80">'.htmlspecialchars($pp).'</textarea>';
                        }else{
                            $form.='<input class="form-control" type="text" value="'.htmlspecialchars($pp).'" name="'.$k.'" size="70">';
                            //$form.=$col_humen_name.'<br><input type="text" value="'.$sqll[$pp[0]].'" name="'.$pp[0].'" size="70"><br><br>';
                        }
                    }
                    $form.='</div>';
                }
                //echo $pp[0].' - '.$pp[1].'<br>';
            }

            $out.='<form action="'.$this->all_configs['prefix'].'wrapper/'.$this->all_configs['arrequest'][1].'/update/'.$sqll['id'].'" method="POST">
                '.$form.'
                <input type="submit" value="'.l('save').'" class="btn btn-primary" /> 
            </form>';
        }
###############################################################################
        if(isset($this->all_configs['arrequest'][1]) && isset($this->all_configs['arrequest'][2]) && $this->table_exists($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][2] == 'update' && is_numeric($this->all_configs['arrequest'][3])){
            $out.='<h3>Таблиця «'.$this->genconfig_tablename($this->all_configs['arrequest'][1]).'»</h3>';

            $sql = $this->all_configs['db']->query("SHOW COLUMNS FROM ".$this->all_configs['arrequest'][1])->assoc();
            $sql_value = array();

            
            foreach($sql as $pp){
                if(isset($conf[$this->all_configs['arrequest'][1]]['columns'][$pp['Field']][1]) && $conf[$this->all_configs['arrequest'][1]]['columns'][$pp['Field']][1] == 1){ //не РО в конфиге
                } else {
                    $sql_value[] = $this->all_configs['db']->makeQuery('?c = ?', array($pp['Field'], $_POST[$pp['Field']]));
                }
            }
            $sql_values = implode(', ', $sql_value);
            $sql = $this->all_configs['db']->query("UPDATE ?q SET ?q WHERE id=?i", array($this->all_configs['arrequest'][1], $sql_values, $this->all_configs['arrequest'][3]));
            header('Location: '.$this->all_configs['prefix'].'wrapper/'.$this->all_configs['arrequest'][1].'');
            exit;
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