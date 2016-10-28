<?php

require_once __DIR__ . '/abstract_gincore_import_provider.php';

class exported_gincore_items extends abstract_gincore_import_provider
{
    public $cols = array(
        0 => 'ID',
    );
    protected $categories = array();
    protected $managers = array();

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->cols = array(
            'category' => lq('Категория'),
            'title' => lq('Наименование'),
            'vendor_code' => lq('Артикул'),
            'price_purchase' => lq('Цена закупки'),
            'price_wholesale' => lq('Цена оптовая'),
            'price' => lq('Цена розничная'),
            'percent_from_profit' => lq('% от прибыли'),
            'fixed_payment' => lq('Фиксированная оплата'),
            'balance' => lq('Уведомлять меня об остатке'),
            'minimum_balance' => lq('Неснижаемый остаток'),
            'automargin' => lq('Автонаценка розница'),
            'automargin_type' => lq('В валюте'),
            'wholesale_automargin' => lq('Автонаценка опт'),
            'wholesale_automargin_type' => lq('В валюте'),
//            'manager' => l('manager')
        );

        $arr_additional = array(
            array('label' => lq('% от прибыли'), 'name' => 'percent_from_profit'),
            array('label' => lq('Фиксированная оплата'), 'name' => 'fixed_payment'),
            array('label' => lq('Уведомлять меня об остатке'), 'name' => 'notify_by_balance'),
            array('label' => lq('Неснижаемый остаток'), 'name' => 'minimum_balance'),
            array('label' => lq('Автонаценка розница'), 'name' => 'automargin'),
            array('label' => lq('Автонаценка опт'), 'name' => 'wholesale_automargin'),
        );

        
        $this->categories = db()->query('select id, title from {categories}', array())->assoc('title');
        $this->managers = db()->query('select id, fio, login, email from {users}', array())->assoc('id');
    }

    /**
     * @return array
     */
    public function get_cols()
    {
        return $this->cols;
    }

    /**
     * @inheritdoc
     */
    public function check_format($header_row)
    {
        $this->header_row = array_flip($header_row);
        return true;
    }

    /**
     * @param $row
     * @return int
     */
    public function get_category($row)
    {
        $title = trim($this->getColValue('category', $row));
        if (!empty($title)) {
            if (isset($this->categories[$title])) {
                $id = $this->categories[$title]['id'];
            } else {
                $id = db()->query('SELECT id FROM {categories} WHERE title=?', array($title))->el();
            }
        }
        return empty($id) ? false : $id;
    }

    /**
     * @param $row
     * @return int
     */
    public function get_manager($row)
    {
        $value = trim($this->getColValue('manager', $row));
        if (!empty($value)) {
            $manager = $this->findManager($value);
            if (empty($manager)) {
                $id = db()->query('SELECT id FROM {users} WHERE fio=? OR login=? OR email=?',
                    array($value, $value, $value))->el();
            } else {
                $id = $manager['id'];
            }
        }
        return empty($id) ? false : $id;
    }

    /**
     * @param $row
     * @return int
     */
    public function get_id($row)
    {
        return (int)$row[0];
    }

    /**
     * @param $value
     * @return array|mixed
     */
    private function findManager($value)
    {
        foreach ($this->managers as $manager) {
            if ($manager['fio'] == $value || $manager['login'] == $value || $manager['email'] == $value) {
                return $manager;
            }
        }
        return array();
    }
}
