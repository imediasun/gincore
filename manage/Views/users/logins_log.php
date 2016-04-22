<div id="login_log" class="tab-pane">
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
                            <div class="panel-body">
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
</div>
