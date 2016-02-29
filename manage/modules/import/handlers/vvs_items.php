<?php

require_once __DIR__ . '/abstract_import_provider.php';

class vvs_items extends abstract_import_provider
{
    /**
     * @return array
     */
    public function get_cols()
    {
        return array();
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