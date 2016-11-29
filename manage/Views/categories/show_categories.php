<div class="col-md-6">
    <input class="form-control" id="tree_search" type="text" name="tree_search"
           placeholder="<?= l('поиск по дереву') ?>">

    <div class="well four-column" id="search_results" style="display: none;">
        <ul></ul>
    </div>
</div>
<div class="col-md-6">
    <?php if ($this->all_configs['oRole']->hasPrivilege('create-filters-categories')): ?>
        <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create"
           class="btn btn-success pull-right">
            <?= l('Создать категорию') ?>
        </a>
    <?php endif; ?>
</div>


<div class="clearfix m-b-lg"></div>

<div class="col-md-6">

    <div class="hpanel">
        <div class="panel-heading hbuilt showhide cursor-pointer">
            <div class="panel-tools">
                <a class=""><i class="fa fa-chevron-up"></i></a>
            </div>
            <?= l('Дерево категорий') ?>
        </div>
        <div class="panel-body">
            <?php if (!empty($categories)): ?>

                <div id="categories-jstree" style="display: none;">
                    <?= build_array_tree($categories, array($cat_id), 4) ?>
                </div>

            <?php else: ?>
                <p class="text-error"><?= l('Не существует ниодной категории') ?></p>
            <?php endif; ?>
        </div>
    </div>

</div>
<div class="col-md-6" id="show-category">

</div>

<div class="clearfix"></div>

