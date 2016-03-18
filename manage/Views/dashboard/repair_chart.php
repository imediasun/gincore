<div class="col-lg-12">
    <div class="hpanel">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="text-center small">
                        <i class="fa fa-laptop"></i> <?= l('Сравнительный анализ по категориям/моделям/запчастям') ?>
                    </div>
                    <div class="flot-chart" style="height: 160px">
                        <script>
                            $(function () {
                                init_chart(
                                    '#flot-repair-line-chart',
                                    [
                                        <?php foreach($byItems as $id => $points): ?>
                                        {
                                            points: [<?= implode(',', $points) ?>],
                                            legend: "<?= htmlspecialchars($items[$id]['title']) ?>"
                                        },
                                        <?php endforeach; ?>
                                        <?php foreach($byModels as $id => $points): ?>
                                        {
                                            points: [<?= implode(',', $points) ?>],
                                            legend: "<?= htmlspecialchars($models[$id]['title']) ?>"
                                        },
                                        <?php endforeach; ?>
                                        <?php foreach($byCategories as $id => $points): ?>
                                        {
                                            points: [<?= implode(',', $points) ?>],
                                            legend: "<?= htmlspecialchars($categories[$id]['title']) ?>"
                                        },
                                        <?php endforeach; ?>
                                    ]
                                );
                            });
                        </script>
                        <div class="flot-chart-content" id="flot-repair-line-chart"></div>
                    </div>
                </div>
                <div class="col-md-12 text-center">
                    &nbsp;
                </div>
                <div class="col-md-12 text-center">
                    <form method="POST">
                        <fieldset>
                            <label class="col-sm-3">
                                <?= l('Категории'); ?>:

                                <select class="multiselect input-small" data-type="categories" multiple="multiple"
                                        name="categories_id[]">
                                    <?= build_array_tree($categories, $selectedCategories) ?>
                                </select>
                            </label>
                            <label class="col-sm-3">
                                <?= l('Модели'); ?>:

                                <select class="multiselect input-small" data-type="models" multiple="multiple"
                                        name="models_id[]">
                                    <?= build_array_tree($models, $selectedModels) ?>
                                </select>
                            </label>
                            <label class="col-sm-3">
                                <?= l('Запчасти'); ?>:
                                <select class="multiselect input-small" data-type="goods" multiple="multiple"
                                        name="goods_id[]">
                                    <?= build_array_tree($items, $selectedItems) ?>
                                </select>
                            </label>
                            <div class="col-sm-3">
                                <button type="submit" class="btn btn-primary"> <?= l('Применить') ?></button>
                            </div>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
