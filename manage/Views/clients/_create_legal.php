<form class="form-horizontal" method="post">
    <fieldset>
        <div class="col-sm-7">
            <div class="control-group">
                <div>
                    <label class="control-label"><?= l('Название организации') ?>: <b class="text-danger">*</b></label>
                </div>
                <div class=" controls">
                    <input value="<?= (isset($_POST['fio']) ? h($_POST['fio']) : '') ?>"
                           name="fio" required class="form-control"/>
                </div>
            </div>
            <div class="control-group">
                <div>
                    <label class="control-label"><?= l('Телефон') ?>:<b class="text-danger">*</b> </label>
                </div>
                <div class="controls">
                    <input<?= input_phone_mask_attr() ?>
                        value="<?= (isset($_POST['phone']) ? h($_POST['phone']) : '') ?>"
                        name="phone" required class="form-control"/>
                </div>
            </div>
            <div class="control-group">
                <div>
                    <label class="control-label"><?= l('Регистрационные данные 1') ?>: </label>
                </div>
                <div class="controls">
                    <input
                        value="<?= (isset($_POST['reg_data_1']) ? h($_POST['reg_data_1']) : '') ?>"
                        name="reg_data_1" class="form-control"/>
                </div>
            </div>
            <div class="control-group">
                <div>
                    <label class="control-label"><?= l('Регистрационные данные 2') ?>: </label>
                </div>
                <div class="controls">
                    <input
                        value="<?= (isset($_POST['reg_data_2']) ? h($_POST['reg_data_2']) : '') ?>"
                        name="reg_data_2" class="form-control"/>
                </div>
            </div>
            <div class="control-group">
                <div>
                    <label class="control-label"><?= l('Юридический адрес') ?>: </label>
                </div>
                <div class="controls">
                    <input
                        value="<?= (isset($_POST['legal_address']) ? h($_POST['legal_address']) : '') ?>"
                        name="legal_address" class="form-control"/>
                </div>
            </div>
            <div class="control-group">
                <div>
                    <label class="control-label"><?= l('Фактический адрес') ?>: </label>
                </div>
                <div class="controls">
                    <input
                        value="<?= (isset($_POST['residential_address']) ? h($_POST['residential_address']) : '') ?>"
                        name="residential_address" class="form-control"/>
                </div>
            </div>
            <div class="control-group">
                <div>
                    <label class="control-label"><?= l('Электронная почта') ?>: </label>
                </div>
                <div class=" controls">
                    <input value="<?= (isset($_POST['email']) ? h($_POST['email']) : '') ?>"
                           name="email" class="form-control"/>
                </div>
            </div>
            <div class="control-group">
                <div>
                    <label class="control-label"><?= l('Примечание') ?>: </label>
                </div>
                <div class=" controls">
                    <textarea name="note" class="form-control"><?= (isset($_POST['note']) ? h($_POST['note']) : '') ?></textarea>
                </div>
            </div>
            <div class=" control-group" style="margin-top: 10px">
                <?php if ($contractors): ?>
                    <?= $this->renderFile('clients/contractors_list', array(
                        'contractors' => $contractors,
                        'client' => array(
                            'id' => null
                        ),
                        'new_client' => true,
                        'infopopover' => InfoPopover::getInstance()->createQuestion('l_create_new_client_contractor_info')
                    )); ?>
                <?php endif; ?>
            </div>
            <div class=" control-group" style="margin-top: 10px">
                <?= $this->renderFile('clients/tags_list', array(
                    'tags' => $tags,
                    'new_client' => true,
                    'client' => array(
                        'id' => null
                    ),
                    'infopopover' => InfoPopover::getInstance()->createQuestion('l_create_new_client_tags_info')
                )); ?>
            </div>
            <div class=" control-group" style="margin-top: 10px; text-align: right">
                    <input id="save_legal" class="btn btn-primary" type="submit"
                           value="<?= l('Сохранить изменения') ?>" name="create-legal">
            </div>
        </div>
    </fieldset>
</form>
