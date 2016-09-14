<div class="hidden js-filters"><?= $repairOrdersFilters ?></div>
<?php if ($orders && count($orders) > 0): ?>
    <div style="overflow-x: auto">

    <table class="table table-striped table-fs-12">
        <thead>
        <?php if ($debts > 0 || $urgent > 0): ?>
            <tr class="overhead">
                <td class="<?= isset($columns['npp'])?'': 'hide' ?>"></td>
                <td class="<?= isset($columns['notice'])?'': 'hide' ?>"></td>
                <td class="<?= isset($columns['date'])?'': 'hide' ?>"></td>
                <td class="center <?= isset($columns['accepter'])?'': 'hide' ?>"></td>
                <td class="center <?= isset($columns['manager'])?'': 'hide' ?>"></td>
                <td class="center <?= isset($columns['engineer'])?'': 'hide' ?>"></td>
                <td class="center <?= isset($columns['status'])?'': 'hide' ?>"></td>
                <td class="<?= isset($columns['components'])?'': 'hide' ?>"></td>
                <td class="<?= isset($columns['device'])?'': 'hide' ?>"></td>
                <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
                    <td class="center <?= isset($columns['amount'])?'': 'hide' ?>"></td>
                    <td class="center <?= isset($columns['paid'])?'': 'hide' ?>">
                        <?php $url = $this->all_configs['prefix'] . 'orders' . (isset($_GET['other']) && strpos($_GET['other'],
                                'pay') !== false ? '' : '?other=pay'); ?>
                        <?php if ($debts > 0): ?>
                            <a href="<?= $url ?>"
                               class="label label-success urgent"
                               title='<?= l('Ожидаемая сумма оплаты') ?>'><?= sprintf('%.2f', round($debts, 2)) ?>
                                &nbsp; <?= viewCurrency() ?></a>
                        <?php endif; ?>
                    </td>
                <?php else: ?>
                    <td class="center"></td>
                <?php endif; ?>
                <td class="<?= isset($columns['client'])?'': 'hide' ?>"></td>
                <td class="<?= isset($columns['phone'])?'': 'hide' ?>"></td>
                <td class="<?= isset($columns['terms'])?'': 'hide' ?>">
                    <?php if ($urgent > 0): ?>
                        <a href="<?= $this->all_configs['prefix'] ?>orders?other=urgent"
                           class="label label-warning urgent"
                           title='<?= l('Срочные ремонты') ?>'><?= $urgent ?></a>
                    <?php endif; ?>
                </td>
                <td class="<?= isset($columns['location'])?'': 'hide' ?>"></td>
                <td class="<?= isset($columns['sn'])?'': 'hide' ?>"></td>
                <td class="<?= isset($columns['repair'])?'': 'hide' ?>"></td>
                <td class="<?= isset($columns['date_end'])?'': 'hide' ?>"></td>
                <td class="<?= isset($columns['warranty'])?'': 'hide' ?>"></td>
                <td class="<?= isset($columns['adv_channel'])?'': 'hide' ?>"></td>
                <td></td>
            </tr>
        <?php endif; ?>
        <tr class="head">
            <td class="<?= isset($columns['npp'])?'': 'hide' ?>"><?= l('номер заказа') ?></td>
            <td class=" <?= isset($columns['notice'])?'': 'hide' ?>" title="<?= l('Возможность ставить напоминания по заказам себе и другим пользователям') ?>"><i
                    class="fa fa-bell cursor-pointer btn-timer" href="javascript:void(0);"></i></td>
            <td class="<?= isset($columns['date'])?'': 'hide' ?>"><?= l('Дата') ?></td>
            <td class="center <?= isset($columns['accepter'])?'': 'hide' ?>"><?= l('Приемщик') ?></td>
            <td class="center <?= isset($columns['manager'])?'': 'hide' ?>"><?= l('manager') ?></td>
            <td class="<?= isset($columns['engineer'])?'': 'hide' ?>">
                <?= l('Инженер') ?>
            </td>
            <td class="center <?= isset($columns['status'])?'': 'hide' ?>"><?= l('Статус') ?></td>
            <td class="center <?= isset($columns['components'])?'': 'hide' ?>" title="<?= l('Ожидает запчастей') ?>"><i class="fa fa-cogs" aria-hidden="true"></i></td>
            <td class="<?= isset($columns['device'])?'': 'hide' ?>"><?= l('Устройство') ?></td>
            <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
                <td class="center <?= isset($columns['amount'])?'': 'hide' ?>"><?= l('Стоимость') ?></td>
                <td class="center <?= isset($columns['paid'])?'': 'hide' ?>">
                    <?= l('Оплачено') ?>
                </td>
            <?php else: ?>
                <td class="center"><?= l('Оплата') ?></td>
            <?php endif; ?>
            <td class="<?= isset($columns['client'])?'': 'hide' ?>"><?= l('Клиент') ?></td>
            <td class="<?= isset($columns['phone'])?'': 'hide' ?>"><?= l('Контактный тел') ?></td>
            <td class="<?= isset($columns['terms'])?'': 'hide' ?>">
                <?= l('Сроки') ?>
            </td>
            <td class="<?= isset($columns['location'])?'': 'hide' ?>"><?= l('Склад') ?></td>
            <td class="<?= isset($columns['sn'])?'': 'hide' ?>">
                <?= l('Серийный номер') ?>
            </td>
            <td class="<?= isset($columns['repair'])?'': 'hide' ?>">
                <?= l('Тип ремонта') ?>
            </td>
            <td class="<?= isset($columns['date_end'])?'': 'hide' ?>">
                <?= l('Дата готовности') ?>
            </td>
            <td class="<?= isset($columns['warranty'])?'': 'hide' ?>">
                <?= l('Гарантия') ?>
            </td>
            <td class="<?= isset($columns['adv_channel'])?'': 'hide' ?>">
                <?= l('Рекламный канал') ?>
            </td>
            <td>
                <?= $this->renderFile('orders/repair_order_column_filter', array(
                    'columns' => $columns
                )) ?>
            </td>
        </tr>
        </thead>
        <tbody id="table_clients_orders">

        <?php foreach ($orders as $order): ?>
            <?= $this->DisplayOrder->asRepairRow($order, $columns); ?>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>


    <?= page_block($count_page, $count, '#show_orders', null,
        $this->renderFile('orders/_export_button', array(
            'prefix' => $prefix
        ))); ?>

<?php else: ?>
    <div class="span9"><p class="text-danger"><?= l('Заказов не найдено') ?></p></div>
<?php endif; ?>
<script>
    jQuery(document).ready(function () {
        $('.multiselect').multiselect(multiselect_options);
        $("#tree").Tree();
    });
</script>
