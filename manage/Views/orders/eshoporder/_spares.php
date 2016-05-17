<thead>
<tr>
    <th></th>
    <th><?= l('Наименование') ?></th>
    <th><?= l('Цена') ?><br>(<?= viewCurrency() ?>)</th>
    <th><?= l('Скидка') ?></th>
    <th><?= l('Кол-во') ?></th>
    <th><?= l('Сумма') ?><br>(<?= viewCurrency() ?>)</th>
    <th class="<?= $prefix == 'quick' ? 'col-sm-3' : '' ?>"><?= l('Гарантия') ?><br><?= l('мес.') ?></th>
    <th></th>
    <th></th>
</tr>
</thead>
<tbody id="goods-table">
<?php if ($goods): ?>
    <?php foreach ($controller->productsGroup($goods) as $hash => $product): ?>
        <?php $quantity = count($product['group']); ?>
        <?php if ($quantity > 1): ?>
            <?= $controller->show_eshop_product($product, $quantity, $hash, true); ?>
        <?php endif; ?>
        <?php foreach ($product['group'] as $row): ?>
            <?= $controller->show_eshop_product($row, 1, $hash, $quantity > 1, $quantity > 1); ?>
        <?php endforeach; ?>
    <?php endforeach; ?>
<?php endif; ?>
</tbody>
