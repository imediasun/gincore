<?php

$host = $_SERVER['HTTP_HOST'];
if(in_array($host, array('www.restore.in.ua', 'restore.in.ua'))){
    $url = 'http://restore.kiev.ua'.$_SERVER['REQUEST_URI'].'?utm_source=city&utm_medium=board&utm_campaign=april';
    redirect301($url);
}

$sources = $db->query("SELECT id, source FROM {sources}")->vars();
$utm_source = isset($_COOKIE['utm_source']) ? $_COOKIE['utm_source'] : '';
if(!in_array($utm_source, $sources)){
    $utm_source = '';
    setcookie('utm_source', null, -1, $prefix);
}
$get_utm_source = isset($_GET['utm_source']) ? $_GET['utm_source'] : '';
if(in_array($get_utm_source, $sources)){
    $utm_source = $get_utm_source;
    setcookie('utm_source', $get_utm_source, time() + 86400 * 30, $prefix);
}

if($utm_source){
    $utm_source_phones = $db->query("SELECT phone_static,phone_mobile "
                                   ."FROM {sources} WHERE source = ?", array($utm_source), 'row');
}

function replace_phones($content){
    global $settings, $utm_source, $utm_source_phones, $template_vars;
	$alarm = (int)$settings['content_alarm'];
	$default_phones['static'] = trim($template_vars['l_content_tel']);
    $default_phones['mobile'] = trim($template_vars['l_content_phone_mob']);
	
	if($utm_source and $alarm){
		$change_array['static'] = $settings['content_phone_alarm'];
		$change_array['mobile'] = $settings['content_phone_mob_alarm'];
	}
	elseif($utm_source and !$alarm){
		$change_array['static'] = $utm_source_phones['phone_static'];
		$change_array['mobile'] = $utm_source_phones['phone_mobile'];
	}
	elseif(!$utm_source and $alarm){
		$change_array['static'] = $settings['content_phone_alarm'];
		$change_array['mobile'] = $settings['content_phone_mob_alarm'];
	}
	elseif(!$utm_source and !$alarm){
		return $content; // exit if nothing to change - no utm or alarm
	}
	// change phones and return 
	return str_replace($default_phones, $change_array, $content);
}