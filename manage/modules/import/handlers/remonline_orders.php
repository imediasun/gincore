<?php

require_once __DIR__.'/abstract_import_provider.php';

class remonline_orders extends abstract_import_provider
{
    private $cols = array(
        0 => 'Принят',
        1 => 'Принял',
        2 => '№ заказа',
        3 => 'Тип заказа',
        4 => 'Статус',
        5 => 'Имя клиента',
        6 => 'Телефон',
        7 => 'Адрес',
        8 => 'Email',
        9 => 'Тип устройства',
        10 => 'Бренд',
        11 => 'Модель',
        12 => 'Серийный номер',
        13 => 'Комплектация',
        14 => 'Внешний вид',
        15 => 'Неисправность со слов заказчика',
        16 => 'Крайний срок',
        17 => 'Ориентировочная цена',
        18 => 'Аванс',
        19 => 'Срочно',
        20 => 'Готов',
        21 => 'Инженер',
        22 => 'Выдан',
        23 => 'Выдал',
        24 => 'Себестоимость запчастей',
        25 => 'Оплачено',
        26 => 'Заметки приемщика',
        27 => 'Заметки исполнителя',
        28 => 'Выполненные работы',
        29 => 'Установленные запчасти',
        30 => 'Вердикт / рекомендации клиенту',
        31 => 'Забрать у клиента',
        32 => 'Доставить клиенту',
    );

    private $statuses = array(
        'Новый' => 'order-status-new',
        'Согласовано, передано в работу' => 'order-status-work',
        'Мастер назначен' => 'order-status-waits',
        'Клиент отказался' => 'order-status-refused',
        'Не починится' => 'order-status-unrepairable',
//        'order-status-nowork',
//        'order-status-issued',
//        'order-status-rework',
        'На выдачу' => 'order-status-ready',
//        'order-status-service',
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
        preg_match_all('/A([0-9]+)\/?[0-9]*/', $data[2], $ids);
        $id = $ids[1][0];
        return $id;
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_client_fio($data)
    {
        return $data[5];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_client_phone($data)
    {
        return $data[6];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_status_id($data)
    {
        $status_id = $this->all_configs['configs'][$this->statuses[import_helper::remove_whitespace($data[4])]];
        return $status_id;
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_date_add($data)
    {
        return $data[0];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_accepter($data)
    {
        return $data[1];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_engineer($data)
    {
        return $data[21];
    }

    /**
     * @return string
     */
    function get_manager()
    {
        return '';
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_address($data)
    {
        return $data[7];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_category($data)
    {
        return $data[9];
    }

    /**
     * @param $data
     * @return string
     */
    function get_device($data)
    {
        return $data[10] . ' ' . $data[11];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_serial($data)
    {
        return $data[12];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_equipment($data)
    {
        return $data[13];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_appearance($data)
    {
        return $data[14];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_defect($data)
    {
        return $data[15];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_date_end($data)
    {
        return $data[16];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_summ($data)
    {
        return $data[17];
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_summ_prepaid($data)
    {
        return $data[18];
    }

    /**
     * @param $data
     * @return array
     */
    function get_comments($data)
    {
        $comments = array(
            'acceptor' => array(),
            'engineer' => array()
        );
        if ($data[26]) {
            $comments['acceptor'][] = $data[26];
        }
        if ($data[27]) {
            $comments['engineer'][] = $data[27];
        }
        if ($data[28]) {
            $comments['engineer'][] = $data[28];
        }
        if ($data[31] === 'true') {
            $comments['acceptor'][] = lq('Забрать у клиента');
        }
        return $comments;
    }
}