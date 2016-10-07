<?php if ($user['use_fixed_payment'] || $user['use_percent_from_profit']): ?>
    <a href="#" onclick="$('.detailed-calculation').toggle(); return false;"
       class="compact"><?= l('Подробный расчет') ?>
        <i class="fa fa-caret-down" aria-hidden="true"></i>
    </a>
    <div class="detailed-calculation" style="display:none">
        <div class="compact">
            <?= l('Способ начисление заработной платы:') ?>
            <?= l('Диверсифицированный') ?>
            <?php if ($user['use_fixed_payment']): ?>
                <?= l('фиксированная оплата') ?>
            <?php endif; ?>
            <?php if ($user['use_percent_from_profit']): ?>
                <?= l('% от продаж товаров/услуг') ?>
            <?php endif; ?>
        </div>

        <table class="table table-compact table-borderless">
            <thead>
            <tr>
                <th> <?= l('Id заказа'); ?> </th>
                <th> <?= l('Товары/Услуги') ?> </th>
                <th> <?= l('Себестоимость') ?> </th>
                <th> <?= l('Отпускная цена') ?> </th>
                <th> <?= l('Зарплата') ?> </th>
            </tr>
            </thead>
            <?php $salary = $selling_price = $cost_price = 0 ?>
            <?php if (!empty($detailedSalary)): ?>
                <tbody>
                <?php foreach ($detailedSalary as $detailed): ?>
                    <tr>
                        <td>
                            <a href="<?= $this->all_configs['prefix'] ?>orders/create/<?= $detailed['order_id'] ?>"><?= $detailed['order_id']; ?></a>
                        </td>
                        <td>
                            <?= $detailed['product']; ?>
                        </td>
                        <td>
                            <?php $cost_price += $detailed['cost_price']; ?>
                            <?= round($detailed['cost_price'] / 100, 2); ?>
                        </td>
                        <td>
                            <?php $selling_price += $detailed['selling_price']; ?>
                            <?= round($detailed['selling_price'] / 100, 2); ?>
                        </td>
                        <td>
                            <?php $salary += $detailed['salary']; ?>
                            <?= round($detailed['salary'] / 100, 2); ?>
                            <?php if (!empty($detailed['percent'])): ?>
                                (<?= $detailed['percent'] ?>%)
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            <?php endif; ?>
            <tfoot>
            <tr class="border-top">
                <td> <?= count($detailedSalary) ?> <?= l('pcs.') ?> </td>
                <td></td>
                <td> <?= round($cost_price / 100 / 100, 2) ?> </td>
                <td> <?= round($selling_price / 100, 2) ?> </td>
                <td> <?= round($salary / 100, 2) ?> </td>
            </tr>
            </tfoot>
        </table>
    </div>
<?php endif; ?>
