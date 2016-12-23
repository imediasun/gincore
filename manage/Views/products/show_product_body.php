<?php if (is_array($errors) && array_key_exists('error', $errors)): ?>
    <div class="alert alert-error fade in">
        <button class="close" data-dismiss="alert" type="button">×</button>
        <?= $errors['error'] ?>
    </div>
<?php endif; ?>
<?php if (isset($_GET['error']) && ($_GET['error'] == 'manager')): ?>
    <div class="alert alert-danger fade in">
        <button class="close" data-dismiss="alert" type="button">×</button>
        <?= l('Закрепите менеджера за товаром или привяжите контрагента к клиенту') ?>
    </div>
<?php endif; ?>

<div class="tabbable">
    <ul class="nav nav-tabs">
        <li>
            <a class="click_tab default" data-open_tab="products_main" onclick="click_tab(this, event)"
               data-toggle="tab" href="#main">
                <?= l('Основные') ?>
            </a>
        </li>
        <li>
            <a class="click_tab" data-open_tab="products_additionally" onclick="click_tab(this, event)"
               data-toggle="tab" href="#additionally">
                <?= l('Дополнительно') ?>
            </a>
        </li>
        <li>
            <a class="click_tab" data-open_tab="products_managers" onclick="click_tab(this, event)" data-toggle="tab"
               href="#managers">
                <?= l('Менеджеры') ?>
            </a>
        </li>
        <li>
            <a class="click_tab" data-open_tab="products_financestock" onclick="click_tab(this, event)"
               data-toggle="tab" href="#financestock">
                Finance/Stock
            </a>
        </li>
        <?php if ($this->all_configs['oRole']->hasPrivilege('external-marketing')): ?>
            <li>
                <a class="click_tab" data-open_tab="products_omt" onclick="click_tab(this, event)" data-toggle="tab"
                   href="#omt" title="Outside marketing tools">
                    OMT
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <div class="tab-content">
        <div class="tab-pane" id="main"></div>
        <div class="tab-pane" id="additionally"></div>
        <div class="tab-pane" id="managers"></div>
        <div class="tab-pane" id="financestock"></div>

        <?php if ($this->all_configs['oRole']->hasPrivilege('external-marketing')): ?>
            <div class="tab-pane" id="omt"></div>
        <?php endif; ?>
    </div>
</div>
