<div class="tw100">
    <select class="order-status form-control block-right p60" name="status">
        <?php if (!is_integer($active)): ?>
            <option value="-1"><?= l('Поменять') ?></option>
        <?php endif; ?>
        <?php foreach ($this->all_configs['configs']['sale-order-status'] as $k => $status): ?>
            <?php $selected = $k == $active ? 'selected' : ''; ?>
            <?php $style = 'style="color:#' . htmlspecialchars($status['color']) . '"'; ?>
            <option <?= $selected ?>  <?= $style ?> value="<?= $k ?>"><?= htmlspecialchars($status['name']) ?></option>
        <?php endforeach; ?>
    </select>
</div>
