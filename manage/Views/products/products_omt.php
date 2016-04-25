<ul class="nav nav-pills">
    <li>
        <a class="click_tab" data-open_tab="products_omt_notices" onclick="click_tab(this, event)" href="#omt-notices"
           title="<?= l('Уведомления') ?>">
            <?= l('Уведомления') ?>
        </a>
    </li>
    <li>
        <a class="click_tab" data-open_tab="products_omt_procurement" onclick="click_tab(this, event)"
           href="#omt-procurement" title="<?= l('Управление закупками') ?>">
            <?= l('Упр. закупками') ?>
        </a>
    </li>
</ul>
<div class="pill-content">
    <div id="omt-notices" class="pill-pane"></div>
    <div id="omt-procurement" class="pill-pane"></div>
</div>
