<?php

require_once __DIR__.'/../manage/FlashMessage.php';

class access
{
    public $remTime = 7776000; // 90 days

    private $salt = 'salt';

    private $gid = 'gid';
    private $uid = 'uid';
    private $sid = 'sid';

    private $user = null;

    private $standart;

    protected $users_table = 'sessions';
    protected $guests_table = 'guests';

    protected $all_configs;

    function __construct(&$all_configs, $standart = true)
    {
        include_once $all_configs['sitepath'] . 'mail.php';

        $this->all_configs = &$all_configs;
        $this->standart = $standart;

        if (!isset($_SESSION))
            session_start();

        if ($this->standart == true)
            return $this->gen_user();

        return null;
    }

    function gen_user()
    {
        $this->remTime = $this->all_configs['configs']['cookie-live'];
        $this->salt = $this->all_configs['db_prefix'] . $this->all_configs['configs']['salt'];
        $this->gid = $this->all_configs['db_prefix'] . $this->all_configs['configs']['guest_id'];
        $this->uid = $this->all_configs['db_prefix'] . $this->all_configs['configs']['user_id'];
        $this->sid = $this->all_configs['db_prefix'] . $this->all_configs['configs']['session_id'];

        /* курс
        if (isset($_COOKIE[$this->uid])) {
            $person = $this->all_configs['db']->query('SELECT person FROM {clients} WHERE id=?i', array($_COOKIE[$this->uid]))->el();
            if ($person && $person == 2 && (!isset($_COOKIE[$this->all_configs['db_prefix'] . $this->all_configs['configs']['course']]) || $_COOKIE[$this->all_configs['db_prefix'] . $this->all_configs['configs']['course']] == $this->all_configs['configs']['default-course'])) {
                $this->setcookie($this->all_configs['db_prefix'] . $this->all_configs['configs']['course'], $this->all_configs['configs']['default-course-corp']);
            }
        }*/

        // если нету сид
        if (isset($_COOKIE[$this->uid]) && !empty($_COOKIE[$this->uid]) && (!isset($_COOKIE[$this->sid]) || intval($_COOKIE[$this->sid]) < 0)) {
            $this->clearcookie($this->gid);
            $sid = $this->create_new(intval($_COOKIE[$this->uid]));
            return $this->check(intval($_COOKIE[$this->uid]), $this->users_table, $this->uid, $sid);
        }

        // если пользователь
        if (isset($_COOKIE[$this->uid]) && !empty($_COOKIE[$this->uid])) {
            $this->clearcookie($this->gid);
            return $this->check(intval($_COOKIE[$this->uid]), $this->users_table, $this->uid, $_COOKIE[$this->sid]);
        }

        // если гость
        if (isset($_COOKIE[$this->gid])) {
            return $this->check('', $this->guests_table, $this->gid);
        }

        //создаем нового гостя
        return $this->create_new();
    }

//    function OFF_set_region()
//    {
//        // страны
//        $countries = array('UA' => 'UA');
//        // город по умолчанию
//        $city = isset($this->all_configs['configs']['default-city']) ? $this->all_configs['configs']['default-city'] : 13;
//        // регион по умолчанию
//        $region = isset($this->all_configs['configs']['default-region']) ? $this->all_configs['configs']['default-region'] : 12;
//        // определенный регион по умолчанию
//        $geo_region = 0;
//
//        //определяем координаты
//        if (@geoip_record_by_name(get_ip())) {
//            $geo = geoip_record_by_name(get_ip());
//
//            // если Киев, временное решение
//            if ($geo && isset($geo['region']) && $geo['region'] == 13) {
//                $geo['region'] = 12;
//            }
//
//            // найденна область
//            if ($geo && array_key_exists('country_code', $geo) && array_key_exists('region', $geo) &&
//                array_key_exists($geo['country_code'], $countries) &&
//                array_key_exists($geo['region'], $this->all_configs['configs']['regions']) &&
//                array_key_exists($geo['region'], $this->all_configs['configs']['cities'])) {
//
//                $_SESSION['region'] = $geo['region'];
//                $_SESSION['geo-region'] = $geo['region'];
//                $this->setcookie($this->all_configs['db_prefix'] . $this->all_configs['configs']['region'], $geo['region']);
//
//                $city = $geo['region'] == 12 ? 13 : 30;
//            }
//        }
//
//        // есть в куках регион
//        if (isset($_COOKIE[$this->all_configs['db_prefix'] . $this->all_configs['configs']['region']]) &&
//            array_key_exists($_COOKIE[$this->all_configs['db_prefix'] . $this->all_configs['configs']['region']], $this->all_configs['configs']['regions']) &&
//            array_key_exists($_COOKIE[$this->all_configs['db_prefix'] . $this->all_configs['configs']['region']], $this->all_configs['configs']['cities'])) {
//
//            $_SESSION['region'] = $_COOKIE[$this->all_configs['db_prefix'] . $this->all_configs['configs']['region']];
//            $this->setcookie($this->all_configs['db_prefix'] . $this->all_configs['configs']['region'], $_COOKIE[$this->all_configs['db_prefix'] . $this->all_configs['configs']['region']]);
//
//            $city = $_COOKIE[$this->all_configs['db_prefix'] . $this->all_configs['configs']['region']] == 12 ? 13 : 30;
//        }
//
//        // сессия региона не установленна
//        if (!isset($_SESSION['region']) ||
//            !array_key_exists($_SESSION['region'], $this->all_configs['configs']['regions']) &&
//            !array_key_exists($_SESSION['region'], $this->all_configs['configs']['cities'])) {
//
//            $_SESSION['region'] = 12;
//            $this->setcookie($this->all_configs['db_prefix'] . $this->all_configs['configs']['region'], 12);
//
//            $city = 13;
//        }
//
//        // регион не определен
//        if (!isset($_SESSION['geo-region']) ||
//            !array_key_exists($_SESSION['geo-region'], $this->all_configs['configs']['regions']) &&
//            !array_key_exists($_SESSION['geo-region'], $this->all_configs['configs']['cities'])) {
//
//            $_SESSION['geo-region'] = $geo_region;
//        }
//
//        // есть в куках город
//        if (isset($_COOKIE[$this->all_configs['db_prefix'] . $this->all_configs['configs']['city']]) &&
//            array_key_exists($_COOKIE[$this->all_configs['db_prefix'] . $this->all_configs['configs']['city']],
//                $this->all_configs['configs']['cities'][$_COOKIE[$this->all_configs['db_prefix'] . $this->all_configs['configs']['region']]])) {
//
//            $_SESSION['city'] = $_COOKIE[$this->all_configs['db_prefix'] . $this->all_configs['configs']['city']];
//            $this->setcookie($this->all_configs['db_prefix'] . $this->all_configs['configs']['city'], $_COOKIE[$this->all_configs['db_prefix'] . $this->all_configs['configs']['city']]);
//        }
//
//        // сессия региона не установленна
//        if (!isset($_SESSION['region'])) {
//
//            $_SESSION['region'] = $region;
//            $this->setcookie($this->all_configs['db_prefix'] . $this->all_configs['configs']['region'], $region);
//        }
//
//        // сессия города не установленна
//        if (!isset($_SESSION['city'])) {
//
//            $_SESSION['city'] = $city;
//            $this->setcookie($this->all_configs['db_prefix'] . $this->all_configs['configs']['city'], $city);
//        }
//    }

