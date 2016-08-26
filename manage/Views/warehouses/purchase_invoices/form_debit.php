<form method="POST" id="debit-so-form">
    <input type="hidden" value="<?= $invoice_id ?>" name="invoice_id"/>

    <div class="form-group">
        <label class="control-label" style="text-align: left">
            <b><?= l('Приходная накладная') ?> N<?= $invoice['id'] ?></b><br>
            <?php if ($invoice): ?>
                <?= l('Приходуется на:') ?> <?= h($invoice['warehouse']) ?> <?= h($invoice['location']); ?>
            <?php endif; ?>
        </label>
    </div>
    <hr>

    <div id="debit-so-form-content">
        <?php foreach ($goods as $id => $good): ?>
            <?php $count = $good['quantity'] ?>
            <?php if ($count > 0): ?>
                <input type="hidden" name="goods[<?= $id ?>]" value="<?= $good['good_id'] ?>" />
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
                        <td>
                            <?= h($good['item']); ?>
                        </td>
                        <td style="text-align: center">
                            <?= $count ?>
                        </td>
                        <td>
                            <div class="checkbox">
                                <label class="">
                                    <input checked
                                           onchange="$('.js-serials-<?= $id ?>').toggle(); return true;"
                                           type="checkbox"
                                           class="dso_auto_serial" name="auto[<?= $id ?>]"/>
                                    <?= l('Сгенерировать') ?>
                                </label>
                                <?= InfoPopover::getInstance()->createQuestion('l_debit_so_auto_serial_info') ?>
                            </div>
                            <div class="checkbox">
                                <label class="">
                                    <input type="checkbox" name="print[<?= $id ?>]" class="dso_print"/>
                                    <?= l('Распечатать') ?>
                                </label>
                                <?= InfoPopover::getInstance()->createQuestion('l_debit_so_print_serial_info') ?>
                            </div>

                        </td>
                    </tr>
                    </tbody>
                </table>
                <table class="table js-serials-<?= $id ?>" style="display: none">
                    <tbody>
                    <?php $cols = 1 ?>
                    <?php for ($i = 0; $i < $count; $i += 4): ?>
                        <tr>
                            <?php for ($j = 0; $j < 4 && $cols <= $count; $j++): ?>
                                <td>
                                    <?= $cols ?>.
                                </td>
                                <td>
                                    <input type="text" class="form-control input-large dso_serial"
                                           placeholder="<?= l('серийный номер') ?>" name="serial[<?= $id ?>][<?= $cols ?>]"/>
                                </td>
                                <?php $cols++ ?>
                            <?php endfor; ?>
                        </tr>
                    <?php endfor; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="center text-error"><?= l('Все изделия оприходованы') ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
</form>
<style>
    .modal.in .modal-dialog {
        width: 800px;
    }
</style>

