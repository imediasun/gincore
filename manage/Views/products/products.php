<div class="tabbable">
    <div class="clearfix nav-tabs">
        <ul class="nav nav-tabs pull-left" style="border-bottom:0">
            <li class="active"><a data-toggle="tab" href="#goods"><?= l('Товары') ?></a></li>
            <?php if ($this->all_configs['configs']['no-warranties'] == false): ?>
                <li><a data-toggle="tab" href="#settings"><?= l('Настройки') ?></a></li>
            <?php endif; ?>
            <?php if ($this->all_configs['oRole']->hasPrivilege('export-goods')): ?>
                <li><a data-toggle="tab" href="#exports"><?= l('Экспорт') ?></a></li>
            <?php endif; ?>
        </ul>
        <div class="pull-right">
            <form class="pull-left m-r-xs" method="post">
                <div class="input-group" style="width:250px">
                    <input class="form-control" name="text" type="text"
                           value="<?= (isset($_GET['s']) ? htmlspecialchars($_GET['s']) : '') ?>"/>
                            <span class="input-group-btn">
                                <input type="submit" name="search" value="<?= l('Поиск') ?>" class="btn"/>
                            </span>
                </div>
            </form>
            <?php if ($this->all_configs['oRole']->hasPrivilege('create-goods')): ?>
                <a href="<?= $this->all_configs['prefix'] . $this->all_configs['arrequest'][0] ?>/create"
                   class="btn btn-success pull-right">
                    <?= l('Добавить товар') ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="tab-content">
        <div id="goods" class="tab-pane active">
            <?php if (empty($goods)): ?>
                <p class="text-error"><?= l('Нет ни одного продутка') ?></p>
            <?php else: ?>
            <?php $count_page = ceil($count_goods / $count_on_page); ?>
            <?php if (isset($_GET['edit']) && !empty($_GET['edit']) && $this->all_configs['oRole']->hasPrivilege('edit-goods')): ?>
            <?php if (isset($_GET['edit']) && !empty($_GET['edit'])): ?>
            <form method="POST">
                <?php endif; ?>
                <?php if ($_GET['edit'] == 'ym_id'): ?>
                    <?php $quick_edit_title = 'yandex market ID'; ?>
                <?php endif; ?>
                <?php if (($_GET['edit'] == 'price' || $_GET['edit'] == 'active_price') && $this->all_configs['oRole']->hasPrivilege('external-marketing')): ?>
                    <?php $quick_edit_title = l('Цена'); ?>
                <?php endif; ?>
                <?php endif; ?>
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <td>
                            <?php switch ($_GET['sort']): ?>
<?php case 'id': ?>
                                    <a href="?sort=rid">
                                        ID<i class="glyphicon glyphicon-chevron-down"></i>
                                    </a>
                                    <?php break; ?>
                                <?php case 'rid': ?>
                                    <a href="?sort=id">
                                        ID<i class="glyphicon glyphicon-chevron-up"></i>
                                    </a>
                                    <?php break; ?>
                                <?php default: ?>
                                    <a href="?sort=rid"> ID
                                        <?php if (!isset($_GET['sort'])): ?>
                                            <i class="glyphicon glyphicon-chevron-down"></i>
                                        <?php endif; ?>
                                    </a>
                                <?php endswitch; ?>
                        </td>
                        <td>
                            <?php switch ($_GET['sort']): ?>
<?php case 'title': ?>
                                    <a href="?sort=rtitle">
                                        <?= l('Название продукта') ?><i class="glyphicon glyphicon-chevron-down"></i>
                                    </a>
                                    <?php break; ?>
                                <?php case 'rtitle': ?>
                                    <a href="?sort=title">
                                        <?= l('Название продукта') ?><i class="glyphicon glyphicon-chevron-up"></i>
                                    </a>
                                    <?php break; ?>
                                <?php default: ?>
                                    <a href="?sort=title"><?= l('Название продукта') ?> </a>
                                <?php endswitch; ?>
                        </td>
                        <td colspan="2">
                            <?php if ($this->all_configs['oRole']->hasPrivilege('edit-goods')): ?>
                                <div class="btn-group">
                                    <a class="btn dropdown-toggle" data-toggle="dropdown" href="#"><i
                                            class="glyphicon glyphicon-wrench"></i></a>
                                    <ul class="dropdown-menu" role="menu" aria-labelledby="dropdownMenu">
                                        <?php if ($this->all_configs['oRole']->hasPrivilege('external-marketing')): ?>
                                            <li <?= (isset($_GET['edit']) && $_GET['edit'] == 'active_price' ? 'class="active"' : '') ?>>
                                                <a tabindex="-1" href="?edit=active_price&<?= get_to_string('edit') ?>">
                                                    <?= l('Редактирование цены и активности') ?>
                                                </a>
                                            </li>
                                        <?php endif; ?>
                                        <li class="divider"></li>
                                        <li <?= (!isset($_GET['edit']) ? 'class="active"' : '') ?>>
                                            <a tabindex="-1" href="<?= $this->all_configs['prefix'] ?>products">
                                                <?= l('Стандартный вид') ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td><?= $quick_edit_title ?></td>
                        <td>
                            <?php switch ($_GET['sort']): ?>
