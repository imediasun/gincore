<?php if ($contractors): ?>
<div class="col-sm-7">
        <label class="control-label"><?= l('Контрагент') ?>: </label>
    </div>
    <div class="col-sm-5">
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
<?php endif; ?>
