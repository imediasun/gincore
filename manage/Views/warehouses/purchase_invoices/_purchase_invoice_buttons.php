<div class="btn-group">
    <a class="btn btn-small dropdown-toggle" data-toggle="dropdown" href="">
        <span class="caret"></span>
    </a>
    <ul class="dropdown-menu pull-right">
        <li>
            <a href="#" onclick="return edit_purchase_invoice(<?= $invoice['id'] ?>);">
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
                <a onclick="return alert_box(this, false, 'form-debit-purchase-invoice')" data-o_id="<?= $invoice['id'] ?>"
                   href="">
                    <i class="glyphicon glyphicon-save"></i>&nbsp; <?= l('Приходовать на склад') ?>
                </a>
            </li>
        <?php endif; ?>
        <?php if ($this->all_configs['oRole']->hasPrivilege('edit-suppliers-orders') && $invoice['state'] == PURCHASE_INVOICE_STATE_CAPITALIZED): ?>
            <li>
                <a onclick="return cancel_purchase_invoice(this, '<?= $invoice['id'] ?>')" data-o_id="" href="">
                    <i class="glyphicon glyphicon-remove"></i>&nbsp; <?= l('Отменить') ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</div>
