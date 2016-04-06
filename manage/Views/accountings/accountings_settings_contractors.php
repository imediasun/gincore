<?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
    <div class="panel-group" id="accordion_contractors">
        <button type="button" onclick="alert_box(this, false, 'create-contractor-form')" class="btn btn-primary">
            <?= l('Создать контрагента') ?>
        </button>
        <br><br>
        <legend><?= l('Редактирование статей контрагента') ?></legend>
        <?php if (count($contractors) > 0): ?>
            <?php foreach ($this->contractors as $contractor): ?>
                <?= $controller->form_contractor($contractor,
                    isset($_GET['ct']) && $_GET['ct'] > 0 ? $_GET['ct'] : 0); ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endif; ?>
