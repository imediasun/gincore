<?php

require_once __DIR__ . '/abstract_import_provider.php';

abstract class abstract_gincore_import_provider extends abstract_import_provider
{
    public $cols = array(
    );
    /**
     * @var array
     */
    protected $header_row;

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
        $colPosition = $this->getColPosition($this->cols[$name]);
        if (!empty($colPosition) && method_exists($this, $method)) {
            return call_user_func_array(array($this, $method), $arguments);
        }
        if (array_key_exists($name, $this->cols)) {
            return $this->getColValue($name, $arguments[0]);
        }
        return false;
    }

    /**
     * @param $name
     * @param $row
     * @return bool
     */
    public function getColValue($name, $row)
    {
        $col = $this->getColPosition($this->cols[$name]);
        return $col !== false && isset($row[$col])  && !empty($row[$col])? $row[$col] : false;
    }

    /**
     * @param $colName
     * @return bool|int
     */
    private function getColPosition($colName)
    {
        return isset($this->header_row[$colName])? $this->header_row[$colName] : false;
    }
}