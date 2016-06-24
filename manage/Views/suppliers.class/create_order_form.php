<h3><?= l('Создание нового заказа поставщику') ?></h3>
<br>
<div class="row row-15">
    <div class="col-sm-<?= ($is_modal ? '12' : '6') ?>">
        <form data-validate="parsley" id="suppliers-order-form" method="post">
            <?php if (empty($suppliers)): ?>
                <p class="text-danger"><?= l('Нет поставщиков') ?></p>
            <?php else: ?>
                <?php if ($order_id): ?>
                    <input type="hidden" name="order_id" value="<?= $order_id ?>"/>
                <?php endif; ?>


                <?php if ($all): ?>
                    <div class="form-group relative">
                        <label><?= l('Поставщик') ?><b class="text-danger">*</b>: </label>
                        <div class="input-group">
                            <select class="form-control" data-required="true" name="warehouse-supplier">
                                <option value=""></option>
                                <?php foreach ($suppliers as $supplier): ?>
                                    <option value="<?= $supplier['id'] ?>"><?= $supplier['title'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-btn">
                                <button type="button" data-form_id="new_supplier_form"
                                        data-action="accountings/ajax?act=create-contractor-form-no-modal"
                                        class="typeahead_add_form btn btn-info"
                                        data-id="supplier_creator"><?= l('Добавить') ?></button>
                            </div>
                        </div>
                        <?php if ($is_modal): ?>
                            <div id="new_supplier_form"
                                 class="typeahead_add_form_box theme_bg new_supplier_form p-md"></div>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label><?= l('Дата поставки') ?><b
                                class="text-danger">*</b>: <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_date_info') ?>
                        </label>
                        <input class="datetimepicker form-control" data-format="yyyy-MM-dd" type="text"
                               name="warehouse-order-date" data-required="true" value=""/>
                    </div>
                <?php endif; ?>
                <?php if (!empty($goods)): ?>
                    <div class="form-group relative">
                        <label><?= l('Запчасть') ?><b class="text-danger">*</b>: </label>
                        <?= typeahead($this->all_configs['db'], 'goods-goods', true, $order['goods_id'],
                            (15 + $typeahead), 'input-xlarge', 'input-medium', '', false, false, '', false,
                            l('Введите'),
                            array(
                                'name' => l('Добавить'),
                                'action' => 'products/ajax/?act=create_form',
                                'form_id' => 'new_device_form'
                            ), false) ?>
                        <?php if ($is_modal): ?>
                            <div id="new_device_form"
                                 class="typeahead_add_form_box theme_bg new_device_form p-md"></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="warehouse_type"><?= l('Тип поставки') ?><b class="text-danger">*</b>:
                        <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_type_info') ?></label>
                    <div class="radio">
                        <label><input data-required="true" type="radio" name="warehouse_type"
                                      value="1"/><?= l('Локально') ?></label>
                    </div>
                    <div class="radio">
                        <label><input type="radio" name="warehouse_type" data-required="true"
                                      value="2"/><?= l('Заграница') ?></label>
                    </div>
                </div>

                <div class="form-group">
                    <label><?= l('Номер') ?>
                        : <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_number_info') ?></label>
                    <input type="text" name="warehouse-order-num" class="form-control" value=""/>
                </div>
                <div class="form-group">
                    <label><?= l('Количество') ?><b class="text-danger">*</b>: </label>
                    <input type="text" data-required="true" onkeydown="return isNumberKey(event)"
                           name="warehouse-order-count" class="form-control"
                           value=""/>
                </div>
                <div class="form-group">
                    <label><?= l('Цена за один') ?> (<?= viewCurrencySuppliers('shortName') ?>)<b
                            class="text-danger">*</b>: </label>
                    <input type="text" data-required="true" onkeydown="return isNumberKey(event, this)"
                           name="warehouse-order-price" class="form-control"
                           value=""/>
                </div>
                <div class="form-group">
                    <label><?= l('Примечание') ?>: </label>
                    <textarea name="comment-supplier" class="form-control"></textarea>
                </div>

                <div class="form-group">
                    <label><?= l('номер ремонта') ?></label>
                    (<?= l('если запчасть заказывается под конкретный ремонт') ?>):
                    <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_order_info') ?>
                    <div class="relative">
                        <input type="text" name="so_co[]" class="form-control clone_clear_val"/>
                        <i class="glyphicon glyphicon-plus cloneAndClear"></i>
                    </div>
                </div>
                <div id="for-new-supplier-order"></div>
                <?php if ($all): ?>
                    <div class="form-group">
                        <?php if (empty($order_id)): ?>
                            <input type="button" class="btn submit-from-btn" onclick="create_supplier_order(this)"
                                   value="<?= l('Создать') ?>"/>
                        <?php else: ?>
                            <input type="submit" class="btn btn-primary submit-from-btn" name="new-order"
                                   value="Создать заказ поставщику"/>
                        <?php endif; ?>
                    </div>
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
