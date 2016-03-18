<div class="form-group">
    <label>
        <span class="cursor-pointer glyphicon glyphicon-list"
              title="<?= $title ?>"
              data-o_id="<?= $order['id'] ?>"
              onclick="alert_box(this, false, 'changes:update-order-<?= $type ?>')">
        </span>
        <?= $title ?>:
    </label>
    <select class="form-control" name="<?= $type ?>">
        <option value=""><?= l('Выбрать') ?></option>
        <?php if ($users): ?>
            <?php foreach ($users as $user): ?>
                <option <?= $user['id'] == $order[$type] ? 'selected' : '' ?>
                    value="<?= $user['id'] ?>">
                    <?= get_user_name($user) ?>
                </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>
