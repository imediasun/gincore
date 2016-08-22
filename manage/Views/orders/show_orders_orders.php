<div class="hidden js-filters"><?= $repairOrdersFilters ?></div>
<?php if ($orders && count($orders) > 0): ?>
    <table class="table table-striped table-fs-12">
        <thead>
        <tr>
            <td><?= l('номер заказа') ?></td>
            <td title="<?= l('Возможность ставить напоминания по заказам себе и другим пользователям') ?>"><i
                    class="fa fa-bell cursor-pointer btn-timer" href="javascript:void(0);"></i></td>
            <td><?= l('Дата') ?></td>
            <td class="center"><?= l('Приемщик') ?></td>
            <td class="center"><?= l('manager') ?></td>
            <td class="center"><?= l('Статус') ?></td>
            <td class="center" title="<?= l('Ожидает запчастей') ?>"><i class="fa fa-cogs" aria-hidden="true"></i></td>
            <td><?= l('Устройство') ?></td>
            <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
                <td class="center"><?= l('Стоимость') ?></td>
                <td class="center">
                    <?php if ($debts > 0): ?>
                        <a href="<?= $this->all_configs['prefix'] ?>orders?other=pay" class="label label-success urgent"
                           title='<?= l('Ожидаемая сумма оплаты') ?>'><?= $debts . viewCurrency() ?></a>
                        <br>
                    <?php endif; ?>
                    <?= l('Оплачено') ?>
                </td>
            <?php else: ?>
                <td class="center"><?= l('Оплата') ?></td>
            <?php endif; ?>
            <td><?= l('Клиент') ?></td>
            <td><?= l('Контактный тел') ?></td>
            <td>
                <?php if ($urgent > 0): ?>
                    <a href="<?= $this->all_configs['prefix'] ?>orders?other=urgent" class="label label-warning urgent"
                       title='<?= l('Срочные ремонты') ?>'><?= $urgent ?></a>
                    <br>
                <?php endif; ?>
                <?= l('Сроки') ?>
            </td>
            <td><?= l('Склад') ?></td>
        </tr>
        </thead>
        <tbody id="table_clients_orders">

        <?php foreach ($orders as $order): ?>
            <?= $this->DisplayOrder->asRepairRow($order); ?>
        <?php endforeach; ?>
        </tbody>
    </table>


    <?= page_block($count_page, $count, '#show_orders', null,
        $this->renderFile('orders/_export_button', array(
            'prefix' => $prefix
        ))); ?>

<?php else: ?>
    <div class="span9"><p class="text-danger"><?= l('Заказов не найдено') ?></p></div>
<?php endif; ?>
<script>
    jQuery(document).ready(function () {
        $('.multiselect').multiselect({
            buttonWidth: '150px'
        });
        $("#tree").Tree();
    });
</script>
