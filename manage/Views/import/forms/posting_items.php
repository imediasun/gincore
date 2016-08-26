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
        <option value="0"><?= l('Выберите поставщика, от которого принимаем товар') ?> </option>
        <?php if (!empty($contractors)): ?>
            <?php foreach ($contractors as $id => $contractor): ?>
                <option value="<?= $id ?>"><?= h($contractor) ?> </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>
<div class="form-group">
    <select class="form-control" name="warehouse" required  onchange="change_warehouse(this)">
        <option value="0"><?= l('Выберите Склад, на который принимаем товар') ?> </option>
        <?php if (!empty($warehouses)): ?>
            <?php foreach ($warehouses as $id => $warehouse): ?>
                <option value="<?= $id ?>"><?= h($warehouse) ?> </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>
<div class="form-group">
    <select class="form-control  select-location" name="location" required>
        <option value="0"><?= l('Выберите локацию на складе') ?> </option>
        <?php if (!empty($locations)): ?>
            <?php foreach ($locations as $id => $location): ?>
                <option value="<?= $id ?>"><?= h($location) ?> </option>
            <?php endforeach; ?>
        <?php endif; ?>
    </select>
</div>
