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
                    <input type="text" placeholder="<?= l('Дата') ?>" name="date" class="daterangepicker form-control "
                           value="<?= $get_date ?>"/>
                </td>
                <td>
                    <input type="submit" class="btn btn-primary" value="<?= l('Фильтровать') ?>">
                </td>
                <td>
                    <button type="button" class="btn fullscreen"><i class="fa fa-arrows-alt"></i></button>
                </td>
                <td>
                    <button type="button" class="btn btn-primary  pull-right "
                            onclick="return manager_setup(this);"><?= l('Настройки') ?></button>
                </td>
            </tr>
        </table>
    </form>
</div>
<?= $filter_stats ?>
<br>
