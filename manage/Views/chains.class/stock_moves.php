<p><?= l('Перемещений не найдено') ?></p>
<?php if (!empty($moves)): ?>
    <table class="table">
        <thead>
        <tr>
            <td><?= l('Дата') ?></td>
            <td><?= l('Менeджер') ?></td>
            <td><?= l('Склад') ?></td>
            <td><?= l('Локация') ?></td>
        </tr>
        </thead>
        <?php foreach ($moves as $move): ?>
            <tr>
                <td>
                    <span title="<?= do_nice_date($move['date_move'], false) ?>"><?= do_nice_date($move['date_move']) ?></span>
                </td>
                <td><?= get_user_name($move) ?></td>
                <td><?= htmlspecialchars($move['title']) ?></td>
                <td><?= htmlspecialchars($move['location']) ?></td>
            </tr>
        <?php endforeach; ?>
    </table>
<?php endif; ?> 
