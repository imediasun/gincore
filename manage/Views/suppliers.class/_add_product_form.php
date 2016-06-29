<div class="row-fluid" data-validate="parsley" id="js-add-product-form">
    <div class="form-group relative col-sm-12">
        <table class="table table-borderless">
            <tr>
                <td class="col-sm-3">
                    <label><?= l('Запчасть') ?><b class="text-danger">*</b>: </label>
                </td>
                <td class="col-sm-2">
                    <label><?= l('Цена') ?>, (<?= viewCurrencySuppliers('shortName') ?>)<b
                            class="text-danger">*</b>: </label>
                </td>
                <td class="col-sm-2">
                    <label><?= l('Количество') ?><b class="text-danger">*</b>: </label>
                </td>
                <td class="col-sm-2">
                    <label><?= l('Сумма') ?></label>
                </td>
                <td>
                    <label><?= l('номер ремонта') ?></label>
                    <?= InfoPopover::getInstance()->createQuestion('l_suppliers_order_order_info') ?>
                </td>
                <td class="col-sm-1">

                </td>
            </tr>
            <tr>
                <td>
                    <?php if (!empty($goods)): ?>
                        <?= typeahead($this->all_configs['db'], 'goods-goods', false, null,
                            (15 + $typeahead), 'input-xlarge exclude-form-validate', 'input-medium exclude-form-validate', '', false, false, '', false,
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
                    <?php endif; ?>
                </td>
                <td>
                    <input type="text" data-required="true" onkeydown="return isNumberKey(event, this)"
                           name="warehouse-order-price" id="supplier_product_cost" class="form-control exclude-form-validate"
                           onkeyup="return recalculate_product_sum(this);"
                           value=""/>

                </td>
                <td>
                    <input type="text" data-required="true" onkeydown="return isNumberKey(event)"
                           name="warehouse-order-count" class="form-control exclude-form-validate" id="supplier_product_quantity"
                           onkeyup="return recalculate_product_sum(this);"
                           value=""/>
                </td>
                <td>
                    <input type="text" readonly
                           class="form-control js-sum"
                           value=""/>
                </td>
                <td>
                    <div class="input-group">
                        <input type="text" name="new_so_co" class="form-control clone_clear_val js-so_co"
                               aria-describedby="basic-addon1"/>
                                    <span class="input-group-addon" id="basic-addon1">
                                        <i class="glyphicon glyphicon-plus"></i>
                                    </span>
                    </div>
                </td>
                <td>
                            <span class="btn btn-sm btn-primary btn-add-good"
                                  onclick="return add_supplier_item_to_table();"
                                  title="<?= l('Добавить товар') ?>">
                                    <span class="small" style="line-height: 22px">
                                        <i class="glyphicon glyphicon-plus"></i>
                                    </span>
                            </span>
                </td>
            </tr>
        </table>
    </div>
</div>
