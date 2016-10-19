<div class="row-fluid">
    <form method="POST" id="action-form">
        <fieldset>
            <div class="col-sm-6" style="border-right: 1px solid">

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
                        <td>
                            <?= l('Категории') ?>
                        </td>
                        <td>
                            <select class="multiselect form-control" multiple="multiple" name="categories[]"
                                    data-placeholder="<?= l('Категории') ?>">
                                <?= build_array_tree($categories, array_keys()); ?>
                            </select>
                        </td>
                    <tr>
                        <td>
                            <?= l('Менеджер') ?>
                        </td>
                        <td>
                            <select class="form-control multiselect " name="manager"
                                    data-placeholder="<?= l('Менеджер') ?>">
                                <?php if (!empty($managers)): ?>
                                    <?php foreach ($managers as $manager): ?>
                                        <option value="<?= $manager['id'] ?>"><?= h($manager['login']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-sm-6">

                <table class="table table-borderless">
                    <tbody>
                    <tr>
                        <td width="25%">
                            <?= l('Розничная цена') ?>
                        </td>
                        <td width="20%">
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
                        <td colspan="3">
                            <input type='checkbox' name="use_automargin"/>
                            <?= l('Автонаценка') ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div class="input-group" style="width:150px">
                                <input type="text" class="form-control" value="" style="min-width: 50px"
                                       name="automargin"
                                       placeholder="<?= l('Розница') ?>"/>
                                <div class="input-group-addon margin-type"
                                     onclick="change_margin_type(this, 'automargin')"
                                     style="cursor: pointer">
                                    <input type="hidden" class="form-control" value="0" name="automargin_type"/>
                                    <span class="currency js-automargin-type" style="display:none"><?= viewCurrency() ?>
                                        &nbsp;<i
                                            class="fa fa-caret-down" aria-hidden="true"></i></span>
                                    <span class="percent js-automargin-type">%&nbsp;<i class="fa fa-caret-down"
                                                                                       aria-hidden="true"></i></span>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="input-group" style="width:150px">
                                <input type="text" class="form-control" value="" style="min-width: 50px"
                                       name="wholesale_automargin" placeholder="<?= l('Опт') ?>"/>
                                <div class="input-group-addon margin-type"
                                     onclick="change_margin_type(this, 'wholesale_automargin')" style="cursor: pointer">
                                    <input type="hidden" class="form-control" value="0"
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
            <div class="clearfix"></div>
            <hr />
            <div class="col-sm-6" style="border-right: 1px solid">

                <table class="table table-borderless">
                    <tbody>

                    </tbody>
                </table>
            </div>
            <div class="col-sm-6">

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
                                <?= l('Неснижаемый остаток') ?>
                        </td>
                        <td>
                            <div class="input-group">
                                <input placeholder="<?= l('количество товаров') ?>"
                                       value="0" type="text" class="form-control" onkeydown="return isNumberKey(event)"
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
                            <input placeholder="<?= l('Количество товара') ?>" class="form-control" name="balance"/>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <input type="hidden" name="action" value="1"/>
            <input type="hidden" name="ids" value="<?= implode('-', $ids) ?>"/>
        </fieldset>
    </form>
</div>
