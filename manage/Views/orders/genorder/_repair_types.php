<select class="form-control" name="repair" >
    <option <?= $order['repair'] == 0 ? 'selected' : ''; ?> value="pay">
        <?= l('Платный') ?>
    </option>
    <option <?= $order['repair'] == 2 ? 'selected' : ''; ?> value="rework">
        <?= l('Доработка') ?>
    </option>
    <?php if (!empty($brands)): ?>
        <?php foreach ($brands as $id => $title): ?>
            <option <?= ($order['repair'] == 1 && $order['brand_id'] == $id)? 'selected' : ''; ?>
                value="<?= $id ?>">
                <?= l('Гарантийный') . ' ' . h($title) ?>
            </option>
        <?php endforeach; ?>
    <?php endif; ?>
</select>
