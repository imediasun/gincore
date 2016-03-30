<div id="edit_tab_roles" class="tab-pane">
    <form class="form-horizontal" method="post">
        <div style="display: inline-block; width: 100%;">
            <?php foreach ($aRoles as $rid => $v): ?>
                <ul class="nav nav-list pull-left" style="width:33%;padding:0 10px">
                    <li class="nav-header">
                        <br><h4 class="text-info"><?= htmlspecialchars($v['name']) ?></h4>
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" <?= $v['avail'] ? 'checked' : '' ?> name="active[<?= $rid ?>]"/>
                                <?= l('активность') ?>
                            </label>
                        </div>
                    </li>
                    <li>
                        <?= l('Дата конца активности группы') ?>
                    </li>
                    <li>
                        <input class="form-control input-sm datepicker" name="date_end[<?= $rid ?>]" type="text"
                               value="<?= $v['date_end'] ?>">
                    </li>
                    <?php foreach ($groups as $group_id => $name): ?>
                        <?php if (!empty($v['children'])): ?>
                            <li>
                                <label class="m-t-sm"><?= $name ?></label>
                            </li>
                            <?php foreach ($v['children'] as $pid => $sv): ?>
                                <li>
                                    <div class="checkbox">
                                        <label>
                                            <input id="per_id_<?= $rid ?>_<?= $pid ?>"
                                                   class="del-<?= $rid ?>-<?= $sv['child'] ?>"
                                                   onchange="per_change(this, '<?= $rid ?>-<?= $sv['child'] ?>', '<?= $rid ?>-<?= $pid ?>')"
                                                   name="permissions[<?= $rid ?>-<?= $pid ?>]" <?= $sv['checked'] ? 'checked' : '' ?>
                                                   type="checkbox"/>
                                            <?= $sv['name'] ?>
                                        </label>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </ul>
                <input type="hidden" name="exist-box[<?= $rid ?>]" value="<?= implode(" ,", $v['all']) ?>"/>
            <?php endforeach;; ?>
        </div>
        <input type="submit" name="create-roles" value="<?= l('Сохранить') ?>"
               class="btn btn-primary"/>
    </form>
</div>
