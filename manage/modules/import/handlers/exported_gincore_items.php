<?php

require_once __DIR__ . '/abstract_import_provider.php';

class exported_gincore_items extends abstract_import_provider
{
    public $cols = array(
        0 => 'ID',
    );
    /**
     * @var array
     */
    protected $header_row;
    protected $categories = array();
    protected $managers = array();

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->cols = array(
            'category_id' => lq('Категория'),
            'title' => lq('Наименование'),
            'price_purchase' => lq('Цена закупки'),
            'price_wholesale' => lq('Цена оптовая'),
            'price' => lq('Цена розничная'),
//            'manager_id' => l('manager')
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
    public function get_category_id($row)
    {
        $title = trim($this->getColValue('category_id', $row));
        if (isset($this->categories[$title])) {
            $id = $this->categories[$title]['id'];
        } else {
            $id = db()->query('SELECT id FROM {categories} WHERE title=?', array($title))->el();
        }
        return empty($id) ? false : $id;
    }

    /**
     * @param $row
     * @return int
     */
    public function get_manager_id($row)
    {
        $value = trim($this->getColValue('manager_id', $row));
        $manager = $this->findManager($value);
        if (empty($manager)) {
            $id = db()->query('SELECT id FROM {users} WHERE fio=? OR login=? OR email=?',
                array($value, $value, $value))->el();
        } else {
            $id = $manager['id'];
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
     * @param $name
     * @param $arguments
     * @return bool|mixed
     */
    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array($name, $arguments);
        }
        $method = 'get_' . $name;
        if (method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arguments);
        }
        if (isset($this->cols[$name])) {
            return $this->getColValue($name, $arguments[0]);
        }
        return false;
    }

    /**
     * @param $name
     * @param $row
     * @return bool
     */
    private function getColValue($name, $row)
    {
        $colName = $this->cols[$name];
        $col = $this->getColPosition($colName);
        return $col !== false && isset($row[$col]) ? $row[$col] : false;
    }

    /**
     * @param $colName
     * @return bool|int
     */
    private function getColPosition($colName)
    {
        return isset($this->header_row[$colName]) ? $this->header_row[$colName] : false;
    }

    /**
     * @param $value
     * @return array|mixed
     */
    private function findManager($value)
    {
        foreach ($this->managers as $manager) {
            if($manager['fio'] == $value || $manager['login'] == $value || $manager['email'] == $value) {
                return $manager;
            }
        }
        return array();
    }
}
