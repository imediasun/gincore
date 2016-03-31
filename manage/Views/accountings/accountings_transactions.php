<?php if ($this->all_configs['oRole']->hasPrivilege('accounting') ||
    $this->all_configs['oRole']->hasPrivilege('accounting-transactions-contractors')
): ?>
    <ul class="nav nav-pills">';
        <?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
            <li>
                <a class="click_tab" data-open_tab="accountings_transactions_cashboxes" onclick="click_tab(this, event)"
                   href="#transactions-cashboxes" title="<?= l('Транзакции касс') ?>">
                    <?= l('Касс') ?>
                </a>
            </li>
        <?php endif; ?>
        <li>
            <a class="click_tab" data-open_tab="accountings_transactions_contractors" onclick="click_tab(this, event)"
               href="#transactions-contractors" title="<?= l('Транзакции контрагентов') ?>">
                <?= l('Контрагентов') ?>
            </a>
        </li>
    </ul>
    <div class="pill-content">
        <?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
            <div id="transactions-cashboxes" class="pill-pane"></div>
        <?php endif; ?>
        <div id="transactions-contractors" class="pill-pane"></div>
    </div>
<?php endif; ?>
