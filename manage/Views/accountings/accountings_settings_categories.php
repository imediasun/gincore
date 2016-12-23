<?php if ($this->all_configs['oRole']->hasPrivilege('accounting')): ?>
    <button class="btn btn-primary" onclick="alert_box(this, false, 'create-cat-<?= $categories_type ?>')" type="button"><?= $title ?></button>
    <br /><br />
    <div class="three-column" id="create-cat-<?= $categories_type ?>"><?= build_array_tree($categories, array(), 3) ?></div>
<?php endif; ?>
