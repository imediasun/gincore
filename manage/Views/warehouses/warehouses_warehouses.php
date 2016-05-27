<div class="bordered" style="padding: 0">
    <div class="well"
         style="border-radius: 0; border-top: none; border-left: none; border-right: none"><?= l('Всего') ?>:
        <?php if ($this->all_configs['oRole']->hasPrivilege('logistics')): ?>
            <?= $cost_of['cur_price'] ?>
            <?php if ($cost_of['cur_price'] != $cost_of['html']): ?>
                (<?= $cost_of['html'] ?>),
            <?php endif; ?>
        <?php endif; ?>
        <?= $cost_of['count'] ?> <?= l('шт.') ?>
    </div>
    <div style="margin:15px">
        <?= $filters ?>
    </div>
</div>
<div id="warehouses_content">
    <?php if (!empty($warehouses)): ?>
        <?php $i = 0; ?>
        <?php foreach ($warehouses as $warehouse): ?>
            <?= $this->renderFile('warehouses/warehouse', array(
                'warehouse' => $warehouse,
                'controller' => $controller,
                'i' => $i
            )); ?>
            <?php $i++; ?>
        <?php endforeach; ?>
    <?php endif; ?>
    <div class="add-warehouse-table" onclick="alert_box(this, false, 'create-warehouse')" data-toggle="tooltip"
         data-placement="top" title="<?= l('Добавить склад') ?>">
        <img src="<?= $prefix ?>img/add_new_cashbox.png">
    </div>
</div>
