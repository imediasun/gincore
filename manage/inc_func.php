<?php

include_once 'inc_func_lang.php';
require_once __DIR__ . '/Core/View.php';

function avatar($avatar_img)
{
    global $all_configs;
    $avatar_path = $all_configs['configs']['users-avatars-path'];
    $img = $avatar_path . $avatar_img;
    $avatar = '';
    if (is_file($all_configs['path'] . $img)) {
        $avatar = $img;
    } else {
        $avatar = $avatar_path . 'default.png';
    }
    return $all_configs['prefix'] . $avatar;
}

function viewCurrencySuppliers($show = 'viewName')
{
    global $all_configs;
    return $all_configs['configs']['currencies'][$all_configs['settings']['currency_suppliers_orders']][$show];
}

function viewCurrency($show = 'viewName')
{
    global $all_configs;
    return $all_configs['configs']['currencies'][$all_configs['settings']['currency_orders']][$show];
}

function db()
{
    global $all_configs;
    return $all_configs['db'];
}

function get_langs()
{
    global $all_configs, $dbcfg;
    $return = array();
    $langs = $all_configs['db']->query("SELECT name, url, `default` FROM {langs} WHERE state = 1")->assoc('url');

    foreach ($langs as $lnge) {
        if ($lnge['default']) {
            $return['def_lang'] = $lnge['url'];
            break;
        }
    }

    $return['langs'] = $langs;

    $cotnent_lang_cookie = $dbcfg['_prefix'] . 'content_lang';
    if (!isset($_COOKIE[$cotnent_lang_cookie])) {
        $return['lang'] = $return['def_lang'];
        setcookie($cotnent_lang_cookie, $return['def_lang'], time() + 3600 * 24 * 30, $all_configs['prefix']);
    } else {
        $return['lang'] = isset($_COOKIE[$cotnent_lang_cookie]) ? $_COOKIE[$cotnent_lang_cookie] : $this->def_lang;
    }
    return $return;
}

function clear_empty_inarray($array)
{
    $ret_arr = array();
    foreach ($array as $val) {
        $val = preg_replace('/[^0-9a-z-A-Z-_?]/', '', urldecode($val)); //trim тут не нужен?
        if (empty($val)) {
            continue;
        }
        if (strpos($val, '?') !== false) {
            $ret_arr[] = strstr($val, '?', true);
        } else {
            $ret_arr[] = $val;
        }
    }
    return $ret_arr;
}

function quote_smart($value)
{
    // если magic_quotes_gpc включена - используем stripslashes
    if (get_magic_quotes_gpc()) {
        $value = stripslashes($value);
    }
    // Если переменная - число, то экранировать её не нужно
    // если нет - то окружем её кавычками, и экранируем
    if (!is_numeric($value)) {
        $value = "'" . mysql_escape_string($value) . "'"; // if DB error mysql_real_escape_string()
    }
    return $value;
}

function gen_list_select($arr, $name, $selected)
{

    $out = '<select name="' . $name . '">';
    foreach ($arr AS $k => $v) {
        $out .= '<option value="' . $k . '" ' . ($selected == $k ? 'selected' : '') . '>' . $v . '</option>';
    }
    $out .= '</select>';

    return $out;
}

################################################################################
//photos

function resize_photo($file, $width, $height, $color_fill = array(255, 255, 255))
{
    $size = GetImageSize($file);

    $realimg = imagecreatetruecolor($width, $height);

    if ($size['mime'] == 'image/png') {
        $userimg = imagecreatefrompng($file);
        imagealphablending($userimg, false);
        imagesavealpha($userimg, true);

        imagealphablending($realimg, false);
        imagesavealpha($realimg, true);

        $color = imagecolorallocatealpha($realimg, $color_fill[0], $color_fill[1], $color_fill[2], 127);
    }
    if ($size['mime'] == 'image/jpeg') {
        $userimg = imagecreatefromjpeg($file);
        $color = imagecolorallocate($realimg, $color_fill[0], $color_fill[1], $color_fill[2]); //r g b
    }
    if ($size['mime'] == 'image/gif') {
        $userimg = imagecreatefromgif($file);
        $color = imagecolorallocate($realimg, $color_fill[0], $color_fill[1], $color_fill[2]); //r g b
    }


    imagefill($realimg, 0, 0, $color);


    $uw = $size[0];
    $uh = $size[1];
    if ($uw >= $uh) {
        //ширина больше высоты, значит надо ширину = $width и домазать сверху и снизу по кусочку.
        $koef = $uw / $uh;
        $rh = round($width / $koef);
        $whole_y = $height - $rh;
        $ry = round($whole_y / 2);
        imagecopyresampled($realimg, $userimg, 0, $ry, 0, 0, $width, $rh, $uw, $uh);
    } else {
        $koef = $uw / $uh;
        $rw = round($koef * $height);
        $whole_x = $width - $rw;
        $rx = round($whole_x / 2);
        imagecopyresampled($realimg, $userimg, $rx, 0, 0, 0, $rw, $height, $uw, $uh);
    }

    if ($size['mime'] == 'image/png') {
        imagepng($realimg, $file);
    }
    if ($size['mime'] == 'image/jpeg') {
        imagejpeg($realimg, $file, 95);
    }
    if ($size['mime'] == 'image/gif') {
        imagegif($realimg, $file);
    }
    imagedestroy($realimg);
    imagedestroy($userimg);
}

//\\\\\\\\\\\\\\\\\\\//////////////////
// сохраняет пнг
function resize_photo_png($file, $width, $height, $color_fill = array(255, 255, 255))
{
    $size = GetImageSize($file);

    $realimg = imagecreatetruecolor($width, $height);
    imagealphablending($realimg, false);
    imagesavealpha($realimg, true);

    if ($size['mime'] == 'image/png') {
        $userimg = imagecreatefrompng($file);
    }
    if ($size['mime'] == 'image/jpeg') {
        $userimg = imagecreatefromjpeg($file);
    }
    if ($size['mime'] == 'image/gif') {
        $userimg = imagecreatefromgif($file);
    }

    imagealphablending($userimg, false);
    imagesavealpha($userimg, true);
    $color = imagecolorallocatealpha($realimg, $color_fill[0], $color_fill[1], $color_fill[2], 127);

    imagefill($realimg, 0, 0, $color);


    $uw = $size[0];
    $uh = $size[1];
    if ($uw >= $uh) {
        //ширина больше высоты, значит надо ширину = $width и домазать сверху и снизу по кусочку.
        $koef = $uw / $uh;
        $rh = round($width / $koef);
        $whole_y = $height - $rh;
        $ry = round($whole_y / 2);
        imagecopyresampled($realimg, $userimg, 0, $ry, 0, 0, $width, $rh, $uw, $uh);
    } else {
        $koef = $uw / $uh;
        $rw = round($koef * $height);
        $whole_x = $width - $rw;
        $rx = round($whole_x / 2);
        imagecopyresampled($realimg, $userimg, $rx, 0, 0, 0, $rw, $height, $uw, $uh);
    }

    imagepng($realimg, $file);

    imagedestroy($realimg);
    imagedestroy($userimg);
}

function resample_photo($file, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h)
{
    $size = getimagesize($file);

    $realimg = imagecreatetruecolor($dst_w, $dst_h);

    if ($size['mime'] == 'image/png') {
        $userimg = imagecreatefrompng($file);
        imagealphablending($userimg, false);
        imagesavealpha($userimg, true);

        imagealphablending($realimg, false);
        imagesavealpha($realimg, true);
    }
    if ($size['mime'] == 'image/jpeg') {
        $userimg = imagecreatefromjpeg($file);
    }
    if ($size['mime'] == 'image/gif') {
        $userimg = imagecreatefromgif($file);
    }

    imagecopyresampled($realimg, $userimg, 0, 0, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);

    if ($size['mime'] == 'image/png') {
        imagepng($realimg, $file);
    }
    if ($size['mime'] == 'image/jpeg') {
        imagejpeg($realimg, $file, 95);
    }
    if ($size['mime'] == 'image/gif') {
        imagegif($realimg, $file);
    }
    imagedestroy($realimg);
    imagedestroy($userimg);
}

