<form action="<?= $this->all_configs['prefix'] ?>products/import" method="post" enctype="multipart/form-data">
    <fieldset>
        <input type="hidden" name="import" value="1" />
        <div class=" form-group">
            <?= l('Для импорта принимаются только файлы XLS ранее экспортированные из базы Gincore.'); ?> <br>
            <?= l('В файле обязательно должно присутствовать поле ID.'); ?>
        </div>
        <div class="form-group">
            <input type="file"/>
        </div>
        <div class="form-group">
            <button type='submit' class="btn btn-primary"><?= l('Загрузить файл') ?></button>
        </div>
    </fieldset>
</form>