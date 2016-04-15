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
    public function getTitle($data)
    {
        return iconv('cp1251', 'utf8', trim($data[0]));
    }

    /**
     * @param $data
     * @return mixed
     */
    public function getCategories($data)
    {
        return array(
            iconv('cp1251', 'utf8', trim($data[1])),
            iconv('cp1251', 'utf8', trim($data[2])),
            iconv('cp1251', 'utf8', trim($data[3])),
            iconv('cp1251', 'utf8', trim($data[4])),
            iconv('cp1251', 'utf8', trim($data[5])),
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

    /**
     * @param $data
     * @return mixed
     */
    public function getCategory($data)
    {
        // TODO: Implement getCategory() method.
    }

    /**
     * @param $data
     * @return mixed
     */
    public function getSubcategories($data)
    {
        // TODO: Implement getSubcategories() method.
    }
}
