<div class="btn-group">
    <a class="btn btn-small dropdown-toggle" data-toggle="dropdown" href="">
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu pull-right">
        <li>
            <a href="">
                <i class="glyphicon glyphicon-pencil"></i>&nbsp;<?= l('Редактировать') ?>
            </a>
        </li>
        <li>
            <a href="<?= $this->all_configs['prefix'] ?>print.php?act=wh_purchase_invoice&object_id=<?= $invoice['id'] ?>"
               target="_blank">
                <i class="fa fa-print" aria-hidden="true"></i> &nbsp; <?= l('Распечатать накладную') ?>
            </a>
        </li>
        <?php if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders')): ?>
        <li>
            <a onclick="return alert_box(this, false, 'form-debit-so')" data-o_id="<?= $invoice['id'] ?>" href="">
                <i class="glyphicon glyphicon-save"></i>&nbsp; <?= l('Приходовать на склад') ?>
            </a>
        </li>
        <?php endif; ?>
        <?php if ($invoice['confirm'] <> 1 && $invoice['avail'] == 1 && $invoice['count_come'] == 0 &&
        (($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') && $invoice['sum_paid'] == 0 &&
        $invoice['count_come'] == 0) || $this->all_configs['oRole']->hasPrivilege('site-administration'))
        ): ?>
        <li>
            <a onclick="return cancel_purchase_invoice(this, '<?= $invoice['id'] ?>')" data-o_id="" href="">
                <i class="glyphicon glyphicon-remove"></i>&nbsp; <?= l('Отменить') ?>
            </a>
        </li>
        <?php endif; ?>
    </ul>
</div>
