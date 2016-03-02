<?php

require_once __DIR__ . '/../import_helper.php';

abstract class abstract_import_provider
{
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
     * @return array
     */
    abstract public function get_cols();
}