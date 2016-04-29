<div class="container-fluid">
    <div class="row">
        <div class="col-sm-10">
            <form method="post" id="sale-over-eshop-form" parsley-validate>
                <input type="hidden" name="type" value="3">
                <?= $client['id'] ?>
                <fieldset>
                    <legend><?= l('Клиент') ?></legend>
                    <div class="form-group">
                        <label><?= l('Укажите данные клиента') ?> <b class="text-danger">*</b>: </label>
                        <div class="row row-15">
                            <div class="col-sm-4" style="padding-right:0px">
                                <?= $client['phone'] ?>
                            </div>
                            <div class="col-sm-2" style="line-height: 34px; ">
                                    <span class="tag"
                                          style="background-color: <?= !empty($tag) ? $tag['color'] : (isset($tags[$client['tag_id']]['color']) ? $tags[$client['tag_id']]['color'] : '') ?>">
                                        <?= htmlspecialchars(!empty($tag) ? $tag['title'] : (isset($tags[$client['tag_id']]['title']) ? $tags[$client['tag_id']]['title'] : '')) ?>
                                    </span>
                            </div>
                            <div class="col-sm-6">
                                <?= $client['fio'] ?>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label style="padding-top:0"><?= l('Код на скидку') ?>: </label>
                        <input type="text" name="code" class="form-control call_code_mask">
                    </div>
                    <div class="form-group">
                        <label><?= l('Рекламный канал') ?> (<?= l('источник') ?>): </label>
                        <?= get_service('crm/calls')->get_referers_list() ?>
                    </div>
                </fieldset>
                <fieldset>
                    <div class="container-fluid items-container">
                        <div class="row">
                            <div class="col-sm-12 no-padding-right">
                                <legend><?= l('Товар') ?></legend>
                            </div>
                        </div>
                        <div class="row">
                            <div class="form-group col-sm-2">
                                    <label class="control-label">
                                        <?= l('Выберите устройство') ?> <b class="text-danger">*</b>:
                                    </label>
                                <div class="form-group ">
                                    <?= typeahead($this->all_configs['db'], 'categories-last', false,
                                        (!empty($order_data) ?
                                            $order_data['product_id'] : 0), 3, 'input-medium popover-info', '',
                                        'display_service_information,get_requests', false, false, '', false,
                                        l('Введите'),
                                        array(
                                        )) ?>
                                </div>
                            </div>
                            <div class="form-group col-sm-2">
                                <label><?= l('Цена') ?> <b class="text-danger">*</b>: </label>
                                <div class="input-group">
                                    <input type="text" id="sale_poduct_cost" class="form-control" value=""
                                           name="price"/>
                                    <span class="input-group-addon"><?= viewCurrency() ?></span>
                                </div>
                                <ul id="sale_product_cost_error" class="parsley-errors-list filled"
                                    style="display: none">
                                    <li class="parsley-required"><?= l('Обязательное поле.') ?></li>
                                </ul>
                            </div>
                            <div class="form-group col-sm-2">
                                <label>
                                    <?= l('Скидка') ?>
                                </label>
                                <div class="form-group">
                                    <input type="text" class="form-control" name="discount" />
                                </div>
                            </div>
                            <div class="form-group col-sm-2">
                                <label>
                                    <?= l('Кол-во') ?>
                                </label>
                                <div class="form-group">
                                    <input type="text" class="form-control" name="quantity" />
                                </div>
                            </div>
                            <div class="form-group col-sm-2">
                                <label><?= l('Сумма') ?> <b class="text-danger">*</b>: </label>
                                <div class="input-group">
                                    <input type="text" id="sale_poduct_sum" class="form-control" value=""
                                           name="sum"/>
                                    <span class="input-group-addon"><?= viewCurrency() ?></span>
                                </div>
                                <ul id="sale_product_cost_error" class="parsley-errors-list filled"
                                    style="display: none">
                                    <li class="parsley-required"><?= l('Обязательное поле.') ?></li>
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
                                        <th class="col-sm-7"><?= l('Товар') ?></th>
                                        <th class="col-sm-4"><?= l('Цена') ?></th>
                                        <th></th>
                                        <th></th>
                                        <th></th>
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
                                                <input type="text" class="form-control js-discount" value=""/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group col-sm-12">
                                                <input type="text" class="form-control js-quantity" value=""/>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group col-sm-12">
                                                <input type="text" class="form-control js-sum" readonly
                                                       onkeyup="recalculate_amount();" value="" name=""/>
                                                <span class="input-group-addon"><?= viewCurrency() ?></span>
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
                                        <td></td>
                                        <td></td>
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
                    <div class="form-group">
                        <label><?= l('Способ доставки') ?>: </label><br>
                        <label class="radio-inline">
                            <input type="radio" checked value="0" name="repair"/><?= l('Самовывоз') ?>
                        </label>
                        <label class="radio-inline">
                            <input type="radio" value="1" name="repair" onclick="alert('click');" /><?= l('Курьером') ?>
                        </label>
                        <label class="radio-inline">
                            <input type="radio" value="2" name="repair" onclick="alert('click');" /><?= l('Почтой') ?>
                        </label>
                    </div>
                    <div class="form-group">
                        <input type="text" name="address" class='form-control hidden' value="" placeholder="<?= l('Укажите адрес') ?>"/>
                    </div>
                </fieldset>
                <div class="row-fluid">
                    <div class="btn-group dropup col-sm-3" style="padding-left: 0">
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
                                    <?= l('Добавить и распечатать накладную на отгрузку товара') ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </form>
            <div class="col-sm-6 relative"></div>
        </div>
    </div>
</div>
