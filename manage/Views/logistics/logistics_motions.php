<div class="row-fluid">
    <?= $filters_block ?>

    <?php if ($chains_moves): ?>
        <table class="table chains table-compact">
            <tbody>


            </tbody>
        </table>
        <?= page_block($count_page, $chains_moves_count_all, '#motions') ?>
    <?php else: ?>
        <div class="col-md-12 center">
            <div style="padding-top: 5%; padding-bottom: 5%;">
                <span style="border-bottom: 1px grey dashed;">
                    <?= l('Нет цепочек') ?>
                </span>
            </div>
        </div>
    <?php endif; ?>
</div>


<?php




$rows = '';
if ($chains_moves) {

    $i = 1;
    foreach ($chains_moves as $move) {
        switch ($move['item_type']) {
            case 1: // order
                $link = '<a href="' . $this->all_configs['prefix'] . 'orders/create/' . $move['item_id'] . '">Заказ №' . $move['item_id'] . '</a>';
                break;
            case 2: // изделие (серийник)
                $move['serial'] = '';
                $link = suppliers_order_generate_serial($move, true, true);
                // значит что изделие (запчасть) двигается в заказе
                if ($move['from_order_id']) {
                    $link .= ' 
                                <span style="color:#666">
                                    (в заказе 
                                     <a style="color:#666" href="' . $this->all_configs['prefix'] . 'orders/create/' . $move['from_order_id'] . '">' .
                        '№' . $move['from_order_id'] .
                        '</a>)
                                </span>';
                }
                break;
        }
        $row_class = '';
        switch ($move['state']) {
            case -1: // не закрыта
                $row_class = 'error';
                break;
            case 0: // закрыта
                $row_class = 'info';
                break;
            case 1: // открыта
                break;
        }
        $rows .= '
                    <tr class="' . $row_class . '">
                        <td>' . ($i++) . '</td>
                        <td class="well">
                            ' . $link . '
                        </td>
                        <td' . ($move['from_move_id'] ? ' class="success"' : '') . '>
                            ' . $warehouses[$chains[$move['chain_id']]['from_wh_id']]['title'] . ' (' . $warehouses[$chains[$move['chain_id']]['from_wh_id']]['locations'][$chains[$move['chain_id']]['from_wh_location_id']]['name'] . ')' . '
                            <span title="' . $move['from_date_move'] . '">' . do_nice_date($move['from_date_move']) . '</span>
                        </td>
                        <td class="chain-body-arrow"></td>
                        <td' . ($move['logistics_move_id'] ? ' class="success"' : '') . '>
                            ' . $warehouses[$chains[$move['chain_id']]['logistic_wh_id']]['title'] . ($chains[$move['chain_id']]['logistic_wh_location_id'] ? ' (' . $warehouses[$chains[$move['chain_id']]['logistic_wh_id']]['locations'][$move['logistic_wh_location_id']]['name'] . ')' : '') . '
                            <span title="' . $move['log_date_move'] . '">' . do_nice_date($move['log_date_move']) . '</span>
                        </td>
                        <td class="chain-body-arrow"></td>
                        <td' . ($move['to_move_id'] ? ' class="success"' : '') . '>
                            ' . $warehouses[$chains[$move['chain_id']]['to_wh_id']]['title'] . ' (' . $warehouses[$chains[$move['chain_id']]['to_wh_id']]['locations'][$chains[$move['chain_id']]['to_wh_location_id']]['name'] . ')
                            <span title="' . $move['to_date_move'] . '">' . do_nice_date($move['to_date_move']) . '</span>
                        </td>
                    </tr>
                    <tr><td colspan="7"></td></tr>
                ';
    }

} else {
    $rows = l('Нет цепочек');
}

?>