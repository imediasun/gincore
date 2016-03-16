<form method="post">
    <legend><?= l('Фильтры') ?>:</legend>
    <div class="form-group"><label><?= l('Товар') ?>:</label>
        <?= typeahead($this->all_configs['db'], 'goods', true,
            isset($_GET['by_gid']) && $_GET['by_gid'] > 0 ? $_GET['by_gid'] : 0, 2, 'input-small', 'input-mini'); ?>
    </div>
    <div class="form-group">
        <label><?= l('Серийный номер') ?>:</label>
        <input name="serial"
               value="<?= isset($_GET['serial']) && !empty($_GET['serial']) ? trim(htmlspecialchars($_GET['serial'])) : ''; ?>"
               type="text" class="form-control" placeholder="<?= l('Серийный номер') ?>">
    </div>
    <div class="form-group">
        <label><?= l('ФИО') ?>:</label>
        <?= typeahead($this->all_configs['db'], 'clients', false,
            isset($_GET['c_id']) && $_GET['c_id'] > 0 ? $_GET['c_id'] : 0) ?>
    </div>
    <div class="form-group">
        <label><?= l('номер заказа на ремонт') ?>:</label>
        <input name="client-order-number"
               value="<?= isset($_GET['con']) && !empty($_GET['con']) ? trim(htmlspecialchars($_GET['con'])) : ''; ?>"
               type="text" class="form-control" placeholder="<?= l('номер заказа на ремонт') ?>">
    </div>
    <div class="form-group">
        <div class="checkbox">
            <label>
                <input name="noitems" <?= (isset($_GET['noi']) ? 'checked' : '') ?>
                       type="checkbox"/> <?= l('Без изделий') ?>
            </label>
        </div>
        <div class="form-group">
            <input type="submit" name="filters" class="btn" value="<?= l('Фильтровать') ?>">
        </div>
    </div>
</form>
