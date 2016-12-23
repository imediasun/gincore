<?php if (!empty($goods)): ?>
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
    <?php foreach ($goods as $product): ?>
        <?= $controller->show_quicksale_product($product); ?>
    <?php endforeach; ?>
    </tbody>
<?php endif; ?>
