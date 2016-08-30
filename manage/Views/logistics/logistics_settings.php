<table class="table chains table-compact">
    <tbody>
    <?php foreach ($chains as $chain): ?>
        <tr
            <?= (!$chain['avail'] ? ' class="danger"' : '') ?>>
            <td><?= $warehouses[$chain['from_wh_id']]['title'] ?>
                (<?= $warehouses[$chain['from_wh_id']]['locations'][$chain['from_wh_location_id']]['name'] ?>)
            </td>
            <td class="chain-body-arrow"></td>
            <td><?= $warehouses[$chain['logistic_wh_id']]['title'] ?>
                <?php if ($chain['logistic_wh_location_id']): ?>
                    (<?= $warehouses[$chain['logistic_wh_id']]['locations'][$chain['logistic_wh_location_id']]['name'] ?>)
                <?php endif; ?>
            </td>
            <td class="chain-body-arrow"></td>
            <td><?= $warehouses[$chain['to_wh_id']]['title'] ?>
                (<?= $warehouses[$chain['to_wh_id']]['locations'][$chain['to_wh_location_id']]['name'] ?>)
            </td>
            <td class="chain-body-arrow"></td>
            <td>
                <?php if ($chain['avail']): ?>
                    <i class="glyphicon glyphicon-remove cursor-pointer"
                       title="<?= l('Удалить цепочку') ?>"
                       onclick="remove_chain(this, <?= $chain['id'] ?>)"></i>
                <?php endif; ?>
            </td>
        </tr>
        <tr>
            <td colspan="7"></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<div class="panel-group" id="accordion-logistics">
    <div class="panel panel-default">
        <div class="panel-heading">
            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion-logistics"
               href="#collapseLogistics-0"><?= l('Добавить логистическую цепочку') ?></a>
        </div>
        <div id="collapseLogistics-0" class="panel-collapse collapse">
            <div class="panel-body">
                <form class="container-fluid">
                    <div class="row">
                        <table class="table table-borderless table-vpaddingless">
                            <tr>
                                <td style="width: 30%">
                                    <p><?= l('Укажите отправную точку (локацию), при перемещении на которую будет автоматически формироватся логистическая цепочка') ?></p>
                                </td>
                                <td></td>
                                <td style="width: 30%">
                                    <p><?= l('Укажите склад логистики') ?></p>
                                </td>
                                <td></td>
                                <td style="width: 30%">
                                    <p><?= l('Укажите точку назначения (локацию), при перемещении на которую будет закрываться логистическая цепочка') ?></p>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <label><?= l('Склад') ?>:</label>
                                </td>
                                <td></td>
                                <td>
                                    <label><?= l('Склад') ?>:</label>
                                </td>
                                <td></td>
                                <td>
                                    <label><?= l('Склад') ?>:</label>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <select data-multi="0" onchange="change_warehouse(this)"
                                            class="form-control select-warehouses-item-move"
                                            name="wh_id_destination[0]">
                                        <option value=""> -- <?= l('выбирите') ?> --</option>
                                        <?php foreach ($warehouses as $wh): ?>
                                            <option value="<?= $wh['id'] ?>"><?= $wh['title'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td class="arrow"><i class="fa fa-caret-right" aria-hidden="true"></i></td>
                                <td>
                                    <select data-multi="1" onchange="change_warehouse(this)"
                                            class="form-control select-warehouses-item-move"
                                            name="wh_id_destination[1]">
                                        <option value=""> -- <?= l('выбирите') ?> --</option>
                                        <?php foreach ($warehouses as $wh): ?>
                                            <option value="<?= $wh['id'] ?>"><?= $wh['title'] ?></option>
                                        <?php endforeach; ?>

                                    </select>
                                </td>
                                <td class="arrow"><i class="fa fa-caret-right" aria-hidden="true"></i></td>
                                <td>
                                    <select data-multi="2" onchange="change_warehouse(this)"
                                            class="form-control select-warehouses-item-move"
                                            name="wh_id_destination[2]">
                                        <option value=""> -- <?= l('выбирите') ?> --</option>
                                        <?php foreach ($warehouses as $wh): ?>
                                            <option value="<?= $wh['id'] ?>"><?= $wh['title'] ?></option>
                                        <?php endforeach; ?>

                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding-top: 5px">
                                    <div class="form-group">
                                        <label><?= l('Локация') ?>:</label>
                                        <select class="multiselect form-control select-location0"
                                                name="location[0]"></select>
                                    </div>
                                </td>
                                <td></td>
                                <td>

                                </td>
                                <td></td>
                                <td style="padding-top: 5px">
                                    <div class="form-group">
                                        <label class="control-label"><?= l('Локация') ?>:</label>
                                        <select class="multiselect form-control select-location2"
                                                name="location[2]"></select>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="row">
                        <div class="col-sm-12">
                            <input class="btn btn-primary" type="button" value="<?= l('Сохранить') ?>"
                                   onclick="create_chain(this)"/>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
