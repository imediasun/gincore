<div class="input-group">
    <select class="form-control" name="warranty">
        <option value=""><?= l('Без гарантии') ?></option>
        <?php foreach ($orderWarranties as $warranty): ?>
            <option <?= ($order['warranty'] == intval($warranty) ? 'selected' : '') ?>
                value="<?= intval($warranty) ?>"><?= intval($warranty) ?></option>
        <?php endforeach; ?>
    </select>
    <div class="input-group-addon"><?= l('мес') ?></div>
</div>
