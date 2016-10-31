<form method="post" class="<?= $isAjax ? '' : 'backgroud-white p-sm' ?>">
    <?php if ($isAjax): ?>
    <div class="emulate_form ajax_form" data-callback="select_typeahead_device" data-method="post"
         data-action="<?= $this->all_configs['prefix'] ?>products/ajax/?act=create_new"
         class="backgroud-white p-sm">
        <?php else: ?>
                <?php endif; ?>
                <fieldset>
                    <legend><?= l('Добавление нового товара/услуги') ?>:</legend>
                    <div class="row-fluid">
                        <div class="col-md-6 col-sm-8">
        
                            <?php if (is_array($errors) && array_key_exists('error', $errors)): ?>
                                <div class="alert alert-danger fade in">
                                    <button class="close" data-dismiss="alert" type="button">×</button>
                                    <?= $errors['error'] ?>
                                </div>
                            <?php endif; ?>
        
                            <?php if ($this->all_configs['configs']['group-goods']): ?>
                                <div class="control-group">
                                    <label class="control-label"><?= l('Группа размеров') ?>: </label>
                                    <div class="controls">
                                        <select name="size_group[]" id="goods_add_size_group">
                                            <option value="0"><?= l('Не выбран') ?></option>
                                            <?php if ($groups_size): ?>
                                                <?php foreach ($groups_size as $group): ?>
                                                    <option value="<?= $group['id'] ?>"><?= h($group['name']) ?></option>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endif; ?>
        
                            <div class="form-group"><label><?= l('Название') ?><b class="text-danger">*</b>: </label>
                                <input autocomplete="off" placeholder="<?= l('введите название') ?>"
                                       class="form-control global-typeahead" data-anyway="1" data-table="goods" name="title"
                                       value="<?= ((array_key_exists('post', $errors) && array_key_exists('title',
                                               $errors['post'])) ? h($errors['post']['title']) : '') ?>"/>
                            </div>
                            <input type="hidden" name="id" value=""/>
        
                            <?php if ($this->all_configs['oRole']->hasPrivilege('external-marketing')): ?>
                                <div class="form-group">
                                    <label class="control-label"><?= l('Розничная цена') ?> (<?= viewCurrency('shortName') ?>
                                        ): </label>
                                    <div class="controls">
                                        <input onkeydown="return isNumberKey(event)" placeholder="<?= l('введите цену') ?>"
                                               class="form-control" name="price"
                                               value="<?= ((array_key_exists('post', $errors) && array_key_exists('price',
                                                       $errors['post'])) ? h($errors['post']['price']) : '') ?>"/>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label"><?= l('Оптовая цена') ?> (<?= viewCurrency('shortName') ?>
                                        ): </label>
                                    <div class="controls">
                                        <input onkeydown="return isNumberKey(event)" placeholder="<?= l('введите цену') ?>"
                                               class="form-control" name="price_wholesale"
                                               value="<?= ((array_key_exists('post',
                                                       $errors) && array_key_exists('price_wholesale',
                                                       $errors['post'])) ? h($errors['post']['price_wholesale']) : '') ?>"/>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="form-group">
                                <label class="control-label"><?= l('Артикул') ?>: </label>
                                <div class="controls">
                                    <input placeholder="<?= l('Артикул') ?>"
                                           class="form-control" name="vendor_code"
                                           value="<?= ((array_key_exists('post', $errors) && array_key_exists('vendor_code',
                                                   $errors['post'])) ? h($errors['post']['vendor_code']) : '') ?>"/>
                                </div>
                            </div>
        
                            <div class="form-group">
                                <div class="checkbox">
                                    <label class="">
                                        <input name="avail" checked="checked" type="checkbox">
                                        <?= l('Активность') ?>
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?= l('Категории') ?>: </label>
                                <div class="controls">
                                    <select class="multiselect input-small form-control" multiple="multiple"
                                            name="categories[]">
                                        <?= build_array_tree($categories, isset($_GET['cat_id']) ? $_GET['cat_id'] : ''); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label"><?= l('manager') ?>: </label>
                                <div class="controls">
                                    <select class="multiselect input-small form-control"
                                            multiple="multiple" <?= $this->all_configs['configs']['manage-product-managers'] == true ? 'multiple="multiple"' : ''; ?>
                                            name="users[]">
                                        <?php if ($managers && count($managers) > 0): ?>
                                            <?php $m = array_key_exists('manager',
                                                $this->all_configs['settings']) ? $this->all_configs['settings']['manager'] : $_SESSION['id']; ?>
        
                                            <?php foreach ($managers as $manager): ?>
                                                <option
                                                    value="<?= $manager['id'] ?>" <?= $manager['id'] == $m ? ' selected ' : ''; ?> ><?= $manager['login'] ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="checkbox">
                                    <label>
                                        <input name="type"<?= ($service ? ' checked' : '') ?> value="1"
                                               type="checkbox"><?= l('Услуга') ?>
                                    </label>
                                </div>
                            </div>
                            <input class="btn btn-primary" type="submit" value="<?= l('Добавить') ?>" name="create-product">
                            <?php if ($isAjax): ?>
                                <button type="button"
                                        class="btn btn-default hide_typeahead_add_form"><?= l('Отмена') ?></button>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6 col-sm-4">
                            <div class="col-md-offset-3 col-md-6 center">

                                <h1><i class="fa fa-warning font-32"></i></h1>
                                <p>
                                    <?= l('Здесь Вы можете добавить товарную номенклатуру (название для товара).') ?>
                                    <?= l('Добавление товарных остатков (приходование товара на склад) производится через раздел Склады-Заказы.') ?>
                                </p>
                            </div>
                        </div>
                    </div>
            </fieldset>
    <?php if ($isAjax): ?>
        </div>
    <?php else: ?>
<?php endif; ?>
</form>
<script>
    jQuery(document).ready(function () {
        $('.multiselect').multiselect(multiselect_options);
    });
</script>