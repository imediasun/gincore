<div class="row-fluid">

    <div class="order-form-edit-nav toggle-hidden-box">
        <?= $navigation ?>
        <script type="text/javascript">
            $(function () {
                gen_tree();
            });
        </script>
        <div class="hidden js-filters"><?= $saleOrdersFilters ?></div>
    </div>

    <form method="post" id="order-form" class="clearfix order-form-edit backgroud-white order-form-p-lg">
        <?php $color = preg_match('/^#[a-f0-9]{6}$/i', trim($order['color'])) ? trim($order['color']) : '#000000'; ?>

        <div class="col-sm-12">
            <div class="row-fluid">
                <div class="span3" style="max-width: 200px">
                    <h3 class="m-t-none">
                        № <?= $order['id'] ?>
                        <?= $this->renderFile('orders/quicksaleorder/_print_buttons', array(
                            'hasEditorPrivilege' => $hasEditorPrivilege,
                            'order' => $order,
                            'print_templates' => $print_templates
                        )) ?>
                        <button data-o_id="<?= $order['id'] ?>" onclick="alert_box(this, false, 'sms-form')"
                                class="btn btn-default" type="button"><i class="fa fa-mobile"></i> SMS
                        </button>
                    </h3>
                </div>
                <div class="span6" style="line-height: 36px; text-align: left">
                    <div class="form-group">
                        <small style="font-size:10px" title="<?= do_nice_date($order['date_add'], false) ?>">
                            <?= l('Создан') ?>: <?= do_nice_date($order['date_add']) ?>
                        </small>
                        &nbsp;
                        <?php if ($order['np_accept'] == 1): ?>
                            <i title="<?= l('Принято через почту') ?>" class="fa fa-suitcase text-danger"></i>
                        <?php else: ?>
                            <i style="color:<?= $color ?>;" title="<?= l('Принято в сервисном центре') ?>"
                               class="<?= h($order['icon']) ?>"></i>
                        <?php endif; ?>
                        <?= $order['aw_title'] ?>&nbsp;<?= timerout($order['id'], true) ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-12">
            <div class="row-fluid">
                <legend> <?= l('Заказ') ?></legend>
                <div class="col-sm-3">
                    <?php $style = isset($this->all_configs['configs']['order-status'][$order['status']]) ? 'style="color:#' . h($this->all_configs['configs']['order-status'][$order['status']]['color']) . '"' : '' ?>
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
                <div class="col-sm-3">
                    <?= $this->renderFile('orders/genorder/_employers', array(
                        'users' => $managers,
                        'order' => $order,
                        'title' => l('manager'),
                        'type' => 'manager'
                    )); ?>
                </div>
                <div class="col-sm-3">
                    <div class="form-group clearfix" style="line-height: 36px">
                        <label>
                            <span onclick="alert_box(this, false, 'stock_moves-order')"
                                  data-o_id="<?= $order['id'] ?>"
                                  class="cursor-pointer glyphicon glyphicon-list"
                                  title="<?= l('История перемещений') ?>">

                            </span>
                            <?= l('Локации') ?>:
                        </label>
                        <?= h($order['wh_title']) ?>
                        <?= h($order['location']) ?>
                        <i title="<?= l('Переместить заказ') ?>"
                           onclick="alert_box(this, false, 'stock_move-order', undefined, undefined, 'messages.php')"
                           data-o_id="<?= $order['id'] ?>"
                           class="glyphicon glyphicon-move cursor-pointer"></i>
                    </div>
                </div>
            </div>


            <div class="row-fluid">
                <div class="span12">
                    <legend> <?= l('Клиент') ?></legend>
                </div>
            </div>
            <div class="row-fluid">
                <div class="col-sm-4">
                    <div class="form-group clearfix">
                        <label class="lh30">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-fio')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
                        </label>
                        <div class="tw100">
                            <input type="text" value="<?= h($order['fio']) ?>" name="fio"
                                   class="form-control" placeholder="<?= l('ФИО') ?>"/>
                        </div>
                    </div>
                    <?php if ($this->all_configs['configs']['can_see_client_infos']): ?>
                        <div class="form-group clearfix">
                            <label class="lh30">
                            <span class="cursor-pointer glyphicon glyphicon-list"
                                  onclick="alert_box(this, false, 'changes:update-order-phone')"
                                  data-o_id="<?= $order['id'] ?>" title="<?= l('История изменений') ?>"></span>
                            </label>
                            <div class="tw100">
                                <input type="text" value="<?= h($order['phone']) ?>" name="phone"
                                       class="form-control" placeholder="<?= l('Телефон') ?>"/>
                            </div>
                        </div>
                    <?php endif; ?>
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
                    <table class="table parts-table cart-table quick-table-items">
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
                                    <?php $status = $this->all_configs['configs']['order-status-issued']; ?>
                                    <?php if ($showButtons): ?>
                                        <input id="update-order" class="btn btn-info" onclick="update_order(this)"
                                               data-o_id="<?= $order['id'] ?>" data-alert_box_not_disabled="true"
                                               type="button" value="<?= l('Сохранить') ?>"/>
                                    <?php endif; ?>
                                </td>
                                <?php if (!empty($goods)): ?>
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
                                        <input type="text" id="order-total" class="form-control js-quick-total"
                                               value="<?= ($order['sum'] / 100) ?>"
                                               name="sum" <?= $order['total_as_sum'] ? 'readonly' : '' ?>/>
                                    </td>
                                    <td class="<?= $prefix == 'quick' ? 'col-sm-3' : '' ?>">
                                        <?php $pay_btn = ''; ?>
                                        <?php if (intval($order['prepay']) > 0 && intval($order['prepay']) > intval($order['sum_paid'])): ?>
                                            <input type="button" class="btn btn-success"
                                                   value="<?= ($order['type'] != 3 ? l('Принять предоплату') : l('Принять оплату')) ?>"
                                                   onclick="pay_client_order(this, 'sale', <?= $order['id'] ?>, 0, 'prepay')"/>
                                        <?php elseif (intval($order['sum']) == 0 || intval($order['sum']) > intval($order['sum_paid'])): ?>
                                            <input type="button"
                                                   class="btn btn-success js-pay-button <?= intval($order['sum']) == 0 ? 'disabled' : '' ?>"
                                                   value="<?= l('Принять оплату') ?>"
                                                   onclick="pay_client_order(this, 'sale', <?= $order['id'] ?>)"/>
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
                                <?php endif; ?>
                            </tr>
                            <?php if (!empty($goods)): ?>
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
                                    <?= h($tags[$order['tag_id']]['title']) ?>
                                </span>
                                        <?php endif; ?>
                                        <span class="text-success">
                                <?= l('Оплачено') ?>: <?= ($order['sum_paid'] / 100) ?> <?= viewCurrency() ?>
                                            <?= '(' . l('из них предоплата') ?> <?= ($order['prepay'] / 100) ?> <?= viewCurrency() ?> <?= h($order['prepay_comment']) . ')' ?>
                            </span>
                                    </td>
                                    <td></td>
                                </tr>
                            <?php endif; ?>
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