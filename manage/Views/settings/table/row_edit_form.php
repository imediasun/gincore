<h3><?= l('Редактируем') ?> <?= $conf[$table]['settings']['name'] ?></h3>

<form
    action="<?= $this->all_configs['prefix'] ?>settings/<?= $this->all_configs['arrequest'][1] ?>/update/<?= $row['id'] ?>"
    method="POST">
    <?php foreach ($row as $k => $pp): ?>

        <?php if (!isset($conf[$table]['columns'][$k][1]) || $conf[$table]['columns'][$k][1] != 1): ?>
            <div class="form-group">
                <label><?= isset($conf[$table]['columns'][$k]) && $conf[$table]['columns'][$k] ? $conf[$table]['columns'][$k][2] : $pp; ?></label>
                <?php if (isset($conf[$table]['columns'][$k][5])): ?>
                    <select class="form-control" name="<?= $k ?>">
                        <option value="0"><?= l('не выбрано') ?></option>
                        <?php foreach ($vars[$pp] as $var_id => $var_value): ?>
                            <option <?= ($var_id == $pp ? 'selected' : '') ?> value="<?= intval($var_id) ?>">
                                <?= htmlspecialchars($var_value) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <?php if (strlen($pp) > 100): ?>
                        <textarea class="form-control" name="<?= $k ?>" rows="9"
                                  cols="80"><?= htmlspecialchars($pp) ?></textarea>
                    <?php else: ?>
                        <input class="form-control" type="text" value="<?= htmlspecialchars($pp) ?>" name="<?= $k ?>"
                               size="70">
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <input type="submit" value="<?= l('save') ?>" class="btn btn-primary"/>
</form>
