<?php

class Exports
{
    private $objPHPExcel;
    private $types = array(
        'Excel2007' => array('extension' => 'xlsx', 'type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        'Excel5' => array('extension' => 'xls', 'type' => 'application/vnd.ms-excel'),
        'CSV' => array('extension' => 'csv', 'type' => 'text/csv'),
        //'HTML'
        //'Excel2003XML',
        //'OOCalc',
        //'SYLK',
        //'Gnumeric',
        //'PDF'?
    );
    protected $filename = 'export';

    function __construct()
    {
        // Set time Limit
        set_time_limit(0);

        // Set Memory Limit 1.0
        ini_set("memory_limit", "500M"); // set your memory limit in the case of memory problem

        /** Include path **/
        //ini_set('include_path', ini_get('include_path').';../Classes/');

        //$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_discISAM;
        //$cacheSettings = array('dir'  => '/usr/local/tmp'); // If you have a large file you can cache it optional
        //PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);

        /** PHPExcel */
        include __DIR__ . '/../manage/classes/PHPExcel.php';

        // Create new PHPExcel object
        $this->objPHPExcel = new PHPExcel();

        $this->setProperties();
    }

    function setProperties($properties = array())
    {
        // set active sheet
        $this->setActiveSheet();

        // Set properties
        $this->objPHPExcel->getProperties()->setCreator("creator");
        $this->objPHPExcel->getProperties()->setLastModifiedBy("creator");
        $this->objPHPExcel->getProperties()->setTitle("Office");
        $this->objPHPExcel->getProperties()->setSubject("Office");
        $this->objPHPExcel->getProperties()->setDescription("document for Office");

        // Rename sheet
        $this->objPHPExcel->getActiveSheet()->setTitle('Simple');
    }

    function setActiveSheet($index = 0)
    {
        $this->objPHPExcel->setActiveSheetIndex($index);
    }

    function build($data = null)
    {
        // add data
        if (is_array($data)) {
            $r = '1';
            foreach ($data as $row) {
                $c = 'A';
                // body
                foreach ($row as $key=>$cell) {
                    $this->objPHPExcel->getActiveSheet()->SetCellValue($c . '1', $key);
                    $r = $r == '1' ? ($r + 1) : $r;
                    $this->implode_row($cell, $c, $r);
                    $c++;
                }
                $r++;
            }
        }
        $this->save();
    }

    /*private function on_server($objWriter)
    {
        $objWriter->save(str_replace('.php', '.xlsx', __FILE__));
    }*/

    private function implode_row($cell, $c, $r)
    {
        if (is_array($cell)) {

        } else {
            if (filter_var($cell, FILTER_VALIDATE_URL)) {
                $this->objPHPExcel->getActiveSheet()->SetCellValue($c . $r, $cell);
                $this->objPHPExcel->getActiveSheet()->getCell($c . $r)->getHyperlink()->setUrl($cell);
            } else {
                $this->objPHPExcel->getActiveSheet()->SetCellValue($c . $r, $cell);
            }
        }
    }

    private function download($objWriter, $type)
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: ' . $type);
        header('Content-Disposition: attachment;filename="' . $this->filename . '"');
        header('Cache-Control: max-age=0');
        $objWriter->save('php://output');
        exit;
    }

    private function save($type = 'Excel2007')
    {
        reset($this->types);
        // type
        $writerType = array_key_exists($type, $this->types) ? $type : key($this->types);
        // filename
        $this->filename .= '(' . date("d-m-Y") . ').' . $this->types[$writerType]['extension'];
        // writer
        $objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, $writerType);
        // download
        $this->download($objWriter, $this->types[$writerType]['type']);
    }
}