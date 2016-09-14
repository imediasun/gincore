<div>
    <form class="form-inline well">
        <?= $manager_filter ?>
        <?= $service_filter ?>
        <div class="input-group">
            <p class="form-control-static" style="display: inline-block; margin-right: 10px;">
                <?= l('Статистика за') ?>
            </p>
            <span class="input-group-btn">
                        <input type="text" placeholder="<?= l('Дата') ?>" name="date"
                               class="daterangepicker form-control "
                               value="<?= $get_date ?>"/>
                        </span>
        </div>
        <div class="input-group" style="margin-right: 0">
            <input type="submit" class="btn btn-primary" value="<?= l('Фильтровать') ?>">
            <button type="button" class="btn fullscreen"><i class="fa fa-arrows-alt"></i></button>
            <button type="button" class="btn btn-primary"
                    onclick="return manager_setup(this);"><?= l('Настройки') ?></button>
        </div>
    </form>
</div>
<?= $filter_stats ?>
<br>
<style>
    input[name='date'] {
        width: 170px
    }
    .form-inline .input-group {
        margin: 5px 5px 5px 0;
    }
</style>