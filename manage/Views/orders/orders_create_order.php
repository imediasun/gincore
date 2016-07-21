<script>
    $(function () {
        $(".test-toggle").bootstrapSwitch();
        $('.cashless-toggle').bootstrapSwitch({
            onText: '<?= l('Безнал'); ?>',
            offText: '<?= l('Нал'); ?>',
            labelWidth: 0,
            size: 'normal'
        });
    });
</script>
<ul class="nav nav-tabs default_tabs" role="tablist">
    <li role="presentation" class="active">
        <a href="#repair" role="tab" data-toggle="tab"><?= l('Заказ на ремонт') ?></a>
    </li>
    <li role="presentation">
        <a href="#sale" role="tab" data-toggle="tab"><?= l('Заказ на продажу') ?></a>
    </li>
</ul>
<div class="tab-content">
    <div class="tab-pane active" id="repair">
        <?php if ($available): ?>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6 js-fields">
                        <span class="specify_order_id"><?= l('Указать номер заказа') ?></span>
                        <span class="hide_order_fields">
                            <?= l('Скрыть поля в квитанции') ?>
                            <?= InfoPopover::getInstance()->createOnLoad('l_hide_order_fields_info') ?>
                        </span>
                    </div>
                </div>
                <div class="row">
                    <?= $this->renderFile('orders/hide_order_fields_form', array(
                        'hide' => $hide,
                        'users_fields' => $users_fields
                    )); ?>
                    <form method="post" id="order-form">
                        <div class="col-sm-6 js-fields">
                            <fieldset>
                                <div class="order_id_input clearfix">
                                    <input style="max-width:200px;float:left"
                                           placeholder="<?= l('введите номер заказа') ?>"
                                           type="text"
                                           class="form-control" name="id">&nbsp;
                                    <?= InfoPopover::getInstance()->createQuestion('l_order_custom_id_info') ?>
                                </div>
                                <legend><?= l('Клиент') ?></legend>
                                <div class="form-group">
                                    <label><?= l('Укажите данные клиента') ?> <b class="text-danger">*</b>: </label>
                                    <div class="row-fluid">
                                        <div class="col-sm-4" style="padding-right:0px; padding-left: 0px">
                                            <?= $client['phone'] ?>
                                        </div>
                                        <div class="col-sm-2" style="line-height: 34px; ">
                                        <span class="tag"
                                              style="background-color: <?= !empty($tag) ? $tag['color'] : (isset($tags[$client['tag_id']]['color']) ? $tags[$client['tag_id']]['color'] : '') ?>">
                                            <?= h(!empty($tag) ? $tag['title'] : (isset($tags[$client['tag_id']]['title']) ? $tags[$client['tag_id']]['title'] : '')) ?>
                                        </span>
                                        </div>
                                        <div class="col-sm-6 input-group">
                                            <?= $client['fio'] ?>
                                            <span class="input-group-addon js-personal" id="personal" onclick="return change_personal_to(2);"> <?= l('Физ') ?>
                                                <i class="fa fa-exchange" aria-hidden="true"></i>
                                            </span>
                                            <span class="input-group-addon js-personal" id="legal"  onclick="return change_personal_to(1);" style="display: none"><?= l('Юр') ?>
                                                <i class="fa fa-exchange" aria-hidden="true"></i>
                                            </span>
                                            <input type="hidden" name="person" value="1" />
                                        </div>
                                    </div>
                                </div>
                            <span class="toggle_btn" data-id="user_more_data">
                                <?= l('Указать дополнительные данные клиента') ?>
                            </span>
                                <div
                                    class="row row-15 toggle_box <?= (!empty($_COOKIE['user_more_data']) ? ' in' : '') ?>"
                                    id="user_more_data">
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><?= l('Укажите email') ?>:</label>
                                            <input placeholder="<?= l('email') ?>" type="text" name="email"
                                                   class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="form-group">
                                            <label><?= l('Укажите адрес') ?>:</label>
                                            <input placeholder="<?= l('адрес') ?>" type="text" name="address"
                                                   class="form-control">
                                        </div>
                                    </div>
                                </div>
                                <?= get_service('crm/calls')->assets() ?>
                                <div class="form-group <?= !isset($hide['crm-order-code']) ? 'hide-field' : '' ?>">
                                    <label style="padding-top:0"><?= l('Код на скидку') ?>: </label>
                                    <input <?= ($order ? ' value="' . $order['code'] . '" disabled' : '') ?> type="text"
                                                                                                             name="code"
                                                                                                             class="form-control call_code_mask"
                                                                                                             id="crm_order_code">
                                </div>
                                <div class="form-group <?= !isset($hide['referrer']) ? 'hide-field' : '' ?>">
                                    <label><?= l('Рекламный канал') . l('источник') ?>): </label>
                                    <div id="crm_order_referer">
                                        <?= get_service('crm/calls')->get_referers_list($order ? $order['referer_id'] : 'null',
                                            '', !!$order) ?>
                                    </div>
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend><?= l('Устройство') ?></legend>
                                <div class="form-group ">
                                    <label class="control-label"><?= l('Выберите устройство') ?> <b
                                            class="text-danger">*</b>: </label>
                                    <?= typeahead($this->all_configs['db'], 'categories-last', false,
                                        (!empty($order_data) ?
                                            $order_data['product_id'] : 0), 3, 'input-medium popover-info', '',
                                        'display_service_information,get_requests', false, false, '', false,
                                        l('Введите'),
                                        array(
                                            'name' => l('Добавить новое'),
                                            'action' => 'categories/ajax/?act=create_form',
                                            'form_id' => 'new_device_form'
                                        )) ?>
                                </div>
                                <div class="form-group <?= !isset($hide['color']) ? 'hide-field' : '' ?>">
                                    <label class="control-label"><?= l('Цвет') ?> : </label>
                                    <?= $colorsSelect ?>
                                </div>
                                <div class="form-group <?= !isset($hide['serial']) ? 'hide-field' : '' ?>">
                                    <label><?= l('Серийный номер') ?>: </label>
                                    <input type="text" class="form-control" value="" name="serial"/>
                                </div>
                                <input type="hidden" value="" id="serial-id" name="serial-id"/>
                                <div class="form-group <?= !isset($hide['equipment']) ? 'hide-field' : '' ?>">
                                    <label><?= l('Комплектация') ?>:</label><br>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" value="1" name="battery"/> <?= l('Аккумулятор') ?>
                                    </label>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" value="1" name="charger"/>
                                        <?= l('Зарядное устройство') ?>
                                        /<?= l('кабель') ?>
                                    </label><br>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" value="1" name="cover"/> <?= l('Задняя крышка') ?>
                                    </label>
                                    <label class="checkbox-inline">
                                        <input type="checkbox" value="1" name="box"/><?= l('Коробка') ?>
                                    </label>
                                    <input type="text" class="m-t-xs form-control" name="equipment"
                                           placeholder="<?= l('укажите свой вариант') ?>">
                                </div>
                                <div class="form-group <?= !isset($hide['repair-type']) ? 'hide-field' : '' ?>">
                                    <label><?= l('Вид ремонта') ?>: </label><br>
                                    <label class="radio-inline">
                                        <input type="radio" checked value="0" name="repair"/><?= l('Платный') ?>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" value="1" name="repair"/><?= l('Гарантийный') ?>
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" value="2" name="repair"/><?= l('Доработка') ?>
                                    </label>
                                </div>
                                <div
                                    class="form-group <?= !isset($hide['defect']) && !isset($hide['defect-description']) ? 'hide-field' : '' ?> ">
                                    <label><?= l('Неисправность со слов клиента') ?>: </label>
                                    <div
                                        class="row row-15 form-group <?= !isset($hide['defect']) ? 'hide-field' : '' ?>">
                                        <div class="col-sm-6">
                                            <label><?= l('Замена') ?>:</label>
                                            <input class="form-control" name="repair_part"
                                                   placeholder="<?= l('укажите деталь') ?>">
                                        </div>
                                        <div class="col-sm-6">
                                            <label><?= l('Качество детали') ?>:</label>
                                            <select class="form-control" name="repair_part_quality">
                                                <option
                                                    value="<?= l('Не согласовано') ?>"><?= l('Не согласовано') ?></option>
                                                <option value="<?= l('Оригинал') ?>"><?= l('Оригинал') ?></option>
                                                <option value="<?= l('Копия') ?>"><?= l('Копия') ?></option>
                                            </select>
                                        </div>
                                    </div>
                                <textarea
                                    class="form-control <?= !isset($hide['defect-description']) ? 'hide-field' : '' ?>"
                                    name="defect"></textarea>
                                </div>
                                <div class="form-group <?= !isset($hide['appearance']) ? 'hide-field' : '' ?>">
                                    <label class="control-label"><?= l('Внешний вид') ?>: </label>
                                <textarea class="form-control"
                                          name="comment"><?= l('Потертости, царапины') ?></textarea>
                                </div>
                            </fieldset>
                            <fieldset>
                                <legend><?= l('Стоимость') ?></legend>
                                <div class="form-group <?= !isset($hide['cost']) ? 'hide-field' : '' ?>">
                                    <label class="col-sm-12"
                                           style="padding-left: 0px; padding-right: 0px"><?= l('Ориентировочная стоимость') ?>
                                        : </label>
                                    <div class="row-fluid">
                                        <div class="col-sm-9" style="padding-left: 0px;">
                                            <div class="input-group">
                                                <input type="text" class="form-control" value=""
                                                       name="approximate_cost"/>
                                                <span class="input-group-addon"><?= viewCurrency() ?></span>
                                            </div>
                                        </div>
                                        <div class="col-sm-3">
                                            <div class="input-group"
                                                 title="<?= l('Отфильтровать все безналичные счета для сверки Вы можете в разделе: Бухгалтерия-Заказы-Заказы клиентов') ?>">
                                                <input type="checkbox" name="cashless" class="cashless-toggle">
                                            </div>
                                        </div>

                                    </div>
                                </div>
                                <div class="form-group <?= !isset($hide['prepaid']) ? 'hide-field' : '' ?>">
                                    <label><?= l('Предоплата') ?>: </label>
                                    <div class="input-group">
                                        <input type="text" placeholder="<?= l('Введите сумму') ?>" class="form-control"
                                               value="" name="sum_paid"/>
                                        <span class="input-group-addon"><?= viewCurrency() ?></span>
                                        <input type="text" placeholder="<?= l('Комментарий к предоплате') ?>"
                                               class="form-control" value="" name="prepay_comment"/>
                                    </div>
                                </div>
                                <div class="form-group <?= !isset($hide['available-date']) ? 'hide-field' : '' ?>">
                                    <label><?= l('Ориентировочная дата готовности') ?>: </label>
                                    <div class="input-group">
                                        <input class="daterangepicker_single form-control" data-format="YYYY-MM-DD"
                                               type="text" name="date_readiness" value=""/>
                                    <span class="input-group-addon">
                                        <i class="glyphicon glyphicon-calendar"
                                           data-time-icon="glyphicon glyphicon-time"
                                           data-date-icon="glyphicon glyphicon-calendar"></i>
                                    </span>
                                    </div>
                                </div>
                                <div class="form-group <?= !isset($hide['addition-info']) ? 'hide-field' : '' ?>">
                                    <label><?= l('Доп. информация') ?></label> <br>
                                    <div class="form-group-row">
                                        <div class="col-sm-6">
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" value="1" name="client_took"/>
                                                    <?= l('Устройство у клиента') ?>
                                                </label>
                                            </div>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" value="1" name="urgent"/>
                                                    <?= l('Срочный ремонт') ?>
                                                </label>
                                            </div>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" value="1" name="np_accept"/>
                                                    <?= l('Принято через почту') ?>
                                                </label>
                                            </div>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" value="1" name="nonconsent"/>
                                                    <?= l('Можно пускать в работу без согласования') ?>
                                                </label>
                                            </div>
                                            <div class="checkbox">
                                                <label>
                                                    <input type="checkbox" value="1" name="is_waiting"/>
                                                    <?= l('Клиент готов ждать 2-3 недели запчасть') ?>
                                                </label>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="checkbox">
                                                <label>
                                                    <input
                                                        onclick="if ($(this).prop('checked')){$('.courier_address').show();}else{$('.courier_address').hide();}"
                                                        type="checkbox" value="1" name="is_courier"/>
                                                    <?= l('Курьер забрал устройство у клиента') ?>
                                                </label>
                                                    <input type="text" style="display:none;"
                                                           placeholder="<?= l('по адресу') ?>"
                                                           class="form-control courier_address" value=""
                                                           name="courier"/>
                                            </div>
                                            <div class="checkbox">
                                                <label>
                                                    <input
                                                        onclick="if ($(this).prop('checked')){$('.replacement_fund').show();}else{$('.replacement_fund').hide();}"
                                                        type="checkbox" value="1" name="is_replacement_fund"/>
                                                    <?= l('Выдан подменный фонд') ?>
                                                </label>
                                                <input type="text" style="display:none;"
                                                       placeholder="<?= l('Модель, серийный номер') ?>"
                                                       class="form-control replacement_fund" value=""
                                                       name="replacement_fund"/>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div style="clear: both"></div>
                                <?php if (!empty($users_fields)): ?>
                                    <?php foreach ($users_fields as $field): ?>
                                        <div
                                            class="form-group <?= !isset($hide[$field['name']]) ? 'hide-field' : '' ?>">
                                            <label class="control-label"><?= $field['title'] ?>: </label>
                                            <textarea class="form-control"
                                                      name="users_fields[<?= $field['name'] ?>]"></textarea>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>

                                <div class="new_fields form-group">
                                    <div
                                        class="form-group js-new_field" style='display:none'>
                                        <label class="control-label"></label>
                                        <textarea class="form-control" name=""></textarea>
                                    </div>

                                </div>

                                <div class="form-group col-sm-12 js-new_users_fields hide-field"
                                     style="padding-left:0; display:none">
                                    <label class="control-label"><?= l('Добавить новое поле') ?>: </label>
                                    <div class="input-group">
                                        <input class="form-control" name="users_field_name"
                                               placeholder="<?= l('Введите название поля') ?>"
                                               aria-describedby="js-add_new_user_fields"/>
                                        <span class="input-group-addon"
                                              id='js-add_new_user_fields'
                                              onclick="return create_new_users_fields(this);"
                                              style="cursor:pointer">+</span>
                                    </div>
                                </div>

                                <div class="form-group col-sm-12" style="padding-left:0">
                                    <div class="btn-group dropup col-6 js-request">
                                        <input id="add-client-order" class="btn btn-primary submit-from-btn"
                                               type="button"
                                               onclick="add_new_order(this,'','create_order')"
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
                                                <a href="#" onclick="add_new_order(this, 'print'); return false;">
                                                    <?= l('Добавить и распечатать квитанцию') ?>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#" onclick="add_new_order(this, 'new_order'); return false;">
                                                    <?= l('Добавить и принять еще одно устройство от этого клиента') ?>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="#"
                                                   onclick="add_new_order(this, 'print_and_new_order'); return false;">
                                                    <?= l('Добавить, распечатать квитанцию и принять еще одно устройство от этого клиента') ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <div class="col-sm-6 js-requests relative">
                            <div id="new_device_form"
                                 class="typeahead_add_form_box theme_bg new_device_form p-md"></div>
                            <fieldset>
                                <legend><?= l('Заявки') ?> <?= InfoPopover::getInstance()->createQuestion('l_create_order_leads_info') ?></legend>
                                <div id="client_requests">
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
                        <br>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <p><?= l('Создание новых заказов запрещено условиями текущего тарифа') ?></p>
            <div class="form-group">
                <a href="<?= $this->all_configs['prefix'] ?>settings/tariffs" target="_blank"
                   class="btn btn-primary"><?= l('Изменить тариф') ?></a>
            </div>
        <?php endif; ?>
    </div>
    <div class="tab-pane" id="sale">
        <?php if ($available): ?>

            <!-- Nav tabs -->
            <ul class="nav nav-tabs" role="tablist">
                <li role="presentation" class="active"><a href="#quick-sale-pane" aria-controls="messages" role="tab"
                                                          data-toggle="tab"><?= l('Быстрая продажа') ?></a></li>
                <li role="presentation"><a href="#eshop-sale-pane" aria-controls="settings" role="tab"
                                           data-toggle="tab"><?= l('Интернет-магазин') ?></a></li>
            </ul>

            <!-- Tab panes -->
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="quick-sale-pane">
                    <?= $orderForSaleForm ?>
                </div>
                <div role="tabpanel" class="tab-pane" id="eshop-sale-pane">
                    <?= $orderEshopForm ?>

                </div>
            </div>
        <?php else: ?>
            <p><?= l('Создание новых заказов запрещено условиями текущего тарифа') ?></p>
            <div class="form-group">
                <a href="<?= $this->all_configs['prefix'] ?>settings/tariffs" target="_blank"
                   class="btn btn-primary"><?= l('Изменить тариф') ?></a>
            </div>
        <?php endif; ?>
    </div>
</div>
