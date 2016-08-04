<?php

require_once __DIR__ . '/abstract_import_provider.php';

class gincore_orders extends abstract_import_provider
{
    protected $all_configs;
    private $cols = array(
        0 => "Номер заказа",
        1 => "Дата принятия заказа",
        2 => "Дата выдачи заказа",
        3 => "Статус заказа",
        4 => "Принятое устройство",
        5 => "Комплектация устройства",
        6 => "S/N: Устройства",
        7 => "Вид ремонта",
        8 => "Неисправность со слов клиента",
        9 => "Примечание/Внешний вид",
        10 => "Стоимость ремонта",
        11 => "Оплачено",
        12 => "Приемщик",
        13 => "Менеджер",
        14 => "Инженер",
        15 => "ФИО Заказчика",
        16 => "Контактный телефон заказчикa"
    );

    /**
     * remonline_orders constructor.
     * @param $all_configs
     */
    function __construct($all_configs)
    {
        $this->all_configs = $all_configs;
    }

    /**
     * @inheritdoc
     */
    public function check_format($header_row)
    {
        return true;
    }

    /**
     * @return array
     */
    function get_cols()
    {
        return $this->cols;
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_id($data)
    {
        return (int) $data[0];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_date_add($data)
    {
        return $data[1];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_date_end($data)
    {
        return $data[2];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_status_id($data)
    {
        $value = (empty($this->codepage) || $this->codepage == 'utf-8') ? $data[3] : mb_ucfirst(iconv('cp1251', 'utf8', import_helper::remove_whitespace($data[3])));
        foreach ($this->all_configs['configs']['order-status'] as $id => $status) {
            if($status['name'] == $value) {
                return $id;
            }
        }
        return 0;
    }

    /**
     * @param $data
     * @return string
     */
    function get_device($data)
    {
        return (empty($this->codepage) || $this->codepage == 'utf-8') ? $data[4] : iconv('cp1251', 'utf8', $data[4]);
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_equipment($data)
    {
        return (empty($this->codepage) || $this->codepage == 'utf-8') ? $data[5] : iconv('cp1251', 'utf8', $data[5]);
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_serial($data)
    {
        return $data[6];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_type_of_repair($data)
    {
        return mb_ucfirst((empty($this->codepage) || $this->codepage == 'utf-8') ? $data[7] : iconv('cp1251', 'utf8', $data[7]));
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_defect($data)
    {
        return (empty($this->codepage) || $this->codepage == 'utf-8') ? $data[8] : iconv('cp1251', 'utf8', $data[8]);
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_appearance($data)
    {
        return (empty($this->codepage) || $this->codepage == 'utf-8') ? $data[9] : iconv('cp1251', 'utf8', $data[9]);
    }

    /**
     * @param $data
     * @return integer
     */
    function get_summ($data)
    {
        return (int)$data[10];
    }

    /**
     * @param $data
     * @return integer
     */
    function get_summ_paid($data)
    {
        return (int)$data[11];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_acceptor($data)
    {
        return (empty($this->codepage) || $this->codepage == 'utf-8') ? $data[12] : iconv('cp1251', 'utf8', $data[12]);
    }

    /**
     * @return string
     */
    function get_manager($data)
    {
        return (empty($this->codepage) || $this->codepage == 'utf-8') ? $data[13] : iconv('cp1251', 'utf8', $data[13]);
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_engineer($data)
    {
        return (empty($this->codepage) || $this->codepage == 'utf-8') ? $data[14] : iconv('cp1251', 'utf8', $data[14]);
    }


    /**
     * @param $data
     * @return mixed
     */
    function get_client_fio($data)
    {
        return (empty($this->codepage) || $this->codepage == 'utf-8') ? $data[15] : iconv('cp1251', 'utf8', $data[15]);
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_client_phone($data)
    {
        return $data[16];
    }
}
