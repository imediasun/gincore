<?php if (!empty($orders)): ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <td><?= l('номер заказа') ?></td>
            <td><i class="fa fa-bell cursor-pointer btn-timer" href="javascript:void(0);"></i></td>
            <td><?= l('Дата') ?></td>
            <td class='center'><?= l('manager') ?></td>
            <td class="center"><?= l('Способ оплаты') ?></td>
            <td class="center"><?= l('Статус') ?></td>
            <td class="center"> <i class="fa fa-cogs" aria-hidden="true"></i> </td>
            <td><?= l('Наименование') ?></td>
            <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
                <td><?= l('Стоимость') ?></td>
                <td><?= l('Оплачено') ?></td>
            <?php endif; ?>
            <td class="center;"><?= l('Клиент') ?></td>
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
