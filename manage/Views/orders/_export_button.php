<?php if ($this->all_configs['oRole']->hasPrivilege('export-clients-and-orders')): ?>
    <a href="<?= $this->all_configs['prefix'] ?>orders/ajax?act=export" target='_blank' class="btn btn-default"
       style="float:right"><?= l('Выгрузить') ?></a>
<?php endif; ?>
