<?php if ($this->all_configs['oRole']->hasPrivilege('create-filters-categories')): ?>
    <p>
        <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create"
           class="btn btn-success">
            <?= l('Создать категорию') ?>
        </a>
    </p>
<?php endif; ?>
<?php if (!empty($categories)): ?>
    <p>
        <input class="form-control" id="tree_search" type="text" name="tree_search"
               placeholder="<?= l('поиск по дереву') ?>">
    </p>
    <div class="well four-column" id="search_results" style="display: none;">
        <ul></ul>
    </div>
    <div class="four-column dd backgroud-white" id="categories-tree">
        <?= build_array_tree($categories, array(), 2) ?>
    </div>
<?php else: ?>
    <p class="text-error"><?= l('Не существует ниодной категории') ?></p>
<?php endif; ?>
