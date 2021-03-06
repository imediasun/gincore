<?php

require_once __DIR__.'/../manage/Core/FlashMessage.php';

class Auth { //класс авторизации
#db settings

    var $cookie_session_name = '';
    var $cookie_expired = 1209600;
    var $allow_cert_auth = true;

    public $db;

    /**
     * Auth constructor.
     * @param $db
     */
    public function __construct($db)
    {
        $this->db = $db;
        if (!isset($_SESSION))
            session_start();
    }

    /**
     * @param $user
     */
    private function SetLogedIn($user)
    {
        mt_srand((double) microtime() * 1000000);
        $cidgen = md5(mt_rand() + mt_rand() . get_ip());
        $hashed_cid = md5(get_ip()) . md5($cidgen);
        $sql = $this->db->query("UPDATE {users} SET cid = ?, uxt = UNIX_TIMESTAMP() WHERE id = ?i", array($hashed_cid, $user["id"]));
        $this->db->query('INSERT INTO {users_login_log} SET user_id =?i, created_at=CURRENT_TIMESTAMP, ip=?', array($user['id'], isset($_SERVER['REMOTE_ADDR'])? $_SERVER['REMOTE_ADDR']: ''));
        setcookie($this->cookie_session_name, '', time() - $this->cookie_expired, '/');
        setcookie($this->cookie_session_name, $cidgen, time() + $this->cookie_expired, '/');
    }

    /**
     * @param $login_unchk
     * @param $pass_unchk
     * @return bool
     */
    function Login($login_unchk, $pass_unchk)
    {
        if (!$login_unchk || !$pass_unchk) return false;

        $user = $this->db->query("SELECT id, state, avail, auth_cert_only, blocked_by_tariff, uxt FROM {users} "
                                ."WHERE (BINARY email=? OR BINARY login=?) AND BINARY pass=? AND deleted = 0",
            array($login_unchk, $login_unchk, $pass_unchk), 'row');

        if($user['blocked_by_tariff'] > 0) {
            FlashMessage::set(l('Ваша учетная запись заблокирована из-за выбранного тарифа'), FlashMessage::DANGER);
            return false;
        }
        if ($user['avail'] != 1 || $user['auth_cert_only'] == 1) {
            return false;
        }

        if ($user) {
            $this->SetLogedIn($user);
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    function IfAuth()
    {

        if (isset($_COOKIE[$this->cookie_session_name])) {

            $hashed_cid = md5(get_ip()) . md5(substr($_COOKIE[$this->cookie_session_name], 0, 32));
            $user = $this->db->query("SELECT * FROM {users} WHERE cid = ?", array($hashed_cid), 'row');

            if ( $user && $user['avail'] == 1 &&  $user['blocked_by_tariff'] == 0) {
                // сертификат расшифрован
                if ($this->CheckCert()){

                    //если он не корректный - отказать
                    if (!isset($_SERVER['SSL_CLIENT_S_DN_CN'])
                        || !isset($_SERVER['SSL_CLIENT_M_SERIAL'])) {
                        return false;
                    }

                    //Если логин совпадает, но не совпадает серийник - отказ.
                    //Если логин не совпадает, возможно залогинился админ, а в браузере сертификат менеджера
                    if ($_SERVER['SSL_CLIENT_S_DN_CN'] == $user['login']
                        && $user['auth_cert_serial'] != $_SERVER['SSL_CLIENT_M_SERIAL']){
                        //$all_configs['db']->query("UPDATE {users} SET cid = '' WHERE id = ?i", array($user['id']));
                        //setcookie($this->cookie_session_name, '', time() - $this->cookie_expired);
                        return false;
                    }
                }

//                $this->db->query("UPDATE {users} SET uxt = UNIX_TIMESTAMP() WHERE id = ?i ", array($user["id"]));
                if ( !isset($_SESSION) )
                    session_start();
                $_SESSION['role'] = $user['role'];
                $_SESSION['id'] = $user['id'];
                return $user;
            } elseif ($user['blocked_by_tariff'] > 0) {
                FlashMessage::set(l('Ваша учетная запись заблокирована из-за выбранного тарифа'), FlashMessage::DANGER);
            }
        }
        return false;
    }

    /**
     * @return bool
     */
    public function checkLastLogin()
    {
        if (isset($_COOKIE[$this->cookie_session_name])) {
            $hashed_cid = md5(get_ip()) . md5(substr($_COOKIE[$this->cookie_session_name], 0, 32));
            $user = $this->db->query("SELECT * FROM {users} WHERE cid = ?", array($hashed_cid), 'row');
            $border = strtotime('+4 hours', strtotime('today'));
            if (!empty($user) && time() > $border && $user['uxt'] < $border) {
                return $this->Logout($user);
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function IfAuthCert()
    {

        if($this->CheckCert()) {
            $clientcn = isset($_SERVER['SSL_CLIENT_S_DN_CN']) ? $_SERVER['SSL_CLIENT_S_DN_CN'] : false;
            $email = isset($_SERVER['SSL_CLIENT_S_DN_Email']) ? $_SERVER['SSL_CLIENT_S_DN_Email'] : false;
            $serial = isset($_SERVER['SSL_CLIENT_M_SERIAL']) ? $_SERVER['SSL_CLIENT_M_SERIAL'] : false;

            if (!$clientcn || !$serial || !$email) return false;

            $user = $this->db->query("SELECT id, avail, auth_cert_serial, cid, is_adm
                                FROM {users} WHERE login =? AND email = ?",
                array($clientcn, $email), 'row');

            if ($user['auth_cert_serial'] != $serial
                || $user['avail'] != 1) return false;


            $this->SetLogedIn($user);
            return $user;
        } else {
            return false;
        }
    }

    /**
     * @return bool
     */
    private function CheckCert(){
        return (isset($_SERVER['SSL_CLIENT_VERIFY'])
            && $_SERVER['SSL_CLIENT_VERIFY'] == 'SUCCESS') ? true : false;
    }

    /**
     * @param $user
     * @return bool
     */
    function Logout($user) {
        $id = $user['id'];
        if (is_numeric($id)) {
            $this->db->query("UPDATE {users} SET cid = '' WHERE id = ?i", array($id));
            setcookie($this->cookie_session_name, '', time() - $this->cookie_expired);
            if ( !isset($_SESSION) )
                session_start();
            $_SESSION['role'] ='';
            $_SESSION['id'] = '';
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $oldpass
     * @param $newpass
     * @return bool
     */
    function ChangePass($oldpass, $newpass) {

        $id = $this->IfAuth();
        if (is_numeric($id)) {
            $sql = $this->db->query("SELECT email FROM {users} WHERE id=?i AND pass=? LIMIT 1", array($id, $oldpass), 'ar');
            if ($sql == 1) {
                $sql = $this->db->query("UPDATE {users} SET pass=? WHERE id=?i LIMIT 1", array($newpass, $id), 'ar');
                if ($sql) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}
