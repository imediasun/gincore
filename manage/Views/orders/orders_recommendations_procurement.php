<?php if ($qty_weeks > 0): ?>
    <table class="table" id="tablesorter">
        <thead>
        <tr>
            <th><?= l('Наименование') ?></th>
            <th><?= l('Общ.ост.') ?></th>
            <th><?= l('Своб.ост.') ?></th>
            <th><?= l('Ожид.пост.(общ.)') ?></th>
            <th><?= l('Ожид.пост.(своб.)') ?></th>
            <th><?= l('Расход (шт/мес)') ?></th>
            <th><?= l('Спрос (шт/мес)') ?></th>
            <th><?= l('Прогноз') ?></th>
            <th><?= l('Рекомендовано еще к заказу') ?></th>
        </tr>
        </thead>
        <tbody>
        <?php $href = $cfg['prefix'] . 'products/create/'; ?>
        <?php if (!empty($amounts)): ?>
            <?php foreach ($amounts as $p_id => $amount): ?>
                <tr>
                    <td><a href="<?= $href . $p_id ?>"><?= htmlspecialchars($amount['title']) ?></a></td>
                    <td><?= (isset($amount['qty_wh']) ? $amount['qty_wh'] : 0) ?></td>
                    <td><?= (isset($amount['qty_store']) ? $amount['qty_store'] : 0) ?></td>
                    <td><?= (isset($amount['qty_wait_wh']) ? $amount['qty_wait_wh'] : 0) ?></td>
                    <td><?= (isset($amount['qty_wait_store']) ? $amount['qty_wait_store'] : 0) ?></td>
                    <td><?= (isset($amount['qty_consumption']) ? $amount['qty_consumption'] : 0) ?></td>
                    <td><?= (isset($amount['qty_demand']) ? $amount['qty_demand'] : 0) ?></td>
                    <td><?= (isset($amount['qty_forecast']) ? $amount['qty_forecast'] : 0) ?></td>
                    <td><?= (isset($amount['qty_recommended']) ? $amount['qty_recommended'] : 0) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-danger"><?= l('Для правильности рассчетов укажите сроки доставки заказа поставщику') ?></p>
<?php endif; ?>
