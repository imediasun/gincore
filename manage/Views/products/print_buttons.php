<div class="btn-group">
    <button type="button" class="btn btn-default dropdown-toggle dropdown-btn" data-toggle="dropdown"
            aria-haspopup="true"
            aria-expanded="false">
        <i class="fa fa-print"></i> <span class="caret"></span>
    </button>
    <ul class="keep-open dropdown-menu print_menu">
            <li>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="print[]"
                               value="<?= print_link(null, 'price_list', '', true, '') ?>">
                        <?= l('Ценник') ?>
                    </label>
                </div>
            </li>
        <li role="separator" class="divider"></li>
        <li class="text-center">
            <button class="btn btn-sm btn-info print_now" type="button"
                    onclick="return print_label(this);"><?= l('Распечатать') ?></button>
        </li>
    </ul>
</div>
