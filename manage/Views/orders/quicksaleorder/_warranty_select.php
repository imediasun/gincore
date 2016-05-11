<div class="input-group">
    <select class="form-control" name="product[<?= $product['id'] ?>][warranty]">
        <option value=""><?= l('Без гарантии') ?></option>
        <?php foreach ($orderWarranties as $warranty): ?>
            <option <?= ($product['warranty'] == intval($warranty) ? 'selected' : '') ?>
                value="<?= intval($warranty) ?>"><?= intval($warranty) ?></option>
        <?php endforeach; ?>
    </select>
    <div class="input-group-addon"><?= l('мес') ?></div>
</div>