################################################################################

function send_mail($to, $sbj, $msgtxt)
{
    GLOBAL $all_configs;

    $subject = "=?UTF-8?B?" . base64_encode($sbj) . "?=\n";

    $ip = ''; // ?

    $message = $msgtxt . "<br><br>\r\n";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8 \r\n";
    $headers .= "X-originating-IP: " . $ip . "\r\n";
    $headers .= 'From: ' . $all_configs['settings']['site_name'] . ' <' . $all_configs['settings']['content_email'] . '>' . "\r\n";

    return (bool)(mail($to, $subject, $message, $headers));
}

function mb_ucfirst($str)
{
    $fc = mb_strtoupper(mb_substr($str, 0, 1));
    return $fc . mb_substr($str, 1);
}

// отправка сообщений через turbosms
function send_sms($phone, $message, $sender = null)
{
    global $all_configs;

    include_once $all_configs['sitepath'] . 'shop/turbosms.class.php';

    if (is_null($sender)) {
        $from = isset($all_configs['settings']['turbosms-from']) ? trim($all_configs['settings']['turbosms-from']) : '';
    } else {
        $from = trim($sender);
    }
    $login = isset($all_configs['settings']['turbosms-login']) ? trim($all_configs['settings']['turbosms-login']) : '';
    $password = isset($all_configs['settings']['turbosms-password']) ? trim($all_configs['settings']['turbosms-password']) : '';

    $turbosms = new turbosms($login, $password);
    $result = array_values((array)$turbosms->send($from, '+' . $phone, $message));

    $result = is_array($result) && isset($result[0]) ? $result[0] : '';

    return array(
        'state' => is_array($result),
        'msg' => is_array($result) ? current($result) : $result
    );
}

function gen_level($page)
{
    global $link, $all_configs;
    $row = $all_configs['db']->query("SELECT id, url, parent FROM {map} WHERE id = ?i", array($page), 'row');
    $link[] = $row['url'];
    if ($row['parent']) {
        gen_level($row['parent']);
    }
}

function gen_full_link($page_id)
{
    global $link;

    $link = array();

    gen_level($page_id);

    krsort($link);

    return implode("/", $link);
}

function getMapIdByProductId($product_id)
{
    global $all_configs;
    return $all_configs['db']->query("SELECT id FROM {map} WHERE category_id = ?i", array($product_id), 'el');
}

function getUsernameById($id)
{
    global $all_configs;
    $user = $all_configs['db']->query("SELECT `fio`, `login` FROM {users} WHERE id = ?i", array($id), 'row');
    return ($user['fio'] ? $user['fio'] : $user['login']);
}

/**
 * Правильный постоянный редирект на УРЛ.
 *
 * @param string $url2redirect
 * @return unknown
 */
function redirect($url2redirect, $permanently = true)
{
    if ($permanently) {
        header('HTTP/1.1 301 Moved Permanently');
    }
    header('Location: ' . $url2redirect);
    echo '
        <!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">
        <html><head>
        <title>301 Moved Permanently</title>
        </head><body>
        <h1>Moved Permanently</h1>
        <p>The document has moved <a href="' . $url2redirect . '">here</a>.</p>
        <hr>
        <address>Server at ' . $_SERVER["HTTP_HOST"] . '</address>
        </body></html>
    ';
    exit;
}

function count_on_page()
{
    global $all_configs, $cfg;

    if (array_key_exists($cfg['tbl'] . $all_configs['configs']['count-on-page'], $_COOKIE)
        && array_key_exists($_COOKIE[$cfg['tbl'] . $all_configs['configs']['count-on-page']],
            $all_configs['configs']['manage-count-on-page'])
    ) {

        $count = $_COOKIE[$cfg['tbl'] . $all_configs['configs']['count-on-page']];
    } else {
        reset($all_configs['configs']['manage-count-on-page']);
        next($all_configs['configs']['manage-count-on-page']);
        $count = key($all_configs['configs']['manage-count-on-page']);
        reset($all_configs['configs']['manage-count-on-page']);
    }

    return $count;
}

/**
 * get real ip
 * */
function get_ip()
{
    $ip = '';
    if (isset($_SERVER['HTTP_X_REAL_IP'])) {
        $ip = $_SERVER['HTTP_X_REAL_IP'];
    } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } elseif (isset($_SERVER['REMOTE_ADDR'])) {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    return $ip;
}

/**
 * Выводит дату как в gmail
 */
function do_nice_date($date_input, $short_format = true, $time = true, $lang = 0, $wrap_title = false)
{
    global $manage_lang;

    if (is_string($date_input)) {
        $date = strtotime($date_input);
    } else {
        $date = $date_input;
    }

    if (!$date || $date == 0) {
        return '';
    }

    $date_mounth = date("m", $date);

    $months = array(
        'ru' => array(
            '01' => 'января',
            '02' => 'февраля',
            '03' => 'марта',
            '04' => 'апреля',
            '05' => 'мая',
            '06' => 'июня',
            '07' => 'июля',
            '08' => 'августа',
            '09' => 'сентября',
            '10' => 'октября',
            '11' => 'ноября',
            '12' => 'декабря',
        ),
        'uk' => array(
            '01' => 'січня',
            '02' => 'лютого',
            '03' => 'березня',
            '04' => 'квітня',
            '05' => 'травня',
            '06' => 'червня',
            '07' => 'липня',
            '08' => 'серпня',
            '09' => 'вересня',
            '10' => 'жовтня',
            '11' => 'листопада',
            '12' => 'грудня',
        ),
        'en' => array(
            '01' => 'January',
            '02' => 'February',
            '03' => 'March',
            '04' => 'April',
            '05' => 'May',
            '06' => 'June',
            '07' => 'July',
            '08' => 'Augest',
            '09' => 'September',
            '10' => 'October',
            '11' => 'November',
            '12' => 'December',
        ),
    );

    $months_short = array(
        'ru' => array(
            '01' => 'янв',
            '02' => 'фев',
            '03' => 'мар',
            '04' => 'апр',
            '05' => 'мая',
            '06' => 'июн',
            '07' => 'июл',
            '08' => 'авг',
            '09' => 'сен',
            '10' => 'окт',
            '11' => 'ноя',
            '12' => 'дек'
        ),
        'uk' => array(
            '01' => 'січ',
            '02' => 'лют',
            '03' => 'бер',
            '04' => 'кві',
            '05' => 'тра',
            '06' => 'чер',
            '07' => 'лип',
            '08' => 'сер',
            '09' => 'вер',
            '10' => 'жов',
            '11' => 'лис',
            '12' => 'гру',
        ),
        'en' => array(
            '01' => 'Jan',
            '02' => 'Feb',
            '03' => 'Mar',
            '04' => 'Apr',
            '05' => 'May',
            '06' => 'Jun',
            '07' => 'Jul',
            '08' => 'Aug',
            '09' => 'Sep',
            '10' => 'Oct',
            '11' => 'Nov',
            '12' => 'Dec'
        ),
    );

    $years_short = array(
        'ru' => 'г',
        'uk' => 'р',
        'en' => '',
    );

    $today = array(
        'ru' => 'Сегодня',
        'uk' => 'Сьогодні',
        'en' => 'Today',
    );

    if ($short_format) {
        //текущий день, месяц и год
        if (date("j.n.Y", $date) == date("j.n.Y")) {
            if ($time == true) {
                $out = date("G:i", $date);
            } else {
                $out = $today[$manage_lang];
            }
            // текущий год и не сегодня
        } elseif (date("Y", $date) == date("Y") && date("j.n", $date) != date("j.n")) {
            $out = date("j", $date) . '&nbsp;' . $months_short[$manage_lang][$date_mounth] . '';
            //не текущий год
        } else {
            $out = date("d.m.y", $date);
        }
    } else {
        if ($time == true) {
            $out = date("j ",
                    $date) . $months[$manage_lang][$date_mounth] . date(" Y {$years_short[$manage_lang]}, G:i", $date);
        } else {
            $out = date("j ", $date) . $months[$manage_lang][$date_mounth] . date(" Y {$years_short[$manage_lang]}",
                    $date);
        }
    }

    if ($wrap_title) {
        return '<span title="' . $date_input . '">' . $out . '</span>';
    } else {
        return $out;
    }
}

