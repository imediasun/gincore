<div class="row-fluid">
    <div class="col-sm-6">
        <form method="POST" id="action-form">
            <fieldset>
                <table class="table table-borderless">
                    <tbody>
                    <tr>
                        <td width="10%">
                            <input type="checkbox" name='active'/>
                        </td>
                        <td width="35%">
                            <label>
                                <?= l('Активный') ?>
                            </label>
                        </td>
                        <td width="10%"></td>
                        <td width="25%">
                            <label> <?= l('Цена поставки') ?></label>
                        </td>
                        <td width="20%">
                            <input type="text" class="form-control" name="price_purchase"/>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" name='delete'/>
                        </td>
                        <td>
                            <label>
                                <?= l('Удалить') ?>
                            </label>
                        </td>
                        <td></td>
                        <td>
                            <label>
                                <?= l('Оптовая цена') ?>
                            </label>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="price_wholesale"/>

                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" name='is_service'/>
                        </td>
                        <td>
                            <label>
                                <?= l('Услуга') ?>
                            </label>
                        </td>
                        <td></td>
                        <td>
                            <select class="multiselect form-control" multiple="multiple" name="categories[]"
                                    data-placeholder="<?= l('Категории') ?>">
                                <?= build_array_tree($categories, array_keys()); ?>
                            </select>
                        </td>
                        <td>
                            <select class="form-control multiselect " name="manager"
                                    data-placeholder="<?= l('Менеджер') ?>">
                                <option value="-1"><?= l('Менеджер') ?></option>
                                <?php if (!empty($managers)): ?>
                                    <?php foreach ($managers as $manager): ?>
                                        <option value="<?= $manager['id'] ?>"><?= h($manager['login']) ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" name="by_balance"/>
                        </td>
                        <td colspan="3">
                            <label>
                                <?= l('Уведомлять меня об остатках') ?>
                            </label>
                        </td>
                        <td>
                            <input placeholder="<?= l('Количество товара') ?>" class="form-control" name="balance"/>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" name="use_minimum_balance"/>
                        </td>
                        <td colspan="2">
                            <label>
                                <?= l('Неснижаемый остаток') ?>
                            </label>
                        </td>
                        <td colspan="2">
                            <div class="input-group">
                                <input placeholder="<?= l('количество товаров') ?>"
                                       value="0" type="text" class="form-control" onkeydown="return isNumberKey(event)"
                                       name="minimum_balance"/>
                                <div class="input-group-addon"><?= l('или менее единиц.') ?></div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type="checkbox" name="each_sale"/>
                        </td>
                        <td colspan="4">
                            <label>
                                <?= l('Уведомлять меня о каждой продаже этого товара') ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <input type='checkbox' name="use_automargin"/>
                        </td>
                        <td>
                            <label>
                                <?= l('Автонаценка') ?>
                            </label>
                        </td>
                        <td></td>
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
                    <tr>
                        <td colspan="5">
                            <span>*<?= l('Будет применено к') ?>&nbsp;<span id="count-selected-record"><?= $count ?></span>&nbsp;<?= l('позициям') ?></span>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <input type="hidden" name="action" value="1"/>
                <input type="hidden" name="ids" value="<?= implode('-', $ids) ?>"/>
            </fieldset>
        </form>
    </div>
</div>
