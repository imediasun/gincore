<?php if (!empty($order)): ?>
    <form method="POST" id="sms-form">
        <div class="form-group">
            <label><?= l('Номер телефона') ?>: </label>
            <div class="controls">
                <input class="form-control" name="phone" type="text"
                       value="<?= htmlspecialchars($order['phone']) ?>"/>
            </div>
        </div>
        <div class="form-group">
            <label class="control-label"><?= l('Текст') ?>: </label>
            <div class="controls">
                <textarea class="form-control show-length" maxlength="69" name="text">
                    <?= l('Ваш заказ')?> №<?= $order['id'] ?> <?= l('готов') ?>. <?= l('Стоимость ремонта') ?>: <?= ($order['sum'] / 100) ?> <?= viewCurrency() ?>
                </textarea>
            </div>
        </div>
        <input type="hidden" name="order_id" value="<?= $order_id ?>"/>
    </form>
<?php else: ?>
    <p><?= l('Заказ не найден') ?></p>
<?php endif; ?>
