<select class="multiselect" name="warehouses[]" multiple="multiple">
    <?php foreach ($warehouses_arr as $warehouse): ?>
        <?php $sel = !empty($form_data['warehouses']) && in_array($warehouse['id'],
            $form_data['warehouses']) ? 'selected' : ''; ?>
        <option <?= $sel ?> value="<?= $warehouse['id'] ?>"><?= htmlspecialchars($warehouse['title']) ?></option>
    <?php endforeach; ?>
</select>
