<?php

/**
 * Жесть!
 */

/**
 * выбрать перевод сразу для нескольких страниц
 */
function get_few_translates($table, $key, $sql_where = ''){
    global $db;
    if(strpos($table, 'core_') !== 0){
        $table = $db->makeQuery('{'.$table.'_strings}', array());
    }else{
        $table = $table.'_strings';
    }
    $translates_all = $db->query("SELECT * 
                                  FROM ?q ?q", array($table, $sql_where ? 'WHERE '.$sql_where : ''), 'assoc');
    $translates = array();
    foreach($translates_all as $trans){
        if(!isset($translates[$trans[$key]])){
            $translates[$trans[$key]] = array();
        }
        $t = $trans;
        unset($t['id']);
        $translates[$trans[$key]][$trans['lang']] = $t;
    }
    return $translates;
}

/**
 * 
 * Получаем масив языков из браузера
 * 
 * @return array 
 */
function get_browser_langs(){
    $langs=array();
    if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        foreach(explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']) as $value) {
            if(strpos($value, ';') !== false) {
                list($value, ) = explode(';', $value);
            }
            if(strpos($value, '-') !== false) {
                list($value, ) = explode('-', $value);
            }
            $langs[] = $value;
        }
    }
    return $langs;
}

/**
 * определяем язык пользователя
 * с браузера или куки
 */
function get_user_lang(){
    global $lang_arr, $tbl_prefix, $settings, $def_lang;
    
    $ip = $_SERVER['REMOTE_ADDR'];
    $record = @geoip_record_by_name($ip);
    $country_lang = '';
    if(isset($record['country_code']) && in_array($record['country_code'], array('UA'))){
        $country_lang = 'uk';
    }
    
    $lang = $def_lang;
    $lang_cookie = isset($_COOKIE[$tbl_prefix.'lang']) ? $_COOKIE[$tbl_prefix.'lang'] : '';

    if (in_array($lang_cookie, $lang_arr)){
        $lang = $lang_cookie;
    } else {
        if($country_lang){
            $lang = $country_lang;
        }else{
            $lang_browser_array = get_browser_langs();
            if (isset($lang_browser_array[0])){
                // если язык браузера НЕ украинский и не русский(?)
                // то врубаем английский
                if(!in_array($lang_browser_array[0], array('uk','ru'))){
                    $lang_browser_array[0] = 'en'; 
                }
            }
            if(isset($lang_browser_array[0]) && in_array($lang_browser_array[0], $lang_arr)){
                $lang = $lang_browser_array[0];
            }
        } 
    }
    return $lang;
}

/**
 * 
 *  почистить значения в массиве
 *  используется как callback в array_map()
 */
function clear_values_in_array($value){
    return '';
}

/**
 * 
 *  смержить заселекченые переводи с нужным массивом
 *  (используется также в админке)
 * 
 *  $lang - текущий язык
 *  $def_lang - дефолтный язык
 *  $data - массив с переводами
 *  $original_array - массив с переводами будем мержить с тим массивом
 *  $select_default_if_null - если нету перевода для какого-то поля и этот параметр
 *                            true, то вернуть дефолтный перевод
 */
function translates_for_page($lang, $def_lang, $data, $original_array, $select_default_if_null = false){
    
    // если нету перевода для дефолтного языка и для выбраного, 
    // то сделать дефолтным любой из существующих
    if(!isset($data[$def_lang]) && !isset($data[$lang])){
        foreach($data as $key => $value){
            if($data[$key]){
                $data[$def_lang] = $data[$key];
                $data[$def_lang]['lang'] = $def_lang;
                break;
            }
        }
    }
    
    // если нету перевода дефолтного, но есть для выбраного языка, то сделать 
    // его дефолтным
    if(!isset($data[$def_lang]) && isset($data[$lang])){ // 
        $data[$def_lang] = array_map('clear_values_in_array', $data[$lang]);
    }
    
    // если нету перевода выбраного языка, но есть для дефолтного, то сделать 
    // его выбраного
    if(!isset($data[$lang]) && isset($data[$def_lang])){ // 
        $data[$lang] = array_map('clear_values_in_array', $data[$def_lang]);
    }
    
    // если нету перевода для выбраного языка но есть для дефолтного и передан
    // параметр $select_default_if_null == true, то выдать дефолтный перевод для
    // полей в которых нет перевода
    if($select_default_if_null){
        $lnges = array();
//        print_r($data);
        if(isset($data[$def_lang])){
            foreach($data[$def_lang] as $key => $value){
                $lnges[$key] = $value;
                if(isset($data[$lang][$key]) && $data[$lang][$key]){
                    $lnges[$key] = $data[$lang][$key];
                }
            }
        }
        $data = $lnges;
    }else{
        $data = array_merge($data[$def_lang], $data[$lang]);
    }
    if(isset($data['id'])){
        unset($data['id']);
    }
    // добавить перевод в общий массив
    return array_merge($original_array, $data);
}


