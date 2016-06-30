<?php echo print_r($_POST, true) ?>
<h3><?= $config['name'] ?></h3>
<form data-='' action="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/add/save"
      method="post">
    <fieldset>
        <legend><?= l('Тип') ?></legend>
        <div class="from-control">
            <?= $this->renderFile('sms_templates/types_list', array(
                'types' => $types,
                'current' => isset($_POST['data']['type'])?$_POST['data']['type']:0
            )) ?>
        </div>
    </fieldset>
    <br><br>

    <fieldset>
        <legend><?= l('Название') ?></legend>
        <?php foreach ($columns as $column): ?>
            <?php if (!in_array($column['Field'], array('id', 'type'))): ?>
                <div class="from-control">
                    <label><?= $column['Field'] ?></label>
                    <input required class="form-control" name="data[<?= $column['Field'] ?>]" type="text" value="<?= isset($_POST['data']['var'])?$_POST['data']['var']:'' ?>">
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </fieldset>
    <br><br>

    <fieldset>
        <legend><?= l('Шаблон') ?></legend>
        <?php foreach ($config['fields'] as $field => $field_name): ?>
            <div class="from-control">
                <label><?= $field_name ?>, <?= $manage_lang ?></label>
                <input required class="form-control" name="translates[<?= $manage_lang ?>][<?= $field ?>]" type="text">
            </div>
        <?php endforeach; ?>
    </fieldset>
    <input type="submit" class="save-btn btn btn-primary" value="<?= l('save') ?>">
</form>
