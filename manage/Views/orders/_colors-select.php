<select class="form-control" name="color">
    <option value="-1"><?= l('Не выбран') ?></option>
    <?php foreach ($colors as $i => $c): ?>
        <option value="<?= $i ?>"><?= $c ?></option>
    <?php endforeach; ?>
</select>