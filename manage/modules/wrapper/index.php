<?php

$modulename[130] = 'wrapper';
$modulemenu[130] = l('Таблицы');
$moduleactive[130] = $ifauth['is_1'];

class wrapper{

    protected $all_configs;

    /**
     * wrapper constructor.
     * @param $all_configs
     */
    function __construct($all_configs){
        global $input_html, $ifauth;

        $this->all_configs = &$all_configs;

        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax'){
            $this->ajax();
        }
        
        if(!$ifauth['is_1']) return false;

        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

    /**
     * @return array
     */
    private function genconfig(){
        global $dbcfg;
        $conf = array(
            $dbcfg['_prefix'].'reviews' => array(
                'settings' => array('name' => l('Отзывы')),
                'columns' => array(
                    'id' => array('0', '1', 'ID', ''),
                    'uxt' => array('0', '1', l('Дата поста'), ''),
                    'user' => array('0', '0', l('Автор'), ''),
                    'email' => array('0', '0', 'email', ''),
                    'comment' => array('0', '0', l('Отзыв'), ''),
                    'mark' => array('0', '0', '"' . l('Оценка') .'" (1&nbsp;' . l('отлично') .' - 5&nbsp;' . l('ужасно') .')', '1'),
                    'ip' => array('1', '1', 'ip', ''),
                    'service_id' => array('0', '0', 'id ' . l('сервиса') .'', '')
                )//columns
            ),
            $dbcfg['_prefix'].'sources' => array(
                'settings' => array('name' => l('Источники рекламы и телефоны')),
                'columns' => array(
                    'id' => array('0', '1', 'ID', ''),
                    'source' => array('0', '0', '' . l('Источник') .'(city,adw)', ''),
                    'phone_mobile' => array('0', '0', l('Телефон мобильный'), ''),
                    'phone_static' => array('0', '0', l('Телефон стационарный'), '')
                )//columns
            ),
            $dbcfg['_prefix'].'page_types' => array(
                'settings' => array('name' => l('Типы страниц')),
                'columns' => array(
                    'id' => array('0', '1', 'ID', ''),
                    'name' => array('0', '0', l('Название'), '')
                )
            ),
            $dbcfg['_prefix'].'map_prices' => array(
                'settings' => array('name' => l('Все цены')),
                'columns' => array(
                    'id' => array('0', '1', 'ID', ''),
                    'map_id' => array('0', '0', 'id ' . l('карты сайта'), ''),
                    'table_type' => array('0', '0', '№ табл.', ''),
                    'name' => array('0', '0', l('Название'), ''),
                    'price_copy_mark' => array('0', '0', l('Префикс копии'), ''),
                    'price_copy' => array('0', '0', l('Цена копии'), ''),
                    'price_mark' => array('0', '0', l('Префикс ориг'), ''),
                    'price' => array('0', '0', l('Цена ориг'), ''),
                    'time_required' => array('0', '0', l('Время'), ''),
                    'prio' => array('0', '0', l('Приоритет'), ''),
                )
            ),
            $dbcfg['_prefix'].'visitors' => array(
                'settings' => array('name' => l('посетители')),
                'columns' => array(
                    'id' => array('0', '1', 'ID', ''),
                    'uxt' => array('0', '1', ''.l('Дата').'', ''),
                    'ip' => array('0', '1', 'IP', ''),
                    'visit_count' => array('0', '1', 'visits', ''),
                    'user_agent' => array('0', '1', 'user agent', ''),
                    'referer' => array('0', '1', 'referer', ''),
                    
                )
            ),
            $dbcfg['_prefix'].'crm_referers' => array(
                'settings' => array('name' => l('Список каналов (источники продаж)')),
                'columns' => array(
                    'id' => array('0', '1', 'ID', ''),
                    'name' => array('0', '0', l('Название'), ''),
                    'group_id' => array('0', '0', l('Группа') . ' (0-' . l('Затраты') .', 1-Context, 2-Remarketing, 3-Search)', ''),
                )
            ),
            $dbcfg['_prefix'].'visitors_system_codes' => array(
                'settings' => array('name' => l('Системные коды на скидку')),
                'columns' => array(
                    'id' => array('0', '1', 'ID', ''),
                    'code' => array('0', '0', l('Код'), ''),
                    'created_at' => array('0', '0', l('Добавлен'), date('Y-m-d H:i:s')),
                    'referer_id' => array('0', '0', l('id источника'), ''),
                    'description' => array('0', '0', l('описание'), '')
                )
            ),
            $dbcfg['_prefix'].'crm_expenses' => array(
                'settings' => array('name' => l('Список затрат')),
                'columns' => array(
                    'id' => array('0', '1', 'ID', ''),
                    'sum_uah' => array('0', '0', l('Сумма') .' '.l(viewCurrency()).'', ''),
                    'referer_id' => array('0', '0', l('Канал'), '', '', 'crm_referers'),
                    'date_add' => array('0', '0', ''.l('Дата').'', date("Y-m-d"), 'datepicker'),
                    'comment' => array('0', '0', l('Коментарий'), ''),
                    'visits' => array('0', '0', l('Показы'), '0'),
                    'clicks' => array('0', '0', l('Клики'), '0'),
                )
            ),
            $dbcfg['_prefix'].'sms_senders' => array(
                'settings' => array('name' => '' . l('СМС') .': ' . l('отправители') .''),
                'columns' => array(
                    'id' => array('0', '1', 'ID', ''),
                    'sender' => array('0', '0', 'Sender', ''),
                    'type' => array('0', '0', l('Тип'), '0'),
                )
            ),
        );
        return $conf;
    }

