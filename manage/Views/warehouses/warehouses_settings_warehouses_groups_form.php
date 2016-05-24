<div class='panel panel-default'>
    <div class='panel-heading'>
        <a class='accordion-toggle' data-toggle='collapse' data-parent='#accordion_warehouses_groups'
           href='#collapse_warehouse_group_<?= empty($group) ? 0 : $group['id'] ?>'>
            <?php if (empty($group)): ?>
                <?= l('Создать  сервисный центр') . ' (' . l('группу складов') . ')' ?>
            <?php else: ?>
                <?= l('Редактировать группу склада') . ' "' . h($group['name']) . '"' ?>
            <?php endif; ?>
        </a>
    </div>
    <div id='collapse_warehouse_group_<?= empty($group) ? 0 : $group['id'] ?>' class='panel-collapse collapse'>
        <div class='panel-body'>
            <form method='POST'>
                <div class='form-group'>
                    <label><?= l('Название') ?>: </label>
                    <input placeholder='<?= l('введите название') ?>' class='form-control' name='name'
                           value='<?= empty($group) ? '' : h($group['name']) ?>'/>
                </div>
                <div class='form-group'>
                    <label><?= l('Цвет') ?> (#000000): </label>
                    <input placeholder='<?= l('введите цвет') ?>' class='colorpicker form-control' name='color'
                           value='<?= empty($group) ? '' : h($group['color']) ?>'/>
                </div>
                <div class='form-group'>
                    <label><?= l('Адрес') ?>: </label>
                    <input placeholder='<?= l('введите адрес') ?>' class='form-control' name='address'
                           value='<?= empty($group) ? '' : h($group['address']) ?>'/>
                </div>
                <div class='form-group'>
                    <label></label>
                    <?php if (empty($group)): ?>
                        <input type='submit' class='btn' name='warehouse-group-add' value='<?= l('Создать') ?>'/>
                    <?php else: ?>
                        <input type='hidden' name='warehouse-group-id' value='<?= $group['id'] ?>'/>
                        <input type='submit' class='btn' name='warehouse-group-edit' value='<?= l('Редактировать') ?>'/>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
