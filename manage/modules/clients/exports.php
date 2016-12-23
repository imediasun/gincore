<?php

require_once __DIR__ . '/../../Core/ExportsToXls.php';

class ExportClientsToXLS extends ExportsToXls
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
        foreach ($data as $client) {

            $sheet->setCellValueByColumnAndRow(0, (int)$id + 1, $client['id']);
            $sheet->setCellValueByColumnAndRow(1, (int)$id + 1, $client['title']);
            $sheet->setCellValueByColumnAndRow(2, (int)$id + 1, $client['fio']);
            $sheet->setCellValueByColumnAndRow(3, (int)$id + 1, $client['person'] != CLIENT_IS_LEGAL ? lq('Физ') : lq('Юр'));
            $sheet->setCellValueByColumnAndRow(4, (int)$id + 1, $client['phones']);
            $sheet->setCellValueByColumnAndRow(5, (int)$id + 1, $client['legal_address']);
            $sheet->setCellValueByColumnAndRow(6, (int)$id + 1, $client['residential_address']);
            $sheet->setCellValueByColumnAndRow(7, (int)$id + 1, $client['date_add']);
            $sheet->setCellValueByColumnAndRow(8, (int)$id + 1, $client['reg_data_1']);
            $sheet->setCellValueByColumnAndRow(9, (int)$id + 1, $client['reg_data_2']);
            $sheet->setCellValueByColumnAndRow(10, (int)$id + 1, $client['note']);
            $id++;
        }

        return $xls;
    }
}
