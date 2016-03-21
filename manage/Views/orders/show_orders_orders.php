<?php if ($orders && count($orders) > 0): ?>
    <table class="table table-hover">
        <thead>
        <tr>
            <td><?= l('номер заказа') ?></td>
            <td></td>
            <td><?= l('Дата') ?></td>
            <td><?= l('Приемщик') ?></td>
            <td><?= l('manager') ?></td>
            <td><?= l('Статус') ?></td>
            <td><?= l('Устройство') ?></td>
            <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
                <td><?= l('Стоимость') ?></td>
                <td><?= l('Оплачено') ?></td>
            <?php else: ?>
                <td><?= l('Оплата') ?></td>
            <?php endif; ?>
            <td><?= l('Клиент') ?></td>
            <td><?= l('Контактный тел') ?></td>
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


    <?= page_block($count_page, $count, '#show_orders'); ?>

<?php else: ?>
    <div class="span9"><p class="text-danger"><?= l('Заказов не найдено') ?></p></div>
<?php endif; ?>
