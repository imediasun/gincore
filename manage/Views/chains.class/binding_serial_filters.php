<div class="col-sm-6">
    <form method="post">
        <legend><?= l('Фильтры') ?>:</legend>
        <div class="form-group">
            <label><?= l('номер заказа на ремонт') ?>:</label>
            <input name="client-order-number"
                   value="<?= isset($_GET['con']) && !empty($_GET['con']) ? trim(htmlspecialchars($_GET['con'])) : ''; ?>"
                   type="text" class="form-control" placeholder="<?= l('номер заказа на ремонт') ?>">
        </div>
        <div class="form-group"><label><?= l('Товар') ?>:</label>
            <?= typeahead($this->all_configs['db'], 'goods', true,
                isset($_GET['by_gid']) && $_GET['by_gid'] > 0 ? $_GET['by_gid'] : 0, 2, 'input-small', 'input-mini'); ?>
        </div>
        <div class="form-group">
            <label><?= l('Склад') ?>:</label>
            <select class="form-control" name="warehouse" onchange="return change_warehouse(this)">
                <option value="0"><?= l('Выберите склад') ?></option>
                <?php if (!empty($warehouses)): ?>
                    <?php foreach ($warehouses as $warehouse): ?>
                        <option
                            value="<?= $warehouse['id'] ?>" <?= isset($_GET['warehouse']) && $warehouse['id'] == $_GET['warehouse']? 'selected' : '' ?>><?= h($warehouse['title']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="form-group">
            <label><?= l('Локация') ?>:</label>
            <select class="form-control select-location" name="location">
                <option value="0"><?= l('Выберите локацию') ?></option>
                <?php if (!empty($locations)): ?>
                    <?php foreach ($locations as $location): ?>
                        <option
                            value="<?= $location['id'] ?>" <?= isset($_GET['location']) && $location['id'] == $_GET['location']? 'selected' : '' ?>><?= h($location['title']) ?></option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
        <div class="form-group">
            <div class="checkbox">
                <label>
                    <input name="noitems" <?= (isset($_GET['noi']) ? 'checked' : '') ?>
                           type="checkbox"/> <?= l('Без изделий') ?>
                </label>
            </div>
            <div class="form-group" style="white-space: nowrap">
                <input type="submit" name="bind-filters" class="btn" value="<?= l('Фильтровать') ?>">
                <?= $this->LockButton->show($_GET['lock-button']) ?>
            </div>
        </div>
    </form>
</div>
