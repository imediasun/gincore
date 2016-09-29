<?php
/* $orderStatus = array(
    0 => array(
        'name' => l('Принят в ремонт'),
        'color' => 'B05DBB',
        'description' =>
    ),
    5 => array(
        'name' => l('В процессе ремонта'),
        'color' => '414CD2',
    ),
    27 => array(
        'name' => l('На согласовании'),
        'color' => '7ca319',
    ),
    30 => array(
        'name' => l('В удаленном сервисе'),
        'color' => '0A0E16',
    ),
    45 => array(
        'name' => l('Принят на доработку'),
        'color' => 'CFAFE7',
    ),
); */
$i = 1;
?>

<div class="row-fluid">
    <form method="POST" id="manager-setup">
        <fieldset>
            <?php foreach ($orderStatus as $id => $status): ?>
                <?php if ((in_array($id, $shows) && $id != 10) || (!$status['system'] && $status['use_in_manager'])): ?>
                    <div class="row-fluid">
                        <div class="col-sm-3">
                            <div class="form-group">
                                <input class="form-control" type="text" readonly value="<?= $status['name'] ?>"
                                       style="background-color: #<?= $status['color']; ?>; color:white; font-size: 0.9em; text-align: center"/>
                            </div>
                        </div>
                        <div class="col-sm-2">
                            <div class="form-group">
                                <input class="form-control" type="text" name="status[<?= $id ?>]"
                                       value="<?= empty($current[$id]) ? (isset($default[$id]) ? $default[$id] : 1) : $current[$id] ?>"
                                       placeholder=""/>
                            </div>
                        </div>
                        <div class="col-sm-7">
                            <?= $i++ == 1 ? l('Укажите максимальное количество дней, которое заказ может находиться в данном статусе без изменений') : ''; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
            <hr/>
            <div class="row-fluid">
                <div class="col-sm-3">
                    <div class="form-group">
                        <input class="form-control" type="text" readonly value="<?= l('Ожидает запчастей') ?>"
                               style="background-color: #90C8EE; color:white; font-size: 0.9em; text-align: center"/>
                    </div>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <input class="form-control" type="text" name="status[10]"
                               value="<?= empty($current[10]) ? $default[10] : $current[10] ?>"
                               placeholder=""/>
                    </div>
                </div>
                <div class="col-sm-7">
                    <?= l('Укажите максимальное количество дней, которое заказ может находиться в данном статусе без изменений')
                    . l(', если не указана дата поставки запчасти (заказ на ремонт не привязан к заказу поставщика)'); ?>
                </div>
            </div>
            <hr/>
            <div class="row-fluid">
                <div class="col-sm-3">
                    <?= l('Макс. количество') ?>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <input class="form-control" type="text" name="status_repair"
                               value="<?= empty($current['status_repair']) ? 0 : $current['status_repair'] ?>"
                               placeholder=""/>
                    </div>
                </div>
                <div class="col-sm-7">
                    <?= l('дней на отгрузку запчасти под ремонт'); ?>
                </div>
            </div>
            <div class="row-fluid">
                <div class="col-sm-3">
                    <?= l('Макс. количество') ?>
                </div>
                <div class="col-sm-2">
                    <div class="form-group">
                        <input class="form-control" type="text" name="status_sold"
                               value="<?= empty($current['status_sold']) ? 0 : $current['status_sold'] ?>"
                               placeholder=""/>
                    </div>
                </div>
                <div class="col-sm-7">
                    <?= l('дней на обработку запроса на покупку детали'); ?>
                </div>
            </div>
        </fieldset>
    </form>
</div>