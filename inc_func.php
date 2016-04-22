<?php


/**
* Fonbrand company.
* Fon CMS (goDB ready)
*
* @category   CMS
* @package    Functions
* @copyright  Copyright 2010-2011 Fon (http://fonbrand.com)
* @license    Fon
* @version    2011.05.31
* @author     Anatoliy Khutornoy <ragenoir@gmail.com>
*
*/



/**
 * Парсит УРЛ по частям, отделенных слешами
 *
 * @return array
 */
function parse_slashed_url() {
    GLOBAL $prefix;
    if ($prefix!='/'){
        $request=str_replace($prefix,'',$_SERVER['REQUEST_URI']);
    } else {
        $request=$_SERVER['REQUEST_URI'];
    }
    $arrequest=clear_empty_inarray(explode('/', $request));
    return $arrequest;
}

/**
 * Чистит пустышки в масиве. А также пропускает только [^0-9a-z-A-Z-_],
 * проверка перенесена из core_sitemap.php
 * Используется только в parse_slashed_url()
 *
 * @param array $array
 * @return array
 */
function clear_empty_inarray($array) {
    $ret_arr = array();
    foreach($array as $val) {
        $val = preg_replace('/[^0-9a-z-A-Z-_?]/', '', urldecode($val)); //trim тут не нужен?
        if (empty($val)) continue;
        if (strpos($val, '?')!==false) {
            $ret_arr[] = strstr($val, '?', true);
        } else {
            $ret_arr[]=$val;
        }
    }
    return $ret_arr;
}

function db(){
    global $all_configs;
    return $all_configs['db'];
}

/**
 *
 * Устанавливает файл, если он отсутствует, устанавливается по-умолчанию
 * По умолчанию default уже надо убирать
 *
 * @param string $prefix
 * @param string $root
 * @return string
 */
function set_default_file($prefix, $root){

    $file=$prefix.$root.'.html';
    if (file_exists($file)){
        return $file;
    } else {
        return $prefix.'default.html';
    }

}

/**
 *
 * Получаем масив языков из браузера
 *
 * @return array
 */
function get_lang(){
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
 * Ескейпит строку для запроса в mysql.
 *
 * @deprecated в goDB не нужен
 * @param string $value
 * @return string
 */
function quote_smart($value) {
    // если magic_quotes_gpc включена - используем stripslashes, а она должна быть! (см. конфиг)
    if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
    }
    // Если переменная - число, то экранировать её не нужно
    // если нет - то окружем её кавычками, и экранируем
    if (!is_numeric($value)) {
            $value = "'" . mysql_real_escape_string($value) . "'";
    }
    return $value;
}

/**
 *
 * @param string $type
 * @param array $modules
 * @return bool
 */
function execute_modules($type, $modules){
    GLOBAL $input_html, $input_js, $input_css, $input, $path;
    if (count($modules)>0){
        foreach ($modules AS $el){
            $path_to_module = 'modules/'.$type.'_'.$el['mod'].'/index.php';
            if (file_exists($path_to_module)) {
                require_once $path_to_module;

//                Вызвать функцию подключения шаблона и темы к модулю
                if($el['template'])
                  add_module_template($el);
                if($el['theme'])
                  add_module_theme($el);

            } else {
                die('Модуль '.$path_to_module.' не найден!');
            }
        }
    } else {
        require_once 'module_'.$type.'_default.php';
    }
    return true;
}

/**
 * Подключение шаблона модуля
 *
 * @param type $el
 */
function add_module_template($el){
    $path_to_template = 'modules/'.$type.'_'.$el['mod'].'/template/'. $el['template'] .'.html';
    if (file_exists($path_to_template)){
//        $path_to_template;

        /*
         * загружаем шаблон,
         * заменяем все выражения переменными из модуля
         * записываем html в $mod['content']
         * 
         * либо это происходит в самом модуле.
         */
        return true;
    }
}

/**
 * Подключение темы модуля
 *
 * @param type $el
 */
function add_module_theme($el){
    $path_to_theme = 'modules/'.$type.'_'.$el['mod'].'/theme/'. $el['theme'];
    if (file_exists($path_to_theme .'/main.css')){
    $input['css_extra'] .= '<link type="text/css" rel="stylesheet" href="{-txt-prefix}'
        . $path_to_theme .'/main.css">';
    }
    if (file_exists($path_to_theme .'/main.js')){
    $input_js['extra_files'] .= '<script type="text/javascript" src="{-txt-prefix}'
        . $path_to_theme .'/main.js"></script>';
    }
}


