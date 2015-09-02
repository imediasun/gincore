<?php

// генерим шаблоны и скрипты для fb director

function gen_block_fb_director($title, $content){
    $block_html = '
        <div class="fb_director_block on_load_popup" data-content="buy-one-click">
            <div class="fb_director_title">
                <i></i>'
                .$title
            .'</div>
            <div class="fb_director_content">'
                .$content
            .'</div>
        </div>';

    return $block_html;
}

function gen_popup_fb_director(){
    global $prefix, $template_vars;
    $popup_one_click = '<div class="form">
        <form method="POST" id="oneclick" data-validate="parsley" action="'.$prefix.'ajax.php?act=feedback&amp;type=12">';
    $popup_one_click .= '<table class="table_oneclick"><tbody>';
    $popup_one_click .=
            '<tr><td colspan="2" class="oclick_user_message top_ocum">
                    '.$template_vars['l_form_director_text'].'
            </td></tr>'
            .'<tr><td class="td_name">'.$template_vars['l_form_director_field_text'].'</td><td class="left"><textarea name="message" data-trigger="keyup" data-rangelength="[20,250]" data-required="true"></textarea></td></tr>'
            .'<tr><td class="td_name">'.$template_vars['l_form_director_field_name'].'</td><td class="left"><input class="input" type="text" name="name" value=""></td></tr>'
            .'<tr><td class="td_name">'.$template_vars['l_form_director_field_contacts'].'</td><td class="left"><input class="input" data-trigger="change" data-required="true" type="text" name="email" value=""></td></tr>'
    //        '<tr><td class="td_name">Ф.И.О.</td><td class="left"><input class="input" type="text" name="fio" value=""></td></tr>'.
    //        '<tr><td class="td_name">Электронная почта</td><td class="left"><input class="input" type="text" name="email" value=""></td></tr>'.
    //        '<tr><td colspan="2" class="oclick_user_message center text-bold">Когда Вам будет удобно принять звонок от нашего менеджера?</td></tr>'.
    //        '<tr><td colspan="2" class="oneclick_select_time"><label><input class="enable-timepicker" type="radio" name="one-click" value="15min"> В течении 15 минут</label></td></tr>'.
    //        '<tr><td colspan="2" class="oneclick_select_time"><label><input class="enable-timepicker" type="radio" name="one-click" value="1hour"> Через час</label></td></tr>'
        ;
        //$popup_one_click .= '<tr><td class="right"><label> Сегодня после <input class="enable-timepicker" type="radio" name="one-click" value="after"></label></td><td class="left"><input disabled type="text" id="time" name="time" /></td></tr>';
        //$popup_one_click .= '<tr><td class="right"><label> Указать временной диапазон <input class="enable-timepicker" type="radio" name="one-click" value="diapason"></label></td></tr>';
        //$popup_one_click .= '<tr><td class="right"><label for="from"> с </label></td><td class="left"><input disabled type="text" id="from" name="from" /></td></tr>';
        //$popup_one_click .= '<tr><td class="right"><label for="to"> до </label></td><td class="left"><input disabled type="text" id="to" name="to" /></td></tr>';
    //    $popup_one_click .=
    //        '<tr><td colspan="2" class="oneclick_select_time"><label><input checked class="enable-timepicker" type="radio" name="one-click" value="any-time"> В любое время</label></td></tr>';


//    $popup_one_click .= '<tr><td></td><td class="right"><input type="button" class="green_btn" value="Отправить" onclick="javascript:$(\'#oneclick\').parsley( \'validate\' );" id="send-one-click" ></td></tr>';
    $popup_one_click .= '<tr><td></td><td class="right"><input type="submit" class="green_btn" value="'.$template_vars['l_form_director_submit'].'" onclick="javascript:$(\'#oneclick\').parsley( \'validate\' );" id="send-one-click" ></td></tr>';

    $popup_one_click .= '</tbody></table>
        <div class="message"></div>
        </form>
        </div>';

    return $popup_one_click;
}

if ($settings['email_fb_director']) {
    $sql = $db->query('SELECT * FROM {map} WHERE parent=?i AND url=?',
        array(13, 'fb_director'), 'row');// fb_director in sitemap
    // lang
    $translates = $db->query("SELECT name, fullname, content, metadescription, metakeywords, lang 
                              FROM {map_strings} WHERE map_id = ?i", array($sql['id']), 'assoc:lang');
    $sql = translates_for_page($lang, $def_lang, $translates, $sql, true);
    
    $input_html['fb_director_block'] = gen_block_fb_director($sql['name'], $sql['content']);
    $input_html['feedback_director'] = gen_popup_fb_director();
    $input_html['feedback_director_1'] = $sql['name'];
}