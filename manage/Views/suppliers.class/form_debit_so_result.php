<?php $count = $order ? $order['count_come'] - $order['count_debit'] : 0; ?>
<?php if ($count > 0): ?>
    <table class="table">
        <thead>
        <tr>
            <td>
                <?= l('Наименование') ?>
            </td>
            <td style="white-space: nowrap; text-align: center">
                <?= l('Кол-во, шт.') ?>
            </td>
            <td width="30%">
                <?= l('Серийные номера') ?>
            </td>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td> <?= h($order['item']); ?> </td>
            <td> <?= $count ?> </td>
            <td> <div class="text-success"> <?= l('Серийные номера успешно добавлены в систему'); ?> </div> </td>
        </tr>
        </tbody>
    </table>
    <table class="table table-borderless">
        <tbody>
        <?php $cols = 1 ?>
        <?php for ($i = 0; $i < count($msg); $i += 5): ?>
            <tr>
                <?php for ($j = 0; $j < 5 && $cols <= count($msg); $j++): ?>
                    <td>
                        <div
                            class="<?= $msg[$cols]['state'] ? 'text-success' : 'text-error' ?>"><?= $msg[$cols]['msg'] ?></div>
                    </td>
                    <?php $cols++ ?>
                <?php endfor; ?>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>
<?php endif; ?>

