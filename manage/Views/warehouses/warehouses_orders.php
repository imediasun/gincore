<ul class="list-unstyled inline clearfix">
    <?php if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders')): ?>
        <li>
            <a class="click_tab btn btn-info" onclick="click_tab(this, event)"
               data-open_tab="warehouses_orders_clients_bind"
               title="<?= l('Привязать серийный номер к заказу') ?>" href="#orders-clients_bind">
                <?= l('Привязать сер . номер') ?><span class="tab_count hide tc_warehouses_clients_orders_bind"></span>
            </a>
        </li>
        <li><a class="click_tab btn btn-primary" onclick="click_tab(this, event)"
               data-open_tab="warehouses_orders_clients_unbind"
             title="<?= l('Отвязать серийный номер от заказа') ?>" href="#orders-clients_unbind">
                <?= l('Отвязать сер . номер') ?><span class="tab_count hide tc_warehouses_clients_orders_unbind"></span>
            </a>
        </li>
    <?php endif; ?>
    <?php if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders')): ?>
        <li>
            <a class="click_tab  btn btn-warning" onclick="click_tab(this, event)"
               data-open_tab="warehouses_orders_suppliers"
            title="<?= l('Заказы поставщику которые ждут приходования') ?>" href="#orders-suppliers">
            <?= l('Заказы поставщику') ?><span class=" tab_count hide tc_debit_suppliers_orders"></span>
            </a>
        </li>
    <?php endif; ?>
    <li class="">
        <button data-toggle="filters" type="button" class="toggle-hidden btn btn-default">
            <i class="fa fa-filter"></i> <?= l('Фильтровать') ?> <i class="fa fa-caret-down"></i>
        </button>
    </li>
    <li class="pull-right">
        <button type="button" class="btn btn-success" onclick="return start_purchase_invoice(this);">
            <i class="fa fa-plus-circle" aria-hidden="true"></i>
            <?= l('Приходовать') ?>
        </button>
    </li>
</ul>
<div class="clearfix hidden theme_bg filters-box p-sm m-b-md" id="filters">
    <div id="orders-menu"></div>
</div>
<div class="pill-content">
    <?php if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders')): ?>
        <div id="orders-suppliers" class="pill-pane"> </div>

        <div id="orders-clients_bind" class="pill-pane"> </div>

        <div id="orders-clients_unbind" class="pill-pane"> </div>
    <?php endif; ?>
    <div id="orders-clients_issued" class="pill-pane"> </div>
    <div id="orders-clients_accept" class="pill-pane"> </div>
</div>
