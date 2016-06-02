<select name="type_id" class="form-control">
    <option value=""></option>
    <?php foreach ($types as $type): ?>
        <option <?= !empty($warehouse) && $type['id'] == $warehouse['type_id'] ? 'selected' : '' ?>
            value="<?= $type['id'] ?>">
            <?= $type['name'] ?>
        </option>
    <?php endforeach; ?>
</select>
