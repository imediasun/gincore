<h2><?= $title ?> (<?= l($current) ?>)</h2>
<form action="<?= $this->all_configs['prefix'] ?>/custom_status/update" method="POST">
    <input type="hidden" name="custom-statuses" value=""/>
    <input type="hidden" name="order-type" value="<?= $current ?>"/>

    <table class="table table-borderless">
        <thead>
        <tr>
            <th></th>
            <th width="40%"><?= l('Наименование статуса') ?></th>
            <th width="10%"><?= l('Цвет') ?></th>
            <th class="text-center"><?= l('Использовать в менеджере заказов') ?><?= InfoPopover::getInstance()->createQuestion('l_use_in_orders_manager') ?></th>
            <th class="text-center" title="<?= l('Активный') ?>">
                <i class="fa fa-power-off" aria-hidden="true"></i>
            </th>
        </tr>
        </thead>
        <tbody>
        <?php $id=1; ?>
        <?php foreach ($statuses as $status): ?>
            <tr>
                <td>
                    <?= $id++ ?>
                </td>
                <td>
                    <input type="hidden" name="ids[]" value="<?= $status['id'] ?>"/>
                    <input class="form-control" type="text"
                           name="name[<?= $status['id'] ?>]" <?= $status['system'] ? 'disabled' : '' ?>
                           value="<?= h($status['name']) ?>"  maxlength="20"/>
                </td>
                <td class="text-center">
                    <div id="demo_endis" class="input-group colorpicker-auto colorpicker-component colorpicker-element">
                        <input class="form-control colorpicker" type="text" name="color[<?= $status['id'] ?>]"
                               placeholder='<?= l('введите цвет') ?>' value="#<?= h($status['color']) ?>">
                        <span class="input-group-addon">
                            <i class='show-color' style="background-color: #<?= h($status['color']) ?>"></i>
                        </span>
                    </div>
                </td>
                <td class="text-center">
                    <center>
                        <input class="checkbox" type="checkbox"
                               name="use_in_manager[<?= $status['id'] ?>]" <?= $status['use_in_manager'] != 0 && !in_array($status['status_id'], $this->all_configs['configs']['order-not-show-in-manager'])? 'checked' : '' ?>  <?= $status['system'] ? 'disabled' : '' ?>/>
                    </center>
                </td>
                <td class="text-center">
                    <center>
                        <input class="checkbox" type="checkbox"
                               name="active[<?= $status['id'] ?>]" <?= $status['active'] != 0 ? 'checked' : '' ?>  <?= $status['system'] ? 'disabled' : '' ?>/>
                    </center>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
        <tfoot>
        <tr>
            <td>

            </td>
            <td>
                <input type="hidden" name="ids[]" value="new"/>
                <input class="form-control" type="text" name="name[new]" value=""  maxlength="20"
                       placeholder="<?= l('Введите название нового статуса') ?>"/>
            </td>
            <td>
                <div id="demo_endis" class="input-group colorpicker-auto colorpicker-component colorpicker-element">
                    <input class="form-control colorpicker" type="text" name="color[new]"
                           placeholder='<?= l('введите цвет') ?>' value="#3498db">
                    <span class="input-group-addon">
                        <i class='show-color' style="background-color: #ffffff"></i>
                    </span>
                </div>
            </td>
            <td style="text-align: center">
                <center>
                    <input class="checkbox" type="checkbox" name="use_in_manager[new]" checked/>
                </center>
            </td>
            <td style="text-align: center">
                <center>
                    <input class="checkbox" type="checkbox" name="active[new]" checked/>
                </center>
            </td>
        </tr>
        <tr class="no-borders">
            <td></td>
            <td> <?= l('*не более 20 символов') ?> </td>
            <td> </td>
            <td style="text-align: center"></td>
            <td style="text-align: center"></td>
        </tr>
        </tfoot>
    </table>
    <div class="form-group">
        <input type="submit" name='update-status' value="<?= l('save') ?>" class="btn btn-primary">
    </div>
</form>
<script>
    function init_colorpickers() {
        $('.colorpicker.colorpicker-element').colorpicker('destroy');
        $('.colorpicker-auto').colorpicker();
    }
    jQuery(document).ready(function () {
        init_colorpickers();
    });
</script>
