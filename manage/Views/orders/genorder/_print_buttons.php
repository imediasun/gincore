<?php if ($hasEditorPrivilege): ?>
    <div class="btn-group">
        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
            <i class="fa fa-print"></i> <span class="caret"></span>
        </button>
        <div class="keep-open dropdown-menu ">
            <ul class="print_menu">
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
                            <input type="checkbox" name="print[]"
                                   value="<?= print_link($order['id'], 'act', '', true) ?>">
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
                <?php foreach ($print_templates as $print_template): ?>
                    <li>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="print[]"
                                       value="<?= print_link($order['id'], $print_template['var'], '', true) ?>">
                                <?= h($print_template['description']) ?>
                            </label>
                        </div>
                    </li>
                <?php endforeach; ?>

                <li role="separator" class="divider"></li>
            </ul>
            <button class="btn btn-sm btn-info" type="button" id="print_now" onclick="return print_now_from_orders(this);"><?= l('Распечатать') ?></button>
        </div>
    </div>
<?php endif; ?>
<script>
    jQuery(document).ready(function () {
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