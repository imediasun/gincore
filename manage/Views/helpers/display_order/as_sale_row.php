<tr class="remove-marked-object">
    <td class="floatleft">
        <?= $accepted ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders') ||
            $this->all_configs['oRole']->hasPrivilege('show-clients-orders')
        ): ?>
            <a href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $order['order_id'] . $get ?>">&nbsp;
                <?= $order['order_id'] ?>
            </a>
            <a class="fa fa-edit"
               href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $order['order_id'] . $get ?>"></a>
        <?php endif; ?>
        <?php //= show_marked($order['order_id'], 'co', $order['m_id']) ?>
        <i class="glyphicon glyphicon-move icon-move cursor-pointer" data-o_id="<?= $order['order_id'] ?>"
           onclick="alert_box(this, false, 'stock_move-order', undefined, undefined, 'messages.php')"
           title="<?= l('Переместить заказ') ?>"></i>
    </td>
    <td><?= timerout($order['order_id']) ?></td>
    <td><span title="<?= do_nice_date($order['date'], false) ?>"><?= do_nice_date($order['date']) ?></span></td>
    <td>
        <?php if ($order['manager'] == 0 && $this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
            <form method="post" action="<?= $this->all_configs['prefix'] ?>orders/create/<?= $order['order_id'] ?>">
                <input name="accept-manager" type="submit" class="btn btn-accept"
                       value="<?= l('Взять заказ') ?>"/>
                <input type="hidden" name="id" value="<?= $order['order_id'] ?>"/>
            </form>
        <?php else: ?>
            <?= get_user_name($order, 'h_') ?>
        <?php endif; ?>
    </td>
    <td style="color:green" class="center"><?= $order['cashless'] ? l('Безнал') : l('Нал') ?></td>
    <td class="center">
        <?= $this->renderFile('orders/_sale_order_status', array(
            'active' => $order['status'],
            'orderId' => $order['order_id']
        )); ?>
    </td>
    <td class="center">
        <?= $ordered ?>
    </td>
    <td class='center' title="<?= $helper->getItemsTooltip($order) ?>"><?= count($order['goods']) ?><?= l('шт.'); ?> <span class="caret"></span></td>
    <td class="center <?= ($order['discount'] > 0 ? 'text-danger' : '') ?>">
        <?= ($order['sum'] / 100) ?>
    </td>
    <td class='center'><?= ($order['sum_paid'] / 100) ?></td>
    <td><?= htmlspecialchars($order['o_fio']) ?></td>
    <td><?= $order['o_phone'] ?></td>
    <?php if ($order['sale_type'] == SALE_TYPE_ESHOP): ?>
        <td title="<?= $helper->getCommentsTooltip($order['order_id']) ?>">
            <?= h($helper->getLastComment($order['order_id'])) ?>
        </td>
    <?php else: ?>
        <td></td>
    <?php endif; ?>
</tr>
