<tr class="remove-marked-object">
    <td class="floatleft">
        <?= show_marked($order['order_id'], 'co', $order['m_id']) ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders') ||
            $this->all_configs['oRole']->hasPrivilege('show-clients-orders')
        ): ?>
            <a href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $order['order_id'] . $get ?>">&nbsp;
                <?= $order['order_id'] ?>
            </a>
            <a class="fa fa-edit"
               href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $order['order_id'] . $get ?>"></a>
        <?php endif; ?>
    </td>
    <td><?= timerout($order['order_id']) ?></td>
    <td><span title="<?= do_nice_date($order['date'], false) ?>"><?= do_nice_date($order['date']) ?></span></td>
    <td>
        <?php if ($order['manager'] == 0 && $this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
            <form method="post" action="<?= $this->all_configs['prefix'] ?>orders/create/<?= $order['order_id'] ?>">
                <input name="accept-manager" type="submit" class="btn btn-accept" value="<?= l('Взять заказ') ?>"/>
                <input type="hidden" name="id" value="<?= $order['order_id'] ?>"/>
            </form>
        <?php else: ?>
            <?= get_user_name($order, 'h_') ?>
        <?php endif; ?>
    </td>
    <td style="color:green" class="center"><?= $order['cashless'] ? l('Безнал') : l('Нал') ?></td>
    <td class="center order-status-col">
        <?= $this->renderFile('orders/_sale_order_status', array(
            'active' => $order['status'],
            'orderId' => $order['order_id'],
            'status' => $this->all_configs['configs']['sale-order-status'],
            'type' => 'sale'
        )); ?>
    </td>
    <td>
        <?php if ($order['delivery_by'] == DELIVERY_BY_COURIER): ?>
            <i class="fa fa-car" aria-hidden="true"></i>
        <?php endif; ?>
        <?php if ($order['delivery_by'] == DELIVERY_BY_POST): ?>
            <i class="fa fa-suitcase" ></i>
        <?php endif; ?>
        <?php if ($order['delivery_by'] == DELIVERY_BY_SELF || $order['sale_type'] == SALE_TYPE_QUICK): ?>
            <?= $accepted ?>
        <?php endif; ?>
    </td>
    <td class="center">
        <?= $ordered ?>
    </td>
    <td class='center' title="<?= implode("\n", $helper->getItemsTooltip($order)) ?>">
        <?= $this->renderFile('helpers/display_order/_items_list', array(
            'count' => count($order['goods']),
            'list' => $helper->getItemsTooltip($order),
            'orderId' => $order['order_id']
        )) ?>
    </td>
    <td class="center <?= ($order['discount'] > 0 ? 'text-danger' : '') ?>">
        <?= ($order['sum'] / 100) ?>
    </td>
    <td class='center'><?= ($order['sum_paid'] / 100) ?></td>
    <td><?= htmlspecialchars($order['o_fio']) ?></td>
    <td><?= $order['o_phone'] ?></td>
    <?php if ($order['sale_type'] == SALE_TYPE_ESHOP): ?>
        <td style="word-wrap:break-word; max-width: 150px"
            title="<?= $helper->getCommentsTooltip($order['order_id']) ?>">
            <div class="overflow-ellipsis"> <?= h($helper->getLastComment($order['order_id'])) ?> </div>
        </td>
    <?php else: ?>
        <td></td>
    <?php endif; ?>
</tr>