    function is_logged_in()
    {
        return $this->get_info() ? true : false;
    }

    function __call($name, $arguments)
    {
        preg_match('/(user_)(.+)/', $name, $arr);

        if (count($arr) == 3) {

            if ($this->is_logged_in()) {
                if (array_key_exists($arr[2], $this->user)) {
                    return htmlspecialchars($this->user[$arr[2]]);
                }
            } else {
                return '';
            }
        }

        if ($this->user === null) {
            throw new Exception('Fatal error: Call to undefined method ' . __CLASS__ . '::' . htmlspecialchars($name) . '()');
        }
    }

    function __toString()
    {
        $this->get_info();

        if ($this->user && isset($this->user['fio']) && mb_strlen(trim($this->user['fio']), 'UTF-8') > 0) {
            return htmlspecialchars(trim($this->user['fio']));
        }
        if (isset($this->user['email']) && filter_var($this->user['email'], FILTER_VALIDATE_EMAIL)) {
            $parts = explode("@", trim($this->user['email']));
            return $parts[0];
        }

        return 'Login';
    }

    private function get_info()
    {
        if (isset($_SESSION['user_id']) && $this->user === null) {
            $this->user = $this->all_configs['db']->query('SELECT * FROM {clients} WHERE id=?i',
                array(intval($_SESSION['user_id'])))->row();
        }

        return $this->user;
    }

