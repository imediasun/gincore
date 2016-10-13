<div class="col-sm-12 well">
    <form method="POST">
        <table class="table table-borderless" style="margin-bottom: 0">
            <tbody>
            <tr>
                <td>
                    <select name="avail[]" class="form-control multiselect" multiple="multiple" data-placeholder="<?= l('Наличие') ?>">
                        <option value="free" <?= in_array('free', $current_avail)? 'selected': '' ?>>
                            <?= l('Есть в свободном остатке') ?>
                        </option>
                        <option value="all" <?= in_array('all', $current_avail)? 'selected': '' ?>>
                            <?= l('Есть в общем остатке') ?>
                        </option>
                        <option value="not" <?= in_array('not', $current_avail)? 'selected': '' ?>>
                            <?= l('Нет в наличии') ?>
                        </option>
                    </select>
                </td>
                <td>
                    <select name="show[]" class="form-control multiselect" multiple="multiple"  data-placeholder="<?= l('Отобразить') ?>">
                        <option value="my" <?= in_array('my', $current_show)? 'selected': '' ?>>
                            <?= l('Мои товары') ?>
                        </option>
                        <option value="empty" <?= in_array('empty', $current_show)? 'selected': '' ?>>
                            <?= l('Не заполненные') ?>
                        </option>
                        <option value="services" <?= in_array('services', $current_show)? 'selected': '' ?>>
                            <?= l('Услуги') ?>
                        </option>
                        <option value="items" <?= in_array('items', $current_show)? 'selected': '' ?>>
                            <?= l('Товары') ?>
                        </option>
                    </select>
                </td>
                <td>
                    <select name="categories[]" class="form-control multiselect" multiple="multiple"  data-placeholder="<?= l('Категории') ?>">
                        <?php if ($categories): ?>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>"  <?= in_array($category['id'], $current_categories)? 'selected': '' ?>>
                                    <?= h($category['title']) ?>
                                </option>>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </td>
                <td>
                    <select name="warehouses[]" class="form-control multiselect" multiple="multiple"  data-placeholder="<?= l('По складам') ?>">
                        <?php if ($warehouses): ?>
                            <?php foreach ($warehouses as $wh_id => $wh_title): ?>
                                <option value="<?= $wh_id ?>" <?= in_array($wh_id, $current_warehouses)? 'selected': '' ?>>
                                    <?= h($wh_title) ?>
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
