<?php

require_once __DIR__ . '/abstract_import_provider.php';

class vvs_items extends abstract_import_provider
{
    public $cols = array(
        1 => 'Item title',
        3 => 'Category'
    );

    /**
     * @param $data
     * @return mixed
     */
    function get_title($data)
    {
        return iconv('cp1251', 'utf8', trim($data[1]));
    }

    /**
     * @param $data
     * @return mixed
     */
    function get_category($data)
    {
        return iconv('cp1251', 'utf8', trim($data[3]));
    }

    /**
     * @return array
     */
    public function get_cols()
    {
        return $this->cols;
    }

    /**
     * @param $header_row
     * @return bool
     */
    public function check_format($header_row)
    {
        return true;
    }
}