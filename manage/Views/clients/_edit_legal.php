<form method="post">
    <input type="hidden" name="client_id" value="<?= $client['id'] ?>"/>
    <div class="col-sm-12">
        <div class="form-group">
            <label class="control-label"><?= l('Название организации') ?>: </label>
            <div class="controls">
                <input value="<?= h($client['fio']) ?>" name="fio" class="form-control"/>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label"><?= l('Юридический адрес') ?>: </label>
            <div class="controls">
                <input value="<?= h($client['legal_address']) ?>" name="legal_address"
                       class="form-control"/>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label"><?= l('Фактический адрес') ?>: </label>
            <div class="controls">
                <input value="<?= h($client['residential_address']) ?>" name="residential_address"
                       class="form-control"/>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label"><?= l('Регистрационные данные 1') ?>: </label>
            <div class="controls">
                <input value="<?= h($client['reg_data_1']) ?>" name="reg_data_1"
                       class="form-control"/>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label"><?= l('Регистрационные данные 2') ?>: </label>
            <div class="controls">
                <input value="<?= h($client['reg_data_2']) ?>" name="reg_data_2"
                       class="form-control"/>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label"><?= l('Электронная почта') ?>: </label>
            <div class="controls">
                <input value="<?= h($client['email']) ?>" name="email"
                       class="form-control "/>
            </div>
        </div>

        <div class="form-group">
            <label class="control-label"><?= l('Телефон') ?>: </label>
            <div class="relative">
                <?= $this->renderFile('clients/phones', array(
                    'phones' => $phones
                )); ?>
                <i class="cloneAndClear glyphicon glyphicon-plus"></i>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label"><?= l('Примечание') ?>: </label>
            <div class="controls">
                <textarea name="note" class="form-control "> <?= h($client['note']) ?></textarea>
            </div>
        </div>
    </div>

    <div class="form-group ">
        <?= $contractorsList; ?>
    </div>
    <div class="form-group ">
        <?= $tagsList ?>
    </div>
    <div class="col-sm-12">
        <div class="form-group">
            <label class="control-label"><?= l('Дата регистрации') ?>: </label>&nbsp;
            <span title="<?= do_nice_date($client['date_add'], false) ?>">
                <?= do_nice_date($client['date_add']) ?>
            </span>
        </div>
        <?php if ($this->all_configs['oRole']->hasPrivilege('site-administration')): ?>
            <div class="form-group">
                <label class="control-label"><?= l('Пароль') ?>: </label>
                <i class="glyphicon glyphicon-lock editable-click" data-type="text"
                   data-pk="<?= $arrequest[2] ?>" data-type="password"
                   data-url="<?= $this->all_configs['prefix'] . $arrequest[0] ?>/ajax?act=change-client-password"
                   data-title="<?= l('Введите новый пароль') ?>" data-display="false"></i>
            </div>
        <?php endif; ?>
    </div>


    <div class="form-group" style="text-align: right">
        <input id="save_all_fixed" class="btn btn-primary" type="submit"
               value="<?= l('Сохранить изменения') ?>" name="edit-client">
    </div>
</form>
