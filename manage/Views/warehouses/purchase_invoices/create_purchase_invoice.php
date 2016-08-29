<div class="row-fluid">
    <div id=""></div>
    <div class="col-sm-12">
        <form id="suppliers-order-form">
            <input type="hidden" name="create-purchase-invoice" />
            <?php if (empty($suppliers)): ?>
                <p class="text-danger"><?= l('Нет поставщиков') ?></p>
            <?php else: ?>
                <div class="row-fluid" data-validate="parsley" id="suppliers-order-form-header">
                    <div class="form-group relative col-sm-12">
                        <table class="table table-borderless">
                            <thead>
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
                            </thead>
                            <tbody>
                            <tr>
                                <td class="col-sm-5">
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
                                                    class="typeahead_add_form btn btn-info"
                                                    data-id="supplier_creator"><?= l('Добавить') ?></button>
                                        </div>
                                    </div>
                                        <div id="new_supplier_form"
                                             class="typeahead_add_form_box theme_bg new_supplier_form p-md"></div>
                                </td>

                                <td class="col-sm-4">
                                    <input class="datetimepicker form-control" data-format="yyyy-MM-dd" type="text"
                                           name="warehouse-order-date" data-required="true" value=""/>
                                </td>
                                <td>
                                    <select class="form-control" data-required="true" name="warehouse-type">
                                        <option value="1"><?= l('Локально') ?> </option>
                                        <option value="2"><?= l('Заграница') ?> </option>
                                    </select>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="col-sm-12">
                        <table class="table table-borderless">
                            <thead>

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
                            </thead>
                            <tbody>
                            <tr>
                                <td class="col-sm-5">
                                    <div class="form-group">
                                        <select name="warehouse" onchange="change_warehouse(this)"
                                                class="select-warehouses-item-move form-control">
                                            <option value=""></option>
                                            <?php if ($warehouses): ?>
                                                <?php foreach ($warehouses as $warehouse): ?>
                                                    <option value="<?= $warehouse['id'] ?>"><?= $warehouse['title'] ?> </option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </td>
                                <td class="col-sm-4">
                                    <div class="form-group">
                                        <select
                                            class="form-control select-location" name="location">
                                        </select>
                                    </div>
                                </td>
                                <td>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="form-group relative col-sm-12">
                        <table class="table table-borderless">
                            <thead>
                            <tr>
                                <td class="col-sm-5">
                                    <label><?= l('Примечание') ?>: </label>
                                </td>
                                <td>
                                </td>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td class="col-sm-5">
                                <textarea name="comment-supplier" class="form-control" rows="1"
                                          style="height: 32px"></textarea>
                                </td>
                                <td>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <hr>

                <?= $this->renderFile('warehouses/purchase_invoices/_add_product_form', array()); ?>
                <?= $this->renderFile('warehouses/purchase_invoices/_cart_items_table', array()); ?>
            <?php endif; ?>
        </form>
    </div>
</div>