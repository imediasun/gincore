<?php

require_once __DIR__ . '/../import_helper.php';
require_once __DIR__.'/../../../Core/ExportsToXls.php';

abstract class abstract_import_provider
{
    public $codepage;

    /**
     * abstract_import_provider constructor.
     */
    public function __construct()
    {
    }

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
                break;
            }
        }
    }

    /**
     * @return array
     */
    abstract public function get_cols();

    /**
     * @return array
     */
    public function get_translated_cols() {
        return $this->get_cols();
    }

    /**
     * @param $data
     */
    public function example($data) {
        $export = new ExportsToXls();
        $xls = $export->makeXLSTitle($export->getXLS(l('Образец')), l('Образец'), $this->get_translated_cols());
        $export->outputXLS($export->makeXLSBody($xls, $data), 'example');
    }
}