<div class="btn-group">
    <a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="#">
        <i class="fa fa-filter"></i> <i class="fa fa-caret-down"></i>
    </a>
    <ul class="dropdown-menu pull-right">
        <li>
            <form method="POST" style="width: 200px">
                <fieldset>
                    <div class="checkbox col-sm-12">
                        <label>
                            <input type="checkbox" name="types[]" value="repair" <?= isset($current['types']) && in_array(0, $current['types'])? 'checked': ''?>> <?= l('Заказы на ремонт') ?>
                        </label>
                        <label >
                            <input type="checkbox" name="types[]" value="warranty" <?= isset($current['warranty']) && in_array(1, $current['warranty'])? 'checked': ''?>> <?= l('Гарантийные') ?>
                        </label>
                        <label >
                            <input type="checkbox" name="types[]" value="not-warranty"<?= isset($current['not-warranty']) && in_array(1, $current['not-warranty'])? 'checked': ''?>> <?= l('Не гарантийные') ?>
                        </label>
                        <label>
                            <input type="checkbox" name="types[]" value="sale" <?= isset($current['types']) && in_array(3, $current['types'])? 'checked': ''?>> <?= l('Продажи') ?>
                        </label>
                    </div>

                    <div class="col-sm-3">
                        <button type="submit" class="btn btn-primary"> <?= l('Применить')?> </button>
                    </div>
                </fieldset>
            </form>
        </li>
    </ul>
</div>
