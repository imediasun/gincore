<div class="panel-group">
    <div class="panel panel-default">
        <div class="panel-heading" role="tab" id="headingOne">
            <h4 class="panel-title">
                <a class="accordion-toggle" data-toggle="collapse" data-parent="#transaction_filters"
                   href="#transaction_filters_collapse<?= (int)$isContractors ?>"><?= l('Фильтры') ?></a>
            </h4>
        </div>
        <div id="transaction_filters_collapse<?= (int)$isContractors ?>" class="panel-collapse collapse <?= $in ?>">
            <div class="panel-body">
                <form method="post">
                    <div class="form-group">
                        <label><?= l('Транзакции за') ?>:</label>
                        <div class="row container-fluid">
                            <div class="col-sm-3">
                                <?= $month_select ?>
                            </div>
                            <div class="col-sm-3">
                                <input type="text" name="date" value="<?= $date ?>"
                                       class="form-control daterangepicker"/>
                            </div>
                            <div class="col-sm-3">
                                <a class="hash_link"
                                   href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>?df=<?= date('01.01.Y',
                                       time()) ?>&dt=<?= date('31.12.Y',
                                       time()) . (($isContractors == true) ? '#transactions-contractors' : '#transactions-cashboxes') ?>">
                                    <?= l('Весь') ?> <?= date('Y', time()) ?> <?= l('год') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?= l('Кассы') ?>:</label>
                        <div class="row container-fluid">
                            <div class="col-sm-3">
                                <select class="form-control" name="include_cashboxes">
                                    <option value="1"><?= l('Показать') ?></option>
                                    <option
                                        <?= ((isset($_GET['cbe']) && $_GET['cbe'] == -1) ? 'selected' : '') ?>
                                        value="-1"><?= l('Исключить') ?>
                                    </option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <select class="multiselect input-small" name="cashboxes[]" multiple="multiple">
                                    <?= build_array_tree($cashboxes,
                                        ((isset($_GET['cb'])) ? explode(',', $_GET['cb']) : array())) ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group"><label><?= l('Статьи') ?>:</label>
                        <div class="row container-fluid">
                            <div class="col-sm-3"><select class="form-control" name="include_categories">
                                    <option value="1"><?= l('Показать') ?></option>
                                    <option
                                        <?= ((isset($_GET['cge']) && $_GET['cge'] == -1) ? 'selected' : '') ?>
                                        value="-1">
                                        <?= l('Исключить') ?>
                                    </option>
                                </select>
                            </div>
                            <div class="col-sm-3">
                                <select class="multiselect form-control" name="categories[]" multiple="multiple">
                                    <?= build_array_tree($categories,
                                        ((isset($_GET['cg'])) ? explode(',', $_GET['cg']) : array())); ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
                        <div class="form-group"><label><?= l('Контрагенты') ?>:</label>
                            <div class="row container-fluid">
                                <div class="col-sm-3">
                                    <select class="form-control" name="include_contractors">
                                        <option value="1"><?= l('Показать') ?></option>
                                        <option
                                            <?= ((isset($_GET['cte']) && $_GET['cte'] == -1) ? 'selected' : '') ?>
                                            value="-1">
                                            <?= l('Исключить') ?>
                                        </option>
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <select class="multiselect form-control" name="contractors[]" multiple="multiple">
                                        <?= build_array_tree($contractors,
                                            ((isset($_GET['ct'])) ? explode(',', $_GET['ct']) : array())); ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group"><label class="control-label"><?= l('По') ?>:</label>
                            <div class="row container-fluid">
                                <div class="col-sm-3">
                                    <input class="form-control" value="<?= $value ?>"
                                           onkeydown="return isNumberKey(event, this)"
                                           type="text" name="by_id" placeholder="<?= l('Введите ид') ?>"/>
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-control" name="by">
                                        <option value="0"></option>
                                        <option <?= ((isset($_GET['o_id']) && $_GET['o_id'] > 0) ? 'selected' : '') ?>
                                            value="o_id">
                                            <?= l('Заказу клиента') ?>
                                        </option>
                                        <option <?= ((isset($_GET['s_id']) && $_GET['s_id'] > 0) ? 'selected' : '') ?>
                                            value="s_id">
                                            <?= l('Заказу поставщика') ?>
                                        </option>
                                        <option <?= ((isset($_GET['t_id']) && $_GET['t_id'] > 0) ? 'selected' : '') ?>
                                            value=" t_id">
                                            <?= l('Транзакции касс') ?>
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <div class="checkbox">
                            <label class="">
                                <?php if (isset($_GET['grp']) && $_GET['grp'] == 1): ?>
                                    <input type="checkbox" name="group" value="1"/>
                                <?php else: ?>
                                    <input type="checkbox" checked name="group" value="1"/>
                                <?php endif; ?>
                                <?= l('Группировать') ?>
                            </label>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="controls">
                            <input class="btn btn-primary" type="submit" name="filter-transactions"
                                   value="<?= l('Применить') ?>"/>
                        </div>
                    </div>

                    <?php if ($isContractors): ?>
                        <input type="hidden" name="hash" value="#transactions-contractors"/>
                    <?php else: ?>
                        <input type="hidden" name="hash" value="#transactions-cashboxes"/>
                    <?php endif; ?>

                </form>
            </div>
        </div>
    </div>
</div>