<?php
/**
 * File, that enables $lang variable.
 * This will remove zero element (en, ru, ua) from $arrequest array.
 * 
 * There is no domain lang. resolution, only by url
 * 
 */

$ajax = isset($ajax) && $ajax;

//default settings
require_once 'inc_lang_config.php';
require_once 'inc_lang_func.php';

$domain = $_SERVER['HTTP_HOST'];

// если зашли на киев (или на ком юа буз поддомена) и чувак со львова то кидаем на львов. 
// а если зашли на львов и не со львова то оставляем на львове
// если поисковый бот, то куда зашел там и оставляем (но если попал на не существующий, то кидаем на киев)
// редирект включен или выключен по настройке
// 
// домен restore.kiev.ua для киева
// домен restore.com.ua для поддоменов

$user_city = 'kiev';
$user_ip = Visitors::get_ip();
//$user_ip = '93.77.134.0'; // lviv test ip

$cookie_city = isset($_COOKIE['user_city']) && ($_COOKIE['user_city'] == 'kiev' || 
                   isset($langs_arr[$_COOKIE['user_city']])) ? $_COOKIE['user_city'] : '';
if($cookie_city){
    $user_city = $cookie_city;
    $user_city_detected = $cookie_city;
}else{
    $user_city_detected = 'kiev';
    if (extension_loaded('geoip')) {
        if (@geoip_record_by_name($user_ip)) {
            $record = geoip_record_by_name($user_ip);
            $record_city = isset($record['city']) ? strtolower($record['city']) : '';
            if(isset($langs_arr[$record_city])){
                $user_city_detected = $record_city;
            }
        }
    }
}

//$user_city_detected = 'lviv';
$subdomain = null;
$url_lang = '';

if(!$ajax){
    
    $req_uri = $_SERVER['REQUEST_URI'] != '/' ? $_SERVER['REQUEST_URI'] : '';
    
    // склейка с определением по поддомену
    $subdomain_old = str_replace(array('restore.kiev.ua', 'restore.com.ua', '.'), '', $domain);
    if(isset($langs_arr[$subdomain_old])){
        redirect301('https://restore.com.ua/'.$subdomain_old.$req_uri);
    }

    $arrequest_has_lang = (count($arrequest) > 0 && in_array($arrequest[0], $lang_arr));
    if(!$arrequest_has_lang){ 
        $subdomain = $user_city = 'kiev';
    }else{
        $subdomain = $arrequest[0];
        $url_lang = $subdomain;
        array_shift($arrequest);
    }

    if(strpos($domain, '192.') !== 0 && strpos($domain, '127.0.') !== 0 && strpos($domain, 'git.fonbrand.com') !== 0){ // на локальном ниче не делаем
        // существует ли поддомен (если зашли на поддомен)
        $subdomain_set = isset($langs_arr[$subdomain]);

        if(!$subdomain_set){
            if($user_city_detected != 'kiev' && !is_crawler() && $settings['cities_geoip_redirect']){
                redirect301('https://restore.com.ua/'.$user_city_detected.$req_uri, false);
            }elseif($domain != 'restore.com.ua'){
                redirect301('https://restore.com.ua'.$req_uri, false);
            }
        }else{
            $user_city = $subdomain;
        }
    }
}
$lang = $user_city;

if(isset($_GET['_cc'])){
    setcookie('user_city', $lang, time() + 86400*2, $prefix);
    $cookie_city = $lang;
    header('Location: https://'.$_SERVER['HTTP_HOST'].str_replace(array('?_cc', '&_cc'), '', $_SERVER['REQUEST_URI']));
    exit;
}

$input['city_select'] = gen_city_select(true);
$input['detected_city_name'] = $langs_arr[$user_city_detected == 'kiev' ? 'default_kiev' : $user_city_detected]['name'];
$input['current_city_name'] = $langs_arr[$user_city == 'kiev' ? 'default_kiev' : $user_city]['name'];
$input['city'] = $lang;
$url_lang = $url_lang ? $url_lang.'/' : '';
$input['url_lang'] = $url_lang;
$template_vars = get_translates('template_vars', 'var_id', 'var', 'text');

// окно выбора города
if(!$cookie_city){
    $list = '';
    foreach($langs_arr as $city => $lng){
        if($city == 'default_kiev'){
            $city = 'kiev';
            $link = 'https://restore.com.ua';
        }else{
            $link = 'https://restore.com.ua/'.$city;
        }
        $active = $city == $user_city_detected;
        $current_city = ($city == $lang ? ' class="current_city"' : '');
        $uri = $_SERVER['REQUEST_URI'];
        if(strpos($uri, '/'.$user_city) === 0){
            $uri = str_replace(array('/'.$user_city.'/', '/'.$user_city), '/', $uri);
        }
        $list .= '
            <li>
                <label>
                    <input'.$current_city.($active ? ' checked="checked"' : '').' data-site="'.$link.$uri.'?_cc" '
                                                        . 'type="radio" name="city_confirm_select" value="'.$city.'">
                    '.$lng['name'].'
                </label>
            </li>
        ';
    }
    $input['first_city_select'] = '
        <div class="first_city_select" id="first_city_select">
            <div class="first_city_select_inner">
                <div class="first_city_select_content" id="first_city_select_confirm">
                    <div class="first_city_select_title">
                        '.$template_vars['l_city_select_your_city'].' - '.$input['detected_city_name'].'?
                    </div>
                    <button type="button" class="black_btn pull-left current_city_confirm">'.$template_vars['l_city_select_your_city_yes'].'</button>
                    <button type="button" class="dotted_btn pull-right" id="first_city_select_another">'.$template_vars['l_city_select_your_city_another'].'</button>
                </div>
                <div class="first_city_select_content hidden" id="first_city_select_change">
                    <div class="row-fcs">
                        <div class="first_city_select_change_left">
                            <div class="first_city_select_title">
                                '.$template_vars['l_city_select_your_city'].':
                            </div>
                            <button type="button" class="black_btn pull-left current_city_confirm">'.$template_vars['l_city_select_your_city_yes'].'</button>
                        </div>
                        <div class="first_city_select_change_right">
                            <ul>
                                '.$list.'
                                <li>
                                    <label>
                                        <input type="radio" name="city_confirm_select" value="kiev" data-site="https://restore.com.ua'.$_SERVER['REQUEST_URI'].'?_cc">
                                        '.$template_vars['l_another_city_radio'].'
                                    </label>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="first_city_select_arrow1"></div>
                <div class="first_city_select_arrow2"></div>
            </div>
        </div>
    ';
}

if($user_city != 'kiev'){ // настройка аварийного режима пока только для киев
    $settings['content_alarm'] = 0;
}