<form style="max-width: 300px" method="post">
    <div class="form-group">
        <label><?= l('Автор') ?>: </label>
        <a href="<?= $this->all_configs['prefix'] ?>users"><?= $author ?></a>
    </div>
    <div class="form-group">
        <label><?= l('manager') ?>: </label>
        <select
            class="multiselect form-control" <?= $this->all_configs['configs']['manage-product-managers'] == true ? 'multiple="multiple"' : ''; ?>
            name="users[]">
            <option value="0"><?= l('Не выбран') ?></option>
            <?php if (!empty($managers)): ?>
                <?php foreach ($managers as $manager): ?>
                    <option
                        value="<?= $manager['id'] ?>" <?= $manager['id'] == $manager['manager'] ? ' selected ' : ''; ?> >
                        <?= $manager['login'] ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>
    </div>
    <?= $controller->btn_save_product('managers_managers'); ?>
</form>
