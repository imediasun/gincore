<?php if ($items && count($items) > 0): ?>
    <table class="table table-compact">
        <thead>
        <tr>
            <td><?= l('Заказ') ?></td>
            <td><?= l('Был принят') ?></td>
            <td><?= l('Сейчас находится') ?></td>
            <td><?= l('Дата') ?></td>
            <td>
                <?php if ($type == 4): ?>
                    <?= l('Нужно отвязать деталь') ?>
                <?php else: ?>
                    <?= l('Нужно привязать деталь') ?>
                <?php endif; ?>
            </td>
            <td><?= l('Сроки') ?></td>
            <td><?= l('Сер.номер') ?></td>
            <td><?= l('Управление') ?></td>
        </tr>
        </thead>
        <tbody>

        <?php foreach ($items as $item): ?>
            <?= $controller->show_stockman_operation($item, $type, $serials) ?>
        <?php endforeach; ?>

        </tbody>
    </table>

    <?php $count_page = $count_on_page > 0 ? ceil($count / $count_on_page) : 0; ?>
    <?= page_block($count_page, $count, $hash); ?>
<?php else: ?>
    <?= l('Нет операций'); ?>
<?php endif; ?>
