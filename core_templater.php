<?php

/**
 * Шаблонизатор
 * 
 * goDB ready
 */

/**
 * Страница состоит из многих шаблонов
 * Заголовочный
 * Главный
 * Второстепенный
 * ?Подвал
 *
 * Основные преобразования происходят в главном и второстепенном.
 */



/**
 * Замена патернов в шаблоне на переменные из соответствующих масивов
 * 
 * @param array $matches
 * @return array
 */
function replace_pattern($matches) {
    GLOBAL $input, $input_js, $input_css, $input_html;

    if ($matches[1]=='txt'){ // && !isset($input[$matches[2]])
        if (isset ($input[$matches[2]])){
            return trim($input[$matches[2]]);
        } else {
            return '';
        }
    }

    if ($matches[1]=='js'){
        if (isset ($input_js[$matches[2]])){
            return trim($input_js[$matches[2]]);
        } else {
            return '';
        }
    }

    if ($matches[1]=='css'){
        return $input_css[$matches[2]];
    }

    if ($matches[1]=='html'){
        if (isset ($input_html[$matches[2]])){
            return trim($input_html[$matches[2]]);
        } else {
            return '';
        }
    }
}

################################################################################


// if news
if($mod['parent']==66) {
    $html_inner=set_default_file('html_inner_', 'news');
}

#загрузка файлов с хтмл-кодом
$html=file_get_contents($html_header);
$html.=file_get_contents($html_body_header);
$html.=file_get_contents($html_template);


#определение заменяемых переменных
$input['prefix'] = $prefix; //{-txt-prefix}
$input['twitter'] = $settings['twitter']; //{-txt-prefix}
$input['facebook'] = $settings['facebook']; //{-txt-prefix}
$input['vkontakte'] = $settings['vkontakte']; //{-txt-prefix}
//$input['slogan'] = $settings['content_slogan']; //{-txt-slogan}
//$input['email'] = $settings['content_email'];
//$input['email'] = '<b>&#8986;</b>&nbsp;10:00 - 20:00, без выходных';

//$visitors = new Visitors();

$input['email'] = '<img style="float:left" height="18" src="' . $prefix . 'images/call-center.png" alt=" "> ';
$input['email'] .=  $template_vars['l_mode-txt']; //24/7 без выходных


//$input['company_name'] = $settings['content_page_title']; //{-txt-company_name}
$input['content_tel'] = $template_vars['l_content_tel']; //{-txt-company_name}
        
#тема сайта
$input['style_theme'] = $settings['style_theme']; //'images';
#цветовая схема
$input['style_color'] = $settings['style_color'];

$input['analytics_codes'] = $template_vars['l_analytics_codes'];

