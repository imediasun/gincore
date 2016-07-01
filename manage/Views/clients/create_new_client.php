<form class="form-horizontal" method="post">
    <fieldset>
        <div class="col-sm-12">
            <legend><?= l('Добавление клиента') ?></legend>
        </div>
        <div class="col-sm-5">
            <div class="control-group">
                <div>
                    <label class="control-label"><?= l('Ф.И.О.') ?>: <b class="text-danger">*</b></label>
                </div>
                <div class=" controls">
                    <input value="<?= (isset($_POST['fio']) ? htmlspecialchars($_POST['fio']) : '') ?>"
                           name="fio" required class="form-control"/>
                </div>
            </div>
            <div class="control-group">
                <div>
                    <label class="control-label"><?= l('Телефон') ?>:<b class="text-danger">*</b> </label>
                </div>
                <div class="controls">
                    <input<?= input_phone_mask_attr() ?>
                        value="<?= (isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '') ?>"
                        name="phone" required class="form-control"/>
                </div>
            </div>
            <div class="control-group">
                <div>
                    <label class="control-label"><?= l('Адрес') ?>: </label>
                </div>
                <div class="controls">
                    <input
                        value="<?= (isset($_POST['legal_address']) ? htmlspecialchars($_POST['legal_address']) : '') ?>"
                        name="legal_address" class="form-control"/>
                </div>
            </div>
            <div class="control-group">
                <div>
                    <label class="control-label"><?= l('Электронная почта') ?>: </label>
                </div>
                <div class=" controls">
                    <input value="<?= (isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '') ?>"
                           name="email" class="form-control"/>
                </div>
            </div>
            <div class=" control-group">
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
            <div class=" control-group">
                <?= $this->renderFile('clients/tags_list', array(
                    'tags' => $tags,
                    'new_client' => true, 
                    'client' => array(
                        'id' => null
                    ),
                    'infopopover' => InfoPopover::getInstance()->createQuestion('l_create_new_client_tags_info')
                )); ?>
            </div>
            <div class=" control-group">
                <div class="controls">
                    <input id="save_all_fixed" class="btn btn-primary" type="submit"
                           value="<?= l('Сохранить изменения') ?>" name="edit-client">
                </div>
            </div>
        </div>
    </fieldset>
</form>
