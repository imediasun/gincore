<?php $count = 0; ?>
<?php if (empty($ratings)): ?>
    <?= l('dashboard_no_stats'); ?>
<?php else: ?>
    <?php foreach ($ratings as $i => $o): ?>
        <?php if ($count == 6): ?>
            <div class="expand-button" onclick="return expand(this);"
                 style="text-align: center; cursor: pointer; margin-bottom: 10px">
                <?= l('Развернуть') ?> <i class="fa fa-chevron-down"></i>
            </div>
        <?php endif; ?>
        <div class="clearfix m-t-sm">
            <span class="font-bold no-margins">
                <?= ($o['fio'] ?: ('id ' . $o['user'])) ?>
                <span class="pull-right text-success"><?= round($o['avg_rating'], 2) ?></span>
            </span>
        </div>
        <?php $count++; ?>
    <?php endforeach; ?>
    <?php if ($count > 6): ?>
        <div class="collapse-button" onclick="return collapse(this);"
             style="text-align: center; cursor: pointer; display:none">
            <?= l('Свернуть') ?> <i class="fa fa-chevron-up"></i>
        </div>
    <?php endif; ?>
<?php endif; ?>
