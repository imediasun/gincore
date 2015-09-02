<?php

/**
 *
 * Определение текущей страницы из карты сайта
 * goDB ready
 *
 */
$for_sql_request=array_slice($arrequest,0, -1);

$lastrequest=array_slice($arrequest, -1, 1);
$all_sql='AND parent=0';
foreach ($for_sql_request AS $el){
    if (isset($this_sql)){
        $this_sql=$db->makeQuery('(SELECT id FROM {map} WHERE state=1 AND parent=?query AND url=?)', array($this_sql, $el));
    } else {
        $this_sql=$db->makeQuery('(SELECT id FROM {map} WHERE state=1 AND parent=0 AND url=?)', array($el));
    }
    $all_sql=' AND parent='.$this_sql;
}

if ($lastrequest) {
    $url=$lastrequest[0];
} else {
    $url='';
    $all_sql='AND parent=0';
}
//////////////////
$sql=$db->makeQuery('SELECT
                *, redirect AS redir, (SELECT url FROM {map} WHERE id=redir) AS urlredirect, UNIX_TIMESTAMP(uxt) AS uxt
              FROM
                {map}
              WHERE
                state=1 ?query AND url=?  AND page_type != ?i',
            array($all_sql, $url, $configs['service-type-page']));//, $all_sql, $db->escape($url));

// predefine multimod vars
$input['forms']=''; 

