<form method="post" style="max-width:300px">
    <div class="form-group">
        <div class="checkbox">
            <label>
                <input name="avail" <?= $product['avail'] == 1 ? 'checked' : '' ?> type="checkbox">
                <?= l('Активность') ?>
            </label>
        </div>
    </div>
    <div class="form-group">
        <div class="checkbox">
            <label>
                <input name="type" <?= $product['type'] == 1 ? 'checked' : '' ?> type="checkbox">
                <?= l('Услуга') ?>
            </label>
        </div>
    </div>
    <div class="form-group">
        <label><?= l('Категории') ?>: </label>
        <select class="multiselect form-control" multiple="multiple" name="categories[]">
            <?= build_array_tree($categories, array_keys($selected_categories)); ?>
        </select>
    </div>
    <?= $btn_save; ?>
</form>
