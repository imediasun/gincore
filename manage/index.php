<?php

ini_set('magic_quotes_gpc',0);

$curmod = $mainmenu = $pre_title = '';
$input = $input_html = $input_js = $input_css = $all_configs = array();
$modulename = $modulemenu = $moduleactive = array();


require_once 'inc_config.php';
require_once 'inc_func.php';

include 'inc_settings.php';

require $all_configs['sitepath'].'inc_lang_func.php';

if(isset($all_configs['arrequest'][0]) && $all_configs['arrequest'][0] == 'set_lang' && isset($all_configs['arrequest'][1])){
    $cotnent_lang_cookie = $config['sql_tbl_prefix'].'content_lang';
    setcookie($cotnent_lang_cookie, $all_configs['arrequest'][1], time() + 3600 * 24 * 30, $all_configs['prefix']);
    header('Location: '.$_SERVER['HTTP_REFERER']);
    exit;
}

$langs = get_langs();

//print_r($all_configs['arrequest']);
//print_r($_GET);
#авторизация
$auth = new Auth($all_configs['db'], $langs['lang'], $langs['def_lang'], $langs['langs']);
$auth->cookie_session_name = $config['sql_tbl_prefix'].'cid';
$ifauth = $auth->IfAuth();
$ifadmin = $ifauth['is_adm'];
//echo $ifauth['login'];

$a0 = isset($all_configs['arrequest'][0]) ? $all_configs['arrequest'][0] : '';

$db = $all_configs['db'];

if(!$ifauth && !in_array($a0, array('login_form'))){
    if(isset($all_configs['arrequest'][0]) && !in_array($all_configs['arrequest'][0], array('login_form', 'logout'))){
        setcookie('login_redirect', $_SERVER['REQUEST_URI'], time() + 1800, $all_configs['prefix']);
    }else{
        setcookie('login_redirect', null, -1, $all_configs['prefix']);
    }
    header("Location: ".$all_configs['prefix']."login_form");
    exit;
}

if(isset($all_configs['arrequest'][0])){

    if($all_configs['arrequest'][0] == 'login_form'){
        $html_header = 'html_header_login.html';
        $html_template = 'html_template_login.html';

        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $pass = isset($_POST['pass']) ? trim($_POST['pass']) : '';
        if($email && $pass){
            $loginrezult = $auth->Login($email, $pass);
            if($loginrezult){
                $ifauth = $auth->IfAuth();
                if(isset($_COOKIE['login_redirect'])){
                    setcookie('login_redirect', null, -1, $all_configs['prefix']);
                    header("Location: ".$_COOKIE['login_redirect']);
                }else{
                    header("Location: ".$all_configs['prefix']);
                }
                exit;
            }else{
                $input['email'] = htmlspecialchars($email);
                $input['error_message'] = '
                    <div class="alert alert-error">
                        <a class="close" data-dismiss="alert" href="#">x</a>'.l('incorrect_login').'
                    </div>';
            }
        }
    }

    if($all_configs['arrequest'][0] == 'logout' && $ifauth){
        $auth->Logout($all_configs);
        header("Location: " . $all_configs['prefix']);
        exit;
    }
}

if(isset($all_configs['arrequest'][0]) && in_array($all_configs['arrequest'][0], array('map','forms','flayers', 'seo'))) {
    //langs
    $lang_switch = '';
    $active_lang_name = '';
    foreach($langs['langs'] as $lnge){
        $active_lang = (!$langs['lang'] && $lnge['default']) || $langs['lang'] == $lnge['url'] ? ' active' : '';
        if($active_lang){
            $active_lang_name = $lnge['name'];
        }
        // class="'.$active_lang.'"
        $lang_switch .= '
            <li>
                <a href="'.$all_configs['prefix'].'set_lang/'.$lnge['url'].'">
                    '.$lnge['name'].'
                </a>
            </li>
        ';
    }
    $lang_switch = '
        <li class="btn-group dropdown" data-toggle="buttons-radio">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                '.$active_lang_name.'
                <span class="caret"></span>
            </a>
            <ul class="dropdown-menu hdropdown notification animated flipInX">
                '.$lang_switch.'
            </ul>
        </li>
    ';
    if(!$ifauth['is_1']){
        $input['lang_switch'] = $lang_switch;
    }
}


