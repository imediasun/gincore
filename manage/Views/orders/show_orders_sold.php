<?php if (!empty($orders)): ?>
    <table class="table">
        <thead>
        <tr>
            <td><?= l('номер заказа') ?></td>
            <td><?= l('Дата') ?></td>
            <td><?= l('manager') ?></td>
            <td><?= l('Способ оплаты') ?></td>
            <td><?= l('Статус') ?></td>
            <td><?= l('Наименование') ?></td>
            <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
                <td><?= l('Стоимость') ?></td>
                <td><?= l('Оплачено') ?></td>
            <?php endif; ?>
            <td><?= l('Клиент') ?></td>
            <td><?= l('Контактный тел') ?></td>
            <td><?= l('Примечание') ?></td>
        </tr>
        </thead>
        <tbody id="table_clients_orders">

        <?php foreach ($orders as $order): ?>
            <?= $this->DisplayOrder->asSaleRow($order); ?>
        <?php endforeach; ?>
        </tbody>
    </table>

    <?= page_block(ceil($count / $count_on_page), $count, '#show_orders'); ?>

<?php else: ?>
    <div class="span9"><p class="text-danger"><?= l('Заказов не найдено') ?></p></div>
<?php endif; ?>
