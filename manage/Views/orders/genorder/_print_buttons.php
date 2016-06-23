<?php if ($hasEditorPrivilege): ?>
    <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
            <i class="fa fa-print"></i> <span class="caret"></span>
        </button>
        <ul class="keep-open dropdown-menu print_menu" style="overflow: visible;">
            <li>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="print[]"
                               value="<?= print_link($order['id'], 'check', '', true) ?>">
                        <?= l('Квитанция') ?>
                    </label>
                </div>
            </li>
            <li>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="print[]"
                               value="<?= print_link($order['id'], 'invoice', '', true) ?>">
                        <?= l('Чек') ?>
                    </label>
                </div>
            </li>
            <li>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="print[]"
                               value="<?= print_link($order['id'], 'warranty', '', true) ?>">
                        <?= l('Гарантия') ?>
                    </label>
                </div>
            </li>
            <li>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="print[]" value="<?= print_link($order['id'], 'act', '', true) ?>">
                        <?= l('Акт выполненых работ') ?>
                    </label>
                </div>
            </li>
            <li>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="print[]"
                               value="<?= print_link($order['id'], 'invoicing', '', true) ?>">
                        <?= l('Счет на оплату') ?>
                    </label>
                </div>
            </li>
            <li>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="print[]"
                               value="<?= print_link($order['id'], 'order_barcode', '', true) ?>">
                        <?= l('Штрих-код') ?>
                    </label><?= InfoPopover::getInstance()->createQuestion('l_it_order-barcode_print_form') ?>
                </div>
            </li>
            <li role="separator" class="divider"></li>
            <li class="text-center">
                <button class="btn btn-sm btn-info" type="button" id="print_now"><?= l('Распечатать') ?></button>
            </li>
        </ul>
    </div>
<?php endif; ?>
<script>
    jQuery(document).ready(function(){
        $('.infopopover_onclick').on('click', function (e) {
            console.log('test');
            e.stopPropagation();
            var $this = $(this);
            if (!$this.hasClass('hasPopover')) {
                init_popover($this);
                $this.addClass('hasPopover');
            }
            $this.popover('toggle');
        });
    });
</script>