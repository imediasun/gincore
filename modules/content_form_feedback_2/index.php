<?php
/**
 * модуль вывода простого контента
 * 
 */

session_start();
//echo 'content_default';

global $mod, $prefix, $url_all_levelsm, $arrequest, $db, $path, $txt, $settings, $template_vars, $lang, $def_lang;

include('kcaptcha/kcaptcha.php');

/*
$captcha = new KCAPTCHA();
if($_REQUEST[session_name()]){
	$_SESSION['captcha_keystring'] = $captcha->getKeyString();
}
*/
$out = '';
$txt = array();

$txt['form_title'] = $template_vars['l_reviews_form_title'];
$txt['form_class'] = 'review';

$marks = $db->query("SELECT id FROM {reviews_marks} ORDER BY id", array(), 'assoc:id');
$options='';
$translates = get_few_translates(
    'reviews_marks', 
    'mark_id', 
    $db->makeQuery("mark_id IN (?q)", array(implode(',', array_keys($marks))))
);
$marks_array = array();
foreach ($marks as $mark){
    $mark = translates_for_page($lang, $def_lang, $translates[$mark['id']], $mark, true);
    $marks_array[$mark['id']] = $mark['name'];
    $options.='<option value="'.$mark['id'].'">'.$mark['name'].'</option>';
}

if(isset($settings['form_description2']) && $settings['form_description2'])
    $txt['form_description'] = $settings['form_description2'];

$txt['form_action'] = $prefix.'ajax.php?act=feedback&amp;type=3';

$txt['form_fields'] = '
            <div class="form-group">
                <span>'.$template_vars['l_reviews_form_field_name'].'</span>
                <input class="form-control" type="text" name="name">
            </div>
            <div class="form-group">
                <span>'.$template_vars['l_reviews_form_field_email'].'</span>
                <input class="form-control" type="text" name="email">
            </div>
            <div class="form-group">
                <span>'.$template_vars['l_reviews_form_field_rate'].'</span>
                <select class="form-control" name="mark">'
                .$options
                .'</select>
            </div>
            <div class="form-group">
                <span>'.$template_vars['l_reviews_form_field_comment'].'</span>
                <textarea rows="3" class="form-control" name="message"></textarea>
            </div>
            <div class="form-group">
                <span>'.$template_vars['l_reviews_form_field_captcha'].'</span><br>
                <img src="'.$prefix.'kcaptcha/?'. session_name().'='.session_id().'">
            </div>
            <div class="form-group">
                <input class="form-control" type="text" name="keystring">
            </div>
            <div class="form-group">
                <input type="submit" class="btn black_btn" name="feedback" value="'.$template_vars['l_reviews_form_submit'].'">
            </div>
            ';


include_once 'class_catalog.php';
$catalog = new Catalog();


    // filter page
   
    $items = '';
    $item_list = '';
    $current_page = 1;
    $get = '';
    if ($_GET) $get = $_GET;
    if (isset($_GET['page'])){
        cannonical_page_for_pagination('page');
        $current_page = intval($_GET['page']);
    }
    
    $items = $catalog->getElements($current_page);
    $pages = $catalog->getNav($current_page, $get);

    foreach ($items as $c){
        
        $item_list .= '<div class="comment">
                            <div class="name">'
                                .htmlspecialchars($c['user'])
                            .'</div>
                            <div class="message">'
                                .htmlspecialchars($c['comment'])            
                            .'</div>
                            <div class="time">
                                <time datetime="'.date('Y-m-d', strtotime($c['uxt'])).'">'.date('d.m.Y', strtotime($c['uxt'])).'</time>
                            </div>
                            <div class="mark_'.$c['mark'].'">'
                                .$marks_array[$c['mark']]
                                .'<svg xmlns="http://www.w3.org/2000/svg" version="1.1" class="svg-triangle">
                                    <polygon points="0,0 7,0 0,7"></polygon>
                                  </svg>'
                            .'</div>
                        </div>';
    }
    
    $item_list = '<div class="items">'.$item_list.'</div>';
    $item_list = $pages.$item_list.$pages;

    $content = $item_list;
    /*
    if($_GET){
//        make page non canonical
        $mod['meta'] = askCanonical($prefix.$page);
    }
    */

/*    
$comments = $db->query("SELECT user, comment, mark, uxt FROM {reviews} ORDER BY id DESC", array(), 'assoc');
$content = '';
foreach($comments as $c){
    $content .= '<div class="comment">
        <div class="name">'
            .$c['user']
        .'</div>
        <div class="message">'
            .$c['comment']            
        .'</div>
        <div class="time">
            <time datetime="'.date('Y-m-d', strtotime($c['uxt'])).'">'.date('d.m.Y', strtotime($c['uxt'])).'</time>
        </div>
        <div class="mark_'.$c['mark'].'">'
            .$marks[$c['mark']]
        .'</div>
    </div>';
    
    
    
}
*/
$out = mod_magic(__DIR__.'/index.html');

$input['forms'] .= $out;
$input['title'] = $mod['name'];
$input['content'] = $content;
gen_content_array($mod['content']);
// full width page
templater_no_left_block();


?>