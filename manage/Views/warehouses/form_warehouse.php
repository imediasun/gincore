<div class='panel panel-default'>
    <div class='panel-heading'>
        <div class="panel-tools">
            <a  class='accordion-toggle' data-toggle='collapse' data-parent='#accordion_warehouses'
                href='#collapse_warehouse_<?= $i ?>'>
                <i class="fa fa-chevron-down"></i>
            </a>
        </div>
        <a class='accordion-toggle' data-toggle='collapse' data-parent='#accordion_warehouses'
           href='#collapse_warehouse_<?= $i ?>'>
            <?php if (empty($warehouse)): ?>
                <?= l('Создать склад'); ?>
            <?php else: ?>
                <?= l('Редактировать склад') . ' ' . $warehouse['title'] ?>
            <?php endif; ?>
        </a>
    </div>
    <?php $readonly = !empty($warehouse) && in_array($warehouse['title'], array(
        lq('Брак'),
        lq('Клиент'),
        lq('Логистика'),
        lq('Недостача'),
    )) ? 'readonly  disabled' : '' ?>
    <div id='collapse_warehouse_<?= $i ?>' class='panel-body collapse <?= $i == 1 ? 'in' : '' ?>'>
        <div class='panel-body'>
            <form method='POST'>
                <div class='form-group'>
                    <label><?= l('Название') ?>: </label>
                    <input placeholder='<?= l(' введите название') ?>' class='form-control'
                           name='title' <?= $readonly ?>
                           value='<?= empty($warehouse) ? '' : h($warehouse['title']) ?>' required/>
                </div>
                <div class='form-group'>
                    <div class='checkbox'>
                        <label>
                            <input
                                data-consider='<?= $i ?>' <?= empty($warehouse) || $warehouse['consider_store'] == 1 ? 'checked' : '' ?>
                                type='checkbox'
                                onclick='consider(this, "<?= $i ?>")' class='btn consider_<?= $i ?>'
                                <?= $readonly ?>
                                name='consider_store' value='1'/>
                            <?= l('Учитывать в свободном остатке') ?>
                        </label>
                    </div>
                    <div class='checkbox'>
                        <label>
                            <input <?= empty($warehouse) || $warehouse['consider_all'] == 1 ? 'checked' : '' ?>
                                type='checkbox' class='btn consider_<?= $i ?>'
                                onclick='consider(this, "<?= $i ?>")' name='consider_all'
                                <?= $readonly ?>
                                value='1'/>
                            <?= l('Учитывать в общем остатке') ?>
                        </label>
                    </div>
                </div>
                <div class='form-group'>
                    <input type='hidden' value='1' name='type'/>
                </div>
                <div class='form-group'>
                    <label><?= l('Принадлежность к Сервисному центру') ?>: </label>
                    <?= $this->renderFile('warehouses/warehouses_groups', array(
                        'warehouse' => $warehouse,
                        'groups' => $groups,
                        'readonly' => $readonly
                    )) ?>
                </div>
                <div class='form-group'>
                    <label><?= l('Категория') ?>: </label>
                    <?= $this->renderFile('warehouses/warehouses_types', array(
                        'warehouse' => $warehouse,
                        'types' => $types,
                        'readonly' => $readonly
                    )) ?>
                </div>
                <div class='form-group'>
                    <label>
                        <?= l('Адрес для квитанции') ?>: </label>
                    <input class='form-control' name='print_address'
                           value='<?= !empty($warehouse) ? h($warehouse['print_address']) : '' ?>'/>
                </div>
                <div class='form-group'>
                    <label> <?= l('Телефон для квитанции') ?>: </label>
                    <input class='form-control' name='print_phone'
                           value='<?= !empty($warehouse) ? h($warehouse['print_phone']) : '' ?>'/>
                </div>
                <div class='form-group'>
                    <label><?= l('Локации') ?>: </label>
                    <?= $this->renderFile('warehouses/warehouses_locations', array(
                        'locations' => empty($warehouse['locations']) ? array() : $warehouse['locations'],
                    )) ?>
                </div>
                <div class='form-group'>
                    <?php if (!isset($isModal)): ?>
                        <?php if (empty($warehouse)): ?>
                            <input type='submit' class='btn' name='warehouse-add' value='<?= l('Создать') ?>'/>
                        <?php else: ?>
                            <input type='hidden' name='warehouse-id' value='<?= $warehouse['id'] ?>'/>
                            <input type='submit' class='btn' name='warehouse-edit' value='<?= l('Сохранить') ?>'/>
                            <input style='margin-left: 10px' type='submit' class='btn' name='warehouse-delete'
                                <?php if (!$warehouse['can_deleted'] || $warehouse['is_system'] || in_array($warehouse['title'],
                                        array(
                                            lq('Брак'),
                                            lq('Клиент'),
                                            lq('Логистика'),
                                            lq('Недостача'),
                                        ))
                                ): ?>
                                    onclick="alert('<?= l('Склад не подлежит удалению, так как задействован в складских и логистических операциях') ?>'); return false"
                                <?php endif; ?>
                                   value='<?= l('Удалить') ?>'/>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
