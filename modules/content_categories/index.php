<?php
/**
 * модуль вывода простого контента
 * 
 */


//echo 'content_default';

global $mod, $prefix, $url_lang, $arrequest, $db, $lang, $def_lang, $template_vars;

//$input['page_tree'] = gen_page_tree();

$content = '';
$picture1 = '';
$id = $mod['parent'];
$mod['template_inner'] = 'categories'; // шаблон

$text = gen_content_array($mod['content']);
$content .= '
        <div class="article">
            <div class="inner">
                <div class="container">
                    '.$text[0].(isset($text[1])?$text[1]:'')
                .'</div>
            </div>
        </div>
            ';
$id = $mod['id'];
$arrequest[1] = '';

$articles = $db->query("SELECT id,url, uxt, picture, gallery FROM {map} "
                      ."WHERE parent = ?i AND state = 1 ORDER BY prio", array($id), 'assoc:id');
$translates = get_few_translates(
            'map', 
            'map_id', 
            $db->makeQuery("map_id IN (?q)", array(implode(',', array_keys($articles))))
        );
//print_r($mod);

$i = 0;
$i_max = 3; // max columns number
foreach($articles as $article){
    $article = translates_for_page($lang, $def_lang, $translates[$article['id']], $article, true);
    $i++;
    if($i==1)
        $picture1 .= '<div class="row">';
    $picture_url = '';
    if($article['picture']) {
            $picture_url = $prefix.'images/'.$article['gallery'].'/'.str_replace('_m.', '.', $article['picture']);
    }
    
    $link_inner = '<span class="image greyscale"'
                .($picture_url ? ' style="background-image: url(\''.$picture_url.'\')"' : '').'></span>
                <span class="title">'.$article['name'].'</span>';
    $link_attr = array(
        'class' => 'category'.(($arrequest[1]==$article['url']) ? ' active' : '').'',
        'data-rel' => $article['url'],
        'data-title' => ($article['fullname'] ? $article['fullname'] : $article['name'])
    );
    $picture1 .= 
        '<div class="col-sm-4">'.
            gen_link($prefix.$url_lang.$arrequest[0].'/'.$article['url'], $link_inner, true, $link_attr).
        '</div>'
    ;
    if($i==$i_max) {
        $i = 0;
        $picture1 .= '</div>';
    }
}
if($i>0 && $i<$i_max) $picture1 .= '</div>';

$picture_block = '';
if ($picture1)
$picture_block = '<div class="categories_block">
                            '.$picture1.'
                </div>';




$content_block = $content;
            
/*
if ($mod['gallery']){
    $input['head_pics'] = get_big_pics($mod['gallery'], 3);
} 
*/
$input['title'] = $mod['name'];
$input['sub_title'] = $template_vars['l_categories_page_sub_title'];
$input['content_images'] = $picture_block;
$input['content'] = $content_block;

//$input_html['css_extra'] = '<link type="text/css" rel="stylesheet" href="'. $prefix .'extra/fancy/jquery.fancybox.css">';
//$input_js['extra_files'] = '<script type="text/javascript" src="'. $prefix .'extra/fancy/jquery.fancybox.js"></script>';


// full width page
templater_no_left_block('100');

/* new full width content design 
 * no banner (opera fake)
 */
$input_html['wide_banner'] = '';
$input['content'] = '';
$input_html['content_beforefooter'] = $content_block;

$input['footer_form'] = '
    <div class="visible-xs mobile_footer_form">
        <div class="mobile_footer_form_title">'.$template_vars['l_mobile_footer_form_title'].'</div>
        '.gen_data_form(3, null).'
    </div>
';