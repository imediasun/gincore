<div class="col-sm-12 well">
    <form>
        <table class="table table-borderless" style="">
            <tbody>
            <tr>
                <td>
                    <select name="avail[]" class="form-control multiselect" multiple="multiple">
                        <option value="-1"><?= l('Наличие') ?></option>
                        <option value="free">
                            <?= l('Есть в свободном остатке') ?>
                        </option>
                        <option value="all">
                            <?= l('Есть в общем остатке') ?>
                        </option>
                        <option value="not">
                            <?= l('Нет в наличии') ?>
                        </option>
                    </select>
                </td>
                <td>
                    <select name="show[]" class="form-control multiselect" multiple="multiple">
                        <option value="-1"><?= l('Отобразить') ?></option>
                        <option value="my">
                            <?= l('Мои товары') ?>
                        </option>
                        <option value="empty">
                            <?= l('Не заполненные') ?>
                        </option>
                        <option value="services">
                            <?= l('Услуги') ?>
                        </option>
                        <option value="items">
                            <?= l('Товары') ?>
                        </option>
                    </select>
                </td>
                <td>
                    <select name="warehouse[]" class="form-control multiselect" multiple="multiple">
                        <option value="-1"><?= l('По складам') ?></option>
                        <?php if ($warehouses): ?>
                            <?php foreach ($warehouses as $wh_id => $wh_title): ?>
                                <option value="<?= $wh_id ?>">
                                    <?= h($wh_title) ?>
                                </option>>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </td>
                <td>
                    <select name="manager[]" class="form-control multiselect" multiple="multiple">
                        <option value="-1"><?= l('Менеджеры') ?></option>
                        <?php if ($managers): ?>
                            <?php foreach ($managers as $mn_id => $mn_title): ?>
                                <option value="<?= $mn_id ?>">
                                    <?= h($mn_title) ?>
                                </option>>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </td>
                <td>
                    <button type="submit" name="filters" class="btn btn-primary"><?= l('Применить') ?></button>
                </td>
            </tr>
            </tbody>
        </table>

    </form>
</div>
