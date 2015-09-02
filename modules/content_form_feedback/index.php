<?php
/**
 * модуль вывода простого контента
 * 
 */


//echo 'content_default';

global $mod, $prefix, $url_all_levelsm, $arrequest, $db, $path, $txt, $settings, $template_vars;



$out = '';
$txt = array();

$txt['form_title'] = $template_vars['l_feedback_form_title'];
$txt['form_class'] = 'feedback';

if(isset($settings['form_description']) && $settings['form_description'])
    $txt['form_description'] = $settings['form_description'];

$txt['form_action'] = $prefix.'ajax.php?act=feedback&amp;type=1';

$txt['form_fields'] = '
            <div class="form_el">
                <span>'.$template_vars['l_feedback_form_field_name'].'</span>
                <input type="text" name="name">
            </div>
            <div class="form_el">
                <span>'.$template_vars['l_feedback_form_field_phone'].'</span>
                <input type="text" name="tel">
            </div>
            <div class="form_el">
                <span>'.$template_vars['l_feedback_form_field_parts'].'</span>
                <textarea name="message"></textarea>
            </div>
            <div class="form_el">
                <input type="submit" name="feedback" value="'.$template_vars['l_feedback_form_field_submit'].'">
            </div>
            ';

$out = mod_magic(__DIR__.'/index.html');

$input['forms'] .= $out;

?>