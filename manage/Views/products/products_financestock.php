<ul class="nav nav-pills">';
    <li>
        <a class="click_tab" data-open_tab="products_financestock_stock" onclick="click_tab(this, event)"
           title="<?= l('Склады') ?>" href="#financestock-stock">
            <?= l('Склады') ?>
        </a>
    </li>
    <li>
        <a class="click_tab" data-open_tab="products_financestock_finance" onclick="click_tab(this, event)"
           title="<?= l('Заказы поставщикам') ?>" href="#financestock-finance">
            <?= l('Заказы поставщикам') ?>
        </a>
    </li>
</ul>
<div class="pill-content">
    <div id="financestock-main" class="pill-pane"></div>
    <div id="financestock-stock" class="pill-pane"></div>
    <div id="financestock-finance" class="pill-pane"></div>
</div>
