<?php
/**
* Fon company.
* Fon CMS (GoDB ready)
*
* @category   CMS
* @package    index
* @copyright  Copyright 2010 Fon (http://fon.kiev.ua)
* @license    Fon
* @version    $$
* @author     Anatoliy Khutornoy <ragenoir@gmail.com>
*/

/**
 * inc_[*].php - подключаемые файлы, например, настройки, функции, которые сами по себе ничего не производят.
 * core_[*].php  - обязательный файлы, вызываются каждый раз.
 * module_[*].php  - файлы подключаются как настроено в админке, создают вывод в свою уникальную переменную своего шаблона.
 * html_header[_main].hml - обязательный файл,  который содержит все html-заголовки. В будущем на странице можно выбрать один из таких файлов.
 * html_template_[шаблон_такой_то].html - главный шаблон конкретной страницы.
 * html_inner_[шаблон_такой_то].html - второстепенный (контентный) шаблон, включается в середину главного шаблона.
 * html_footer[_main].html - не обязательный файл, он вообще не нужен.
 *
 */


/**
 * @todo
 * 
 * Страницу ошибок (404) вынести или в карту сайта, или в отдельный модуль.
 * 
 * Если нет физических страниц и присутствует спец. флаг на любом уровне вложенности,
 * то обработать хвостик в модуле, который стоит первым с конца.
 * 
 * Убрать шаблоны по умолчанию.
 * 
 * Сжатие CSS и JS, вставка их в шаблон (в продакшене)
 * 
 */


###################################################################################################

$input=array();
$input_js=array();
$input_css=array();
$input_html=array();

// for modules
$txt=array();

//$input['host'] = $_SERVER['HTTP_HOST'];
//if($_SERVER['HTTP_HOST'] == 'www.goodzir.com.ua'){
//    header('Location: http://goodzir.com.ua/');
//    exit;
//}

//$http_user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'no-user-agent';
//
//$iphone = strpos($http_user_agent,"iPhone"); 
//$ipod = strpos($http_user_agent,"iPod"); 
//$android = strpos($http_user_agent,"Android"); 
//$berry = strpos($http_user_agent,"BlackBerry"); 
//$symbian = strpos($http_user_agent,"SymbOS"); 
//$ipad = strpos($http_user_agent,"iPad"); 

$HTTP_USER_AGENT = strtolower(isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'no-user-agent');
$android = (bool) strpos($HTTP_USER_AGENT, 'android');
$iphone = !$android && ((bool) strpos($HTTP_USER_AGENT, 'iphone') || (bool) strpos($HTTP_USER_AGENT, 'ipod'));
$ipad = !$android && !$iphone && (bool) strpos($HTTP_USER_AGENT, 'ipad');
$ios = $iphone || $ipad;
$mobile = $android || $ios;

$input['ipad'] = $ipad ? 1 : 0;

//if($iphone || $berry || $ipod || $symbian || $android){
//    header('Location: '.$prefix.'m');
//    exit;
//}




include_once 'class_visitors.php';
        
$user_ip = Visitors::get_ip();

$record = $country = null;
$user_ukraine = false;
if (extension_loaded('geoip')) {
    //if (@geoip_record_by_name($user_ip)) {
    $country = @geoip_country_code_by_name($user_ip); 
//    $country = 'RU';
//    $country = 'UA';
    //if (geoip_record_by_name('78.69.38.116')) {
        //$record = geoip_record_by_name($user_ip);
    //}
} else {
//    require_once "./geoip/geoipcity.inc";
//    $gi = geoip_open("./geoip/GeoIPCity.dat", GEOIP_STANDARD);
//    $record = (array)geoip_record_by_addr($gi, $user_ip);
//    var_dump($record);
    die('Geoip fail');
    //$country = $record[];
}
if ($country == 'UA') $user_ukraine = true;

/*$user_kiev = $record && isset($record['city']) ? $record['city'] == 'Kiev' : true;*/
$user_kiev = true; // test
$user_kiev = isset($_GET['test-kiev']) ? (bool)$_GET['test-kiev'] : $user_kiev;
/**
 * @todo Определение поддомена - определение секции.
 */
