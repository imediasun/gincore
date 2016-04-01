<?php


class tariffs
{
    public static function get($api, $host)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL,$api);
        $result=curl_exec($ch);
        curl_close($ch);

        return json_decode($result);
    }

    public static function post($api, $data)
    {

    }

    public static function load($api, $host)
    {
       $response = self::get($api, $host); 
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