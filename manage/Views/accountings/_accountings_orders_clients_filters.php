<form method="post">
    <div class="col-sm-4">
        <legend><?= l('Фильтры') ?>:</legend>
        <div class="form-group">
            <label><?= l('Дата') ?>:</label>
            <input type="text" placeholder="<?= l('Дата') ?>" name="date" class="daterangepicker form-control"
                   value="<?= $date ?>"/>
        </div>
        <div class="form-group">
            <label><?= l('номер заказа') ?>:</label>
            <input name="client-order_id"
                   value="<?= isset($_GET['co_id']) && $_GET['co_id'] > 0 ? $_GET['co_id'] : ''; ?>"
                   type="text" class="form-control" placeholder="<?= l('номер заказа') ?>">
        </div>
        <div class="form-group">
            <label><?= l('Категория') ?>:</label>
            <?= typeahead($this->all_configs['db'], 'categories', false,
                isset($_GET['g_cg']) && $_GET['g_cg'] > 0 ? $_GET['g_cg'] : 0); ?>
        </div>
        <div class="form-group">
            <label><?= l('ФИО') ?>:</label>
            <input name="client-order"
                   value="<?= isset($_GET['co']) && !empty($_GET['co']) ? trim(htmlspecialchars($_GET['co'])) : ''; ?>"
                   type="text" class="form-control" placeholder="<?= l('ФИО') ?>">
        </div>
        <div class="form-group multiselect-float-right">
            <label style="line-height: 34px"><?= l('Способ оплаты') ?>:</label>
            <select name="cashless" class="form-control multiselect">
                <option <?= !is_numeric($_GET['cashless']) ? 'selected' : '' ?> value=""><?= l('все способы оплаты') ?></option>
                <?php foreach (array(0 => l('Нал'), 1 => l('Безнал')) as $id => $type): ?>
                    <option <?= is_numeric($_GET['cashless']) && ($id == $_GET['cashless']) ? 'selected' : '' ?> value="<?= $id ?>">
                        <?= $type ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label><?= l('Товар') ?>:</label>
            <?= typeahead($this->all_configs['db'], 'goods', true,
                isset($_GET['by_gid']) && $_GET['by_gid'] ? $_GET['by_gid'] : 0, 2, 'input-small', 'input-mini'); ?>
        </div>
        <div class="form-group"><input type="submit" name="filters" class="btn btn-primary"
                                       value="<?= l('Фильтровать') ?>">
        </div>
    </div>
</form>
