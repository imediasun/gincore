<?php

require_once __DIR__ . '/abstract_import_provider.php';
require_once __DIR__ . '/items_inteface.php';

class vvs_items extends abstract_import_provider implements ItemsInterface
{
    public $cols = array(
        1 => 'Item title',
        3 => 'Category'
    );

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

    /**
     * @param $data
     * @return mixed
     */
    function getTitle($data)
    {
        return iconv('cp1251', 'utf8', trim($data[1]));
    }

    /**
     * @param $data
     * @return mixed
     */
    function getCategory($data)
    {
        return trim(iconv('cp1251', 'utf8', trim($data[3])), '[]');
    }

    /**
     * @param $data
     * @return int
     */
    public function getPrice($data)
    {
        return 100;
    }

    /**
     * @param $data
     * @return int
     */
    public function getPurchase($data)
    {
        return 0;
    }

    /**
     * @param $data
     * @return int
     */
    public function getWholesale($data)
    {
        return 0;
    }

    /**
     * @param $data
     * @return mixed
     */
    public function getSubcategories($data)
    {
        return array();
    }

    /**
     * @param $data
     * @return mixed
     */
    public function getCategories($data)
    {
        // TODO: Implement getCategories() method.
    }
}