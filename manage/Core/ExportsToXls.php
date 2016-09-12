<?php

class ExportsToXls
{
    /**
     * @param $xls
     * @param $data
     * @return mixed
     */
    public function makeXLSBody($xls, $data)
    {
        if (empty($data)) {
            return $xls;
        }

        $sheet = $xls->getActiveSheet();
        $id = 1;
        foreach ($data as $row) {

            $col = 0;
            foreach ($row as $item) {

                $sheet->setCellValueByColumnAndRow($col, (int)$id + 1, $item);
                $col++;
            }
            $id++;
        }

        return $xls;
    }

    /**
     * @param $name
     * @return PHPExcel
     */
    public function getXLS($name)
    {
        require_once(__DIR__ . '/../classes/PHPExcel.php');
        require_once(__DIR__ . '/../classes/PHPExcel/Writer/Excel5.php');
        $xls = new PHPExcel();
        $xls->createSheet($name);
        $xls->setActiveSheetIndex(0);
        return $xls;
    }

    /**
     * @param $xls
     * @param $sheetName
     * @param $title
     * @return mixed
     */
    public function makeXLSTitle($xls, $sheetName, $title)
    {
        $sheet = $xls->getActiveSheet();
        $sheet->setTitle($sheetName);
        $sheet->getColumnDimensionByColumn(0)->setAutoSize(true);

        foreach ($title as $id => $name) {
            $cell = $sheet->setCellValueByColumnAndRow($id, 1, $name, true);
            $sheet->getColumnDimensionByColumn($id)->setAutoSize(true);
            $sheet->getStyle($cell->getCoordinate())->getFill()
                ->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $sheet->getStyle($cell->getCoordinate())->getFill()
                ->getStartColor()->setRGB('EEEEEE');
            $sheet->getStyle($cell->getCoordinate())->getAlignment()
                ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle($cell->getCoordinate())->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
        }
        return $xls;
    }

    /**
     * @param $xls
     */
    public function outputXLS($xls, $fileName='report')
    {
        $out = new PHPExcel_Writer_Excel5($xls);
        header('Content-type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename='{$fileName}.xls'");
        $out->save('php://output');
        exit();
    }
}