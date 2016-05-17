<div class="row-fluid">
    <div class="order-form-edit-nav toggle-hidden-box">
        <?= $navigation ?>
        <script type="text/javascript">
            $(function () {
                gen_tree();
            });
        </script>
    </div>

    <form method="post" id="order-form" class="clearfix order-form-edit backgroud-white order-form-p-lg">
        <?php $color = preg_match('/^#[a-f0-9]{6}$/i', trim($order['color'])) ? trim($order['color']) : '#000000'; ?>

        <div class="col-sm-12">
            <div class="row-fluid">
                <div class="span3">
                    <h3 class="m-t-none">
                        № <?= $order['id'] ?>
                        <?= $this->renderFile('orders/eshoporder/_print_buttons', array(
                            'hasEditorPrivilege' => $hasEditorPrivilege,
                            'order' => $order
                        )) ?>
                        <button data-o_id="<?= $order['id'] ?>" onclick="alert_box(this, false, 'sms-form')"
                                class="btn btn-default" type="button"><i class="fa fa-mobile"></i> SMS
                        </button>
                    </h3>
                </div>
                <div class="span3">
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
                        <?= $this->renderFile('orders/eshoporder/_order_status', array(
                            'active' => intval($order['status'])
                        )) ?>
                    </div>
                </div>
                <div class="span3">
                    <?= $this->renderFile('orders/genorder/_employers', array(
                        'users' => $managers,
                        'order' => $order,
                        'title' => l('manager'),
                        'type' => 'manager'
                    )); ?>
                </div>
                <div class="span3">
                    <?php if ($request): ?>
                        <div class="from-group clearfix">
                            <?= l('Заявка') . ' ' . $request['id'] . ' ' . do_nice_date($request['date'],
                                true) . '<br> '
                            . '' . l('Звонок') . ' ' . $request['call_id'] . ' ' . do_nice_date($request['call_date'],
                                true) . ' '
                            . ($request['rf_name'] ? '<br>' . l('Источник') . ': ' . $request['rf_name'] . '' : '') . '  ' ?>
                        </div>
                    <?php else: ?>
                        <div class="form-group clearfix <?= !isset($hide['referrer']) ? 'hide-field' : '' ?>">
                            <label class="lh30">
                                    <span class="cursor-pointer glyphicon glyphicon-list"
                                          onclick="alert_box(this, false, 'changes:update-order-referer_id')"
                                          data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>">
                                    </span>
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

            <div class="row-fluid">
                <legend> <?= l('Клиент') ?></legend>
                <div class="col-sm-6">
                    <div class="form-group clearfix">
                        <label class="lh30">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-fio')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>

                        </label>
                        <div class="tw100">
                            <input type="text" value="<?= htmlspecialchars($order['fio']) ?>" name="fio"
                                   class="form-control" placeholder="<?= l('ФИО') ?>"/>
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label class="lh30">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-phone')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
                        </label>
                        <div class="tw100">
                            <input type="text" value="<?= htmlspecialchars($order['phone']) ?>" name="phone"
                                   class="form-control" placeholder="<?= l('Телефон') ?>"/>
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label class="lh30">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-client_email')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
                        </label>
                        <div class="tw100">
                            <input type="text" value="<?= htmlspecialchars($order['c_email']) ?>" name="email"
                                   class="form-control" placeholder="<?= l('Email') ?>"/>
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label class="lh30">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-delivery_by')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
                        </label>
                        <div class="tw100">
                            <?php foreach ($deliveryByList as $id => $name): ?>
                                <label class="radio-inline">
                                    <input type="radio" <?= $order['delivery_by'] == $id ? 'checked' : '' ?>
                                           value="<?= $id ?>" name="delivery_by"
                                           placeholder="<?= l('Способ доставки') ?>"/><?= $name ?>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="form-group clearfix">
                        <label class="lh30">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-delivery_to')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
                        </label>
                        <div class="tw100">
                            <input type="text" value="<?= htmlspecialchars($order['delivery_to']) ?>" name="delivery_to"
                                   class="form-control" placeholder="<?= l('Адрес доставки') ?>"/>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="row-fluid well well-small" style="min-height: 215px">
                        <?= $this->renderFile('orders/eshoporder/_public_comments', array(
                            'comments_public' => $comments_public,
                            'comments_private' => $comments_private,
                            'onlyEngineer' => $onlyEngineer,
                        )); ?>
                        <?= $this->renderFile('orders/eshoporder/_private_comments', array(
                            'comments_public' => $comments_public,
                            'comments_private' => $comments_private,
                            'onlyEngineer' => $onlyEngineer,
                        )); ?>
                    </div>
                </div>
            </div>
            <div class="row-fluid" style="margin-top: 30px">
                <legend>
                    <?= l('Товар') ?>
                </legend>
                <div class="row-fluid ">
                    <?= $this->renderFile('orders/eshoporder/_product_to_cart', array(
                        'from_shop' => false,
                        'order_data' => $order_data,
                        'order_id' => $order['id']
                    )); ?>
                </div>

            </div>
            <div class="row-fluid" style="margin-top: 30px">
                <legend>
                    <span class="cursor-pointer glyphicon glyphicon-list"
                          onclick="alert_box(this, false, 'changes:update-order-cart')"
                          data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"
                          style="font-size: 13px"></span>
                    <?= l('Корзина') ?>
                </legend>
                <div class="col-sm-12" style="margin-bottom: 20px">
                    <table class="<?= !$goods ? 'hidden ' : '' ?> table parts-table cart-table">
                        <?= $this->renderFile('orders/eshoporder/_spares', array(
                            'onlyEngineer' => $onlyEngineer,
                            'hasEditorPrivilege' => $hasEditorPrivilege,
                            'notSale' => $notSale,
                            'goods' => $goods,
                            'controller' => $controller,
                            'totalChecked' => $order['total_as_sum'],
                            'total' => $productTotal,
                            'orderId' => $order['id'],
                            'orderWarranties' => $orderWarranties
                        )); ?>
                        <?php if ($hasEditorPrivilege): ?>
                            <tfoot style="margin-top:40px">
                            <tr>
                                <td colspan="2">
                                    <?php $status = $this->all_configs['configs']['order-status-issued']; ?>
                                    <?php if ($showButtons): ?>
                                        <input id="close-order" class="btn btn-success"
                                               onclick="issue_order(this)" data-status="<?= $status ?>" type="button"
                                               value="<?= l('Выдать') ?>"/>
                                        <input id="update-order" class="btn btn-info" onclick="update_order(this)"
                                               data-o_id="<?= $order['id'] ?>" data-alert_box_not_disabled="true"
                                               type="button" value="<?= l('Сохранить') ?>"/>
                                    <?php endif; ?>
                                </td>
                                <td></td>
                                <td></td>
                                <td>
                                    <label class="lh30">
                                <span class="cursor-pointer glyphicon glyphicon-list"
                                      onclick="alert_box(this, false, 'changes:update-order-sum')"
                                      data-o_id="<?= $order['id'] ?>"
                                      title="<?= l('История изменений') ?>"></span>
                                        <?= l('Стоимость') ?>:
                                    </label>
                                </td>
                                <td>
                                    <input type="text" id="order-total" class="form-control"
                                           value="<?= ($order['sum'] / 100) ?>"
                                           name="sum" <?= $order['total_as_sum'] ? 'readonly' : '' ?>/>
                                </td>
                                <td class="<?= $prefix == 'quick' ? 'col-sm-3' : '' ?>">
                                    <?php $pay_btn = ''; ?>
                                    <?php if (intval($order['prepay']) > 0 && intval($order['prepay']) > intval($order['sum_paid'])): ?>
                                        <input type="button" class="btn btn-success"
                                               value="<?= ($order['type'] != 3 ? l('Принять предоплату') : l('Принять оплату')) ?>"
                                               onclick="pay_client_order(this, 2, <?= $order['id'] ?>, 0, 'prepay')"/>
                                    <?php elseif (intval($order['sum']) == 0 || intval($order['sum']) > intval($order['sum_paid'])): ?>
                                        <input type="button"
                                               class="btn btn-success js-pay-button <?= intval($order['sum']) == 0 ? 'disabled' : '' ?>"
                                               value="<?= l('Принять оплату') ?>"
                                               onclick="pay_client_order(this, 2, <?= $order['id'] ?>)"/>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <link type="text/css" rel="stylesheet"
                                          href="<?= $this->all_configs['prefix'] ?>modules/accountings/css/main.css?1">
                                    <input id="send-sms" data-o_id="<?= $order['id'] ?>"
                                           onclick="alert_box(this, false, 'sms-form')"
                                           class="hidden" type="button"/>
                                </td>
                                <td></td>
                            </tr>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td colspan="3">
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
                            </span>
                                </td>
                                <td></td>
                            </tr>
                            </tfoot>
                        <?php endif; ?>
                    </table>
                </div>
            </div>

            <?php if (!$hasEditorPrivilege && $onlyEngineer && $order['sum'] == $order['sum_paid'] && $order['sum'] > 0): ?>
                <b class="text-success"><?= l('Заказ клиентом оплачен') ?></b>
            <?php endif; ?>
        </div>
    </form>
</div>

<?= $this->all_configs['chains']->append_js(); ?>
<?= $this->all_configs['suppliers_orders']->append_js(); ?>