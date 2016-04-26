<form method="post" style="max-width: 400px">
    <div class="form-group">
        <label class=""><?= l('Период') ?>:</label>
        <input type="text" name="date" value="<?= $date ?>" class="form-control daterangepicker"/>
    </div>
    <table class="table borderless">
        <?php if ($isAdmin): ?>
            <tr>
                <td>
                    <label><?= l('manager') ?>:</label>
                </td>
                <td>
                    <select class="multiselect form-control report-filter" name="managers[]" multiple="multiple">
                        <?= build_array_tree($managers,
                            ((isset($_GET['mg'])) ? explode(',', $_GET['mg']) : array())); ?>
                    </select>
                </td>
            </tr>
        <?php endif; ?>
        <tr>
            <td><label><?= l('Приемщик') ?>:</label></td>
            <td>
                <select <?= $isAdmin ? '' : 'disabled'; ?> class="multiselect form-control report-filter"
                                                           name="accepters[]"
                                                           multiple="multiple">
                    <?php $selected = !$isAdmin ? $userId : ((isset($_GET['acp'])) ? explode(',',
                        $_GET['acp']) : array()); ?>
                    <?= build_array_tree($accepters, $selected); ?>
                </select>
            </td>
        </tr>
        <tr>
            <td><label><?= l('Статусы') ?>:</label></td>
            <td>
                <select <?= $isAdmin ? '' : 'disabled'; ?> class="multiselect form-control report-filter"
                                                           name="states[]"
                                                           multiple="multiple">
                    <?= build_array_tree($states,
                        ((isset($_GET['sts'])) ? explode(',', $_GET['sts']) : array())); ?>
                </select>
            </td>
        </tr>
        <?php if ($isAdmin): ?>
            <tr>
                <td><label> <?= l('Инженер') ?>:</label></td>
                <td>
                    <select class="multiselect form-control report-filter" name="engineers[]" multiple="multiple">
                        <?= build_array_tree($engineers,
                            ((isset($_GET['eng'])) ? explode(',', $_GET['eng']) : array())); ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td><label><?= l('Товар') ?>:</label></td>
                <td>
                    <?= typeahead($this->all_configs['db'], 'goods', true,
                        isset($_GET['by_gid']) && $_GET['by_gid'] ? $_GET['by_gid'] : 0, 4); ?>
                </td>

            </tr>
            <tr>
                <td><label><?= l('Категория') ?>:</label></td>
                <td>
                    <?= typeahead($this->all_configs['db'], 'categories-last', true,
                        isset($_GET['dev']) && $_GET['dev'] ? $_GET['dev'] : '', 5); ?>
                </td>
            </tr>
        <?php endif; ?>
    </table>
    <div class="form-group">
        <div class="checkbox"><label>
                <input type="checkbox" value="1"
                       name="novaposhta" <?= (isset($_GET['np']) && $_GET['np'] == 1) ? 'checked' : ''; ?> >
                <?= l('принято через почту') ?>
            </label>
        </div>
        <div class="checkbox"><label>
                <input type="checkbox" value="1"
                       name="warranties" <?= (isset($_GET['wrn']) && $_GET['wrn'] == 1) ? 'checked' : ''; ?> >
                <?= l('гарантийные') ?>
            </label>
        </div>
        <div class="checkbox">
            <label>
                <input type="checkbox" value="1"
                       name="nowarranties" <?= (isset($_GET['nowrn']) && $_GET['nowrn'] == 1) ? 'checked' : ''; ?> >
                <?= l('не гарантийные') ?>
            </label>
        </div>
        <input type="hidden" value="1" name="return">
    </div>
    <div class="form-group"><input class="btn btn-primary" type="submit" name="filters" value="<?= l('Применить') ?>"/>
    </div>
</form>
