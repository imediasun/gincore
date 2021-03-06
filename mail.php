<?php

require_once __DIR__ . '/shop/mail.class.php';

class Mailer extends PHPMailer
{

    private $host;
    protected $all_configs;

    public $mail_templates_folder = 'mail_templates/';

    /**
     * Mailer constructor.
     * @param bool $all_configs
     */
    function __construct($all_configs)
    {
        $all_configs['prefix'] = isset($all_configs['siteprefix']) ? $all_configs['siteprefix'] : $all_configs['prefix'];
        $all_configs['path'] = isset($all_configs['sitepath']) ? $all_configs['sitepath'] : $all_configs['path'];
        $this->all_configs = $all_configs;

        $this->host = 'http://' . $_SERVER['HTTP_HOST'] . $this->all_configs['prefix'];

        $this->CharSet = 'utf-8';
        $this->IsHTML(true);
    }

    /**
     * @param        $subject
     * @param        $email
     * @param array $data
     * @param string $body
     */
    function group($subject, $email, $data = array(), $body = 'Новое письмо')
    {
        switch ($subject) {
            case('register'):
                $this->Subject = l('Подтверждение регистрации');
                $this->Body = 'register.html';
                $data['msg'] = l('Благодарим Вас за то, что выбрали наш магазин. Ваш аккаунт<br>
                        успешно создан. Для активации необходимо перейти по ссылке.');
                $data['body_link_1'] = $this->host . 'signin?confirm=' . $data['confirm'];
                $data['body_link_1_title'] = $data['body_link_1'];
                break;

            case('confirm'):
                $this->Subject = l('Подтверждение');
                $this->Body = 'simple.html';
                $data['msg'] = l("Нажмите на ссылку для подтверждения:");
                $data['body_link_1'] = $this->host . 'signin?user=' . $data['user_id'] . '&confirm=' . $data['confirm'];
                $data['body_link_1_title'] = $data['body_link_1'];
                break;

            case('comment-inform'):
                $this->Subject = l('Ответ на отзыв');
                $this->Body = l('Новый ответ на отзыв');
                break;

            case('order-inform'):
                $this->Subject = l('Статус заказа');
                $this->Body = "Статус вашего заказа № {$data['order_id']} изменился";
                unset($data['order_id']);
                $this->IsHTML(true);
                break;

            case('new-order'):
                $this->Subject = l('Подтверждение заказа');
                // for admin note
                if (empty($data)) {
                    $this->Body = $body;
                    break;
                }

                $this->Body = 'new_order.html';
                $data['body_link_1'] = $this->host . 'order?order_id=' . $data['order_id'] . '&amp;order_hash=' . $data['order_hash'];
                $data['body_link_1_title'] = l('Перейти в личный кабинет.');
                $data['body_link_2'] = $this->host . 'coupons';
                $data['body_link_2_title'] = l('Подробнее');
                $data['order_id'] = 'Заказ № ' . $data['order_id'];
                $data['coupons'] = $this->genCoupons();

//                $this->AddEmbeddedImage($this->all_configs['path'] . 'images/logo.png', 'logoimg');
                //$this->AddEmbeddedImage($this->all_configs['path'] . 'images/bg_3.jpg', 'bg_head');

                $delivery = $this->genDelivery($data);
                $data['delivery_title'] = $delivery['title'];
                $data['delivery_content'] = $delivery['content'];

                break;

            case('went-on-sale'):
                $this->Subject = 'Товар появился в продаже';
                $this->Body = 'simple.html';
                $data['msg'] = 'Товар появился в продаже';
                $data['body_link_1'] = $data['url'];
                $data['body_link_1_title'] = array_key_exists('title', $data) ? $data['title'] : $data['body_link_1'];
                break;

            case('settings'):
                $this->Subject = 'Товар появился в продаже';
                $this->Body = 'simple.html';
                $data['msg'] = 'Товар появился в продаже';
                $data['body_link_1'] = $data['url'];
                $data['body_link_1_title'] = array_key_exists('title', $data) ? $data['title'] : $data['body_link_1'];
                break;

            case('order-manager'):
                $this->Subject = l('Вы назначены ответственным');
                $this->Body = l('Вы назначены ответственным по заказу #') . "<a href='{$this->host}manage/orders/create/{$data['order_id']}' >{$data['order_id']}</a>";
                $this->From = $this->all_configs['config']['from-system'];
                break;

            case('courier-logistics'):
                $this->Subject = l('Запрос на перемещение');
                $this->Body = $body;
                $this->From = $this->all_configs['config']['from-system'];
                break;

            case('send-new-comment'):
                $this->Subject = l('Новый отзыв о работе сотрудников');
                $this->Body = l('Закза N') . $data['order_id'] . "<br><br>";
                $this->Body .= h($data['manager']) . ': ' . $data['manager_rating'] . "<br>";
                $this->Body .= h($data['acceptor']) . ': ' . $data['acceptor_rating'] . "<br>";
                $this->Body .= h($data['engineer']) . ': ' . $data['engineer_rating'] . "<br><br>";
                $this->Body .= l('Коментарий:') . "<br>";
                $this->Body .= h($data['comment']) . "<br>";
                $this->From = $this->all_configs['config']['from-system'];
                break;

            case('new-pass'):
                $this->Subject = l('Новый пароль');
                $this->Body = '<div style="margin: 50px 0 70px;">' . $data['pass'] . '</div>';
                $this->From = $this->all_configs['config']['from-system'];
                break;

            case('remind-pass'):
                $this->Subject = l('Напоминание пароля');
                $this->Body = 'simple.html';
                $link = $this->host . 'signin?user=' . $data['user_id'] . '&reminder=' . $data['reminder'];
                $data['msg'] =
                    '<div style="margin: 50px 0; text-align:left;">'
                    . l('Перейдите по ссылке для восстановления пароля ')
                    . '<a href="' . $link . '" target ="_blank">' . $link . '</a>'
                    . '</div>';
                $this->From = $this->all_configs['config']['from-system'];
                break;

            case('forgot-password'):
                $this->Subject = l('Изменение пароля');
                $this->Body = l('Уважаемый') . ' ' . $data['fio'] . "<br>";
                $this->Body .= l('Вы воспользовались формой для восстановления пароля к аккаунту:') . "<br>";
                $this->Body .= l('Сайт:') . ' ' . $_SERVER['SERVER_NAME'] . '<br>';
                $this->Body .= l('Ваш логин:') . ' ' . $data['login'] . '<br>';
                $this->Body .= l('Ваш новый пароль:') . ' ' . $data['password'];
                $this->From = $this->all_configs['config']['from-system'];
                break;

            case('send-excell'):
                $this->Subject = l('Отчет по входам в систему');
                $this->Body = l('Отчет по входам в систему');
                $this->AddAttachment($data['file'], "report.xls");
                $this->From = $this->all_configs['config']['from-system'];
                break;

            case('logistic-notification'):
                $this->Subject = l('Заказ на доставку');
                $this->Body = l('Заказ на доставку') . ':<br>';
                $this->Body .= l('Груз:') . ' ' . $data['type'] . ' ' . $data['cargo'] . '<br>';
                $this->Body .= l('Отправная точка:') . ' ' . $data['from'] . '<br>';
                $this->Body .= l('Точка назначения:') . ' ' . $data['to'];
                $this->From = $this->all_configs['config']['from-system'];
                break;

            default:
                $this->Subject = $subject;
                $this->Body = $body;
                $this->From = $this->all_configs['config']['from-system'];
                break;
        }
        $data['email'] = $email;
        $this->AddEmbeddedImage($this->all_configs['path'] . 'images/logo.png', 'logoimg');
        $this->gen_body_vars($data);
        $this->Body = $this->body_substitution();
        $this->AddAddress($email);

    }

    /**
     * @throws phpmailerException
     */
    function go()
    {
        if (empty($this->From)) {
            $this->From = $this->all_configs['db']->query('SELECT `value` FROM {settings} WHERE `name`="content_email"',
                array())->el();
        }
        $this->FromName = $this->all_configs['db']->query('SELECT `value` FROM {settings} WHERE `name`="site_name"',
            array())->el();
        $this->SetFrom($this->From, $this->FromName);

        $this->Send();
        $this->ClearAddresses();
        $this->ClearAttachments();
        $this->IsHTML(false);
    }

    /**
     * @param        $content
     * @param        $title
     * @param        $user_destination
     * @param int $auto
     * @param string $query
     * @param int $type
     * @param int $prio
     * @param int $transporter
     */
    function send_message(
        $content,
        $title,
        $user_destination,
        $auto = 0,
        $query = '',
        $type = 0,
        $prio = 1,
        $transporter = 1
    )
    {
        if ($transporter == 1) {

            $current_user = (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null);

            if ($user_destination > 0) {
                $this->all_configs['db']->query('INSERT INTO {messages}
                      (`content`, `title`, `ip`, `user_id`, `user_id_destination`, `prio`, `auto`, `type`)
                      VALUES (?, ?, INET_ATON(?), ?n, ?i, ?i, ?i, ?i)',
                    array($content, $title, get_ip(), $current_user, $user_destination, $prio, $auto, $type)
                );
            } else {
                $users = $this->all_configs['db']->query('SELECT u.id FROM {users_permissions} as p
                        LEFT JOIN {users_role_permission}as rp ON rp.permission_id=p.id
                        LEFT JOIN {users} as u ON u.role=rp.role_id ?query
                        WHERE p.link=? AND u.id>0 GROUP BY u.id',
                    array($query, $user_destination))->assoc();

                if ($users) {
                    foreach ($users as $user) {
                        $this->all_configs['db']->query('INSERT INTO {messages}
                              (`content`, `title`, `ip`, `user_id`, `user_id_destination`, `prio`, `auto`, `type`)
                              VALUES (?, ?, INET_ATON(?), ?n, ?i, ?i, ?i, ?i)',
                            array($content, $title, get_ip(), $current_user, $user['id'], $prio, $auto, $type)
                        );
                    }
                }
            }
        }
    }

    /**
     * @return mixed|string
     */
    function body_substitution()
    {
        if (file_exists($this->all_configs['path'] . $this->mail_templates_folder . 'mail_header.html')) {
            $header = file_get_contents($this->all_configs['path'] . $this->mail_templates_folder . 'mail_header.html');
        } else {
            $header = '';
        }
        if (strpos($this->Body,
                '.html') && is_file($this->all_configs['path'] . $this->mail_templates_folder . $this->Body)
        ) {
            $body = file_get_contents($this->all_configs['path'] . $this->mail_templates_folder . $this->Body);
        } else {
            $body = $this->Body;
        }
        if (file_exists($this->all_configs['path'] . $this->mail_templates_folder . 'mail_footer.html')) {
            $footer = file_get_contents($this->all_configs['path'] . $this->mail_templates_folder . 'mail_footer.html');
        } else {
            $footer = '';
        }

        $mail_content = ($header ? $header : '') . ($body ? $body : '') . ($footer ? $footer : '');
        $pattern = "/\{\-(mail_msg)\-([a-zA-Z0-9_]{1,20})\}/";
        $mail_content = preg_replace_callback($pattern, array($this, "replace_pattern"), $mail_content);

        return $mail_content;
    }

    /**
     * replace pattern via $this->mail_msg
     *
     * @param type $matches
     * @return string
     */
    function replace_pattern($matches)
    {
        $mail_msg = $this->mail_msg;
        if ($matches[1] == 'mail_msg') { // && !isset($input[$matches[2]])
            if (isset ($mail_msg[$matches[2]])) {
                return $mail_msg[$matches[2]];
            } else {
                return '';
            }
        }
    }

    /**
     * @param $data
     */
    function gen_body_vars($data)
    {
        global $template_vars;
        $settings = $this->all_configs['settings'];

        $contacts = 'тел.: ' . strip_tags($template_vars['content_tel']) . ' | '
            . '<a href="mailto:' . $settings['content_email'] . '" title="' . $settings['content_email'] . '">' . $settings['content_email'] . '</a> | '
            . '<a href="' . $this->host . '">' . $_SERVER['HTTP_HOST'] . '</a>'
            . '<p></p>';

        // restricted: shipping, address, np_office, office
        $arr = $data;
        $arr['subject'] = $this->Subject;
        $arr['host'] = $this->host;
        $arr['logo_link'] = 'images/logo.png';
        $arr['shop_name'] = $this->all_configs['configs']['shop-name'];
        $arr['site_link'] = $_SERVER['SERVER_NAME'];
        $arr['contacts'] = $contacts;

        $this->mail_msg = $arr;
    }

    /**
     * @return string
     */
    function genCoupons()
    {
        $coupons_html = '';
        $coupons = $this->all_configs['db']->query('SELECT url, image, name FROM {banners} WHERE block=4 AND active=1 ORDER BY prio LIMIT 0, 4')->assoc();
        if ($coupons) {
            $coupons_html .= '<ul style="list-style: none outside none; min-height: 150px; max-height: 150px; height: 150px; overflow: hidden; text-align: center; padding: 0;">';
            foreach ($coupons as $coupon) {
                $this->AddEmbeddedImage($this->all_configs['path'] . 'images/flayers/' . $coupon['image'],
                    $coupon['image']);

                $coupons_html .= '<li style="display:inline-block; margin:0 2px;"><a href="' . $this->host . 'coupons" title="купон-' . $coupon['name'] . '">' .
                    '<img src="cid:' . $coupon['image'] . '" alt="' . $coupon['name'] . '" />' .
                    '</a></li>';
            }
            $coupons_html .= '</ul>';
        }
        return $coupons_html;
    }

    /**
     * @param $data
     * @return array
     */
    function genDelivery($data)
    {
        $m = '';
        $v = '';
        if (isset($data['shipping'])) {
            if ($data['shipping'] == 'express' || $data['shipping'] == 'courier' || $data['shipping'] == 'courier_today') {
                $m = 'Адрес доставки:';
                $v = $data['address'];
            }
            if ($data['shipping'] == 'novaposhta_cash' || $data['shipping'] == 'novaposhta') {
                $m = 'Отделение новой почты:';
                $v = $data['np_office'];
            }
            if ($data['shipping'] == 'pickup') {
                $m = 'Отделение магазина:';
                $v = $data['office'];
            }
        }
        return array('title' => $m, 'content' => $v);
    }

    /**
     *
     */
    function links_style()
    {
        $color = 'orange';
        $this->Body = str_replace('<a ', '<a style="color:' . $color . '" ', $this->Body);
    }
}