<?php case 'avail': ?>
                                    <a href="?sort=ravail">
                                        <?= l('Вкл.') ?><i class="glyphicon glyphicon-chevron-down"></i>
                                    </a>
                                    <?php break; ?>
                                <?php case 'ravail': ?>
                                    <a href="?sort=avail">
                                        <?= l('Вкл.') ?><i class="glyphicon glyphicon-chevron-up"></i>
                                    </a>
                                    <?php break; ?>
                                <?php default: ?>
                                    <a href="?sort=avail"><?= l('Вкл.') ?> </a>
                                <?php endswitch; ?>
                        </td>
                        <td>
                            <?php switch ($_GET['sort']): ?>
<?php case 'price': ?>
                                    <a href="?sort=rprice">
                                        <?= l('Цена') ?><i class="glyphicon glyphicon-chevron-down"></i>
                                    </a>
                                    <?php break; ?>
                                <?php case 'rprice': ?>
                                    <a href="?sort=price">
                                        <?= l('Цена') ?><i class="glyphicon glyphicon-chevron-up"></i>
                                    </a>
                                    <?php break; ?>
                                <?php default: ?>
                                    <a href="?sort=price"><?= l('Цена') ?></a>
                                <?php endswitch; ?>
                        </td>
                        <td>
                            <?php switch ($_GET['sort']): ?>
<?php case 'date': ?>
                                    <a href="?sort=rdate">
                                        <?= l('Дата') ?><i class="glyphicon glyphicon-chevron-down"></i>
                                    </a>
                                    <?php break; ?>
                                <?php case 'rdate': ?>
                                    <a href="?sort=date"><?= l('Дата') ?>
                                        <i class="glyphicon glyphicon-chevron-up"></i>
                                    </a>
                                    <?php break; ?>
                                <?php default: ?>
                                    <a href="?sort=date"><?= l('Дата') ?> </a>
                                <?php endswitch; ?>
                        </td>
                        <td title="<?= l('Общий остаток') ?>"><?= l('Общ') ?></td>
                        <td title="<?= l('Свободный остаток') ?>"><?= l('Своб') ?></td>
                    </tr>
                    </thead>
                    <tbody>
                    P
                    </tbody>
                </table>
                <?= page_block($count_page, $count_goods); ?>

                <?php if ($this->all_configs['configs']['no-warranties'] == false): ?>
        </div>
        <div id="settings" class="tab-pane">
            <?php if ($this->all_configs['oRole']->hasPrivilege('create-goods')): ?>
                <form method="post">
                    <h4><?= l('При добавлении нового товара будут автоматически добавленны такие настройки') ?>:</h4>

                    <?php $is_warranty = (array_key_exists('warranty',
                            $this->all_configs['settings']) && $this->all_configs['settings']['warranty'] > 0); ?>
                    <div class="control-group"><label class="control-label"><?= l('Гарантии') ?>: </label>
                        <div class="controls">
                            <label class="radio"><input
                                    onclick="$('.default-warranty').prop('disabled', true);" <?= ($is_warranty ? '' : 'checked') ?>
                                    type="radio" name="warranty" value="0"><?= l('Без гарантий') ?></label>
                            <label class="radio"><input
                                    onclick="$('.default-warranty').prop('disabled', false);" <?= ($is_warranty ? 'checked' : '') ?>
                                    type="radio" name="warranty" value="1"><?= l('С гарантиями') ?></label>
                            <div class="well">
                                <?php $config_warranties = array_key_exists('warranties',
                                    $this->all_configs['settings']) ?
                                    (array)unserialize($this->all_configs['settings']['warranties']) : array(); ?>

                                <?php foreach ($warranties as $m => $warranty): ?>
                                    <label class="checkbox"><?= $m ?> <?= l('мес') ?>
                                        <input class="default-warranty" type="checkbox" value="<?= $m ?>"
                                            <?= (array_key_exists($m, $config_warranties) ? ' checked ' : '') ?>
                                            <?= ($is_warranty ? '' : ' disabled ') ?> name="warranties[]"></label>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="control-group"><label class="control-label"><?= l('manager') ?>: </label>
                        <div class="controls"><select class="multiselect input-small" name="users">

                                <?php if ($managers && count($managers) > 0): ?>
                                    <?php $m = array_key_exists('manager', $this->all_configs['settings'])
                                        ? $this->all_configs['settings']['manager'] : $_SESSION['id']; ?>

                                    <?php foreach ($managers as $manager): ?>
                                        <option
                                            value="<?= $manager['id'] ?>" <?= $manager['id'] == $m ? ' selected ' : ''; ?> >
                                            <?= $manager['login'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select></div>
                    </div>

                    <div class="control-group">
                        <div class="controls">
                            <input type="submit" value="<?= l('Сохранить') ?>" name="default-add-product"
                                   class="btn btn-primary"/>
                        </div>
                    </div>
                </form>
            <?php else: ?>
                <p class="text-error"><?= l('У Вас нет прав для добавления новых товаров') ?></p>
            <?php endif; ?>
            <?php endif; ?>

            <?php if (isset($_GET['edit']) && !empty($_GET['edit'])): ?>
                <input type="submit" name="quick-edit" value="<?= l('Сохранить') ?>"
                       class="btn quick-edit-save_btn"/>
                </form>
            <?php endif; ?>

            <?php endif; ?>
        </div>
        <?php if ($this->all_configs['oRole']->hasPrivilege('export-goods')): ?>
            <div id="exports" class="tab-pane">
                <?= $product_exports_form; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
