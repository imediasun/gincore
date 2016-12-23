<div id="orders" class="tab-pane">
    <?php if ($orders && count($orders) > 0): ?>
        <table class="table table-compact">
            <thead>
            <tr>
                <td></td>
                <td><?= l('номер заказа') ?></td>
                <td><?= l('Дата') ?></td>
                <td><?= l('Приемщик') ?></td>
                <td><?= l('manager') ?></td>
                <td><?= l('Статус') ?></td>
                <td><?= l('Устройство') ?></td>
                <td><?= l('Стоимость') ?></td>
                <td><?= l('Оплачено') ?></td>
                <td><?= l('Клиент') ?></td>
                <td><?= l('Контактный тел.') ?></td>
                <td><?= l('Сроки') ?></td>
                <td><?= l('Склад') ?></td>
            </tr>
            </thead>
            <tbody id="table_clients_orders">
            <?php foreach ($orders as $order): ?>
                <?= display_client_order($order); ?>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?= page_block(ceil($count / $count_on_page), $count); ?>
    <?php else: ?>
        <div class="span9"><p class="text-error"><?= l('Заказов не найдено') ?></p></div>
    <?php endif; ?>
</div>
