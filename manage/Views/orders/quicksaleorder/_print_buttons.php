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
                                   value="<?= print_link($order['id'], 'invoice', '', true) ?>">
                            <?= l('Чек') ?>
                        </label>
                    </div>
                </li>
                <li>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="print[]"
                                   value="<?= print_link($order['id'], 'sale_warranty', '', true) ?>">
                            <?= l('Гарантийный талон') ?>
                        </label>
                    </div>
                </li>
                <li>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="print[]"
                                   value="<?= print_link($order['id'], 'waybill', '', true) ?>">
                            <?= l('Накладная на отгрузку товара') ?>
                        </label>
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
            <center>
                <button class="btn btn-sm btn-info" type="button" id="print_now"
                        onclick="return print_now_from_orders(this);"><?= l('Распечатать') ?></button>
            </center>
        </div>
    </div>
<?php endif; ?>
