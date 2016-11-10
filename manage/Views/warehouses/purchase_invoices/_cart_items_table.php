<div class="row-fluid" data-validate="parsley" id="suppliers-order-form-cart" style="margin-bottom: 20px">
    <div class="col-sm-12">
        <table class="table supplier-table-items" style="<?= empty($goods) ? 'display:none' : '' ?>">
            <tbody>
            <tr class="js-supplier-row-cloning" style="display: none">
                <?php if (!empty($goods)): ?>
                    <td></td>
                <?php endif; ?>
                <td class="col-sm-5">
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
                <?php if (empty($goods)): ?>
                    <td class="col-sm-1">
                        <a href="#" onclick="return remove_supplier_row(this);">
                            <i class="glyphicon glyphicon-remove"></i>
                        </a>
                    </td>
                <?php endif; ?>
            </tr>

            <?php $total = 0 ?>
            <?php if (!empty($goods)): ?>
                <?php foreach ($goods as $id => $good): ?>
                    <tr class="row-item">
                        <td><?= empty($good['good_id']) ? $good['not_found'] : '' ?></td>
                        <td class="col-sm-3">
                            <input type="hidden" class="form-control js-supplier-item-id" name="item_ids[<?= $id ?>]"
                                   value="<?= $good['good_id'] ?>">
                            <input type="text" readonly class="form-control js-supplier-item-name"
                                   value="<?= $good['product'] ?>"/>
                            <input type="hidden" readonly name="edited[<?= $id ?>]" value="<?= $id ?>"/>
                        </td>
                        <td class="col-sm-2">
                            <input type="text" class="form-control js-supplier-price"
                                   onkeyup="recalculate_amount_supplier();"
                                   value="<?= round($good['price'] / 100, 2) ?>"
                                   name="amount[<?= $id ?>]"/>
                        </td>
                        <td class="col-sm-2">
                            <input type="text" class="form-control js-supplier-quantity" name="quantity[<?= $id ?>]"
                                   onkeyup="recalculate_amount_supplier();" value="<?= $good['quantity'] ?>"/>
                        </td>
                        <td class="col-sm-2">
                            <input type="text" class="form-control js-supplier-sum disabled" readonly
                                   onkeyup="recalculate_amount_supplier(this);"
                                   value="<?= $good['price'] / 100 * $good['quantity'] ?>"/>
                            <?php $total += $good['price'] / 100 * $good['quantity'] ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
            <tfoot>
            <tr class="row-amount">
                <td colspan="<?= empty($goods) ? 3 : 4 ?>">
                    <label><?= l('Итоговая стоимость:') ?></label>
                </td>
                <td>
                    <input type="text" readonly class="form-control js-supplier-total" value="<?= $total ?>"/>
                </td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
