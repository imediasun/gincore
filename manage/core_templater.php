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
            return $input[$matches[2]];
        } else {
            return '';
        }
    }

    if ($matches[1]=='js'){
        if (isset ($input_js[$matches[2]])){
            return $input_js[$matches[2]];
        } else {
            return '';
        }
    }

    if ($matches[1]=='css'){
        if (isset ($input_css[$matches[2]])){
            return $input_css[$matches[2]];
        } else {
            return '';
        }
    }

    if ($matches[1]=='html'){
        if (isset ($input_html[$matches[2]])){
            return $input_html[$matches[2]];
        } else {
            return '';
        }
    }
}

################################################################################

#загрузка файлов с хтмл-кодом
$html = file_get_contents($html_header);
$html .= file_get_contents($html_template);


#определение заменяемых переменных
if(!isset($all_configs['arrequest'][0])){
    $input['home_active'] = ' class="active"';
}
$input['siteprefix'] = $all_configs['siteprefix'];
$input['prefix'] = $all_configs['prefix']; //{-txt-prefix}
$input['page_title'] = $pre_title;
$input['module'] = isset($all_configs['arrequest'][0]) ? $all_configs['arrequest'][0] : '';

$input_html['timer'] = timerout(0);
$input_html['mainmenu'] = $mainmenu;
if (isset($infoblock)){
    $input_html['infoblock'] = $infoblock->genblock();
}

###################################################################################

// шаблон и файлы js, css из модуля
if($curmod){
    
    $mod_path = $all_configs['path'].'modules/'.$curmod.'/';
    $mod_prefix = $all_configs['prefix'].'modules/'.$curmod.'/';
    
    $css = '';
    if(is_dir($mod_path.'css/')){
        $css_files = scandir($mod_path.'css/');
        foreach($css_files as $file){
            if($file != '.' && $file != '..'){
                $css .= '<link type="text/css" rel="stylesheet" href="'.$mod_prefix.'css/'.$file.'?1">';
            }
        }
    }
    $input_css['module'] = $css;
    
    $js = '';
    if(is_dir($mod_path.'js/')){
        $js_files = scandir($mod_path.'js/');
        $main_js = '';
        foreach($js_files as $file){
            if($file != '.' && $file != '..'){
                $link = '<script type="text/javascript" src="'.$mod_prefix.'js/'.$file.'?10"></script>';
                if($file == 'main.js'){
                    $main_js = $link;
                    continue;
                }
                $js .= $link;
            }
        }
        // подрубаем файл main после всех скриптов
        $js .= $main_js;
    }
    $input_js['module'] = $js;
    
    $input_html['module_content'] = file_get_contents($mod_path.'index.html');
    $pattern = "/\{\-(txt|html)\-([a-zA-Z0-9_]{1,20})\}/";
    $input_html['module_content'] = preg_replace_callback($pattern, "replace_pattern", $input_html['module_content'] );
}

$pattern = "/\{\-(txt|js|css|html)\-([a-zA-Z0-9_]{1,20})\}/";
$html = preg_replace_callback($pattern, "replace_pattern", $html);
 