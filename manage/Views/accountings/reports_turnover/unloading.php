<?php $href = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/export?act=reports-turnover&' . get_to_string(); ?>
<div class="col-sm-12 well">
    <a class="btn btn-default pull-right" href="<?= $href ?>" target="_blank"><?= l('Выгрузить') ?></a>
    <div class="col-sm-5">
        <table class="table borderless">
            <tr>
                <td>
                    <?= l('Оборот') ?>:
                </td>
                <td>
                    <strong><?= show_price($turnover, 2, ' ') ?>
                        <?= (array_key_exists($cco,
                            $currencies) ? ' ' . $currencies[$cco]['shortName'] : '') ?></strong>
                </td>
            </tr>
            <?php if ($isAdmin): ?>
                <tr>
                    <td>
                        <?= l('Операционная прибыль') ?>:  <?=  InfoPopover::getInstance()->createQuestion('l_accountings_report_operating_income_info')?>
                    </td>
                    <td>
                        <a id="show_reports_turnover_profit_button" class="btn"><?= l('Рассчитать') ?> </a>
                        <strong> <span class="reports_turnover_profit invisible">
                    <?= show_price($profit, 2, ' ') ?>
                    <?= (array_key_exists($cco, $currencies) ? ' ' . $currencies[$cco]['shortName'] : '') ?>
                </span> </strong>
                    </td>
                </tr>
                <tr>
                    <td>
                        <?= l('Средняя наценка') ?>:  <?=  InfoPopover::getInstance()->createQuestion('l_accountings_report_average_mergine_info')?>
                    </td>
                    <td>
                        <a id="show_reports_turnover_margin_button" class="btn"> <?= l('Рассчитать') ?> </a>
                        <strong> <span class="reports_turnover_margin invisible">
                    <?= (is_numeric($avg) ? round($avg, 2) : 0) ?> %
                </span> </strong>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
</div>
