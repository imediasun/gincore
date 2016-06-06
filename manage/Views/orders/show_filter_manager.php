<?php if ($showWrapper): ?>
    <div style="z-index: 1">
<?php endif; ?>
    <div class="span5">
<?php if (!$compact): ?>
    <label><?= l('manager') ?>:</label>
<?php else: ?>
    <p class="form-control-static"><?= l('manager') ?>:</p>
    </div>
    <div class="span6">
    <span class="input-group-btn">
<?php endif; ?>
    <select <?= ($compact ? ' data-numberDisplayed="0"' : '') ?>
        class="multiselect <?= ($showWrapper ? ' btn-sm ' : '') ?>" name="managers[]" multiple="multiple">
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
<?php if ($showWrapper): ?>
    </div>
<?php endif; ?>