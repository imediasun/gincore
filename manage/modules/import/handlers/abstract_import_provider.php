<?php

require_once __DIR__ . '/../import_helper.php';

abstract class abstract_import_provider
{
    public $codepage;

    /**
     * @param $header_row
     * @return bool
     */
    public function check_format($header_row)
    {
        $cols = $this->get_cols();
        if (!empty($cols)) {
            foreach ($header_row as $col => $name) {
                if (empty($name)) {
                    continue;
                }
                if (!isset($cols[$col])
                    || import_helper::remove_whitespace($cols[$col]) != import_helper::remove_whitespace($name)
                ) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * @inheritdoc
     */
    public function define_codepage($header_row)
    {
        $this->codepage = null;
        $list = array('utf-8', 'windows-1251');
        foreach ($list as $item) {
            $sample = iconv($item, $item, $header_row[0]);
            if (md5($sample) == md5($header_row[0])) {
                $this->codepage = $item;
            }
        }
    }

    /**
     * @return array
     */
    abstract public function get_cols();
}