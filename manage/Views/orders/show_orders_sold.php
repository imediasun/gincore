<div class="hidden js-filters"><?= $saleOrdersFilters ?></div>
<?php if (!empty($orders)): ?>
    <table class="table table-striped table-fs-12">
        <thead>
        <?php if ($debts > 0): ?>
            <tr class="overhead">
                <td></td>
                <td></td>
                <td></td>
                <td class="center"></td>
                <td class="center"></td>
                <td class="center"></td>
                <td class="center"></td>
                <td></td>
                <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
                    <td class="center"></td>
                    <td class="center">
                        <?php if ($debts > 0): ?>
                            <a href="<?= $this->all_configs['prefix'] ?>orders?other=pay#show_orders-sold"
                               class="label label-success urgent"
                               title='<?= l('Ожидаемая сумма оплаты') ?>'><?= sprintf('%.2f', round($debts, 2)) ?>
                                &nbsp; <?= viewCurrency() ?></a>
                        <?php endif; ?>
                    </td>
                <?php else: ?>
                    <td class="center"></td>
                <?php endif; ?>
                <td></td>
                <td></td>
                <td>
                </td>
                <td></td>
            </tr>
        <?php endif; ?>
        <tr class="head">
            <td><?= l('номер заказа') ?></td>
            <td title="<?= l('Возможность ставить напоминания по заказам себе и другим пользователям') ?>"><i
                    class="fa fa-bell cursor-pointer btn-timer" href="javascript:void(0);"></i></td>
            <td><?= l('Дата') ?></td>
            <td class='center'><?= l('manager') ?></td>
            <td class="center"><?= l('Способ оплаты') ?></td>
            <td class="center"><?= l('Статус') ?></td>
            <td class="center" title="<?= l('Способ доставки') ?>"><i class="fa fa-truck" aria-hidden="true"></i></td>
            <td class="center" title="<?= l('Ожидает запчастей') ?>"><i class="fa fa-cogs" aria-hidden="true"></i></td>
            <td><?= l('Наименование') ?></td>
            <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
                <td><?= l('Стоимость') ?></td>
                <td><?= l('Оплачено') ?></td>
            <?php endif; ?>
            <td class="center"><?= l('Клиент') ?></td>
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


    <?= page_block(ceil($count / $count_on_page), $count, '#show_orders-sold', null,
        $this->renderFile('orders/_export_button', array(
            'prefix' => $prefix
        ))); ?>

<?php else: ?>
    <div class="span9"><p class="text-danger"><?= l('Заказов не найдено') ?></p></div>
<?php endif; ?>
<script>
    jQuery(document).ready(function () {
        $('.multiselect').multiselect(multiselect_options);
        $(".tree").Tree();
    });
</script>