/**
 * autocomplete
 * @param        $db
 * @param string $table
 * @param bool   $show_categories
 * @param int    $object_id
 * @param int    $i
 * @param string $class
 * @param string $class_select
 * @param string $function
 * @param bool   $multi
 * @param bool   $anyway
 * @param string $m
 * @param bool   $no_clear_if_null
 * @param string $placeholder
 * @param array  $add_btn
 * @param bool   $disabled
 * @param string $add_attr
 * @return string
 */
function typeahead(
    $db,
    $table = 'goods',
    $show_categories = false,
    $object_id = 0,
    $i = 1,
    $class = 'input-medium',
    $class_select = 'input-small',
    $function = '',
    $multi = false,
    $anyway = false,
    $m = '',
    $no_clear_if_null = false,
    $placeholder = '',
    $add_btn = array(),
    $disabled = false,
    $add_attr = ''
) {

    if (empty($placeholder)) {
        $placeholder = l('Введите');
    }
    $iterator = $i;

    $object_name = '';
    if ($object_id > 0) {
        if ($table == 'locations') {
            $object = $db->query('SELECT GROUP_CONCAT(w.title, " ", l.location) as title
                FROM {warehouses_locations} as l, {warehouses} as w
                WHERE l.id=?i AND w.id=l.wh_id', array(intval($object_id)))->row();
        } else {
            $tbl = $table;
            $tbl_where = '';
            if ($table == 'goods-goods' || $table == 'goods-service' || $table == 'new-goods') {
                $tbl_where = ' AND avail = 1';
                $tbl = 'goods';
            }
            if ($table == 'categories-last' || $table == 'categories-goods') {
                $tbl_where = ' AND (avail = 1 OR deleted=1)';
                $tbl = 'categories';
            }
            $object = $db->query('SELECT * FROM {' . $tbl . '}
                                  WHERE id = ?i ?q', array(intval($object_id), $tbl_where))->row();
        }
        if ($object) {
            $object_name = array_key_exists('title', $object) ? htmlspecialchars($object['title']) :
                (array_key_exists('fio', $object) ? get_user_name($object) : '');
        }
    }
    $categories = $db->query('SELECT title, url, id FROM {categories}
                WHERE avail=1 AND parent_id=0 GROUP BY title ORDER BY title')->assoc();

    $template = $add_btn ? 'typeahead_with_btn' : 'typeahead';
    $view = new View();
    return $view->renderFile("inc_func/{$template}", array(
        'add_btn' => $add_btn,
        'show_categories' => $show_categories,
        'categories' => isset($categories) ? $categories : array(),
        'disabled' => $disabled,
        'class_select' => $class_select,
        'iterator' => $iterator,
        'no_clear_if_null' => $no_clear_if_null,
        'table' => $table,
        'multi' => $multi,
        'm' => $m,
        'placeholder' => $placeholder,
        'add_attr' => $add_attr,
        'anyway' => $anyway,
        'function' => $function,
        'object_name' => $object_name,
        'object_id' => $object_id,
        'class' => $class
    ));
}

function input_phone_mask_attr()
{
    global $all_configs;
    $phone_conf = $all_configs['configs']['countries'][$all_configs['settings']['country']]['phone'];
    if (!empty($phone_conf['mask'])) {
        return ' data-phone_mask="' . $phone_conf['mask'] . '" ';
    } else {
        return '';
    }
}

function client_double_typeahead($id = null, $callbacks = '')
{
    global $all_configs;
    $client = $all_configs['db']->query("SELECT * FROM {clients} WHERE id = ?i", array($id), 'row');

    $input_id = 'typeahead-double-' . microtime(true) . rand(1, 99999);
    $value_field = '<input class="typeahead-double-value" id="' . $input_id . '" type="hidden" name="client_id" value="' . ($id ?: '') . '">';
    $phone_field = '<input' . input_phone_mask_attr() . ' data-function="' . $callbacks . '" data-table="clients" data-field="phone" 
                     class="form-control typeahead-double" data-id="' . $input_id . '" type="text" 
                     placeholder="' . l('Телефон') . '" name="client_phone" required
                     value="' . ($client ? $client['phone'] : $client['phone']) . '">';
    $fio_field = '<input data-function="' . $callbacks . '" data-table="clients" data-field="fio" 
                   class="form-control typeahead-double" type="text"  data-id="' . $input_id . '" 
                   placeholder="' . l('ФИО') . '" name="client_fio" required
                   value="' . ($client ? $client['fio'] : $client['fio']) . '">';
    $id = '<input data-function="' . $callbacks . '" data-table="clients" data-field="id"
                   class="form-control typeahead-double" type="hidden"  data-id="' . $input_id . '" 
                    name="clients" 
                   value="' . ($client ? $client['id'] : $client['id']) . '">';
    return array(
        'phone' => $value_field . $phone_field,
        'fio' => $fio_field,
        'id' => $id,
        'tag_id' => $client['tag_id']
    );
}

/**
 * Выводим цену
 * */
function show_price($price, $zero = 2, $space = '', $delimiter = '.', $course = 100, $currencies = array(), $count = 1)
{
    $price_html = '';
    $price = (array)$price;
    $currencies = (array)$currencies;
    $last = end($price);

    foreach ($price as $c => $p) {
        $p = ((($p * ($course / 100)) / 100) * $count);
        $return = number_format($p, $zero, $delimiter, $space);
        $price_html .= str_replace(array(' ', '\xA0'), '&nbsp;', trim($return));
        $price_html .= array_key_exists($c, $currencies) && array_key_exists('shortName', $currencies[$c])
            ? '&nbsp;' . htmlspecialchars(($currencies[$c]['shortName'])) : '';
        $price_html .= count($price) > 1 && $p != $last ? '; ' : '';
    }

    return $price_html;
}

/**
 * страниная навигация
 * @param        $count_page
 * @param        $count
 * @param string $hash
 * @param null   $a_url
 * @return string
 */
function page_block($count_page, $count, $hash = '', $a_url = null)
{
    $count_page = ceil($count_page);

    $a_url = $a_url === null || !is_array($a_url) ? $_GET : $a_url;
    $view = new View();
    return $view->renderFile('inc_func/page_block', array(
        'count_page' => $count_page,
        'count' => $count,
        'hash' => $hash,
        'a_url' => $a_url
    ));
}

function check_page($count, $cur = 1, $need = 1)
{
    $ar = array();

    if ($cur == 1 || empty($cur)) {
        for ($i = 1; $i < 2 + $need; $i++) {
            $ar[] = $i;
        }
        if (2 + $need <= $count) {
            if ($count > 3) {
                $ar[] = '...';
            }
            $ar[] = $count;
        }

        return $ar;
    }
    if ($cur >= $need + 2) {
        $ar[] = 1;
        if ($cur > 3) {
            $ar[] = '...';
        }
    }


    for ($i = 1; $i <= $count; $i++) {
        if ($cur + $need >= $i && $cur <= $i + $need) {
            $ar[] = $i;
            continue;
        }
    }
    if ($cur + $need < $count) {
        if ($cur < $count - 2) {
            $ar[] = '...';
        }
        $ar[] = $count;
    }

    return $ar;
}

/**
 * for page_block
 * @param null $count
 * @return string
 */
function select_count_on_page($count = null)
{
    global $all_configs;

    $count = $count === null ? count_on_page() : $count;

    $view = new View($all_configs);
    return $view->renderFile('inc_func/select_count_on_page', array(
        'count' => $count,
    ));
}

// возвращает курс $currency к основной валюте 
function getCourse($currency, $convert_to_nocents = false)
{
    global $all_configs;
    $c = $all_configs['db']->query("SELECT course FROM {cashboxes_courses} "
        . "WHERE currency = ?i", array($currency), 'el');
    $c = (float)number_format($c, 2, '.', '');
    return $convert_to_nocents ? $c / 100 : $c;
}

/**
 * warehouses amount
 * */
function cost_of($warehouses, $settings, $suppliers_orders)
{
    $cso = $settings['currency_suppliers_orders'];
    $cco = $settings['currency_orders'];
    $c = $suppliers_orders->currencies;
    $cur_price = $price = $count = 0;
    $sum = array();

    if ($warehouses && count($warehouses) > 0) {
        foreach ($warehouses as $warehouse) {
            if ($warehouse['consider_all'] == 1) {
                $cur_price += show_price($warehouse['all_amount'], 2, '', '.', getCourse($cso));
                $price += $warehouse['all_amount'];
                $count += intval($warehouse['sum_qty']);

                $sum[$cso] = array_key_exists($cso,
                    $sum) ? $sum[$cso] + $warehouse['all_amount'] : $warehouse['all_amount'];
            }
        }
    }

    ksort($sum);

    return array(
        'amount' => $sum,
        'cur_price' => show_price(($cur_price * 100), 2, ' ') . (array_key_exists($cco,
                $c) ? ' ' . $c[$cco]['shortName'] : ''),
        'html' => show_price($price, 2, ' ') . (array_key_exists($cso, $c) ? ' ' . $c[$cso]['shortName'] : ''),
        'count' => $count,
    );
}


function map_array_addkey_to_string(&$el, $key, $prefix)
{
    $el = $prefix . '[' . $key . ']=' . $el;
}

/**
 * _GET to string
 * */
function get_to_string($except = array(), $get = null)
{
    if ($get === null) {
        $get = $_GET;
    }
    $except = (array)$except;
    $queryString = array();

    foreach ($get as $key => $value) {
        if (!empty($value) && $key != 'act' && !in_array($key, $except)) {
            if (!is_array($value)) {
                $queryString[] = $key . '=' . urlencode($value);
            } else {
                array_walk($value, 'map_array_addkey_to_string', $key);
                $queryString[] = implode('&', $value);

            }
        }
    }
    return implode('&', $queryString);
}

/*
 * type = 1 options
 * type = 2 li drug and drop
 * type = 3 li
 * */
function build_array_tree($objects, $selected = array(), $type = 1)
{
    $new = array();

    foreach ($objects as $a) {

        if (!array_key_exists('parent_id', $a)) {
            return display_array_tree($objects, $selected, $type, 0, '');
        }

        $new[$a['parent_id']][$a['id']] = $a;
    }

    $tree = count($new) > 0 ? createTree($new, $new[0]) : array();

    return display_array_tree($tree, $selected, $type, 0, '');
}

function display_array_tree($array, $selected = array(), $type = 1, $index = 0, $tree = '')
{
    global $all_configs;

    $space = "";
    if ($type == 1) {
        for ($i = 0; $i < $index; $i++) {
            $space .= ' - ';
        }
    }
    if ($type == 2) {
        $tree .= '<ol class="dd-list nav nav-list">';
    }
    if ($type == 3) {
        $tree .= '<ul class="nav nav-list ' . ($index ? 'm-l-22 border-left' : '') . '">';
    }

    if (gettype($array) == "array") {
        $index++;
        $selected = (array)$selected;

        while (list ($x, $tmp) = each($array)) {

            $arrow = '';
            if (array_key_exists('arrow', $tmp)) {
                $arrow = ($tmp['arrow'] == 2 ? ' &#8593; ' : ' &#8595; ');
            }

            $tmp['title'] = array_key_exists('title', $tmp) ? $tmp['title'] : (array_key_exists('name',
                $tmp) ? $tmp['name'] : '');

            if ($type == 1) {
                $tree .= '<option ' . (is_array($selected) && in_array($tmp['id'], $selected) ? 'selected' : '');
                $tree .= ' value="' . $tmp['id'] . '">' . $space . $arrow . htmlspecialchars($tmp['title']) . '</option>';
            }
            if ($type == 2) {
                if(empty($recycledBin)) {
                    require_once __DIR__.'/Models/Categories.php';
                    $Categories = new MCategories($all_configs);
                    $recycledBin = $Categories->getRecycleBin();
                }
                $tree .= '<li class=" ' . (is_array($selected) && in_array($tmp['id'], $selected) ? 'active' : '');
                $tree .= ' dd-item ui-state-default" data-id="' . $tmp['id'] . '">';
                $tree .= '<div class="dd-handle"><i class="icon-move glyphicon glyphicon-move"></i></div>';
                $tree .= '<a href="' . $all_configs['prefix'] . 'categories/create/' . $tmp['id'] . '" class="' . (($tmp['id'] == $recycledBin['id']) ? 'recyclebin' : '') . '">';
                $tree .= '<span class="category-title">' . htmlspecialchars($tmp['title']) . '</span>';
                if ($tmp['id'] != $recycledBin['id']) {
                    $tree .= '<i class="js-delete-category fa fa-times" aria-hidden="true"></i>';
                } else {
                    $tree .= InfoPopover::getInstance()->createQuestion('l_recycled_bin_info', 'js-recycle-bin');
                }
                $tree .= '</a>';
            }
            if ($type == 3) {
                $tree .= '<li class="' . (is_array($selected) && in_array($tmp['id'], $selected) ? 'active' : '');
                $tree .= ($tmp['parent_id'] == 0 ? ' first-multi-column' : '') . '">';
                $tree .= '<a class="object_id" data-o_id="' . $tmp['id'] . '" href="' . '">';
                $tree .= $tmp['id'] . '. ' . htmlspecialchars($tmp['title']) . '</a>';
            }
            if (array_key_exists('child', $tmp)) {
                $tree = display_array_tree($tmp['child'], $selected, $type, $index, $tree);
            } else {
                if ($type == 2 || $type == 3) {
                    $tree .= '</li>';
                }
            }
        }
    }

    if ($type == 2) {
        $tree .= '</ol>';
    }
    if ($type == 3) {
        $tree .= '</ul>';
    }

    return $tree;
}

function createTree(&$list, $parent)
{
    $tree = array();

    if (is_array($parent) && count($parent) > 0) {
        foreach ($parent as $k => $l) {
            if (isset($list[$l['id']])) {
                $l['child'] = createTree($list, $list[$l['id']]);
            }
            $tree[] = $l;
        }
    }

    return $tree;
}

/**
 * get user from array
 * */
function get_user_name($user, $p = '', $link = false, $admin = false)
{
    global $prefix;

    $return = '';

    if (isset($user[$p . 'fio']) && mb_strlen(trim($user[$p . 'fio']), 'UTF-8') > 0) {
        $return = trim($user[$p . 'fio']);
    } elseif (isset($user[$p . 'login']) && mb_strlen(trim($user[$p . 'login']), 'UTF-8') > 0) {
        $return = trim($user[$p . 'login']);
    } elseif (isset($user[$p . 'name']) && mb_strlen(trim($user[$p . 'name']), 'UTF-8') > 0) {
        $return = trim($user[$p . 'name']);
    } elseif (isset($user[$p . 'email']) && mb_strlen(trim($user[$p . 'email']), 'UTF-8') > 0) {
        $return = trim($user[$p . 'email']);
    } elseif (isset($user[$p . 'title']) && mb_strlen(trim($user[$p . 'title']), 'UTF-8') > 0) {
        $return = trim($user[$p . 'title']);
    }

    if ($link == true && isset($user['id']) && $user['id'] > 0) {
        if ($admin == false) {
            $return = '<a title="' . htmlspecialchars($return) . '" href="' . $prefix . 'clients/create/' . $user['id'] . '">' . htmlspecialchars($return) . '</a>';
        } else {
            $return = '<a title="' . htmlspecialchars($return) . '" href="' . $prefix . 'users">' . htmlspecialchars($return) . '</a>';
        }
    }

    return $return;
}

/**
 * recursive get categories
 */
function get_childs_categories($db, $id, $data = array())
{
    if ($id > 0) {
        array_push($data, $id);

        $parents = $db->query('SELECT cg.id FROM {categories} as cg WHERE cg.parent_id=?i', array($id))->assoc();

        if ($parents) {
            foreach ($parents as $parent) {
                if ($parent['id'] > 0) {
                    $data = get_childs_categories($db, $parent['id'], $data);
                }
            }
        }
    }

    return $data;
}

/**
 * выводим заказ (строчку заказа)
 * */
function display_client_order($order)
{
    global $all_configs;

    $status = '<span class="muted">' . l('Сообщите менеджеру') . '</span>';
    if (array_key_exists($order['status'], $all_configs['configs']['order-status'])) {
        $status_name = $all_configs['configs']['order-status'][$order['status']]['name'];
        $status_color = $all_configs['configs']['order-status'][$order['status']]['color'];
        $status = '<span style="color:#' . $status_color . '">' . $status_name . '</span>';
    }

    $ordered = '';
    if ($order['status'] == $all_configs['configs']['order-status-waits'] && count($order['goods']) > 0) {
        $ordered = str_repeat(' <i class="fa fa-minus-circle text-danger pull-right"></i> ',
            count($order['goods']) - count($order['finish']));
        if (count($order['finish']) > 0) {
            $ordered .= str_repeat(' <i class="fa fa-plus-circle text-success pull-right"></i> ',
                count($order['finish']));
        }
    }

    $color = preg_match('/^#[a-f0-9]{6}$/i', trim($order['color'])) ? trim($order['color']) : '#000000';
    $accepted = mb_strlen($order['courier'],
        'UTF-8') > 0 ? '<i style="color:' . $color . ';" title="' . l('Курьер забрал устройство у клиента') . '" class="fa fa-truck"></i> ' : '';
    $accepted .= $order['np_accept'] == 1 ? '<i title="' . l('Принято через почту') . '" class="fa fa-suitcase text-danger"></i> ' :
        '<i style="color:' . $color . ';" title="' . l('Принято в') . ' ' . htmlspecialchars($order['aw_wh_title']) . '" class="' . htmlspecialchars($order['icon']) . '"></i> ';

    $get = '?' . get_to_string($_GET);

    return '<tr class="remove-marked-object">'
    . '<td class="floatleft">' .
    ($all_configs['oRole']->hasPrivilege('edit-clients-orders') || $all_configs['oRole']->hasPrivilege('show-clients-orders') ?
        '<a href="' . $all_configs['prefix'] . 'orders/create/' . $order['order_id'] . $get . '">&nbsp;' . $order['order_id'] . '</a> ' .
        '<a class="fa fa-edit" href="' . $all_configs['prefix'] . 'orders/create/' . $order['order_id'] . $get . '"></a> '
        : '')
    . show_marked($order['order_id'], 'co', $order['m_id'])
    . '<i class="glyphicon glyphicon-move icon-move cursor-pointer" data-o_id="' . $order['order_id'] . '" onclick="alert_box(this, false, \'stock_move-order\', undefined, undefined, \'messages.php\')" title="' . l('Переместить заказ') . '"></i></td>'
    . '<td>' /* . $order['order_id'] */ . timerout($order['order_id']) . '</td>'
    . '<td><span title="' . do_nice_date($order['date'], false) . '">' . do_nice_date($order['date']) . '</span></td>'
    . '<td>' . get_user_name($order, 'a_') . '</td>'
    . '<td>' . (($order['manager'] == 0 && $all_configs['oRole']->hasPrivilege('edit-clients-orders')) ?
        '<form method="post" action="' . $all_configs['prefix'] . 'orders/create/' . $order['order_id'] . '">'
        . '<input name="accept-manager" type="submit" class="btn btn-accept" value="' . l('Взять заказ') . '" /><input type="hidden" name="id" value="' . $order['order_id'] . '" />'
        . '</form>'
        : get_user_name($order, 'h_')) . '</td>'
    . '<td>' . $status . $ordered . '</td>'
    . '<td>' . htmlspecialchars($order['product']) . ' ' . htmlspecialchars($order['note']) . '</td>'

    . ($all_configs['oRole']->hasPrivilege('edit-clients-orders') ?
        '<td class="' . ($order['discount'] > 0
            ? 'text-danger' : '') . '">' . ($order['sum'] / 100) . '</td>'
        . '<td>' . ($order['sum_paid'] / 100) . '</td>'
        : (($order['sum'] == $order['sum_paid'] && $order['sum'] > 0) ? '<td>да</td>' : '<td></td>'))

    . '<td>' . $accepted . htmlspecialchars($order['o_fio']) . '</td>'
    . '<td>' . $order['o_phone'] . '</td>'
    . '<td' . ($order['urgent'] == 1 ? ' class="text-danger">' . l('Срочно') . ' ' : '>' . l('Не срочно')) . '</td>'
    . '<td>' . htmlspecialchars($order['wh_title']) . ' ' . htmlspecialchars($order['location']) . '</td></tr>';
}

/*
 * ссылка на печать
 * */
function print_link(
    $object_id,
    $act,
    $name = '<i class="cursor-pointer fa fa-print"></i>',
    $only_link = false,
    $addition = ''
) {
    global $all_configs;

    if (is_array($object_id)) {
        $object_id = implode(',', $object_id);
    }

    if ($object_id) {
        $url = $all_configs['prefix'] . 'print.php?act=' . $act . '&object_id=' . $object_id . $addition;
        if (!$only_link) {
            return '<a title="print ' . $act . '" target="_blank" href="' . $url . '">' . $name . '</a>';
        } else {
            return $url;
        }
    }
}

function full_pathinfo($path_file)
{
    $path_file = strtr($path_file, array('\\' => '/'));

    preg_match("~[^/]+$~", $path_file, $file);
    preg_match("~([^/]+)[.$]+(.*)~", $path_file, $file_ext);
    preg_match("~(.*)[/$]+~", $path_file, $dirname);

    return array(
        'dirname' => isset($dirname[1]) ? $dirname[1] : '',
        'basename' => isset($file['0']) ? $file[0] : '',
        'extension' => (isset($file_ext[2])) ? $file_ext[2] : false,
        'filename' => (isset($file_ext[1])) ? $file_ext[1] : (isset($file['0']) ? $file[0] : '')
    );
}

if (!function_exists('mb_wordwrap')) {
    function mb_wordwrap($string, $width = 75, $break = "\n", $cut = false)
    {
        $stringWidth = mb_strlen($string);
        $breakWidth = mb_strlen($break);

        $result = '';
        $lastStart = $lastSpace = 0;

        for ($current = 0; $current < $stringWidth; $current++) {
            $char = mb_substr($string, $current, 1);

            if ($breakWidth === 1) {
                $possibleBreak = $char;
            } else {
                $possibleBreak = mb_substr($string, $current, $breakWidth);
            }

            if ($possibleBreak === $break) {
                $result .= mb_substr($string, $lastStart, $current - $lastStart + $breakWidth);
                $current += $breakWidth - 1;
                $lastStart = $lastSpace = $current + 1;
            } elseif ($char === ' ') {
                if ($current - $lastStart >= $width) {
                    $result .= mb_substr($string, $lastStart, $current - $lastStart) . $break;
                    $lastStart = $current + 1;
                }

                $lastSpace = $current;
            } elseif ($current - $lastStart >= $width && $cut && $lastStart >= $lastSpace) {
                $result .= mb_substr($string, $lastStart, $current - $lastStart) . $break;
                $lastStart = $lastSpace = $current;
            } elseif ($current - $lastStart >= $width && $lastStart < $lastSpace) {
                $result .= mb_substr($string, $lastStart, $lastSpace - $lastStart) . $break;
                $lastStart = $lastSpace = $lastSpace + 1;
            }
        }

        if ($lastStart !== $current) {
            $result .= mb_substr($string, $lastStart, $current - $lastStart);
        }

        return $result;
    }
}

function cut_string($str, $count = 30, $show_tooltip = true)
{
    // режем длинные слова
    $tree_dots = '...';
    if (mb_strlen($str, 'UTF-8') <= ($count + mb_strlen($tree_dots, 'UTF-8'))) {
        $out = htmlspecialchars($str);
    } else {
        $out = mb_substr($str, 0, $count, 'UTF-8') . (mb_strlen($str, 'UTF-8') > $count ? $tree_dots : '');
        $out = htmlspecialchars($out);
        if ($show_tooltip) {
            $out = '<span class="popover-info" data-content="' . htmlspecialchars($str) . '">' . $out . '</span>';
        }
    }

    return $out;
}

function link_to_logistic($order, $shipping_tabs = null, $only_bool = false)
{
    global $all_configs;

    $link = $order['order_id'];//'#motions_orders';
    $bool = false;

    if ($shipping_tabs == null && array_key_exists('manage-orders-shipping-tab', $all_configs['configs'])
        && count($all_configs['configs']['manage-orders-shipping-tab']) > 0
    ) {
        $shipping_tabs = $all_configs['configs']['manage-orders-shipping-tab'];
    }

    if ($shipping_tabs && in_array($order['status'], $all_configs['configs']['order-statuses-logistic'])) {

        foreach ($shipping_tabs as $tab) {
            if (($tab['city'] > 0 && $tab['city'] == $order['city']) || $tab['city'] == 0) {
                // ok
            } else {
                continue;
            }
            if ((in_array($order['shipping'],
                    $tab['shippings'])) || (empty($order['shipping']) && $tab['default'] == 1)
            ) {
                // ok
            } else {
                continue;
            }

            $bool = true;
            $link = '<a href="' . $all_configs['prefix'] . 'logistics?o_id=' . $order['order_id']
                . '#' . $tab['href'] . '">' . $order['order_id'] . '</a>';
            break;
        }
    }

    if ($only_bool == true) {
        return $bool;
    } else {
        return $link;
    }
}

function show_marked($object_id, $type, $marked = 0)
{
    if ($type == 'oi' || $type == 'woi') {
        $active = 'fa fa-bookmark-o';
        if ($marked > 0) {
            $active = 'fa fa-bookmark';
        }
    } else {
        $active = 'star-marked-unactive';
        if ($marked > 0) {
            $active = 'star-marked-active';
        }
    }

    $remove = '';
    if (isset($_GET['marked']) && $_GET['marked'] == $type) {
        $remove = 'star-remove-icons';
    }

    $onclick = 'onclick="icons_marked(this, ' . $object_id . ', \'' . $type . '\')"';

    return '<span ' . $onclick . ' class="icons-marked ' . $active . ' ' . $remove . '"></span>';
}

/**
 * dir to array
 * */
function dirToArray($dir, $one = true)
{
    $result = null;
    $cdir = scandir($dir);

    if (is_array($cdir)) {
        $result = array();
        foreach ($cdir as $key => $value) {
            if (!in_array($value, array(".", ".."))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $value) && $one == false) {
                    $result[$value] = dirToArray($dir . DIRECTORY_SEPARATOR . $value);
                } else {
                    $result[] = $value;
                }
            }
        }
    }

    return $result;
}

/**
 * @param      $order
 * @param null $title
 * @param bool $link
 * @return null|string
 */
function supplier_order_number($order, $title = null, $link = true)
{
    global $all_configs;
    if (!array_key_exists('parent_id', $order) || !array_key_exists('number', $order) || !array_key_exists('num',
            $order)
    ) {
        $order = $all_configs['db']->query('SELECT number, parent_id, id, num FROM {contractors_suppliers_orders} WHERE id=?i',
            array($order['id']))->row();
    }
    $number = ($order['parent_id'] > 0 && $order['parent_id'] != $order['id']) ? $order['parent_id'] . '/' . $order['number'] : $order['num'];

    if ($number != $order['id']) {
        $out = $number . ' (' . $order['id'] . ')';
    } else {
        $out = $order['id'];
    }
    if (!$title) {
        $title = '№' . $out;
    }

    if ($link == true) {
        $href = $all_configs['prefix'] . 'orders/edit/' . $order['id'] . '#create_supplier_order';
        return '<a class="hash_link" href="' . $href . '">' . $title . '</a>';
    } else {
        return $title;
    }
}

/**
 * генерирование/разгенерирование серийника заказа поставщика
 *
 * @param        $itemId
 * @param bool   $generate
 * @param bool   $link
 * @param string $class
 * @return int|mixed|string
 */
function suppliers_order_generate_serial_by_id($itemId, $generate = true, $link = false, $class = '')
{
    global $all_configs;
    $item = $all_configs['db']->query('SELECT serial, id as item_id FROM {warehouses_goods_items} WHERE id=?i',
        array($itemId))->row();
    return suppliers_order_generate_serial($item, $generate, $link, $class);
}

/**
 * генерирование/разгенерирование серийника заказа поставщика
 *
 * @param        $order
 * @param bool   $generate
 * @param bool   $link
 * @param string $class
 * @return int|mixed|string
 */
function suppliers_order_generate_serial($order, $generate = true, $link = false, $class = '')
{
    global $all_configs;

    $serial = trim($order['serial']);
    if ($generate) {
        if (mb_strlen($serial, 'UTF-8') == 0) {
            if ($order['item_id'] > 0) {
                $serial = $all_configs['configs']['erp-serial-prefix'] . str_pad('', (7 - strlen($order['item_id'])),
                        0) . $order['item_id'];
            } elseif (array_key_exists('last_item_id', $order) && $order['last_item_id'] > 0) {
                $order = $all_configs['db']->query(
                    'SELECT i.id as item_id, i.serial FROM {warehouses_goods_items} as i WHERE i.id=?i',
                    array($order['last_item_id']))->row();

                return suppliers_order_generate_serial($order, $generate, $link, 'muted');
            }
        }
        $serial = htmlspecialchars(urldecode($serial));
    } else {
        if (preg_match('/^(' . $all_configs['configs']['erp-serial-prefix'] . ')([0-9]{' . $all_configs['configs']['erp-serial-count-num'] . '})$/',
                $serial) == 1
        ) {
            $serial = preg_replace("|[^0-9]|i", "", $serial);
            $serial = intval($serial);
        } else {
            $serial = urldecode($order['serial']);
        }
    }

    if ($link && $generate) {
        return '<a class="' . $class . '" href="' . $all_configs['prefix'] . 'warehouses?serial=' . $serial . '#show_items">' . $serial . '</a>';
    } else {
        return $serial;
    }
}

/**
 * @param      $order_id
 * @param bool $show_timer
 * @return string
 */
function timerout($order_id, $show_timer = false)
{
    $view = new View();
    return $view->renderFile('inc_func/timerout', array(
        'order_id' => $order_id,
        'show_timer' => $show_timer
    ));
}

function update_order_status($order, $new_status)
{
    global $all_configs;

    $return = array('state' => false, 'msg' => '');

    $user_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
    $mod_id = $all_configs['configs']['orders-manage-page'];

    if (isset($order['id']) && (isset($all_configs['configs']['order-status'][$new_status]) || isset($all_configs['configs']['sale-order-status'][$new_status])) && $order['id'] > 0 && (!isset($order['status']) || $new_status != $order['status'])) {

        $return['state'] = true;
        if (in_array($new_status, $all_configs['configs']['order-statuses-orders'])) {
            $qty = $all_configs['db']->query('SELECT COUNT(id) FROM {orders_goods} WHERE (item_id IS NULL OR unbind_request IS NOT NULL) AND order_id=?i AND type=?i',
                array($order['id'], 0))->el();
            if ($qty > 0) {
                $return['state'] = false;
                $return['msg'] = l('Отвяжите неиспользуемые запчасти');
            }
        }

        if ($order['type'] != ORDER_SELL && in_array($new_status,
                $all_configs['configs']['order-statuses-dis-if-spare-part'])
        ) {
            $qty = $all_configs['db']->query('SELECT COUNT(id) FROM {orders_goods} WHERE order_id=?i AND type=?i',
                array($order['id'], 0))->el();
            if ($qty > 0) {
                $return['state'] = false;
                $return['msg'] = l('Сначала отвяжите все запчасти');
            }
        }

        if ($return['state'] == true) {
            $status_id = $all_configs['db']->query('INSERT INTO {order_status} (`status`, order_id, user_id) VALUES (?i, ?i, ?i)',
                array($new_status, $order['id'], $user_id), 'id');

            $all_configs['db']->query('UPDATE {orders} SET status_id=?i, `status`=?i WHERE id=?i',
                array($status_id, $new_status, $order['id']));

            $all_configs['db']->query('INSERT INTO {changes} SET user_id=?i, work=?, map_id=?i, object_id=?i',
                array($user_id, 'update-order-status', $mod_id, $order['id']));

            $return['state'] = true;

            // смс
            if (isset($order['phone']) && isset($order['notify']) && $order['notify'] == 1) {
                $name = htmlspecialchars($all_configs['configs']['order-status'][$new_status]['name']);
                $result = send_sms($order['phone'],
                    l('Статус Вашего заказа') . ' №' . $order['id'] . ' изменился на "' . $name . '"');
                $return['msg'] = $result['msg'];
            }
            // готов в комментарий
            if ($new_status == $all_configs['configs']['order-status-ready']) {
                $all_configs['db']->query('INSERT INTO {orders_comments} (text, user_id, order_id) VALUES (?, ?i, ?i)',
                    array('Заказ готов', $user_id, $order['id']));
            }
        }

        // при приеме заказа на доработку ставим склад менеджера как и в случае обычного приема заказа
        if ($new_status == $all_configs['configs']['order-status-rework']) {
            // склад менеджер
            $wh = $all_configs['db']->query(
                'SELECT wh_id, location_id FROM {warehouses_users} WHERE user_id=?i AND main=?i',
                array($user_id, 1))->row();
            $all_configs['db']->query('UPDATE {orders} SET wh_id=?i, location_id=?i WHERE id=?i',
                array($wh['wh_id'], $wh['location_id'], $order['id']));
        }
    }

    // пробуем закрыть заказ
    if (in_array($new_status, $all_configs['configs']['order-statuses-closed'])) {
        $return['closed'] = $all_configs['chains']->close_order($order['id'], $mod_id);
    }


    return $return;
}

if ((!function_exists('mb_str_replace')) &&
    (function_exists('mb_substr')) && (function_exists('mb_strlen')) && (function_exists('mb_strpos'))
) {
    function mb_str_replace($search, $replace, $subject)
    {
        if (is_array($subject)) {
            $ret = array();
            foreach ($subject as $key => $val) {
                $ret[$key] = mb_str_replace($search, $replace, $val);
            }
            return $ret;
        }

        foreach ((array)$search as $key => $s) {
            if ($s == '') {
                continue;
            }
            $r = !is_array($replace) ? $replace : (array_key_exists($key, $replace) ? $replace[$key] : '');
            $pos = mb_strpos($subject, $s, 0, 'UTF-8');
            while ($pos !== false) {
                $subject = mb_substr($subject, 0, $pos, 'UTF-8') . $r . mb_substr($subject,
                        $pos + mb_strlen($s, 'UTF-8'), 65535, 'UTF-8');
                $pos = mb_strpos($subject, $s, $pos + mb_strlen($r, 'UTF-8'), 'UTF-8');
            }
        }
        return $subject;
    }
}

function get_service($service)
{
    global $all_configs;
    // load interface
    require_once $all_configs['path'] . 'services/service.php';
    if (strpos($service, '/') !== false) {
        $service_parts = explode('/', $service);
        $service_folder = $service_parts[0];
        $class = $service_parts[1];
        $path = $service;
    } else {
        $service_folder = $service;
        $class = $service;
        $path = $service;
    }
    $class_namespace = 'services\\' . $service_folder . '\\';
    $class_name = $class_namespace . $class;
    if (!class_exists($class_name)) {
        $all_path = $all_configs['path'] . 'services/' . $path . '/' . $class . '.php';
        if (file_exists($all_path)) {
            require_once $all_path;
            if (class_exists($class_name)) {
                if (get_parent_class($class_name) == 'service') {
                    $inst = $class_name::getInstanse();
                    $inst->set_all_configs($all_configs);
                    return $inst;
                } else {
                    throw new Exception(l('Сервис') . ' ' . $class_name . ' ' . l('не унаследует класс') . ' service');
                }
            } else {
                throw new Exception(l('Сервис') . ' ' . $class_name . ' ' . l('не найден'));
            }
        } else {
            throw new Exception(l('Файл сервиса') . ' ' . $class_name . ' ' . l('не найден') . ' (' . $all_path . ')');
        }
    } else {
        return $class_name::getInstanse();
    }
    return null;
}

function transliturl($str)
{
    $tr = array(
        "А" => "a",
        "Б" => "b",
        "В" => "v",
        "Г" => "g",
        "Д" => "d",
        "Е" => "e",
        "Ж" => "zh",
        "З" => "z",
        "И" => "i",
        "Й" => "y",
        "К" => "k",
        "Л" => "l",
        "М" => "m",
        "Н" => "n",
        "О" => "o",
        "П" => "p",
        "Р" => "r",
        "С" => "s",
        "Т" => "t",
        "У" => "u",
        "Ф" => "f",
        "Х" => "h",
        "Ц" => "ts",
        "Ч" => "ch",
        "Ш" => "sh",
        "Щ" => "sch",
        "Ъ" => "",
        "Ы" => "yi",
        "Ь" => "",
        "Э" => "e",
        "Ю" => "yu",
        "Я" => "ya",
        "а" => "a",
        "б" => "b",
        "в" => "v",
        "г" => "g",
        "д" => "d",
        "е" => "e",
        "ж" => "zh",
        "з" => "z",
        "и" => "i",
        "й" => "y",
        "к" => "k",
        "л" => "l",
        "м" => "m",
        "н" => "n",
        "о" => "o",
        "п" => "p",
        "р" => "r",
        "с" => "s",
        "т" => "t",
        "у" => "u",
        "ф" => "f",
        "х" => "h",
        "ц" => "ts",
        "ч" => "ch",
        "ш" => "sh",
        "щ" => "sch",
        "ъ" => "",
        "ы" => "yi",
        "ь" => "",
        "э" => "e",
        "ю" => "yu",
        "я" => "ya",
        " " => "-",
        "." => "",
        "/" => "-",
        "(" => "",
        ")" => ""
    );
    $str = trim($str);
    $str = strtolower($str);
    $str = strtr($str, $tr);
    $str = preg_replace("/[^a-z0-9-+_\s]/", "", $str);
    $str = preg_replace('/-{2,}/', '-', $str);
    $str = preg_replace('/_{2,}/', '_', $str);

    return $str;
}

/**
 * @param $prefix
 * @param $string
 * @param $length
 * @return array
 */
function prepare_for_serial_search($prefix, $string, $length)
{
    $matches = array();
    preg_match("/{$prefix}0*/", $string, $matches);
    if (empty($matches)) {
        return array('', $length);
    }
    $prefix = ltrim($string, $matches[0]);
    return array($prefix, $length - strlen($prefix));
}

/**
 * check if https
 *
 * @return bool
 */
function isHTTPS()
{
    return isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1)
    || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https';
}


