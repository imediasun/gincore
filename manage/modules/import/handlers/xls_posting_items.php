<?php

require_once __DIR__ . '/abstract_gincore_import_provider.php';

class xls_posting_items extends abstract_gincore_import_provider
{
    public $cols = array();
    public $goods = array();

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->cols = array(
            'title' => lq('Наименование'),
            'vendor_code' => lq('Артикул'),
            'quantity' => lq('Количество'),
            'price' => lq('Закупочная цена'),
        );
        $this->goods = db()->query('select id, title from {goods}', array())->assoc('title');
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
    public function get_item_id($row)
    {
        $title = trim($this->getColValue('title', $row));
        if (!empty($title)) {
            if (isset($this->goods[$title])) {
                $id = $this->goods[$title]['id'];
            } else {
                $id = db()->query('SELECT id FROM {goods} WHERE title=? OR vendor_code=?', array($title, $title))->el();
            }
        }
        return empty($id) ? false : $id;
    }

    /**
     * @return array
     */
    public function get_translated_cols()
    {
        return array_values($this->get_cols());
    }
}
