<?php $url = $this->all_configs['prefix'] . (isset($this->all_configs['arrequest'][0]) ? $this->all_configs['arrequest'][0] . '/' : '') . 'export'; ?>
<div class="row-fluid">
    <form method="post">
        <input type="hidden" name="stocktaking" value="<?= $stocktaking['id'] ?>"/>
        <table class="table table-borderless stocktaking-filters">
            <tbody>
            <tr>
                <td style="width: 7%; text-align: right">
                    <label><?= l('Склад') ?></label>
                </td>
                <td style="width: 12%">
                    <select class="form-control" readonly disabled="disabled" name="warehouse">
                        <?php if (!empty($warehouses)): ?>
                            <?php foreach ($warehouses as $warehouse): ?>
                                <option <?= $warehouse['id'] == $current_warehouse ? 'selected' : '' ?>
                                    value="<?= $warehouse['id'] ?>"><?= $warehouse['title'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </td>
                <td style="width: 3%"></td>
                <td></td>
                <td></td>
                <td style="width: 3%"></td>
                <td style="width: 15%; text-align: right">
                    <label><?= l('Недостача') ?>:
                    <?= max(0, $count - count($stocktaking['checked_serials']['both'])) ?>&nbsp;<?= l('шт.') ?>
                    </label>
                </td>
                <td style="width: 10%">
                    <a target='_blank' href='<?= $url . '?act=export-deficit&stocktaking-id=' . $stocktaking['id'] ?>'
                       class="btn btn-primary"><?= l('Экспорт') ?></a>
                </td>
            </tr>
            <tr>
                <td style="text-align: right">
                    <label><?= l('Локация') ?></label>
                </td>
                <td>
                    <select class="form-control multiselect" readonly disabled="disabled" name="location" multiple="multiple">
                        <?php if (!empty($locations)): ?>
                            <?php foreach ($locations as $location): ?>
                                <option <?= in_array($location['id'], $current_locations) ? 'selected' : '' ?>
                                    value="<?= $location['id'] ?>"><?= $location['name'] ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>

                </td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: right;">
                    <label><?= l('Излишек') ?>:
                    <?= max(0, count($stocktaking['checked_serials']['surplus'])) ?>&nbsp;<?= l('шт.') ?>
                    </label>
                </td>
                <td>
                    <a target='_blank' href='<?= $url . '?act=export-surplus&stocktaking-id=' . $stocktaking['id'] ?>'
                       class="btn btn-primary"><?= l('Экспорт') ?></a>
                </td>
            </tr>
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td style="text-align: right; white-space: nowrap">
                    <label><?= l('Серийный N') ?></label>
                </td>
                <td>
                    <div class="input-group col-sm-10">
                        <input autofocus class="form-control" name="serial" placeholder="<?= l('серийный номер') ?>"
                               value="<?= ((isset($_GET['serial']) && !empty($_GET['serial'])) ? htmlspecialchars(urldecode($_GET['serial'])) : '') ?>"/>
                        <div class="input-group-btn">
                            <input class="btn" type="submit" name="filter-serial" value="<?= l('Поиск') ?>"/>
                        </div>
                    </div>
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <tr>
                <td colspan="8" style="padding-top: 0">
                    <div class="input-group col-sm-12" style="text-align: center">
                        <?php switch ($last['result']): ?>
<?php case CHECK_BOTH: ?>
                                <span style='color: green'><?= $last['message'] ?></span>
                                <i class="fa fa-check" aria-hidden="true" style="color: green"></i>
                                <?php break; ?>
                            <?php case CHECK_SURPLUS: ?>
                                <span style='color: #FF7F27'><?= $last['message'] ?></span>
                                <span class="color: #FF7F27"><?= l('Излишек') ?><i class="fa fa-times" aria-hidden="true"
                                                                                   style="color: #FF7F27"></i></span>
                                <?php break; ?>
                            <?php default: ?>
                                <span style='color: red'><?= $last['message'] ?></span>
                            <?php endswitch; ?>
                    </div>
                </td>
            </tr>
            </tbody>
        </table>
    </form>
</div>
<style>
    .multiselect-btn-group{
        width: 200px !important;
    }

    .multiselect-btn-group > button{
        width: 200px !important;
        text-align: left;
    }
</style>
<script>
    jQuery(document).ready(function () {
        init_multiselect();
    });
</script>
