<h3><?= l('Редактирование заказа поставщику') ?> №<?= $order_id ?></h3>
<br>
<div class="row row-15">
    <div class="col-sm-<?= ($is_modal ? '12' : '6') ?>">
        <form data-validate="parsley" id="suppliers-order-form" method="post">
            <?php if (empty($suppliers)): ?>
                <p class="text-danger"><?= l('Нет поставщиков') ?></p>
            <?php else: ?>
                <?php if ($order['sum_paid'] == 0 && $order['count_debit'] != $order['count_come']): ?>
                    <div class="form-group">
                        <label><?= l('Склад') ?>
                            : <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_wh_info') ?></label>
                        <select name="warehouse" <?= $disabled ?> onchange="change_warehouse(this)"
                                class="select-warehouses-item-move form-control">
                            <option value=""></option>
                            <?php if ($warehouses): ?>
                                <?php foreach ($warehouses as $warehouse): ?>
                                    <option <?= ($warehouse['id'] == $order['wh_id']) ? 'selected' : '' ?>
                                        value="<?= $warehouse['id'] ?>"><?= $warehouse['title'] ?> </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><?= l('Локация') ?>:</label>
                        <select
                            class="form-control select-location" name="location">
                            <?= $controller->gen_locations($order['wh_id'], $order['location_id']); ?>
                        </select>
                    </div>
                <?php endif; ?>
                <input type="hidden" name="order_id" value="<?= $order_id ?>"/>
                <?php if ($all): ?>
                    <div class="form-group relative">
                        <label><?= l('Поставщик') ?><b class="text-danger">*</b>: </label>
                        <div class="input-group">
                            <select class="form-control" data-required="true"
                                    name="warehouse-supplier" <?= $disabled ?>>
                                <option value=""></option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option <?= ($order['supplier'] == $supplier['id']) ? 'selected' : '' ?>
                                        value="<?= $supplier['id'] ?>"><?= $supplier['title'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-btn">
                                <button type="button" data-form_id="new_supplier_form"
                                        data-action="accountings/ajax?act=create-contractor-form-no-modal"
                                        class="typeahead_add_form btn btn-info" data-id="supplier_creator">
                                    <?= l('Добавить') ?>
                                </button>
                            </div>
                        </div>
                        <?php if ($is_modal): ?>
                            <div id="new_supplier_form"
                                 class="typeahead_add_form_box theme_bg new_supplier_form p-md"></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label><?= l('Дата поставки') ?><b class="text-danger">*</b>:
                            <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_date_info') ?>
                        </label>
                        <input class="datetimepicker form-control" <?= $disabled ?> data-format="yyyy-MM-dd" type="text"
                               name="warehouse-order-date" data-required="true"
                               value="<?= ($order['date_wait'] ? date('Y-m-d',
                                   strtotime($order['date_wait'])) : '') ?>"/>
                    </div>
                <?php endif; ?>


                <?php if ($goods): ?>
                    <div
                        class="form-group relative" <?= ($has_orders ? 'onclick="alert(\'' . l('Данный заказ поставщику создан на основании заказа клиенту. Вы не можете изменить запчасть в данном заказе.') . '\');return false;"' : '') ?>>
                        <label><?= l('Запчасть') ?><b class="text-danger">*</b>: </label>
                        <?= typeahead($this->all_configs['db'], 'goods-goods', true, $order['goods_id'],
                            (15 + $typeahead), 'input-xlarge', 'input-medium', '', false, false, '', false,
                            l('Введите'),
                            array(
                                'name' => l('Добавить'),
                                'action' => 'products/ajax/?act=create_form',
                                'form_id' => 'new_device_form'
                            ), $has_orders) ?>
                        <?php if ($is_modal): ?>
                            <div id="new_device_form"
                                 class="typeahead_add_form_box theme_bg new_device_form p-md"></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <label for="warehouse_type">
                        <?= l('Тип поставки') ?> <b class="text-danger">*</b>:
                        <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_type_info') ?></label>
                    <div class="radio">
                        <label>
                            <input data-required="true" type="radio" name="warehouse_type"
                                   value="1" <?= ($order['warehouse_type'] == 1 ? 'checked' : '') ?> />
                            <?= l('Локально') ?>
                        </label>
                    </div>
                    <div class="radio">
                        <label>
                            <input type="radio" name="warehouse_type" data-required="true" value="2"
                                <?= ($order['warehouse_type'] == 2 ? 'checked' : '') ?> />
                            <?= l('Заграница') ?>
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <label>
                        <?= l('Номер') ?>:
                        <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_number_info') ?>
                    </label>
                    <input type="text" <?= $disabled ?> name="warehouse-order-num" class="form-control"
                           value="<?= $order['num'] ?>"/>
                </div>
                <div class="form-group">
                    <label>
                        <?= l('Количество') ?><b class="text-danger">*</b>: </label>
                    <input type="text" <?= $disabled ?> data-required="true" onkeydown="return isNumberKey(event)"
                           name="warehouse-order-count" class="form-control"
                           value="<?= h($order['count']) ?>"/>
                </div>
                <div class="form-group">
                    <label>
                        <?= l('Цена за один') ?> (<?= viewCurrencySuppliers('shortName') ?>)<b class="text-danger">*</b>:
                    </label>
                    <input type="text" <?= $disabled ?> data-required="true" onkeydown="return isNumberKey(event,
                    this)" name="warehouse-order-price" class="form-control"
                           value="<?= h($order['price']) ?>"/>
                </div>
                <div class="form-group">
                    <label><?= l('Примечание') ?>: </label>
                    <textarea <?= $disabled ?> name="comment-supplier"
                                               class="form-control"><?= h($order['comment']) ?></textarea>
                </div>
                <div class="form-group">
                    <label><?= l('номер ремонта') ?></label>
                    (<?= l('если запчасть заказывается под конкретный ремонт') ?>):
                    <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_order_info') ?>
                    <?= $so_co ?>
                </div>
                <div id="for-new-supplier-order"></div>
                <?php if ($all): ?>
                    <div class="form-group"><?= $order['btn'] ?></div>
                <?php endif; ?>
            <?php endif; ?>
        </form>
    </div>
    <div class="col-sm-6 relative">
        <?php if (!$is_modal): ?>
            <div id="new_supplier_form" class="typeahead_add_form_box theme_bg new_supplier_form p-md"></div>
            <div id="new_device_form" class="typeahead_add_form_box theme_bg new_device_form p-md"></div>
        <?php endif; ?>
    </div>
</div>
<?= $info_html ?>