function generate_xls_with_login_logs()
{
    require_once(__DIR__ . '/classes/PHPExcel.php');
    require_once(__DIR__ . '/classes/PHPExcel/Writer/Excel5.php');
    $users = db()->query('SELECT id, login, email, fio FROM {users} WHERE avail=1 AND deleted=0')->assoc();
    foreach ($users as $id => $user) {
        $users[$id]['logs'] = db()->query('SELECT DATE_FORMAT(created_at,\'%d-%m-%Y\') as date_add, MIN(created_at) as stamp, ip FROM {users_login_log} WHERE user_id=?i AND created_at > ? GROUP by date_add ORDER by date_add ASC',
            array($user['id'], date('Y-1-1', time())))->assoc();
    }
    $xls = new PHPExcel();
    $currentYear = date('Y');
    $currentMonth = date('m');
    foreach (range(1, $currentMonth) as $item) {
        $xls->createSheet($item);
        $xls->setActiveSheetIndex($item);
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle("{$item}-{$currentYear}");
        $sheet->getColumnDimensionByColumn(0)->setAutoSize(true);
        $sheet->setCellValue("A1", 'Пользователь');
        $sheet->getStyle('A1')->getFill()
            ->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $sheet->getStyle('A1')->getFill()
            ->getStartColor()->setRGB('EEEEEE');
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);

        foreach (range(1, 31) as $day) {
            $cell = $sheet->setCellValueByColumnAndRow($day, 1, $day, true);
            $sheet->getColumnDimensionByColumn($day)->setAutoSize(true);
            $sheet->getStyle($cell->getCoordinate())->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $sheet->getStyle($cell->getCoordinate())->getFill()
                ->getStartColor()->setRGB('EEEEEE');
            $sheet->getStyle($cell->getCoordinate())->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell->getCoordinate())->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
    }
    $xls->removeSheetByIndex(0);
    foreach ($users as $id => $user) {
        foreach (range(1, $currentMonth) as $item) {
            $xls->setActiveSheetIndex((int)$item - 1);
            $sheet = $xls->getActiveSheet();
            $sheet->setCellValueByColumnAndRow(
                0,
                (int)$id + 2,
                $user['login']);
        }
        if (!empty($user['logs'])) {
            $last = '';
            foreach ($user['logs'] as $log) {
                list($day, $month, $year) = explode('-', $log['date_add']);
                $xls->setActiveSheetIndex((int)$month - 1);
                $sheet = $xls->getActiveSheet();
                $cell = $sheet->setCellValueByColumnAndRow(
                    (int)$day,
                    (int)$id + 2,
                    date('H:i', strtotime($log['stamp'])),
                    true
                );
                if (!empty($last) && $log['ip'] != $last) {
                    $sheet->getStyle($cell->getCoordinate())->getFill()
                        ->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
                    $sheet->getStyle($cell->getCoordinate())->getFill()
                        ->getStartColor()->setRGB('FFEAEA');
                }
                $last = $log['ip'];
            }
        }
    }

    return new PHPExcel_Writer_Excel5($xls);
}

