<select name="role" class="form-control">
    <option value=""><?= l('выберите роль') ?></option>
    <?php foreach ($roles as $role_id => $role_name): ?>
        <?php $sel = !empty($form_data['role']) && $role_id == $form_data['role'] ? ' selected' : ''; ?>
        <option <?= $sel ?> value="<?= $role_id ?>"><?= htmlspecialchars($role_name) ?></option>
    <?php endforeach; ?>
</select>
