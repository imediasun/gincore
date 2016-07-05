<div class="row-fluid">
    <div class="col-sm-6">
        <h2><?= l('Создать новую') ?></h2>
        <form method="POST">
            <input type="hidden" name="new-stocktaking"/>
            <table class="table table-borderless stocktaking-filters">
                <tbody>
                <tr>
                    <td>
                        <label><?= l('Склад') ?></label>
                    </td>
                    <td>
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
                    <td>
                        <label><?= l('Локация') ?></label>
                    </td>
                    <td>
                        <select required class="form-control select-location" name="locations[]">
                            <?= $whSelect ?>
                        </select>

                    </td>
                </tr>
                <tr>
                    <td>
                    </td>
                    <td>
                        <button class='btn btn-primary' type="submit"><?= l('Новая инвентаризация') ?></button>
                    </td>
                </tr>
                </tbody>
            </table>
        </form>
    </div>
</div>
<div class="row-fluid">

    <?php if (!empty($stocktakings)): ?>
        <div class="col-sm-12">
            <h2><?= l('Выбрать из существующих') ?></h2>
            <table class="table  table-striped stocktaking-filters">
                <thead>
                <tr>
                    <td>
                        ID
                    </td>
                    <td>
                        <?= l('Статус') ?>
                        <?= $stocktaking['history'] ? l('Сохраненная') : l('Активная') ?>
                    </td>
                    <td>
                        <?= l('Создана') ?>
                    </td>
                    <td>
                        <?= l('Склад') ?>
                    </td>
                    <td>

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
                            <?= "{$stocktaking['warehouse']}({$stocktaking['location']})" ?>
                        </td>
                        <td>
                            <a class="btn btn-default" href="?stocktaking=<?= $stocktaking['id'] ?>"
                            <?php if($stocktaking['history']): ?>
                               onclick="return confirm('<?= l('При выборе станет текущей!') ?>')"
                            <?php endif; ?>
                            ><?= l('Выбрать') ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
