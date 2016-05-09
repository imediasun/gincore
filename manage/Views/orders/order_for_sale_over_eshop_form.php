<div class="container-fluid">
    <div class="row">
        <form method="post" id="eshop-sale-form" parsley-validate>
            <div class="col-sm-6">
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
                                <div class="form-group" id="categories-selected">
                                    <?= typeahead($this->all_configs['db'], 'goods-goods', false,
                                        (!empty($order_data) ?
                                            $order_data['product_id'] : 0), 3, 'input-medium popover-info', '',
                                        'display_service_information,get_requests', false, false, '', false,
                                        l('Введите'),
                                        array()) ?>
                                </div>
                            </div>
                            <div class="form-group col-sm-2">
                                <label><?= l('Цена') ?> <b class="text-danger">*</b>: </label>
                                <div class="input-group">
                                    <input type="text" id="eshop_sale_poduct_cost" class="form-control" value=""
                                           name="price" onkeyup="return sum_calculate();"/>
                                    <span class="input-group-addon"><?= viewCurrency() ?></span>
                                </div>
                                <ul id="eshop_sale_product_cost_error" class="parsley-errors-list filled"
                                    style="display: none">
                                    <li class="parsley-required"><?= l('Обязательное поле.') ?></li>
                                </ul>
                            </div>
                            <div class="form-group col-sm-2">
                                <label>
                                    <?= l('Скидка, %') ?>
                                </label>
                                <div class="form-group">
                                    <input type="text" id="eshop_sale_poduct_discount" class="form-control"
                                           name="discount" onkeyup="return sum_calculate();"/>
                                </div>
                            </div>
                            <div class="form-group col-sm-2">
                                <label>
                                    <?= l('Кол-во') ?>
                                </label>
                                <div class="form-group">
                                    <input type="text" id="eshop_sale_poduct_quantity" class="form-control"
                                           name="quantity" onkeyup="return sum_calculate();"/>
                                </div>
                            </div>
                            <div class="form-group col-sm-2">
                                <label><?= l('Сумма') ?> <b class="text-danger">*</b>: </label>
                                <div class="input-group">
                                    <input type="text" id="eshop_sale_poduct_sum" class="form-control disabled" value=""
                                           name="sum" disabled/>
                                    <span class="input-group-addon"><?= viewCurrency() ?></span>
                                </div>
                                <ul id="eshop_sale_product_cost_error" class="parsley-errors-list filled"
                                    style="display: none">
                                    <li class="parsley-required"><?= l('Обязательное поле.') ?></li>
                                </ul>
                            </div>

                            <div class="form-group col-sm-2" style="padding: 0px">
                                <label>&nbsp;</label><br>
                                <button class="btn-sm btn-primary class" onclick="return add_eshop_item_to_table();"
                                        title="<?= l('Добавить товар') ?>">
                                    <span class="small"> <?= l('В&nbsp;корзину') ?> </span>
                                </button>
                            </div>
                        </div>
                        <?= $this->renderFile('orders/_cart_items_table', array(
                            'prefix' => 'eshop',
                            'orderWarranties' => $orderWarranties,
                            'defaultWarranty' => $defaultWarranty
                        )) ?>
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
                            <input type="radio" value="1" name="repair" onclick="alert('click');"/><?= l('Курьером') ?>
                        </label>
                        <label class="radio-inline">
                            <input type="radio" value="2" name="repair" onclick="alert('click');"/><?= l('Почтой') ?>
                        </label>
                    </div>
                    <div class="form-group">
                        <input type="text" name="address" class='form-control hidden' value=""
                               placeholder="<?= l('Укажите адрес') ?>"/>
                    </div>
                </fieldset>
                <div class="row-fluid">
                    <div class="btn-group dropup col-sm-3" style="padding-left: 0">
                        <input id="add-client-order" class="btn btn-primary submit-from-btn"
                               type="button"
                               onclick="eshop_sale(this)"
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
                                <a href="#" onclick="eshop_sale(this, 'print_waybill'); return false;">
                                    <?= l('Добавить и распечатать накладную на отгрузку товара') ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 js-requests relative">
                <div id="new_device_form"
                     class="typeahead_add_form_box theme_bg new_device_form p-md"></div>
                <fieldset>
                    <legend><?= l('Заявки') ?></legend>
                    <div id="eshop_client_requests">
                        <?php if ($order): ?>
                            <?= get_service('crm/requests')->get_requests_list_by_order_client($order_data['client_id'],
                                $order_data['product_id'], $_GET['on_request']) ?>
                        <?php else: ?>
                            <span
                                class="muted"><?= l('выберите клиента или устройство чтобы увидеть заявки') ?></span>
                        <?php endif; ?>
                    </div>
                </fieldset>
            </div>
        </form>
    </div>
</div>