function h($string)
{
    return htmlspecialchars($string);
}

function price_with_discount($product)
{
    if ($product['discount_type'] == DISCOUNT_TYPE_PERCENT) {
        $price = $product['price'] / 100 * (1 - $product['discount'] / 100);
    } else {
        $price = $product['price'] / 100 - $product['discount'];
    }
    return $price;
}

function sum_with_discount($product, $quantity = 0)
{
    $count = $quantity > 0 ? $quantity : $product['count'];
    return price_with_discount($product) * $count;
}

function simple_password_generator($length = 9)
{
    $alpha = "abcdefghijklmnopqrstuvwxyz";
    $alpha_upper = strtoupper($alpha);
    $numeric = "0123456789";

    $chars = $alpha . $alpha_upper . $numeric;

    $len = strlen($chars);
    $password = '';

    for ($i = 0; $i < $length; $i++) {
        $password .= substr($chars, rand(0, $len - 1), 1);
    }

    return str_shuffle($password);
}

function convert_number_to_words($number)
{

    $hyphen = '-';
    $conjunction = ' and ';
    $separator = ', ';
    $negative = 'negative ';
    $decimal = ' point ';
    $dictionary = array(
        0 => 'zero',
        1 => 'one',
        2 => 'two',
        3 => 'three',
        4 => 'four',
        5 => 'five',
        6 => 'six',
        7 => 'seven',
        8 => 'eight',
        9 => 'nine',
        10 => 'ten',
        11 => 'eleven',
        12 => 'twelve',
        13 => 'thirteen',
        14 => 'fourteen',
        15 => 'fifteen',
        16 => 'sixteen',
        17 => 'seventeen',
        18 => 'eighteen',
        19 => 'nineteen',
        20 => 'twenty',
        30 => 'thirty',
        40 => 'fourty',
        50 => 'fifty',
        60 => 'sixty',
        70 => 'seventy',
        80 => 'eighty',
        90 => 'ninety',
        100 => 'hundred',
        1000 => 'thousand',
        1000000 => 'million',
        1000000000 => 'billion',
        1000000000000 => 'trillion',
        1000000000000000 => 'quadrillion',
        1000000000000000000 => 'quintillion'
    );

    if (!is_numeric($number)) {
        return false;
    }

    if (($number >= 0 && (int)$number < 0) || (int)$number < 0 - PHP_INT_MAX) {
        // overflow
        trigger_error(
            'convert_number_to_words only accepts numbers between -' . PHP_INT_MAX . ' and ' . PHP_INT_MAX,
            E_USER_WARNING
        );
        return false;
    }

    if ($number < 0) {
        return $negative . convert_number_to_words(abs($number));
    }
    
    $fraction = null;

    if (strpos($number, '.') !== false) {
        list($number, $fraction) = explode('.', $number);
    }

    switch (true) {
        case $number < 21:
            $string = $dictionary[$number];
            break;
        case $number < 100:
            $tens = ((int)($number / 10)) * 10;
            $units = $number % 10;
            $string = $dictionary[$tens];
            if ($units) {
                $string .= $hyphen . $dictionary[$units];
            }
            break;
        case $number < 1000:
            $hundreds = intval($number / 100);
            $remainder = $number % 100;
            $string = $dictionary[$hundreds] . ' ' . $dictionary[100];
            if ($remainder) {
                $string .= $conjunction . convert_number_to_words($remainder);
            }
            break;
        default:
            $baseUnit = pow(1000, floor(log($number, 1000)));
            $numBaseUnits = (int)($number / $baseUnit);
            $remainder = $number % $baseUnit;
            $string = convert_number_to_words($numBaseUnits) . ' ' . $dictionary[$baseUnit];
            if ($remainder) {
                $string .= $remainder < 100 ? $conjunction : $separator;
                $string .= convert_number_to_words($remainder);
            }
            break;
    }

    if (null !== $fraction && is_numeric($fraction)) {
        $string .= $decimal;
        $words = array();
        foreach (str_split((string)$fraction) as $number) {
            $words[] = $dictionary[$number];
        }
        $string .= implode(' ', $words);
    }

    return $string;
}