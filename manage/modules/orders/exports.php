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
        return $this->makeXLSBodyRepair($xls, $data['orders'], $data['columns']);
    }

    /**
     * @param $xls
     * @param $orders
     * @return mixed
     */
    protected function makeXLSBodyRepair($xls, $orders, $columns)
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
            $j = 0;
            if (isset($columns['npp'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1, $order['order_id']);
                $j++;
            }

            if (isset($columns['date'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1, $order['date']);
                $j++;
            }

            if (isset($columns['accepter'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1, get_user_name($order, 'a_'));
                $j++;
            }

            if (isset($columns['manager'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1,
                    $order['manager'] == 0 ? '' : get_user_name($order, 'h_'));
                $j++;
            }

            if (isset($columns['engineer'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1, get_user_name($order, 'e_'));
                $j++;
            }

            if (isset($columns['status'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1,
                    $all_configs['configs']['order-status'][$order['status']]['name']);
                $j++;
            }

            if (isset($columns['components'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1, implode("\n", $tooltip));
                $j++;
            }

            if (isset($columns['device'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1, h($order['product']) . h($order['note']));
                $j++;
            }

            if (isset($columns['amount'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1, $order['sum'] / 100);
                $j++;
            }

            if (isset($columns['paid'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1, $order['sum_paid'] / 100);
                $j++;
            }

            if (isset($columns['client'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1, h($order['o_fio']));
                $j++;
            }

            if (isset($columns['phone'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1, $order['o_phone']);
                $j++;
            }

            if (isset($columns['terms'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1,
                    ($order['urgent'] == 1) ? l('Срочно') : l('Не срочно'));
                $j++;
            }

            if (isset($columns['location'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1,
                    h($order['wh_title']) . '(' . h($order['location']) . ')');
                $j++;
            }

            if (isset($columns['sn'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1, h($order['serial']));
                $j++;
            }

            if (isset($columns['repair'])) {
                switch ($order['repair']) {
                    case 1:
                        $repair = l('Гарантийный');
                        break;
                    case 2:
                        $repair = l('Доработка');
                        break;
                    default:
                        $repair = l('Платный');
                }
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1, h($repair));
                $j++;
            }

            if (isset($columns['date_end'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1, h($order['date_readiness']));
                $j++;
            }

            if (isset($columns['warranty'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1, h($order['warranty']));
                $j++;
            }

            if (isset($columns['adv_channel'])) {
                $sheet->setCellValueByColumnAndRow($j, (int)$id + 1,
                    h(get_service('crm/calls')->get_referrer($order['referer_id'])));
            }
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
