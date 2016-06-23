<form method="post" style="max-width: 400px">
    <table class="table borderless">
        <tr>
            <td style="padding: 0 5px 0 0">
                <input type="text" name="date" value="<?= $date ?>"
                       class="btn btn-info daterangepicker"/><?php //=  InfoPopover::getInstance()->createQuestion('l_accountings_report_period_info')?>
            </td>
            <td style="padding: 0 5px 0 0 ">
                <div class="btn-group">
                    <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="#">
                        <span style="margin-right:10px"><?= l('Тип заказа') ?></span><i class="fa fa-filter"></i> <i
                            class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu pull-down">
                        <li>

                            <div class="col-sm-12 form-group">
                                <div class="checkbox"><label>
                                        <input type="checkbox" value="1"
                                               name="repair" <?= (isset($_GET['repair']) && $_GET['repair'] == 1) ? 'checked' : ''; ?> >
                                        <?= l('Ремонты') ?>
                                    </label>
                                </div>
                                <div class="checkbox"><label>
                                        <input type="checkbox" value="1"
                                               name="sale" <?= (isset($_GET['sale']) && $_GET['sale'] == 1) ? 'checked' : ''; ?> >
                                        <?= l('Продажи') ?>
                                    </label>
                                </div>
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
                        </li>
                    </ul>
                </div>
            </td>
            <?php if ($isAdmin): ?>
                <td style="padding: 0 5px 0 0 ">
                    <select class="multiselect  form-control report-filter" name="managers[]" multiple="multiple"
                            data-placeholder="<?= l('manager') ?>">
                        <?= build_array_tree($managers,
                            ((isset($_GET['mg'])) ? explode(',', $_GET['mg']) : array())); ?>
                    </select>
                </td>
            <?php endif; ?>
            <td style="padding: 0 5px 0 0 ">
                <select <?= $isAdmin ? '' : 'disabled'; ?> class="multiselect form-control report-filter"
                                                           name="accepters[]"
                                                           multiple="multiple" data-placeholder="<?= l('Приемщик') ?>">
                    <?php $selected = !$isAdmin ? $userId : ((isset($_GET['acp'])) ? explode(',',
                        $_GET['acp']) : array()); ?>
                    <?= build_array_tree($accepters, $selected); ?>
                </select>
            </td>
            <?php if ($isAdmin): ?>
                <td style="padding: 0 5px 0 0 ">
                    <select class="multiselect form-control report-filter" name="engineers[]"
                            multiple="multiple" data-placeholder="<?= l('Инженер') ?>">
                        <?= build_array_tree($engineers,
                            ((isset($_GET['eng'])) ? explode(',', $_GET['eng']) : array())); ?>
                    </select>
                </td>
            <?php endif; ?>
            <td style="padding: 0 5px 0 0 ">
                <select <?= $isAdmin ? '' : 'disabled'; ?> class="multiselect form-control report-filter"
                                                           name="states[]"
                                                           multiple="multiple" data-placeholder="<?= l('Статусы') ?>">
                    <?= build_array_tree($states,
                        ((isset($_GET['sts'])) ? explode(',', $_GET['sts']) : array())); ?>
                </select>
            </td>
            <?php if ($isAdmin): ?>
                <td style="padding: 0 5px 0 0 ">
                    <?= typeahead($this->all_configs['db'], 'goods', false,
                        isset($_GET['by_gid']) && $_GET['by_gid'] ? $_GET['by_gid'] : 0, 4, 'input-100px',
                        'input-100px', '', false, false, '', false, l('Товар')); ?>
                    <?php //=  InfoPopover::getInstance()->createQuestion('l_accountings_report_product_info')?>
                </td>

                <td style="padding: 0 5px 0 0 ">
                    <?= typeahead($this->all_configs['db'], 'categories-last', false,
                        isset($_GET['dev']) && $_GET['dev'] ? $_GET['dev'] : '', 5, 'input-100px', 'input-100px', '',
                        false, false, '', false, l('Категория')); ?>
                    <?php //=  InfoPopover::getInstance()->createQuestion('l_accountings_report_category_info')?>
                </td>
            <?php endif; ?>
            <td style="padding: 0 5px 0 0 ">
                <input class="btn btn-primary" type="submit" name="filters" value="<?= l('Применить') ?>"/>
            </td>
        </tr>
    </table>
</form>
<style>
    .multiselect-btn-group {
        width: 105px !important;
    }

    button.multiselect {
        width: 105px !important;
    }
</style>
