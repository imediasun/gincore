<div class="row-fluid">
    <div class="col-sm-6">
        <form id="import_form" method="post">
            <input type="hidden" name="import_type" value="posting_items"/>
            <input type="hidden" value="xls" name="handler">
            <input type="hidden" name="accepter_as_manager" value="1">

            <div class="form-group">
                <?php if (file_exists(GINCORE_ROOT . '/manage/modules/import/templates/posting_items.xls')): ?>
                    <?php $extension = '.xls'; ?>
                <?php else: ?>
                    <?php $extension = '.csv'; ?>
                <?php endif; ?>
                <a href="<?= $this->all_configs['prefix'] ?>modules/import/templates/posting_items<?= $extension ?>">
                    <i class="fa fa-file-excel-o"
                       aria-hidden="true"></i>&nbsp;<?= l(sprintf('Скачать образец файла для импорта %s',
                        'posting_items')) ?>
                </a>
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
                <select class="form-control" name="warehouse" required onchange="change_warehouse(this)">
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
                </select>
            </div>
            <div class="form-group">
                <input type="file" name="file">
            </div>
        </form>
    </div>
</div>
