<?php $count = 0; ?>
<?php if (empty($orders)): ?>
    <?= l('dashboard_no_stats'); ?>
<?php else: ?>
    <?php foreach ($orders as $i => $o): ?>
        <?php $p = $constructor->percent_format($o['orders'] / $allOrders * 100); ?>
        <div class="clearfix m-t-sm">
            <span class="font-bold no-margins">
                <?= ($o['fio'] ?: ('id ' . $o['engineer'])) ?>
                <span class="pull-right text-success"><?= $o['orders'] ?> (<?= $p ?>%)</span>
            </span>
        </div>
        <?php $count++; ?>
        <?php if ($count == 6): ?>
            <div class="expand-button" onclick="return expand(this);" style="text-align: center; cursor: pointer">
                <?= l('Развернуть') ?> <i class="fa fa-chevron-down"></i>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if ($count > 6): ?>
        <div class="collapse-button" onclick="return collapse(this);"
             style="text-align: center; cursor: pointer; display:none">
            <?= l('Свернуть') ?> <i class="fa fa-chevron-up"></i>
        </div>
    <?php endif; ?>
<?php endif; ?>
