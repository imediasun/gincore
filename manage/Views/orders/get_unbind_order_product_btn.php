<?php if ($this->all_configs['oRole']->hasPrivilege('debit-suppliers-orders') || $this->all_configs['oRole']->hasPrivilege('logistics')): ?>
    <input class="btn btn-xs" type="button" value="<?= l('Отвязать') ?>"
           onclick="alert_box(this,null,'bind-move-item-form',{object_id:<?= $item_id ?>},null,'warehouses/ajax/')">
<?php endif; ?>