    function check($user_id = '', $table, $id, $sid = '')
    {
        if ($user_id > 0) {
            $user = $this->all_configs['db']->query('SELECT id, confirm, pass FROM {clients} WHERE id=?i', array($user_id))->row();
            if (!$user) {
                unset($_SESSION['user_id']);
                $this->clearcookie($this->uid);
                $this->clearcookie($this->sid);
                return $this->create_new();
            }
        }

        if (!isset($_COOKIE[$this->salt])) {
            $this->clearcookie($this->gid);
            $this->clearcookie($this->uid);
            $this->clearcookie($this->sid);
            $_SESSION['guest_id'] = '';
            $_SESSION['user_id'] = '';
            unset($_SESSION['guest_id']);
            unset($_SESSION['user_id']);
            return $this->create_new();
        }

        return $this->find($table, $id, $user_id, $sid);
    }

    private function create_new($user_id = '')
    {
        $browser_salt = $this->unique_salt();
        $unique_salt = $this->unique_salt();
        $myhash = $this->myhash($unique_salt . $user_id, $browser_salt . $user_id);

        $sql = '';
        $table = $this->guests_table;
        if (intval($user_id) > 0) {
            $sql = ', user_id=' . intval($user_id);
            $table = $this->users_table;
        }

        $new_id = $this->all_configs['db']->query("INSERT INTO {?query} SET hash=?, salt=?, ip=INET_ATON(?) ?query",
            array($table, $myhash, $unique_salt, get_ip(), $sql), 'id');

        if ($user_id > 0) {

            if (isset($_COOKIE[$this->gid]))
                $this->all_configs['db']->query("DELETE FROM {" . $this->guests_table . "} WHERE id=?i", array($_COOKIE[$this->gid]));

            $this->setcookie($this->sid, $new_id);
            $this->setcookie($this->salt, $browser_salt);
            $this->setcookie($this->uid, $user_id);

            $_SESSION['user_id'] = $user_id;
            $_SESSION['guest_id'] = '';
            unset($_SESSION['guest_id']);
            $this->clearcookie($this->gid);

            return $new_id;
        } elseif ($new_id > 0) {
            $this->setcookie($this->salt, $browser_salt);
            $this->setcookie($this->gid, $new_id);
            $_SESSION['guest_id'] = $new_id;
            $_SESSION['user_id'] = '';
            unset($_SESSION['user_id']);
            $this->clearcookie($this->sid);
            $this->clearcookie($this->uid);
        }
    }

