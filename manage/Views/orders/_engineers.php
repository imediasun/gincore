<select class="form-control" name="<?= $type ?>">
    <option value=""><?= l('Выбрать') ?></option>
    <?php if ($users): ?>
        <?php foreach ($users as $user): ?>
            <?php if (!$user['deleted'] || $user['id'] == $order[$type]): ?>
                <option <?= $user['id'] == $order[$type] ? 'selected' : '' ?>
                    value="<?= $user['id'] ?>">
                    <?= get_user_name($user) ?>
                    <?php if(!empty($user['workload_by_service']) || !empty($user['workload_by_order'])): ?>
                        (<?= sprintf(l('загруженность')." %d&nbsp;". l('ремонт').', '.l('из них ожидают запчастей и на согласовании').': %d', (int)$user['workload_by_service'] + $user['workload_by_order'], $user['wait_parts_o'] + (int)$user['wait_parts_s']) ?>)
                    <?php endif; ?>
                </option>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</select>
