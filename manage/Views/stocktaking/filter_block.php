<?php $url = $this->all_configs['prefix'] . (isset($this->all_configs['arrequest'][0]) ? $this->all_configs['arrequest'][0] . '/' : '') . 'export'; ?>
<div class="row row-15">
    <form method="post">
        <input type="hidden" name="stocktaking" value="<?= $stocktaking['id'] ?>"/>
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
                    <span class="js-deficit"><?= max(0,
                            $count - count($stocktaking['checked_serials']['both'])) ?></span><?= l('шт.') ?>
                </td>
                <td>
                    <a target='_blank' href='<?= $url . '?act=export-deficit&stocktaking-id=' . $stocktaking['id'] ?>'
                       class="btn btn-primary"><?= l('Экспорт') ?></a>
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
                    <span class="js-surplus"><?= max(0,
                            count($stocktaking['checked_serials']['surplus'])) ?></span><?= l('шт.') ?>
                </td>
                <td>
                    <a target='_blank' href='<?= $url . '?act=export-surplus&stocktaking-id=' . $stocktaking['id'] ?>'
                       class="btn btn-primary"><?= l('Экспорт') ?></a>
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
                        <input class="form-control" name="serial" placeholder="<?= l('серийный номер') ?>"
                               value="<?= ((isset($_GET['serial']) && !empty($_GET['serial'])) ? htmlspecialchars(urldecode($_GET['serial'])) : '') ?>"/>
                        <div class="input-group-btn">
                            <input class="btn" type="submit" name="filter-serial" value="<?= l('Поиск') ?>"/>
                        </div>
                    </div>
                    <?php switch ($last['result']): ?>
<?php case CHECK_BOTH: ?>
                            <span style='color: green'><?= $last['message'] ?></span>
                            <i class="fa fa-check" aria-hidden="true" style="color: green"></i>
                            <?php break; ?>
                        <?php case CHECK_SURPLUS: ?>
                            <span style='color: yellow'><?= $last['message'] ?></span>
                            <span class="color: yellow"><?= l('Излишек') ?><i class="fa fa-times" aria-hidden="true"
                                                                              style="color: yellow"></i></span>
                            <?php break; ?>
                        <?php default: ?>
                            <span style='color: red'><?= $last['message'] ?></span>
                        <?php endswitch; ?>
                </td>
                <td></td>
                <td></td>

            </tr>
            </tbody>
        </table>
    </form>
</div>
