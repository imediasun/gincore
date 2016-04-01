<div id="create_tab_user" class="tab-pane">
    <form method="post" class="<?= empty($isEdit) ? 'create-user': 'edit-user' ?>">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <?= $error ?>
            </div>
        <?php endif; ?>
        <fieldset>
            <legend><?= l('Добавление нового пользователя') ?></legend>
            <?php if (!empty($form_data['id'])): ?>
                <input type="hidden" name="user_id" value="<?= $form_data['id'] ?>"/>
            <?php endif; ?>
            <div class="row-fluid">
                <div class="col-sm-3">
                    <center>
                        <div class="form-group">
                            <img class="upload_avatar_btn" data-uid="<?= $form_data['id'] ?>" width="170"
                                 src="<?= $controller->avatar($form_data['avatar']) ?>">
                        </div>
                        <div class="form-group">
                            <div class="checkbox">
                                <label><input <?= (!empty($form_data['avail']) || !$form_data ? 'checked' : '') ?>
                                        type="checkbox"
                                        name="avail"/><?= l('Активность') ?>
                                </label>
                            </div>
                        </div>
                    </center>
                </div>
                <div class="col-sm-3">
                    <div class="form-group">
                        <label><?= l('Логин') ?> <b class="text-danger">*</b>:</label>
                        <input class="form-control"
                               value="<?= (isset($form_data['login']) ? htmlspecialchars($form_data['login']) : '') ?>"
                               name="login"
                               placeholder="<?= l('введите логин') ?>">
                    </div>
                    <div class="form-group">
                        <label><?= l('E-mail') ?> <b class="text-danger">*</b>:</label>
                        <input class="form-control"
                               value="<?= (isset($form_data['email']) ? htmlspecialchars($form_data['email']) : '') ?>"
                               name="email"
                               placeholder="<?= l('введите e-mail') ?>">
                    </div>
                    <?php if (empty($isEdit)): ?>
                        <div class="form-group">
                            <label><?= l('Пароль') ?> <b class="text-danger">*</b>:</label>
                            <input class="form-control" value="" name="pass" placeholder="<?= l('введите пароль') ?>">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label><?= l('ФИО') ?>:</label>
                        <input class="form-control"
                               value="<?= (isset($form_data['fio']) ? htmlspecialchars($form_data['fio']) : '') ?>"
                               name="fio"
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
                               value="<?= (isset($form_data['phone']) ? htmlspecialchars($form_data['phone']) : '') ?>"
                               name="phone"
                               placeholder="<?= l('введите телефон') ?>">
                    </div>

                </div>
            </div>
            <hr>
            <div class="row-fluid">
                <table class="table">
                    <tbody>
                    <tr>
                        <td class="col-sm-6">
                            <div class="form-group">
                                <label><?= l('Укажите склад и локацию, на которую по умолчанию перемещается устройство принятое на ремонт данным сотрудником') ?></label>
                            </div>
                        </td>
                        <td class="col-sm-6">
                            <div class="form-group">
                                <div class="clearfix">
                                    <div class="pull-left m-r-lg">
                                        <label><?= l('Склад') ?>:</label><br>
                                        <?= $this->renderFile('users/warehouses_options', array(
                                            'warehouses' => $warehouses,
                                            'form_data' => $form_data
                                        )) ?>
                                    </div>
                                    <div class="pull-left">
                                        <label><?= l('Локация') ?>:</label><br>
                                        <?= $this->renderFile('users/warehouses_locations_options', array(
                                            'warehouses_locations' => $warehouses_locations,
                                            'form_data' => $form_data
                                        )) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-sm-6">
                            <div class="form-group">
                                <label><?= l('Укажите склады к которым сотрудник имеет доступ') ?></label><br>
                            </div>

                        </td>
                        <td class="col-sm-6">
                            <div class="form-group">
                                <?= $this->renderFile('users/warehouses_arr_options', array(
                                    'warehouses_arr' => $warehouses_arr,
                                    'form_data' => $form_data
                                )) ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-sm-6">
                            <div class="form-group">
                                <label><?= l('Укажите кассы к которым сотрудник имеет доступ') ?></label><br>
                            </div>

                        </td>
                        <td class="col-sm-6">
                            <div class="form-group">
                                <?= $this->renderFile('users/cashboxes_options', array(
                                    'cashboxes' => $cashboxes,
                                    'form_data' => $form_data
                                )) ?>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td class="col-sm-6">
                            <div class="form-group">
                                <label><?= l('Роль') ?></label>
                            </div>
                        </td>
                        <td class="col-sm-6">
                            <div class="form-group">
                                <?= $this->renderFile('users/roles_options', array(
                                    'roles' => $roles,
                                    'form_data' => $form_data
                                )) ?>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <?php if (empty($isEdit)): ?>
                <div class="control-group">
                    <div class="controls">
                        <input class="btn btn-primary" type="submit" name="create-user"
                               onclick="return add_user_validation();"
                               value="<?= l('Создать') ?>">
                    </div>
                </div>
            <?php endif; ?>
        </fieldset>
    </form>
</div>

