<?php if ($this->all_configs["oRole"]->hasPrivilege("site-administration")
    || $this->all_configs['oRole']->hasPrivilege('accounting-reports-turnover')
    || $this->all_configs['oRole']->hasPrivilege('partner')
): ?>
    <ul class="nav nav-pills">
        <li>
            <a onclick="click_tab(this, event)" data-open_tab="accountings_reports_turnover" class="click_tab"
               href="#reports-turnover">
                <?= l('Оборот') ?>
            </a>
        </li>
        <?php if ($this->all_configs["oRole"]->hasPrivilege("site-administration")): ?>
            <li>
                <a onclick="click_tab(this, event)" data-open_tab="accountings_reports_net_profit" class="click_tab"
                   href="#reports-net_profit">
                    <?= l('Чистая прибыль') ?>
                </a>
            </li>
            <li>
                <a onclick="click_tab(this, event)" data-open_tab="accountings_reports_cost_of" class="click_tab"
                   href="#reports-cost_of">
                    <?= l('Стоимость компании') ?>
                </a>
            </li>
            <li>
                <a onclick="click_tab(this, event)" data-open_tab="accountings_reports_cash_flow" class="click_tab"
                   href="#reports-cash_flow">
                    <?= l('Денежный поток') ?>
                </a>
            </li>
            <li>
                <a onclick="click_tab(this, event)" data-open_tab="accountings_reports_annual_balance" class="click_tab"
                   href="#reports-annual_balance">
                    <?= l('Годовые балансы') ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <div class="pill-content">
        <div id="reports-turnover" class="pill-pane"></div>
        <?php if ($this->all_configs["oRole"]->hasPrivilege("site-administration")): ?>
            <div id="reports-cash_flow" class="pill-pane"></div>
            <div class="pill-content">
                <div id="reports-annual_balance" class="pill-pane"></div>
                <div id="reports-cost_of" class="pill-pane"></div>
                <div id="reports-net_profit" class="pill-pane"></div>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>
