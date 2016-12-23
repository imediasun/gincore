<form method="POST">
    <table class="table-navigation">
        <tr>
            <td>
                <div class="input-group">
                    <a class="btn btn-default" href="#" title=""
                       onclick="return show_action_form(this, 'action-form')"
                       title="<?= l('Действия') ?>">
                        <i class="fa fa-sliders" aria-hidden="true"></i>
                    </a>
                </div>

            </td>
            <td>
                <div class="input-group">
                    <select name="avail[]" class="form-control multiselect" multiple="multiple"
                            data-placeholder="<?= l('Наличие') ?>">
                        <option value="free" <?= in_array('free', $current_avail) ? 'selected' : '' ?>>
                            <?= l('Есть в свободном остатке') ?>
                        </option>
                        <option value="all" <?= in_array('all', $current_avail) ? 'selected' : '' ?>>
                            <?= l('Есть в общем остатке') ?>
                        </option>
                        <option value="not" <?= in_array('not', $current_avail) ? 'selected' : '' ?>>
                            <?= l('Нет в наличии') ?>
                        </option>
                        <option value="mb" <?= in_array('mb', $current_avail) ? 'selected' : '' ?>>
                            <?= l('Ниже неснижаемого остатка') ?>
                        </option>
                    </select>
                </div>

            </td>
            <td>
                <div class="input-group">
                    <select name="show[]" class="form-control multiselect" multiple="multiple"
                            data-placeholder="<?= l('Отобразить') ?>">
                        <option value="my" <?= in_array('my', $current_show) ? 'selected' : '' ?>>
                            <?= l('Мои товары') ?>
                        </option>
                        <option value="empty" <?= in_array('empty', $current_show) ? 'selected' : '' ?>>
                            <?= l('Не заполненные') ?>
                        </option>
                        <option value="services" <?= in_array('services', $current_show) ? 'selected' : '' ?>>
                            <?= l('Услуги') ?>
                        </option>
                        <option value="items" <?= in_array('items', $current_show) ? 'selected' : '' ?>>
                            <?= l('Товары') ?>
                        </option>
                        <option value="na" <?= in_array('na', $current_show) ? 'selected' : '' ?>>
                            <?= l('Не активные') ?>
                        </option>
                    </select>
                </div>

            </td>
            <td>
                <div class="input-group">
                    <select name="categories[]" class="form-control multiselect" multiple="multiple"
                            data-placeholder="<?= l('Категории') ?>">
                        <?= build_array_tree($categories, explode('-', $_GET['cats'])); ?>
                    </select>
                </div>

            </td>
            <td>
                <div class="input-group">
                    <select name="warehouses[]" class="form-control multiselect" multiple="multiple"
                            data-placeholder="<?= l('По складам') ?>">
                        <?php if ($warehouses): ?>
                            <?php foreach ($warehouses as $wh_id => $wh_title): ?>
                                <option value="<?= $wh_id ?>" <?= in_array($wh_id,
                                    $current_warehouses) ? 'selected' : '' ?>>
                                    <?= h($wh_title) ?>
                                </option>>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

            </td>
            <td>
                <div class="input-group">
                    <button type="submit" name="filters" class="btn btn-primary"><?= l('Применить') ?></button>
                </div>

            </td>
            <td width="100%">
                <div class="input-group" style="min-width:150px; max-width: 350px; height: 34px">
                    <input class="form-control" name="text" type="text"
                           placeholder="<?= l('Название товара или артикул') ?>"
                           value="<?= (isset($_GET['s']) ? h($_GET['s']) : '') ?>"/>
                    <span class="input-group-btn">
                <button type="submit" name="search" class="btn">
                    <i class="fa fa-search" aria-hidden="true" title="<?= l('Поиск') ?>"></i>
                </button>
            </span>
                </div>

            </td>
            <td>
                <?php if ($this->all_configs['oRole']->hasPrivilege('create-goods')): ?>
                        <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create"
                           class="btn btn-success pull-right">
                            <i class="fa fa-plus-circle" aria-hidden="true"></i>
                            <?= l('Товар') ?>
                        </a>
                <?php endif; ?>

            </td>
        </tr>
    </table>
</form>
