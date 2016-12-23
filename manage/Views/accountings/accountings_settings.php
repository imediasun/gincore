<?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
    <ul class="nav nav-pills">
        <?php if ($this->all_configs['oRole']->hasPrivilege('site-administration')): ?>
            <li>
                <a class="click_tab" onclick="click_tab(this, event)" data-open_tab="accountings_settings_cashboxes"
                   href="#settings-cashboxes" title="<?= l('Создать/редактировать кассу') ?>">
                    <?= l('Кассы') ?>
                </a>
            </li>
            <li>
                <a class="click_tab" onclick="click_tab(this, event)" data-open_tab="accountings_settings_currencies"
                   href="#settings-currencies" title="<?= l('Валюты') ?>">
                    <?= l('Валюты') ?>
                </a>
            </li>
        <?php endif; ?>
        <li>
            <a class="click_tab" onclick="click_tab(this, event)"
               data-open_tab="accountings_settings_categories_expense"
               href="#settings-categories_expense" title="<?= l('Создать/редактировать статью расход') ?>">
                <?= l('Статьи расходов') ?>
            </a>
        </li>
        <li>
            <a class="click_tab" onclick="click_tab(this, event)" data-open_tab="accountings_settings_categories_income"
               href="#settings-categories_income" title="<?= l('Создать/редактировать статью приход') ?>">
                <?= l('Статьи поступлений') ?>
            </a>
        </li>
        <li>
            <a class="click_tab" onclick="click_tab(this, event)" data-open_tab="accountings_settings_contractors"
               href="#settings-contractors" title="<?= l('Создание/редактирование контрагентов') ?>">
                <?= l('Контрагенты') ?>
            </a>
        </li>
    </ul>
    <div class="pill-content">
        <?php if ($this->all_configs['oRole']->hasPrivilege('site-administration')): ?>
            <div id="settings-cashboxes" class="pill-pane"></div>
            <div id="settings-currencies" class="pill-pane"></div>
        <?php endif; ?>

        <?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
            <div id="settings-categories_expense" class="pill-pane"></div>
            <div id="settings-categories_income" class="pill-pane"></div>
            <div id="settings-contractors" class="pill-pane"></div>
        <?php endif; ?>
    </div>
<?php endif; ?>
