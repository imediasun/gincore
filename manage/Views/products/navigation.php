<ul class="list-unstyled inline clearfix m-b-md">
    <li class="">
        <a class="btn btn-default" href="#" title=""
           onclick="return show_action_form(this, 'action-form', '<?= json_encode($_GET) ?>')">
            <?= l('Действия') ?> <i class="fa fa-caret-down" aria-hidden="true"></i>
        </a>
    </li>
    <li class="">
        <button data-toggle=".js-filters" type="button" class="toggle-hidden btn btn-default">
            <i class="fa fa-filter"></i> <?= l('Фильтровать') ?>
            <i class="fa fa-caret-down"></i>
        </button>
    </li>
    <li style="max-width:280px">
        <form class="form-inline" method="post">
            <div class="input-group" style="width:250px">
                <input class="form-control" name="text" type="text"
                       value="<?= (isset($_GET['s']) ? h($_GET['s']) : '') ?>"/>
                <span class="input-group-btn">
                                <input type="submit" name="search" value="<?= l('Поиск') ?>" class="btn"/>
                            </span>
            </div>
        </form>
    </li>
    <?php if ($this->all_configs['oRole']->hasPrivilege('create-goods')): ?>
        <li class="pull-right">
            <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create"
               class="btn btn-success pull-right">
                <?= l('Добавить товар') ?>
            </a>
        </li>
    <?php endif; ?>
</ul>
<div class="hidden js-filters"><?= $filters ?></div>
