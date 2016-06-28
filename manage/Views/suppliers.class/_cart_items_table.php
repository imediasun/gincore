<div class="row-fluid" data-validate="parsley" id="suppliers-order-form-cart" >
    <div class="col-sm-12">
        <table class="table supplier-table-items" style="display:none">
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
            </tbody>
            <tfoot>
            <tr class="row-amount">
                <td>
                    <label><?= l('Итоговая стоимость:') ?></label>
                </td>
                <td></td>
                <td></td>
                <td>
                    <input type="text" readonly class="form-control js-supplier-total" value=""/>
                </td>
                <td></td>
                <td></td>
            </tr>
            </tfoot>
        </table>
    </div>
</div>