    /**
     * @param $table_name
     * @return mixed
     */
    private function genconfig_tablename($table_name){
        $conf = $this->genconfig();
        return isset($conf[$table_name]['settings']['name']) && $conf[$table_name]['settings']['name'] ? $conf[$table_name]['settings']['name'] : $table_name;
    }

    /**
     * @return string
     */
    private function genmenu(){
        global $dbcfg;

        $conf = $this->genconfig();

        $out = '<h4>' . l('Доступные таблицы') . '</h4>';

        $sql = $this->all_configs['db']->query("SHOW TABLES FROM ?q", array($dbcfg['dbname']))->assoc();

        if(!$sql){
            $out.= l('Ошибка получения списка таблиц');
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
            $out.='<br><a href="'.$this->all_configs['prefix'].'wrapper/'.$this->all_configs['arrequest'][1].'/add">' . l('Добавить в текущую таблицу') .'</a><br>';
        }

        $out.='<br>';

        return $out;
    }

    /**
     * @param $table
     * @return bool
     */
    private function table_exists($table){
        if($this->all_configs['db']->query("SHOW TABLES LIKE ?", array($table))->ar())
            return true;
        else
            return false;
    }

    /**
     * @return string
     */
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
            $out = '<h3>' . l('Редактор таблиц сайтa') . '</h3><br>';
            if($ifauth['is_adm'] != 1){
                $out.='<br>' . l('Недостаточно прав для изменения') .'!<br><br>';
            }
            $out.='';
        }
###############################################################################
        if(isset($this->all_configs['arrequest'][1]) && $this->table_exists($this->all_configs['arrequest'][1]) && !isset($this->all_configs['arrequest'][2])){
            $out.='<h3>' . l('Таблицa') .' «'.$this->genconfig_tablename($this->all_configs['arrequest'][1]).'»</h3>';

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
                        <td><a href="'.$this->all_configs['prefix'].'wrapper/'.$this->all_configs['arrequest'][1].'/del/'.$mm[0].'" class="glyphicon glyphicon-remove" onclick="return confirm(\'' . l('Удалить') .'?\');"></a></td>';
                
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
            $out.='<h3>' . l('Таблица') .' «'.$this->genconfig_tablename($this->all_configs['arrequest'][1]).'»</h3>';

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
                        $form .= '<select class="form-group" name="'.$pp[0].'"><option value="0">' . l('не выбрано') . '</option>';
                        foreach ($vars as $var_id=>$var_value) {
                            $form .= '<option'.($var_id == $pp ? ' selected' : '').' value="' . intval($var_id) . '">' . htmlspecialchars($var_value) . '</option>';
                        }
                        $form .= '</select>';
                    } else {
                        $form .= '<input class="form-control" type="text" value="'.$conf[$this->all_configs['arrequest'][1]]['columns'][$pp[0]][3].'" name="'.$pp[0].'" size="70">';
                    }
                    $form.='</div>';
                }
            }

            $out.='<form action="'.$this->all_configs['prefix'].'wrapper/'.$this->all_configs['arrequest'][1].'/insert" method="POST">
                '.$form.'
                <input type="submit" value="'.l('Добавить').'" class="btn btn-primary" />
            </form>';
        }
