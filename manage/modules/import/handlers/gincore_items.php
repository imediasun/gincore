<?php

require_once __DIR__ . '/abstract_import_provider.php';
require_once __DIR__ . '/items_inteface.php';

class gincore_items extends abstract_import_provider implements ItemsInterface
{
    public $cols = array(
        0 => 'наименование товара',
        1 => 'категория товара',
        2 => 'подкатегория товара 1',
        3 => 'подкатегория товара 2',
        4 => 'подкатегория товара 3',
        5 => 'подкатегория товара 4',
        6 => 'розничная цена',
        7 => 'закупочная цена',
    );

    /**
     * @param $data
     * @return mixed
     */
    function getTitle($data)
    {
        return iconv('cp1251', 'utf8', trim($data[0]));
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
     * @param $data
     * @return array
     */
    function getSubcategories($data)
    {
        return array(
            $data[2],
            $data[3],
            $data[4],
            $data[5],
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
        return (int)$data['6'] * 100;
    }

    /**
     * @param $data
     * @return int
     */
    public function getPurchase($data)
    {
        return (int)$data['7'] * 100;
    }

    /**
     * @param $data
     * @return int
     */
    public function getWholesale($data)
    {
        return 0;
    }
}
