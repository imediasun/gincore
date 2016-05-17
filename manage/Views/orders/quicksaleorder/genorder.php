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
                        <?= $this->renderFile('orders/quicksaleorder/_print_buttons', array(
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
                        <?= $this->renderFile('orders/quicksaleorder/_order_status', array(
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
            </div>

            <div class="row-fluid">
                <div class="span12">
                    <legend> <?= l('Клиент') ?></legend>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span4">
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
                </div>
            </div>
            <div class="row-fluid">
                <legend>
                    <span class="cursor-pointer glyphicon glyphicon-list"
                          onclick="alert_box(this, false, 'changes:update-order-cart')"
                          data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"
                          style="font-size: 13px"></span>
                    <?= l('Корзина') ?>
                </legend>
                <div class="col-sm-12">
                    <table class="<?= !$goods ? 'hidden ' : '' ?> table parts-table cart-table">
                    <?= $this->renderFile('orders/quicksaleorder/_spares', array(
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
                                               onclick="issue_order(this)" data-status="<?= $status ?>" type="button"
                                               value="<?= l('Выдать') ?>"/>
                                        <input id="update-order" class="btn btn-info" onclick="update_order(this)"
                                               data-o_id="<?= $order['id'] ?>" data-alert_box_not_disabled="true"
                                               type="button" value="<?= l('Сохранить') ?>"/>
                                    <?php endif; ?>
                                </td>
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