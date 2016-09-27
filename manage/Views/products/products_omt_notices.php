<form method="post" style="max-width:550px">
    <table class="table table-borderless">
        <tbody>
        <tr>
            <td colspan="3">
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
            <td colspan="2">
                <div class="input-group">
                    <input placeholder="<?= l('количество товаров') ?>"
                           value="<?= ($user && $user['balance'] > 0) ? $user['balance'] : ''; ?>"
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
                                                                                       name="use_minimum_balance"/>
                    <?= l('неснижаемый остаток') ?> <?= InfoPopover::getInstance()->createQuestion('l_good_minimum_balance') ?>
                </label>
            </td>
            <td colspan="2">
                <input placeholder="<?= l('количество товаров') ?>"
                       value="<?= $product['minimum_balance'] ?>"
                       type="text" class="form-control" onkeydown="return isNumberKey(event)"
                       name="minimum_balance" style="width: 150px;"/>
            </td>
        </tr>
        <tr>
            <td>
                <label class="checkbox-inline">
                    <input <?= ($product['use_automargin'] == 1) ? 'checked' : '' ?> type="checkbox" name="use_automargin"/>
                    <?= l('Автонаценка') ?> <?= InfoPopover::getInstance()->createQuestion('l_good_automargin') ?>
                </label>
            </td>
            <td width="30%">
                <div class="input-group" style="width:150px">
                    <input type="text" class="form-control" value="<?= $product['automargin'] ?>"  style="min-width: 50px" name="automargin"/>
                    <div class="input-group-addon margin-type" onclick="change_margin_type(this, 'automargin')" style="cursor: pointer">
                        <input type="hidden" class="form-control" value="<?= $product['automargin_type'] ?>" name="automargin_type"/>
                        <span class="currency js-automargin-type"  <?= $product['automargin_type']? 'style="display:none"':'' ?>><?= viewCurrency() ?></span>
                        <span class="percent js-automargin-type"  <?= !$product['automargin_type']? 'style="display:none"':'' ?>>%</span>
                    </div>
                </div>
            </td>
            <td>
                <?= l('Розница') ?>
            </td>
        </tr>
        <tr>
            <td>
            </td>
            <td>
                <div class="input-group" style="width:150px">
                    <input type="text" class="form-control" value="<?= $product['wholesale_automargin'] ?>"  style="min-width: 50px" name="wholesale_automargin"/>
                    <div class="input-group-addon margin-type" onclick="change_margin_type(this, 'wholesale_automargin')" style="cursor: pointer">
                        <input type="hidden" class="form-control" value="<?= $product['wholesale_automargin_type'] ?>" name="wholesale_automargin_type"/>
                        <span class="currency js-wholesale_automargin-type" <?= $product['wholesale_automargin_type']? 'style="display:none"':'' ?>><?= viewCurrency() ?></span>
                        <span class="percent js-wholesale_automargin-type"  <?= !$product['wholesale_automargin_type']? 'style="display:none"':'' ?>>%</span>
                    </div>
                </div>
            </td>
            <td>
                <?= l('Опт') ?>
            </td>
        </tr>
        </tbody>
    </table>
    <?= $btn_save; ?>
</form>