    function find($table, $id, $user_id, $sid)
    {
        if ($user_id > 0 && $sid > 0) {
            $result = $this->all_configs['db']->query("SELECT * FROM {" . $this->users_table . "} WHERE id=?i", array($sid))->row();

            if (!$result || $user_id != $result['user_id'] || $result['hash'] != $this->myhash($result['salt'] . $user_id, $_COOKIE[$this->salt] . $user_id)) {
                if (isset($_COOKIE[$sid]))
                    $this->all_configs['db']->query("DELETE FROM {" . $table . "} WHERE id=?i", array($_COOKIE[$sid]));
                $this->clearcookie($this->gid);
                $this->clearcookie($this->salt);
                $this->clearcookie($this->sid);
                $_SESSION['guest_id'] = '';
                $_SESSION['user_id'] = '';
                unset($_SESSION['guest_id']);
                unset($_SESSION['user_id']);
                return $this->create_new();
            }
            if ($result['hash'] === $this->myhash($result['salt'] . $user_id, $_COOKIE[$this->salt] . $user_id)) {
                $c = $_COOKIE[$this->salt];
                $this->setcookie($this->salt, $c);
                $this->setcookie($id, $user_id);
                $this->setcookie($this->sid, $result['id']);
                $_SESSION['user_id'] = $user_id;
                return $user_id;
            }
        } else {
            unset($_SESSION['user_id']);
            $result = $this->all_configs['db']->query("SELECT * FROM {" . $this->guests_table . "} WHERE id=?i", array($_COOKIE[$id]))->row();

            if (!$result || $result['hash'] != $this->myhash($result['salt'] . $user_id, $_COOKIE[$this->salt] . $user_id)) {
                if (isset($_COOKIE[$id]))
                    $this->all_configs['db']->query("DELETE FROM {" . $table . "} WHERE id=?i", array($_COOKIE[$id]));
                $this->clearcookie($this->gid);
                $this->clearcookie($this->salt);
                $this->clearcookie($this->sid);
                $_SESSION['guest_id'] = '';
                $_SESSION['user_id'] = '';
                unset($_SESSION['guest_id']);
                unset($_SESSION['user_id']);
                return $this->create_new();
            }

            if ($result['hash'] === $this->myhash($result['salt'] . $user_id, $_COOKIE[$this->salt] . $user_id)) {
                $c = $_COOKIE[$this->salt];
                $this->setcookie($this->salt, $c);
                $this->setcookie($id, $result['id']);
                $_SESSION['guest_id'] = $result['id'];
                return $result['id'];
            }
        }

        return $this->create_new();
    }

    private function setcookie($name, $value, $time = null)
    {
        $time = $time === null ? time() + $this->remTime : $time;

        setcookie($name, $value, $time, $this->all_configs['prefix']);
    }

    private function clearcookie($name)
    {
        $this->setcookie($name, '', time() - 3600);
    }

    function unique_salt()
    {
        return substr(sha1(mt_rand()), 0, 22);
    }

    function myhash($unique_salt, $browser_salt)
    {
        $salt = "f#@V)Hu^%Hgfds";
        $hash = sha1($unique_salt . $browser_salt);

        $hash = sha1($hash, $salt);

        return sha1($hash);
    }

    function update_user($data, $user_id = null)
    {
        $result = array('state' => true);
        $set = array();

        if ($user_id === null && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        }

        if ($result['state'] == true && isset($data['fio'])) {
            $set['fio'] = trim($data['fio']);
        }

        if ($result['state'] == true && !empty($data['legal_address'])) {
            $set['legal_address'] = trim($data['legal_address']);
        }

        if ($result['state'] == true && isset($data['phone']) && mb_strlen(trim($data['phone']), 'UTF-8') > 0) {
            $result_phone = $this->update_phones($data['phone']);
            if ($result_phone !== true) {
                $result['state'] = false;
                $result['msg'] = 'Телефон неверный';
            }
        }

        if ($result['state'] == true && isset($data['email']) && mb_strlen(trim($data['email']), 'UTF-8') > 0) {
            $result_phone = $this->update_email($data['email']);
            if ($result_phone !== true) {
                $result['state'] = false;
                $result['msg'] = 'Эл.адрес неверный';
            }
        }

        if ($result['state'] == true && isset($data['password_1']) && mb_strlen(trim($data['password_1']), 'UTF-8') > 0) {
            if ($result['state'] == true && (!isset($data['password_old']) || $this->wrap_pass($data['password_old']) !== $this->user_pass())) {
                $result['state'] = false;
                $result['msg'] = 'Старый пароль неверный';
            }
            if ($result['state'] == true && (!isset($data['password_2']) || trim($data['password_1']) != trim($data['password_2']))) {
                $result['state'] = false;
                $result['msg'] = 'Пароли не совпадают';
            }
            if ($result['state'] == true) {
                $set['pass'] = $this->wrap_pass($data['password_1']);
            }
        }

        if ($result['state'] == true && count($set) > 0 && intval($user_id) > 0) {
            $ar = $this->all_configs['db']->query('UPDATE {clients} SET ?set WHERE id=?i',
                array($set, intval($user_id)))->ar();

            if (isset($data['mail'])) {
                $mailer = new Mailer($this->all_configs);
                $mailer->group('new-pass', $this->user_email(), array('pass' => trim($data['password_1'])));
                $mailer->go();
            }
        }

        return $result;
    }