////generate modules

$input['current_admin'] = ($ifauth['fio'] ?: $ifauth['login']);
$input['position_admin'] = ($ifauth['position'] ?: $db->query("SELECT name FROM {users_roles} WHERE id = ?i", array($ifauth['role']), 'el'));
$input['hide_sidebar'] = isset($_COOKIE['hide_menu']) && $_COOKIE['hide_menu'] ? 'hide-sidebar' : '';

$modules = scandir('./modules/');

foreach($modules as $mod_folder){
    $module = $all_configs['path'].'/modules/'.$mod_folder.'/index.php';
    if(file_exists($module)){
        require_once $module;
    }
}

$additionally = '';
if($modulename){
    foreach($modulename as $k => $v){
        if(isset($all_configs['arrequest'][0]) && $all_configs['arrequest'][0] == $v){
            $curmod = $v;
            $pre_title = strip_tags($modulemenu[$k]);
        }

        if ($moduleactive[$k] == true) {
            if (($v == 'marketing' && $all_configs['oRole']->hasPrivilege('monitoring')) ||
                ($v == 'products' && $all_configs['oRole']->hasPrivilege('show-goods'))
                || ($v == 'categories' && $all_configs['oRole']->hasPrivilege('show-categories-filters'))
                || ($v == 'users' && $all_configs['oRole']->hasPrivilege('edit-users'))
                || ($v == 'map' && $all_configs['oRole']->hasPrivilege('edit-map'))
                || ($v == 'langs' && $all_configs['oRole']->hasPrivilege('edit-map'))
                || ($v == 'translates' && $all_configs['oRole']->hasPrivilege('edit-map'))
                || ($v == 'flayers' && $all_configs['oRole']->hasPrivilege('site-administration'))
                || ($v == 'settings' && $all_configs['oRole']->hasPrivilege('site-administration'))
                || ($v == 'wrapper' && $all_configs['oRole']->hasPrivilege('site-administration'))
                || ($v == 'offices' && $all_configs['oRole']->hasPrivilege('site-administration'))
                || ($v == 'debug' && $all_configs['oRole']->hasPrivilege('site-administration'))
                || ($v == 'subdomains' && $all_configs['oRole']->hasPrivilege('site-administration'))
                || ($v == 'forms' && $all_configs['oRole']->hasPrivilege('site-administration'))
                || ($v == 'banners' && $all_configs['oRole']->hasPrivilege('site-administration'))
                || ($v == 'imports' && $all_configs['oRole']->hasPrivilege('site-administration')
                    && $all_configs['configs']['manage-show-imports'] == true)
                || ($v == 'orders' && ($all_configs['oRole']->hasPrivilege('edit-clients-orders') || $all_configs['oRole']->hasPrivilege('show-clients-orders')
                        || $all_configs['oRole']->hasPrivilege('edit-suppliers-orders') || $all_configs['oRole']->hasPrivilege('edit-tradein-orders') || $all_configs['oRole']->hasPrivilege('orders-manager')))
                || ($v == 'clients' && $all_configs['oRole']->hasPrivilege('edit-goods'))
                || ($v == 'chat' && $all_configs['oRole']->hasPrivilege('chat'))
                || ($v == 'accountings' && ($all_configs['oRole']->hasPrivilege('accounting') || $all_configs['oRole']->hasPrivilege('accounting-contractors')
                        || $all_configs['oRole']->hasPrivilege('accounting-reports-turnover') || $all_configs['oRole']->hasPrivilege('partner') || $all_configs['oRole']->hasPrivilege('accounting-transactions-contractors')))
                || ($v == 'warehouses' && $all_configs['configs']['erp-use'] == true && ($all_configs['oRole']->hasPrivilege('debit-suppliers-orders')
                        || /*$all_configs['oRole']->hasPrivilege('logistics') || */$all_configs['oRole']->hasPrivilege('scanner-moves')))
                || ($v == 'logistics' && $all_configs['configs']['erp-use'] == true && ($all_configs['oRole']->hasPrivilege('logistics')
                        /*|| $all_configs['oRole']->hasPrivilege('edit-clients-orders')*/))
                || ($v == 'logistics_old' && $all_configs['configs']['erp-use'] == true && ($all_configs['oRole']->hasPrivilege('logistics')
                        /*|| $all_configs['oRole']->hasPrivilege('edit-clients-orders')*/))
                || ($v == 'tasks')
                || ($v == 'statistics')
                || ($v == 'seo' && $all_configs['oRole']->hasPrivilege('edit-map'))
                    
            ) {
                if ($v == 'map' || $v == 'langs' || $v == 'translates' || $v == 'chat'
                    || $v == 'settings' || $v == 'users' || $v == 'offices' || $v == 'wrapper'
                    || $v == 'banners' || $v == 'imports' || $v == 'forms' || $v == 'subdomains' 
                    || $v == 'debug'  || $v == 'tasks' || $v == 'flayers' || $v == 'statistics' 
                    || $v == 'seo') {
                    $additionally .= '<li ' . ($curmod == $v ? 'class="active"' : '') . '>'
                                  . '<a href="' . $all_configs['prefix'] . $v . '" >' . $modulemenu[$k] . '</a></li>';
                } else {
                    $mainmenu .= '<li ' . ($curmod == $v ? 'class="active"' : '') . '>'
                              . '<a href="' . $all_configs['prefix'] . $v . '" >' . $modulemenu[$k] . '</a></li>';
                }
            }
        }

        /*if($moduleactive[$k] == true){
            if ($v == 'subdomains' || $v == 'admin' || $v == 'debug') {
                $additionally .= '<li ' . ($curmod == $v ? 'class="active"' : '') . '><a href="' . $all_configs['prefix'] . $v . '" >' . $modulemenu[$k] . '</a></li>';
            } else {
                $mainmenu .= '<li '.($curmod == $v ? 'class="active"' : '').'"><a href="'.$all_configs['prefix'].$v.'" >'.$modulemenu[$k].'</a></li>';
            }
        }*/
    }
    if(!empty($additionally))
        $mainmenu .= '
            <li>
                <a href="#"><span class="nav-label">Еще</span><span class="fa arrow"></span> </a>
                <ul class="nav nav-second-level collapse">
                    '.$additionally.'
                </ul>
            </li>';
}
################################################################################

