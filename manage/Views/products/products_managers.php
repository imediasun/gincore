<?php if ($product): ?>
    <ul class="nav nav-pills">
        <li>
            <a class="click_tab" data-open_tab="products_managers_managers" onclick="click_tab(this, event)"
               title="<?= l('Уведомления') ?>" href="#managers-managers">
                <?= l('Менеджеры') ?>
            </a>
        </li>
        <li>
            <a class="click_tab" data-open_tab="products_managers_history" onclick="click_tab(this, event)"
               title="<?= l('Уведомления') ?>" href="#managers-history">
                <?= l('История изменений') ?>
            </a>
        </li>
    </ul>
    <div class="pill-content">

        <div id="managers-managers" class="pill-pane">
        </div>

        <div id="managers-history" class="pill-pane">
        </div>
    </div>
<?php endif; ?>
