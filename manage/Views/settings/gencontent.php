<h3>«<?= $pp['title'] ?>»</h3>

<?php if (!isset($this->all_configs['arrequest'][2])): ?>

    <?php if (isset($pp['description'])): ?>
        <h5 class="text-info"><?= htmlspecialchars($pp['description']) ?></h5>
    <?php endif; ?>

    <br>
    <form action="<?= $this->all_configs['prefix'] ?>settings/<?= $pp['id'] ?>/update" method="POST">
        <div class="form-group">
            <label><?= l('sets_param') ?></label>: <?= $pp['name'] ?>
        </div>
        <?php
        switch ($pp['name']):
            case 'default_order_warranty':
                ?>
                <div class="form-group">
                    <label><?= l('sets_value') ?>:</label>
                    <div class="input-group">
                        <select class="form-control" name="value">
                            <option value=""><?= l('Без гарантии') ?></option>
                            <?php foreach ($orderWarranties as $warranty): ?>
                                <option <?= ($pp['value'] == intval($warranty) ? 'selected' : '') ?>
                                    value="<?= intval($warranty) ?>"><?= intval($warranty) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="input-group-addon"><?= l('мес') ?></div>
                    </div>
                </div>
                <?php break; ?>

            <?php case 'time_zone': ?>
            <div class="form-group">
                <label><?= l('Континент') ?>:</label>
                <select id="tz_continents" class="form-control" name="continent">
                    <option value="all"><?= l('Все') ?></option>
                    <?php foreach ($timeZones as $cont => $zones): ?>
                        <option <?= (in_array($pp['value'], $zones) ? 'selected' : '') ?>
                            value="<?= $cont ?>"><?= $cont ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label><?= l('Временная зона') ?>:</label>
                <select id="tz_zones" class="form-control" name="time_zone">
                    <?php foreach ($timeZones as $cont => $zones): ?>
                        <?php foreach ($zones as $zone): ?>
                            <option data-continent="<?= $cont ?>" <?= ($pp['value'] == $zone ? 'selected' : '') ?>
                                    value="<?= $zone ?>"><?= $zone ?></option>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php break; ?>
        <?php case 'crm-requests-statuses': ?>
            <?php $values = json_decode($pp['value'], true); ?>
            <input type="hidden" name="crm-requests-statuses" value="" />
            <table class="table">
                <thead>
                <tr>
                    <th>id</th>
                    <th><?= l('Наименование статуса') ?></th>
                    <th><?= l('Закрывать заявку') ?></th>
                    <th><?= l('Удалить') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($values as $id => $value): ?>
                    <tr>
                        <td>
                            <?= $id ?>
                        </td>
                        <td>
                            <input class="form-control" type="text" name="name[<?= $id ?>]"
                                   value="<?= h($value['name']) ?>"/>
                        </td>
                        <td>
                            <input class="form-control" type="checkbox"
                                   name="close[<?= $id ?>]" <?= $value['active'] == 0 ? 'checked' : '' ?> />
                        </td>
                        <td>
                            <input class="form-control" type="checkbox" name="delete[<?= $id ?>]"/>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <thead>
                <tr>
                    <td>
                    </td>
                    <td>
                        <input class="form-control" type="text" name="name[new]" value="" placeholder="<?= l('Введите название нового статуса') ?>"/>
                    </td>
                    <td>
                        <input class="form-control" type="checkbox" name="close[new]"/>
                    </td>
                    <td>
                    </td>
                </tr>
                </thead>
            </table>
            <?php break; ?>

        <?php default: ?>
            <div class="form-group">
                <label><?= l('sets_value') ?>:</label>
                <input type="text" class="form-control"
                       id="inputParam" <?= ($pp['ro'] == '1' ? 'disabled="disabled"' : '') ?>
                       name="value" value="<?= $pp['value'] ?>"/>
            </div>
            <?php break; ?>

        <?php endswitch; ?>

        <div class="form-group">
            <input type="submit" value="<?= l('save') ?>" class="btn btn-primary">
        </div>
    </form>

<?php endif; ?>