###############################################################################
        if(isset($this->all_configs['arrequest'][1]) && isset($this->all_configs['arrequest'][2]) && $this->table_exists($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][2] == 'insert'){
            $out.='<h3>' . l('Таблица') .' «'.$this->genconfig_tablename($this->all_configs['arrequest'][1]).'»</h3>';

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
            $out.='<h3>' . l('Таблица') .' «'.$this->genconfig_tablename($this->all_configs['arrequest'][1]).'»</h3>';

            $sql = $this->all_configs['db']->query("SHOW COLUMNS FROM ".$this->all_configs['arrequest'][1]);
            $cols = array();

            $sqll = $this->all_configs['db']->query("SELECT * FROM ?q WHERE id = ?i", array($this->all_configs['arrequest'][1], $this->all_configs['arrequest'][3]), 'row');
            $form = '';
            foreach($sqll as $k => $pp){ 
                $col_humen_name = isset($conf[$this->all_configs['arrequest'][1]]['columns'][$k]) && $conf[$this->all_configs['arrequest'][1]]['columns'][$k] ? $conf[$this->all_configs['arrequest'][1]]['columns'][$k][2] : $pp;
           
                if(isset($conf[$this->all_configs['arrequest'][1]]['columns'][$k][1]) && $conf[$this->all_configs['arrequest'][1]]['columns'][$k][1] == 1){
                } else {
                    $cols[] = $pp;
                    $form .= '<div class="form-group"><label>'.$col_humen_name.'</label>';
                    if (isset($conf[$this->all_configs['arrequest'][1]]['columns'][$k][5])) {
                        $vars = $this->all_configs['db']->query('SELECT id, name FROM {?query}',
                            array($conf[$this->all_configs['arrequest'][1]]['columns'][$k][5]))->vars();
                        $form.='<select class="form-control" name="'.$k.'"><option value="0">' . l('не выбрано') . '</option>';
                        foreach ($vars as $var_id=>$var_value) {
                            $form.='<option'.($var_id == $pp ? ' selected' : '').' value="' . intval($var_id) . '">' . htmlspecialchars($var_value) . '</option>';
                        }
                        $form.='</select>';
                    } else {
                        if(strlen($pp) > 100){
                            $form.='<textarea class="form-control" name="'.$k.'" rows="9" cols="80">'.htmlspecialchars($pp).'</textarea>';
                        }else{
                            $form.='<input class="form-control" type="text" value="'.htmlspecialchars($pp).'" name="'.$k.'" size="70">';
                        }
                    }
                    $form.='</div>';
                }
            }

            $out.='<form action="'.$this->all_configs['prefix'].'wrapper/'.$this->all_configs['arrequest'][1].'/update/'.$sqll['id'].'" method="POST">
                '.$form.'
                <input type="submit" value="'.l('save').'" class="btn btn-primary" /> 
            </form>';
        }
###############################################################################
        if(isset($this->all_configs['arrequest'][1]) && isset($this->all_configs['arrequest'][2]) && $this->table_exists($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][2] == 'update' && is_numeric($this->all_configs['arrequest'][3])){
            $out.='<h3>' . l('Таблиця') .' «'.$this->genconfig_tablename($this->all_configs['arrequest'][1]).'»</h3>';

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

    /**
     *
     */
    private function ajax(){

        $data = array(
            'state' => false
        );

        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode($data);
        exit;
    }

}