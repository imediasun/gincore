<?php

/**
 * модуль редактора страниц и карты сайта
 */

include $all_configs['path'].'modules/map/langs.php';
include 'inc_helper_price.php';

$lang_arr = array_merge($lang_arr, $map_lang);

// нужные переводы для шаблона
$input['loading'] = l('loading');
$input['close'] = l('close');
$input['saving'] = l('saving');
$input['map_published_short'] = l('map_published_short');
$input['map_add_subpage'] = l('map_add_subpage');

// настройки
$modulename[] = 'map';
$modulemenu[] = l('map_modulemenu');  //карта сайта

$moduleactive[] = !$ifauth['is_2'];

///////////
class map{

    static $allowed_ext = array('jpg', 'gif', 'png', 'JPG', 'jpeg', 'JPEG');

    protected $all_configs;
    private $lang;
    private $def_lang;
    private $langs;
    function __construct($all_configs, $lang, $def_lang, $langs){
        global $input_html, $ifauth;

        $this->def_lang = $def_lang;
        $this->lang = $lang;
        $this->langs = $langs;
        $this->all_configs = &$all_configs;

        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'ajax'){
            $this->ajax();
        }
				
        if($ifauth['is_2']) return false;
        if (!$all_configs['oRole']->hasPrivilege('edit-map'))  return false;
        
		$input_html['mmenu'] = $this->genmenu();

