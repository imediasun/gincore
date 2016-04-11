<?php if ($this->all_configs['oRole']->hasPrivilege('accounting') ||
    $this->all_configs['oRole']->hasPrivilege('accounting-contractors')
): ?>
    <?php if ($contractors): ?>
        <table class="table">
            <thead>
            <tr>
                <td></td>
                <td><?= l('Название') ?></td>
                <td><?= l('Сумма') ?></td>
                <td></td>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($contractors as $contractor): ?>
                <tr class="<?= ($contractor['amount'] > 0 ? 'success' : '') . ($contractor['amount'] < 0 ? 'danger' : '') ?>">
                    <td><?= $contractor['id'] ?></td>
                    <td>
                        <a class="hash_link"
                           href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0]; ?>?ct=<?= $contractor['id'] ?>#transactions-contractors">
                            <?= $contractor['name'] ?>
                        </a>
                    </td>
                    <td>
                        <?= show_price($contractor['amount']) ?>
                    </td>
                    <td>
                        <input class="btn btn-default btn-xs" type="button" value="<?= l('Проверить') ?>"
                               onclick="check_contractor_amount(this, <?= $contractor['id'] ?>)"/>
                        <div class="pull-right">
                            <?= ($contractor['amount'] > 0 ? l('Вы должны') : ($contractor['amount'] < 0 ? l('Вам должны') : '')) ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-error"><?= l('Список контрагентов пуст.') ?></p>
    <?php endif; ?>
<?php endif; ?>