/**
 * Генерим массив переводов с задаными парамертами
 * 
 * @global type $db
 * @global type $lang
 * @global type $def_lang
 * @param type $table
 * @param type $key
 * @param type $array_key
 * @param type $data_field
 * @param type $q
 * @return type
 */
function get_translates($table, $key, $array_key = 'id', $data_field = '', $q = '', $return_all_translates = false){
    global $lang, $def_lang, $db;
    if(strpos($table, 'core_') !== 0){
        $tableq = $db->makeQuery('{'.$table.'}', array());
    }
    $vars = $db->query("SELECT * FROM ?q ?q", array($tableq, $q), 'assoc');
    $translates_var = get_few_translates($table, $key);
    $all_translates = array();
    $translates = array();
    foreach($vars as $var){
        $tvar = translates_for_page($lang, $def_lang, $translates_var[$var['id']], $var, true);
        $translates[$tvar[$array_key]] = $data_field ? $tvar[$data_field] : $tvar;
        $all_translates[$tvar[$array_key]] = $translates_var[$var['id']];
    }
    if($return_all_translates){
        return array($translates, $all_translates);
    }else{
        return $translates;
    }
}



function set_lang_cookie($lang){
    global $tbl_prefix, $settings, $prefix;
    // удаляем старую куку
    setcookie($tbl_prefix.'lang', null, -1, $prefix);
    // ставим
    setcookie($tbl_prefix.'lang', $lang, time()+3600*24*30, $prefix);
}

// $photo = images/blog/987chicago_beach-wallpaper-1920x1080_m.jpg
function get_photo_by_lang($photo){
    global $lang, $path;
    $suff = array('_m.', '_m2.', '_s.');
    $path_info = pathinfo($photo);
    if(isset($path_info['extension'])){
        $dir_path = $path.$path_info['dirname'].'/';
        $has_suf = '';
        foreach($suff as $suf){
            if(strpos($photo, $suf) !== false){
                $has_suf = $suf;
            }
        }
        if($has_suf){
            $filename = str_replace($has_suf, '', $path_info['filename'].'.');
        }else{
            $has_suf = '.';
            $filename = $path_info['filename'];
        }
        // search image for current lang
        $lang_file = $filename.'_'.$lang.$has_suf.$path_info['extension'];
        $lang_filedirpath = $dir_path.$lang_file;
        if(file_exists($lang_filedirpath)){
            return $path_info['dirname'].'/'.$lang_file;
        }else{
            return $photo;
        }
    }else{
        return $photo;
    }
}

function is_crawler(){
    $crawlers = array(
        'Google' => 'Google',
        'yandex' => 'YandexBot',
        'MSN' => 'msnbot',
        'Rambler' => 'Rambler',
        'Yahoo' => 'Yahoo',
        'AbachoBOT' => 'AbachoBOT',
        'accoona' => 'Accoona',
        'AcoiRobot' => 'AcoiRobot',
        'ASPSeek' => 'ASPSeek',
        'CrocCrawler' => 'CrocCrawler',
        'Dumbot' => 'Dumbot',
        'FAST-WebCrawler' => 'FAST-WebCrawler',
        'GeonaBot' => 'GeonaBot',
        'Gigabot' => 'Gigabot',
        'Lycos spider' => 'Lycos',
        'MSRBOT' => 'MSRBOT',
        'Altavista robot' => 'Scooter',
        'AltaVista robot' => 'Altavista',
        'ID-Search Bot' => 'IDBot',
        'eStyle Bot' => 'eStyle',
        'Scrubby robot' => 'Scrubby',
        'Facebook' => 'facebookexternalhit',
    );
    $crawlers_agents = implode('|', $crawlers);
    if(isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/'.$crawlers_agents.'/i', $_SERVER['HTTP_USER_AGENT'])){
        return true;
    }else{
        return false;
    }
}

function gen_city_select($triangle = false){
    global $langs_arr, $user_city;
    $current = $user_city;
    if($user_city == 'kiev'){
        $current = 'default_kiev';
    }
    $list = '';
    foreach($langs_arr as $city => $lng){
        $link = 'https://restore.com.ua';
        if($city != 'default_kiev'){
            $link = 'https://restore.com.ua/'.$city;
        }
        $uri = $_SERVER['REQUEST_URI'];
        if(strpos($uri, '/'.$user_city) === 0){
            $uri = str_replace(array('/'.$user_city.'/', '/'.$user_city), '/', $uri);
        }
        $active = $city == $current;
        $list .= '<li'.($active ? ' class="active"' : '').' data-city="'.$link.$uri.'">'.$lng['name'].'</li>';
    }
    $select = ' 
        <div class="city_select">
            <div class="current_city">'.
                '<span class="city_name">'.$langs_arr[$current]['name'].'</span>'.
                ($triangle ? '<span class="triangle">▼</span>' : '').
            '</div>
            <div class="cities_dropdown">
                <div class="cities_dropdown_inner">
                    <div class="arrow"></div>
                    <ul>
                        '.$list.'
                    </ul>
                </div>
            </div>
        </div>
    ';
    return $select;
}