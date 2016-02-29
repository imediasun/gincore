<?php

class import_helper
{
    /**
     * форматируем дату
     * @param $date
     * @return bool|string
     */
    public static function format_date($date)
    {
        $date = str_replace('/', '-', $date);
        return date('Y-m-d H:i:s', strtotime($date));
    }

    /**
     * чистит телефон, оставляет только цифры
     *
     * @param $phone
     * @return mixed
     */
    public static function clear_phone($phone)
    {
        return preg_replace("/[^0-9\+]/", "", $phone);
    }

    /**
     * чистит лишние пробелы между словами + с конца и начала
     *
     * @param $string
     * @return string
     */
    public static function remove_whitespace($string)
    {
        return trim(preg_replace('/\s+/', ' ', $string));
    }
}