function removeslashes($value){
    if (get_magic_quotes_gpc()) {
            $value = stripslashes($value);
    }
    return $value;
}


#############
function send_mail($to, $sbj, $msgtxt){
    GLOBAL $settings;

    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $xip = ISSET($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';

    $subject="=?UTF-8?B?".base64_encode($sbj)."?=\n";

    $message = $msgtxt."<br><br>\r\n";
    $message.='<hr>IP: '.$ip.' xIP: '.$xip;
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8 \r\n";
    $headers .= "X-originating-IP: " . $ip ."\r\n";
    $headers .= 'From: '.$settings['site_name'].' <'.$settings['content_email'].'>' . "\r\n" ;
    //
//    $headers .= 'BC: '.$settings['admin_email'] . "\r\n";
    //$headers .= 'Bc: ragenoir@gmail.com' . "\r\n";

    if (mail($to, $subject, $message, $headers)) {
        return true;
    } else {
        return false;
    }
}

/**
 * Правильный постоянный редирект на УРЛ.
 *
 * @param string $url2redirect
 * @return unknown
 */
function redirect301($url2redirect, $permanently = true){

    if($permanently){
        header ('HTTP/1.1 301 Moved Permanently');
    }
    header ('Location: '.$url2redirect);
    echo '
        <!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
        <html><head>
        <title>301 Moved Permanently</title>
        </head><body>
        <h1>Moved Permanently</h1>
        <p>The document has moved <a href="'.$url2redirect.'">here</a>.</p>
        <hr>
        <address>Server at '.$_SERVER["HTTP_HOST"].'</address>
        </body></html>
    ';
    exit;
}


function check_phone($phone){
    return $phone;
    return preg_match('/^380 \(\d{2}\) \d{3}\-\d{2}\-\d{2}$/', $phone);
}

// чистим номер телефона
function clear_phone($phone){
    return str_replace(array(' ','(',')','-'),'',trim($phone));
}



function gen_level($page){
    global $link, $db;
    $row = $db->query("SELECT id, url, parent FROM {map} WHERE id = ?i", array($page), 'row');
    $link[] = $row['url'];
    if($row['parent']){
        gen_level($row['parent']);
    }
}

function gen_full_link($page_id){
    global $db, $link;

    $link = array();

    gen_level($page_id);

    krsort($link);

    return implode("/", $link);
}

/**
 * замена переменных в шаблонах внутри модулей
 *
 * @global type $txt
 * @param type $matches
 * @return string
 */
function replace_in_mod($matches){
    global $txt;
    if ($matches[1]=='mod'){ // && !isset($input[$matches[2]])
        if (isset ($txt[$matches[2]])){
            return $txt[$matches[2]];
        } else {
            return '';
        }
    }
}

/**
 * вызывает замену переменных в файле (для модулей)
 *
 * @param type $file - файл шаблона для замены
 * @return type
 */
function mod_magic($file){
    $content = file_get_contents($file);
    $pattern = "/\{\-(mod)\-([a-zA-Z0-9_]{1,20})\}/";
    $replaced = preg_replace_callback($pattern, "replace_in_mod", $content);
    return $replaced;
}

/*
 * Выбор катринок у которых есть миниатюра типа объект ' _m2 '
 */

function get_big_pics($page_gallery, $num = 0){
    global $path, $prefix, $db;

    $pics = '';

    $dir = $path . 'images/' . trim($page_gallery);

    if (trim($page_gallery) && is_dir($dir)) {
        $scandir = scandir($dir);
        $i = 0;
        if (is_array($scandir)) {
            foreach ($scandir as $file) {

                if ($file != '.' && $file != '..' && strpos($file, '_om2.')) {
                    $big_image = str_replace('_om2.', '.', $file);
                    $pics .= '<div class="drop-shadow" style="background-image: url(\''.$prefix.get_photo_by_lang('images/'.$page_gallery.'/'.$big_image).'\')"></div>';
                    if (++$i == $num)
                        return $pics;
    //                $pics .= '<div class="image_delimiter"></div>';
                }
            }
        }
        if (isset($big_image)) {
            while ($i++ != $num) {
                $pics .= '<div class="drop-shadow" style="background-image: url(\''.$prefix.get_photo_by_lang('images/'.$page_gallery.'/'.$big_image).'\')"></div>';
            }
        }
    }
    return $pics;
}



/**
 * Генерим список статей / книг / альбомов
 *
 * @global type $db
 * @global type $prefix
 * @global type $settings
 * @global type $lang
 * @global type $def_lang
 * @global type $tpl_translates
 * @param type $sql_where
 * @param type $sql_data
 * @param type $show_category
 * @return string
 */
function gen_articles($sql_where, $sql_data, $show_category = true, $type = 'articles'){
    global $db, $prefix, $url_lang, $settings, $lang, $def_lang, $template_vars;

    $articles = '';

    $sql_what = "id, uxt, gallery, picture, url, parent";
    $sql_where = $db->makeQuery($sql_where, $sql_data);

    $last_articles = $db->query("SELECT ?q
                                 FROM {map} as m
                                 WHERE ?q 
                                 ORDER BY id DESC", array($sql_what, $sql_where), 'assoc:id');

    if($last_articles){
        $translates = get_few_translates(
            'map',
            'map_id',
            $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($last_articles))))
        );
        foreach($last_articles as $article){
            $article = translates_for_page($lang, $def_lang, $translates[$article['id']], $article, true);
            $img = '';
            if($article['picture']){
                $pic_prop = '';
                $pic = $article['picture'];
                if($type == 'books'){
                    $pic_prop = ' width="150"';
                }
                if($type == 'albums'){
//                    $pic = str_replace('_m.', '_m2.', $article['picture']);
                }
                $image_src = $prefix.get_photo_by_lang('images/'.$article['gallery'].'/'.$pic);
                $img = '<img'.$pic_prop.' src="http://'.$_SERVER['HTTP_HOST'].$image_src.'" alt="'.$article['name'].'"> ';
            }

            $article_link = $prefix.$url_lang.gen_full_link($article['parent']).'/'.$article['url'];

            $content = explode('<!-- pagebreak -->', $article['content']);

            $picture = '';
            if(strpos($article['content'], '{-page_video-}') !== false){
                $video = $db->query("SELECT * FROM {video2page} WHERE map_id = ?i", array($article['id']), 'row');
                if($video['link'] && !$img){
                    $img = '<img src="//img.youtube.com/vi/'.$video['link'].'/0.jpg" alt="'.$article['name'].'"_youtube>';
                }
            }

            if($img){
                $picture = '   
                    <p>
                        <a href="'.$article_link.'" style="border: none;">
                            '.$img.'
                        </a>
                    </p>
                    ';
            }

            $category = $db->query("SELECT id, parent, url FROM {map} WHERE id = ?i", array($article['parent']), 'row');

            if($type == 'articles'){

//                $tags = get_page_tags($article['id']);
                $tags_list = '';
                if(isset($tags) && $tags){
                    $tags_list = '<li class="tags">'.implode(', ', $tags).'</li>';
                }

                $cat = '';
                if($show_category){
                    $caterogy_parent_link = gen_full_link($category['parent']);
                    $cat = '
                        <a href="'.$prefix.$url_lang.(!$caterogy_parent_link ? '' : $caterogy_parent_link.'/' ).$category['url'].'" rel="bookmark">
                            <span class="catch_headline">'.$category['name'].'</span>
                        </a>
                    ';
                }

                $articles .= '
                            <article class="type-post post">
                                <h2>
                                    '.$cat.'
                                        <span class="headline">'.$article['name'].'</span>
                                </h2>

                                <ul class="postmetadata clearfix">
                                    <li class="date">'//.format_date(strtotime($article['uxt']))
                                    .'</li>
                                    '.$tags_list.'
                                </ul>

                                '.$content[0].'

                                '.$picture.'
                                <p>
                                    <a href="'.$article_link.'" class="buttn">'.$tpl_translates['read_more'].'...</a>
                                </p>
                            </article>
                        ';
            }elseif($type == 'block'){ // вывод блоков

                    $articles .= '
                            <article class="type-post post">
                                <h2>
                                        <span class="headline">'.$article['fullname'].'</span>
                                </h2>
                                '.($img ? $img : '') .'
                                '.$content[0].'
                                
                                    <a href="'.$article_link.'" class="buttn">'.$template_vars['l_article_detail_btn'].'</a>
                                
                            </article>
                        ';

            }elseif($type == 'albums'){ // вывод списка альбомов
                $articles .= '
                            <div class="album_outer">
                                <a href="'.$article_link.'" class="album">
                                    <span class="album_picture">
                                        '.$img.'
                                    </span>
                                    <span class="album_title">
                                        '.$article['name'].'
                                    </span>
                                </a>
                            </div>
                            ';
            }
        }
        return $articles;
    }else{
        return '';
    }
}


