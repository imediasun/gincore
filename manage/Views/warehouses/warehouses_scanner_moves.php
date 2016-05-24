<?php if ($this->all_configs['oRole']->hasPrivilege('scanner-moves')): ?>?
    <div id="scanner-moves-alert" class="alert fade">
        <button type="button" class="close" data-dismiss="alert">&times;</button>
        <div id="scanner-moves-alert-body"></div>
    </div>
    <label>
        <?= l('Укажите номер заказа, изделия или локации. После чего нажмите Enter. Или используйте сканер.') ?>
        <?= InfoPopover::getInstance()->createQuestion('l_warehouses_scanner_moves_info') ?>
    </label>
    <input value="" id="scanner-moves" type="text" placeholder="<?= l('заказ, изделие или локация') ?>"
           class="form-control"/>
    <input value="" id="scanner-moves-old" type="hidden" placeholder="<?= l('заказ или локация') ?>"
           class="form-control"/>
<?php endif; ?>
