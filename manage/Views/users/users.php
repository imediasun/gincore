<div id="edit_tab_users" class="tab-pane active">
    <form enctype="multipart/form-data" method="post" id="users-form">
        <input type="hidden" value="<?= $tariff['number_of_users'] ?>" name="limit"/>
        <table class="table table-striped">
            <thead>
            <tr>
                <td>ID</td>
                <td><?= l('Фото') ?></td>
                <td><i class="glyphicon glyphicon-envelope"></i></td>
                <td><?= l('Логин') ?></td>
                <td title="<?= l('Активный') ?>"><i class="glyphicon glyphicon-off"></i></td>
                <td><?= l('Пароль') ?></td>
                <td><?= l('Роль') ?></td>
                <td><?= l('ФИО') ?></td>
                <td>
                    <?php if (isset($_GET['sort']) && $_GET['sort'] == 'position'): ?>
                        <a href="?sort=rposition"><?= l('Должность') ?><i class="glyphicon glyphicon-chevron-down"></i>
                        </a>
                    <?php elseif (isset($_GET['sort']) && $_GET['sort'] == 'rposition'): ?>
                        <a href="?sort=position"><?= l('Должность') ?><i class="glyphicon glyphicon-chevron-up"></i>
                        </a>
                    <?php else: ?>
                        <a href="?sort=position"><?= l('Должность') ?> </a>
                    <?php endif; ?>
                </td>
                <td><?= l('Телефон') ?></td>
                <td><?= l('Эл. почта') ?></td>
                <td title="<?= l('Серийный номер сертификата') ?>"><?= l('Номер сертиф.') ?></td>
                <td title="<?= l('Вход только по сертификату') ?>"><?= l('Вход по сертиф.') ?></td>
                <td title="<?= l('Удалить') ?>"><i class="glyphicon glyphicon-remove"></i></td>
            </tr>
            </thead>
            <tbody>

            <?php $yet = array(); ?>
            <?php if (count($users) > 0): ?>
                <?php $i = 0; ?>
                <?php foreach ($users as $user): ?>
                    <?php if (!array_key_exists($user['id'], $yet)): ?>
                        <tr class="user-row">
                            <td><a href="#" class="js-edit-user" data-uid="<?= $user['id'] ?>"><?= $user['id'] ?></a>
                            </td>
                            <td>
                                <img class="upload_avatar_btn" data-uid="<?= $user['id'] ?>" width="40"
                                     src="<?= $controller->avatar($user['avatar']) ?>">
                            </td>
                            <td>
                                <input type="checkbox" name="send-mess-user[<?= $user['id'] ?>]" class="send-mess-user"
                                       value="<?= $user['id'] ?>"/>
                            </td>
                            <td><a href="#" class="js-edit-user"
                                   data-uid="<?= $user['id'] ?>"><?= htmlspecialchars($user['login']) ?></a></td>
                            <td><input class="checkbox js-block-by-tariff" <?= $user['avail'] ? 'checked' : '' ?>
                                       type="checkbox"
                                       name="avail_user[<?= $user['id'] ?>]"/>
                            </td>
                            <td style="text-align:center;">
                                <i class="fa fa-lock editable-click" data-type="text"
                                   data-pk="<?= $user['id'] ?>"
                                   data-type="password"
                                   data-url="<?= $this->all_configs['arrequest'][0] ?>/ajax?act=change-admin-password"
                                   data-title="Введите новый пароль" data-display="false"></i></td>
                            <td>
                                <select class="form-control input-sm" name="roles[<?= $user['id'] ?>]">
                                    <option value=""></option>

                                    <?php $yet1 = array(); ?>
                                    <?php foreach ($activeRoles as $per): ?>
                                        <?php if (!array_key_exists($per['role_id'], $yet1)): ?>
                                            <?php if ($per['role_id'] == $user['role_id']): ?>
                                                <option selected
                                                        value="<?= $per['role_id'] ?>"><?= htmlspecialchars($per['role_name']) ?> </option>
                                            <?php else: ?>
                                                <option
                                                    value="<?= $per['role_id'] ?>"><?= htmlspecialchars($per['role_name']) ?></option>
                                            <?php endif; ?>
                                            <?php $yet1[$per['role_id']] = $per['role_id']; ?>
                                        <?php endif; ?>
                                    <?php endforeach; ?>

                                </select></td>
                            <td>
                                <input placeholder="<?= l('введите ФИО') ?>" class="form-control input-sm"
                                       name="fio[<?= $user['id'] ?>]" value="<?= htmlspecialchars($user['fio']) ?>"/>
                            </td>
                            <td>
                                <input placeholder="<?= l('введите должность') ?>" class="form-control input-sm"
                                       name="position[<?= $user['id'] ?>]"
                                       value="<?= htmlspecialchars($user['position']) ?>"/>
                            </td>
                            <td>
                                <input placeholder="<?= l('введите телефон') ?>" onkeydown="return isNumberKey(event)"
                                       class="form-control input-sm" name="phone[<?= $user['id'] ?>]"
                                       value="<?= $user['phone'] ?>"/>
                            </td>
                            <td>
                                <input placeholder="<?= l('введите email') ?>" class="form-control input-sm"
                                       name="email[<?= $user['id'] ?>]" value="<?= $user['email'] ?>"/>
                            </td>
                            <td>
                                <input placeholder="" class="form-control input-sm"
                                       name="auth_cert_serial[<?= $user['id'] ?>]"
                                       value="<?= $user['auth_cert_serial'] ?>"/>
                            </td>
                            <td>
                                <input <?= $user['auth_cert_only'] ? 'checked' : '' ?> type="checkbox"
                                                                                       name="auth_cert_only[<?= $user['id'] ?>]"/>
                            </td>
                            <td>
                                <a href="#" class="danger delete-user" title="<?= l('Удалить') ?>">
                                    <i class="glyphicon glyphicon-remove"
                                       onclick="delete_user(this, <?= $user['id'] ?>);"
                                       data-id="<?= $user['id'] ?>"></i>
                                </a>
                            </td>
                        </tr>
                        <?php $yet[$user['id']] = $user['id']; ?>
                        <?php if ($user['avail']): ?>
                            <?php $i++ ?>
                        <?php endif; ?>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php endif; ?>

            <a class="btn btn-success send-mess" href="#"><?= l('Отправить сообщение') ?></a>

            </tbody>
        </table>
        <input type="submit" name="change-roles" value="<?= l('Сохранить') ?>"
               class="btn btn-primary js-change-roles-btn"/>
    </form>
</div>
<div id="upload_avatar" class="modal fade">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                        aria-hidden="true">×</span></button>
                <h4 class="modal-title"><?= l('Аватар') ?></h4>
            </div>
            <div class="modal-body">
                <div id="fileuploader"></div>
            </div>
        </div>
    </div>
</div>

