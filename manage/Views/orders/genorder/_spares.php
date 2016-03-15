<div class="relative well well-small parts-well">
    <h4><?= l('Запчасти') ?></h4>
    <table class="<?= !$goods ? 'hidden ' : '' ?> table parts-table">
        <thead>
        <tr>
            <td><?= l('Наименование') ?></td>
            <?php if ($hasEditorPrivilege): ?>
                <td><?= l('Цена') ?>(<?= viewCurrency() ?>)</td>
            <?php endif; ?>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        </thead>
        <tbody id="goods-table">
        <?php if ($goods): ?>
            <?php foreach ($goods as $product): ?>
                <?= $controller->show_product($product); ?>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <?php if ($notSale): ?>
        <?php if (!$onlyEngineer): ?>
            <div class="form-group"><label><?= l('Выберите запчасть') ?></label>
                <?= typeahead($this->all_configs['db'], 'goods-goods', false, 0, 6,
                    'input-medium popover-info', '', 'order_products',
                    false, false, '', false, l('Введите'),
                    array(
                        'name' => l('Добавить новую'),
                        'action' => 'products/ajax/?act=create_form',
                        'form_id' => 'order_new_device_form'
                    )); ?>
            </div>
            <div id="order_new_device_form" class="typeahead_add_form_box theme_bg order_new_device_form"></div>
        <?php endif; ?>
        <hr/>
        <h4><?= l('Работы') ?></h4>
        <table class="<?= (!$services ? 'hidden ' : '')?> table parts-table">
            <thead>
            <tr>
                <td><?= l('Наименование') ?></td>
                <?php if ($hasEditorPrivilege): ?>
                    <td><?= l('Цена') ?>(<?= viewCurrency() ?>)</td>
                <?php endif; ?>
                <td></td>
                <td></td>
            </tr>
            </thead>
            <tbody id="service-table">
            <?php if ($services): ?>
                <?php foreach ($services as $service): ?>
                    <?= $controller->show_product($service); ?>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
        <div class="form-group">
            <label><?= l('Укажите работу') ?></label>
            <?= typeahead($this->all_configs['db'], 'goods-service', false, 0, 7,
                'input-medium popover-info', '', 'order_products',
                false, false, '', false, l('Введите'),
                array(
                    'name' => l('Добавить новую'),
                    'action' => 'products/ajax/?act=create_form&service=1',
                    'form_id' => 'order_new_work_form'
                )) ?>
        </div>
        <div id="order_new_work_form" class="typeahead_add_form_box theme_bg order_new_work_form"></div>
    <?php endif; ?>
</div>
