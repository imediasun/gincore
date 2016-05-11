<div class="relative">
    <label class="lh30">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-cart')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
        <?= l('Корзина') ?>:
    </label>
    <table class="<?= !$goods ? 'hidden ' : '' ?> table parts-table">
        <thead>
        <tr>
            <th><?= l('Наименование') ?></th>
            <th><?= l('Цена') ?>(<?= viewCurrency() ?>)</th>
            <th><?= l('Скидка') ?></th>
<!--            <th>--><?//= l('Количество') ?><!--</th>-->
            <th><?= l('Сумма') ?></th>
            <th class="<?= $prefix == 'quick' ? 'col-sm-3' : '' ?>"><?= l('Гарантия') ?></th>
            <th></th>
            <th></th>
        </tr>
        </thead>
        <tbody id="goods-table">
        <?php if ($goods): ?>
            <?php foreach ($goods as $product): ?>
                <?= $controller->show_eshop_product($product); ?>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>
    <div class="form-group"><label><?= l('Выберите товар') ?></label>
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
</div>
