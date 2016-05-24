<div class="tabbable">
    <ul class="nav nav-tabs">
        <?php if ($this->all_configs["oRole"]->hasPrivilege("debit-suppliers-orders") ||
            $this->all_configs["oRole"]->hasPrivilege("logistics")
        ): ?>
            <li><a class="click_tab default" data-open_tab="warehouses_warehouses" onclick="click_tab(this, event)"
                   data-toggle="tab" href="<?= $mod_submenu[0]['url'] ?>"><?= $mod_submenu[0]['name'] ?></a>
            </li>
        <?php endif; ?>
        <?php if ($this->all_configs["oRole"]->hasPrivilege("scanner-moves")): ?>
            <li><a class="click_tab default" data-open_tab="warehouses_scanner_moves" onclick="click_tab(this, event)"
                   data-toggle="tab" href="<?= $mod_submenu[1]['url'] ?>"><?= $mod_submenu[1]['name'] ?></a>
            </li>
        <?php endif; ?>
        <?php if ($this->all_configs["oRole"]->hasPrivilege("debit-suppliers-orders") ||
            $this->all_configs["oRole"]->hasPrivilege("logistics")
        ): ?>
            <li><a class="click_tab" data-open_tab="warehouses_show_items" onclick="click_tab(this, event)"
                   data-toggle="tab" href="<?= $mod_submenu[2]['url'] ?>"><?= $mod_submenu[2]['name'] ?></a>
            </li>
        <?php endif; ?>
        <?php if ($this->all_configs["oRole"]->hasPrivilege("debit-suppliers-orders") ||
            $this->all_configs["oRole"]->hasPrivilege("logistics")
        ): ?>
            <li><a class="click_tab" data-open_tab="warehouses_orders" onclick="click_tab(this, event)"
                   data-toggle="tab"
                   href="<?= $mod_submenu[3]['url'] ?>"><?= $mod_submenu[3]['name'] ?><span
                        class="tab_count hide tc_sum_warehouses_orders"></span></a></li>
        <?php endif; ?>
        <?php if ($this->all_configs["oRole"]->hasPrivilege("site-administration")): ?>
            <li><a class="click_tab" data-open_tab="warehouses_settings" onclick="click_tab(this, event)"
                   data-toggle="tab"
                   href="<?= $mod_submenu[4]['url'] ?>"><?= $mod_submenu[4]['name'] ?></a></li>
        <?php endif; ?>
    </ul>
    <div class="tab-content">

        <?php if ($this->all_configs['oRole']->hasPrivilege('scanner-moves')): ?>
            <div id="scanner_moves" class="tab-pane">
            </div>
        <?php endif; ?>
        <?php if ($this->all_configs["oRole"]->hasPrivilege("debit-suppliers-orders") ||
            $this->all_configs["oRole"]->hasPrivilege("logistics")
        ): ?>
            <div id="warehouses" class="tab-pane clearfix">
            </div>
        <?php endif; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders') ||
            $this->all_configs['oRole']->hasPrivilege('logistics')
        ): ?>
            <div id="orders" class="tab-pane clearfix">
            </div>
        <?php endif; ?>

        <?php if ($this->all_configs["oRole"]->hasPrivilege("debit-suppliers-orders") ||
            $this->all_configs["oRole"]->hasPrivilege("logistics")
        ): ?>
            <div id="show_items" class="tab-pane">
            </div>
        <?php endif; ?>

        <?php if ($this->all_configs['oRole']->hasPrivilege('site-administration')): ?>
            <div id="settings" class="tab-pane">
            </div>
        <?php endif; ?>


        <div id="inventories" class="tab-pane">
        </div>


    </div>
</div>

<?= $this->all_configs['suppliers_orders']->append_js(); ?>
<?= $this->all_configs['chains']->append_js(); ?>

