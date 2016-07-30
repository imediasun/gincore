<table class="table" style="font-size: 14px;">
    <thead>
    <tr style="font-weight: bold;">
        <td style="border: 1px solid grey; text-align: center">
            <?= l('N п/п') ?>
        </td>
        <td style="border: 1px solid grey; text-align: center">
            <?= l('Наименование') ?>
        </td>
        <td style="border: 1px solid grey; text-align: center">
            <?= l('Количество') ?>
        </td>
        <td style="border: 1px solid grey; text-align: center">
            <?= l('Цена') ?><br>
            <?= viewCurrencySuppliers() ?>
        </td>
        <td style="border: 1px solid grey; text-align: center">
            <?= l('Сумма') ?><br>
            <?= viewCurrencySuppliers() ?>
        </td>
    </tr>
    </thead>

    <tbody>
    <?php if (!empty($orders)): ?>
        <?php $i = 1; ?>
        <?php foreach ($orders as $order): ?>
            <tr>
                <td style="border: 1px solid grey">
                    <?= $i++ ?>
                </td>
                <td style="border: 1px solid grey">
                    <?= h($order['title']) ?>
                </td>
                <td style="border: 1px solid grey; text-align: right; padding-right: 30px !important;">
                    <?= h($order['count']) ?>
                </td>
                <td style="border: 1px solid grey; text-align: right; padding-right: 30px !important;">
                    <?= h($order['price']) / 100 ?>
                </td>
                <td style="border: 1px solid grey; text-align: right; padding-right: 30px !important;">
                    <?= $order['count'] * $order['price'] / 100 ?>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
    <tfoot>
    <tr>
        <td style="border: 1px solid grey; text-align: left; font-weight: bold" colspan="4">
            <?= l('Итого') ?>
        </td>
        <td style="border: 1px solid grey; text-align: right; padding-right: 30px !important;">
            <?= $amount ?>
        </td>
    </tr>

    </tfoot>
</table>
