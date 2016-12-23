<div class="row-fluid">
    <div class="col-sm-12" style="text-align: center">
        <h2><?= l('Создать новую инвентаризацию') ?></h2>
    </div>
    <div class="col-sm-12">
        <form method="POST">
            <input type="hidden" name="new-stocktaking"/>
            <center>
                <table class="table table-borderless stocktaking-filters" style="width:320px; margin-left: -100px">
                    <tbody>
                    <tr>
                        <td style="text-align: right">
                            <label><?= l('Склад б/л') ?></label>
                        </td>
                        <td style="width: 200px">
                            <select required onchange="change_warehouse(this)" class="form-control" name="warehouses[]">
                                <option> <?= l('Выбрать') ?></option>
                                <?php if (!empty($warehouses)): ?>
                                    <?php foreach ($warehouses as $warehouse): ?>
                                        <option value="<?= $warehouse['id'] ?>"><?= $warehouse['title'] ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: right">
                            <label><?= l('Локация') ?></label>
                        </td>
                        <td>
                            <select required class="multiselect form-control select-location" name="locations[]"
                                    multiple="multiple">
                                <?= $whSelect ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        </td>
                        <td>
                            <button class='btn btn-primary' type="submit" style="width: 100%"><?= l('Новая инвентаризация') ?></button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </center>
        </form>
    </div>
</div>
<div class="row-fluid">
    <div class="col-sm-12" style="text-align: center">
        <h2><?= l('Выбрать из существующих') ?></h2>
    </div>
    <div class="col-sm-12">
        <?php if (!empty($stocktakings)): ?>
            <table class="table  table-striped stocktaking-filters">
                <thead>
                <tr>
                    <td width="5%">
                        ID
                    </td>
                    <td width="5%">
                        <?= l('Статус') ?>
                        <?= $stocktaking['history'] ? l('Сохраненная') : l('Текущая') ?>
                    </td>
                    <td width="5%">
                        <?= l('Создана') ?>
                    </td>
                    <td>
                        <?= l('Склад') ?>
                    </td>
                    <td width="5%">

                    </td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($stocktakings as $stocktaking): ?>
                    <tr>
                        <td>
                            <?= $stocktaking['id'] ?>
                        </td>
                        <td>
                            <?= $stocktaking['history'] ? l('Сохраненная') : l('Активная') ?>
                        </td>
                        <td>
                            <?= $stocktaking['history'] ? $stocktaking['saved_at'] : $stocktaking['created_at'] ?>
                        </td>
                        <td>
                            <?= "{$stocktaking['warehouse']}(" . implode(', ', $stocktaking['locations']) . ")" ?>
                        </td>
                        <td>
                            <a class="btn btn-default" href="?stocktaking=<?= $stocktaking['id'] ?>"
                                <?php if ($stocktaking['history']): ?>
                                    onclick="return confirm('<?= l('При выборе станет текущей!') ?>')"
                                <?php endif; ?>
                            ><?= l('Выбрать') ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <center><p><?= l('Нет сохраненных инвентаризаций') ?></p></center>
        <?php endif; ?>
    </div>
</div>
<style>
    .multiselect-btn-group, button.select-location {
        width: 200px !important;
    }

    button.select-location {
        text-align: left;
    }
</style>
<script>
    jQuery(document).ready(function () {
        init_multiselect();
    });
</script>