<option value=""><?= l('Выберите') ?></option>
<?php foreach ($options as $k => $v): ?>
    <option<?= ($selected == $k ? ' selected' : '') ?>
        value="<?= $k ?>"><?= is_array($v) && isset($v['name']) ? l($v['name']) : l($v) ?></option>
<?php endforeach; ?>
