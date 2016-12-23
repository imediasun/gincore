<div class="hpanel">
    <div class="panel-body">
        <h3><?= l('Добавление нового параметра') ?></h3>
        <form action="<?= $this->all_configs['prefix'] ?>settings/add/ok" method="post">
            <div class="form-group">
                <label><?= l('sets_param') ?>:</label>
                <input type="text" name="name" class="form-control" value="">
            </div>
            <div class="form-group">
                <label><?= l('sets_value') ?>: </label>
                <textarea class="form-control" name="value"></textarea>
            </div>
            <div class="form-group">
                <label><?= l('name') ?>: </label>
                <textarea class="form-control" name="title"></textarea>
            </div>
            <div class="form-group">
                <label><?= l('Описание') ?>: </label>
                <textarea class="form-control" name="description"></textarea>
            </div>
            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="ro" value="1"> <?= l('sets_read_only') ?>
                    </label>
                </div>
            </div>
            <input type="submit" value="<?= l('save') ?>" class="btn btn-primary">
        </form>
    </div>
</div>
