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

    <table class="table table-borderless">
        <tr>
            <td width="40%">
                <label><?= l('Статья') ?>: </label>
            </td>
            <td>
                <input class='form-control' placeholder='<?= l(' введите название статьи') ?>' name='title'
                       value='<?= $contractor_category ? htmlspecialchars($contractor_category['name']) : '' ?>'/>

            </td>
        </tr>
        <tr>
            <td width="40%">
                <label><?= l('Родительская статья') ?>: </label>
            </td>
            <td>
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
            </td>
        </tr>
        <tr>
            <td width="40%">
                <label><?= l('Комментарий') ?>: </label>
            </td>
            <td>
            <textarea class='form-control' name='comment'
                      placeholder='<?= l(' введите комментарий к статье') ?>'><?= $contractor_category ? htmlspecialchars($contractor_category['comment']) : '' ?></textarea>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <div class='checkbox'>
                    <label>
                        <input
                            type='checkbox' <?= $contractor_category ? ($contractor_category['avail'] == 1 ? 'checked' : '') : 'checked' ?>
                            class='btn' name='avail' value='1'/>
                        <?= l('Отображать') ?>
                    </label>
                </div>
            </td>
        </tr>
        <?php if (!empty($contractors)): ?>
            <tr>
                <td width="40%">
                    <label>
                        <?= l('Статья доступна следующим контрагентам') ?>:
                    </label>
                </td>
                <td>
                    <select class='multiselect' name='contractors[]' multiple="multiple">
                        <?php foreach ($contractors as $contractor): ?>
                            <option value="<?= $contractor['id'] ?>" <?= in_array($contractor['id'], $contractors_category_links)? 'selected': '' ?>><?= $contractor['title'] ?> </option>
                        <?php endforeach; ?>
                    </select>

                </td>
            </tr>
        <?php endif; ?>
    </table>

</form>

