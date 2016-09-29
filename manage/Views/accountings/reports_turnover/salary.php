<div class="row-fluid">
    <?php if (!empty($users)): ?>
        <?php foreach ($users as $user): ?>
            <div class="col-sm-4 well well-bordered" style="margin-right: 19px; padding: 10px 0px 10px 10px">
                <table class="table table-compact table-no-border" style="margin-bottom: 0px">
                    <tr>
                        <td><?= l('Зарплата сотрудника') ?><?= InfoPopover::getInstance()->createQuestion('l_it_users_salary_from_orders_profit') ?></td>
                        <td><?= $user['fio'] ?></td>
                    </tr>
                    <tr>
                        <td><?= l('Ремонты') ?></td>
                        <td>
                            <?php if ($user['use_fixed_payment'] || $user['use_percent_from_profit']): ?>
                                <?= round(($repairProfit[$user['id']] / 100), 2) ?> &nbsp;<?= viewCurrency() ?>
                            <?php else: ?>
                                <?= round(($repairProfit[$user['id']] / 100) * ($user['salary_from_repair'] / 100), 2) ?>
                                &nbsp;<?= viewCurrency() ?>(<?= $user['salary_from_repair'] ?>%)
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?= l('Продажи') ?></td>
                        <td>
                            <?php if ($user['use_fixed_payment'] || $user['use_percent_from_profit']): ?>
                                <?= round(($saleProfit[$user['id']] / 100), 2) ?> &nbsp;<?= viewCurrency() ?>
                            <?php else: ?>
                                <?= round(($saleProfit[$user['id']] / 100) * ($user['salary_from_sale'] / 100), 2) ?>
                                &nbsp;<?= viewCurrency() ?>(<?= $user['salary_from_sale'] ?>%)
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
