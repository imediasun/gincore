<?php

require_once __DIR__.'/abstract_import_provider.php';

class gincore_clients extends abstract_import_provider
{
    private $cols = array(
        0 => 'id',
        1 => 'phones',
        2 => 'email',
        3 => 'fio',
        4 => 'legal_address',
        5 => 'date_add',
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
        return $data[3];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_email($data)
    {
        return $data[2];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_address($data)
    {
        return $data[4];
    }
}