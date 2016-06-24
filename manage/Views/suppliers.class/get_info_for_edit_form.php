<?php if ($order['count_debit'] > 0): ?>
    <div class="form-group">
        <label><?= l('Создал') ?>:&nbsp;</label>
        <?= get_user_name($order) ?>
    </div>
<?php endif; ?>
<?php if ($order['count_come'] > 0): ?>
    <div class="form-group">
        <label><?= l('Принято') ?>:&nbsp;</label>
        <?= $order['count_come'] ?>&nbsp;<?= l('шт.') ?>
    </div>
<?php endif; ?>
<?php if ($order['count_debit'] > 0): ?>

    <div class="form-group">
        <label><?= l('Оприходовано') ?>:&nbsp;</label>
        <?= $order['count_debit'] ?>&nbsp;<?= l('шт.') ?>
        <?php $url = $this->all_configs['prefix'] . 'print.php?act=label&object_id=' . $order['items']; ?>
        <a target="_blank" title="Печать" href="<?= $url ?>"><i class="fa fa-print"></i></a>
    </div>
<?php endif; ?>
<?php if ($order['wh_id'] > 0): ?>
    <div class="form-group">
        <label><?= l('Склад') ?>:&nbsp;</label>
        <a class="hash_link" href="<?= $this->all_configs['prefix'] ?>warehouses?whs=<?= $order['wh_id'] ?>#show_items">
            <?= $order['wh_title'] ?>
        </a>
    </div>
    <div class="form-group">
        <label><?= l('Локация') ?>:&nbsp;</label>
        <?= $order['location'] ?>
    </div>
<?php endif; ?>
<h4><?= l('Операции') ?></h4>
