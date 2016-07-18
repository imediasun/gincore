<?php

require_once __DIR__ . '/../../Core/ExportsToXls.php';

class ExportOrdersToXLS extends ExportsToXls
{

    /**
     * @param $xls
     * @param $data
     * @return mixed
     */
    public function makeXLSBody($xls, $data)
    {
        if (empty($data['orders'])) {
            return $xls;
        }
        if ($data['type'] == ORDER_SELL) {
            return $this->makeXLSBodySale($xls, $data['orders']);
        }
        return $this->makeXLSBodyRepair($xls, $data['orders']);
    }

    /**
     * @param $xls
     * @param $orders
     * @return mixed
     */
    protected function makeXLSBodyRepair($xls, $orders)
    {
        global $all_configs;

        $sheet = $xls->getActiveSheet();
        $id = 1;
        foreach ($orders as $order) {

            $tooltip = array();
            if (!empty($order['items'])) {
                foreach ($order['items'] as $item) {
                    $tooltip[] = $item['title'] . ' - ' . $item['count'] . l('шт.');
                }
            }
            $sheet->setCellValueByColumnAndRow(0, (int)$id + 1, $order['order_id']);
            $sheet->setCellValueByColumnAndRow(1, (int)$id + 1, $order['date']);
            $sheet->setCellValueByColumnAndRow(2, (int)$id + 1, get_user_name($order, 'a_'));
            $sheet->setCellValueByColumnAndRow(3, (int)$id + 1,
                $order['manager'] == 0 ? '' : get_user_name($order, 'h_'));
            $sheet->setCellValueByColumnAndRow(4, (int)$id + 1,
                $all_configs['configs']['order-status'][$order['status']]['name']);
            $sheet->setCellValueByColumnAndRow(5, (int)$id + 1, implode("\n", $tooltip));
            $sheet->setCellValueByColumnAndRow(6, (int)$id + 1, h($order['product']) . h($order['note']));
            $sheet->setCellValueByColumnAndRow(7, (int)$id + 1, $order['sum'] / 100);
            $sheet->setCellValueByColumnAndRow(8, (int)$id + 1, $order['sum_paid'] / 100);
            $sheet->setCellValueByColumnAndRow(9, (int)$id + 1, h($order['o_fio']));
            $sheet->setCellValueByColumnAndRow(10, (int)$id + 1, $order['o_phone']);
            $sheet->setCellValueByColumnAndRow(11, (int)$id + 1,
                ($order['urgent'] == 1) ? l('Срочно') : l('Не срочно'));
            $sheet->setCellValueByColumnAndRow(12, (int)$id + 1,
                h($order['wh_title']) . '(' . h($order['location']) . ')');
            $id++;
        }

        return $xls;

    }

    /**
     * @param $xls
     * @param $orders
     * @return mixed
     */
    protected function makeXLSBodySale($xls, $orders)
    {
        global $all_configs;
        $sheet = $xls->getActiveSheet();
        $id = 1;
        foreach ($orders as $order) {
            $delivery = l('Самовывоз');
            if ($order['delivery_by'] == DELIVERY_BY_COURIER) {
                $delivery = l('Курьер');
            }
            if ($order['delivery_by'] == DELIVERY_BY_POST) {
                $delivery = l('Почта');
            }

            $tooltip = array();
            if (!empty($order['items'])) {
                foreach ($order['items'] as $item) {
                    $tooltip[] = $item['title'] . ' - ' . $item['count'] . l('шт.');
                }
            }

            $sheet->setCellValueByColumnAndRow(0, (int)$id + 1, $order['order_id']);
            $sheet->setCellValueByColumnAndRow(1, (int)$id + 1, $order['date']);
            $sheet->setCellValueByColumnAndRow(2, (int)$id + 1, get_user_name($order, 'a_'));
            $sheet->setCellValueByColumnAndRow(3, (int)$id + 1,
                $order['manager'] == 0 ? '' : get_user_name($order, 'h_'));
            $sheet->setCellValueByColumnAndRow(4, (int)$id + 1, $order['cashless'] ? l('Безнал') : l('Нал'));
            $sheet->setCellValueByColumnAndRow(5, (int)$id + 1,
                $all_configs['configs']['sale-order-status'][$order['status']]['name']);
            $sheet->setCellValueByColumnAndRow(6, (int)$id + 1, $delivery);
            $sheet->setCellValueByColumnAndRow(7, (int)$id + 1, implode("\n", $tooltip));
            $sheet->setCellValueByColumnAndRow(8, (int)$id + 1, $order['sum'] / 100);
            $sheet->setCellValueByColumnAndRow(9, (int)$id + 1, $order['sum_paid'] / 100);
            $sheet->setCellValueByColumnAndRow(10, (int)$id + 1, h($order['o_fio']));
            $sheet->setCellValueByColumnAndRow(11, (int)$id + 1, $order['o_phone']);
            $id++;
        }

        return $xls;

    }
}
