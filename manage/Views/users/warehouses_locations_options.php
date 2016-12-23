<select class="multiselect form-control select-location" name="location">
    <?php if (!empty($warehouses_locations)): ?>
        <?php foreach ($warehouses_locations as $id => $location): ?>
            <?php if (trim($location['name'])): ?>
                <?php $sel = !empty($form_data['location']) && $form_data['location'] == $id ? ' selected' : ''; ?>
                <option <?= $sel ?> value="<?= $id ?>">
                    <?= htmlspecialchars($location['name']) ?>
                </option>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</select>
