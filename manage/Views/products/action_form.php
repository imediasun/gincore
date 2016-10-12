<div class="row-fluid">
    <form method="POST" id="action-form">
        <fieldset>
            <div class="col-sm-6">
                <div class="form-group">
                    <label>
                        <?= l('Активный') ?>
                        <input type="checkbox" name='active'/>
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <?= l('Удалить') ?>
                        <input type="checkbox" name='delete'/>
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <?= l('Сервис') ?>
                        <input type="checkbox" name='is_service'/>
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <?= l('Категории') ?>
                    </label>
                    <select class="multiselect form-control" multiple="multiple" name="categories[]">
                        <?= build_array_tree($categories, array_keys()); ?>
                    </select>
                </div>
            </div>
            <div class="col-sm-6">
                <label> <?= l('Цена поставки') ?></label>
                <input type="text" class="form-control" name="price_purchase"/>
                <label>
                    <?= l('Оптовая цена') ?>
                </label>
                <input type="text" class="form-control" name="price_wholesale"/>
                <label>
                    <?= l('Менеджер') ?>
                </label>
                <select class="form-control" name="manager">
                    <option value="-1"><?= l('Выберите') ?></option>
                </select>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                    <label>
                        <?= l('Уведомлять меня об остатках') ?>
                        <input type="checkbox" name="by_balance"/>
                    </label>
                </div>
                <input placeholder="<?= l('Количество товара') ?>" class="form-control" name="balance"/>
                <div class="form-group">
                    <label>
                        <?= l('Неснижаемый остаток') ?>
                        <input type="checkbox" name="use_minimum_balance"/>
                    </label>
                </div>
                <div class="input-group">
                    <input placeholder="<?= l('количество товаров') ?>"
                           value="0" type="text" class="form-control" onkeydown="return isNumberKey(event)"
                           name="minimum_balance" style="width: 150px;"/>
                    <div class="input-group-addon"><?= l('или менее единиц.') ?></div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="form-group">
                    <label>
                        <?= l('Уведомлять меня о каждой продаже этого товара') ?>
                        <input type="checkbox" name="each_sale"/>
                    </label>
                </div>
                <div class="form-group">
                    <label>
                        <?= l('Автонаценка') ?>
                        <input type='checkbox' name="use_automargin"/>
                    </label>
                </div>
                <div class="input-group" style="width:150px">
                    <input type="text" class="form-control" value="0" style="min-width: 50px" name="automargin"
                           placeholder="<?= l('Розница') ?>"/>
                    <div class="input-group-addon margin-type" onclick="change_margin_type(this, 'automargin')"
                         style="cursor: pointer">
                        <input type="hidden" class="form-control" value="0" name="automargin_type"/>
                        <span class="currency js-automargin-type" style="display:none"><?= viewCurrency() ?>&nbsp;<i
                                class="fa fa-caret-down" aria-hidden="true"></i></span>
                        <span class="percent js-automargin-type">%&nbsp;<i class="fa fa-caret-down"
                                                                           aria-hidden="true"></i></span>
                    </div>
                </div>
                <div class="input-group" style="width:150px">
                    <input type="text" class="form-control" value="0" style="min-width: 50px"
                           name="wholesale_automargin" placeholder="<?= l('Опт') ?>"/>
                    <div class="input-group-addon margin-type"
                         onclick="change_margin_type(this, 'wholesale_automargin')" style="cursor: pointer">
                        <input type="hidden" class="form-control" value="0" name="wholesale_automargin_type"/>
                        <span class="currency js-wholesale_automargin-type" style="display:none"><?= viewCurrency() ?>
                            &nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i></span>
                        <span class="percent js-wholesale_automargin-type">%&nbsp;<i class="fa fa-caret-down"
                                                                                     aria-hidden="true"></i></span>
                    </div>
                </div>
            </div>
            <input type="hidden" name="action" value="1"/>
            <div>

                <span>*<?= l('Будет применено к') ?>&nbsp;<?= $count ?>&nbsp;<?= l('позициям') ?></span>
            </div>
        </fieldset>
    </form>
</div>