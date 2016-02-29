<?php

require_once __DIR__.'/abstract_import_handler.php';

class import_items extends abstract_import_handler
{
    /**
     * @param $rows
     * @return array
     */
    public function run($rows)
    {
        return array(
            'state' => true,
            'message' => $this->gen_result_table($rows)
        );
    }

    /**
     * @todo Implement get_result_row() method.
     *
     * @param $row
     * @return string
     */
    protected function get_result_row($row)
    {
        return "<td>".$row[1]."</td>";
    }
}