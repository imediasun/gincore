<div class="row-fluid">
    <?php if (!empty($users)): ?>
        <?php foreach ($users as $user): ?>
            <div class="col-sm-3 well well-bordered" style="margin-right: 19px; padding-right: 0px">
                <table class="table table-no-border">
                    <tr>
                        <td><?= l('Зарплата сотрудника') ?></td>
                        <td><?= $user['fio'] ?></td>
                    </tr>
                    <tr>
                        <td><?= l('Ремонты') ?></td>
                        <td><?= round(($repairProfit[$user['id']]/ 100) * ($user['salary_from_repair']/100), 2) ?></td>
                    </tr>
                    <tr>
                        <td><?= l('Продажи') ?></td>
                        <td><?= round(($saleProfit[$user['id']]/100) * ($user['salary_from_sale']/100), 2) ?></td>
                    </tr>
                </table>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
