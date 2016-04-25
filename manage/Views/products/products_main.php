<?php if ($product): ?>
    <form method="post">
        <div class="form-group">
            <label><?= l('Название') ?>: </label>
            <input class="form-control" placeholder="<?= l('введите название') ?>" name="title"
                   value="<?= (is_array($errors) && array_key_exists('post', $errors) && array_key_exists('title',
                           $errors['post'])) ? htmlspecialchars($errors['post']['title']) : htmlspecialchars($product['title']); ?>"/>
        </div>
        <div class="form-group">
            <label><?= l('Штрих код') ?> : </label>
            <input placeholder="<?= l('штрих код') ?>" class="form-control" name="barcode"
                   value="<?= ((is_array($errors) && array_key_exists('post', $errors) && array_key_exists('title',
                           $errors['post'])) ? htmlspecialchars($errors['post']['barcode']) : $product['barcode']) ?>"/>
        </div>
        <div class="form-group">
            <label><?= l('Приоритет') ?>: </label>
            <input onkeydown="return isNumberKey(event)" class="form-control" name="prio"
                   value="<?= ((is_array($errors) && array_key_exists('post', $errors) && array_key_exists('prio',
                           $errors['post'])) ? htmlspecialchars($errors['post']['prio']) : $product['prio']) ?>"/>
        </div>
        <div class="form-group">
            <label><?= l('Розничная цена') ?> (<?= viewCurrency('shortName') ?>): </label>
            <?= number_format($product['price'] / 100, 2, '.', ' ') ?>
        </div>
        <div class="form-group">
            <label><?= l('Закупочная цена последней партии') ?> (<?= viewCurrencySuppliers('shortName') ?>): </label>
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
        <?= $btn_save; ?>
    </form>
<?php endif; ?>