$sql=$db->plainQuery($sql);
if ($sql->ar()==1){
    $error404=false;
    $mod = $sql->row();
    
    // lang
    $translates = $db->query("SELECT * 
                              FROM {map_strings} WHERE map_id = ?i", array($mod['id']), 'assoc:lang');
    $mod = translates_for_page($lang, $def_lang, $translates, $mod, true);
    
    #Редирект по настройке страницы
    if ($mod['redirect']){
       
        redirect301(str_replace('//', '/', $prefix.implode('/',$arrequest).'/'.$mod['urlredirect']));
    }
    
    #interactive map
    require_once 'inc_mod_interactive_map.php';
    if (isset($settings['global_interactive_map'])
            && $settings['global_interactive_map']
            && is_file('inc_mod_interactive_map.php')
       ){
        require_once 'inc_mod_interactive_map.php';
    }
    
    #banners
    if (isset($settings['global_banners'])
            && $settings['global_banners']
            && is_file('inc_mod_banners.php')
       ){
        require_once 'inc_mod_banners.php';
    }
    
    //смотрим модули (их шаблоны и темы), подключенные к странице
    $modules_menu=array();
    $modules_content=array();
    $modules_blocks=array();
    $sqlmod = 'SELECT * FROM {map_module} WHERE page_id=?i';
    $sqlmod = $db->query($sqlmod, array($mod['id']), 'assoc');

    // форма
    $mod['content'] = content_form($mod['content']);

    foreach ($sqlmod AS $ppmod){
        if (strpos($ppmod['module'], 'menu_')===0){
//            $modules_menu[]=str_replace('menu_', '', $ppmod['module']);
            $modules_menu[] = array ('mod' => str_replace('menu_', '', $ppmod['module']),
                                    'template' => $ppmod['template'],
                                    'theme' => $ppmod['theme']);
        }
        if (strpos($ppmod['module'], 'content_')===0){
//            $modules_content[]=str_replace('content_', '', $ppmod['module']);
            $modules_content[] = array ('mod' => str_replace('content_', '', $ppmod['module']),
                                    'template' => $ppmod['template'],
                                    'theme' => $ppmod['theme']);
        }
        if (strpos($ppmod['module'], 'blocks_')===0){
            $modules_blocks[] = array ('mod' => str_replace('blocks_', '', $ppmod['module']),
                                    'template' => $ppmod['template'],
                                    'theme' => $ppmod['theme']);
        }
    }


    #хреновая конструкция, придумать лучший вариант
    /**
     * Количество вложенных страниц. Проходим по каждому -1 (он уже получен выше)
     *
     */
    $titles=array(); #массив со списком названий страниц страница->категория->раздел
    $titles[]=$mod['name'];
    $url_all_levels=array(); #двухмерный масив со выдержками содержания каждой страницы: раздел->категория->страница
    $current_urls=array();
    $ss=array();#используется только в цикле.
    $ss[0]['parent']=$mod['parent'];
    for ($t=0;$t<=(count($arrequest)-2);$t++){
        $p = $ss[0]['parent'];
        $ss = $db->query('SELECT (SELECT module FROM {map_module} WHERE page_id = m.id LIMIT 1) as module, id, url, parent, gallery, picture, page_color 
                          FROM {map} as m WHERE id=?i', array($p), 'assoc');
        $sstranslates = $db->query("SELECT name, fullname, content, metadescription, metakeywords, lang 
                              FROM {map_strings} WHERE map_id = ?i", array($p), 'assoc:lang');
        $ss[0] = translates_for_page($lang, $def_lang, $sstranslates, $ss[0], true);
        $url_all_levels[]= $ss[0];
        $titles[]=$ss[0]['name'];
        $current_urls[]=$ss[0]['url'].'/';
    }
    $url_all_levels=array_reverse($url_all_levels);
    $current_urls=array_reverse($current_urls);
    $url_all_levels[]=$mod;
    $current_url=$prefix.implode('', $current_urls).$mod['url'].'/'; #текущий УРЛ с закрывающим слешем. Для создания меню и ссылок


    /**
     * Модули замопляют эл-ты масивов
     *
     * $input_js
     * $input
     * $input_css
     * $input_html 
     */
    ### подключение модулей
    execute_modules('menu', $modules_menu);
    execute_modules('content', $modules_content);
    execute_modules('blocks', $modules_blocks);

    
    #google map
    if (isset($mod['is_gmap']) && $mod['is_gmap']==1){
        require_once 'inc_mod_gmap.php';
    }
    
//    #генерация меню
//    $input_html['root_menu']=gen_root_menu();
//    $input_html['second_menu']=gen_second_menu();

    #ХТМЛ заголовок
    $new_html_header='html_header_'.$mod['template_header'].'.html';
    if (file_exists($new_html_header)){
        $html_header=$new_html_header;
    } else {
        $error404=true;
    }
    
    #основной шаблон
    $new_html_template='html_template_'.$mod['template'].'.html';
    if (file_exists($new_html_template)){
        $html_template=$new_html_template;
    } else {
        $error404=true;
    }
    
    #шапка шаблон
    $new_html_body_header='html_body_header_'.$mod['template_body_header'].'.html';
    if (file_exists($new_html_body_header)){
        $html_body_header=$new_html_body_header;
    } else {
        $error404=true;
    }

    #встроенный шаблон
    $new_html_inner='html_inner_'.$mod['template_inner'].'.html';
    if (file_exists($new_html_inner)){
        $html_inner=$new_html_inner;
    } else {
        $error404=true;
    }




}



if ($error404) {
    header('HTTP/1.0 404 Not Found');
    $mod=$db->query('SELECT * FROM {map} WHERE url=? AND state=1', array('error404'), 'row');
    $translates = $db->query("SELECT * 
                              FROM {map_strings} WHERE map_id = ?i", array($mod['id']), 'assoc:lang');
    $mod = translates_for_page($lang, $def_lang, $translates, $mod, true);
    
    #Редирект по настройке страницы
    if ($mod['redirect']){
        redirect301(str_replace('//', '/', $prefix.$url_lang.implode('/',$arrequest).'/'.$mod['urlredirect']));
    }

    #google map
    if (isset($mod['is_gmap']) && $mod['is_gmap']==1){
        require_once 'inc_mod_gmap.php';
    }
    //смотрим модули (их шаблоны и темы), подключенные к странице
    $modules_menu=array();
    $modules_content=array();
    $modules_blocks=array();
    $sqlmod = 'SELECT * FROM {map_module} WHERE page_id=?i';
    $sqlmod = $db->query($sqlmod, array($mod['id']), 'assoc');

    foreach ($sqlmod AS $ppmod){
        if (strpos($ppmod['module'], 'menu_')===0){
//            $modules_menu[]=str_replace('menu_', '', $ppmod['module']);
            $modules_menu[] = array ('mod' => str_replace('menu_', '', $ppmod['module']),
                                    'template' => $ppmod['template'],
                                    'theme' => $ppmod['theme']);
        }
        if (strpos($ppmod['module'], 'content_')===0){
//            $modules_content[]=str_replace('content_', '', $ppmod['module']);
            $modules_content[] = array ('mod' => str_replace('content_', '', $ppmod['module']),
                                    'template' => $ppmod['template'],
                                    'theme' => $ppmod['theme']);
        }
        if (strpos($ppmod['module'], 'blocks_')===0){
            $modules_blocks[] = array ('mod' => str_replace('blocks_', '', $ppmod['module']),
                                    'template' => $ppmod['template'],
                                    'theme' => $ppmod['theme']);
        }
    }

    ### подключение модулей
    execute_modules('menu', $modules_menu);
    execute_modules('content', $modules_content);
    execute_modules('blocks', $modules_blocks);

    #ХТМЛ заголовок
    $new_html_header='html_header_'.$mod['template_header'].'.html';
    if (file_exists($new_html_header))
        $html_header=$new_html_header;

    #основной шаблон
    $new_html_template='html_template_'.$mod['template'].'.html';
    if (file_exists($new_html_template))
        $html_template=$new_html_template;

    #шапка шаблон
    $new_html_body_header='html_body_header_'.$mod['template_body_header'].'.html';
    if (file_exists($new_html_body_header))
        $html_body_header=$new_html_body_header;

    #встроенный шаблон
    $new_html_inner='html_inner_'.$mod['template_inner'].'.html';
    if (file_exists($new_html_inner))
        $html_inner=$new_html_inner;

}