<?php if ($this->all_configs['oRole']->hasPrivilege('create-filters-categories')): ?>
    <p>
        <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create"
           class="btn btn-success">
            <?= l('Создать категорию') ?>
        </a>
    </p>
<?php endif; ?>
<div class="dd" id="categories-tree">
    <?= build_array_tree($categories, array($cat_id), 2) ?>
</div>
