<?php

require_once __DIR__ . '/../Core/Helper.php';

class DisplayOrder extends Helper
{
    /**
     * @param $order
     * @param $statuses
     * @return string
     */
    public function asSaleRow($order, $statuses)
    {
        $status = '<span class="muted">' . l('Сообщите менеджеру') . '</span>';
        if (array_key_exists($order['status'], $statuses)) {
            $status_name = $statuses[$order['status']]['name'];
            $status_color = $statuses[$order['status']]['color'];
            $status = '<span style="color:#' . $status_color . '">' . $status_name . '</span>';
        }

        $ordered = '';
        if ($order['status'] == $this->all_configs['configs']['order-status-waits'] && count($order['goods']) > 0) {
            $ordered = str_repeat(' <i class="fa fa-minus-circle text-danger pull-right"></i> ',
                count($order['goods']) - count($order['finish']));
            if (count($order['finish']) > 0) {
                $ordered .= str_repeat(' <i class="fa fa-plus-circle text-success pull-right"></i> ',
                    count($order['finish']));
            }
        }

        $color = preg_match('/^#[a-f0-9]{6}$/i', trim($order['color'])) ? trim($order['color']) : '#000000';
        $accepted = mb_strlen($order['courier'],
            'UTF-8') > 0 ? '<i style="color:' . $color . ';" title="' . l('Курьер забрал устройство у клиента') . '" class="fa fa-truck"></i> ' : '';
        $accepted .= $order['np_accept'] == 1 ? '<i title="' . l('Принято через почту') . '" class="fa fa-suitcase text-danger"></i> ' :
            '<i style="color:' . $color . ';" title="' . l('Принято в') . ' ' . htmlspecialchars($order['aw_wh_title']) . '" class="' . htmlspecialchars($order['icon']) . '"></i> ';

        $get = '?' . get_to_string($_GET);

        return $this->view->renderFile('helpers/display_order/as_sale_row', array(
            'get' => $get,
            'status' => $status,
            'order' => $order,
            'ordered' => $ordered,
            'accepted' => $accepted,
            'color' => $color,
            'helper' => $this,
            'statuses' => $statuses
        ));
    }

    /**
     * @param $order
     * @param $columns
     * @return string
     */
    public function asRepairRow($order, $columns, $statuses)
    {
        $status = '<span class="muted">' . l('Сообщите менеджеру') . '</span>';
        if (array_key_exists($order['status'], $statuses)) {
            $status_name = $statuses[$order['status']]['name'];
            $status_color = $statuses[$order['status']]['color'];
            $status = '<span style="color:#' . $status_color . '">' . $status_name . '</span>';
        }

        $ordered = '';
        if ($order['status'] == $this->all_configs['configs']['order-status-waits'] && count($order['goods']) > 0) {
            $ordered = str_repeat(' <i class="fa fa-minus-circle text-danger pull-right"></i> ',
                count($order['goods']) - count($order['finish']));
            if (count($order['finish']) > 0) {
                $ordered .= str_repeat(' <i class="fa fa-plus-circle text-success pull-right"></i> ',
                    count($order['finish']));
            }
        }
        $services = '';
        if (count($order['services']) > 0) {
            foreach ($order['services'] as $service) {
                $services .= ' <i class="fa fa-plus-circle text-success pull-right" title="' . $service['title'] . '"></i> ';
            }
        }

        $color = preg_match('/^#[a-f0-9]{6}$/i', trim($order['color'])) ? trim($order['color']) : '#000000';
        $accepted = mb_strlen($order['courier'],
            'UTF-8') > 0 ? '<i style="color:' . $color . ';" title="' . l('Курьер забрал устройство у клиента') . '" class="fa fa-truck"></i> ' : '';
        $accepted .= $order['np_accept'] == 1 ? '<i title="' . l('Принято через почту') . '" class="fa fa-suitcase text-danger"></i> ' :
            '<i style="color:' . $color . ';" title="' . l('Принято в') . ' ' . htmlspecialchars($order['aw_wh_title']) . '" class="' . htmlspecialchars($order['icon']) . '"></i> ';

        $get = '?' . get_to_string($_GET);
        return $this->view->renderFile('helpers/display_order/as_repair_row', array(
            'get' => $get,
            'status' => $status,
            'order' => $order,
            'ordered' => $ordered,
            'accepted' => $accepted,
            'color' => $color,
            'helper' => $this,
            'columns' => $columns,
            'statuses' => $statuses,
            'services' => $services
        ));
    }

    /**
     * @param $order
     * @return array
     */
    public function getItemsTooltip($order)
    {
        $tooltip = array();
        if (!empty($order['items'])) {
            foreach ($order['items'] as $item) {
                $tooltip[] = $item['title'] . ' - ' . $item['count'] . l('шт.');
            }
        }
        return $tooltip;
    }

    /**
     * @param $orderId
     * @return mixed
     */
    public function getLastComment($orderId)
    {
        return $this->all_configs['db']->query('SELECT oc.text
                FROM {orders_comments} as oc 
                WHERE oc.order_id=?i AND oc.private=1 ORDER BY oc.date_add DESC LIMIT 1', array($orderId))->el();
    }

    /**
     * @param $orderId
     * @return string
     */
    public function getCommentsTooltip($orderId)
    {
        $comments = $this->all_configs['db']->query('SELECT oc.date_add, oc.text, u.fio, u.phone, u.login, u.email, oc.id
                FROM {orders_comments} as oc LEFT JOIN {users} as u ON u.id=oc.user_id
                WHERE oc.order_id=?i AND oc.private=1 ORDER BY oc.date_add DESC', array($orderId))->assoc();
        $tooltip = '';
        if (!empty($comments)) {
            foreach ($comments as $comment) {
                $tooltip .= $comment['text'];
                $tooltip .= "\n";
            }
        }
        return $tooltip;
    }
}
