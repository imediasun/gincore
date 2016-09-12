<div>
    <form class="form-inline well">
        <table class="table table-borderless" style="margin-bottom: 0px">
            <tr>
                <td>
                    <?= $manager_filter ?>
                </td>
                <td>
                    <?= $service_filter ?>
                </td>
                <td>
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
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <input type="submit" class="btn btn-primary" value="<?= l('Фильтровать') ?>">
                    <button type="button" class="btn fullscreen"><i class="fa fa-arrows-alt"></i></button>
                    <button type="button" class="btn btn-primary"
                            onclick="return manager_setup(this);"><?= l('Настройки') ?></button>
                </td>
            </tr>
        </table>
    </form>
</div>
<?= $filter_stats ?>
<br>
