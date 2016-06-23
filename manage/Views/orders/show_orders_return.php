<div class="hidden js-filters"><?= $repairOrdersFilters ?></div>
<?php if (!empty($orders)): ?>
    <div id="show_orders">
        <table class="table" >
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
    </div>

    <?= page_block(ceil($count / $count_on_page), $count, '#show_orders'); ?>

<?php else: ?>
    <div class="span9"><p class="text-danger"><?= l('Заказов не найдено') ?></p></div>
<?php endif; ?>
<script>
    jQuery(document).ready(function(){
        $('.multiselect').multiselect({
            buttonWidth: '150px'
        });
        $(".tree").Tree();
    });
</script>
