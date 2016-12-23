<table class="table">
    <thead>
    <tr>
        <td><?= l('Статус') ?></td>
        <td><?= l('Автор') ?></td>
        <td><?= l('Дата') ?></td>
    </tr>
    </thead>
    <tbody>
    <?php if (!empty($statuses)): ?>
        <?php foreach ($statuses as $status): ?>
            <tr>
                <td><?= (isset($sts[$status['status']]) ? $sts[$status['status']]['name'] : '') ?></td>
                <td><?= get_user_name($status) ?></td>
                <td>
            <span title="<?= do_nice_date($status['date'], false) ?>">
                <?= do_nice_date($status['date']) ?>
            </span>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
