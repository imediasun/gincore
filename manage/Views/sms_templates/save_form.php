<h3><?= $config['name'] ?></h3>
<form data-='' action="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/add/save"
      method="post">
    <div class="form-group">
        <label><?= l('Тип') ?></label>
        <div class="from-control">
            <?= $this->renderFile('sms_templates/types_list', array(
                'types' => $types,
                'current' => isset($_POST['data']['type'])?$_POST['data']['type']:0
            )) ?>
        </div>
    </div>
    <div class="form-group">
        <label><?= l('Название') ?></label>
        <?php foreach ($columns as $column): ?>
            <?php if (!in_array($column['Field'], array('id', 'type'))): ?>
                <div class="from-control">
                    <label><?= $column['Field'] ?></label>
                    <input required class="form-control" name="data[<?= $column['Field'] ?>]" type="text" value="<?= isset($_POST['data']['var'])?$_POST['data']['var']:'' ?>">
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    <div class="form-group">
        <label><?= l('Шаблон') ?></label>
        <?php foreach ($config['fields'] as $field => $field_name): ?>
            <div class="from-control">
                <label><?= $field_name ?>, <?= $manage_lang ?></label>
                <input required class="form-control" name="translates[<?= $manage_lang ?>][<?= $field ?>]" type="text">
            </div>
        <?php endforeach; ?>
    </div>
    <div class="form-group">
        <input type="submit" class="btn btn-primary" value="<?= l('save') ?>">
    </div>
</form>
