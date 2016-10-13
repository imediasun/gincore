<!--     Main info-->
<div class="hpanel panel-collapse">
    <div class="panel-heading hbuilt">
        <div class="panel-tools">
            <a class="showhide"><i class="fa fa-chevron-up"></i></a>
        </div>
        <?= l('Основная информация') ?>
    </div>
    <div class="panel-body" style="display: none;">
        <div class="form-group">
            <label><?= l('Название') ?>: </label>
            <input class="form-control" placeholder="<?= l('введите название') ?>" name="title"
                   value="<?= (is_array($errors) && array_key_exists('post',
                           $errors) && array_key_exists('title',
                           $errors['post'])) ? h($errors['post']['title']) : h($product['title']); ?>"/>
        </div>
        <div class="form-group">
            <label><?= l('Штрих код') ?> : </label>
            <input placeholder="<?= l('штрих код') ?>" class="form-control" name="barcode"
                   value="<?= ((is_array($errors) && array_key_exists('post',
                           $errors) && array_key_exists('title',
                           $errors['post'])) ? h($errors['post']['barcode']) : $product['barcode']) ?>"/>
        </div>
        <div class="form-group">
            <label><?= l('Артикул') ?> : </label>
            <input placeholder="<?= l('Артикул') ?>" class="form-control" name="vendor_code"
                   value="<?= ((is_array($errors) && array_key_exists('post',
                           $errors) && array_key_exists('vendor_code',
                           $errors['post'])) ? h($errors['post']['vendor_code']) : $product['vendor_code']) ?>"/>
        </div>
        <div class="form-group">
            <label><?= l('Приоритет') ?>: </label>
            <input onkeydown="return isNumberKey(event)" class="form-control" name="prio"
                   value="<?= ((is_array($errors) && array_key_exists('post',
                           $errors) && array_key_exists('prio',
                           $errors['post'])) ? h($errors['post']['prio']) : $product['prio']) ?>"/>
        </div>
        <div class="form-group">
            <label><?= l('Розничная цена') ?> (<?= viewCurrency('shortName') ?>): </label>
            <?= number_format($product['price'] / 100, 2, '.', ' ') ?>
        </div>
        <div class="form-group">
            <label><?= l('Закупочная цена последней партии') ?> (<?= viewCurrencySuppliers('shortName') ?>
                ): </label>
            <?= number_format($product['price_purchase'] / 100, 2, '.', ' ') ?>
        </div>
        <div class="form-group">
            <label><?= l('Оптовая цена') ?> (<?= viewCurrency('shortName') ?>): </label>
            <?= number_format($product['price_wholesale'] / 100, 2, '.', ' ') ?>
        </div>
        <div class="form-group">
            <label><?= l('Свободный остаток') ?>:</label>
            <?= intval($product['qty_store']) ?>
        </div>
        <div class="form-group">
            <label><?= l('Общий остаток') ?>:</label>
            <?= intval($product['qty_wh']) ?>
        </div>
    </div>
</div>