<?php

require_once __DIR__.'/abstract_import_provider.php';

class gincore_clients extends abstract_import_provider
{
    private $cols = array(
        0 => 'fio',
        1 => 'contragent type',
        2 => 'phone',
        3 => 'email',
        4 => 'legal_address',
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
        return explode(',', $data[2]);
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
        return $data[4];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_contractor_id($data)
    {
        return $data[1];
    }
}