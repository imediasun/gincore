<div class="row-fluid" data-validate="parsley" id="suppliers-order-form-cart" style="margin-bottom: 20px">
    <div class="col-sm-12">
        <table class="table supplier-table-items" style="<?= empty($orders) ? 'display:none' : '' ?>">
            <tbody>
            <tr class="js-supplier-row-cloning" style="display: none">
                <td class="col-sm-3">
                    <input type="hidden" class="form-control js-supplier-item-id" name="" value="">
                    <input type="text" readonly class="form-control js-supplier-item-name" value=""/>
                </td>
                <td class="col-sm-2">
                    <input type="text" class="form-control js-supplier-price"
                           onkeyup="recalculate_amount_supplier();" value="" name=""/>
                </td>
                <td class="col-sm-2">
                    <input type="text" class="form-control js-supplier-quantity"
                           onkeyup="recalculate_amount_supplier();" value=""/>
                </td>
                <td class="col-sm-2">
                    <input type="text" class="form-control js-supplier-sum dasabled" readonly
                           onkeyup="recalculate_amount_supplier(this);" value="" name=""/>
                </td>
                <td>
                    <input type="text" class="form-control js-supplier-order_numbers dasabled" readonly value=""
                           name=""/>
                </td>
                <td class="col-sm-1">
                    <a href="#" onclick="return remove_supplier_row(this);">
                        <i class="glyphicon glyphicon-remove"></i>
                    </a>
                </td>
            </tr>

            <?php $total = 0 ?>
            <?php if (!empty($orders)): ?>

                <?php foreach ($orders as $id => $order): ?>
                    <?php $readonly = ($order['count'] == $order['count_come'])? 'readonly': ''; ?>
                    <tr class="row-item">
                        <td class="col-sm-3">
                            <input type="hidden" class="form-control js-supplier-item-id" name="item_ids[<?= $id ?>]"
                                   value="<?= $order['goods_id'] ?>">
                            <input type="text" readonly class="form-control js-supplier-item-name"
                                   value="<?= $order['product'] ?>"/>
                            <input type="hidden" readonly name="edited[<?= $id ?>]" value="<?= $id ?>"/>
                        </td>
                        <td class="col-sm-2">
                            <input type="text" class="form-control js-supplier-price"
                                   onkeyup="recalculate_amount_supplier();" <?= $readonly ?> value="<?= round($order['price'], 2) ?>"
                                   name="amount[<?= $id ?>]"/>
                        </td>
                        <td class="col-sm-2">
                            <input type="text" class="form-control js-supplier-quantity" name="quantity[<?= $id ?>]"
                                   onkeyup="recalculate_amount_supplier();" <?= $readonly ?> value="<?= $order['count'] ?>"/>
                        </td>
                        <td class="col-sm-2">
                            <input type="text" class="form-control js-supplier-sum disabled" readonly
                                   onkeyup="recalculate_amount_supplier(this);"
                                   value="<?= $order['price'] * $order['count'] ?>"/>
                            <?php $total += $order['price'] * $order['count'] ?>
                        </td>
                        <td>
                            <input type="hidden" class="form-control js-supplier-order_numbers disabled"
                                   value="<?= implode(',', $order['cos']) ?>"
                                   name="so_co[<?= $id ?>]"/>
                            <input type='text' class="form-control js-supplier-order_numbers" style="background-color: #eeeeee"  value="<?= implode(',', $order['cos']) ?>" />
                        </td>
                        <td class="col-sm-2">
                            <?php if ($order['count_come'] > 0): ?>
                                <div>
                                    <label><?= l('Принято') ?>:&nbsp;</label>
                                    <?= $order['count_come'] ?>&nbsp;<?= l('шт.') ?>
                                </div>
                            <?php endif; ?>
                            <?php if ($order['count_debit'] > 0): ?>

                                <div>
                                    <label><?= l('Оприходовано') ?>:&nbsp;</label>
                                    <?= $order['count_debit'] ?>&nbsp;<?= l('шт.') ?>
                                    <?php $url = $this->all_configs['prefix'] . 'print.php?act=label&object_id=' . $order['items']; ?>
                                    <a target="_blank" title="Печать" href="<?= $url ?>"><i class="fa fa-print"></i></a>
                                </div>
                            <?php endif; ?>
                            <?php if ($order['wh_id'] > 0): ?>
                                <div>
                                    <label><?= l('Склад') ?>:&nbsp;</label><br>
                                    <a class="hash_link" href="<?= $this->all_configs['prefix'] ?>warehouses?whs=<?= $order['wh_id'] ?>#show_items">
                                        <?= $order['wh_title'] ?>, <?= $order['location'] ?>
                                    </a>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
            <tfoot>
            <tr class="row-amount">
                <td>
                    <label><?= l('Итоговая стоимость:') ?></label>
                </td>
                <td></td>
                <td></td>
                <td>
                    <input type="text" readonly class="form-control js-supplier-total" value="<?= $total ?>"/>
                </td>
                <td></td>
                <td></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
