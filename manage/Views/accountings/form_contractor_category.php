<form method='POST' class='form_contractor_category form-horizontal'>
    <input type='hidden' name='transaction_type' value='<?= $type ?>'/>

    <?php if ($contractor_category): ?>
        <div class="form-group">
            <label>ID: <?= $contractor_category['id'] ?></label>
        </div>
        <input type='hidden' name='contractor_category-edit' value='1'/>
        <input type='hidden' name='contractor_category-id' value='<?= $contractor_category['id'] ?>'/>

    <?php else: ?>
        <input type='hidden' name='contractor_category-add' value='1'/>
    <?php endif; ?>

    <div class='form-group'>
        <label><?= l('Статья') ?>: </label>
        <input class='form-control' placeholder='<?= l(' введите название статьи') ?>' name='title'
               value='<?= $contractor_category ? htmlspecialchars($contractor_category['name']) : '' ?>'/>
    </div>
    <div class='form-group'>
        <label><?= l('Родительская статья') ?>: </label>

        <?php if ($contractor_category): ?>
            <select class='multiselect' name='parent_id'>
                <option value=''><?= l('Высшая') ?></option>
                <?= build_array_tree($categories, $contractor_category['parent_id']) ?>
            </select>
        <?php else: ?>
            <select class='multiselect' name='parent_id'>
                <option value=''><?= l('Высшая') ?></option>
                <?= build_array_tree($categories) ?>
            </select>
        <?php endif; ?>
    </div>
    <div class='form-group'>
        <label><?= l('Комментарий') ?>: </label>
        <div class='controls'>
            <textarea class='form-control' name='comment'
                      placeholder='<?= l(' введите комментарий к статье') ?>'><?= $contractor_category ? htmlspecialchars($contractor_category['comment']) : '' ?></textarea>
        </div>
    </div>
    <div class='form-group'>
        <div class='checkbox'>
            <label>
                <input
                    type='checkbox' <?= $contractor_category ? ($contractor_category['avail'] == 1 ? 'checked' : '') : 'checked' ?>
                    class='btn' name='avail' value='1'/>
                <?= l('Отображать') ?>
            </label>
        </div>
    </div>
</form>

