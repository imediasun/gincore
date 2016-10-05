<div class="col-sm-12">
    <div class="row-fluid">
        <div class="col-sm-9">
            <form>
                <table class="table table-borderless" style="table-layout: fixed">
                    <tbody>
                    <tr>
                        <td>
                            <?= l('Наличие') ?>
                        </td>
                        <td>
                            <?= l('Отобразить') ?>
                        </td>
                        <td>
                            <?= l('По складам') ?>
                        </td>
                        <td>
                            <?= l('Менеджеры') ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <select name="avail[]">
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
                            <select name="show[]">
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
                            <select name="warehouse[]">
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
                            <select name="manager[]">
                                <?php if ($managers): ?>
                                    <?php foreach ($managers as $mn_id => $mn_title): ?>
                                        <option value="<?= $mn_id ?>">
                                            <?= h($mn_title) ?>
                                        </option>>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <button type="submit" name="filters"><?= l('Применить') ?></button>
                        </td>
                    </tr>
                    </tbody>
                </table>

            </form>
        </div>
        <div class="col-sm-3">
            <table class="table table-borderless">
                <tbody>
                <tr>
                    <td>

                        <form class="pull-left m-r-xs" method="post">
                            <div class="input-group" style="width:250px">
                                <input class="form-control" name="text" type="text"
                                       value="<?= (isset($_GET['s']) ? h($_GET['s']) : '') ?>"/>
                                <span class="input-group-btn">
                                <input type="submit" name="search" value="<?= l('Поиск') ?>" class="btn"/>
                            </span>
                            </div>
                        </form>
                    </td>
                    <td>
                        <?php if ($this->all_configs['oRole']->hasPrivilege('create-goods')): ?>
                            <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create"
                               class="btn btn-success pull-right">
                                <?= l('Добавить товар') ?>
                            </a>
                        <?php endif; ?>

                    </td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
