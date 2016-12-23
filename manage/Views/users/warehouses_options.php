<select onchange="change_warehouse(this)" class="multiselect form-control"
        name="warehouse">
    <?php foreach ($warehouses as $warehouse): ?>
        <?php $sel = !empty($form_data['warehouse']) && $form_data['warehouse'] == $warehouse['id'] ? ' selected' : ''; ?>
        <option <?= $sel ?> value="<?= $warehouse['id'] ?>"><?= $warehouse['title'] ?></option>
    <?php endforeach; ?>
</select>