function gallery($page_gallery, $suf = '_gm', $class = 'gallery_preview'){
    global $path, $prefix, $db;

    $pics = '';

    if(trim($page_gallery)){
        $pics = '';
        $scandir = scandir($path.'images/'.$page_gallery);
        $image_titles = $db->query("SELECT image, name FROM {image_titles} WHERE gallery = ?", array($page_gallery), 'assoc:image');
        foreach($scandir as $file){
            if($file != '.' && $file != '..' && strpos($file, $suf.'.') !== false){
                $big_image = str_replace($suf.'.', '.', $file);
                $big = str_replace($suf.'.', '.', $prefix.'images/'.$page_gallery.'/'.$file);
//                $spath = $path.'images/'.$page_gallery.'/'.$big_image;
//                $image_props = getimagesize($spath);
                $ititle = isset($image_titles[$file]) ? $image_titles[$file]['name'] : '';
                if(!$ititle){
                    $ititle = isset($image_titles[$big_image]) ? $image_titles[$big_image]['name'] : '';
                }
                if(!$ititle){
                    $m2 = str_replace($suf.'.', '_gm2.', $file);
                    $ititle = isset($image_titles[$m2]) ? $image_titles[$m2]['name'] : '';
                }
                if(!$ititle){
                    $m = str_replace($suf.'.', '_gm.', $file);
                    $ititle = isset($image_titles[$m]) ? $image_titles[$m]['name'] : '';
                }
                $props = '';
//                $props = ' data-gallery="'.$page_gallery.'" data-width="'.$image_props[0].'" data-height="'.$image_props[1].'" data-big="'.$big.'" data-image="'.$big_image.'"';
                $pics .= '<a data-fancybox-group="gallery" href="'.$big.'" title="'.$ititle.'"'.$props.' class="fancy '.$class.'"><span style="background-image: url(\''.$prefix.'images/'.$page_gallery.'/'.$file.'\')"></span></a>';
            }
        }
    }
    return $pics;
}

