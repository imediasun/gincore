<?php if ($contractors): ?>
    <div class="form-group">
        <label class="control-label"><?= l('Контрагент') ?>: </label>
        <div class="controls">
            <select name="contractor_id" class="multiselect form-control">
                <option value=""><?= l('Не выбран') ?></option>
                <?php foreach ($contractors as $contractor): ?>
                    <?php if ($contractor['id'] == $client['contractor_id']): ?>
                        <option selected value="<?= $contractor['id'] ?>">
                            <?= htmlspecialchars($contractor['title']) ?>
                        </option>
                    <?php else: ?>
                        <option value="<?= $contractor['id'] ?>">
                            <?= htmlspecialchars($contractor['title']) ?>
                        </option>
                    <?php endif; ?>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <?php if ($this->all_configs['oRole']->hasPrivilege('site-administration')): ?>
        <div class="form-group">
            <label class="control-label"><?= l('Пароль') ?>: </label>
            <i class="glyphicon glyphicon-warning-sign editable-click" data-type="text"
               data-pk="<?= $this->all_configs['arrequest'][2] ?>" data-type="password"
               data-url="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/ajax?act=change-client-password"
               data-title="<?= l('Введите новый пароль') ?>" data-display="false"></i>
        </div>
    <?php endif; ?>
<?php endif; ?>
