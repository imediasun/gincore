<div class="input-group">
    <p class="form-control-static" style="display: inline-block; margin-right: 10px; white-space: nowrap"><?= l('manager') ?>:</p>
    <span class="input-group-btn" style="margin-left: 10px">
    <select <?= ($compact ? ' data-numberDisplayed="0"' : '') ?>
        class="multiselect <?= ($showWrapper ? ' btn-sm ' : '') ?>" name="managers[]" multiple="multiple">
        <?php foreach ($managers as $manager): ?>
            <option <?= ($mg_get && in_array($manager['id'], $mg_get) ? 'selected' : '') ?>
                value="<?= $manager['id'] ?>"><?= htmlspecialchars($manager['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
