<div class="row-fluid">

    <div class="order-form-edit-nav toggle-hidden-box">
        <?= $navigation ?>
        <script type="text/javascript">
            $(function () {
                gen_tree();
            });
        </script>
    </div>

    <form method="post" id="order-form" class="clearfix order-form-edit backgroud-white p-lg">
        <?php $color = preg_match('/^#[a-f0-9]{6}$/i', trim($order['color'])) ? trim($order['color']) : '#000000'; ?>

        <div class="span6">
            <div class="row-fluid">
                <div class="span6">
                    <h3 class="m-t-none">
                        № <?= $order['id'] ?>
                        <?= $this->renderFile('orders/genorder/_print_buttons', array(
                            'hasEditorPrivilege' => $hasEditorPrivilege,
                            'order' => $order
                        )) ?>
                        <button data-o_id="' . $order['id'] . '" onclick="alert_box(this, false, 'sms-form')"
                                class="btn btn-default" type="button"><i class="fa fa-mobile"></i> SMS
                        </button>
                    </h3>
                </div>
                <div class="span6">
                    <div class="form-group center">
                        <small style="font-size:10px" title="<?= do_nice_date($order['date_add'], false) ?>">
                            <?= l('Принят') ?>: <?= do_nice_date($order['date_add']) ?>
                        </small>
                        <br>
                        <?php if (mb_strlen($order['courier'], 'UTF-8') > 0): ?>
                            <i style="color:<?= $color ?>;" title="<?= l('Курьер забрал устройство у клиента') ?>"
                               class="fa fa-truck"></i>
                        <?php endif; ?>
                        <?php if ($order['np_accept'] == 1): ?>
                            <i title="<?= l('Принято через почту') ?>" class="fa fa-suitcase text-danger"></i>
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
                    <?php if (!$onlyEngineer): ?>
                        <div class="form-group">
                            <label>
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-fio')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
                                <?= l('Заказчик') ?>:
                            </label>
                            <input type="text" value="<?= htmlspecialchars($order['fio']) ?>" name="fio"
                                   class="form-control"/>
                        </div>
                        <div class="form-group">
                            <label>
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-phone')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
                                <?= l('Телефон') ?>:
                            </label>
                            <input type="text" value="<?= htmlspecialchars($order['phone']) ?>" name="phone"
                                   class="form-control"/></div>
                        <div class="form-group">
                            <label>
                            <span class="cursor-pointer glyphicon glyphicon-list" title="<?= l('История изменений') ?>"
                                  data-o_id="<?= $order['id'] ?>"
                                  onclick="alert_box(this, false, 'changes:update-order-category')"></span>
                                <i class="glyphicon glyphicon-picture cursor-pointer" data-o_id="<?= $order['id'] ?>"
                                   onclick="alert_box(this, null, 'order-gallery')"></i>
                                <?= l('Устройство') ?>:
                            </label>
                            <?= typeahead($this->all_configs['db'], 'categories-goods', false, $order['category_id'], 4,
                                'input-medium') ?>
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?= l('Цвет') ?>: </label>
                            <select class="form-control" name="color">
                                <?php if (is_null($order['o_color'])): ?>
                                    <option value="-1" selected disabled><?= l('Не выбран') ?></option>
                                <?php endif; ?>
                                <?php foreach ($this->all_configs['configs']['devices-colors'] as $id => $color): ?>
                                    <option <?= (!is_null($order['o_color']) && $order['o_color'] == $i ? ' selected' : '') ?>
                                        value="<?= $id ?>">
                                        <?= $color ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <?= htmlspecialchars($order['note']) ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($notSale): ?>
                        <?php if (!$onlyEngineer): ?>
                            <div class="form-group">
                                <label>
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-serial')"
                                  data-o_id="<?= $order['id'] ?>"
                                  title="<?= l('История изменений') ?>">

                            </span>
                                    S/N:
                                </label>
                                <input type="text" value="<?= htmlspecialchars($order['serial']) ?>" name="serial"
                                       class="form-control"/>
                            </div>
                        <?php endif; ?>
                        <div class="form-group">
                            <label><?= l('Комлектация') ?>:</label><br>
                            <?= implode(', ', $parts) ?>
                        </div>

                        <div class="form-group">
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
                        <div class="form-group">
                            <label><?= l('Сроки') ?>:</label>
                            <?= ($order['urgent'] == 1 ? l('Срочный') : l('Не срочный')) ?>
                        </div>
                        <div class="form-group">
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
                        <div class="form-group">
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
                        <div class="form-group">
                            <label><?= l('Ориентировочная дата готовности') ?>: </label>
                            <span title="<?= do_nice_date($order['date_readiness'],
                                false) ?>"><?= do_nice_date($order['date_readiness']) ?></span>
                        </div>
                        <?php if ($hasEditorPrivilege): ?>
                            <div class="form-group"><label><?= l('Ориентировочная стоимость') ?>: </label>
                                <?= ($order['approximate_cost'] / 100) ?> <?= viewCurrency() ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="span6">
                    <div class="form-group">
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

                    <div class="form-group">
                        <label><?= l('Приемщик') ?>:</label>
                        <?= get_user_name($order, 'a_') ?>
                    </div>
                    <?php $style = isset($this->all_configs['configs']['order-status'][$order['status']]) ? 'style="color:#' . htmlspecialchars($this->all_configs['configs']['order-status'][$order['status']]['color']) . '"' : '' ?>
                    <div class="form-group">
                        <label>
                            <span <?= $style ?>></span>
                                <span class="cursor-pointer glyphicon glyphicon-list"
                                      title="<?= l('История перемещений') ?>"
                                      data-o_id="<?= $order['id'] ?>"
                                      onclick="alert_box(this, false, 'order-statuses')">

                                </span>
                            <?= l('Статус') ?>:
                        </label>
                        <?= $this->all_configs['chains']->order_status(intval($order['status'])) ?>
                    </div>
                    <?php if ($notSale): ?>
                        <?php if ($order['manager'] == 0 && $hasEditorPrivilege): ?>
                            <div class="form-group">
                                <label> <?= l('manager') ?>: </label>
                                <input type="submit" name="accept-manager" class="accept-manager btn btn-default btn-xs"
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

                        <div class="form-group">
                            <span style="margin:4px 10px 0 0"
                                  class="pull-left cursor-pointer glyphicon glyphicon-list muted"
                                  onclick="alert_box(this, false, 'changes:update-order-client_took')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>">
                            </span>
                            <label class="checkbox-inline">
                                <input type="checkbox" value="1" <?= ($order['client_took'] == 1 ? 'checked' : '') ?>
                                       name="client_took">
                                <?= l('Устройство у клиента') ?>
                            </label>
                        </div>
                        <?php $onclick = 'if ($(this) . prop(\'checked\')){$(\'.replacement_fund\').val(\'\');$(\'.replacement_fund\').prop(\'disabled\', false);$(\'.replacement_fund\').show();$(this).parent().parent().addClass(\'warning\');}else{$(\'.replacement_fund\').hide();$(this).parent().parent().removeClass(\'warning\');}'; ?>
                        <div class="form-group <?= ($order['is_replacement_fund'] == 1 ? ' warning' : '') ?>">
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
                        <div class="form-group">
                            <label class="checkbox-inline">
                                <input type="checkbox" value="1" <?= ($order['nonconsent'] == 1 ? 'checked' : '') ?>
                                       name="nonconsent"/>
                                <?= l('Можно пускать в работу без согласования') ?>
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-inline">
                                <input type="checkbox" value="1" <?= ($order['is_waiting'] == 1 ? 'checked' : '') ?>
                                       name="is_waiting"/>
                                <?= l('Клиент готов ждать 2-3 недели запчасть') ?>
                            </label>
                        </div>

                        <?php if ($order['return_id'] > 0 || $this->all_configs['oRole']->hasPrivilege('edit_return_id')): ?>
                            <div class="form-group">
                                <label><?= l('Номер возврата') ?>: </label>
                                <?php if ($this->all_configs['oRole']->hasPrivilege('edit_return_id')): ?>
                                    <?= $order['id'] ?>-<input type="text" value="<?= $order['return_id'] ?>"
                                                               name="return_id" class="form-control"/>
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
                        <div class="from-group">
                            <?= l('Заявка') . ' ' . $request['id'] . ' ' . do_nice_date($request['date'],
                                true) . '<br> '
                            . '' . l('Звонок') . ' ' . $request['call_id'] . ' ' . do_nice_date($request['call_date'],
                                true) . ' '
                            . ($request['code'] ? '<br>Код: ' . $request['code'] : '') . '  '
                            . ($request['rf_name'] ? '<br>' . l('Источник') . ': ' . $request['rf_name'] . '' : '') . '  ' ?>
                        </div>
                    <?php else: ?>
                        <div class="from-group">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-code')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>">

                            </span>
                            <label><?= l('Код скидки') ?>:</label>
                            <input <?= (!$hasEditorPrivilege ? ' disabled' : '') ?> class="form-control" type="text"
                                                                                    name="code"
                                                                                    value="<?= htmlspecialchars($order['code']) ?>"><br>
                        </div>
                        <div class="from-group">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-referer_id')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
                            <label><?= l('Источник') ?>:</label>
                            <?= get_service('crm/calls')->get_referers_list($order['referer_id'], '',
                                !$hasEditorPrivilege) ?>
                            <br>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php if ($hasEditorPrivilege): ?>
                <div class="row-fluid">
                    <div class="span6">
                        <?php
                        $hide = in_array($order['status'], $this->all_configs['configs']['order-status-issue-btn']) ? ''
                            : 'style="display:none;"';
                        $status = $order['status'] == $this->all_configs['configs']['order-status-ready'] ?
                            $this->all_configs['configs']['order-status-issued']
                            : ($order['status'] == $this->all_configs['configs']['order-status-refused'] || $order['status']
                            == $this->all_configs['configs']['order-status-unrepairable']
                                ? $this->all_configs['configs']['order-status-nowork'] : $order['status']);
                        ?>
                        <?php if ($showButtons): ?>
                            <input id="close-order" <?= $hide ?> class="btn btn-success"
                                   onclick="issue_order(this)" data-status="<?= $status ?>" type="button"
                                   value="<?= l('Выдать') ?>"/>
                            <input id="update-order" class="btn btn-info" onclick="update_order(this)"
                                   data-o_id="<?= $order['id'] ?>" data-alert_box_not_disabled="true"
                                   type="button" value="<?= l('Сохранить') ?>"/>
                        <?php endif; ?>
                    </div>
                    <div class="span6">
                        <div class="from-control">
                        <span class="cursor-pointer glyphicon glyphicon-list"
                              onclick="alert_box(this, false, 'changes:update-order-sum')"
                              data-o_id="<?= $order['id'] ?>"
                              title="<?= l('История изменений') ?>"></span>
                            <label><?= l('Стоимость ремонта') ?>: </label>
                            <div class="input-group input-group-sm">
                                <input type="text" id="order-total" class="form-control"
                                       value="<?= ($order['sum'] / 100) ?>" name="sum"/>
                                <div class="input-group-addon"><?= viewCurrency() ?></div>
                                <div class="input-group-btn">
                                    <?php $pay_btn = ''; ?>
                                    <?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
                                        <?php if (intval($order['prepay']) > 0 && intval($order['prepay']) > intval($order['sum_paid'])): ?>
                                            <input type="button" class="btn btn-success btn-xs"
                                                   value="<?= ($order['type'] != 3 ? l('Принять предоплату') : l('Принять оплату')) ?>"
                                                   onclick="pay_client_order(this, 2, <?= $order['id'] ?>, 0, 'prepay')"/>
                                        <?php elseif (intval($order['sum']) > intval($order['sum_paid'])): ?>
                                            <input type="button" class="btn btn-success"
                                                   value="<?= l('Принять оплату') ?>"
                                                   onclick="pay_client_order(this, 2, <?= $order['id'] ?>)"/>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                </div>
                            </div>
                        <span class="text-success">
                            <?= l('Оплачено') ?>: <?= ($order['sum_paid'] / 100) ?> <?= viewCurrency() ?>
                            (<?= l('из них предоплата') ?> <?= ($order['prepay'] / 100) ?> <?= viewCurrency() ?> <?= htmlspecialchars($order['prepay_comment']) ?>
                            )
                        </span>
                            <small id="product-total"><?= ($productTotal / 100) ?> <?= viewCurrency() ?></small>
                        </div>
                        <link type="text/css" rel="stylesheet"
                              href="'.$this->all_configs['prefix'].'modules/accountings/css/main.css?1">
                        <input id="send-sms" data-o_id="<?= $order['id'] ?>"
                               onclick="alert_box(this, false, 'sms-form')"
                               class="hidden" type="button"/>
                    </div>
                </div>

            <?php elseif ($onlyEngineer && $order['sum'] == $order['sum_paid'] && $order['sum'] > 0): ?>
                <b class="text-success"><?= l('Заказ клиентом оплачен') ?></b>
            <?php endif; ?>
        </div>
        <div class="span6">
            <div class="row-fluid well well-small">
                <?= $this->renderFile('orders/genorder/_public_comments', array(
                    'comments_public' => $comments_public,
                    'comments_private' => $comments_private,
                    'onlyEngineer' => $onlyEngineer,
                )); ?>
                <?= $this->renderFile('orders/genorder/_private_comments', array(
                    'comments_public' => $comments_public,
                    'comments_private' => $comments_private,
                    'onlyEngineer' => $onlyEngineer,
                )); ?>
            </div>
            <?= $this->renderFile('orders/genorder/_spares', array(
                'onlyEngineer' => $onlyEngineer,
                'hasEditorPrivilege' => $hasEditorPrivilege,
                'notSale' => $notSale,
                'goods' => $goods,
                'services' => $services,
                'controller' => $controller
            )); ?>
        </div>
    </form>
</div>

<?= $this->all_configs['chains']->append_js(); ?>
<?= $this->all_configs['suppliers_orders']->append_js(); ?>