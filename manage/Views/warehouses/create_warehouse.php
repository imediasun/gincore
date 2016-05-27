<div class='panel panel-default'>
    <div class='panel-heading'>
        <a class='accordion-toggle' data-toggle='collapse' data-parent='#accordion_warehouses'
           href='#collapse_warehouse_<?= $i ?>'>
            <?= l('Создать склад'); ?>
        </a>
    </div>
    <div id='collapse_warehouse_<?= $i ?>' class='panel-body collapse <?= $i == 1 ? 'in' : '' ?>'>
        <div class='panel-body'>
            <form method='POST'>
                <div class='form-group'>
                    <label><?= l('Название') ?>: </label>
                    <input placeholder='<?= l(' введите название') ?>' class='form-control' name='title'
                           value='' required/>
                </div>
                <div class='form-group'>
                    <div class='checkbox'>
                        <label>
                            <input
                                data-consider='<?= $i ?>'
                                type='checkbox'
                                onclick='consider(this, "<?= $i ?>")' class='btn consider_<?= $i ?>'
                                name='consider_store' value='1'/>
                            <?= l('Учитывать в свободном остатке') ?>
                        </label>
                    </div>
                    <div class='checkbox'>
                        <label>
                            <input type='checkbox' class='btn consider_<? $i ?>'
                                   onclick='consider(this, "<?= $i ?>")' name='consider_all'
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
                        'groups' => $groups
                    )) ?>
                </div>
                <div class='form-group'>
                    <label><?= l('Категория') ?>: </label>
                    <?= $this->renderFile('warehouses/warehouses_types', array(
                        'warehouse' => $warehouse,
                        'types' => $types
                    )) ?>
                </div>
                <div class='form-group'>
                    <label>
                        <?= l('Адрес для квитанции') ?>: </label>
                    <input class='form-control' name='print_address'
                           value=''/>
                </div>
                <div class='form-group'>
                    <label> <?= l('Телефон для квитанции') ?>: </label>
                    <input class='form-control' name='print_phone'
                           value=''/>
                </div>
                <div class='form-group'>
                    <label><?= l('Локации') ?>: </label>
                    <?= $this->renderFile('warehouses/warehouses_locations', array(
                        'locations' => empty($warehouse['locations']) ? array() : $warehouse['locations']
                    )) ?>
                </div>
                <div class='form-group'>
                    <?php if (!isset($isModal)): ?>
                        <input type='submit' class='btn' name='warehouse-add' value='<?= l('Создать') ?>'/>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
