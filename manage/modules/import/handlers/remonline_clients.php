<?php

require_once __DIR__.'/abstract_import_provider.php';

class remonline_clients extends abstract_import_provider
{
    private $cols = array(
        0 => 'Имя',
        1 => 'Телефон',
        2 => 'Адрес',
        3 => 'Email',
    );

    /**
     * @return array
     */
    function get_cols()
    {
        return $this->cols;
    }

    /**
     * @param $data
     * @return array
     */
    function get_phones($data)
    {
        return explode(',', $data[1]);
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_fio($data)
    {
        return $data[0];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_email($data)
    {
        return $data[3];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_address($data)
    {
        return $data[2];
    }

}