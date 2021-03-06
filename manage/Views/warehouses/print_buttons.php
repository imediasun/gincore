<div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle dropdown-btn<?= $prefix ?>" data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false">
        <i class="fa fa-print"></i> <span class="caret"></span>
    </button>
    <ul class="keep-open dropdown-menu print_menu">
        <?php if ($prefix == '_location'): ?>
            <li>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="print[]"
                               value="<?= print_link($objectId, 'location', '', true,
                                   !empty($addition) ? $addition : '') ?>">
                        <?= l('Локация') ?>
                    </label>
                </div>
            </li>
        <?php else: ?>
            <li>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="print[]"
                               value="<?= print_link(isset($whItemId) ? $whItemId : $objectId, 'label' . $prefix, '', true,
                                   !empty($addition) ? $addition : '') ?>">
                        <?= l('Этикетка (штрих-код)') ?>
                    </label>
                </div>
            </li>
            <li>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="print[]"
                               value="<?= print_link($objectId, 'price_list' . $prefix, '', true,
                                   !empty($addition) ? $addition : '') ?>">
                        <?= l('Ценник') ?>
                    </label>
                </div>
            </li>
        <?php endif; ?>
        <li role="separator" class="divider"></li>
        <li class="text-center">
            <button class="btn btn-sm btn-info print_now" type="button"
                    onclick="return print_now(this);"><?= l('Распечатать') ?></button>
        </li>
    </ul>
</div>
