<?php if ($contractors): ?>
    <div class="<?= !empty($new_client) ? 'span7' : 'col-sm-7' ?>">
        <label class="control-label"><?= l('Контрагент') ?>: </label>
        <?= !empty($infopopover) ? $infopopover : '' ?>
    </div>
    <div class="<?= !empty($new_client) ? 'span5' : 'col-sm-5' ?>" style="text-align: right; min-width: 150px">
        <select name="contractor_id" class="multiselect form-control">
            <option value=""><?= l('Не выбран') ?></option>
            <?php foreach ($contractors as $contractor): ?>
                <option <?= ($contractor['id'] == $client['contractor_id']) ? 'selected' : '' ?>
                    value="<?= $contractor['id'] ?>">
                    <?= htmlspecialchars($contractor['title']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="clearfix"></div>
<?php endif; ?>
