<form method="post">
    <table class="table">
        <thead>
        <tr>
            <td><?= l('Сотрудник') ?></td>
            <td><?= l('Укажите склады к которым сотрудник имеет доступ') ?></td>
            <td><?= l('Укажите склад и локацию, на которую по умолчанию перемещается устройство принятое на ремонт
                данным сотрудником') ?>
            </td>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user_id => $user): ?>
            <tr>
                <td><?= get_user_name($user) ?></td>
                <td>
                    <select class="multiselect" name="warehouses_users[<?= $user_id ?>][]" multiple="multiple">
                        <?php $whs = $wh_users && isset($wh_users[$user_id]) ? explode(',',
                            $wh_users[$user_id]) : array(); ?>
                        <?php foreach ($warehouses as $warehouse): ?>
                            <?php $selected = in_array($warehouse['id'], $whs) ? 'selected' : ''; ?>
                            <option <?= $selected ?> value="<?= $warehouse['id'] ?>">
                                <?= h($warehouse['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <?php $selected = $wh_mains && isset($wh_mains[$user_id]) ? $wh_mains[$user_id] : ''; ?>
                    <?= typeahead($this->all_configs['db'], 'locations', false, $selected, $user_id, 'input-large', '',
                        '', true, false, $user_id); ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="3">
                <input type="submit" class="btn" name="set-warehouses_users" value="<?= l('Сохранить') ?>"/>
            </td>
        </tr>
        </tbody>
    </table>
</form>
