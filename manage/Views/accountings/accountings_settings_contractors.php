<?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
    <div class="panel-group row-fluid" id="accordion_contractors">
        <div class="col-sm-12">
        <button type="button" onclick="alert_box(this, false, 'create-contractor-form')" class="btn btn-primary">
            <?= l('Создать контрагента') ?>
        </button>
        <br><br>
        <legend><?= l('Редактирование статей контрагента') ?></legend>
        </div>
        <div class="col-sm-6">
        <?php if (count($contractors) > 0): ?>
            <?php foreach ($contractors as $contractor): ?>
                <?= $controller->form_contractor($contractor,
                    isset($_GET['ct']) && $_GET['ct'] > 0 ? $_GET['ct'] : 0); ?>
            <?php endforeach; ?>
        <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
