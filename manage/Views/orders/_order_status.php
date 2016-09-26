<div class="tw100">
    <select class="order-status form-control block-right p60" name="status">
        <?php if (!is_integer($active)): ?>
            <option value="-1"><?= l('Поменять') ?></option>
        <?php endif; ?>
        <?php foreach ($statuses as $k => $status): ?>
            <?php if ($status['active'] || $k == $active): ?>
                <?php $selected = $k == $active ? 'selected' : ''; ?>
                <?php $style = 'style="color:#' . h($status['color']) . '"'; ?>
                <option <?= $selected ?>  <?= $style ?> value="<?= $k ?>"><?= h($status['name']) ?></option>
            <?php endif; ?>
        <?php endforeach; ?>
    </select>
</div>
