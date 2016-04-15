<h3><?= $config['name'] ?></h3>
<form action="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/add/save"
      method="post">
    <fieldset>
        <legend><?= l('Данные') ?></legend>
        <?php foreach ($columns as $column): ?>
            <?php if ($column['Field'] != 'id'): ?>
                <div class="from-control">
                    <label><?= $column['Field'] ?></label>
                    <input class="form-control" name="data[<?= $column['Field'] ?>]" type="text">
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    </fieldset>
    <br><br>

    <fieldset>
        <legend><?= l('Переводы') ?></legend>
        <?php foreach ($config['fields'] as $field => $field_name): ?>
            <?php foreach ($languages as $lng => $l): ?>
                <div class="from-control">
                    <label><?= $field_name ?>, <?= $lng ?></label>
                    <input class="form-control" name="translates[<?= $lng ?>][<?= $field ?>]" type="text">
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    </fieldset>
    <input type="submit" class="save-btn btn btn-primary" value="<?= l('save') ?>">
</form>
