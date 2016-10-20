<!--  Notifications-->
<div class="hpanel panel-collapse">
    <div class="panel-heading hbuilt showhide">
        <div class="panel-tools">
            <i class="fa fa-chevron-up"></i>
        </div>
        <?= l('Уведомления') ?>
    </div>
    <div class="panel-body" style="display: none;">
        <table class="table table-borderless">
            <tbody>
            <tr>
                <td colspan="3">
                    <div class="checkbox">
                        <label>
                            <input <?= ($notifications && $notifications['each_sale'] == 1) ? 'checked' : '' ?> type="checkbox" name="each_sale"/>
                            <?= l('уведомлять меня о каждой продаже этого товара') ?>
                        </label>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="checkbox-inline">
                        <input <?= ($notifications && $notifications['by_balance'] == 1) ? 'checked' : '' ?> type="checkbox" name="by_balance"/>
                        <?= l('уведомлять меня об остатке') ?>
                    </label>
                </td>
                <td colspan="2">
                    <div class="input-group">
                        <input placeholder="<?= l('количество товаров') ?>"
                               value="<?= ($notifications && $notifications['balance'] > 0) ? $notifications['balance'] : ''; ?>"
                               type="text" class="form-control" onkeydown="return isNumberKey(event)"
                               name="balance" style="width: 150px;"/>
                        <div class="input-group-addon"><?= l('или менее единиц.') ?></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td>
                    <label class="checkbox-inline">
                        <input <?= ($product['use_minimum_balance'] == 1) ? 'checked' : '' ?> type="checkbox"
                                                                                              name="use_minimum_balance" value="on"/>
                        <?= l('неснижаемый остаток') ?>
                        &nbsp;<i class="fa fa-question-circle" data-toggle="tooltip" title="<?= l('l_good_minimum_balance') ?>" ></i>
                    </label>
                </td>
                <td colspan="2">
                    <input placeholder="<?= l('количество товаров') ?>"
                           value="<?= $product['minimum_balance'] ?>"
                           type="text" class="form-control" onkeydown="return isNumberKey(event)"
                           name="minimum_balance" style="width: 150px;"/>
                </td>
            </tr>
            </tbody>
        </table>

    </div>
</div>