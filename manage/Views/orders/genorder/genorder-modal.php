<div class="modal-dialog" role="document" style="width: 90%; margin-top:-20px">
    <div class="modal-content">
        <div class="modal-header" style="padding: 5px 15px">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="exampleModalLabel" style="font-size:13px"><?= l('Редактирование заказа') ?></h4>
        </div>
        <div class="modal-body">
            <form method="post" id="order-form" class="clearfix order-form-edit backgroud-white order-form-p-lg row-fluid"
                  style="margin:0;">
                <?php $color = preg_match('/^#[a-f0-9]{6}$/i',
                    trim($order['color'])) ? trim($order['color']) : '#000000'; ?>

                <div class="span6">
                    <input id='order_id' type="hidden" name="order_id" value="<?= $order['id'] ?>" />
                    <input type="hidden" name="is_modal" value="1" />
                    <div class="bordered">
                        <div class="row-fluid">

                            <div class="span6">
                                <h3 class="m-t-none">
                                    № <?= $order['id'] ?>
                                    <?= $this->renderFile('orders/genorder/_print_buttons', array(
                                        'hasEditorPrivilege' => $hasEditorPrivilege,
                                        'order' => $order
                                    )) ?>
                                    <button data-o_id="<?= $order['id'] ?>" onclick="alert_box(this, false, 'sms-form')"
                                            class="btn btn-default" type="button"><i class="fa fa-mobile"></i> SMS
                                    </button>
                                </h3>
                            </div>
                            <div class="span6" style="line-height: 36px">
                                <div class="form-group center">
                                    <small style="font-size:10px"
                                           title="<?= do_nice_date($order['date_add'], false) ?>">
                                        <?= l('Принят') ?>: <?= do_nice_date($order['date_add']) ?>
                                    </small>
                                    &nbsp;
                                    <?php if (!empty($homeMasterRequest)): ?>
                                        <i style="color:<?= $color ?>; font-size: 10px" title="<?= $homeMasterRequest['address'] ?>, <?= $homeMasterRequest['date']?>"
                                           class="fa fa-car"></i>
                                    <?php endif; ?>
                                    <?php if (mb_strlen($order['courier'], 'UTF-8') > 0): ?>
                                        <i style="color:<?= $color ?>;"
                                           title="<?= l('Курьер забрал устройство у клиента') ?>"
                                           class="fa fa-truck"></i>
                                    <?php endif; ?>
                                    <?php if ($order['np_accept'] == 1): ?>
                                        <i title="<?= l('Принято через почту') ?>"
                                           class="fa fa-suitcase text-danger"></i>
                                    <?php else: ?>
                                        <i style="color:<?= $color ?>;" title="<?= l('Принято в сервисном центре') ?>"
                                           class="<?= htmlspecialchars($order['icon']) ?>"></i>
                                    <?php endif; ?>
                                    <?= $order['aw_title'] ?>&nbsp;<?= timerout($order['id'], true) ?>
                                </div>
                            </div>
                        </div>

                        <div class="row-fluid">
                            <div class="span6">
                                <div class="form-group clearfix">
                                    <label class="lh30">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-fio')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
                                        <a href="<?= Url::create(array(
                                            'controller' => 'clients',
                                            'action' => 'create',
                                            $order['user_id']
                                        )) ?>" title="<?= l('Карточка клиента') ?>" target="_blank">
                                            <i class="fa fa-info" aria-hidden="true" style="padding: 0 3px 0 3px"></i>
                                        </a>
                                        <?= l('Заказчик') ?>:
                                    </label>
                                    <div class="tw100">
                                        <input type="text" value="<?= htmlspecialchars($order['fio']) ?>" name="fio"
                                               class="form-control"/>
                                    </div>
                                </div>
                                <div class="form-group clearfix">
                                    <label class="lh30">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-phone')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
                                        <?= l('Телефон') ?>:
                                    </label>
                                    <div class="tw100">
                                        <input type="text" value="<?= htmlspecialchars($order['phone']) ?>" name="phone"
                                               class="form-control"/>
                                    </div>
                                </div>
                                <div class="form-group clearfix">
                                    <label>
                            <span class="cursor-pointer glyphicon glyphicon-list" title="<?= l('История изменений') ?>"
                                  data-o_id="<?= $order['id'] ?>"
                                  onclick="alert_box(this, false, 'changes:update-order-category')"></span>
                                        <i class="glyphicon glyphicon-picture cursor-pointer"
                                           data-o_id="<?= $order['id'] ?>"
                                           onclick="alert_box(this, null, 'order-gallery')"></i>
                                        <i class="fa fa-info" aria-hidden="true" data-o_id="<?= $order['id'] ?>"
                                           onclick="return show_category_addition_info(this);"
                                           title="<?= l('Важная информация') ?>"
                                           style="cursor:pointer; padding: 0 3px 0 3px"></i>
                                        <?= l('Устройство') ?>:
                                    </label>
                                    <?= typeahead($this->all_configs['db'], 'categories-goods', false,
                                        $order['category_id'], 4,
                                        'input-medium', '', 'display_category_information,get_requests') ?>
                                </div>
                                <div class="form-group clearfix <?= !isset($hide['color']) ? 'hide-field' : '' ?>">
                                    <label class="control-label lh30"><?= l('Цвет') ?>: </label>
                                    <div class="tw100">
                                        <select class="form-control" name="color">
                                            <?php if (is_null($order['o_color'])): ?>
                                                <option value="-1" selected disabled><?= l('Не выбран') ?></option>
                                            <?php endif; ?>
                                            <?php foreach ($this->all_configs['configs']['devices-colors'] as $id => $color): ?>
                                                <option <?= (!is_null($order['o_color']) && $order['o_color'] == $id) ? 'selected' : '' ?>
                                                    value="<?= $id ?>">
                                                    <?= $color ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?= htmlspecialchars($order['note']) ?>
                                </div>
                                <?php if ($notSale): ?>
                                    <div class="form-group clearfix <?= !isset($hide['serial']) ? 'hide-field' : '' ?>">
                                        <label class="lh30">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-serial')"
                                  data-o_id="<?= $order['id'] ?>"
                                  title="<?= l('История изменений') ?>">

                            </span>
                                            S/N:
                                        </label>
                                        <div class="tw100">
                                            <input type="text" value="<?= htmlspecialchars($order['serial']) ?>"
                                                   name="serial"
                                                   class="form-control"/>
                                        </div>
                                    </div>
                                    <div
                                        class="form-group clearfix <?= !isset($hide['equipment']) ? 'hide-field' : '' ?>">
                                        <label><?= l('Комлектация') ?>:</label><br>
                                        <?= implode(', ', $parts) ?>
                                    </div>

                                    <div
                                        class="form-group clearfix <?= !isset($hide['repair-type']) ? 'hide-field' : '' ?>">
                                        <label><?= l('Вид ремонта') ?>:</label>
                                        <?php
                                        switch ($order['repair']) {
                                            case 0:
                                                echo l('Платный');
                                                break;
                                            case 1:
                                                echo l('Гарантийный');
                                                break;
                                            case 2:
                                                echo l('Доработка');
                                                break;
                                        } ?>
                                    </div>
                                    <div class="form-group clearfix">
                                        <label><?= l('Сроки') ?>:</label>
                                        <?= ($order['urgent'] == 1 ? l('Срочный') : l('Не срочный')) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="span6">
                                <div class="form-group clearfix">
                                    <label>
                            <span onclick="alert_box(this, false, 'stock_moves-order')"
                                  data-o_id="<?= $order['id'] ?>"
                                  class="cursor-pointer glyphicon glyphicon-list"
                                  title="<?= l('История перемещений') ?>">

                            </span>
                                        <?= l('Локации') ?>:
                                    </label>
                                    <?= htmlspecialchars($order['wh_title']) ?>
                                    <?= htmlspecialchars($order['location']) ?>
                                    <i title="<?= l('Переместить заказ') ?>"
                                       onclick="alert_box(this, false, 'stock_move-order', undefined, undefined, 'messages.php')"
                                       data-o_id="<?= $order['id'] ?>"
                                       class="glyphicon glyphicon-move cursor-pointer"></i>
                                </div>

                                <?php $style = isset($this->all_configs['configs']['order-status'][$order['status']]) ? 'style="color:#' . htmlspecialchars($this->all_configs['configs']['order-status'][$order['status']]['color']) . '"' : '' ?>
                                <div class="form-group clearfix">
                                    <label class="lh30">
                                        <span <?= $style ?>></span>
                                <span class="cursor-pointer glyphicon glyphicon-list"
                                      title="<?= l('История перемещений') ?>"
                                      data-o_id="<?= $order['id'] ?>"
                                      onclick="alert_box(this, false, 'order-statuses')">

                                </span>
                                        <?= l('Статус') ?>:
                                    </label>
                                    <?= $this->renderFile('orders/genorder/_order_status', array(
                                        'active' => intval($order['status'])
                                    )) ?>
                                </div>
                                <div class="form-group clearfix">
                                    <label><?= l('Приемщик') ?>:</label>
                                    <?= get_user_name($order, 'a_') ?>
                                </div>
                                <?php if ($notSale): ?>
                                    <?php if ($order['manager'] == 0 && $hasEditorPrivilege): ?>
                                        <div class="form-group clearfix">
                                            <label> <?= l('manager') ?>: </label>
                                            <input type="submit" name="accept-manager"
                                                   class="accept-manager btn btn-default btn-xs"
                                                   value="<?= l('Взять заказ') ?>"/>
                                            <input type="hidden" name="accept-manager" value=""/>
                                        </div>
                                    <?php else: ?>
                                        <?= $this->renderFile('orders/genorder/_employers', array(
                                            'users' => $managers,
                                            'order' => $order,
                                            'title' => l('manager'),
                                            'type' => 'manager'
                                        )); ?>
                                    <?php endif; ?>
                                    <?= $this->renderFile('orders/genorder/_employers', array(
                                        'users' => $engineers,
                                        'order' => $order,
                                        'title' => l('Инженер'),
                                        'type' => 'engineer'
                                    )); ?>
                                <?php endif; ?>
                                <div
                                    class="form-group clearfix <?= !isset($hide['defect']) || !isset($hide['defect-description']) ? 'hide-field' : '' ?>">
                                    <label>
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  title="<?= l('История изменений') ?>"
                                  data-o_id="<?= $order['id'] ?>"
                                  onclick="alert_box(this, false, 'changes:update-order-defect')">

                            </span>
                                        <?= l('Неисправность со слов клиента') ?>:
                                    </label>
                        <textarea class="form-control"
                                  name="defect"><?= htmlspecialchars($order['defect']) ?></textarea>
                                </div>
                                <div class="form-group clearfix <?= !isset($hide['appearance']) ? 'hide-field' : '' ?>">
                                    <label>
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  title="<?= l('История изменений') ?>"
                                  data-o_id="<?= $order['id'] ?>"
                                  onclick="alert_box(this, false, 'changes:update-order-comment')">

                            </span>
                                        <?= l('Примечание') ?>/<?= l('Внешний вид') ?>:
                                    </label>
                            <textarea class="form-control"
                                      name="comment"><?= htmlspecialchars($order['comment']) ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row-fluid bordered">
                        <div class="span6">
                            <?php if ($notSale): ?>
                                <div
                                    class="form-group clearfix <?= !isset($hide['available-date']) ? 'hide-field' : '' ?>">
                                    <label><?= l('Ориентировочная дата готовности') ?>: </label>
                            <span title="<?= do_nice_date($order['date_readiness'],
                                false) ?>"><?= do_nice_date($order['date_readiness']) ?></span>
                                </div>
                                <?php if ($hasEditorPrivilege): ?>
                                    <div class="form-group clearfix"><label><?= l('Ориентировочная стоимость') ?>
                                            : </label>
                                        <?= ($order['approximate_cost'] / 100) ?> <?= viewCurrency() ?>
                                    </div>
                                <?php endif; ?>
                                <div
                                    class="form-group clearfix <?= !isset($hide['addition-info']) ? 'hide-field' : '' ?>">
                            <span style="margin:4px 10px 0 0"
                                  class="pull-left cursor-pointer glyphicon glyphicon-list muted"
                                  onclick="alert_box(this, false, 'changes:update-order-client_took')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>">
                            </span>
                                    <label class="checkbox-inline">
                                        <input type="checkbox"
                                               value="1" <?= ($order['client_took'] == 1 ? 'checked' : '') ?>
                                               name="client_took">
                                        <?= l('Устройство у клиента') ?>
                                    </label>
                                </div>
                                <?php $onclick = 'if ($(this) . prop(\'checked\')){$(\'.replacement_fund\').val(\'\');$(\'.replacement_fund\').prop(\'disabled\', false);$(\'.replacement_fund\').show();$(this).parent().parent().addClass(\'warning\');}else{$(\'.replacement_fund\').hide();$(this).parent().parent().removeClass(\'warning\');}'; ?>
                                <div
                                    class="form-group clearfix <?= !isset($hide['addition-info']) ? 'hide-field' : '' ?> <?= ($order['is_replacement_fund'] == 1 ? ' warning' : '') ?>">
                            <span style="margin:4px 10px 0 0"
                                  class="pull-left cursor-pointer glyphicon glyphicon-list muted"
                                  onclick="alert_box(this, false, 'changes:update-order-replacement_fund')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
                                    <label class="checkbox-inline">
                                        <input onclick="<?= $onclick ?>" type="checkbox" value="1"
                                            <?= ($order['is_replacement_fund'] == 1 ? 'checked' : '') ?>
                                               name="is_replacement_fund"/>
                                        <?= l('Подменный фонд') ?>
                                    </label>
                                    <input <?= ($order['is_replacement_fund'] == 1 ? 'disabled' : 'style="display:none;"') ?>
                                        type="text" placeholder="<?= l('Модель, серийный номер') ?>"
                                        class="form-control replacement_fund"
                                        value="<?= htmlspecialchars($order['replacement_fund']) ?>"
                                        name="replacement_fund"/>
                                </div>
                                <div
                                    class="form-group clearfix <?= !isset($hide['addition-info']) ? 'hide-field' : '' ?>">
                                    <label class="checkbox-inline">
                                        <input type="checkbox"
                                               value="1" <?= ($order['nonconsent'] == 1 ? 'checked' : '') ?>
                                               name="nonconsent"/>
                                        <?= l('Можно пускать в работу без согласования') ?>
                                    </label>
                                </div>
                                <div
                                    class="form-group clearfix <?= !isset($hide['addition-info']) ? 'hide-field' : '' ?>">
                                    <label class="checkbox-inline">
                                        <input type="checkbox"
                                               value="1" <?= ($order['is_waiting'] == 1 ? 'checked' : '') ?>
                                               name="is_waiting"/>
                                        <?= l('Клиент готов ждать 2-3 недели запчасть') ?>
                                    </label>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="span6">
                            <?php if ($notSale): ?>
                                <?php if (false && ($order['return_id'] > 0 || $this->all_configs['oRole']->hasPrivilege('edit_return_id'))): ?>
                                    <div class="form-group clearfix">
                                        <label><?= l('Номер возврата') ?>: </label>
                                        <?php if ($this->all_configs['oRole']->hasPrivilege('edit_return_id')): ?>
                                            <label class="lh30" style="font-weight: normal">
                                                <?= $order['id'] ?>-
                                            </label>
                                            <div class="tw100">
                                                <?php if (!empty($returns)): ?>
                                                    <select name="return_id" class="form-control">
                                                        <option value="-1"><?= l("Не выбрано") ?></option>
                                                        <?php foreach ($returns as $return): ?>
                                                            <option <?= $return['id'] == $order['return_id'] ? 'selected' : '' ?>
                                                                value="<?= $return['id'] ?>">
                                                                <?= $return['id'] . "(" . ($return['value_from'] / 100) . ' ' . $this->all_configs['configs']['currencies'][$return['currency']]['name'] . ")" ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <?= $order['id'] ?>-<?= $order['return_id']; ?>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?= $this->renderFile('orders/genorder/_warranties', array(
                                'order' => $order,
                                'orderWarranties' => $orderWarranties
                            )); ?>
                            <?php if ($request): ?>
                                <div class="from-group clearfix">
                                    <?= l('Заявка') . ' ' . $request['id'] . ' ' . do_nice_date($request['date'],
                                        true) . '<br> '
                                    . '' . l('Звонок') . ' ' . $request['call_id'] . ' ' . do_nice_date($request['call_date'],
                                        true) . ' '
                                    . ($request['code'] ? '<br>Код: ' . $request['code'] : '') . '  '
                                    . ($request['rf_name'] ? '<br>' . l('Источник') . ': ' . $request['rf_name'] . '' : '') . '  ' ?>
                                </div>
                            <?php else: ?>
                                <div
                                    class="form-group clearfix <?= !isset($hide['crm-order-code']) ? 'hide-field' : '' ?>">
                                    <label class="lh30">
                                <span class="cursor-pointer glyphicon glyphicon-list"
                                      onclick="alert_box(this, false, 'changes:update-order-code')"
                                      data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>">

                                </span>
                                        <?= l('Код скидки') ?>:
                                    </label>
                                    <div class="tw100">
                                        <input <?= (!$hasEditorPrivilege ? ' disabled' : '') ?> class="form-control"
                                                                                                type="text"
                                                                                                name="code"
                                                                                                value="<?= htmlspecialchars($order['code']) ?>">
                                    </div>
                                </div>
                                <div class="form-group clearfix <?= !isset($hide['referrer']) ? 'hide-field' : '' ?>">
                                    <label class="lh30">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-referer_id')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
                                        <?= l('Источник') ?>:
                                    </label>

                                    <div class="tw100">
                                        <?= get_service('crm/calls')->get_referers_list($order['referer_id'], '',
                                            !$hasEditorPrivilege, '') ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?= $this->renderFile('orders/genorder/_users_fields', array(
                        'order' => $order,
                        'users_fields' => $users_fields,
                        'hide' => $hide,
                        'showUsersFields' => $showUsersFields
                    )); ?>
                    <?php if ($hasEditorPrivilege): ?>
                        <div class="row-fluid">
                            <div class="span3">
                                <?php
                                $hide = in_array($order['status'],
                                    $this->all_configs['configs']['order-status-issue-btn']) ? ''
                                    : 'style="display:none;"';
                                $status = $order['status'] == $this->all_configs['configs']['order-status-ready'] ?
                                    $this->all_configs['configs']['order-status-issued']
                                    : ($order['status'] == $this->all_configs['configs']['order-status-refused'] || $order['status']
                                    == $this->all_configs['configs']['order-status-unrepairable']
                                        ? $this->all_configs['configs']['order-status-nowork'] : $order['status']);
                                ?>
                                <?php if ($showButtons): ?>
                                    <input id="close-order" <?= $hide ?> class="btn btn-success"
                                           onclick="issue_order(this, 'repair', <?= $order['id'] ?>)"
                                           data-status="<?= $status ?>"
                                           data-debt="<?= $order['sum'] - $order['sum_paid'] - $order['discount'] ?>"
                                           type="button"
                                           value="<?= l('Выдать') ?>"/>
                                    <input id="update-order" class="btn btn-info" onclick="update_order(this)"
                                           data-o_id="<?= $order['id'] ?>" data-alert_box_not_disabled="true"
                                           type="button" value="<?= l('Сохранить') ?>"/>
                                <?php endif; ?>
                            </div>
                            <div class="span9">
                                <div class="from-control clearfix">
                                    <label class="lh30">
                                <span class="cursor-pointer glyphicon glyphicon-list"
                                      onclick="alert_box(this, false, 'changes:update-order-sum')"
                                      data-o_id="<?= $order['id'] ?>"
                                      title="<?= l('История изменений') ?>"></span>
                                        <?= l('Стоимость ремонта') ?>:
                                    </label>
                                    <div class="tw100">
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="order-total" class="form-control"
                                                   value="<?= ($order['sum'] / 100) ?>"
                                                   name="sum" <?= $order['total_as_sum'] ? 'readonly' : '' ?>/>
                                            <div class="input-group-addon"><?= viewCurrency() ?></div>
                                            <div class="input-group-btn">
                                                <?php $pay_btn = ''; ?>
                                                <?php if (intval($order['prepay']) > 0 && intval($order['prepay']) > (intval($order['sum_paid'] + $order['discount']))): ?>
                                                    <input type="button" class="btn btn-success btn-xs  js-pay-button"
                                                           value="<?= ($order['type'] != 3 ? l('Принять предоплату') : l('Принять оплату')) ?>"
                                                           onclick="pay_client_order(this, 'repair', <?= $order['id'] ?>, 0, 'prepay')"/>
                                                <?php elseif (intval($order['sum']) == 0 || intval($order['sum']) > (intval($order['sum_paid'] + $order['discount']))): ?>
                                                    <input type="button"
                                                           class="btn btn-success js-pay-button <?= intval($order['sum']) == 0 ? 'disabled' : '' ?>"
                                                           value="<?= l('Принять оплату') ?>"
                                                           onclick="pay_client_order(this, 'repair', <?= $order['id'] ?>)"/>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row-fluid clearfix">
                                    <div class="col-sm-12" style="text-align: center; padding: 0">
                                        <?php if ($order['cashless']): ?>
                                            <span class="text-danger"><?= l('Безнал') ?></span>
                                        <?php endif; ?>
                                        <?php if ($order['tag_id'] != 0): ?>
                                            <span class="tag"
                                                  style="background-color: <?= $tags[$order['tag_id']]['color'] ?>">
                                    <?= htmlspecialchars($tags[$order['tag_id']]['title']) ?>
                                </span>
                                        <?php endif; ?>
                                        <span class="text-success">
                                <?= l('Оплачено') ?>: <?= ($order['sum_paid'] / 100) ?> <?= viewCurrency() ?>
                                            <?= '(' . l('из них предоплата') ?> <?= ($order['prepay'] / 100) ?> <?= viewCurrency() ?> <?= htmlspecialchars($order['prepay_comment']) . ')' ?>
                                            <?php if ($order['discount'] > 0): ?>
                                                <?= l('Скидка') . ': ' . $order['discount'] / 100 ?> <?= viewCurrency() ?>
                                            <?php endif; ?>
                            </span>
                                    </div>
                                </div>
                                <link type="text/css" rel="stylesheet"
                                      href="<?= $this->all_configs['prefix'] ?>modules/accountings/css/main.css?1">
                                <input id="send-sms" data-o_id="<?= $order['id'] ?>"
                                       onclick="alert_box(this, false, 'sms-form')"
                                       class="hidden" type="button"/>
                            </div>
                        </div>

                    <?php elseif ($onlyEngineer && $order['sum'] == ($order['sum_paid'] + $order['discount']) && $order['sum'] > 0): ?>
                        <b class="text-success"><?= l('Заказ клиентом оплачен') ?></b>
                    <?php endif; ?>
                </div>
                <div class="span6" style="margin-left: 15px">
                    <div class="row-fluid well well-small">
                        <?= $this->renderFile('orders/genorder/_public_comments', array(
                            'comments_public' => $comments_public,
                            'comments_private' => $comments_private,
                            'onlyEngineer' => $onlyEngineer,
                            'modal' => true
                        )); ?>
                        <?= $this->renderFile('orders/genorder/_private_comments', array(
                            'comments_public' => $comments_public,
                            'comments_private' => $comments_private,
                            'onlyEngineer' => $onlyEngineer,
                            'modal' => true
                        )); ?>
                    </div>
                    <?= $this->renderFile('orders/genorder/_spares', array(
                        'onlyEngineer' => $onlyEngineer,
                        'hasEditorPrivilege' => $hasEditorPrivilege,
                        'notSale' => $notSale,
                        'goods' => $goods,
                        'services' => $services,
                        'controller' => $controller,
                        'totalChecked' => $order['total_as_sum'],
                        'total' => $productTotal,
                        'orderId' => $order['id']
                    )); ?>
                </div>
            </form>
        </div>

        <?= $this->all_configs['chains']->append_js(); ?>
        <?= $this->all_configs['suppliers_orders']->append_js(); ?>
    </div>
</div>
<script>
    jQuery(document).ready(function(){
        $('#print_now').on('click', function(){
            return print_now(this);
        });
    });
</script>