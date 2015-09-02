<?php

include 'inc_config.php';
//include 'class_sms.php';

if (!$debug){
    error_reporting(0);
}else{
    error_reporting(E_ALL);
}
header("Content-Type: text/html; charset=UTF-8");
session_start();

include 'inc_func.php';
//include 'class_auth.php';

$settings=$db->query("SELECT name, value FROM {settings}")->vars();

$act = isset($_GET['act']) ? $_GET['act'] : '';

$out = array();

/*
if($act == 'feedback'){
    $type = isset($_GET['type']) ? $_GET['type'] : '';
    if($type){
        if($type == 1){
            $email = isset($_POST['email']) ? trim($_POST['email']) : '';
            $message = isset($_POST['message']) ? trim($_POST['message']) : '';
            if($message && $email){
                $msgtxt = '
                    <b>E-mail</b>: '.htmlspecialchars($email).' <br><br>
                    <b>Сообщение</b>: '.htmlspecialchars($message).' <br><br>
                ';

                $sms_text = "Обратная связь: ".$email.", ".$message;
                $sms_text = mb_substr($sms_text, 0, 135)."...";

                send_mail($settings['content_email'], 'Вопрос с сайта', $msgtxt);
                $out = array('state' => true, 'msg' => 'Сообщение отправлено. Спасибо.');
            }else{
                $out = array('state' => false, 'msg' => 'Заполните все поля');
            }
        }
        if($type == 2){
            $name = isset($_POST['name']) ? trim($_POST['name']) : '';
            $tel = isset($_POST['tel']) ? trim($_POST['tel']) : '';
            $time = isset($_POST['time']) ? trim($_POST['time']) : '';
            if($time && $tel && $name){
                $msgtxt = '
                    <b>Имя</b>: '.htmlspecialchars($name).' <br><br>
                    <b>Телефон</b>: '.htmlspecialchars($tel).' <br><br>
                    <b>Время для звонка</b>: '.htmlspecialchars($time).' <br><br>
                ';

                $sms_text = "Обратный звонок: ".$name.", ".$tel.", ".$time;

                send_mail($settings['content_email'], 'Запрос на обратный звонок', $msgtxt);
                $msg = '';
                $out = array('state' => true, 'msg' => $msg);
            }else{
                $out = array('state' => false, 'msg' => 'Заполните все поля');
            }
        }
        if($settings['sms_telephones']){
            $sms_len = strlen($sms_text);
            $cnt_sms = $sms_len / 70;
            $cnt_sms = is_float($cnt_sms) ? (int)$cnt_sms + 1 : $cnt_sms;
            $tel = explode(',', $settings['sms_telephones']);
            $sms = new Sms();
            $sms->SendSms($sms_text, $settings['sms_telephones'], $cnt_sms * count($tel));
        }
    }
}
*/
#############################################################################

if(isset($_POST['act']) && $_POST['act'] == 'comebacker_show'){
    $ref = isset($_COOKIE['s_ref']) ? htmlspecialchars($_COOKIE['s_ref']) : '';
    $db->query("INSERT INTO {comebacker_shows}(ip,date,referer) "
                    ."VALUES(INET_ATON(?),NOW(),?)", array(
                        $_SERVER['REMOTE_ADDR'],$ref
                    ));
}

$user_id = isset($_SESSION['id']) ? intval($_SESSION['id']) : null;
// увеличиваем спрос на товар
if ($act == 'add-product-demand') {
    if (isset($_POST['product_id']) && intval($_POST['product_id']) > 0 && $user_id > 0) {
        // раз в минуту
        $demand_id = $db->query('SELECT id FROM {goods_demand} WHERE user_id=?i AND  goods_id=?i AND date_add>?',
            array($user_id, intval($_POST['product_id']), date('Y-m-d H:i:s', time()-60)))->el();
        if (!$demand_id) {
            $db->query('INSERT INTO {goods_demand} (user_id, goods_id) VALUES (?n, ?i)',
                array($user_id, intval($_POST['product_id'])));
        }
        $out['qty'] = $db->query('SELECT COUNT(goods_id) FROM {goods_demand} WHERE goods_id=?i',
            array(intval($_POST['product_id'])))->el();
    }
}

