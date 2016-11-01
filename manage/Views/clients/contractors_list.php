<?php if ($contractors): ?>
    <div class="<?= !empty($new_client) ? 'span5' : 'col-sm-5' ?>">
        <label class="control-label"><?= l('Контрагент') ?>: </label>
        <?= !empty($infopopover) ? $infopopover : '' ?>
    </div>
    <div class="<?= !empty($new_client) ? 'span7' : 'col-sm-7' ?>" style="text-align: right; min-width: 150px">
        <div class="btn-group" role="group" aria-label="...">
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
                <button type="button" title="<?= l('Создать контрагента') ?> "
                        onclick="add_contractor_from_client_form(this);" class="btn btn-info">
                    <i class="glyphicon glyphicon-plus"></i>
                </button>
            <?php endif; ?>

        </div>
    </div>
    <div class="clearfix"></div>
<?php endif; ?>
