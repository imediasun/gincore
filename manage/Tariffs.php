<?php

require_once __DIR__ . '/Session.php';
require_once __DIR__ . '/../gincore/vendor/autoload.php';

class Tariffs
{
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
        $data['sign'] = self::getSignature($data);
        if (class_exists('Requests')) {
            $result = Requests::get($api, array('Accept' => 'application/json'), $data);
        } else {
            if ($curl = curl_init()) {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_URL, $api . '?' . (empty($data) ? '' : implode('&', $data)));
                $result = json_decode(curl_exec($curl), true);
                curl_close($curl);
            }
        }

        return empty($result) || !self::checkSignature($result) ? array() : json_decode($result, true);
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
        $data['host'] = $host;
        $data['sign'] = self::getSignature($data);
        if (class_exists('Requests')) {
            $result = Requests::post($api, array('Accept' => 'application/json'), $data);
        } else {
            if ($curl = curl_init()) {
                curl_setopt($curl, CURLOPT_URL, $api . '?host=' . $host);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, (empty($data) ? '' : implode('&', $data)));
                $result = json_decode(curl_exec($curl), true);
                curl_close($curl);
            }
        }

        return empty($result) || !self::checkSignature($result) ? array() : $result;
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
        if ($session->check('last_check_tariff') && $session->get('last_check_tariff') < strtotime('-1 hour')) {
            $response = self::get($api, $host);
            if (empty($response) || !self::validate($response)) {
                throw new Exception('api error');
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
    public static function isNewUserAvailable($api, $host)
    {
        $response = self::get($api, $host, array(
            'add_user' => 1
        ));
        return !empty($response) && $response['available'] == 1;
    }

    /**
     * @param $api
     * @param $host
     * @return array|mixed
     */
    public static function isNewOrderAvailable($api, $host)
    {
        $response = self::get($api, $host, array(
            'add_order' => 1
        ));
        return !empty($response) && $response['available'] == 1;
    }

    /**
     * @param $api
     * @param $host
     * @return array|mixed
     */
    public static function addUser($api, $host)
    {
        return self::post($api, $host, array(
            'add_new_user' => 1
        ));
    }

    /**
     * @param $api
     * @param $host
     * @return array|mixed
     */
    public static function addOrder($api, $host)
    {
        return self::post($api, $host, array(
            'add_new_order' => 1
        ));
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
                l('Текущий тариф'),
                json_encode($response),
                l('Текущий тариф'),
            ))->el();
        } else {
            db()->query("UPDATE {settings} SET value=? WHERE name='tariff'", array(
                json_encode($response),
            ));
        }
    }

    /**
     * @param Session $session
     * @return mixed
     * @throws Exception
     */
    private static function getSavedTariff($session)
    {
        if (!$session->check('tariff')) {
            $response = db()->query("SELECT value FROM {settings} WHERE name='tariff'", array())->el();
            if (empty($response)) {
                throw new Exception('Tariff not set');
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
        return md5(empty($keyAPI) ? json_encode($data) : $keyAPI . json_encode($data));
    }

    /**
     * @param $result
     * @return bool
     */
    private static function checkSignature($result)
    {
        if (empty($result['sign'])) {
            return false;
        }
        $signature = $result['sign'];
        unset($result['sign']);
        return strcmp($signature, self::getSignature($result)) === 0;
    }

    /**
     * @return mixed
     */
    private static function getAPIKey()
    {
        $session = Session::getInstance();
        if ($session->check('api_key')) {
            return $session->get('api_key');
        }
        $keyAPI = db()->query("SELECT value FROM {settings} WHERE name='api_key'")->el();
        $session->set('api_key', $keyAPI);
        return $keyAPI;
    }
}