//    функция для корректного substr() мультибайтовых символов
function bite_str($string, $start, $len, $byte=2)
{
    $str     = "";
    $count   = 0;
    $str_len = strlen($string);
    for ($i=0; $i<$str_len; $i++) {
        if (($count+1-$start)>$len) {
        $str  .= "...";
        break;
    } elseif ((ord(substr($string,$i,1)) <= 128) && ($count < $start)) {
        $count++;
    } elseif ((ord(substr($string,$i,1)) > 128) && ($count < $start)) {
        $count = $count+2;
        $i     = $i+$byte-1;
    } elseif ((ord(substr($string,$i,1)) <= 128) && ($count >= $start)) {
        $str  .= substr($string,$i,1);
        $count++;
    } elseif ((ord(substr($string,$i,1)) > 128) && ($count >= $start)) {
        $str  .= substr($string,$i,$byte);
        $count = $count+2;
        $i     = $i+$byte-1;
    }
    }
    return $str;
}

function gen_content_array($content){
    global $input_html;
    $text = explode('<!-- pagebreak -->', $content);
     $share_horizontal = '';
    if(!isset($text[2]) && !isset($text[3]))
        $share_horizontal = ' share_horizontal';
    $input_html['footer_text'] = '<div class="footer1'.$share_horizontal.'">
                                <div class="footer_text container">
                                    <!--Social links-->
                                    <div id="share">
                                        <div>
                                        </div> 
                                    </div>
                                    <div class="row">
                                        <div class="col-sm-6"><p>'
                                            .(isset($text[2])?$text[2]:'')
                                        .'</div>
                                        <div class="col-sm-6"><p>'
                                            .(isset($text[3])?$text[3]:'')
                                        .'</div>
                                    </div>
                                    {-txt-footer_form}
                                </div>
    <script>
    $(document).ready(function(){
        function setShareIcons () {
            new Ya.share({
                element: "share",
                l10n: "ru",
                elementStyle: {
                    type: "none",
                    quickServices:  ["gplus", "facebook", "vkontakte"],
                },
            });
        }
        // wait until page loaded and pjax converted
        timeout_id = setTimeout( setShareIcons , 1000);        
    });
    </script>
                              </div>';
    return $text;
}

