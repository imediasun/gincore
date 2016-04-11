<div class="tabbable">
    <ul class="nav nav-tabs">
        <?php if ($isCashier): ?>
            <li>
                <a class="click_tab default" data-open_tab="accountings_cashboxes" onclick="click_tab(this, event)"
                   data-toggle="tab" href="<?= $mod_submenu[0]['url'] ?>">
                    <?= $mod_submenu[0]['name'] ?>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($isCashier ||
            $this->all_configs['oRole']->hasPrivilege('accounting-transactions-contractors')
        ): ?>
            <li>
                <a class="click_tab default" data-open_tab="accountings_transactions" onclick="click_tab(this, event)"
                   data-toggle="tab" href="<?= $mod_submenu[1]['url'] ?>">
                    <?= $mod_submenu[1]['name'] ?>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($this->all_configs["oRole"]->hasPrivilege("site-administration")
            || $this->all_configs['oRole']->hasPrivilege('accounting-reports-turnover')
            || $this->all_configs['oRole']->hasPrivilege('partner')
        ): ?>
            <li>
                <a class="click_tab default" data-open_tab="accountings_reports" onclick="click_tab(this, event)"
                   data-toggle="tab" href="<?= $mod_submenu[2]['url'] ?>">
                    <?= $mod_submenu[2]['name'] ?>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
            <li>
                <a class="click_tab" data-open_tab="accountings_orders" onclick="click_tab(this, event)"
                   data-toggle="tab" href="<?= $mod_submenu[3]['url'] ?>">
                    <?= $mod_submenu[3]['name'] ?><span class="tab_count hide tc_sum_accountings_orders"></span>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('accounting') ||
            $this->all_configs['oRole']->hasPrivilege('accounting-contractors')
        ): ?>
            <li>
                <a class="click_tab default" data-open_tab="accountings_contractors" onclick="click_tab(this, event)"
                   data-toggle="tab" href="<?= $mod_submenu[4]['url'] ?>">
                    <?= $mod_submenu[4]['name'] ?>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
            <li>
                <a class="click_tab" data-open_tab="accountings_settings" onclick="click_tab(this, event)"
                   data-toggle="tab" href="<?= $mod_submenu[5]['url'] ?>">
                    <?= $mod_submenu[5]['name'] ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <div class="tab-content">
        <?php if ($isCashier): ?>
            <div id="cashboxes" class="content_tab tab-pane clearfix"></div>
        <?php endif; ?>

        <?php if ($isCashier ||
            $this->all_configs['oRole']->hasPrivilege('accounting-transactions-contractors')
        ): ?>
            <div id="transactions" class="content_tab tab-pane clearfix"></div>
        <?php endif; ?>
        <?php if ($this->all_configs["oRole"]->hasPrivilege("site-administration")
            || $this->all_configs['oRole']->hasPrivilege('accounting-reports-turnover')
            || $this->all_configs['oRole']->hasPrivilege('partner')
        ): ?>
            <div id="reports" class="content_tab tab-pane clearfix"></div>
        <?php endif; ?>

        <?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
            <div id="orders_pre" class="content_tab tab-pane clearfix"></div>
            <div id="a_orders" class="content_tab tab-pane clearfix"></div>
        <?php endif; ?>

        <?php if ($this->all_configs['oRole']->hasPrivilege('accounting') ||
            $this->all_configs['oRole']->hasPrivilege('accounting-contractors')
        ): ?>
            <div id="contractors" class="content_tab tab-pane clearfix"></div>
        <?php endif; ?>

        <?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
            <div id="settings" class="content_tab tab-pane clearfix"></div>
        <?php endif; ?>
    </div>
</div>
