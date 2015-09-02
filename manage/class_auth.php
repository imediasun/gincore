<?php

class Auth { //класс авторизации
#db settings

    var $cookie_session_name = '';
    var $cookie_expired = 1209600;
    var $allow_cert_auth = true;

    public $db;

    public function __construct($db)
    {
        $this->db = $db;
        if (!isset($_SESSION))
            session_start();
    }

    private function SetLogedIn($user)
    {
        mt_srand((double) microtime() * 1000000);
        $cidgen = md5(mt_rand() + mt_rand() . get_ip());
        $hashed_cid = md5(get_ip()) . md5($cidgen);
        $sql = $this->db->query("UPDATE {users} SET cid = ?, uxt = UNIX_TIMESTAMP() WHERE id = ?i", array($hashed_cid, $user["id"]));
        setcookie($this->cookie_session_name, '', time() - $this->cookie_expired, '/');
        setcookie($this->cookie_session_name, $cidgen, time() + $this->cookie_expired, '/');
    }

    function Login($login_unchk, $pass_unchk)
    {
        if (!$login_unchk || !$pass_unchk) return false;

        $user = $this->db->query("SELECT id, state, avail, auth_cert_only FROM {users} WHERE email=? AND pass=? ",
            array($login_unchk, $pass_unchk), 'row');

        if ($user['avail'] != 1 || $user['auth_cert_only'] == 1) {
            return false;
        }

        if ($user) {
            $this->SetLogedIn($user);
            return true;
        }

        return false;
    }

    function IfAuth()
    {

        if (isset($_COOKIE[$this->cookie_session_name])) {

            $hashed_cid = md5(get_ip()) . md5(substr($_COOKIE[$this->cookie_session_name], 0, 32));
            $user = $this->db->query("SELECT * FROM {users} WHERE cid = ?", array($hashed_cid), 'row');

            if ( $user && $user['avail'] == 1 ) {


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

                $this->db->query("UPDATE {users} SET uxt = UNIX_TIMESTAMP() WHERE id = ?i ", array($user["id"]));
                if ( !isset($_SESSION) )
                    session_start();
                $_SESSION['role'] = $user['role'];
                $_SESSION['id'] = $user['id'];
                return $user;
            } else { #num_rows
                return false;
            }

        } else { #isset cookie
            return false;
        }
    }

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

    private function CheckCert(){
        return (isset($_SERVER['SSL_CLIENT_VERIFY'])
            && $_SERVER['SSL_CLIENT_VERIFY'] == 'SUCCESS') ? true : false;
    }

    function Logout($all_configs) {

        $ifauth = $this->IfAuth($all_configs);
        $id = $ifauth['id'];
        if (is_numeric($id)) {
            $all_configs['db']->query("UPDATE {users} SET cid = '' WHERE id = ?i", array($id));
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
