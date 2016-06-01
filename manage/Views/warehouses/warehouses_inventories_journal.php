<?php if (empty($left_html)): ?>
    <p class="text-error"><?= l('Инвентаризация не найдена') ?></p>
<?php else: ?>
    <?php if (array_key_exists(2, $this->all_configs['arrequest']) && $this->all_configs['arrequest'][2] > 0): ?>
        <?php if ($left['open']): ?>
            <?= $controller->scan_serial_form(1); ?>
        <?php endif; ?>
        <table class="table table-striped">
            <thead>
            <tr>
                <td></td>
                <td><?= l('Сер. №') ?></td>
                <td><?= l('Дата') ?></td>
                <td><?= l('Наименование') ?></td>
                <td><?= l('Кладовщик') ?></td>
                <td><?= l('Склад') ?></td>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($inventories)): ?>
                <?php $i = 1; ?>
                <?php foreach ($inventories as $inv): ?>
                    <?php $inv['i'] = $i; ?>
                    <?php $i++; ?>
                    <?= $controller->display_scanned_item($inv, $left['inv']['wh_id']); ?>
                <?php endforeach; ?>
            <?php else: ?>
                <td colspan="6"><?= l('Сканирований нет') ?></td>
            <?php endif; ?>
            </tbody>
        </table>
    <?php endif; ?>
<?php endif; ?>
