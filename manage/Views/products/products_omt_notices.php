<form method="post" style="max-width:400px">
    <table class="table table-borderless">
        <tbody>
        <tr>
            <td colspan="2">
                <div class="checkbox">
                    <label>
                        <input <?= ($user && $user['each_sale'] == 1) ? 'checked' : '' ?> type="checkbox" name="each_sale"/>
                        <?= l('уведомлять меня о каждой продаже этого товара') ?>
                    </label>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <label class="checkbox-inline">
                    <input <?= ($user && $user['by_balance'] == 1) ? 'checked' : '' ?> type="checkbox" name="by_balance"/>
                    <?= l('уведомлять меня об остатке') ?>
                </label>
            </td>
            <td>
                <div class="input-group">
                    <input placeholder="<?= l('количество товаров') ?>"
                           value="<?= ($user && $user['balance'] > 0) ? $user['balance'] : ''; ?>"
                           type="text" class="form-control" onkeydown="return isNumberKey(event)"
                           name="balance"/>
                    <div class="input-group-addon"><?= l('или менее единиц.') ?></div>
                </div>
            </td>
        </tr>
        <tr>
            <td>
                <label class="checkbox-inline">
                    <input <?= ($user && $user['by_balance'] == 1) ? 'checked' : '' ?> type="checkbox"
                                                                                       name="use_minimum_balance"/>
                    <?= l('неснижаемый остаток') ?> <?= InfoPopover::getInstance()->createQuestion('l_good_minimum_balance') ?>
                </label>
            </td>
            <td>
                <input placeholder="<?= l('количество товаров') ?>"
                       value="<?= ($user && $user['balance'] > 0) ? $user['balance'] : ''; ?>"
                       type="text" class="form-control" onkeydown="return isNumberKey(event)"
                       name="minimum_balance"/>
            </td>
        </tr>
        <tr>
            <td>
                <label class="checkbox-inline">
                    <input <?= ($user && $user['by_balance'] == 1) ? 'checked' : '' ?> type="checkbox" name="use_automargin"/>
                    <?= l('уведомлять меня об остатке') ?>
                </label>
            </td>
            <td>
                <div class="input-group col-sm-7">
                    <input type="text" class="form-control" value="0"  style="min-width: 50px" name="margin"/>
                    <div class="input-group-addon margin-type" onclick="change_margin_type(this, 'automargin')" style="cursor: pointer">
                        <input type="hidden" class="form-control" value="1" name="automargin_type"/>
                        <span class="currency js-automargin-type" style="display:none"><?= viewCurrency() ?></span>
                        <span class="percent js-automargin-type" >%</span>
                    </div>
                </div>
                <div class="input-group col-sm-7" style="margin-top:5px">
                    <input type="text" class="form-control" value="0"  style="min-width: 50px" name="wholesale-margin"/>
                    <div class="input-group-addon margin-type" onclick="change_margin_type(this, 'wholesale_automargin')" style="cursor: pointer">
                        <input type="hidden" class="form-control" value="1" name="wholesale_automargin_type"/>
                        <span class="currency js-wholesale_automargin-type" style="display:none"><?= viewCurrency() ?></span>
                        <span class="percent js-wholesale_automargin-type" >%</span>
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>
    <?= $btn_save; ?>
</form>
