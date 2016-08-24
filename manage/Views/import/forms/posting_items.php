<div class="form-group">
    <div class="checkbox">
        <label>
            <input type="checkbox" name="accepter_as_manager" value="1">
            <?= l('назначить приемщика менеджером, если последний не указан') ?>
        </label>
    </div>
</div>
<div class="form-group">
    <select class="form-control" name="contractor" required>
        <option value="0"><?= l('Выберите поставщика') ?> </option>
        <?php if (!empty($contractors)): ?>
            <?php foreach ($contractors as $id => $contractor): ?>
                <option value="<?= $id ?>"><?= h($contractor) ?> </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>
<div class="form-group">
    <select class="form-control" name="location" required>
        <option value="0"><?= l('Выберите Склад(локация)') ?> </option>
        <?php if (!empty($locations)): ?>
            <?php foreach ($locations as $id => $location): ?>
                <option value="<?= $id ?>"><?= h($location) ?> </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>
