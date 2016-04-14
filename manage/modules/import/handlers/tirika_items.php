<?php

require_once __DIR__ . '/abstract_import_provider.php';
require_once __DIR__ . '/items_inteface.php';

class tirika_items extends abstract_import_provider implements ItemsInterface
{
    public $cols = array(
        '1' => 'категория товара',
        '2' => 'наименование товара',
        '3' => 'розничная цена',
        '4' => 'валюта розничной цены',
        '5' => 'остаток на складе',
        '6' => 'закупочная цена',
        '7' => 'валюта закупки',
    );

    /**
     * @param $data
     * @return mixed
     */
    function getTitle($data)
    {
        return iconv('cp1251', 'utf8', trim($data[2]));
    }

    /**
     * @param $data
     * @return mixed
     */
    function getCategory($data)
    {
        $categories = explode('\\', $data[1]);
        return iconv('cp1251', 'utf8', trim($categories[0]));
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

    /**
     * @param $data
     * @return int
     */
    public function getPrice($data)
    {
        return (int) $data['3'] * 100;
    }

    /**
     * @param $data
     * @return int
     */
    public function getPurchase($data)
    {
        return (int) $data['7'] * 100;
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
