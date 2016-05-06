<form id="form-accept-so" method="post">

    <div class="form-group"><label"><?= l('Количество') ?>: </label>
        <div class="controls">
            <input class="form-control" type="text" name="count" placeholder="<?= l('Количество') ?>"/></div>
    </div>

    <div class="form-group"><label"><?= l('Склад') ?>: </label>
        <div class="controls">
            <?php if ($warehouses): ?>
                <select name="wh_id" onchange="change_warehouse(this)"
                        class="form-control select-warehouses-item-move">
                    <option value=""></option>
                    <?php foreach ($warehouses as $wh_id => $wh_title): ?>
                        <?php $selected = $order && $wh_id == $order['wh_id'] ? 'selected' : ''; ?>
                        <option <?= $selected ?> value="<?= $wh_id ?>"><?= htmlspecialchars($wh_title) ?></option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <p class="text-danger"><?= l('Нет складов') ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="form-group"><label><?= l('Локация') ?>:</label>
        <div class="controls">
            <select class="multiselect select-location form-control" name="location">
                <?= $controller->gen_locations($order ? $order['wh_id'] : 0); ?>
            </select></div>
    </div>

    <div class="form-group">
        <label><?= l('Дата проверки') ?>: </label>
        <div class="controls">
            <input class="form-control datetimepicker" placeholder="<?= l('Дата проверки') ?>"
                   data-format="yyyy-MM-dd hh:mm:ss" type="text" name="date_check" value=""/>
        </div>
    </div>
    <div class="form-group">
        <div class="checkbox">
            <label class="">
                <input type="checkbox" name="without_check" value="1"/>
                <?= l('Без проверки') ?>
            </label>
        </div>
    </div>

    <div id="order_supplier_date_wait" style="display:none;" class="form-group">
        <label class="control-label"><?= l('Дата поставки оставшегося в заказе товара') ?>: </label>
        <div class="controls">
            <input class="form-control datetimepicker" placeholder="<?= l('дата') ?>" data-format="yyyy-MM-dd" type="text"
                   name="date_come" value=""/>
        </div>
    </div>

    <input type="hidden" name="order_id" value="<?= $order['id'] ?>"/>
</form>
