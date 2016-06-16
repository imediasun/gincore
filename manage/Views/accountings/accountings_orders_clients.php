<?php if ($orders): ?>
    <table class="table table-bordered table-medium">
        <thead>
        <tr>
            <td></td>
            <td><?= l('Наименование') ?></td>
            <td><?= l('ФИО клиента') ?></td>
            <td><?= l('Кто запросил') ?></td>
            <td><?= l('Дата запроса') ?></td>
            <td><?= l('Заказ') ?></td>
            <td><?= l('Сумма') ?></td>
            <td><?= l('Оплачено') ?></td>
            <td><?= l('Скидка') ?></td>
            <td><?= l('К оплате') ?></td>
            <td><?= l('Управление') ?></td>
        </tr>
        </thead>
        <tbody>
        <?php $i = 1; ?>
        <?php foreach ($orders as $order): ?>
            <tr class="">
                <td><?= $i++ ?></td>
                <td>
                    <?php if (isset($order['goods']) && count($order['goods']) > 0): ?>
                        <?php foreach ($order['goods'] as $product): ?>
                            <?php $href = $this->all_configs['prefix'] . 'products/create/' . $product['goods_id']; ?>
                            <a href="<?= $href ?>"><?= htmlspecialchars($product['title']) ?></a><br/>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </td>
                <td><?= get_user_name($order) ?></td>
                <td><?= get_user_name($order) ?></td>
                <td>
                <span title="<?= do_nice_date($order['date_add'],
                    false) ?>"><?= do_nice_date($order['date_add']) ?></span>
                </td>
                <?php $href = $this->all_configs['prefix'] . 'orders/create/' . $order['id']; ?>
                <td>
                    <a href="<?= $href ?>">№<?= $order['id'] ?></a>
                </td>
                <?php if (intval($order['prepay']) > 0 && intval($order['prepay']) > intval($order['sum_paid'])): ?>
                    <td><?= show_price($order['prepay']) ?></td>
                    <td><?= show_price($order['sum_paid']) ?></td>
                <?php else: ?>
                    <td><?= show_price($order['sum']) ?></td>
                    <td><?= show_price($order['sum_paid']) ?></td>
                <?php endif; ?>
                <td><?= show_price($order['discount']) ?></td>
                <td><?= show_price($order['sum'] - $order['sum_paid'] - $order['discount']) ?></td>
                <td>

                    <?php if (intval($order['sum']) < intval($order['sum_paid'])): ?>
                        <input type="button" class="btn btn-xs" value="<?= l('Выдать оплату') ?>"
                               onclick="pay_client_order(this, 1, <?= $order['id'] ?>)"/>
                    <?php endif; ?>
                    <?php $type = $order['type'] == ORDER_REPAIR ? 'repair' : 'sale' ?>
                    <?php if (intval($order['sum']) > (intval($order['sum_paid']) + $order['discount'])): ?>
                        <?php if (intval($order['prepay']) > 0 && intval($order['prepay']) > intval($order['sum_paid'])): ?>
                            <input type="button" class="btn btn-xs" value=" <?= l('Принять предоплату') ?>"
                                   onclick="pay_client_order(this, '<?= $type ?>', <?= $order['id'] ?>, 0, 'prepay')"/>
                        <?php else: ?>
                            <input type="button" class="btn btn-xs" value="<?= l('Принять оплату') ?>"
                                   onclick="pay_client_order(this, '<?= $type ?>', <?= $order['id'] ?>)"/>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?= page_block($count_page, $count, '#a_orders-clients'); ?>
<?php else: ?>
    <p class="text-error"><?= l('Нет заказов') ?></p>
<?php endif; ?>