    function login($email, $pass, $return_result = false, $without_pass = false)
    {
        $result = array('state' => true, 'msg' => '');

        if ($this->is_logged_in())
            return $result;

        if (empty($pass) && $without_pass === false) {
            $result = array('state' => false, 'msg' => "Пароль не может быть пустым.");
        }
        if (empty($email)) {
            $result = array('state' => false, 'msg' => "Электронная почта не может быть пустая.");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $result = array('state' => false, 'msg' => "Электронная почта указана неверно.");
        }

        if ($result['state'] == true) {
            $user = $this->all_configs['db']->query('SELECT id, confirm, pass FROM {clients} WHERE email=?', array($email))->row();
            if (!$user) {
                $result = array('state' => false, 'msg' => "Нет такой электронной почте в нашей системе.", 'type' => "email_wrong");
            } else {
                if ($this->wrap_pass($pass) != trim($user['pass']) && $without_pass === false) {
                    $result = array('state' => false, 'msg' => "Неверный пароль.");
                } else {

                    if (isset($_SESSION['user_id'])) {
                        $this->move_shopping_cart($user['id'], $_SESSION['user_id']);
                    }
                    $this->create_new($user['id']);

                    if ($return_result) {
                        $result = array('state' => true, 'user' => $user);
                    } else {
                        if (!empty($_POST['referer'])) {
                            header("Location: {$_POST['referer']}");
                        } else {
                            header("Location: {$this->all_configs['prefix']}my-account");
                        }
                        exit;
                    }
                }
            }
        }

        return $result;
    }

    function move_shopping_cart($user_id, $guest_id)
    {
        if (intval($user_id) > 0 &&  intval($guest_id) > 0) {
            $this->all_configs['db']->query('UPDATE {shopping_cart} SET user_id=?i WHERE guest_id=?i',
                array(intval($user_id), intval($guest_id)));
        }
    }

    function wrap_pass($pass)
    {
        return sha1(trim($pass));
    }

    function urlsafe_b64encode($string)
    {
        $data = base64_encode($string);
        $data = str_replace(array('+','/','='),array('-','_','.'),$data);
        return $data;
    }

    private function urlsafe_b64decode($string)
    {
        $data = str_replace(array('-','_','.'),array('+','/','='),$string);
        $mod4 = strlen($data) % 4;
        if ($mod4) {
            $data .= substr('====', $mod4);
        }
        return base64_decode($data);
    }

    function edit($post)
    {
        $result = array('state' => true, 'msg' => '');

        $email = isset($post['email']) && mb_strlen(trim($post['email']), 'UTF-8') > 0 ? $this->is_email($post['email']) : null;
        $phone = isset($post['phone']) && count(array_filter($post['phone'])) > 0 ? $this->is_phone($post['phone']) : null;

        if (!isset($post['id']) || intval($post['id']) == 0) {
            $result['state'] = false;
            $result['msg'] = 'Клиент не найден.';
        }
        if ($result['state'] == true && $email === false) {
            $result['state'] = false;
            $result['msg'] = 'Электронная почта указана неверно.';
        }
        if ($result['state'] == true && $phone === false) {
            $result['state'] = false;
            $result['msg'] = 'Неправильный формат номера телефона.';
        }
        if ($result['state'] == true && !$email && !$phone) {
            $result['state'] = false;
            $result['msg'] = 'Укажите телефон или эл.почту.';
        }
        $fio = isset($post['fio']) ? trim($post['fio']) : '';
        $legal_address = isset($post['legal_address']) ? trim($post['legal_address']) : '';
        $contractor_id = isset($post['contractor_id']) ? $post['contractor_id'] : '';
        $tag_id = isset($post['tag_id']) ? $post['tag_id'] : 0;
        $tagQuery = $this->all_configs['db']->makeQuery('tag_id = ?i,', array($tag_id));
        if (($tag_id == $this->all_configs['configs']['blacklist-tag-id']) && !$this->all_configs['oRole']->hasPrivilege('add-client-to-blacklist')) {
            $tagQuery = '';
            FlashMessage::set(l('У вас нет прав на добавление клиента в черный список'), FlashMessage::DANGER);
        }
        if ($result['state'] == true) {
            $this->update_phones($post['phone'], $post['id']);
            $this->update_email($post['email'], $post['id']);

            $this->all_configs['db']->query('UPDATE {clients} SET fio=?, legal_address=?, ?q contractor_id=?i WHERE id=?i',
                array($fio, $legal_address, $tagQuery, $contractor_id, $post['id']));
        }

        return $result;
    }

