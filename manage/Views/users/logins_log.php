<div id="login_log" class="tab-pane">
    <div class="row-fluid">
        <div class="col-sm-6 text-right">
            <a href="<?= $this->all_configs['prefix'] ?>users/generate_log_file"><i
                    class="fa fa-download "></i>&nbsp;<?= l('Выгрузить отчет') ?></a>
        </div>
    </div>
    <?php if (!empty($users)): ?>
        <div class="row-fluid">
            <div class="panel-group col-sm-6" id="accordion">
                <?php foreach ($users as $user): ?>
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h4 class="panel-title">
                                <a data-toggle="collapse" data-parent="#accordion" href="#collapse<?= $user['id'] ?>">
                                    <?= empty($user['fio']) ? $user['login'] : $user['fio'] ?>
                                </a>
                            </h4>
                        </div>
                        <div id="collapse<?= $user['id'] ?>" class="panel-collapse collapse">
                            <div class="panel-body scrolled-logs">
                                <?php if (empty($user['logs'])): ?>
                                    <?= l('Нет статистики') ?>
                                <?php else: ?>
                                    <table class="table">
                                        <thead>
                                        <tr>
                                            <td><?= l('Дата входа') ?></td>
                                            <td>IP</td>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($user['logs'] as $logs): ?>
                                            <tr>
                                                <td><?= $logs['created_at'] ?></td>
                                                <td><?= $logs['ip'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    <div class="row-fluid">
        <div class="col-sm-6 text-right">
            <form method="POST" class='form-inline'>
                <fieldset>
                    <input type="hidden" name="save-send-log-email" value="1"/>
                    <div class="form-group">
                        <label style="line-height: 44px">
                            <input type="checkbox" name="send_email"
                                <?= $emailSettings['need_send_login_log']['value'] ? 'checked' : '' ?>/>
                            <?= l('Отправлять ежедневный отчет ') ?>
                        </label>
                    </div>
                    <div class="form-group">
                        <label style="line-height: 44px">
                            <?= l('на ящик') ?>
                            <input type="email" name="email" class="form-control"
                                   placeholder="<?= l('Введите email') ?>"
                                   value="<?= $emailSettings['email_for_send_login_log']['value'] ?>"/>
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary"> <?= l('Сохранить') ?></button>
                </fieldset>
            </form>
        </div>
    </div>
</div>
