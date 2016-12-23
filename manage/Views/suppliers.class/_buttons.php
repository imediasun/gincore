<div class="btn-group">
    <a class="btn btn-small dropdown-toggle" data-toggle="dropdown" href="">
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu pull-right">
        <li>
            <?= $controller->supplier_order_number($order,
                '<i class="glyphicon glyphicon-pencil"></i>&nbsp;' . l('Редактировать')) ?>
        </li>
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
        <?php if ($order['avail'] == 1 &&
            $this->all_configs['oRole']->hasPrivilege('accounting') && $order['sum_paid'] == 0 && $order['count_come'] >
            0 && $only_pay == true
        ): ?>
            <li>
                <a onclick="return pay_supplier_order(this, 1, '<?= $order['id'] ?>')" data-o_id="" href="">
                    <i class="glyphicon glyphicon-usd"></i>&nbsp;<?= l('Оплатить') ?>
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
        <?php if ($order['confirm'] <> 1 && $order['avail'] == 1 && $order['count_come'] == 0 &&
            (($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') && $order['sum_paid'] == 0 &&
                    $order['count_come'] == 0) || $this->all_configs['oRole']->hasPrivilege('site-administration'))
        ): ?>
            <li>
                <a onclick="return avail_supplier_order(this, '<?= $order['id'] ?>', 0)" data-o_id="" href="">
                    <i class="glyphicon glyphicon-remove"></i>&nbsp; <?= l('Отменить') ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</div>