function gen_advantages_block($pos='vertical'){
    global $db, $lang, $def_lang;
    $configs = Configs::getInstance()->get();
    $id = $configs['advantages-page'];//adv id in sitemap
    $out = '';
    $translates = $db->query("SELECT content, lang 
                              FROM {map_strings} as s
                              LEFT JOIN {map} as m ON s.map_id = m.id
                              WHERE m.state = 1 AND s.map_id = ?i", array($id), 'assoc:lang');
    $content = translates_for_page($lang, $def_lang, $translates, array(), true);
    if($content) {
        $content = $content['content'];
        if ($pos != 'vertical')
            $pos = 'horizontal';
//        $content = str_replace(array('<p>','</p>'), array('<div><span>','</span></div>'), $content);
        $out = '<div class="advantages '.$pos.'">'
            .$content
            .'</div>';
    }
    return $out;
}


function gen_link($url, $inner_html, $hidden_link, $attr = array()){
    $attrs_string = '';
    if($hidden_link){
        $attr['rel'] = 'nofollow';
        $attr['class'] = (isset($attr['class']) ? $attr['class'].' ' : '').'anchor';
    }
    if($attr){
        foreach($attr as $at => $vl){
            $attrs_string .= ' '.$at.'="'.$vl.'"';
        }
    }
    if($hidden_link){
        $link = '<span'.$attrs_string.' data-url="'.$url.'">'.$inner_html.'</span>';
    }else{
        $link = '<a'.$attrs_string.' href="'.$url.'">'.$inner_html.'</a>';
    }
    return $link;
}

/**
 * get real ip
 * */
function get_ip()
{
    $ip = '';
    if (isset($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

// отправка сообщений через turbosms
function send_sms($phone, $message)
{
    global $path, $settings;

    include_once $path . 'shop/turbosms.class.php';

    $from = isset($settings['turbosms-from']) ? trim($settings['turbosms-from']) : '';
    $login = isset($settings['turbosms-login']) ? trim($settings['turbosms-login']) : '';
    $password = isset($settings['turbosms-password']) ? trim($settings['turbosms-password']) : '';

    $turbosms = new turbosms($login, $password);
    $result = array_values((array)$turbosms->send($from, '+' . $phone, $message));

    $result = is_array($result) && isset($result[0]) ? $result[0] : '';

    return array(
        'state' => is_array($result) ? true : false,
        'msg' => is_array($result) ? current($result) : $result
    );
}

function get_request_time(){
    global $template_vars;
    $consult_msg_array = array(
        array ('0', '5', $template_vars['l_request_time_0_5_title'], $template_vars['l_request_time_0_5_text']),
        array ('5', '10', $template_vars['l_request_time_5_10_title'], $template_vars['l_request_time_5_10_text']),
        array ('10', '18', $template_vars['l_request_time_10_18_title'], $template_vars['l_request_time_10_18_text']),
        array ('18', '20', $template_vars['l_request_time_18_20_title'], $template_vars['l_request_time_18_20_text']),
        array ('20', '23', $template_vars['l_request_time_20_23_title'], $template_vars['l_request_time_20_23_text']),
        array ('23', '24', $template_vars['l_request_time_23_24_title'], $template_vars['l_request_time_23_24_text']),

    );

    // выходные
    if (date('N', time()) > 5)
        $consult_msg_array = array(
            array ('0', '5', $template_vars['l_weekend_request_time_0_5_title'], $template_vars['l_weekend_request_time_0_5_text']),
            array ('5', '11', $template_vars['l_weekend_request_time_5_11_title'], $template_vars['l_weekend_request_time_5_11_text']),
            array ('11', '18', $template_vars['l_weekend_request_time_11_18_title'], $template_vars['l_weekend_request_time_11_18_text']),
            array ('18', '23', $template_vars['l_weekend_request_time_18_23_title'], $template_vars['l_weekend_request_time_18_23_text']),
            array ('23', '24', $template_vars['l_weekend_request_time_23_24_title'], $template_vars['l_weekend_request_time_23_24_text']),

        );
    $msg1 = '';
    $msg2 = '';
    // check time
    $hour = date('H', time());
    foreach ($consult_msg_array as $arr) {
        if($hour>=$arr[0] && $hour<$arr[1]) {
            $msg1 = $arr[2];
            $msg2 = $arr[3];
            break;
        }
    }
    return array($msg1, $msg2);
}

function mb_ucfirst($word){
    return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr(mb_convert_case($word, MB_CASE_LOWER, 'UTF-8'), 1, mb_strlen($word), 'UTF-8');
}

function curl_get($url, $use_ssl = false, $data = array(), $method = 'GET'){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    if($method == 'POST'){
        curl_setopt($ch, CURLOPT_POST, true);
    }
    if($use_ssl){
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
    }
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    if($data){
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/x-www-form-urlencoded"
        ));
    }
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $return_data = curl_exec($ch);
    curl_close($ch);
    return $return_data;
}

function cannonical_page_for_pagination($get_apram = null){
    global $input_html, $prefix, $arrequest;
    $get_arr = $_GET;
    if(!is_null($get_apram)){
        unset($get_arr[$get_apram]);
    }
    $get = http_build_query($get_arr);
    $input_html['cannonical_page'] =
        '<meta name="robots" content="noindex, follow" />'.
        '<link rel="canonical" href="http://'.$_SERVER['HTTP_HOST'].$prefix.implode('/', $arrequest).($get ? '?'.$get : '').'"/>';
}

function gen_breadcrumbs(){
    global $arrequest, $url_all_levels, $template_vars, $prefix, $url_lang;
    if($arrequest && $url_all_levels){
        $bc_url = '';
        $bc_levels = $url_all_levels;
        array_unshift($bc_levels, array('url' => '', 'name' => $template_vars['l_bread_crumbs_homepage_name']));
        $crumbs = array();
        $levels_cnt = count($bc_levels);
        $i = 0;
        foreach($bc_levels as $level){
            $i ++;
            $last = $i == $levels_cnt;
            $bc_url .= ($bc_url ? '/' : '').$level['url'];
            $crumbs[] = '
                <div'.(!$last ? ' itemscope itemtype="http://data-vocabulary.org/Breadcrumb"' : '').'>
                    '.(!$last ? '<a itemprop="url" href="'.$prefix.$url_lang.$bc_url.'">' : '').'
                        <span itemprop="title">'.$level['name'].'</span>
                    '.(!$last ? '</a>' : '').'
                </div>
            ';
        }
        return '
            <div class="bread_crumbs" id="bread_crumbs">
                <div class="pull-right">
                    '.implode('<div class="bc_separator">/</div>', $crumbs).'
                </div>
            </div>
        ';
    }
    return '';
}

function seohide_html($html){
    return '<span class="hashed_data" data-content="'.base64_encode($html).'"></span>';
}

function generate_xls_with_login_logs()
{
    require_once(__DIR__ . '/manage/classes/PHPExcel.php');
    require_once(__DIR__ . '/manage/classes/PHPExcel/Writer/Excel5.php');
    $users = db()->query('SELECT id, login, email, fio FROM {users} WHERE avail=1 AND deleted=0')->assoc();
    foreach ($users as $id => $user) {
        $users[$id]['logs'] = db()->query('SELECT DATE_FORMAT(created_at,\'%d-%m-%Y\') as date_add, MIN(created_at) as stamp FROM {users_login_log} WHERE user_id=?i AND created_at > ? GROUP by date_add ORDER by date_add ASC',
            array($user['id'], date('1-1-Y', time())))->assoc();
    }
    $xls = new PHPExcel();
    $currentYear = date('Y');
    foreach (range(1, 12) as $item) {
        $xls->createSheet($item);
        $xls->setActiveSheetIndex($item);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle("{$item}-{$currentYear}");
        $sheet->setCellValue("A1", 'Пользователь');
        $sheet->getStyle('A1')->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('A1')->getFill()
            ->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        foreach (range(1, 31) as $day) {
            $cell = $sheet->setCellValueByColumnAndRow($day, 1, $day, true);
            $sheet->getStyle($cell->getCoordinate())->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $sheet->getStyle($cell->getCoordinate())->getFill()
                ->getStartColor()->setRGB('EEEEEE');
            $sheet->getStyle($cell->getCoordinate())->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell->getCoordinate())->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
    }
    $xls->removeSheetByIndex(0);
    foreach ($users as $id => $user) {
        if (!empty($user['logs'])) {
            foreach ($user['logs'] as $log) {
                list($day, $month, $year) = explode('-', $log['date_add']);
                $xls->setActiveSheetIndex((int)$month - 1);
                $sheet = $xls->getActiveSheet();
                $sheet->setCellValueByColumnAndRow(
                    0,
                    (int) $id + 2,
                    $user['login']);
                $sheet->setCellValueByColumnAndRow(
                    (int)$day,
                    (int) $id + 2,
                    date('H:m', strtotime($log['stamp'])));
            }
        }
    }

    return new PHPExcel_Writer_Excel5($xls);
}
