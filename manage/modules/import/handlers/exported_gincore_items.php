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

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->cols = array(
            'category_id' => l('Категория'),
            'title' => l('Наименование'),
            'price_purchase' => l('Цена закупки'),
            'price_wholesale' => l('Цена оптовая'),
            'price' => l('Цена розничная'),
//            'manager_id' => l('manager')
        );
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
        $value = trim($this->getColValue('category_id', $row));
        $id = db()->query('SELECT id FROM {categories} WHERE title=?', array($value))->el();
        return empty($id) ? false : $id;
    }

    /**
     * @param $row
     * @return int
     */
    public function get_manager_id($row)
    {
        $value = trim($this->getColValue('manager_id', $row));
        $id = db()->query('SELECT id FROM {users} WHERE fio=? OR login=? OR email=?', array($value, $value, $value))->el();
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
}
