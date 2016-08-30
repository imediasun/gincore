<?php $count = $order ? $order['count_come'] - $order['count_debit'] : 0; ?>
<?php if ($count > 0): ?>
    <table class="table">
        <thead>
        <tr>
            <td>
                <?= l('Наименование') ?>
            </td>
            <td width='20%' style="white-space: nowrap; text-align: center">
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
            <td style="text-align: center"> <?= $count ?> </td>
            <td> <div class="text-success"> <?= l('Серийные номера успешно добавлены в систему'); ?> </div> </td>
        </tr>
        </tbody>
    </table>
    <div class="row">

        <?php $cols = 1 ?>
        <?php for ($i = 0; $i < count($msg); $i += 5): ?>
                <?php for ($j = 0; $j < 5 && $cols <= count($msg); $j++): ?>
                        <div class="span1 <?= $msg[$cols]['state'] ? 'text-success' : 'text-error' ?>"><?= $msg[$cols]['msg'] ?></div>
                    <?php $cols++ ?>
                <?php endfor; ?>
        <?php endfor; ?>
    </div>
<?php endif; ?>

