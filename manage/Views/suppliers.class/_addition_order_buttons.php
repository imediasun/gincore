<div class="btn-group">
    <a class="btn btn-small btn-default dropdown-toggle" data-toggle="dropdown" href="">
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu pull-right">
        <li>
            <a href="<?= $this->all_configs['prefix']?>print.php?act=purchase_invoice&object_id=<?= $order['id'] ?>" target="_blank">
                <i class="fa fa-print" aria-hidden="true"></i> &nbsp; <?= l('Распечатать накладную') ?>
            </a>
        </li>
        <?php if ($order['avail'] == 1): ?>
            <li>
                <a onclick="return alert_box(this, false, 'so-operations')" data-o_id="<?= $order['id'] ?>" href="">
                    <i class="glyphicon glyphicon-wrench"></i>&nbsp; <?= l('Ремонты') ?>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($order['confirm'] <> 1 && $order['avail'] == 1 &&
            $this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') && $order['count_come'] == 0 &&
            $only_debit == false
        ): ?>
            <li>
                <a onclick="return alert_box(this, false, 'form-accept-so')" data-o_id="<?= $order['id'] ?>" href="">
                    <i class="glyphicon glyphicon-log-in"></i>&nbsp; <?= l('Принять') ?>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders')): ?>
            <?php if ($order['confirm'] <> 1 && $order['avail'] == 1 && $order['count_debit'] != $order['count_come'] &&
                $order['wh_id'] > 0 && $only_debit == true
            ): ?>
                <li>
                    <a onclick="return alert_box(this, false, 'form-debit-so')" data-o_id="<?= $order['id'] ?>" href="">
                        <i class="glyphicon glyphicon-save"></i>&nbsp; <?= l('Приходовать на склад') ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($order['confirm'] == 0 && $order['avail'] == 1 &&
            (($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') && $order['sum_paid'] == 0 &&
                    $order['count_come'] == 0) || $this->all_configs['oRole']->hasPrivilege('site-administration'))
        ): ?>
            <?php if ($order['unavailable'] == 0): ?>
                <li>
                    <a onclick="return end_supplier_order(this, '<?= $order['id'] ?> ', 0)" data-o_id="" href="">
                        <i class="glyphicon glyphicon-ban-circle"></i>&nbsp;<?= l('Запчасть не доступна к заказу') ?>
                    </a>
                </li>
            <?php endif; ?>
        <?php endif; ?>
    </ul>
</div>
