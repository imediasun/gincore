<tr>
    <td class="span5">
        <p class="form-control-static"><?= l('manager') ?>:</p>
    </td>
    <td class="span6">
        <span class="input-group-btn">
            <select class="multiselect " name="managers[]" multiple="multiple">
                <option value="-1"><?= l('Сотрудник не указан') ?></option>
                <?php foreach ($managers as $manager): ?>
                    <option <?= ($mg_get && in_array($manager['id'], $mg_get) ? 'selected' : '') ?>
                        value="<?= $manager['id'] ?>"><?= htmlspecialchars($manager['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </span>
    </td>
</tr>
