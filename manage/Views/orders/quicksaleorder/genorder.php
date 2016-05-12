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

        <div class="span6">
            <div class="bordered">
                <div class="row-fluid">

                    <div class="span4">
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
                    <div class="span4">
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
                    </div>
                    <div class="span4">
                        <?= $this->renderFile('orders/genorder/_employers', array(
                            'users' => $managers,
                            'order' => $order,
                            'title' => l('manager'),
                            'type' => 'manager'
                        )); ?>
                    </div>
                </div>

                <div class="row-fluid">
                    <div class="span3">
                        <div class="form-group clearfix">
                            <label class="lh30">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-fio')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
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
                    </div>
                </div>
            </div>
            <div class="row-fluid bordered">
                <div class="span12">
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
                </div>
            </div>
            <?php if ($hasEditorPrivilege): ?>
                <div class="row-fluid">
                    <div class="span3">
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
                                        <?php if (intval($order['prepay']) > 0 && intval($order['prepay']) > intval($order['sum_paid'])): ?>
                                            <input type="button" class="btn btn-success btn-xs"
                                                   value="<?= ($order['type'] != 3 ? l('Принять предоплату') : l('Принять оплату')) ?>"
                                                   onclick="pay_client_order(this, 2, <?= $order['id'] ?>, 0, 'prepay')"/>
                                        <?php elseif (intval($order['sum']) == 0 || intval($order['sum']) > intval($order['sum_paid'])): ?>
                                            <input type="button"
                                                   class="btn btn-success js-pay-button <?= intval($order['sum']) == 0 ? 'disabled' : '' ?>"
                                                   value="<?= l('Принять оплату') ?>"
                                                   onclick="pay_client_order(this, 2, <?= $order['id'] ?>)"/>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row-fluid clearfix">
                            <div class="col-sm-12" style="text-align: right; padding: 0">
                                <?php if ($order['cashless']): ?>
                                    <span class="text-danger"><?= l('Безнал') ?></span>
                                <?php endif; ?>
                                <?php if ($order['tag_id'] != 0): ?>
                                    <span class="tag" style="background-color: <?= $tags[$order['tag_id']]['color'] ?>">
                                    <?= htmlspecialchars($tags[$order['tag_id']]['title']) ?>
                                </span>
                                <?php endif; ?>
                                <span class="text-success">
                                <?= l('Оплачено') ?>: <?= ($order['sum_paid'] / 100) ?> <?= viewCurrency() ?>
                                    <?= '(' . l('из них предоплата') ?> <?= ($order['prepay'] / 100) ?> <?= viewCurrency() ?> <?= htmlspecialchars($order['prepay_comment']) . ')' ?>
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

            <?php elseif ($onlyEngineer && $order['sum'] == $order['sum_paid'] && $order['sum'] > 0): ?>
                <b class="text-success"><?= l('Заказ клиентом оплачен') ?></b>
            <?php endif; ?>
        </div>
    </form>
</div>

<?= $this->all_configs['chains']->append_js(); ?>
<?= $this->all_configs['suppliers_orders']->append_js(); ?>