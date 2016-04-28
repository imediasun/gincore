<div class="container-fluid">
    <div class="row">
        <div class="col-sm-6">
            <form method="post" id="sale-form" parsley-validate>
                <input type="hidden" name="type" value="3">
                <fieldset>
                    <div class="container-fluid items-container">
                        <div class="row">
                            <div class="col-sm-12 no-padding-right">
                                <legend><?= l('Товар') ?></legend>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-6">
                                <label class="control-label">
                                    <?= l('Код товара') ?> (<?= l('серийный номер') ?>) <b class="text-danger">*</b>:
                                </label>
                                <?= typeahead($this->all_configs['db'], 'serials', false, '', 4,
                                    'input-medium clone_clear_val',
                                    '', 'display_serial_product_title_and_price', false, true) ?>
                                <small class="clone_clear_html product-title"></small>
                                <input type="hidden" name="items" id="item_id" value="">
                            </div>
                            <div class="form-group col-sm-4">
                                <label><?= l('Цена продажи') ?> <b class="text-danger">*</b>: </label>
                                <div class="input-group">
                                    <input type="text" id="sale_poduct_cost" class="form-control" value=""
                                           name="price"/>
                                    <span class="input-group-addon"><?= viewCurrency() ?></span>
                                </div>
                                <ul id="sale_product_cost_error" class="parsley-errors-list filled"
                                    style="display: none">
                                    <li class="parsley-required"><?= l('Обязательное поле') ?></li>
                                </ul>
                            </div>

                            <div class="form-group col-sm-2" style="padding: 0px">
                                <label>&nbsp;</label><br>
                                <button class="btn-sm btn-primary class" onclick="return add_item_to_table();"
                                        title="<?= l('Добавить товар') ?>">
                                    <span class="small"> <?= l('В&nbsp;корзину') ?> </span>
                                </button>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-items" style="display:none">
                                    <thead>
                                    <tr>
                                        <th class="col-sm-6"><?= l('Товар') ?></th>
                                        <th class="col-sm-3"><?= l('Цена') ?></th>
                                        <th class="col-sm-3"><?= l('Гарантия') ?></th>
                                        <th></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <tr class="js-row-cloning" style="display: none">
                                        <td>
                                            <div class="input-group col-sm-12">
                                                <input type="hidden" class="form-control js-item-id" name="" value="">
                                                <input type="text" readonly class="form-control js-item-name" value=""/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group col-sm-12">
                                                <input type="text" class="form-control js-price"
                                                       onkeyup="recalculate_amount();" value="" name=""/>
                                                <span class="input-group-addon"><?= viewCurrency() ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group col-sm-12">
                                                <select class="form-control js-warranty" name="">
                                                    <option value=""><?= l('Без гарантии') ?></option>
                                                    <?php foreach ($orderWarranties as $warranty): ?>
                                                        <option value="<?= intval($warranty) ?>"><?= intval($warranty) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <div class="input-group-addon"><?= l('мес') ?></div>
                                            </div>
                                        </td>
                                        <td>
                                            <a href="#" onclick="return remove_row(this);">
                                                <i class="glyphicon glyphicon-remove"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    </tbody>
                                    <tfoot>
                                    <tr class="row-amount">
                                        <td>
                                            <div class="input-group col-sm-12">
                                                <label><?= l('Итоговая стоимость:') ?></label>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group col-sm-12">
                                                <input type="text" readonly class="form-control js-total" value=""/>
                                                <span class="input-group-addon"><?= viewCurrency() ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group"
                                                 title="<?= l('Отфильтровать все безналичные счета для сверки Вы можете в разделе: Бухгалтерия-Заказы-Заказы клиентов') ?>">
                                                <input type="checkbox" name="cashless" class="cashless-toggle">
                                            </div>
                                        </td>
                                        <td></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?= l('Гарантия') ?>: </label>
                        <div class="input-group">
                            <select class="form-control" name="warranty">
                                <option value=""><?= l('Без гарантии') ?></option>
                                <?php foreach ($orderWarranties as $warranty): ?>
                                    <option value="<?= intval($warranty) ?>"><?= intval($warranty) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-group-addon"><?= l('мес') ?></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?= l('Скрытый комментарий к заказу') ?>: </label>
                        <textarea name="private_comment" class="form-control" rows="3"></textarea>
                    </div>
                </fieldset>

                <div class="btn-group dropup">
                    <input id="add-client-order" class="btn btn-primary submit-from-btn"
                           type="button"
                           onclick="sale_order(this)"
                           value="<?= l('Добавить') ?>"/>
                    <button type="button" class="btn btn-info dropdown-toggle"
                            data-toggle="dropdown"
                            aria-haspopup="true"
                            aria-expanded="false">
                        <span class="caret"></span>
                        <span class="sr-only">Toggle Dropdown</span>
                    </button>
                    <ul class="dropdown-menu">
                        <li>
                            <a href="#" onclick="sale_order(this, 'print_check'); return false;">
                                <?= l('Добавить и распечатать чек') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#" onclick="sale_order(this, 'print_warranty'); return false;">
                                <?= l('Добавить и распечатать чек и гарантийный талон') ?>
                            </a>
                        </li>
                        <li>
                            <a href="#"
                               onclick="sale_order(this, 'print_invoice'); return false;">
                                <?= l('Добавить и распечатать накладную на отгрузку товара') ?>
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="form-group">
                    <label>

                    <input type="checkbox">
                        <?= l('Автоматически принять деньги в кассу'); ?>
                        <select>
                            <option value="0"><?= l('Выбрать') ?></option>
                        </select>
                        <?= l('и закрыть заказ') ?>
                    </label>
                </div>
<!--                <input class="btn btn-primary" type="button" onclick="" value="--><?//= l('Добавить') ?><!--"/>-->
            </form>
            <div class="col-sm-6 relative"></div>
        </div>
    </div>
</div>
