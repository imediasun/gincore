<table class="<?= !$goods ? 'hidden ' : '' ?> table parts-table cart-table">
    <thead>
    <tr>
        <th><?= l('Наименование') ?></th>
        <th><?= l('Цена') ?>(<?= viewCurrency() ?>)</th>
        <th><?= l('Скидка') ?></th>
        <th><?= l('Сумма') ?>(<?= viewCurrency() ?>)</th>
        <th class="<?= $prefix == 'quick' ? 'col-sm-3' : '' ?>"><?= l('Гарантия') ?></th>
        <th></th>
        <th></th>
    </tr>
    </thead>
    <tbody id="goods-table">
    <?php if ($goods): ?>
        <?php foreach ($goods as $product): ?>
            <?= $controller->show_eshop_product($product); ?>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
