<form method="post">
    <div class="clearfix theme_bg filters-box p-sm m-b-md">
        <div class="form-group"><label><?= l('Категории') ?></label>
            <select class="multiselect form-control" multiple="multiple" name="ctg[]">
                <?= build_array_tree($categories, isset($_GET['ctg']) ? explode(',', $_GET['ctg']) : null); ?>
            </select></div>
        <div class="form-group"><label><?= l('Сроки доставки') ?></label>
            <?php $s = isset($_GET['tso']) ? intval($_GET['tso']) : 0; ?>
            <select class="form-control" name="tso">
                <option <?= ($s == 4 ? 'selected' : '') ?> value="4">4</option>
                <option <?= ($s == 3 ? 'selected' : '') ?> value="3">3</option>
                <option <?= ($s == 2 ? 'selected' : '') ?> value="2">2</option>
                <option <?= ($s == 1 ? 'selected' : '') ?> value="1">1</option>
            </select>
        </div>
        <div class="form-group"><label><?= l('Дата от') ?>:</label>
            <input type="text" placeholder="<?= l('Дата') ?>" name="date" class="daterangepicker form-control"
                            value="<?= $date ?>"/>
        </div>
        <input type="submit" class="btn btn-primary" value="<?= l('Применить') ?>" name="procurement-filter"/>
    </div>
</form>