    function registration($post)
    {
        $mailer = new Mailer($this->all_configs);
        $result = array('state' => true, 'id' => 0, 'new' => false);

        $email = isset($post['email']) && mb_strlen(trim($post['email']), 'UTF-8') > 0 ? $this->is_email($post['email']) : null;
        $phone = isset($post['phone']) && mb_strlen(trim($post['phone']), 'UTF-8') > 0 ? $this->is_phone($post['phone']) : null;

        if ($result['state'] == true && $email === false) {
            $result['state'] = false;
            $result['msg'] = 'Электронная почта указана неверно.';
        }
        if ($result['state'] == true && $phone === false) {
            $result['state'] = false;
            $result['msg'] = 'Неправильный формат номера телефона.';
        }
        if ($result['state'] == true && !$email && !$phone) {
            $result['state'] = false;
            $result['msg'] = 'Укажите телефон или эл.почту.';
        }
        $pass = isset($post['password']) ? trim($post['password']) : null;
        $rpass = isset($post['rpass']) ? trim($post['rpassword']) : $pass;
        $tag_id = isset($post['tag_id']) ? (int)$post['tag_id'] : 0;

        $person = (!isset($post['person']) || $post['person'] == 'true') ? 1 : 2;
        $fio = isset($post['fio']) ? trim($post['fio']) : null;
        $contractor_id = isset($post['contractor_id']) ? $post['contractor_id'] : '';

        $address = isset($post['legal_address']) ? trim($post['legal_address']) : null;

        if ($result['state'] == true && $pass != $rpass) {
            $result['state'] = false;
            $result['msg'] = 'Пароли не совпадают.';
        }
        if (!$pass)
            $pass = $this->rand_str(10);

        $confirm = $this->standart == true ? $this->rand_str(20) : null;

        if ($result['state'] == true) {

            $client_email = $this->get_client($email);
            $client_phones = $this->get_client(null, $phone);

            // только телефон
            if ($result['id'] == 0 && !$email && $client_phones) {
                $result['state'] = false;
                $result['id'] = $client_phones['id'];
                $result['msg'] = 'Указанный Вами номер телефона уже зарегестрирован.';
            }
            // только почта
            if ($result['id'] == 0 && !$phone && $client_email) {
                $result['state'] = false;
                $result['id'] = $client_email['id'];
                $result['msg'] .= 'Указанная Вами эл.почта уже зарегестрирована.';
            }
            if ($result['id'] == 0 && $client_email && $client_phones && $client_email['id'] == $client_phones['id']) {
                // один и тот же клиент
                $result['state'] = false;
                $result['id'] = $client_phones['id'];
                $result['msg'] = 'Такой клиент уже существует';
            } elseif ($result['id'] == 0 && $client_email && $client_phones && count($client_email['phones']) == 0 && !$this->is_email($client_phones['email'])) {
                // разные клиенты с пустыми данными (один без телефона другой без почты)
                $result['state'] = false;
                $result['id'] = $client_email['id'];
                $result['msg'] = 'Данные не пренадлежат одному клиенту';
                $content = '<a href="' . $this->all_configs['prefix'] . 'manage/clients/group_clients?client_1=' . $client_email['id'] . '&client_2=' . $client_phones['id'] . '">Склейка клиентов</a>';
                $mailer->send_message($content, 'Склейка клиентов', 'edit-suppliers-orders', 1);
            }
            if ($result['id'] == 0 && ($client_email || $client_phones)) {

                $result['msg'] = '';

                if ($client_email && count($client_email['phones']) > 0) {
                    $result['state'] = false;
                    $result['msg'] .= 'Указанная Вами эл.почта зарегестрирована под номерами телефонов ';
                    foreach ($client_email['phones'] as $client_phone) {
                        $result['msg'] .= $this->hide_phone($client_phone) . ' ';
                    }
                }
                if ($client_phones && $this->is_email($client_phones['email'])) {
                    $result['state'] = false;
                    $result['msg'] .= 'Указанный Вами номер телефона зарегестрирован под эл.почтой ';
                    $result['msg'] .= $this->hide_email($client_phones['email']);
                }
                $result['msg'] .= '. Укажите другую эл.почту или другой номер телефона.';
            }
        }

        if ($result['state'] == true) {
            try {
                $tagQuery = $this->all_configs['db']->makeQuery('tag_id = ?i,', array($tag_id));
                if (($tag_id == $this->all_configs['configs']['blacklist-tag-id']) && !$this->all_configs['oRole']->hasPrivilege('add-client-to-blacklist')) {
                    $tagQuery = '';
                    FlashMessage::set(l('У вас нет прав на добавление клиента в черный список'), FlashMessage::DANGER);
                }
                $result['id'] = $this->all_configs['db']->query('INSERT INTO {clients}
                    (`email`, legal_address, `confirm`, `pass`, `fio`, `person`, contractor_id, tag_id)
                    VALUES (?n, ?n, ?n, ?, ?, ?, ?q ?i)',
                    array($email, $address, $confirm, $this->wrap_pass($pass), $fio, $person, $tagQuery, $contractor_id), 'id');

                $result['new'] = true;
                $result['msg'] = 'Успешно зарегестирован.';
            } catch (go\DB\Exceptions\Exception $e) {
                $result['new'] = false;
                $result['msg'] = 'Указанные Вами электронная почта или номер телефона уже зарегистрированны в нашей системе.';
            }
        }

        if ($result['id'] > 0) {

//            if (isset($post['delivery']) && !empty($post['delivery']) && trim($post['delivery']) > 0
//                    && isset($post['city']) && isset($post['region']) && array_key_exists($post['region'], $this->all_configs['configs']['regions'])
//                    && array_key_exists($post['city'], $this->all_configs['configs']['cities'][$post['region']])) {
//                $this->all_configs['db']->query('INSERT IGNORE INTO {clients_delivery_addresses} (user_id, content, region, city) VALUES (?i, ?, ?i, ?i)',
//                    array($result['id'], $post['delivery'], $post['region'], $post['city']));
//            }
            $this->update_phones($phone, $result['id']);
            $this->update_email($email, $result['id']);

            if ($this->standart == false)
                return $result;

            /*if ($result['new'] == true) {
                if ($email) {
                    $mailer->group('register', $email, array('pass' => $pass, 'confirm' => $confirm, 'user_id' => $result['id']));
                    $mailer->go();
                }
                if (isset($_SESSION['user_id'])) {
                    $this->move_shopping_cart($result['id'], $_SESSION['user_id']);
                }
                $this->create_new($result['id']);
            }*/
        }

        return $result;
    }