if($curmod){
    $all_configs['curmod'] = $curmod;
    new $curmod($all_configs, $langs['lang'], $langs['def_lang'], $langs['langs']);

    require_once 'classes/infoblock.class.php';
    $infoblock = new Infoblock($all_configs, $langs['lang'], $langs['def_lang'], $langs['langs']);
    $input_html['call_btn'] = get_service('crm/calls')->create_call_form();
}else{
    $all_configs['curmod'] = '';
    $input_html['module_content'] = '<div style="text-align:center;margin-top: 60px;">'.l('manage_main_text').'</div>';
}
/*
// сообщения
$messages = get_messages();
if ($messages && array_key_exists('html', $messages) && array_key_exists('i_count', $messages)
    && array_key_exists('count', $messages) && array_key_exists('count_show', $messages)) {
    $messages_html = $messages['html'];
    $new_mess_count = $messages['i_count'];
    $count_mess = $messages['count'];
    $count_show = $messages['count_show'];
    $input_html['messages'] = '<div class="messages-block">' . $messages_html . '</div>' . (($count_mess > $count_show) ? '<p id="show-more-massages" href="">Еще</p>' : '');
    ################################################################################
    $input['new_mess_count'] = $new_mess_count;
    $input['count_mess'] = $count_mess;
}*/

################################################################################

$input['main'] = l('main');
$input['exit'] = l('exit');
$input['sign_in'] = l('sign_in');
$input['txtemail'] = l('email');
$input['password'] = l('password');

################################################################################
if(isset($all_configs['arrequest'][0]) && !$curmod){
    header("HTTP/1.1 404 Not Found");
    $input_html['module_content'] = l('error404_desc');
    $pre_title = l('error404_title');
}

include 'core_templater.php';


header("Content-Type: text/html; charset=UTF-8");

echo $html;