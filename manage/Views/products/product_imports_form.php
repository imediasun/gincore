<form id="import_form" method="post">
    <fieldset>
        <input type="hidden" name="import" value="1" />
        <input type="hidden" name="import_type" value="gincore_items" />
        <input type="hidden" name="handler" value="exported" />
        <div class=" form-group">
            <?= l('Для импорта принимаются только файлы XLS ранее экспортированные из базы Gincore.'); ?> <br>
            <?= l('В файле обязательно должно присутствовать поле ID.'); ?>
        </div>
        <div class="form-group">
            <input name='file' type="file"/>
        </div>
        <div class="form-group">
            <a  class="btn btn-primary"  onclick="return start_import_goods(this);"><?= l('Загрузить файл') ?></a>
        </div>
    </fieldset>
</form>
<div id="upload_messages"></div>