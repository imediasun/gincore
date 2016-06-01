<select name="group_id" class="form-control">
    <option value=""></option>
    <?php foreach ($groups as $group): ?>
        <option <?= !empty($warehouse) && $group['id'] == $warehouse['group_id'] ? 'selected' : '' ?> value="<?= $group['id'] ?>">
            <?= $group['name'] ?>
        </option>
    <?php endforeach; ?>
</select>