if($act == 'auth' && trim($_POST['token'])){

//    $s = file_get_contents('http://ulogin.ru/token.php?token='.$_POST['token']);

    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, 'http://ulogin.ru/token.php?token='.$_POST['token']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    $s = curl_exec($ch);
    curl_close($ch);

    if($s){
        $user_data = json_decode($s, true);

        if(!isset($user_data['error'])){

            $auth = new Auth($db);

            if(isset($_COOKIE['eoptika_cid'])){
                $hashed_cid = md5($_SERVER['REMOTE_ADDR']).md5(substr($_COOKIE['eoptika_cid'],0,32));
                $db->query("UPDATE {clients} SET cid = ? WHERE cid = ?i", array('', $hashed_cid));
                setcookie('eoptika_cid', false, -1);
            }

            $user_isset = $db->query('SELECT id, network, name, lastname, avatar FROM {clients} WHERE uid = ?q AND network = ?', array($user_data['uid'], $user_data['network']), 'row');

            if($user_isset){
                $db->query("UPDATE {clients} SET name = ?, lastname = ?, avatar = ? WHERE id = ?i"
                           , array($user_data['first_name'], $user_data['last_name'], $user_data['photo'], $user_isset['id']));
                $auth->Login($user_data['uid'], $user_isset['network']);
                $user = $user_isset;
            }else{
                $db->query("INSERT INTO {clients}(uid, network, name, lastname, date_reg, avatar, ip)
                            VALUES(?q, ?, ?, ?, NOW(), ?, ?)"
                        , array($user_data['uid'], $user_data['network'], $user_data['first_name'],
                                $user_data['last_name'], $user_data['photo'], $_SERVER['REMOTE_ADDR']));
                $auth->Login($user_data['uid'], $user_data['network']);
                $user = array(
                    'network' => $user_data['network'],
                    'name' => $user_data['first_name'],
                    'lastname' => $user_data['last_name'],
                    'avatar' => $user_data['photo']
                );
            }

            $out = array('state' => true, 'user' => $user);
        }else{
            $out = array('state' => false);
        }
    }else{
        $out = array('state' => false);
    }
}

if($act == 'add_comment'){
    $gallery = isset($_POST['gallery']) ? trim($_POST['gallery']) : 0;
    $image = isset($_POST['image']) ? trim($_POST['image']) : 0;
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : 0;
    if($gallery && $image){
        $auth = new Auth($db);
        $ifauth = $auth->IfAuth();
        if($ifauth){
            if($comment){
                $db->query("INSERT INTO {gallery_comments}(gallery, photo, user_id, comment, date)
                            VALUES(?, ?, ?i, ?, NOW())", array($gallery, $image, $ifauth['id'], $comment));
                $out = array(
                    'state' => true
                );
                $out['data'] = comments($gallery);
            }else{
                $out = array(
                    'state' => false,
                    'msg' => 'Введите ваш комментарий'
                );
            }
        }else{
            $out = array(
                'state' => false,
                'msg' => 'Авторизируйтесь'
            );
        }
    }
}

if($act == 'feedback'){
    $type = isset($_GET['type']) ? $_GET['type'] : '';

    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $tel = isset($_POST['tel']) ? trim($_POST['tel']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    $day = isset($_POST['day']) ? trim($_POST['day']) : '';
    $time = isset($_POST['time']) ? trim($_POST['time']) : '';

    $tel = preg_replace('~[^0-9]+~', '', $tel);
    if(strlen($tel)<9) $tel='';

    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $mark = isset($_POST['mark']) ? trim($_POST['mark']) : '';
    $keystring = isset($_POST['keystring']) ? trim($_POST['keystring']) : '';

    if($type){
//        simple
        if($type == 1){
            if($name && $tel){
                $msgtxt = '
                    <b>Имя</b>: '.htmlspecialchars($name).' <br><br>
                    <b>Телефон</b>: '.htmlspecialchars($tel).' <br><br>
                    <b>Сообщение</b>: '.htmlspecialchars($message).' <br><br>
                    ';

                send_mail($settings['content_email'], 'Вопрос с сайта', $msgtxt);
                $out = array('state' => true,
                            'msg' => 'Заявка отправлена. Спасибо.');
            } else {
                $out = array('state' => false,
                            'err' => 'Заполните все поля');
            }
        }
//        with time
        if($type == 11){
            if($name && $tel && $day && $time){
                $msgtxt = '
                    <b>Имя</b>: '.htmlspecialchars($name).' <br><br>
                    <b>Телефон</b>: '.htmlspecialchars($tel).' <br><br>
                    <b>День недели</b>: '.htmlspecialchars($day).' <br><br>
                    <b>Время</b>: '.htmlspecialchars($time).' <br><br>
                    ';

                send_mail($settings['content_email_2'], 'Запись на СТО с сайта', $msgtxt);
                $out = array('state' => true,
                            'msg' => 'Заявка отправлена. Спасибо.');
            } else {
                $out = array('state' => false,
                            'err' => 'Заполните все поля');
            }
        }

        if($type == 12){
            if($message && $email){
                $msgtxt = '
                    <b>Имя</b>: '.htmlspecialchars($name).' <br><br>
                    <b>Контакты</b>: '.htmlspecialchars($email).' <br><br>
                    <b>Сообщение</b>: '.htmlspecialchars($message).' <br><br>
                    ';

                send_mail($settings['email_fb_director'], 'Жалоба с сайта '.$settings['site_name'], $msgtxt);
                $out = array('state' => true,
                            'msg' => 'Заявка отправлена. Спасибо.');
            } else {
                $out = array('state' => false,
                            'err' => 'Заполните все поля');
            }
        }

        if($type == 3){
            if($name && $email && $message && $keystring && isset($_SESSION['captcha_keystring'])){
                if($_SESSION['captcha_keystring'] === $keystring){


                    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
                    $xip = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : '';
                    if ($xip || !$ip){
                        $ip = $xip;
                    }

                    $id=$db->query("INSERT INTO {reviews} (user, email, comment, mark, ip)
                                VALUES(?, ?, ?, ?i, ?)",
                                array($name, $email, $message, $mark, $ip), 'id');
                    if ($id)
                        $out = array('state' => true,
                                    'msg' => 'Комментарий отправлен. Спасибо.');
                    else
                        $out = array('state' => false,
                    'err' => 'Ошибка БД.');

                } else {
                    $out = array('state' => false,
                            'err' => 'Не верно введены символы с картинки');
                }

            } else {
                $out = array('state' => false,
                            'err' => 'Заполните все поля');
            }
        }
    }
}

// Стоимость, по которой можно сдать устройство:
if ($act == 'tradein') {
    if ( !isset($_POST['state']) || !isset($_POST['moisture']) || !isset($_POST['goods_id']) || $_POST['goods_id'] < 1 ) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Произошла ошибка', 'error'=>true));
        exit;
    }
    $goods = $db->query('SELECT hotline_price FROM {map} WHERE buy_old=1 AND hotline_price>0 AND id=?i',
        array($_POST['goods_id']))->row();

    if ( !$goods ) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Этот товар не участвует в программе trade-in', 'error'=>true));
        exit;
    }
    $tradein = $settings['max-percent-tradein'];

    if ( $_POST['moisture'] == 'yes' )
        $tradein -= 20;

    if ( $_POST['state'] == 'good' )
        $tradein -= 10;

    if ( $_POST['state'] == 'defects' )
        $tradein -= 30;

    if ( $tradein < 10 ) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'К сожалению, мы не можем предложить вам деньги за этот продукт'));
        exit;
    }
    $price = ceil(($goods['hotline_price']/100)*$tradein);

    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode(array('message' => '* Ориентировочная стоимость.',
        'price' => $price. ' грн.*'));
    exit;
}

