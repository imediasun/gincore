<p class="label label-info"><?= l('Отобразить') ?></p>
<div class="well">
    <ul style="padding-left:25px">
        <li>
            <label class="checkbox">
                <input type="checkbox" <?= $controller->click_filters('show', 'my') ?>>
                <?= l('Мои товары') ?>
            </label>
        </li>
        <li>
            <label class="checkbox">
                <input type="checkbox" <?= $controller->click_filters('show', 'empty'); ?>>
                <?= l('Не заполненные') ?>
            </label>
        </li>
        <li>
            <label class="checkbox">
                <input type="checkbox" <?= $controller->click_filters('show', 'services'); ?>>
                <?= l('Услуги') ?>
            </label>
        </li>
        <li>
            <label class="checkbox">
                <input type="checkbox" <?= $controller->click_filters('show', 'items'); ?>>
                <?= l('Товары') ?>
            </label>
        </li>
    </ul>
</div>

<p class="label label-info"><?= l('По складам') ?></p>
<div class="well">
    <ul style="padding-left:25px">
        <?php if ($warehouses): ?>
            <?php foreach ($warehouses as $wh_id => $wh_title): ?>
                <li>
                    <label class="checkbox">
                        <input type="checkbox" name="warehouse" value="<?= $wh_id ?>" <?= $controller->click_filters('wh',
                            $wh_id) ?>>
                        <?= htmlspecialchars($wh_title) ?>
                    </label>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>
</div>
