<h3><?= l('Редактирование заказа поставщику') ?> №<?= $order_id ?></h3>
<br>
<div class="row row-15">
    <form data-validate="parsley" id="suppliers-order-form" method="post">
        <?php if (empty($suppliers)): ?>
            <p class="text-danger"><?= l('Нет поставщиков') ?></p>
        <?php else: ?>
            <?php if ($order['sum_paid'] == 0 && $order['count_debit'] != $order['count_come']): ?>
                <div class="row-fluid">
                    <div class="col-sm-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="col-sm-5">
                                    <label><?= l('Склад') ?>
                                        : <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_wh_info') ?></label>
                                </td>
                                <td class="col-sm-4">
                                    <label><?= l('Локация') ?>:</label>
                                </td>
                                <td>
                                </td>
                            </tr>
                            <tr>
                                <td class="col-sm-5">
                                    <div class="form-group">
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
                                </td>
                                <td class="col-sm-4">
                                    <div class="form-group">
                                        <select
                                            class="form-control select-location" name="location">
                                            <?= $controller->gen_locations($order['wh_id'], $order['location_id']); ?>
                                        </select>
                                    </div>
                                </td>
                                <td>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endif; ?>

            <input type="hidden" name="order_id" value="<?= $order_id ?>"/>

            <?php if ($all): ?>
                <div class="row-fluid" data-validate="parsley" id="suppliers-order-form-header">
                    <div class="form-group relative col-sm-6">
                        <table class="table table-borderless">
                            <tr>
                                <td class="col-sm-5">
                                    <label><?= l('Поставщик') ?><b class="text-danger">*</b>: </label>
                                </td>
                                <td class="col-sm-4">
                                    <label><?= l('Дата поставки') ?><b
                                            class="text-danger">*</b>: <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_date_info') ?>
                                    </label>
                                </td>
                                <td>
                                    <label for="warehouse_type"><?= l('Тип поставки') ?><b class="text-danger">*</b>:
                                        <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_type_info') ?>
                                    </label>
                                </td>
                            </tr>
                            <tr>
                                <td class="col-sm-5">
                                    <div class="input-group">
                                        <select class="form-control" data-required="true" name="warehouse-supplier">
                                            <option value=""></option>
                                            <?php foreach ($suppliers as $supplier): ?>
                                                <option <?= ($order['supplier'] == $supplier['id']) ? 'selected' : '' ?>
                                                    value="<?= $supplier['id'] ?>"><?= $supplier['title'] ?></option>
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
                                </td>

                                <td class="col-sm-4">
                                    <input class="datetimepicker form-control" data-format="yyyy-MM-dd" type="text"
                                           name="warehouse-order-date" data-required="true"
                                           value="<?= ($order['date_wait'] ? date('Y-m-d',
                                               strtotime($order['date_wait'])) : '') ?>"/>
                                </td>
                                <td>
                                    <select class="form-control" data-required="true">
                                        <option value="1" <?= ($order['warehouse_type'] == 1 ? 'selected' : '') ?>
                                                name="warehouse_type"><?= l('Локально') ?> </option>
                                        <option value="2"<?= ($order['warehouse_type'] == 2 ? 'selected' : '') ?>
                                                name="warehouse_type"><?= l('Заграница') ?> </option>
                                    </select>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            <?php endif; ?>
            <div class="row-fluid">
                <div class="form-group relative col-sm-6">
                    <table class="table table-borderless">
                        <tr>
                            <td class="col-sm-5">
                                <label><?= l('Примечание') ?>: </label>
                            </td>
                            <td class="col-sm-4">
                                <label><?= l('Номер') ?>
                                    : <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_number_info') ?></label>
                            </td>
                            <td>
                            </td>
                        </tr>
                        <tr>
                            <td class="col-sm-5">
                                <textarea <?= $disabled ?> name="comment-supplier" class="form-control" rows="1"
                                                           style="height: 32px"><?= h($order['comment']) ?></textarea>
                            </td>
                            <td class="col-sm-4">
                                <input type="text" <?= $disabled ?> name="warehouse-order-num" class="form-control"
                                       value="<?= $order['num'] ?>"/>
                            </td>
                            <td>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <?= $this->renderFile('suppliers.class/_add_product_form', array(
                'order' => $order,
                'goods' => $goods,
                'is_modal' => $is_modal
            )); ?>
            <?= $this->renderFile('suppliers.class/_cart_items_table', array(
                'items' => $order['items']
            )); ?>

            <div id="for-new-supplier-order"></div>
            <?php if ($all): ?>
                <div class="row-fluid">
                    <div class="form-group col-sm-6">
                        <?= $order['btn'] ?>
                    </div>
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
<?= $info_html ?>
