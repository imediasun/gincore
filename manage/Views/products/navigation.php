<form method="POST">
    <ul class="list-unstyled inline clearfix m-b-md">
        <li>
            <div class="input-group">
                <a class="btn btn-default" href="#" title=""
                   onclick="return show_action_form(this, 'action-form', '<?= json_encode($_GET) ?>')" title="<?= l('Действия')?>">
                    <i class="fa fa-sliders" aria-hidden="true"></i>
                </a>
            </div>
        </li>
        <li>
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
        </li>
        <li>
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
                </select>
            </div>
        </li>
        <li>
            <div class="input-group">
                <select name="categories[]" class="form-control multiselect" multiple="multiple"
                        data-placeholder="<?= l('Категории') ?>">
                    <?php if ($categories): ?>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?= $category['id'] ?>" <?= in_array($category['id'],
                                $current_categories) ? 'selected' : '' ?>>
                                <?= h($category['title']) ?>
                            </option>>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </li>
        <li>
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
        </li>
        <li>
            <div class="input-group">
                <button type="submit" name="filters" class="btn btn-primary"><?= l('Применить') ?></button>
            </div>
        </li>
        <li style="max-width:280px">
            <div class="input-group" style="width:250px; height: 34px">
                <input class="form-control" name="text" type="text" placeholder="<?= l('Название товара или артикул') ?>"
                       value="<?= (isset($_GET['s']) ? h($_GET['s']) : '') ?>"/>
                <span class="input-group-btn">
                <input type="submit" name="search" value="<?= l('Поиск') ?>" class="btn"/>
            </span>
            </div>
        </li>
        <?php if ($this->all_configs['oRole']->hasPrivilege('create-goods')): ?>
            <li class="pull-right">
                <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create"
                   class="btn btn-success pull-right">
                    <i class="fa fa-plus-circle" aria-hidden="true"></i>
                    <?= l('Добавить товар') ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</form>
