<tr class="remove-marked-object">
    <td class="floatleft <?= isset($columns['npp'])?'': 'hide' ?>">
        <?php if ($order['home_master_request'] == 1): ?>
            <i style="color:<?= $color ?>; font-size: 10px"
               title="<?= $order['hmr_address'] ?>, <?= $order['hmr_date'] ?>"
               class="fa fa-car"></i>
        <?php endif; ?>
        <?= $accepted ?>
        <?= show_marked($order['order_id'], 'co', $order['m_id']) ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders') ||
            $this->all_configs['oRole']->hasPrivilege('show-clients-orders')
        ): ?>
            <a href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $order['order_id'] . $get ?>">&nbsp;
                <?= $order['order_id'] ?></a>
            <a class="fa fa-edit"
               href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $order['order_id'] . $get ?>"></a>
        <?php endif; ?>
    </td>
    <td class="<?= isset($columns['notice'])?'': 'hide' ?>"><?= timerout($order['order_id']) ?></td>
    <td class="<?= isset($columns['date'])?'': 'hide' ?>"><span title="<?= do_nice_date($order['date'], false) ?>"><?= do_nice_date($order['date']) ?></span></td>
    <td class="<?= isset($columns['accepter'])?'': 'hide' ?>" title="<?= get_user_name($order, 'a_') ?>">
        <span class="visible-lg"><?= cut_string(get_user_name($order, 'a_'), 20) ?></span>
        <span class="hidden-lg"><?= cut_string(get_user_name($order, 'a_'), 10) ?></span>
    </td>
    <td class="<?= isset($columns['manager'])?'': 'hide' ?>" title="<?= get_user_name($order, 'h_') ?>">
        <?php if ($order['manager'] == 0 && $this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
            <form method="post" action="<?= $this->all_configs['prefix'] ?>orders/create/<?= $order['order_id'] ?>">
                <input name="accept-manager" type="submit" class="btn btn-accept" value="<?= l('Взять заказ') ?>"/>
                <input type="hidden" name="id" value="<?= $order['order_id'] ?>"/>
            </form>
        <?php else: ?>
            <span class="visible-lg"><?= cut_string(get_user_name($order, 'h_'), 20) ?></span>
            <span class="hidden-lg"><?= cut_string(get_user_name($order, 'h_'), 10) ?></span>
        <?php endif; ?>
    </td>
    <td class="<?= isset($columns['engineer'])?'': 'hide' ?>" title="<?= get_user_name($order, 'e_') ?>"><?= mb_strimwidth(get_user_name($order, 'e_'), 0, 30, "...") ?></td>
    <td class="center order-status-col <?= isset($columns['status'])?'': 'hide' ?>">
        <?= $this->renderFile('orders/_sale_order_status', array(
            'active' => $order['status'],
            'orderId' => $order['order_id'],
            'status' => $statuses,
            'showPayForm' => $order['sum'] - $order['sum_paid'] - $order['discount'],
            'type' => 'repair'
        )); ?>
    </td>
    <td class="center <?= isset($columns['components'])?'': 'hide' ?>"><?= $ordered ?></td>
    <td class="center <?= isset($columns['services'])?'': 'hide' ?>"><?= $services ?></td>
    <td class="<?= isset($columns['device'])?'': 'hide' ?>" title="<?=  h($order['product']) . h($order['note']) ?>">
        <span class="visible-lg"><?= cut_string($order['product'] . $order['note'], 20) ?></span>
        <span class="hidden-lg"><?= cut_string($order['product'] . $order['note'], 10) ?></span>
    </td>

    <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
        <td class="center <?= ($order['discount'] > 0 ? 'text-danger' : '') ?> <?= isset($columns['amount'])?'': 'hide' ?>"><?= ($order['sum'] / 100) ?> </td>
        <td class="center <?= isset($columns['paid'])?'': 'hide' ?>"><?= ($order['sum_paid'] / 100) ?></td>
    <?php else: ?>
        <td class="center"><?= ($order['sum'] == $order['sum_paid'] && $order['sum'] > 0) ? l('да') : '' ?></td>
    <?php endif; ?>
    <td class="<?= isset($columns['client'])?'': 'hide' ?>" title="<?= h($order['o_fio']) ?>">
        <span class="visible-lg"><?= cut_string($order['o_fio'], 20) ?></span>
        <span class="hidden-lg"><?= cut_string($order['o_fio'], 10) ?></span>
    </td>
    <td class="<?= isset($columns['phone'])?'': 'hide' ?>">
        <?php if ($this->all_configs['configs']['can_see_client_infos']): ?>
            <?= $order['o_phone'] ?>
        <?php endif; ?>
    </td>
    <td class="<?= $order['urgent'] == 1 ? 'text-danger' : '' ?> <?= isset($columns['terms'])?'': 'hide' ?>">
        <?= ($order['urgent'] == 1) ? l('Срочно') : l('Не срочно') ?>
    </td>
    <td class="<?= isset($columns['location'])?'': 'hide' ?>">
        <span class="visible-lg"><?= cut_string($order['wh_title'] . ' ' . $order['location'], 30) ?></span>
        <span class="hidden-lg"><?= cut_string($order['wh_title'] . ' ' . $order['location'], 15) ?></span>
    </td>
    <td class="<?= isset($columns['sn'])?'': 'hide' ?>">
        <?= h($order['serial']) ?>
    </td>
    <td class="<?= isset($columns['repair'])?'': 'hide' ?>">
        <?php
        switch ($order['repair']) {
            case 1:
                echo l('Гарантийный');
                break;
            case 2:
                echo l('Доработка');
                break;
            default:
                echo l('Платный');
        } ?>
    </td>
    <td class="<?= isset($columns['date_end'])?'': 'hide' ?>">
        <?= do_nice_date($order['date_readiness']) ?>
    </td>
    <td class="<?= isset($columns['warranty'])?'': 'hide' ?>">
        <?= h($order['warranty']) ?>
    </td>
    <td class="<?= isset($columns['adv_channel'])?'': 'hide' ?>">
        <?= get_service('crm/calls')->get_referrer($order['referer_id']) ?>
    </td>
    <td></td>
</tr>
