<div id="create_inventories" class="input-append">
    <select id="create-inventory-wh_id" name="warehouse">
        <?= $this->all_configs['chains']->get_options_for_move_item_form(false); ?>
    </select>
    <button class="btn" onclick="create_inventories(this)" type="button"><?= l('Начать') ?></button>
</div>
<?php if (!empty($list)): ?>
    <table class="table table-hover">
        <thead>
        <tr>
            <td></td>
            <td><?= l('Дата начала') ?></td>
            <td><?= l('Склад') ?></td>
            <td><?= l('Кладовщик') ?> (<?= l('создатель') ?>)</td>
            <td><?= l('Дата завершения') ?></td>
            <td><?= l('Кол-во на складе') ?></td>
            <td><?= l('Кол-во проинвентаризовано') ?></td>
            <td><?= l('Недостача') ?></td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($list as $l): ?>
            <?php $l['count_items'] = isset($counts_items[$l['id']]) ? $counts_items[$l['id']] : 0; ?>
            <?php $l['count_inv_items'] = isset($counts_inv_items[$l['id']]) ? $counts_inv_items[$l['id']] : 0; ?>
            <?php if ($l['id'] == 0): ?>
                <?php continue; ?>
            <?php endif; ?>
            <tr class="inventory-row" onclick="open_inventory(this, '<?= $l['id'] ?>)">
                <td><?= $l['id'] ?></td>
                <td>
                    <span title="<?= do_nice_date($l['date_start'], false) ?>">
                        <?= do_nice_date($l['date_start']) ?>
                    </span>
                </td>
                <td><?= h($l['title']) ?></td>
                <td><?= get_user_name($l) ?></td>
                <td>
                    <span title="<?= do_nice_date($l['date_stop'], false) ?>">
                        <?= do_nice_date($l['date_stop']) ?>
                    </span>
                </td>
                <td><?= $l['count_items'] ?></td>
                <td><?= $l['count_inv_items'] ?></td>
                <td><?= ($l['count_items'] - $l['count_inv_items']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-error"><?= l('Инвентаризаций нет') ?></p>
<?php endif; ?>
