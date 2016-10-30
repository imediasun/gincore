<div class="relative well well-small parts-well">
    <h4><?= l('Запчасти') ?></h4>
    <table class="<?= !$goods ? 'hidden ' : '' ?> table parts-table">
        <thead>
        <tr>
            <td><?= l('Наименование') ?></td>
            <?php if ($hasEditorPrivilege): ?>
                <td>
                    <div class="dropdown dropdown-inline">
                        <button class="as_link" type="button" id="dropdownMenuCashboxes"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"
                                style="background-color: #F7F9FA">
                            <span class="btn-title-price_type">
                                <?= $price_type == ORDERS_GOODS_PRICE_TYPE_WHOLESALE ? l('Цена, о') : l('Цена, р') ?>
                            </span>
                            <span class="caret"></span>
                            <input type="hidden" name="price_type" value="<?= $price_type ?>"/>
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuCashboxes">
                            <li><a href="#" data-price_type="<?= ORDERS_GOODS_PRICE_TYPE_RETAIL ?>"
                                   onclick="return change_price_type_of_goods(this)"
                                   data-title="<?= l('Цена, р') ?>"><?= l('Цена розничная') ?></a>
                            </li>
                            <li><a href="#" data-price_type="<?= ORDERS_GOODS_PRICE_TYPE_WHOLESALE ?>"
                                   onclick="return change_price_type_of_goods(this)"
                                   data-title="<?= l('Цена, о') ?>"><?= l('Цена оптовая') ?></a>
                            </li>
                        </ul>
                    </div>
                </td>
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
        <table class="<?= (!$services ? 'hidden ' : '') ?> table parts-table">
            <thead>
            <tr>
                <td><?= l('Наименование') ?></td>
                <?php if ($hasEditorPrivilege): ?>
                    <td>
                        <div class="dropdown dropdown-inline">
                            <button class="as_link" type="button" id="dropdownMenuCashboxes"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"
                                    style="background-color: #F7F9FA">
                            <span class="btn-title-price_type_of_service">
                                <?= $price_type_of_service == ORDERS_GOODS_PRICE_TYPE_WHOLESALE ? l('Цена, о') : l('Цена, р') ?>
                            </span>
                                <span class="caret"></span>
                                <input type="hidden" name="price_type_of_service"
                                       value="<?= $price_type_of_service ?>"/>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuCashboxes">
                                <li><a href="#" data-price_type="<?= ORDERS_GOODS_PRICE_TYPE_RETAIL ?>"
                                       onclick="return change_price_type_of_service(this)"
                                       data-title="<?= l('Цена, р') ?>"><?= l('Цена розничная') ?></a>
                                </li>
                                <li><a href="#" data-price_type="<?= ORDERS_GOODS_PRICE_TYPE_WHOLESALE ?>"
                                       onclick="return change_price_type_of_service(this)"
                                       data-title="<?= l('Цена, о') ?>"><?= l('Цена оптовая') ?></a>
                                </li>
                            </ul>
                        </div>
                    </td>
                <?php endif; ?>
                <td></td>
                <td></td>
                <td style="text-align: center;"> <?= l('Мастер') ?> <?= InfoPopover::getInstance()->createQuestion('l_engineer_of_service'); ?> </td>
            </tr>
            </thead>
            <tbody id="service-table">
            <?php if ($services): ?>
                <?php foreach ($services as $service): ?>
                    <?= $controller->show_product($service, $engineers, $order['engineer']); ?>
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
        <hr/>
        <div class="checkbox text-left">
            <span style="display: inline-block"><?= l('Итого') ?>:</span>
            <span class='total-sum' style="display: inline-block;"> <?= (int)$total / 100 ?></span>
            <span style="display: inline-block; margin-right: 20px"><?= viewCurrency() ?></span>
            <label class="tooltips" data-toggle="tooltip" data-placement="bottom"
                   title="<?= l('Автоматически дублировать Итого в стоимость ремонта') ?>">
                <input class='total-sum' type="hidden" value="<?= (int)$total / 100 ?>"/>
                <input id='total-sum-checkbox' type="checkbox" <?= ($totalChecked) ? 'checked' : '' ?>
                       onclick="set_total_as_sum(this, <?= $orderId ?>);"/>
                <?= l('"Итого" = "стоимость ремонта"') ?>
            </label>
        </div>
    <?php endif; ?>
</div>
