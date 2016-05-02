<select class="order-status form-control" name="status">
    <?php if (!is_integer($active)): ?>
        <option value="-1"><?= l('Поменять') ?></option>
    <?php endif; ?>
    <?php foreach ($orderStates as $k => $status): ?>
        <option <?= $k === $active ? 'selected' : '' ?> style="color:#<?= htmlspecialchars($status['color']) ?>"
                                                        value="<?= $k ?>">
            <?= htmlspecialchars($status['name']) ?>
        </option>
    <?php endforeach; ?>
</select>
