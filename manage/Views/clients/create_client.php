<a class="btn btn-default" href="<?= $this->all_configs['prefix'] . $arrequest[0] ?>">
    <?= l('Список клиентов') ?></a>
<a class="btn btn-default" href="<?= $this->all_configs['prefix'] . $arrequest[0] ?>/create">
    <?= l('Создать клиента') ?></a>
<br><br>

<?= l('Редактирование клиента') ?> ID: <?= $client['id'] ?>
<fieldset>
    <legend>
        <?= htmlspecialchars(empty($client['fio']) ? $client['email'] : $client['fio']) ?>, <?= l('тел') ?>
        : <?= implode(', ', $phones) ?>

        <?php if ($client['tag_id'] != 0): ?>
            <span class="tag" style="background-color: <?= $tags[$client['tag_id']]['color'] ?>">
                <?= htmlspecialchars($tags[$client['tag_id']]['title']) ?>
            </span>
        <?php endif; ?>
    </legend>
</fieldset>

<div class="tabbable">
    <ul class="nav nav-tabs">
        <li <?= (!$new_call_id ? ' class="active"' : '') ?>>
            <a href="#main" data-toggle="tab"> <?= l('Основные') ?> </a>
        </li>
        <li>
            <a href="#calls" data-toggle="tab"><?= l('Звонки') ?></a>
        </li>
        <li>
            <a href="#requests" data-toggle="tab"><?= l('Заявки') ?></a>
        </li>
        <li class="">
            <a href="#orders" data-toggle="tab"><?= l('Заказы') ?></a>
        </li>
        <?php if ($new_call_id): ?>
            <li class="active">
                <a href="#new_call" data-toggle="tab"> <?= l('Новый звонок') ?></a>
            </li>
        <?php endif; ?>
    </ul>
</div>
<div class="tab-content">

    <?php if ($new_call_id): ?>
        <?= $newCallForm; ?>
    <?php endif; ?>
    <div id="main" class="tab-pane<?= (!$new_call_id ? ' active' : '') ?>">
        <div class="row">
            <div class="col-sm-4">
                <form method="post">
                    <div class="col-sm-12">
                        <div class="form-group">
                            <label class="control-label"><?= l('Электронная почта') ?>: </label>
                            <div class="controls">
                                <input value="<?= htmlspecialchars($client['email']) ?>" name="email"
                                       class="form-control "/>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?= l('Ф.И.О.') ?>: </label>
                            <div class="controls">
                                <input value="<?= htmlspecialchars($client['fio']) ?>" name="fio" class="form-control"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label"><?= l('Адрес') ?>: </label>
                            <div class="controls">
                                <input value="<?= htmlspecialchars($client['legal_address']) ?>" name="legal_address"
                                       class="form-control"/>
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
                            <label class="control-label"><?= l('Дата регистрации') ?>: </label>
                            <div class="controls">
                        <span title="<?= do_nice_date($client['date_add'], false) ?>">
                            <?= do_nice_date($client['date_add']) ?>
                        </span>
                            </div>
                        </div>
                        <?php if ($this->all_configs['oRole']->hasPrivilege('site-administration')): ?>
                            <div class="form-group">
                                <label class="control-label"><?= l('Пароль') ?>: </label>
                                <i class="glyphicon glyphicon-warning-sign editable-click" data-type="text"
                                   data-pk="<?= $arrequest[2] ?>" data-type="password"
                                   data-url="<?= $this->all_configs['prefix'] . $arrequest[0] ?>/ajax?act=change-client-password"
                                   data-title="<?= l('Введите новый пароль') ?>" data-display="false"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group ">
                        <?= $contractorsList; ?>
                    </div>
                    <div class="form-group ">
                        <?= $tagsList ?>
                    </div>


                    <div class="form-group">
                        <div class="controls">
                            <input id="save_all_fixed" class="btn btn-primary" type="submit"
                                   value="<?= l('Сохранить изменения') ?>" name="edit-client">
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div id="calls" class="tab-pane">
        <?= get_service('crm/calls')->calls_list_table($client['id']) ?>
    </div>
    <div id="requests" class="tab-pane">
        <?= get_service('crm/requests')->requests_list($client['id']) ?>
    </div>

    <?= $ordersList; ?>
</div>
