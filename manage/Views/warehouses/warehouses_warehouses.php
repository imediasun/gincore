<div class="well"><?= l('Всего') ?>:
    <?php if ($this->all_configs['oRole']->hasPrivilege('logistics')): ?>
        <?= $cost_of['cur_price'] ?> (<?= $cost_of['html'] ?>),
    <?php endif; ?>
    <?= $cost_of['count'] ?> <?= l('шт.') ?>
</div>';
<?= $filters ?>
<div id="warehouses_content">
    <?php if (!empty($warehouses)): ?>
        <div class="pull-left vertical-line"></div>
        <?php $i = 0; ?>
        <?php foreach ($warehouses as $warehouse): ?>
            <div class="show_warehouse">
                <h5>
                    <a class="hash_link"
                       href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>?whs=<?= $warehouse['id'] ?>#show_items">
                        <?= $warehouse['title'] ?>
                    </a>
                    <?= (!$i ? InfoPopover::getInstance()->createOnLoad('l_warehouses_title_info') : '') ?>
                    <?= print_link(array_keys($warehouse['locations']), 'location'); ?>
                </h5>
                <div><?= l('Общий остаток') ?>: <?= intval($warehouse['sum_qty']) ?> <?= l('шт.') ?></div>
                <?php if ($this->all_configs['oRole']->hasPrivilege('logistics')): ?>
                    <div>
                        <?= l('Общая сумма') ?>:
                        <?= $controller->show_price($warehouse['all_amount'], 2,
                            getCourse($this->all_configs['settings']['currency_suppliers_orders'])); ?>
                        <?= viewCurrency() ?>
                        (<?= $this->show_price($warehouse['all_amount']) . viewCurrencySuppliers() ?> )
                    </div>
                <?php endif; ?>
            </div>
            <div class="pull-left vertical-line"></div>
            <?php $i++; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</div>