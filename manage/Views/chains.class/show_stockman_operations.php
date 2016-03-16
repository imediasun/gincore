<?php if ($operations && count($operations) > 0): ?>
    <table class="table table-compact">
        <thead>
        <tr>
            <td><?= l('Заказ') ?></td>
            <td><?= l('Дата') ?></td>
            <td><?= l('Наименование') ?></td>
            <td><?= l('Склад') ?></td>
            <?php if ($type == 1): ?>
                <td><?= l('Сроки') ?></td>
            <?php endif; ?>
            <?php if ($type == 2): ?>
                <td>Куда</td>
            <?php endif; ?>
            <td><?= l('Сер.номер') ?></td>
            <td><?= l('Управление') ?></td>
            <td><?= l('Сервисный центр') ?></td>
        </tr>
        </thead>
        <tbody>

        <?php foreach ($operations as $op): ?>
            <?= $controller->show_stockman_operation($op, $type, $serials) ?>
        <?php endforeach; ?>

        </tbody>
    </table>

    <?php $count_page = $count_on_page > 0 ? ceil($count / $count_on_page) : 0; ?>
    <?= page_block($count_page, $count, $hash); ?>
<?php else: ?>
    <?= l('Нет операций'); ?>
<?php endif; ?>
