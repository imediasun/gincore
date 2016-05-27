<style>
    .warehouse-title {
        width: 100%;
        height: 26px;
        background-color: #F7F9FA;
        margin-bottom: 20px;
        border-bottom: 1px solid #ddd;
    }

    .warehouse-title > div {
        float: left;
        line-height: 26px;
    }

    .warehouse-title .warehouse-filter {
        width: 30px;
        padding: 0 5px;
    }

    .warehouse-title .warehouse-print {
        float: right;
        width: 30px;
        padding: 0 5px;
        border-left: 1px solid #ddd;
        text-align: center;
    }

    .warehouse-title > .title {
        padding: 0 10px;
        text-align: center;
        width: 210px;
    }

    .show_warehouse {
        float: left;
        height: 130px;
        margin: 0 10px 10px 0;
        width: 273px;
        border: 1px solid #ddd;
        border-radius: 5px;
    }

    .show_warehouse > .warehouse-content {
        text-align: center;
        line-height: 26px;
    }


</style>
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
            <?= (!$i ? InfoPopover::getInstance()->createOnLoad('l_warehouses_title_info') : '') ?>
        </div>
        <div class="warehouse-print">
            <?= print_link(array_keys($warehouse['locations']), 'location'); ?>
        </div>
    </div>
    <div class="warehouse-content"><?= l('Общий остаток') ?>: <?= intval($warehouse['sum_qty']) ?> <?= l('шт.') ?></div>
    <?php if ($this->all_configs['oRole']->hasPrivilege('logistics')): ?>
        <div class="warehouse-content">
            <?= l('Общая сумма') ?>:
            <?= $controller->show_price($warehouse['all_amount'], 2,
                getCourse($this->all_configs['settings']['currency_suppliers_orders'])); ?>
            <?= viewCurrency() ?>
            <?php if (viewCurrency() != viewCurrencySuppliers()): ?>
                (<?= $controller->show_price($warehouse['all_amount']) . viewCurrencySuppliers() ?>)
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
