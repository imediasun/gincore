<select class="form-control" name="<?= $type ?>">
    <option value=""><?= l('Выбрать') ?></option>
    <?php if ($users): ?>
        <?php foreach ($users as $user): ?>
            <?php if (!$user['deleted'] || $user['id'] == $order[$type]): ?>
                <option <?= $user['id'] == $order[$type] ? 'selected' : '' ?>
                    value="<?= $user['id'] ?>">
                    <?= get_user_name($user) ?>
                </option>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</select>
