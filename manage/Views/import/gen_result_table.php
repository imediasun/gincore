<h3><?= l('Результат импорта:') ?></h3>
<table class="table table-stripped table-hover">
    <?php foreach ($results as $row_result): ?>
        <?php if($onlyError && $row_result['state']): ?>
                <?php continue; ?>
        <?php endif; ?>
        <?php if (isset($row_result['state']) && !$row_result['state']): ?>
            <?php $type = 'danger'; ?>
        <?php elseif (isset($row_result['state_type']) && $row_result['state_type'] === 1): ?>
            <?php $type = 'info'; ?>
        <?php else: ?>
            <?php $type = 'success'; ?>
        <?php endif; ?>
        <tr class='<?= $type ?>'><?= $controller->get_result_row($row_result) ?></tr>
    <?php endforeach; ?>
</table>
