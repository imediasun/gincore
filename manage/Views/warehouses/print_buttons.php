<div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle dropdown-btn<?= $prefix ?>" data-toggle="dropdown" aria-haspopup="true"
            aria-expanded="false">
        <i class="fa fa-print"></i> <span class="caret"></span>
    </button>
    <ul class="keep-open dropdown-menu print_menu">
        <li>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="print[]"
                           value="<?= print_link($objectId, 'label' . $prefix, '', true) ?>">
                    <?= l('Этикетка (штрих-код)') ?>
                </label>
            </div>
        </li>
        <li>
            <div class="checkbox">
                <label>
                    <input type="checkbox" name="print[]"
                           value="<?= print_link($objectId, 'price_list' . $prefix, '', true) ?>">
                    <?= l('Ценник') ?>
                </label>
            </div>
        </li>
        <?php if ($prefix == '_location'): ?>
            <li>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="print[]"
                               value="<?= print_link($objectId, 'location', '', true) ?>">
                        <?= l('Этикетки') ?>
                    </label>
                </div>
            </li>
        <?php endif; ?>
        <li role="separator" class="divider"></li>
        <li class="text-center">
            <button class="btn btn-sm btn-info print_now" type="button" onclick="return print_now(this);"><?= l('Распечатать') ?></button>
        </li>
    </ul>
</div>