// продажа бу товара
if ($act == 'sell-tradein') {
    if ( !isset($_POST['state']) || !isset($_POST['moisture']) || !isset($_POST['goods_id']) || $_POST['goods_id'] < 1 ) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Произошла ошибка', 'error'=>true));
        exit;
    }
    $goods = $db->query('SELECT `name`, hotline_price FROM {map} WHERE buy_old=1 AND hotline_price>0 AND id=?i',
        array($_POST['goods_id']))->row();

    if ( !$goods ) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Этот товар не участвует в программе trade-in', 'error'=>true));
        exit;
    }
    $tradein = $settings['max-percent-tradein'];

    $moisture = ' Влага не попадала.';
    if ( $_POST['moisture'] == 'yes' ){
        $tradein -= 20;
        $moisture = ' Попадала влага.';
    }

    $state = ' Состояние идеальное.';
    if ( $_POST['state'] == 'good' ) {
        $tradein -= 10;
        $state = ' Состояние: хороше.';
    }

    if ( $_POST['state'] == 'defects' ) {
        $tradein -= 30;
        $state = ' Состояние: есть дефекты.';
    }

    if ( $tradein < 10 ) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'К сожалению, мы не можем предложить вам деньги за этот продукт', 'error'=>true));
        exit;
    }

    $price = ceil(($goods['hotline_price']/100)*$tradein);

    if ( !empty($_POST['email']) && !filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) ) {
        header("Content-Type: application/json; charset=UTF-8");
        echo json_encode(array('message' => 'Неверная электронная почта', 'error'=>true));
        exit;
    }
    $msgtxt = 'Гость ' . $_POST['email'] . ' ' . $_POST['phone'] . '.<br>
        Товар - ' . $goods['name'] . '.<br>
        Приблизительная цена - ' . $price. ' грн.<br>'
        . $moisture . $state;

    send_mail($settings['email_trade_in'], 'Покупка б/у '.$settings['site_name'], $msgtxt);
//    header("Content-Type: application/json; charset=UTF-8");
//    echo json_encode(array('message' => 'С Вами свяжется сотрудник сервисного центра для уточнения деталей.'));
//    exit;
    $out = array('state' => true,
                'msg' => 'С Вами свяжется сотрудник сервисного центра для уточнения деталей.');
}

header('Content-type: application/json; charset=utf-8');
echo json_encode($out);
