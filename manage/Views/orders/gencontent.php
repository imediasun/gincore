<div class="tabbable">
    <ul class="nav nav-tabs">
        <?php if ($this->all_configs['oRole']->hasPrivilege('show-clients-orders')): ?>
            <li>
                <a class="click_tab default" data-open_tab="orders_show_orders" onclick="click_tab(this, event)"
                   data-toggle="tab" href="<?= $mod_submenu[0]['url'] ?>">
                    <?= $mod_submenu[0]['name'] ?><span class="tab_count hide tc_clients_orders"></span>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('create-clients-orders')): ?>
            <li>
                <a class="click_tab" data-open_tab="orders_create_order" onclick="click_tab(this, event)"
                   data-toggle="tab" href="<?= $mod_submenu[1]['url'] ?>">
                    <?= $mod_submenu[1]['name'] ?>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')): ?>
            <li>
                <a class="click_tab" data-open_tab="orders_show_suppliers_orders" onclick="click_tab(this, event)"
                   data-toggle="tab" href="<?= $mod_submenu[2]['url'] ?>">
                    <?= $mod_submenu[2]['name'] ?><span class="tab_count hide tc_suppliers_orders"></span>
                </a>
            </li>
            <li>
                <a class="click_tab" data-open_tab="orders_create_supplier_order" onclick="click_tab(this, event)"
                   data-toggle="tab" href="<?= $mod_submenu[3]['url'] ?>">
                    <?= $mod_submenu[3]['name'] ?>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('orders-manager')): ?>
            <li>
                <a class="click_tab default" data-open_tab="orders_manager" onclick="click_tab(this, event)"
                   data-toggle="tab" href="<?= $mod_submenu[4]['url'] ?>">
                    <?= $mod_submenu[4]['name'] ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <div class="tab-content">
        <?php if ($this->all_configs['oRole']->hasPrivilege('show-clients-orders')): ?>
            <div id="show_orders" class="tab-pane clearfix"></div>
        <?php endif; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('create-clients-orders')): ?>
            <div id="create_order" class="tab-pane clearfix"></div>
        <?php endif; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('orders-manager')): ?>
            <div id="orders_manager" class="tab-pane clearfix"></div>
        <?php endif; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders')): ?>
            <div id="show_suppliers_orders" class="tab-pane clearfix"></div>
        <?php endif; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('orders-manager')): ?>
            <div id="create_supplier_order" class="tab-pane clearfix"></div>
        <?php endif; ?>
    </div>
</div>
<?= $this->all_configs['suppliers_orders']->append_js(); ?>