if (isset($mod)){ 
    $input['logo_title'] = $mod['name']; //--new
    
    $input['page_title']=($mod['fullname']?$mod['fullname']:$mod['name']);
    $input['page_title_js'] = addslashes($input['page_title']);

    if ($mod['metadescription']){
        $input_html['metadescription']='<meta name="description" content="'.$mod['metadescription'].'">';
    }
    if ($mod['metakeywords']){
        $input_html['metakeywords']='<meta name="keywords" content="'.$mod['metakeywords'].'">';
    }
    if ($mod['meta']){
        $input_html['meta']=$mod['meta'];
    }

    $input_js['page']=$mod['url'];
    
    if($user_city != 'kiev'){
        $lang_name = $db->query("SELECT name 
                                 FROM {map_strings} WHERE map_id = ?i AND lang = ?", array($mod['id'], $lang), 'el');
        if(!$lang_name){
            $input_html['cannonical_page'] = 
                '<link rel="canonical" href="//restore.com.ua'.str_replace('/'.$url_lang, '/', $_SERVER['REQUEST_URI']).'"/>';
        }
    }
    
    if ($template_vars['l_about_us_page_video']) {
        $input_html['video_block']= gen_video_block($template_vars['l_about_us_page_video']) ;
    }
}

// Video about us
if ($mod['url'] == 'about_us' && $template_vars['l_about_us_page_video']) {
    // full width page
    templater_no_left_block();
//    if($template_vars['l_about_us_page_video'])
//        $input['video']= gen_video_block($template_vars['l_about_us_page_video']) ;
}

// переводы начинающиеся с l_js_ запихиваем в js в переменную L
$js_translates = array();
foreach($template_vars as $var => $val){
    if(strpos($var, 'l_js_') === 0){
        $js_translates[str_replace('l_js_', '', $var)] = $val;
    }
}
$input['js_translates'] = json_encode($js_translates);

//$input['page_color'] = $mod['page_color'] ? $mod['page_color'] : '#ffffff';

if (isset ($mod) && !isset($input['content'])){
    //$input['page_title']=$mod['name'].'. '.$input['page_title'];
//    $input['content']=$mod['content'];
    $text = gen_content_array($mod['content']);
    $input['content']=$text[0] . (isset($text[1]) ? $text[1] : '');
    $input_js['page']=$mod['url'];

}
/*
if(!isset($input['content_image'])){
    $input['content_image'] = '<img src="'.$prefix.'images/bg_main.jpg" alt=" ">';
}
*/

if ($error404 &&  !isset($input['content'])){
    $input['image'] = $input['image'] = '<img src="'.$prefix.'images/company/kompaniya.jpg" alt=" ">';
    $input_js['page'] = 'none';
    $input['id_inner'] = '';
    $input['page_title']='Страница не найдена';
    header("HTTP/1.1 404 Not Found");
    $input['content'] = 'Запрашиваемая вами страница не найдена.';
    $input['title'] = 'Ошибка';
    //$input['index'] = 'index2';
    
    /* === @todo проработать  === */
    $html_inner = 'html_inner_default.html';
    execute_modules('menu', array()); //добавляем меню
}

###################################################################################

#запустить магию на внутренний шаблон
$input_html['inner']=file_get_contents($html_inner);
$pattern="/\{\-(txt|html)\-([a-zA-Z0-9_]{1,200})\}/";
$input_html['inner']=preg_replace_callback($pattern, "replace_pattern", $input_html['inner'] );

if(isset($_SERVER['HTTP_X_PJAX'])){ 
    /*
    $html = ($mod['is_gmap'] ? '<script>gmap_companyname="'.$settings['site_name'].'";var gmap_lat='.$mod['lat'].';var gmap_lng='.$mod['lng'].';</script>' : '').
            '<title>'.$input['page_title'].'</title>'.$input_html['inner']
            .'<div class="temp_wide_flayer">'. $input_html['wide_banner'] .'</div>'
            .$input_html['footer_text']
            .(isset($input_html['service_block'])?$input_html['service_block']:'')
            .(isset($input_html['buyold_block'])?$input_html['buyold_block']:'')
            .(isset($input_html['buyold_popup'])?$input_html['buyold_popup']:'')
            .'<div class="side_banners">'.(isset($input_html['flayers'])?$input_html['flayers']:'').'</div>'
        ;
    */
    $html = 
        '<title>'.$input['page_title'].'</title>'
        .$input_html['inner']
//        .(isset($input_html['service_block'])?$input_html['service_block']:'')
//        .(isset($input_html['buyold_block'])?$input_html['buyold_block']:'')
        .(isset($input_html['buyold_popup'])?$input_html['buyold_popup']:'')
        .$input_html['footer_text']
    ;
}else{
    #... на общий шаблон
    $pattern="/\{\-(txt|js|css|html)\-([a-zA-Z0-9_]{1,200})\}/";
    $html = preg_replace_callback($pattern, "replace_pattern", $html);
    // так надо
    $html = preg_replace_callback($pattern, "replace_pattern", $html);
}

$html = replace_phones($html);
