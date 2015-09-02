<?php
/**
 * модуль вывода простого контента
 * 
 */


//echo 'content_default';

global $mod, $prefix, $url_all_levelsm, $arrequest, $db, $path, $txt, $settings;



$out = '';
$txt = array();

$txt['form_title'] = 'Запись на СТО';
$txt['form_class'] = 'feedback';

$txt['form_action'] = $prefix.'ajax.php?act=feedback&amp;type=11';

if(isset($settings['form_description1']) && $settings['form_description1'])
    $txt['form_description'] = $settings['form_description1'];

$txt['form_fields'] = '
            <div class="form_el">
                <span>Ваше имя</span>
                <input type="text" name="name">
            </div>
            <div class="form_el">
                <span>Ваш телефон</span>
                <input type="text" name="tel">
            </div>
            <div class="form_el">
                <span>День недели</span>
                <input type="text" name="day">
            </div>
            <div class="form_el">
                <span>Время</span>
                <input type="text" name="time">
            </div>
            <div class="form_el">
                <input type="submit" name="feedback" value="Записаться">
            </div>
            ';

$out = mod_magic(__DIR__.'/index.html');

$input['forms'] .= $out;

?>