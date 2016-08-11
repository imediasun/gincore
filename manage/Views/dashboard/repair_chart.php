<div class="col-lg-12">
    <div class="hpanel">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="text-center small">
                        <i class="fa fa-laptop"></i> <?= l('Сравнительный анализ по категориям/моделям/запчастям') ?>
                    </div>
                    <div id='js-repair-chart-part' class="flot-chart" style="height: 160px">
                        <?= $this->renderFile('dashboard/_repair_chart', array(
                            'byItems' => $byItems,
                            'byModels' => $byModels,
                            'byCategories' => $byCategories,
                            'ticks' => $ticks,
                            'tickSize' => $tickSize
                        )) ?>
                    </div>
                </div>
                <div class="col-md-12 text-center">
                    &nbsp;
                </div>
                <div class="col-md-12 text-center">
                    <form method="POST" id="repair-chart-form">
                        <fieldset>
                            <label class="col-sm-4">
                                <?= l('Категории'); ?>:
                                <div class="btn btn-primary"
                                       data-href="<?= $this->all_configs['prefix'] ?>dashboard/ajax?act=category-select"
                                       onclick="return load_selects(this);"
                                       data-child="#load-models-btn" >
                                    <?= l('Загрузить') ?>
                                </div>
                            </label>
                            <label class="col-sm-3">
                                <?= l('Модели'); ?>:
                                <div id="load-models-btn"
                                     data-href="<?= $this->all_configs['prefix'] ?>dashboard/ajax?act=models-select"
                                     data-child="#load-items-btn"
                                     data-parent="">

                                </div>
                            </label>
                            <label class="col-sm-3">
                                <?= l('Запчасти'); ?>:
                                <div id="load-items-btn"
                                     data-href="<?= $this->all_configs['prefix'] ?>dashboard/ajax?act=items-select"
                                     data-parent="">
                                </div>
                            </label>
                            <div class="col-sm-2">
                                <button type="submit" class="btn btn-primary" style="margin-top: 19px"
                                        onclick="return load_repair_chart_part(this);"> <?= l('Применить') ?></button>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
