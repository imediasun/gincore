<form id="form-create-client" method="post">
    <div class="form-group">
        <label><?= l('Электронная почта') ?>: </label>
        <input type="text" class="form-control" name="email" value=""
               placeholder="<?= l('Электронная почта') ?>"/>
    </div>
    <div class="form-group">
        <label class="control-label"><?= l('Ф.И.О') ?>: </label>
        <input class="form-control" type="text" name="fio" value=""
               placeholder="<?= l('Ф.И.О') ?>"/>
    </div>
    <div class="form-group">
        <label class="control-label"><?= l('Телефон') ?>: </label>
        <input class="form-control" type="text" name="phone" value=""
               placeholder="<?= l('Телефон') ?>"/>
    </div>
</form>
