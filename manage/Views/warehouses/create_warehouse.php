<div class='panel-body'>
    <form method='POST' id="create-warehouse-modal">
        <div class='form-group'>
            <label><?= l('Название') ?>:<b class="text-danger">*</b> </label>
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
                <?= InfoPopover::getInstance()->createQuestion('l_warehouses_create_free') ?>
            </div>
            <div class='checkbox'>
                <label>
                    <input type='checkbox' class='btn consider_<?= $i ?>'
                           onclick='consider(this, "<?= $i ?>")' name='consider_all'
                           value='1'/>
                    <?= l('Учитывать в общем остатке') ?>
                </label>
                <?= InfoPopover::getInstance()->createQuestion('l_warehouses_create_total') ?>
            </div>
        </div>
        <div class='form-group'>
            <input type='hidden' value='1' name='type'/>
        </div>
        <div class='form-group'>
            <label>
                <?= l('Принадлежность к Сервисному центру') ?>:
                <?= InfoPopover::getInstance()->createQuestion('l_warehouses_create_service_center') ?>
            </label>
            <?= $this->renderFile('warehouses/warehouses_groups', array(
                'warehouse' => $warehouse,
                'groups' => $groups
            )) ?>
        </div>
        <div class='form-group'>
            <label>
                <?= l('Категория') ?>:
                <?= InfoPopover::getInstance()->createQuestion('l_warehouses_create_category') ?>
            </label>
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
            <label>
                <?= l('Локации') ?>:<b class="text-danger">*</b>
                <?= InfoPopover::getInstance()->createQuestion('l_warehouses_create_location') ?>
            </label>
            <?= $this->renderFile('warehouses/warehouses_locations', array(
                'locations' => empty($warehouse['locations']) ? array() : $warehouse['locations']
            )) ?>
        </div>
        <input type='hidden' name='warehouse-add' value='<?= l('Создать') ?>'/>
        <input type='hidden' name='modal' value='1'/>
    </form>
</div>
