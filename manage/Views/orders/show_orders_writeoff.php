<?php if (!empty($orders)): ?>
    <table class="table table-fs-12">
        <thead>
        <tr>
            <td></td>
            <td><?= l('номер заказа') ?></td>
            <td><?= l('Дата') ?></td>
            <td><?= l('Приемщик') ?></td>
            <td><?= l('manager') ?></td>
            <td><?= l('Статус') ?></td>
            <td><?= l('Устройство') ?></td>
            <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
                <td><?= l('Стоимость') ?></td>
                <td><?= l('Оплачено') ?></td>
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

    <?= page_block(ceil($count / $count_on_page), $count, '#show_orders-writeoff', null,
        $this->renderFile('orders/_export_button', array(
            'prefix' => $prefix
        ))); ?>

<?php else: ?>
    <div class="span9"><p class="text-danger"><?= l('Заказов не найдено') ?></p></div>
<?php endif; ?>
