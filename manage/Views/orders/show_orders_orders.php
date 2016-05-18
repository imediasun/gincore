<div class="hidden js-filters"><?= $repairOrdersFilters ?></div>
<?php if ($orders && count($orders) > 0): ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <td><?= l('номер заказа') ?></td>
            <td title="<?= l('Возможность ставить напоминания по заказам себе и другим пользователям') ?>"><i class="fa fa-bell cursor-pointer btn-timer" href="javascript:void(0);"></i></td>
            <td><?= l('Дата') ?></td>
            <td class="center"><?= l('Приемщик') ?></td>
            <td class="center"><?= l('manager') ?></td>
            <td class="center"><?= l('Статус') ?></td>
            <td class="center" title="<?= l('Ожидает запчастей') ?>"> <i class="fa fa-cogs" aria-hidden="true"></i> </td>
            <td><?= l('Устройство') ?></td>
            <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
                <td class="center"><?= l('Стоимость') ?></td>
                <td class="center"><?= l('Оплачено') ?></td>
            <?php else: ?>
                <td class="center"><?= l('Оплата') ?></td>
            <?php endif; ?>
            <td><?= l('Клиент') ?></td>
            <td><?= l('Контактный тел') ?></td>
            <td><?= l('Сроки') ?></td>
            <td><?= l('Склад') ?></td>
        </tr>
        </thead>
        <tbody id="table_clients_orders">

        <?php foreach ($orders as $order): ?>
            <?= $this->DisplayOrder->asRepairRow($order); ?>
        <?php endforeach; ?>
        </tbody>
    </table>


    <?= page_block($count_page, $count, '#show_orders'); ?>

<?php else: ?>
    <div class="span9"><p class="text-danger"><?= l('Заказов не найдено') ?></p></div>
<?php endif; ?>
<script>
    jQuery(document).ready(function(){
        $('.multiselect').multiselect();
        $("#tree").Tree();
    });
</script>