//$host = explode('.', 'test.com', -1);
//
//$subdomain = $host[0];
//echo $subdomain;

include 'inc_config.php';
require_once 'configs.php';
$configs = Configs::get();



if (!$debug){
    error_reporting(0);
}else{
    error_reporting(E_ALL);
}
header("Content-Type: text/html; charset=UTF-8");

include 'inc_func.php';
include 'inc_func_forms.php';
include 'inc_sources.php';

if($_SERVER['HTTP_HOST'] == 'restore.kiev.ua') {
    redirect301('https://restore.com.ua'.$_SERVER['REQUEST_URI']);
    exit;
}

//настройки глобальные $settings[SETTING]
$settings=$db->query("SELECT name, value FROM {settings}")->vars();
#создание масива из строки УРЛ
$arrequest=parse_slashed_url();

$lower_url = strtolower($_SERVER['REQUEST_URI']);
if($lower_url !== $_SERVER['REQUEST_URI']){
    redirect301($lower_url);
}

$request = $_SERVER['REQUEST_URI'];
if(substr($request, -1, 1) == '/' && $arrequest){
    redirect301(substr($request, 0, strlen($request)-1));
}

include_once 'core_lang.php';
$input = array_merge($input, $template_vars);


Visitors::getInstance()->init_visitors();
$input['ga_user_id'] = Visitors::getInstance()->get_user_id();
$input['ga_user_type'] = Visitors::getInstance()->get_user_type();
$input['visitor_code'] = Visitors::getInstance()->get_code();


include 'inc_func_video.php';
include 'inc_func_fb_director.php';
include 'inc_func_news.php';
include 'inc_func_mod_templater.php';


#Редирект со страницы / (если установлен)
if ($settings['site_index_redirect'] && !isset($arrequest[0])) { 
    redirect301($prefix.$settings['site_index_redirect']);
}

function is_ssl(){
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || 
           (isset($_SERVER['HTTP_HTTPS']) && $_SERVER['HTTP_HTTPS'] == 'on');
}

