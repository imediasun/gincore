<?php if ($contractors): ?>
    <div class="<?= !empty($new_client) ? 'span7' : 'col-sm-7' ?>">
        <label class="control-label"><?= l('Контрагент') ?>: </label>
        <?= !empty($infopopover) ? $infopopover : '' ?>
    </div>
    <div class="<?= !empty($new_client) ? 'span5' : 'col-sm-5' ?> relative" style="text-align: right; min-width: 150px">
            <select name="contractor_id" class="multiselect form-control" id="contractor-select">
                <option value=""><?= l('Не выбран') ?></option>
                <?php foreach ($contractors as $contractor): ?>
                    <option <?= ($contractor['id'] == $client['contractor_id']) ? 'selected' : '' ?>
                        value="<?= $contractor['id'] ?>">
                        <?= htmlspecialchars($contractor['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <?php if(empty($new_client)): ?>
                <i class="add_contractor_icon glyphicon glyphicon-plus cursor-pointer" style="position: absolute; right: -11px; top: 10px;" onclick="add_contractor_from_client_form(null);"
                   title="<?= l('Создать контрагента') ?>"></i>
            <?php endif; ?>
    </div>
    <div class="clearfix"></div>
<?php endif; ?>
