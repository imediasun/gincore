<tr>
    <td class="col-sm-5">
        <a href="<?= $url ?>" data-action="sidebar_product" data-id_product="<?= $product['goods_id'] ?>">
            <?= htmlspecialchars($product['title']) ?>
        </a>
    </td>
    <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
        <td class="col-sm-2">
            <form method="POST" onsubmit="return false;">
                <div class="input-group floating-width">
                    <input class="form-control global-typeahead input-medium popover-info visible-price"
                           type="text" onkeypress="change_input_width(this, this.value.length);"
                           value="<?= ($product['price'] / 100) ?>"/>
                    <div class="input-group-btn" style="display:none">
                        <button class="btn btn-info"
                                onclick="return change_visible_prices(this, <?= $product['id'] ?>)">
                            <span class="glyphicon glyphicon-ok"></span>&nbsp;
                        </button>
                    </div>
                </div>
            </form>
        </td>
    <?php endif; ?>
    <td class="col-sm-1">
        <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
            <i title="<?= l('удалить') ?>" class="glyphicon glyphicon-remove remove-product"
               onclick="order_products(this, <?= $product['goods_id'] ?>, <?= $product['id'] ?>, 1, 1 <?= ($supplier_order['count'] == 1 && $supplier_order['confirm'] != 1) ? ', 1' : '' ?>)"></i>
        <?php endif; ?>
    </td>
    <?php if ($product['type'] == 0): ?>

        <?php if ($product['item_id'] > 0): ?>
            <td><?= suppliers_order_generate_serial($product, true, true) ?>
                <?php if ($product['so_id'] > 0): ?>
                    <a href="<?= $this->all_configs['prefix'] ?>orders/edit/<?= $product['so_id'] ?>#create_supplier_order">
                        <small class="muted">№<?= $product['so_id'] ?></small>
                    </a>
                <?php endif; ?>
            </td>
            <td>
                <?php if (!strtotime($product['unbind_request']) && $this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
                    <i title="<?= l('отвязать') ?>" class="glyphicon glyphicon-minus cursor-pointer"
                       onclick="btn_unbind_request_item_serial(this, '<?= $product['item_id'] ?>')"></i>
                <?php else: ?>
                    <?= $controller->get_unbind_order_product_btn($product['item_id']); ?>
                <?php endif; ?>
            </td>
        <?php else: ?>
            <td colspan="2" class="col-sm-4">
                <div class="order_product clearfix">
                    <div class="text-info">
                        <?php
                        $create_role = $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders');
                        $accept_role = $this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders');
                        $bind_role = $this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders');
                        $role_alert = "alert('" . l('У Вас недостаточно прав для этой операции') . "')";
                        $avail_create = $avail_accept = $avail_bind = false;
                        $accept_action = $bind_action = $create_action = '';
                        $accept_data = '';
                        ?>

                        <?php if ($product['unavailable'] == 1): ?>
                            <?= l('Запчасть не доступна к заказу') ?>
                            <?php if ($product['so_id'] > 0): ?>
                                <a href="<?= $this->all_configs['prefix'] ?>orders/edit/<?= $product['so_id'] ?>#create_supplier_order">
                                    <small class="muted">№<?= $product['so_id'] ?></small>
                                </a>
                            <?php endif; ?>
                        <?php elseif ($product['count_debit'] > 0): ?>
                            <?php
                            $avail_bind = true;
                            $bind_action = 'bind_product(this,' . $product['goods_id'] . ')';
                            ?>
                            <?= l('Ожидание отгрузки запчасти') ?>
                            <span title="<?= do_nice_date($product['date_debit'], false) ?>">
                                <?= do_nice_date($product['date_debit']) ?>
                            </span>
                            <?php if ($product['so_id'] > 0): ?>
                                <a href="<?= $this->all_configs['prefix'] ?>orders/edit/<?= $product['so_id'] ?>#create_supplier_order">
                                    <small class="muted">№<?= $product['so_id'] ?></small>
                                </a>
                            <?php endif; ?>
                        <?php elseif ($product['count_come'] > 0): ?>
                            <?php
                            $avail_accept = true;
                            $accept_action = "alert_box(this,false,'form-debit-so',{},null,'warehouses/ajax/')";
                            $accept_data = ' data-o_id="' . $supplier_order['id'] . '"';
                            ?>
                            <?= l('Запчасть была принята') ?>
                            <span title="' . do_nice_date($product['date_come'], false) . '">
                                <?= do_nice_date($product['date_come']) ?>
                            </span>
                            <?php if ($product['so_id'] > 0): ?>
                                <a href="<?= $this->all_configs['prefix'] ?>orders/edit/<?= $product['so_id'] ?>#create_supplier_order">
                                    <small class="muted">№<?= $product['so_id'] ?></small>
                                </a>
                            <?php endif; ?>
                        <?php elseif ($product['supplier'] > 0): ?>
                            <?php
                            $avail_accept = true;
                            $accept_action = "alert_box(this, false, 'form-accept-so-and-debit')";
                            $accept_data = ' data-o_id="' . $supplier_order['id'] . '"';
                            ?>

                            <?= l('Запчасть заказана') ?>
                            (<?= l('заказ поставщику') ?> № <a
                                href="<?= $this->all_configs['prefix'] ?>orders/edit/<?= $product['so_id'] ?>#create_supplier_order">
                                <small class="muted"><?= $product['so_id'] ?></small>
                            </a>)
                            <?= l('Дата поставки') ?>
                            <span title="<?= do_nice_date($product['date_wait'], false) ?>">
                                <?= do_nice_date($product['date_wait']) ?>
                            </span>
                        <?php elseif ($product['count_order'] > 0): ?>
                            <?php
                            $date_attach = $controller->getOrderSuppliersClientsDateAdd($product);
                            $avail_create = true;
                            $create_action = 'show_suppliers_order(this, ' . $supplier_order['id'] . ')';
                            ?>
                            <span title="<?= do_nice_date($date_attach, false) ?>">
                                <?= do_nice_date($date_attach) ?>
                            </span>
                            <?= l('Отправлен запрос на закупку') ?>
                            <?php if ($product['so_id'] > 0): ?>
                                <a href="<?= $this->all_configs['prefix'] ?>orders/edit/<?= $product['so_id'] ?>#create_supplier_order">
                                    <small class="muted">№<?= $product['so_id'] ?></small>
                                </a>
                            <?php endif; ?>
                            <?= l('от') ?>
                            <span title="<?= do_nice_date($product['date_add'], false) ?>">
                                <?= do_nice_date($product['date_add']) ?>
                            </span>
                        <?php else: ?>
                            <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
                                <input style="width:100%" type="button"
                                       data-order_product_id="<?= $product['id'] ?>" class="btn btn-small"
                                       onclick="order_item(this)" value="<?= l('Заказать') ?>"/>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="order_product_menu">
                        <button style="min-width:30px" type="button" class="btn btn-primary btn-sm dropdown-toggle"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <span class="caret"></span>
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>
                                <a data-alert_box_not_disabled="true"
                                   class="<?= (!$avail_create || !$create_role ? 'text-muted' : '') ?>"
                                   onclick="<?= ($create_role ? $create_action : $role_alert) ?>;return false;">
                                    <i class="fa fa-pencil"></i> <?= l('Создать заказ поставщику') ?>
                                </a>
                            </li>
                            <li>
                                <a data-alert_box_not_disabled="true" <?= $accept_data ?>
                                   class="<?= (!$avail_accept || !$accept_role ? 'text-muted' : '') ?>"
                                   onclick="<?= ($accept_role ? $accept_action : $role_alert) ?>;return false;">
                                    <i class="fa fa-wrench"></i>
                                    <?= l('Принять и оприходовать заказ') ?>
                                </a>
                            </li>
                            <li>
                                <a data-alert_box_not_disabled="true"
                                   class="<?= (!$avail_bind || !$bind_role ? 'text-muted' : '') ?>"
                                   onclick="<?= ($bind_role ? $bind_action : $role_alert) ?>; return false;">
                                    <i class="fa fa-random"></i> <?= l('Отгрузить деталь под ремонт') ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </td>
        <?php endif; ?>
    <?php else: ?>
        <td class="col-sm-2"></td>
        <td class="col-sm-2" style="text-align: center">
            <?php if (!empty($engineers)): ?>
                <div class="btn-group js-repair-order-column-filter" style="margin-left: 5px">
                    <a class="dropdown-toggle" data-toggle="dropdown" href="#"
                       id="user_<?= $product['id'] ?>"  title="<?= get_user_name($engineers[$product['engineer']]) ?>">
                        <i class="fa fa-user" aria-hidden="true"
                           style="color: <?= empty($colors[$product['engineer']])? $colors[$order_engineer]: $colors[$product['engineer']] ?>"></i>
                        <i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu pull-right" style="max-height: 600px;">
                        <?php foreach ($engineers as $engineer): ?>
                            <li style="padding: 0 10px; white-space: nowrap">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="engineer_<?= $product['id'] ?>"
                                               onclick="return set_engineer_of_service(this);"
                                               value="<?= $engineer['id'] ?>" <?= $engineer['id'] == $product['engineer'] || (empty($product['engineer']) && $engineer['id'] == $order_engineer) ? 'checked' : '' ?>
                                               data-service_id="<?= $product['id'] ?>"
                                               data-color="<?= $colors[$engineer['id']] ?>"
                                        />

                                        <?= get_user_name($engineer) ?>
                                        <?php if(!empty($engineer['workload_by_service']) || !empty($engineer['workload_by_order'])): ?>
                                            (<?= sprintf(l('загруженность')." %d&nbsp;". l('ремонт').', '.l('из них ожидают запчастей и на согласовании').': %d', (int)$engineer['workload_by_service'] + $engineer['workload_by_order'], $engineer['wait_parts_o'] + (int)$engineer['wait_parts_s']) ?>)
                                        <?php endif; ?>
                                    </label>
                                </div>
                            </li>
                        <?php endforeach; ?>

                    </ul>
                </div>
            <?php endif; ?>
        </td>
    <?php endif; ?>
</tr>
<script>
    function set_engineer_of_service(_this) {
        var id = $(_this).attr('data-service_id'), color;
        $.ajax({
            url: prefix + module + '/ajax/?act=set-engineer-of-service',
            type: 'POST',
            dataType: "json",
            data: {
                engineer_id: $(_this).val(),
                service_id: id
            },
            success: function (msg) {
                if (msg && msg['state']) {
                    color = $(_this).attr('data-color') || '#ddd';
                    $('#user_' + id + ' >.fa-user').css('color', color);
                }
            }
        });
    }
    function getRandomInt(min, max)
    {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }
</script>