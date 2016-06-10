<div class="span1">
    <p class="form-control-static"><?= l('manager') ?>:</p>
</div>
<div class="span2">
    <span class="input-group-btn">
    <select <?= ($compact ? ' data-numberDisplayed="0"' : '') ?>
        class="multiselect <?= ($showWrapper ? ' btn-sm ' : '') ?>" name="managers[]" multiple="multiple">
        <?php foreach ($managers as $manager): ?>
            <option <?= ($mg_get && in_array($manager['id'], $mg_get) ? 'selected' : '') ?>
                value="<?= $manager['id'] ?>"><?= htmlspecialchars($manager['name']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
