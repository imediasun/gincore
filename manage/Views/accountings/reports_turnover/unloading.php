<?php $href = $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] . '/export?act=reports-turnover&' . get_to_string(); ?>
<div class="col-sm-12 well" style="white-space: nowrap;">
    <table class="table borderless"  style="nowrap; margin-bottom: 0px">
        <tr>
            <td>
                <?= l('Оборот') ?>:
            </td>
            <td>
                <strong><?= show_price($turnover, 2, ' ') ?>
                    <?= (array_key_exists($cco,
                        $currencies) ? ' ' . $currencies[$cco]['shortName'] : '') ?></strong>
            </td>
            <?php if ($isAdmin): ?>
                <td>
                    <?= l('Операционная прибыль') ?>:<?=  InfoPopover::getInstance()->createQuestion('l_accountings_report_operating_income_info')?>
                </td>
                <td>
                    <a id="show_reports_turnover_profit_button" class="btn"><?= l('Рассчитать') ?> </a>
                    <strong> <span class="reports_turnover_profit invisible">
                    <?= show_price($profit, 2, ' ') ?>
                    <?= (array_key_exists($cco, $currencies) ? ' ' . $currencies[$cco]['shortName'] : '') ?>
                </span> </strong>
                </td>
                <td>
                    <?= l('Средняя наценка') ?>:<?=  InfoPopover::getInstance()->createQuestion('l_accountings_report_average_mergine_info')?>
                </td>
                <td>
                    <a id="show_reports_turnover_margin_button" class="btn"> <?= l('Рассчитать') ?> </a>
                    <strong> <span class="reports_turnover_margin invisible">
                    <?= (is_numeric($avg) ? round($avg, 2) : 0) ?> %
                </span> </strong>
                </td>
            <?php endif; ?>
            <td class="col-sm-6">
                <a class="btn btn-default pull-right" href="<?= $href ?>" target="_blank"><?= l('Выгрузить') ?></a>
            </td>
        </tr>
    </table>
</div>
