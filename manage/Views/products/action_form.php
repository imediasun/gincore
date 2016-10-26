<div class="row-fluid">
    <form method="POST" id="action-form">
        <fieldset>
            <div class="hpanel panel-collapse">
                <div class="panel-heading hbuilt showhide">
                    <div class="panel-tools">
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <?= l('Основные параметры') ?>
                </div>
                <div class="panel-body" style="display: none;">
                    <div class="col-sm-10">
                        <table class="table table-borderless">
                            <tbody>
                            <tr>
                                <td colspan="2">
                                    <div class="input-group">
                                        <input type="checkbox" name='active'/>
                                        <?= l('Активный') ?>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <input type="checkbox" name='delete'/>
                                    <?= l('Удалить') ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <input type="checkbox" name='is_service'/>
                                    <?= l('Услуга') ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <input type="checkbox" name='is_item'/>
                                    <?= l('Товар') ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?= l('Категории') ?>
                                </td>
                                <td width="46%">
                                    <select class="multiselect form-control" multiple="multiple" name="categories[]"
                                            data-placeholder="<?= l('Категории') ?>">
                                        <?= build_array_tree($categories, array_keys()); ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?= l('Менеджер') ?>
                                </td>
                                <td>
                                    <select class="form-control multiselect " name="manager"
                                            data-placeholder="<?= l('Менеджер') ?>">
                                        <?php if (!empty($managers)): ?>
                                            <?php foreach ($managers as $manager): ?>
                                                <option
                                                    value="<?= $manager['id'] ?>"><?= h($manager['login']) ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="hpanel panel-collapse">
                <div class="panel-heading hbuilt showhide">
                    <div class="panel-tools">
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <?= l('Ценообразование') ?>
                </div>
                <div class="panel-body" style="display: none;">
                    <div class="col-sm-10">
                        <table class="table table-borderless">
                            <tbody>
                            <tr>
                                <td>
                                    <?= l('Розничная цена') ?>
                                </td>
                                <td width="46%">
                                    <input type="text" class="form-control" name="price"/>

                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <?= l('Оптовая цена') ?>
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="price_wholesale"/>

                                </td>
                            </tr>
                            <tr>
                                <td></td>
                            </tr>
                            <tr>
                                <td colspan="2" style="line-height: 30px">
                                    <input type='checkbox' name="use_automargin"/>
                                    <?= l('Автонаценка') ?><?= InfoPopover::getInstance()->createQuestion('l_good_automargin') ?>
                                </td>
                            </tr>
                            <tr>
                                <td><?= l('Розничная цена') ?></td>
                                <td>
                                    <div class="input-group" style="width:150px">
                                        <input type="text" class="form-control" value="0" style="min-width: 50px"
                                               name="automargin"
                                               placeholder="<?= l('Розница') ?>"/>
                                        <div class="input-group-addon margin-type"
                                             onclick="change_margin_type(this, 'automargin')"
                                             style="cursor: pointer">
                                            <input type="hidden" class="form-control" value="1" name="automargin_type"/>
                                            <span class="currency js-automargin-type"
                                                  style="display:none"><?= viewCurrency() ?>
                                                &nbsp;<i
                                                    class="fa fa-caret-down" aria-hidden="true"></i></span>
                                            <span class="percent js-automargin-type">%&nbsp;<i class="fa fa-caret-down"
                                                                                               aria-hidden="true"></i></span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><?= l('Оптовая цена') ?></td>
                                <td>
                                    <div class="input-group" style="width:150px">
                                        <input type="text" class="form-control" value="0" style="min-width: 50px"
                                               name="wholesale_automargin" placeholder="<?= l('Опт') ?>"/>
                                        <div class="input-group-addon margin-type"
                                             onclick="change_margin_type(this, 'wholesale_automargin')"
                                             style="cursor: pointer">
                                            <input type="hidden" class="form-control" value="1"
                                                   name="wholesale_automargin_type"/>
                                            <span class="currency js-wholesale_automargin-type"
                                                  style="display:none"><?= viewCurrency() ?>
                                                &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i></span>
                                            <span class="percent js-wholesale_automargin-type">%&nbsp;<i
                                                    class="fa fa-caret-down"
                                                    aria-hidden="true"></i></span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="hpanel panel-collapse">
                <div class="panel-heading hbuilt showhide">
                    <div class="panel-tools">
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <?= l('Оплата сотруднику за продажу товара/услуги') ?> <?= InfoPopover::getInstance()->createQuestion('l_pay_employee_for_sale') ?>
                </div>
                <div class="panel-body" style="display: none;">
                    <div class="col-sm-10">
                        <table class="table table-borderless">
                            <tbody>
                            <tr>
                                <td>
                                    <?= l('% от прибыли') ?>
                                </td>
                                <td width="46%">
                                    <div class="input-group" style="width:150px">
                                        <input type="text" class="form-control"
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
                                        <input type="text" class="form-control"
                                               style="min-width: 50px" name="fixed_payment"/>
                                        <div class="input-group-addon" style="cursor: pointer; width:50px">
                                            <span class="currency"><?= viewCurrency() ?></span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    *<?= l('Если поля не будут заполнены их значения будут браться из основной категории') ?></td>
                            </tr>
                            <tr>
                                <td>
                                    <?= l('Основная категория') ?>
                                </td>
                                <td>
                                    <select class="form-control" name="category_for_margin" style="width: 150px;">
                                        <option value="-1"> <?= l('Выберите категорию') ?></option>
                                        <?= build_array_tree($categories, array()); ?>
                                    </select>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="hpanel panel-collapse">
                <div class="panel-heading hbuilt showhide">
                    <div class="panel-tools">
                        <i class="fa fa-chevron-down"></i>
                    </div>
                    <?= l('Уведомления') ?>
                </div>
                <div class="panel-body" style="display: none;">
                    <div class="col-sm-10">
                        <table class="table table-borderless">
                            <tbody>
                            <tr>
                                <td colspan="2">
                                    <input type="checkbox" name="each_sale"/>
                                    <?= l('Уведомлять меня о каждой продаже этого товара') ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" name="use_minimum_balance"/>
                                    <?= l('Неснижаемый остаток') ?><?= InfoPopover::getInstance()->createQuestion('l_good_minimum_balance') ?>
                                </td>
                                <td width="46%">
                                    <div class="input-group">
                                        <input placeholder="<?= l('количество товаров') ?>"
                                               value="0" type="text" class="form-control"
                                               onkeydown="return isNumberKey(event)"
                                               name="minimum_balance"/>
                                        <div class="input-group-addon"><?= l('или менее') ?></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <input type="checkbox" name="by_balance"/>
                                    <?= l('Уведомлять меня об остатках') ?>
                                </td>
                                <td>
                                    <input placeholder="<?= l('Количество товара') ?>" class="form-control"
                                           name="balance"/>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <input type="hidden" name="action" value="1"/>
            <input type="hidden" name="ids" value="<?= implode('-', $ids) ?>"/>
        </fieldset>
    </form>
</div>
