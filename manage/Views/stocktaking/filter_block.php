<div class="row row-15">
    <form method="post">
        <input type="hidden" name="stocktaking" value="<?= $stocktaking['id'] ?>" />
        <table class="table table-borderless stocktaking-filters">
            <tbody>
            <tr>
                <td>
                    <label><?= l('Склад') ?></label>
                </td>
                <td>
                    <select class="form-control" readonly disabled="disabled" name="warehouse">
                        <?php if (!empty($warehouses)): ?>
                            <?php foreach ($warehouses as $warehouse): ?>
                                <option <?= $warehouse['id'] == $current_warehouse ? 'selected' : '' ?>
                                    value="<?= $warehouse['id'] ?>"><?= $warehouse['title'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </td>
                <td></td>
                <td></td>
                <td>
                    <label><?= l('Недостача') ?>:</label>
                    <span class="js-deficit"><?= max(0, count($stocktaking['checked_serials']['deficit'])) ?></span><?= l('шт.') ?>
                </td>
                <td>
                    <button type="button" class="btn btn-primary" name="export-deficit"><?= l('Экспорт') ?></button>
                </td>
            </tr>
            <tr>
                <td>
                    <label><?= l('Локация') ?></label>
                </td>
                <td>
                    <select class="form-control" readonly disabled="disabled" name="location">
                        <?php if (!empty($locations)): ?>
                            <?php foreach ($locations as $location): ?>
                                <option <?= $location['id'] == $current_location ? 'selected' : '' ?>
                                    value="<?= $location['id'] ?>"><?= $location['name'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

                </td>
                <td></td>
                <td></td>
                <td>
                    <label><?= l('Излишек') ?>:</label>
                    <span class="js-surplus"><?= max(0, $count - count($stocktaking['checked_serials']['both'])) ?></span><?= l('шт.') ?>
                </td>
                <td>
                    <button type="button" class="btn btn-primary" name="export-surplus"><?= l('Экспорт') ?></button>
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td>
                    <lable><?= l('Серийный N') ?></lable>
                </td>
                <td>

                    <div class="input-group col-sm-6">
                        <input class="form-control" name="serial" placeholder="<?= l('серийный номер') ?>" value="<?= ((isset($_GET['serial']) && !empty($_GET['serial'])) ? htmlspecialchars(urldecode($_GET['serial'])) : '') ?>" />
                        <div class="input-group-btn">
                            <input class="btn" type="submit" name="filter-serial" value="<?= l('Поиск') ?>" />
                        </div>
                    </div>
                    <?= $last['message'] ?>
                    <?php if($last['result'] == CHECK_BOTH): ?>
                        <i class="fa fa-check" aria-hidden="true" style="color: green"></i>
                    <?php else: ?>
                        <span class="color:red"><?= l('Излишек') ?><i class="fa fa-times" aria-hidden="true" style="color: red"></i></span>
                    <?php endif; ?>
                </td>
                <td></td>
                <td></td>

            </tr>
            </tbody>
        </table>
    </form>
</div>
