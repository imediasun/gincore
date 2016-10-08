<input type="button" class="btn btn-mini btn-success" onclick="edit_supplier_order(this)"
       value="<?= l('Сохранить') ?>"/>
<input <?= ($order['avail'] == 1 ? '' : 'disabled') ?> type="button" class="btn btn-mini btn-warning"
                                                       onclick="avail_supplier_order(this,  '<?= $order_id ?>', 0)"
                                                       value="<?= l('Отменить') ?>"/>
<?php if ($order['price'] > 0 && $order['supplier'] > 0): ?>
    <?php if ($order['confirm'] <> 1 && $order['avail'] == 1 &&
        $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') && $order['count_come'] == 0
    ): ?>
        <a class='btn btn-primary' onclick="return alert_box(this, false, 'form-accept-so')"
           data-o_id="<?= $order['id'] ?>"
           href="">
            <i class="glyphicon glyphicon-log-in"></i>&nbsp; <?= l('Принять') ?>
        </a>
    <?php endif; ?>
    <?php if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders')): ?>
        <?php if ($order['confirm'] <> 1 && $order['avail'] == 1 && $order['count_debit'] != $order['count_come'] &&
            $order['wh_id'] > 0
        ): ?>
            <?php $url = $this->all_configs['prefix'].'warehouses/ajax'; ?>
            <a class='btn btn-primary' onclick="return alert_box(this, false, 'form-debit-so', {}, null, '<?= $url ?>')"
               data-o_id="<?= $order['id'] ?>" href="">
                <i class="glyphicon glyphicon-save"></i>&nbsp; <?= l('Приходовать') ?>
            </a>
        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>
