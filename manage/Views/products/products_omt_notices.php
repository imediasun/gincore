<form method="post" style="max-width:400px">
    <div class="form-group">
        <div class="checkbox">
            <label>
                <input <?= ($user && $user['each_sale'] == 1) ? 'checked' : '' ?> type="checkbox" name="each_sale"/> 
                <?= l('уведомлять меня о каждой продаже этого товара') ?> 
            </label>
        </div>
    </div>
    <div class="form-group">
        <label class="checkbox-inline">
            <input <?= ($user && $user['by_balance'] == 1) ? 'checked' : '' ?> type="checkbox" name="by_balance"/>
            <?= l('уведомлять меня об остатке') ?>
        </label>
        <div class="input-group">
            <input placeholder="<?= l('количество товаров') ?>"
                   value="<?= ($user && $user['balance'] > 0) ? $user['balance'] : ''; ?>"
                   type="text" class="form-control" onkeydown="return isNumberKey(event)"
                   name="balance"/>
            <div class="input-group-addon"><?= l('или менее единиц.') ?></div>
        </div>
    </div>
    <?= $btn_save; ?>
</form>
