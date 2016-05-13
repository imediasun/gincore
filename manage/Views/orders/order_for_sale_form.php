<div class="container-fluid">
    <div class="row">
        <div class="col-sm-7">
            <form method="post" id="quick-sale-form" parsley-validate>
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
                                <button class="btn-sm btn-primary class" onclick="return add_quick_item_to_table();"
                                        title="<?= l('Добавить товар') ?>">
                                    <span class="small"> <?= l('В&nbsp;корзину') ?> </span>
                                </button>
                            </div>
                        </div>
                        <?= $this->renderFile('orders/_cart_items_table', array(
                            'prefix' => 'quick',
                            'orderWarranties' => $orderWarranties,
                            'defaultWarranty' => $defaultWarranty
                        )) ?>
                    </div>

                    <div class="form-group">
                        <label><?= l('Скрытый комментарий к заказу') ?>: </label>
                        <textarea name="private_comment" class="form-control" rows="3"></textarea>
                    </div>
                </fieldset>
                <div class="row-fluid">
                    <div class="btn-group dropup col-sm-3" style="padding-left: 0">
                        <input id="add-client-order" class="btn btn-primary submit-from-btn"
                               type="button"
                               onclick="quick_sale(this)"
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
                                <a href="#" onclick="quick_sale(this, 'print_check'); return false;">
                                    <?= l('Добавить и распечатать чек') ?>
                                </a>
                            </li>
                            <li>
                                <a href="#" onclick="quick_sale(this, 'print_sale_warranty'); return false;">
                                    <?= l('Добавить и распечатать чек и гарантийный талон') ?>
                                </a>
                            </li>
                            <li>
                                <a href="#"
                                   onclick="quick_sale(this, 'print_invoice'); return false;">
                                    <?= l('Добавить и распечатать накладную на отгрузку товара') ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div class="form-group col-sm-9 form-inline">
                        <label>
                            <input type="checkbox" name="auto-cash">
                            <?= l('Автоматически принять деньги в кассу'); ?>
                        </label>
                        <input type="hidden" name="cashbox" value="0" />
                        <div class="dropdown dropdown-inline">
                            <button class="as_link" type="button" id="dropdownMenuCashboxes" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                <span class="btn-title"><?= l('Выбрать') ?></span>
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuCashboxes">
                            <?php foreach ($cashboxes as $cashbox): ?>
                                <li><a href="#" data-cashbox="<?= $cashbox['id'] ?>" onclick="return select_cashbox(this)"><?= $cashbox['name'] ?></a></li>
                            <?php endforeach; ?>
                            </ul>
                        </div>
                        <label>
                            <?= l('и закрыть заказ') ?>
                        </label>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-5 relative">
            <div class="container-fluid items-container">
                <div class="row">
                    <div class="col-sm-12 no-padding-right">
                        <legend style="border: 0;">&nbsp;</legend>
                    </div>
                </div>
                <div class="row">
                    <?= '*' . l('Торговая точка-режим экспресс продажи товаров без указани данных клиента. Достаточно ввести код товара с упаковки и указать цену продажи'); ?>
                </div>
            </div>
        </div>
    </div>
</div>
