<?php
/**
 * модуль вывода контента Startpage
 * 
 */

//echo 'content_default';

global $mod, $prefix, $url_lang, $arrequest, $db, $txt, $settings, $lang, $def_lang, $template_vars;

$input['content'] = $mod['content'];

$input['title'] = $mod['name'];
/*
if ($mod['gallery']){
    $input['mainpage_gallery'] = get_big_pics($mod['gallery'], 4);
} else {
    $input['mainpage_gallery'] = '';
}
*/

$advantages = gen_advantages_block();

$content= '';
$news_block = '';
$block1 = '';
$block2 = '';
$block3 = '';
        
$marks = $db->query("SELECT id FROM {reviews_marks}ORDER BY id", array(), 'assoc:id');
$comments = $db->query("SELECT user, comment, mark, uxt FROM {reviews} ORDER BY id DESC LIMIT 0,3", array(), 'assoc');
$block2 = '<h1><i></i>'.$template_vars['l_index_page_reviews_title'].'</h1>';
$translates = get_few_translates(
    'reviews_marks', 
    'mark_id', 
    $db->makeQuery("mark_id IN (?q)", array(implode(',', array_keys($marks))))
);
foreach($comments as $c){
    $mark = translates_for_page($lang, $def_lang, $translates[$c['mark']], array(), true);
    $block2 .= '<div class="comment">
        <div class="name">'
            .$c['user']
        .'</div>
        <div class="message">'
           .((strlen($c['comment']) > 50) ? bite_str($c['comment'], 0, 45) : $c['comment'])
        .'</div>
        <div class="time">
            <time datetime="'.date('Y-m-d', strtotime($c['uxt'])).'">'.date('d.m', strtotime($c['uxt'])).'</time>
        </div>
        <div class="mark_'.$c['mark'].'">'
            .$mark['name']
                    .'<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-triangle">
            <polygon points="0,0 7,0 0,7"></polygon>
          </svg>'
        .'</div>'

    .'</div>';
}

$block1 = gen_articles(
    "url = ? AND state = 1", 
    array('about_us'),
    false,
    'block'
);
/*
$block2 = gen_articles(
    "url = ? AND state = 1", 
    array('reviews'),
    false,
    'block'
);
$block3 = gen_articles(
    "url = ? AND state = 1", 
    array('contacts'),
    false,
    'block'
);
*/

$block1 = '<div class="col-sm-7 col-md-6 about mainpage_content_blocks ">'
        .'<h1><i></i>'.$template_vars['l_index_page_about_company_title'].'</h1>'
        .$block1
        .'</div>';
$block2 = '<div class="col-sm-5 col-md-3 reviews">'
        .$block2
        .'<a class="buttn" href="'.$prefix.$url_lang.'restore/reviews">'.$template_vars['l_index_page_reviews_all_btn'].'</a>'
        .'</div>';
//$block3 = '<div class="contacts drop-shadow">'.$block3.'</div>';


$content = $block1.$block3.$block2;
/*
$text = explode('<!-- pagebreak -->', $mod['content']);
$footer = '<div class="footer1">
    <div class="footer_text inner">
    <div class="left">'
        .$text[2]
        .'</div>
    <div class="right"><p>'
        .$text[3]
        .'</div>
            </div></div>';
  

$input_html['footer_text'] = $footer;
 
 */
$text = gen_content_array($mod['content']);
//$input_html['wide_banner'] = '';
$input['content'] = $content;

$input['footer_form'] = '
    <div class="visible-xs mobile_footer_form">
        <div class="mobile_footer_form_title">'.$template_vars['l_mobile_footer_form_title'].'</div>
        '.gen_data_form(3, null).'
    </div>
';

// убираем ненужные блоки
$input_html['flayers'] = '';
$input_html['wide_banner'] = '';
$input_html['news_block'] = '';
//$input_html['video_block'] = '';
$input_html['fb_director_block'] = '';
$input_html['advantages_block'] = $advantages;
$input_html['root_menu'] = '<div class="footer_menu_1">'
                    .gen_root_menu(0, false, true)
                    .'</div>';


if ($settings['content_block_news_quantity']) {
    $news_block = gen_news_block($settings['content_block_news_quantity']) ;
}

$news_block = '
            <div class="article">
                <div class="inner_fullwidth">'
                .$news_block
                .'</div>
            </div>
            ';

$input_html['content_beforefooter'] = $news_block;
/*
if ($mod['picture']){
    $input['image'] = '<img src="'.$prefix.'images/'.$mod['gallery'].'/'.str_replace('_m.', '.', $mod['picture']).'" alt="'.$input['title'].'">';
} else {
    $input['image'] = '';
}
*/
