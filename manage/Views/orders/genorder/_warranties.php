<div class="form-group">
    <span class="cursor-pointer glyphicon glyphicon-list muted"
          onclick="alert_box(this, false, 'changes:update-order-warranty')"
          data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>">
    </span>
    <label><?= l('Гарантия') ?>: </label>
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
</div>
