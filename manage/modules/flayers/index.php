<?php

include $all_configs['path'].'modules/flayers/langs.php';

$lang_arr = array_merge($lang_arr, $flayers_lang);


// настройки
$modulename[] = 'flayers';
$modulemenu[] = l('flayers_modulemenu');  //карта сайта

$moduleactive[] = !$ifauth['is_2'];

class flayers{

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

        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax'){
            $this->ajax();
        }
        
        if($ifauth['is_2']) return false;
        
        $input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }
    
    private function genmenu(){
        
        $flayers_arr = $this->all_configs['db']->query("SELECT id, active, is_double, page_id FROM {banners} ORDER BY is_double DESC, prio")->assoc('id');
        $translates = get_few_translates(
            'banners', 
            'banner_id', 
            $this->all_configs['db']->makeQuery("banner_id IN (?q)", array(implode(',', array_keys($flayers_arr))))
        );
        $flayers = '<h3>Баннера</h3><ul>'
            .'<li><a'.(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'add_new' ? ' style="font-weight: bold"' : '').'  href="'.$this->all_configs['prefix'].'flayers/add_new">Добавить баннер</a></li>
            ';
        
        $flayer_type = '';
        foreach($flayers_arr as $fl){
            $fl = translates_for_page($this->lang, $this->def_lang, $translates[$fl['id']], $fl, true);
            if($flayer_type != $fl['is_double']) {
                $flayer_type = $fl['is_double'];
                $flayers .= '</ul><h5>';
                switch ($flayer_type) {
                    case 0:
                        $flayers .= 'Левые баннеры';
                        break;
                    case 1:
                        $flayers .= 'Нижние баннеры';
                        break;
                    case 2:
                        $flayers .= 'Главные баннеры';
                        break;
                    case 3:
                        $flayers .= 'Сервисные баннеры';
                        break;
                    default:
                        break;
                } 
                $flayers .= '</h5><ul>';
            }
            $flayers .= '
                <li'.(!$fl['active'] ? ' style="text-decoration: line-through"' : '').'>
                    <a'.(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == $fl['id'] ? ' style="font-weight: bold"' : '').' href="'.$this->all_configs['prefix'].'flayers/'.$fl['id'].'">
                        '.$fl['name'].'
                    </a>
                </li>';
        }
        $flayers .= '</ul>';
        
        $out = $flayers
            .'

            <h3>Расстановка</h3>
            <ul>
                <li><a'.(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'sorting' ? ' style="font-weight: bold"' : '').'  href="'.$this->all_configs['prefix'].'flayers/sorting/3">Главных баннеров</a></li>
                <li><a'.(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'sorting' ? ' style="font-weight: bold"' : '').'  href="'.$this->all_configs['prefix'].'flayers/sorting/2">Нижних баннеров</a></li>
                <li><a'.(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'sorting' ? ' style="font-weight: bold"' : '').'  href="'.$this->all_configs['prefix'].'flayers/sorting/1">Левых баннеров</a></li>
            </ul>
        ';
        
        return $out;
    }
    
    private function save_photo(){
        $filename = '';
        $status = true;
        $message = '';
        if(isset($_FILES['image']) && trim($_FILES['image']['name'])){
            $filename = pathinfo($_FILES['image']['name']);
            $ext = $filename['extension'];
            if(in_array($filename['extension'], array('JPG', 'jpg', 'GIF', 'gif', 'PNG', 'png', 'JPEG', 'jpeg'))){
                $file_hash = substr(md5(microtime()), 0, 15);
                $filename = $file_hash.'.'.$ext;
                $path_to_directory = $this->all_configs['sitepath']."flayers/";               
                $source = $_FILES['image']['tmp_name'];  
//                $source = str_replace('\\', '/', $source);
                $target = $path_to_directory.$filename;
                if(move_uploaded_file($source, $target)){
                    chmod($target, 0777);
                }else{
                    $status = false;
                }
            }else{
                $message = 'Недопустимый формат файла';
                $status = false;
            }
        }
        return $filename;
    }
    
    private function form($flayer = array()){
        
        $categories = $this->all_configs['db']->query('
            SELECT DISTINCT m.id FROM {map} AS m
            INNER  JOIN {map_module} d ON (m.id=page_id)
            WHERE m.state=1 AND d.module="content_service_list"')->assoc('id');
        $translates = get_few_translates(
            'map', 
            'map_id', 
            $this->all_configs['db']->makeQuery("map_id IN (?q)", array(implode(',', array_keys($categories))))
        );
        $category_select = '<select name="category" class="form-control"><option value="0">Нет разделов</option>';
        foreach ( $categories as $category ) {
            $category = translates_for_page($this->lang, $this->def_lang, $translates[$category['id']], $category, true);
            if (isset($flayer['page_id']) && $flayer['page_id'] == $category['id'])
                $category_select .= '<option selected="selected" value="' . $category['id'] . '">' . $category['name'] . '</option>';
            else
                $category_select .= '<option value="' . $category['id'] . '">' . $category['name'] . '</option>';
        }
        $category_select .= '</select>';
        
        $form = '
            <div class="form-group">
                <div class="checkbox">
                    <label class="checkbox">
                        <input type="checkbox" name="active" value="1"'.
                            (($flayer && $flayer['active']) || !$flayer ? ' checked="checked"' : '').'> отображается
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label>Тип </label>
                <select name="is_double" class="form-control">
                    <option value="0">'.l('flayers_simple').'</option>
                    <option value="1" '.($flayer && $flayer['is_double']==1 ? ' selected="selected"' : '').'>'.l('flayers_double').'</option>
                    <option value="2" '.($flayer && $flayer['is_double']==2 ? ' selected="selected"' : '').'>'.l('flayers_main').'</option>
                    <option value="3" '.($flayer && $flayer['is_double']==3 ? ' selected="selected"' : '').'>'.l('flayers_service').'</option>
                </select>
            </div>
            <div class="form-group">
                <div id="categories_box">
                    <label>Сервис:</label>
                    ' . $category_select . '
                </div>
            </div>
            <div class="form-group">
                <label>URL:</label>
                <input type="text" class="form-control" name="url" value="'.($flayer && $flayer['url'] ? $flayer['url'] : '').'">
            </div>
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="hidden_link" value="1"'.
                            (($flayer && $flayer['hidden_link']) ? ' checked="checked"' : '').'> эмулировать ссылку 
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label>Название:</label>
                <input type="text" class="form-control" name="name" value="'.($flayer && $flayer['name'] ? $flayer['name'] : '').'">
            </div>
            <div class="form-group">
                <label>Изображение:</label>
                '.($flayer && $flayer['image'] ? '<img src="'.$this->all_configs['siteprefix'].'flayers/'.$flayer['image'].'"><br>' : '').'
                <strong>'.l('flayers_simple').'</strong> - 350 х (175, 560) px <br>
                <strong>'.l('flayers_double').'</strong> - рабочее пространство (970х200) общее (1920х240) px <br>
                <strong>'.l('flayers_main').'</strong> - рабочее пространство (970х400) общее (1920х400) px <br>
                <input type="file" name="image">
            </div>
        ';
        
        return $form;
    }
    
    private function gencontent(){
        
        $out = '<h3>Модуль управления баннерами</h3>';
        
        if(isset($this->all_configs['arrequest'][1])){
            
            // сортировка
            if($this->all_configs['arrequest'][1] == 'sorting'){
                if(isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2]!='save'){
                    $flayers = $this->all_configs['db']->query("SELECT * FROM {banners} WHERE active = 1 AND is_double = ?i ORDER BY prio",
                            array($this->all_configs['arrequest'][2]-1), 'assoc:id');
                    $translates = get_few_translates(
                        'banners', 
                        'banner_id', 
                        $this->all_configs['db']->makeQuery("banner_id IN (?q)", array(implode(',', array_keys($flayers))))
                    );
                    
                    $html = '';
                    foreach($flayers as $flayer){
                        $flayer = translates_for_page($this->lang, $this->def_lang, $translates[$flayer['id']], $flayer, true);
                        $img = $flayer && $flayer['image'] ? '<img src="'.$this->all_configs['siteprefix'].'flayers/'.$flayer['image'].'">' : $flayer['name'];
                        
                        // more banner types
                        switch ($flayer['is_double']) {
                            case 1:
                                $class = 'double';
                                break;
                            case 2:
                                $class = 'main';
                                break;
                            default :
                                $class = '';  
                        }
                        
                        $html .= '<li'.($class ? ' class="'.$class.'"' : '').'>
                                        '.$img.'
                                         <input type="hidden" name="flayer_prio['.$flayer['id'].']" value="'.$flayer['prio'].'">
                                  </li>';
                    }

                    $out = '
                        <h3>Сортировка</h3>
                        <form action="'.$this->all_configs['prefix'].'flayers/sorting/save" method="post">
                            <ul id="sortable">
                                '.$html.'
                            </ul>
                            <div style="clear:both"></div>
                            <br>
                            <br>
                            <input type="submit" id="save_sorting" class="btn btn-primary" value="'.l('save').'">
                        </form>
                    ';
                }else{
                    if(isset($_POST['flayer_prio'])){
                        //print_r($_POST['sort_arr']);
                        foreach($_POST['flayer_prio'] as $id => $prio){
                            $this->all_configs['db']->query("UPDATE {banners} SET prio = ?i WHERE id = ?i", array($prio, $id));
                        }
                        
                        header('Location: '.$this->all_configs['prefix'].'flayers/sorting');
                        exit;
                    }
                }
                
            }
            
            // редактирование
            if(is_numeric($this->all_configs['arrequest'][1])){
                
                $id = $this->all_configs['arrequest'][1];
                $flayer = $this->all_configs['db']->query("SELECT * FROM {banners} WHERE id = ?i", array($id), 'row');
                $flayer_langs = $this->all_configs['db']->query("SELECT *
                                          FROM {banners_strings} 
                                          WHERE banner_id = ?i", array($id), 'assoc:lang');
                $flayer = translates_for_page($this->lang, $this->def_lang, $flayer_langs, $flayer);
                if(!isset($this->all_configs['arrequest'][2])){

                    $out = '
                        <h3>Баннер «'.$flayer['name'].'»</h3>
                        <form action="'.$this->all_configs['prefix'].'flayers/'.$this->all_configs['arrequest'][1].'/save" method="post" enctype="multipart/form-data">
                            '.$this->form($flayer).'
                            <div class="form-goup">
                                <input type="submit" class="btn btn-primary" value="'.l('save').'">
                            </div>
                        </form>

                    ';
                
                }else{
                    
                    if($this->all_configs['arrequest'][2] == 'save'){
                        
                        $active = isset($_POST['active']) ? 1 : 0;
                        $hidden_link = isset($_POST['hidden_link']) ? 1 : 0;
                        $is_double = $_POST['is_double'];
                        $url = trim($_POST['url']);
                        $name = trim($_POST['name']);
                        $image = $this->save_photo();
                        $category = $_POST['category'];
                        
                        $current_picture_path = $this->all_configs['sitepath'].'flayers/'.$flayer['image'];
                        if($image && is_file($this->all_configs['sitepath'].'flayers/'.$flayer['image'])){
                            unlink($current_picture_path);
                        }
                        
                        $image = $image ?: $flayer['image'];
                        
                        $this->all_configs['db']->query("UPDATE {banners} SET active = ?i, image = ?, hidden_link = ?i, is_double = ?i, page_id = ?i WHERE id = ?i 
                                          ", array($active, $image, $hidden_link, $is_double, $category, $id));
                        
                        $this->all_configs['db']->query("
                            INSERT INTO {banners_strings}(banner_id, name, url, lang)
                            VALUES(?i:id, ?:name, ?:url, ?:lang)
                            ON DUPLICATE KEY UPDATE name = ?:name, url = ?:url", 
                            array(
                                'id' => $id,
                                'lang' => $this->lang,
                                'name' => $name, 
                                'url' => $url, 
                            )
                        ); // query
                        
                        header('Location: '.$this->all_configs['prefix'].'flayers/'.$id);
                        exit;
                        
                    }
                }
                
            }
            
            // добавление
            if($this->all_configs['arrequest'][1] == 'add_new'){
                
                if(!isset($this->all_configs['arrequest'][2])){

                    $out = '
                        <form action="'.$this->all_configs['prefix'].'flayers/add_new/save" method="post" enctype="multipart/form-data">
                            <h3>Добавление нового баннера</h3>
                            '.$this->form().'
                            <div class="form-goup">
                                <input type="submit" class="btn btn-primary" value="'.l('save').'">
                            </div>
                        </form>
                    ';
                }else{
                    
                    if($this->all_configs['arrequest'][2] == 'save'){
                        
                        $active = isset($_POST['active']) ? 1 : 0;
                        $hidden_link = isset($_POST['hidden_link']) ? 1 : 0;
                        $is_double = $_POST['is_double'];
                        $url = trim($_POST['url']);
                        $name = trim($_POST['name']);
                        $image = $this->save_photo();
                        $category = $_POST['category'];
                        
                        $id = $this->all_configs['db']->query("INSERT INTO {banners}(active, prio, image, hidden_link, is_double, page_id) 
                                          VALUES(?i, 0, ?, ?i, ?i, ?i)", array($active, $image, $hidden_link, $is_double, $category), 'id');
                        
                        $this->all_configs['db']->query("
                                INSERT INTO {banners_strings}(banner_id, name, url, lang)
                                VALUES(?i:id, ?:name, ?:url, ?:lang)",
                                array(
                                    'id' => $id,
                                    'name' => $name, 
                                    'url' => $url,
                                    'lang' => $this->def_lang,
                                )
                        ); // query
                        
                        header('Location: '.$this->all_configs['prefix'].'flayers/'.$id);
                        exit;
                        
                    }
                }
                
            }
            
        } else {
            // banners start page
            $out .= '
            <ol>
                <li>
                    <p>Для начала необходимо добавить баннер, выбрав в левом меню
                    соответствующий пункт.
                    <p>Галочка "отображается" позволяет включить либо отключить показ баннера на сайте.
                    Отключенные баннеры отображаются только в Административном разделе перечёркнутыми.
                    <p>Тип баннера определяет его положение относительно сраницы
                    сайта.
                    <p> УРЛ - полный адрес страницы сайта, например: http://mysite.com/sale
                    <p> Название баннера будет отображаться только в меню административной части.
                    Для удобного поиска давайте осмысленные имена баннерам.
                </li>
                <li>
                    <p>Для редактирования баннера - выберите его в левом меню.
                    <p>На странице редактирования баннера вы можете изменить его настройки.
                    <p>Изменить изображение баннера можно просто загрузив новое изображение.
                    <p>Удалять баннеры нельзя, но их можно отключать сняв галочку "отображать".
                    Для того чтобы не хранить много отключенных баннеров просто
                    отредактируйте их, указав настройки новых баннеров и включите их.
                </li>
                <li>
                    <p>Раздел расстановка позволяет управлять положением баннеров
                    в группах относительно друг-друга.
                    <p>Положение баннера меняется его перетаскиванием вверх и вниз.
                </li>
                <li>
                    <p>На главной странице вместо нижнего баннера отображается
                    описание страницы которое можно изменить в "Карте сайта".
                </li>
            </ol>
            ';
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

