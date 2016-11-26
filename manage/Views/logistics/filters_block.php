<form role="form" class="form-inline">
    <div class="form-group col-md-2">
        <label class="sr-only" for="whFromSelect"><?= l('Отправная точка') ?></label>
        <select class="multiselect form-control" data-buttonWidth="100%"
                data-nonSelectedText="<?= l('Отправная точка') ?>" name="whfrom[]" multiple="multiple" id="whFromSelect">
            <?php foreach ($warehouses as $whouse): ?>
                <option value="<?= $whouse['id'] ?>" <?= (in_array($whouse['id'], $wh_from)) ? 'selected' : '' ?> ><?= $whouse['title'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group col-md-2">
        <label class="sr-only" for="whToSelect"><?= l('Точка назначения') ?></label>
        <select class="multiselect form-control" data-buttonWidth="100%"
                data-nonSelectedText="<?= l('Точка назначения') ?>" name="whto[]" multiple="multiple" id="whToSelect">
            <?php foreach ($warehouses as $whouse): ?>
                <option value="<?= $whouse['id'] ?>" <?= (in_array($whouse['id'], $wh_to)) ? 'selected' : '' ?> ><?= $whouse['title'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group col-md-2">
        <label class="sr-only"><?= l('Дата') ?></label>
        <div class="input-group date">
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            <input type="text" name="date" class="daterangepicker form-control" value="<?= $date ?>" placeholder="<?= l('Дата') ?>">
        </div>
    </div>

    <div class="form-group col-md-2">
        <label class="sr-only"><?= l('Номер заказа или изделия') ?></label>
        <input type="text" name="number" class="form-control" value="<?= $number?>" placeholder="<?= l('Номер заказа или изделия') ?>">
    </div>

    <div class="col-md-2">
        <button onclick="send_get_form(this)" class="btn btn-primary"><i class="fa fa-filter"></i>&nbsp;&nbsp; <?= l('Фильтровать') ?></button>
    </div>

    <div class="col-md-2 m-t-xs">
        <div class="checkbox">
            <label>
                <input <?= (isset($_GET['serials_in_orders']) ? ' checked' : '') ?> value="1" type="checkbox" name="serials_in_orders"> <?= l('разгрупировать') ?>
            </label>
        </div>
    </div>
</form>
<div class="clearfix"></div>
