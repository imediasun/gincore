<?php if (!empty($histories)): ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <td><?= l('Автор') ?></td>
            <td><?= l('Редактирование') ?></td>
            <td><?= l('Дата') ?></td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($histories as $history): ?>
            <tr>
                <td><a href="<?= $this->all_configs['prefix'] ?>users"><?= $history['login'] ?></a></td>
                <td><?= $history['change'] ?></td>
                <td><span title="<?= do_nice_date($history['date_add'],
                        false) ?>"><?= do_nice_date($history['date_add']) ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-error"><?= l('Нет ни одного изменения') ?></p>
<?php endif; ?>
