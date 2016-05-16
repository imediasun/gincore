<tr class="remove-marked-object">
    <td class="floatleft">
        <?= $accepted ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders') ||
            $this->all_configs['oRole']->hasPrivilege('show-clients-orders')
        ): ?>
            <a href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $order['order_id'] . $get ?>">&nbsp;
                <?= $order['order_id'] ?></a>
            <a class="fa fa-edit"
               href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $order['order_id'] . $get ?>"></a>
        <?php endif; ?>
    </td>
    <td><?= timerout($order['order_id']) ?></td>
    <td><span title="<?= do_nice_date($order['date'], false) ?>"><?= do_nice_date($order['date']) ?></span></td>
    <td><?= get_user_name($order, 'a_') ?></td>
    <td>
        <?php if ($order['manager'] == 0 && $this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
            <form method="post" action="<?= $this->all_configs['prefix'] ?>orders/create/<?= $order['order_id'] ?>">
                <input name="accept-manager" type="submit" class="btn btn-accept"
                       value="<?= l('Взять заказ') ?>"/><input type="hidden" name="id"
            </form>
        <?php else: ?>
            <?= get_user_name($order, 'h_') ?>
        <?php endif; ?>
    </td>
    <td class="center">
        <?= $this->renderFile('orders/_sale_order_status', array(
            'active' => $order['status'],
            'orderId' => $order['order_id'],
            'status' => $this->all_configs['configs']['order-status']
        )); ?>
    </td>
    <td class="center"><?= $ordered ?></td>
    <td><?= h($order['product']) ?> <?= h($order['note']) ?></td>

    <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
        <td class="center <?= ($order['discount'] > 0 ? 'text-danger' : '') ?>"><?= ($order['sum'] / 100) ?> </td>
        <td class="center"><?= ($order['sum_paid'] / 100) ?></td>
    <?php else: ?>
        <td class="center"><?= ($order['sum'] == $order['sum_paid'] && $order['sum'] > 0) ? l('да') : '' ?></td>
    <?php endif; ?>
    <td><?= h($order['o_fio']) ?></td>
    <td><?= $order['o_phone'] ?></td>
    <td class="<?= $order['urgent'] == 1 ? 'text-danger' : '' ?>">
        <?= ($order['urgent'] == 1) ? l('Срочно') : l('Не срочно') ?>
    </td>
    <td><?= h($order['wh_title']) . ' ' . h($order['location']) ?></td>
</tr>
