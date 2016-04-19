<?php

require_once __DIR__ . '/Core/Session.php';
require_once __DIR__ . '/../gincore/vendor/autoload.php';

class Tariff
{
    /**
     * @return mixed
     */
    public static function current()
    {
        return self::getSavedTariff(Session::getInstance());
    }

    /**
     * @param $api
     * @param $host
     * @return string
     */
    public static function getURL($api, $host)
    {
        try {
            $response = self::get($api, $host, array(
                'act' => 'get_link_to_tariffs_page'
            ));
        } catch (Exception $e) {
            $response = array();
        }
        return empty($response['link']) ? '/' : $response['link'];
    }

    /**
     * @param $result
     * @return mixed
     */
    protected static function process($result)
    {
        $result = gettype($result) == 'string' ? json_decode($result, true) : $result;
        $return = empty($result) || !self::checkSignature($result) ? array() : $result;
        unset($return['signature']);
        return $return;
    }

    /**
     * @param       $api
     * @param       $host
     * @param array $data
     * @return array|mixed
     */
    protected static function get($api, $host, $data = array())
    {
        $result = null;
        $data['host'] = $host;
        $data['key'] = self::getAPIKey();
        $data['signature'] = self::getSignature($data);
        $url = $api . (empty($data) ? '' : '?' . http_build_query($data));
        if (class_exists('Requests')) {
            $response = Requests::get($url, array('Accept' => 'application/json'));
            $result = $response->body;
        } else {
            if ($curl = curl_init()) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_URL, $url);
                $result = curl_exec($curl);
                curl_close($curl);
            }
        }
        return self::process($result);
    }

    /**
     * @param       $api
     * @param       $host
     * @param array $data
     * @return array|mixed
     */
    protected static function post($api, $host, $data = array())
    {
        $result = null;
        $data['key'] = self::getAPIKey();
        $data['host'] = $host;
        $data['signature'] = self::getSignature($data);
        if (class_exists('Requests')) {
            $response = Requests::post($api, array('Accept' => 'application/json'), $data);
            $result = $response->body;
        } else {
            if ($curl = curl_init()) {
                curl_setopt($curl, CURLOPT_URL, $api);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                $result = curl_exec($curl);
                curl_close($curl);
            }
        }
        return self::process($result);
    }

    /**
     * @param $api
     * @param $host
     * @return array|mixed
     * @throws Exception
     */
    public static function load($api, $host)
    {
        $session = Session::getInstance();
        if (!$session->check('last_check_tariff') || $session->get('last_check_tariff') < strtotime('-1 minutes')) {
            $response = self::get($api, $host, array('act' => 'load'));
            if (empty($response) || !self::validate($response)) {
                $response = array(
                    'id' => -1,
                    'name' => l('Стартовый'),
                    'start' => date('d-m-Y H:i'),
                    'period' => date('d-m-Y H:i', strtotime('+30 days')),
                    'number_of_users' => 1,
                    'number_of_orders' => 30
                );
            }
            self::saveTariff($response);
        }
        return self::getSavedTariff($session);
    }

    /**
     * @param $api
     * @param $host
     * @return array|mixed
     */
    public static function isAddUserAvailable($api, $host)
    {
        try {
            $response = self::get($api, $host, array(
                'act' => 'add_user_available',
                'current' => self::getCurrentUsers()
            ));
        } catch (Exception $e) {
            $response = array();
        }
        return !empty($response) && $response['available'] == 1;
    }

    /**
     * @return int
     */
    public static function getCurrentUsers()
    {
        return (int)db()->query('SELECT count(*) FROM {users} WHERE avail=1 AND blocked_by_tariff=0 AND deleted=0')->el();
    }

    /**
     * @param $api
     * @param $host
     * @return array|mixed
     */
    public static function isAddOrderAvailable($api, $host)
    {
        $tariff = self::current();
        if (empty($tariff['start'])) {
            return false;
        }
        try {
            $response = self::get($api, $host, array(
                'act' => 'add_order_available',
                'current' => self::getCurrentOrders($tariff)
            ));
        } catch (Exception $e) {
            $response = array();
        }
        return !empty($response) && $response['available'] == 1;
    }

    /**
     * @param $tariff
     * @return int
     */
    public static function getCurrentOrders($tariff)
    {
        return (int)db()->query('SELECT count(*) FROM {orders} WHERE date_add > ?',
            array($tariff['start']))->el();
    }

    /**
     * @param $api
     * @param $host
     * @return array|mixed
     */
    public static function addUser($api, $host)
    {
        try {
            $response = self::post($api, $host, array(
                'act' => 'add_new_user'
            ));
        } catch (Exception $e) {
            $response = array();
        }
        return $response;
    }

    /**
     * @param $api
     * @param $host
     * @return array|mixed
     */
    public static function addOrder($api, $host)
    {
        try {
            $response = self::post($api, $host, array(
                'act' => 'add_new_order'
            ));
        } catch (Exception $e) {
            $response = array();
        }
        return $response;
    }

    /**
     * @param $response
     * @return bool
     */
    public static function validate($response)
    {
        /**
         * айди тарифа
         * название тарифа
         * дата старта
         * период действия
         * количество сотрудников
         * кол-во заказов
         * */
        return (!empty($response)
            && is_array($response)
            && isset($response['id'])
            && isset($response['name'])
            && isset($response['start'])
            && isset($response['period'])
            && isset($response['number_of_users'])
            && isset($response['number_of_orders'])
            && isset($response['key'])
        );
    }

    /**
     * @param $response
     */
    private static function saveTariff($response)
    {
        $count = db()->query("SELECT count(*) FROM {settings} WHERE name='tariff'", array())->el();
        if (empty($count)) {
            db()->query("INSERT INTO {settings} (description, name, value, title) VALUES (?, 'tariff', ?, ?)", array(
                lq('Текущий тариф'),
                json_encode($response),
                lq('Текущий тариф'),
            ));
        } else {
            db()->query("UPDATE {settings} SET value=? WHERE name='tariff'", array(
                json_encode($response),
            ));
        }
        Session::getInstance()->set('last_check_tariff', time());
        Session::getInstance()->set('tariff', $response);
    }

    /**
     * @param Session $session
     * @return mixed
     * @throws Exception
     */
    private static function getSavedTariff($session)
    {
        if (!$session->check('tariff')) {
            $response = db()->query("SELECT `value` FROM {settings} WHERE name='tariff'", array())->el();
            if (empty($response)) {
                $response = json_encode(array());
            }
            $session->set('last_check_tariff', time());
            $session->set('tariff', json_decode($response, true));
        }
        return $session->get('tariff');
    }

    /**
     * @param $data
     * @return string
     */
    private static function getSignature($data)
    {
        $keyAPI = self::getAPIKey();
        return md5($keyAPI . implode(';', $data) . $keyAPI);
    }

    /**
     * @param $result
     * @return bool
     */
    private static function checkSignature($result)
    {
        if (empty($result['signature'])) {
            return false;
        }
        $signature = trim($result['signature']);
        unset($result['signature']);
        return strcmp($signature, self::getSignature($result)) === 0;
    }

    /**
     * @return mixed
     */
    private static function getAPIKey()
    {
        global $all_configs;
        $keyAPI = !empty($all_configs['settings']['api_key']) ? $all_configs['settings']['api_key'] : null;
        if (!empty($keyAPI)) {
            return $keyAPI;
        }
        $session = Session::getInstance();
        if ($session->check('api_key')) {
            return $session->get('api_key');
        }
        $keyAPI = db()->query("SELECT value FROM {settings} WHERE name='api_key'")->el();
        $session->set('api_key', $keyAPI);
        return $keyAPI;
    }

    /**
     * @param $tariff
     * @return array|mixed
     */
    public static function blockUsers($tariff)
    {
        /** выбираем ид незаблокированного активного суперюзера */
        $adminId = db()->query('SELECT u.id
                    FROM {users} as u, {users_permissions} as p, {users_role_permission} as l
                    WHERE p.link IN (?l) AND l.permission_id=p.id AND u.role=l.role_id AND u.avail=1 AND u.deleted=0 AND u.blocked_by_tariff <> 2',
            array(array('site-administration')))->el();

        /** сбрасываем блокировку всем юзерам, которые не блокировались в ручном режиме */
        /** необходимо на случай если в новом тарифе допустимо большее число неблокированных юзеров */
        db()->query("UPDATE {users} SET blocked_by_tariff=?i WHERE NOT blocked_by_tariff=?i",
            array(USER_ACTIVATED_BY_TARIFF, USER_DEACTIVATED_BY_TARIFF_MANUAL));

        /** выбираем активных юзеров которые не будут блокироваться в автоматическом режиме  */
        $userIds = db()->query('SELECT id FROM {users} WHERE (deleted=0 AND avail=1 AND NOT id=?i) OR blocked_by_tariff=?i LIMIT ?i',
            array($adminId, USER_DEACTIVATED_BY_TARIFF_MANUAL, $tariff['number_of_users'] - 1))->col();

        $query = '';
        if (!empty($userIds)) {
            $query = db()->makeQuery('NOT id in (?li) AND', array($userIds));
        }

        /** блокируем оставшихся */
        db()->query("UPDATE {users} SET blocked_by_tariff=?i WHERE ?q NOT id=?i",
            array(USER_DEACTIVATED_BY_TARIFF_AUTOMATIC, $query, $adminId));
    }

}
