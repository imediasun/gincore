<form class="form-horizontal" method="post">
    <fieldset>
        <legend><?= l('Новый отзыв о магазине') ?></legend>
        <div class="control-group"><label class="control-label"><?= l('Клиент') ?>: </label>
            <div class="controls"><?= typeahead($this->all_configs['db'], 'clients', false, 0, 2) ?></div>
        </div>
        <div class="control-group">
            <div class="controls">
                <label class="radio"><input type="radio" name="status" value="1"/>
                    <?= $this->all_configs['configs']['reviews-shop-status'][1] ?>,</label>
                <label class="radio"><input type="radio" name="status" value="2"/>
                    <?= $this->all_configs['configs']['reviews-shop-status'][2] ?>,</label>
                <label class="radio"><input type="radio" name="status" value="3"/>
                    <?= $this->all_configs['configs']['reviews-shop-status'][3] ?></label>
            </div>
        </div>
        <div class="control-group">
            <div class="controls"><label class="radio"><input type="radio" name="become_status" value="1"/>
                    <?= $this->all_configs['configs']['reviews-shop-become_status'][1] ?>,</label>
                <label class="radio"><input type="radio" name="become_status" value="2"/>
                    <?= $this->all_configs['configs']['reviews-shop-become_status'][2] ?>,</label>
                <label class="radio"><input type="radio" name="become_status" value="3"/>
                    <?= $this->all_configs['configs']['reviews-shop-become_status'][3] ?></label></div>
        </div>
        <div class="control-group"><label class="control-label"><?= l('Комментарий') ?>: </label>
            <div class="controls"><textarea class="span5" name="text"></textarea></div>
        </div>
        <div class="control-group"><label class="control-label"><?= l('Одобрен') ?>: </label>
            <div class="controls"><input type="checkbox" name="avail"/></div>
        </div>
        <div class="control-group">
            <div class="controls"><input class="btn btn-primary" type="submit" value="<?= l('Добавить') ?>"
                                         name="add-shop-reviews"></div>
        </div>
    </fieldset>
</form>