    public function get_client($email = null, $phones = null, $or = false)
    {
        $user = null;
        $query = null;

        if ($phones && is_array($phones) && count($phones) > 0) {
            $query = $this->all_configs['db']->makeQuery('clp.phone IN (?list)', array(array_values($phones)));
        }

        if ($email && is_string($email)) {
            $email_query = $this->all_configs['db']->makeQuery('cl.email=?', array($email));
            if($or && $query){
                $query .= ' OR '.$email_query;
            }else{
                $query = $email_query;
            }
        }

        if ($query != null) {
            $user = $this->all_configs['db']->query('SELECT cl.id, cl.email FROM {clients} as cl
                LEFT JOIN {clients_phones} clp ON clp.client_id=cl.id WHERE (?query) LIMIT 1', array($query))->row();
        }

        if ($user) {
            $user['phones'] = $this->all_configs['db']->query('SELECT client_id, phone '
                    .'FROM {clients_phones} WHERE client_id = ?i', array($user['id']))->vars();
        }

        return $user;
    }

    private function update_phones($phone, $user_id = null)
    {
        $phones = $this->is_phone($phone);

        if ($user_id === null && isset($_SESSION['user_id'])) {
            $user_id = $_SESSION['user_id'];
        }

        if ($phones && intval($user_id) > 0) {
            try {
                $this->all_configs['db']->query('UPDATE {clients} SET phone=?n WHERE id=?i',
                    array(current($phones), intval($user_id)));

                $this->all_configs['db']->query("DELETE FROM {clients_phones} WHERE client_id = ?i", array($user_id));
                foreach ($phones as $p) {
                    $this->all_configs['db']->query('INSERT IGNORE INTO {clients_phones} (phone, client_id) VALUES (?, ?i)',
                        array($p, intval($user_id)));
                }
            } catch (go\DB\Exceptions\Exception $e) {
                return false;
            }

            return true;
        }

        return null;
    }

    private function update_email($email, $user_id = null)
    {
        if ($this->is_email($email) && $user_id > 0) {
            try {
                $this->all_configs['db']->query('UPDATE {clients} SET email=?n WHERE id=?i',
                    array($email, $user_id));
            } catch (go\DB\Exceptions\Exception $e) {
                return false;
            }

            return true;
        }

        return null;
    }

    private function hide_phone($client_phone)
    {
        $out = '';

        if ($this->is_phone($client_phone)) {
            $n = mb_strlen($client_phone, 'UTF-8');
            $out = mb_substr($client_phone, 0, 5, 'UTF-8');
            $out .= str_repeat ('*', $n - 6);
            $out .= mb_substr($client_phone, -1, 1, 'UTF-8');
        }

        return htmlspecialchars($out);
    }

    private function hide_email($client_email)
    {
        $out = '';
        $reg = array();

        if (filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
            preg_match('/(.+)@(.+)\.(.+)/', trim($client_email), $reg);
        }

        if (is_array($reg) && count($reg) == 4) {
            $out = mb_substr($reg[1], 0, 1, 'UTF-8');
            $n = mb_strlen($reg[1], 'UTF-8');
            if ($n <= 4) {
                $out .= str_repeat ('*', $n - 1);
            } else {
                $out .= str_repeat ('*', $n - 2);
                $out .= mb_substr($reg[1], -1, 1, 'UTF-8');
            }
            $out .= '@' . mb_substr($reg[2], 0, 1, 'UTF-8');

            $n = mb_strlen($reg[2], 'UTF-8');
            if ($n <= 4) {
                $out .= str_repeat ('*', $n - 1);
            } else {
                $out .= str_repeat ('*', $n - 2);
                $out .= mb_substr($reg[2], -1, 1, 'UTF-8');
            }
            $out .= '.' . $reg[3];
        }

        return $out;
    }

    function rand_str($length = 32, $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890')
    {
        $chars_length = (strlen($chars) - 1);
        $string = $chars{rand(0, $chars_length)};
        for ($i = 1; $i < $length; $i = strlen($string)) {
            $r = $chars{rand(0, $chars_length)};
            if ($r != $string{$i - 1}) $string .= $r;
        }
        // Return the string
        return $string;
    }

    function is_phone($value)
    {
        $return = array();
        $phones = is_array($value) ? array_filter($value) : explode(',', $value);
        $phone_conf = $this->all_configs['configs']['countries'][$this->all_configs['settings']['country']]['phone'];
        $phone_length = $phone_conf['length'];
        $code = $phone_conf['code'];
        $code_length = strlen($code);
        $short_code = $phone_conf['short_code'];
        $short_code_length = strlen($short_code);
        foreach ($phones as $phone) {
            $phone = preg_replace("/[^0-9]/", "", $phone);
            $length = mb_strlen('' . $phone, 'UTF-8');
            if ($length == $phone_length) {
                // без кода
                $return[] = $code.$phone;
            }elseif($length == $phone_length+$short_code_length && strpos(''.$phone, ''.$short_code) === 0){
                // с коротким кодом - замена короткого на обычный
                $phone_wo_short_code = substr($phone, $short_code_length);
                $return[] = $code.$phone_wo_short_code;
            }elseif($length == $phone_length+$code_length && strpos(''.$phone, ''.$code) === 0){
                // с обычным кодом
                $return[] = $phone;
            }
        }
        $return = array_filter($return);
        return count($return) > 0 ? $return : false;
    }

    function is_email($value)
    {
        return is_string($value) ? filter_var(trim($value), FILTER_VALIDATE_EMAIL) : false;
    }
}
