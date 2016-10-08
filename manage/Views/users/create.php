<?php if (!empty($isEdit)): ?>
    <style>
        .modal-dialog {
            margin-top: 20px;
        }
    </style>
<?php endif; ?>
<div id="create_tab_user" class="tab-pane row-fluid <?= empty($isEdit) ? '' : 'edit_tab_user' ?>"
     style="padding-bottom: 0">
    <?php if ($available): ?>
        <form method="post"
              class="<?= empty($isEdit) ? 'create-user' : 'edit-user' ?> <?= empty($isEdit) ? 'col-sm-6' : '' ?>">
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <?= $error ?>
                </div>
            <?php endif; ?>
            <fieldset>
                <legend>
                    <?php if ($isEdit): ?>
                        <?= l('Редактирование информации пользователя') ?>
                    <?php else: ?>
                        <?= l('Добавление нового пользователя') ?>
                    <?php endif; ?>
                </legend>
                <?php if (!empty($form_data['id'])): ?>
                    <input type="hidden" name="user_id" value="<?= $form_data['id'] ?>"/>
                <?php endif; ?>
                <div class="row-fluid">
                    <div class="col-sm-3">
                        <center>
                            <div class="form-group">
                                <img class="upload_avatar_btn img-responsive"
                                     data-uid="<?= $form_data['id'] ?>" <?= empty($isEdit) ? '' : 'style="width: 170px"' ?>
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
                                   value="<?= (isset($form_data['login']) ? h($form_data['login']) : '') ?>"
                                   name="login"
                                   placeholder="<?= l('введите логин') ?>">
                        </div>
                        <div class="form-group">
                            <label><?= l('E-mail') ?> <b class="text-danger">*</b>:</label>
                            <input class="form-control"
                                   value="<?= (isset($form_data['email']) ? h($form_data['email']) : '') ?>"
                                   name="email"
                                   placeholder="<?= l('введите e-mail') ?>">
                        </div>
                        <div class="form-group">
                            <label><?= l('Пароль') ?>
                                <?php if (empty($isEdit)): ?>
                                    <b class="text-danger">*</b>
                                <?php endif; ?>:
                            </label>
                            <input type='password' class="form-control" value="" name="pass"
                                   placeholder="<?= l('введите пароль') ?>">
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <label><?= l('ФИО') ?>:</label>
                            <input class="form-control"
                                   value="<?= (isset($form_data['fio']) ? h($form_data['fio']) : '') ?>"
                                   name="fio"
                                   placeholder="<?= l('введите фио') ?>">
                        </div>
                        <div class="form-group">
                            <label><?= l('Должность') ?></label>
                            <input class="form-control"
                                   value="<?= (isset($form_data['position']) ? h($form_data['position']) : '') ?>"
                                   name="position" placeholder="<?= l('введите должность') ?>">
                        </div>
                        <div class="form-group">
                            <label><?= l('Телефон') ?></label>
                            <input<?= input_phone_mask_attr() ?> onkeydown="return isNumberKey(event)"
                                                                 class="form-control"
                                                                 value="<?= (isset($form_data['phone']) ? h($form_data['phone']) : '') ?>"
                                                                 name="phone"
                                                                 placeholder="<?= l('введите телефон') ?>">
                        </div>

                    </div>
                </div>
                <hr>
                <div class="row-fluid">
                    <table class="table" style="margin-bottom: 0px">
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
                                <div class="form-group" style="max-width: 150px">
                                    <?= $this->renderFile('users/roles_options', array(
                                        'roles' => $roles,
                                        'form_data' => $form_data
                                    )) ?>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="col-sm-6">
                                <div class="form-group">
                                    <label>
                                        <?= l('Зарплата от прибыли с заказов за ремонт') ?><?= InfoPopover::getInstance()->createQuestion('l_it_profit_from_repair_orders') ?>
                                    </label><br>
                                </div>

                            </td>
                            <td class="col-sm-6">
                                <div class="input-group col-sm-5">
                                    <input type="text" class="form-control"
                                           value="<?= (isset($form_data['salary_from_repair']) ? h($form_data['salary_from_repair']) : '') ?>"
                                           name="salary_from_repair" aria-describedby="basic-addon1"/>
                                    <span class="input-group-addon" id="basic-addon1">%</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="col-sm-6">
                                <div class="form-group">
                                    <label>
                                        <?= l('Зарплата от прибыли с продаж') ?><?= InfoPopover::getInstance()->createQuestion('l_it_understood_operating_profit') ?>
                                    </label><br>
                                </div>

                            </td>
                            <td class="col-sm-6">
                                <div class="input-group col-sm-5">
                                    <input type="text" class="form-control"
                                           value="<?= (isset($form_data['salary_from_sale']) ? h($form_data['salary_from_sale']) : '') ?>"
                                           name="salary_from_sale" aria-describedby="basic-addon2"/>
                                    <span class="input-group-addon" id="basic-addon2">%</span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="col-sm-6">
                                <div class="form-group">
                                    <label>
                                        <?= l('Диверсифицированный способ начисления зарплаты') ?><?= InfoPopover::getInstance()->createQuestion('l_diversified_way_of_payroll') ?>
                                    </label><br>
                                </div>

                            </td>
                            <td class="col-sm-6">
                                <div class="form-group">
                                    <label style="margin-right:20px">
                                        <input type="checkbox"
                                               name="use_percent_from_profit" <?= isset($form_data['use_percent_from_profit']) && $form_data['use_percent_from_profit'] ? 'checked' : '' ?>/>
                                        <?= l('% от продажи товара/услуги') ?><?= InfoPopover::getInstance()->createQuestion('l_percent_from_profit') ?>
                                    </label>
                                    <label>
                                        <input type="checkbox"
                                               name="use_fixed_payment" <?= isset($form_data['use_fixed_payment']) && $form_data['use_fixed_payment'] ? 'checked' : '' ?>/>
                                        <?= l('фиксированная оплата') ?><?= InfoPopover::getInstance()->createQuestion('l_fixed_payment') ?>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="col-sm-6">
                                <div class="form-group">
                                    <label><?= l('Отправлять сотруднику уведомления, если он будет назначен ответственным инженером или менеджером по заказу') ?></label>
                                </div>
                            </td>
                            <td class="col-sm-6">
                                <div class="form-group">
                                    <label style="margin-right:20px">
                                        <input type="checkbox"
                                               name="over_email" <?= isset($form_data['send_over_email']) && $form_data['send_over_email'] ? 'checked' : '' ?>/>
                                        <?= l('через email') ?>
                                    </label>
                                    <label>
                                        <input type="checkbox"
                                               name="over_sms" <?= isset($form_data['send_over_sms']) && $form_data['send_over_sms'] ? 'checked' : '' ?>/>
                                        <?= l('через SMS') ?>
                                    </label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="col-sm-6">
                                <div class="form-group">
                                    <label>
                                        <?= l('Показывать контактные данные клиентов') ?><?= InfoPopover::getInstance()->createQuestion('l_shos_client_infos') ?>
                                    </label>
                                </div>
                            </td>
                            <td class="col-sm-6">
                                <div class="form-group">
                                    <input type="checkbox"
                                           name="show_client_info" <?= isset($form_data['show_client_info']) && !$form_data['show_client_info'] ? '' : 'checked' ?>/>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="col-sm-6">
                                <div class="form-group">
                                    <label>
                                        <?= l('Сотрудник может видеть только свои заказы на ремонт или продажу') ?><?= InfoPopover::getInstance()->createQuestion('l_show_only_users_orders') ?>
                                    </label>
                                </div>
                            </td>
                            <td class="col-sm-6">
                                <div class="form-group">
                                    <input type="checkbox"
                                           name="show_only_his_orders" <?= isset($form_data['show_only_his_orders']) && !$form_data['show_only_his_orders'] ? '' : 'checked' ?>/>
                                </div>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>

                <?php if (empty($isEdit)): ?>
                    <div class="control-group text-right">
                        <div class="controls">
                            <input class="btn btn-primary" type="submit" name="create-user"
                                   onclick="return add_user_validation();"
                                   value="<?= l('Создать') ?>">
                        </div>
                    </div>
                <?php endif; ?>
            </fieldset>
        </form>
    <?php else: ?>
        <p><?= l('Создание новых пользователей запрещено условиями текущего тарифа') ?></p>
        <div class="form-group">
            <a href="<?= $this->all_configs['prefix'] ?>settings/tariffs" target="_blank"
               class="btn btn-primary"><?= l('Изменить тариф') ?></a>
        </div>
    <?php endif; ?>
</div>


