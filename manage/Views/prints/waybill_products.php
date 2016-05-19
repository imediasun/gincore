<table>
    <thead>
    <tr>
        <td>
            <?= l('N п/п') ?>
        </td>
        <td>
            <?= l('Наименование') ?>
        </td>
        <td>
            <?= l('Количество') ?>
        </td>
        <td>
            <?= l('Цена') ?><br>
            <?= viewCurrency() ?>
        </td>
        <td>
            <?= l('Скидка') ?>
        </td>
        <td>
            <?= l('Сумма') ?><br>
            <?= viewCurrency() ?>            
        </td>
    </tr>
    </thead>

    <tbody>
    <?php if (!empty($goods)): ?>
        <?php $i = 1; ?>
        <?php foreach ($goods as $good): ?>
            <tr>
                <td>
                    <?= $i++ ?>
                </td>
                <td>
                    <?= h($good['title']) ?>
                </td>
                <td>
                    <?= h($good['count']) ?>
                </td>
                <td>
                    <?= h($good['price']) / 100 ?>
                </td>
                <td>
                    <?= h($good['discount']) ?> <?= $good['discount_type'] == DISCOUNT_TYPE_PERCENT ? '%' : viewCurrency() ?>
                </td>
                <td>
                    <?= sum_with_discount($good) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
