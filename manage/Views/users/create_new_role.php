<div id="edit_tab_create" class="tab-pane">
    <form method="post">
        <fieldset>
            <legend><?= l('Добавление новой роли') ?></legend>
            <div class="form-group">
                <label><?= l('Название') ?>:</label>
                <input class="form-control" value="" name="name" placeholder="<?= l('введите название') ?>">
            </div>
            <div class="form-group">
                <label><?= l('Права доступа') ?>:</label>
                <?= l('отметьте нужные') ?>
            </div>
            <?php $showedPermission = array(); ?>
            <?php foreach ($groups as $group_id => $name): ?>
                <div class="form-group">
                    <?php $i = 0; ?>
                    <?php foreach ($permissions as $permission): ?>
                        <?php if ($permission['group_id'] == $group_id && !in_array($permission['per_id'],
                                $showedPermission)
                        ): ?>
                            <?php // выводим только заголовок не пустой группы ?>
                            <?php if ($i == 0): ?>
                                <label><?= $name ?></label>
                            <?php endif; ?>
                            <div class="checkbox">
                                <label>
                                    <input id="per_id_a_<?= $permission['per_id'] ?>"
                                           class="del-a-<?= $permission['child'] ?>"
                                           onchange="per_change(this, 'a-<?= $permission['child'] ?>', 'a-<?= $permission['per_id'] ?>')"
                                           type="checkbox" name="permissions[a-<?= $permission['per_id'] ?>]">
                                    <?= htmlspecialchars($permission['per_name']) ?>
                                </label>
                            </div>
                            <?php $showedPermission[] = $permission['per_id']; ?>
                            <?php $i++ ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            <div class="control-group">
                <div class="controls">
                    <input class="btn btn-primary" type="submit" name="add-role" value="<?= l('Создать') ?>">
                </div>
            </div>
        </fieldset>
    </form>
</div>
