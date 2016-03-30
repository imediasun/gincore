<form method="post">
    <?= $msg ?>
    <fieldset>
        <legend><?= l('Добавление нового пользователя') ?></legend>
        <div class="form-group">
            <label><?= l('Логин') ?> <b class="text-danger">*</b>:</label>
            <input class="form-control"
                   value="<?= (isset($form_data['login']) ? htmlspecialchars($form_data['login']) : '') ?>" name="login"
                   placeholder="<?= l('введите логин') ?>">
        </div>
        <div class="form-group">
            <label><?= l('E-mail') ?> <b class="text-danger">*</b>:</label>
            <input class="form-control"
                   value="<?= (isset($form_data['email']) ? htmlspecialchars($form_data['email']) : '') ?>" name="email"
                   placeholder="<?= l('введите e-mail') ?>">
        </div>
        <div class="form-group">
            <label><?= l('Пароль') ?> <b class="text-danger">*</b>:</label>
            <input class="form-control" value="" name="pass" placeholder="<?= l('введите пароль') ?>">
        </div>
        <div class="form-group">
            <label><?= l('ФИО') ?>:</label>
            <input class="form-control"
                   value="<?= (isset($form_data['fio']) ? htmlspecialchars($form_data['fio']) : '') ?>" name="fio"
                   placeholder="<?= l('введите фио') ?>">
        </div>
        <div class="form-group">
            <label><?= l('Должность') ?></label>
            <input class="form-control"
                   value="<?= (isset($form_data['position']) ? htmlspecialchars($form_data['position']) : '') ?>"
                   name="position" placeholder="<?= l('введите должность') ?>">
        </div>
        <div class="form-group">
            <label><?= l('Телефон') ?></label>
            <input onkeydown="return isNumberKey(event)" class="form-control"
                   value="<?= (isset($form_data['phone']) ? htmlspecialchars($form_data['phone']) : '') ?>" name="phone"
                   placeholder="<?= l('введите телефон') ?>">
        </div>
        <div class="form-group">
            <div class="checkbox">
                <label><input <?= (!empty($form_data['avail']) || !$form_data ? 'checked' : '') ?> type="checkbox"
                                                                                                   name="avail"/><?= l('Активность') ?>
                </label>
            </div>
        </div>
        <div class="form-group">
            <label><?= l('Укажите склад и локацию, на которую по умолчанию перемещается устройство принятое на ремонт данным сотрудником') ?></label>
            <div class="clearfix">
                <div class="pull-left m-r-lg">
                    <label><?= l('Склад') ?>:</label><br>
                    <select onchange="change_warehouse(this)" class="multiselect form-control" name="warehouse">
                        <?= $warehouses_options ?>
                    </select>
                </div>
                <div class="pull-left">
                    <label><?= l('Локация') ?>:</label><br>
                    <select class="multiselect form-control select-location" name="location">
                        <?= $warehouses_options_locations ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label><?= l('Укажите склады к которым сотрудник имеет доступ') ?></label><br>
            <select class="multiselect" name="warehouses[]" multiple="multiple">
                <?= $warehouses ?>
            </select>
        </div>
        <div class="form-group">
            <label><?= l('Роль') ?></label>
            <select name="role" class="form-control">
                <option value=""><?= l('выберите роль') ?></option>
                <?= $role_html ?>
            </select>
        </div>

        <div class="control-group">
            <div class="controls">
                <input class="btn btn-primary" type="submit" name="create-user" onclick="return add_user_validation();"
                       value="<?= l('Создать') ?>">
            </div>
        </div>
    </fieldset>
</form>
