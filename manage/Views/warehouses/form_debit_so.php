<form method="POST" id="debit-so-form">
    <input type="hidden" value="<?= $order_id ?>" name="order_id"/>

    <div class="form-group"><label class="control-label">
            <center>
                <b><?= l('Серийный номер') ?></b><br>
                <?php if ($order): ?>
                    <?= h($order['title']) ?> <?= h($order['location']); ?>
                <?php endif; ?>
            </center>
        </label>
        <div class="pull-right">
            <div class="checkbox">
                <label>
                    <input id="dso_auto_serial_all"
                           onchange="$('#debit-so-form input.dso_serial').val('');$('#debit-so-form input.dso_auto_serial').prop('checked', $(this).is(':checked') ? true : true);"
                           type="checkbox"/>
                    <b><?= l('Создать все') ?> </b>
                </label>
                <?= InfoPopover::getInstance()->createQuestion('l_debit_so_create_all_info') ?>
            </div>
            <div class="checkbox">
                <label>
                    <input type="checkbox" id="dso_print_all"
                           onchange="$('#debit-so-form input.dso_print').prop('checked', $(this).is(':checked') ? true : false);"/>
                    <b> <?= l('Распечатать все') ?></b>
                </label>
            </div>
        </div>
    </div>
    <hr>

    <?php if ($count > 0): ?>
        <?php for ($i = 1; $i <= $count; $i++): ?>
            <div class="form-group" id="dso-group-<?= $i ?>">
                <input
                    onkeyup="if(this.value.trim()== ''){$('#dso-group-<?= $i ?> input.dso_auto_serial, #dso_auto_serial_all').prop('checked', true);}else{$('#dso-group-<?= $i ?> input.dso_auto_serial, #dso_auto_serial_all').prop('checked', false);}"
                    type="text"
                    class="form-control input-large dso_serial" placeholder="<?= l('серийный номер') ?>"
                    name="serial[<?= $i ?>]"/>
                <div class="checkbox">
                    <label class="">
                        <input checked
                               onchange="$('#dso_auto_serial_all').prop('checked', false);$('#dso-group-<?= $i ?> input.dso_serial').val('');this.checked=true;"
                               type="checkbox"
                               class="dso_auto_serial" name="auto[<?= $i ?>]"/>
                        <?= l('Сгенерировать серийник') ?>
                    </label>
                    <?= InfoPopover::getInstance()->createQuestion('l_debit_so_auto_serial_info') ?>
                </div>
                <div class="checkbox">
                    <label class="">
                        <input onchange="$('#dso_print_all').prop('checked', false);" type="checkbox"
                               name="print[<?= $i ?>]" class="dso_print"/>
                        <?= l('Распечатать серийник') ?>
                    </label>
                    <?= InfoPopover::getInstance()->createQuestion('l_debit_so_print_serial_info') ?>
                </div>
                <div class="dso-msg center"></div>
            </div>
        <?php endfor; ?>
    <?php else: ?>
        <p class="center text-error"><?= l('Все изделия оприходованы') ?></p>
    <?php endif; ?>
</form>
