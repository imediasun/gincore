<?php

require_once __DIR__ . '/Session.php';

class Tariffs
{
    public static function get($api, $host, $data = array())
    {
        $result = null;
        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_URL, $api . '?host=' . $host . (empty($data) ? '' : implode('&', $data)));
            $result = curl_exec($curl);
            curl_close($curl);
        }

        return empty($result) ? array() : json_decode($result);
    }

    public static function post($api, $host, $data = array())
    {
        $result = null;
        if ($curl = curl_init()) {
            curl_setopt($curl, CURLOPT_URL, $api . '?host=' . $host);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, (empty($data) ? '' : implode('&', $data)));
            $result = curl_exec($curl);
            curl_close($curl);
        }

        return empty($result) ? array() : json_decode($result);
    }

    public static function load($api, $host)
    {
        $session = Session::getInstance();
        if ($session->check('last_check_tariff') && $session->get('last_check_tariff') < strtotime('-1 hour')) {
            $response = self::get($api, $host);
            if (empty($response)) {
                throw new Exception('api error');
            }
            /**
             * айди тарифа
             * дата старта
             * период действия
             * количество сотрудников
             * кол-во заказов
             * */
            db()->query();

            $session->set('last_check_tariff', time());
            $session->set('tariff', $response);
        } else {
            $response = $session->get('tariff');
        }
        return $response;
    }

    public static function newUserAvailable($api, $host)
    {

    }

    public static function nesOrderAvailable($api, $host)
    {

    }

    public static function addOrder($api, $host)
    {

    }
}