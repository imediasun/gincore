<div class="row-fluid" data-validate="parsley" id="suppliers-order-form-cart" style="margin-bottom: 20px">
    <div class="col-sm-12">
        <table class="table supplier-table-items" style="<?= empty($invoices) ? 'display:none' : '' ?>">
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
            <?php if (!empty($invoices)): ?>

                <?php foreach ($invoices as $id => $invoice): ?>
                    <?php $readonly = ($invoice['count'] == $invoice['count_come'])? 'readonly': ''; ?>
                    <tr class="row-item">
                        <td class="col-sm-3">
                            <input type="hidden" class="form-control js-supplier-item-id" name="item_ids[<?= $id ?>]"
                                   value="<?= $invoice['goods_id'] ?>">
                            <input type="text" readonly class="form-control js-supplier-item-name"
                                   value="<?= $invoice['product'] ?>"/>
                            <input type="hidden" readonly name="edited[<?= $id ?>]" value="<?= $id ?>"/>
                        </td>
                        <td class="col-sm-2">
                            <input type="text" class="form-control js-supplier-price"
                                   onkeyup="recalculate_amount_supplier();" <?= $readonly ?> value="<?= round($invoice['price'], 2) ?>"
                                   name="amount[<?= $id ?>]"/>
                        </td>
                        <td class="col-sm-2">
                            <input type="text" class="form-control js-supplier-quantity" name="quantity[<?= $id ?>]"
                                   onkeyup="recalculate_amount_supplier();" <?= $readonly ?> value="<?= $invoice['count'] ?>"/>
                        </td>
                        <td class="col-sm-2">
                            <input type="text" class="form-control js-supplier-sum disabled" readonly
                                   onkeyup="recalculate_amount_supplier(this);"
                                   value="<?= $invoice['price'] * $invoice['quantity'] ?>"/>
                            <?php $total += $invoice['price'] * $invoice['quantity'] ?>
                        </td>
                        <td>
                            <input type="hidden" class="form-control js-supplier-order_numbers disabled"
                                   value="<?= implode(',', $invoice['cos']) ?>"
                                   name="so_co[<?= $id ?>]"/>
                            <input type="text" class="form-control js-supplier-order_numbers" style="background-color: #eeeeee"
                                   value="<?= implode(',', $invoice['cos']) ?>"
                            />
                        </td>
                        <td class="col-sm-2">
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
