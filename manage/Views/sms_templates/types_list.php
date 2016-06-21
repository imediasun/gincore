<select class="form-control" name="data[type]">
    <option disabled selected><?= l('Выберите') ?></option>
    <?php foreach ($types as $type => $id): ?>
        <option value="<?= $id ?>"><?= l($type) ?></option>
    <?php endforeach; ?>
</select>
