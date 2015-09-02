<?php
$ajax = true;
include 'inc_config.php';
include_once 'class_visitors.php';
$settings = $db->query("SELECT name, value FROM {settings}")->vars();

include 'core_lang.php';

if (!$debug) {
    error_reporting(0);
} else {
    error_reporting(E_ALL);
}
header("Content-Type: application/json; charset=UTF-8");

include 'inc_func.php';
include 'inc_func_forms.php';


require_once($path . 'configs.php');
$configs = Configs::get();

$form_id = isset($_GET['form_id']) ? $_GET['form_id'] : '';
$out = array();

if ($form_id && $_POST) {

    Visitors::getInstance()->init_visitors();
    $code = Visitors::getInstance()->get_code(true);
    
    $fields = $db->query("SELECT id, required, type, data_type "
                        ."FROM {forms_fields} WHERE form_id = ?i AND active = 1", array($form_id), 'assoc:id');

    if ($fields) {
        $translates = get_few_translates(
            'forms_fields', 
            'field_id', 
            $db->makeQuery("field_id IN (?q)", array(implode(',', array_keys($fields))))
        );
        $error = false;
        $data = array();
        $email = '';
        $admin_email_text = '';
        foreach ($fields as $field) {
            $field = translates_for_page($lang, $def_lang, $translates[$field['id']], $field, true);
            if (isset($_POST['fields'][$field['id']]) || $field['type'] == 'checkbox') {
                if ($field['type'] == 'checkbox') {
                    $value = isset($_POST['fields'][$field['id']]) ? '+' : '-';
                } else {
                    $value = trim($_POST['fields'][$field['id']]);
                }
                if ($field['required'] && !$value) {
                    $error = true;
                    $out = array(
                        'state' => false,
                        'msg' => 'Заполните обязательные поля'
                    );
                    break;
                }
                if ($field['data_type'] == 'email') {
                    $email = $value;
                    if (!preg_match("([a-zA-Z0-9_]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+)", $value)) {
                        $error = true;
                        $out = array(
                            'state' => false,
                            'msg' => 'Эл. адрес указан неверно'
                        );
                        break;
                    }
                }

                $admin_email_text .= '<strong>' . $field['name'] . '</strong>: ' . htmlspecialchars($value) . '<br><br>';

                $data[] = $db->makeQuery("(?i, ?, '!user_id!')", array($field['id'], $value));
            }
        }
        if (!$error && $data) {
            $sref = isset($_COOKIE['s_ref']) ? $_COOKIE['s_ref'] : '(direct)';
            $from_page = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
            $user_id = $db->query("INSERT INTO {forms_users}(form_id, date, ip, from_page, referal) VALUES(?i, NOW(), ?, ?, ?)", 
                                    array($form_id, get_ip(), $from_page, $sref), 'id');
            $db->query("INSERT INTO {forms_data}(field_id, `value`, user_id)
                        VALUES ?q", array(str_replace("'!user_id!'", $user_id, implode(',', $data))));

            $form = $db->query("SELECT * FROM {forms} WHERE id = ?i", array($form_id), 'row');
            $translates = $db->query("SELECT * 
                                      FROM {forms_strings} WHERE forms_id = ?i", array($form_id), 'assoc:lang');
            $form = translates_for_page($lang, $def_lang, $translates, $form, true);

            $out = array(
                'state' => true,
                'msg' => $form['user_result_text']
            );

            if ($form['send_result']) {
                //send to email
                $admin_email_text .= '
                    <b>Код на скидку</b>: '.$code.' 
                    <br><br>
                    <b>Со страницы:</b> '.htmlspecialchars($from_page).'<br>
                    <b>Пришли на страницу с:</b> '.htmlspecialchars($sref).'
                ';
                send_mail($settings['content_email'], $form['user_result_title'], $admin_email_text);
            }

            if ($form['send_result_to_user']) {
                //send to client email
                send_mail($email, $form['user_result_title'], $form['user_result_text'], false);
            }

            if (!empty($form['function']) && function_exists($form['function'])) {
                
                $result = $form['function']();

                if ($result && isset($result['state']) && $result['state'] == true) {
                    if (isset($result['html'])) {
                        $out['html'] = $result['html'];
                    } else {
                        $out['msg'] = $form['user_result_text'];
                    }
                    $out['name'] = $form['name'];
                } else {
                    $out = array(
                        'state' => false,
                        'msg' => isset($result['msg']) ? $result['msg'] : 'Произошла ошибка, попробуйте еще раз'
                    );
                }
            }
        }

    }

}

echo json_encode($out);

function arrToStr($arr)
{
    return is_array($arr) ? arrToStr(current($arr)) : trim($arr);
};

/**
 * функции для форм должны начинатся с ajax_forms_
 * */

/**
 * return status callback
 * */
function ajax_forms_callback()
{
    global $db, $prefix, $path, $settings, $configs;

    $phone = isset($_POST['fields']) ? preg_replace('/[^0-9]+/', '', arrToStr($_POST['fields'])) : '';
    //$order_id = isset($_POST['hiddens']) ? preg_replace('/[^0-9]+/', '', arrToStr($_POST['hiddens'])) : '';

       
    /* тут баг, не приходит номер заказа :(
     * поєтому отправляем на почту консультации
    if ($phone && $order_id) {
        $manager = $db->query('SELECT manager FROM {orders} WHERE id=?', array($order_id))->el();

        // уведомление менеджеру
        if ($manager) {
            include 'mail.php';
            $mailer = new Mailer(array('prefix' => $prefix, 'path' => $path, 'db' => $db, 'settings' => $settings, 'configs' => $configs));
            $href = $prefix . 'manage/orders/create/' . $order_id;
            $body = 'Клиент хочет узнать статус ремонта по заказу <a href="' . $href . '">№' . $order_id . '</a> т.' . htmlspecialchars($phone);
            $mailer->send_message($body, 'Статус ремонта', $manager, 1, '', 2);
        }

        return array('state' => true);
    }
     * 
     */

    if ($phone){
//        include 'mail.php';
//        $email = $settings['consult_email'];
//        $mailer = new Mailer(array('prefix' => $prefix, 'path' => $path, 'db' => $db, 'settings' => $settings, 'configs' => $configs));
//        $body = 'Клиент хочет узнать статус ремонта по заказу. Тел. ' . htmlspecialchars($phone);
//        $mailer->send_message($body, 'Статус ремонта', $email, 1, '', 2);
//        return array('state' => true, 'msg' => 'Запрос отправлен. Спасибо.');
        $body = 'Клиент хочет узнать статус ремонта по заказу.<br> '
               .'Тел. ' . htmlspecialchars($phone).'<br>'
               .'<a href="http://'.$_SERVER['HTTP_HOST'].$prefix.'manage/orders?cl='.$phone.'#show_orders-orders">Ремонты пользователя</a>';
        send_mail($settings['consult_email'], 'Статус ремонта', $body);
        return array('state' => true, 'msg' => 'Запрос отправлен. Спасибо.');
    }   
    
    return array('state' => false, 'msg' => 'Произошла ошибка, попробуйте еще раз');
}

/**
 * return status repair
 * */
function ajax_forms_repair_status()
{
    global $db, $configs;

    $html = '<p class="data_form_message error">Ремонт не найден!</p>';

    $orders = null;

    $order_id = isset($_POST['fields']) ? preg_replace('/[^0-9]+/', '', arrToStr($_POST['fields'])) : '';

    if (!empty($order_id)) {
        $orders = $db->query('SELECT o.*, cg.title FROM {orders} as o, {categories} as cg
            WHERE o.phone=? AND o.category_id=cg.id ORDER BY o.date_add DESC',  array($order_id))->assoc();
    }

    if ($orders) {
        $html = '';
        foreach ($orders as $order) {
            $status = isset($configs['order-status'][$order['status']]) ? htmlspecialchars($configs['order-status'][$order['status']]['name']) : '';
            $html .= '<h2>Ремонт №' . $order['id'] . '</h2><br />';
            $html .= '<p>от ' . date("d/m/Y", strtotime($order['date_add'])) . '</p>';
            $html .= '<p>Статус: ' . $status . '</p>';
            $html .= '<p><b> Устройство: </b> ' . htmlspecialchars($order['title']) . ' <b> Серийный номер: </b> ' . htmlspecialchars($order['serial']) . ' </p>';

            $comments = $db->query('SELECT * FROM {orders_comments} WHERE order_id=?i AND private=0 ORDER BY date_add DESC',
                array($order['id']))->assoc();
            if ($comments) {
                $html .= '<table class="table table-striped"><thead><tr><td><center>Дата</center></td><td>Текущий статус ремонта</td></tr></thead><tbody>';
                foreach ($comments as $comment) {
                    $html .= '<tr><td><center>' . date("d.m.Y<b\\r/>H:i", strtotime($comment['date_add'])) . '</center></td>';
                    $html .= '<td>' . htmlspecialchars(wordwrap($comment['text'], 25, " ", true)) . '</td></tr>';
                }
                $html .= '</tbody></table>';
            }

            // обратный звонок
            $html .= '<br /><h3 class="center">Недостаточно информации или остались вопросы?<br />Мы перезвоним Вам!</h3>';
            $phone = '+380 ' . mb_substr($order_id, 3, 2) . ' ' . mb_substr($order_id, 5, 3) . '-' . mb_substr($order_id, 8, 2) . '-' . mb_substr($order_id, 10, 2);
            $html .= content_form('{-form_1-}', array(1 => array('phone' => $phone, 'hidden' => $order['id'])));
            $html .= '<p class="center muted">* График работы инженерной с 11.00 до 18.00.<br>Ожидайте звонка Вашего специалиста в указаное время.</p>';
            $html .= '<br /><br />';
        }
    }

    return array('html' => $html, 'state' => true);
}