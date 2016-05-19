<tr class="<?= $group && $quantity > 1 ? 'items-group ' : '' ?> <?= $hide ? $hash . '_item' : $hash . '_group row-item' ?>"
    style="<?= $hide ? 'display:none' : '' ?>">
    <td>
        <?php if ($group && $quantity > 1): ?>
            <i class="fa fa-chevron-right items-show" aria-hidden="true" onclick="toggle_items('<?= $hash ?>')"></i>
            <i class="fa fa-chevron-down items-hide" aria-hidden="true" onclick="toggle_items('<?= $hash ?>')"
               style="display: none"></i>
        <?php endif; ?>
    </td>
    <td class="col-sm-3">
        <a href="<?= $url ?>">
            <?= htmlspecialchars($product['title']) ?>
        </a>
    </td>
    <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
        <td class="col-sm-1">
            <input class="form-control global-typeahead input-medium popover-info js-eshop-price"
                   type="text" value="<?= ($product['price'] / 100) ?>"
                   name="product[<?= $product['id'] ?>][price]" onkeyup="recalculate_amount_eshop();"/>
        </td>
    <?php endif; ?>
    <td class="col-sm-1" style="min-width: 100px">
        <div class="input-group">
            <input class="form-control global-typeahead input-medium popover-info js-eshop-discount"
                   type="text" value="<?= ($product['discount']) ?>" name="product[<?= $product['id'] ?>][discount]"
                   onkeyup="recalculate_amount_eshop();"/>
            <div class="input-group-addon" onclick="change_discount_type(this)" style="cursor: pointer">
                <input class='js-product-discount-type js-eshop-discount-type' type="hidden"
                       name="product[<?= $product['id'] ?>][discount_type]" value="<?= $product['discount_type'] ?>"/>
                <span class="currency"
                      style="display:<?= $product['discount_type'] == DISCOUNT_TYPE_PERCENT ? 'none' : '' ?>"><?= viewCurrency() ?></span>
                <span class="percent"
                      style="display:<?= $product['discount_type'] != DISCOUNT_TYPE_PERCENT ? 'none' : '' ?>">%</span>
            </div>
        </div>
    </td>
    <td class="col-sm-1">
        <input readonly class="form-control global-typeahead input-medium popover-info disable js-eshop-quantity"
               type="text" value="<?= isset($quantity) ? $quantity : $product['count'] ?>"
               onkeyup="recalculate_amount_eshop();"/>
    </td>
    <td class="col-sm-1">
        <input readonly class="form-control global-typeahead input-medium popover-info disable js-eshop-sum"
               type="text" value="<?= sum_with_discount($product, $quantity) ?>"/>
    </td>
    <td class="col-sm-1" style="min-width: 110px">
        <?= $this->renderFile('orders/eshoporder/_warranty_select', array(
            'product' => $product,
            'orderWarranties' => $orderWarranties
        )); ?>
    </td>
    <td class="col-sm-1">
        <?php if ($this->all_configs['oRole']->hasPrivilege('edit-clients-orders')): ?>
            <i title="<?= l('удалить') ?>" class="glyphicon glyphicon-remove remove-product"
               onclick="order_products(this, <?= $product['goods_id'] ?>, '<?= $product['id'] ?>', 1, 1, 1)"></i>
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
                            <?php $avail_bind = true; ?>
                            <?php if ($group && $quantity > 1): ?>
                                <?php $bind_action = "bind_group_product(this,'{$hash}','{$product['order_id']}')"; ?>
                            <?php else: ?>
                                <?php $bind_action = "bind_product(this,'{$product['goods_id']}')"; ?>
                            <?php endif; ?>
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
                            <span title="<?= do_nice_date($product['date_come'], false) ?>">
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
        <td colspan="2"></td>
    <?php endif; ?>
</tr>
