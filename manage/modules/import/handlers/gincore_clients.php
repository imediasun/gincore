<?php

require_once __DIR__.'/abstract_import_provider.php';

class gincore_clients extends abstract_import_provider
{
    protected $contractors;
    private $cols = array(
        0 => 'fio',
        1 => 'contragent type',
        2 => 'phone',
        3 => 'email',
        4 => 'legal_address',
    );

    public function __construct()
    {
        $this->contractors = db()->query('SELECT id, title FROM {contractors}', array())->assoc('title');
    }

    private $availableContractors = array(
        'Поставщик',
        'Сотрудник',
        'Покупатель'
    );

    /**
     * @return array
     */
    public function get_cols()
    {
        return $this->cols;
    }

    /**
     * @param $data
     * @return array
     */
    public function get_phones($data)
    {
        $phones = $data[2];
        return explode(',', preg_replace('/[\+\-\(\)]/', '', $phones));
    }

    /**
     * @param $data
     * @return mixed
     */
    public function get_fio($data)
    {
        return iconv('cp1251', 'utf8', $data[0]);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function get_email($data)
    {
        return $data[3];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_address($data)
    {
        return iconv('cp1251', 'utf8', $data[4]);
    }

    /**
     * @param $data
     * @return mixed
     */
    public function get_contractor_id($data)
    {
        $type = ucfirst(iconv('CP1251', 'UTF-8', $data[1]));
        if(in_array($type, $this->availableContractors) && isset($this->contractors[$type])) {
            return $this->contractors[$type]['id'];
        }
        return '';
    }

    /**
     * @inheritdoc
     */
    public function check_format($row)
    {
        return true;
    }
}