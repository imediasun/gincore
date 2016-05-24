<div class='panel panel-default'>
    <div class='panel-heading'>
        <a class='accordion-toggle' data-toggle='collapse' data-parent='#accordion_warehouses_types'
           href='#collapse_warehouse_type_<?= empty($type) ? 0 : $type['id'] ?>'>
            <?php if (empty($type)): ?>
                <?= l('Создать категорию склада'); ?>
            <?php else: ?>
                <?= l('Редактировать категорию склада') . ' "' . h($type['name']) . '"' ?>
            <?php endif; ?>
        </a>
    </div>
    <div id='collapse_warehouse_type_<?= empty($type) ? 0 : $type['id'] ?>' class='panel-collapse collapse'>
        <div class='panel-body'>
            <form method='POST'>
                <div class='form-group'>
                    <label><?= l('Название') ?>: </label>
                    <input placeholder='<?= l('введите название') ?>' class='form-control' name='name'
                           value='<?= empty($type) ? '' : h($type['name']) ?>'/>
                </div>
                <div class='form-group'>
                    <label><?= l('Иконка') ?> (fa fa-home): </label>
                    <input placeholder='<?= l('введите иконку') ?>' class='form-control' name='icon'
                           value='<?= empty($type) ? '' : h($type['icon']) ?>'/>
                </div>
                <div class='form-group'>
                    <label></label>
                    <?php if (empty($type)): ?>
                        <input type='submit' class='btn' name='warehouse-type-add' value='<?= l('Создать') ?>'/>
                    <?php else: ?>
                        <input type='hidden' name='warehouse-type-id' value='<?= $type['id'] ?>'/>
                        <input type='submit' class='btn' name='warehouse-type-edit' value='<? l('Редактировать') ?>'/>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
