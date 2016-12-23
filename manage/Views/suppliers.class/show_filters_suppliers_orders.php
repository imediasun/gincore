<form method="post">
    <?php if ($inner_wrapper): ?>
    <div class="clearfix theme_bg filters-box filters-box-sm p-sm m-b-md">
        <?php endif; ?>
        <div class="row row-15">
            <div class="col-sm-2 b-r">
                <div class="btn-group-vertical">
                    <a class="btn btn-default <?= (!isset($_GET['fco']) && !isset($_GET['marked']) && count($_GET) <= 3 ? 'disabled' : '') ?> text-left"
                       href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '#' . $hash ?>">
                        <?= l('Всего') ?> : <span id="count-clients-orders"><?= $count ?></span>
                    </a>
                    <a class="btn btn-default <?= (isset($_GET['fco']) && $_GET['fco'] == 'unworked' ? 'disabled' : '') ?> text-left"
                       href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?fco=unworked#' . $hash ?>">
                        <?= l('Необработано') ?>: <span
                            id="count-clients-untreated-orders"><?= $count_unworked ?></span>
                    </a>
                    <a class="btn btn-default <?= (isset($_GET['marked']) && $_GET['marked'] == 'so' ? 'disabled' : '') ?> text-left"
                       href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '?marked=so#' . $hash ?>">
                        <?= l('Отмеченные') ?>: <span class="icons-marked star-marked-active"> </span>
                        <span id="count-marked-so"><?= $count_marked ?></span>
                    </a>
                </div>
                <br><br>
                <div class="col-sm-12" style="white-space: nowrap; padding-left: 0">
                <input type="submit" name="filter-orders" class="btn btn-primary" value="<?= l('Фильтровать') ?>">
                <?= $this->LockButton->show($_GET['lock-button']) ?>
                </div>
            </div>
            <?php if ($show_nav): ?>
                <div class="col-sm-2 b-r">
                    <div class="checkbox">
                        <label>
                            <input <?= (isset($_GET['whk']) ? 'checked' : '') ?> type="checkbox" name="wh-kiev"/>
                            <?= l('Локально') ?>
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input <?= (isset($_GET['wha']) ? 'checked' : '') ?> type="checkbox" name="wh-abroad"/>
                            <?= l('Заграница') ?>
                        </label>
                    </div>
                </div>
            <?php endif; ?>
            <div class="col-sm-3 b-r">
                <?= $controller->show_filter_service_center(); ?>
                <div class="input-group" style="margin-bottom: 10px">
                    <p class="form-control-static"><?= l('Поставщик') ?>:</p>
                    <span class="input-group-btn">
                        <select class="multiselect form-control" multiple="multiple" name="suppliers[]">
                            <?php foreach ($suppliers as $supplier): ?>
                                <option <?= ((isset($_GET['sp']) && in_array($supplier['id'],
                                        explode(',', $_GET['sp']))) ? 'selected' : '') ?>
                                    value="<?= $supplier['id'] ?>">
                                    <?= $supplier['title'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select></span></div>
                <?php if ($show_nav): ?>
                    <div class="input-group">
                        <p class="form-control-static"><?= l('Статус') ?>:</p>
                        <span class="input-group-btn">
                            <select data-numberDisplayed="2" class="form-control multiselect"
                                    style="width: 98px" name="so-status">
                                <option value="0"><?= l('Выбрать') ?></option>
                                <option <?= (isset($_GET['sst']) && $_GET['sst'] == 1 ? 'selected' : '') ?> value="1">
                                    <?=  l('Не принятые') ?>
                                </option>
                                <option <?= (isset($_GET['sst']) && $_GET['sst'] == 2 ? 'selected' : '') ?> value="2">
                                    <?= l('Удаленные') ?>
                                </option>
                                <option <?= (isset($_GET['sst']) && $_GET['sst'] == 3 ? 'selected' : '') ?> value="3">
                                    <?= l('Просроченные') ?>
                                </option>
                                <option <?= (isset($_GET['sst']) && $_GET['sst'] == 4 ? 'selected' : '') ?> value="4">
                                    <?= l('Ожидаем поступления') ?>
                                </option>
                                <option <?= (isset($_GET['sst']) && $_GET['sst'] == 5 ? 'selected' : '') ?> value="5">
                                    <?= l('Не обработанные') ?>
                                </option>
                            </select>
                        </span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-sm-2 b-r">
                <div class="form-group">
                    <input type="text" placeholder="<?= l('Дата') ?>" name="date" class="daterangepicker form-control"
                           value="<?= $date ?>"/>
                    <input type="hidden" placeholder="<?= l('номер заказа') ?>" name="supplier_order_id"
                           class="form-control"
                           value="<?= (isset($_GET['so_id']) && $_GET['so_id'] > 0 ? $_GET['so_id'] : '') ?>"/>
                </div>

                <div class="form-group">
                    <input type="text" placeholder="<?= l('номер заказа поставщику') ?>" name="supplier_order_id_part"
                           class="form-control"
                           value="<?= (isset($_GET['pso_id']) && $_GET['pso_id'] > 0 ? $_GET['pso_id'] : '') ?>"/>
                </div>

                <div class="form-group">
                    <input type="text" placeholder="<?= l('номер заказа клиента') ?>" name="client-order"
                           class="form-control"
                           value="<?= (isset($_GET['co']) && $_GET['co'] > 0 ? $_GET['co'] : '') ?>"/>
                </div>
            </div>
            <div class="col-sm-3">
                <?php if ($show_my): ?>
                    <?php $my = $this->all_configs['oRole']->hasPrivilege('site-administration') || $this->all_configs['oRole']->hasPrivilege('edit-map') ? false : true; ?>
                    <div class="form-group">
                        <div class="checkbox">
                            <?php $hasPermision = $this->all_configs['oRole']->hasPrivilege('read-other-suppliers-orders'); ?>
                            <label>
                                <input name="my"
                                       type="checkbox" <?= $my || (isset($_GET['my']) && $_GET['my'] == 1 || !$hasPermision) ? ' checked ' : ''; ?> <?= ($my ? ' disabled ' : '') ?> <?= !$hasPermision? 'readonly': '' ?> />
                                <?= l('Только мои') ?>
                            </label>
                            <?= InfoPopover::getInstance()->createQuestion('l_its_only_my_orders') ?>
                        </div>
                    </div>
                <?php endif; ?>
                <div class="form-group">
                    <div class="checkbox">
                        <label>
                            <input name="noavail"
                                   type="checkbox" <?= ((isset($_GET['avail']) && $_GET['avail'] == 0) ? ' checked ' : '') ?> />
                            <?= l('Не активные') ?>
                        </label>
                    </div>
                </div>
                <div class="form-group">
                    <label><?= l('Товар') ?>:</label>
                    <?= typeahead($this->all_configs['db'], 'goods-goods', true, isset($_GET['by_gid']) &&
                    $_GET['by_gid'] ? $_GET['by_gid'] : 0, 6, 'input-small', 'input-mini') ?>
                </div>
            </div>
        </div>
        <?php if ($inner_wrapper): ?>
    </div>
<?php endif; ?>
</form>
