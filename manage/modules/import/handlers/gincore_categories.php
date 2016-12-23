<?php

require_once __DIR__ . '/abstract_gincore_import_provider.php';

class gincore_categories extends abstract_gincore_import_provider
{
    public $cols = array();
    protected $categories = array();
    protected $managers = array();

    /**
     * @inheritdoc
     */
    public function __construct()
    {
        parent::__construct();
        $this->cols = array(
            'title' => lq('Категория'),
            'parent_id' => lq('Родитель'),
            'content' => lq('Описание'),
            'information' => lq('Важная информация'),
            'percent_from_profit' => lq('зп, % от прибыли'),
            'fixed_payment' => lq('зп, фиксированная оплата'),
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
     * @return array
     */
    public function get_translated_cols()
    {
        return array_values($this->get_cols());
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
     * @return string
     */
    public function get_title($row)
    {
        $title = trim($this->getColValue('title', $row));

        return (empty($this->codepage) || $this->codepage == 'utf-8') ? $title : iconv('cp1251', 'utf8',
            trim($title));
    }

    /**
     * @param $row
     * @return string
     */
    public function get_content($row)
    {
        $content = trim($this->getColValue('content', $row));

        return (empty($this->codepage) || $this->codepage == 'utf-8') ? $content : iconv('cp1251', 'utf8',
            trim($content));
    }

    /**
     * @param $row
     * @return string
     */
    public function get_information($row)
    {
        $info = trim($this->getColValue('information', $row));
        return (empty($this->codepage) || $this->codepage == 'utf-8') ? $info : iconv('cp1251', 'utf8',
            trim($info));
    }

    /**
     * @param $row
     * @return int
     */
    public function get_parent_id($row)
    {
        $title = trim($this->getColValue('parent_id', $row));
        return (empty($this->codepage) || $this->codepage == 'utf-8') ? $title : iconv('cp1251', 'utf8',
            trim($title));
    }
}
