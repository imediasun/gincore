<h3><?= l('Создание нового заказа поставщику') ?></h3>
<?= l('Сначала выберите и добавьте изделие, укажите цену и количество, затем нажмите Создать') ?>
<br>
<br>
<form method="post" id="suppliers-order-form" style="position: relative">
    <?php if (empty($suppliers)): ?>
        <p class="text-danger"><?= l('Нет поставщиков') ?></p>
    <?php else: ?>
        <?php if ($order_id): ?>
            <input type="hidden" name="order_id" value="<?= $order_id ?>"/>
        <?php endif; ?>
            <div class="row-fluid" data-validate="parsley" id="suppliers-order-form-header">
                <div class="form-group relative col-sm-12">
                    <table class="table table-borderless">
                        <thead>
                        <tr>
                            <?php if ($all): ?>
                                <td class="col-sm-4">
                                    <label><?= l('Поставщик') ?><b class="text-danger">*</b>: </label>
                                </td>
                                <td class="col-sm-2">
                                    <label><?= l('Дата поставки') ?><b
                                            class="text-danger">*</b>: <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_date_info') ?>
                                    </label>
                                </td>
                                <td class="col-sm-2">
                                    <label for="warehouse_type"><?= l('Тип поставки') ?><b class="text-danger">*</b>:
                                        <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_type_info') ?>
                                    </label>
                                </td>
                            <?php endif; ?>
                            <td class="col-sm-1">
                                <label><?= l('Номер') ?>
                                    : <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_number_info') ?></label>
                            </td>

                            <td class="col-sm-2">
                                <label><?= l('Примечание') ?>: </label>
                            </td>
                            <td class="col-sm-1">
                            </td>

                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <?php if ($all): ?>
                                <td class="col-sm-4">
                                    <div class="input-group">
                                        <select class="form-control" data-required="true" name="warehouse-supplier">
                                            <option value=""></option>
                                            <?php foreach ($suppliers as $supplier): ?>
                                                <option
                                                    value="<?= $supplier['id'] ?>"><?= $supplier['title'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="input-group-btn">
                                            <button type="button" data-form_id="new_supplier_form"
                                                    data-action="accountings/ajax?act=create-contractor-form-no-modal"
                                                    class="typeahead_add_form btn btn-default"
                                                    data-id="supplier_creator"><i class="glyphicon glyphicon-plus"></i></button>
                                        </div>
                                    </div>
                                    <?php if ($is_modal): ?>
                                        <div id="new_supplier_form"
                                             class="typeahead_add_form_box theme_bg new_supplier_form p-md"></div>
                                    <?php endif; ?>
                                </td>
    
                                <td class="col-sm-2">
                                    <input class="datetimepicker form-control" data-format="yyyy-MM-dd" type="text"
                                           name="warehouse-order-date" data-required="true" value=""/>
                                </td>
                                <td class="col-sm-2">
                                    <select class="form-control" data-required="true">
                                        <option value="1" name="warehouse_type"><?= l('Локально') ?> </option>
                                        <option value="2" name="warehouse_type"><?= l('Заграница') ?> </option>
                                    </select>
                                </td>
                            <?php endif; ?>

                            <td class="col-sm-1">
                                <input type="text" name="warehouse-order-num" class="form-control" value=""/>
                            </td>

                            <td class="col-sm-2">
                                <textarea name="comment-supplier" class="form-control" rows="1"
                                          style="height: 32px"></textarea>
                            </td>

                            <td class="col-sm-1">
                            </td>

                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        <hr>
        <?= $this->renderFile('suppliers.class/_add_product_form', array(
            'order' => $order,
            'goods' => $goods,
            'is_modal' => $is_modal
        )); ?>
        <?= $this->renderFile('suppliers.class/_cart_items_table', array()); ?>


        <?php if ($all): ?>
            <div class="row-fluid">
                <div class="col-sm-12 form-group">
                    <?php if (empty($order_id)): ?>
                        <input id='js-create-button' type="button" class="btn submit-form-btn" style='display:none' onclick="create_supplier_order(this)"
                               value="<?= l('Создать') ?>"/>
                    <?php else: ?>
                        <input type="submit" class="btn btn-primary submit-from-btn" name="new-order"
                               value="Создать заказ поставщику"/>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</form>
<div class="row-fluid">
    <div class="col-sm-6 relative">
        <?php if (!$is_modal): ?>
            <div id="new_supplier_form" class="typeahead_add_form_box theme_bg new_supplier_form p-md"></div>
            <div id="new_device_form" class="typeahead_add_form_box theme_bg new_device_form p-md"></div>
        <?php endif; ?>
    </div>
</div>
