<?php if ($product): ?>
    <?php $disabled_row = $all_configs['oRole']->hasPrivilege('external-marketing') ? '' : 'disabled'; ?>

    <form method="post">
        <div class="form-group">
            <label><?= l('Розничная цена') ?> (<?= viewCurrency('shortName') ?>): </label>
            <input <?= $disabled_row ?> onkeydown="return isNumberKey(event, this)" placeholder="<?= l('цена') ?>"
                                        class="form-control" name="price"
                                        value="<?= number_format($product['price'] / 100, 2, '.', '') ?>"/>
        </div>
        <?php
        $disabled_row = '';
        if (!$all_configs['oRole']->hasPrivilege('external-marketing') ||
            $all_configs['configs']['onec-use'] || $all_configs['configs']['erp-use']
        ) {
            $disabled_row = 'disabled';
        } ?>

        <?php if (array_key_exists('use-goods-old-price',
                $this->all_configs['configs']) && $this->all_configs['configs']['use-goods-old-price']
        ): ?>
            <div class="form-group">
                <label><?= l('Старая цена') ?> (<?= viewCurrency('shortName') ?>): </label>
                <input placeholder="<?= l('старая цена') ?>" <?= $disabled_row; ?>
                       onkeydown="return isNumberKey(event, this)" class="form-control" name="old_price"
                       value="<?= number_format($product['old_price'] / 100, 2, '.', '') ?>"/>
            </div>
        <?php endif; ?>
        <div class="form-group">
            <label><?= l('Оптовая цена') ?> (<?= viewCurrency('shortName') ?>): </label>
            <input placeholder="<?= l('оптовая цена') ?>" onkeydown="return isNumberKey(event, this)"
                   class="form-control" name="price_wholesale"
                   value="<?= number_format($product['price_wholesale'] / 100, 2, '.', '') ?>"/>
        </div>
        <div class="form-group">
            <label><?= l('Закупочная цена последней партии') ?> (<?= viewCurrencySuppliers('shortName') ?>): </label>
            <input placeholder="<?= l('закупочная цена') ?>" <?= $disabled_row ?>
                   onkeydown="return isNumberKey(event, this)" class="form-control" name="price_purchase"
                   value="<?= number_format($product['price_purchase'] / 100, 2, '.', '') ?>"/>
        </div>
        <div class="form-group">
            <label><?= l('Свободный остаток') ?>:</label>
            <input <?= $disabled_row ?> placeholder="<?= l('количество') ?>" onkeydown="return isNumberKey(event)"
                                        class="form-control" name="exist" value="<?= $product['qty_store'] ?>"/>
        </div>
        <div class="form-group">
            <label><?= l('Общий остаток') ?>:</label>
            <input <?= $disabled_row ?> placeholder="<?= l('количество') ?>" onkeydown="return isNumberKey(event)"
                                        class="form-control" name="qty_wh" value="<?= $product['qty_wh'] ?>"/>
        </div>
        <?= $btn_save_product ?>
    </form>
<?php endif; ?>
