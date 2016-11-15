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
        $exploded = explode(' ', trim($date));
        if (empty($exploded[0])) {
            return date('Y-m-d H:i:0');
        }
        $exploded = preg_split("/[-\/\.]/", $exploded[0]);
        $format = 'd.m.y';
        if (isset($exploded[2]) && strlen($exploded[2]) == 4) {
            $format = 'd.m.Y';
        } elseif (strlen($exploded[0]) == 4) {
            $format = 'Y.m.d';
        }
        $date = DateTime::createFromFormat($format, implode('.', $exploded));
        return $date === false ? date('Y-m-d H:i:0') : $date->format('Y-m-d H:i:0');
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