<!--  Notifications-->
<div class="hpanel panel-collapse">
    <div class="panel-heading hbuilt">
        <div class="panel-tools">
            <a class="showhide"><i class="fa fa-chevron-up"></i></a>
        </div>
        <?= l('Упр. закупками') ?>
    </div>
    <div class="panel-body" style="display: none;">

        <?php $disabled_row = $this->all_configs['oRole']->hasPrivilege('external-marketing') ? '' : 'disabled'; ?>

        <div class="form-group">
            <label><?= l('Розничная цена') ?> (<?= viewCurrency('shortName') ?>): </label>
            <input <?= $disabled_row ?> onkeydown="return isNumberKey(event, this)" placeholder="<?= l('цена') ?>"
                                        class="form-control" name="price"
                                        value="<?= number_format($product['price'] / 100, 2, '.', '') ?>"/>
        </div>
        <?php
        $disabled_row = '';
        if (!$this->all_configs['oRole']->hasPrivilege('external-marketing') ||
            $this->all_configs['configs']['onec-use'] || $this->all_configs['configs']['erp-use']
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

        <div class="form-group m-t-lg m-l-sm">
            <label class="checkbox-inline">
                <input <?= ($product['use_automargin'] == 1) ? 'checked' : '' ?> type="checkbox" name="use_automargin"/>
                <?= l('Автонаценка') ?> <?= InfoPopover::getInstance()->createQuestion('l_good_automargin') ?>
            </label>
        </div>

        <table class="table table-borderless">
            <tbody>
            <tr>
                <td width="30%">
                    <div class="input-group" style="width:150px">
                        <input type="text" class="form-control" value="<?= $product['automargin'] ?>"  style="min-width: 50px" name="automargin"/>
                        <div class="input-group-addon margin-type" onclick="change_margin_type(this, 'automargin')" style="cursor: pointer">
                            <input type="hidden" class="form-control" value="<?= $product['automargin_type'] ?>" name="automargin_type"/>
                            <span class="currency js-automargin-type"  <?= $product['automargin_type']? 'style="display:none"':'' ?>><?= viewCurrency() ?>&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i></span>
                            <span class="percent js-automargin-type"  <?= !$product['automargin_type']? 'style="display:none"':'' ?>>%&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i></span>
                        </div>
                    </div>
                </td>
                <td>
                    <?= l('Розница') ?>
                </td>
            </tr>
            <tr>
                <td>
                    <div class="input-group" style="width:150px">
                        <input type="text" class="form-control" value="<?= $product['wholesale_automargin'] ?>"  style="min-width: 50px" name="wholesale_automargin"/>
                        <div class="input-group-addon margin-type" onclick="change_margin_type(this, 'wholesale_automargin')" style="cursor: pointer">
                            <input type="hidden" class="form-control" value="<?= $product['wholesale_automargin_type'] ?>" name="wholesale_automargin_type"/>
                            <span class="currency js-wholesale_automargin-type" <?= $product['wholesale_automargin_type']? 'style="display:none"':'' ?>><?= viewCurrency() ?>&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i></span>
                            <span class="percent js-wholesale_automargin-type"  <?= !$product['wholesale_automargin_type']? 'style="display:none"':'' ?>>%&nbsp;<i class="fa fa-caret-down" aria-hidden="true"></i></span>
                        </div>
                    </div>
                </td>
                <td>
                    <?= l('Опт') ?>
                </td>
            </tr>
            </tbody>
        </table>

    </div>
</div>