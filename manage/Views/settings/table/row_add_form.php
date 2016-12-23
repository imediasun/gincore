<h3><?= l('Добавляем в') ?> <?= $conf[$table]['settings']['name'] ?></h3>

<form action="<?= $this->all_configs['prefix'] ?>settings/<?= $this->all_configs['arrequest'][1] ?>/insert"
      method="POST">

    <?php foreach ($columns as $pp): ?>
        <?php $pp = array_values($pp); ?>

        <?php if ($conf[$table]['columns'][$pp[0]][1] != 1): ?>
            <div class="form-group">
                <label><?= $conf[$table]['columns'][$pp[0]] ? $conf[$table]['columns'][$pp[0]][2] : $pp[0]; ?></label>
                <?php if (isset($conf[$table]['columns'][$pp[0]][5])): ?>
                    <select class="form-group" name="<?= $pp[0] ?>">
                        <option value="0"><?= l('не выбрано') ?></option>
                        <?php foreach ($vars[$pp[0]] as $var_id => $var_value): ?>
                            <option <?= ($var_id == $pp ? 'selected' : '') ?>
                                value="<?= intval($var_id) ?>"><?= htmlspecialchars($var_value) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input class="form-control" type="text" value="<?= $conf[$table]['columns'][$pp[0]][3] ?>"
                           name="<?= $pp[0] ?>"
                           size="70">
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <input type="submit" value="<?= l('Добавить') ?>" class="btn btn-primary"/>
</form>
