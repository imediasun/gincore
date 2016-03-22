<form class="form-horizontal" method="post">
    <fieldset>
        <legend><?= l('Добавление клиента') ?></legend>
        <div class="control-group">
            <label class="control-label"><?= l('Электронная почта') ?>: </label>
            <div class="controls">
                <input value="<?= (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '') ?>"
                       name="email" class="form-control"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?= l('Телефон') ?>:<b class="text-danger">*</b> </label>
            <div class="controls">
                <input value="<?= (isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '') ?>"
                       name="phone" required class="form-control"/>
            </div>
        </div>
        <div class="control-group">
            <label class="control-label"><?= l('Ф.И.О.') ?>: <b class="text-danger">*</b></label>
            <div class="controls">
                <input value="<?= (isset($_POST['fio']) ? htmlspecialchars($_POST['fio']) : '') ?>"
                       name="fio" required class="form-control"/>
            </div>
        </div>
        <?php if ($contractors): ?>
            <div class="control-group"><label class="control-label"><?= l('Контрагент') ?>: </label>
                <div class="controls">
                    <select name="contractor_id" class="multiselect">
                        <option value=""><?= l('Не выбран') ?></option>
                        <?php foreach ($contractors as $contractor): ?>
                            <option value="<?= $contractor['id'] ?>">
                                <?= htmlspecialchars($contractor['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        <?php endif; ?>
        <div class="control-group">
            <div class="controls">
                <input id="save_all_fixed" class="btn btn-primary" type="submit"
                       value="<?= l('Сохранить изменения') ?>" name="edit-client">
            </div>
        </div>
    </fieldset>
</form>
