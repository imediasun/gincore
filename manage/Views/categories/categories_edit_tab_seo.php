<?php if ($this->all_configs['oRole']->hasPrivilege('edit-filters-categories')): ?>
    <form method="post">
        <input type="hidden" value="<?= $cat_id ?>" name="category_id"/>
        <div class="form-group">
            <label><?= l('Заголовок страницы') ?>: </label>
            <input class="form-control" data-symbol_counter="70" type="text" value="<?= $cur_category['page_title'] ?>" name="page_title"/>
        </div>
        <div class="form-group">
            <label><?= l('Описание страницы') ?>: </label>
            <input class="form-control seo_description" data-symbol_counter="150" type="text"
                   value="<?= $cur_category['page_description'] ?>" name="page_description"/>
        </div>
        <div class="form-group">
            <label><?= l('Ключевые слова') ?>: </label>
            <input class="form-control seo_keywords" data-symbol_counter="150" type="text"
                   value="<?= $cur_category['page_keywords'] ?>" name="page_keywords"/>
        </div>
        <div class="form-group">
            <label style="float: left; margin: 4px 10px 0 0"><?= l('Редактор') ?>:</label>
            <input type="checkbox" id="toggle_mce" <?= ((isset($_COOKIE['mce_on']) && $_COOKIE['mce_on']) ||
            !isset($_COOKIE['mce_on']) ? 'checked="checked"' : '') ?>>
            <textarea id="page_content" name="page_content" class="mcefull" rows="18" cols="80"
                      style="width:650px;height:320px;"><?= $cur_category['page_content'] ?> </textarea>
        </div>
        <div class="form-group">
            <input class="btn btn-primary" type="submit" value="<?= l('Сохранить') ?>" name="edit-seo"/>
        </div>
    </form>
<?php endif; ?>
