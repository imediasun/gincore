<?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
    <ul class="nav nav-pills">
        <li>
            <a onclick="click_tab(this, event)" data-open_tab="accountings_pre_noncash" class="click_tab"
               href="#orders_pre-noncash" title="<?= l('Безнал') ?>">
                <?= l('Безнал') ?><span class="tab_count hide tc_accountings_noncash_orders_pre"></span>
            </a>
        </li>
        <li>
            <a onclick="click_tab(this, event)" data-open_tab="accountings_orders_pre_credit" class="click_tab"
               href="#orders_pre-credit" title="<?= l('Кредит') ?>">
                <?= l('Кредит') ?>
                <span class="tab_count hide tc_accountings_credit_orders_pre"></span>
            </a>
        </li>
    </ul>
    <div class="pill-content">
        <div id="orders_pre-noncash" class="pill-pane"></div>
        <div id="orders_pre-credit" class="pill-pane"></div>
    </div>
<?php endif; ?>
