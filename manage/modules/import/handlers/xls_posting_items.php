<?php

require_once __DIR__ . '/abstract_gincore_import_provider.php';

class xls_posting_items extends abstract_gincore_import_provider
{
    /**
     * @return array
     */
    public function get_cols()
    {
        // TODO: Implement get_cols() method.
    }

    /**
     * @inheritdoc
     */
    public function check_format($row)
    {
        return true;
    }
}
