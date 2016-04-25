<?php if ($goods_suppliers): ?>
    <table class="table table-striped">
        <thead>
        <tr>
            <td><?= l('Поставщик') ?></td>
            <td><?= l('Цена закупки') ?></td>
            <td><?= l('Цена продажи') ?></td>
            <td><?= l('Количество') ?></td>
            <td><?= l('Дата') ?></td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($goods_suppliers as $vgs): ?>
            <tr>
                <td><?= htmlspecialchars($vgs['title']) ?></td>
                <td><?= number_format($vgs['price'] / 100, 2, ',', ' ') ?></td>
                <td><?= number_format($vgs['price_sell'] / 100, 2, ',', ' ') ?></td>
                <td><?= htmlspecialchars($vgs['qty']) ?></td>
                <td><?= do_nice_date($vgs['date_add']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-error"><?= l('Нет информации') ?></p>
<?php endif; ?>
