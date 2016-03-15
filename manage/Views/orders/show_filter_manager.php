<div>
    <div class="<?= $compact ? 'input-group' : 'form-group' ?>">
        <?php if (!$compact): ?>
            <label><?= l('manager') ?>:</label>
        <?php else: ?>
        <p class="form-control-static"><?= l('manager') ?>:</p>
        <span class="input-group-btn">
    <?php endif; ?>
            <select <?= ($compact ? ' data-numberDisplayed="0"' : '') ?>
                class="multiselect <?= ($compact ? ' btn-sm ' : '') ?>" name="managers[]" multiple="multiple">
                <?php foreach ($managers as $manager): ?>
                    <option <?= ($mg_get && in_array($manager['id'], $mg_get) ? 'selected' : '') ?>
                        value="<?= $manager['id'] ?>"><?= htmlspecialchars($manager['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($compact): ?>
        </span>
    <?php endif; ?>
    </div>
</div>
