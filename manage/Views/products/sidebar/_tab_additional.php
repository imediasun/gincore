<!--     Additional info-->
<div class="hpanel panel-collapse">
    <div class="panel-heading hbuilt showhide">
        <div class="panel-tools">
            <i class="fa fa-chevron-up"></i>
        </div>
        <?= l('Доп. информация') ?>
    </div>
    <div class="panel-body" style="display: none;">
        <table class="table table-borderless">
            <tbody>
            <tr>
                <td colspan="2">
                    <div class="checkbox" style="margin: 0">
                        <label>
                            <input name="avail" <?= $product['avail'] == 1 ? 'checked' : '' ?> type="checkbox">
                            <?= l('Активность') ?>
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="checkbox" style="margin: 0">
                        <label>
                            <input name="deleted" <?= $product['deleted'] == 1 ? 'checked' : '' ?> type="checkbox">
                            <?= l('Удалить') ?>
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <div class="checkbox" style="margin: 0">
                        <label>
                            <input name="type" <?= $product['type'] == 1 ? 'checked' : '' ?> type="checkbox">
                            <?= l('Услуга') ?>
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <label><?= l('Категории') ?>: </label>
                </td>
                <td>
                    <select class="good-multiselect form-control" multiple="multiple" name="categories[]">
                        <?= build_array_tree($categories, array_keys($selected_categories)); ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td colspan="2"><hr /></td>
            </tr>
            <tr>
                <td colspan="2">
                    <?= l('Оплата сотруднику за продажу товара/услуги') ?>
                    &nbsp;<i class="fa fa-question-circle" data-toggle="tooltip" title="<?= l('l_pay_employee_for_sale') ?>" ></i>
                </td>
            </tr>
            <tr>
                <td>
                    <?= l('% от прибыли') ?>
                </td>
                <td>
                    <div class="input-group" style="width:150px">
                        <input type="text" class="form-control" value="<?= $product['percent_from_profit'] ?>"
                               style="min-width: 50px" name="percent_from_profit"/>
                        <div class="input-group-addon" style="cursor: pointer; width:50px">
                            <span class="percent">%</span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <?= l('фиксированная оплата') ?>
                </td>
                <td>
                    <div class="input-group" style="width:150px">
                        <input type="text" class="form-control" value="<?= round($product['fixed_payment'], 2) ?>"
                               style="min-width: 50px" name="fixed_payment"/>
                        <div class="input-group-addon" style="cursor: pointer; width:50px">
                            <span class="currency"><?= viewCurrency() ?></span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2">*<?= l('Если поля не будут заполнены их значения будут браться из основной категории') ?></td>
            </tr>
            <tr>
                <td>
                    <label><?= l('Основная категория') ?>: </label>
                </td>
                <td>
                    <select class="form-control" name="category_for_margin" style="width: 150px;">
                        <?= build_array_tree($categories, array($product['category_for_margin'])); ?>
                    </select>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>