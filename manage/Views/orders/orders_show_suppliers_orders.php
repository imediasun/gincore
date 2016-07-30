<div>
    <ul class="list-unstyled inline clearfix">
        <li class="">
            <a class="click_tab btn btn-info" href="#show_suppliers_orders-all" title=""
               onclick="click_tab(this, event)" data-open_tab="orders_show_suppliers_orders_all">
                <i class="fa fa-bolt"></i> <?= l('Все заказы') ?>
                <span class="tab_count hide tc_suppliers_orders_all"></span>
            </a>
        </li>
        <li class="">
            <a <?= InfoPopover::getInstance()->createOnHoverAttr('l_suppliers_orders_wait_info') ?>
                class="click_tab btn btn-danger" href="#show_suppliers_orders-wait" title=""
                onclick="click_tab(this, event)" data-open_tab="orders_show_suppliers_orders_wait">
                <i class="fa fa-clock-o"></i> <?= l('Ожидают проверки') ?>
            </a>
        </li>
        <li class="">
            <a class="click_tab btn btn-warning" href="#show_suppliers_orders-return" title=""
               onclick="click_tab(this, event)" data-open_tab="show_orders_return">
                <i class="fa fa-exchange"></i> <?= l('Возвраты поставщикам') ?>
            </a>
        </li>
        <li class="">
            <a class="click_tab btn btn-primary" href="#show_suppliers_orders-procurement" title=""
               onclick="click_tab(this, event)"
               data-open_tab="orders_recommendations_procurement">
                <?= l('Рекомендации по закупкам') ?>
            </a>
        </li>
        <li class="">
            <button data-toggle="filters" type="button" class="toggle-hidden btn btn-default">
                <i class="fa fa-filter"></i> <?= l('Фильтровать') ?> <i class="fa fa-caret-down"></i>
            </button>
        </li>
        <li class="pull-right">
            <a href="<?= $this->all_configs['prefix'] ?>orders/#create_supplier_order"
               class="btn btn-success hash_link"><?= l('Создать заказ') ?></a>
        </li>
    </ul>
    <div class="hidden" id="filters">
        <div id="show_suppliers_orders-menu"></div>
    </div>
    <div class="pill-content">
        <div id="show_suppliers_orders-all" class="pill-pane"></div>
        <div id="show_suppliers_orders-wait" class="pill-pane"></div>
        <div id="show_suppliers_orders-return" class="pill-pane"></div>
        <div id="show_suppliers_orders-procurement" class="pill-pane"></div>
    </div>
</div>
