<?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
    <ul class="list-unstyled inline clearfix">
        <li>
            <a class="click_tab btn btn-info" onclick="click_tab(this, event)"
               data-open_tab="accountings_orders_clients"
               href="#a_orders-clients" title="<?= l('Заказы клиентов') ?>">
                <?= l('Клиентов') ?><span class="tab_count hide tc_accountings_clients_orders"></span>
            </a>
        </li>
        <li>
            <a class="click_tab btn btn-warning" onclick="click_tab(this, event)"
               data-open_tab="accountings_orders_suppliers"
               href="#a_orders-suppliers" title="<?= l('Заказы поставщику') ?>">
                <?= l('Поставщику') ?><span class="tab_count hide tc_accountings_suppliers_orders"></span>
            </a>
        </li>
        <li class="">
            <button data-toggle="filters" type="button" class="toggle-hidden btn btn-default">
                <i class="fa fa-filter"></i>
                <?= l('Фильтровать') ?> <i class="fa fa-caret-down"></i>
            </button>
        </li>
    </ul>
    <div class="clearfix hidden theme_bg p-sm m-b-md" id="filters">
        <div id="a_orders-menu"></div>
    </div>
    <div class="pill-content">
        <div id="a_orders-suppliers" class="pill-pane">
        </div><!--#a_orders-suppliers-->

        <div id="a_orders-clients" class="pill-pane">

        </div><!--#a_orders-clients--></div><!--.pill-content-->
<?php endif; ?>
