<div class="panel-group" id="accordion-logistics">

    <div class="hpanel panel-collapse">
        <div class="panel-heading hbuilt showhide cursor-pointer">
            <div class="panel-tools">
                <a class=""><i class="fa fa-chevron-up"></i></a>
            </div>
            <?= l('Добавить логистическую цепочку') ?>
        </div>
        <div class="panel-body" style="display: none;">
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
                                <select data-multi="0" onchange="change_warehouse(this)"
                                        class="form-control select-warehouses-item-move"
                                        name="wh_id_destination[0]">
                                    <option value=""><?= l('Склад') ?></option>
                                    <?php foreach ($warehouses as $wh): ?>
                                        <option value="<?= $wh['id'] ?>"><?= $wh['title'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td class="text-center"><i class="fa fa-caret-right" aria-hidden="true" style="font-size: 18px"></i></td>
                            <td>
                                <select data-multi="1" onchange="change_warehouse(this)"
                                        class="form-control select-warehouses-item-move"
                                        name="wh_id_destination[1]">
                                    <option value=""><?= l('Склад') ?></option>
                                    <?php foreach ($warehouses as $wh): ?>
                                        <option value="<?= $wh['id'] ?>"><?= $wh['title'] ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </td>
                            <td class="text-center"><i class="fa fa-caret-right" aria-hidden="true" style="font-size: 18px"></i></td>
                            <td>
                                <select data-multi="2" onchange="change_warehouse(this)"
                                        class="form-control select-warehouses-item-move"
                                        name="wh_id_destination[2]">
                                    <option value=""><?= l('Склад') ?></option>
                                    <?php foreach ($warehouses as $wh): ?>
                                        <option value="<?= $wh['id'] ?>"><?= $wh['title'] ?></option>
                                    <?php endforeach; ?>

                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding-top: 5px">
                                <div class="form-group">
                                    <select data-buttonWidth="100%" data-nonSelectedText="<?= l('Локация') ?>"
                                            class="multiselect text-left form-control select-location0"
                                            name="location[0]"></select>
                                </div>
                            </td>
                            <td></td>
                            <td>

                            </td>
                            <td></td>
                            <td style="padding-top: 5px">
                                <div class="form-group">
                                    <select data-buttonWidth="100%" data-nonSelectedText="<?= l('Локация') ?>"
                                            class="multiselect text-left form-control select-location2"
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

<?php if($chains): ?>
    <div class="m-t-lg m-b-sm">
    <div class="chains_new m-t-sm">
        <div class="chain_item">
            <div class="chain_number">№</div>
            <div class="chain_logistics"><?= l('Отправная точка') ?></div>
            <div class="chain_logistics"><?= l('Логистика') ?></div>
            <div class="chain_logistics"><?= l('Точка назначения') ?></div>
            <div class="chain_status"><?= l('Удалить') ?></div>
        </div>
    </div>
    <?php $i=1; ?>
    <?php foreach ($chains as $chain): ?>
        <div class="chains_new m-t-sm">
            <div class="chain_item <?= (!$chain['avail'] ? 'danger' : '') ?>">
                <div class="chain_number"><?= $i++ ?></div>

                <div class="chain_logistics with_bordered with_arrrow first">
                    <?= $warehouses[$chain['from_wh_id']]['title'] ?>
                    (<?= $warehouses[$chain['from_wh_id']]['locations'][$chain['from_wh_location_id']]['name'] ?>)
                </div>
                <div class="chain_logistics with_bordered with_arrrow">
                    <?= $warehouses[$chain['logistic_wh_id']]['title'] ?>
                    <?php if ($chain['logistic_wh_location_id']): ?>
                        (<?= $warehouses[$chain['logistic_wh_id']]['locations'][$chain['logistic_wh_location_id']]['name'] ?>)
                    <?php endif; ?>
                </div>
                <div class="chain_logistics with_bordered last">
                    <?= $warehouses[$chain['to_wh_id']]['title'] ?>
                    (<?= $warehouses[$chain['to_wh_id']]['locations'][$chain['to_wh_location_id']]['name'] ?>)
                </div>
                <div class="chain_status">
                    <?php if ($chain['avail']): ?>
                        <i class="glyphicon glyphicon-remove cursor-pointer"
                           title="<?= l('Удалить цепочку') ?>"
                           onclick="remove_chain(this, <?= $chain['id'] ?>)"></i>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endforeach; ?>

</div>
<?php else: ?>
    <div class="col-md-12 center">
        <div style="padding-top: 5%; padding-bottom: 5%;">
                <span style="border-bottom: 1px grey dashed;">
                    <?= l('Нет цепочек') ?>
                </span>
        </div>
    </div>
<?php endif; ?>