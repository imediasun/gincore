<?php $count_goods = 0; ?>
<table class="table table-compact table-of-order">
    <thead>
    <tr>
        <td></td>
        <td><?= l('номер заказа') ?></td>
        <td><?= l('Устройство') ?></td>
        <td><?= l('Запчасти') ?></td>
        <td><?= l('Работа') ?></td>
        <td><?= l('Цена продажи') ?></td>
        <?php if ($isAdmin): ?>
            <td><?= l('Стоимость работ') ?> <?= InfoPopover::getInstance()->createQuestion('l_accountings_report_table_cost_info') ?></td>
        <?php endif; ?>
        <?php if ($isAdmin): ?>
            <td><?= l('Цена запчасти') ?> <?= InfoPopover::getInstance()->createQuestion('l_accountings_report_table_part_cost_info') ?></td>
            <td class="reports_turnover_profit invisible"><?= l('Операц. приб.') ?></td>
            <td class="reports_turnover_margin invisible"><?= l('Наценка %') ?></td>
        <?php endif; ?>
    </tr>
    </thead>
    <tbody>
    <?php $services_prices = 0; ?>
    <?php foreach ($amounts['orders'] as $order): ?>
        <tr class="<?= $order['has_return'] ? 'orange' : '' ?>">
            <td>
                <?php if ($order['has_return']): ?>
                    <b class="text-danger"> <?= l('возврат') ?> </b>
                <?php endif; ?>
                <?php if ($order['has_more'] > 0): ?>
                    <b class="text-success"> <?= l('ряд транзакций') ?> </b>
                <?php endif; ?>
            </td>
            <td>
                <a href="<?= $this->all_configs['prefix'] . 'orders/create/' . $order['order_id'] ?>"><?= $order['order_id'] ?></a>
            </td>
            <td>
                <a href="<?= $this->all_configs['prefix'] . 'categories/create/' . $order['category_id'] ?>"><?= $order['title'] ?></a>
            </td>
            <td>
                <?php if (isset($order['goods'])): ?>
                    <?php foreach ($order['goods'] as $g): ?>
                        <a href="<?= $this->all_configs['prefix'] . 'products/create/' . $g['goods_id'] ?>"><?= $g['title'] ?></a>
                        <br/>
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>
            <td>
                <?php $services_price = 0; ?>
                <?php if (isset($order['services'])): ?>
                    <?php foreach ($order['services'] as $s): ?>
                        <a href="<?= $this->all_configs['prefix'] . 'products/create/' . $s['goods_id'] ?>"><?= $s['title'] ?></a>
                        <br/>
                        <?php $services_price += $s['price']; ?>
                        <?php $services_prices += $s['price']; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </td>
            <td>
                <?php if ($order['turnover'] < 0 && $order['has_return']): ?>
                    &infin;
                <?php else: ?>
                    <?= show_price($order['turnover'], 2, ' ') ?>
                <?php endif; ?>
            </td>
            <?php if ($isAdmin): ?>
                <td>
                    <?= show_price($services_price, 2, ' ') ?>
                </td>
            <?php endif; ?>
            <?php if ($isAdmin): ?>
                <td>
                    <?= show_price($order['purchase'], 2, ' ') ?>
                </td>
                <td class="reports_turnover_profit invisible">
                    <?= show_price($order['profit'], 2, ' ') ?>
                </td>
                <td class="reports_turnover_margin invisible">
                    <?= (is_numeric($order['avg']) ? round($order['avg'], 2) . '%' : $order['avg']) ?>
                </td>
            <?php endif; ?>
        </tr>
        <?php $count_goods++; ?>
    <?php endforeach; ?>

    <tr>
        <td colspan="8"></td>
    </tr>
    <tr>
        <td><?= l('Итого') ?></td>
        <td><?= $count_goods . l('шт') ?>.</td>
        <td></td>
        <td></td>
        <td></td>
        <td><?= show_price($amounts['turnover'], 2, ' ') ?></td>
        <td><?= show_price($services_prices, 2, ' ') ?></td>
        <td>
            &sum;<?= show_price($amounts['purchase'], 2, ' ') ?><br/>&equiv;<?= show_price($amounts['purchase2'], 2,
                ' ') ?>
        </td>
        <td class="reports_turnover_profit invisible">
            <?= show_price(($amounts['profit']), 2, ' ') ?>
        </td>
        <td class="reports_turnover_margin invisible">
            <?= (is_numeric($amounts['avg']) ? round($amounts['avg'], 2) . '%' : $amounts['avg']) ?>
        </td>
    </tr>
    </tbody>
</table>
