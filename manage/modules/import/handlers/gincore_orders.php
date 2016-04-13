<?php

require_once __DIR__ . '/abstract_import_provider.php';

class gincore_orders extends abstract_import_provider
{
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
        11 => "Приемщик",
        12 => "Менеджер",
        13 => "Инженер",
        14 => "ФИО Заказчика",
        15 => "Контактный телефон заказчикa"
    );

    private $statuses = array(
        'Новый' => 'order-status-new',
        'Согласовано, передано в работу' => 'order-status-work',
        'Мастер назначен' => 'order-status-waits',
        'Клиент отказался' => 'order-status-refused',
        'Не починится' => 'order-status-unrepairable',
        'На выдачу' => 'order-status-ready',
        'Согласовать с клиентом' => 'order-status-agreement'
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
        preg_match_all('/A([0-9]+)\/?[0-9]*/', $data[0], $ids);
        $id = $ids[1][0];
        return $id;
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
        $status_id = $this->all_configs['configs'][$this->statuses[import_helper::remove_whitespace($data[3])]];
        return $status_id;
    }

    /**
     * @param $data
     * @return string
     */
    function get_device($data)
    {
        return $data[4];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_equipment($data)
    {
        return $data[5];
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
        return $data[7];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_defect($data)
    {
        return $data[8];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_appearance($data)
    {
        return $data[9];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_summ($data)
    {
        return $data[10];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_accepter($data)
    {
        return $data[11];
    }

    /**
     * @return string
     */
    function get_manager($data)
    {
        return $data[12];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_engineer($data)
    {
        return $data[13];
    }


    /**
     * @param $data
     * @return mixed
     */
    function get_client_fio($data)
    {
        return $data[14];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_client_phone($data)
    {
        return $data[15];
    }
}
