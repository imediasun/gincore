<div class="row row-15">
    <form method="post" id="warehouses-filter-block-from">
        <div class="col-sm-4">
            <div class="row row-15">
                <div class="col-sm-6">
                    <div class="form-group">
                        <label><?= l('Склад') ?>:</label><br>
                        <select onchange="change_warehouse(this)" class="multiselect form-control" name="warehouses[]"
                                id="warehouses-filter-select" multiple="multiple">
                            <?= $warehousesOptions ?>
                        </select>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="form-group">
                        <label><?= l('Локация') ?>:</label><br>
                        <select class="multiselect form-control select-location" name="locations[]" multiple="multiple">
                            <?= $whSelect ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="form-group" style="max-width:400px">
                <label><?= l('Поиск по товару или артикулу') ?>: </label>
                <?= typeahead($this->all_configs['db'], 'goods', true, isset($_GET['pid']) && $_GET['pid'] > 0 ? intval($_GET['pid']): 0, $i) ?>
            </div>
            <div class="form-group">
                <input class="btn btn-info" type="submit" name="filter-warehouses" value="<?= l('Применить') ?>" />
            </div>
        </div>
        <div class="col-sm-2">
            <div class="form-group">
                <label><?= l('Тип вывода') ?>:</label>
                <div class="radio">
                    <label><input checked type="radio" value="item" name="display" /><?= l('по изделию') ?></label>
                </div>
                <div class="radio">
                    <label><input  <?= ((isset($_GET['d']) && $_GET['d'] == 'a') ? 'checked' : '') ?> type="radio" value="amount" name="display" /><?= l('по наименованию') ?></label>
                </div>
            </div>
        </div>
    </form>
    <div class="col-sm-6">
        <form class="form-horizontal" method="POST">
            <label class="col-sm-6 control-label"><?= l('Серийный номер') ?>:&nbsp;</label>
            <div class="input-group col-sm-6">
                <input class="form-control" name="serial" placeholder="<?= l('серийный номер') ?>" value="<?= ((isset($_GET['serial']) && !empty($_GET['serial'])) ? htmlspecialchars(urldecode($_GET['serial'])) : '') ?>" />
                <div class="input-group-btn">
                    <input class="btn" type="submit" name="filter-warehouses" value="<?= l('Поиск') ?>" />
                </div>
            </div>
        </form>
        <form class="form-horizontal m-t-sm" method="POST">
            <label class="col-sm-6 control-label"><?= l('Номер заказа поставщику') ?>:&nbsp;</label>
            <div class="input-group col-sm-6">
                <input class="form-control" name="so_id" placeholder="<?= l('номер заказа поставщику') ?>" value="<?= ((isset($_GET['so_id']) && $_GET['so_id']>0) ? intval($_GET['so_id']) : '') ?>" />
                <div class="input-group-btn">
                    <input class="btn" type="submit" name="filter-warehouses" value="<?= l('Поиск') ?>" />
                </div>
            </div>
        </form>
    </div>
</div>