function ssl_redirect($sll_redirect){
    if($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && strpos($_SERVER['HTTP_HOST'], '192.') === false 
                                              && strpos($_SERVER['HTTP_HOST'], 'git.fonbrand.com') === false){
        if($sll_redirect && !is_ssl()){
            redirect301('https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        }elseif(!$sll_redirect && is_ssl()){
            redirect301('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
        }
    }
}

ssl_redirect(true);

/* comebacker */
$sreferer = !empty($_SERVER['HTTP_REFERER']) ? urldecode(rawurldecode($_SERVER['HTTP_REFERER'])) : '(direct)';
$sref = isset($_COOKIE['s_ref']) ? $_COOKIE['s_ref'] : $sreferer;
if(!isset($_COOKIE['s_ref'])){
    setcookie('s_ref', $sreferer, time() + 86400 * 30, $prefix);
}
if($settings['comebacker_active'] && !isset($_COOKIE['cmb_off'])){
//if(true){
    $cmb_content_id = $db->query("SELECT id FROM {map} WHERE page_type = 2 LIMIT 1")->el();
    $translates = $db->query("SELECT content, lang 
                              FROM {map_strings} WHERE map_id = ?i", array($cmb_content_id), 'assoc:lang');
    $cmb_content = translates_for_page($lang, $def_lang, $translates, array(), true);
    $cmb_content = $cmb_content['content'];
//    $cmb_content = '
//        <div style="margin-top:295px; padding:10px 0 ;" class="clearfix">
//            <div style="width:88%;margin:0 auto;color:#4E4E4E">
//                <p style="font-size:22px;text-align:center;color:#222;">
//                    Нашли дешевле? Вернем 120% от разницы в цене!
//                </p>
//                <p style="font-size:14px;text-align:left">
//                    Отстались вопросы? Давайте мы Вам перезвоним и бесплатно проконсультируем!
//                </p>
//                <p>{-form_3-}</p>
//            </div>
//        </div>
//        <!-- pagebreak -->
//        <div style="padding:15px;">
//            <p style="text-align: center;"><span style="font-size: x-large;">Спасибо!</span></p>
//            <p style="text-align: center;"><span style="font-size: x-large;">Ваша анкета принята!</span></p>
//            <p style="text-align: center;">%time_big% мы наберем Вас</p>
//            <p style="text-align: center;">и предоставим бесплатную консультацию.</p>
//        </div>
//    ';
    $msg_time = get_request_time();
    $cmb_content = str_replace('%time_big%', mb_ucfirst($msg_time[1]), $cmb_content);
    $cmb_content = str_replace('%time%', $msg_time[1], $cmb_content);
    $cmb_content = explode('<!-- pagebreak -->', $cmb_content);
    preg_match_all('/{-form_([0-9]+)-}/', $cmb_content[0], $cmb_form);
    $form = gen_data_form($cmb_form[1][0], array(), true, true, 'div');
    $form_html = '<div class="comebacker_form">'.
                     $form['form']['header'].
                     $form['fields'].
                     '<div class="comebacker_form_submit">'.
                         '<div>'.
                             '<input type="submit" class="btn" value="'.$settings['comebacker_submit'].'">'.
                         '</div>'.
                     '</div>'.
                      $form['form_message'].
                     $form['form']['footer'].
                 '</div>';
    $content = str_replace(array("<span>".$cmb_form[0][0]."</span>","<p>".$cmb_form[0][0]."</p>"), $form_html, $cmb_content[0]);
    $input_html['comebacker'] = 
        '<link type="text/css" rel="stylesheet" href="'.$prefix.'extra/comebacker.css?7" />
        <!-- Modal comebacker -->
        <div id="comebacker_form" class="comebacker_modal">
            <div class="modal-dialog">  
                <div class="modal-content">
                    <div class="modal-body">
                        <div class="comebacker_desc">
                            '.$content.'
                        </div>
                        <div class="comebacker_final_msg data_form_final_message">
                            '.$cmb_content[1].'
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="comebacker_alpha" id="comebacker_alpha"></div>
        <script type="text/javascript" src="'.$prefix.'extra/comebacker.js?7"></script>'
    ;
}

/* / comebacker */

#Редирект со старых страниц (+ префикс для локалки)
$url2redirect = '';
$request_url =  preg_replace('/'.str_replace('/', '\/', $prefix.$url_lang).'/', '/', $_SERVER['REQUEST_URI'], 1);


$url2redirect = $db->query("SELECT link_to FROM {map_glue} WHERE link_from = ? OR link_from = ?", array($request_url, $request_url.'/'), 'el');


if ($url2redirect)
//    echo preg_replace('/\//', $prefix, $url2redirect, 1);
    redirect301(preg_replace('/\//', $prefix.$url_lang, $url2redirect, 1));
### END of redirect

/**
 * Определение карты сайта и текущей страницы.
 * Изменяемый файл, зависит от дизайна.
 *
 * Обязательные корневое меню, $mod[] - весь вывод из таблицы по текущей странице.
 * Подключение и исполнение подключенных на странице модулей.
 */
$error404=true;

/**
 * Содержит вывод результирующей страницы
 */
$html='';
$pre_title='';

$html_header=set_default_file('html_header_', 'default');
$html_body_header=set_default_file('html_body_header_h_', 'default');
$html_template=set_default_file('html_template_', 'default');
$html_inner=set_default_file('html_inner_', 'default');

$submenu_function = 'gen_submenu';

/**
 * генерим карту сайта, заполняем масивы $input*, 
 * получаем 
 *      $mod, 
 *      $url_all_levels, 
 *      $current_url,
 *      $url_all_levels
 * 
 * так же подключается герератор меню
 */
require_once 'core_sitemap.php';



/**
 * Движек вывода и шаблонов
 * Из таблицы - внешнийи внутренний шаблоны
 *
 * Преобразование шаблонов text/html, js, css.
 * Передача переменных в js из php.
 *
 */
require_once 'core_templater.php';

//$html = preg_replace('/\s+/', ' ', $html);
$html = str_replace('> <', '><', $html);
echo $html;

//echo '<!-- ';
//printf(
//    "\n%' 8d:%f",
//    memory_get_peak_usage(true),
//    microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']
//);
//echo '-->';
