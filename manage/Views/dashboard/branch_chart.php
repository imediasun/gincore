<div class="col-lg-12">
    <div class="hpanel">
        <div class="panel-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="text-center small">
                        <i class="fa fa-laptop"></i> <?= l('Сравнительный анализ заказов по филиалам') ?>
                    </div>
                    <div class="flot-chart" style="height: 160px">
                        <script>
                            <?php $colors = array(); ?>
                            $(function () {
                                init_chart(
                                    '#flot-branch-line-chart',
                                    [
                                        <?php foreach($orders as $wh => $points): ?>
                                        {
                                            points: [<?= implode(',', $points) ?>],
                                            legend: "<?= isset($branches[$wh]['title']) ? $branches[$wh]['title'] : '' ?>"
                                            <?php $colors[$wh] = "'{$branches[$wh]['color']}'"; ?>
                                        },
                                        <?php endforeach; ?>
                                    ],
                                    [<?= implode(',', $colors) ?>],
                                    <?= $tickSize ?>
                                );
                            });
                        </script>
                        <div class="flot-chart-content" id="flot-branch-line-chart"></div>
                    </div>
                </div>
                <div class="col-md-12 text-center">
                    &nbsp;
                </div>
                <div class="col-md-12 text-center">
                    <form method="POST">
                        <fieldset>
                            <select class="multiselect input-small" data-type="branches" multiple="multiple"
                                    name="branches_id[]">
                                <?= build_array_tree($branches, $selected) ?>
                            </select>
                            <button type="submit" class="btn btn-primary"> <?= l('Применить') ?></button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