        $input_html['mcontent'] = $this->gencontent();
    }

    //генерация элемента в меню карты сайта
    private function gen_menu_a_tag($pp, $a_class, $aclass='', $qty = 0, $check = ''){
        //$aclass.$boldclass.$activeclass
        if($qty > 0){
            $qty_txt = ' <span class="clip" id="clip'.$pp['id'].'">('.$qty.')</span>';
        }else{
            $qty_txt = '';
        }
        $a_notpage = $pp['is_page'] == 0 ? ' notpage' : '';
        $out = '<li title="'.$pp['name'].'" item-expanded="" id="menu-'.$pp['id'].'" item-selected="'.$a_class
                .'" class="'.(!$pp['parent'] ? 'menu_root_li' : '')
                .'"> <a class="'.$aclass.$a_notpage.'" href="'.$this->all_configs['prefix'].'map/'.$pp['id'].'">'
                .$pp['name'].'</a>'; //<a href="'.$this->all_configs['prefix'].'map/'.$pp['id'].'/del"><b>X</b></a>
        return $out;
    }

    private function gen_menu_map_level($parent, $section, $def_lang){
        global $out;
        $sql = $this->all_configs['db']->query("SELECT id, page_type, state, url, parent, 
                                  (SELECT url FROM {map} WHERE id = m.parent) as parent_url, template_inner, is_page 
                           FROM {map} as m WHERE parent=?i AND section=?i ORDER BY prio", array($parent, $section), 'assoc');
        $out.='<ul class="map_menu_level">';
        foreach($sql as $pp){
            $langss = $this->all_configs['db']->query("SELECT name, lang FROM {map_strings} 
                                 WHERE map_id = ?i", array($pp['id']), 'assoc:lang');
            $pp = translates_for_page($this->lang, $this->def_lang, $langss, $pp, true);
//            print_r($pp);
//            $boldclass = $pp['url'] == $pps['redirect'] ? ' bold' : '';
            $boldclass = '';
            $activeclass = isset($this->all_configs['arrequest'][1]) && $pp['id'] == $this->all_configs['arrequest'][1] ? 'true' : '';
            $aclass = $pp['state'] == 0 ? ' disabledpage ' : '';

            if($pp['state']){
                $this->menu_types_arr[$pp['parent_url'].'_'.$pp['url']] = $pp['template_inner'];
            }

            $out.=$this->gen_menu_a_tag($pp, $activeclass, $aclass);

            $sql1 = $this->all_configs['db']->query("SELECT id FROM {map} WHERE parent=?i ORDER BY prio", array($pp['id']), 'ar');

            if($sql1){
                $this->gen_menu_map_level($pp['id'], $section, $def_lang);
            }
        }
        $out.='</ul>';

    }
    
    private function genmenu(){
        global $ifauth, $out, $map_lang, $lng;

        if($ifauth['is_1'])
            exit;

        $out = '<h4>'.l('map_modulemenu').' <a style="text-decoration:none" href="'.$this->all_configs['prefix'].'map/add">+</a></h4>';


        $sqls = $this->all_configs['db']->query("SELECT * FROM {section} ORDER BY prio")->assoc();


        foreach($sqls as $pps){
            $out.='<br><b>'.$pps['name'].'</b> <small>-&gt; '.$pps['redirect'].'</small>';

            $this->gen_menu_map_level(0, $pps['id'],$this->def_lang);

        }#section

//        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][0] == 'map' && is_numeric($this->all_configs['arrequest'][1]))
//            $out.='<br><a href="'.$this->all_configs['prefix'].'map/add/'.$this->all_configs['arrequest'][1].'">Добавить</a><br>';
//        else
//            $out.='<br><a href="'.$this->all_configs['prefix'].'map/add">Добавить</a><br>';



        return $out;
    }

    /**
     * Генерация групп списков модулей
     *
     * @global string $path
     * @global array $config
     * @param integer $page_id
     * @param array $mage_modules
     * @return string
     */
    private function gen_modules_list($page_id, $modules){
        GLOBAL $map_lang, $lng;

        $mod_path = $this->all_configs['path'].'modules/map/site_content_modules/';
        $files = array();
        if(is_dir($mod_path)){
            $files = scandir($mod_path);
        }
        $content_box = '';
        $menu_box = '';
        $nofile_box = '';
        $out = '';
        foreach($files as $file){
            if(strpos($file, 'module_') === 0 && $files != '.' && $files != '..'){

                $fp = fopen($mod_path.$file, "r");
                $line = fgets($fp, 10);
                $line = fgets($fp, 4096);
                $line = trim(str_replace('//--// Модуль: ', '', $line));
                fclose($fp);

                $file = str_replace('module_', '', $file);
                $file = str_replace('.php', '', $file);

                if(in_array($file, $modules)){
                    $founded_key_array = array_keys($modules, $file);
                    $checked = 'checked="checked"';
                    unset($modules[$founded_key_array[0]]);
                }else{
                    $checked = '';
                }

                if(strpos($file, 'menu_') === 0){
                    $filemask = str_replace('menu_', '', $file);
                    //if ($filemask!='default'){
                    $menu_box.='<label class="checkbox"><input type="checkbox" name="module['.$file.']" value="'.$file.'" '.$checked.' /> '.$line.' ('.$file.')</label>';
                    //}
                }
                if(strpos($file, 'content_') === 0){
                    $filemask = str_replace('content_', '', $file);
                    //if ($filemask!='default'){
                    $content_box.='<label class="checkbox"><input type="checkbox" name="module['.$file.']" value="'.$file.'" '.$checked.' /> '.$line.' ('.$file.')</label>';
                    //}
                }
            }
        }
        $out.='<fieldset>
                    <legend> '.l('map_module_for_menu').' </legend>
                    '.($menu_box ? $menu_box : '&mdash;&mdash;').'
               </fieldset><br>';
        $out.='<fieldset>
                    <legend> '.l('map_module_for_content').' </legend>
                    '.($content_box ? $content_box : '&mdash;&mdash;').'
               </fieldset><br>';
        //    $out.='<fieldset>
        //                <legend> Модули для медиа </legend>
        //                '.$media_box.'
        //           </fieldset><br><br>';
        foreach($modules AS $el){
            $nofile_box.='<input type="checkbox" name="module['.$el.']" value="'.$el.'" checked="checked" /> '.$el.'<br>';
        }
        $out.='<fieldset>
                    <legend> '.l('map_module_wo_files').' </legend>
                    '.($nofile_box ? $nofile_box : '&mdash;&mdash;').'
               </fieldset>';

        return $out;
    }

    /**
     *
     * Сохранение списка модулей при апдейте страницы
     *
     * @global array $config
     * @param integer $page_id
     */
    private function save_modules_from_post($page_id){

        $this->all_configs['db']->query("DELETE FROM {map_module} WHERE page_id = ?i", array($page_id));

        if(isset($_POST['module']) && $_POST['module']){
            $values = array();
            foreach($_POST['module'] AS $el){
                $values[] = $this->all_configs['db']->makeQuery('(?i, ?)', array($page_id, $el));
            }

            $this->all_configs['db']->query("INSERT INTO {map_module} (page_id, module) VALUES ?q", array(implode(', ', $values)));
        }
    }

    /**
     * Генерация списка шаблонов по типу
     *
     * @global string $this->all_configs['sitepath']
     * @param string $type
     * @param string $name
     * @param string $selected
     * @return string
     */
    private function gen_templates_list($type, $name, $selected = ''){
        $modules = array();

        $out = '<select name="'.$name.'">';
        $files = scandir($this->all_configs['sitepath']);
        foreach($files as $file){
            if(strpos($file, 'html_'.$type) !== false){

                $file = str_replace('html_'.$type.'_', '', $file);
                $file = str_replace('.html', '', $file);
                //if ($file!='default')
                $out.='<option value="'.$file.'" '.($selected == $file ? 'selected' : '').'>'.$file.'</option>';
            }
        }
        $out.='</select>';
        return $out;
    }
    
    protected function out_choose_picture($pp){
        $files = array();
        if(is_dir($this->all_configs['sitepath'].'images/')){
            $files = scandir($this->all_configs['sitepath'].'images/');
        }
        $sel_gallery = ' <select name="gallery" id="sel_gallery"> 
                        <option id="no_gal" value="">'.l('map_not_selected').'</option>
                        ';
        foreach($files as $file){
            if(is_dir($this->all_configs['sitepath'].'images/'.$file) && $file != '.' && $file != '..'){
                if($pp['gallery'] == $file){
                    $selected = 'selected="selected"';
                }else{
                    $selected = '';
                }
                $sel_gallery.='<option '.$selected.' value="'.$file.'">'.$file.'</option>';
            }
        }

        $sel_gallery.='</select>';


        if($pp['picture'] || file_exists($this->all_configs['sitepath'].'images/'.$pp['gallery'].'/'.$pp['picture'])){
            $out_choose_picture_html_image = '<img id="current_picture" src="'.($pp['picture'] ? $this->all_configs['siteprefix'].'images/'.$pp['gallery'].'/'.$pp['picture'] : $this->all_configs['prefix'].'modules/map/img/no_picture.jpg').'" align="right"/>';
        }else{
            $out_choose_picture_html_image = '<b>'.l('map_out_choose_adv_picture_html_image_file_not_found').'</b><br><br>';
        }

        if(!is_writable($this->all_configs['sitepath'].'images/'.$pp['gallery'])){
            $out_choose_picture_error = '<b>'.l('map_out_choose_picture_error').'</b><br><br>';
        }else{
            $out_choose_picture_error = '';
        }

        $parent_page_type = $this->all_configs['db']->query("SELECT page_type FROM {map} WHERE id = ?i", array($pp['parent']), 'el');
        
        $choose_picture2 = '';
        if($parent_page_type == 2 && isset($pp['picture2'])){ // бренды
            
            $out_choose_picture_html_image2 = '';
            if($pp['picture2'] || file_exists($this->all_configs['sitepath'].'images/'.$pp['gallery'].'/'.$pp['picture2'])){
                $out_choose_picture_html_image2 = '<img id="current_picture2" src="'.($pp['picture2'] ? $this->all_configs['siteprefix'].'images/'.$pp['gallery'].'/'.$pp['picture2'] : $this->all_configs['prefix'].'modules/map/img/no_picture.jpg').'" align="right"/>';
            }else{
                $out_choose_picture_html_image2 = '<b>'.l('map_out_choose_adv_picture_html_image_file_not_found').'</b><br><br>';
            }
            
            $choose_picture2 = '
                <fieldset>
                    <legend> Шапка бренду </legend> 
                    <div class="input-append">
                        <input id="appendedInputButtons" class="span3 Ppicture"  type="text" size="30" name="picture2" value="'.$pp['picture2'].'" class="" id="picture2">
                        <input class="btn bt_page_photo_choose" type="button" data-file="picture2" value="'.l('select').'" id="bt_page_photo_choose2"  />
                        <input class="btn" type="button" value="'.l('clear').'" id="bt_page_photo_clear2" /> 
                    </div>
                    '.$out_choose_picture_html_image2.'
                </fieldset>
            ';
        }
        
        $out_choose_picture = '
            <fieldset>
                    <legend> '.l('map_images').' </legend>
                    '.$out_choose_picture_error.'

                <label>'.l('map_gallery_folder').':</label> '.$sel_gallery.' 
                <span id="gallery_options"'.($pp['gallery'] ? '' : ' style="display:none"').'>
                    <label class="checkbox"><input type="checkbox" id="resizeFoto" name="resizeFoto"> '.l('map_create_thumbs_page').'</label>
                    <label class="checkbox"><input type="checkbox" id="resizeFotoNews" name="resizeFotoNews"> '.l('map_create_thumbs_gallery').'</label>
<!--                    <label class="checkbox"><input type="checkbox" id="resize_product" name="resize_product"> '.l('map_resize_product').'</label>
                    <label class="checkbox"><input type="checkbox" id="resize_gallery" name="resize_gallery"> '.l('map_resize_gallery').'</label><br>
                  <label class="checkbox"><input type="checkbox" id="add_watermark" name="add_watermark"> '.l('map_add_watermark').'</label><br>
-->                   <div id="file-uploader">
                        <noscript>
                            <p>Please enable JavaScript to use file uploader.</p>
                            <!-- or put a simple form for upload here -->
                        </noscript>
                    </div>
                    <fieldset>
                        <legend> '.l('map_image_to_page').' </legend> 
                        <div class="input-append">
                            <input id="appendedInputButtons" class="span3 Ppicture"  type="text" size="30" name="picture" value="'.$pp['picture'].'" class="" id="picture">
                            <input class="btn bt_page_photo_choose" type="button" data-file="picture" value="'.l('select').'" id="bt_page_photo_choose" />
                            <input class="btn" type="button" value="'.l('clear').'" id="bt_page_photo_clear" /> 
                        </div>
                        '.$out_choose_picture_html_image.'
                    </fieldset>
                    '.$choose_picture2.'
                </span>
                <div id="new_gal" '.(!$pp['gallery'] ? '' : ' style="display:none"').'>
                    <label>'.l('map_create_folder').':</label> 
                    <div class="input-append">
                        <input id="appendedInputButtons" class="span3" type="text" value="'.(isset($pp['url']) ? $pp['url'] : '').'" name="new_gal"> 
                        <input class="btn" type="button" value="'.l('create').'"></div>
                    </div>
            </fieldset>
        ';
        return $out_choose_picture;
    }

    protected function gen_parentoption($page_parent = 0, $page_id = 0){
        $sql0 = $this->all_configs['db']->query("SELECT * FROM {map} WHERE parent=0 AND section=?i ORDER BY prio", array(1), 'assoc:id');
        $parentoption = '';

        $translates0 = get_few_translates(
                'map', 'map_id', $this->all_configs['db']->makeQuery("map_id IN(?q)", array(implode(',', array_keys($sql0))))
        );
        foreach($sql0 as $pp0){
            $pp0t = translates_for_page($this->lang, $this->def_lang, $translates0[$pp0['id']], $pp0, true);
            $optsel0 = $page_parent == $pp0['id'] ? 'selected' : '';
            $optdis0 = $page_id == $pp0['id'] ? 'disabled ' : ''; #отключение выбора родителя

            $parentoption.='<option '.$optdis0.$optsel0.' value="'.$pp0['id'].'">- '.$pp0t['name'].'</option>';

            $sql1 = $this->all_configs['db']->query("SELECT * FROM {map} WHERE parent = ?i AND section = ?i ORDER BY section, prio", array($pp0['id'], 1), 'assoc:id');
            if($sql1){
                $translates1 = get_few_translates(
                        'map', 'map_id', $this->all_configs['db']->makeQuery("map_id IN(?q)", array(implode(',', array_keys($sql1))))
                );
                foreach($sql1 as $pp1){
                    $pp1t = translates_for_page($this->lang, $this->def_lang, $translates1[$pp1['id']], $pp0, true);
                    $optsel1 = $page_parent == $pp1['id'] ? 'selected' : '';
                    $optdis1 = $page_parent == $pp0['id'] ? 'disabled ' : ''; #отключение выбора родителя
                    $parentoption.='<option '.$optdis1.$optsel1.' value="'.$pp1['id'].'">- - '.$pp1t['name'].'</option>';

                    $sql2 = $this->all_configs['db']->query("SELECT * FROM {map} WHERE parent = ?i AND section = ?i ORDER BY section, prio", array($pp1['id'], 1), 'assoc:id');
                    if($sql2){
                        $optdis2 = '';
                        $translates2 = get_few_translates(
                                'map', 'map_id', $this->all_configs['db']->makeQuery("map_id IN(?q)", array(implode(',', array_keys($sql2))))
                        );
                        foreach($sql2 as $pp2){
                            $pp2t = translates_for_page($this->lang, $this->def_lang, $translates2[$pp2['id']], $pp0, true);
                            $optsel2 = $page_parent == $pp2['id'] ? 'selected' : '';
                            //$optdis2=$page_parent==$pp1['id']?'disabled ':''; #отключение выбора родителя
                            $parentoption.='<option '.$optdis2.$optsel2.' value="'.$pp2['id'].'">- - = '.$pp2t['name'].'</option>';

                            $sql3 = $this->all_configs['db']->query("SELECT * FROM {map} WHERE parent = ?i AND section = ?i ORDER BY section, prio", array($pp2['id'], 1), 'assoc:id');
                            if($sql3){
                                $translates3 = get_few_translates(
                                        'map', 'map_id', $this->all_configs['db']->makeQuery("map_id IN(?q)", array(implode(',', array_keys($sql3))))
                                );
                                foreach($sql3 as $pp3){
                                    $pp3t = translates_for_page($this->lang, $this->def_lang, $translates3[$pp3['id']], $pp3, true);
                                    $optsel3 = $page_parent == $pp3['id'] ? 'selected' : '';
                                    //$optdis2=$page_parent==$pp1['id']?'disabled ':''; #отключение выбора родителя
                                    $parentoption.='<option '.$optsel3.' value="'.$pp3['id'].'">- - = = '.$pp3t['name'].'</option>';

                                    $sql4 = $this->all_configs['db']->query("SELECT * FROM {map} WHERE parent = ?i AND section = ?i ORDER BY section, prio", array($pp3['id'], 1), 'assoc:id');
                                    if($sql4){
                                        $translates4 = get_few_translates(
                                                'map', 'map_id', $this->all_configs['db']->makeQuery("map_id IN(?q)", array(implode(',', array_keys($sql4))))
                                        );
                                        foreach($sql4 as $pp4){
                                            $pp4t = translates_for_page($this->lang, $this->def_lang, $translates4[$pp4['id']], $pp4, true);
                                            $optsel4 = $page_parent == $pp4['id'] ? 'selected' : '';
                                            $parentoption.='<option '.$optsel4.' value="'.$pp4['id'].'">- - = = ='.$pp4t['name'].'</option>';
                                        }//while pp4
                                    }
                                }//while pp3
                            }
                        }//while pp2
                    }
                }//while pp1
            }
        }
        return $parentoption;
    }


    public function gencontent(){
        GLOBAL $lng, $map_lang, $ifauth;

        $id = isset($_POST['id']) ? $_POST['id'] : '';
        $prio = isset($_POST['prio']) ? $_POST['prio'] : '';
        $name = isset($_POST['name']) ? $_POST['name'] : '';
        $page_color = isset($_POST['page_color']) ? $_POST['page_color'] : '';
        $fullname = isset($_POST['fullname']) ? $_POST['fullname'] : '';
        $url = isset($_POST['url']) ? $_POST['url'] : '';
        $state = isset($_POST['state']) ? $_POST['state'] : '';
        $parent = isset($_POST['parent']) ? $_POST['parent'] : '';
        $section = isset($_POST['section']) ? $_POST['section'] : '';
        $content = isset($_POST['content']) ? $_POST['content'] : '';
        $picture = isset($_POST['picture']) ? $_POST['picture'] : '';
        $picture2 = isset($_POST['picture2']) ? $_POST['picture2'] : '';
        $adv_picture = isset($_POST['adv_picture']) ? $_POST['adv_picture'] : '';
        $module = isset($_POST['module']) ? $_POST['module'] : '';
        $hidf1 = isset($_POST['hidf1']) ? $_POST['hidf1'] : '';
        $hidf2 = isset($_POST['hidf2']) ? $_POST['hidf2'] : '';
        $hidf3 = isset($_POST['hidf3']) ? $_POST['hidf3'] : '';
        $template = isset($_POST['template']) ? $_POST['template'] : '';
        $template_header = isset($_POST['template_header']) ? $_POST['template_header'] : '';
        $template_inner = isset($_POST['template_inner']) ? $_POST['template_inner'] : '';
        $template_footer = isset($_POST['template_footer']) ? $_POST['template_footer'] : '';
        $template_body_header = isset($_POST['template_body_header']) ? $_POST['template_body_header'] : '';
        $redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '';
        $is_gmap = isset($_POST['is_gmap']) ? $_POST['is_gmap'] : '';
        $lat = isset($_POST['lat']) ? $_POST['lat'] : '';
        $lng = isset($_POST['lng']) ? $_POST['lng'] : '';
        $is_page = isset($_POST['is_page']) ? $_POST['is_page'] : '';
        $metadescription = isset($_POST['metadescription']) ? $_POST['metadescription'] : '';
        $metakeywords = isset($_POST['metakeywords']) ? $_POST['metakeywords'] : '';
        $meta = isset($_POST['meta']) ? $_POST['meta'] : '';
        $post_uxt = isset($_POST['uxt']) ? $_POST['uxt'] : '';
        $gallery = isset($_POST['gallery']) ? $_POST['gallery'] : '';
        $page_type = isset($_POST['page_type']) ? $_POST['page_type'] : '';
        $hotline_url = isset($_POST['hotline_url']) ? $_POST['hotline_url'] : '';
        $buy_old = isset($_POST['buy_old']) ? $_POST['buy_old'] : '';
        $description_name = isset($_POST['description_name']) ? $_POST['description_name'] : '';
        $chat_caption = isset($_POST['chat_caption']) ? $_POST['chat_caption'] : '';
        $category_id = isset($_POST['category_id']) && intval($_POST['category_id']) > 0 ? intval($_POST['category_id']) : null;
        $youtube_videos = isset($_POST['youtube_videos']) ? $_POST['youtube_videos'] : '';
        
        $hash = isset($_POST['hash']) ? $_POST['hash'] : '';

        $content_ua = isset($_POST['content_ua']) ? $_POST['content_ua'] : '';
        $name_ua = isset($_POST['name_ua']) ? $_POST['name_ua'] : '';
        $fullname_ua = isset($_POST['fullname_ua']) ? $_POST['fullname_ua'] : '';

        $content_en = isset($_POST['content_en']) ? $_POST['content_en'] : '';
        $name_en = isset($_POST['name_en']) ? $_POST['name_en'] : '';
        $fullname_en = isset($_POST['fullname_en']) ? $_POST['fullname_en'] : '';

        $not_mobile = isset($_POST['not_mobile']) ? $_POST['not_mobile'] : '';
        $not_site = isset($_POST['not_site']) ? $_POST['not_site'] : '';

        $out = '';

//        $ut=date_parse($uxt);
//        $uxt=mktime($ut['hour'], $ut['minute'], $ut['second'], $ut['month'], $ut['day'], $ut['year']);

        $results = '';
        
        // поиск
        $query = isset($_POST['query']) ? trim($_POST['query']) : '';
        $fields = isset($_POST['search_fields']) && $_POST['search_fields'] ? $_POST['search_fields'] : array();
        if($query && $fields){
            if($query && $fields){
//                $out = '<h3>Пошук «'.htmlspecialchars($query).'»</h3>';
                
                $where = array();
                foreach($fields as $field => $v){
                    $where[] = $this->all_configs['db']->makeQuery(" `".mysql_real_escape_string($field)."` LIKE ? ", array('%'.$query.'%'));
                }
                
                $where = implode(' OR ', $where);
                
                $pages = '<ul>';
                $p = $this->all_configs['db']->query('SELECT id, name FROM {map} WHERE ?q', array($where), 'assoc');
                foreach($p as $pg){
                    $pages .= '<li><a href="'.$this->all_configs['prefix'].'map/'.$pg['id'].'">'.$pg['name'].'</a></li>';
                }
                $pages .= '</ul>';
                
                $results .= '
                    '.($p ? $pages : 'Не знайдено.').'
                ';
                
            }
        }
        
//        if(!isset($this->all_configs['arrequest'][1])){


            $out = '<h3>'.l('map_module_map').'</h3>';
          
            $search = '
                <fieldset>
                    <form method="post" action="'.$this->all_configs['prefix'].'map#search">
                        <legend>'.l('map_search_title').'</legend>
                        '.l('map_search_where').':
                        <label class="checkbox">
                            <input type="checkbox" '.(isset($_POST['search_fields']['name']) || !isset($_POST['query']) ? 'checked="checked"' : '').' name="search_fields[name]"> '.l('map_search_name').'
                        </label>
                        <label class="checkbox">
                            <input type="checkbox" '.(isset($_POST['search_fields']['fullname']) || !isset($_POST['query']) ? 'checked="checked"' : '').' name="search_fields[fullname]"> '.l('map_search_head').'
                        </label>
                        <label class="checkbox">
                            <input type="checkbox" '.(isset($_POST['search_fields']['content']) || !isset($_POST['query']) ? 'checked="checked"' : '').' name="search_fields[content]"> '.l('map_search_content').'
                        </label>
                        <label class="checkbox">
                            <input type="checkbox"  '.(isset($_POST['search_fields']['url']) || !isset($_POST['query']) ? 'checked="checked"' : '').' name="search_fields[url]"> '.l('map_search_link').'
                        </label>
                        <br>
                        <input value="'.htmlspecialchars($query).'" style="width: 400px;" placeholder="'.l('map_search_placeholder').'" type="text" name="query"><br>
                        <input type="submit" class="btn btn-primary" value="'.l('map_search_button').'">
                    </form>
                </fieldset>
            ';
			
			
			$prices = '
			<h3>Экспорт таблиц</h3>
			<a class="btn" href="'.$this->all_configs['prefix'].'map/export-map-price-small-csv/#prices" >'.l('map_export_price_table_button').'</a>
			<a class="btn" href="'.$this->all_configs['prefix'].'map/export-map-price-full-csv#prices" >'.l('map_export_price_full_table_button').'</a>
			<hr>
			<h3>Импорт таблиц</h3>
			<form action="'.$this->all_configs['prefix'].'map/upload-prices#prices" method=post enctype=multipart/form-data>
			<input name="import_prices"  id="import_prices"  type="file">
			<input type = "submit" class="btn btn-primary" value="Upload" id="upload_prices" >
			</form>
			<span class="label label-info">При редактировании в Excell сохранять как CSV(MS-DOS) разделители - точка с запятой</span>
			';
			
			
            if($results){
                $search .= '
                    <fieldset>
                        <legend>'.l('map_search_results').'</legend>
                        '.$results.'
                    </fieldset>
                ';
            }
            
            $out.='
                <div class="tabbable"> 
                    <ul class="nav nav-tabs">
                        <li class="active"><a href="#desc" data-toggle="tab">'.l('map_description_tab').'</a></li>
                        <li><a href="#search" data-toggle="tab">'.l('map_search_tab').'</a></li>
						<li><a href="#prices" data-toggle="tab">'.l('map_prices_tab').'</a></li>
						
                    </ul>


                    <div class="tab-content">
                        <div class="tab-pane active" id="desc">
                            '.l('map_description').'
                        </div>
                        
                        <div class="tab-pane" id="search">
                            '.$search.'
                        </div>
						<div class="tab-pane" id="prices">
                            '.$prices.'
                        </div>
                    </div>
                </div>
                                
            ';
//        }


###############################################################################
        if(isset($this->all_configs['arrequest'][1]) && is_numeric($this->all_configs['arrequest'][1])){
            $pp = $this->all_configs['db']->query("SELECT * FROM {map} WHERE id=?i", array($this->all_configs['arrequest'][1]), 'row');
            $map_langs = $this->all_configs['db']->query("SELECT *
                                     FROM {map_strings} 
                                     WHERE map_id = ?i", array($this->all_configs['arrequest'][1]), 'assoc:lang');
            $pp = translates_for_page($this->lang, $this->def_lang, $map_langs, $pp);
            
            $out.='<ul>';

            $pre_title = $pp['name'].($pp['fullname'] ? ' ('.$pp['fullname'].')' : '').'. ';

            $sqlmod = $this->all_configs['db']->query("SELECT * FROM {map_module} WHERE page_id = ?i", array($pp['id']), 'assoc');
            $page_modules = array();
            foreach($sqlmod as $ppmod){
                $page_modules[] = $ppmod['module'];
            }

            $page = $pp['name'];
            if($pp['is_page'] && $pp['state']){
                $page = '<a href="'.$this->all_configs['siteprefix'].gen_full_link($pp['id']).'" target="_blank">'.$pp['name'].'</a>';
            }
            
            $out = '<h3>'.l('map_selected_page').' «'.$page.'»</h3>';

            $parent_page_type = $this->all_configs['db']->query("SELECT page_type FROM {map} WHERE id = ?i", array($pp['parent']), 'el');
            
            if(class_exists('social') && in_array($parent_page_type, array(1, 6, 7))){
                $out .= social::get_buttons($pp['id']).'<br>';
                $out .= '<script type="text/javascript" src="'.$this->all_configs['prefix'].'modules/social/js/main.js"></script>';
            }
            
            if(!isset($this->all_configs['arrequest'][2]) || substr($this->all_configs['arrequest'][2], 0, 3) == 'mod'){
                #создаем список для выпадающего меню родителей
                $parentoption = $this->gen_parentoption($pp['parent']);
                
                #создали список.

                $sqls = $this->all_configs['db']->query("SELECT * FROM {section} ORDER BY prio")->assoc();
                $sel_section = ' <select name="section">';
                foreach($sqls as $pps){
                    $sel_section.='<option '.($pp['section'] == $pps['id'] ? 'selected="selected"' : '').' value="'.$pps['id'].'">'.$pps['name'].'</option>';
                }
                $sel_section.='</select>';


                #Выполняем код в модулях
                /**
                 * Обязательные в модулях:
                 * if ($this->all_configs['arrequest'][2]=='update') - сохранение
                 * if (!$this->all_configs['arrequest'][2]){ - вывод
                 *      $module_li_tab - ХТМЛ вкладки
                 *      $tab_content - наполнение вкладки
                 *
                 */
                $module_li_tab = array();
                $tab_content = array();

                foreach($page_modules AS $el){
                    if(file_exists($this->all_configs['path'].'modules/map/site_content_modules/module_'.$el.'.php'))
                        require_once $this->all_configs['path'].'modules/map/site_content_modules/module_'.$el.'.php';
                }


                //работа с картинкой
                //if (file_exists($this->all_configs['sitepath'].'images/pages')){
                #определяем все папки в images/
                

                $out.='
                    <form  action="'.$this->all_configs['prefix'].'map/'.$pp['id'].'/update" method="POST" enctype="multipart/form-data" class="changeme" id="form_map">

                        <!-- the tabs -->
                        <div class="tabbable"> <!-- Only required for left/right tabs -->
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#content" data-toggle="tab">'.l('map_tab_page').'</a></li>
                                <li><a href="#settings" data-toggle="tab">'.l('map_tab_settings').'</a></li>
                                <li><a href="#modules" data-toggle="tab">'.l('map_tab_modules').'</a></li>
                                <li><a href="#prices" data-toggle="tab">'.l('map_tab_prices').'</a></li>
                                <!--<li><a href="#order" data-toggle="tab">'.l('map_tab_order').'</a></li>-->
                                '.implode("\n", $module_li_tab).'
                            </ul>


                            <div class="tab-content">
                            

                                <!-- контент-->
                                
                                <div class="tab-pane active" id="content">
              
                                    <label class="checkbox">
                                        <input type="checkbox" name="state" value="1" '.
                        ($pp['state'] == 1 ? 'checked="checked"' : '').'> '.l('map_published').'<br><br>
                                    </label>

                                    '.l('map_page_name').'<br>
                                    <input type="text" size="50" name="name" value="'.$pp['name'].'" class="" ><br><br>

                                    '.l('map_page_url').'<br>
                                    <input type="text" size="50" name="url" value="'.$pp['url'].'" class="" ><br><br>

                                    '.l('map_page_fullname').'<br>
                                    <textarea name="fullname" style="width: 350px" rows="3">'.$pp['fullname'].'</textarea><br><br>

                                    '.l('map_description_name').'<br>
                                    <input type="text" size="50" name="description_name" value="'.$pp['description_name'].'" class="" ><br><br>

                                    Заголовок для консультанта<br>
                                    <input style="margin-bottom:0" type="text" size="50" name="chat_caption" value="'.$pp['chat_caption'].'" class="" ><br>
                                    <small class="text-info" style="display:block;margin-top:-4px">проконсультирую по ремонту ......</small><br><br>

                                    '.l('map_product_category').'<br>
                                    <input type="text" size="50" name="category_id" value="'.$pp['category_id'].'" class="" ><br><br>

                                    <div style="margin: 0 0 5px 5px;">
                                        <div style="float: left; margin: 4px 10px 0 0">Редактор:</div>
                                        <input type="checkbox" id="toggle_mce"'.((isset($_COOKIE['mce_on']) && $_COOKIE['mce_on']) || !isset($_COOKIE['mce_on']) ? 'checked="checked"' : '').'>
                                    </div>
                                    <textarea id="page_content" name="content" class="mcefull" rows="18" cols="80" style="width:650px;height:320px;">'.$pp['content'].'</textarea>
                                    <br><br>
                                    
                                    Видео ютуб. Вставлять <i>код</i> видео. Пример: T9z1P8srdQM<br>
                                    <textarea id="youtube_videos" name="youtube_videos" rows="3" cols="300"  style="width:650px;">'.$pp['youtube_videos'].'</textarea>
                                    <br><br>

                                    
                                    '.$this->out_choose_picture($pp).'
                                    
                                    <br><br>
                                    
                                    <!--<input type="submit" value="'.l('save').'" class="btn btn-primary">-->
                                   
                            </div>


                            <!-- настройки -->
                            
                            <div class="tab-pane" id="settings">
                        ';

                $page_types = $this->all_configs['db']->query("SELECT * FROM {page_types}")->assoc();

                $types = '<select name="page_type"><option value="0">---------</option>';
                foreach($page_types as $type){
                    $selected = ($pp['page_type'] == $type['id'] ? 'selected="selected"' : '');
                    $types .= '<option '.$selected.' value="'.$type['id'].'">'.$type['name'].'</option>';
                }
                $types .= '</select>';


                $out.='      
                            
                            '.l('map_page_type').' <br>
                            '.$types.' <br><br>
                            <label class="checkbox"><input type="checkbox" name="is_page" value="1" '.
                        ($pp['is_page'] == 1 ? 'checked="checked"' : '').'> '.l('map_page_is_page').'</label><br>

                            <label class="checkbox"><input type="checkbox" name="buy_old" value="1" '.
                            ($pp['buy_old'] == 1 ? 'checked="checked"' : '').'> '.l('map_buy_old').'</label>

                            '.l('map_hotline_price').' '.$pp['hotline_price'].' грн.<br><br>
                                
                            '.l('map_parse_hotline').'<br>
                            <input type="text" name="hotline_url"  class="span8" value="'.$pp['hotline_url'].'"><br><br>
                                                        
                            '.l('map_page_prio').'<br>
                            <input type="text" name="prio" value="'.$pp['prio'].'" class=":integer" ><br><br>

                            '.l('map_page_color').'<br>
                            <input type="text" name="page_color" value="'.$pp['page_color'].'" ><br><br>
                            
                            '.l('map_page_parent').'<br>
                            <select name="parent">
                                <option value="0">*'.l('map_max_category').'*</option>
                                '.$parentoption.'
                            </select><br>
                            <small class="text-warning">'.l('map_page_parent_warning').'</small><br><br>

                            '.($pp['parent'] == 0 ? ''.l('map_section').'<br>'.$sel_section.'<br><br>' : '');

                $out_r = l('map_page_redirect').'<br>';
                #создаем список для выпадающего меню редиректа
                $sql0 = $this->all_configs['db']->query("SELECT * FROM {map} WHERE parent = ?i AND section = ?i ORDER BY section, prio", array($pp['id'], $pp['section']), 'assoc:id');
                $out_r.='<select name="redirect">';
                $out_r.='<option '.($pp['redirect'] == 0 ? 'selected' : '').' value="0">'.l('map_page_no_redirect').'</option>';
                if($sql0){
                    $translates0 = get_few_translates(
                            'map', 'map_id', $this->all_configs['db']->makeQuery("map_id IN(?q)", array(implode(',', array_keys($sql0))))
                    );
                    foreach($sql0 as $pp0){
                        $pp0 = translates_for_page($this->lang, $this->def_lang, $translates0[$pp0['id']], $pp0, true);
                        $optsel0 = $pp['redirect'] == $pp0['id'] ? 'selected' : '';
                        $out_r.='<option '.$optsel0.' value="'.$pp0['id'].'">'.$pp0['name'].' ('.$pp0['url'].')</option>';
                    }
                }
                $out.=$out_r.'</select><br><br>';

                $out.=l('map_page_date').'<br><input type="text" name="uxt" value="'.date("Y-m-d H:i:s", strtotime($pp['uxt'])).'" size="10" id="datetimepick" /> <br><br>';
                $out.='<fieldset>
                            <legend> '.l('map_templates').' </legend>
                            '.l('map_page_template_header').'<br>
                            '.$this->gen_templates_list('header', 'template_header', $pp['template_header']).'
                            <br><br>
                            '.l('map_page_template_body_header').'<br>
                            '.$this->gen_templates_list('body_header', 'template_body_header', $pp['template_body_header']).'
                            <br><br>
                            '.l('map_page_template').'<br>
                            '.$this->gen_templates_list('template', 'template', $pp['template']).'
                            <br><br>
                            '.l('map_page_template_inner').'<br>
                            '.$this->gen_templates_list('inner', 'template_inner', $pp['template_inner']).'
                            <br><br>
                        </fieldset>
                        <br><br>';
//                            Футер<br>
//                            '.$this->gen_templates_list('footer', 'template_footer', $pp['template_footer']).'



                $out.='
                            
                            <fieldset>
                                <legend> '.l('map_meta').' </legend>
                            '.l('map_meta_description').'<br><textarea rows="10" cols="80" style="width:580px;height:100px;" name="metadescription">'.$pp['metadescription'].'</textarea><br><br>
                            '.l('map_meta_keywords').'<br><textarea rows="10" cols="80" style="width:580px;height:100px;" name="metakeywords">'.$pp['metakeywords'].'</textarea><br><br>
                            &lt;meta name="robots" content="follow,index"&gt;<br><textarea name="meta" rows="10" cols="80" style="width:580px;height:50px;">'.$pp['meta'].'</textarea>
                            </fieldset>
                            <br><br>
                            <label class="checkbox">
                                <input type="checkbox" name="is_gmap" value="1" '.
                        ($pp['is_gmap'] == 1 ? 'checked="checked"' : '').'> '.l('map_map_coords').'
                            </label>
                            Lat <input type="text" name="lat" value="'.$pp['lat'].'" class=":float" ><br>
                            Lng <input type="text" name="lng" value="'.$pp['lng'].'" class=":float" >


                            <!--<br><br><br>
                            <input type="submit" value="'.l('save').'" class="btn btn-primary">-->
                        </div>
<!-- модули -->
                        <div class="tab-pane" id="modules">
                            '.$this->gen_modules_list($pp['id'], $page_modules).'

                            <!--<br><br><br>
                            <input type="submit" value="'.l('save').'" class="btn btn-primary">-->
                         

                       </div>
<!-- Цены -->
                        <div class="tab-pane" id="prices">
						<h4>Экспорт таблиц</h4>
			<a class="btn btn-small" href="'.$this->all_configs['prefix'].'map/export-map-price-small-csv/'.$pp['id'].'#prices" >'.l('map_export_price_table_button').'</a>
			<a class="btn btn-small" href="'.$this->all_configs['prefix'].'map/export-map-price-full-csv/'.$pp['id'].'#prices" >'.l('map_export_price_full_table_button').'</a>
						<hr>
                            <h4>Услуги</h4>
                            '.  Inc_helper_map_price::show_tables_by_type($pp['id'], 1, $this->lang == $this->def_lang).'
                            <h4>Ремонт</h4>
                            '.  Inc_helper_map_price::show_tables_by_type($pp['id'], 2, $this->lang == $this->def_lang).'
                       </div>
                       
<!-- сортировка -->
                       <!--<div class="tab-pane" id="order">
                               
                                

                                <ul class="sortTable">
                                
                               ';
                $i = 1;
                $all_sql = $this->all_configs['db']->query("SELECT * FROM {map} WHERE parent=?i ORDER BY `prio` ASC", array($pp['id']), 'assoc:id');
                if($all_sql){
                    $all_sql_trans = get_few_translates(
                            'map', 'map_id', $this->all_configs['db']->makeQuery("map_id IN(?q)", array(implode(',', array_keys($all_sql))))
                    );
                    foreach($all_sql as $all_row){
                        $all_row = translates_for_page($this->lang, $this->def_lang, $all_sql_trans[$all_row['id']], $all_row, true);
                        if($all_row['state']){
                            $out .= '
                                <li>
                                    <table cellpadding="5">
                                        <tr>
                                            <td valign="middle">
                                                <input type="hidden" name="sort_arr['.$all_row['id'].']" value="'.$all_row['prio'].'">
                                                <b>'.$i++.'</b>
                                            </td>

                                            <td valign="middle">
                                                <a style="font-size: 16px;" href="'.$this->all_configs['prefix'].'map/'.$all_row['id'].'">
                                                    '.(isset($all_row['article']) && $all_row['article'] ? $all_row['article'] : $all_row['name']).'
                                                </a>
                                            </td>
                                        </tr>
                                    </table>
                                </li>
                           ';
                        }
                    }
                    $out .= '  
                        </ul>

                        <br><br>
                            <input type="submit" id="saveSortOrder" value="'.l('save').'" class="btn btn-primary">
                   ';
                }else{
                    $out .= l('map_no_childrens');
                }


                $out .= '  
                       </div>-->
                        <input type="submit" id="save_all_fixed" value="'.l('map_save_changes').'" class="btn btn-primary">
                   </form>
                ';


#Закладки из модулей
                $out.=implode("\n", $tab_content);

                $out.='
                    </div>
                ';

                $out.=isset($outadditional) ? $outadditional : '';
            }  //if (!$this->all_configs['arrequest'][2]){

//            if(isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'del'){ //удаление страницы
//                header('Content-Type: text/html; charset=utf-8');
//                echo $ifauth['login'];
//                if ($ifauth['login']=='i@restore.kiev.ua') {
//                    
//                    $rmid =  $this->all_configs['db']->query("SELECT COUNT(*) FROM {map} WHERE parent = ?i", array($this->all_configs['arrequest'][1]), 'el');
//                    echo '<br> Найдено дочек '.$rmid;
//                    if (!$rmid) {
//                        $this->all_configs['db']->query("DELETE FROM {map} WHERE id = ?i", array($this->all_configs['arrequest'][1]));
//                        echo '<br> Удаляем '. $this->all_configs['arrequest'][1];
//                    }
//                } else {
//                    echo '<br>Запрещено';
//                }
//                exit;
//            }
            
            if(isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'update'){


                if(isset($_POST['sort_arr'])){
                    //print_r($_POST['sort_arr']);
                    foreach($_POST['sort_arr'] as $map_id => $sort_val){
                        $this->all_configs['db']->query("UPDATE {map} SET prio = ?i WHERE id = ?i", array($sort_val, $map_id));
                    }
                }

                $this->all_configs['db']->query("INSERT INTO {map_strings}(map_id, name, fullname, content, 
                                                        metadescription, metakeywords,lat,lng,
                                                        description_name,chat_caption, lang)
                            VALUES(?i, ?, ?, ?, ?, ?, ?f, ?f, ?, ?, ?) 
                            ON DUPLICATE KEY 
                            UPDATE name = ?, fullname = ?, content = ?, 
                                   metadescription = ?, metakeywords = ?, 
                                   lat = ?f, lng = ?f, 
                                   description_name = ?,chat_caption = ?", 
                        array($this->all_configs['arrequest'][1], $name, $fullname, $content, $metadescription,
                              $metakeywords,$lat,$lng, $description_name,$chat_caption, $this->lang, $name, $fullname, 
                              $content, $metadescription, $metakeywords,$lat,$lng,$description_name,$chat_caption));
                //list($ud, $um, $uy)=explode('-', $post_uxt);
                //$post_uxt=mktime(0,0,0, $um, $ud, $uy);

//                print_r($_POST);
//                exit;
                
                $sql = $this->all_configs['db']->query("UPDATE {map} SET
                        meta=?,
                        url=?,
                        prio=?i,
                        state=?i,
                        parent=?i,
                        picture=?,
                        picture2=?,
                        template_header=?,
                        template=?,
                        template_inner=?,
                        template_body_header=?,
                        template_footer=?,
                        redirect=?i,
                        is_gmap=?i,
                        is_page=?i,
                        uxt=?,
                        gallery=?,
                        page_type=?i,
                        page_color=?,
                        buy_old=?i,
                        hotline_url=?,
                        category_id=?n,
                        youtube_videos=?
                     WHERE id = ?i", array(
                    $meta,
                    $url,
                    $prio,
                    $state,
                    $parent,
                    $picture,
                    $picture2,
                    $template_header,
                    $template,
                    $template_inner,
                    $template_body_header,
                    $template_footer,
                    $redirect,
                    $is_gmap,
                    $is_page,
                    $post_uxt,
                    $gallery,
                    $page_type,
                    $page_color,
                    $buy_old,
                    $hotline_url,
                    $category_id,
                    $youtube_videos,     
                    $this->all_configs['arrequest'][1]));

                #нафиг отделять секции?
//                if ($sql) {

                if($section != 0){
                    $pps = $this->all_configs['db']->query("SELECT section FROM {map} WHERE id = ?i", array($this->all_configs['arrequest'][1]), 'row');
                    if($pps['section'] == $section){
                        $sql = $this->all_configs['db']->query("UPDATE {map} SET section = ?i WHERE id = ?i", array($section, $this->all_configs['arrequest'][1]));
                    }else{
                        $sql = $this->all_configs['db']->query("UPDATE {map} SET section = ?i, state=0 WHERE id = ?i", array($section, $this->all_configs['arrequest'][1]));
                    }
                }//-/ if ($section!=0)
                #Сохраняем модули
                $this->save_modules_from_post($this->all_configs['arrequest'][1]);

                foreach($page_modules AS $el){
                    if(file_exists($this->all_configs['path'].'modules/map/site_content_modules/module_'.$el.'.php'))
                        require_once $this->all_configs['path'].'modules/map/site_content_modules/module_'.$el.'.php';
                }

                $out.=l('map_update_success').' <a href="'.$this->all_configs['prefix'].'map/'.$this->all_configs['arrequest'][1].'">'.l('map_continue').'</a>';
                header("location: ".$this->all_configs['prefix'].'map/'.$this->all_configs['arrequest'][1].$hash);
                exit;
//                } else {
//                    $out.='Ошибка. <a href="' . $this->all_configs['prefix'] . 'map/' . $this->all_configs['arrequest'][1] . $hash . '">Вернуться</a>';
//                }
            }//update
        }
###############################################################################
        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'add'){

            $sqls = $this->all_configs['db']->query("SELECT * FROM {section} ORDER BY prio")->assoc();
            $sel_section = ' <select name="section">';
            foreach($sqls as $pps){
                $sel_section .= '<option '.(isset($pp['section']) && $pp['section'] == $pps['id'] ? 'selected="selected"' : '').' value="'.$pps['id'].'">'.$pps['name'].'</option>';
            }
            $sel_section.='</select>';
            if(isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'copy'){
                $out = '<h3>Копіювання сторінки</h3><br>';
                
                $copy_page = $this->all_configs['db']->query("SELECT id, url FROM {map} WHERE id = ?i", array($this->all_configs['arrequest'][3]), 'row');
                $langss = $this->all_configs['db']->query("SELECT name, lang FROM {map_strings} 
                                     WHERE map_id = ?i", array($copy_page['id']), 'assoc:lang');
                $copy_page = translates_for_page($this->lang, $this->def_lang, $langss, $copy_page, true);


                $out .= '
                    <form action="'.$this->all_configs['prefix'].'map/addnow/copy/'.$this->all_configs['arrequest'][3].'" method="POST">
                        <label class="checkbox"><input type="checkbox" checked="checked" value="1" name="state"> '.l('map_published').'</label><br>
                        '.l('map_page_name').'<br>
                            <input type="text" name="name" value="'.$copy_page['name'].'"  class=":required" id="pagename"><br><br>
                        '.l('map_page_url').'<br>
                            <input type="text" name="url" value="'.$copy_page['url'].'"  class="" id="pageurl"><br><br>
                        '.l('map_section').'<br>
                        '.$sel_section.'<br><br>
                        '.l('map_page_parent').'<br>
                        <select name="parent">
                            <option value="0">*'.l('map_max_category').'*</option>
                            '.$this->gen_parentoption().'
                        </select><br><br>
                        <input type="submit" class="btn btn-primary" value="'.l('create').'">
                        </form>
                    <br><br>
                ';
            }else{
                $out = '<h3>'.l('map_add_page').'</h3><br>';

                $sel_parent = '';
                if(isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2]){
                    $ppp = $this->all_configs['db']->query("SELECT * FROM {map} WHERE id = ?i", array($this->all_configs['arrequest'][2]), 'row');
                    $translate = $this->all_configs['db']->query("SELECT name,lang FROM {map_strings} WHERE map_id = ?i", array($ppp['id']), 'assoc:lang');
                    $ppp = translates_for_page($this->lang, $this->def_lang, $translate, $ppp);
                    $sel_parent = l('map_sel_parent').'<br>';
                    $sel_parent .= '<input type="checkbox" name="id" value="'.$ppp['id'].'" checked> '.$ppp['name'].'</option>';
                    $sel_parent .= '<br><br>';
                }

                $out.='<form action="'.$this->all_configs['prefix'].'map/addnow" method="POST">
                    <label class="checkbox"><input type="checkbox" checked="checked" value="1" name="state"> '.l('map_published').'</label><br>
                    '.l('map_page_name').'<br>
                        <input type="text" name="name" value=""  class=":required" id="pagename"><br><br>
                    '.l('map_page_url').'<br>
                        <input type="text" name="url" value=""  class="" id="pageurl"><br><br>
                    '.l('map_section').'<br>
                    '.$sel_section.'<br><br>
                    '.$sel_parent.'
                    <input type="submit" class="btn btn-primary" value="'.l('create').'">
                    </form>
                    <br><br>
                   .

                    ';
            }
        }
///////
        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'addnow'){
            if(isset($this->all_configs['arrequest'][2]) && $this->all_configs['arrequest'][2] == 'copy'){
                $copy_from = $this->all_configs['arrequest'][3];
                $new_page_id = $this->copy_page($copy_from, $_POST);
            }else{
                $add_module = false;
                $page_type = 0;
                
                $id = isset($_POST['id']) ? $_POST['id'] : '';
                if(!is_numeric($id)){
                    $id = 0;
                }
                $is_page = isset($_POST['is_page']) ? 1 : 0;
                $state = isset($_POST['state']) ? 1 : 0;
                
                $new_page_id = $this->create_page(array(
                    'id' => $id, // parent
                    'state' => $state,
                    'is_page' => $is_page,
                    'prio' => 0,
                    'name' => $_POST['name'],
                    'url' => $_POST['url'],
                    'section' => $_POST['section'],
                    'page_type' => $page_type
                ), $add_module);
            }
            $out = l('created').'. <a href="'.$this->all_configs['prefix'].'map/'.$id.'">'.l('map_continue').'</a>. '.l('map_wait_for_redirect');
            $out .= '<META HTTP-EQUIV="refresh" CONTENT="1; URL='.$this->all_configs['prefix'].'map/'.$id.'">'."\n";
        }

        ###############################################################################

        if ( isset ( $this->all_configs['arrequest'][1] ) && $this->all_configs['arrequest'][1] == 'export-map-price-small-csv' ) { 

                $filter = $cat_id = null;
                if ( isset ( $this->all_configs['arrequest'][2] ) ){
                        $cat_id = (int)$this->all_configs['arrequest'][2];
                        $filter = $this->all_configs['db']->makeQuery(' AND m.parent = ?i', array($cat_id)) ;
                }

                $sql = $this->all_configs['db']->makeQuery("SELECT 
                p.id ,
                p.map_id ,
                p.price ,
                p.prio 
                FROM {map_prices} AS p
                INNER JOIN {map} AS m ON m.id = p.map_id 
                WHERE p.table_type= ? ?q", array(1, $filter));
                $price_table = $this->all_configs['db']->plainQuery($sql)->assoc('id');

                if ( $price_table ){
                        $row = 0 ;
                        $translates0 = get_few_translates(
                            'map_prices', 'row_id', $this->all_configs['db']->makeQuery("row_id IN(?q)", array(implode(',', array_keys($price_table))))
                        );
                        foreach ( $price_table as $ind ){
                                $ind = translates_for_page($this->lang, $this->def_lang, $translates0[$ind['id']], $ind, true);
                                $langss = $this->all_configs['db']->query("SELECT name, lang FROM {map_strings} 
                                                     WHERE map_id = ?i", array($ind['map_id']), 'assoc:lang');
                                $pp = array();
                                $pp = translates_for_page($this->lang, $this->def_lang, $langss, $pp, true);

                                $export[$row]['id'] = $ind['id'];
                                $export[$row]['map_id'] = $ind['map_id'];
                                $export[$row]['equipment_name'] = $pp['name'];
                                $export[$row]['name'] = $ind['name'];
                                $export[$row]['price_mark'] = $ind['price_mark'];
                                $export[$row]['price'] = $ind['price'];
                                $export[$row]['price_new'] = $ind['price'];
                                $export[$row]['time_required'] = $ind['time_required'];
                                $export[$row]['prio'] = $ind['prio'];

                                $row ++ ;
                        }

                        Inc_helper_map_price::download_send_headers( "map_small_export_".$cat_id."_" . date("YmdHis") . ".csv" );
                        echo Inc_helper_map_price::array2csv( $export );
                        exit;
                }

        }

        if ( isset( $this->all_configs['arrequest'][1] ) && $this->all_configs['arrequest'][1] == 'export-map-price-full-csv' ) { 

                $filter = $cat_id = null;
                if ( isset ( $this->all_configs['arrequest'][2] ) ){
                        $cat_id = (int)$this->all_configs['arrequest'][2];
                        $filter = $this->all_configs['db']->makeQuery(' AND m.parent = ?i', array($cat_id)) ;
                }
                
                $sql = "SELECT 
                p.id ,
                p.map_id ,
                p.price_copy ,
                p.price ,
                p.prio 
                FROM {map_prices} AS p
                INNER JOIN {map} AS m ON m.id = p.map_id 
                WHERE p.table_type= ? ".$filter;
                $price_table = $this->all_configs['db']->query( $sql, array( 2 ) )->assoc('id');

                if ( $price_table ){
                        $translates0 = get_few_translates(
                            'map_prices', 'row_id', $this->all_configs['db']->makeQuery("row_id IN(?q)", array(implode(',', array_keys($price_table))))
                        );
                        $row = 0 ;
                        foreach ( $price_table as $ind ){
                                $ind = translates_for_page($this->lang, $this->def_lang, $translates0[$ind['id']], $ind, true);
                                $langss = $this->all_configs['db']->query("SELECT name, lang FROM {map_strings} 
                                                     WHERE map_id = ?i", array($ind['map_id']), 'assoc:lang');
                                $pp = array();
                                $pp = translates_for_page($this->lang, $this->def_lang, $langss, $pp, true);
                                
                                $export[$row]['id'] = $ind['id'];
                                $export[$row]['map_id'] = $ind['map_id'];
                                $export[$row]['equipment_name'] = $pp['name'];
                                $export[$row]['name'] = $ind['name'];
                                $export[$row]['price_copy_mark'] = $ind['price_copy_mark'];
                                $export[$row]['price_copy'] = $ind['price_copy'];
                                $export[$row]['price_copy_new'] = $ind['price_copy'];
                                $export[$row]['price_mark'] = $ind['price_mark'];
                                $export[$row]['price'] = $ind['price'];
                                $export[$row]['price_new'] = $ind['price'];
                                $export[$row]['time_required'] = $ind['time_required'];
                                $export[$row]['prio'] = $ind['prio'];

                                $row ++ ;
                        }

                        Inc_helper_map_price::download_send_headers( "map_full_export_".$cat_id."_" . date("YmdHis") . ".csv" );
                        echo Inc_helper_map_price::array2csv( $export );
                        exit;
                }
        }

###############################################################################
        if(isset($this->all_configs['arrequest'][1]) && $this->all_configs['arrequest'][1] == 'upload-prices'){

                if ( isset ($_FILES['import_prices']) ){

                        $file_name = $_FILES['import_prices']['name'];
                        $file_ext = pathinfo( $file_name, PATHINFO_EXTENSION );
                        $file_size = $_FILES['import_prices']['size'];

                        $error = null;
                        $msg = null;
                        $rows = null;

                        if( $file_ext != 'csv' ){
                                $error = 'необходим формат CSV';	
                        }
                        if( $file_size > 20971520 ){
                                $error = 'максимальный размер 20МБ';	
                        }

                        if ( !$error ){
                                $contents = file_get_contents($_FILES['import_prices']['tmp_name']);
                                $rows .= $this->parse_prices_to_db( $contents );
                                $msg .= '<span class="label label-success"> обновлено '.$rows.' записей из файла ' .$file_name .'</span>';  
                        }
                        else {
                                $msg .= '<span class="label label-warning">'.$error.'</span>';
                        }


                        $out .= '<div class="panel panel-default">'
                . '<div class="panel-body">'.$msg.'</div></div>';
                                //$out .= $file_name.$file_ext.$file_size;
                }
        }
		
        if (isset($this->all_configs['arrequest'][1]) 
                && $this->all_configs['arrequest'][1] == 'del-from-prices-table'
                && is_numeric($this->all_configs['arrequest'][2]) 
                && is_numeric($this->all_configs['arrequest'][3])) { 
            
            //@todo переделать на POST!
            $this->all_configs['db']->query("DELETE FROM {map_prices} WHERE id = ?i LIMIT 1", array($this->all_configs['arrequest'][2]));
            $this->all_configs['db']->query("DELETE FROM {map_prices_strings} WHERE row_id = ?i", array($this->all_configs['arrequest'][2]));
            header("location: ".$this->all_configs['prefix'].'map/'.$this->all_configs['arrequest'][3].'#prices');
            exit;
        }
        
        
        return $out;
    }

    private function create_page($fields, $add_module){
        $default_lang = $this->all_configs['db']->query("SELECT url, name FROM {langs} WHERE `default` = 1")->row();
//        if($add_module !== false){
//            $module_templates = $this->get_module_templates($add_module, '', false);
//            $template = isset($module_templates[0]) ? $module_templates[0] : 'content_default';
//        }else{
            $template = 'default';
//        }

        $id = $this->all_configs['db']->query("INSERT INTO {map}(url, section, parent, state, is_page, page_type, template_inner, prio) 
                          VALUES (?, ?i, ?i, ?i, ?i, ?i, ?, ?i)
        ", array(trim($fields['url']), $fields['section'], $fields['id'], $fields['state'], 
                1, $fields['page_type'], $template, $fields['prio']), 'id');
        $this->all_configs['db']->query("INSERT INTO {map_strings}(map_id, name, fullname, content, lang) "
                . "VALUES(?i, ?, '', '', ?)", array($id, $fields['name'], $default_lang['url']));
        if($add_module !== false){
            if($add_module != 'content_default'){
                $this->all_configs['db']->query("INSERT INTO {map_module}(page_id,module) VALUES(?i,?)", array($id, $add_module));
            }
        }
        return $id;
    }
    
    private function copy_page($source_id, $new_data){
        $ppage = $this->all_configs['db']->query("SELECT * FROM {map} WHERE id = ?i", array($source_id), 'row');
        $ppage_translates = $this->all_configs['db']->query("SELECT * FROM {map_strings} WHERE map_id = ?i", array($source_id), 'assoc:lang');
        
        $ppage = array_merge($ppage, $new_data);
        if(isset($new_data['name'])){
            $ppage_translates[$this->lang]['name'] = $new_data['name'];
            unset($ppage['name']);
        }

        $fields = array();
        $values = array();
        foreach($ppage as $field => $value){
            if($field != 'id'){
                $fields[] = $field;
                if($field == 'uxt'){
                    $values[] = 'NOW()';
                }else{
                    $values[] = $this->all_configs['db']->makeQuery('?', array($value));
                }
            }
        }

        $id = $this->all_configs['db']->query("INSERT INTO {map}(?q) VALUES(?q)", 
                    array(implode(',', $fields), implode(',', $values)), 'id');

        foreach($ppage_translates as $translate){
            if(!isset($translate['lang']) || !$translate['lang']) continue;
            $translate['map_id'] = $id;
            if(!isset($translate['name'])){
                $translate['name'] = '';
            }
            if(!isset($translate['fullname'])){
                $translate['fullname'] = '';
            }
            if(!isset($translate['content'])){
                $translate['content'] = '';
            }
            if(!isset($translate['metakeywords'])){
                $translate['metakeywords'] = '';
            }
            if(!isset($translate['metadescription'])){
                $translate['metadescription'] = '';
            }
            if(!isset($translate['description_name'])){
                $translate['description_name'] = '';
            }
            if(!isset($translate['chat_caption'])){
                $translate['chat_caption'] = '';
            }
            $this->all_configs['db']->query("INSERT INTO {map_strings}(map_id, name, fullname, content, metakeywords, 
                                                                       metadescription, description_name, chat_caption, lang)"
                    . " VALUES(?i:map_id, ?:name, ?:fullname, ?:content, ?:metakeywords, 
                                ?:metadescription, ?:description_name, ?:chat_caption,  ?:lang)", $translate);
        }
        
        $modules = $this->all_configs['db']->query("SELECT * FROM {map_module} WHERE page_id = ?i", array($source_id), 'assoc');
        $mods = array();
        foreach($modules as $module){
            $mods[] = $this->all_configs['db']->makeQuery("(?i, ?)", array($id, $module['module']));
        }

        if($mods){
            $query = $this->all_configs['db']->query("INSERT INTO {map_module}(page_id, module) VALUES ?q", 
                        array(implode(',', $mods)));
        }
        
        return $id;
    }
    
    ###############################################################################

    protected function ajax_upload_picture_for_page(){

        $gallery = isset($_GET['gallery']) ? str_replace('/', '', $_GET['gallery']) : '';
        
        require_once 'class_image.php';
        
        function resize_me($a, $w, $h, $file, $gallery, $scale){
            $empty_file = str_replace('_s3', '', $file);
            $path_parts = pathinfo($empty_file);

            $new_img_name = $path_parts['filename'].$a.'.'.$path_parts['extension'];

            $img = new LiveImage($this->all_configs['sitepath'].'images/'.$gallery.'/'.$file);
            $img->resize($w, $h, $scale);
            //$img->resize(135, 125, true);

            $img->output(null, $this->all_configs['sitepath'].'images/'.$gallery.'/'.$new_img_name);
            chmod($this->all_configs['sitepath'].'images/'.$gallery.'/'.$new_img_name, 0777);
        }

        $upload_filename = $this->translitIt($_GET['qqfile']);
        $path_upl_fname = pathinfo($upload_filename);

        //    /if($path_upl_fname['extension']!='png' && $_GET['resizeFoto']){
        //        echo json_encode(array('error'=>'Допускаютя только png изображения'));
        //        exit;
        //    }


        if(file_exists($this->all_configs['sitepath'].'images/'.$gallery.'/'.$upload_filename)){
            $upload_filename = rand(1, 1000).$upload_filename;
        }



        $_GET['qqfile'] = $upload_filename;

        require_once 'class_qqupload.php';
        // list of valid extensions, ex. array("jpeg", "xml", "bmp")
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif');
        // max file size in bytes
        $sizeLimit = 20 * 1024 * 1024;

        $uploader = new qqFileUploader($allowedExtensions, $sizeLimit);
        $result = $uploader->handleUpload($this->all_configs['sitepath'].'images/'.$gallery.'/');
        chmod($this->all_configs['sitepath'].'images/'.$gallery.'/'.$result['filename'], 0777);

        $saved_file_name = $result['filename'];
        $image_info = getimagesize($this->all_configs['sitepath'].'images/'.$gallery.'/'.$saved_file_name);
        $width = $image_info[0];
        $height = $image_info[1];

        $max_wh = 1920;

        if($width > $max_wh || $height > $max_wh){
            if($width >= $height){
                $w = $max_wh;
                $h = $max_wh * ($height / $width);
            }

            if($width < $height){
                $h = $max_wh;
                $w = $max_wh * ($width / $height);
            }

            resize_me('', $w, $h, $saved_file_name, $gallery, true);
        }

        if($_GET['resizeFoto']){ // обрезка для страницы

            $w = 370;
            $h = 370 * ($height / $width);

            $w1 = 144;
            $h1 = 144 * ($height / $width);

            resize_me('_m', $w, $h, $saved_file_name, $gallery, true);

            resize_me('_s', $w1, $h1, $saved_file_name, $gallery, true);
        }

        if($_GET['resize_product']){ // обрезка для продукта

            $h = 500;
            $w = 500 * ($width / $height);
            
            $h1 = 200;
            $w1 = 200 * ($width / $height);

            resize_me('_pm', $w, $h, $saved_file_name, $gallery, true);
            resize_me('_ps', $w1, $h1, $saved_file_name, $gallery, true);
        }

        if($_GET['resizeFotoNews']){ // обрезка для объекта banner
            
            $w = 370;
            $h = 370 * ($height / $width);
            
            $w1 = 144;
            $h1 = 144 * ($height / $width);
            
            
            if($width >= $height){
                $h2 = 200;
                $w2 = 200 * ($height / $width);
            }

            if($width < $height){
                $w2 = 200;
                $h2 = 200 * ($width / $height);
            }
            
            resize_me('_os', $w1, $h1, $saved_file_name, $gallery, true);
            resize_me('_om2', $w2, $h2, $saved_file_name, $gallery, true);
            resize_me('_om', $w, $h, $saved_file_name, $gallery, true);
        }

        if($_GET['resize_gallery']){ // обрезка для gallery
            
            $w = 100;
            $h = 100 * ($height / $width);
            
            $w1 = 50;
            $h1 = 50 * ($height / $width);
            
            
            if($width >= $height){
                $h = 100;
                $w = 100 * ($height / $width);
            }

            if($width < $height){
                $w = 100;
                $h = 100 * ($width / $height);
            }
            
            resize_me('_gm', $w, $h, $saved_file_name, $gallery, true);
        }

        // добавляем водный знак
        if($_GET['add_watermark']){
            $this->add_watermark($gallery, $saved_file_name);
        }

        // to pass data through iframe you will need to encode all html tags
        echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);
        exit;
    }
    
    protected function translitIt($str){
        $tr = array(
            "А" => "a", "Б" => "b", "В" => "v", "Г" => "g",
            "Д" => "d", "Е" => "e", "Ж" => "j", "З" => "z", "И" => "i",
            "Й" => "y", "і" => "i", "І" => "I", "ї" => "yi", "Ї" => "YI", "К" => "k",
            "Л" => "l", "М" => "m", "Н" => "n",
            "О" => "o", "П" => "p", "Р" => "r", "С" => "s", "Т" => "t",
            "У" => "u", "Ф" => "f", "Х" => "h", "Ц" => "ts", "Ч" => "ch",
            "Ш" => "sh", "Щ" => "sch", "Ъ" => "", "Ы" => "yi", "Ь" => "",
            "Э" => "e", "Ю" => "yu", "Я" => "ya", "а" => "a", "б" => "b",
            "в" => "v", "г" => "g", "д" => "d", "е" => "e", "ж" => "j",
            "з" => "z", "и" => "i", "й" => "y", "к" => "k", "л" => "l",
            "м" => "m", "н" => "n", "о" => "o", "п" => "p", "р" => "r",
            "с" => "s", "т" => "t", "у" => "u", "ф" => "f", "х" => "h",
            "ц" => "ts", "ч" => "ch", "ш" => "sh", "щ" => "sch", "ъ" => "y",
            "ы" => "yi", "ь" => "", "э" => "e", "ю" => "yu", "я" => "ya",
            " " => "_", "/" => "_"
        );
        return strtr($str, $tr);
    }
    
    protected function add_watermark(){
        
        $gallery = isset($_GET['gallery']) ? str_replace('/', '', $_GET['gallery']) : '';
        $picture = isset($_GET['picture']) ? str_replace('/', '', $_GET['picture']) : '';

        // размер водного знака
        $mark_w = 227;
        $mark_h = 25;

        $image_path = $this->all_configs['sitepath'].'images/'.$gallery.'/'.$picture;
        $watermark_path = $this->all_configs['path'].'modules/map/img/watermark.png';

        $image_size = getimagesize($image_path);

        $pic_w = $image_size[0];
        $pic_h = $image_size[1];

        $top = $pic_h - $mark_h - 20;
        $left = $pic_w - $mark_w - 20;


        $photo = imagecreatefromjpeg($image_path);
        $watermark = imagecreatefrompng($watermark_path);
        // This is the key. Without ImageAlphaBlending on, the PNG won't render correctly.
        imagealphablending($photo, true);
        // Copy the watermark onto the master, $offset px from the bottom right corner.
        imagecopy($photo, $watermark, $left, $top, 0, 0, imagesx($watermark), imagesy($watermark));
        // Output to the browser - please note you should save the image once and serve that instead on a production website.
        imagejpeg($photo, $image_path);
    }
    
    protected function ajax_choose_picture_for_page(){
        
        $id = isset($_GET['id']) ? $_GET['id'] : '';
        $del = isset($_GET['del']) ? $_GET['del'] : '';
        $filtr = isset($_GET['filtr']) ? $_GET['filtr'] : '';
        $gallery = isset($_GET['gallery']) ? str_replace('/', '', $_GET['gallery']) : '';
        
        if($del){
            $del = urldecode($del);
            if(preg_match("/^[a-zA-Z0-9_.-]+$/", $del)){
                echo 'удалено '.htmlspecialchars($del).'<br>';
                unlink($this->all_configs['sitepath'].'images/'.$gallery.'/'.$del);
            }else{
                echo 'Не удаляется '.$del.'<br>';
            }
        }

        $files = array();
        if(is_dir($this->all_configs['sitepath'].'images/'.$gallery.'/')){
            $files = scandir($this->all_configs['sitepath'].'images/'.$gallery.'/');
        }
        $cnt = 0;
        $out = '';
        foreach($files as $file){
            $path_parts = pathinfo($file);
            if(isset($path_parts['extension']) && in_array($path_parts['extension'], self::$allowed_ext) && $file != 'nopicture.jpg'){
                if($filtr == 'small' && (strpos($file, '_m.') === false && strpos($file, '_s1.') === false))
                    continue;

                $img_title = $this->all_configs['db']->query("SELECT name, name_en FROM {image_titles} WHERE image = ? AND gallery = ?", array($file, $gallery), 'row');

                $tmp_img = getimagesize($this->all_configs['sitepath'].'images/'.$gallery.'/'.$file);
                $out.='<div class="fmfoto2">
                    '.$tmp_img[0].' x '.$tmp_img[1].'<br>
                    <a><img src="'.$this->all_configs['siteprefix'].'images/'.$gallery.'/'.$file.'" alt="'.$file.'" ></a><br>
                        '.$file.'
                    <div style="text-align:right">
                        <span class="imgTitle" data-gallery="'.$gallery.'" data-file="'.$file.'">
                            <textarea placeholder="'.l('map_image_title_ru').'" title="'.l('map_image_title_ru').'" rows="2" cols="30" type="text" name="title_ru">'.$img_title['name'].'</textarea>
                            <textarea style="display:none" placeholder="'.l('map_image_title_en').'" title="'.l('map_image_title_en').'" rows="2" cols="30" type="text" name="title_en">'.$img_title['name_en'].'</textarea>
                            <br>
                            <span class="change_title change_title_active" data-lng="ru">RU</span> <span class="change_title" data-lng="en">EN</span>&nbsp;&nbsp;&nbsp;<input type="button" class="btn btn-mini btn-primary saveTitle" value="'.l('save').'"> 
                        </span>'
                        .  (strpos($file, '_m.') ? '<span class="crop" rel="'.$file.'">'.l('map_crop').'</span>' : '')
                        .'<span class="make_watermark" rel="'.$file.'">'.l('map_add_watermark').'</span> <span class="del_rename" rel="'.$file.'">'.l('delete').'</span>
                    </div>
                </div>';
            }
        }
        //                <span id="filtr_not_selected" class="pointer '.($filtr=='not_selected'?'bold':'').'">Не привязаные ('.$cnt.')</span> &nbsp;&nbsp; 
        $out_header = '
            <div style="position: absolute; top: 10px" class="btn-group" data-toggle="buttons-radio">
              <button id="filtr_thumb" type="button" class="btn-primary btn-small btn'.($filtr == 'small' ? ' active' : '').'">'.l('map_thumbnails').'</button>
              <button id="filtr_all" type="button" class="btn-primary btn-small btn'.(!$filtr ? ' active' : '').'">'.l('map_all').'</button>
            </div>
                    ';
        return $out_header.$out;
    }

    protected function crop(){
        
        $crop = isset($_GET['crop']) ? $_GET['crop'] : '';
        $file = isset($_GET['picture']) ? $_GET['picture'] : '';
        $gallery = isset($_GET['gallery']) ? str_replace('/', '', $_GET['gallery']) : '';
        $x = isset($_GET['x']) ? floor($_GET['x']) : '';
        $y = isset($_GET['y']) ? floor($_GET['y']) : '';
        $w = isset($_GET['width']) ? floor($_GET['width']) : '';
        $h = isset($_GET['height']) ? floor($_GET['height']) : '';
        $out = '';
        
        if($crop){
            $tmp_img = getimagesize($this->all_configs['sitepath'].'images/'.$gallery.'/'.$file);
            $file1 = str_replace('_m.', '.', $file);
            if(is_file($this->all_configs['sitepath'].'images/'.$gallery.'/'.$file1)){
                if ( copy($this->all_configs['sitepath'].'images/'.$gallery.'/'.$file1,
                        $this->all_configs['sitepath'].'images/'.$gallery.'/'.$file)
                    ) {
                        resample_photo($this->all_configs['sitepath'].'images/'.$gallery.'/'.$file,
                                $x, $y, 
                                $tmp_img[0], $tmp_img[1], 
                                $w, $h);
                    echo 'Миниатюра создана '.$file.'<br>';
                }else {
                    echo 'Ошибка ресемплинга файла '.$file.'<br>';
                }
            }else{
                echo 'Нет файла '.$file1.'<br>';
            }
        } else {
            $path_parts = pathinfo($file);
            if(isset($path_parts['extension']) && in_array($path_parts['extension'], self::$allowed_ext) && $file != 'nopicture.jpg'){
                if(strpos($file, '_m.') === false)
                    return false;

                $file1 = str_replace('_m.', '.', $file);
                $tmp_img = getimagesize($this->all_configs['sitepath'].'images/'.$gallery.'/'.$file);
                $out.='<div class="crop_foto">
                    '.$file.'<br>
                    mini '.$tmp_img[0].' x '.$tmp_img[1].'<br>
                    <img class="crop_image" src="'.$this->all_configs['siteprefix'].'images/'.$gallery.'/'.$file1.'" alt="'.$file1.'" ><br>
                    <div class="btn-group">'
                        .  (strpos($file, '_m.') ? '<button class="do_crop btn-primary btn-small btn" type="button" rel="'.$file.'">'.l('map_crop').'</button>' : '')
                    .'</div>
                </div>';
                
                $out .= '<script>
                            init_jcrop('.$tmp_img[0]/$tmp_img[1].', '.$tmp_img[0].', 0'.$tmp_img[1].');
                        </script>';
            }
            $out_header = '
            <div style="position: absolute; top: 10px" class="btn-group" data-toggle="buttons-radio">
              <button id="filtr_thumb" type="button" class="btn-primary btn-small btn'.((isset($filtr) && $filtr == 'small') ? ' active' : '').'">'.l('map_thumbnails').'</button>
            </div>
                    ';
            $out = $out_header.$out;
        } 
            
        return $out;
    }
    
    protected function new_gallery(){
        $file = isset($_GET['file']) ? $_GET['file'] : '';
        $dir = $this->translitIt($file);
        $pathf = $this->all_configs['sitepath'].'/images/'.$dir;

        if(mkdir($pathf)){
            $out = $dir;
            chmod($pathf, 0777);
        }else{
            $out = 'fail';
        }
        return $out;
    }
    
    protected function save_title(){
        $sql = $this->all_configs['db']->query("SELECT id FROM {image_titles} WHERE image = ? AND gallery = ?", array($_GET['picture'], $_GET['gallery']), 'el');
        if($sql){
            $upd = $this->all_configs['db']->query("UPDATE {image_titles} SET name = ?, name_en = ? WHERE `image` = ? AND `gallery` = ?", array($_GET['title_ru'], $_GET['title_en'], $_GET['picture'], $_GET['gallery']));
        }else{
            $ins = $this->all_configs['db']->query("INSERT INTO {image_titles}(name, name_en,image,gallery) VALUES(?,?,?,?)", array($_GET['title_ru'], $_GET['title_en'], $_GET['picture'], $_GET['gallery']));
        }

        exit;
    }
    
    private function ajax(){

        require_once 'class_image.php';

        ################################################################################
        $act = isset($_GET['act']) ? $_GET['act'] : '';
        

        $out = '';

        if(isset($_POST['social']) && class_exists('social')){
            $social = new social();
//            $social->ajax();
            exit;
        }
        
        ################################################################################
        
        if($act == 'choose_picture_for_page'){
            $out = $this->ajax_choose_picture_for_page();
        }
        
        ################################################################################
        
        if($act == 'crop'){
            $out = $this->crop();
        }
        
        ################################################################################

        if($act == 'savetitle'){
            $this->save_title();
        }
        
        ################################################################################
        
        if($act == 'upload_picture_for_page'){
            $out = $this->ajax_upload_picture_for_page();
        }

        ################################################################################

        if($act == 'new_gallery'){ //$file - имя галереи
            $out = $this->new_gallery();
        }

        ################################################################################

        if($act == 'make_watermark'){
            $this->add_watermark();
        }

        ################################################################################
        
        // menu
        // меняем парента
        if($act == 'save_page_parent'){
            $page_id = isset($_POST['page_id']) ? $_POST['page_id'] : 0;
            $new_parent = isset($_POST['new_parent']) ? $_POST['new_parent'] : 0;
            $this->all_configs['db']->query("UPDATE {map} SET parent = ?i WHERE id = ?i", array($new_parent, $page_id));
            if(isset($_POST['order'])){
                $order = '';
                foreach($_POST['order'] as $pageid => $prio){
                    $this->all_configs['db']->query("UPDATE {map} SET prio = ?i WHERE id = ?i", array($prio, $pageid));
                }
            }
        }
        // меняем статус страницы
        if($act == 'update_map_state'){
            $page_id = isset($_POST['page_id']) ? $_POST['page_id'] : 0;
            $state = isset($_POST['state']) ? $_POST['state'] : 0;
            $this->all_configs['db']->query("UPDATE {map} SET state = ?i WHERE id = ?i", array($state, $page_id));
        }
        
        //
        if($act == 'price_add_row' && isset($_POST['name']) && isset($_POST['map'])){
            //var_dump($_REQUEST);
            if (isset($_POST['pricecopymark'])){
                $table_type = 2;
            } else {
                $table_type = 1;
                $_POST['pricecopymark'] = 0;
                $_POST['pricecopy'] = 0;
            }
            
            $id = $this->all_configs['db']->query("INSERT INTO {map_prices} (map_id, table_type, 
                        price_copy,price,prio)
			VALUES (?i, ?i, ?, ?, ?i )", 
                    array($_POST['map'], $table_type, $_POST['pricecopy'],
                          $_POST['price'], $_POST['prio']
                    ), 'id');
            if($id){
                $this->all_configs['db']->query("INSERT INTO {map_prices_strings}(row_id, name, price_copy_mark, price_mark, time_required,hidden, lang) "
                . "VALUES(?i,?,?,?,?,0,?)", array($id, $_POST['name'],$_POST['pricecopymark'],$_POST['pricemark'],$_POST['timerequired'], $this->def_lang));
            }
            echo $id;
            exit;
        }
        if($act == 'price_edit_row'){
            //var_dump($_REQUEST);
            $col = $_POST['name'];
            $value = $_POST['value'];
            $row_id = $_POST['pk'];
            
            if (is_numeric($row_id) && $col){
                if(in_array($col, array('name','price_copy_mark','price_mark','time_required','hidden'))){
                    $data = array(
                        'update_col' => $col,
                        'update_value' => $value,
                        'row_id' => $row_id, 
                        'name' => '',
                        'price_copy_mark' => '',
                        'price_mark' => '',
                        'time_required' => '', 
                        'hidden' => 0, 
                        'lang' => $this->lang
                    );
                    $data[$col] = $value;
                    $this->all_configs['db']->query("INSERT INTO {map_prices_strings}"
                                                                . "(row_id, name, price_copy_mark, price_mark, "
                                                                . "time_required,hidden, lang) "
                                                    . "VALUES(?i:row_id,?:name,?:price_copy_mark,?:price_mark,"
                                                            . "?:time_required,?i:hidden,?:lang)"
                                                    . "ON DUPLICATE KEY UPDATE "
                                                        . "?q:update_col = ?:update_value", 
                                            $data);
                }elseif(in_array($col, array('price_copy','price','prio'))){
                    $this->all_configs['db']->query("UPDATE {map_prices} SET ?col = ? WHERE id = ?i LIMIT 1", array($col, $value, $row_id));
                }
                echo 'done';
            }
        }
        
        if($act == 'price_copy_table'){
            $table = isset($_POST['table']) ? (int)$_POST['table'] : 0;
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $current_id = isset($_POST['current_id']) ? (int)$_POST['current_id'] : 0;
            if($table && $id && $current_id){
                $copy_rows = $this->all_configs['db']->query("
                    SELECT id,price_copy,price,prio FROM {map_prices} WHERE map_id = ?i AND table_type = ?i
                ", array($id, $table), 'assoc');
                foreach($copy_rows as $row){
                    $new_row = $this->all_configs['db']->query("
                            INSERT INTO {map_prices}(map_id,table_type,price_copy,price,prio)
                            VALUES (?i, ?i, ?i, ?i, ?i)
                        ", array($current_id,$table,$row['price_copy'],$row['price'],$row['prio']), 'id');

                    $this->all_configs['db']->query("
                            INSERT INTO {map_prices_strings}(row_id,name,price_copy_mark,price_mark,time_required,hidden,lang)
                            SELECT ?i,name,price_copy_mark,price_mark,time_required,hidden,lang
                            FROM {map_prices_strings}
                            WHERE row_id = ?i
                        ", array($new_row,$row['id']));
                }
                $out['state'] = true;
            }else{
                $out['state'] = false;
                $out['msg'] = 'Введите id страницы источника';
            }
        }
        
        ################################################################################
        if(is_array($out)){
            header("Content-Type: application/json; charset=UTF-8");
            echo json_encode($out);
        }else{
            header("Content-Type: text/html; charset=UTF-8");
            echo $out;
        }
        exit;
    }

	protected function parse_prices_to_db( $contents ){
		$prices = array();
		$err = null;
		$c = 0;
		$inserts = 0;
		
		$lines = explode( PHP_EOL, $contents );
		
		if ( is_array ( $lines ) ) {
			$table_head = str_getcsv ($lines[0] , ';', '"');
			// check marker in header of price table - row 7 
			if ( !isset ( $table_head[7] ) ){
				$err = 'incorrect header of price table';
			}
		}
		else {
			$err = 'any line in price';
		}
		
		// check table type 1
		if ( !$err and $table_head[7] == 'time_required' ){
			
			$price_table_type = 1;

			// static sql query - change only parameters
			$sql = "UPDATE {map_prices} SET price = ?i , prio = ?i WHERE id = ?i";	
						
			foreach ( $lines as $line ) {
			// skip first line - header of table
				if (!$c){
					$c ++;
					continue;
				}
	
				// parse line of data	
				$price_line = str_getcsv( $line , ';', '"' );
				if ( is_array( $price_line ) and isset ( $price_line[2] ) ) {					
					$param = array (
						$price_line[6],
						$price_line[8],
						$price_line[0],
						);
					$insertion = $this->all_configs['db']->query( $sql, $param, 'ar' );	
					if ($insertion) {$inserts ++;} ;	// success counter				
				}
			$c ++ ;
			}
	
		}
		
		// check table type 2
		if ( !$err and $table_head[7] == 'price_mark' ){
			
			$price_table_type = 2;			
			// static sql query - change only parameters
			$sql = "UPDATE {map_prices} SET price_copy = ?i , price = ?i , prio = ?i WHERE id = ?i";			
			foreach ( $lines as $line ) {
			// skip first line - header of table
				if (!$c){
					$c ++;
					continue;
				}
	
				// parse line of data	
				$price_line = str_getcsv( $line , ';', '"' );
				if ( is_array( $price_line ) and isset ( $price_line[2] ) ) {					
					$param = array (
						$price_line[6],
						$price_line[9],
						$price_line[11],
						$price_line[0],
						);					
					$insertion = $this->all_configs['db']->query( $sql, $param, 'ar' );	
					if ($insertion) {$inserts ++;} ;	// success counter				
				}
			$c ++ ;
			}
		}
		
		return $inserts;
	}
}
