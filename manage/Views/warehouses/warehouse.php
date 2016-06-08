<div class="show_warehouse">
    <div class="warehouse-title">
        <div class="warehouse-filter">
            <a class="hash_link"
               href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>?whs=<?= $warehouse['id'] ?>#show_items">
                <i class="fa fa-list" aria-hidden="true"></i>
            </a>
        </div>
        <div class="title">
            <a class="hash_link"
               href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>?whs=<?= $warehouse['id'] ?>#show_items">
                <?= $warehouse['title'] ?>
            </a>
            <?= ($warehouse['type'] == 2)  ? InfoPopover::getInstance()->createQuestion('l_warehouses_title_info_shortage') : '' ?>
            <?= ($warehouse['type'] == 4)  ? InfoPopover::getInstance()->createQuestion('l_warehouses_title_info_client') : '' ?>
        </div>
        <div class="warehouse-print">
            <?= $this->renderFile('warehouses/print_buttons', array(
                'objectId' => array_keys($warehouse['locations']),
                'prefix' => '_location'
            )) ?>
        </div>
    </div>
    <div class="warehouse-content">
        <table class="table table-borderless">
            <tbody>
            <tr>
                <td>
                    <?= l('Общий остаток') ?>:
                </td>
                <td>
                    <?= intval($warehouse['sum_qty']) ?> <?= l('шт.') ?>
                </td>
            </tr>
            <?php if ($this->all_configs['oRole']->hasPrivilege('logistics')): ?>
                <tr>
                    <td>
                        <?= l('Общая сумма') ?>
                    </td>
                    <td>
                        <?= $controller->show_price($warehouse['all_amount'], 2,
                            getCourse($this->all_configs['settings']['currency_suppliers_orders'])); ?>
                        <?= viewCurrency() ?>
                        <?php if (viewCurrency() != viewCurrencySuppliers()): ?>
                            (<?= $controller->show_price($warehouse['all_amount']) . viewCurrencySuppliers() ?>)
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
