<?php if ($this->all_configs['oRole']->hasPrivilege('site-administration')): ?>
    <ul class="nav nav-pills">
        <li>
            <a class="click_tab" data-open_tab="warehouses_settings_warehouses_groups" onclick="click_tab(this, event)"
               href="#settings-warehouses_groups" title="<?= l('Создать') ?>/<?= l('редактировать группу склада') ?>">
                <?= l('Сервисные центры') ?>
            </a>
        </li>
        <li>
            <a class="click_tab" id="add_warehouses" data-open_tab="warehouses_settings_warehouses"
               onclick="click_tab(this, event)" href="#settings-warehouses" title="Создать/редактировать склад">
                <?= l('Склады') ?>
            </a>
        </li>
        <li>
            <a class="click_tab" data-open_tab="warehouses_settings_warehouses_types" onclick="click_tab(this, event)"
               href="#settings-warehouses_types" title="<?= l('Создать') ?>/<?= l('редактировать категорию склада') ?>">
                <?= l('Категории') ?>
            </a>
        </li>
        <li>
            <a class="click_tab" data-open_tab="warehouses_settings_warehouses_users" onclick="click_tab(this, event)"
               href="#settings-warehouses_users" title="<?= l('Закрепить администратора за кассой') ?>">
                <?= l('Администраторы') ?>
            </a>
        </li>
    </ul>
    <div class="pill-content">
        <div id="settings-warehouses" class="pill-pane"></div>
        <div id="settings-warehouses_users" class="pill-pane"></div>
        <div id="settings-warehouses_groups" class="pill-pane"></div>
        <div id="settings-warehouses_types" class="pill-pane"></div>
    </div>
<?php endif; ?>

