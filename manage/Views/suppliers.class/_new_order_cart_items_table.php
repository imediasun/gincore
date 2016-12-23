<div class="row-fluid" data-validate="parsley" id="suppliers-order-form-cart" style="margin-bottom: 20px">
    <div class="col-sm-12">
        <table class="table supplier-table-items" style="<?= empty($cart) ? 'display:none' : '' ?>">
            <tbody>
            <tr class="js-supplier-row-cloning" style="display: none">
                <td class="col-sm-4">
                    <input type="hidden" class="form-control js-supplier-item-id" name="" value="">
                    <input type="text" readonly class="form-control js-supplier-item-name" value=""/>
                </td>
                <td class="col-sm-1">
                    <input type="text" class="form-control js-supplier-price"
                           onkeyup="recalculate_amount_supplier();" value="" name=""/>
                </td>
                <td class="col-sm-1">
                    <input type="text" class="form-control js-supplier-quantity"
                           onkeyup="recalculate_amount_supplier();" value=""/>
                </td>
                <td class="col-sm-1">
                    <input type="text" class="form-control js-supplier-sum dasabled" readonly
                           onkeyup="recalculate_amount_supplier(this);" value="" name=""/>
                </td>
                <td class="col-sm-1">
                    <input type="text" class="form-control js-supplier-order_numbers dasabled" readonly value=""
                           name=""/>
                </td>
                <td class="col-sm-1">
                    <a href="#" onclick="return remove_supplier_row(this);">
                        <i class="glyphicon glyphicon-remove"></i>
                    </a>
                </td>
                <td class="col-sm-3"></td>
            </tr>

            <?php $total = 0 ?>
            <?php if (!empty($cart)): ?>
                <?php foreach ($cart as $id => $item): ?>
                    <tr class="row-item">
                        <td class="col-sm-4">
                            <input type="hidden" class="form-control js-supplier-item-id" name="item_ids[<?= $id ?>]"
                                   value="<?= $item['id'] ?>">
                            <input type="text" readonly class="form-control js-supplier-item-name"
                                   value="<?= $item['title'] ?>"/>
                        </td>
                        <td class="col-sm-1">
                            <input type="text" class="form-control js-supplier-price"
                                   onkeyup="recalculate_amount_supplier();" value="<?= round($item['price'], 2) ?>"
                                   name="amount[<?= $id ?>]"/>
                        </td>
                        <td class="col-sm-1">
                            <input type="text" class="form-control js-supplier-quantity" name="quantity[<?= $id ?>]"
                                   onkeyup="recalculate_amount_supplier();"  value="<?= $item['quantity'] ?>"/>
                        </td>
                        <td class="col-sm-1">
                            <input type="text" class="form-control js-supplier-sum disabled" readonly
                                   onkeyup="recalculate_amount_supplier(this);"
                                   value="<?= $item['price'] * $item['quantity'] ?>"/>
                            <?php $total += $item['price'] * $item['quantity'] ?>
                        </td>
                        <td class="col-sm-1">
                            <input type="text" class="form-control js-supplier-order_numbers disabled" readonly value=""
                                   name=""/>
                        </td>
                        <td class="col-sm-2">
                            <a href="#" onclick="return remove_supplier_row(this);">
                                <i class="glyphicon glyphicon-remove"></i>
                            </a>
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
                <td></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
