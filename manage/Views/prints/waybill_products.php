<table class="table" style="font: 12px;">
    <thead>
    <tr style="font-weight: bold; text-align: center">
        <td style="border: 1px solid grey">
            <?= l('N п/п') ?>
        </td>
        <td style="border: 1px solid grey">
            <?= l('Наименование') ?>
        </td>
        <td style="border: 1px solid grey">
            <?= l('Количество') ?>
        </td>
        <td style="border: 1px solid grey">
            <?= l('Цена') ?><br>
            <?= viewCurrency() ?>
        </td>
        <td style="border: 1px solid grey">
            <?= l('Скидка') ?>
        </td>
        <td style="border: 1px solid grey">
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
                <td style="border: 1px solid grey">
                    <?= $i++ ?>
                </td>
                <td style="border: 1px solid grey">
                    <?= h($good['title']) ?>
                </td>
                <td style="border: 1px solid grey; text-align: right; padding-right: 30px !important;">
                    <?= h($good['count']) ?>
                </td>
                <td style="border: 1px solid grey; text-align: right; padding-right: 30px !important;">
                    <?= h($good['price']) / 100 ?>
                </td>
                <td style="border: 1px solid grey; text-align: right; padding-right: 30px !important;">
                    <?= h($good['discount']) ?> <?= $good['discount_type'] == DISCOUNT_TYPE_PERCENT ? '%' : viewCurrency() ?>
                </td>
                <td style="border: 1px solid grey; text-align: right; padding-right: 30px !important;">
                    <?= sum_with_discount($good) ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>
