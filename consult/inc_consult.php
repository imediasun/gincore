<?php

/* время:
 * в таблицу падает время UTC и смещение для юзера или админа
 * тогда для юзера будет время относительно его часового пояса, а для админа админского ч.п.
 */

global $settings, $template_vars;
//$activate = $db->query("SELECT value FROM {consult_settings} WHERE param='activate'")->el();
$activate = true;
$consult = '';

$msg_time = get_request_time();

if($activate){
    
    $msg1 = $msg_time[0];
    $msg2 = $msg_time[1];
    
    $consult = $db->query("SELECT * FROM {map} WHERE url=? AND state=1", array('konsultant'), 'row');

    $picture = '';
    $name = '';
    $device = 'устройство';
    if($consult) {
        $translates = $db->query("SELECT content, lang 
                                  FROM {map_strings} WHERE map_id = ?i", array($consult['id']), 'assoc:lang');
        $consult = translates_for_page($lang, $def_lang, $translates, $consult, true);
        if($consult['gallery'] && $consult['picture'])
            $picture= '<span class="consultant_photo"
                style="background-image:
                url(\''.$prefix.'images/'.$consult['gallery'].'/'.$consult['picture'].'\')">
                </span>';
        $name = trim(strip_tags($consult['content']));
    }
    
    if(!empty($mod['chat_caption'])){
        $chat_caption = $mod['chat_caption'];
    }else{
        $chat_caption = str_replace($template_vars['l_default_chat_caption'], '', $mod['name']);
    }
    
    $consult_msg1 = $msg1.$template_vars['l_chat_msg1'];
    $consult_msg2 = str_replace('%time%', $msg2, $template_vars['l_chat_msg2']);
    $consult_msg2 = str_replace('%device%', $chat_caption, $consult_msg2);
    $consult_msg3 = $template_vars['l_chat_msg3'].$template_vars['l_content_tel'];

    $consult = '
        <div id="consult_btn">Консультант</div>
            <div id="consult">
                <div class="consult_header clearfix">
                    <div id="close_consult"></div>'
                    .$picture
                    .'Консультант '
                    .$name
                    .'<div id="consult_state"></div>
                </div>
                    
                <div class="consult_inner">
                    <div id="consult_start_form">'
                    .$consult_msg1
                    .'<span class="consult_recall">'
                        .$consult_msg2
                    .'</span>'
                    .'<label>
                            <!--<span class="consult_label"></span>-->
                            <input type="text" name="phone" id="consult_phone" placeholder="380 (__) ___-__-__" value="">
                    </label>
                        <span class="error_message"></span>
                        <input class="consult_send_btn" type="button" id="start_consult" value="'.$template_vars['l_chat_submit'].'">
                        <div class="consult_manager">'
                            .$consult_msg3
                        .'</div>
                    </div>
                </div>
                <span class="success_message"></span>
            </div>
    ';
}

$input['consult'] = $consult;

?>
