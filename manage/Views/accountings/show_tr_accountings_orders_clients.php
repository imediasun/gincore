<?php if ($type == 1): ?>
<?php $class = $chain['return'] == 0 && $chain['delivery_cost'] > $chain['delivery_paid'] ? '' : 'class="success"'; ?>
<td <?= $class ?>><?= l('Оплата за доставку'); ?>
<?php elseif ($type == 2): ?>
<?php $class = $chain['return'] == 0 && $chain['payment_cost'] > $chain['payment_paid'] ? '' : 'class="success"'; ?>
<td <?= $class ?>><?= l('Оплата за комиссию'); ?>
<?php else: ?>
<?php $class = $chain['return'] == 0 && $chain['price'] > $chain['paid'] ? '' : 'class="success"'; ?>
<td <?= $class ?>>
    <a href="<?= $this->all_configs['prefix'] ?>products/create/<?= $chain['goods_id'] ?>">
        <?= htmlspecialchars($chain['g_title']) ?>
    </a>
    <?php endif; ?>
</td>
<td <?= $class ?>><?= get_user_name($chain) ?></td>
<td <?= $class ?>><?= get_user_name($chain, 'u_') ?></td>
<td <?= $class ?>>
    <span title="<?= do_nice_date($chain['date_add'], false) ?>"><?= do_nice_date($chain['date_add']) ?></span>
</td>
<td <?= $class ?>>
    <span title="<?= do_nice_date($chain['date_accept'], false) ?>"><?= do_nice_date($chain['date_accept']) ?></span>
</td>
<td <?= $class ?>>
    <a href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $chain['order_id'] ?>">
        <?= htmlspecialchars($chain['order_id']) ?>
    </a>
</td>
<?php if ($type == 1): ?>
    <td <?= $class ?>><?= show_price($chain['delivery_cost']) ?></td>
    <td <?= $class ?>><?= show_price($chain['delivery_paid']) ?></td>
<?php elseif ($type == 2): ?>
    <td <?= $class ?>><?= show_price($chain['payment_cost']) ?></td>
    <td <?= $class ?>><?= show_price($chain['payment_paid']) ?></td>
<?php else: ?>
    <td <?= $class ?>><?= show_price($chain['price']) ?></td>
    <td <?= $class ?>><?= show_price($chain['paid']) ?></td>
<?php endif; ?>
<td <?= $class ?>>
    <?php if ($chain['return'] == 1 && $chain['paid'] > 0 && $type == 0): ?>
        <input type="button" class="btn btn-xs" value="<?= l('Выдать оплату') ?>"
               onclick="pay_client_order(this, 1, <?= $chain['order_id'] ?>, <?= $chain['b_id'] ?>)"/>
    <?php endif; ?>
    <?php if ($chain['return'] == 0 && $chain['price'] > $chain['paid'] && $type == 0): ?>
        <input type="button" class="btn btn-xs" value="<?= l('Принять оплату') ?>"
               onclick="pay_client_order(this, 2, <?= $chain['order_id'] ?>, <?= $chain['b_id'] ?>)"/>
    <?php elseif ($chain['return'] == 0 && $chain['delivery_cost'] > $chain['delivery_paid'] && $type == 1): ?>
        <input type="button" class="btn btn-xs" value="<?= l('Принять оплату') ?>"
               onclick="pay_client_order(this, 2, <?= $chain['order_id'] ?>, <?= $chain['b_id'] ?>, 'delivery')"/>
    <?php elseif ($chain['return'] == 0 && $chain['payment_cost'] > $chain['payment_paid'] && $type == 2): ?>
        <input type="button" class="btn btn-xs" value="<?= l('Принять оплату') ?>"
               onclick="pay_client_order(this, 2, <?= $chain['order_id'] ?>, <?= $chain['b_id'] ?>, 'payment')"/>
    <?php endif; ?>
</td>
