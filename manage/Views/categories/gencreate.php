<?php if ($this->all_configs['oRole']->hasPrivilege('create-filters-categories')): ?>
    <?php if ($ajax && !$is_modal): ?>
        <div class="emulate_form ajax_form" data-callback="select_typeahead_device" data-method="post" data-action="<?= $this->all_configs['prefix'] ?>categories/ajax/?act=create_new">
    <?php else: ?>
        <form method="post" action="<?= $this->all_configs['prefix'] ?>categories/create" id="category-create-form">
        <fieldset>
    <?php endif; ?>
    <legend><?= l('Добавление новой категории') ?> (<?= l('название устройства') ?>)</legend>
    <?php if (isset($_GET['error']) && $_GET['error'] == 'url'): ?>
        <p class="text-error"><?= l('Категория с таким названием уже существует') ?></p>
    <?php endif; ?>
    <div class="form-group">
        <label><?= l('Название') ?>:</label>
        <input autocomplete="off"
               placeholder="<?= l('Укажите название устройства или категории. Пример: IPhone 5') ?>"
               class="form-control global-typeahead" data-anyway="1" data-table="categories" name="title"
               value="<?= ($name ? htmlspecialchars($name) : '') ?>"/>
    </div>
    <div class="form-group">
        <div class="checkbox">
            <label>
                <input name="avail" type="checkbox" checked="checked">
                <?= l('Активность') ?>
            </label>
            <?= InfoPopover::getInstance()->createQuestion('l_create_category_active_info') ?>
        </div>
    </div>
    <div class="form-group">
        <label><?= l('Высшая (родительская) категория') ?>:</label>
        <?= typeahead($this->all_configs['db'], 'categories', false, 0, 1, 'input-large', '', '',
            false, false, '', false,
            l('Укажите название высшей категории или оставьте пустым. Пример: Iphone')) ?>
    </div>
    <div class="form-group">
        <label><?= l('Описание') ?>:</label>
        <textarea placeholder="<?= l('краткое описание') ?>" name="content" class="form-control" rows="3"></textarea>
    </div>
    <?php if($is_modal): ?>
        <input type="hidden" name="create-category" value="<?= l('Создать') ?>"/>
    <?php else: ?>
        <div class="form-group">
            <input class="btn btn-primary" type="submit" value="<?= l('Создать') ?>" name="create-category"/>
            <?php if ($ajax): ?>
                <button type="button" class="btn btn-default hide_typeahead_add_form"><?= l('Отмена') ?></button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if ($ajax && !$is_modal): ?>
        </div>
    <?php else: ?>
        </fieldset>
        </form>
    <?php endif; ?>
<?php else: ?>
    <p class="text-error"><?= l('У Вас нет прав для создания новой категории') ?></p>
<?php endif; ?>

