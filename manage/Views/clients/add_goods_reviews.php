<form class="form-horizontal" method="post">
    <fieldset>
        <legend><?= l('Новый отзыв') ?></legend>
        <div class="control-group"><label class="control-label"><?= l('Клиент') ?>: </label>
            <div class="controls"><?= typeahead($this->all_configs['db'], 'clients', false, 0, 3) ?></div>
        </div>
        <div class="control-group"><label class="control-label"><?= l('Товар') ?>: </label>
            <div class="controls">
                <?= typeahead($this->all_configs['db'], 'goods', true) ?>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?= l('Комментарий') ?>: </label>
            <div class="controls">
                <textarea class="span5" name="text"></textarea>
            </div>
        </div>
        <div class="control-group"><label class="control-label"><?= l('Рейтинг') ?>: </label>
            <div class="controls">
                <select name="rating" class="span5">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>"><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
        </div>
        <div class="control-group"><label class="control-label"><?= l('Полезность') ?>: </label>
            <div class="controls">
                <input type="text" class="span5" onkeydown="return isNumberKey(event)" name="usefulness_yes" value=""/>
            </div>
        </div>
        <div class="control-group"><label class="control-label"><?= l('Бесполезность') ?>: </label>
            <div class="controls">
                <input type="text" class="span5" onkeydown="return isNumberKey(event)" name="usefulness_no" value=""/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?= l('Одобрен') ?>: </label>
            <div class="controls"><input type="checkbox" name="avail"/></div>
        </div>
        <div class="control-group">
            <div class="controls">
                <input class="btn btn-primary" type="submit" value="<?= l('Добавить') ?>" name="add-goods-reviews">
            </div>
        </div>
    </fieldset>
</form>
