<?php if ($this->all_configs['oRole']->hasPrivilege('show-categories-filters')): ?>

    <form method="post" enctype="multipart/form-data">
        <fieldset>
            <legend>
                <?php if (!empty($cur_category['thumbs'])): ?>
                    <img src="<?= $this->all_configs['siteprefix'] . $this->cat_img . $cur_category['thumbs'] ?>"/>
                <?php endif; ?>
                <?= l('Редактирование категории') ?> ID: <?= $cur_category['id'] ?>. <?= $cur_category['title'] ?>
            </legend>
            <?php if (isset($_GET['error']) && $_GET['error'] == 'url'): ?>
                <p class="text-error"><?= l('Категория с таким названием уже существует') ?></p>
            <?php endif; ?>
            <div class="form-group"><label><?= l('Название') ?>:</label>
                <input class="form-control" name="title" value="<?= $cur_category['title'] ?>"/>
            </div>
            <input type="hidden" class="span5" name="id" value="<?= $cur_category['id'] ?>"/>

            <div class="form-group">
                <div class="checkbox">
                    <label>
                        <input name="avail" <?= ($cur_category['avail'] == 1) ? 'checked' : '' ?> type="checkbox">
                        <?= l('Активность') ?>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="control-label"><?= l('Родитель') ?>:</label>
                <div class="controls">
                    <?= typeahead($this->all_configs['db'], 'categories', false, $cur_category['parent_id'], 2,
                        'input-large') ?>
                </div>
                <div class="form-group">
                    <label><?= l('Описание') ?>: </label>
                    <div class="controls">
                        <textarea name="content" class="form-control"
                                  rows="3"><?= htmlspecialchars($cur_category['content']) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label><?= l('Приоритет') ?>: </label>
                        <input class="form-control" type="text" value="<?= $cur_category['prio'] ?>" name="prio"/>
                    </div>
                    <div class="form-group">
                        <label><?= l('Важная информация') ?>: </label>
                        <textarea name="information" class="form-control"
                                  rows="3"><?= $cur_category['information'] ?></textarea>
                    </div>
                    <div class="form-group">
                        <label><?= l('Рейтинг') ?>: </label>
                        <input class="form-control" type=text" onkeydown="return isNumberKey(event, this)"
                               placeholder="<?= l('рейтинг') ?>" value="<?= $cur_category['rating'] ?>" name="rating"/>
                    </div>
                    <div class="form-group">
                        <label><?= l('Количество голосов') ?>: </label>
                        <input class="form-control" onkeydown="return isNumberKey(event)" type=text"
                               placeholder="<?= l('голоса') ?>" value="<?= $cur_category['votes'] ?>" name="votes"/>
                    </div>

                    <?php if ($this->all_configs['oRole']->hasPrivilege('edit-filters-categories')): ?>
                        <div class="form-group">
                            <div class="controls">
                                <input class="btn btn-primary " type="submit" value="<?= l('Сохранить') ?>"
                                       name="edit-category"/>
                                <?php if ($cur_category['deleted']): ?>
                                    <input class="btn btn-primary " type="submit"
                                           value="<?= l('Восстановить из корзины') ?>"
                                           name="recovery-category"/>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <script>$(":input:not(:disabled)").prop("disabled", true)</script>
                    <?php endif; ?>
        </fieldset>
    </form>
<?php else: ?>
    <p class="text-error"><?= l('У Вас нет прав для просмотра категорий') ?></p>
<?php endif; ?>