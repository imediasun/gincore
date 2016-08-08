<div style="max-height: 300px; overflow: auto">
    <table class="table">
        <thead>
        <tr>
            <td><?= l('manager') ?></td>
            <td><?= l('Дата') ?></td>
            <td><?= l('Изменение') ?></td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($changes as $change): ?>
            <tr>
                <td><?= get_user_name($change) ?></td>
                <td><span title="<?= do_nice_date($change['date_add'],
                        false) ?>"><?= do_nice_date($change['date_add']) ?></span></td>
                <td><?= h($change['change']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
