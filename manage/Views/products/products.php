<div class="tabbable">
    <div class="clearfix nav-tabs">
        <ul class="nav nav-tabs pull-left" style="border-bottom:0">
            <li class="active"><a data-toggle="tab" href="#goods"><?= l('Товары') ?></a></li>
            <?php if ($this->all_configs['configs']['no-warranties'] == false): ?>
                <li><a data-toggle="tab" href="#settings"><?= l('Настройки') ?></a></li>
            <?php endif; ?>
            <?php if ($this->all_configs['oRole']->hasPrivilege('export-goods')): ?>
                <li><a data-toggle="tab" href="#exports"><?= l('Экспорт') ?></a></li>
                <li><a data-toggle="tab" href="#imports"><?= l('Импорт') ?></a></li>
            <?php endif; ?>
        </ul>
    </div>
    <div class="tab-content">
        <div id="goods" class="tab-pane active">
            <div class="row-fluid" style="margin-bottom: 20px">
                <?= $filters ?>
            </div>
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
                <?= $this->renderFile('products/list/as_table', array(
                    'goods' => $goods,
                    'quick_edit_title' => $quick_edit_title,
                    'isEditable' => $isEditable,
                    'columns' => $columns,
                    'item_in_cart' => $item_in_cart
                )) ?>
                <?= page_block($count_page, $count_goods, '', null,
                    $this->renderFile('products/_delete_all_button')); ?>

                <div class="pull-right" style="margin-right: 20px">
                    <?= $this->renderFile('products/print_buttons', array()) ?>
                </div>

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
            <div id="imports" class="tab-pane">
                <?= $product_imports_form; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
