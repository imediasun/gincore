<small>
    <a href="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/copy">
        <?= l('скопировать языки') ?>
    </a>
</small>
<h3>
    <?= $config['name'] ?>
    <a href="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/add">+</a>
</h3>
<form action="<?= $this->all_configs['prefix'] . $url ?>/<?= $this->all_configs['arrequest'][1] ?>/save" method="post">
    <fieldset class="main">
        <?php foreach ($translates as $id => $langs): ?>
            <legend>(id <?= $id ?>)</legend>
            <div>
                <?php if (isset($this->config[$this->all_configs['arrequest'][1]]['var'])): ?>
                    <?php if (is_array($this->config[$this->all_configs['arrequest'][1]]['var'])): ?>
                        <?php $vars_vals = array(); ?>
                        <?php foreach ($this->config[$this->all_configs['arrequest'][1]]['var'] as $var): ?>
                            <?php $vars_vals[] = $table[$id][$var]; ?>
                        <?php endforeach; ?>
                        <span class="muted"><?= implode(', ', $vars_vals) ?></span>
                    <?php else: ?>
                        <span
                            class="muted"><?= $table[$id][$this->config[$this->all_configs['arrequest'][1]]['var']] ?></span>
                    <?php endif; ?>
                <?php endif; ?>
                <?php foreach ($config['fields'] as $field => $field_name): ?>
                    <legend><?= $field_name ?></legend>
                    <p class="text-muted"><?= $field ?></p>

                    <?php foreach ($langs as $lng => $translate): ?>
                        <?php $value = htmlspecialchars($translate[$field]); ?>
                        <span class="form-group" style="display:block">
                            <label><?= $languages[$lng]['name'] ?>, <?= $lng ?></label>
                            <?php $f_name = 'data[' . $id . '][' . $lng . '][' . $field . ']'; ?>
                            <?php if (strlen($value) > 50): ?>
                                <textarea class="form-control" style="height: 150px"
                                          name="<?= $f_name ?>"><?= $value ?></textarea>
                            <?php else: ?>
                                <input class="form-control" type="text" name="<?= $f_name ?>" value="<?= $value ?>">
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                <?php endforeach; ?>
            </div>
            <br><br>
        <?php endforeach; ?>
        <input type="submit" class="save-btn btn btn-primary" value="<?= l('save') ?>">
    </fieldset>
</form>
